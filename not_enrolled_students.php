<?php
require 'dbconfig.php';
session_start();

// Retrieve success and error messages from the session
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear the messages from the session
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Redirect unauthorized users
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'superadmin' && $_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin_cs' && $_SESSION['role'] != 'admin_csd' && $_SESSION['role'] != 'admin_pc')) {
    header('Location: index.php');
    exit();
}

// Initialize variables
$selectedProgramId = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$studentsPerPage = 16;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $studentsPerPage;
$whereConditions = ['students.status = "Not Enrolled"'];
$params = [];

// Build the base SQL query
$sql = "SELECT students.*, program.program_name, 
        students.year_level,
        students.birthdate,
        students.phone_number,
        students.created_at AS enrollment_date 
        FROM students 
        LEFT JOIN program ON students.program_id = program.program_id
        WHERE students.is_archived = 0 AND students.status = 'Not Enrolled'";

if ($selectedProgramId > 0) {
    $whereConditions[] = 'students.program_id = ?';
    $params[] = $selectedProgramId;
}

if (!empty($_GET['search'])) {
    $searchTerm = "%{$_GET['search']}%";
    $whereConditions[] = '(students.student_no LIKE ? 
                          OR students.first_name LIKE ? 
                          OR students.surname LIKE ? 
                          OR students.middle_name LIKE ? 
                          OR program.program_name LIKE ? 
                          OR students.year_level LIKE ?)';
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($whereConditions)) {
    $sql .= " AND " . implode(' AND ', $whereConditions);
}

$sql .= " ORDER BY students.student_id ASC LIMIT ? OFFSET ?";
$params[] = $studentsPerPage;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all programs for the dropdown
$programQuery = "SELECT * FROM program";
$programStmt = $pdo->query($programQuery);
$programs = $programStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total not enrolled students for pagination
$totalStudentsSQL = "SELECT COUNT(*) FROM students WHERE is_archived = 0 AND status = 'Not Enrolled'";
$totalStudentsParams = [];

if ($selectedProgramId > 0) {
    $totalStudentsSQL .= " AND program_id = ?";
    $totalStudentsParams[] = $selectedProgramId;
}

if (!empty($_GET['search'])) {
    $totalStudentsSQL .= " AND (student_no LIKE ? OR first_name LIKE ? OR surname LIKE ?)";
    $totalStudentsParams = array_merge($totalStudentsParams, [$searchTerm, $searchTerm, $searchTerm]);
}

$totalStudentsStmt = $pdo->prepare($totalStudentsSQL);
$totalStudentsStmt->execute($totalStudentsParams);
$totalStudents = $totalStudentsStmt->fetchColumn();
$totalPages = ceil($totalStudents / $studentsPerPage);

$noResultMessage = (empty($students)) ? 'No students found.' : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
    <!-- Bootstrap 4 CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Our Custom CSS -->
    <link rel="stylesheet" href="css/navigation.css">
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Lato:300,300i,400,400i,700,700i" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap">
    <!-- Vendor CSS Files -->
    <!-- <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- Include Bootstrap CSS in your layout -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

    
</head>
    <style>
        /* table custom css */
        .table-striped tbody tr:nth-of-type(odd) {
        background-color: #e6f7e4;
        }
        .table thead th {
        background-color: #4c704c;
        color: white; /* Change text color to white for better visibility */
        }
        /* pagination css */
        .pagination-container {
        display: flex;
        justify-content: flex-end; /* Align items to the right */
        }

        .pagination a {
            color: white; /* Text color */
            text-decoration: none; /* Remove underline */
            padding: 8px 12px; /* Padding for each link */
            margin-left: 5px; /* Margin between links */
            border-radius: 4px; /* Rounded corners */
        }

        .pagination a.active {
            background-color: darkgreen; /* Active link background color */
        }
        @media print {
            #sidebar, .pagination-container, .btn, form {
                display: none; /* Hide sidebar, pagination, buttons, and form during print */
            }
            
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .table th, .table td {
                border: 1px solid #ddd;
                padding: 8px;
            }
        }
        
        /* PRINT CSS */
        @media print {
            /* Hide the menu text "Students" */
            .menu-text {
                display: none !important;
            }
            
            /* Add color to the table header */
            table thead th {
                font-weight: 200px;
                color: black !important; 
            }
            /* Adjust table layout to remove gaps caused by hidden columns */
            table {
                border-collapse: collapse;
            }
        }
        @media print {
        body {
            counter-reset: page; /* Initialize the page counter */
        }

        #printFooter {
            display: block !important;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            padding-left: 20px;
            font-size: 16px;
            text-align: left !important;
        }

        #printFooter p {
            margin: 0;
            text-align: left !important;
        }

        /* Display the current page number */
        #printFooter .page-number:before {
            counter-increment: page; /* Increment the page counter */
            content: "" counter(page); /* Display the current page number */
            }
        }
        @media print {
        #printHeader {
            display: flex;
            align-items: center;
            width: 100%;
        }

        #printHeader .container {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0;
        }

        #printHeader .row {
            display: flex;
            width: 100%;
            flex-wrap: nowrap; /* Prevents wrapping of columns */
        }

        #printHeader img {
            max-width: 150px; /* Adjust size as needed */
            margin-right: -15px; /* Space between image and text */
        }

        #printHeader .col-md-10 {
            text-align: center;
        }
        #printHeader .ngi {
            font-size: 12px;
        }
    }
    .page-link:hover{
        color:gray;
    }
    .status-enrolled { color: blue; font-weight: bold; }
.status-notenrolled { color: red; font-weight: bold; }
.status-graduate { color: green; font-weight: bold; }

    .custom-alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1050; /* Ensures it's above other content */
    width: auto; /* Fits the alert's content */
    max-width: 90%; /* Avoids excessively wide alerts */
    padding: 15px;
    border-radius: 5px;
    color: #fff;
    font-size: 16px;
    display: none; /* Hidden by default */
    opacity: 1;
    transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    .custom-alert-success {
        background-color: #28a745; /* Success color */
    }

    .custom-alert-danger {
        background-color: #dc3545; /* Error color */
    }
    
.submenu-item.active {
    background-color: #4c704c; /* Submenu item active background color */
    border-top-left-radius: 20px;
    border-bottom-left-radius: 20px;
    color: white; /* Submenu item text color */
}
    </style>


<body>



<aside id="sidebar">
            <div class="logo">Your Logo</div>
            <nav id="sidebar">
            <ul class="list-unstyled components">
            <li>
        <a href="main.php" class="nav-link ">Dashboard</a>
    </li>
     <li class="nav-item">
    <!-- Main link for Student Menu -->
    <a href="#studentSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link active">
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
            <a href="not_enrolled_students.php" class="nav-link active">Not Enrolled</a>
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
                <a href="setting.php" class="nav-link ">Settings</a>
          </li>
<?php elseif ($_SESSION['role'] == 'admin_cs' || $_SESSION['role'] == 'admin_csd' || $_SESSION['role'] == 'admin_pc'): ?>
<li>
    <a href="#counselingSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
        Counseling
        <?php
        $roleMapping = [
            'admin_cs' => 'Program Coordinator',
            'admin_csd' => 'Student and Discipline',
            'admin_pc' => 'program_coordinator'
        ];
        
        $currentRole = $roleMapping[$_SESSION['role']];
        
        $pendingStmt = $pdo->prepare("SELECT COUNT(*) FROM counseling_sessions 
            WHERE status IS NULL 
            AND assigned_to = :role 
            AND is_archived = 0");
        $pendingStmt->execute(['role' => $currentRole]);
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
            <div class="menu-header">
                <button type="button" id="sidebarCollapse" class="btn menu-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi    bi-list" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                    </svg>
                </button>
                <span class="menu-text">Not Enrolled</span>
                <div id="students-section">
                <div class="col py-3"> 
            <div class="user-info">
    
                <div id="students-section" style="padding-top:15px; padding-left:10px">
        
                <div class="d-flex justify-content-between align-items-center mb-1">
    <div class="d-flex align-items-center">
        <!-- Add Button -->
        <a href="add_student.php" class="btn btn-outline-success mb-3 mr-1">
            <span class="d-flex align-items-center justify-content-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-person-add mr-2" viewBox="0 0 16 16">
                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                    <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                </svg>
                Add a Student
            </span>
        </a>
        <!-- Search Button -->
        <button id="search-toggle" class="btn btn-outline-primary mb-3 mr-1">
            <span class="d-flex align-items-center justify-content-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search mr-2" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                </svg>
                Search
            </span>
        </button>
        <!-- Back Button -->
        <a href="student.php" class="btn btn-outline-secondary mb-3 mr-1">
            <span class="d-flex align-items-center justify-content-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left mr-2" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                </svg>
                Back
            </span>
        </a>
        <!-- Initially Hidden Search Form -->
        <div id="search-form" class="d-none ml-3 align-items-center" style="padding-bottom:16px;">
            <form action="student.php" method="GET" class="d-flex">
                <input type="text" name="search" placeholder="Search " class="form-control mr-2">
                <!-- Program Dropdown -->
                <select name="program_id" class="form-control mr-2">
                    <option value="0">All Programs</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?= $program['program_id']; ?>" <?= $selectedProgramId == $program['program_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($program['program_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
            </form>
        </div>
        
    </div>

    <div class="d-flex align-items-center">
    
        <!-- Import Students Button -->
        
        
        <!-- Print Button -->
        <button id="printButton" class="btn btn-success mb-3" style="background: #e48189; border:none;">
            Print
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
            </svg>
        </button>
    </div>
</div>



<div class="modal fade" id="importStudentsModal" tabindex="-1" role="dialog" aria-labelledby="importStudentsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importStudentsModalLabel">Import Students</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importStudentsForm" enctype="multipart/form-data">
                    <div class="form-group mb-4">
                        <label for="fileInput">Select Excel File</label>
                        <input type="file" class="form-control-file" id="fileInput" name="file" accept=".xlsx,.xls" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="d-block mb-3">Select Status for Imported Students:</label>
                        <div class="form-check mb-2">
                            <input type="radio" id="statusEnrolled" name="importStatus" value="Enrolled" class="form-check-input" required>
                            <label class="form-check-label" for="statusEnrolled">Enrolled</label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="radio" id="statusNotEnrolled" name="importStatus" value="Not Enrolled" class="form-check-input">
                            <label class="form-check-label" for="statusNotEnrolled">Not Enrolled</label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="radio" id="statusGraduate" name="importStatus" value="Graduate" class="form-check-input">
                            <label class="form-check-label" for="statusGraduate">Graduate</label>
                        </div>
                        <div id="statusError" class="invalid-feedback" style="display: none;">
                            Please select a status before importing.
                        </div>
                    </div>
                </form>
            </div>
            <!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: white;">
        <div class="spinner-border text-light" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <h4 class="mt-2">Please wait while importing...</h4>
    </div>
</div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info" form="importStudentsForm">Import</button>
            </div>
        </div>
    </div>
</div>


<!-- PRINT HEADER  -->
        <header id="printHeader" class="d-none d-print-block">
            <div class="container">
                <div class="row">
                    <!-- Image on the left side -->
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <img src="../Oserve/assets/img/cvsulogo.png" alt="University Logo" class="img-fluid">
                    </div>
                    <!-- Centered Text -->
                    <div class="col-md-8 text-center">
                        <h5 style="font-weight:70px;">Republic of the Philippines</h5>
                        <h2>Cavite State University</h2>
                        <h4>Carmona Campus</h4>
                        <p class="ngi">Market Road, Carmona, Cavite <br>
                        ☏(046)487-6328/cvsucarmona@cvsu.edu.ph <br>
                        www.cvsu.edu.ph</p>
                    </div>
                    <!-- Empty column to push text to the center -->
                    <div class="col-md-2"></div>
                </div>
            </div>
            <h1 id="printHeader" class="d-none d-print-block text-center mb-4">Student Records</h1>
        </header>

        <table id="mainTableContainer" class="table table-hover table-bordered">
    <thead>
        <tr>
            <th>STUDENT NUMBER</th>
            <th>LAST NAME</th>
            <th>FIRST NAME</th>
            <th>MIDDLE NAME</th>
            <th>PROGRAM</th>
            <th>YEAR LEVEL</th>
            <th>SEX</th>
            <th>BIRTHDATE</th>
            <th>PHONE NUMBER</th>
            <th>STATUS</th>
            <th>TIMESTAMP</th>
            </tr>
    </thead>
    <tbody>
    <?php if ($noResultMessage): ?>
            <tr>
                <td colspan="11" class="text-center"><?php echo $noResultMessage; ?></td>
            </tr>
        <?php endif; ?>
    <?php foreach ($students as $student): ?>
    <tr>
        <td><?= htmlspecialchars($student['student_no']) ?></td>
        <td><?= htmlspecialchars($student['surname']) ?></td>
        <td><?= htmlspecialchars($student['first_name']) ?></td>
        <td><?= htmlspecialchars($student['middle_name']) ?></td>
        <td><?= htmlspecialchars($student['program_name']) ?></td>
        <td><?= htmlspecialchars($student['year_level']) ?></td>
        <td><?= htmlspecialchars($student['gender']) ?></td>
        <td><?= htmlspecialchars($student['birthdate']) ?></td>
        <td><?= htmlspecialchars($student['phone_number']) ?></td>
        <td style="font-weight: bold; color: 
        <?php
        switch($student['status']) {
            case 'Enrolled':
                echo 'blue';
                break;
            case 'Not Enrolled':
                echo 'red';
                break;
            case 'Graduate':
                echo 'green';
                break;
            default:
                echo 'black';
        }
        ?>;">
        <?= htmlspecialchars($student['status']) ?>
        <td><?= date('M d, Y h:i A', strtotime($student['enrollment_date'])) ?></td>

        <!-- Confirmation Modal -->
        <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Archive</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to archive this student?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="confirmArchive" class="btn btn-success">Yes, proceed</a>
            </div>
            </div>
        </div>
        </div>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select all archive buttons
            const archiveButtons = document.querySelectorAll('.archive-btn');

            archiveButtons.forEach(button => {
            button.addEventListener('click', function () {
                const studentId = this.getAttribute('data-id'); // Get the student ID
                const confirmLink = document.getElementById('confirmArchive');

                // Update the confirm button's href to the correct archive link
                confirmLink.setAttribute('href', `archive_student.php?id=${studentId}`);
            });
            });
        });
        </script>
<!-- Archived Students Table (Initially Hidden) -->
<!-- <div id="archiveTableContainer" style="display: none;">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Program</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($archivedStudents as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                    <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['surname']) ?></td>
                    <td><?= htmlspecialchars($student['program_name']) ?></td>
                    <td>
                        <form action="unarchive_student.php" method="POST" style="display: inline;">
                            <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                            <button type="submit" class="btn btn-outline-success btn-sm" onclick="return confirm('Are you sure you want to restore this student?');">Restore</button>
                        </form>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div> -->


<div class="pagination-container">
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-end">
            <li class="page-item <?php if ($current_page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=1" style="background: <?php echo ($current_page <= 1 ? '#e6d6ff' : '#d0b3ff'); ?>; border:none;">First</a>
            </li>
            <li class="page-item <?php if ($current_page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" tabindex="-1" style="background: <?php echo ($current_page <= 1 ? '#e6d6ff' : '#d0b3ff'); ?>; border:none;">Previous</a>
            </li>

            <?php
            // Determine the range of pages to display
            $startPage = max(1, $current_page - 2);
            $endPage = min($totalPages, $current_page + 2);

            // Adjust start page if end page is close to the total pages
            if ($endPage - $startPage < 4) {
                $startPage = max(1, $endPage - 4);
            }

            // Ensure we don't show more than the total pages
            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>" 
                    style="background: <?php echo ($i == $current_page ? '#5f486a' : '#886798'); ?>; border:none; color: white;">
                    <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php if ($current_page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" 
                style="background: #886798; border:none; color:#ddd;">Next</a>
            </li>
            <li class="page-item <?php if ($current_page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $totalPages; ?>" 
                style="background: #886798; border:none; color:#ddd;">Last</a>
            </li>
        </ul>
    </nav>
</div>




  
    

</div>

</div>
</div>
</div>
</div>
<!-- ARCHIVE STUDENT MODAL -->
<div class="modal fade" id="archivedStudentsModal" tabindex="-1" role="dialog" aria-labelledby="archivedStudentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archivedStudentsModalLabel">Archived Students</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Program</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archivedStudents as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                            <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['surname']) ?></td>
                            <td><?= htmlspecialchars($student['program_name']) ?></td>
                            <td>
                                <form action="unarchive_student.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                                    <button type="button" class="btn btn-outline-success restore-btn" 
                                            data-id="<?= $student['student_id']; ?>" 
                                            data-toggle="modal" data-target="#restoreModal">
                                        Restore
                                    </button>
                                </form>
                                <!-- Success Alert (hidden by default) -->
                                <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
                                    Restore student successfully.
                                </div>
                                <!-- Restore Confirmation Modal -->
                                <div class="modal fade" id="restoreModal" tabindex="-1" role="dialog" aria-labelledby="restoreModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="restoreModalLabel">Confirm Restore</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to restore this student?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                                        <form id="restoreForm" action="unarchive_student.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="student_id" id="restoreStudentId">
                                        <button type="submit" class="btn btn-success">Restore</button>
                                        </form>
                                    </div>
                                    </div>
                                </div>
                                </div>
                                <script>
                                    $(document).ready(function() {
                                        // Show the success alert if session contains success message
                                        if (<?php echo isset($_SESSION['restore_success']) ? 'true' : 'false'; ?>) {
                                            $('#successAlert').show();
                                            // Optionally, hide it after a few seconds
                                            setTimeout(function() {
                                                $('#successAlert').fadeOut();
                                            }, 5000);
                                            // Unset session success message after displaying
                                            <?php unset($_SESSION['restore_success']); ?>
                                        }

                                        // When the restore button is clicked, set the student ID in the hidden field of the form
                                        $('.restore-btn').on('click', function() {
                                            var studentId = $(this).data('id');
                                            $('#restoreStudentId').val(studentId);
                                        });
                                    });
                                </script>
                            </td>
                            <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                // Select all restore buttons
                                const restoreButtons = document.querySelectorAll('.restore-btn');

                                restoreButtons.forEach(button => {
                                button.addEventListener('click', function () {
                                    const studentId = this.getAttribute('data-id'); // Get the student ID
                                    const restoreInput = document.getElementById('restoreStudentId');

                                    // Set the value of the hidden input to the student's ID
                                    restoreInput.value = studentId;
                                });
                                });
                            });
                            </script>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
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

<!-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->


<!-- jQuery CDN - Slim version (=without AJAX) -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<!-- Popper.JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
<!-- jQuery Custom Scroller CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>

<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
    // Initialize custom scrollbar if needed
    if ($.fn.mCustomScrollbar) {
        $("#sidebar").mCustomScrollbar({
            theme: "minimal"
        });
    }

    // Toggle sidebar collapse
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar, #content').toggleClass('active'); // Ensure 'active' class is handled properly

        // Toggle any Bootstrap collapse elements inside
        $('.collapse.in').toggleClass('in');

        // Handle aria-expanded for accessibility
        $('a[aria-expanded=true]').attr('aria-expanded', 'false');
    });
});

    // auto-dismiss script
    $(document).ready(function() {
        setTimeout(function() {
            $('.custom-alert').fadeOut('slow', function() {
                $(this).alert('close');
            });
        }, 4000); // 3 seconds before auto-dismiss
    });

    $(document).ready(function() {
    // Function to show pop-up
    function showPopup(message, isSuccess) {
        var popup = $('<div class="custom-alert"></div>');
        popup.text(message);
        popup.addClass(isSuccess ? 'custom-alert-success' : 'custom-alert-danger');
        $('body').append(popup);
        popup.fadeIn().delay(3000).fadeOut(function() {
            $(this).remove(); // Remove the pop-up from DOM after fade-out
        });
    }

    // Check for messages and show pop-ups
    <?php if ($successMessage): ?>
        showPopup("<?php echo addslashes($successMessage); ?>", true);
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        showPopup("<?php echo addslashes($errorMessage); ?>", false);
    <?php endif; ?>
    });

    function deleteStudent(studentId) {
    if (confirm('Are you sure you want to delete this student?')) {
        fetch(`delete_student.php?student_id=${studentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Remove the student row from the table or refresh the page
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while trying to delete the student.');
            });
    }
}

document.getElementById('printButton').addEventListener('click', function() {
    // Show the header only for printing
    document.getElementById('printHeader').style.display = 'block';
    
    // Trigger the print dialog
    window.print();
    
    // Hide the header after printing
    document.getElementById('printHeader').style.display = 'none';
});

$(document).ready(function() {
    // Toggle the search form display
    $('#search-toggle').click(function() {
        $('#search-form').toggleClass('d-none');
    });

    // Show and hide the success message overlay
    const overlay = $('#message-overlay');
    const message = $('#success-message').text().trim();

    if (message) {
        overlay.show();
        setTimeout(function() {
            overlay.hide();
        }, 5000);
    }

    $('#close-overlay').click(function() {
        overlay.hide();
    });

    // Logic for populating the modal on opening
    $('#statusModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var studentName = button.data('student-name');
        var studentNumber = button.data('student-number');
        var clearanceStatus = button.data('clearance-status');

        var modal = $(this);
        modal.find('.modal-body #studentName').text(studentName);
        modal.find('.modal-body #studentNumber').text(studentNumber);
        modal.find('.modal-body #clearanceStatus').val(clearanceStatus);
    });

    // Clear modal data on close
    $('#statusModal').on('hidden.bs.modal', function() {
        var modal = $(this);
        modal.find('.modal-body #studentName').text('');
        modal.find('.modal-body #studentNumber').text('');
        modal.find('.modal-body #clearanceStatus').val('complete');
        modal.find('input[type="checkbox"]').prop('checked', false);
    });

    // Remove event listeners that might conflict
    overlay.off('click').click(function() {
        $(this).hide();
    });

    // QR Code modal display
    $('.qr-link').click(function() {
        var qrCodeSrc = $(this).data('qrcode');
        $('#qrModalImage').attr('src', qrCodeSrc);
        $('#qrDownloadLink').attr('href', qrCodeSrc);
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

<script>
$('#importStudentsForm').on('submit', function(e) {
    e.preventDefault();
    
    $('#loadingOverlay').show();
    
    var formData = new FormData(this);
    formData.append('status', $('input[name="importStatus"]:checked').val());
    
    $.ajax({
        url: 'import_students.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#loadingOverlay').hide();
            
            var successPopup = $('<div>', {
                class: 'alert alert-success custom-import-alert',
                css: {
                    'position': 'fixed',
                    'top': '20px',
                    'left': '50%',
                    'transform': 'translateX(-50%)',
                    'z-index': '9999',
                    'padding': '20px 40px',
                    'border-radius': '10px',
                    'background-color': '#4CAF50',
                    'color': 'white',
                    'box-shadow': '0 4px 8px rgba(0,0,0,0.2)',
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '10px',
                    'font-size': '16px'
                }
            }).html(`
                <div style="display: flex; align-items: center; gap: 15px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    <div>
                        <strong>Success!</strong>
                        <div>${response}</div>
                    </div>
                </div>
            `);

            $('body').append(successPopup);

            setTimeout(function() {
    successPopup.fadeOut('slow', function() {
        $(this).remove();
        location.reload();
    });
}, 1000);
        },
        error: function(xhr, status, error) {
            $('#loadingOverlay').hide();
            alert('Import failed. Please try again.');
            console.error(error);
        }
    });
});



// Hide error message when a status is selected
$('input[name="importStatus"]').on('change', function() {
    $('#statusError').hide();
});



$('#archivedStudentsModal').on('show.bs.modal', function (e) {
    $.ajax({
        url: 'get_archived_students.php',
        method: 'GET',
        success: function(data) {
            $('#archivedStudentsModal .modal-body').html(data);
            
            // Re-bind click event to unarchive buttons
            bindUnarchiveButtons();
        },
        error: function() {
            $('#archivedStudentsModal .modal-body').html('Error loading archived students.');
        }
    });
});

function bindUnarchiveButtons() {
    $('.unarchive-btn').on('click', function() {
        var studentId = $(this).data('student-id');
        unarchiveStudent(studentId);
    });
}

function unarchiveStudent(studentId) {
    console.log("Unarchiving student with ID:", studentId); // Debugging line
    $.ajax({
        url: 'unarchive_student.php',
        method: 'POST',
        data: { student_id: studentId },
        success: function(response) {
            console.log("Response from unarchive_student.php:", response); // Debugging line
            var result = JSON.parse(response);
            if (result.success) {
                alert(result.message);
                // Remove the row from the table
                $('button[data-student-id="' + studentId + '"]').closest('tr').remove();
            } else {
                alert('Error: ' + result.message);
            }
        },
        error: function() {
            alert('An error occurred while trying to unarchive the student.');
        }
    });
}

    
    function toggleDropdown(link) {
    const submenu = document.getElementById('studentSubmenu');
    
    // Toggle active class on the main menu link
    link.classList.toggle('active');

    // Check if submenu is currently shown
    if (submenu.classList.contains('show')) {
        // If submenu is shown, just return to prevent closing
        return;
    } else {
        // If submenu is hidden, show it
        submenu.classList.add('show');
    }
}
$(document).ready(function () {
    // Handle submenu toggle
    $('.dropdown-toggle').on('click', function () {
        $(this).next('.collapse').collapse('toggle');
    });
    
    // Highlight active submenu item
    const currentPage = window.location.pathname.split('/').pop();
    $(`#studentSubmenu a[href="${currentPage}"]`).addClass('active');
});


</script>
    <footer id="printFooter" style="display: none;">
        <hr>
        <p style="text-align: center;">Prepared by:</p><br>
        <p style="text-align: center;">Prepared to:</p>
        <!-- <p style="text-align: center;">Page <span class="page-number"></span></p> -->
    </footer>
</body>
</html>


