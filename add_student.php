<?php
require 'dbconfig.php'; // Include your database connection

session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin', 'staff', 'admin_cs', 'admin_csd', 'admin_pc'])) {
    header('Location: index.php');
    exit();
}

try {
    $dsn = "mysql:host=$host;dbname=$database";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch programs from the database
    $programQuery = "SELECT * FROM program";
    $stmt = $pdo->prepare($programQuery);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Could not connect to the database $database :" . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_no = $_POST['student_no'];
    $first_name = $_POST['first_name'];
    $surname = $_POST['surname'];
    $middle_name = isset($_POST['no_middle_name']) ? 'N/A' : $_POST['middle_name'];
    $gender = strtoupper($_POST['gender']);
    $program_id = $_POST['program_id'];
    $year_level = $_POST['year_level'];
    $birthdate = $_POST['birthdate'];
    $phone_number = $_POST['phone_number'];
    $email = !empty($_POST['email']) ? $_POST['email'] : null; // Add email field
    $status = $_POST['status'];

    // Check if the student number is empty
    if (empty($student_no)) {
        $_SESSION['error_message'] = "Please fill out the Student Number field.";
        header('Location: add_student.php');
        exit();
    }

    // Check if the student number already exists
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_no = :student_no");
    $checkStmt->execute([':student_no' => $student_no]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        // Student number already exists, set an error message
        $_SESSION['error_message'] = "This student number is already taken.";
        header('Location: add_student.php');
        exit();
    } else {
        // Student number is unique, proceed with insertion

        // Check if middle name is empty when checkbox is not checked
        if (!isset($_POST['no_middle_name']) && empty($middle_name)) {
            $_SESSION['error_message'] = "Please fill out the Middle Name field or check the 'I don't have a middle name' box.";
            header('Location: add_student.php');
            exit();
        }

        try {
            // Update the SQL query to include email
            $sql = "INSERT INTO students (student_no, first_name, surname, middle_name, gender, program_id, year_level, birthdate, phone_number, email, status)
                    VALUES (:student_no, :first_name, :surname, :middle_name, :gender, :program_id, :year_level, :birthdate, :phone_number, :email, :status)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':student_no' => $student_no,
                ':first_name' => $first_name,
                ':surname' => $surname,
                ':middle_name' => $middle_name,
                ':gender' => $gender,
                ':program_id' => $program_id,
                ':year_level' => $year_level,
                ':birthdate' => $birthdate,
                ':phone_number' => $phone_number,
                ':email' => $email, // Add email parameter
                ':status' => $status
            ]);

            // Get the last inserted student ID
            $student_id = $pdo->lastInsertId();

            // Generate QR code
            $qrData = "Student No: $student_no\nName: $first_name $surname";
            $qrFilename = 'qrcodes/' . $student_id . '.png';

            // Check if the student status is 'Active'
            if ($status === 'Active') {
                // Insert a new row into the requirements table for the new student
                $requirementsStmt = $pdo->prepare("INSERT INTO requirements (student_id, req1, req2, req3, clearance_status) VALUES (:student_id, '', '', '', 'Active')");
                $requirementsStmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
                $requirementsStmt->execute();
            }

            // Set success message
            $_SESSION['success_message'] = 'Added a student successfully!';
        } catch (PDOException $e) {
            // Set an error message if something goes wrong during insertion
            $_SESSION['error_message'] = 'Error adding student: ' . $e->getMessage();
        }

        // Redirect to the student list page
        header('Location: student.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>

        /* Add custom styles here if needed */
        .card {
            border: none;
            border-radius: 5px;
            background-color: #ffffff;
            padding: 22px 20px;
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
        /* input.form-control, select.form-control {
        text-transform: uppercase;
    } */

    </style>  
<body>
<div class="container mt-5">
<div class="container mt-5">
    <div class="d-flex justify-content-center">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Add Student</h2>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>
<form action="add_student.php" method="POST" id="studentForm">
    <div class="form-row" style="padding-top: 20px;">
        <div class="form-group col-md-6">
            <label for="student_no">Student No.</label>
            <input type="text" class="form-control form-control-lg" id="student_no" name="student_no" required>
        </div>
        <div class="form-group col-md-6">
            <label for="gender">Sex</label>
            <select class="form-control form-control-lg" id="gender" name="gender" style="text-transform: uppercase;" required>
                <option value="Female">Female</option>
                <option value="Male">Male</option>
                <option value="Other">Other</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="surname">Surname</label>
        <input type="text" class="uppercase form-control form-control-lg" id="surname" name="surname" pattern="[A-Za-z ñ' IIVX]+" required title="Please enter only letters and spaces">
    </div>
    <div class="form-group">
        <label for="first_name">First Name</label>
        <input type="text" class="form-control form-control-lg" id="first_name" name="first_name" pattern="[A-Za-z ]+" required title="Please enter only letters and spaces">
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="middle_name">Middle Name</label>
            <input type="text" class="form-control form-control-lg" id="middle_name" name="middle_name" pattern="[A-Za-z ]+" title="Please enter only letters and spaces">
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="no_middle_name" name="no_middle_name">
                <label class="form-check-label" for="no_middle_name">
                    I don't have a middle name
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label for="program_id">Program</label>
            <select class="form-control form-control-lg" id="program_id" name="program_id" required>
                <?php foreach ($programs as $program): ?>
                    <option value="<?= $program['program_id']; ?>"><?= htmlspecialchars($program['program_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-row">
    <div class="form-group col-md-6">
    <label for="year_level">Year Level</label>
    <select class="form-control form-control-lg" id="year_level" name="year_level" required>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
    </select>
</div>
        <div class="form-group col-md-6">
            <label for="birthdate">Birthdate</label>
            <input type="date" class="form-control form-control-lg" id="birthdate" name="birthdate" required>
        </div>
    </div>
    <div class="form-row">
    <div class="form-group col-md-6">
        <label for="phone_number">Phone Number</label>
        <div class="input-group">
            <span class="input-group-text" style="border-radius: 5px 0 0 5px;">+63</span>
            <input type="tel" class="form-control form-control-lg" id="phone_number" name="phone_number" 
                   maxlength="10" 
                   oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                   pattern="[0-9]{10}" 
                   title="Please enter a valid 10-digit phone number" 
                   >
        </div>
    </div>

    <!-- Add email field here -->
    <div class="form-group col-md-6">
        <label for="email">Email Address</label>
        <input type="email" class="form-control form-control-lg" id="email" name="email" 
            placeholder="cc.firstname.lastname@cvsu.edu.ph">
        <small class="form-text text-muted">Leave blank if no email address is available.</small>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="status">Status</label>
        <select class="form-control form-control-lg" id="status" name="status" required>
            <option value="" disabled selected>Choose...</option>
            <option value="Enrolled">Enrolled</option>
            <option value="Graduate">Graduate</option>
            <option value="Not Enrolled">Not Enrolled</option>
        </select>
    </div>
</div>

    <div class="row justify-content-end mt-2">
        <div class="col-md-6 text-right">
            <button type="submit" class="btn btn-success">SUBMIT</button>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">BACK</a>
        </div>
    </div>
</form>
        </div>
    </div>
</div>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var middleNameInput = document.getElementById('middle_name');
    var noMiddleNameCheckbox = document.getElementById('no_middle_name');
    var studentForm = document.getElementById('studentForm');
    var ageInput = document.getElementById('age');

    noMiddleNameCheckbox.addEventListener('change', function() {
        if (this.checked) {
            middleNameInput.value = '';
            middleNameInput.disabled = true;
            middleNameInput.required = false;
        } else {
            middleNameInput.disabled = false;
            middleNameInput.required = true;
        }
    });

    studentForm.addEventListener('submit', function(e) {
        if (!noMiddleNameCheckbox.checked && middleNameInput.value.trim() === '') {
            e.preventDefault();
            alert('Please fill out the Middle Name field or check the "I don\'t have a middle name" box.');
        }
    });

    document.getElementById('student_no').addEventListener('input', function(e) {
    let value = this.value.replace(/[^0-9]/g, ''); // Allow only numeric values
    if (value.length > 9) {
        value = value.slice(0, 9); // Limit the input to a maximum of 9 digits
    }
    this.value = value;
    // Check if there are exactly 9 digits
    if (value.length !== 9) {
        this.setCustomValidity('Student number must have exactly 9 digits.');
    } else {
        this.setCustomValidity('');
    }
});

    // Restrict age input to two digits and positive numbers
    ageInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
        if (this.value < 1) this.value = '';
        if (this.value > 99) this.value = '99';
    });

    // Restrict name inputs to letters and spaces only
    ['surname', 'first_name', 'middle_name'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', function() {
        // Restrict input to letters, spaces, ñ, and Roman numerals
        let value = this.value.replace(/[^A-Za-z ñ' IIVX]+/g, '');

        // Capitalize the first letter of each word, but avoid capitalizing after 'ñ'
        value = value.replace(/\b\w/g, function(char, index, fullString) {
            // Check if the previous character is "ñ"
            if (index > 0 && fullString[index - 1].toLowerCase() === 'ñ') {
                return char.toLowerCase(); // Don't capitalize after ñ
            }
            return char.toUpperCase(); // Capitalize normally
        });

        // Ensure the letter after an apostrophe is lowercase
        value = value.replace(/'(\w)/g, function(match, char) {
            return "'" + char.toLowerCase();
        });

        // Add a dot if the surname has "Sr" or "Jr"
        if (id === 'surname') {
            if (value.includes('Sr')) {
                value = value.replace('Sr', 'Sr.');
            } else if (value.includes('Jr')) {
                value = value.replace('Jr', 'Jr.');
            }
        }

        this.value = value;
    });
});
});




document.getElementById('phone_number').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
});


document.addEventListener('DOMContentLoaded', function() {
    // Convert input to uppercase while typing
    const inputFields = document.querySelectorAll('input[type="text"]');
    inputFields.forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    // Convert select options to uppercase
    const selectFields = document.querySelectorAll('select');
    selectFields.forEach(function(select) {
        Array.from(select.options).forEach(function(option) {
            option.text = option.text.toUpperCase();
        });
    });

    // Ensure form submission data is uppercase
    document.getElementById('studentForm').addEventListener('submit', function() {
        inputFields.forEach(function(input) {
            input.value = input.value.toUpperCase();
        });
    });
});

const inputFields = document.querySelectorAll('input[type="text"]');
    inputFields.forEach(function(input) {
        // Skip the email field
        if (input.id !== 'email') {
            input.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }
    });

    const selectFields = document.querySelectorAll('select');
    selectFields.forEach(function(select) {
        Array.from(select.options).forEach(function(option) {
            option.text = option.text.toUpperCase();
        });
    });


    document.getElementById('studentForm').addEventListener('submit', function() {
        inputFields.forEach(function(input) {
            // Skip the email field
            if (input.id !== 'email') {
                input.value = input.value.toUpperCase();
            }
        });
    });

    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            this.value = this.value.toLowerCase();
        });
    }
    
</script>
</body>
</html>