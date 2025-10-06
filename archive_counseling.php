<?php
session_start();
require 'dbconfig.php';

try {
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("No counseling session ID provided for archiving.");
    }

    // Sanitize and validate the ID
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception("Invalid counseling session ID.");
    }

    // Establish database connection
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Archive the counseling session
    $stmt = $pdo->prepare("UPDATE counseling_sessions SET is_archived = 1 WHERE counseling_id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success_message'] = "Counseling session successfully archived.";
    } else {
        throw new Exception("Failed to archive counseling session.");
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}

// Redirect to the counseling sessions page
header('Location: counseling.php');
exit();
