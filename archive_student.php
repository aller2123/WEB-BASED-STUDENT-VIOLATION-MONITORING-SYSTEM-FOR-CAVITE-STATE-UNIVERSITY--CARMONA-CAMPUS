<?php
require 'dbconfig.php';
session_start();

if (isset($_GET['id'])) {
    $student_id = $_GET['id'];
    $stmt = $pdo->prepare("UPDATE students SET is_archived = 1 WHERE student_id = ?");
    if ($stmt->execute([$student_id])) {
        $_SESSION['success_message'] = "Student archived successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to archive student.";
    }
}

header('Location: student.php');
exit();
