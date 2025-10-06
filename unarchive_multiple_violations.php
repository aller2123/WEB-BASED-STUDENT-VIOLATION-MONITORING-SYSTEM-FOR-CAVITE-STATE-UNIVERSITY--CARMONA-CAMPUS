<?php
session_start();
require 'dbconfig.php';

if (isset($_GET['id'])) {
    $violation_id = $_GET['id'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Begin transaction
        $pdo->beginTransaction();

        // Update the is_archived status to 0 (unarchive)
        $stmt = $pdo->prepare("UPDATE multiple_violations SET is_archived = 0 WHERE id = ?");
        $stmt->execute([$violation_id]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success_message'] = "Group violation successfully unarchived.";
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error unarchiving group violation: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "No violation ID provided for unarchiving.";
}

// Redirect back to the violations page
header("Location: violation.php");
exit();
