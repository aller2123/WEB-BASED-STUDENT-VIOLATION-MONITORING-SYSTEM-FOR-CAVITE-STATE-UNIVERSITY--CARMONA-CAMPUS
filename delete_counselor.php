<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';

header('Content-Type: application/json'); // Ensure we're outputting a JSON format

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['counselor_id'])) {
        $counselorId = $_POST['counselor_id'];

        // ... Additional code to check for counselor in use ...

        $stmt = $pdo->prepare("DELETE FROM counselors WHERE counselors_id = :counselors_id");
        $stmt->bindParam(':counselors_id', $counselorId, PDO::PARAM_INT);
        $success = $stmt->execute();

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Counselor deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete counselor.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No counselor ID provided.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
