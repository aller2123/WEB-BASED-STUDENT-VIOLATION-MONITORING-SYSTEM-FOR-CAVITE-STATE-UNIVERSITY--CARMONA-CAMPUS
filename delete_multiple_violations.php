<?php
session_start();
require 'dbconfig.php';

if (isset($_GET['id'])) {
    $violation_id = $_GET['id'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("DELETE FROM multiple_violations WHERE id = ?");
        $stmt->execute([$violation_id]);

        $_SESSION['success_message'] = "Group violation successfully deleted.";
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error deleting group violation: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "No violation ID provided for deletion.";
}

header("Location: violation.php");
exit();
