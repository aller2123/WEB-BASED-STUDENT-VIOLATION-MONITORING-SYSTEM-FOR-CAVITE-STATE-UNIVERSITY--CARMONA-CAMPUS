<?php
session_start();
require 'dbconfig.php';

if (isset($_GET['ids'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $ids = explode(',', $_GET['ids']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        $stmt = $pdo->prepare("DELETE FROM violations WHERE id IN ($placeholders)");
        $stmt->execute($ids);

        $_SESSION['success_message'] = "Violation(s) deleted successfully.";
        
        echo json_encode(['status' => 'success', 'message' => 'Violation(s) deleted successfully']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting violation(s): ' . $e->getMessage()]);
    }
}

header('Location: violation.php');
exit();
?>
