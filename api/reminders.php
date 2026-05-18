<?php
header("Content-Type: application/json");
session_start();
include "../acc/connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "not_logged_in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

$method = $_SERVER['REQUEST_METHOD'];


if ($method === 'GET') {

    $sql = "SELECT id, reminder_date, reminder_time, reminder_text, reminder_color FROM reminders WHERE user_id = ?
            ORDER BY reminder_date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $reminders = [];

    while ($row = $result->fetch_assoc()) {
        $reminders[] = [
            "id" => $row["id"], 
            "date" => $row["reminder_date"], 
            "time" => $row["reminder_time"],
            "title" => $row["reminder_text"], 
            "color" => $row["reminder_color"]
        ];
    }

    echo json_encode($reminders);
    exit;
}


if ($method === 'POST' && $_POST['action'] === 'create') {

    if (empty($_POST['date']) || empty($_POST['title'])) {
        echo json_encode(["error" => "missing_required_fields"]);
        exit;
    }

    $date = $_POST['date'];
    $title = $_POST['title'];
    $time = isset($_POST['time']) && $_POST['time'] !== '' ? $_POST['time'] : null;
    $color = isset($_POST['color']) && $_POST['color'] !== '' ? $_POST['color'] : '#3498db';
    
    $sql = "INSERT INTO reminders (user_id, reminder_date, reminder_time, reminder_text, reminder_color)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(["error" => "prepare_failed: " . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("issss", $user_id, $date, $time, $title, $color);

    if (!$stmt->execute()) {
        echo json_encode(["error" => "execute_failed: " . $stmt->error]);
        exit;
    }

    echo json_encode(["status" => "created", "id" => $stmt->insert_id]);
    $stmt->close();
    exit;
}


if ($method === 'POST' && $_POST['action'] === 'update') {

    $sql = "UPDATE reminders SET reminder_date = ?, reminder_text = ?, reminder_color = ?
            WHERE id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssii", $_POST['date'], $_POST['title'], $_POST['color'], $_POST['id'], $user_id
    );

    $stmt->execute();

    echo json_encode(["status" => "updated"]);
    exit;
}


if ($method === 'POST' && $_POST['action'] === 'delete') {

    $sql = "DELETE FROM reminders
            WHERE id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_POST['id'], $user_id);

    $stmt->execute();

    echo json_encode(["status" => "deleted"]);
    exit;
}