<?php
session_start();
require 'dbconfig.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

    $studentId = $_GET['student_id'];

    $stmt = $pdo->prepare("
    SELECT students.student_id, program.program_id, program.program_name
    FROM students
    JOIN program ON students.program_id = program.program_id
    WHERE students.student_id = :student_id
");

    $stmt->bindParam(':student_id', $studentId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $studentId = $result['student_id'];
    $programId = $result['program_id'];
    $programName = $result['program_name'];
    
    $response = [
        'student_id' => $studentId,
        'program_id' => $programId,
        'program_name' => $programName
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
