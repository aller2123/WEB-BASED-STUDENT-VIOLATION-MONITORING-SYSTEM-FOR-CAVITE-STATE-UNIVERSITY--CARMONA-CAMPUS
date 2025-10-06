<?php
require 'dbconfig.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $studentID = $_POST['student_id'];
    error_log("Received student ID: " . $studentID);

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Log the query and parameters
        $query = "SELECT students.student_id, students.first_name, students.middle_name, students.surname, program.program_name AS program 
                  FROM students
                  LEFT JOIN program ON students.program_id = program.program_id
                  WHERE students.student_id = ?";
        error_log("Executing query: " . $query);
        error_log("With parameter: " . $studentID);

        $stmt = $pdo->prepare($query);
        $stmt->execute([$studentID]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            $response = [
                'success' => true,
                'student_id' => $student['student_id'],
                'first_name' => $student['first_name'],
                'middle_name' => $student['middle_name'],
                'surname' => $student['surname'],
                'program' => $student['program']
            ];
            error_log("Student details found: " . json_encode($response));
        } else {
            $response = [
                'success' => false,
                'message' => 'Student not found'
            ];
            error_log("Student not found for ID: " . $studentID);
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
        error_log("Database error: " . $e->getMessage());
    }

    echo json_encode($response);
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request'
    ];
    error_log("Invalid request: " . json_encode($_POST));
    echo json_encode($response);
}
?>
