<?php
require 'dbconfig.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $student_no = $_POST['student_no'];

        // Modified query without attendance_summary references
        $query = "SELECT s.*, p.program_name, v.type_of_violation, v.full_info
                 FROM students s
                 LEFT JOIN program p ON s.program_id = p.program_id
                 LEFT JOIN violations v ON s.student_no = v.student_no
                 WHERE s.student_no = :student_no";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':student_no', $student_no);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            // Check if there are any violations for the student
            $violations = [];
            $stmt->execute(); // Re-execute the statement to fetch all rows
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['type_of_violation']) && !empty($row['full_info'])) {
                    $violations[] = [
                        'type_of_violation' => $row['type_of_violation'],
                        'full_info' => $row['full_info'],
                    ];
                }
            }

            // Add the violations array to the student data
            $student['violations'] = $violations;

            // Return the student information as JSON
            echo json_encode($student);
        } else {
            // Return an error message if the student is not found
            echo json_encode(['error' => 'Student not found']);
        }
    }
} catch (PDOException $e) {
    // Handle the error
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>