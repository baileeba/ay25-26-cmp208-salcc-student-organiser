<?php
session_start();
header('Content-Type: application/json');

include "../acc/connect.php";

// Check if user is logged in
if(!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = isset($_POST['currentPassword']) ? $_POST['currentPassword'] : '';
    $newPassword = isset($_POST['newPassword']) ? $_POST['newPassword'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';

    // Validation
    if(empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "All fields are required"]);
        exit();
    }

    if($newPassword !== $confirmPassword) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "New passwords do not match"]);
        exit();
    }

    if(strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Password must be at least 6 characters long"]);
        exit();
    }

    // Get current password from database
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows != 1) {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "User not found"]);
        exit();
    }

    $row = $result->fetch_assoc();

    // Verify current password
    if(!password_verify($currentPassword, $row["password"])) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Current password is incorrect"]);
        exit();
    }

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->bind_param("si", $hashedPassword, $_SESSION["user_id"]);

    if($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Password changed successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Error updating password: " . $stmt->error]);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
}

$conn->close();
?>
