<?php
ob_start();

include "../acc/connect.php";
include "SMTPMailer.php";
include "../config/email_config.php";

error_log("=== Starting Automated Reminder Cron Script ===");
error_log("Current time: " . date('Y-m-d H:i:s'));

try {
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    $current_datetime = date('Y-m-d H:i:s');
    $tomorrow_date = date('Y-m-d', strtotime('+1 day'));
    
    $mailer = new SMTPMailer(
        SMTP_HOST,
        SMTP_PORT,
        SMTP_USER,
        SMTP_PASS,
        SENDER_EMAIL,
        SENDER_NAME
    );
    

    if (date('H:i') >= '08:00' && date('H:i') < '08:05') {
        error_log("Checking for tomorrow's assignments and events...");
        
        $query = "SELECT a.assignment_id, a.title, a.due_date, a.due_time, c.course_name, u.name, u.email
                  FROM assignments a
                  JOIN courses c ON a.course_id = c.course_id
                  JOIN users u ON a.user_id = u.user_id
                  WHERE a.due_date = ? AND a.status != 'completed'
                  AND NOT EXISTS (
                    SELECT 1 FROM email_notifications 
                    WHERE user_id = a.user_id AND item_type = 'assignment' 
                    AND item_id = a.assignment_id AND reminder_type = 'day_before'
                    AND DATE(sent_at) = CURDATE()
                  )";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $tomorrow_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($assignment = $result->fetch_assoc()) {
            $subject = "Reminder: " . $assignment['title'] . " due tomorrow";
            $message = buildAssignmentEmail(
                $assignment['name'],
                $assignment['title'],
                $assignment['course_name'],
                $assignment['due_date'],
                $assignment['due_time'],
                'tomorrow'
            );
            
            if ($mailer->sendEmail($assignment['email'], $subject, $message, true)) {
                $insert_stmt = $conn->prepare("INSERT INTO email_notifications (user_id, item_type, item_id, reminder_type) VALUES (?, 'assignment', ?, 'day_before')");
                
                $user_id_query = "SELECT user_id FROM assignments WHERE assignment_id = ?";
                $uid_stmt = $conn->prepare($user_id_query);
                $uid_stmt->bind_param("i", $assignment['assignment_id']);
                $uid_stmt->execute();
                $uid_result = $uid_stmt->get_result();
                $uid_row = $uid_result->fetch_assoc();
                
                $insert_stmt->bind_param("ii", $uid_row['user_id'], $assignment['assignment_id']);
                $insert_stmt->execute();
                error_log("Sent day-before reminder for assignment: " . $assignment['title'] . " to " . $assignment['email']);
            }
        }
        
        $query = "SELECT e.event_id, e.title, e.event_date, e.start_time, c.course_name, u.name, u.email
                  FROM events e
                  JOIN courses c ON e.course_id = c.course_id
                  JOIN users u ON e.user_id = u.user_id
                  WHERE e.event_date = ?
                  AND NOT EXISTS (
                    SELECT 1 FROM email_notifications 
                    WHERE user_id = e.user_id AND item_type = 'event' 
                    AND item_id = e.event_id AND reminder_type = 'day_before'
                    AND DATE(sent_at) = CURDATE()
                  )";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $tomorrow_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($event = $result->fetch_assoc()) {
            $subject = "Reminder: " . $event['title'] . " coming tomorrow";
            $message = buildEventEmail(
                $event['name'],
                $event['title'],
                $event['course_name'],
                $event['event_date'],
                $event['start_time'],
                'tomorrow'
            );
            
            if ($mailer->sendEmail($event['email'], $subject, $message, true)) {
                $insert_stmt = $conn->prepare("INSERT INTO email_notifications (user_id, item_type, item_id, reminder_type) VALUES (?, 'event', ?, 'day_before')");
                
                $user_id_query = "SELECT user_id FROM events WHERE event_id = ?";
                $uid_stmt = $conn->prepare($user_id_query);
                $uid_stmt->bind_param("i", $event['event_id']);
                $uid_stmt->execute();
                $uid_result = $uid_stmt->get_result();
                $uid_row = $uid_result->fetch_assoc();
                
                $insert_stmt->bind_param("ii", $uid_row['user_id'], $event['event_id']);
                $insert_stmt->execute();
                error_log("Sent day-before reminder for event: " . $event['title'] . " to " . $event['email']);
            }
        }
    }
    
    error_log("Checking for today's due assignments and events...");
    
    $query = "SELECT a.assignment_id, a.title, a.due_date, a.due_time, c.course_name, u.name, u.email
              FROM assignments a
              JOIN courses c ON a.course_id = c.course_id
              JOIN users u ON a.user_id = u.user_id
              WHERE a.due_date = ? AND a.status != 'completed'
              AND a.due_time IS NOT NULL
              AND NOT EXISTS (
                SELECT 1 FROM email_notifications 
                WHERE user_id = a.user_id AND item_type = 'assignment' 
                AND item_id = a.assignment_id AND reminder_type = 'at_time'
                AND DATE(sent_at) = CURDATE()
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($assignment = $result->fetch_assoc()) {
        $due_time = strtotime($assignment['due_time']);
        $current_unix = strtotime($current_time);
        $diff = abs($due_time - $current_unix);
        
        if ($diff <= 300) { 
            $subject = "Assignment due NOW: " . $assignment['title'];
            $message = buildAssignmentEmail(
                $assignment['name'],
                $assignment['title'],
                $assignment['course_name'],
                $assignment['due_date'],
                $assignment['due_time'],
                'now'
            );
            
            if ($mailer->sendEmail($assignment['email'], $subject, $message, true)) {
                $insert_stmt = $conn->prepare("INSERT INTO email_notifications (user_id, item_type, item_id, reminder_type) VALUES (?, 'assignment', ?, 'at_time')");
                
                $user_id_query = "SELECT user_id FROM assignments WHERE assignment_id = ?";
                $uid_stmt = $conn->prepare($user_id_query);
                $uid_stmt->bind_param("i", $assignment['assignment_id']);
                $uid_stmt->execute();
                $uid_result = $uid_stmt->get_result();
                $uid_row = $uid_result->fetch_assoc();
                
                $insert_stmt->bind_param("ii", $uid_row['user_id'], $assignment['assignment_id']);
                $insert_stmt->execute();
                error_log("Sent at-time reminder for assignment: " . $assignment['title'] . " to " . $assignment['email']);
            }
        }
    }
    
    $query = "SELECT e.event_id, e.title, e.event_date, e.start_time, c.course_name, u.name, u.email
              FROM events e
              JOIN courses c ON e.course_id = c.course_id
              JOIN users u ON e.user_id = u.user_id
              WHERE e.event_date = ? AND e.start_time IS NOT NULL
              AND NOT EXISTS (
                SELECT 1 FROM email_notifications 
                WHERE user_id = e.user_id AND item_type = 'event' 
                AND item_id = e.event_id AND reminder_type = 'at_time'
                AND DATE(sent_at) = CURDATE()
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($event = $result->fetch_assoc()) {
        $start_time = strtotime($event['start_time']);
        $current_unix = strtotime($current_time);
        $diff = abs($start_time - $current_unix);
        
        if ($diff <= 300) {
            $subject = ucfirst(str_replace('_', ' ', $event['event_type'])) . " starting NOW: " . $event['title'];
            $message = buildEventEmail(
                $event['name'],
                $event['title'],
                $event['course_name'],
                $event['event_date'],
                $event['start_time'],
                'now'
            );
            
            if ($mailer->sendEmail($event['email'], $subject, $message, true)) {
                $insert_stmt = $conn->prepare("INSERT INTO email_notifications (user_id, item_type, item_id, reminder_type) VALUES (?, 'event', ?, 'at_time')");
                
                $user_id_query = "SELECT user_id FROM events WHERE event_id = ?";
                $uid_stmt = $conn->prepare($user_id_query);
                $uid_stmt->bind_param("i", $event['event_id']);
                $uid_stmt->execute();
                $uid_result = $uid_stmt->get_result();
                $uid_row = $uid_result->fetch_assoc();
                
                $insert_stmt->bind_param("ii", $uid_row['user_id'], $event['event_id']);
                $insert_stmt->execute();
                error_log("Sent at-time reminder for event: " . $event['title'] . " to " . $event['email']);
            }
        }
    }
    
    error_log("Checking for classes starting in 5 minutes...");
    
    $day_names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $today_day = $day_names[date('w')];
    
    $query = "SELECT cs.schedule_id, cs.course_id, cs.start_time, c.course_name, c.course_code, c.user_id, u.name, u.email
              FROM class_schedule cs
              JOIN courses c ON cs.course_id = c.course_id
              JOIN users u ON c.user_id = u.user_id
              WHERE cs.day_of_week = ? AND cs.start_time IS NOT NULL
              AND NOT EXISTS (
                SELECT 1 FROM email_notifications 
                WHERE user_id = c.user_id AND item_type = 'class' 
                AND item_id = cs.schedule_id AND reminder_type = 'five_minutes_before'
                AND DATE(sent_at) = CURDATE()
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today_day);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($class = $result->fetch_assoc()) {
        $class_start = strtotime($class['start_time']);
        $current_unix = strtotime($current_time);
        $diff = $class_start - $current_unix;
        
        
        if ($diff > 240 && $diff <= 360) {
            $subject = "Class starting in 5 minutes: " . $class['course_code'];
            $message = buildClassReminderEmail(
                $class['name'],
                $class['course_name'],
                $class['course_code'],
                $class['start_time']
            );
            
            if ($mailer->sendEmail($class['email'], $subject, $message, true)) {
                $insert_stmt = $conn->prepare("INSERT INTO email_notifications (user_id, item_type, item_id, reminder_type) VALUES (?, 'class', ?, 'five_minutes_before')");
                $insert_stmt->bind_param("ii", $class['user_id'], $class['schedule_id']);
                $insert_stmt->execute();
                error_log("Sent 5-minute reminder for class: " . $class['course_code'] . " to " . $class['email']);
            }
        }
    }
    
    error_log("=== Completed Automated Reminder Cron Script ===");
    
} catch (Exception $e) {
    error_log("ERROR in cron script: " . $e->getMessage());
}

$conn->close();

function buildAssignmentEmail($user_name, $title, $course_name, $due_date, $due_time, $timing) {
    $formatted_date = date('M d, Y', strtotime($due_date));
    $formatted_time = $due_time ? date('g:i A', strtotime($due_time)) : 'Not specified';
    
    $timing_text = ($timing === 'tomorrow') ? 'tomorrow' : 'TODAY';
    $urgency = ($timing === 'now') ? '<strong>URGENT:</strong>' : '';
    
    $html = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #ff6b6b; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { margin: 20px 0; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ff6b6b; }
                .item-title { font-weight: bold; font-size: 18px; color: #333; }
                .item-course { color: #666; font-style: italic; }
                .item-date { color: #d9534f; font-weight: bold; }
                .footer { margin-top: 30px; text-align: center; color: #999; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>$urgency Assignment Due $timing_text</h1>
                </div>
                <div class='content'>
                    <p>Hi $user_name,</p>
                    <p>Your assignment <strong>$title</strong> for <strong>$course_name</strong> is due $timing_text.</p>
                    <div class='item-date'>Due: $formatted_date at $formatted_time</div>
                    <p>Make sure to submit your work on time!</p>
                </div>
                <div class='footer'>
                    <p>This is an automated reminder from StudSort.</p>
                </div>
            </div>
        </body>
    </html>
    ";
    
    return $html;
}

function buildEventEmail($user_name, $title, $course_name, $event_date, $start_time, $timing) {
    $formatted_date = date('M d, Y', strtotime($event_date));
    $formatted_time = $start_time ? date('g:i A', strtotime($start_time)) : 'Not specified';
    
    $timing_text = ($timing === 'tomorrow') ? 'tomorrow' : 'TODAY';
    $urgency = ($timing === 'now') ? '<strong>HAPPENING NOW:</strong>' : '';
    
    $html = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { margin: 20px 0; padding: 15px; background-color: #cfe2ff; border-left: 4px solid #007bff; }
                .item-title { font-weight: bold; font-size: 18px; color: #333; }
                .item-course { color: #666; font-style: italic; }
                .item-date { color: #0056b3; font-weight: bold; }
                .footer { margin-top: 30px; text-align: center; color: #999; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>$urgency Event $timing_text</h1>
                </div>
                <div class='content'>
                    <p>Hi $user_name,</p>
                    <p>Your event <strong>$title</strong> for <strong>$course_name</strong> is $timing_text.</p>
                    <div class='item-date'>Date/Time: $formatted_date at $formatted_time</div>
                    <p>Don't miss it!</p>
                </div>
                <div class='footer'>
                    <p>This is an automated reminder from StudSort.</p>
                </div>
            </div>
        </body>
    </html>
    ";
    
    return $html;
}

function buildClassReminderEmail($user_name, $course_name, $course_code, $start_time) {
    $formatted_time = date('g:i A', strtotime($start_time));
    
    $html = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { margin: 20px 0; padding: 15px; background-color: #d4edda; border-left: 4px solid #28a745; }
                .item-title { font-weight: bold; font-size: 18px; color: #333; }
                .item-time { color: #155724; font-weight: bold; }
                .footer { margin-top: 30px; text-align: center; color: #999; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Class Starting in 5 Minutes</h1>
                </div>
                <div class='content'>
                    <p>Hi $user_name,</p>
                    <p>Your class <strong>$course_code: $course_name</strong> is starting in 5 minutes!</p>
                    <div class='item-time'>Starts at: $formatted_time</div>
                    <p>Get ready to join!</p>
                </div>
                <div class='footer'>
                    <p>This is an automated reminder from StudSort.</p>
                </div>
            </div>
        </body>
    </html>
    ";
    
    return $html;
}

?>
