<?php
function sendSMS($phoneNumber, $message) {
    // InfoBip API Key
    $apiKey = 'YOUR_INFOBIP_API_KEY';
    
    // Format the phone number (remove +63 if present and ensure 09 prefix)
    $phoneNumber = preg_replace('/^\+63/', '0', $phoneNumber);
    
    // Prepare the API request
    $ch = curl_init();
    $parameters = [
        'apikey' => $apiKey,
        'number' => $phoneNumber,
        'message' => $message,
        'sendername' => 'CVSU-COUNSEL'
    ];
    
    curl_setopt($ch, CURLOPT_URL, 'https://api.infobip.com/sms/1/text/single');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $name = $_POST['name'];
    $schedule = $_POST['schedule'];
    
    // Shorter message format
    $message = "Hi {$name}, CVSU Counseling: Schedule on {$schedule}. Be on time.";
    
    if (sendSMS($phone, $message)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
