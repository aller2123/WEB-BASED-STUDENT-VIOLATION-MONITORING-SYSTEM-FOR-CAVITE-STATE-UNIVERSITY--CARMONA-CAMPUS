<?php
// Start the session and include the utils.php file
session_start();
require 'C:\xampp\htdocs\Oserve\utils\utils.php';

// Get the PDO object
$pdo = getPDO();

// Fetch history for ALL users (no user ID passed to getHistory)
$history = getHistory($pdo); 
// Now $history contains an array of history records that you can display in your HTML


$entriesPerPage = 10; // Number of actions per page

// Total number of history entries
$totalEntries = count($history);
$totalPages = ceil($totalEntries / $entriesPerPage); // Total number of pages

// Get the current page from the query string, default to 1
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages)); // Ensure it's within range

// Calculate the offset for the current page
$offset = ($currentPage - 1) * $entriesPerPage;

// Slice the history array for the current page
$currentActions = array_slice($history, $offset, $entriesPerPage);

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Activity History</title>
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
    
    <style>
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

/* PRINT CSS */
@media print {
    /* Hide unnecessary elements for printing */
    .menu-text,
    .sus,
    .pagination,
    .btn,
    .input-group {
        display: none !important;
    }

    body {
        margin: 0; /* Remove default margins */
        padding: 0; /* Remove default padding */
        counter-reset: page; /* Initialize the page counter */
    }

    /* Ensure elements use full page width */
    .container, .content, .menu-header {
        width: 100%;
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
        background: white; /* Ensure footer background is white */
        z-index: 1000; /* Ensure footer is on top */
    }

    #printFooter p {
        margin: 0;
        text-align: left !important;
    }

    /* Display the current page number */
    #printFooter .page-number:before {
        counter-increment: page; /* Increment the page counter */
        content: "Page " counter(page); /* Display the current page number */
    }

    /* Print header styles */
    #printHeader {
        display: flex;
        align-items: center;
        width: 100%;
        margin-bottom: 20px; /* Add margin below header */
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

    /* Adjust table layout to avoid overlapping with footer */
    .history {
        margin-bottom: 100px; /* Increase space for the footer */
        page-break-inside: auto; /* Prevent breaking the table within a page */
    }

    table {
        width: 100%; /* Ensure table uses full page width */
        border-collapse: collapse; /* Collapse borders to avoid extra spacing */
    }

    th, td {
        padding: 8px; /* Add padding for readability */
        border: 1px solid #ddd; /* Add borders to table cells */
    }

    /* Ensure the table does not extend into the footer area */
    tr {
        page-break-inside: avoid; /* Avoid breaking rows inside pages */
    }

    thead {
        display: table-header-group; /* Keep table header visible on each page */
    }

    tfoot {
        display: table-footer-group; /* Keep table footer visible on each page */
    }
}

/*--------------------------------------------------------------
# Back to top button
--------------------------------------------------------------*/
.back-to-top {
    position: fixed;
    bottom: 20px; /* Adjust position as needed */
    right: 20px; /* Adjust position as needed */
    display: none; /* Initially hidden */
    background-color: #00e600; /* Button color */
    color: white; /* Icon color */
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    cursor: pointer;
    text-align: center;
    z-index: 1000; /* Ensure it's on top of other content */
    transition: opacity 0.3s ease, transform 0.3s ease;
}

/* Show the button */
.back-to-top.show {
    display: block; /* Show the button */
    opacity: 1; /* Fully opaque */
    transform: translateY(0); /* No vertical movement */
}

/* Hidden state with reduced opacity */
.back-to-top {
    opacity: 0; /* Fully transparent */
    transform: translateY(20px); /* Start from below */
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
            <a href="history.php" class="nav-link active">History</a>
        </li>
        <li>
                <a href="setting.php" class="nav-link ">Settings</a>
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
    <div class="menu-header">
        <button type="button" id="sidebarCollapse" class="btn menu-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
            </svg>
        </button>
        <span class="menu-text">History</span>
        <div class="col py-3">
            <div id="students-section" style="padding-top:15px; padding-left:10px">
                <!-- Search Bar, Search Button, and Print Button aligned horizontally -->
                <div class="d-flex justify-content-between align-items-center" style="padding-bottom: 10px;">
                    <h2 class="sus" style="padding-left:15px; margin: 0;">Activity Log</h2>
                    <div class="input-group" style="max-width: 600px;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search time, day, week, month, year or user">
                        <div class="input-group-append">
                            <button id="searchButton" class="btn btn-outline-primary" type="button">Search</button>
                            <button id="printButton" class="btn btn-success ml-1" style="background: #e48189; border:none; color:white;">
                Print
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/>
                    <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                </svg>
            </button>
                        </div>
                    </div>
                </div>
                <!-- Display user history -->
                <div class="history">
                    <?php if (!empty($history)): ?>

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
                                <h1 id="printHeader" class="d-none d-print-block text-center mb-4">Activity Log</h1>
                            </header>

                                    <table id="historyTable" class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Day</th>
                                                <th>Week</th>
                                                <th>Month</th>
                                                <th>Year</th>
                                                <th>Date/Time</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($currentActions as $entry): ?>
                                                <tr>
                                                    <td><?php echo date('l', strtotime($entry['timestamp'])); ?></td>
                                                    <td><?php echo date('W', strtotime($entry['timestamp'])); ?></td>
                                                    <td><?php echo date('F', strtotime($entry['timestamp'])); ?></td>
                                                    <td><?php echo date('Y', strtotime($entry['timestamp'])); ?></td>
                                                    <td>
                                                        <?php echo date('m/d/Y', strtotime($entry['timestamp'])); ?><br>
                                                        <span><?php echo date('h:i A', strtotime($entry['timestamp'])); ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($entry['action']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <nav aria-label="Page navigation example">
                                        <ul class="pagination justify-content-end">
                                            <li class="page-item <?php if ($currentPage <= 1) echo 'disabled'; ?>">
                                                <a class="page-link" href="?page=1" style="background: <?php echo ($currentPage <= 1 ? '#e6d6ff' : '#d0b3ff'); ?>; border:none; gray;">First</a>
                                            </li>
                                            <li class="page-item <?php if ($currentPage <= 1) echo 'disabled'; ?>">
                                                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" tabindex="-1" style="background: <?php echo ($currentPage <= 1 ? '#e6d6ff' : '#d0b3ff'); ?>; border:none;">Previous</a>
                                            </li>

                                            <?php
                                            // Determine the range of pages to display
                                            $startPage = max(1, $currentPage - 2);
                                            $endPage = min($totalPages, $currentPage + 2);

                                            // Adjust start page if end page is close to the total pages
                                            if ($endPage - $startPage < 4) {
                                                $startPage = max(1, $endPage - 4);
                                            }

                                            // Ensure we don't show more than the total pages
                                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                                <li class="page-item <?php if ($i == $currentPage) echo 'active'; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>" 
                                                    style="background: <?php echo ($i == $currentPage ? '#5f486a' : '#886798'); ?>; border:none; color: white;">
                                                    <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <li class="page-item <?php if ($currentPage >= $totalPages) echo 'disabled'; ?>">
                                                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" 
                                                style="background: #886798; border:none; color:#ddd;">Next</a>
                                            </li>
                                            <li class="page-item <?php if ($currentPage >= $totalPages) echo 'disabled'; ?>">
                                                <a class="page-link" href="?page=<?php echo $totalPages; ?>" 
                                                style="background: #886798; border:none; color:#ddd;">Last</a>
                                            </li>
                                        </ul>
                                    </nav>


                                <?php else: ?>
                                    <p>No history available.</p>
                                <?php endif; ?>
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

<!-- Back to Top Button -->
<!-- <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
</a> -->

   <!-- jQuery CDN - Slim version (=without AJAX) -->
   <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <!-- Popper.JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
    <!-- jQuery Custom Scroller CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>
    <!-- Vendor JS Files -->
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>

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

        // Search functionality
        $('#searchInput').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            $('#historyTable tbody tr').each(function() {
                var text = $(this).text().toLowerCase();
                if (text.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
    document.getElementById('printButton').addEventListener('click', function() {
    // Show the header only for printing
    document.getElementById('printHeader').style.display = 'block';
    
    // Trigger the print dialog
    window.print();
    
    // Delay hiding the header to ensure print dialog is processed
    setTimeout(function() {
        document.getElementById('printHeader').style.display = 'none';
    }, 100); // Adjust delay if needed
});

function confirmLogout() {
    // Show a confirmation dialog
    var confirmation = confirm("Are you sure you want to logout?");
    
    // If the user clicks "OK", return true to proceed with the logout
    // If the user clicks "Cancel", return false to prevent the logout
    return confirmation;
}
// window.addEventListener('scroll', function() {
//     var backToTopButton = document.querySelector('.back-to-top');
//     if (window.scrollY > 300) { // Adjust the scroll position as needed
//         backToTopButton.classList.add('show');
//     } else {
//         backToTopButton.classList.remove('show');
//     }
// });

// document.querySelector('.back-to-top').addEventListener('click', function(e) {
//     e.preventDefault(); // Prevent the default anchor click behavior
//     window.scrollTo({
//         top: 0,
//         behavior: 'smooth' // Smooth scrolling
//     });
// });



</script>
        <footer id="printFooter" style="display: none;">
            <hr>
            <p style="text-align: center;">Prepared by:</p><br>
            <p style="text-align: center;">Prepared to:</p>
            <!-- <p style="text-align: center;">Page <span class="page-number"></span></p> -->
        </footer>
</body>
</html>
