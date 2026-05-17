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
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $target_date = $_POST["target_date"] ?? "";
    $progress_percentage = $_POST["progress_percentage"] ?? "";
    $status = $_POST["status"] ?? "";

    if (empty($goal_id)) {
        echo json_encode(["error" => "Goal ID is required"]);
        exit();
    }

    $verify_sql = "SELECT goal_id FROM goals WHERE goal_id = ? AND user_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $goal_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(["error" => "Goal not found or unauthorized"]);
        exit();
    }

    $updates = [];
    $params = [];
    $types = "";

    if (!empty($title)) {
        $updates[] = "title = ?";
        $params[] = $title;
        $types .= "s";
    }
    if (!empty($description)) {
        $updates[] = "description = ?";
        $params[] = $description;
        $types .= "s";
    }
    if (!empty($target_date)) {
        $updates[] = "target_date = ?";
        $params[] = $target_date;
        $types .= "s";
    }
    if ($progress_percentage !== "") {
        $updates[] = "progress_percentage = ?";
        $params[] = (int)$progress_percentage;
        $types .= "i";
    }
    if (!empty($status)) {
        $updates[] = "status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if (empty($updates)) {
        echo json_encode(["error" => "No fields to update"]);
        exit();
    }


    $params[] = $goal_id;
    $types .= "i";

    $sql = "UPDATE goals SET " . implode(", ", $updates) . " WHERE goal_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["error" => "Database error: " . $conn->error]);
        exit();
    }

    
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Goal updated successfully"
        ]);
    } else {
        echo json_encode(["error" => "Failed to update goal: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
?>
