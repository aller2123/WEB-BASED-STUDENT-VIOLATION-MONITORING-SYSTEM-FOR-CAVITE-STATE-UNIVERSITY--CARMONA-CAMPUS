
<?php
session_start();
require_once 'setting.php'; 

if (isset($_POST['mission_statement'])) {
    $newMission = $_POST['mission_statement'];

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = :newMission WHERE setting_key = 'mission_statement'");
    $stmt->bindParam(':newMission', $newMission);

    if ($stmt->execute()) {
        echo "Mission statement updated successfully!";
    } else {
        echo "Error updating mission statement.";
    }
}
?>
