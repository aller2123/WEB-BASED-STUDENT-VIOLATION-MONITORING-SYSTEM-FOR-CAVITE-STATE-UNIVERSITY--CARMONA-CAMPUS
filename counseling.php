<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

// Redirect only non-admin and non-staff users (if there are other roles)
$allowedRoles = ['superadmin', 'admin_cs', 'admin_csd', 'admin_pc'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: index.php');
    exit();
}

// Database connection settings
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$dsn = "mysql:host=$host;dbname=$database";
// Create a new PDO instance
try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $database :" . $e->getMessage());
}


// Check for search query and prepare the base SQL query
// Check for search query and prepare the base SQL query
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$searchTerm = '%' . $search . '%';
$sql = "SELECT cs.*, c.counselors_name
        FROM counseling_sessions AS cs
        LEFT JOIN counselors AS c ON cs.counselors_id = c.counselors_id
        WHERE cs.is_archived = 0";  // Add this condition

// Initialize where conditions
$whereConditions = [];
$parameters = [];

// Add search conditions if a search term is provided
if (!empty($search)) {
    $whereConditions[] = "(cs.student_full_name LIKE :search OR c.counselors_name LIKE :search OR cs.year_and_section LIKE :search)";
    $parameters[':search'] = $searchTerm;
}

// Add status condition if a status is selected
if (!empty($status)) {
    $whereConditions[] = "cs.status = :status";
    $parameters[':status'] = $status; // Bind the status parameter
}

$sql = "SELECT cs.*, c.counselors_name 
        FROM counseling_sessions AS cs
        LEFT JOIN counselors AS c ON cs.counselors_id = c.counselors_id
        WHERE cs.is_archived = 0 
        AND (cs.status IS NULL OR cs.status = '' OR cs.status = 'No Schedule Yet')";


// Add role-specific conditions
if ($_SESSION['role'] == 'admin_pc') {
    $sql .= " AND cs.assigned_to LIKE '%program_coordinator%'";
} elseif ($_SESSION['role'] == 'admin_csd') {
    $sql .= " AND cs.assigned_to LIKE '%coordinator_discipline%'";
} elseif ($_SESSION['role'] == 'admin_cs') {
    $sql .= " AND cs.assigned_to LIKE '%coordinator_welfare%'";
}

// Add search and status conditions if they exist
if (!empty($whereConditions)) {
    $sql .= " AND " . implode(' AND ', $whereConditions);
}
// Prepare the count query
$countSql = "SELECT COUNT(*) FROM counseling_sessions AS cs
              LEFT JOIN counselors AS c ON cs.counselors_id = c.counselors_id";

if (!empty($whereConditions)) {
    $countSql .= ' WHERE ' . implode(' AND ', $whereConditions);
}

// ```php
// Prepare and execute the total count query
$countStmt = $pdo->prepare($countSql);
if (!empty($parameters)) {
    foreach ($parameters as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
}
$countStmt->execute();
$totalRows = $countStmt->fetchColumn();
$rowsPerPage = 10;
$totalPages = ceil($totalRows / $rowsPerPage);
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $rowsPerPage;

// Validate the current page
if ($current_page < 1) {
    $current_page = 1;
} elseif ($current_page > $totalPages) {
    $current_page = $totalPages;
}

// Final SQL with pagination
$sql .= " LIMIT :offset, :rowsPerPage";

// Prepare and execute the SQL statement
$stmt = $pdo->prepare($sql);

// Bind parameters for search term and pagination
if (!empty($parameters)) {
    foreach ($parameters as $key => $value) {
        $stmt->bindValue($key, $value);
    }
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':rowsPerPage', $rowsPerPage, PDO::PARAM_INT);
$stmt->execute();

$counselingSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch violation details for students with violations
$sql = "SELECT v.full_name, v.year_and_section, 
         GROUP_CONCAT(DISTINCT v.type_of_violation SEPARATOR ', ') AS violation_types,
         GROUP_CONCAT(DISTINCT v.full_info SEPARATOR ', ') AS violation_details
FROM violations AS v
WHERE v.full_name IN (SELECT student_full_name FROM counseling_sessions WHERE with_violation = 1)
AND v.year_and_section IN (SELECT year_and_section FROM counseling_sessions WHERE with_violation = 1)
GROUP BY v.full_name, v.year_and_section";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$violationDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle messages
if (isset($_SESSION['success_message'])) {
    echo '<div class="custom-alert custom-alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']); // Clear the session message after displaying it
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="custom-alert custom-alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']); // Clear the session message after displaying it
}

// Handle paragraph submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paragraph']) && isset($_POST['counseling_id'])) {
    $counselingId = $_POST['counseling_id'];
    $paragraph = $_POST['paragraph'];
    
    if (!isset($_SESSION['paragraphs'][$counselingId])) {
        $_SESSION['paragraphs'][$counselingId] = array();
    }
    
    $_SESSION['paragraphs'][$counselingId][] = $paragraph;

    // Set success message
    $_SESSION['success_message'] = 'Paragraph submitted successfully!';

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch archived counseling sessions without pagination
$stmtArchived = $pdo->prepare("SELECT cs.*, c.counselors_name FROM counseling_sessions AS cs LEFT JOIN counselors AS c ON cs.counselors_id = c.counselors_id WHERE cs.is_archived = 1");
$stmtArchived->execute();
$archivedCounselingSessions = $stmtArchived->fetchAll(PDO::FETCH_ASSOC);



// Add this near the top of the file with other database operations
if (isset($_GET['action']) && $_GET['action'] == 'restore' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("UPDATE counseling_sessions SET is_archived = 0 WHERE counseling_id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['success_message'] = "Counseling session restored successfully.";
    header("Location: counseling.php");
    exit();
}




?>
<!-- Display the alert messages if present -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="custom-alert custom-alert-success"><?php echo $_SESSION['success_message']; ?></div>
    <?php unset($_SESSION['success_message']); // Clear the session message after displaying it ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="custom-alert custom-alert-danger"><?php echo $_SESSION['error_message']; ?></div>
    <?php unset($_SESSION['error_message']); // Clear the session message after displaying it ?>
<?php endif; ?>

<script>
  // Display the alert messages if present
  window.onload = function() {
        let successAlert = document.querySelector('.custom-alert-success');
        let errorAlert = document.querySelector('.custom-alert-danger');

        // Function to handle alerts
        function showAlert(alertElement) {
            if (alertElement && alertElement.textContent.trim() !== '') {
                alertElement.style.display = 'block';
                setTimeout(() => { alertElement.style.opacity = '0'; }, 2000); // Fade out after 2 seconds
                setTimeout(() => { alertElement.remove(); }, 2500); // Remove element after fade out
            } else if (alertElement) {
                alertElement.style.display = 'none'; // Hide the alert if it's empty
            }
        }

        showAlert(successAlert);
        showAlert(errorAlert);
    }
</script>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Counseling</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
    <!-- Our Custom CSS -->
    <link rel="stylesheet" href="css/navigation.css">
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
   

    <style>
        
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

        .btn-success{
            float:right;
            /* background-color: #4f8f1e; */
            /* color: #d8f0c6; */
            border: none;
            margin-right: 1px; /* Set margin between submit button and cancel button to 5px */
        }
        .btn-success:active {
            background-color: #43771c !important; /* Change the background color to red when the button is active */
        }
        .btn-danger{
            float:right;
            color: #d8f0c6;
            border: none;
            /* border-radius: 20px; */
            margin-right: 1px; /* Set margin between submit button and cancel button to 5px */
        }
        /* status custom css */
        .status-scheduled {
            color: blue;
        }
        .status-ongoing {
            color: darkorange;
        }
        .status-completed {
            color: green;
        }
        /* table custom css */
        .table-striped tbody tr:nth-of-type(odd) {
        background-color: #e6f7e4;
        }
        .table thead th {
        background-color: #4c704c;
        color: white; /* Change text color to white for better visibility */
        }
        th {
            text-transform: uppercase;
        }

        .text-muted {
            color: #6c757d;
        }
        .status-completed::before {
            content: "\2714";
            color: green;
            margin-right: 5px;
        }
        .file-actions {
            display: flex;
            align-items: center; /* Align items vertically */
        }

        .view-button {
            margin-right: 10px; /* Adjust margin as needed */
        }
        
        



.form-row {
    display: flex;
    align-items: center;
}

.form-row .col {
    margin-right: 10px;
}

.form-row .col:last-child {
    margin-right: 0;
}

@media print {
    #sidebar, .pagination, .btn, .input-group, #status {
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
            /* Hide the menu text "Counseling" */
            .menu-text {
                display: none !important;
            }
            /* Hide specific columns in the table header and body */
            table thead th:nth-child(5),
                /* table thead th:nth-child(7), */
                table thead th:nth-child(8),
                table thead th:nth-child(9),
                table tbody td:nth-child(5),
                /* table tbody td:nth-child(7), */
                table tbody td:nth-child(8),
                table tbody td:nth-child(9) {
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
    .table-responsive {
        overflow: hidden; /* Disable scroll */
    }

    .table {
        table-layout: fixed; /* Prevent resizing of table cells */
        width: 100%; /* Ensure table takes full width of its container */
    }

    /* .table td {
        overflow: hidden;
        text-overflow: ellipsis; 
        white-space: nowrap; 
    } */
    /* .custom-file-upload {
        display: flex;
        align-items: center;
        gap: 10px;
    } */
    
    .file-label {
        background-color: transparent;
        border: solid gray px;
        color: gray;
        padding: 4px;
        border-radius: px;
        cursor: pointer;
    }

    #fileNameDisplay {
        display: block;
        font-size:medium;
        color: #555;
    }
    /* .btn-outline-warning:hover{
        background-color:transparent;
        color:darkorange;
    } */
    /* .upload-btn{
        background: linear-gradient(135deg, #1bd889, #033a1a);
        min-width: 110px;
        background: #76d214;
        color: fffff;
        border: none;
    } */
    /* .upload-btn:hover{
        background: linear-gradient(135deg, #149a60, #022712);
        background: #9cee44;
        color: fffff;
        border: none;
    } */
    .view-btn{
        /* background: linear-gradient(135deg, #49b3ff, #1e3dd3); */
        background: #85a0f9;
        color: fffff;
        border: none;
    }
    .view-btn:hover{
        /* background: linear-gradient(135deg, #3a93e8, #172b99); */
        background: #7690a2;
        color: fffff;
        /* border: none; */
    }
    .add-btn:hover{
        background: #ff8266;
        border: none;
        color: fffff;
    }
    .choose-btn:hover{
        background: #5d895d;
        /* border: none; */
    }
    
    /* Custom alert CSS */
    .custom-alert {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1050;
        width: auto;
        max-width: 90%;
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
    .alert-warning {
        position: fixed;
        background-color: #dc3545; /* Error color */
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1050;
        width: auto;
        max-width: 90%;
        padding: 15px;
        border-radius: 5px;
        color: #fff;
        font-size: 16px;
        display: none; /* Hidden by default */
        opacity: 1;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    .alert-warning.fade-out {
        opacity: 0; /* Fade out effect */
        visibility: hidden; /* Hide the element */
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    #uploadBtn:disabled {
        background-color: gray;
        color: white; /* Optional: make text more visible on gray background */
        border-color: gray; /* Match border with background */
        cursor: not-allowed; /* Show the not-allowed cursor */
    }
    .edit-btn {
        background-color: #28a745;
    }
    .edit-btn:hover {
        background-color:rgb(37, 148, 63);
    }
    .arc-btn {
        background-color:red;
        border: none;
    }
    .arc-btn:hover {
        background-color:rgb(182, 18, 26);
    }
    label{
        margin-bottom: 5px;
        text-transform: uppercase;
        color: #444444;
        font-weight: 600;
    }
    #studentFullName {
        border-radius: 0 !important; /* Removes border radius and overrides any Bootstrap styles */
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
    

    
    </style>
</head>

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
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                    </svg>
                </button>
                <span class="menu-text"> Pending Counseling</span>
                <div class="col py-3">
                    <div id="alertMessage" class="custom-alert custom-alert-success"></div>
                <div id="counseling-section" style="padding-top:15px; padding-left:10px;">
 <!-- SEARCH -->               
 <div class="d-flex justify-content-between align-items-center">
    <!-- Left Side: Add Student and Search -->
    <div class="d-flex align-items-center">
    <button class="btn btn-outline-success mb-3 mr-1" data-toggle="modal" data-target="#addStudentModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-person-add mr-1" viewBox="0 0 16 16">
            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
            <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
        </svg>
    Walk-in</button>
        <form class="form-inline d-flex align-items-center mr-2" method="GET" action="counseling.php">
            <div class="input-group w-120">
                <label class="sr-only" for="search">Search</label>
                <input 
                    type="text" 
                    class="form-control" 
                    style="border-radius:5px 0 0 5px;" 
                    id="search" 
                    name="search" 
                    placeholder="Search" 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                
            </div>
            <!-- <select name="status" class="form-control mr-1" id="status">
                    <option value="">All Statuses</option>
                    <option value="Completed" <?php if (isset($_GET['status']) && $_GET['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                    <option value="Scheduled" <?php if (isset($_GET['status']) && $_GET['status'] == 'Scheduled') echo 'selected'; ?>>Scheduled</option>
            </select> -->
                <div class="input-group-append">
                    <button class="btn btn-outline-primary ml-1" type="submit">Search</button>
                    <?php if (!empty($search) || !empty($_GET['status'])): ?>
                        <a href="?page=1" class="btn btn-dark">Clear</a>
                    <?php endif; ?>
                </div>
        </form>
        
    </div>

    <!-- Right Side: Counseling Sessions and Print buttons -->
    <div class="d-flex align-items-center mb-2">
        <?php if ($_SESSION['role'] == 'superadmin'): ?>
            <button id="showArchivedCounseling" data-toggle="tooltip" data-placement="top" title="Archived Sessions" class="btn btn-warning mr-1" style="border:none;">
                Counseling
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                    <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </button>
        <?php endif; ?>
        
        <button id="printButton" class="btn btn-success" style="background: #e48189; border:none; color:white;">
            Print
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
            </svg>
        </button>
    </div>
</div>
<script>
    // Enable Bootstrap tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>


<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">WALK-IN FORM</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="add_walkin.php" method="POST" onsubmit="uppercaseName()"> <!-- Updated action -->
                <div class="form-group">
                    <label for="studentFullName">Full Name</label>
                    <input type="text" class="form-control text-uppercase no-border-radius" id="studentFullName" name="student_full_name" required 
                        oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '');">
                </div>
                <div class="form-group">
                        <label for="yearAndSection">Year and Section</label>
                        <select class="form-control text-uppercase" id="yearAndSection" name="year_and_section" required>
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
                    <div class="form-group">
                        <label for="counselorId">Counselor</label>
                        <select class="form-control" id="counselorId" name="counselor_id" required>
                            <option value="">SELECT COUNSELOR</option>
                            <?php
                            // Fetch counselors from the database
                            $counselors = $pdo->query("SELECT * FROM counselors")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($counselors as $counselor) {
                                echo '<option value="' . htmlspecialchars($counselor['counselors_id']) . '">' . htmlspecialchars($counselor['counselors_name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <!-- Cancel Button -->
                        <button type="button" class="btn btn-outline-secondary mr-1" data-dismiss="modal">CANCEL</button>
                        
                        <!-- Add Button -->
                        <button type="submit" class="btn btn-success">SUBMIT</button>
                    </div>
                </form>
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
                        <h3>Cavite State University</h3>
                        <h4>Carmona Campus</h4>
                        <p class="ngi">Market Road, Carmona, Cavite <br>
                        ☏(046)487-6328/cvsucarmona@cvsu.edu.ph <br>
                        www.cvsu.edu.ph</p>
                    </div>
                    <!-- Empty column to push text to the center -->
                    <div class="col-md-2"></div>
                </div>
            </div>
            <h1 id="printHeader" class="d-none d-print-block text-center mb-4">Student Counseling Records</h1>
        </header>
        <div class="table-responsive" style="overflow: hidden;">        
        <table id="mainTable" class="table table-hover table-bordered">
        <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Year and Section</th>
                                    <th>With Violation</th>
                                    <th>Counselor</th>
                                    <th>type of violation</th>
                                    <th>Status</th>
                                    <th>Date Created</th>
                                    <th>Action</th> 
                                </tr>
                            </thead>
                            <tbody>
                            <!-- no result display -->
                            <!-- <?php if ($noResultMessage): ?>
                                <tr>
                                    <td colspan="9" class="text-center"><?php echo $noResultMessage; ?></td>
                                </tr>
                            <?php endif; ?> -->
    <?php $rowColor = true; ?>
    <?php foreach ($counselingSessions as $session): ?>
        <tr>
            <?php $rowColor = !$rowColor; ?>
            <td><?php
    $fullName = htmlspecialchars($session['student_full_name']);
    $fullName = str_replace([' N/A ', 'N/A'], '', $fullName);
    echo trim($fullName);

    // Add NEW badge for recent entries
    $created = new DateTime($session['timestamp']);
    $now = new DateTime();
    $interval = $created->diff($now);
    
    if ($interval->days < 1): ?>
        <span class="badge badge-success" style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; margin-left: 5px;">NEW</span>
    <?php endif; ?>
</td>

            <td><?= htmlspecialchars($session['year_and_section']); ?></td>
            <td>
                <?php if ($session['with_violation']): ?>
                    <span class="text-success">
                        <i class="fas fa-check-circle"></i>
                    </span>
                <?php else: ?>
                    <span class="text-dark font-weight-bold">No Violation</span>
                <?php endif; ?>
            </td>

<td><?= htmlspecialchars($session['counselors_name']) ?: 'No Schedule Yet' ?></td>

<td>
<a href="#" 
       class="btn btn-primary ml-4"
       data-toggle="modal" 
       data-target="#violationModal<?= $session['counseling_id']; ?>" 
       <?php if (!$session['with_violation']): ?> 
           style="pointer-events: none; opacity: 0.5;" 
           title="No Violation" 
       <?php endif; ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
        </svg>
    </a>

    <!-- Violation Details Modal -->
<div class="modal fade" id="violationModal<?= $session['counseling_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="violationModalLabel<?= $session['counseling_id']; ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="violationModalLabel<?= $session['counseling_id']; ?>">Violation Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                if ($session['with_violation']) {
                    $studentViolationDetails = array_filter($violationDetails, function ($detail) use ($session) {
                        return $detail['full_name'] === $session['student_full_name'] && $detail['year_and_section'] === $session['year_and_section'];
                    });

                    if (!empty($studentViolationDetails)) {
                        foreach ($studentViolationDetails as $detail) {
                            echo '<p><strong>Type:</strong> ' . htmlspecialchars($detail['violation_types']) . '</p>';
                            echo '<p><strong>Details:</strong> ' . htmlspecialchars($detail['violation_details']) . '</p>';
                        }
                    } else {
                        echo '<p>No violation details found.</p>';
                    }
                }
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

</td>


<td class="status-<?php echo strtolower(htmlspecialchars($session['status'])); ?>">
    <?php
    if ($session['status'] === 'Scheduled') {
        $formattedScheduleTime = isset($session['schedule_time']) && !empty($session['schedule_time']) ? date('Y-m-d h:i A', strtotime($session['schedule_time'])) : '';
        echo htmlspecialchars($session['status']) . ($formattedScheduleTime ? ' (' . $formattedScheduleTime . ')' : '');
    } else {
        echo htmlspecialchars($session['status']) ?: 'No Schedule Yet';
    }
    ?>
</td>


<td>
    <?php 
    $timestamp = new DateTime($session['timestamp']);
    echo $timestamp->format('M d, Y h:i A'); 
    ?>
</td>


<!-- upload button disable and enable -->
<script>
    // Function to display the file name and enable the upload button
    document.getElementById("customFileInput").addEventListener("change", function() {
    var fileInput = document.getElementById("customFileInput");
    var fileNameDisplay = document.getElementById("fileNameDisplay");
    var uploadBtn = document.getElementById("uploadBtn");

    if (fileInput.files.length > 0) {
        // Show the selected file name
        fileNameDisplay.textContent = fileInput.files[0].name;
        // Enable the upload button
        uploadBtn.disabled = false;
    } else {
        // Reset the file name display and disable the button if no file is chosen
        fileNameDisplay.textContent = "No file chosen";
        uploadBtn.disabled = true;
    }
});
</script>



</td>


<td class="action-buttons" style="justify-content: center;">
                <a href="edit_pending.php?id=<?= $session['counseling_id']; ?>" data-toggle="tooltip" data-placement="top" title="Edit this student" class="edit-btn btn btn-outline-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="white" class="bi bi-pen" viewBox="0 0 16 16">
                        <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z"/>
                    </svg>
                </a>
                <!-- archive button -->
                <button type="button" 
                        class="arc-btn btn btn-warning archive-counseling-btn" 
                        data-id="<?= $session['counseling_id']; ?>" 
                        data-toggle="modal" 
                        data-target="#archiveCounselingModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="white" class="bi bi-archive" viewBox="0 0 16 16">
                        <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                </button>
            </td>
            <!-- Archive Counseling Session Confirmation Modal -->
            <div class="modal fade" id="archiveCounselingModal" tabindex="-1" role="dialog" aria-labelledby="archiveCounselingModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archiveCounselingModalLabel">Confirm Archive</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to archive this counseling session?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <form id="archiveCounselingForm" action="archive_counseling.php" method="GET" style="display: inline;">
                    <input type="hidden" name="id" id="archiveCounselingId">
                    <button type="submit" class="btn btn-success">Yes, proceed</button>
                    </form>
                </div>
                </div>
            </div>
            </div>
        </tr>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const archiveButtons = document.querySelectorAll('.archive-counseling-btn');
            const archiveInput = document.getElementById('archiveCounselingId');

            archiveButtons.forEach(button => {
            button.addEventListener('click', function () {
                const counselingId = this.getAttribute('data-id');
                archiveInput.value = counselingId;
            });
            });
        });
        </script>

    <?php endforeach; ?>
</tbody>
</table>
</div>
<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-end">
        <li class="page-item <?php if ($current_page <= 1) echo 'disabled'; ?>">
            <a class="page-link" href="?page=1" style="background: <?php echo ($current_page <= 1 ? '#e6d6ff' : '#d0b3ff'); ?>; border:none; color: gray;">First</a>
        </li>
        <li class="page-item <?php if ($current_page <= 1) echo 'disabled'; ?>">
            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" tabindex="-1" style="background: <?php echo ($current_page <= 1 ? '#e6d6ff' : '#d0b3ff'); ?>; border:none; color: gray;">Previous</a>
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
               style="background: <?php echo ($current_page >= $totalPages ? '#e6d6ff' : '#d0b3ff'); ?>;border:none; color: gray;">Next</a>
        </li>
        <li class="page-item <?php if ($current_page >= $totalPages) echo 'disabled'; ?>">
            <a class="page-link" href="?page=<?php echo $totalPages; ?>" 
               style="background: <?php echo ($current_page >= $totalPages ? '#e6d6ff' : '#d0b3ff'); ?>; border:none; color: gray;">Last</a>
        </li>
    </ul>
</nav>

            
        </div>
<!-- archive counseling modal -->
        <div class="modal fade" id="archivedCounselingModal" tabindex="-1" role="dialog" aria-labelledby="archivedCounselingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archivedCounselingModalLabel">Archived Counseling Sessions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Year and Section</th>
                            <th>With Violation</th>
                            <th>Counselor</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archivedCounselingSessions as $session): ?>
                            <tr>
                                <td><?= htmlspecialchars($session['student_full_name']) ?></td>
                                <td><?= htmlspecialchars($session['year_and_section']) ?></td>
                                <td><?= $session['with_violation'] ? 'Yes' : 'No' ?></td>
                                <td><?= htmlspecialchars($session['counselors_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($session['status']) ?></td>
                                <td>
                                    <!-- Restore Button -->
                                    <a href="javascript:void(0);" class="btn btn-outline-success btn-sm mb-1 w-100" 
                                    data-toggle="modal" data-target="#restoreConfirmationModal"
                                    onclick="setRestoreActionLink('unarchive_counseling.php?id=<?= $session['counseling_id'] ?>', 'Are you sure you want to restore this session?')">
                                        Restore
                                    </a>

                                    <!-- Delete Button -->
                                    <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm w-100" 
                                    data-toggle="modal" data-target="#actionConfirmationModal"
                                    onclick="setActionLink('delete_counseling.php?id=<?= $session['counseling_id'] ?>', 'Are you sure you want to permanently delete this session?')">
                                        Delete
                                    </a>
                                </td>
                                <!-- Modal for Action Confirmation -->
                                <div class="modal fade" id="restoreConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="restoreConfirmationModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="restoreConfirmationModalLabel">Confirmation</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body" id="confirmationMessage">
                                                <!-- Dynamic confirmation message will be inserted here -->
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                                                <!-- Restore form, will be dynamically updated with the correct action URL -->
                                                <form id="restoreForm" action="" method="POST">
                                                    <button type="submit" class="btn btn-success">Yes, proceed</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </tr>
                            <!-- JavaScript to Set Restore Action Link and Message -->
                            <script>
                                function setRestoreActionLink(actionUrl, message) {
                                    // Set the confirmation message dynamically
                                    document.getElementById('confirmationMessage').innerText = message;
                                    
                                    // Set the form action URL dynamically
                                    var restoreForm = document.getElementById('restoreForm');
                                    restoreForm.action = actionUrl;
                                }
                            </script>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- Action Confirmation Modal -->
<div class="modal fade" id="actionConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="actionConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionConfirmationModalLabel">Confirm Action</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                Are you sure you want to proceed with this action?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="confirmActionLink" class="btn btn-success" style="color:white;">Yes, proceed</a>
            </div>
        </div>
    </div>
</div>

    

   
    <!-- archived counseling sessions -->
<div id="archivedCounselingTable" style="display: none;">
    <table class="table table-hover table-bordered">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Year and Section</th>
                <th>With Violation</th>
                <th>Counselor</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($archivedCounselingSessions as $session): ?>
                <tr>
                    <td><?= htmlspecialchars($session['student_full_name']) ?></td>
                    <td><?= htmlspecialchars($session['year_and_section']) ?></td>
                    <td><?= $session['with_violation'] ? 'Yes' : 'No' ?></td>
                    <td><?= htmlspecialchars($session['counselors_name']) ?></td>
                    <td><?= htmlspecialchars($session['status']) ?></td>
                    <td>
                        <a href="unarchive_counseling.php?id=<?= $session['counseling_id'] ?>" class="btn btn-outline-success btn-sm mb-1" onclick="return confirm('Are you sure you want to restore this session?')">Restore</a>
                        <a href="delete_counseling.php?id=<?= $session['counseling_id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to permanently delete this session?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>



<!-- Archive Modal Main Table-->
<div class="modal fade" id="archiveConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="archiveConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="archiveConfirmationModalLabel">Confirm Archiving</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to archive this counseling session?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <a href="#" id="confirmArchiveLink" class="btn btn-warning">Yes, proceed</a>
      </div>
    </div>
  </div>
</div>

<!-- Restore Modal Archive Counseling Sessions -->
<div class="modal fade" id="actionConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="actionConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="actionConfirmationModalLabel">Confirm Action</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modalBody">
        Are you sure you want to proceed with this action?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
        <a href="#" id="confirmActionLink" class="btn btn-warning">Yes, proceed</a>
      </div>
    </div>
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



    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

        
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
    <!-- jQuery Custom Scroller CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>
    <!-- Our Custom JS -->
        <script type="text/javascript">
            // Print functionality
                document.getElementById('printButton').addEventListener('click', function() {
                // Show the header only for printing
                document.getElementById('printHeader').style.display = 'block';
                
                // Trigger the print dialog
                window.print();
                
                // Hide the header after printing
                document.getElementById('printHeader').style.display = 'none';
            });
            
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

// For file uploads
$('.file-upload-form').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'upload_file_or_paragraph.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            var successPopup = $('<div>', {
                class: 'upload-success-alert',
                css: {
                    'position': 'fixed',
                    'top': '20px',
                    'left': '50%',
                    'transform': 'translateX(-50%)',
                    'z-index': '9999',
                    'padding': '15px 30px',
                    'border-radius': '8px',
                    'background-color': '#4CAF50',
                    'color': 'white',
                    'box-shadow': '0 4px 8px rgba(0,0,0,0.2)',
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '10px',
                    'font-size': '15px'
                }
            }).html(`
                <div style="display: flex; align-items: center; gap: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-folder-plus" viewBox="0 0 16 16">
                        <path d="m.5 3 .04.87a2 2 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14H9v-1H2.826a1 1 0 0 1-.995-.91l-.637-7A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09L14.54 8h1.005l.256-2.819A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2m5.672-1a1 1 0 0 1 .707.293L7.586 3H2.19q-.362.002-.683.12L1.5 2.98a1 1 0 0 1 1-.98z"/>
                        <path d="M13.5 9a.5.5 0 0 1 .5.5V11h1.5a.5.5 0 1 1 0 1H14v1.5a.5.5 0 1 1-1 0V12h-1.5a.5.5 0 0 1 0-1H13V9.5a.5.5 0 0 1 .5-.5"/>
                    </svg>
                    <div>
                        <strong>Upload Complete!</strong>
                        <div>File successfully uploaded</div>
                    </div>
                </div>
            `);

            $('body').append(successPopup);

            setTimeout(function() {
                successPopup.fadeOut('fast', function() {
                    $(this).remove();
                    location.reload();
                });
            }, 1500);
        },
        error: function() {
            alert('Upload failed. Please try again.');
        }
    });
});



function confirmLogout() {
    // Show a confirmation dialog
    var confirmation = confirm("Are you sure you want to logout?");
    
    // If the user clicks "OK", return true to proceed with the logout
    // If the user clicks "Cancel", return false to prevent the logout
    return confirmation;
}




$(document).ready(function() {
    $('#showArchivedCounseling').click(function() {
        $('#archivedCounselingModal').modal('show');
    });
});



$('.file-upload-form').on('submit', function(e) {
    var paragraph = $(this).find('textarea[name="paragraph"]').val();
    var fileInput = $(this).find('input[name="file"]').val();

    // Check if both fields are empty
    if (!paragraph && !fileInput) {
        e.preventDefault(); // Prevent form submission
        alert('Please enter a paragraph or upload a file.');
    }
});


function showDetailsPopup(counselingId) {
    $('#counselingIdInput').val(counselingId);
    $('#detailsPopupModal').modal('show');
}

$(document).ready(function() {
    $('#detailsForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'upload_file_or_paragraph.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#detailsPopupModal').modal('hide');
                location.reload();
            }
        });
    });
});


function showFileName() {
        var fileInput = document.getElementById('customFileInput');
        var fileNameDisplay = document.getElementById('fileNameDisplay');
        
        // Get the file name
        var fileName = fileInput.files[0] ? fileInput.files[0].name : 'No file chosen';
        
        // Display the file name
        fileNameDisplay.textContent = fileName;
    }


// Function to disable the Group w/ Violation button
function disableToggleButton() {
    document.getElementById('toggleTableView').disabled = true;
}

// Function to enable the Group w/ Violation button
function enableToggleButton() {
    document.getElementById('toggleTableView').disabled = false;
}

// Function to show the archived counseling table
function showArchived() {
    // Hide all other tables
    document.getElementById('mainTable').style.display = 'none';
    document.getElementById('alternateTable').style.display = 'none'; // Hide Group with Violations table

    // Show the archived counseling table
    document.getElementById('archivedCounselingTable').style.display = 'block';

    // Disable the Group w/ Violation button
    disableToggleButton();
}

// Function to show the archived group counseling table
function showGroupArchived() {
    // Hide all other tables
    document.getElementById('mainTable').style.display = 'none';
    document.getElementById('alternateTable').style.display = 'none'; // Hide Group with Violations table
    document.getElementById('archivedCounselingTable').style.display = 'none'; // Hide archived counseling table


    // Disable the Group w/ Violation button
    disableToggleButton();
}

// Function to show the main table
function showMain() {
    // Hide all archived tables
    document.getElementById('archivedCounselingTable').style.display = 'none';
    document.getElementById('archivedGroupCounselingTable').style.display = 'none';

    // Show the main table
    document.getElementById('mainTable').style.display = 'block';
    document.getElementById('alternateTable').style.display = 'none'; // Hide Group with Violations table if not toggled

    // Enable the Group w/ Violation button
    enableToggleButton();
}

// Function to show the Group w/ Violation (alternate) table


// Event listener for Group w/ Violation button
document.getElementById('toggleTableView').addEventListener('click', showGroupViolations);
document.querySelector('#customFileInput').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        document.querySelector('#fileNameDisplay').innerText = fileName;
    });
    
    // Open file dialog when clicking the label
    document.querySelector('.file-label').addEventListener('click', function() {
        document.querySelector('#customFileInput').click();
    });


 // Show custom alert
 function showAlert(message, alertType) {
        const alertDiv = document.getElementById('alertMessage');
        alertDiv.classList.remove('custom-alert-success', 'custom-alert-danger'); // Remove any previous alert types
        alertDiv.classList.add(alertType); // Add the appropriate alert type
        alertDiv.innerText = message; // Set the message
        alertDiv.style.display = 'block'; // Show the alert
        
        // Hide the alert after 5 seconds
        setTimeout(() => {
            alertDiv.style.display = 'none';
        }, 5000);
    }

    // Trigger the alert on page load if there is a success message in the session
    <?php if (isset($_SESSION['success_message'])): ?>
        showAlert("<?php echo $_SESSION['success_message']; ?>", 'custom-alert-success');
        <?php unset($_SESSION['success_message']); ?> <!-- Clear the success message after use -->
    <?php endif; ?>


    // function setArchiveLink(counselingId) {
    //     document.getElementById('confirmArchiveLink').setAttribute('href', 'archive_counseling.php?id=' + counselingId);
    // }
    // function setActionLink(url, action) {
    //     document.getElementById('confirmActionLink').setAttribute('href', url);
    //     document.getElementById('modalBody').innerText = 'Are you sure you want to ' + action + '?';
    // }


    function setActionLink(link, message) {
    document.getElementById('confirmActionLink').setAttribute('href', link);
    document.getElementById('modalBody').innerText = message;
}


function showAlert(message, alertType) {
    const alertDiv = document.getElementById('alertMessage');
    alertDiv.classList.remove('custom-alert-success', 'custom-alert-danger'); // Remove any previous alert types
    alertDiv.classList.add(alertType); // Add the appropriate alert type
    alertDiv.innerText = message; // Set the message
    alertDiv.style.display = 'block'; // Show the alert
    
    // Hide the alert after 5 seconds
    setTimeout(() => {
        alertDiv.style.display = 'none';
    }, 5000);
}

function uppercaseName() {
        // Get the value of the Full Name input and convert it to uppercase
        var fullNameInput = document.getElementById('studentFullName');
        fullNameInput.value = fullNameInput.value.toUpperCase();
    }
</script>



    <footer id="printFooter" style="display: none;">
        <hr>
        <p style="text-align: center;">Prepared by:</p><br>
        <p style="text-align: center;">Prepared to:</p>
        <!-- <p style="text-align: center;">Page <span class="page-number"></span></p> -->
    </footer>
    </body>
    </html>