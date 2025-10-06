<?php
require 'dbconfig.php'; // Include your database connection

session_start(); // Start the session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;

    if ($studentId > 0) {
        // Update query to unarchive the student
        $sql = "UPDATE students SET is_archived = 0 WHERE student_id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$studentId])) {
            // Set success message in session and redirect
            $_SESSION['success_message'] = "Student restored successfully.";
            header('Location: student.php');
            exit();
        } else {
            // Set error message in session and redirect
            $_SESSION['error_message'] = "Failed to unarchive student.";
            header('Location: student.php');
            exit();
        }
    } else {
        // Set error message for invalid student ID
        $_SESSION['error_message'] = "Invalid student ID.";
        header('Location: student.php');
        exit();
    }
}
?>
