<?php
session_start();
require 'dbconfig.php';

// Ensure the user is logged in and has the right role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin', 'staff', 'admin_cs', 'admin_csd', 'admin_pc'])) {
    header('Location: index.php');
    exit();
}

$violationId = $_GET['id'] ?? null;

if ($violationId) {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $stmt = $pdo->prepare("SELECT violations.*, program.program_name FROM violations JOIN program ON violations.program_id = program.program_id WHERE violations.id = :id");
    $stmt->execute([':id' => $violationId]);
    $violationDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$violationDetails) {
        header('Location: violation.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $yearAndSection = $_POST['year_and_section'] ?? '';
    $programId = $_POST['program_id'] ?? 0;
    $phoneNumber = $_POST['phone_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $typeOfViolation = $_POST['type_of_violation'] ?? '';
    
    // Handle multiple statuses for minor violations
    if (isset($_POST['status']) && is_array($_POST['status'])) {
        // Update each violation status individually
        foreach ($_POST['status'] as $violationId => $status) {
            $updateStmt = $pdo->prepare("
                UPDATE violations 
                SET status = :status,
                    cleared_date = CASE WHEN :status = 'Cleared' THEN CURRENT_DATE ELSE NULL END,
                    ongoing_timestamp = CASE WHEN :status = 'Ongoing' THEN NOW() ELSE ongoing_timestamp END,
                    scheduled_timestamp = CASE WHEN :status = 'Scheduled' THEN NOW() ELSE scheduled_timestamp END,
                    completed_timestamp = CASE WHEN :status = 'Completed' THEN NOW() ELSE completed_timestamp END
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':status' => $status,
                ':id' => $violationId
            ]);
        }
    } else {
        // Single status update
        $status = $_POST['status'] ?? '';
        $updateStmt = $pdo->prepare("
            UPDATE violations 
            SET status = :status,
                cleared_date = CASE WHEN :status = 'Cleared' THEN CURRENT_DATE ELSE NULL END,
                ongoing_timestamp = CASE WHEN :status = 'Ongoing' THEN NOW() ELSE ongoing_timestamp END,
                scheduled_timestamp = CASE WHEN :status = 'Scheduled' THEN NOW() ELSE scheduled_timestamp END,
                completed_timestamp = CASE WHEN :status = 'Completed' THEN NOW() ELSE completed_timestamp END
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':status' => $status,
            ':id' => $violationId
        ]);
    }

    // Update common fields only once
    $updateCommonStmt = $pdo->prepare("
        UPDATE violations 
        SET full_name = :full_name,
            year_and_section = :year_and_section,
            program_id = :program_id,
            phone_number = :phone_number,
            email = :email
        WHERE full_name = :original_full_name
    ");
    $updateCommonStmt->execute([
        ':full_name' => $fullName,
        ':year_and_section' => $yearAndSection,
        ':program_id' => $programId,
        ':phone_number' => $phoneNumber,
        ':email' => $email,
        ':original_full_name' => $violationDetails['full_name']
    ]);

    $_SESSION['success_message'] = "Violation updated successfully!";
    header('Location: violation.php');
    exit();
}
// Fetch programs for select dropdown
$programsStmt = $pdo->query("SELECT * FROM program");
$programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($violationDetails['type_of_violation'] === 'minor') {
    $stmt = $pdo->prepare("SELECT id, full_info, status FROM violations 
                          WHERE full_name = :full_name 
                          AND type_of_violation = 'minor'
                          ORDER BY id DESC");
    $stmt->execute([':full_name' => $violationDetails['full_name']]);
    $minorViolations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Violation</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f4;
        }
        .card {
            border: none;
            border-radius: 5px;
            background-color: #ffffff;
            padding: 22px 30px;
            max-width: 900px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(10, 82, 25, 0.37);
            margin-top: -20px;
            margin-bottom: 30px;
        }
        body{
            background-color: #f3f6f4;
        }
        
        h2, label{
            color: #444444;
        }
        h2{
            text-align: center;
            margin-top: -10px;
        }
        label{
            margin-bottom: 5px;
            text-transform: uppercase;
            color: #444444;
            font-weight: 600;
        }
        .card-title {
            text-transform: uppercase;
        }

        .form-control {
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: .375rem .75rem;
        }

        .form-group label {
            color: #495057; /* Dark grey color */
            margin-bottom: .5rem;
        }

        /* Adjust the margin if necessary */
        .form-group .btn {
            margin-right: 0.5rem; /* This adds space between the buttons */
        }

        /* If you need to align the buttons with the form fields */
        .form-group.d-flex {
            align-items: center; /* This aligns the buttons vertically with the form inputs */
        }

        /* Remove background color of card header */
        .card-header {
            background-color: transparent;
            border: none;
        }
    </style>
</head>
<body>
    <!-- ... sidebar and other elements ... -->

    <!-- Main content -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title text-center">Edit Violation</h3>
                    </div>

                    <form action="edit_violation.php?id=<?= $violationId; ?>" method="post">
    <?php if ($violationDetails): ?>
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" class="form-control form-control-lg" id="full_name" name="full_name" value="<?= htmlspecialchars($violationDetails['full_name']); ?>">
        </div>
        
        <!-- Year & Section -->
        <div class="form-group">
            <label for="year_and_section">Year & Section:</label>
            <select class="form-control form-control-lg" id="year_and_section" name="year_and_section" required>
                <option value="">Select Year and Section</option>
                <option value="1st Year A" <?= ($violationDetails['year_and_section'] == '1st Year A') ? 'selected' : ''; ?>>1st Year A</option>
                <option value="1st Year B" <?= ($violationDetails['year_and_section'] == '1st Year B') ? 'selected' : ''; ?>>1st Year B</option>
                <option value="1st Year C" <?= ($violationDetails['year_and_section'] == '1st Year C') ? 'selected' : ''; ?>>1st Year C</option>
                <option value="1st Year D" <?= ($violationDetails['year_and_section'] == '1st Year D') ? 'selected' : ''; ?>>1st Year D</option>
                <option value="1st Year E" <?= ($violationDetails['year_and_section'] == '1st Year E') ? 'selected' : ''; ?>>1st Year E</option>
                <option value="1st Year F" <?= ($violationDetails['year_and_section'] == '1st Year F') ? 'selected' : ''; ?>>1st Year F</option>
                <option value="1st Year G" <?= ($violationDetails['year_and_section'] == '1st Year G') ? 'selected' : ''; ?>>1st Year G</option>
                <option value="1st Year H" <?= ($violationDetails['year_and_section'] == '1st Year H') ? 'selected' : ''; ?>>1st Year H</option>
                <option value="1st Year I" <?= ($violationDetails['year_and_section'] == '1st Year I') ? 'selected' : ''; ?>>1st Year I</option>
                
                <option value="2nd Year A" <?= ($violationDetails['year_and_section'] == '2nd Year A') ? 'selected' : ''; ?>>2nd Year A</option>
                <option value="2nd Year B" <?= ($violationDetails['year_and_section'] == '2nd Year B') ? 'selected' : ''; ?>>2nd Year B</option>
                <option value="2nd Year C" <?= ($violationDetails['year_and_section'] == '2nd Year C') ? 'selected' : ''; ?>>2nd Year C</option>
                <option value="2nd Year D" <?= ($violationDetails['year_and_section'] == '2nd Year D') ? 'selected' : ''; ?>>2nd Year D</option>
                <option value="2nd Year E" <?= ($violationDetails['year_and_section'] == '2nd Year E') ? 'selected' : ''; ?>>2nd Year E</option>
                <option value="2nd Year F" <?= ($violationDetails['year_and_section'] == '2nd Year F') ? 'selected' : ''; ?>>2nd Year F</option>
                <option value="2nd Year G" <?= ($violationDetails['year_and_section'] == '2nd Year G') ? 'selected' : ''; ?>>2nd Year G</option>
                <option value="2nd Year H" <?= ($violationDetails['year_and_section'] == '2nd Year H') ? 'selected' : ''; ?>>2nd Year H</option>
                <option value="2nd Year I" <?= ($violationDetails['year_and_section'] == '2nd Year I') ? 'selected' : ''; ?>>2nd Year I</option>
                
                <option value="3rd Year A" <?= ($violationDetails['year_and_section'] == '3rd Year A') ? 'selected' : ''; ?>>3rd Year A</option>
                <option value="3rd Year B" <?= ($violationDetails['year_and_section'] == '3rd Year B') ? 'selected' : ''; ?>>3rd Year B</option>
                <option value="3rd Year C" <?= ($violationDetails['year_and_section'] == '3rd Year C') ? 'selected' : ''; ?>>3rd Year C</option>
                <option value="3rd Year D" <?= ($violationDetails['year_and_section'] == '3rd Year D') ? 'selected' : ''; ?>>3rd Year D</option>
                <option value="3rd Year E" <?= ($violationDetails['year_and_section'] == '3rd Year E') ? 'selected' : ''; ?>>3rd Year E</option>
                <option value="3rd Year F" <?= ($violationDetails['year_and_section'] == '3rd Year F') ? 'selected' : ''; ?>>3rd Year F</option>
                <option value="3rd Year G" <?= ($violationDetails['year_and_section'] == '3rd Year G') ? 'selected' : ''; ?>>3rd Year G</option>
                <option value="3rd Year H" <?= ($violationDetails['year_and_section'] == '3rd Year H') ? 'selected' : ''; ?>>3rd Year H</option>
                <option value="3rd Year I" <?= ($violationDetails['year_and_section'] == '3rd Year I') ? 'selected' : ''; ?>>3rd Year I</option>
                
                <option value="4th Year A" <?= ($violationDetails['year_and_section'] == '4th Year A') ? 'selected' : ''; ?>>4th Year A</option>
                <option value="4th Year B" <?= ($violationDetails['year_and_section'] == '4th Year B') ? 'selected' : ''; ?>>4th Year B</option>
                <option value="4th Year C" <?= ($violationDetails['year_and_section'] == '4th Year C') ? 'selected' : ''; ?>>4th Year C</option>
                <option value="4th Year D" <?= ($violationDetails['year_and_section'] == '4th Year D') ? 'selected' : ''; ?>>4th Year D</option>
                <option value="4th Year E" <?= ($violationDetails['year_and_section'] == '4th Year E') ? 'selected' : ''; ?>>4th Year E</option>
                <option value="4th Year F" <?= ($violationDetails['year_and_section'] == '4th Year F') ? 'selected' : ''; ?>>4th Year F</option>
                <option value="4th Year G" <?= ($violationDetails['year_and_section'] == '4th Year G') ? 'selected' : ''; ?>>4th Year G</option>
                <option value="4th Year H" <?= ($violationDetails['year_and_section'] == '4th Year H') ? 'selected' : ''; ?>>4th Year H</option>
                <option value="4th Year I" <?= ($violationDetails['year_and_section'] == '4th Year I') ? 'selected' : ''; ?>>4th Year I</option>
            </select>
        </div>

        <!-- Program -->
        <div class="form-group">
            <label for="program_id">Program:</label>
            <select class="form-control form-control-lg" id="program_id" name="program_id">
                <?php foreach ($programs as $program): ?>
                    <option value="<?= $program['program_id']; ?>" <?= $violationDetails['program_id'] == $program['program_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($program['program_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Contact Information -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="phone_number">Phone Number:</label>
                <div class="input-group">
                    <span class="input-group-text" style="border-radius: 5px 0 0 5px;">+63</span>
                    <input type="tel" class="form-control form-control-lg" id="phone_number" name="phone_number" 
                           value="<?= htmlspecialchars($violationDetails['phone_number'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="email">Email Address:</label>
                <input type="email" class="form-control form-control-lg" id="email" name="email" 
                       value="<?= htmlspecialchars($violationDetails['email'] ?? ''); ?>" 
                       placeholder="example@email.com">
            </div>
        </div>

      <!-- Type of Violation -->
      <div class="form-group">
    <?php if ($violationDetails['type_of_violation'] === 'minor' && count($minorViolations) > 1): ?>
        <?php foreach ($minorViolations as $index => $violation): ?>
            <div class="mb-3">
                <label>Violation <?= $index + 1 ?> Details:</label>
                <p class="form-control-static"><?= htmlspecialchars($violation['full_info']) ?></p>
                
                <label for="status_<?= $violation['id'] ?>">Status for Violation <?= $index + 1 ?>:</label>
                <select class="form-control" id="status_<?= $violation['id'] ?>" name="status[<?= $violation['id'] ?>]">
                    <option value="Ongoing" <?= $violation['status'] == 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="Scheduled" <?= $violation['status'] == 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="Completed" <?= $violation['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <label for="status">Status:</label>
        <select class="form-control form-control-lg" id="status" name="status">
            <option value="Ongoing" <?= $violationDetails['status'] == 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
            <option value="Scheduled" <?= $violationDetails['status'] == 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
            <option value="Completed" <?= $violationDetails['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
        </select>
    <?php endif; ?>
</div>

    <!-- buttons -->
    <div class="d-flex justify-content-end mt-5">
        <!-- <a href="violation.php" class="btn btn-outline-secondary mr-1">CANCEL</a> -->
        <a href="javascript:history.back()" class="btn btn-outline-secondary mr-1">CANCEL</a>
        <?php if ($violationDetails): ?>
            <button type="submit" class="btn btn-success">UPDATE</button>
        <?php endif; ?>
    </div>
</form>
<?php endif; ?>

        <!-- ... -->
        </div>
        </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Phone number validation
        $("#phone_number").on('input', function() {
            // Remove all non-numeric characters
            let value = $(this).val().replace(/\D/g, '');
            
            // Limit to 10 digits
            value = value.substring(0, 10);
            
            $(this).val(value);
        });
        
        // Email validation
        $("#email").on('input', function() {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if ($(this).val() && !emailPattern.test($(this).val())) {
                $(this).get(0).setCustomValidity('Please enter a valid email address');
            } else {
                $(this).get(0).setCustomValidity('');
            }
        });
        
        // Name validation
        $("#full_name").on('input', function() {
            // Restrict input to letters, spaces, and some special characters
            let value = $(this).val().replace(/[^A-Za-z ñÑ'.,-]/g, '');
            
            // Capitalize the first letter of each word
            value = value.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
            
            $(this).val(value);
        });
        
        // Status change handler
        $("#status").on('change', function() {
            const status = $(this).val();
            // You can add any specific behavior based on status change here
            // For example, show/hide additional fields
        });
    });
    </script>
</body>
</html>
