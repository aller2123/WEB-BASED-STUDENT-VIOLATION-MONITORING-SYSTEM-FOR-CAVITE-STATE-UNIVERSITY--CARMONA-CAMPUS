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

// Make sure we have an ID
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Make sure we still have the ID
    if (!isset($_POST['counseling_id']) && isset($_GET['id'])) {
        $counselingId = $_GET['id'];
    } else if (isset($_POST['counseling_id'])) {
        $counselingId = $_POST['counseling_id'];
    } else {
        $_SESSION['error_message'] = 'No counseling ID provided for update.';
        header('Location: counseling.php');
        exit;
    }
    $stmt = $pdo->prepare("UPDATE counseling_sessions SET 
    student_full_name = :student_full_name, 
    year_and_section = :year_and_section, 
    phone_number = :phone_number,
    email = :email,
    with_violation = :with_violation, 
    counselors_id = :counselors_id, 
    status = :status,
    schedule_time = CASE WHEN :status = 'Scheduled' THEN :schedule_time ELSE schedule_time END 
    WHERE counseling_id = :counseling_id");

    $updateSuccessful = $stmt->execute([
        ':student_full_name' => $_POST['student_full_name'],
        ':year_and_section' => $_POST['year_and_section'],
        ':phone_number' => $_POST['phone_number'],
        ':email' => $_POST['email'],
        ':with_violation' => isset($_POST['with_violation']) ? 1 : 0,
        ':counselors_id' => $_POST['counselors_id'],
        ':status' => $_POST['status'],
        ':schedule_time' => ($_POST['status'] === 'Scheduled') ? $_POST['schedule_time'] : null, 
        ':counseling_id' => $counselingId
    ]);

    if ($updateSuccessful) {
        // Check if notification should be sent
        if (isset($_POST['send_notification']) && $_POST['send_notification'] == 1) {
            $_SESSION['success_message'] = 'Counseling session updated successfully and notification sent!';
            $actionDescription = "Updated counseling session for " . $_POST['student_full_name'] . " and sent email notification";
        } else {
            $_SESSION['success_message'] = 'Counseling session updated successfully!';
            $actionDescription = "Updated counseling session for " . $_POST['student_full_name'];
        }
        
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
        header('Location: counseling.php'); 
        exit;
    } else {
        $_SESSION['error_message'] = 'Failed to update counseling session.';
    }
}
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

      $_SESSION['success_message'] = 'Counseling session updated successfully!';
    // Log the activity
    recordActivity($pdo, $_SESSION['user_id'], $actionDescription);
    
    // Redirect with success message
    header('Location: counseling.php');
    exit();
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
    <!-- Make sure these are included before your custom scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>
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

        <form action="edit_pending.php?id=<?= htmlspecialchars($counselingId) ?>" method="POST">
            <input type="hidden" name="counseling_id" value="<?= htmlspecialchars($counselingId) ?>">
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


            <div class="form-check ml-4">
            <input class="form-check-input" style="margin-left:-40px;" type="checkbox" for="flexCheckChecked" name="with_violation" <?= $session['with_violation'] ? 'checked' : ''; ?> disabled>
                <label class="form-check-label mb-2" style="margin-left:-20px; margin-top:-5px;"  for="flexCheckChecked">With Violation</label>
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
    <input class="form-check-input" style="margin-left:-40px;" type="checkbox" for="flexCheckChecked" name="with_violation" <?= $session['with_violation'] ? 'checked' : ''; ?> disabled>
    <label class="form-check-label mb-2" style="margin-left:-20px; margin-top:-5px;"  for="flexCheckChecked">With Violation</label>
</div>

            <div class="form-group">
    <label for="status">Status:</label>
    <select class="form-group form-control-lg" id="status" name="status" required>
        <option value="">Choose</option>
        <option value="Scheduled" <?= $session['status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
    </select>
</div>

<div class="form-group" id="schedule-time-container">
    <label for="schedule-time">Schedule Time:</label>
    <input type="text" class="form-control form-control-lg" id="schedule-time" name="schedule_time" 
           value="<?= isset($session['schedule_time']) ? date('Y-m-d h:i A', strtotime($session['schedule_time'])) : ''; ?>">
</div>


                <div class="text-right mt-4"> 
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">CANCEL</a>
                    <button type="button" id="updateBtn" class="btn btn-success">UPDATE</button>
                </div>
            </form>
            </div>
            </div>
    </div>
<!-- </div> -->
    <script>
$(document).ready(function() {
    // Initialize flatpickr with specific settings
    flatpickr("#schedule-time", {
        enableTime: true,
        dateFormat: "Y-m-d h:i K",
        minDate: "today",
        time_24hr: false,
        minuteIncrement: 30,
        onChange: function(selectedDates, dateStr) {
            validateTimeSlot(dateStr);
        }
    });
    function validateTimeSlot(selectedTime) {
        const counselorId = $("#counselor_id").val();
        const sessionId = <?= json_encode($counselingId) ?>;

        $.ajax({
            type: "POST",
            url: "check_timeslot.php",
            data: {
                counselor_id: counselorId,
                schedule_time: selectedTime,
                session_id: sessionId
            },
            dataType: "json",
            success: function(response) {
                if (!response.available) {
                    alert("This time slot is already booked for the selected counselor. Please choose another time.");
                    $("#schedule-time").val('');
                }
            }
        });
    }

    // Show/hide schedule time based on status
    $("#status").change(function() {
        if ($(this).val() === "Scheduled") {
            $("#schedule-time-container").show();
        } else {
            $("#schedule-time-container").hide();
            $("#schedule-time").val('');
        }
    }).trigger('change');

    // Phone number validation
    $("#phone_number").on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
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

    // Handle the update button click
    $("#updateBtn").click(function(e) {
        e.preventDefault();
        
        // Check if form is valid before showing modal
        if ($('form')[0].checkValidity()) {
            // Show the notification confirmation modal
            $("#notificationConfirmationModal").modal('show');
        } else {
            // Trigger form validation
            $('form')[0].reportValidity();
        }
    });
    
    // Handle the "No, Skip" button click - Update without notification
    $("#skipNotificationBtn").click(function() {
        // Close the modal
        $("#notificationConfirmationModal").modal('hide');
        
        // Submit the form data via AJAX
        submitFormData(false);
    });
    
    // Handle the "Yes, Send Notification" button click
    $("#sendNotificationBtn").click(function() {
        // Close the first modal
        $("#notificationConfirmationModal").modal('hide');
        
        // Get values from the main form
        var studentName = $("#student_full_name").val();
        var studentEmail = $("#email").val();
        var scheduleTime = $("#schedule-time").val();
        
        // Populate the notification details form
        $("#notification-email").val(studentEmail);
        $("#notification-name").val(studentName);
        $("#notification-schedule").val(scheduleTime);
        
        // Generate a default message
        var defaultMessage = "Dear " + studentName + ",\n\n";
        defaultMessage += "Your counseling session has been scheduled for " + scheduleTime + ".\n";
        defaultMessage += "Please be on time.\n\n";
        defaultMessage += "Regards,\nCounseling Office";
        
        $("#notification-message").val(defaultMessage);
        
        // Show the notification details modal
        setTimeout(function() {
            $("#notificationDetailsModal").modal('show');
        }, 500);
    });
    
    // Handle the final confirmation to send notification
    $("#confirmSendNotificationBtn").click(function() {
        // Get the notification details
        var notificationData = {
            email: $("#notification-email").val(),
            name: $("#notification-name").val(),
            schedule: $("#notification-schedule").val(),
            subject: $("#notification-subject").val(),
            message: $("#notification-message").val()
        };
        
        // Validate email
        if (!notificationData.email) {
            $("#emailAlert").removeClass("alert-success").addClass("alert-danger").text("Email address is required").show();
            return;
        }
        
        // Show loading state
        $("#confirmSendNotificationBtn").prop("disabled", true).text("Sending...");
        
        // Send email via AJAX
        $.ajax({
            type: "POST",
            url: "send_email.php",
            data: notificationData,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    // Email sent successfully
                    $("#emailAlert").removeClass("alert-danger").addClass("alert-success").text("Email sent successfully!").show();
                    
                    // Close the modal after a short delay and submit the form data
                    setTimeout(function() {
                        $("#notificationDetailsModal").modal('hide');
                        // Submit form data with notification flag
                        submitFormData(true);
                    }, 1500);
                } else {
                    // Email sending failed
                    $("#emailAlert").removeClass("alert-success").addClass("alert-danger").text("Failed to send email: " + response.message).show();
                    $("#confirmSendNotificationBtn").prop("disabled", false).text("Send Notification");
                }
            },
            error: function() {
                // AJAX request failed
                $("#emailAlert").removeClass("alert-success").addClass("alert-danger").text("Failed to send email. Please try again.").show();
                $("#confirmSendNotificationBtn").prop("disabled", false).text("Send Notification");
            }
        });
    });
    
    // Function to submit form data via AJAX
    function submitFormData(notificationSent) {
        // Get all form data
        var formData = {
            counseling_id: <?= json_encode($counselingId) ?>,
            student_full_name: $("#student_full_name").val(),
            year_and_section: $("#year_and_section").val(),
            phone_number: $("#phone_number").val(),
            email: $("#email").val(),
            with_violation: $("#with_violation").is(":checked") ? 1 : 0,
            counselors_id: $("#counselor_id").val(),
            status: $("#status").val(),
            schedule_time: $("#status").val() === 'Scheduled' ? $("#schedule-time").val() : '',
            send_notification: notificationSent ? 1 : 0
        };
        
        // Send AJAX request to update the counseling session
        $.ajax({
            type: "POST",
            url: "update_counseling_ajax.php", // Create this file to handle the update
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    // Update successful
                    var successMsg = notificationSent ? 
                        "Counseling session updated successfully and notification sent!" : 
                        "Counseling session updated successfully!";
                    
                    $("#successMessage").text(successMsg);
                    $("#successChoicesModal").modal('show');
                } else {
                    // Update failed
                    alert("Failed to update counseling session: " + response.message);
                }
            },
            error: function() {
                // AJAX request failed
                alert("An error occurred while updating the counseling session. Please try again.");
            }
        });
    }
    
    // Handle the "Stay on Page" button click
    $("#stayOnPageBtn").click(function() {
        $("#successChoicesModal").modal('hide');
        // Reload the current page to refresh the data
        location.reload();
    });
    
    // Handle the "Go to Listing" button click
    $("#goToListingBtn").click(function() {
        window.location.href = "counseling.php";
    });
});


$(document).ready(function() {
    // Add custom alert HTML
    $(`<div class="alert alert-warning alert-dismissible fade" role="alert" id="timeSlotAlert" style="display:none">
        <strong>Time Slot Unavailable!</strong> This schedule is already booked. Please select another time.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>`).insertBefore('form');

    // Initialize flatpickr with specific settings
    flatpickr("#schedule-time", {
    enableTime: true,
    dateFormat: "Y-m-d H:i", // Change from "Y-m-d h:i K" to "Y-m-d H:i"
    minDate: "today",
    time_24hr: true, // Set to true for 24-hour format
    minuteIncrement: 30,
    onChange: function(selectedDates, dateStr) {
        validateTimeSlot(dateStr);
    }
});

    function validateTimeSlot(selectedTime) {
        const counselorId = $("#counselor_id").val();
        const sessionId = <?= json_encode($counselingId) ?>;

        $.ajax({
            type: "POST",
            url: "check_timeslot.php",
            data: {
                counselor_id: counselorId,
                schedule_time: selectedTime,
                session_id: sessionId
            },
            dataType: "json",
            success: function(response) {
                if (!response.available) {
                    $("#timeSlotAlert").fadeIn().addClass('show');
                    setTimeout(() => {
                        $("#timeSlotAlert").fadeOut().removeClass('show');
                    }, 3000);
                    $("#schedule-time").val('');
                }
            },
            error: function() {
                console.log("Time slot check completed");
            }
        });
    }

    // Show/hide schedule time based on status
    $("#status").change(function() {
        if ($(this).val() === "Scheduled") {
            $("#schedule-time-container").show();
        } else {
            $("#schedule-time-container").hide();
            $("#schedule-time").val('');
        }
    }).trigger('change');
});


</script>

<!-- Notification Confirmation Modal -->
<div class="modal fade" id="notificationConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="notificationConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="notificationConfirmationModalLabel">Send Notification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        Would you like to send a notification to the student about this scheduled counseling session?
        <!-- Add hidden input to store the counseling ID -->
        <input type="hidden" id="modal-counseling-id" value="<?= htmlspecialchars($counselingId) ?>">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" id="skipNotificationBtn">No, Skip</button>
        <button type="button" class="btn btn-success" id="sendNotificationBtn">Yes, please</button>
      </div>
    </div>
  </div>
</div>

<!-- Notification Details Modal -->
<div class="modal fade" id="notificationDetailsModal" tabindex="-1" role="dialog" aria-labelledby="notificationDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="notificationDetailsModalLabel">Notification Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="emailAlert" class="alert" style="display: none;"></div>
        <form id="notificationDetailsForm">
          <!-- Hidden field for counseling ID -->
          <input type="hidden" id="notification-counseling-id" name="counseling_id" value="<?= htmlspecialchars($counselingId) ?>">
          
          <div class="form-group">
            <label for="notification-email">Email:</label>
            <input type="email" class="form-control" id="notification-email" name="notification_email">
          </div>
          <div class="form-group">
            <label for="notification-name">Student Name:</label>
            <input type="text" class="form-control" id="notification-name" name="notification_name" readonly>
          </div>
          <div class="form-group">
            <label for="notification-schedule">Schedule:</label>
            <input type="text" class="form-control" id="notification-schedule" name="notification_schedule" readonly>
          </div>
          <div class="form-group">
            <label for="notification-subject">Subject:</label>
            <input type="text" class="form-control" id="notification-subject" name="notification_subject" value="Counseling Session Scheduled">
          </div>
          <div class="form-group">
            <label for="notification-message">Message:</label>
            <textarea class="form-control" id="notification-message" name="notification_message" rows="5"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="confirmSendNotificationBtn">Send Notification</button>
      </div>
    </div>
  </div>
</div>

<!-- Success Modal with Choices -->
<div class="modal fade" id="successChoicesModal" tabindex="-1" role="dialog" aria-labelledby="successChoicesModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="successChoicesModalLabel">Update Successful</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="successMessage">Counseling session has been updated successfully!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="goToListingBtn">Go to Counseling List</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>


