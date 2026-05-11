<?php
    header("Content-Type: application/json");

    session_start();

    include "acc/connect.php";

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([]);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    $sql = "SELECT reminder_date, reminder_text, reminder_color FROM reminders WHERE user_id = ?
        ORDER BY reminder_date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $reminders = [];

    while ($row = $result->fetch_assoc()) {
        $reminders[] = [
            "date" => $row["reminder_date"],
            "title" => $row["reminder_text"],
            "color" => $row["reminder_color"]
        ];
    }

    echo json_encode($reminders);

    $stmt->close();
    $conn->close();
?>