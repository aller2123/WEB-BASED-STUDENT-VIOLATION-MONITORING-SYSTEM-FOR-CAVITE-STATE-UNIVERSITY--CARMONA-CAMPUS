<?php
session_start();
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection variables
$host = 'localhost';
$dbUsername = 'root'; // MySQL root username
$password = ''; // MySQL root password
$dbname = 'SIMS'; // Database name

try {
    // Connect to database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $form_username = $_POST['username'];
        $email = $_POST['email'];
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        // Validate role
        if (!in_array($role, ['superadmin', 'staff', 'admin_cs', 'admin_csd', 'admin_pc'])) {
            $_SESSION['error_message'] = "Invalid role selected.";
            session_write_close(); // Ensure session data is saved before redirect
            header('Location: users.php');
            exit();
        }

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $form_username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Username or email already exists
            $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing_user['username'] === $form_username) {
                $_SESSION['error_message'] = "The username '$form_username' is already taken.";
            } else {
                $_SESSION['error_message'] = "The email '$email' is already registered.";
            }
            session_write_close(); // Ensure session data is saved before redirect
            header('Location: users.php');
            exit();
        } else {
            // Username and email do not exist, proceed with insertion
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
            $stmt->bindParam(':username', $form_username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $pass);
            $stmt->bindParam(':role', $role);
            $stmt->execute();

            $_SESSION['success_message'] = "User created successfully.";
            session_write_close(); // Ensure session data is saved before redirect
            header('Location: users.php');
            exit();
        }
    } else {
        // Form not submitted, redirect or handle accordingly
        $_SESSION['error_message'] = "Form submission failed.";
        session_write_close();
        header('Location: users.php');
        exit();
    }
} catch(PDOException $e) {
    // Error occurred during database connection or query execution
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    session_write_close(); // Ensure session data is saved before redirect
    header('Location: users.php');
    exit();
}
?>
