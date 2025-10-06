<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);


if (!isset($_SESSION['role'])) {
    header('Location: index.php'); // Redirect to login if not authenticated
    exit();
}


// Fall back for the username display
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'No username set';

// Fetch enrolled students count
$enrolledStudentsStmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Enrolled'");
if (!$enrolledStudentsStmt) {
    die("Error fetching enrolled students: " . print_r($pdo->errorInfo(), true));
}
$enrolledStudentsCount = $enrolledStudentsStmt->fetchColumn();

// Fetch graduate students count
$graduateStudentsStmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Graduate'");
if(!$graduateStudentsStmt) {
    die("Error fetching graduate students: " . print_r($pdo->errorInfo(), true));
}
$graduateStudentsCount = $graduateStudentsStmt->fetchColumn();

// Fetch not enrolled students count
$notEnrolledStudentsStmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Not Enrolled'");
if(!$notEnrolledStudentsStmt) {
    die("Error fetching not enrolled students: " . print_r($pdo->errorInfo(), true));
}
$notEnrolledStudentsCount = $notEnrolledStudentsStmt->fetchColumn();

// Add this PHP code near the top where other counts are fetched
$maleCountStmt = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'Male'");
$femaleCountStmt = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'Female'");
$maleCount = $maleCountStmt->fetchColumn();
$femaleCount = $femaleCountStmt->fetchColumn();



// Add this with your other database queries
// Update the pending count query to include NULL status
$pendingCounselingStmt = $pdo->query("SELECT COUNT(*) FROM counseling_sessions WHERE status IS NULL OR status = 'Pending'");
$scheduledCounselingStmt = $pdo->query("SELECT COUNT(*) FROM counseling_sessions WHERE status = 'Scheduled'");
$completedCounselingStmt = $pdo->query("SELECT COUNT(*) FROM counseling_sessions WHERE status = 'Completed'");

$pendingCount = $pendingCounselingStmt->fetchColumn();
$scheduledCount = $scheduledCounselingStmt->fetchColumn();
$completedCount = $completedCounselingStmt->fetchColumn();



// Query to get monthly counseling data
$monthlyStmt = $pdo->query("
    SELECT 
        MONTH(timestamp) as month,
        COUNT(*) as count,
        status
    FROM counseling_sessions 
    WHERE YEAR(timestamp) = YEAR(CURRENT_DATE)
    GROUP BY MONTH(timestamp), status
    ORDER BY month
");
$monthlyData = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);

// Process data for the chart
$months = [];
$pendingData = array_fill(0, 12, 0);
$scheduledData = array_fill(0, 12, 0);
$completedData = array_fill(0, 12, 0);

foreach ($monthlyData as $data) {
    $monthIndex = $data['month'] - 1;
    switch ($data['status']) {
        case NULL:
        case 'Pending':
            $pendingData[$monthIndex] += $data['count'];
            break;
        case 'Scheduled':
            $scheduledData[$monthIndex] += $data['count'];
            break;
        case 'Completed':
            $completedData[$monthIndex] += $data['count'];
            break;
    }
}

// Query for violation types distribution
$violationStmt = $pdo->query("
    SELECT type_of_violation, COUNT(*) as count
    FROM violations
    GROUP BY type_of_violation
");

$violationData = $violationStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize counts
$majorViolationCount = 0;
$minorViolationCount = 0;

// Process the results
foreach ($violationData as $data) {
    if (strtolower($data['type_of_violation']) == 'major') {
        $majorViolationCount = $data['count'];
    } else if (strtolower($data['type_of_violation']) == 'minor') {
        $minorViolationCount = $data['count'];
    }
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <!-- Bootstrap 4 CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/navigation.css">
    <!-- Scrollbar Custom CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Lato:300,300i,400,400i,700,700i" rel="stylesheet">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap">
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <!-- FullCalendar JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Include Bootstrap CSS in your layout -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    
        .align-items-center {
            align-items: center;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .ml-auto {
            margin-left: auto;
        }
        .user-container {
            margin-right: 20px; /* Adjust this value as needed for spacing */
            margin-top: 25px;
        }
        .card-link1, .card-link2, .card-link3 {
        display: block;
        text-decoration: none; /* Ensures links don't have underline */
        }

        
        /* print process */
@media print {
    body {
        margin: 0;
        padding: 60px 0 0 0; /* Add top padding to prevent content from being hidden under the fixed header */
    }
    

    #printHeader2 {
        position: fixed;
        top: 300;
        left: 0;
        right: 0;
        background: white;
        z-index: 1000;
        margin-top: 0;
        padding: 10px 0;
        display: block !important;
    }

    .card1, .card2, .card3, .card4, 
    .menu-text, .user, .button, .printBtn, .menu-btn {
        display: none !important;
    }

    .chart-card {
        page-break-before: always;
        page-break-after: always;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
        padding: 30px !important;
        text-align: center !important;
    }

    .header-logo {
        width: 9rem !important;
        margin-left: 50px;
    }
}


        .header-text {
            flex: 2;  /* Ensures text stays centered */
            text-align: center;
            margin-top: -20px;
        }

        .empty-space {
            flex: 1; /* Keeps balance on the right */
        }

        /* Adjustments for printing */
        @media print {
            .header-logo {
                width: 9rem; /* Make logo smaller for printing */
                margin-left:50px;
            }
            .print-chart-data {
                display: block !important;
                page-break-inside: avoid;
            }
        }
        /* For print layout */
        @media print {
    /* Nuclear option to force visibility */
    body * {
        visibility: visible !important;
    }
    
    /* Specific card title styling */
    .card-title {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative !important;
        font-size: 20pt !important;
        color: black !important;
        background: white !important;
        padding: 10px !important;
        border: 1px solid black !important; /* For debugging */
        margin-top: 100px;
    }
    .line-card-title {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative !important;
        font-size: 20pt !important;
        color: black !important;
        background: white !important;
        padding: 10px !important;
        margin-top: -30px;
    }
    
    /* Remove any potential hiding */
    .card-header, .card-title, .chart-card {
        display: block !important;
        height: auto !important;
        overflow: visible !important;
        border: none !important;
    }
    
    /* Fix for potential overlaps */
    body {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    /* Force show all content that might be hidden */
    .d-print-block {
        display: block !important;
    }

    .chart-card {
        page-break-before: always;
        page-break-after: always;
        width: 80% !important;
        max-width: 90% !important;
        box-sizing: border-box;
        padding: 20px !important;
        /* text-align: center !important;   Ensure the chart's container is centered */
        /* margin-top: 100px; */
        /* margin-left: 80px; */
        margin: 80px auto 0 auto;
    }
    .line-card {
        page-break-after: always;
        width: 90% !important;
        max-width: 100% !important;
        box-sizing: border-box;
        padding: 30px !important;
        text-align: center !important; /* Center contents inside */
        margin: 100px auto 0 auto; /* Top margin stays, left & right auto centers it */
    }

    .card-header h3 {
        font-size: 20pt !important;
        margin-bottom: -20px;
    }

    .card-body canvas {
        width: 100% !important;             /* Ensure it takes up full width */
        height: auto !important;            /* Maintain aspect ratio */
        max-width: 100% !important;         /* Limit width to 100% */
        max-height: 80vh !important;        /* Limit height to 80% of the page height */
        display: block !important;
        margin: 0 auto !important;
    }
    

    .col-md-6 {
        display: block !important;
        width: 100% !important;
        margin-bottom: 40px !important;
    }
        /* Adjusting the specific Counseling Sessions Status chart */
        #counselingStatusChart {
        width: 100% !important;           /* Set the width to 100% of the container */
        height: 500px !important;         /* Set height specifically for this chart */
        max-width: none !important;       /* Allow it to expand beyond the container */
        max-height: 90vh !important;      /* Prevent it from exceeding the page height */
        display: block !important;
        margin-left: auto !important;     /* Left margin auto for centering */
        margin-right: auto !important;    /* Right margin auto for centering */
    }
}



        
        .card-link1:hover .card1 {
            background: #bdcdff;
            border-color: #c1f0c1;
            transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease; /* Longer and smoother transition */
            transform: scale(0.989); /* Slight shrink effect to simulate pressing */
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Slightly more defined shadow for depth */
        }


        
        .card-link2:hover .card2 {
            background: #c1f0c1;
            border-color: #bdcdff;
            transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease; /* Longer and smoother transition */
            transform: scale(0.989); /* Slight shrink effect to simulate pressing */
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Slightly more defined shadow for depth */
            
        }
        .card-link3:hover .card3 {
            background: #ffcccc;
            border-color: #ffcccc;
            transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease; /* Longer and smoother transition */
            transform: scale(0.989); /* Slight shrink effect to simulate pressing */
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Slightly more defined shadow for depth */
            
        }
        .card2 {
            background-color: #ffffff;
            color: #239023;
        } .c2 {
            color: #4d4d4d;
        }
        .card1 {
            background-color: #ffffff;
            font-weight: lighter;
            color: #1a53ff;
        } .c1 {
            color: #4d4d4d;
        }
        .card3 {
            background-color: #ffffff;
            color: #ff1a1a;
        } .c3 {
            color: #4d4d4d;
        }
        .card4 {
            background-color:  #c2f9ff;
            color: #4d4d4d;
        }

        .card1, .card2, .card3, .card4 {
            transition: background-color 0.3s ease;
            height: 100%; /* Ensures card stretches to fill column height */
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Distribute space evenly */
            /* color: white; */
            border: none;
            border-radius: 10px;
            /* background: #ffffff; */
            box-shadow:  11px 11px 22px #b1b1b1,
                        -11px -11px 22px #ffffff;
        }
        .chart-card, .line-card {
            /* border: 1px solid #ddd; */
            border-radius: 10px;
            background: #ffffff;
            box-shadow:  11px 11px 22px #b1b1b1,
                        -11px -11px 22px #ffffff;

        }
        .card-header {
            background-color: #f2f2f2;
        }
        .card-title {
            margin-bottom: -5px;
        }
        .col-md-3 h5 {
            font-size: 18px;
            color:#4d4d4d;
        }
        

        .equal-height {
            min-height: 150px; /* Adjust as needed to ensure a consistent height */
        }
        h3 {
            font-size:x-large;
            color: #4d4d4d;
        }

        .row {
            align-items: stretch; /* Ensures all columns are stretched to the same height */
        }

        /* Customize FullCalendar text color */
        .fc {
            color: #5b3e3e; /* Change this to your desired dark color */
        }
        .fc-daygrid-day-number {
            color: #6a4848; /* Change this to your desired dark color */
        }
        .fc-event {
    font-size: 8px;
    display: block;
    color: #3e5b3e; /* Change this to your desired dark color */
    white-space: normal; /* Allows the text to wrap */
    overflow: visible; /* Ensures content is not clipped */
    text-overflow: clip; /* Prevents the text from being cut off */
    padding: 1px; /* Adds padding for better spacing */
}
        .fc-daygrid-day-top {
            color: #333; /* Change this to your desired dark color */
        }
        .fc-col-header-cell {
            color: #333; /* Change this to your desired dark color */
        }
        .fc-col-header-cell-cushion {
            color: #a27676;
        }
        .fc-today-button {
            color: red; /* Change this to your desired color */
        }
        .btn-secondary {
            background-color: transparent;
            color: #444444;
            border: none;
            border-radius: 20px;
        }
        .btn-primary {
            border-radius: 5px;
        }
        .btn-secondary:not(:active) {
            border: 1px solid #aa5082;
        }

        .btn-secondary:hover {
            background-color: #74425d;
            color: #d8f0c6;
        }
        #print-button-container {
            display: flex;
            justify-content: flex-end; /* Align button to the right */
            margin-bottom: 20px; /* Optional: Add some space between the button and content */
            margin-top: 20px;
        }

        .printBtn {
            padding: 5px 10px;  /* Adjust padding for button */
            font-size: 18px;      /* Font size */
            background-color: #e48189; /* Button color */
            color: white;         /* Text color */
            border: none;         /* Remove border */
            border-radius: 5px;
            cursor: pointer;      /* Pointer cursor on hover */
        }

        .printBtn:hover {
            background-color: #e06c75; /* Darker button color on hover */
        }
        

        
    </style>
<body>

<aside id="sidebar">
            <div class="logo">Your Logo</div>
            <nav id="sidebar">
            <ul class="list-unstyled components">
            <li>
        <a href="main.php" class="nav-link active">Dashboard</a>
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

        <!-- Page Content -->
        <div id="content">
        <div class="menu-header d-flex justify-content-between align-items-center">
    <!-- Left section: Menu button and Dashboard Text -->
    <div class="d-flex align-items-center">
        <button type="button" id="sidebarCollapse" class="btn menu-btn mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
            </svg>
        </button>
        <span class="menu-text mb-4">Dashboard</span>
    </div>

    <!-- Right section: User Info and Print Button -->
    <div class="d-flex align-items-center">
        <span class="user mt-3"><h5>Hello, <?php echo htmlspecialchars($username); ?></h5></span>
        
        <button class="printBtn btn btn-primary ml-3 mt-2 mr-4" onclick="console.log('Print button clicked'); printCharts();">
            Charts
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
            </svg>
        </button>
    </div>
</div>

    <div class="col py-3">   
        <!-- Content area with cards -->
        <div class="row">
            <div class="col-md-3">
                <a href="active_students.php" class="card-link1">
                    <div class="card1 p-3 equal-height position-relative overflow-hidden">
                        <div class="icon-box d-flex justify-content-between align-items-center">
                            <h3 class="c1">Enrolled</h3>
                            <i class="bi bi-backpack2 position-absolute" 
                            style="color: #99b3ff; font-size: 5.5rem; right: -40px; top: 50%; 
                                    transform: translateY(-50%) scaleX(-1);">
                            </i>
                        </div>
                        <h5>Total Enrolled: <br><?php echo $enrolledStudentsCount; ?></h5>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="graduated_students.php" class="card-link2">
                    <div class="card2 p-3 equal-height position-relative overflow-hidden">
                        <div class="icon-box d-flex justify-content-between align-items-center">
                            <h3 class="c2">Graduates</h3>
                            <i class="bi bi-mortarboard position-absolute" 
                            style="color: #99ff99; font-size: 6rem; right: -40px; top: 50%; 
                                    transform: translateY(-50%) scaleX(-1);">
                            </i>
                        </div>
                        <h5>Total Graduates: <br><?php echo $graduateStudentsCount; ?></h5>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="inactive_students.php" class="card-link3">
                    <div class="card3 p-3 equal-height position-relative overflow-hidden">
                        <div class="icon-box d-flex justify-content-between align-items-center">
                            <h3 class="c3">Not Enrolled</h3>
                            <i class="bi bi-file-earmark-x position-absolute" 
                            style="color: #ff8080; font-size: 5rem; right: -30px; top: 50%; 
                                    transform: translateY(-50%) scaleX(-1);">
                            </i>
                        </div>
                        <h5>Total Not Enrolled: <br><?php echo $notEnrolledStudentsCount; ?></h5>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <div class="card4 p-3 equal-height" style="border-radius:10px;">
                    <h3>Quick Search</h3>
                    <form id="student-info-form">
                        <h6 class="mt-3">Student info.</h6>
                        <div class="d-flex">
                            <input type="text" style="border-radius:5px 0 0 5px;" class="form-control" id="student_no" name="student_no" placeholder="Student number" required>
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<!-- PRINT HEADER  -->
<header id="printHeader2" class="d-none d-print-block" style="margin-top: -40px;">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between header-container">
            <!-- Left: Small Logo -->
            <div class="logo-container" style="margin-left: 40px;">
                <img src="../Oserve/assets/img/cvsulogo.png" alt="University Logo" class="header-logo">
            </div>

            <!-- Center: University Details -->
            <div class="header-text text-center">
                <h5 style="margin: 0; font-size: 12pt;">Republic of the Philippines</h5>
                <h2 style="margin: 5px 0; font-size: 16pt;">Cavite State University</h2>
                <h4 style="margin: 5px 0; font-size: 14pt;">Carmona Campus</h4>
                <p class="ngi" style="margin: 5px 0; font-size: 10pt;">
                    Market Road, Carmona, Cavite <br>
                    ☏(046)487-6328 / cvsucarmona@cvsu.edu.ph <br>
                    www.cvsu.edu.ph
                </p>
            </div>

            <!-- Right: Empty Space -->
            <div class="empty-space"></div>
        </div>
    </div>
</header>
    <div class="row mt-4">
    <div class="col-md-6 mb-4">
    <div class="line-card d-print-block">
        <div class="card-header d-print-block">
            <h3 class="line-card-title text-center d-print-block" style="margin-bottom: -5px;">Monthly Counseling Session Status</h3>
        </div>
        <div class="card-body d-print-block">
            <canvas id="monthlyCounselingChart" style="height: 300px;"></canvas>
            
            <!-- Print Table for Chart Data -->
            <div class="print-chart-data d-none d-print-block mt-4">
                <!-- <h5 class="text-center">Monthly Counseling Sessions (Chart Data)</h5> -->
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Pending</th>
                            <th>Scheduled</th>
                            <th>Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        for ($i = 0; $i < count($months); $i++) {
                            echo "<tr>
                                <td>{$months[$i]}</td>
                                <td>" . number_format($pendingData[$i]) . "</td>
                                <td>" . number_format($scheduledData[$i]) . "</td>
                                <td>" . number_format($completedData[$i]) . "</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="col-md-6 mb-4">
    <div class="chart-card d-print-block">
        <div class="card-header text-center d-print-block">
            <h3 class="card-title d-print-block">Counseling Sessions Status</h3>
        </div>
        <div class="card-body d-print-block">
            <canvas id="counselingStatusChart"></canvas>

            <!-- Print Table for Chart Data -->
            <div class="print-chart-data d-none d-print-block mt-4">
                <h5 class="text-center">Counseling Sessions Status (Chart Data)</h5>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Pending</td>
                            <td><?php echo number_format($pendingCount); ?></td>
                        </tr>
                        <tr>
                            <td>Scheduled</td>
                            <td><?php echo number_format($scheduledCount); ?></td>
                        </tr>
                        <tr>
                            <td>Completed</td>
                            <td><?php echo number_format($completedCount); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="col-md-4 mb-4">
    <div class="chart-card d-print-block">
        <div class="card-header text-center d-print-block">
            <h3 class="card-title d-print-block">Total Male & Female</h3>
        </div>
        <div class="card-body d-print-block">
            <canvas id="genderChart"></canvas>

            <!-- Print Table for Chart Data -->
            <div class="print-chart-data d-none d-print-block mt-4">
                <h5 class="text-center">Total Male & Female (Chart Data)</h5>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Gender</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Male</td>
                            <td><?php echo number_format($maleCount); ?></td>
                        </tr>
                        <tr>
                            <td>Female</td>
                            <td><?php echo number_format($femaleCount); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="col-md-4 mb-4">
    <div class="chart-card d-print-block">
        <div class="card-header text-center d-print-block">
            <h3 class="card-title d-print-block">Student's Status Pie Chart</h3>
        </div>
        <div class="card-body d-print-block">
            <canvas id="studentStatusChart"></canvas>

            <!-- Print Table for Chart Data -->
            <div class="print-chart-data d-none d-print-block mt-4">
                <h5 class="text-center">Student's Status (Chart Data)</h5>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Enrolled</td>
                            <td><?php echo number_format($enrolledStudentsCount); ?></td>
                        </tr>
                        <tr>
                            <td>Graduate</td>
                            <td><?php echo number_format($graduateStudentsCount); ?></td>
                        </tr>
                        <tr>
                            <td>Not Enrolled</td>
                            <td><?php echo number_format($notEnrolledStudentsCount); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="col-md-4 mb-4">
    <div class="chart-card d-print-block">
        <div class="card-header text-center d-print-block">
            <h3 class="card-title d-print-block">Violation Types Distribution</h3>
        </div>
        <div class="card-body d-print-block">
            <canvas id="violationTypeChart"></canvas>

            <!-- Print Table for Chart Data -->
            <div class="print-chart-data d-none d-print-block mt-4">
                <h5 class="text-center">Violation Types (Chart Data)</h5>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Violation Type</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Major Violations</td>
                            <td><?php echo number_format($majorViolationCount); ?></td>
                        </tr>
                        <tr>
                            <td>Minor Violations</td>
                            <td><?php echo number_format($minorViolationCount); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

<!-- Modal Structure -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Event Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Event details will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Event Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Event details will be displayed here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Student Information Modal -->
<div class="modal fade" id="studentInfoModal" tabindex="-1" role="dialog" aria-labelledby="studentInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentInfoModalLabel"><strong>STUDENT INFORMATION</strong></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="student-info"></div>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                    <button id="printButton" class="btn btn-primary" style="background: #e48189; border:none;">Print</button>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script type="text/javascript">
        
    </script>
    
    <script type="text/javascript">
        $(document).ready(function () {
            // Initialize scrollbar
            $("#sidebar").mCustomScrollbar({
                theme: "minimal"
            });
            // Toggle sidebar on button click
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar, #content').toggleClass('active');
                $('.collapse.in').toggleClass('in');
                $('a[aria-expanded=true]').attr('aria-expanded', 'false');
            });
        });





document.addEventListener('DOMContentLoaded', function() {
    fetch('get_events.php')
        .then(response => response.json())
        .then(events => {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: events,
                eventMouseEnter: function(info) {
                    var eventInfo = document.getElementById('event-info');
                    eventInfo.innerHTML = `
                        <strong>${info.event.title}</strong><br>
                        Description: ${info.event.extendedProps.description}
                        Date: ${info.event.start.toLocaleDateString()}<br>
                        Time: ${info.event.start.toLocaleTimeString()}<br>
                        Program: ${info.event.extendedProps.event_program}<br>
                        
                    `;
                    eventInfo.style.display = 'block';
                    eventInfo.style.left = info.jsEvent.pageX + 'px';
                    eventInfo.style.top = info.jsEvent.pageY + 'px';
                },
                eventMouseLeave: function() {
                    var eventInfo = document.getElementById('event-info');
                    eventInfo.style.display = 'none';
                },
                eventClick: function(info) {
                    // Populate the modal with full event details
                    var modalBody = document.querySelector('#eventModal .modal-body');
                    modalBody.innerHTML = `
                        <p style="color: #333; margin-bottom:10px;">Title: ${info.event.title}</p>
                        <p style="color: #333;">Description: ${info.event.extendedProps.description}</p>
                        <p style="color: #333;">Date: ${info.event.start.toLocaleDateString()}</p>
                        <p style="color: #333;">Time: ${info.event.start.toLocaleTimeString()}</p>
                        <p style="color: #333;">Program: ${info.event.extendedProps.event_program}</p>
                    `;

                    // Show the modal
                    $('#eventModal').modal('show');
                },
                dateClick: function(info) {
                    // Optionally handle date clicks here if needed
                }
            });
            calendar.render();
        })
        .catch(error => console.error('Error fetching events:', error));
});





$(document).ready(function() {
            $('.form-control').focus(function() {
                $(this).siblings('label').addClass('label-hidden');
            });

            $('.form-control').blur(function() {
                if (!$(this).val()) {
                    $(this).siblings('label').removeClass('label-hidden');
                }
            });

            // Handle the "See Your Information" form submission
            $('#student-info-form').submit(function(e) {
                e.preventDefault();
                var student_no = $('#student_no').val();

                // Send AJAX request to get student information
                $.ajax({
                    url: 'get_student_info.php',
                    type: 'POST',
                    data: { student_no: student_no },
                    dataType: 'json',
                    success: function(response) {
    if (response.error) {
        $('#student-info').html('<p>' + response.error + '</p>');
    } else {
        var studentInfo = '<p><strong style="color:rgb(54, 56, 58); font-weight: bold;">Student No:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;"> ' + response.student_no + '</span></p>';
        studentInfo += '<p><strong style="color: rgb(54, 56, 58); font-weight: bold;">First Name:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;">' + response.first_name + '</span></p>';
        studentInfo += '<p><strong style="color:rgb(54, 56, 58); font-weight: bold;">Surname:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;">' + response.surname + '</span></p>';
        studentInfo += '<p><strong style="color:rgb(54, 56, 58); font-weight: bold;">Middle Name:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;">' + response.middle_name + '</span></p>';
        studentInfo += '<p><strong style="color:rgb(54, 56, 58); font-weight: bold;">Gender:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;">' + response.gender + '</span></p>';
        
        studentInfo += '<p><strong style="color:rgb(54, 56, 58); font-weight: bold;">Status:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;">' + response.status + '</span></p>';
        studentInfo += '<p><strong style="color:rgb(54, 56, 58); font-weight: bold;">Program:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;">' + (response.program_name ? response.program_name : 'N/A') + '</span></p>';

        // Display violation details
        if (response.violations.length > 0) {
            studentInfo += '<p><strong style="color:rgb(54, 56, 58); font-weight: bold;">Violations:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;"> </span> </p>';
            studentInfo += '<ul>';
            response.violations.forEach(function(violation) {
                studentInfo += '<li>';
                studentInfo += '<p><strong style="color:rgb(54, 56, 58); font-weight: bold;">Violation Type:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;">' + violation.type_of_violation + '</span></p>';
                studentInfo += '<p><strong style="color:rgb(54, 56, 58); font-weight: bold;">Violation Details:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;">' + violation.full_info + '</span></p>';
                studentInfo += '</li>';
            });
            studentInfo += '</ul>';
        } else {
            studentInfo += '<p><strong style="color:rgb(54, 56, 58); font-weight: bold;">Violations:</strong> <span style="color: rgb(89, 89, 90); font-weight: bold;"> N/A </span></p>';
        }

        $('#student-info').html(studentInfo);
    }

    // Show the modal
    $('#studentInfoModal').modal('show');
},


                    error: function() {
                        $('#student-info').html('<p>An error occurred while fetching student information.</p>');
                        $('#studentInfoModal').modal('show');
                    }
                });
            });
        });
        document.getElementById('student_no').addEventListener('input', function(e) {
    let value = this.value.replace(/[^0-9]/g, '');
    if (value.length > 9) {
        value = value.slice(0, 9);
    }
    this.value = value;
});

    document.getElementById('printButton').addEventListener('click', function() {
    // Get the modal content
    var modalContent = document.querySelector('#studentInfoModal .modal-content').cloneNode(true);
    
    // Remove the modal title and close button
    var modalHeader = modalContent.querySelector('.modal-header');
    if (modalHeader) {
        modalHeader.remove();
    }

    // Remove the modal footer (buttons)
    var modalFooter = modalContent.querySelector('.modal-footer');
    if (modalFooter) {
        modalFooter.remove();
    }

    // Get the original page content
    var originalContents = document.body.innerHTML;

    // Add the custom print header and footer
    var printHeader = `
        <header id="printHeader" class="d-print-block">
            <style>
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
            </style>
            <div class="container">
                <div class="row">
                    <!-- Image on the left side -->
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <img src="../Oserve/assets/img/cvsulogo.png" alt="University Logo" class="img-fluid">
                    </div>
                    <!-- Centered Text -->
                    <div class="col-md-8 text-center">
                        <h5 style="font-weight:70px;">Republic of the Philippines</h5>
                        <h2 style="font-size:26px;">Cavite State University</h2>
                        <h4>Carmona Campus</h4>
                        <p class="ngi">Market Road, Carmona, Cavite <br>
                        (046)487-6328/cvsucarmona@cvsu.edu.ph <br>
                        www.cvsu.edu.ph</p>
                    </div>
                    <!-- Empty column to push text to the center -->
                    <div class="col-md-2"></div>
                </div>
            </div>
            <h1 id="printHeader" class="d-none d-print-block text-center mb-4">Student Record</h1>
        </header>`;

    var printFooter = `
        <footer id="printFooter" class="d-print-block" style="text-align: center;">
            <hr>
            <p>Prepared by:</p><br>
            <p>Prepared for:</p>
        </footer>`;

    // Replace the body content with the print layout (header, modal content, footer)
    document.body.innerHTML = printHeader + modalContent.innerHTML + printFooter;

    // Trigger the print dialog
    window.print();

    // Revert the body back to the original content after printing
    document.body.innerHTML = originalContents;

    // Reload the page to restore functionality
    window.location.reload();
});
function confirmLogout() {
    // Use jQuery to trigger the Bootstrap modal
    $('#logoutModal').modal('show');
    
    // Prevent further link execution
    return false;
}

//Student's Status Pie Chart
var ctx = document.getElementById('studentStatusChart').getContext('2d');
var studentStatusChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Enrolled', 'Graduate', 'Not Enrolled'],
        datasets: [{
            data: [<?php echo $enrolledStudentsCount; ?>, <?php echo $graduateStudentsCount; ?>, <?php echo $notEnrolledStudentsCount; ?>],
        backgroundColor: [
            '#99b3ff',   // for #99b3ff (light blue)
            '#99ff99',   // for #99ff99 (light green)
            
            '#ff8080'    // for #ff8080 (light red)
        ],
        borderColor: [
            '#99b3ff',   // for #99b3ff (light blue)
            '#99ff99',   // for #99ff99 (light green)
            
            '#ff8080'    // for #ff8080 (light red)
        ],
            borderWidth: 1
        }]
    },
    options: {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
        padding: 10
    },
    plugins: {
        datalabels: {
            formatter: (value, context) => {
                const total = context.chart.data.datasets[0].data.reduce((acc, val) => acc + val, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                const formattedValue = value.toLocaleString(); // Add comma for 4+ digit values
                return `${percentage}%\n(${formattedValue})`; // Display percentage & formatted value
            },
            color: '#4d4d4d',
            font: {
                size: 14,
                weight: 'bold'
            },
            anchor: 'center',
            align: 'center',
            clip: true,
        },
        legend: {
            labels: {
                font: {
                    size: 14
                }
            }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.raw.toLocaleString(); // Format value with comma
                    const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                    const percentage = ((context.raw * 100) / total).toFixed(1);
                    return `${label}: ${percentage}% (${value})`;
                }
            }
        }
    }
},
    plugins: [ChartDataLabels] // Add the plugin here
});

//Total Male & Female
var genderCtx = document.getElementById('genderChart').getContext('2d');
var genderChart = new Chart(genderCtx, {
    type: 'pie',
    data: {
        labels: ['Male Students', 'Female Students'],
        datasets: [{
            data: [<?php echo $maleCount; ?>, <?php echo $femaleCount; ?>],
            backgroundColor: [
                '#80bdff',   // for #007bff (blue)
                '#f6a2be'    // for #f06090 (pink)
            ],
            borderColor: [
                '#80bdff',   // for #007bff (blue)
                '#f6a2be'    // for #f06090 (pink)
            ],
            borderWidth: 1
        }]
    },
    options: {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
        padding: 10
    },
    plugins: {
        datalabels: {
            formatter: (value, context) => {
                const total = context.chart.data.datasets[0].data.reduce((acc, val) => acc + val, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                const formattedValue = value.toLocaleString(); // Add comma for 4+ digit values
                return `${percentage}%\n(${formattedValue})`; // Display percentage & formatted value
            },
            color: '#4d4d4d',
            font: {
                size: 14,
                weight: 'bold'
            },
            anchor: 'center',
            align: 'center',
            clip: true,
        },
        legend: {
            labels: {
                font: {
                    size: 14
                }
            }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.raw.toLocaleString(); // Format value with comma
                    const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                    const percentage = ((context.raw * 100) / total).toFixed(1);
                    return `${label}: ${percentage}% (${value})`;
                }
            }
        }
    }
},
    plugins: [ChartDataLabels] // Add the plugin here
});



//Counseling Sessions Status
var counselingStatusCtx = document.getElementById('counselingStatusChart').getContext('2d');
var counselingStatusChart = new Chart(counselingStatusCtx, {
    type: 'pie',
    data: {
        labels: ['Pending', 'Scheduled', 'Completed'],
        datasets: [{
            data: [<?php echo $pendingCount; ?>, <?php echo $scheduledCount; ?>, <?php echo $completedCount; ?>],
            backgroundColor: [
                '#ffc14d',   // for #ffc14d (light orange)
                '#99b3ff',  // for #99b3ff (light blue)
                '#99ff99'   // for #99ff99 (light green)
                
                
            ],
            borderColor: [
                '#ffc14d',    // for #ffc14d (light orange)
                '#99b3ff',  // for #99b3ff (light blue)
                '#99ff99'   // for #99ff99 (light green)
                
                
            ],
            borderWidth: 1
        }]
    },
    options: {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
        padding: 10 // Adds space around the chart
    },
    plugins: {
        datalabels: {
            formatter: (value, context) => {
                const total = context.chart.data.datasets[0].data.reduce((acc, val) => acc + val, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return `${percentage}%\n(${value})`; // Show both inside the chart
            },
            color: '#4d4d4d', // Change to white for better contrast
            font: {
                size: 14,
                weight: 'bold'
            },
            anchor: 'center', // Centers the label inside the segment
            align: 'center',  // Ensures text stays inside
            clip: true, // Prevents text from overflowing
        },
        legend: {
            labels: {
                font: {
                    size: 14
                }
            }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.raw;
                    const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                    const percentage = ((value * 100) / total).toFixed(1);
                    return `${label}: ${percentage}% (${value})`;
                }
            }
        }
    }
},
    plugins: [ChartDataLabels] // Add the plugin here
});


//Monthly Counseling Sessions
var monthlyCtx = document.getElementById('monthlyCounselingChart').getContext('2d');
var monthlyCounselingChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [
            {
                label: 'Pending',
                data: <?php echo json_encode($pendingData); ?>,
                borderColor: '#ffc14d',
                backgroundColor: '#ffc14d',
                tension: 0.4
            },
            {
                label: 'Scheduled',
                data: <?php echo json_encode($scheduledData); ?>,
                borderColor: '#99b3ff',
                backgroundColor: '#99b3ff',
                tension: 0.4
            },
            {
                label: 'Completed',
                data: <?php echo json_encode($completedData); ?>,
                borderColor: '#99ff99',
                backgroundColor: '#99ff99',
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                position: 'top'
            }
        }
    }
});

//Violation Types Distribution
var violationTypeCtx = document.getElementById('violationTypeChart').getContext('2d');
var violationTypeChart = new Chart(violationTypeCtx, {
    type: 'pie',
    data: {
        labels: ['Major Violations', 'Minor Violations'],
        datasets: [{
            data: [<?php echo $majorViolationCount; ?>, <?php echo $minorViolationCount; ?>],
            backgroundColor: [
                '#b5add2',
                '#c69d6c', 
            ],
            borderColor: [
                '#b5add2', 
                '#c69d6c'
            ],
            borderWidth: 1
        }]
    },
    options: {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
        padding: 10
    },
    plugins: {
        datalabels: {
            formatter: (value, context) => {
                const total = context.chart.data.datasets[0].data.reduce((acc, val) => acc + val, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                const formattedValue = value.toLocaleString(); // Add comma formatting
                return `${percentage}%\n(${formattedValue})`; // Show percentage & value inside the chart
            },
            color: '#4d4d4d', // White text inside chart
            font: {
                size: 14,
                weight: 'bold'
            },
            anchor: 'center',
            align: 'center',
            clip: true,
        },
        legend: {
            labels: {
                font: {
                    size: 14
                }
            }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.raw.toLocaleString(); // Format value with commas
                    const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                    const percentage = ((context.raw * 100) / total).toFixed(1);
                    return `${label}: ${percentage}% (${value})`;
                }
            }
        }
    }
},
    plugins: [ChartDataLabels] // Add the plugin here
});


//print all charts
function printCharts() {
    const printArea = document.getElementById("print-area");
    printArea.innerHTML = ""; // Clear previous print content

    const charts = [
        { id: "monthlyCounselingChart", title: "Monthly Counseling Sessions" },
        { id: "studentStatusChart", title: "Student's Status Pie Chart" },
        { id: "genderChart", title: "Total Male & Female" },
        { id: "counselingStatusChart", title: "Counseling Sessions Status" },
        { id: "violationTypeChart", title: "Violation Types Distribution" }
    ];

    charts.forEach(chartInfo => {
        let canvas = document.getElementById(chartInfo.id);
        if (!canvas) return;

        let img = document.createElement("img");
        img.src = canvas.toDataURL("image/png");
        img.style.width = "100%";
        img.style.display = "block";
        img.style.margin = "10px 0";

        let chartTitle = document.createElement("h3");
        chartTitle.innerText = chartInfo.title;
        chartTitle.style.textAlign = "center";

        let chartContainer = document.createElement("div");
        chartContainer.appendChild(chartTitle);
        chartContainer.appendChild(img);

        printArea.appendChild(chartContainer);
    });

    // **Trigger the print directly on the page**
    printArea.style.display = "block";
    window.print();
    printArea.style.display = "none"; // Hide after printing
}
function printCharts() {
    setTimeout(() => {
        window.print();
    }, 500); // Delay to ensure charts render
}

function printCharts() {
    $('#eventModal').modal('hide'); // Close modal before printing

    setTimeout(() => {
        window.print();
    }, 500);
}

    </script>
</body>
</html>
