<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Determine assigned_to based on role
    if ($_SESSION['role'] == 'admin_pc') {
        $assigned_to = 'program_coordinator';
    } elseif ($_SESSION['role'] == 'admin_csd') {
        $assigned_to = 'coordinator_discipline';
    } elseif ($_SESSION['role'] == 'admin_cs') {
        $assigned_to = 'coordinator_welfare';
    } else {
        $assigned_to = 'superadmin';
    }

    // Insert query with proper parameter binding
    $sql = "INSERT INTO counseling_sessions (student_full_name, year_and_section, counselors_id, assigned_to, timestamp) 
            VALUES (:student_full_name, :year_and_section, :counselor_id, :assigned_to, NOW())";

    $stmt = $pdo->prepare($sql);
    
    // Bind all parameters explicitly
    $stmt->bindParam(':student_full_name', $_POST['student_full_name']);
    $stmt->bindParam(':year_and_section', $_POST['year_and_section']);
    $stmt->bindParam(':counselor_id', $_POST['counselor_id']);
    $stmt->bindParam(':assigned_to', $assigned_to);

    $stmt->execute();

    $_SESSION['success_message'] = "Walk-in student added successfully!";
    header('Location: counseling.php');
    exit();

} catch(PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: counseling.php');
    exit();
}
?>
