<?php
    session_start();
    include "connect.php";

    if(!isset($_SESSION["user_id"])) {
        echo json_encode(["success" => false, "message" => "Not authenticated"]);
        exit();
    }

    $user_id = $_SESSION["user_id"];

    // get goal with the nearest target date that is active
    $query = "SELECT goal_id, title, description, target_date, progress_percentage, status 
              FROM goals 
              WHERE user_id = ? AND status IN ('active')
              ORDER BY target_date ASC 
              LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $goal = $result->fetch_assoc();
        echo json_encode(["success" => true, "goal" => $goal]);
    } else {
        echo json_encode(["success" => false, "message" => "No goals found"]);
    }

    $stmt->close();
    $conn->close();
?>