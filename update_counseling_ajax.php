<?php
session_start();
require 'C:\xampp\htdocs\Oserve\utils\utils.php';  // Include utils for database and activity logging

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';

$response = ['success' => false, 'message' => ''   ];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if we have the required data
    if (!isset($_POST['counseling_id'])) {
        $response['message'] = 'No counseling ID provided';
        echo json_encode($response);
        exit;
    }
    
    $counselingId = $_POST['counseling_id'];
    $studentName = $_POST['student_full_name'] ?? '';
    $yearAndSection = $_POST['year_and_section'] ?? '';
    $phoneNumber = $_POST['phone_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $withViolation = isset($_POST['with_violation']) ? 1 : 0;
    $counselorsId = $_POST['counselors_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $scheduleTime = ($status === 'Scheduled') ? $_POST['schedule_time'] : null;
    $notificationSent = isset($_POST['send_notification']) && $_POST['send_notification'] == 1;
    
    // Update the counseling session
    $stmt = $pdo->prepare("UPDATE counseling_sessions SET 
        student_full_name = :student_full_name, 
        year_and_section = :year_and_section, 
        phone_number = :phone_number,
        email = :email,
        with_violation = :with_violation, 
        counselors_id = :counselors_id, 
        status = :status,
        schedule_time = CASE WHEN :status = 'Scheduled' THEN :schedule_time ELSE schedule_time END 
        WHERE counseling_id = :counseling_id");
    
    $updateSuccessful = $stmt->execute([
        ':student_full_name' => $studentName,
        ':year_and_section' => $yearAndSection,
        ':phone_number' => $phoneNumber,
        ':email' => $email,
        ':with_violation' => $withViolation,
        ':counselors_id' => $counselorsId,
        ':status' => $status,
        ':schedule_time' => $scheduleTime,
        ':counseling_id' => $counselingId
    ]);
    
    if ($updateSuccessful) {
        // Log the activity
        if ($notificationSent) {
            $actionDescription = "Updated counseling session for " . $studentName . " and sent email notification";
        } else {
            $actionDescription = "Updated counseling session for " . $studentName;
        }
        
        recordActivity($pdo, $_SESSION['user_id'], $actionDescription);
        
        // Update corresponding violation based on counseling session status
        if ($status == 'Completed') {
            // Update violation status to 'Completed'
            $stmt = $pdo->prepare("UPDATE violations SET status = 'Completed' WHERE full_name = ? AND year_and_section = ?");
            $stmt->execute([$studentName, $yearAndSection]);
        } elseif ($status == 'Scheduled') {
            // Update violation status to 'Scheduled'
            $stmt = $pdo->prepare("UPDATE violations SET status = 'Scheduled' WHERE full_name = ? AND year_and_section = ?");
            $stmt->execute([$studentName, $yearAndSection]);
        }
        
        $response['success'] = true;
        $response['message'] = 'Counseling session updated successfully';
    } else {
        $response['message'] = 'Failed to update counseling session';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>
