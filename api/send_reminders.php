<?php
/**
 * Simple Email Reminder System
 * Sends reminders for tasks, goals, and classes due in next 24 hours
 */

include "../acc/connect.php";
include "../config/email_config.php";
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Get all users with email notifications enabled
$query = "SELECT user_id, email, name FROM users WHERE email_notifications_enabled = 1";
$result = $conn->query($query);

$sent = 0;
$errors = [];

while ($user = $result->fetch_assoc()) {
    $user_id = $user['user_id'];
    $email = $user['email'];
    $name = $user['name'];
    
    // Get upcoming assignments (due in next 24 hours)
    $assignments = [];
    $stmt = $conn->prepare("
        SELECT title, due_date, due_time FROM assignments 
        WHERE user_id = ? AND status != 'completed' 
        AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        LIMIT 10
    ");
    
    if (!$stmt) {
        $errors[] = "Database prepare error for assignments: " . $conn->error;
        continue;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $assign_result = $stmt->get_result();
    while ($row = $assign_result->fetch_assoc()) {
        $assignments[] = $row;
    }
    
    // Get active goals (target date in next 24 hours)
    $goals = [];
    $stmt = $conn->prepare("
        SELECT title, target_date FROM goals 
        WHERE user_id = ? AND status = 'active' 
        AND target_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        LIMIT 10
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $goal_result = $stmt->get_result();
    while ($row = $goal_result->fetch_assoc()) {
        $goals[] = $row;
    }
    
    // Get classes for today/tomorrow
    $classes = [];
    $today = date('l');
    $tomorrow = date('l', strtotime('+1 day'));
    $stmt = $conn->prepare("
        SELECT c.course_name, c.course_code, cs.start_time, cs.location 
        FROM class_schedule cs
        JOIN courses c ON cs.course_id = c.course_id
        WHERE c.user_id = ? AND cs.day_of_week IN (?, ?)
    ");
    $stmt->bind_param("iss", $user_id, $today, $tomorrow);
    $stmt->execute();
    $class_result = $stmt->get_result();
    while ($row = $class_result->fetch_assoc()) {
        $classes[] = $row;
    }
    
    // If there are any reminders, send email
    if (!empty($assignments) || !empty($goals) || !empty($classes)) {
        $emailBody = buildEmail($name, $assignments, $goals, $classes);
        
        if (sendEmail($email, $name, $emailBody)) {
            $sent++;
        } else {
            $errors[] = "Failed to send to $email";
        }
    }
}

echo json_encode([
    'success' => true,
    'emails_sent' => $sent,
    'errors' => $errors
]);

function buildEmail($name, $assignments, $goals, $classes) {
    $text = "Hello $name,\n\nHere are your upcoming reminders:\n\n";
    
    if (!empty($assignments)) {
        $text .= "ASSIGNMENTS:\n";
        foreach ($assignments as $a) {
            $text .= "- {$a['title']} (Due: {$a['due_date']})\n";
        }
        $text .= "\n";
    }
    
    if (!empty($goals)) {
        $text .= "GOALS:\n";
        foreach ($goals as $g) {
            $text .= "- {$g['title']} (Target: {$g['target_date']})\n";
        }
        $text .= "\n";
    }
    
    if (!empty($classes)) {
        $text .= "CLASSES:\n";
        foreach ($classes as $c) {
            $text .= "- {$c['course_name']} ({$c['course_code']}) at {$c['start_time']}\n";
        }
        $text .= "\n";
    }
    
    $text .= "Visit StudSort to manage your tasks.\n";
    
    return $text;
}

function sendEmail($to_email, $to_name, $text) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->SMTPDebug = 2; // Set to 2 for debugging
        
        $mail->setFrom(SENDER_EMAIL, SENDER_NAME);
        $mail->addAddress($to_email, $to_name);
        
        $mail->Subject = 'StudSort Reminders - Next 24 Hours';
        $mail->Body = $text;
        $mail->IsHTML(false);
        
        $result = $mail->send();
        
        if ($result) {
            error_log("Reminder email sent successfully to $to_email");
        }
        
        return $result;
    } catch (Exception $e) {
        $error_msg = "Email failed for $to_email: " . $e->getMessage();
        error_log($error_msg);
        error_log("SMTP Debug: Host=" . SMTP_HOST . ", Port=" . SMTP_PORT . ", User=" . SMTP_USER);
        return false;
    }
}
?>
