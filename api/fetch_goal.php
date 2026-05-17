<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

require_once 'connect.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT goal_id, title, description, target_date, progress_percentage, status FROM goals WHERE user_id = ? ORDER BY target_date ASC");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$goals = [];
while ($row = $result->fetch_assoc()) {
    $goals[] = $row;
}

echo json_encode(['success' => true, 'goals' => $goals]);

$stmt->close();
$conn->close();
?>