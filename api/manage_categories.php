<?php
session_start();
include "../acc/connect.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION["user_id"];
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

if ($action === 'get') {
    // Fetch all courses for the user (treating them as categories)
    $query = "SELECT course_id as id, course_name as name, course_code, instructor FROM courses WHERE user_id = ? ORDER BY course_name ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode(['success' => true, 'categories' => $categories]);
    exit();
}

if ($action === 'fetch' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($course_id === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid course ID']);
        exit();
    }
    
    // Verify the course belongs to the user
    $course_query = "SELECT course_id, course_name, course_code, instructor FROM courses WHERE course_id = ? AND user_id = ?";
    $course_stmt = $conn->prepare($course_query);
    $course_stmt->bind_param("ii", $course_id, $user_id);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();
    
    if ($course_result->num_rows === 0) {
        $course_stmt->close();
        echo json_encode(['success' => false, 'error' => 'Course not found']);
        exit();
    }
    
    $course = $course_result->fetch_assoc();
    $course_stmt->close();
    
    // Fetch ALL class schedules for this course
    $schedule_query = "SELECT schedule_id, day_of_week, start_time, end_time, location FROM class_schedule WHERE course_id = ? ORDER BY day_of_week ASC, start_time ASC";
    $schedule_stmt = $conn->prepare($schedule_query);
    $schedule_stmt->bind_param("i", $course_id);
    $schedule_stmt->execute();
    $schedule_result = $schedule_stmt->get_result();
    
    $schedules = [];
    while ($row = $schedule_result->fetch_assoc()) {
        $schedules[] = $row;
    }
    
    $schedule_stmt->close();
    
    echo json_encode([
        'success' => true,
        'course' => $course,
        'schedules' => $schedules
    ]);
    exit();
}

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    $instructor = isset($_POST['instructor']) ? trim($_POST['instructor']) : '';
    $schedules_json = isset($_POST['schedules']) ? $_POST['schedules'] : '[]';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Course name is required']);
        exit();
    }
    
    if (strlen($name) > 100) {
        echo json_encode(['success' => false, 'error' => 'Course name is too long']);
        exit();
    }
    
    // Check if course already exists for this user
    $check_query = "SELECT course_id FROM courses WHERE user_id = ? AND LOWER(course_name) = LOWER(?)";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("is", $user_id, $name);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $check_stmt->close();
        echo json_encode(['success' => false, 'error' => 'Course already exists']);
        exit();
    }
    
    $check_stmt->close();
    
    // Insert new course
    $insert_query = "INSERT INTO courses (user_id, course_name, course_code, instructor) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("isss", $user_id, $name, $code, $instructor);
    
    if (!$insert_stmt->execute()) {
        $insert_stmt->close();
        echo json_encode(['success' => false, 'error' => 'Error adding course']);
        exit();
    }
    
    $new_course_id = $insert_stmt->insert_id;
    $insert_stmt->close();
    
    // Decode schedules from JSON and insert them
    $schedules = json_decode($schedules_json, true);
    
    if (!is_array($schedules)) {
        $schedules = [];
    }
    
    // Insert schedules for the new course
    if (!empty($schedules)) {
        $valid_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $schedule_insert = "INSERT INTO class_schedule (course_id, day_of_week, start_time, end_time, location) VALUES (?, ?, ?, ?, ?)";
        $schedule_stmt = $conn->prepare($schedule_insert);
        
        foreach ($schedules as $schedule) {
            $day = isset($schedule['day']) ? trim($schedule['day']) : '';
            $start = isset($schedule['start']) ? trim($schedule['start']) : '';
            $end = isset($schedule['end']) ? trim($schedule['end']) : '';
            $location = isset($schedule['location']) ? trim($schedule['location']) : '';
            
            // Skip empty slots
            if (empty($day) && empty($start) && empty($end)) {
                continue;
            }
            
            // Validate day
            if (!in_array($day, $valid_days)) {
                $schedule_stmt->close();
                echo json_encode(['success' => false, 'error' => 'Invalid day of week: ' . $day]);
                exit();
            }
            
            // Validate times
            if (empty($start) || empty($end)) {
                $schedule_stmt->close();
                echo json_encode(['success' => false, 'error' => 'Start and end times are required for each class']);
                exit();
            }
            
            $schedule_stmt->bind_param("issss", $new_course_id, $day, $start, $end, $location);
            
            if (!$schedule_stmt->execute()) {
                $schedule_stmt->close();
                echo json_encode(['success' => false, 'error' => 'Error creating schedule']);
                exit();
            }
        }
        
        $schedule_stmt->close();
    }
    
    echo json_encode(['success' => true, 'message' => 'Course added successfully']);
    exit();
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($course_id === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid course ID']);
        exit();
    }
    
    // Verify the course belongs to the user
    $verify_query = "SELECT course_id FROM courses WHERE course_id = ? AND user_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("ii", $course_id, $user_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->get_result()->num_rows === 0) {
        $verify_stmt->close();
        echo json_encode(['success' => false, 'error' => 'Course not found']);
        exit();
    }
    
    $verify_stmt->close();
    
    // Delete the course
    $delete_query = "DELETE FROM courses WHERE course_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $course_id, $user_id);
    
    if ($delete_stmt->execute()) {
        $delete_stmt->close();
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
    } else {
        $delete_stmt->close();
        echo json_encode(['success' => false, 'error' => 'Error deleting course']);
    }
    exit();
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $course_name = isset($_POST['course_name']) ? trim($_POST['course_name']) : '';
    $course_code = isset($_POST['course_code']) ? trim($_POST['course_code']) : '';
    $instructor = isset($_POST['instructor']) ? trim($_POST['instructor']) : '';
    $schedules_json = isset($_POST['schedules']) ? $_POST['schedules'] : '[]';
    
    if ($course_id === 0 || empty($course_name)) {
        echo json_encode(['success' => false, 'error' => 'Course name is required']);
        exit();
    }
    
    // Verify the course belongs to the user
    $verify_query = "SELECT course_id FROM courses WHERE course_id = ? AND user_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("ii", $course_id, $user_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->get_result()->num_rows === 0) {
        $verify_stmt->close();
        echo json_encode(['success' => false, 'error' => 'Course not found']);
        exit();
    }
    
    $verify_stmt->close();
    
    // Update the course
    $update_query = "UPDATE courses SET course_name = ?, course_code = ?, instructor = ? WHERE course_id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssii", $course_name, $course_code, $instructor, $course_id, $user_id);
    
    if (!$update_stmt->execute()) {
        $update_stmt->close();
        echo json_encode(['success' => false, 'error' => 'Error updating course']);
        exit();
    }
    
    $update_stmt->close();
    
    // Decode schedules from JSON
    $schedules = json_decode($schedules_json, true);
    
    if (!is_array($schedules)) {
        $schedules = [];
    }
    
    // Delete all existing schedules for this course
    $delete_schedules = "DELETE FROM class_schedule WHERE course_id = ?";
    $delete_stmt = $conn->prepare($delete_schedules);
    $delete_stmt->bind_param("i", $course_id);
    
    if (!$delete_stmt->execute()) {
        $delete_stmt->close();
        echo json_encode(['success' => false, 'error' => 'Error updating schedules']);
        exit();
    }
    
    $delete_stmt->close();
    
    // Insert new schedules
    if (!empty($schedules)) {
        $valid_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $schedule_insert = "INSERT INTO class_schedule (course_id, day_of_week, start_time, end_time, location) VALUES (?, ?, ?, ?, ?)";
        $schedule_stmt = $conn->prepare($schedule_insert);
        
        foreach ($schedules as $schedule) {
            $day = isset($schedule['day']) ? trim($schedule['day']) : '';
            $start = isset($schedule['start']) ? trim($schedule['start']) : '';
            $end = isset($schedule['end']) ? trim($schedule['end']) : '';
            $location = isset($schedule['location']) ? trim($schedule['location']) : '';
            
            // Validate day
            if (!in_array($day, $valid_days)) {
                $schedule_stmt->close();
                echo json_encode(['success' => false, 'error' => 'Invalid day of week: ' . $day]);
                exit();
            }
            
            // Validate times
            if (empty($start) || empty($end)) {
                $schedule_stmt->close();
                echo json_encode(['success' => false, 'error' => 'Start and end times are required for each class']);
                exit();
            }
            
            $schedule_stmt->bind_param("issss", $course_id, $day, $start, $end, $location);
            
            if (!$schedule_stmt->execute()) {
                $schedule_stmt->close();
                echo json_encode(['success' => false, 'error' => 'Error creating schedule']);
                exit();
            }
        }
        
        $schedule_stmt->close();
    }
    
    echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
