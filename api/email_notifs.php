<?php

header("Content-Type: application/json");
session_start();
include "../acc/connect.php";
include "SMTPMailer.php";
include "../config/email_config.php";

if(!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit();
}

$user_id = $_SESSION["user_id"];


$user_query = "SELECT name, email FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!$user_data) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$user_name = $user_data['name'];
$user_email = $user_data['email'];


$upcoming_query = "SELECT a.assignment_id, a.title, a.due_date, a.due_time, c.course_name 
                   FROM assignments a 
                   JOIN courses c ON a.course_id = c.course_id 
                   WHERE a.user_id = ? AND a.due_date >= CURDATE() AND a.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                   ORDER BY a.due_date ASC, a.due_time ASC";
$stmt = $conn->prepare($upcoming_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$assignments_result = $stmt->get_result();
$upcoming_assignments = [];
while ($row = $assignments_result->fetch_assoc()) {
    $upcoming_assignments[] = $row;
}


$events_query = "SELECT e.event_id, e.title, e.event_date, e.start_time, e.event_type, c.course_name 
                 FROM events e 
                 JOIN courses c ON e.course_id = c.course_id 
                 WHERE e.user_id = ? AND e.event_date >= CURDATE() AND e.event_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                 ORDER BY e.event_date ASC, e.start_time ASC";
$stmt = $conn->prepare($events_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$events_result = $stmt->get_result();
$upcoming_events = [];
while ($row = $events_result->fetch_assoc()) {
    $upcoming_events[] = $row;
}


$email_subject = 'StudSort: Your Upcoming Reminders';
$email_html = buildEmailContent($user_name, $upcoming_assignments, $upcoming_events);

// Debug logging
error_log("=== Email Notification via Gmail SMTP ===");
error_log("User ID: $user_id");
error_log("User Email: $user_email");
error_log("Subject: $email_subject");
error_log("Assignments count: " . count($upcoming_assignments));
error_log("Events count: " . count($upcoming_events));
error_log("============================");

// Initialize Gmail SMTP mailer
$mailer = new SMTPMailer(
    SMTP_HOST,
    SMTP_PORT,
    SMTP_USER,
    SMTP_PASS,
    SENDER_EMAIL,
    SENDER_NAME
);

// Send the email
$mail_result = $mailer->sendEmail($user_email, $email_subject, $email_html, true);

if ($mail_result === true) {
    error_log("Email sent successfully to $user_email");
    echo json_encode(['success' => true, 'message' => 'Reminder email sent successfully to ' . $user_email]);
} else {
    error_log("FAILED to send email to $user_email via Gmail SMTP");
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Check server logs for details.']);
}


function buildEmailContent($user_name, $assignments, $events) {
    $html = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .header h1 { margin: 0; }
                .section { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #4CAF50; }
                .section h2 { margin-top: 0; color: #4CAF50; }
                .item { padding: 10px 0; border-bottom: 1px solid #ddd; }
                .item:last-child { border-bottom: none; }
                .item-title { font-weight: bold; color: #333; }
                .item-date { color: #666; font-size: 0.9em; }
                .item-course { color: #999; font-size: 0.9em; font-style: italic; }
                .empty-message { color: #999; font-style: italic; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #999; font-size: 0.9em; }
                .button { display: inline-block; background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>StudSort Reminders</h1>
                </div>
                
                <p>Hi $user_name,</p>
                <p>Here are your upcoming assignments and events for the next 7 days:</p>
    ";

    
    $html .= "<div class='section'>";
    $html .= "<h2>Upcoming Assignments</h2>";
    
    if (count($assignments) > 0) {
        foreach ($assignments as $assignment) {
            $due_date = date('M d, Y', strtotime($assignment['due_date']));
            $due_time = $assignment['due_time'] ? date('g:i A', strtotime($assignment['due_time'])) : 'Not specified';
            $html .= "
                <div class='item'>
                    <div class='item-title'>{$assignment['title']}</div>
                    <div class='item-course'>{$assignment['course_name']}</div>
                    <div class='item-date'>Due: $due_date at $due_time</div>
                </div>
            ";
        }
    } else {
        $html .= "<p class='empty-message'>No upcoming assignments</p>";
    }
    
    $html .= "</div>";

    
    $html .= "<div class='section'>";
    $html .= "<h2>Upcoming Events</h2>";
    
    if (count($events) > 0) {
        foreach ($events as $event) {
            $event_date = date('M d, Y', strtotime($event['event_date']));
            $event_time = $event['start_time'] ? date('g:i A', strtotime($event['start_time'])) : 'Not specified';
            $event_type = ucfirst(str_replace('_', ' ', $event['event_type']));
            $html .= "
                <div class='item'>
                    <div class='item-title'>{$event['title']} ($event_type)</div>
                    <div class='item-course'>{$event['course_name']}</div>
                    <div class='item-date'>Date: $event_date at $event_time</div>
                </div>
            ";
        }
    } else {
        $html .= "<p class='empty-message'>No upcoming events</p>";
    }
    
    $html .= "</div>";

    
    $html .= "
                <div class='footer'>
                    <p>This is an automated email from StudSort. Please don't reply to this email.</p>
                    <p>To manage your email preferences, log in to your StudSort account and visit the profile settings.</p>
                </div>
            </div>
        </body>
    </html>
    ";

    return $html;
}

?>
