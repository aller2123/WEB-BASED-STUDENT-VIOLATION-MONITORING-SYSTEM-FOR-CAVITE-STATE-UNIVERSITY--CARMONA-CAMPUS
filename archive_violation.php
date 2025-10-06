<?php
session_start();
require 'dbconfig.php';

// Ensure only authorized users can access this file
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin', 'staff', 'admin_cs', 'admin_csd', 'admin_pc'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['ids'])) {
    $ids = explode(',', $_GET['ids']);
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("UPDATE violations SET is_archived = 1 WHERE id = ?");

        $pdo->beginTransaction();

        foreach ($ids as $id) {
            $stmt->execute([$id]);
        }

        $pdo->commit();

        $_SESSION['success_message'] = "Violation(s) successfully archived.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error archiving violation(s): " . $e->getMessage();
    }
}

header('Location: violation.php');
exit();
