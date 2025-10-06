<?php
// Database connection details
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve data from the AJAX request
    $violationType = $_POST['violationType'];
    $description = $_POST['description'];

    // Prepare the SQL statement
    $sql = "INSERT INTO typeofviolation (violation_type, description) VALUES (:type, :details)";
    $stmt = $pdo->prepare($sql);

    // Bind the parameters
    $stmt->bindParam(':type', $violationType, PDO::PARAM_STR);
    $stmt->bindParam(':details', $description, PDO::PARAM_STR);

    // Execute the statement
    if ($stmt->execute()) {
        $response = array('status' => 'success', 'message' => 'Violation added successfully.');
    } else {
        $response = array('status' => 'error', 'message' => 'Error adding violation.');
    }
} catch (PDOException $e) {
    $response = array('status' => 'error', 'message' => 'Error: ' . $e->getMessage());
}

// Return the response as JSON
echo json_encode($response);
?>
