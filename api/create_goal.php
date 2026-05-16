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

if (!isset($data['title']) || empty(trim($data['title']))) {
    echo json_encode([
        "success" => false,
        "message" => "goal title is required"
    ]);
    exit();
}

if (!isset($data['target_date']) || empty($data['target_date'])) {
    echo json_encode([
        "success" => false,
        "message" => "target date is required"
    ]);
    exit();
}

$title = trim($data['title']);
$description = isset($data['description']) ? trim($data['description']) : '';
$target_date = $data['target_date'];
$progress_percentage = isset($data['progress_percentage']) ? intval($data['progress_percentage']) : 0;

if ($progress_percentage < 0 || $progress_percentage > 100) {
    $progress_percentage = 0;
}

$query = "INSERT INTO goals (user_id, title, description, target_date, progress_percentage, status)
VALUES (?, ?, ?, ?, ?, 'active')";

$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "database error: " . $conn->error
    ]);
    exit();
}

$stmt->bind_param("isssi", $user_id, $title, $description, $target_date, $progress_percentage);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "goal created successfully",
        "goal_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "error creating goal: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>