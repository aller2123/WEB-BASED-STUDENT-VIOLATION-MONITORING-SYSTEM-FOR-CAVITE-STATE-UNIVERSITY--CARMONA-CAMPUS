<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection variables
$host = 'localhost';
$dbUsername = 'root'; // Use the root username for MySQL
$password = ''; // The password for the MySQL root user
$dbname = 'SIMS'; // The database name

try {
    // Establish the database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");

    // Check if the form was submitted and user_id is present
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
        // Collect form data
        $userId = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        // Prepare and execute the update statement
        $stmt = $conn->prepare("UPDATE users SET username = :username, email = :email, role = :role WHERE user_id = :user_id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':user_id', $userId);

        $stmt->execute();

        // Check if any row was actually updated
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "User updated successfully.";
        } else {
            $_SESSION['error_message'] = "No changes made to the user or user does not exist.";
        }
        header('Location: users.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header('Location: edit_user.php?id=' . (isset($_POST['user_id']) ? $_POST['user_id'] : ''));
    exit();
}

// Redirect to the users page if the form was not submitted
header('Location: users.php');
exit();
?>
