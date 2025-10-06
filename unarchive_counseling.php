<?php
session_start();
require 'dbconfig.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("UPDATE counseling_sessions SET is_archived = 0 WHERE counseling_id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $_SESSION['success_message'] = "Counseling session successfully unarchived.";
        } else {
            $_SESSION['error_message'] = "Failed to unarchive counseling session.";
        }
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "No counseling ID provided.";
}

header('Location: counseling.php');
exit();
