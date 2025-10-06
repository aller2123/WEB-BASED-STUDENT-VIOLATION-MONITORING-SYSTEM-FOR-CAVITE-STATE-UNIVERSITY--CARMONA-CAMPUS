<?php
session_start();
require_once 'setting.php'; // Assuming setting.php includes dbconfig.php

if (isset($_POST['setting_key']) && isset($_POST['setting_value'])) {
    $settingKey = $_POST['setting_key'];
    $settingValue = $_POST['setting_value'];

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = :settingValue WHERE setting_key = :settingKey");
    $stmt->bindParam(':settingValue', $settingValue);
    $stmt->bindParam(':settingKey', $settingKey);

    if ($stmt->execute()) {
        echo "Setting updated successfully!";
    } else {
        echo "Error updating setting.";
    }
}
?>
