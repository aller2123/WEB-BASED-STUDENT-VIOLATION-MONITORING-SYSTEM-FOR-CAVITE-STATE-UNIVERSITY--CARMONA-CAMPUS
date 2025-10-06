<?php
require 'C:\xampp\htdocs\Oserve\utils\utils.php';
session_start();

require 'dbconfig.php';

$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
    exit('Student ID is required.');
}

$pdo = getPDO();

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :student_id");
$stmt->execute([':student_id' => $student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$programsQuery = "SELECT program_id, program_name FROM program";
$programsStmt = $pdo->query($programsQuery);
$programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);

// Check for success or error messages in session
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;

// Clear the messages from the session
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Student</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <style>
        body {
            /* font-family: Arial, sans-serif; */
            background-color: #f3f6f4;
            /* margin: 0;
            padding: 0; */
        }
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
        

        .popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 20px;
            transform: translateX(-50%);
            padding: 15px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
        }
        .popup.success {
            background-color: #28a745;
        }
        .popup.error {
            background-color: #dc3545;
        }
        
        .form-control-plaintext {
            display: block;
            width: 100%;
            padding: 0.375rem 0;
            margin-bottom: 0;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: transparent;
            border: solid transparent;
            border-width: 1px 0;
        }

    </style>
</head>
<body>
<div id="popupMessage" class="popup"></div>

<div class="container mt-5">
    <div class="d-flex justify-content-center">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Edit Student</h2>
                <form action="update_student_handler.php" method="POST" style="padding: -10px;">
                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['student_id']); ?>">
    
                    <div class="form-row" style="padding-top: 20px; display: flex;">
                        <div class="form-group col-md-6" style="flex: 1; margin-right: 5px;">
                            <label for="student_no">Student No.</label>
                            <input type="text" class="form-control form-control-lg" id="student_no" name="student_no" value="<?= htmlspecialchars($student['student_no']); ?>" class="form-control-plaintext">
                        </div>
                        <div class="form-group col-md-6" style="flex: 1; margin-left: 5px;">
                            <label for="gender">Sex</label>
                            <select class="form-control form-control-lg" id="gender" name="gender" required>
                                <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : ''; ?>>FEMALE</option>
                                <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : ''; ?>>MALE</option>
                                <option value="Other" <?= $student['gender'] === 'Other' ? 'selected' : ''; ?>>OTHER</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="surname">Surname</label>
                        <input class="form-control form-control-lg" type="text" id="surname" name="surname" value="<?= htmlspecialchars($student['surname']); ?>" class="form-control-plaintext">
                    </div>

                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input class="form-control form-control-lg" type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($student['first_name']); ?>" class="form-control-plaintext">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="middle_name">Middle Name</label>
                            <input class="form-control form-control-lg" type="text" class="form-control" id="middle_name" name="middle_name" value="<?= htmlspecialchars($student['middle_name']); ?>" pattern="[A-Za-z /]+" title="Please enter only letters and spaces">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="program_id">Program</label>
                            <select class="form-control form-control-lg" id="program_id" name="program_id" required>
                                <?php foreach ($programs as $program): ?>
                                    <option value="<?= $program['program_id']; ?>" <?= $student['program_id'] == $program['program_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($program['program_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="year_level">Year Level</label>
                            <select class="form-control form-control-lg" id="year_level" name="year_level" required>
                                <option value="1" <?= $student['year_level'] === '1' ? 'selected' : ''; ?>>1</option>
                                <option value="2" <?= $student['year_level'] === '2' ? 'selected' : ''; ?>>2</option>
                                <option value="3" <?= $student['year_level'] === '3' ? 'selected' : ''; ?>>3</option>
                                <option value="4" <?= $student['year_level'] === '4' ? 'selected' : ''; ?>>4</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="birthdate">Birthdate</label>
                            <input class="form-control form-control-lg" type="date" class="form-control" id="birthdate" name="birthdate" value="<?= htmlspecialchars($student['birthdate']); ?>" required>
                        </div>
                    </div>
                      <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="phone_number">Phone Number</label>
                              <div class="input-group">
                                  <span class="input-group-text" style="border-radius: 5px 0 0 5px;">+63</span>
                                  <input type="tel" class="form-control form-control-lg" id="phone_number" name="phone_number" 
                                  value="<?= htmlspecialchars($student['phone_number']); ?>"
                                  >
                              </div>
                          </div>
                          <div class="form-group col-md-6">
                              <label for="email">Email Address</label>
                              <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                   value="<?= htmlspecialchars($student['email'] ?? ''); ?>" 
                                   placeholder="cc.firstname.lastname@cvsu.edu.ph">
                          </div>
                      </div>

                      <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="status">Status</label>
                              <select class="form-control form-control-lg" id="status" name="status" required>
                                  <option value="" disabled>Choose...</option>
                                  <option value="Enrolled" <?= $student['status'] === 'Enrolled' ? 'selected' : ''; ?>>Enrolled</option>
                                  <option value="Not Enrolled" <?= $student['status'] === 'Not Enrolled' ? 'selected' : ''; ?>>Not Enrolled</option>
                                  <option value="Graduate" <?= $student['status'] === 'Graduate' ? 'selected' : ''; ?>>Graduate</option>
                              </select>
                          </div>
                      </div>
                    <div class="row justify-content-end mt-3">
                        <div class="col-md-6 text-right">
                            <a href="javascript:history.back()" class="btn btn-outline-secondary">BACK</a>
                            <button type="submit" class="btn btn-success">SUBMIT</button>
                        </div>
                    </div>
                </form>
    </div>
</div>

<script>
function showPopup(message, isSuccess) {
    const popup = document.getElementById('popupMessage');
    popup.textContent = message;
    popup.className = isSuccess ? 'popup success' : 'popup error';
    popup.style.display = 'block';
    setTimeout(() => {
        popup.style.display = 'none';
    }, 3000);
}


document.addEventListener('DOMContentLoaded', function() {
    var ageInput = document.getElementById('age');

    ageInput.addEventListener('input', function() {
        // Remove any non-digit characters
        this.value = this.value.replace(/\D/g, '');
        
        // Ensure the value is between 1 and 99
        var age = parseInt(this.value, 10);
        if (age < 1) this.value = '';
        if (age > 99) this.value = '99';
        
        // Limit to two digits
        if (this.value.length > 2) {
            this.value = this.value.slice(0, 2);
        }
    });
});

document.getElementById('student_no').addEventListener('input', function(e) {
    // Restrict input to digits only
    let value = this.value.replace(/[^0-9]/g, '');

    // Automatically add a dash after 4 digits
    if (value.length > 4) {
        value = value.slice(0, 4) + '-' + value.slice(4, 9); // Limit to 5 digits after the dash
    }

    // Set the value back to the input
    this.value = value;
});

document.querySelector('form').addEventListener('submit', function() {
        var middleNameInput = document.getElementById('middle_name');
        if (!middleNameInput.value.trim()) {
            middleNameInput.value = 'N/A';
        }
    });
    
    ['surname', 'first_name', 'middle_name'].forEach(function(id) { 
    const inputField = document.getElementById(id);
    
    // Event listener for real-time input modifications
    inputField.addEventListener('input', function() {
        // Restrict input to letters, spaces, ñ, ', /, and Roman numerals
        let value = this.value.replace(/[^A-Za-z ñ'\/ IIVX]+/g, '');

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
    
    // Event listener for when the user leaves the input field
    inputField.addEventListener('blur', function() {
        if (id === 'middle_name' && this.value.trim() === '') {
            this.value = 'N/A';
        }
    });
});


document.getElementById('phone_number').addEventListener('input', function(e) {
    // Remove all non-numeric characters
    let value = this.value.replace(/\D/g, '');
    
    // Limit to 10 digits
    value = value.substring(0, 10);
    
    this.value = value;
});


// Add this to the script section
document.getElementById('email').addEventListener('input', function() {
    // Basic email validation
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (this.value && !emailPattern.test(this.value)) {
        this.setCustomValidity('Please enter a valid email address');
    } else {
        this.setCustomValidity('');
    }
});

<?php if ($successMessage): ?>
showPopup(<?= json_encode($successMessage) ?>, true);
<?php elseif ($errorMessage): ?>
showPopup(<?= json_encode($errorMessage) ?>, false);
<?php endif; ?>
</script>

</body>
</html>







