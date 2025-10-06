<?php 
include 'dbconfig.php';
session_start();

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';


if (isset($_POST['authsubmit'])) {

    $email = $_POST['email'];

    try {
        // Prepared statement to avoid SQL injection
        $check_validEmail = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $check_validEmail->execute(['email' => $email]);

        if ($check_validEmail->rowCount() == 1) {
            $validEmail_row = $check_validEmail->fetch();
            $valid_email = $validEmail_row['email'];
            //$valid_fullname = $validEmail_row['first_name'] . ' ' . $validEmail_row['last_name'];

            try {
                $generateCode = uniqid();
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);

                // Uncomment the line below for debugging SMTP issues
                // $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;

                $mail->isSMTP();
                $mail->SMTPAuth = true;

                $mail->Host = "smtp.gmail.com";
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->Username = "fairyross.narito@cvsu.edu.ph";
                $mail->Password = "whxu mzwp umeh kfqs"; // Consider using environment variables for better security

                // Replace with the actual sender email address and name
                $mail->setFrom("fairyross.narito@cvsu.edu.ph", "Oserve");

                $mail->addAddress($email);

                $mail->Subject = "Forget Password Authentication Code";
                $mail->Body = $generateCode;

                $mail->send();

                $_SESSION['okToEnterAuthenticationCode'] = true;
                $_SESSION['ForgetPasswordAuthentication'] = $generateCode;
                $_SESSION['validEmail'] = $valid_email;

                echo 'Message has been sent';
                //set_Notice('Authentication Code is sent to your Email', 'success', true);
                header('location: index.php');
                exit();

            } catch (PHPMailer\PHPMailer\Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                //set_Notice("Message could not be sent. Mailer Error: {$mail->ErrorInfo}", 'danger', true);
                header('location: index.php');
                exit();
            }
        }

    } catch (PDOException $e) {
        echo "Query failed: " . $e->getMessage();
    }

} elseif (isset($_POST['submitAuthenCode'])) {
    $post_code = $_POST['authenCode'];
    $email_code = $_SESSION['ForgetPasswordAuthentication'];

    if ($post_code == $email_code) {
        $_SESSION['okToEnterPassword'] = true;

        echo "Correct authentication code, you can now update your password";
        //set_Notice('Correct Authentication Code', 'success', true);
        header('location: index.php');
        exit();

    } else {
        echo "Incorrect authentication code";
        $_SESSION['notOkToEnterPassword'] = true;
        //set_Notice('Incorrect Authentication Code', 'danger', true);
        header('location: index.php');
        exit();
    }

} elseif (isset($_POST['submitNewPass'])) {
    $valid_email = $_SESSION['validEmail'];
    $password = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);

    try {
        // Use prepared statements for the update query
        $update_pass = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
        $update_pass->execute([
            'password' => $password,
            'email' => $valid_email
        ]);

        if ($update_pass) {
            //set_Notice('Password Updated!!', 'success', true);
            header("location: index.php");
            exit();
        }

    } catch (PDOException $e) {
        echo "Update failed: " . $e->getMessage();
    }
}

?>
