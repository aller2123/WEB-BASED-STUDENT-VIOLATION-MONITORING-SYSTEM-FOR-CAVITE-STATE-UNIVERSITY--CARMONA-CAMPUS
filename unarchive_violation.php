<?php
session_start();
require 'dbconfig.php';

if (isset($_GET['ids'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $ids = explode(',', $_GET['ids']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        $stmt = $pdo->prepare("UPDATE violations SET is_archived = 0 WHERE id IN ($placeholders)");
        $stmt->execute($ids);

        $_SESSION['success_message'] = "Violation(s) restored successfully.";
        
        echo json_encode(['status' => 'success', 'message' => 'Violation(s) restored successfully']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error restoring violation(s): ' . $e->getMessage()]);
    }
}

header('Location: violation.php');
exit();
?>
