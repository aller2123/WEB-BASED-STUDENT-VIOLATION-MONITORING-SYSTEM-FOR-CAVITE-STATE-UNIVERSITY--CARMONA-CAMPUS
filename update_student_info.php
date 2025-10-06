<?php
require 'dbconfig.php'; // Include your database connection
session_start();

// Check if the user is authorized
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff')) {
    header('Location: index.php');
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'];
    $req1 = $_POST['req1'];
    $req2 = $_POST['req2'];
    $req3 = $_POST['req3'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Update the requirements table with the new values
        $stmt = $pdo->prepare("UPDATE requirements SET req1 = ?, req2 = ?, req3 = ? WHERE student_id = ?");
        $stmt->execute([$req1, $req2, $req3, $studentId]);

        // Redirect back to the requirements page with a success message
        $_SESSION['success_message'] = 'Student information updated successfully.';
        header('Location: requirements.php');
        exit();
    } catch (PDOException $e) {
        // Handle the error
        $_SESSION['error_message'] = 'Error updating student information: ' . $e->getMessage();
        header('Location: requirements.php');
        exit();
    }
}
?>
