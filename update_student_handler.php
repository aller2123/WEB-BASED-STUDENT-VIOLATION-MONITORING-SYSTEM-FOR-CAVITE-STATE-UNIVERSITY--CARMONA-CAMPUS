<?php
require 'C:\xampp\htdocs\Oserve\utils\utils.php';
session_start();

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentId = $_POST['student_id'];
    $studentNo = $_POST['student_no'];
    $gender = $_POST['gender'];
    $surname = $_POST['surname'];
    $firstName = $_POST['first_name'];
    $middleName = $_POST['middle_name'];
    $programId = $_POST['program_id'];
    $yearLevel = $_POST['year_level'];
    $birthdate = $_POST['birthdate'];
    $phoneNumber = $_POST['phone_number'];
    $status = $_POST['status'];
    $email = $_POST['email'];

    $sql = "UPDATE students SET 
        student_no = :student_no,
        first_name = :first_name,
        surname = :surname,
        middle_name = :middle_name,
        gender = :gender,
        program_id = :program_id,
        year_level = :year_level,
        birthdate = :birthdate,
        phone_number = :phone_number,
        status = :status,
        email = :email
    WHERE student_id = :student_id";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':student_id' => $studentId,
        ':student_no' => $studentNo,
        ':first_name' => $firstName,
        ':surname' => $surname,
        ':middle_name' => $middleName,
        ':gender' => $gender,
        ':program_id' => $programId,
        ':year_level' => $yearLevel,
        ':birthdate' => $birthdate,
        ':phone_number' => $phoneNumber,
        ':status' => $status,
        ':email' => $email
    ]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = 'Student updated successfully.';
            
            // Record the edit action in the history
            $username = $_SESSION['username'] ?? 'Unknown User';
            $actionDescription = sprintf("User %s updated student with ID: %s", $username, $studentId);
            recordActivity($pdo, $_SESSION['user_id'] ?? 0, $actionDescription);
        } else {
            $_SESSION['info_message'] = 'No changes were made to the student record.';
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Update failed: ' . $e->getMessage();
    }

    // Redirect to the student table page
    header("Location: student.php");
    exit();
} else {
    $_SESSION['error_message'] = 'Invalid request method.';
    header('Location: student.php');
    exit();
}
?>