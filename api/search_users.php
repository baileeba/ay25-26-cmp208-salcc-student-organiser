<?php

    session_start();
    include "../acc/connect.php";
    header('Content-Type: application/json');

    if(!isset($_SESSION["user_id"])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $user_id = $_SESSION["user_id"];

    // Handle get_courses action
    if (isset($_GET['action']) && $_GET['action'] === 'get_courses') {
        $query = "SELECT course_id, course_code, course_name FROM courses WHERE user_id = ? ORDER BY course_code ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $courses = [];
        while ($course = $result->fetch_assoc()) {
            $courses[] = $course;
        }
        
        echo json_encode($courses);
        exit();
    }
    $search_term = isset($_POST['search']) ? trim($_POST['search']) : '';

    if (strlen($search_term) < 2) {
        echo json_encode([]);
        exit();
    }

    $query = "
        SELECT u.user_id, u.name, u.username
        FROM users u
        WHERE u.user_id != ? 
        AND (u.name LIKE ? OR u.username LIKE ?)
        LIMIT 20
    ";

    $stmt = $conn->prepare($query);
    $search_pattern = '%' . $search_term . '%';
    $stmt->bind_param("iss", $user_id, $search_pattern, $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];

    while ($user = $result->fetch_assoc()) {
        $friendship_check = "
            SELECT friendship_id FROM friendships 
            WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)
        ";
        $stmt2 = $conn->prepare($friendship_check);
        $stmt2->bind_param("iiii", $user_id, $user['user_id'], $user['user_id'], $user_id);
        $stmt2->execute();
        $friendship_result = $stmt2->get_result();

        if ($friendship_result->num_rows > 0) {
            $user['buttonText'] = 'Friends';
            $user['buttonClass'] = 'btn-pending';
            $user['action'] = 'already_friends';
        } else {
            $request_check = "
                SELECT request_id, status FROM friend_requests 
                WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
            ";
            $stmt2 = $conn->prepare($request_check);
            $stmt2->bind_param("iiii", $user_id, $user['user_id'], $user['user_id'], $user_id);
            $stmt2->execute();
            $request_result = $stmt2->get_result();

            if ($request_result->num_rows > 0) {
                $request = $request_result->fetch_assoc();
                if ($request['status'] == 'pending') {
                    $user['buttonText'] = 'Request Sent';
                    $user['buttonClass'] = 'btn-pending';
                    $user['action'] = 'pending';
                }
            } else {
                $user['buttonText'] = 'Add Friend';
                $user['buttonClass'] = 'btn-add';
                $user['action'] = 'send_request';
            }
        }

        $users[] = $user;
    }

    echo json_encode($users);

?>
