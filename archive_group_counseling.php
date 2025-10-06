<?php
session_start();
require 'dbconfig.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    
    $stmt = $pdo->prepare("UPDATE multiple_counseling_sessions SET is_archived = 1 WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Group counseling session archived successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to archive group counseling session.";
    }
}

header('Location: multiple_counseling.php');
exit();
?>
