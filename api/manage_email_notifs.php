<?php
session_start();
include "../acc/connect.php";
include "../config/email_config.php";
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION["user_id"];

// Function to ensure the column exists
function ensureEmailNotificationColumn($conn) {
    $checkColumn = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'email_notifications_enabled'";
    $result = $conn->query($checkColumn);
    
    if ($result->num_rows === 0) {
        // Column doesn't exist, create it
        $conn->query("ALTER TABLE users ADD COLUMN email_notifications_enabled BOOLEAN DEFAULT 0");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'status') {
    // Ensure column exists
    ensureEmailNotificationColumn($conn);
    
    // Get current email notification status
    $query = "SELECT email_notifications_enabled FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'enabled' => (bool)$user['email_notifications_enabled']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'User not found']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Ensure column exists
    ensureEmailNotificationColumn($conn);
    
    $action = $_POST['action'];
    
    if ($action === 'enable' || $action === 'disable') {
        $enabled = ($action === 'enable') ? 1 : 0;
        
        // Get user email and name before updating
        $userQuery = "SELECT email, name FROM users WHERE user_id = ?";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param("i", $user_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userData = $userResult->fetch_assoc();
        
        $query = "UPDATE users SET email_notifications_enabled = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $enabled, $user_id);
        
        if ($stmt->execute()) {
            // Send email based on action
            if ($action === 'enable' && $userData) {
                sendConfirmationEmail($userData['email'], $userData['name']);
            } elseif ($action === 'disable' && $userData) {
                sendThankYouEmail($userData['email'], $userData['name']);
            }
            
            echo json_encode([
                'success' => true,
                'enabled' => (bool)$enabled,
                'message' => $action === 'enable' ? 'Email notifications enabled' : 'Email notifications disabled'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update preferences']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit();
}

function sendConfirmationEmail($to_email, $to_name) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SENDER_EMAIL, SENDER_NAME);
        $mail->addAddress($to_email, $to_name);
        
        $mail->Subject = 'Email Notifications Enabled - StudSort';
        $mail->Body = "Hello $to_name,\n\n" .
                      "Great! You've successfully enabled email notifications for StudSort.\n\n" .
                      "You will now receive emails for:\n" .
                      "- Class reminders when it's time for your classes\n" .
                      "- Upcoming assignments and deadlines\n" .
                      "- Task reminders and notifications\n" .
                      "- Goal deadline alerts\n\n" .
                      "You can manage or disable these notifications anytime from your profile settings.\n\n" .
                      "Best regards,\n" .
                      "The StudSort Team";
        
        $result = $mail->send();
        return $result;
    } catch (Exception $e) {
        error_log("Confirmation email failed for $to_email: " . $e->getMessage());
        return false;
    }
}

function sendThankYouEmail($to_email, $to_name) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SENDER_EMAIL, SENDER_NAME);
        $mail->addAddress($to_email, $to_name);
        
        $mail->Subject = 'Thanks for Using StudSort Notifications';
        $mail->Body = "Hello $to_name,\n\n" .
                      "Thank you for using StudSort's email notification service. We appreciate you being part of our community!\n\n" .
                      "If you ever want to re-enable notifications or have any feedback, feel free to reach out.\n\n" .
                      "Best regards,\n" .
                      "The StudSort Team";
        $mail->IsHTML(false);
        
        $result = $mail->send();
        
        if ($result) {
            error_log("Thank you email sent successfully to $to_email");
        }
        
        return $result;
    } catch (Exception $e) {
        $error_msg = "Thank you email failed for $to_email: " . $e->getMessage();
        error_log($error_msg);
        error_log("SMTP Debug: Host=" . SMTP_HOST . ", Port=" . SMTP_PORT . ", User=" . SMTP_USER);
        return false;
    }
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
