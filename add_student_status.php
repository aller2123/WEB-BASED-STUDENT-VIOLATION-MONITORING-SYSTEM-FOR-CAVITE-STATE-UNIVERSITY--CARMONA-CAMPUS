<?php
require 'dbconfig.php'; // Include your database connection
session_start();
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection variables
$host = 'localhost';
$dbUsername = 'root'; // MySQL root username
$password = ''; // MySQL root password
$dbname = 'SIMS'; // Database name
$conn = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Output current GET parameters (for debugging purposes)
// echo "<pre>GET: "; print_r($_GET); echo "</pre>";

// Redirect unauthorized users
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff')) {
    header('Location: index.php');
    exit();
}

// Check if the student ID is provided and is not just white spaces
if (!isset($_GET['student_id']) || empty(trim($_GET['student_id']))) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
        $studentId = $_POST['student_id'];
        $req1 = isset($_POST['req1']) ? $_POST['req1'] : 'No';
        $req2 = isset($_POST['req2']) ? $_POST['req2'] : 'No';
        $req3 = isset($_POST['req3']) ? $_POST['req3'] : 'No';
        $clearanceStatus = $_POST['clearance_status'] ?? 'Incomplete';
    
        $updateSql = "UPDATE requirements SET req1 = :req1, req2 = :req2, req3 = :req3, clearance_status = :clearance_status WHERE student_id = :student_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            ':req1' => $req1,
            ':req2' => $req2,
            ':req3' => $req3,
            ':clearance_status' => $clearanceStatus,
            ':student_id' => $studentId
        ]);
    
        $_SESSION['success_message'] = "Student status updated successfully.";
        header('Location: student.php');
        exit();
    }
    
            header('Location: status.php?student_id=' . urlencode($studentId));
}

$studentId = trim($_GET['student_id']);
    
// Continue with the rest of your code...


// Database operation to fetch student data
try {
    $sql = "SELECT * FROM students WHERE student_id = :student_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['student_id' => $studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo "Student not found";
        exit();
    }

    $studentName = isset($student['first_name']) ? $student['first_name'] . ' ' . $student['surname'] : 'N/A';
    $studentNo = $student['student_no'] ?? 'N/A';

    $reqSql = "SELECT req1, req2, req3, clearance_status FROM requirements WHERE student_id = :student_id";
    $reqStmt = $pdo->prepare($reqSql);
    $reqStmt->execute(['student_id' => $studentId]);
    $requirements = $reqStmt->fetch(PDO::FETCH_ASSOC);

    $req1 = $requirements['req1'] ?? 'No';
    $req2 = $requirements['req2'] ?? 'No';
    $req3 = $requirements['req3'] ?? 'No';
    $clearanceStatus = $requirements['clearance_status'] ?? 'Incomplete';
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle the form submission for updating requirements
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $studentId = $_POST['student_id'];
    $req1 = $_POST['req1'] ?? 'No';
    $req2 = $_POST['req2'] ?? 'No';
    $req3 = $_POST['req3'] ?? 'No';
    $clearanceStatus = $_POST['clearanceStatus'] ?? 'Incomplete';

    try {
        $updateSql = "UPDATE requirements SET req1 = :req1, req2 = :req2, req3 = :req3, clearance_status = :clearance_status WHERE student_id = :student_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            ':req1' => $req1,
            ':req2' => $req2,
            ':req3' => $req3,
            ':clearance_status' => $clearanceStatus,
            ':student_id' => $studentId
        ]);

        if ($updateStmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Requirements updated successfully.";
        } else {
            $_SESSION['error_message'] = "No changes were made.";
        }

        header('Location: status.php?student_id=' . urlencode($studentId));
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header('Location: status.php?student_id=' . $studentId);
        exit();
    }
}
?>