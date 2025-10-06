<?php
session_start();
require 'dbconfig.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin', 'staff', 'admin_cs', 'admin_csd', 'admin_pc'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: violation.php');
    exit();
}

$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_names = $_POST['student_names'];
    $y_and_s = $_POST['y_and_s'];
    $program_related = $_POST['program_related'];
    $type = $_POST['type'];
    $info = $_POST['info'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE multiple_violations SET student_names = ?, y_and_s = ?, program_related = ?, type = ?, info = ?, status = ? WHERE id = ?");
    $stmt->execute([$student_names, $y_and_s, $program_related, $type, $info, $status, $id]);

    $newStatus = $_POST['status'];
    
    // Update counseling status
    $stmt = $pdo->prepare("UPDATE multiple_counseling_sessions SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);

    // If status is set to 'Completed', update corresponding violation status
    if ($newStatus == 'Completed') {
        $stmt = $pdo->prepare("UPDATE multiple_violations SET status = 'Completed' WHERE student_names = ? AND y_and_s = ? AND program_related = ?");
        $stmt->execute([$session['student_names'], $session['year_section'], $session['program']]);
    }

    $_SESSION['success_message'] = "Group violation updated successfully.";
    header('Location: violation.php');
    exit();
}
$stmt = $pdo->prepare("SELECT * FROM multiple_violations WHERE id = ?");
$stmt->execute([$id]);
$violation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$violation) {
    header('Location: violation.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Group Violation</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<style>
    body {
              background-color: #f8f9fa;
              padding-top: 10px;
              padding-bottom: 10px;
          }
          .form-container {
              background-color: #ffffff;
              padding: 30px;
              border-radius: 10px;
              box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
          }
          h2 {
              color: #4c704c;
              margin-bottom: 30px;
          }
          .form-group label {
              font-weight: bold;
              color: #495057;
          }
          .checkbox-group {
              max-height: 200px;
              overflow-y: auto;
              border: 1px solid #ced4da;
              border-radius: 4px;
              padding: 10px;
          }
          .btn-submit {
              background-color: #4c704c;
              border-color: #4c704c;
          }
          .btn-submit:hover {
              background-color: #3a5a3a;
              border-color: #3a5a3a;
          }
          /* custom buttons */
        .btn-success{
            background-color: #4f8f1e;
            color: #d8f0c6;
            border: none;
            border-radius: 20px;
            padding: 12px 24px; /* Increased padding */
            margin-right: 1px; /* Set margin between submit button and cancel button*/
            
        }
        .btn-secondary {
            background-color: transparent;
            color: #444444;
            padding: 12px 24px; /* Increased padding */
            border-radius: 20px;
            
        }
        .btn-secondary:not(:active) {
            border: 1px solid #aa5082; /* Add a 2px solid border with color #452235 when not active */
        }
        .btn-secondary:hover {
            background-color: #74425d; /* Change background color on hover */
            color: #d8f0c6;  
        }
        .btn-success:hover{
            background-color: #43771c;
            color: #d8f0c6;
        }
        .btn-success:active {
            background-color: #43771c !important; /* Change the background color to red when the button is active */
        }
        .btn-secondary:active {
            background-color: #74425d !important; /* Change the background color to red when the button is active */
        }
        .btn-success,
        .btn-secondary {
            font-size: 14px; /* Adjust the font size as desired */
            padding: 0.375rem 0.75rem; /* Ensure padding remains unchanged */
            line-height: 1.5; /* Ensure line height remains unchanged */
            margin-top: 20px;
            margin-bottom: -10px;
        }
    </style>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 form-container">
        <h2>Edit Group Violation</h2>
        <form action="multiple_violations.php" method="POST">
            <div class="form-group">
                <label for="student_names">Student Names</label>
                <textarea class="form-control" id="student_names" name="student_names" rows="3" required><?= htmlspecialchars($violation['student_names']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="y_and_s">Year and Section</label>
                <input type="text" class="form-control" id="y_and_s" name="y_and_s" value="<?= htmlspecialchars($violation['y_and_s']) ?>" required>
            </div>
            <div class="form-group">
                <label for="program_related">Program</label>
                <input type="text" class="form-control" id="program_related" name="program_related" value="<?= htmlspecialchars($violation['program_related']) ?>" required>
            </div>
            <div class="form-group">
                <label for="type">Type of Violation</label>
                <select class="form-control" id="type" name="type" required>
                    <option value="Minor" <?= $violation['type'] == 'Minor' ? 'selected' : '' ?>>Minor</option>
                    <option value="Major" <?= $violation['type'] == 'Major' ? 'selected' : '' ?>>Major</option>
                </select>
            </div>
            <div class="form-group">
                <label for="info">Violation Details</label>
                <textarea class="form-control" id="info" name="info" rows="5" required><?= htmlspecialchars($violation['info']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Ongoing" <?= $violation['status'] == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                    <option value="Scheduled" <?= $violation['status'] == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="Completed" <?= $violation['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
                
                <div class="form-group">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">Update Violation</button>
                        <a href="multiple_violations.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </div>
            
        </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
