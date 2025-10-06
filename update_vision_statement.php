<?php
session_start();
require_once 'setting.php'; // Assuming your database connection is in setting.php

if (isset($_POST['vision_statement'])) {
    $newVision = $_POST['vision_statement'];

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = :newVision WHERE setting_key = 'vision_statement'");
    $stmt->bindParam(':newVision', $newVision);

    if ($stmt->execute()) {
        echo "Vision statement updated successfully!";
    } else {
        echo "Error updating vision statement.";
    }
}
?>
