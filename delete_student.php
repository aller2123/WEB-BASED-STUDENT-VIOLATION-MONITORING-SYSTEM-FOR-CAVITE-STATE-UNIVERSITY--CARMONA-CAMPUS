<?php
session_start();
require 'dbconfig.php';
require 'C:\xampp\htdocs\Oserve\utils\utils.php';

// Function to send JSON response
function sendJsonResponse($success, $message) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

// Check if the user is logged in and has the correct role to delete a student
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'superadmin')) {
    sendJsonResponse(false, 'Unauthorized access. You do not have permission to delete students.');
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = :student_id");
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $username = $_SESSION['username'];
            $actionDescription = sprintf("User %s deleted student with ID: %s", $username, $student_id);
            recordActivity($pdo, $_SESSION['user_id'], $actionDescription);
            $pdo->commit();
            sendJsonResponse(true, 'Student successfully deleted.');
        } else {
            $pdo->rollBack();
            sendJsonResponse(false, 'No student found with that ID.');
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendJsonResponse(false, "Error deleting student: " . $e->getMessage());
    }
} else {
    sendJsonResponse(false, 'Invalid request.');
}
?>