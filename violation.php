<?php
session_start();
date_default_timezone_set('Asia/Manila');

require 'dbconfig.php';

// Redirect unauthorized users
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin', 'staff', 'admin_cs', 'admin_csd', 'admin_pc'])) {
    header('Location: index.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    // Fetch total records for pagination
    $itemsPerPage = 10;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($current_page - 1) * $itemsPerPage;

    // Count total violations for pagination
    $countQuery = $pdo->query("SELECT COUNT(*) FROM violations WHERE is_archived = 0");
    $totalItems = $countQuery->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);

 // Modify the GROUP BY query to respect individual violation statuses
$stmt = $pdo->prepare("SELECT
    CASE 
        WHEN type_of_violation = 'major' THEN 1
        ELSE COUNT(*)
    END AS count,
    full_name,
    v.phone_number,
    v.email,
    year_and_section,
    program.program_name,
    type_of_violation,
    CASE 
        WHEN type_of_violation = 'major' THEN full_info
        ELSE GROUP_CONCAT(DISTINCT full_info ORDER BY v.id SEPARATOR ', ')
    END AS full_info,
    v.id,
    v.status,
    GROUP_CONCAT(DISTINCT v.id) AS group_ids,
    v.created_at,
    v.updated_at,
    v.ongoing_timestamp,
    v.scheduled_timestamp,
    v.completed_timestamp,
    MAX(v.offense_count) as offense_count,
    v.reported_by  /* Add this line */
FROM violations v
JOIN program ON v.program_id = program.program_id
WHERE v.is_archived = 0
GROUP BY 
    full_name, 
    CASE 
        WHEN type_of_violation = 'major' THEN v.id
        ELSE type_of_violation 
    END,
    v.program_id, 
    year_and_section,
    v.created_at,
    v.phone_number,
    v.email,
    v.id,
    v.status,
    v.reported_by  /* Add this line */
ORDER BY v.created_at DESC
LIMIT :limit OFFSET :offset");




$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

    $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$noResultMessage = (empty($violations)) ? 'No result.' : '';

$fullName = $_POST['full_name'] ?? $_GET['full_name'] ?? '';
$yearAndSection = $_POST['year_and_section'] ?? $_GET['year_and_section'] ?? '';

// Check if the student exists and update their year and section
$checkStudentStmt = $pdo->prepare("SELECT id, year_and_section FROM violations WHERE full_name = :full_name ORDER BY id DESC LIMIT 1");
$checkStudentStmt->execute([':full_name' => $fullName]);
$existingStudent = $checkStudentStmt->fetch(PDO::FETCH_ASSOC);

if ($existingStudent) {
    // Update year and section for existing student if it's different
    if ($existingStudent['year_and_section'] != $yearAndSection) {
        $updateStmt = $pdo->prepare("UPDATE violations SET year_and_section = :year_and_section WHERE full_name = :full_name");
        $updateStmt->execute([
            ':year_and_section' => $yearAndSection,
            ':full_name' => $fullName
        ]);
    }
}

$stmt = $pdo->query("
        SELECT
            COUNT(*) AS count,
            full_name,
            year_and_section,
            program.program_name,
            type_of_violation,
            GROUP_CONCAT(DISTINCT full_info SEPARATOR ' ') AS full_info,
            MAX(status) AS status,
            GROUP_CONCAT(DISTINCT violations.id) AS group_ids
        FROM violations
        JOIN program ON violations.program_id = program.program_id
        WHERE is_archived = 0
        GROUP BY full_name, type_of_violation, violations.program_id
    ");
$violations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$violations = $violations ?: [];

// Pagination for archived violations
$archivedItemsPerPage = 5;
$archivedCurrentPage = isset($_GET['archived_page']) ? (int)$_GET['archived_page'] : 1;
$archivedOffset = ($archivedCurrentPage - 1) * $archivedItemsPerPage;

// Count total records for the archived violations
$countArchivedQuery = $pdo->query("SELECT COUNT(*) FROM violations WHERE is_archived = 1");
$totalArchivedItems = $countArchivedQuery->fetchColumn();
$totalArchivedPages = ceil($totalArchivedItems / $archivedItemsPerPage);

// Fetch archived violations with pagination


// Directly output session messages for debugging
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
} else {
    $successMessage = '';
}

if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
} else {
    $errorMessage = '';
}






$violations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo->query("SELECT v.*, p.program_name 
FROM violations v
JOIN program p ON v.program_id = p.program_id
WHERE v.is_archived = 0
ORDER BY v.created_at DESC");

$multipleViolations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$_SESSION['multiple_violations'] = $multipleViolations;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    
    // Fetch data from multiple_violations
    $stmt = $pdo->query("SELECT id, program_related, y_and_s, type, info, student_names, created_at, status 
    FROM multiple_violations 
    WHERE is_archived = 0
    ORDER BY created_at DESC");
    
    $multipleViolations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$whereClause = "WHERE violations.is_archived = 0";

if ($search) {
    $whereClause .= " AND (
        full_name LIKE :search OR
        program.program_name LIKE :search OR
        type_of_violation LIKE :search OR
        year_and_section LIKE :search
    )";
}

$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS count,
        full_name,
        MAX(year_and_section) AS year_and_section,
        program.program_name,
        type_of_violation,
        GROUP_CONCAT(DISTINCT full_info SEPARATOR ' ') AS full_info,
        MAX(status) AS status,
        GROUP_CONCAT(DISTINCT violations.id) AS group_ids
    FROM violations
    JOIN program ON violations.program_id = program.program_id
    $whereClause
    GROUP BY full_name, type_of_violation, violations.program_id
    LIMIT :limit OFFSET :offset
");

if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$violations = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Capture the filter from the dropdown
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all'; // Default to 'all'

// Base WHERE clause
$whereClause = "WHERE violations.is_archived = 0";

// Add search criteria if applicable
if ($search) {
    $whereClause .= " AND (
        full_name LIKE :search OR
        program.program_name LIKE :search OR
        type_of_violation LIKE :search OR
        year_and_section LIKE :search
    )";
}

// Add status filter criteria
if ($statusFilter === 'completed') {
    $whereClause .= " AND status = 'Completed'";
} elseif ($statusFilter === 'ongoing') {
    $whereClause .= " AND status = 'Ongoing'";
} elseif ($statusFilter === 'scheduled') {
    $whereClause .= " AND status = 'Scheduled'";
}

// Prepare the main query
$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS count,
        full_name,
        MAX(year_and_section) AS year_and_section,
        program.program_name,
        type_of_violation,
        GROUP_CONCAT(DISTINCT full_info SEPARATOR ' ') AS full_info,
        MAX(status) AS status,
        GROUP_CONCAT(DISTINCT violations.id) AS group_ids
    FROM violations
    JOIN program ON violations.program_id = program.program_id
    $whereClause
    GROUP BY full_name, type_of_violation, violations.program_id
    LIMIT :limit OFFSET :offset
");




// Bind parameters
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$sql = "UPDATE violations 
        SET status = :new_status,
        ongoing_timestamp = CASE WHEN :new_status = 'Ongoing' THEN NOW() ELSE ongoing_timestamp END,
        scheduled_timestamp = CASE WHEN :new_status = 'Scheduled' THEN NOW() ELSE scheduled_timestamp END,
        completed_timestamp = CASE WHEN :new_status = 'Completed' THEN NOW() ELSE completed_timestamp END
        WHERE id = :violation_id";

$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$violations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "ALTER TABLE violations 
        ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";

// Then modify your violation insert query to include timestamps
// When adding a new violation, always set the initial status to "Ongoing"
$sql = "INSERT INTO violations (
    full_name, 
    year_and_section,
    program_id,
    type_of_violation,
    full_info,
    status,
    phone_number,
    email,
    created_at,
    updated_at,
    ongoing_timestamp
) VALUES (?, ?, ?, ?, ?, 'Ongoing', ?, ?, NOW(), NOW(), NOW())";

?>





<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Violations</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
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
    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }

    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

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
        .btn-remove-outline {
            border: none;
            box-shadow: none;
        }.btn-remove-outline:hover{
            background-color: transparent;
        }
        th {
            text-transform: uppercase;
        }
        .btn.btn-primary {
        background-color: transparent; /* Remove background color */
        border: none;
        padding: 0; /* Adjust padding as needed */
        display: inline-flex; /* Ensures inline display */
        align-items: center; /* Aligns content vertically */
        color: green;
        }
        .btn.btn-primary svg {
        fill: blue; /* Set SVG icon fill color */
        margin-right: 10px; /* Adjust spacing between icon and text */
        }

        @media print {
    #sidebar, .pagination-container, .btn, .input-group-append {
        display: none;
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

@media print {
    /* Hide the menu text "Students" */
    .menu-text,
    .page-link,
    .input-group,
    .badge {
        display: none !important;
    }

    /* Main table column hiding */
    #mainTable thead th:nth-child(3), /* Main table Info */
    #mainTable thead th:nth-child(7), /* Main table Action */
    #mainTable thead th:nth-child(12),
    #mainTable tbody td:nth-child(3), /* Main table Info */
    #mainTable tbody td:nth-child(7),  /* Main table Action */ 
    #mainTable tbody td:nth-child(12)
    {
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
            margin-top:-50px;
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
        .alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 15px;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            opacity: 1;
            transition: opacity 0.5s ease, visibility 0.5s ease;
            visibility: hidden;
        }
        .alert-show {
            visibility: visible;
        }
        .alert-success {
            background-color: #28a745;
        }
        .alert-danger {
            background-color: #dc3545;
        }
        .custom-margin {
            margin-left: -20px; /* Adjust right margin */
        }
        .margin{
            margin-right: 250px;
        }
        #disableBtn {
            cursor:not-allowed;
        }
        .dark-text {
            color: #333333; /* Darker shade of gray */
        }
        .view-btn {
            background-color:rgb(32, 117, 245);
            border: none;
        }
        .view-btn:hover {
            background-color:rgb(30, 88, 212);
        }
        .modal-body {
            color: #242424 !important; /* Darker text color */
            font-weight: normal !important; /* Ensures text is not bold */
            font-family: Arial, sans-serif !important;
        }
        .modal-body strong {
            color: #333 !important; /* Darker text color for paragraph tags */
            font-family: Arial, sans-serif !important;
            font-weight: bold !important;
        }
        .modal-body p {
            color: #333 !important; /* Darker text color for paragraph tags */
            font-family: Arial, sans-serif !important;
        }
        .name {
            color:rgb(70, 70, 70) !important;
        }
        .name {
            color: white !important;
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
    <a href="#violationSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link active">
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
    <a href="#violationSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link active">
        Violations
    </a>
    <ul class="collapse list-unstyled" id="violationSubmenu">
        <li>
            <a href="violation.php?status=ongoing" class="nav-link active">Ongoing</a>
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
    <a href="#violationSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link active">
        Violations
    </a>
    <ul class="collapse list-unstyled" id="violationSubmenu">
        <li>
            <a href="violation.php?status=ongoing" class="nav-link active">Ongoing</a>
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
        
        <!-- Page Content -->
        <div id="content">
            <div class="menu-header">
                <!-- Menu header content -->
                <button type="button" id="sidebarCollapse" class="btn menu-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                    </svg>
                </button>
                <span class="menu-text">Violation</span>
            </div>
            <div id="students-section" class="col py-3"> 
            <div id="students-section" style="padding-top:6px; padding-left:10px">
                <!-- Add Violation button -->
                <div class="d-flex justify-content-between align-items-center mb-1">
    <div class="d-flex align-items-center">

    <a href="#" class="btn btn-outline-success mb-3 mr-1" id="directAddStudentBtn">
    <span class="d-flex align-items-center justify-content-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-person-add mr-2" viewBox="0 0 16 16">
            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
            <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
        </svg> 
        Add Student
    </span>
</a>

<!-- Add Student Choice Modal -->
<div class="modal fade" id="addStudentChoiceModal" tabindex="-1" role="dialog" aria-labelledby="addStudentChoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentChoiceModalLabel">Choose an option</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body d-flex justify-content-center">
                <button type="button" class="btn btn-outline-success mr-2" id="addSingleStudentBtn">Add Single Student</button>
                <!-- Commented out multiple students option
                <button type="button" class="btn btn-outline-info" id="addMultipleStudentsBtn">Add Multiple Students</button>
                -->
            </div>
        </div>
    </div>
</div>


        <!-- Add Student Button -->
         <!--
        <a href="add_violation.php" class="btn btn-outline-success mb-3 mr-1">
            <span class="d-flex align-items-center justify-content-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-person-add mr-2" viewBox="0 0 16 16">
                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                    <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                </svg> 
                Add Student
            </span>
        </a>
    -->
        <form action="" method="GET" class="mb-3">
    <div class="input-group">
        <input type="text" class="form-control" placeholder="Search" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <div class="input-group-append">
            <button class="btn btn-outline-primary" type="submit">Search</button>
            <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
                <a href="violation.php" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </div>
</form>
        <!-- Multiple Violations Button -->
       
    </div>

    <div class="d-flex align-items-center">
        <!-- Toggle Table View Button -->

        <!-- Violation Type Dropdown -->
    <!-- Status Filter Dropdown -->
<!-- <div class="nav-item dropdown mb-3">
    <button class="btn btn-info dropdown-toggle mr-1" type="button" id="statusFilterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Filter by Status
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-down-fill" viewBox="0 0 16 16">
            <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
        </svg>
    </button>
    <div class="dropdown-menu" aria-labelledby="statusFilterDropdown">
        <a class="dropdown-item" href="#" id="filterAll">Show All</a>
        <a class="dropdown-item" href="#" id="filterCompleted">Show Completed</a>
        <a class="dropdown-item" href="#" id="filterOngoing">Show Ongoing</a>
        <a class="dropdown-item" href="#" id="filterScheduled">Show Scheduled</a>
    </div>
</div> -->
        <!-- Print Button -->
        <?php if ($_SESSION['role'] == 'superadmin'): ?>
            <button id="showArchivedModal" data-toggle="tooltip" data-placement="top" title="Archived Students" class="btn btn-warning mb-3 mr-1" style="border:none;">
                Violations
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                    <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </button>
         
        <?php endif; ?>
        <button id="printButton" class="btn btn-success mb-3" style="background: #e48189; border:none;">
            Print
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
            </svg>
        </button>
        <script>
            // Print functionality
                document.getElementById('printButton').addEventListener('click', function() {
                // Show the header only for printing
                document.getElementById('printHeader').style.display = 'block';
                
                // Trigger the print dialog
                window.print();
                
                // Hide the header after printing
                document.getElementById('printHeader').style.display = 'none';
            });
            </script>

        <!-- <?php if ($_SESSION['role'] == 'superadmin'): ?>
            <button id="showArchivedModal" class="btn btn-secondary mb-3" style="background: #4c704c; border:none;">
                Archived Violations
            </button>
        <?php endif; ?>

        <button id="showArchivedMultipleModal" class="btn btn-secondary mb-3" style="background: #4c704c; border:none;">
            Archived Group Violations
        </button> -->
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
                        <h3>Cavite State University</h3>
                        <h4>Carmona Campus</h4>
                        <p class="ngi">Market Road, Carmona, Cavite <br>
                        (046)487-6328/cvsucarmona@cvsu.edu.ph <br>
                        www.cvsu.edu.ph</p>
                    </div>
                    <!-- Empty column to push text to the center -->
                    <div class="col-md-2"></div>
                </div>
            </div>
            <h1 id="printHeader" class="d-none d-print-block text-center mt-4 mb-4">Violation Records</h1>
        </header>
        <table id="mainTable" class="table table-hover table-bordered">
      <thead>
    <tr>
        <th>Full Name</th>
        <th>Phone Number</th> 
        <th>Email</th>
        <th>Year & Section</th>
        <th>Program</th>
        <th>Type of Violation</th>
        <th>Details</th>
        <th>Status</th>
        <th>Offense Number</th>
        <th>Created</th>
        <th>Last Updated</th>
        <th>Reported By</th>  <!-- Add this new column -->
        <th>Action</th>
    </tr>
</thead>


<tbody>

    <?php foreach ($violations as $violation): ?>
        <tr class="violation-row <?= strtolower($violation['type_of_violation']) ?>-violation">
     
        <?php
    if ($violation['type_of_violation'] == 'minor' && $violation['count'] >= 2) {
        // First delete any existing counseling sessions without phone numbers
        $deleteStmt = $pdo->prepare("
            DELETE FROM counseling_sessions 
            WHERE student_full_name = :full_name 
            AND (phone_number IS NULL OR phone_number = '')
        ");
        $deleteStmt->execute([':full_name' => $violation['full_name']]);
    
        // Get phone number, email, and violation details in one query
        $stmt = $pdo->prepare("
            SELECT 
                v.full_name,
                v.year_and_section,
                v.phone_number,
                v.email,
                GROUP_CONCAT(DISTINCT full_info SEPARATOR ', ') as violation_details
            FROM violations v 
            WHERE full_name = :full_name 
            AND type_of_violation = 'minor'
            AND phone_number IS NOT NULL
            GROUP BY full_name, year_and_section, phone_number, email
            LIMIT 1
        ");
        $stmt->execute([':full_name' => $violation['full_name']]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Check for existing valid counseling session
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM counseling_sessions 
            WHERE student_full_name = :full_name 
            AND phone_number IS NOT NULL
        ");
        $checkStmt->execute([':full_name' => $violation['full_name']]);
    
        // Only insert if no valid record exists
        if ($checkStmt->fetchColumn() == 0 && $details) {
            $counselingStmt = $pdo->prepare("
                INSERT INTO counseling_sessions 
                (student_full_name, year_and_section, with_violation, details, status, phone_number, email) 
                VALUES 
                (:full_name, :year_section, 1, :details, NULL, :phone_number, :email)
            ");
    
            $counselingStmt->execute([
                ':full_name' => $details['full_name'],
                ':year_section' => $details['year_and_section'],
                ':details' => $details['violation_details'],
                ':phone_number' => $details['phone_number'],
                ':email' => $details['email']
            ]);
        }
    }
    

    if ($violation['type_of_violation'] == 'major') {
        // First delete any existing counseling sessions without phone numbers
        $deleteStmt = $pdo->prepare("
            DELETE FROM counseling_sessions 
            WHERE student_full_name = :full_name 
            AND (phone_number IS NULL OR phone_number = '')
        ");
        $deleteStmt->execute([':full_name' => $violation['full_name']]);
    
        // Get phone number, email, and violation details in one query
        $stmt = $pdo->prepare("
            SELECT 
                v.full_name,
                v.year_and_section,
                v.phone_number,
                v.email,
                GROUP_CONCAT(DISTINCT full_info SEPARATOR ', ') as violation_details
            FROM violations v 
            WHERE full_name = :full_name 
            AND type_of_violation = 'major'
            AND phone_number IS NOT NULL
            GROUP BY full_name, year_and_section, phone_number, email
            LIMIT 1
        ");
        $stmt->execute([':full_name' => $violation['full_name']]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Check for existing valid counseling session
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM counseling_sessions 
            WHERE student_full_name = :full_name 
            AND phone_number IS NOT NULL
        ");
        $checkStmt->execute([':full_name' => $violation['full_name']]);
    
        // Only insert if no valid record exists
        if ($checkStmt->fetchColumn() == 0 && $details) {
            $counselingStmt = $pdo->prepare("
                INSERT INTO counseling_sessions 
                (student_full_name, year_and_section, with_violation, details, status, phone_number, email) 
                VALUES 
                (:full_name, :year_section, 1, :details, NULL, :phone_number, :email)
            ");
    
            $counselingStmt->execute([
                ':full_name' => $details['full_name'],
                ':year_section' => $details['year_and_section'],
                ':details' => $details['violation_details'],
                ':phone_number' => $details['phone_number'],
                ':email' => $details['email']
            ]);
        }
    }
    
        
        ?>
    </tr>
 
   

    <td>
    <?php
    // Get creation timestamps for violations
$timestampQuery = $pdo->prepare("
SELECT v.id, v.created_at 
FROM violations v 
WHERE v.is_archived = 0
");
$timestampQuery->execute();
$creationTimes = $timestampQuery->fetchAll(PDO::FETCH_KEY_PAIR);


    $fullName = htmlspecialchars($violation['full_name']);
    $fullName = str_replace([' N/A ', 'N/A'], '', $fullName);
    echo trim($fullName);
    
    // Get violation IDs from group_ids
    $violationIds = explode(',', $violation['group_ids']);
    $latestCreation = null;
    
    // Find the most recent creation time
    foreach ($violationIds as $id) {
        if (isset($creationTimes[$id])) {
            $currentCreation = strtotime($creationTimes[$id]);
            if (!$latestCreation || $currentCreation > $latestCreation) {
                $latestCreation = $currentCreation;
            }
        }
    }
    
    // Check if violation is new (less than 24 hours old)
    if ($latestCreation && (time() - $latestCreation) < 86400) {
        echo '<span class="badge badge-success" 
              style="background-color: #28a745; 
              color: white; 
              padding: 4px 8px; 
              border-radius: 12px; 
              font-size: 12px; 
              margin-left: 5px;">NEW</span>';
    }
    ?>
</td>


<td>
        <?php
        // Fetch phone number directly from database for this violation
        $phoneStmt = $pdo->prepare("SELECT phone_number FROM violations WHERE full_name = ? LIMIT 1");
        $phoneStmt->execute([$violation['full_name']]);
        $phoneNumber = $phoneStmt->fetchColumn();
        echo $phoneNumber ? htmlspecialchars($phoneNumber) : 'N/A';
        ?>
    </td>

    <td>
    <?php
    // Fetch email directly from database for this violation
    $emailStmt = $pdo->prepare("SELECT email FROM violations WHERE full_name = ? LIMIT 1");
    $emailStmt->execute([$violation['full_name']]);
    $email = $emailStmt->fetchColumn();
    
    if ($email) {
        // Show eye icon button that opens modal
        echo '<a href="#" class="view-btn btn btn-outline-primary" data-toggle="modal" data-target="#emailDetailModal' . md5($violation['full_name']) . '">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" class="bi bi-eye" viewBox="0 0 16 16">
                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
            </svg>
        </a>';
        
        // Add the modal for this email - showing just the email
        echo '<div class="modal fade" id="emailDetailModal' . md5($violation['full_name']) . '" tabindex="-1" aria-labelledby="emailDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-white bg-info">
                        <h5 class="modal-title" id="emailDetailModalLabel">Email Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>';
    } else {
        // Show disabled button with eye icon
        echo '<button class="btn btn-secondary" disabled title="No email available">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" class="bi bi-eye" viewBox="0 0 16 16">
            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
        </svg>
    </button>';
    }
    ?>
</td>



   <td><?= htmlspecialchars($violation['year_and_section']) ?></td>
            <td><?= htmlspecialchars($violation['program_name']) ?></td>
            <td><?= htmlspecialchars($violation['type_of_violation']) ?></td>
            
    <!-- Trigger button for modal -->
    <td>
        


    <div class="d-flex align-items-center">
        <button type="button" class="view-btn btn btn-outline-primary btn-md d-flex align-items-center" data-toggle="modal" data-target="#violationModal<?= md5($violation['full_name'] . $violation['group_ids']); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" class="bi bi-eye" viewBox="0 0 16 16">
                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
            </svg>
        </button>
        <span class="ml-2">(<?= $violation['count'] ?>)</span>
    </div>


</td>
           
           <!-- Modal -->
           <div class="modal fade" id="violationModal<?= md5($violation['full_name'] . $violation['group_ids']); ?>" tabindex="-1" aria-labelledby="violationModalLabel<?= md5($violation['full_name'] . $violation['group_ids']); ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-black">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="violationModalLabel<?= md5($violation['full_name'] . $violation['group_ids']); ?>">Violation Details for <strong class="name"><?= htmlspecialchars($violation['full_name']) ?></strong></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
   <div class="modal-body">
    <?php
    $violationIds = explode(',', $violation['group_ids']);
    
    // First, let's gather all offenses for this student to count occurrences
    $offenseCounts = [];
    $studentName = $violation['full_name'];
    
    // Query to get all violations for this student
    $allViolationsStmt = $pdo->prepare("
        SELECT full_info, offense_count 
        FROM violations 
        WHERE full_name = ? 
        ORDER BY id ASC
    ");
    $allViolationsStmt->execute([$studentName]);
    $allViolations = $allViolationsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count occurrences of each violation type
    foreach ($allViolations as $v) {
        $offenseInfo = trim($v['full_info']);
        if (!isset($offenseCounts[$offenseInfo])) {
            $offenseCounts[$offenseInfo] = 1;
        } else {
            $offenseCounts[$offenseInfo]++;
        }
    }
    
    // Now display each violation with occurrence indicator
    foreach ($violationIds as $id) {
        $stmt = $pdo->prepare("SELECT full_info, status, action_perform, offense_count FROM violations WHERE id = ?");
        $stmt->execute([$id]);
        $violationInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($violationInfo) {
            echo "<p class='dark-text'><strong>Offense Count:</strong> " . htmlspecialchars($violationInfo['offense_count']) . "</p>";
            
            // Get the occurrence number for this violation
            $offenseInfo = trim($violationInfo['full_info']);
            $occurrenceNumber = $offenseCounts[$offenseInfo];
            
            // Display the violation with occurrence indicator if it's repeated
            $occurrenceText = '';
            if ($occurrenceNumber > 1) {
                // Convert number to ordinal (2 -> 2nd, 3 -> 3rd, etc.)
                $ordinal = $occurrenceNumber . ($occurrenceNumber == 1 ? 'st' : 
                                              ($occurrenceNumber == 2 ? 'nd' : 
                                              ($occurrenceNumber == 3 ? 'rd' : 'th')));
                $occurrenceText = " <span style='color: #d9534f;'>({$ordinal})</span>";
            }
            
            echo "<p class='dark-text'><strong>Violation:</strong> " . 
                 nl2br(htmlspecialchars($violationInfo['full_info'])) . 
                 $occurrenceText . "</p>";
                 
            echo "<p class='dark-text'><strong>Action Performed:</strong> " . 
                 nl2br(htmlspecialchars($violationInfo['action_perform'])) . "</p>";
                 
            echo "<p class='dark-text'><strong>Status:</strong> " . 
                 htmlspecialchars($violationInfo['status']) . "</p>";
                 
            
        }
    }
    ?>
</div>
<div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                    </div>

</td>
<td>
    <span style="color: <?= 
        $violation['status'] == 'Ongoing' ? '#e69500' : 
        ($violation['status'] == 'Scheduled' ? 'blue' : 'green'); ?>">
        <strong><?= htmlspecialchars($violation['status']) ?></strong>
    </span>
</td>
<td>
    <?php
    // Get all violation IDs for this group
    $violationIds = explode(',', $violation['group_ids']);
    $firstId = trim($violationIds[0]);
    
    // Get the student name and violation type
    $studentName = $violation['full_name'];
    $violationType = $violation['type_of_violation'];
    
    // Get the offense count from the first violation
    $offenseStmt = $pdo->prepare("SELECT offense_count FROM violations WHERE id = ?");
    $offenseStmt->execute([$firstId]);
    $offenseCount = $offenseStmt->fetchColumn();
    
    // Display the base offense count
    echo htmlspecialchars(!empty($offenseCount) ? $offenseCount : $violation['count']);
    
    // For minor violations, check if this is a repeat offense
    if ($violationType == 'minor') {
        // Count how many groups of minor violations this student has had
        $minorGroupsStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT DATE(created_at)) 
            FROM violations 
            WHERE full_name = ? 
            AND type_of_violation = 'minor'
            AND created_at <= (SELECT created_at FROM violations WHERE id = ?)
        ");
        $minorGroupsStmt->execute([$studentName, $firstId]);
        $occurrenceNumber = $minorGroupsStmt->fetchColumn();
        
        // Display occurrence indicator if it's a repeat offense
        if ($occurrenceNumber > 1) {
            // Convert number to ordinal
            $ordinal = $occurrenceNumber . ($occurrenceNumber == 1 ? 'st' : 
                                          ($occurrenceNumber == 2 ? 'nd' : 
                                          ($occurrenceNumber == 3 ? 'rd' : 'th')));
            echo " <span style='color: #d9534f;'>({$ordinal})</span>";
        }
    }
    // For major violations, check if this is a repeat offense
    elseif ($violationType == 'major') {
        // Count how many major violations this student has had
        $majorCountStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM violations 
            WHERE full_name = ? 
            AND type_of_violation = 'major'
            AND created_at <= (SELECT created_at FROM violations WHERE id = ?)
        ");
        $majorCountStmt->execute([$studentName, $firstId]);
        $occurrenceNumber = $majorCountStmt->fetchColumn();
        
        // Display occurrence indicator if it's a repeat offense
        if ($occurrenceNumber > 1) {
            // Convert number to ordinal
            $ordinal = $occurrenceNumber . ($occurrenceNumber == 1 ? 'st' : 
                                          ($occurrenceNumber == 2 ? 'nd' : 
                                          ($occurrenceNumber == 3 ? 'rd' : 'th')));
            echo " <span style='color: #d9534f;'>({$ordinal})</span>";
        }
    }
    ?>
</td>


<td>
    
    <?php 
        $createdAt = !empty($violation['created_at']) ? 
            date('M d, Y h:i A', strtotime($violation['created_at']) . ' Asia/Manila') : 
            date('M d, Y h:i A');
        echo $createdAt;
    ?>
</td>


<td>
    <span style="color: <?= 
        $violation['status'] == 'Ongoing' ? '#e69500' : 
        ($violation['status'] == 'Scheduled' ? 'blue' : 'green'); ?>">
        <strong><?= htmlspecialchars($violation['status']) ?></strong>
        <?php
        switch($violation['status']) {
            case 'Ongoing':
                $timestamp = isset($violation['ongoing_timestamp']) ? date('M d, Y h:i A', strtotime($violation['ongoing_timestamp'])) : date('M d, Y h:i A');
                echo "<br>Since: " . $timestamp;
                break;
            case 'Scheduled':
                $timestamp = isset($violation['scheduled_timestamp']) ? date('M d, Y h:i A', strtotime($violation['scheduled_timestamp'])) : date('M d, Y h:i A');
                echo "<br>At: " . $timestamp;
                break;
            case 'Completed':
                $timestamp = isset($violation['completed_timestamp']) ? date('M d, Y h:i A', strtotime($violation['completed_timestamp'])) : date('M d, Y h:i A');
                echo "<br>On: " . $timestamp;
                break;
        }
        ?>
    </span>
</td>
        
<!-- Add this before the Action column -->
<td>
    <?php
    // Get the first ID from group_ids
    $violationIds = explode(',', $violation['group_ids']);
    $firstId = trim($violationIds[0]);
    
    // Fetch reported_by using the first ID
    $reportedByStmt = $pdo->prepare("SELECT reported_by FROM violations WHERE id = ? LIMIT 1");
    $reportedByStmt->execute([$firstId]);
    $reportedBy = $reportedByStmt->fetchColumn();
    echo htmlspecialchars($reportedBy);
    ?>
</td>



<td class="action-buttons">
    <div class="d-flex justify-content-center">
    <?php
    $violationIds = explode(',', $violation['group_ids']);
    $firstId = trim($violationIds[0]);

    if ($violation['status'] !== 'Completed') {
        echo '<a href="edit_violation.php?id=' . urlencode($firstId) . '" class="btn btn-sm btn-success mr-1" data-toggle="tooltip" data-placement="top" title="Edit this student">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z"/>
            </svg>
        </a>';
    } else {
        echo '<button id="disableBtn" class="btn btn-sm btn-secondary mr-1" disabled>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z"/>
            </svg>
        </button>';
    }
        ?>
        <a href="#" class="btn btn-danger btn-sm"  
            data-toggle="modal" data-target="#archiveConfirmationModal" 
            onclick="setArchiveLink('archive_violation.php?ids=<?= urlencode($violation['group_ids']) ?>')">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                    <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                </svg>
        </a>

    </div>
    <script>
        // Enable Bootstrap tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</td>

            
        </tr>
    <?php endforeach; ?>
</tbody>


</table>

<!-- Archive Confirmation Modal -->
<div class="modal fade" id="archiveConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="archiveConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveConfirmationModalLabel">Confirm Archive</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to archive this student?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmArchiveButton">Yes, proceed</button>
            </div>
        </div>
    </div>
</div>

<div class="pagination-container">
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">

            <!-- First Page Button -->
            <li class="page-item <?php if ($current_page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=1" style="background: <?php echo ($current_page <= 1 ? '#e6d6ff' : '#d0b3ff'); ?>; border:none;">
                    First
                </a>
            </li>

            <!-- Previous Page Button -->
            <li class="page-item <?php if ($current_page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" tabindex="-1" 
                   style="background: <?php echo ($current_page <= 1 ? '#e6d6ff' : '#d0b3ff'); ?>; border:none;">
                   Previous
                </a>
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

            <!-- Next Page Button -->
            <li class="page-item <?php if ($current_page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" 
                   style="background: #886798; border:none; color:#ddd;">
                   Next
                </a>
            </li>

            <!-- Last Page Button -->
            <li class="page-item <?php if ($current_page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $totalPages; ?>" 
                   style="background: #886798; border:none; color:#ddd;">
                   Last
                </a>
            </li>

        </ul>
    </nav>
</div>

</div>



   </div>
   


                                </div>
                        </div>
            </div>


<div class="modal fade" id="archiveConfirmModal" tabindex="-1" role="dialog" aria-labelledby="archiveConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="archiveConfirmModalLabel">Confirm Archive</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to archive this student?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmArchive">Archive</button>
      </div>
    </div>
  </div>
</div>

<!-- archived violation -->
<div class="modal fade" id="archivedMultipleViolationsModal" tabindex="-1" role="dialog" aria-labelledby="archivedMultipleViolationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archivedMultipleViolationsModalLabel">Archived Group Violations</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                require 'dbconfig.php';

                try {
                    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $stmt = $pdo->query("SELECT * FROM multiple_violations WHERE is_archived = 1 ORDER BY created_at DESC");
                    $archivedViolations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo "<table class='table table-striped'>
                            <thead>
                                <tr>
                                    <th>Student Names</th>
                                    <th>Year & Section</th>
                                    <th>Program</th>
                                    <th>Type</th>
                                    <th>Info</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>";

                    foreach ($archivedViolations as $violation) {
                        echo "<tr>
                                <td>" . htmlspecialchars($violation['student_names']) . "</td>
                                <td>" . htmlspecialchars($violation['y_and_s']) . "</td>
                                <td>" . htmlspecialchars($violation['program_related']) . "</td>
                                <td>" . htmlspecialchars($violation['type']) . "</td>
                                <td>" . htmlspecialchars($violation['info']) . "</td>
                                <td>" . htmlspecialchars($violation['created_at']) . "</td>
                                <td>
                                    <button class='btn btn-sm btn-outline-success mb-2 btn-confirm' data-id='" . $violation['id'] . "' data-action='restore'>Restore</button>
                                    <button class='btn btn-outline-danger btn-sm btn-confirm' data-id='" . $violation['id'] . "' data-action='delete'>Delete</button>
                                </td>
                            </tr>";
                    }

                    echo "</tbody></table>";

                } catch(PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>




<!-- Add this modal to your HTML, preferably at the end of the body -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete the selected violation(s)?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
      </div>
    </div>
  </div>
</div>

  <!-- Archived Violations Modal -->
<div class="modal fade" id="archivedViolationsModal" tabindex="-1" role="dialog" aria-labelledby="archivedViolationsModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archived Violations</h5>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Year & Section</th>
                            <th>Program</th>
                            <th>Type</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $archivedStmt = $pdo->query("
                            SELECT v.*, p.program_name 
                            FROM violations v
                            JOIN program p ON v.program_id = p.program_id
                            WHERE v.is_archived = 1
                            ORDER BY v.created_at DESC
                        ");
                        while($archived = $archivedStmt->fetch()) {
                            echo "<tr>
                                <td>" . htmlspecialchars($archived['full_name']) . "</td>
                                <td>" . htmlspecialchars($archived['year_and_section']) . "</td>
                                <td>" . htmlspecialchars($archived['program_name']) . "</td>
                                <td>" . htmlspecialchars($archived['type_of_violation']) . "</td>
                                <td>" . htmlspecialchars($archived['full_info']) . "</td>
                                <td>" . htmlspecialchars($archived['status']) . "</td>
                                <td>
                                    <a href='#' class='btn btn-sm btn-outline-success mb-1 w-100' 
                                       onclick='showConfirmModal(\"restore\", " . $archived['id'] . ")'>Restore</a>
                                    <a href='#' class='btn btn-outline-danger btn-sm w-100' 
                                       onclick='showConfirmModal(\"delete\", " . $archived['id'] . ")'>Delete</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Custom Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage" style="color: #333;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmButton">Yes, proceed</button>
            </div>
        </div>
    </div>
</div>

<!-- Inline JavaScript for custom confirmation modal -->
<script type="text/javascript">
    let actionType = '';
    let violationId = '';

    // Function to show the confirmation modal with the appropriate message
    function showConfirmModal(action, id) {
        actionType = action;
        violationId = id;

        let message = '';
        if (action === 'restore') {
            message = 'Are you sure you want to restore this violation?';
        } else if (action === 'delete') {
            message = 'Are you sure you want to permanently delete this violation?';
        }

        // Set the message in the modal
        document.getElementById('confirmationMessage').innerText = message;

        // Show the custom confirmation modal
        $('#confirmationModal').modal('show');
    }

    // Handle confirmation
    document.getElementById('confirmButton').addEventListener('click', function() {
        if (actionType === 'restore') {
            window.location.href = 'unarchive_violation.php?ids=' + violationId;
        } else if (actionType === 'delete') {
            window.location.href = 'delete_violation.php?ids=' + violationId;
        }

        // Close the modal
        $('#confirmationModal').modal('hide');
    });
</script>

</td>

                            </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>




<script>
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


</script>


<!-- jQuery (needed for Bootstrap JS) -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<!-- Popper.js (required for Bootstrap tooltips and popovers) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>

<!-- Bootstrap JS (use the bundle version to include Popper.js) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- Optional: jQuery Custom Scroller if needed -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>


<script type="text/javascript">
    $(document).ready(function () {
        // Initialize sidebar with custom scrollbar
        $("#sidebar").mCustomScrollbar({
            theme: "minimal"
        });

        // Toggle sidebar functionality
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar, #content').toggleClass('active');
            $('.collapse.in').toggleClass('in');
            $('a[aria-expanded=true]').attr('aria-expanded', 'false');
        });

        // Add violation functionality
        $('#addViolationBtn').click(function () {
            const violationType = $('#violation_type').val();
            const violationDetails = $('#violationDetails').val();

            // AJAX request to add the violation type and details
            $.ajax({
                type: "POST",
                url: "add_violation_type.php",
                data: {
                    violationType: violationType,
                    description: violationDetails
                },
                dataType: "json",
                success: function (response) {
                    alert(response.message);
                    if (response.status === 'success') {
                        location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    console.log("Error:", error);
                    alert("An error occurred while adding the violation.");
                }
            });

            // Close the modal
            $('#addViolationModal').modal('hide');
        });

        $(document).ready(function() {
    // Handle toggling the Violation Menu dropdown
    $('a.dropdown-toggle').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        // Close other dropdowns
        $('.collapse').not($(this).next('.collapse')).removeClass('show');
        
        // Toggle the current dropdown
        $(this).next('.collapse').toggleClass('show');
    });

    // Handle toggling the Show Violations dropdown
    $('#violationTypeDropdown').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();

        // Close other dropdowns
        $('.dropdown-menu').not($(this).siblings('.dropdown-menu')).removeClass('show');
        
        // Toggle the current dropdown
        $(this).siblings('.dropdown-menu').toggleClass('show');
    });

    // Filter table rows based on violation type
    $('.dropdown-menu a').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).toggleClass('active');

        // Filtering logic
        if ($('#showAllBtn').hasClass('active')) {
            $('.violation-row').show();
        } else {
            $('.violation-row').hide();
            if ($('#showMajorBtn').hasClass('active')) {
                $('.major-violation').show();
            }
            if ($('#showMinorBtn').hasClass('active')) {
                $('.minor-violation').show();
            }
        }
    });

    // Close dropdowns when clicking outside
    $(document).on('click', function(event) {
        if (!$(event.target).closest('.dropdown-toggle').length) {
            $('.dropdown-menu').removeClass('show');
            $('.collapse').removeClass('show'); // Close collapse dropdowns too
        }
    });
});




        // Update pagination links when filtering
        function updatePaginationLinks() {
            $('.pagination .page-link').each(function () {
                var href = $(this).attr('href');
                if (href.indexOf('showMajor') === -1 && href.indexOf('showMinor') === -1) {
                    if ($('#showMajorBtn').hasClass('active')) {
                        href += '&showMajor=1';
                    } else if ($('#showMinorBtn').hasClass('active')) {
                        href += '&showMinor=1';
                    }
                    $(this).attr('href', href);
                }
            });
        }

        $('#showAllBtn, #showMajorBtn, #showMinorBtn').click(function () {
            updatePaginationLinks();
        });

        updatePaginationLinks();

        

        // Delete violations functionality
        window.deleteViolations = function (ids) {
            $('#deleteConfirmModal').modal('show');

            $('#confirmDelete').off('click').on('click', function () {
                $.ajax({
                    url: 'delete_violation.php',
                    type: 'GET',
                    data: { ids: ids },
                    dataType: 'json',
                    success: function (response) {
                        $('#deleteConfirmModal').modal('hide');
                        alert(response.message);
                        if (response.status === 'success') {
                            location.reload();
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#deleteConfirmModal').modal('hide');
                        alert("An error occurred while processing your request.");
                    }
                });
            });
        };

        // Show alerts for success or error messages
        function showAlert(type, message) {
            var alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-' + type + ' alert-show';
            alertDiv.innerHTML = message;
            document.body.appendChild(alertDiv);

            setTimeout(function () {
                alertDiv.style.opacity = '0'; // Fade out
                setTimeout(function () {
                    alertDiv.remove(); // Remove from DOM
                }, 500); // Time for fade out transition
            }, 3000); // Time to show alert
        }

        <?php if ($successMessage): ?>
            showAlert('success', '<?php echo addslashes($successMessage); ?>');
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            showAlert('danger', '<?php echo addslashes($errorMessage); ?>');
        <?php endif; ?>
    });
    function confirmLogout() {
    // Show a confirmation dialog
    var confirmation = confirm("Are you sure you want to logout?");
    
    // If the user clicks "OK", return true to proceed with the logout
    // If the user clicks "Cancel", return false to prevent the logout
    return confirmation;
}




window.archiveViolations = function (ids) {
    $('#archiveConfirmModal').modal('show');

    $('#confirmArchive').off('click').on('click', function () {
        $.ajax({
            url: 'archive_violation.php',
            type: 'GET',
            data: { ids: ids },
            dataType: 'json',
            success: function (response) {
                $('#archiveConfirmModal').modal('hide');
                alert(response.message);
                if (response.status === 'success') {
                    location.reload();
                }
            },
            error: function (xhr, status, error) {
                $('#archiveConfirmModal').modal('hide');
                alert("An error occurred while processing your request.");
            }
        });
    });
};
$('#showArchivedModal').click(function() {
            $('#archivedViolationsModal').modal('show');
        });
       



        $(document).ready(function() {
    $('#showArchivedModal').click(function() {
        $('#archivedViolationsModal').modal({
            backdrop: 'static',
            keyboard: false
        });
    });

    $('#archivedViolationsModal').on('show.bs.modal', function (e) {
        $('body').addClass('modal-open');
    });

    $('#archivedViolationsModal').on('hidden.bs.modal', function (e) {
        $('body').removeClass('modal-open');
    });

    $('.close, [data-dismiss="modal"]').click(function() {
        $('#archivedViolationsModal').modal('hide');
    });
});


$(document).ready(function() {
    $('.table tbody tr').click(function() {
        $(this).toggleClass('highlighted-row');
    });
});



$(document).ready(function() {
    $('#toggleTableView').click(function() {
        $('#mainTable, #alternateTable').toggle();
        $(this).text(function(i, text) {
            return text === "Student w/ violation" ? "Group w/ violation" : "Student w/ violation";
        });
    });
});


</script>

<script>
$(document).ready(function() {
    function updateViolationsTable() {
        var programs = [];
        $('input[name="program_id[]"]:checked').each(function() {
            programs.push($(this).next('label').text().trim());
        });
        var yearSection = $('#y_and_s').val();
        var violationType = $('#type').val();
        var violationDetails = $('#info').val();
        var studentNames = $('#student_names').val().split('\n').filter(name => name.trim() !== '');

        var tableBody = $('#violationsTable tbody');
        tableBody.empty();

        studentNames.forEach(function(name) {
            var row = $('<tr>');
            row.append($('<td>').text(programs.join(', ')));
            row.append($('<td>').text(yearSection));
            row.append($('<td>').text(violationType));
            row.append($('<td>').text(violationDetails));
            row.append($('<td>').text(name.trim()));
            tableBody.append(row);
        });
    }

    $('form input, form select, form textarea').on('change keyup', updateViolationsTable);
});




$(document).ready(function() {
    $('#showArchivedMultipleModal').click(function() {
        $('#archivedMultipleViolationsModal').modal('show');
        $.ajax({
            url: 'get_archived_multiple_violations.php',
            type: 'GET',
            success: function(response) {
                $('#archivedMultipleViolationsModal .modal-body').html(response);
            },
            // error: function() {
            //     alert('Error loading archived violations');
            // }
        });
    });
});
// Function to update pagination visibility based on the displayed table
function updatePagination() {
    // Check which table is visible and display the correct pagination

    // Main Table
    if ($('#mainTable').is(':visible')) {
        $('#mainTablePagination').show();
        $('#archivedViolationsPagination').hide();
        $('#archivedGroupViolationsPagination').hide();
    }
    // Archived Violations Table
    else if ($('#archivedViolationsTable').is(':visible')) {
        $('#mainTablePagination').hide();
        $('#archivedViolationsPagination').show();
        $('#archivedGroupViolationsPagination').hide();
    }
    // Archived Group Violations Table
    else if ($('#archivedGroupViolationsTable').is(':visible')) {
        $('#mainTablePagination').hide();
        $('#archivedViolationsPagination').hide();
        $('#archivedGroupViolationsPagination').show();
    }
}

// Call this function every time a table is toggled or shown
function showArchiveTable() {
    console.log("showArchiveTable called");
    $('#mainTable').hide();
    $('#archivedGroupViolationsTable').hide();
    $('#archivedViolationsTable').show();
    
    // Hide the alternate table (if any)
    $('#alternateTable').hide();

    // Update pagination visibility
    updatePagination();

    // Disable the toggle button if archive tables are shown
    updateToggleButtonState();
}

function toggleArchivedGroupViolations() {
    console.log("toggleArchivedGroupViolations called");
    $('#mainTable').hide();
    $('#archivedViolationsTable').hide();

    const table = document.getElementById('archivedGroupViolationsTable');
    table.style.display = table.style.display === 'none' ? 'block' : 'none';

    $('#alternateTable').hide();

    // Update pagination visibility
    updatePagination();

    // Disable the toggle button if archive tables are shown
    updateToggleButtonState();
}

// Function to check the visibility of tables and update the button state
function updateToggleButtonState() {
    const archivedViolationsVisible = $('#archivedViolationsTable').is(':visible');
    const archivedGroupViolationsVisible = $('#archivedGroupViolationsTable').is(':visible');
    const toggleButton = $('#toggleTableView');

    // Disable the toggle button if either archive table is displayed
    if (archivedViolationsVisible || archivedGroupViolationsVisible) {
        toggleButton.prop('disabled', true);
    } else {
        toggleButton.prop('disabled', false);
    }
}

$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); // Initialize Bootstrap tooltips
});

//Archive Confirmation Modal
function setArchiveLink(actionUrl) {
    // Attach form submission to the confirm button
    document.getElementById('confirmArchiveButton').onclick = function() {
        window.location.href = actionUrl; // Redirect to the archive link
    };
}

$(document).ready(function() {
    $('#filterAll').click(function(event) {
        event.preventDefault();
        window.location.href = '?page=1'; // Redirect to the same page without filters
    });

    $('#filterCompleted').click(function(event) {
        event.preventDefault();
        window.location.href = '?status=completed&page=1'; // Redirect with the completed filter
    });

    $('#filterOngoing').click(function(event) {
        event.preventDefault();
        window.location.href = '?status=ongoing&page=1'; // Redirect with the ongoing filter
    });

    $('#filterScheduled').click(function(event) {
        event.preventDefault();
        window.location.href = '?status=scheduled&page=1'; // Redirect with the scheduled filter
    });
});


$(document).ready(function() {
    // Function to create loading overlay
    function createLoadingOverlay(message, redirectUrl) {
        const loadingOverlay = $(`
            <div class="redirect-overlay" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.95);
                z-index: 9999;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                transition: all 0.3s ease;
            ">
                <div class="loader" style="
                    width: 50px;
                    height: 50px;
                    border: 5px solid #f3f3f3;
                    border-top: 5px solid #4CAF50;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                "></div>
                <div class="redirect-message" style="
                    margin-top: 20px;
                    font-size: 18px;
                    color: #333;
                    font-family: 'Poppins', sans-serif;
                ">
                    <span>${message}</span>
                    <div class="progress-bar" style="
                        width: 200px;
                        height: 4px;
                        background: #eee;
                        margin-top: 10px;
                        border-radius: 2px;
                        overflow: hidden;
                    ">
                        <div class="progress" style="
                            width: 0%;
                            height: 100%;
                            background: #4CAF50;
                            transition: width 1s linear;
                        "></div>
                    </div>
                </div>
            </div>
        `);

        // Add CSS animation
        if (!$('style').text().includes('@keyframes spin')) {
            $('<style>')
                .text(`
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                `)
                .appendTo('head');
        }

        // Add overlay to body
        $('body').append(loadingOverlay);

        // Animate progress bar
        setTimeout(() => {
            loadingOverlay.find('.progress').css('width', '100%');
        }, 100);

        // Redirect after animation
        setTimeout(() => {
            loadingOverlay.fadeOut(300, function() {
                $(this).remove();
                window.location.href = redirectUrl;
            });
        }, 1200);
    }

    // Modify the Add Student button to skip the modal and go directly to add_violation.php
    $('.btn-outline-success.mb-3.mr-1').click(function(e) {
        e.preventDefault();
        // Skip showing the modal
        createLoadingOverlay('Redirecting to Add Student Form', 'add_violation.php');
    });

    // Keep this for backward compatibility if the modal is still shown
    $('#addSingleStudentBtn').click(function() {
        $('#addStudentChoiceModal').modal('hide');
        createLoadingOverlay('Redirecting to Add Student Form', 'add_violation.php');
    });

    // Commented out multiple students button functionality
    /*
    $('#addMultipleStudentsBtn').click(function() {
        $('#addStudentChoiceModal').modal('hide');
        createLoadingOverlay('Redirecting to Add Multiple Students Form', 'add_multiple_students.php');
    });
    */
});





</script>

    <footer id="printFooter" style="display: none;">
        <hr>
        <p style="text-align: center;">Prepared by:</p><br>
        <p style="text-align: center;">Prepared to:</p>
        <!-- <p style="text-align: center;">Page <span class="page-number"></span></p> -->
    </footer>


</div>
</body>
</html>
