<?php

    session_start();
    include "../acc/connect.php";

    if(!isset($_SESSION["user_id"])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $user_id = $_SESSION["user_id"];
    $request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($request_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
        exit();
    }

    if (!in_array($action, ['accepted', 'declined'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
    }

    $verify_query = "
        SELECT sender_id, receiver_id FROM friend_requests 
        WHERE request_id = ? AND receiver_id = ?
    ";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $request_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
        exit();
    }

    $request_data = $result->fetch_assoc();
    $sender_id = $request_data['sender_id'];

    if ($action === 'accepted') {
        $friendship_query = "
            INSERT INTO friendships (user_id_1, user_id_2)
            VALUES (?, ?)
        ";
        $stmt = $conn->prepare($friendship_query);
        $stmt->bind_param("ii", $user_id, $sender_id);

        if ($stmt->execute()) {
            // Delete the friend request after accepting
            $delete_query = "DELETE FROM friend_requests WHERE request_id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $request_id);
            $delete_stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Friend request accepted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to accept friend request']);
        }
    } else if ($action === 'declined') {
        $delete_query = "DELETE FROM friend_requests WHERE request_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $request_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Friend request declined']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to decline friend request']);
        }
    }

?>
