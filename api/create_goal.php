<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

require_once 'connect.php';

$user_id = $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$target_date = $_POST['target_date'] ?? '';

if (empty($title)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Goal title is required']);
    exit();
}

if (empty($target_date)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Target date is required']);
    exit();
}

$target_timestamp = strtotime($target_date);
if ($target_timestamp <= time()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Target date must be in the future']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO goals (user_id, title, description, target_date, status) VALUES (?, ?, ?, ?, 'active')");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param('isss', $user_id, $title, $description, $target_date);

if ($stmt->execute()) {
    $goal_id = $stmt->insert_id;
    echo json_encode([
        'success' => true, 
        'message' => 'Goal created successfully',
        'goal_id' => $goal_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error creating goal: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>