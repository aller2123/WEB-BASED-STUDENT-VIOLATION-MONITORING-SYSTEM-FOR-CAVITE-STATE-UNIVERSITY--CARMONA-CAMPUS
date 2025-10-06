<?php
require 'dbconfig.php';

try {
    $programName = $_POST['programName'];
    
    $stmt = $pdo->prepare("INSERT INTO program (program_name) VALUES (?)");
    $result = $stmt->execute([$programName]);
    
    if($result) {
        echo json_encode(['success' => true, 'message' => 'Program added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add program']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
