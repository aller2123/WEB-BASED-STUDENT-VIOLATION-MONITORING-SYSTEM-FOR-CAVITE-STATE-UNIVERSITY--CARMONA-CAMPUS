<?php
session_start();
require 'dbconfig.php'; // Include your database connection settings
require 'C:\xampp\htdocs\Oserve\utils\utils.php'; // Ensure this path is correct

// Redirect unauthorized users
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff')) {
    header('Location: index.php');
    exit();
}

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if there is an AJAX request to update the student's information
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $studentId = $_POST['student_id'];
    $req1 = $_POST['req1'];
    $req2 = $_POST['req2'];
    $req3 = $_POST['req3'];

    try {
        // Update the requirements table with the new values
        $stmt = $pdo->prepare("UPDATE requirements SET req1 = :req1, req2 = :req2, req3 = :req3 WHERE student_id = :student_id");
        $stmt->execute([
            ':req1' => $req1,
            ':req2' => $req2,
            ':req3' => $req3,
            ':student_id' => $studentId
        ]);

        // Log the action to history
        $actionDesc = "Updated clearance for student ID $studentId";
        recordActivity($pdo, $_SESSION['user_id'], "User {$_SESSION['username']} " . $actionDesc);

        // Return a success response
        echo 'Student information updated successfully.';
    } catch (PDOException $e) {
        // Return an error response
        echo 'Error updating student information: ' . $e->getMessage();
    }

    // No need to redirect, as the AJAX request will handle the response
    exit();
}
?>
