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
$mode = $_GET['mode'] ?? 'single';

if ($mode === "single") {

    $query = "SELECT goal_id, title, description, target_date, progress_percentage, status
    FROM goals
    WHERE user_id = ?
    AND status = 'active'
    ORDER BY target_date ASC
    LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $goal = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "goal" => $goal]);

    } else {
        echo json_encode([
            "success" => false,
            "message" => "no goals found"]);
    }
}

else if ($mode === "all") {

    $query = "SELECT goal_id, title, description, target_date, progress_percentage, status FROM goals
    WHERE user_id = ?
    ORDER BY target_date ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $goals = [];

    while ($row = $result->fetch_assoc()) {
        $goals[] = $row;
    }

    echo json_encode([
        "success" => true,
        "goals" => $goals
    ]);
}

$stmt->close();
$conn->close();
?>