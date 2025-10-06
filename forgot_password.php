<?php
require 'dbconfig.php'; // Include your database connection
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Check if the email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate a unique reset token and expiration
        $reset_token = bin2hex(random_bytes(50));
        $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Update the user record with the reset token and expiration
        $updateStmt = $pdo->prepare("UPDATE users SET reset_token = :reset_token, reset_expires = :reset_expires WHERE email = :email");
        $updateStmt->execute([
            ':reset_token' => $reset_token,
            ':reset_expires' => $reset_expires,
            ':email' => $email
        ]);

        // Send the reset link to the user's email
        // Here, you'd normally send an email with the reset link including the token
        // Example link: http://yourdomain.com/reset_password.php?token=$reset_token

        http_response_code(200); // Success
    } else {
        http_response_code(400); // Email not found
    }
} else {
    http_response_code(400); // Bad request
}
?>
