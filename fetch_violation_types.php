<?php
require 'dbconfig.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

    // Fetch violation types from the database
    $stmt = $pdo->query("SELECT violation_type, description FROM typeofviolation");
    $violationTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the violation types as JSON
    echo json_encode($violationTypes);
} catch (PDOException $e) {
    die("Could not connect to the database $database :" . $e->getMessage());
}
