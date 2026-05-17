<?php

    session_start();
    include "../acc/connect.php";

    if(!isset($_SESSION["user_id"])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $user_id = $_SESSION["user_id"];
    $target_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($target_user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit();
    }

    if ($target_user_id == $user_id) {
        echo json_encode(['success' => false, 'message' => 'Cannot add yourself as friend']);
        exit();
    }

    if ($action === 'send_request') {
        // Check if request already exists
        $check_query = "
            SELECT request_id FROM friend_requests 
            WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("iiii", $user_id, $target_user_id, $target_user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Friend request already exists']);
            exit();
        }

        // Create friend request
        $insert_query = "
            INSERT INTO friend_requests (sender_id, receiver_id, status)
            VALUES (?, ?, 'pending')
        ";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $user_id, $target_user_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Friend request sent']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send friend request']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

?>
