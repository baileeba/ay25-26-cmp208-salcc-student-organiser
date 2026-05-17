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

    // Fetch all goals for the user
    $sql = "SELECT goal_id, title, description, target_date, progress_percentage, status 
            FROM goals 
            WHERE user_id = ? 
            ORDER BY target_date ASC";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(["error" => "Database error: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $goals = [];
    while ($row = $result->fetch_assoc()) {
        $goals[] = $row;
    }

    echo json_encode($goals);

    $stmt->close();
    $conn->close();
?>
