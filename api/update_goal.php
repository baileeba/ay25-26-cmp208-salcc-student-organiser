<?php
header("Content-Type: application/json");
session_start();
include "connect.php";

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "not authenticated"
    ]);
    exit();
}

$user_id = $_SESSION["user_id"];
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['goal_id']) || !isset($data['title'])) {
    echo json_encode([
        "success" => false,
        "message" => "missing required fields"
    ]);
    exit();
}

$goal_id = intval($data['goal_id']);
$title = trim($data['title']);
$description = isset($data['description']) ? trim($data['description']) : '';
$target_date = $data['target_date'];
$progress_percentage = isset($data['progress_percentage']) ? intval($data['progress_percentage']) : 0;
$status = isset($data['status']) ? $data['status'] : 'active';


if ($progress_percentage < 0 || $progress_percentage > 100) {
    $progress_percentage = 0;
}

if (!in_array($status, ['active', 'completed', 'abandoned'])) {
    $status = 'active';
}

$check_query = "SELECT goal_id FROM goals WHERE goal_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $goal_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "goal not found or unauthorized"
    ]);
    exit();
}

$check_stmt->close();

$query = "UPDATE goals 
SET title = ?, description = ?, target_date = ?, progress_percentage = ?, status = ?
WHERE goal_id = ? AND user_id = ?";

$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "database error: " . $conn->error
    ]);
    exit();
}

$stmt->bind_param("ssssii", $title, $description, $target_date, $progress_percentage, $status,
$goal_id, $user_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "goal updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "error updating goal: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
