<?php
/**
 * Cron Job Trigger for Email Reminders
 * Call via: https://your-domain/api/trigger_reminders.php?key=YOUR_KEY
 */

header('Content-Type: application/json');

$api_key = 'studsort_reminder_key'; // Change this!
$provided_key = $_GET['key'] ?? $_POST['key'] ?? '';

if ($provided_key !== $api_key) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Include and run the reminder system
include 'send_reminders.php';
?>
