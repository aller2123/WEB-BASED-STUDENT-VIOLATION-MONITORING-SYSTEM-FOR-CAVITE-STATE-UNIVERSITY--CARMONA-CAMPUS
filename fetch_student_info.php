<?php
require 'dbconfig.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = $_POST['student_number'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $stmt = $pdo->prepare("SELECT s.surname, s.first_name, s.middle_name, s.email, s.phone_number, p.program_name, p.program_id
                                FROM students s
                                JOIN program p ON s.program_id = p.program_id
                                WHERE s.student_no = :student_number");
        $stmt->bindParam(':student_number', $student_number);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $response = array(
                'surname' => $row['surname'],
                'first_name' => $row['first_name'],
                'middle_name' => $row['middle_name'],
                'program_name' => $row['program_name'],
                'program_id' => $row['program_id'],
                'email' => $row['email'],
                'phone_number' => $row['phone_number'] // Added phone_number to the response
            );
            echo json_encode($response);
        } else {
            $response = array('error' => 'Student not found');
            echo json_encode($response);
        }
    } catch (PDOException $e) {
        $response = array('error' => 'Error fetching student information: ' . $e->getMessage());
        echo json_encode($response);
    }
}
?>
