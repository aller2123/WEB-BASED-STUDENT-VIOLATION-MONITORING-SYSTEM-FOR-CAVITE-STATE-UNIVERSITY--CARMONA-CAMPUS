<?php
$host = 'localhost';
$user = 'root';
$password = '';  // If your root user has a password, include it here
$database = 'SIMS'; // Correct database name assigned to the variable

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password, $options); // Use the correct variable $database
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Database connection failed: ' . $e->getMessage()); // Added error message for better debugging
}
?>
