<?php
session_start();
require 'database_connection.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $violationId = $_POST['violation_id'];

    // Prepare and execute the delete statement
    $stmt = $pdo->prepare("DELETE FROM violations WHERE violation_id = :violation_id");
    $stmt->bindParam(':violation_id', $violationId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove violation.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>