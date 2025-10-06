<?php
require 'dbconfig.php';

// Set headers to prevent caching and specify JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Get the student number from POST data
$student_number = isset($_POST['student_number']) ? $_POST['student_number'] : '';

if (empty($student_number)) {
    echo json_encode([]);
    exit;
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simple query to get violations
    $stmt = $pdo->prepare("SELECT 
                          DATE_FORMAT(date_created, '%Y-%m-%d') as date_created,
                          type_of_violation, 
                          full_info, 
                          offense_count
                      FROM violations 
                      WHERE student_no = ?
                      ORDER BY date_created DESC");
    $stmt->execute([$student_number]);
    
    // Fetch all violations
    $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return as JSON
    echo json_encode($violations);
    
} catch (PDOException $e) {
    // Return empty array on error
    echo json_encode([]);
}
