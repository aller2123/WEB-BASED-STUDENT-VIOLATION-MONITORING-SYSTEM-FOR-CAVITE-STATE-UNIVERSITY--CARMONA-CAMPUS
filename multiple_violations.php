<?php
session_start();
require 'dbconfig.php';

// Redirect unauthorized users
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin', 'staff', 'admin_cs', 'admin_csd', 'admin_pc'])) {
    header('Location: index.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

    // Pagination setup
    $itemsPerPage = 10; // Adjust as needed
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $itemsPerPage;
    
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $whereClause = $search ? "WHERE (student_names LIKE :search OR program_related LIKE :search) AND is_archived = 0" : "WHERE is_archived = 0";

    $stmt = $pdo->prepare("SELECT * FROM multiple_violations $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    if ($search) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $multipleViolations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update total records query for pagination
    $totalRecordsStmt = $pdo->prepare("SELECT COUNT(*) FROM multiple_violations $whereClause");
    if ($search) {
        $totalRecordsStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $totalRecordsStmt->execute();
    $totalRecords = $totalRecordsStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $itemsPerPage);
} catch (PDOException $e) {
    die("Could not connect to the database $database :" . $e->getMessage());
}

// Rest of the PHP logic for multiple violations...
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
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Dosis:300,400,500,,600,700,700i|Lato:300,300i,400,400i,700,700i" rel="stylesheet">
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
        .btn-secondary {
            background-color: transparent;
            color: #444444;
            border: none;
            border-radius: 20px;
        }

        .btn-secondary:not(:active) {
            border: 1px solid #aa5082;
        }

        .btn-secondary:hover {
            background-color: #74425d;
            color: #d8f0c6;
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
    #sidebar, .pagination-container, .btn, .form-control {
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
    .page-link {
        display: none !important;
    }

    /* Main table column hiding */
    #mainTable thead th:nth-child(5), /* Main table Info */
    #mainTable thead th:nth-child(8), /* Main table Action */
    #mainTable tbody td:nth-child(5), /* Main table Info */
    #mainTable tbody td:nth-child(8)  /* Main table Action */ {
        display: none !important;
    }

    /* Alternate table column hiding */
    #multipleViolationsTable thead th:nth-child(5), /* Alternate table Info */
    #multipleViolationsTable thead th:nth-child(8), /* Alternate table Action */
    #multipleViolationsTable tbody td:nth-child(5), /* Alternate table Info */
    #multipleViolationsTable tbody td:nth-child(8)  /* Alternate table Action */ {
        display: none !important;
    }

    /* Archived Violation table column hiding */
    #archivedViolationsTable thead th:nth-child(5), /* Archived table Details */
    #archivedViolationsTable thead th:nth-child(8), /* Archived table Action */
    #archivedViolationsTable tbody td:nth-child(5), /* Archived table Details */
    #archivedViolationsTable tbody td:nth-child(8)  /* Archived table Action */ {
        display: none !important;
    }
    /* Archived Counseling table column hiding */
    #archivedGroupViolationsTable thead th:nth-child(5), /* Archived table Details */
    #archivedGroupViolationsTable thead th:nth-child(7), /* Archived table Action */
    #archivedGroupViolationsTable tbody td:nth-child(5), /* Archived table Details */
    #archivedGroupViolationsTable tbody td:nth-child(7)  /* Archived table Action */ {
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

        /* .highlighted-row {
    background-color: #e6f7ff !important;
    transition: background-color 0.3s ease;
} */
/* .table tbody tr:hover {
    cursor: pointer;
    background-color: #f5f5f5;
} */

/* #mainTable, #alternateTable {
    cursor: pointer;
} */


    </style>
<body>


   

<aside id="sidebar">
            <div class="logo">Your Logo</div>
            <nav id="sidebar">
            <ul class="list-unstyled components">
            <li>
        <a href="main.php" class="nav-link ">Dashboard</a>
    </li>
    <li>
    <li>
    <a href="#studentSubmenu" data-toggle="collapse" aria-expanded="false" class="nav-link dropdown-toggle">Student Menu</a>
        <ul class="collapse list-unstyled" id="studentSubmenu">
            <li><a href="student.php">Students</a></li>
            <li><a href="javascript:void(0);" class="submenu-item" onclick="showArchiveTable()">Archive</a></li>
            <!-- <li><a href="javascript:void(0);" onclick="showArchiveTable()">Archive</a></li> -->
            <!-- <li><a href="#">sub-menu3</a></li> -->
        </ul>
    </li>
   
    <?php if ($_SESSION['role'] == 'superadmin'): ?>
       
        <li>
            <a href="#counselingSubmenu" data-toggle="collapse" aria-expanded="false" class="nav-link dropdown-toggle" onclick="toggleDropdown()">Counseling Menu</a>
            <ul class="collapse list-unstyled" id="counselingSubmenu">
            <li><a href="counseling.php">Students</a></li>
            <li><a href="multiple_counseling.php" class="submenu-item">Groups</a></li>

            <!-- <li><a href="#" onclick="showAlternateTable()">Groups</a></li> -->
                <!-- <li><a href="#" onclick="showArchived()">Archive Counselings</a></li>
                <li><a href="#" onclick="showGroupArchived()">Archive Group Counselings</a></li> -->
            </ul>
        </li>
        <li>
            <a href="#violationSubmenu" data-toggle="collapse" aria-expanded="false" class="nav-link active dropdown-toggle" onclick="toggleDropdown()">Violation Menu</a>
            <ul class="collapse list-unstyled" id="violationSubmenu">
                <li><a href="violation.php">Students</a></li>
                <li><a href="multiple_violations.php">Groups</a></li>

                <!-- <li><a href="javascript:void(0);" onclick="showArchiveTable()">Archive Violations</a></li>
                <li><a href="javascript:void(0);" onclick="toggleArchivedGroupViolations()">Archive Group Violations</a></li>  -->
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
            <a href="#counselingSubmenu" data-toggle="collapse" aria-expanded="false" class="nav-link dropdown-toggle" onclick="toggleDropdown()">Counseling Menu</a>
            <ul class="collapse list-unstyled" id="counselingSubmenu">
            <li><a href="counseling.php">Students</a></li>
            <li><a href="multiple_counseling.php" class="submenu-item">Groups</a></li>
                <!-- <li><a href="#" onclick="showArchived()">Archive Counselings</a></li>
                <li><a href="#" onclick="showGroupArchived()">Archive Group Counselings</a></li> -->
            </ul>
        </li>
        <li>
            <a href="#violationSubmenu" data-toggle="collapse" aria-expanded="false" class="nav-link active dropdown-toggle" onclick="toggleDropdown()">Violation Menu</a>
            <ul class="collapse list-unstyled" id="violationSubmenu">
                <li><a href="violation.php">Students</a></li>
                <li><a href="multiple_violations.php">Groups</a></li>

                <!-- <li><a href="javascript:void(0);" onclick="showArchiveTable()">Archive Violations</a></li>
                <li><a href="javascript:void(0);" onclick="toggleArchivedGroupViolations()">Archive Group Violations</a></li>  -->
            </ul>
        </li>
        
    <?php else: ?>
       
        <li>
            <a href="violation.php" class="nav-link">Violation</a>
        </li>
        
    <?php endif; ?>
    <li>
        <a href="logout.php" class="nav-link" onclick="return confirmLogout()">Logout</a>
    </li>
</ul>

            </nav>
        </aside>
        

        <!-- Page Content -->
        <div id="content">
            <div class="menu-header">
                <button type="button" id="sidebarCollapse" class="btn menu-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                    </svg>
                </button>
                <span class="menu-text">Group Violations</span>
            </div>
            <div id="students-section" class="col py-3">
            <div id="students-section" style="padding-top:6px; padding-left:10px">
            <div class="d-flex justify-content-between align-items-center mb-1">
            
                <!-- Left-side buttons -->
                <div class="d-flex align-items-center">
                    <a href="add_multiple_violations.php" class="btn btn-outline-primary mb-3 mr-1">
                        <span class="d-flex align-items-center justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-people mr-2" viewBox="0 0 16 16">
                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                            </svg>
                            Multiple
                        </span>
                    </a>   

                    <form action="" method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search by student name or program" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                                <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
                                    <a href="multiple_violations.php" class="btn btn-outline-secondary">Clear</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Right-side print button -->
                <div class="d-flex align-items-center">
                    <button id="printButton" class="btn btn-success mb-3" style="background: #e48189; border:none;">
                        Print
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                            <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/>
                            <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                        </svg>
                    </button>
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
                        (046)487-6328/cvsucarmona@cvsu.edu.ph <br>
                        www.cvsu.edu.ph</p>
                    </div>
                    <!-- Empty column to push text to the center -->
                    <div class="col-md-2"></div>
                </div>
            </div>
            <h1 id="printHeader" class="d-none d-print-block text-center mb-4">Violation Records</h1>
        </header>
                <table id="multipleViolationsTable" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Student Names</th>
                            <th>Year & Section</th>
                            <th>Program</th>
                            <th>Type</th>
                            <th>Info</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($multipleViolations as $violation): ?>
                            <tr>
                                <td><?= htmlspecialchars($violation['student_names']) ?></td>
                                <td><?= htmlspecialchars($violation['y_and_s']) ?></td>
                                <td><?= htmlspecialchars($violation['program_related']) ?></td>
                                <td><?= htmlspecialchars($violation['type']) ?></td>
                                <td>
                                    <!-- Add a button to view details in a modal -->
                                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#infoModal<?= $violation['id'] ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                        </svg>
                                    </button>
                                </td>
                                <td><?= htmlspecialchars($violation['status']) ?></td>
                                <td><?= htmlspecialchars($violation['created_at']) ?></td>
                                <td>
                                    <!-- Add edit and archive buttons -->
                                        <div class="d-flex align-items-center">
                                        <a href="edit_multiple_violations.php?id=<?= urlencode($violation['id']) ?>" class="btn btn-outline-success mr-1 btn-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                                            <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z"/>
                                        </svg>
                                        </a>
                                        <a href="#" class="btn btn-outline-warning btn-md" 
                                        data-toggle="modal" data-target="#archiveMultipleConfirmationModal" 
                                        onclick="setArchiveMultipleLink('archive_multiple_violations.php?id=<?= urlencode($violation['id']) ?>')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                                                <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                            </svg>
                                        </a>

                                    </div>
                                </td>
                            </tr>
                            
                            
                            <!-- Modal for violation info -->
                            <div class="modal fade" id="infoModal<?= $violation['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel<?= $violation['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <!-- Modal Header -->
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="infoModalLabel<?= $violation['id'] ?>">Violation Information</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <!-- Modal Body -->
                                        <div class="modal-body">
                                            <strong>Violation Details:</strong> <?= htmlspecialchars($violation['info']) ?>
                                        </div>

                                        <!-- Modal Footer -->
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    </tbody>
                </table>

<!-- Archive Multiple Violations Confirmation Modal -->
<div class="modal fade" id="archiveMultipleConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="archiveMultipleConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveMultipleConfirmationModalLabel">Confirm Archive Multiple Violations</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to archive this multiple violation?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmArchiveMultipleButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="mainTablePagination pagination-container">
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-end">
            <!-- First Button -->
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=1" style="background: <?php echo ($page <= 1 ? '#e6d6ff' : '#d0b3ff'); ?>; border:none; color: <?php echo ($page <= 1 ? 'gray' : 'white'); ?>;">First</a>
            </li>

            <!-- Previous Button -->
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" style="background: #d0b3ff; border:none;">Previous</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" style="background: #e6d6ff; border:none;" tabindex="-1">Previous</a>
                </li>
            <?php endif; ?>

            <!-- Page Numbers -->
            <?php 
            $maxPagesToShow = 5;
            $startPage = max(1, $page - floor($maxPagesToShow / 2));
            $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
            
            if ($endPage - $startPage + 1 < $maxPagesToShow) {
                $startPage = max(1, $endPage - $maxPagesToShow + 1);
            }

            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <?php if ($page == $i): ?>
                    <li class="page-item active">
                        <a class="page-link" href="#" style="background: #46354e; border:none;"><?php echo $i; ?></a>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $i; ?>" style="background: #644c70; border:none;"><?php echo $i; ?></a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- Next Button -->
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" style="background: #644c70; border:none;">Next</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" style="background: #e6d6ff; border:none;" tabindex="-1">Next</a>
                </li>
            <?php endif; ?>

            <!-- Last Button -->
            <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $totalPages; ?>" style="background: <?php echo ($page >= $totalPages ? '#e6d6ff' : '#644c70'); ?>; border:none; color: <?php echo ($page >= $totalPages ? 'gray' : 'white'); ?>;">Last</a>
            </li>
        </ul>
    </nav>
</div>

                        </div>
                    
                        
                        </div>
                
            
    
        

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- jQuery (necessary for Bootstrap and other JS plugins to function) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS (if needed) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- mCustomScrollbar JS and CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>

<script>
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

    $('#printButton').click(function () {
        document.getElementById('printHeader').style.display = 'block';
        window.print();
        document.getElementById('printHeader').style.display = 'none';
    });

    function showAlert(type, message) {
        var alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type + ' alert-show';
        alertDiv.innerHTML = message;
        document.body.appendChild(alertDiv);

        setTimeout(function () {
            alertDiv.style.opacity = '0';
            setTimeout(function () {
                alertDiv.remove();
            }, 500);
        }, 3000);
    }

    $('#toggleTableView').click(function() {
        $('#mainTable, #alternateTable').toggle();
        $(this).text(function(i, text) {
            return text === "Student w/ violation" ? "Group w/ violation" : "Student w/ violation";
        });
    });

    $('[data-toggle="tooltip"]').tooltip();
});

function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}

//Archive Confirmation Modal
function setArchiveMultipleLink(actionUrl) {
    // Attach form submission to the confirm button
    document.getElementById('confirmArchiveMultipleButton').onclick = function() {
        window.location.href = actionUrl; // Redirect to the archive link
    };
}
</script>

</body>
</html>

=