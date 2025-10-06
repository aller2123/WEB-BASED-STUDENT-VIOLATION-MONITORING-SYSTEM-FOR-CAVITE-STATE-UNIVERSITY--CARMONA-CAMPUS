<?php
// Include necessary files
require 'dbconfig.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';
    $schedule = $_POST['schedule'] ?? '';
    $subject = $_POST['subject'] ?? 'Counseling Session Reminder';
    $message = $_POST['message'] ?? '';
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'osas.carmona@gmail.com'; // Replace with your email
        $mail->Password = 'dmlt joas chhw ygkx'; // Replace with your email password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('yours.osas.carmona@gmail.com', 'Counseling Office'); // Replace with your email and name
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        // Convert plain text message to HTML
        $htmlMessage = nl2br(htmlspecialchars($message));
        
        // Add disclaimer to the email
        $disclaimer = "<hr><p style='font-size: 12px; color: #666;'><strong>DISCLAIMER:</strong> This email and any files transmitted with it are confidential and intended solely for the use of the individual or entity to whom they are addressed. If you have received this email in error, please notify the sender immediately and delete this email from your system. Any unauthorized copying, disclosure or distribution of the material in this email is strictly prohibited.</p>";
        
        $mail->Body = $htmlMessage . $disclaimer;
        $mail->AltBody = strip_tags($message) . "\n\nDISCLAIMER: This email and any files transmitted with it are confidential and intended solely for the use of the individual or entity to whom they are addressed. If you have received this email in error, please notify the sender immediately and delete this email from your system. Any unauthorized copying, disclosure or distribution of the material in this email is strictly prohibited.";
        
        // Send the email
        $mail->send();
        
        // Log the email in the database if needed
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
            $stmt = $pdo->prepare("INSERT INTO email_logs (recipient_email, recipient_name, subject, message, sent_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$email, $name, $subject, $message]);
        } catch (PDOException $e) {
            // Log the error but continue
            error_log('Database error: ' . $e->getMessage());
        }
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
    }
} else {
    // Not a POST request
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
