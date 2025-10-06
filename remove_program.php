<?php
require 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['program_id'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $stmt = $pdo->prepare("DELETE FROM program WHERE program_id = ?");
        $stmt->execute([$_POST['program_id']]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
