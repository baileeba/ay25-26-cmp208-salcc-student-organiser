<?php
header("Content-Type: application/json");
session_start();
include "acc/connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "not_logged_in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch active assignments for the user
    $sql = "SELECT a.assignment_id, a.title, a.description, a.due_date, a.due_time, 
                   a.priority, a.status, a.weight_percentage, c.course_code, c.course_name
            FROM assignments a
            JOIN courses c ON a.course_id = c.course_id
            WHERE a.user_id = ? AND a.status IN ('not_started', 'in_progress')
            ORDER BY a.due_date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $assignments = [];

    while ($row = $result->fetch_assoc()) {
        $assignments[] = [
            "id" => $row["assignment_id"],
            "title" => $row["title"],
            "description" => $row["description"],
            "due_date" => $row["due_date"],
            "due_time" => $row["due_time"],
            "priority" => $row["priority"],
            "status" => $row["status"],
            "weight_percentage" => $row["weight_percentage"],
            "course_code" => $row["course_code"],
            "course_name" => $row["course_name"]
        ];
    }

    echo json_encode($assignments);
    exit;
}

if ($method === 'POST' && $_POST['action'] === 'create') {
    // Get user's courses to validate course_id
    $course_id = $_POST['course_id'];
    
    $validate_sql = "SELECT course_id FROM courses WHERE course_id = ? AND user_id = ?";
    $validate_stmt = $conn->prepare($validate_sql);
    $validate_stmt->bind_param("ii", $course_id, $user_id);
    $validate_stmt->execute();
    
    if ($validate_stmt->get_result()->num_rows === 0) {
        echo json_encode(["error" => "invalid_course"]);
        exit;
    }

    $sql = "INSERT INTO assignments (user_id, course_id, title, description, due_date, due_time, priority, status, weight_percentage, is_group_assignment)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $status = isset($_POST['status']) ? $_POST['status'] : 'not_started';
    $weight = isset($_POST['weight_percentage']) ? $_POST['weight_percentage'] : null;
    $is_group = isset($_POST['is_group_assignment']) ? 1 : 0;

    $stmt->bind_param(
        "iissssdsii",
        $user_id,
        $course_id,
        $_POST['title'],
        $_POST['description'],
        $_POST['due_date'],
        $_POST['due_time'],
        $_POST['priority'],
        $status,
        $weight,
        $is_group
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "created", "assignment_id" => $stmt->insert_id]);
    } else {
        echo json_encode(["error" => "failed_to_create"]);
    }
    exit;
}

if ($method === 'POST' && $_POST['action'] === 'update') {
    $sql = "UPDATE assignments 
            SET title = ?, description = ?, due_date = ?, due_time = ?, 
                priority = ?, status = ?, weight_percentage = ?
            WHERE assignment_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssdsii",
        $_POST['title'],
        $_POST['description'],
        $_POST['due_date'],
        $_POST['due_time'],
        $_POST['priority'],
        $_POST['status'],
        $_POST['weight_percentage'],
        $_POST['assignment_id'],
        $user_id
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "updated"]);
    } else {
        echo json_encode(["error" => "failed_to_update"]);
    }
    exit;
}

if ($method === 'POST' && $_POST['action'] === 'delete') {
    $sql = "DELETE FROM assignments WHERE assignment_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_POST['assignment_id'], $user_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "deleted"]);
    } else {
        echo json_encode(["error" => "failed_to_delete"]);
    }
    exit;
}

echo json_encode(["error" => "invalid_request"]);
?>
