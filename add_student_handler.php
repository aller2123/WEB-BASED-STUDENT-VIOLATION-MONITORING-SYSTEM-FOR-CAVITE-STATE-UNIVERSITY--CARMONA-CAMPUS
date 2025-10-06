<?php
session_start();
require 'C:\xampp\htdocs\Oserve\utils\utils.php'; // Include utils for database connection and activity logging

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'dbconfig.php'; // Assuming database configuration (host, user, password, database) is in this file

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "INSERT INTO students (student_no, gender, surname, first_name, middle_name, address, age, program_id, status) VALUES (:student_no, :gender, :surname, :first_name, :middle_name, :address, :age, :program_id, :status)";

        $stmt = $pdo->prepare($sql);
        
        // Binding parameters from the form
        $stmt->bindParam(':student_no', $_POST['student_no'], PDO::PARAM_STR);
        $stmt->bindParam(':gender', $_POST['gender'], PDO::PARAM_STR);
        $stmt->bindParam(':surname', $_POST['surname'], PDO::PARAM_STR);
        $stmt->bindParam(':first_name', $_POST['first_name'], PDO::PARAM_STR);
        $stmt->bindParam(':middle_name', $_POST['middle_name'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $_POST['address'], PDO::PARAM_STR);
        $stmt->bindParam(':age', $_POST['age'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $_POST['status'], PDO::PARAM_STR);
        $stmt->bindParam(':program_id', $_POST['program_id'], PDO::PARAM_INT);

        $stmt->execute();

        // If the insert was successful
       // Inside your script after a successful insert
if ($stmt->rowCount() > 0) {
    $_SESSION['success_message'] = 'Student added successfully.';
    // Assume that $username and $user_id are stored in session
    $username = $_SESSION['username'];
    $actionDescription = "User {$username} added a new student with student_no: {$_POST['student_no']}";
    recordActivity($pdo, $_SESSION['user_id'], $actionDescription);

    header("Location: student.php"); // Redirect to student list
    exit();
}

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error adding student: " . $e->getMessage();
        header("Location: add_student.php"); // Redirect back to the add form
        exit();
    }
} else {
    header("Location: add_student.php"); // Redirect to the add form if not a POST request
    exit();
}
?>
