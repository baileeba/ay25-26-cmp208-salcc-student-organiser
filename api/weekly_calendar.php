<?php
header("Content-Type: application/json");
session_start();
include "../acc/connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "not_logged_in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the start of the week - use provided Monday or calculate from today
if (isset($_GET['monday'])) {
    // Use the Monday date provided by the frontend
    $monday = DateTime::createFromFormat('Y-m-d', $_GET['monday']);
    if ($monday === false) {
        $monday = new DateTime();
        $monday->modify('Monday this week');
    }
} else {
    // Fallback to server's current week
    $monday = new DateTime();
    $monday->modify('Monday this week');
}

$sunday = clone $monday;
$sunday->modify('Sunday this week');

$monday_date = $monday->format('Y-m-d');
$sunday_date = $sunday->format('Y-m-d');

// Fetch reminders for the week
$reminders_sql = "SELECT id, reminder_date, reminder_time, reminder_text, reminder_color, reminder_type 
                  FROM reminders 
                  WHERE user_id = ? AND reminder_date BETWEEN ? AND ?
                  ORDER BY reminder_date ASC, reminder_time ASC";

$stmt = $conn->prepare($reminders_sql);
$stmt->bind_param("iss", $user_id, $monday_date, $sunday_date);
$stmt->execute();
$reminders_result = $stmt->get_result();

$reminders = [];
while ($row = $reminders_result->fetch_assoc()) {
    $reminders[] = [
        "id" => $row["id"],
        "date" => $row["reminder_date"],
        "time" => $row["reminder_time"],
        "text" => $row["reminder_text"],
        "color" => $row["reminder_color"],
        "type" => $row["reminder_type"],
        "category" => "reminder"
    ];
}

// Fetch classes for the week
$classes_sql = "SELECT cs.schedule_id, cs.day_of_week, cs.start_time, cs.end_time, cs.location, 
                       c.course_code, c.course_name
                FROM class_schedule cs
                JOIN courses c ON cs.course_id = c.course_id
                WHERE c.user_id = ?
                ORDER BY cs.start_time ASC";

$stmt = $conn->prepare($classes_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$classes_result = $stmt->get_result();

$classes = [];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

while ($row = $classes_result->fetch_assoc()) {
    // Find the date for this day of week in the current week
    $day_index = array_search($row["day_of_week"], $days);
    $class_date = clone $monday;
    $class_date->modify("+{$day_index} days");
    
    $classes[] = [
        "id" => $row["schedule_id"],
        "date" => $class_date->format('Y-m-d'),
        "day" => $row["day_of_week"],
        "start_time" => $row["start_time"],
        "end_time" => $row["end_time"],
        "location" => $row["location"],
        "course_code" => $row["course_code"],
        "course_name" => $row["course_name"],
        "category" => "class"
    ];
}

// Fetch assignments for the week
$assignments_sql = "SELECT a.assignment_id, a.title, a.due_date, a.due_time, 
                           a.priority, a.color, c.course_code, c.course_name
                    FROM assignments a
                    INNER JOIN courses c ON a.course_id = c.course_id
                    WHERE a.user_id = ? AND a.due_date BETWEEN ? AND ?
                    ORDER BY a.due_date ASC, a.due_time ASC";

$stmt = $conn->prepare($assignments_sql);
$stmt->bind_param("iss", $user_id, $monday_date, $sunday_date);
$stmt->execute();
$assignments_result = $stmt->get_result();

$assignments = [];
while ($row = $assignments_result->fetch_assoc()) {
    $assignments[] = [
        "id" => $row["assignment_id"],
        "date" => $row["due_date"],
        "time" => $row["due_time"],
        "title" => $row["title"],
        "priority" => $row["priority"],
        "color" => $row["color"],
        "course_code" => $row["course_code"],
        "course_name" => $row["course_name"],
        "category" => "assignment"
    ];
}

// Combine and sort all events
$events = array_merge($reminders, $classes, $assignments);
usort($events, function($a, $b) {
    $date_cmp = strcmp($a['date'], $b['date']);
    if ($date_cmp !== 0) return $date_cmp;
    
    $time_a = isset($a['time']) ? $a['time'] : $a['start_time'];
    $time_b = isset($b['time']) ? $b['time'] : $b['start_time'];
    return strcmp($time_a, $time_b);
});

echo json_encode([
    "week_start" => $monday_date,
    "week_end" => $sunday_date,
    "events" => $events
]);
