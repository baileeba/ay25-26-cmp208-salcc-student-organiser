<?php

    session_start();
    include "../acc/connect.php";

    if(!isset($_SESSION["user_id"])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $user_id = $_SESSION["user_id"];
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
        exit();
    }

    $user_query = "SELECT email FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $user_email = $user_data['email'];


    $admin_email = 'ayarmarks@gmail.com';
    $email_subject = 'Contact Form Submission: ' . $subject;
    $email_body = "User Email: " . $user_email . "\n\n";
    $email_body .= "Subject: " . $subject . "\n\n";
    $email_body .= "Message:\n" . $message;
    $headers = "From: " . $user_email;

    if (mail($admin_email, $email_subject, $email_body, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Your message has been sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.']);
    }

?>
