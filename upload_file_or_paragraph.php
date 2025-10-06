<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

// Set response header to JSON
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// Allowed extensions and MIME types
$allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
$allowedMimeTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'image/gif'
];

// Handle file upload
if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $filePath = 'uploads/' . $fileName;

    // Validate file extension
    if (!in_array($fileExtension, $allowedExtensions)) {
        $response['message'] = 'Invalid file type. Only PDF, Word, Excel, and image files are allowed.';
        echo json_encode($response);
        exit;
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmpPath);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        $response['message'] = 'Invalid file type detected by MIME check.';
        echo json_encode($response);
        exit;
    }

    // Move file to the upload directory
    if (move_uploaded_file($fileTmpPath, $filePath)) {
        $stmt = $pdo->prepare("UPDATE counseling_sessions SET file_path = ?, file_name = ? WHERE counseling_id = ?");
        if ($stmt->execute([$filePath, $fileName, $_POST['counseling_id']])) {
            $response['success'] = true;
            $response['message'] = 'File uploaded successfully!';
        } else {
            $response['message'] = 'Database update failed.';
        }
    } else {
        $response['message'] = 'File upload failed.';
    }
}

// Handle paragraph submission
if (isset($_POST['paragraph']) && !empty($_POST['paragraph'])) {
    $stmt = $pdo->prepare("UPDATE counseling_sessions SET paragraph = ? WHERE counseling_id = ?");
    if ($stmt->execute([$_POST['paragraph'], $_POST['counseling_id']])) {
        $response['success'] = true;
        $response['message'] = 'Paragraph saved successfully!';
    }
}

echo json_encode($response);
exit();
