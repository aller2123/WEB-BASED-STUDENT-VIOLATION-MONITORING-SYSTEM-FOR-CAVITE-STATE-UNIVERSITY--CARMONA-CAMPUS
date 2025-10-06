<?php
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

function sendSMS($phoneNumber, $message) {
    error_log("Original phone number: " . $phoneNumber);
    
    // Format phone number
    $originalPhone = $phoneNumber;
    $phoneNumber = '+63' . substr(preg_replace('/[^0-9]/', '', $phoneNumber), -10);
    
    error_log("Formatted phone number: " . $phoneNumber);
    error_log("Message content: " . $message);

    $account_sid = 'ACf73c8ee0311cef04b4ec4be6037215ef';
    $auth_token = 'a1437eba7b00f405c139003ae952a993';
    $twilio_number = '+17756289199';

    try {
        $client = new Client($account_sid, $auth_token);
        $result = $client->messages->create(
            $phoneNumber,
            [
                'from' => $twilio_number,
                'body' => $message
            ]
        );
        
        error_log("SMS SID: " . $result->sid);
        error_log("SMS Status: " . $result->status);
        error_log("SMS Error Code: " . ($result->errorCode ?? 'none'));
        error_log("SMS Error Message: " . ($result->errorMessage ?? 'none'));
        
        return [
            'success' => true, 
            'message' => 'SMS sent successfully', 
            'status' => $result->status,
            'sid' => $result->sid,
            'original_number' => $originalPhone,
            'formatted_number' => $phoneNumber
        ];
    } catch (Exception $e) {
        error_log("SMS Error: " . $e->getMessage());
        return [
            'success' => false, 
            'message' => 'SMS Error: ' . $e->getMessage(),
            'original_number' => $originalPhone,
            'formatted_number' => $phoneNumber
        ];
    }
}
// Ensure we always return JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $name = $_POST['name'] ?? '';
    $schedule = $_POST['schedule'] ?? '';
    
    if (empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Phone number is required']);
        exit;
    }
    
    $message = "Hi {$name}, CVSU Counseling: Your schedule is on {$schedule}. Be on time. Thanks!";
    
    $result = sendSMS($phone, $message);
    echo json_encode($result);
} else {
    // Return error for non-POST requests
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
