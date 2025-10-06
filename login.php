<?php
session_start();

// Database connection variables
$host = 'localhost';
$dbUsername = 'root';
$password = '';
$dbname = 'SIMS';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the username and password from the form
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: main.php');
            } else {
                header('Location: admin_dashboard.php');
            }
            exit();
        } else {
            // Authentication failed
            $_SESSION['login_error'] = 'Invalid username or password.';
            header('Location: index.php#portfolio');
            exit();
        }
    }
} catch (PDOException $e) {
   // Authentication failed
$_SESSION['login_error'] = 'Invalid username or password.';
header('Location: index.php?scroll_to_login=true#portfolio');
exit();
}
?>
