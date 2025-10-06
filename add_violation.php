<?php
require 'dbconfig.php';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $stmt = $pdo->query("SELECT student_no FROM students WHERE status = 'enrolled'");
    $studentNumbers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    // Fetch violation types from the database, excluding 'minor' and 'major'
    $violationTypesStmt = $pdo->prepare("SELECT violation_type FROM typeofviolation WHERE violation_type NOT IN ('minor', 'major')");
    $violationTypesStmt->execute();
    $violationTypes = $violationTypesStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Could not connect to the database $database :" . $e->getMessage());
}

session_start();



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $full_name = $_POST['surname'] . ' ' . $_POST['firstname'] . ' ' . $_POST['middle_name'];
    $year_and_section = $_POST['year_and_section'];
    $program_id = $_POST['program_id'];
    $type_of_violation = $_POST['violation_type'];
    $full_info = '';
    $case_offense = $_POST['case_offense'];
    $offense_count = $_POST['selected_violations'];

    if ($type_of_violation === 'minor_other') {
        $type_of_violation = 'Other'; // Set to 'Other' if 'Others' is selected
        $full_info = $_POST['other_violation'];
    } else {
        $full_info = isset($_POST['violation_details']) ? implode(', ', $_POST['violation_details']) : '';
    }
    $counselor_id = isset($_POST['counselor']) ? $_POST['counselor'] : null; // Get the selected counselor ID

    // Start a transaction
    $pdo->beginTransaction();
// In fetch_student_info.php
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_no = :student_number AND status = 'enrolled'");
    try {
      // Prepare SQL and bind parameters for violations table
// Update the SQL query to include reported_by
$stmt = $pdo->prepare("INSERT INTO violations (full_name, year_and_section, program_id, type_of_violation, full_info, offense_count, action_perform, student_no, phone_number, email, reported_by) 
VALUES (:full_name, :year_and_section, :program_id, :type_of_violation, :full_info, :offense_count, :action_perform, :student_no, :phone_number, :email, :reported_by)");

// Make sure all parameters are bound in the same order
$stmt->bindParam(':full_name', $full_name);
$stmt->bindParam(':year_and_section', $year_and_section);
$stmt->bindParam(':program_id', $program_id);
$stmt->bindParam(':type_of_violation', $type_of_violation);
$stmt->bindParam(':full_info', $full_info);
$stmt->bindParam(':offense_count', $offense_count);
$stmt->bindParam(':action_perform', $_POST['action_perform']);
$stmt->bindParam(':student_no', $_POST['student_number']);
$stmt->bindParam(':phone_number', $_POST['phone_number']);
$stmt->bindParam(':email', $_POST['email']);
$stmt->bindParam(':reported_by', $_POST['reported_by']);

        // Execute the prepared statement for violations table
        $stmt->execute();

        // Check if the violation type is "major" or if the student has two or more minor violations
    // Check if the violation type is "major" or if the student has more than two minor violations
    function countDuplicateOffenses($pdo, $fullName, $offenseNumber) {
        $searchPattern = '%' . $offenseNumber . '%';
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM violations WHERE full_name = :full_name AND offense_count LIKE :offense_number");
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':offense_number', $searchPattern);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    // In your main code where counseling decision is made
    $shouldCreateCounselingSession = false;
    if ($type_of_violation === 'major') {
        $shouldCreateCounselingSession = true;
    } else {
        // Get the selected offense numbers
        $offenseNumbers = explode(',', $_POST['selected_violations']);
        
        // Check each offense number for duplicates
        foreach ($offenseNumbers as $number) {
            $number = trim($number);
            if (countDuplicateOffenses($pdo, $full_name, $number) >= 2) {
                $shouldCreateCounselingSession = true;
                break;
            }
        }
    }
    

        if ($shouldCreateCounselingSession) {
            // First, check if the student already has a counseling session
            $checkExistingSession = $pdo->prepare("SELECT counseling_id FROM counseling_sessions WHERE student_full_name = :full_name AND year_and_section = :year_and_section");
            $checkExistingSession->bindParam(':full_name', $full_name);
            $checkExistingSession->bindParam(':year_and_section', $year_and_section);
            $checkExistingSession->execute();
            $existingSessionId = $checkExistingSession->fetchColumn();

            // Fetch all violation details for the student
            $violationDetailsStmt = $pdo->prepare("SELECT full_info FROM violations WHERE full_name = :full_name AND year_and_section = :year_and_section");
            $violationDetailsStmt->bindParam(':full_name', $full_name);
            $violationDetailsStmt->bindParam(':year_and_section', $year_and_section);
            $violationDetailsStmt->execute();
            $violationDetails = $violationDetailsStmt->fetchAll(PDO::FETCH_COLUMN);

            // Combine all violation details into a single string
            $combinedViolationDetails = implode(', ', $violationDetails);

            if ($existingSessionId) {
                // Update existing counseling session with new violation details
                $updateSessionStmt = $pdo->prepare("UPDATE counseling_sessions SET details = :details, with_violation = 1 WHERE counseling_id = :counseling_id");
                $updateSessionStmt->bindParam(':details', $combinedViolationDetails);
                $updateSessionStmt->bindParam(':counseling_id', $existingSessionId);
                $updateSessionStmt->execute();
            } else if ($shouldCreateCounselingSession) {
                // Create new counseling session
                $assigned_to = isset($_POST['assign_to']) ? implode(', ', $_POST['assign_to']) : '';
                $counselingStmt = $pdo->prepare("INSERT INTO counseling_sessions (student_full_name, year_and_section, with_violation, details, counselors_id, assigned_to, phone_number, email) VALUES (:student_full_name, :year_and_section, :with_violation, :details, :counselors_id, :assigned_to, :phone_number, :email)");
                $counselingStmt->bindParam(':student_full_name', $full_name);
                $counselingStmt->bindParam(':year_and_section', $year_and_section);
                $counselingStmt->bindValue(':with_violation', 1, PDO::PARAM_BOOL);
                $counselingStmt->bindParam(':details', $combinedViolationDetails);
                $counselingStmt->bindParam(':counselors_id', $counselor_id, PDO::PARAM_INT);
                $counselingStmt->bindParam(':assigned_to', $assigned_to);
                $counselingStmt->bindParam(':phone_number', $_POST['phone_number']);
                $counselingStmt->bindParam(':email', $_POST['email']);
                $counselingStmt->execute();
            }
        }

        // Commit the transaction
        $pdo->commit();
        $_SESSION['success_message'] = "Violation added successfully.";
    } catch (PDOException $e) {
        // Roll back the transaction on error
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error adding violation: " . $e->getMessage();
    }


    $assigned_to = isset($_POST['assign_to']) ? $_POST['assign_to'] : [];
    $valid_assignments = [];

    foreach ($assigned_to as $assignment) {
        if ($_SESSION['role'] == 'superadmin' ||
           ($_SESSION['role'] == 'admin_pc' && $assignment == 'program_coordinator') ||
           ($_SESSION['role'] == 'admin_csd' && $assignment == 'coordinator_discipline') ||
           ($_SESSION['role'] == 'admin_cs' && $assignment == 'coordinator_welfare')) {
            $valid_assignments[] = $assignment;
        }
    }

    $assigned_to = implode(', ', $valid_assignments);
    // Redirect to the violation page
    header('Location: violation.php');
    exit();
}

// Function to count the number of minor violations for a student
function countMinorViolations($pdo, $fullName, $yearAndSection)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM violations WHERE full_name = :full_name AND year_and_section = :year_and_section AND type_of_violation = 'minor'");
    $stmt->bindParam(':full_name', $fullName);
    $stmt->bindParam(':year_and_section', $yearAndSection);
    $stmt->execute();
    return $stmt->fetchColumn();
}

$offense_count = isset($_POST['selected_violations']) ? $_POST['selected_violations'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <title>Add STUDENT</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<style>
    /* Add custom styles here if needed */
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
            /* font-weight: 600; */
        }
        .card-title {
            text-transform: uppercase;
        }
        #violation_checkboxes input[type="checkbox"] {
            transform: scale(1.5); /* Increase the scale to enlarge the checkbox */
            margin-top: 8px; /* Optional: Add spacing between checkbox and label */
        }
        .one, .two, .three, .four, .five, .six, .seven, .eight, .nine {
            text-transform: uppercase;
            color: #444444;
            font-weight: 600;
        }
        
        
</style>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
            <div class="card">
                
                    <h3 class="card-title text-center mb-4">ADD STUDENT</h3>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                <div class="form-row" style="padding-top: 20px;">
                    <div class="one form-group col-md-6">
                        <label for="student_number">Student No.</label>
                        <input 
                            type="text" 
                            class="form-control form-control-lg" 
                            id="student_number" 
                            name="student_number" 
                            list="student_numbers" 
                            placeholder="Type or select a student number" 
                            required 
                            maxlength="9" 
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)">
                        <datalist id="student_numbers">
                            <?php
                            sort($studentNumbers);
                            foreach ($studentNumbers as $studentNumber): ?>
                                <option value="<?= htmlspecialchars($studentNumber); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="two form-group col-md-6">
                        <label for="program_name">Program</label>
                        <input 
                            type="text" 
                            class="form-control form-control-lg" 
                            id="program_name" 
                            name="program_name" 
                            readonly>
                        <input 
                            type="hidden" 
                            id="program_id" 
                            name="program_id" 
                            value="">
                    </div>
                </div>

                      <div class="form-row">
                          <div class="three form-group col-md-4">
                              <label for="surname">Surname</label>
                              <input type="text" class="form-control form-control-lg" id="surname" name="surname" readonly>
                          </div>
                          <div class="four form-group col-md-4">
                              <label for="firstname">First Name</label>
                              <input type="text" class="form-control form-control-lg" id="firstname" name="firstname" readonly>
                          </div>
                          <div class="five form-group col-md-4">
                              <label for="middle_name">Middle name</label>
                              <input type="text" class="form-control form-control-lg" id="middle_name" name="middle_name" readonly>
                          </div>
                      </div>

                      <div class="form-row">
    <div class="form-group col-md-6">
        <label for="phone_number" style="font-weight:600;">PHONE NUMBER</label>
        <input type="tel" class="form-control form-control-lg" id="phone_number" name="phone_number" readonly>
    </div>
    <div class="form-group col-md-6">
        <label for="email" style="font-weight:600;">EMAIL</label>
        <input type="email" class="form-control form-control-lg" id="email" name="email" readonly>
    </div>
</div>


                    

                    <div class="form-row">
                        <div class="six form-group col-md-6">
                            <label for="year_and_section">Year and Section</label>
                            <select 
                                class="form-control form-control-lg" 
                                id="year_and_section" 
                                name="year_and_section" 
                                required>
                                <option value="">Select Year and Section</option>
                                <option value="1st Year A">1st Year A</option>
                                <option value="1st Year B">1st Year B</option>
                                <option value="1st Year C">1st Year C</option>
                                <option value="1st Year D">1st Year D</option>
                                <option value="1st Year E">1st Year E</option>
                                <option value="1st Year F">1st Year F</option>
                                <option value="1st Year G">1st Year G</option>
                                <option value="1st Year H">1st Year H</option>
                                <option value="1st Year I">1st Year I</option>
                                <option value="2nd Year A">2nd Year A</option>
                                <option value="2nd Year B">2nd Year B</option>
                                <option value="2nd Year C">2nd Year C</option>
                                <option value="2nd Year D">2nd Year D</option>
                                <option value="2nd Year E">2nd Year E</option>
                                <option value="2nd Year F">2nd Year F</option>
                                <option value="2nd Year G">2nd Year G</option>
                                <option value="2nd Year H">2nd Year H</option>
                                <option value="2nd Year I">2nd Year I</option>
                                <option value="3rd Year A">3rd Year A</option>
                                <option value="3rd Year B">3rd Year B</option>
                                <option value="3rd Year C">3rd Year C</option>
                                <option value="3rd Year D">3rd Year D</option>
                                <option value="3rd Year E">3rd Year E</option>
                                <option value="3rd Year F">3rd Year F</option>
                                <option value="3rd Year G">3rd Year G</option>
                                <option value="3rd Year H">3rd Year H</option>
                                <option value="3rd Year I">3rd Year I</option>
                                <option value="4th Year A">4th Year A</option>
                                <option value="4th Year B">4th Year B</option>
                                <option value="4th Year C">4th Year C</option>
                                <option value="4th Year D">4th Year D</option>
                                <option value="4th Year E">4th Year E</option>
                                <option value="4th Year F">4th Year F</option>
                                <option value="4th Year G">4th Year G</option>
                                <option value="4th Year H">4th Year H</option>
                                <option value="4th Year I">4th Year I</option>
                            </select>
                        </div>
                        <div class="seven form-group col-md-6">
                            <label for="violation_type">Type of Violation</label>
                            <select 
                                class="form-control form-control-lg" 
                                id="violation_type" 
                                name="violation_type" 
                                required>
                                <option value="">Select Type of Violation</option>
                                <option value="minor">Minor</option>
                                <option value="major">Major</option>
                                <!-- <option value="minor_other">Others</option> -->
                                <?php foreach ($violationTypes as $violationType): ?>
                                    <option value="<?= $violationType; ?>"><?= $violationType; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
 
                    <div class="form-group mt-3 mb-4">
    <label for="reported_by" style="font-weight:600;">REPORTED BY</label>
    <input type="text" 
           class="form-control form-control-lg" 
           id="reported_by" 
           name="reported_by" 
           placeholder="Enter name of person reporting the violation"
           required>
</div>

                    <div class="form-group" style=" margin-top:4px; border: 1px solid rgb(202, 202, 202); padding: 8px; border-radius: 5px; max-height: 200px; overflow-y: auto;" id="violation_checkboxes">
                        <!-- Violation details checkboxes will be populated dynamically -->
                    </div>
                    
                    
                    
                    <div class="d-flex justify-content-end" style="margin-top:20px;">
                        <a href="violation.php" class="btn btn-outline-secondary mr-2">CANCEL</a>
                        <button type="submit" class="btn btn-success">SUBMIT</button>
                    </div>
                </form>
                
            </div>

            </div>
            
        </div>
    </div>


    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function validateForm() {
            let isValid = true;
            const requiredFields = ['student_number', 'surname', 'firstname', 'year_and_section', 'violation_type'];

            requiredFields.forEach(field => {
                const fieldElement = document.getElementById(field);
                if (fieldElement.value.trim() === '') {
                    isValid = false;
                    fieldElement.classList.add('is-invalid');
                } else {
                    fieldElement.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                alert('Please fill in all required fields.');
            }

            return isValid;
        }
        $(document).ready(function() {
    // Fetch violation types from the server
    $.ajax({
        type: "GET",
        url: "fetch_violation_types.php",
        dataType: "json",
        success: function(violationTypesData) {
            // Populate the violation details checkboxes
            const violationCheckboxes = $('#violation_checkboxes');
            violationCheckboxes.empty();
            
            // Add a text box to display selected violation numbers
            violationCheckboxes.before(`
    <div class="form-group mt-3 mb-4">
        <label for="action_perform" style="font-weight:600;">ACTION PERFORM</label>
        <textarea
            class="form-control form-control-lg"
            id="action_perform"
            name="action_perform"
            rows="3"
            placeholder="Enter the actions taken for this violation"
            required></textarea>
    </div>
    
    <div class="form-group mt-3 mb-4">
        <label for="selected_violations" style="font-weight:600;">OFFENSE NUMBER</label>
        <input type="text" class="form-control form-control-lg" id="selected_violations" 
               name="selected_violations" readonly 
               placeholder="Numbers of selected violations will appear here">
    </div>
`);
            
            // Initialize a counter for continuous numbering
            let counter = 1;
            
            violationTypesData.forEach(function(violationType) {
                const description = violationType.description;
                let violationList = [description];
                let numberedCheckboxes = '';
                
                for(let i = 0; i < violationList.length; i++) {
                    numberedCheckboxes += `
                        <div class="form-check">
                            <input class="form-check-input violation-checkbox" type="checkbox" 
                                   name="violation_details[]" 
                                   value="${violationList[i].trim()}"
                                   data-violation-number="${counter}">
                            <label class="form-check-label">${counter}. ${violationList[i].trim()}</label>
                        </div>
                    `;
                    counter++; // Increment the counter for each item
                }
                
                violationCheckboxes.append(`
                    <div class="violation-type-checkboxes" data-violation-type="${violationType.violation_type}" style="display: none;">
                        ${numberedCheckboxes}
                    </div>
                `);
            });

            // Add event listener for checkbox changes to update the text box
            $(document).on('change', '.violation-checkbox', updateSelectedViolations);
   // In the JavaScript section that handles checkbox selection:
function updateSelectedViolations() {
    const selectedCheckboxes = $('.violation-checkbox:checked');
    let selectedNumbers = [];
    
    selectedCheckboxes.each(function() {
        selectedNumbers.push($(this).data('violation-number'));
    });
    
    // Sort the numbers numerically
    selectedNumbers.sort(function(a, b) {
        return a - b;
    });
    
    // Update the text box with selected violation numbers
    $('#selected_violations').val(selectedNumbers.join(', '));
    
    // Add a hidden field with the total count
    if ($('#total_offense_count').length === 0) {
        $('<input>').attr({
            type: 'hidden',
            id: 'total_offense_count',
            name: 'total_offense_count',
            value: selectedNumbers.length
        }).insertAfter('#selected_violations');
    } else {
        $('#total_offense_count').val(selectedNumbers.length);
    }
}

            // Violation type change handling
            $('#violation_type').change(function() {
                const violationType = $(this).val();
                $('.violation-type-checkboxes').hide();
                $(`.violation-type-checkboxes[data-violation-type="${violationType}"]`).show();

                if (violationType === 'minor_other') {
                    $('#other_violation_container').show();
                    $('#violation_checkboxes').hide();
                    $('#selected_violations').closest('.form-group').hide();
                } else {
                    $('#other_violation_container').hide();
                    $('#violation_checkboxes').show();
                    $('#selected_violations').closest('.form-group').show();
                }
                
                // Clear the selected violations text box when changing violation type
                $('#selected_violations').val('');
                $('.violation-checkbox').prop('checked', false);
            });
        },
        error: function(xhr, status, error) {
            console.log("Error:", error);
        }
    });
});
        
$('#violation_type').change(function() {
    const violationType = $(this).val();
    $('.violation-type-checkboxes').hide();
    $(`.violation-type-checkboxes[data-violation-type="${violationType}"]`).show();

    // Remove existing options
    $('#counselor-options').remove();
    $('#assign-options').remove();

    if (violationType === 'major') {
        $.ajax({
            type: "GET",
            url: "fetch_counselors.php",
            dataType: "json",
            success: function(counselors) {
                const assignOptions = `
                    <div id="assign-options">
                        <div class="form-group">
                            <label class="nine">Assign to :</label>
                            ${getAssignmentOptions()}
                        </div>
                    </div>
                    <div id="counselor-options" class="eight form-group mt-3">
                        <label for="counselor">Select a Counselor :</label>
                        <select class="form-control form-control-lg" id="counselor" name="counselor" required>
                            <option value="">Select a counselor</option>
                            ${counselors.map(counselor => `<option value="${counselor.id}">${counselor.name}</option>`).join('')}
                        </select>
                    </div>
                `;

                // Insert before the submit buttons
                $('.d-flex.justify-content-end').before(assignOptions);

                // Limit checkbox selection
                $('.assign-checkbox').on('change', function() {
                    if($('.assign-checkbox:checked').length > 2) {
                        this.checked = false;
                    }
                });
            }
        });
    }
});

function getAssignmentOptions() {
    let options = '';
    <?php if ($_SESSION['role'] == 'superadmin' || $_SESSION['role'] == 'admin_pc' || $_SESSION['role'] == 'staff'): ?>
        options += `
            <div class="form-check">
                <input class="form-check-input assign-checkbox" type="checkbox" name="assign_to[]" id="assign_program_coordinator" value="program_coordinator">
                <label class="form-check-label" for="assign_program_coordinator">
                    Program Coordinator BSCS Campus Inspector
                </label>
            </div>
        `;
    <?php endif; ?>

    <?php if ($_SESSION['role'] == 'superadmin' || $_SESSION['role'] == 'admin_csd' || $_SESSION['role'] == 'staff'): ?>
        options += `
            <div class="form-check">
                <input class="form-check-input assign-checkbox" type="checkbox" name="assign_to[]" id="assign_coordinator_discipline" value="coordinator_discipline">
                <label class="form-check-label" for="assign_coordinator_discipline">
                    Program Coordinator Student and Discipline
                </label>
            </div>
        `;
    <?php endif; ?>
    return options;
}

       
            $('#violation_type').change(function() {
                const violationType = $(this).val();
                
                // Hide all violation type checkboxes initially
                $('.violation-type-checkboxes').hide();
                
                // Show and style the selected violation type checkboxes
                $(`.violation-type-checkboxes[data-violation-type="${violationType}"]`)
                    .css('font-size', '1.2rem') // Make the font size larger
                    .show();
            });


            $('#student_number').change(function() {
    var selectedValue = $(this).val();
    if (selectedValue !== '') {
        $.ajax({
            type: "POST",
            url: "fetch_student_info.php",
            data: { student_number: selectedValue },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    console.log("Error:", response.error);
                } else {
                    $("#surname").val(response.surname);
                    $("#firstname").val(response.first_name);
                    $("#middle_name").val(response.middle_name);
                    $("#program_id").val(response.program_id);
                    $("#program_name").val(response.program_name);
                    $("#phone_number").val(response.phone_number);
                }
            }
        });
    }
});

// Keep the input handler as well
$('#student_number').change(function() {
    var selectedValue = $(this).val();
    if (selectedValue !== '') {
        $.ajax({
            type: "POST",
            url: "fetch_student_info.php",
            data: { student_number: selectedValue },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    console.log("Error:", response.error);
                } else {
                    $("#surname").val(response.surname);
                    $("#firstname").val(response.first_name);
                    $("#middle_name").val(response.middle_name);
                    $("#program_id").val(response.program_id);
                    $("#program_name").val(response.program_name);
                    $("#phone_number").val(response.phone_number);
                    $("#email").val(response.email); // Add this line to populate email
                }
            }
        });
    }
});

// Also update the input handler
$('#student_number').on('input', function() {
    var input = $(this).val();
    if(input) {
        $.ajax({
            url: 'fetch_student_info.php',
            method: 'POST',
            data: { student_number: input },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    console.log("Error:", response.error);
                } else {
                    $("#surname").val(response.surname);
                    $("#firstname").val(response.first_name);
                    $("#middle_name").val(response.middle_name);
                    $("#program_id").val(response.program_id);
                    $("#program_name").val(response.program_name);
                    $("#phone_number").val(response.phone_number);
                    $("#email").val(response.email); // Add this line to populate email
                }
            }
        });
    }
});


    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

