<?php
session_start();
require 'C:\xampp\htdocs\Oserve\utils\utils.php';  // Include utils for database and activity logging

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';

$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
$counselorsStmt = $pdo->query("SELECT counselors_id, counselors_name FROM counselors");
$counselors = $counselorsStmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['id'])) {
    $counselingId = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM counseling_sessions WHERE counseling_id = :counseling_id");
    $stmt->bindParam(':counseling_id', $counselingId, PDO::PARAM_INT);
    $stmt->execute();
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

 
    if (!$session) {
        $_SESSION['error_message'] = 'Counseling session not found.';
        header('Location: counseling.php');
        exit;
    }
} else {
    $_SESSION['error_message'] = 'No ID provided.';
    header('Location: counseling.php');
    exit;
}
// Fetch distinct year and section combinations from the counseling_sessions table
$yearSectionStmt = $pdo->query("SELECT DISTINCT year_and_section FROM counseling_sessions");
$yearSections = $yearSectionStmt->fetchAll(PDO::FETCH_COLUMN);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE counseling_sessions SET 
    student_full_name = :student_full_name, 
    year_and_section = :year_and_section, 
    phone_number = :phone_number,
    email = :email,
    with_violation = :with_violation, 
    counselors_id = :counselors_id, 
    status = :status,
    remarks = CASE WHEN :status = 'Completed' THEN :remarks ELSE remarks END,
    schedule_time = CASE 
        WHEN :status = 'Scheduled' THEN STR_TO_DATE(:schedule_time, '%Y-%m-%d %h:%i %p')
        ELSE schedule_time 
    END 
    WHERE counseling_id = :counseling_id");


    $updateSuccessful = $stmt->execute([
        ':student_full_name' => $_POST['student_full_name'],
        ':year_and_section' => $_POST['year_and_section'],
        ':phone_number' => $_POST['phone_number'],
        ':email' => $_POST['email'],
        ':with_violation' => isset($_POST['with_violation']) ? 1 : 0,
        ':counselors_id' => $_POST['counselors_id'],
        ':status' => $_POST['status'],
        ':remarks' => ($_POST['status'] === 'Completed') ? $_POST['remarks'] : null,
        ':schedule_time' => ($_POST['status'] === 'Scheduled') ? $_POST['schedule_time'] : null, 
        ':counseling_id' => $counselingId // Binding the counseling_id
    ]);

    if ($updateSuccessful) {
        $_SESSION['success_message'] = 'Counseling session updated successfully!';
        echo "<script>
                alert('Counseling session updated successfully!');
            </script>";
        $actionDescription = "Updated counseling session for " . $_POST['student_full_name'];
        recordActivity($pdo, $_SESSION['user_id'], $actionDescription);  // Log this action in the history
    
        // Update corresponding violation based on counseling session status
        if ($_POST['status'] == 'Completed') {
            // Update violation status to 'Completed'
            $stmt = $pdo->prepare("UPDATE violations SET status = 'Completed' WHERE full_name = ? AND year_and_section = ?");
            $stmt->execute([$_POST['student_full_name'], $_POST['year_and_section']]);
        } elseif ($_POST['status'] == 'Scheduled') {
            // Update violation status to 'Scheduled' (or any other status you prefer)
            $stmt = $pdo->prepare("UPDATE violations SET status = 'Scheduled' WHERE full_name = ? AND year_and_section = ?");
            $stmt->execute([$_POST['student_full_name'], $_POST['year_and_section']]);
        }

        // Redirect to counseling.php after successful update
        header('Location: scheduled_counseling.php'); 
        exit;
    }
}

$stmt = $pdo->prepare("SELECT *, DATE_FORMAT(schedule_time, '%Y-%m-%d %I:%i %p') AS formatted_schedule_time FROM counseling_sessions WHERE counseling_id = :counseling_id");
$stmt->bindParam(':counseling_id', $counselingId, PDO::PARAM_INT);
$stmt->execute();
$session = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<?php
if (isset($_POST['update'])) {
    // Your code to update the session, e.g., SQL query
    $updateSuccess = true; // Assuming update is successful

    if ($updateSuccess) {
        echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    var alertBox = document.getElementById("customAlert");
                    alertBox.style.display = "block";
                    setTimeout(function() {
                        alertBox.style.opacity = 0;
                    }, 3000); // Hide alert after 3 seconds

                    // Redirect after the alert fades out
                    setTimeout(function() {
                        window.location.href = "counseling.php";
                    }, 3500); // Redirect after 3.5 seconds
                });
              </script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Counseling Session</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <link rel="stylesheet" href="css/edit_counseling.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

</head>
    <style>
        .card {
            border: none;
            border-radius: 5px;
            background-color: #ffffff;
            padding: 22px 20px;
            max-width: 1000px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(10, 82, 25, 0.37);
            /* margin-top: -10px; */
            margin-bottom: 30px;
        }
        body {
            background-color: #f4f4f4;
        }
        .form-check-input {
            transform: scale(1.2); /* Makes the checkbox 1.5 times its original size */
        }

        .form-check-label {
            font-size: 1.1rem; /* Increases label font size */
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
    </style>
<body>
    <!-- <div class="container"> -->
        <div class="d-flex justify-content-center">
            <div class="card">
            <div class="card-body">
            <h2 class="card-title">Edit Student</h2>

            <!-- Display Error Message -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error_message']; ?>
                    <?php unset($_SESSION['error_message']); ?> 
                </div>
            <?php endif; ?>

        <!-- Display Success Message -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <!-- <div id="customAlert" class="custom-alert custom-alert-success">
            Session updated successfully!
        </div> -->

        <form action="edit_counseling.php?id=<?= htmlspecialchars($counselingId) ?>" method="POST">
            <div class="form-group">
                <label for="student_full_name">Full Name:</label>
                <input type="text" id="student_full_name" name="student_full_name" value="<?= htmlspecialchars($session['student_full_name']) ?>" readonly>
            </div>

            <div class="form-group">
    <label for="year_and_section">Year and Section</label>
    <select class="form-control form-control-lg" id="year_and_section" name="year_and_section" required>
        <option value="">Select Year and Section</option>
        <option value="1st Year A" <?= $session['year_and_section'] === '1st Year A' ? 'selected' : ''; ?>>1st Year A</option>
        <option value="1st Year B" <?= $session['year_and_section'] === '1st Year B' ? 'selected' : ''; ?>>1st Year B</option>
        <option value="1st Year C" <?= $session['year_and_section'] === '1st Year C' ? 'selected' : ''; ?>>1st Year C</option>
        <option value="1st Year D" <?= $session['year_and_section'] === '1st Year D' ? 'selected' : ''; ?>>1st Year D</option>
        <option value="1st Year E" <?= $session['year_and_section'] === '1st Year E' ? 'selected' : ''; ?>>1st Year E</option>
        <option value="1st Year F" <?= $session['year_and_section'] === '1st Year F' ? 'selected' : ''; ?>>1st Year F</option>
        <option value="1st Year G" <?= $session['year_and_section'] === '1st Year G' ? 'selected' : ''; ?>>1st Year G</option>
        <option value="1st Year H" <?= $session['year_and_section'] === '1st Year H' ? 'selected' : ''; ?>>1st Year H</option>
        <option value="1st Year I" <?= $session['year_and_section'] === '1st Year I' ? 'selected' : ''; ?>>1st Year I</option>

        <option value="2nd Year A" <?= $session['year_and_section'] === '2nd Year A' ? 'selected' : ''; ?>>2nd Year A</option>
        <option value="2nd Year B" <?= $session['year_and_section'] === '2nd Year B' ? 'selected' : ''; ?>>2nd Year B</option>
        <option value="2nd Year C" <?= $session['year_and_section'] === '2nd Year C' ? 'selected' : ''; ?>>2nd Year C</option>
        <option value="2nd Year D" <?= $session['year_and_section'] === '2nd Year D' ? 'selected' : ''; ?>>2nd Year D</option>
        <option value="2nd Year E" <?= $session['year_and_section'] === '2nd Year E' ? 'selected' : ''; ?>>2nd Year E</option>
        <option value="2nd Year F" <?= $session['year_and_section'] === '2nd Year F' ? 'selected' : ''; ?>>2nd Year F</option>
        <option value="2nd Year G" <?= $session['year_and_section'] === '2nd Year G' ? 'selected' : ''; ?>>2nd Year G</option>
        <option value="2nd Year H" <?= $session['year_and_section'] === '2nd Year H' ? 'selected' : ''; ?>>2nd Year H</option>
        <option value="2nd Year I" <?= $session['year_and_section'] === '2nd Year I' ? 'selected' : ''; ?>>2nd Year I</option>

        <option value="3rd Year A" <?= $session['year_and_section'] === '3rd Year A' ? 'selected' : ''; ?>>3rd Year A</option>
        <option value="3rd Year B" <?= $session['year_and_section'] === '3rd Year B' ? 'selected' : ''; ?>>3rd Year B</option>
        <option value="3rd Year C" <?= $session['year_and_section'] === '3rd Year C' ? 'selected' : ''; ?>>3rd Year C</option>
        <option value="3rd Year D" <?= $session['year_and_section'] === '3rd Year D' ? 'selected' : ''; ?>>3rd Year D</option>
        <option value="3rd Year E" <?= $session['year_and_section'] === '3rd Year E' ? 'selected' : ''; ?>>3rd Year E</option>
        <option value="3rd Year F" <?= $session['year_and_section'] === '3rd Year F' ? 'selected' : ''; ?>>3rd Year F</option>
        <option value="3rd Year G" <?= $session['year_and_section'] === '3rd Year G' ? 'selected' : ''; ?>>3rd Year G</option>
        <option value="3rd Year H" <?= $session['year_and_section'] === '3rd Year H' ? 'selected' : ''; ?>>3rd Year H</option>
        <option value="3rd Year I" <?= $session['year_and_section'] === '3rd Year I' ? 'selected' : ''; ?>>3rd Year I</option>

        <option value="4th Year A" <?= $session['year_and_section'] === '4th Year A' ? 'selected' : ''; ?>>4th Year A</option>
        <option value="4th Year B" <?= $session['year_and_section'] === '4th Year B' ? 'selected' : ''; ?>>4th Year B</option>
        <option value="4th Year C" <?= $session['year_and_section'] === '4th Year C' ? 'selected' : ''; ?>>4th Year C</option>
        <option value="4th Year D" <?= $session['year_and_section'] === '4th Year D' ? 'selected' : ''; ?>>4th Year D</option>
        <option value="4th Year E" <?= $session['year_and_section'] === '4th Year E' ? 'selected' : ''; ?>>4th Year E</option>
        <option value="4th Year F" <?= $session['year_and_section'] === '4th Year F' ? 'selected' : ''; ?>>4th Year F</option>
        <option value="4th Year G" <?= $session['year_and_section'] === '4th Year G' ? 'selected' : ''; ?>>4th Year G</option>
        <option value="4th Year H" <?= $session['year_and_section'] === '4th Year H' ? 'selected' : ''; ?>>4th Year H</option>
        <option value="4th Year I" <?= $session['year_and_section'] === '4th Year I' ? 'selected' : ''; ?>>4th Year I</option>
    </select>
</div>

    <!-- Add this after the year_and_section field and before the with_violation checkbox -->
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="phone_number">Phone Number:</label>
            <div class="input-group">
                <span class="input-group-text" style="border-radius: 5px 0 0 5px;">+63</span>
                <input type="tel" class="form-control form-control-lg" id="phone_number" name="phone_number" 
                     value="<?= htmlspecialchars($session['phone_number'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group col-md-6">
            <label for="email">Email Address:</label>
            <input type="email" class="form-control form-control-lg" id="email" name="email" 
                 value="<?= htmlspecialchars($session['email'] ?? ''); ?>" 
                 placeholder="example@email.com">
        </div>
    </div>

    <div class="form-check ml-4">
        <input class="form-check-input" 
             style="margin-left:-40px;" 
             type="checkbox" 
             id="with_violation"
             name="with_violation" 
             value="1"
             <?= ($session['with_violation'] == 1) ? 'checked' : ''; ?>>
        <label class="form-check-label mb-2" 
             style="margin-left:-20px; margin-top:-5px;"  
             for="with_violation">With Violation</label>
    </div>

            <div class="form-group">
                <label for="details">Details:</label>
                <textarea id="details" class="form-control form-control-lg" rows="3" readonly><?= htmlspecialchars($session['details']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="counselor_id">Counselor:</label>
                <select class="form-group form-control-lg" id="counselor_id" name="counselors_id" class="form-control form-control-lg" required>
                    <?php foreach ($counselors as $counselor): ?>
                        <option value="<?= htmlspecialchars($counselor['counselors_id']); ?>" <?= isset($session) && $session['counselors_id'] == $counselor['counselors_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($counselor['counselors_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
    <label for="status">Status:</label>
    <select class="form-group form-control-lg" id="status" name="status" required>
        <option value="">Choose</option>
        <option value="Scheduled" <?= $session['status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
        <option value="Completed" <?= $session['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
    </select>
</div>

<div class="form-group" data-toggle="tooltip" data-placement="top" title="Click to edit"  id="schedule-time-container" style="display: none;">
    <label for="schedule-time">Schedule Time:</label>
    <input type="text" class="form-control form-control-lg" id="schedule-time" name="schedule_time" value="<?= $session['status'] === 'Scheduled' ? htmlspecialchars($session['formatted_schedule_time']) : ''; ?>" readonly>
</div>

<!-- Add this after the status dropdown -->
<div class="form-group" id="remarks-container" style="display: none;">
    <label for="remarks">Remarks:</label>
    <textarea class="form-control form-control-lg" id="remarks" name="remarks" rows="3"><?= htmlspecialchars($session['remarks'] ?? ''); ?></textarea>
</div>


<div class="text-right mt-4"> 
    <a href="scheduled_counseling.php" class="btn btn-outline-secondary">CANCEL</a>
    <button type="submit" class="btn btn-success">UPDATE</button>
</div>

            </form>
            </div>
            </div>
    </div>
<!-- </div> -->
    <script>
$(document).ready(function() {
    // Initialize the date and time picker
    flatpickr("#schedule-time", {
    enableTime: true,
    dateFormat: "Y-m-d h:i K", // Format: 2023-06-01 09:00 AM
    time_24hr: false // Use 12-hour format
});


    // Show/hide the time picker container based on the selected status
    $("#status").change(function() {
        if ($(this).val() === "Scheduled") {
            $("#schedule-time-container").show();
        } else {
            $("#schedule-time-container").hide();
        }
    });

    // Trigger the change event on page load to show/hide the time picker container
    $("#status").trigger("change");
});


// Add this to the script section
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
});

</script>



<script>
$(document).ready(function() {
    // Initialize the date and time picker
   // Initialize flatpickr with the current session date/time
flatpickr("#schedule-time", {
    enableTime: true,
    dateFormat: "Y-m-d h:i K",
    time_24hr: false,
    defaultDate: "<?= !empty($session['formatted_schedule_time']) ? $session['formatted_schedule_time'] : null ?>",
    minDate: "today",
    minuteIncrement: 30,
    onChange: function(selectedDates, dateStr, instance) {
        // Enable the input after a date is selected
        instance.input.removeAttribute('readonly');
    }
});

    // Show/hide the time picker container based on the selected status
    $("#status").change(function() {
        if ($(this).val() === "Scheduled") {
            $("#schedule-time-container").show();
            $("#remarks-container").hide();
        } else if ($(this).val() === "Completed") {
            $("#schedule-time-container").hide();
            $("#remarks-container").show();
        } else {
            $("#schedule-time-container").hide();
            $("#remarks-container").hide();
        }
    });

    // Trigger the change event on page load to show/hide the containers
    $("#status").trigger("change");
    
    // Rest of your existing JavaScript...
});
</script>

</body>
</html>


