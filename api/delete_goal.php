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

if (!isset($data['goal_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "goal_id is required"
    ]);
    exit();
}

$goal_id = intval($data['goal_id']);

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

$query = "DELETE FROM goals WHERE goal_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "database error: " . $conn->error
    ]);
    exit();
}

$stmt->bind_param("ii", $goal_id, $user_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "goal deleted successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "error deleting goal: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>