<?php
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff')) {
    header('Location: index.php');
    exit();
}

require 'dbconfig.php'; // Ensure this file contains your PDO connection code

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'], $_POST['status'])) {
    $studentId = $_POST['student_id'];
    $status = $_POST['status'];

    $sql = "UPDATE students SET status = :status WHERE student_id = :student_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Status updated successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to update status.";
    }
    
    header("Location: student.php"); // Redirect back to the student page
    exit();
}
?>
