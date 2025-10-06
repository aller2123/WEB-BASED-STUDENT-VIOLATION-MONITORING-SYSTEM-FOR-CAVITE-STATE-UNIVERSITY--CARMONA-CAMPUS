<?php
require 'dbconfig.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $stmt = $pdo->query("SELECT counselors_id AS id, counselors_name AS name FROM counselors");
    $counselors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($counselors);
} catch (PDOException $e) {
    die("Could not connect to the database $database :" . $e->getMessage());
}
?>
