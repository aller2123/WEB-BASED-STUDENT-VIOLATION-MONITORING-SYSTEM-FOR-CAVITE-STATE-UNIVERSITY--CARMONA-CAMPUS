<?php
session_start();
require 'C:\xampp\htdocs\Oserve\utils\utils.php';

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';

$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

$counselorId = $_POST['counselor_id'];
$scheduleTime = $_POST['schedule_time'];
$sessionId = $_POST['session_id'];

// Check for existing appointments
$stmt = $pdo->prepare("SELECT COUNT(*) FROM counseling_sessions 
    WHERE counselors_id = :counselor_id 
    AND schedule_time = :schedule_time 
    AND status = 'Scheduled'
    AND counseling_id != :current_session_id");

$stmt->execute([
    ':counselor_id' => $counselorId,
    ':schedule_time' => $scheduleTime,
    ':current_session_id' => $sessionId
]);

$isAvailable = ($stmt->fetchColumn() == 0);

echo json_encode(['available' => $isAvailable]);
