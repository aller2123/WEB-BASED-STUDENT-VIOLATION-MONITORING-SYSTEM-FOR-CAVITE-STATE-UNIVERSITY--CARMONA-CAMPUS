<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

if (!isset($_SESSION['role'])) {
    header('Location: index.php');
    exit();
}


$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'No username set';

$sql = "SELECT cs.*, c.counselors_name, cs.schedule_time
FROM counseling_sessions AS cs
LEFT JOIN counselors AS c ON cs.counselors_id = c.counselors_id";
// Fetch counselor data
$counselorsStmt = $pdo->query("SELECT counselors_id, counselors_name FROM counselors");
$counselors = $counselorsStmt->fetchAll(PDO::FETCH_ASSOC);


$sql = "SELECT cs.*, c.counselors_name, cs.file_path
        FROM counseling_sessions AS cs
        LEFT JOIN counselors AS c ON cs.counselors_id = c.counselors_id";




$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'system_description'");
$stmt->execute();
$systemDescription = $stmt->fetchColumn(); 

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'vision_statement'");
$stmt->execute();
$visionStatement = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mission_statement'");
$stmt->execute();
$missionStatement = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'quality_policy'");
$stmt->execute();
$qualityPolicy = $stmt->fetchColumn();


$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'services_paragraph'");
$stmt->execute();
$servicesParagraph = $stmt->fetchColumn(); 

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'career_services_paragraph'");
$stmt->execute();
$careerServicesParagraph = $stmt->fetchColumn();

// $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'job_fair_paragraph'");
// $stmt->execute();
// $jobFairParagraph = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'counseling_paragraph'");
$stmt->execute();
$counselingParagraph = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'student_participation_paragraph'");
$stmt->execute();
$studentParticipationParagraph = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'high_passing_rate_paragraph'");
$stmt->execute();
$highPassingRateParagraph = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'gender_development_paragraph'");
$stmt->execute();
$genderDevelopmentParagraph = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'health_seminars_paragraph'");
$stmt->execute();
$healthSeminarsParagraph = $stmt->fetchColumn()

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Lato:300,300i,400,400i,700,700i" rel="stylesheet">    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap">
    <!-- Vendor CSS Files -->
    <!-- <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">  
    <style>
        .card-header {
            display: flex;
            align-items: center;
            height: 60px;
        }
        
        .settings-header {
            font-size: 18px;
            font-weight: bold;
        }
        
        .button-container {
            display: flex;
            align-items: center;
            height: 60px;
        }
        
        .btn {
            display: flex;
            align-items: center;
            height: 40px;
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
        /* .btn-success {
            background-color: #4f8f1e;
            color: #d8f0c6;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            font-size: 14px; 
        } */
        /* settings css */
        .card-header{
            background: #f2f2f2;
        }
        .card-header:hover{
            background-color: #d9d9d9;
        }
        .settings-header{
            color: #4d4d4d;
        }
        .custom-modal-top {
            margin-top: 5%;  /* Adjust the value to move it higher or lower */
        }
        
    </style>
</head>
<body>
<div id="wrapper">

<aside id="sidebar">
            <div class="logo">Your Logo</div>
            <nav id="sidebar">
            <ul class="list-unstyled components">
            <li>
        <a href="main.php" class="nav-link ">Dashboard</a>
    </li>
     <li class="nav-item">
    <!-- Main link for Student Menu -->
    <a href="#studentSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link">
        Students
    </a>
    <!-- Submenu items -->
    <ul class="collapse list-unstyled" id="studentSubmenu">
        <li>
            <a href="student.php" class="nav-link">All Students</a>
        </li>
        <li>
            <a href="enrolled_students.php" class="nav-link">Enrolled</a>
        </li>
        <li>
            <a href="not_enrolled_students.php" class="nav-link">Not Enrolled</a>
        </li>
        <li>
            <a href="graduate_students.php" class="nav-link">Graduate</a>
        </li>
    </ul>
</li>
   
    <?php if ($_SESSION['role'] == 'superadmin'): ?>
       
       <li>
     <a href="#counselingSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
        Counseling
        <?php
        // Get pending count
        $pendingStmt = $pdo->query("SELECT COUNT(*) FROM counseling_sessions WHERE status IS NULL");
        $pendingCount = $pendingStmt->fetchColumn();
        
        if ($pendingCount > 0) {
            echo '<span class="badge badge-danger pending-badge">' . $pendingCount . '</span>';
        }
        ?>
    </a>
    <ul class="collapse list-unstyled" id="counselingSubmenu">
        <li>
            <a href="counseling.php" class="nav-link">
                Pending
                <?php if ($pendingCount > 0): ?>
                    <span class="badge badge-danger"><?= $pendingCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="scheduled_counseling.php" class="nav-link">Scheduled</a>
        </li>
        <li>
            <a href="completed_counseling.php" class="nav-link">Completed</a>
        </li>
    </ul>
</li>
<li>
    <a href="#violationSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
        Violations
    </a>
    <ul class="collapse list-unstyled" id="violationSubmenu">
        <li>
            <a href="violation.php?status=ongoing" class="nav-link">Ongoing</a>
        </li>
        <li>
            <a href="violation.php?status=scheduled" class="nav-link">Scheduled</a>
        </li>
        <li>
            <a href="violation.php?status=completed" class="nav-link">Completed</a>
        </li>
    </ul>
</li>

        <li>
            <a href="users.php" class="nav-link ">Users</a>
        </li>
        <li>
            <a href="history.php" class="nav-link">History</a>
        </li>
        <li>
                <a href="setting.php" class="nav-link active">Settings</a>
            </li>
    <?php elseif ($_SESSION['role'] == 'admin_cs' || $_SESSION['role'] == 'admin_csd' || $_SESSION['role'] == 'admin_pc'): ?>
       
        <li>
    <a href="#counselingSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Counseling</a>
    <ul class="collapse list-unstyled" id="counselingSubmenu">
        <li>
            <a href="counseling.php" class="nav-link">Pending</a>
        </li>
        <li>
            <a href="scheduled_counseling.php" class="nav-link">Scheduled</a>
        </li>
        <li>
            <a href="completed_counseling.php" class="nav-link">Completed</a>
        </li>
    </ul>
</li>

        
<li>
    <a href="#violationSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
        Violations
    </a>
    <ul class="collapse list-unstyled" id="violationSubmenu">
        <li>
            <a href="violation.php?status=ongoing" class="nav-link">Ongoing</a>
        </li>
        <li>
            <a href="violation.php?status=scheduled" class="nav-link">Scheduled</a>
        </li>
        <li>
            <a href="violation.php?status=completed" class="nav-link">Completed</a>
        </li>
    </ul>
</li>

        
    <?php else: ?>
       
        <li>
    <a href="#violationSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
        Violations
    </a>
    <ul class="collapse list-unstyled" id="violationSubmenu">
        <li>
            <a href="violation.php?status=ongoing" class="nav-link">Ongoing</a>
        </li>
        <li>
            <a href="violation.php?status=scheduled" class="nav-link">Scheduled</a>
        </li>
        <li>
            <a href="violation.php?status=completed" class="nav-link">Completed</a>
        </li>
    </ul>
</li>

    <?php endif; ?>
    <li>
        <a href="#" class="nav-link" data-toggle="modal" data-target="#logoutModal">Logout</a>
    </li>
</ul>

            </nav>
        </aside>

        <div id="content">
    <!-- <div class="container">         -->
    <div class="menu-header">
    <button type="button" id="sidebarCollapse" class="btn menu-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
        </svg>
        <span class="menu-text" style="padding-left:15px; margin-top:-15px;">Settings</span>
    </button>
</div>

<div class="container">
    <div class="row">
        <!-- Column 1 -->
        <div class="col-md-6">
            <!-- Student Information Settings -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#studentInfoCollapse" aria-expanded="true" aria-controls="studentInfoCollapse">
                    <div class="settings-header">Student Information Settings</div>
                </div>
                <div id="studentInfoCollapse" class="collapse">
                    <div class="card-body">
                        <div class="button-container d-flex justify-content-center">
                            <button type="button" class="btn btn-primary mb-3 mr-2" data-toggle="modal" data-target="#addProgramModal">
                                <span class="d-flex align-items-center justify-content-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg mr-2" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                                    </svg>
                                    Add Program
                                </span>
                            </button>

                            <button type="button" class="btn btn-danger mb-3 " data-toggle="modal" data-target="#removeProgramModal">
    <span class="d-flex align-items-center justify-content-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-lg mr-2" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"/>
        </svg>
        Remove Program
    </span>
</button>
                        </div>

                        
                    </div>
                </div>
            </div>


            <!-- Add this modal -->
<div class="modal fade" id="removeProgramModal" tabindex="-1" role="dialog" aria-labelledby="removeProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="removeProgramModalLabel">
                    <i class="fas fa-trash-alt"></i> Remove Program
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <form id="removeProgramForm" method="post" action="remove_program.php">
                    <div class="form-group">
                        <label for="programSelect"><strong>Select Program to Remove</strong></label>
                        <select class="form-control border-danger" id="programSelect" name="program_id" required>
                            <option value="">-- Select Program --</option>
                            <?php
                            $stmt = $pdo->query("SELECT program_id, program_name FROM program");
                            while ($row = $stmt->fetch()) {
                                echo "<option value='" . $row['program_id'] . "'>" . htmlspecialchars($row['program_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" form="removeProgramForm" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
    </div>
</div>


            <!-- Counselor Settings -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#counselorSettingsCollapse" aria-expanded="true" aria-controls="counselorSettingsCollapse">
                    <div class="settings-header">Counselor Settings</div>
                </div>
                <div id="counselorSettingsCollapse" class="collapse">
                    <div class="card-body">
                        <div class="button-container d-flex justify-content-center">
                            <button type="button" class="btn btn-primary mb-3 mr-2" data-toggle="modal" data-target="#addCounselorModal">
                                <span class="d-flex align-items-center justify-content-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg mr-2" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                                    </svg>
                                    Add Counselor
                                </span>
                            </button>
                            <button type="button" class="btn btn-danger mb-3" data-toggle="modal" data-target="#deleteCounselorModal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" fill="currentColor" class="bi bi-dash mr-2" viewBox="0 0 16 16">
                                    <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8"/>
                                </svg>
                                Remove Counselor
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Violation Settings -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#violationSettingsCollapse" aria-expanded="true" aria-controls="violationSettingsCollapse">
                    <div class="settings-header">Violation Settings</div>
                </div>
                <div id="violationSettingsCollapse" class="collapse">
                    <div class="card-body">
                        <div class="button-container d-flex justify-content-center">
                            <a href="#" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addViolationModal">
                                <span class="d-flex align-items-center justify-content-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg mr-2" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                                    </svg>
                                    Add Violation
                                </span>
                                
                            </a>
                                                    </div>
                    </div>
                </div>
            </div>
            
            
            <!-- Career Services -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#careerServCollapse" aria-expanded="true" aria-controls="qualityPolicyCollapse">
                    <div class="settings-header">Career Services</div>
                </div>
                <div id="careerServCollapse" class="collapse">
                    <div class="card-body">
                        <form id="careerServicesParagraphForm" action="update_setting.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="career_services_paragraph" name="setting_value" rows="5"><?php echo htmlspecialchars($careerServicesParagraph); ?></textarea>
                                <input type="hidden" name="setting_key" value="career_services_paragraph">
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">SAVE CHANGES</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Job Fair -->
            <!-- <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#jobFairCollapse" aria-expanded="true" aria-controls="qualityPolicyCollapse">
                    <div class="settings-header">Job Fair</div>
                </div>
                <div id="jobFairCollapse" class="collapse">
                    <div class="card-body">
                        <form id="jobFairParagraphForm" action="update_setting.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="job_fair_paragraph" name="setting_value" rows="5"><?php echo htmlspecialchars($jobFairParagraph); ?></textarea>
                                <input type="hidden" name="setting_key" value="job_fair_paragraph">
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-outline-success">Save Changes</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div> -->

            <!-- Counseling -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#counselingCollapse" aria-expanded="true" aria-controls="qualityPolicyCollapse">
                    <div class="settings-header">Counseling</div>
                </div>
                <div id="counselingCollapse" class="collapse">
                    <div class="card-body">
                        <form id="counselingParagraphForm" action="update_setting.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="counseling_paragraph" name="setting_value" rows="5"><?php echo htmlspecialchars($counselingParagraph); ?></textarea>
                                <input type="hidden" name="setting_key" value="counseling_paragraph">
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit"class="btn btn-success">SAVE CHANGES</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Student's Active Participation -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#studentActCollapse" aria-expanded="true" aria-controls="qualityPolicyCollapse">
                    <div class="settings-header">Student's Active Participation</div>
                </div>
                <div id="studentActCollapse" class="collapse">
                    <div class="card-body">
                        <form id="studentParticipationParagraphForm" action="update_setting.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="student_participation_paragraph" name="setting_value" rows="5"><?php echo htmlspecialchars($studentParticipationParagraph); ?></textarea>
                                <input type="hidden" name="setting_key" value="student_participation_paragraph">
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">SAVE CHANGES</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Services -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#servicesParaghCollapse" aria-expanded="true" aria-controls="qualityPolicyCollapse">
                    <div class="settings-header">Services</div>
                </div>
                <div id="servicesParaghCollapse" class="collapse">
                    <div class="card-body">
                        <form id="servicesParagraphForm" action="update_setting.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="services_paragraph" name="setting_value" rows="5"><?php echo htmlspecialchars($servicesParagraph); ?></textarea>
                                <input type="hidden" name="setting_key" value="services_paragraph">
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">SAVE CHANGES</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 2 -->
        <div class="col-md-6">
            

            <!-- Banner -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#systemDescriptionCollapse" aria-expanded="true" aria-controls="systemDescriptionCollapse">
                    <div class="settings-header">Hero Banner Text</div>
                </div>
                <div id="systemDescriptionCollapse" class="collapse">
                    <div class="card-body">
                        <form id="systemDescriptionForm" action="update_system_description.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="systemDescription" name="system_description" rows="3"><?php echo htmlspecialchars($systemDescription); ?></textarea>
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">SAVE CHANGES</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Vision Statement -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#visionStatementCollapse" aria-expanded="true" aria-controls="visionStatementCollapse">
                    <div class="settings-header">Vision Statement</div>
                </div>
                <div id="visionStatementCollapse" class="collapse">
                    <div class="card-body">
                        <form id="visionStatementForm" action="update_vision_statement.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="visionStatement" name="vision_statement" rows="3"><?php echo htmlspecialchars($visionStatement); ?></textarea>
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">SAVE CHANGES</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mission Statement -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#missionStatementCollapse" aria-expanded="true" aria-controls="missionStatementCollapse">
                    <div class="settings-header">Mission Statement</div>
                </div>
                <div id="missionStatementCollapse" class="collapse">
                    <div class="card-body">
                        <form id="missionStatementForm" action="update_mission_statement.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="missionStatement" name="mission_statement" rows="3"><?php echo htmlspecialchars($missionStatement); ?></textarea>
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">SAVE CHANGES</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quality Policy -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#qualityPolicyCollapse" aria-expanded="true" aria-controls="qualityPolicyCollapse">
                    <div class="settings-header">Quality Policy</div>
                </div>
                <div id="qualityPolicyCollapse" class="collapse">
                    <div class="card-body">
                        <form id="qualityPolicyForm" action="update_quality_policy.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="qualityPolicy" name="quality_policy" rows="3"><?php echo htmlspecialchars($qualityPolicy); ?></textarea>
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">SAVE CHANGES</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- High Passing Rate -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#highPassCollapse" aria-expanded="true" aria-controls="qualityPolicyCollapse">
                    <div class="settings-header">High Passing Rate</div>
                </div>
                <div id="highPassCollapse" class="collapse">
                    <div class="card-body">
                        <form id="highPassingRateParagraphForm" action="update_setting.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="high_passing_rate_paragraph" name="setting_value" rows="5"><?php echo htmlspecialchars($highPassingRateParagraph); ?></textarea>
                                <input type="hidden" name="setting_key" value="high_passing_rate_paragraph">
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">SAVE CHANGES</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>

         <!-- Gender and Development Services -->
<div class="card mb-4">
    <div class="card-header" data-toggle="collapse" data-target="#gadServCollapse" aria-expanded="true" aria-controls="qualityPolicyCollapse">
        <div class="settings-header">Gender and Development Services</div>
    </div>
    <div id="gadServCollapse" class="collapse">
        <div class="card-body">
            <form id="genderDevelopmentParagraphForm" action="update_setting.php" method="post">
                <div class="form-group">
                    <textarea class="form-control" id="gender_development_paragraph" name="setting_value" rows="5"><?php echo htmlspecialchars($genderDevelopmentParagraph); ?></textarea>
                    <input type="hidden" name="setting_key" value="gender_development_paragraph">
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">SAVE CHANGES</button>
                </div>
            </form>
        </div>
    </div>
</div>
            <!-- Student's Health Seminars -->
            <div class="card mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#studentHealthCollapse" aria-expanded="true" aria-controls="qualityPolicyCollapse">
                    <div class="settings-header">Student's Health Seminars</div>
                </div>
                <div id="studentHealthCollapse" class="collapse">
                    <div class="card-body">
                        <form id="healthSeminarsParagraphForm" action="update_setting.php" method="post">
                            <div class="form-group">
                                <textarea class="form-control" id="health_seminars_paragraph" name="setting_value" rows="5"><?php echo htmlspecialchars($healthSeminarsParagraph); ?></textarea>
                                <input type="hidden" name="setting_key" value="health_seminars_paragraph">
                            </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">SAVE CHANGES</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Program Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1" role="dialog" aria-labelledby="addProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <!-- Header with custom style -->
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="addProgramModalLabel">
                    <i class="fas fa-plus-circle"></i> Add Program
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Body -->
            <div class="modal-body">
                <form id="addProgramForm" action="add_program.php" method="post">
                    <div class="form-group">
                        <label for="programName"><strong>Program Name</strong></label>
                        <input type="text" class="form-control border-info" id="programName" name="programName" 
                               placeholder="Enter program name" required>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-secondary mr-2" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-check"></i> Add Program
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Add Counselor Modal -->
<div class="modal fade" id="addCounselorModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-modal-top" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLabel">Add Counselor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addCounselorForm">
                    <div class="form-group">
                        <label for="counselorName">Counselor Name</label>
                        <input type="text" class="form-control border-primary" id="counselorName" name="counselor_name" placeholder="Enter counselor name" required>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Add Counselor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Delete Counselor Modal -->
<div class="modal fade" id="deleteCounselorModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-modal-top" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Remove Counselor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="deleteCounselorForm">
                    <div class="form-group">
                        <label for="deleteCounselorSelect">Select Counselor to Delete</label>
                        <select class="form-control border-danger" id="deleteCounselorSelect" name="counselor_id" required>
                            <option value="">-- Select Counselor --</option>
                            <?php foreach ($counselors as $counselor): ?>
                                <option value="<?= htmlspecialchars($counselor['counselors_id']); ?>">
                                    <?= htmlspecialchars($counselor['counselors_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-danger">Delete Counselor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="addViolationModal" tabindex="-1" aria-labelledby="addViolationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addViolationModalLabel">
                    <i class="fas fa-exclamation-circle"></i> Add Violation
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="violationForm">
                    <div class="form-group">
                        <label for="violation_type">Type of Violation</label>
                        <select class="form-control border-primary" id="violation_type" name="violation_type" required>
                            <option value="">Select Type of Violation</option>
                            <option value="minor">Minor</option>
                            <option value="major">Major</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="violationDetails">Violation Details:</label>
                        <textarea class="form-control border-primary" id="violationDetails" rows="3" placeholder="Provide details about the violation" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="addViolationBtn">
                    <i class="fas fa-check"></i> Add Violation
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Remove Violation Modal -->
<div class="modal fade" id="removeViolationModal" tabindex="-1" role="dialog" aria-labelledby="removeViolationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeViolationModalLabel">Remove Violation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="removeViolationForm">
                    <div class="form-group">
                        <label for="violationSelect">Select Violation to Remove</label>
                        <select class="form-control" id="violationSelect" name="violation_id" required>
                            <option value="">-- Select Violation --</option>
                            <!-- Populate with violations from the database -->
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Remove Violation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

  <!-- Logout Confirmation Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to log out?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>




    <!-- jQuery Slim, Popper.js, Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
    <!-- jQuery Custom Scroller -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#sidebar").mCustomScrollbar({
                theme: "minimal"
            });

            $('#sidebarCollapse').on('click', function () {
                $('#sidebar, #content').toggleClass('active');
                $('.collapse.in').toggleClass('in');
                $('a[aria-expanded=true]').attr('aria-expanded', 'false');
            });
        });



        $(document).ready(function() {
    // Enable input in Add Program modal
    $('#addProgramModal').on('shown.bs.modal', function () {
        $('#programName').focus();
        $('#programName').prop('readonly', false);
    });

    // Clear input when modal is closed
    $('#addProgramModal').on('hidden.bs.modal', function () {
        $('#programName').val('');
    });
});


        $('#addProgramForm').on('submit', function(e) {
    e.preventDefault();
    var programName = $('#programName').val();
    
    $.ajax({
        type: "POST",
        url: "add_program.php",
        data: { programName: programName },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Program Added Successfully!</h4>
                            <p style="color: #666;">${programName} has been added to the system</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        $('#addProgramModal').modal('hide');
                        location.reload();
                    });
                }, 2000);
            }
        },
        error: function() {
            alert("Error adding program");
        }
    });
});



        
        $(document).ready(function() {
          // Add Counselor Success Display
$('#addCounselorForm').on('submit', function(e) {
    e.preventDefault();
    var counselorName = $('#counselorName').val();
    
    $.ajax({
        type: "POST",
        url: "add_counselor_handler.php",
        data: { counselor_name: counselorName },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Counselor Added Successfully!</h4>
                            <p style="color: #666;">${counselorName} has been added as a counselor</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        $('#addCounselorModal').modal('hide');
                        location.reload();
                    });
                }, 2000);
            }
        }
    });
});
        });
        
            $(document).ready(function() {
        });
        $('#deleteCounselorForm').on('submit', function(e) {
    e.preventDefault();
    var counselorId = $('#deleteCounselorSelect').val();
    var counselorName = $('#deleteCounselorSelect option:selected').text();
    
    if (counselorId) {
        $.ajax({
            type: 'POST',
            url: 'delete_counselor.php',
            data: { 'counselor_id': counselorId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const successOverlay = $(`
                        <div class="success-overlay" style="
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(255, 255, 255, 0.95);
                            z-index: 9999;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                        ">
                            <div class="success-content" style="text-align: center;">
                                <div class="success-icon" style="
                                    width: 80px;
                                    height: 80px;
                                    background: #dc3545;
                                    border-radius: 50%;
                                    margin: 0 auto 20px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                ">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-trash" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                    </svg>
                                </div>
                                <h4 style="color: #dc3545; margin-bottom: 10px;">Counselor Removed Successfully!</h4>
                                <p style="color: #666;">${counselorName} has been removed from the system</p>
                            </div>
                        </div>
                    `);

                    $('body').append(successOverlay);
                    
                    setTimeout(() => {
                        successOverlay.fadeOut(300, function() {
                            $(this).remove();
                            $('#deleteCounselorModal').modal('hide');
                            location.reload();
                        });
                    }, 2000);
                }
            }
        });
    }
});       
   
    $(document).ready(function() {
        $('.view-btn').click(function() {
            var sessionId = $(this).data('session-id');
            $.ajax({
                url: 'fetch_counseling_details.php',
                type: 'GET',
                data: { session_id: sessionId },
                success: function(response) {
                    $('#counselingDetails').html(response);
                    $('#detailsModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('AJAX error:', status, error);
                    $('#counselingDetails').html('Unable to fetch details at this time.');
                    $('#detailsModal').modal('show');
                }
            });
        });
  });



  $(document).ready(function() {
    $('#addViolationBtn').click(function() {
        const violationType = $('#violation_type').val();
        const violationDetails = $('#violationDetails').val();

        // Validate input
        if (!violationType || !violationDetails) {
            alert("Please fill in all fields.");
            return;
        }

        // Send an AJAX request to add the violation type and details
        $.ajax({
            type: "POST",
            url: "add_violation_type.php",
            data: {
                violationType: violationType,
                description: violationDetails
            },
            dataType: "json",
            success: function(response) {
                if (response.status === 'success') {
                    // Create and show success overlay
                    const successOverlay = $(`
                        <div class="success-overlay" style="
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(255, 255, 255, 0.95);
                            z-index: 9999;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                        ">
                            <div class="success-content" style="text-align: center;">
                                <div class="success-icon" style="
                                    width: 80px;
                                    height: 80px;
                                    background: #4f8f1e;
                                    border-radius: 50%;
                                    margin: 0 auto 20px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                ">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                        <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                    </svg>
                                </div>
                                <h4 style="color: #4f8f1e; margin-bottom: 10px;">Violation Added Successfully!</h4>
                                <p style="color: #666;">The violation has been added to the system.</p>
                            </div>
                        </div>
                    `);

                    $('body').append(successOverlay);
                    
                    setTimeout(() => {
                        successOverlay.fadeOut(300, function() {
                            $(this).remove();
                            $('#addViolationModal').modal('hide');
                            location.reload(); // Reload to show the new violation in the list
                        });
                    }, 2000);
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.log("Error:", error);
                alert("An error occurred while adding the violation.");
            }
        });

        // Close the modal
        $('#addViolationModal').modal('hide');
    });
});

$(document).ready(function() {
    $('#systemDescriptionForm').on('submit', function(e) {
        e.preventDefault(); 
        var newDescription = $('#systemDescription').val();

        $.ajax({
            type: "POST",
            url: "update_system_description.php",
            data: { system_description: newDescription },
            success: function(response) { 
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">System Description Updated Successfully!</h4>
                            <p style="color: #666;">The system description has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        $('#systemDescription').val(newDescription); // Optionally update the textarea
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating system description.");
            }
        });
    });
});
$(document).ready(function() {
    $('#visionStatementForm').on('submit', function(e) {
        e.preventDefault(); 
        var newVision = $('#visionStatement').val();

        $.ajax({
            type: "POST",
            url: "update_vision_statement.php",
            data: { vision_statement: newVision },
            success: function(response) { 
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Vision Statement Updated Successfully!</h4>
                            <p style="color: #666;">The vision statement has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        $('#visionStatement').val(newVision); // Optionally update the textarea
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating vision statement.");
            }
        });
    });
});


$(document).ready(function() {
    $('#missionStatementForm').on('submit', function(e) {
        e.preventDefault(); 
        var newMission = $('#missionStatement').val();

        $.ajax({
            type: "POST",
            url: "update_mission_statement.php",
            data: { mission_statement: newMission },
            success: function(response) { 
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12 .736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Mission Statement Updated Successfully!</h4>
                            <p style="color: #666;">The mission statement has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        $('#missionStatement').val(newMission); // Optionally update the textarea
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating mission statement.");
            }
        });
    });
});


    $(document).ready(function() {
    $('#qualityPolicyForm').on('submit', function(e) {
        e.preventDefault(); 
        var newPolicy = $('#qualityPolicy').val();

        $.ajax({
            type: "POST",
            url: "update_quality_policy.php",
            data: { quality_policy: newPolicy },
            success: function(response) { 
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Quality Policy Updated Successfully!</h4>
                            <p style="color: #666;">The quality policy has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        $('#qualityPolicy').val(newPolicy); // Optionally update the textarea
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating quality policy.");
            }
        });
    });
});

$(document).ready(function() {
    $('#servicesParagraphForm').on('submit', function(e) {
        e.preventDefault();
        var newParagraph = $('#services_paragraph').val();

        $.ajax({
            type: "POST",
            url: "update_setting.php", // Ensure this URL points to the correct file for handling the update
            data: { setting_key: 'services_paragraph', setting_value: newParagraph },
            success: function(response) {
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Services Updated Successfully!</h4>
                            <p style="color: #666;">The Services paragraph has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        // Optionally refresh the page or just hide the modal
                        // location.reload(); 
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating Services paragraph.");
            }
        });
    });
});


    $(document).ready(function() {
    $('#careerServicesParagraphForm').on('submit', function(e) {
        e.preventDefault();
        var newParagraph = $('#career_services_paragraph').val();

        $.ajax({
            type: "POST",
            url: "update_setting.php", // Ensure this URL points to the correct file for handling the update
            data: { setting_key: 'career_services_paragraph', setting_value: newParagraph },
            success: function(response) {
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Career Services Updated Successfully!</h4>
                            <p style="color: #666;">The Career Services paragraph has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        // Optionally refresh the page or just hide the modal
                        // location.reload(); 
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating Career Services paragraph.");
            }
        });
    });
});
$(document).ready(function() {
    $('#jobFairParagraphForm').on('submit', function(e) {
        e.preventDefault();
        var newParagraph = $('#job_fair_paragraph').val();

        $.ajax({
            type: "POST",
            url: "update_setting.php", // Ensure this URL points to the correct file for handling the update
            data: { setting_key: 'job_fair_paragraph', setting_value: newParagraph },
            success: function(response) {
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Job Fair Updated Successfully!</h4>
                            <p style="color: #666;">The Job Fair paragraph has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        // Optionally refresh the page or just hide the modal
                        // location.reload(); 
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating Job Fair paragraph.");
            }
        });
    });
});



$(document).ready(function() {
    $('#counselingParagraphForm').on('submit', function(e) {
        e.preventDefault();
        var newParagraph = $('#counseling_paragraph').val();

        $.ajax({
            type: "POST",
            url: "update_setting.php", // Ensure this URL points to the correct file for handling the update
            data: { setting_key: 'counseling_paragraph', setting_value: newParagraph },
            success: function(response) {
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Counseling Updated Successfully!</h4>
                            <p style="color: #666;">The Counseling paragraph has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        // Optionally refresh the page or just hide the modal
                        // location.reload(); 
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating Counseling paragraph.");
            }
        });
    });
});


$(document).ready(function() {
    $('#studentParticipationParagraphForm').on('submit', function(e) {
        e.preventDefault();
        var newParagraph = $('#student_participation_paragraph').val();

        $.ajax({
            type: "POST",
            url: "update_setting.php", // Ensure this URL points to the correct file for handling the update
            data: { setting_key: 'student_participation_paragraph', setting_value: newParagraph },
            success: function(response) {
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Student Participation Updated Successfully!</h4>
                            <p style="color: #666;">The Student Participation paragraph has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        // Optionally refresh the page or just hide the modal
                        // location.reload(); 
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating Student Participation paragraph.");
            }
        });
    });
});


$(document).ready(function() {
    $('#highPassingRateParagraphForm').on('submit', function(e) {
        e.preventDefault(); 
        var newHighPassingRate = $('#high_passing_rate_paragraph').val();

        $.ajax({
            type: "POST",
            url: "update_setting.php",
            data: { setting_key: 'high_passing_rate_paragraph', setting_value: newHighPassingRate },
            success: function(response) { 
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">High Passing Rate Updated Successfully!</h4>
                            <p style="color: #666;">The high passing rate paragraph has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        $('#high_passing_rate_paragraph').val(newHighPassingRate); // Optionally update the textarea
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating high passing rate.");
            }
        });
    });
});

        
$(document).ready(function() {
    $('#healthSeminarsParagraphForm').on('submit', function(e) {
        e.preventDefault(); 
        var newHealthSeminars = $('#health_seminars_paragraph').val();

        $.ajax({
            type: "POST",
            url: "update_setting.php",
            data: { setting_key: 'health_seminars_paragraph', setting_value: newHealthSeminars },
            success: function(response) { 
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Health Seminars Updated Successfully!</h4>
                            <p style="color: #666;">The health seminars paragraph has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        $('#health_seminars_paragraph').val(newHealthSeminars); // Optionally update the textarea
                    });
                }, 2000);
            },
            error: function() {
                alert("Error updating health seminars.");
            }
        });
    });
});
   

$(document).ready(function() {
    $('#removeProgramForm').on('submit', function(e) {
        e.preventDefault();
        var programId = $('#programSelect').val();
        var programName = $('#programSelect option:selected').text();

        // Custom confirmation modal
        const confirmOverlay = $(`
            <div class="confirm-overlay" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
            ">
                <div class="confirm-dialog" style="
                    background: white;
                    padding: 25px;
                    border-radius: 10px;
                    text-align: center;
                    max-width: 400px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                ">
                    <h4 style="color: #dc3545; margin-bottom: 20px;">Remove Program</h4>
                    <p style="margin-bottom: 20px;">Are you sure you want to remove "${programName}"?</p>
                    <div style="display: flex; justify-content: center; gap: 10px;">
                        <button class="btn btn-secondary cancel-btn">Cancel</button>
                        <button class="btn btn-danger confirm-btn">Remove</button>
                    </div>
                </div>
            </div>
        `);

        $('body').append(confirmOverlay);

        confirmOverlay.find('.cancel-btn').click(function() {
            confirmOverlay.remove();
        });

        confirmOverlay.find('.confirm-btn').click(function() {
            confirmOverlay.remove();
            
            $.ajax({
                type: 'POST',
                url: 'remove_program.php',
                data: { program_id: programId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Success overlay
                        const successOverlay = $(`
                            <div class="success-overlay" style="
                                position: fixed;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                background: rgba(255, 255, 255, 0.95);
                                z-index: 9999;
                                display: flex;
                                justify-content: center;
                                align-items: center;
                            ">
                                <div class="success-content" style="text-align: center;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="#28a745" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                    </svg>
                                    <h4 style="color: #28a745; margin-top: 15px;">Program Successfully Removed!</h4>
                                </div>
                            </div>
                        `);

                        $('body').append(successOverlay);
                        
                        setTimeout(() => {
                            successOverlay.fadeOut(300, function() {
                                $(this).remove();
                                location.reload();
                            });
                        }, 1500);
                    } else {
                        // Error notification
                        const errorOverlay = $(`
                            <div class="alert alert-danger" style="
                                position: fixed;
                                top: 20px;
                                left: 50%;
                                transform: translateX(-50%);
                                z-index: 9999;
                                padding: 15px 25px;
                                border-radius: 5px;
                            ">
                                Error: ${response.message}
                            </div>
                        `);

                        $('body').append(errorOverlay);
                        setTimeout(() => errorOverlay.fadeOut(300, function() { $(this).remove(); }), 3000);
                    }
                },
                error: function() {
                    // Network error notification
                    const networkErrorOverlay = $(`
                        <div class="alert alert-danger" style="
                            position: fixed;
                            top: 20px;
                            left: 50%;
                            transform: translateX(-50%);
                            z-index: 9999;
                            padding: 15px 25px;
                            border-radius: 5px;
                        ">
                            Network error occurred while removing program
                        </div>
                    `);

                    $('body').append(networkErrorOverlay);
                    setTimeout(() => networkErrorOverlay.fadeOut(300, function() { $(this).remove(); }), 3000);
                }
            });
        });
    });
});


$(document).ready(function() {
    $('#genderDevelopmentParagraphForm').on('submit', function(e) {
        e.preventDefault(); 
        var newGenderDevelopment = $('#gender_development_paragraph').val();

        $.ajax({
            type: "POST",
            url: "update_setting.php", // Ensure this URL points to the correct file for handling the update
            data: { setting_key: 'gender_development_paragraph', setting_value: newGenderDevelopment },
            success: function(response) { 
                // Create and show success overlay
                const successOverlay = $(`
                    <div class="success-overlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    ">
                        <div class="success-content" style="text-align: center;">
                            <div class="success-icon" style="
                                width: 80px;
                                height: 80px;
                                background: #4f8f1e;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f8f1e; margin-bottom: 10px;">Gender Development Updated Successfully!</h4>
                            <p style="color: #666;">The gender development paragraph has been updated.</p>
                        </div>
                    </div>
                `);

                $('body').append(successOverlay);
                
                setTimeout(() => {
                    successOverlay.fadeOut(300, function() {
                        $(this).remove();
                        $('#gender_development_paragraph').val(newGenderDevelopment); // Optionally update the textarea
                    });
                }, 2000);
 },
            error: function() {
                alert("Error updating gender development paragraph.");
            }
        });
    });
});


function confirmLogout() {
    // Show a confirmation dialog
    var confirmation = confirm("Are you sure you want to logout?");
    
    // If the user clicks "OK", return true to proceed with the logout
    // If the user clicks "Cancel", return false to prevent the logout
    return confirmation;
}


    </script>
</body>
</html>