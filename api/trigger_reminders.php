<?php
header('Content-Type: application/json');

$api_key = 'studsort_reminder_key';
$provided_key = $_GET['key'] ?? $_POST['key'] ?? '';

if ($provided_key !== $api_key) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

include 'send_reminders.php';
?>
