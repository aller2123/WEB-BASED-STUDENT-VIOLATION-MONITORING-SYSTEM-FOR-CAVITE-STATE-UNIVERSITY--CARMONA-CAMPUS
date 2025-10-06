<?php
session_start();
require 'dbconfig.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("UPDATE multiple_counseling_sessions SET is_archived = 0 WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $_SESSION['success_message'] = "Group counseling session successfully restored.";
        } else {
            $_SESSION['error_message'] = "Failed to restore group counseling session.";
        }
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
}

header('Location: counseling.php');
exit();
