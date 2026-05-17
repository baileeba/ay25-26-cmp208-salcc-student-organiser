<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

require_once 'connect.php';

$user_id = $_SESSION['user_id'];
$goal_id = intval($_POST['goal_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$target_date = $_POST['target_date'] ?? '';
$progress_percentage = intval($_POST['progress_percentage'] ?? 0);
$status = $_POST['status'] ?? 'active';

if ($goal_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid goal ID']);
    exit();
}

$verify_stmt = $conn->prepare("SELECT goal_id FROM goals WHERE goal_id = ? AND user_id = ?");
if (!$verify_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$verify_stmt->bind_param('ii', $goal_id, $user_id);
$verify_stmt->execute();
$result = $verify_stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Goal not found or unauthorized']);
    exit();
}

$verify_stmt->close();

if ($progress_percentage < 0 || $progress_percentage > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Progress must be between 0 and 100']);
    exit();
}

$valid_statuses = ['active', 'completed', 'abandoned'];
if (!in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$updates = [];
$params = [];
$types = '';

if (!empty($title)) {
    $updates[] = "title = ?";
    $params[] = $title;
    $types .= 's';
}

if (!empty($description)) {
    $updates[] = "description = ?";
    $params[] = $description;
    $types .= 's';
}

if (!empty($target_date)) {
    $updates[] = "target_date = ?";
    $params[] = $target_date;
    $types .= 's';
}

$updates[] = "progress_percentage = ?";
$params[] = $progress_percentage;
$types .= 'i';

$updates[] = "status = ?";
$params[] = $status;
$types .= 's';


$params[] = $goal_id;
$params[] = $user_id;
$types .= 'ii';

$query = "UPDATE goals SET " . implode(', ', $updates) . " WHERE goal_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Goal updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating goal: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>