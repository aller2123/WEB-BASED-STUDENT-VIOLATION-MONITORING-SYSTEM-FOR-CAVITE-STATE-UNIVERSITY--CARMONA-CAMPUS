<?php
require_once 'vendor/autoload.php';

// Log the callback data
$messageSid = $_POST['MessageSid'];
$messageStatus = $_POST['MessageStatus'];
$to = $_POST['To'];

// Store status in log file
$logMessage = date('Y-m-d H:i:s') . " Message $messageSid to $to status: $messageStatus\n";
file_put_contents('sms_log.txt', $logMessage, FILE_APPEND);
