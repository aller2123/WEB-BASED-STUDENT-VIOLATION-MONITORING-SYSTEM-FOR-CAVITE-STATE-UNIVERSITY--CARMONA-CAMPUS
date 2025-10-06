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

        // Update the is_archived status
        $stmt = $pdo->prepare("UPDATE multiple_violations SET is_archived = 1 WHERE id = ?");
        $stmt->execute([$violation_id]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success_message'] = "Group violation successfully archived.";
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error archiving group violation: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "No violation ID provided for archiving.";
}

// Redirect back to the violations page
header("Location: multiple_violations.php");
exit();
