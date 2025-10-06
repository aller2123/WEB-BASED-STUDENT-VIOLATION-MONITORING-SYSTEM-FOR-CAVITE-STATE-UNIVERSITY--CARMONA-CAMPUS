<?php
session_start();
require 'C:\xampp\htdocs\Oserve\utils\utils.php'; // Ensure this path is correct

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Fetch counselors data
$counselorsStmt = $pdo->query("SELECT counselors_id, counselors_name FROM counselors");
$counselors = $counselorsStmt->fetchAll(PDO::FETCH_ASSOC);

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $studentFullName = $_POST['student_full_name'] ?? '';
    $year = $_POST['year'] ?? '';
    $section = $_POST['section'] ?? '';
    $withViolation = isset($_POST['with_violation']) ? 1 : 0;
    $counselorsId = $_POST['counselors_id'] ?? null;
    $status = $_POST['status'] ?? '';
    $details = $_POST['details'] ?? '';

    // SQL query to insert data into the database
    $sql = "INSERT INTO counseling_sessions (student_full_name, year, section, with_violation, counselors_id, status, details) 
            VALUES (:student_full_name, :year, :section, :with_violation, :counselors_id, :status, :details)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':student_full_name' => $studentFullName,
        ':year' => $year,
        ':section' => $section,
        ':with_violation' => $withViolation,
        ':counselors_id' => $counselorsId,
        ':status' => $status,
        ':details' => $details
    ]);

    // Check if insertion was successful
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = 'Counseling schedule added successfully!';
        $username = $_SESSION['username']; // Assuming username is stored in session
        $actionDescription = "User {$username} added a new counseling schedule for {$studentFullName}";
        recordActivity($pdo, $_SESSION['user_id'], $actionDescription);
    } else {
        $_SESSION['error_message'] = 'Failed to add the counseling schedule.';
    }

    // Redirect back to counseling.php
    header('Location: counseling.php');
    exit;
}


// After successfully adding a new counseling session
$_SESSION['success_message'] = 'New counseling session added successfully!';
header('Location: counseling.php');
exit;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Counseling Schedule</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            background-color: #ffffff;
            padding: 20px;
            margin-top: 20px;
            max-width: 500px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(10, 82, 25, 0.37);
        }

        /* Custom button styles */
        .btn-success {
            background-color: #4f8f1e;
            color: #d8f0c6;
            border: none;
            border-radius: 20px;
            margin-right: 1px; /* Set margin between submit button and cancel button to 5px */
        }

        .btn-secondary {
            background-color: transparent;
            color: #444444;
            border: none;
            border-radius: 20px;
        }

        .btn-secondary:not(:active) {
            border: 1px solid #aa5082; /* Add a 2px solid border with color #452235 when not active */
        }

        .btn-secondary:hover {
            background-color: #74425d; /* Change background color on hover */
            color: #d8f0c6;
        }

        .btn-success:hover {
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

        h2, label {
            color: #444444;
        }

        label {
            margin-bottom: 5px;
        }

        .form-check-label {
            margin-top: -10px;
        }
        body {
            background-color: #f4f4f4;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="header-title text-center">Add Counseling Schedule</h2>
        <form action="add_counseling.php" method="POST">
            <div class="form-row" style="padding-top:20px;">
                <div class="form-group col-md-6">
                    <label for="studentFullName">Full Name</label>
                    <input type="text" class="form-control" id="studentFullName" name="student_full_name" placeholder="Full Name" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="year">Year</label>
                    <input type="number" class="form-control" id="year" name="year" placeholder="Year" required>
                </div>
            </div>

            <div class="form-group">
                <label for="section">Section</label>
                <input type="text" class="form-control" id="section" name="section" placeholder="Section" required>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="withViolation" name="with_violation">
                    <label class="form-check-label" for="withViolation">With Violation</label>
                </div>
            </div>

            <div class="form-group">
                <label for="details">Details</label>
                <input type="text" class="form-control" id="details" name="details" placeholder="Details" required>
            </div>

            <div class="form-group">
                <label for="counselorId">Counselor Name</label>
                <select class="form-control" id="counselorId" name="counselors_id" required>
                    <?php foreach ($counselors as $counselor): ?>
                        <option value="<?= htmlspecialchars($counselor['counselors_id']); ?>">
                            <?= htmlspecialchars($counselor['counselors_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>

            <div class="form-group row">
                <div class="col-md-6 offset-md-6 text-right">
                    <!-- Submit Button -->
                    <button id="submitBtn" type="submit" class="btn btn-success" style="padding: 0.5rem 1rem;">Add Schedule</button>
                    <!-- Cancel Button -->
                    <button type="button" class="btn btn-secondary" onclick="location.href='counseling.php'">Cancel</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
