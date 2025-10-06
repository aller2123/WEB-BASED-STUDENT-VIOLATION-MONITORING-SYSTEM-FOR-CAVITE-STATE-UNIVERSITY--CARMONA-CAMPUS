
<?php
session_start();
require_once 'setting.php'; 

if (isset($_POST['quality_policy'])) {
    $newPolicy = $_POST['quality_policy'];

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = :newPolicy WHERE setting_key = 'quality_policy'");
    $stmt->bindParam(':newPolicy', $newPolicy);

    if ($stmt->execute()) {
        echo "Quality policy updated successfully!";
    } else {
        echo "Error updating quality policy.";
    }
}
?>
