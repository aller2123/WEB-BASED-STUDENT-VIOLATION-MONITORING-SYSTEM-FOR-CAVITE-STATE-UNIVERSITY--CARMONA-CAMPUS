<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

// Allowed file types
$allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
$maxFileSize = 10 * 1024 * 1024; // 10 MB

$counselingId = $_POST['counseling_id'];
$file = $_FILES['file'];

if ($file['error'] === UPLOAD_ERR_OK) {
    // Check if the file type is allowed
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF and document files are allowed.']);
        exit;
    }

    // Check if the file size exceeds the limit
    if ($file['size'] > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds the 10 MB limit.']);
        exit;
    }

    $uploadDir = 'uploads/';
    $fileName = basename($file['name']);
    
    // Sanitize the file name to prevent issues with special characters
    $fileName = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $fileName);
    $filePath = $uploadDir . $fileName;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Update the database
        $sql = "UPDATE counseling_sessions SET file_path = :file_path, file_name = :file_name WHERE counseling_id = :counseling_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':file_path', $filePath);
        $stmt->bindParam(':file_name', $fileName);
        $stmt->bindParam(':counseling_id', $counselingId);
        $stmt->execute();

        echo json_encode(['success' => true, 'file_name' => $fileName, 'file_path' => $filePath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'File upload failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or an error occurred during the upload.']);
}
?>
