<?php

session_start();
include "../acc/connect.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['pdfFile'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'No file uploaded'
    ]);
    exit();
}

$user_id = $_SESSION["user_id"];
$file = $_FILES['pdfFile'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'error' => 'File upload error'
    ]);
    exit();
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);

if ($mime !== 'application/pdf') {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid PDF file'
    ]);
    exit();
}

if ($file['size'] > 5000000) {
    echo json_encode([
        'success' => false,
        'error' => 'File too large. Maximum size is 5MB.'
    ]);
    exit();
}

$temp_dir = sys_get_temp_dir();
$temp_file = $temp_dir . DIRECTORY_SEPARATOR . uniqid() . '.pdf';

if (!move_uploaded_file($file['tmp_name'], $temp_file)) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save uploaded file'
    ]);
    exit();
}

try {

    require_once __DIR__ . '/../vendor/autoload.php';

    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($temp_file);
    $text = $pdf->getText();

    $debug = parseScheduleFromPDF($text);
    $schedules = $debug['schedules'];
    $debug_info = $debug['debug'];
    $debug_info['courses_inserted'] = [];
    $debug_info['schedules_inserted'] = [];
    $debug_info['duplicates_skipped'] = [];
    $debug_info['database_errors'] = [];

    if (empty($schedules)) {
        unlink($temp_file);
        echo json_encode([
            'success' => false,
            'error' => 'No schedule data found in PDF.',
            'preview' => substr($text, 0, 2000),
            'debug' => $debug_info
        ]);
        exit();
    }

    $courses_query = $conn->query("SELECT course_id, course_code FROM courses WHERE user_id = $user_id");

    $user_courses = [];
    while ($row = $courses_query->fetch_assoc()) {
        $user_courses[strtoupper($row['course_code'])] = (int)$row['course_id'];
    }

    $inserted_count = 0;

    foreach ($schedules as $schedule) {
        $course_code = strtoupper($schedule['course_code']);

        if (!isset($user_courses[$course_code])) {
            $course_name = $conn->real_escape_string($schedule['course_name']);
            $instructor = $conn->real_escape_string($schedule['instructor']);

            $insert_course_sql = "INSERT INTO courses (user_id, course_code, course_name, instructor)
            VALUES ($user_id,'$course_code','$course_name','$instructor')";

            if ($conn->query($insert_course_sql)) {
                $course_id = $conn->insert_id;
                $user_courses[$course_code] = $course_id;
                $debug_info['courses_inserted'][] = $course_code;
            } else {
                $debug_info['database_errors'][] = "Failed to insert course $course_code: " . $conn->error;
            }
        }

        if (isset($user_courses[$course_code])) {
            $course_id = $user_courses[$course_code];
            $day_of_week = $schedule['day'];
            $start_time = $schedule['start_time'];
            $end_time = $schedule['end_time'];
            $location = isset($schedule['location']) ? $conn->real_escape_string($schedule['location']) : null;
            $location_sql = $location ? "'$location'" : "NULL";

            $duplicate_check = $conn->query("SELECT schedule_id FROM class_schedule
                WHERE course_id = '$course_id'
                AND day_of_week = '$day_of_week'
                AND start_time = '$start_time'
                AND end_time = '$end_time'
            ");

            if ($duplicate_check->num_rows === 0) {
                $sql = "INSERT INTO class_schedule (course_id, day_of_week, start_time, end_time, location)
                VALUES ('$course_id','$day_of_week','$start_time','$end_time',$location_sql)";

                if ($conn->query($sql)) {
                    $inserted_count++;
                    $debug_info['schedules_inserted'][] = "$course_code: $day_of_week $start_time-$end_time";
                } else {
                    $debug_info['database_errors'][] = "Failed to insert schedule for $course_code: " . $conn->error;
                }
            } else {
                $debug_info['duplicates_skipped'][] = "$course_code: $day_of_week $start_time-$end_time";
            }
        }
    }

    unlink($temp_file);

    echo json_encode([
        'success' => true,
        'message' => "Imported $inserted_count class schedule(s)",
        'schedules_found' => count($schedules),
        'debug' => $debug_info
    ]);

} catch (Exception $e) {
    if (file_exists($temp_file)) {
        unlink($temp_file);
    }

    echo json_encode([
        'success' => false,
        'error' => 'Error parsing PDF: ' . $e->getMessage()
    ]);
}

function parseScheduleFromPDF($text) {
    $schedules = [];
    $debug = [];
    
    // Clean up the text
    $text = preg_replace('/\r/', '', $text);
    $text = preg_replace('/\xe2\x80\x93/', '-', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    $debug['raw_text_length'] = strlen($text);
    $debug['first_500_chars'] = substr($text, 0, 500);

    $day_map = [
        'MON' => 'Monday',
        'TUE' => 'Tuesday',
        'WED' => 'Wednesday',
        'THU' => 'Thursday',
        'FRI' => 'Friday',
        'SAT' => 'Saturday',
        'SUN' => 'Sunday'
    ];

    // Pattern: Credits Section Location Days-Times CourseName CourseCode Instructor
    // Example: 3.00B TRB-1W-02 MON 8:10am-10:00amHuman Computer InteractionCIT208 Joseph, Shane
    // First find all course codes [A-Z]{3}\d{3}
    preg_match_all('/([A-Z]{3}\d{3})/', $text, $course_codes, PREG_OFFSET_CAPTURE);

    $debug['course_pattern'] = '/([A-Z]{3}\d{3})/';
    $debug['courses_found'] = count($course_codes[1]);
    $debug['matched_courses'] = array_map(function($c) { return $c[0]; }, $course_codes[1]);

    if (empty($course_codes[1])) {
        $debug['error'] = 'No courses matched the pattern';
        return ['schedules' => [], 'debug' => $debug];
    }

    // Process each course code found
    for ($i = 0; $i < count($course_codes[1]); $i++) {
        $course_code = $course_codes[1][$i][0];
        $offset = $course_codes[1][$i][1];

        // Get text from current course code to next course code (or end of text)
        $next_offset = ($i + 1 < count($course_codes[1])) ? $course_codes[1][$i + 1][1] : strlen($text);
        $course_section = substr($text, $offset, $next_offset - $offset);

        // Get text before course code for metadata extraction
        $before_start = max(0, $offset - 500);
        $before_text = substr($text, $before_start, $offset - $before_start);

        // Extract location (text before credits - usually TRB-... or SCI-...)
        $location = 'TBA';
        if (preg_match('/(TRB-[\w\-]+|SCI-[\w\-]+|TBA)\s/', $before_text, $loc_match)) {
            $location = trim($loc_match[1]);
        }

        // Find credits in the text before course code (pattern: \d+\.\d{2})
        $credits = 0;
        $section = 'B';
        if (preg_match('/(\d+\.\d{2})([A-Z]?)\s+/', $before_text, $match)) {
            $credits = (float)$match[1];
            if (!empty($match[2])) {
                $section = $match[2];
            }
        }

        // Extract course name - look for text between time patterns and course code
        $full_before_text = substr($text, 0, $offset);
        $course_name = 'Unknown Course';
        
        // Try to find the course name between the last time pattern and the course code
        if (preg_match('/(\d{1,2}:\d{2}(?:am|pm))\s*([A-Za-z\s\-–]+?)\s*' . preg_quote($course_code) . '/', $full_before_text, $match)) {
            $course_name = trim($match[2]);
        }
        // If not found, try simpler pattern
        else if (preg_match('/([A-Za-z][A-Za-z\s\-–]*?)\s*' . preg_quote($course_code) . '/', $full_before_text, $match)) {
            $course_name = trim($match[1]);
        }

        // Extract instructor (text after course code)
        $instructor = 'TBA';
        if (preg_match('/' . preg_quote($course_code) . '\s+([A-Za-z\s,\.]+?)\s+\d{2}\/\d{2}\/\d{4}/', substr($text, $offset), $match)) {
            $instructor = trim($match[1]);
        }

        // Find all day/time combinations ONLY in the section belonging to this course
        $day_pattern = '/(MON|TUE|WED|THU|FRI|SAT|SUN)\s+(\d{1,2}:\d{2}(?:am|pm))\s*-\s*(\d{1,2}:\d{2}(?:am|pm))/i';
        preg_match_all($day_pattern, $course_section, $schedule_matches, PREG_SET_ORDER);

        $debug["course_$i"] = [
            'code' => $course_code,
            'section' => $section,
            'credits' => $credits,
            'location' => $location,
            'name' => $course_name,
            'instructor' => $instructor,
            'schedules_found' => count($schedule_matches)
        ];

        if (!empty($schedule_matches)) {
            foreach ($schedule_matches as $sched) {
                $day_abbr = strtoupper($sched[1]);
                $start_time_str = $sched[2];
                $end_time_str = $sched[3];

                // Convert to 24-hour format
                $start_time = date("H:i:s", strtotime($start_time_str));
                $end_time = date("H:i:s", strtotime($end_time_str));

                $day = isset($day_map[$day_abbr]) ? $day_map[$day_abbr] : $day_abbr;

                $schedule = [
                    'course_code' => $course_code,
                    'course_name' => $course_name,
                    'credits' => $credits,
                    'instructor' => $instructor,
                    'day' => $day,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'location' => $location
                ];

                // Prevent duplicates
                $duplicate = false;
                foreach ($schedules as $existing) {
                    if (
                        $existing['course_code'] === $schedule['course_code'] &&
                        $existing['day'] === $schedule['day'] &&
                        $existing['start_time'] === $schedule['start_time']
                    ) {
                        $duplicate = true;
                        break;
                    }
                }

                if (!$duplicate) {
                    $schedules[] = $schedule;
                }
            }
        }
    }

    return ['schedules' => $schedules, 'debug' => $debug];
}
?>