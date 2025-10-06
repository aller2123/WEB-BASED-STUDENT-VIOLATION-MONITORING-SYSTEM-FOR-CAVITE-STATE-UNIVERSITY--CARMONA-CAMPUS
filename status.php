<?php
require 'dbconfig.php'; // Include your database connection
session_start();


// Database connection variables
$host = 'localhost';
$dbUsername = 'root'; // MySQL root username
$password = ''; // MySQL root password
$dbname = 'SIMS'; // Database name
$conn = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



// Redirect unauthorized users
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'superadmin' && $_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin_cs' && $_SESSION['role'] != 'admin_csd' && $_SESSION['role'] != 'admin_pc')) {
    header('Location: index.php');
    exit();
}

// Check if the student ID is provided and is not just white spaces
if (!isset($_GET['student_id']) || empty(trim($_GET['student_id']))) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
        $studentId = $_POST['student_id'];
        $req1 = $_POST['req1'] ?? 'No';
        $req2 = $_POST['req2'] ?? 'No';
        $req3 = $_POST['req3'] ?? 'No';
        $clearanceStatus = $_POST['clearanceStatus'] ?? 'Incomplete';
    
        
            $stmt = $conn->prepare("INSERT INTO requirements (student_id,req1,req2,req3,clearance_status) VALUES (:student_id, :req1, :req2, :req3, :clearanceStatus)");
            $stmt->bindParam(':student_id', $studentId);
            $stmt->bindParam(':req1', $req1);
            $stmt->bindParam(':req2', $req2);
            $stmt->bindParam(':req3', $req3);
            $stmt->bindParam(':clearance_status', $clearanceStatus);
            $stmt->execute();

            $_SESSION['success_message'] = "Requirements added successfully.";
            header('Location: requirement.php');
            exit();
        
    } 
         exit();
}

$studentId = trim($_GET['student_id']);
    
// Continue with the rest of your code...


// Database operation to fetch student data
try {
    $sql = "SELECT * FROM students WHERE student_id = :student_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['student_id' => $studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo "Student not found";
        exit();
    }

    $studentName = isset($student['first_name']) ? $student['first_name'] . ' ' . $student['surname'] : 'N/A';
    $studentNo = $student['student_no'] ?? 'N/A';

    $reqSql = "SELECT req1, req2, req3, clearance_status FROM requirements WHERE student_id = :student_id";
    $reqStmt = $pdo->prepare($reqSql);
    $reqStmt->execute(['student_id' => $studentId]);
    $requirements = $reqStmt->fetch(PDO::FETCH_ASSOC);

    $req1 = $requirements['req1'] ?? 'No';
    $req2 = $requirements['req2'] ?? 'No';
    $req3 = $requirements['req3'] ?? 'No';
    $clearanceStatus = $requirements['clearance_status'] ?? 'Incomplete';
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}


// Handle the form submission for updating requirements
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $studentId = $_POST['student_id'];
    $req1 = $_POST['req1'] ?? 'No';
    $req2 = $_POST['req2'] ?? 'No';
    $req3 = $_POST['req3'] ?? 'No';
    $clearanceStatus = $_POST['clearanceStatus'] ?? 'Incomplete';

    try {
        $updateSql = "UPDATE requirements SET req1 = :req1, req2 = :req2, req3 = :req3, clearance_status = :clearance_status WHERE student_id = :student_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            ':req1' => $req1,
            ':req2' => $req2,
            ':req3' => $req3,
            ':clearance_status' => $clearanceStatus,
            ':student_id' => $studentId
        ]);

        if ($updateStmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Requirements updated successfully.";
            header('Location: requirements.php'); // Redirect to requirements.php
            exit();
        } else {
            $_SESSION['error_message'] = "No changes were made.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Your head elements, make sure to include Bootstrap if you're using it for styling -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Student Clearance Status</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
</head>
    <style>
        /* button custom css */
        .btn-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            margin-right: -15px;
            margin-bottom: -25px;
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
            padding: 10px 15px; /* Increased padding */
            font-size: 14px; /* Adjusted font size */
        }
        .btn-success:hover {
            background-color: #43771c;
        }
        .btn-secondary:hover {
            background-color: #74425d;
            color: #d8f0c6;
        }
        .container {
            max-width: 1600px;
            width: 100%;
            margin: 50px auto;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .card-body {
            padding: 10px;
        }
        .card-header {
            background-color: transparent;
            border: none;
        }
        body {
            background-color: #f4f4f4;
        }
        .card {
            background-color: #ffffff;
            padding: 30px;
            margin-bottom: 40px;
            max-width: 1200px;
            margin-top: -10px;
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(10, 82, 25, 0.37);
        }
    </style>
<body>
<div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
            <div class="card">
            <div class="card-header">
                <h3 class="card-title text-center">Student Clearances Status</h3>
            </div>
            <div class="card-body">
            <form action="update_requirement_status.php" method="post">

                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($studentId) ?>">
                    <input type="hidden" name="requirement_id" value="<?= htmlspecialchars($requirement_id) ?>">

                    <div class="mb-3">
                        <label for="studentName" class="form-label">Name:</label>
                        <input type="text" class="form-control" id="studentName" value="<?= htmlspecialchars($studentName) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="studentNo" class="form-label">Student No.:</label>
                        <input type="text" class="form-control" id="studentNo" value="<?= htmlspecialchars($student['student_no']) ?>" readonly>
                    </div>

                    <label for="requirement1" class="form-label">Requirement 1: Certificate of Grade</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="req1" id="req1Yes" value="Yes" <?= $req1 == 'Yes' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="req1Yes">
                            Yes
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="req1" id="req1No" value="No" <?= $req1 == 'No' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="req1No">
                            No
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="requirement2" class="form-label">Requirement 2: Student Information Form/admission form</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="req2" id="req2Yes" value="Yes" <?= $req2 == 'Yes' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="req2Yes">
                                Yes
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="req2" id="req2No" value="No" <?= $req2 == 'No' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="req2No">
                                No
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="requirement3" class="form-label">Requirement 3: Good Moral Certificate</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="req3" id="req3Yes" value="Yes" <?= $req3 == 'Yes' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="req3Yes">
                                Yes
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="req3" id="req3No" value="No" <?= $req3 == 'No' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="req3No">
                                No
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="clearanceStatus" class="form-label">Clearance Status:</label>
                        <select class="form-select" id="clearance_status" name="clearance_status">
                            <option value="Incomplete" <?= $clearanceStatus == 'Incomplete' ? 'selected' : '' ?>>Incomplete</option>
                            <option value="Complete" <?= $clearanceStatus == 'Complete' ? 'selected' : '' ?>>Complete</option>
                        </select>

                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">Save</button>
                        <a href="requirements.php" class="btn btn-secondary ml-2" data-bs-dismiss="modal">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Make sure to include Bootstrap JS and its dependencies if you're using Bootstrap components -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const req1Radios = document.querySelectorAll('input[name="req1"]');
    const req2Radios = document.querySelectorAll('input[name="req2"]');
    const req3Radios = document.querySelectorAll('input[name="req3"]');
    const clearanceStatus = document.getElementById('clearance_status');

    function updateClearanceStatus() {
        const allYes = 
            document.querySelector('input[name="req1"]:checked').value === 'Yes' &&
            document.querySelector('input[name="req2"]:checked').value === 'Yes' &&
            document.querySelector('input[name="req3"]:checked').value === 'Yes';

        clearanceStatus.value = allYes ? 'Complete' : 'Incomplete';
    }

    req1Radios.forEach(radio => radio.addEventListener('change', updateClearanceStatus));
    req2Radios.forEach(radio => radio.addEventListener('change', updateClearanceStatus));
    req3Radios.forEach(radio => radio.addEventListener('change', updateClearanceStatus));

    // Initial check
    updateClearanceStatus();


    
});
</script>

</body>
</html>

