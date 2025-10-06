<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['counselor_name'])) {
    $counselorName = trim($_POST['counselor_name']);

    // Assuming your table is named `counselors` and has columns `counselors_id` (auto-increment) and `counselors_name`
    $sql = "INSERT INTO counselors (counselors_name) VALUES (:counselorName)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([':counselorName' => $counselorName])) {
        echo json_encode(["success" => true, "message" => "Counselor added successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add counselor."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Request method not supported or missing counselor name."]);
}
?>
