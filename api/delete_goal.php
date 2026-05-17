<?php
    session_start();
    include "../acc/connect.php";
    header('Content-Type: application/json');

    if (!isset($_SESSION["user_id"])) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit();
    }

    $user_id = $_SESSION["user_id"];
    $goal_id = $_POST["goal_id"] ?? "";


    if (empty($goal_id)) {
        echo json_encode(["error" => "Goal ID is required"]);
        exit();
    }


    $sql = "DELETE FROM goals WHERE goal_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["error" => "Database error: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("ii", $goal_id, $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                "success" => true,
                "message" => "Goal deleted successfully"
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Goal not found"]);
        }
    } else {
        echo json_encode(["error" => "Failed to delete goal: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
?>
