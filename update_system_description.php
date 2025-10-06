// update_system_description.php
<?php
session_start();
require_once 'setting.php'; 

if (isset($_POST['system_description'])) {
    $newDescription = $_POST['system_description'];

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = :newDescription WHERE setting_key = 'system_description'");
    $stmt->bindParam(':newDescription', $newDescription);

    if ($stmt->execute()) {
        echo "System description updated successfully!"; // Display success message
    } else {
        echo "Error updating system description.";
    }
} 
?>
