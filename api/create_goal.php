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
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $target_date = $_POST["target_date"] ?? "";


    if (empty($title)) {
        echo json_encode(["error" => "Goal title is required"]);
        exit();
    }

    if (empty($target_date)) {
        echo json_encode(["error" => "Target date is required"]);
        exit();
    }


    $sql = "INSERT INTO goals (user_id, title, description, target_date, progress_percentage, status) 
            VALUES (?, ?, ?, ?, 0, 'active')";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(["error" => "Database error: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("isss", $user_id, $title, $description, $target_date);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Goal created successfully",
            "goal_id" => $stmt->insert_id
        ]);
    } else {
        echo json_encode(["error" => "Failed to create goal: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
?>
