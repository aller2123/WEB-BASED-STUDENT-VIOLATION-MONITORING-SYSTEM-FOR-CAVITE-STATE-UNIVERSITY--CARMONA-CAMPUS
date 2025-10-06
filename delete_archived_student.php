<?php
require 'dbconfig.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $studentId = $_POST['student_id'];
    
    try {
        $sql = "DELETE FROM students WHERE student_id = ? AND is_archived = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$studentId]);
        
        $_SESSION['success_message'] = "Student permanently deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting student: " . $e->getMessage();
    }
}

header('Location: student.php');
exit();
