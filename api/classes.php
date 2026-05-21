<?php
header("Content-Type: application/json");
session_start();
include "../acc/connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "not_logged_in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all classes for the user
$classes_sql = "SELECT cs.schedule_id, cs.day_of_week, cs.start_time, cs.end_time, cs.location, 
                       c.course_code, c.course_name, c.course_color
                FROM class_schedule cs
                JOIN courses c ON cs.course_id = c.course_id
                WHERE c.user_id = ?
                ORDER BY cs.day_of_week, cs.start_time ASC";

$stmt = $conn->prepare($classes_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$classes_result = $stmt->get_result();

$classes = [];
$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

while ($row = $classes_result->fetch_assoc()) {
    $classes[] = [
        "id" => $row["schedule_id"],
        "day" => $row["day_of_week"],
        "start_time" => $row["start_time"],
        "end_time" => $row["end_time"],
        "location" => $row["location"],
        "course_code" => $row["course_code"],
        "course_name" => $row["course_name"],
        "color" => $row["course_color"],
        "type" => "class"
    ];
}

echo json_encode($classes);
?>
