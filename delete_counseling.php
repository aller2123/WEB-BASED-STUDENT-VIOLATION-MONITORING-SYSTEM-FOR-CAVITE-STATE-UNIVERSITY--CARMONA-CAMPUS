<?php
session_start();
require 'C:\xampp\htdocs\Oserve\utils\utils.php'; // Include your database connection

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';

// Establish connection
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

// Check if the 'id' GET parameter is set
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the session details for logging before deletion
    $selectStmt = $pdo->prepare("SELECT * FROM counseling_sessions WHERE counseling_id = :counseling_id");
    $selectStmt->execute([':counseling_id' => $id]);
    $session = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if ($session) {
        // Prepare the delete statement
        $stmt = $pdo->prepare("DELETE FROM counseling_sessions WHERE counseling_id = :counseling_id");
        $stmt->execute([':counseling_id' => $id]);

        if ($stmt->rowCount() > 0) {
            // If deletion was successful, log this action
            $actionDescription = "User  {$_SESSION['username']} deleted counseling session for {$session['student_full_name']} (ID: $id)";
            recordActivity($pdo, $_SESSION['user_id'], $actionDescription);
            $_SESSION['success_message'] = 'Counseling session deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to delete counseling session.';
        }
    } else {
        $_SESSION['error_message'] = 'Counseling session not found.';
    }
} else {
    $_SESSION['error_message'] = 'No ID specified for deletion.';
}

// Redirect back to the counseling page
header('Location: counseling.php');
exit;
?>