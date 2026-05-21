<?php
/**
 * Test script for debugging reminders
 */

// Set working directory to API folder
chdir(__DIR__);

// Set the GET parameter for authentication
$_GET['key'] = 'studsort_reminder_key';

echo "=== REMINDER DEBUG TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "Working Directory: " . getcwd() . "\n\n";

// Include and run the reminder system
include 'send_reminders.php';
?>
