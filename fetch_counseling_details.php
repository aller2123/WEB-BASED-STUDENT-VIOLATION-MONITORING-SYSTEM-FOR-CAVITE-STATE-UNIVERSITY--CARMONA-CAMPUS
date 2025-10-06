<?php
require 'dbconfig.php'; // Make sure this path is correct

$sessionId = $_GET['session_id'] ?? '';

if ($sessionId) {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT details FROM counseling_sessions WHERE counseling_id = :session_id");
    $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo htmlspecialchars($row['details']);
    } else {
        echo "No details available.";
    }
} else {
    echo "Session ID is missing.";
}
?>
