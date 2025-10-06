<?php
require 'dbconfig.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = $_POST['student_id'];
    $req1 = $_POST['req1'] ?? 'No';
    $req2 = $_POST['req2'] ?? 'No';
    $req3 = $_POST['req3'] ?? 'No';
    $clearanceStatus = $_POST['clearance_status'];

    $sql = "UPDATE requirements SET req1 = :req1, req2 = :req2, req3 = :req3, clearance_status = :clearance_status WHERE student_id = :student_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':req1' => $req1,
        ':req2' => $req2,
        ':req3' => $req3,
        ':clearance_status' => $clearanceStatus,
        ':student_id' => $studentId
    ]);
    $_SESSION['success_message'] = "Student requirements updated successfully.";

    header('Location: requirements.php');
    exit();
}
