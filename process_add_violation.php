<?php
session_start();
require 'dbconfig.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $full_name = $_POST['full_name'];
    $year_and_section = $_POST['year_and_section'];
    $program_id = $_POST['program_id'];
    $type_of_violation = $_POST['type_of_violation'];
    $full_info = $_POST['full_info'];
    $case_offense = $_POST['case_offense'];
    $student_no = $_POST['student_no'];

    // Prepare SQL and bind parameters
    $stmt = $pdo->prepare("INSERT INTO violations (full_name, year_and_section, program_id, type_of_violation, full_info, case_offense, student_no) VALUES (:full_name, :year_and_section, :program_id, :type_of_violation, :full_info, :case_offense, :student_no)");
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':year_and_section', $year_and_section);
    $stmt->bindParam(':program_id', $program_id);
    $stmt->bindParam(':type_of_violation', $type_of_violation);
    $stmt->bindParam(':full_info', $full_info);
    $stmt->bindParam(':case_offense', $case_offense);
    $stmt->bindParam(':student_no', $student_no);

    // Execute the prepared statement
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Violation added successfully.";
    } else {
        $_SESSION['error_message'] = "Error adding violation.";
    }

    // Redirect to the violation page
    header('Location: violation.php');
    exit();
}
?>
