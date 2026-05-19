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

    $schedules = parseScheduleFromPDF($text);

    if (empty($schedules)) {

        unlink($temp_file);

        echo json_encode([
            'success' => false,
            'error' => 'No schedule data found in PDF.',
            'preview' => substr($text, 0, 1500)
        ]);

        exit();
    }

    $courses_query = $conn->query("SELECT course_id, course_code
        FROM courses
        WHERE user_id = $user_id
    ");

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

            $credits = (float)$schedule['credits'];

            $insert_course_sql = "INSERT INTO courses (user_id, course_code, course_name,
            instructor, credits)
            VALUES ($user_id, '$course_code', '$course_name', '$instructor', $credits)";

            if ($conn->query($insert_course_sql)) {

                $course_id = $conn->insert_id;

                $user_courses[$course_code] = $course_id;
            }
        }

        if (isset($user_courses[$course_code])) {

            $course_id = $user_courses[$course_code];

            $day_of_week = $schedule['day'];

            $start_time = $schedule['start_time'];

            $end_time = $schedule['end_time'];

            $location = isset($schedule['location'])
                ? $conn->real_escape_string($schedule['location'])
                : null;

            $location_sql = $location
                ? "'$location'"
                : "NULL";

            $duplicate_check = $conn->query("SELECT schedule_id
                FROM class_schedule
                WHERE course_id = '$course_id'
                AND day_of_week = '$day_of_week'
                AND start_time = '$start_time'
                AND end_time = '$end_time'
            ");

            if ($duplicate_check->num_rows === 0) {

                $sql = "INSERT INTO class_schedule (course_id, day_of_week, start_time,
                end_time, location)
                VALUES ('$course_id','$day_of_week','$start_time','$end_time',$location_sql)";

                if ($conn->query($sql)) {
                    $inserted_count++;
                }
            }
        }
    }

    unlink($temp_file);

    echo json_encode([
        'success' => true,
        'message' => "Imported $inserted_count class schedule(s)",
        'schedules_found' => count($schedules)
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

    $text = preg_replace('/\r/', '', $text);

    $lines = explode("\n", $text);

    $day_map = [
        'MON' => 'Monday',
        'TUE' => 'Tuesday',
        'WED' => 'Wednesday',
        'THU' => 'Thursday',
        'FRI' => 'Friday',
        'SAT' => 'Saturday',
        'SUN' => 'Sunday'
    ];

    $current_course = null;

    $current_course_name = null;

    $current_instructor = null;

    $current_credits = 0;

    foreach ($lines as $line) {

        $line = trim($line);

        if (empty($line)) {
            continue;
        }

        if (preg_match('/([A-Z]{3}\d{3})/', $line, $course_match)) {

            $current_course = strtoupper($course_match[1]);

            if (
                preg_match(
                    '/([A-Z]{3}\d{3})\s+[A-Z]\s+(.+?)\s+(\d+\.\d{2})\s+(.+?)\s+\d{2}\/\d{2}\/\d{4}/',
                    $line,
                    $details
                )
            ) {

                $current_course_name = trim($details[2]);

                $current_credits = (float)$details[3];

                $current_instructor = trim($details[4]);

            } else {

                $current_course_name = $current_course;

                $current_credits = 0;

                $current_instructor = 'TBA';
            }
        }

        if (
            $current_course &&
            preg_match(
                '/([A-Z0-9\-]+)\s+(MON|TUE|WED|THU|FRI|SAT|SUN)\s+(\d{1,2}:\d{2}(?:am|pm))-(\d{1,2}:\d{2}(?:am|pm))/i',
                $line,
                $matches
            )
        ) {

            $location = trim($matches[1]);

            $day_abbr = strtoupper($matches[2]);

            $start_time = date(
                "H:i:s",
                strtotime($matches[3])
            );

            $end_time = date(
                "H:i:s",
                strtotime($matches[4])
            );

            $schedule = [
                'course_code' => $current_course,
                'course_name' => $current_course_name,
                'credits' => $current_credits,
                'instructor' => $current_instructor,
                'day' => $day_map[$day_abbr],
                'start_time' => $start_time,
                'end_time' => $end_time,
                'location' => $location
            ];

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

    return $schedules;
}