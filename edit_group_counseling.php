<?php
session_start();
require 'dbconfig.php';

$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM multiple_counseling_sessions WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $student_names = $_POST['student_names'];
    $year_section = $_POST['year_section'];
    $program = $_POST['program'];
    $violation_type = $_POST['violation_type'];
    $violation_details = $_POST['violation_details'];
    $assigned_team = $_POST['assigned_team'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE multiple_counseling_sessions SET 
        student_names = :student_names,
        year_section = :year_section,
        program = :program,
        violation_type = :violation_type,
        violation_details = :violation_details,
        assigned_team = :assigned_team,
        status = :status
        WHERE id = :id");

    $stmt->execute([
        'student_names' => $student_names,
        'year_section' => $year_section,
        'program' => $program,
        'violation_type' => $violation_type,
        'violation_details' => $violation_details,
        'assigned_team' => $assigned_team,
        'status' => $status,
        'id' => $id
    ]);

    $_SESSION['success_message'] = "Group counseling session updated successfully.";
    header("Location: counseling.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Group Counseling Session</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<style>
    body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .card {
            border: none;
            border-radius: 5px;
            background-color: #ffffff;
            padding: 22px 20px;
            max-width: 1000px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(10, 82, 25, 0.37);
            margin-top: -20px;
            margin-bottom: 30px;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            margin-bottom: 30px;
        }
        h2, label{
            color: #444444;
        }
        h2{
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        .form-group {
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            display: block;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* Ensure padding and border are included in the width */
        }
        /* button custom css */
        .btn-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn-container .btn {
            margin-left: 5px;
        }
        .btn-success {
            background-color: #4f8f1e;
            color: #d8f0c6;
            border: none;
            border-radius: 20px;
            padding: 10px 20px; /* Increased padding */
            font-size: 14px; /* Adjusted font size */
        }
        .btn-secondary {
            background-color: transparent;
            color: #444444;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            border: 1px solid #aa5082;
            padding: 10px 20px; /* Increased padding */
            font-size: 14px; /* Adjusted font size */
        }
        .btn-secondary:hover {
            background-color: #74425d;
            color: #d8f0c6;
        }
        .btn-success:hover {
            background-color: #43771c;
        }

    </style>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-center">
                <div class="card">
                    <div class="card-body">
        <h2>Edit Group Counseling Session</h2>
        <form action="edit_group_counseling.php" method="POST">
            <input type="hidden" name="id" value="<?= $session['id'] ?>">
            <div class="form-group">
                <label for="student_names">Student Names</label>
                <input type="text" class="form-control" id="student_names" name="student_names" value="<?= htmlspecialchars($session['student_names']) ?>" required>
            </div>
            <div class="form-group">
                <label for="year_section">Year & Section</label>
                <input type="text" class="form-control" id="year_section" name="year_section" value="<?= htmlspecialchars($session['year_section']) ?>" required>
            </div>
            <div class="form-group">
                <label for="program">Program</label>
                <input type="text" class="form-control" id="program" name="program" value="<?= htmlspecialchars($session['program']) ?>" required>
            </div>
            <div class="form-group">
                <label for="violation_type">Violation Type</label>
                <input type="text" class="form-control" id="violation_type" name="violation_type" value="<?= htmlspecialchars($session['violation_type']) ?>" required>
            </div>
            <div class="form-group">
                <label for="violation_details">Violation Details</label>
                <textarea class="form-control" id="violation_details" name="violation_details" rows="3" required><?= htmlspecialchars($session['violation_details']) ?></textarea>
            </div>
            <div class="form-group">
    <label for="assigned_team">Assigned Team(s)</label>
    <input type="text" class="form-control" id="assigned_team" name="assigned_team" value="<?= htmlspecialchars($session['assigned_team']) ?>" placeholder="Enter assigned team(s), separated by commas">
    <small class="form-text text-muted"><em>Enter multiple teams separated by commas, e.g., "Program Coordinator, Coordinator for Discipline"</em></small>

</div>

            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Scheduled" <?= $session['status'] == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="Ongoing" <?= $session['status'] == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                    <option value="Completed" <?= $session['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="btn-container">
            <button type="submit" class="btn btn-success">Update</button>
            <a href="multiple_counseling.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
    </div>
</body>
</html>
