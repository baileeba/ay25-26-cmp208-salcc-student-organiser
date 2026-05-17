<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

require_once 'connect.php';

$user_id = $_SESSION['user_id'];
$goal_id = intval($_POST['goal_id'] ?? 0);

if ($goal_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid goal ID']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM goals WHERE goal_id = ? AND user_id = ?");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$stmt->bind_param('ii', $goal_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Goal deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Goal not found']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting goal: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>