<?php
session_start();
require 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $program_names = $_POST['program_name'] ?? [];
    $y_and_s = $_POST['y_and_s'];
    $type = $_POST['type'];
    $info = $_POST['info'];
    $assigned_to = isset($_POST['assigned_to']) ? trim($_POST['assigned_to']) : '';

    // Get student names and combine into a single string
    $student_names = explode("\n", trim($_POST['student_names']));
    $student_names = array_filter(array_map('trim', $student_names));
    $student_names_string = implode(", ", $student_names);

    // Combine program names into a single string
    $program_names_string = implode(", ", $program_names);

    // Prepare the SQL statement for inserting violations
    $stmt = $pdo->prepare("INSERT INTO multiple_violations (program_related, y_and_s, type, info, student_names, assigned_to) VALUES (?, ?, ?, ?, ?, ?)");

    // Execute the insert statement
    if (!$stmt->execute([$program_names_string, $y_and_s, $type, $info, $student_names_string, $assigned_to])) {
        error_log(print_r($stmt->errorInfo(), true));
        $_SESSION['error_message'] = "Failed to add violations.";
        header('Location: violation.php');
        exit();
    }

    // If it's a major violation, also insert into multiple_counseling_sessions
    if ($type === 'Major') {
        $stmt = $pdo->prepare("INSERT INTO multiple_counseling_sessions (student_names, year_section, program, violation_type, violation_details, assigned_team) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_names_string, $y_and_s, $program_names_string, $type, $info, $assigned_to]);
    }


    if ($type === 'Major' || $type === 'Minor') {
        if ($type === 'Minor') {
            foreach ($student_names as $student) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM multiple_violations WHERE student_names LIKE ? AND type = 'Minor'");
                $stmt->execute(['%' . $student . '%']);
                $count = $stmt->fetchColumn();

                if ($count >= 2) {
                    // Insert into multiple_counseling_sessions for students with 2 or more minor violations
                    $stmt = $pdo->prepare("INSERT INTO multiple_counseling_sessions (student_names, year_section, program, violation_type, violation_details, assigned_team) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$student, $y_and_s, $program_names_string, 'Minor (Multiple)', $info, $assigned_to]);
                }
            }
        } else {
            // For major violations, insert all students
            $stmt = $pdo->prepare("INSERT INTO multiple_counseling_sessions (student_names, year_section, program, violation_type, violation_details, assigned_team) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$student_names_string, $y_and_s, $program_names_string, $type, $info, $assigned_to]);
        }
    }


    $_SESSION['success_message'] = "Multiple violations added successfully.";
    header('Location: multiple_violations.php');
    exit();
}
?>
\end{code}