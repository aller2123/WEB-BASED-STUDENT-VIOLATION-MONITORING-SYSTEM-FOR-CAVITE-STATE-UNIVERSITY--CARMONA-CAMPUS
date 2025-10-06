<?php
session_start();
require 'dbconfig.php';

$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

// Fetch active students by program
$activeByProgramStmt = $pdo->query("SELECT program.program_name, COUNT(students.student_id) AS student_count 
                                    FROM students 
                                    JOIN program ON students.program_id = program.program_id 
                                    WHERE students.status = 'Graduate' 
                                    GROUP BY program.program_name");

$activeByProgram = $activeByProgramStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Students by Program</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add custom styles here or link to your stylesheet -->
</head>
<style>
        /* custom.css */
        body {
            background-color: #f8f9fa; /* Light gray background */
        }

        .container {
            background: #ffffff; /* White background for content */
            padding: 20px;
            border-radius: 5px; /* Rounded corners for container */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for container */
        }

        /* Table styles */
        .table thead th {
            background-color: #4c704c; /* Bootstrap primary color for header */
            color: white;
        }

        .table tbody td {
            background-color: #ffffff; /* White background for table body */
        }

        /* Header styles */
        h2 {
            color: #333333; /* Dark text for headings */
            margin-bottom: 10px; /* Adjusted margin bottom */
            margin-top: 0; /* Remove default margin-top */
        }
        /* PRINT CSS */
        @media print {
            /* Hide the menu text "Students" */
            .menu-text,
            .input-group,
            .mb-3,
            .col-md-4,
            .btn {
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
            font-size: 14px;
        }
    }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-3"> Graduate Students by Program</h2>
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by Program or Student Number">
                        <div class="input-group-append">
                        <button id="searchButton" class="btn btn-outline-primary" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search mr-2" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>    
                        Search</button>
                        </div>
                </div>
            </div>
            <div class="col-md-4 btn-back">
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <span class="d-flex align-items-center justify-content-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left mr-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Back</a>
            </div>
            <div class="col-md-4 text-right">
                <button id="printButton" class="btn btn-primary" style="background: #e48189; border:none;">Print
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/>
                    <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                </svg>
                </button>
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
            <h1 id="printHeader" class="d-none d-print-block text-center mb-4">Bachelor Graduates Records</h1>
        </header>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead">
                    <tr>
                        <th>Program</th>
                        <th> Graduates Students</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeByProgram as $program): ?>
                        <tr>
                            <td><?= htmlspecialchars($program['program_name']); ?></td>
                            <td><?= $program['student_count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Optional: include Bootstrap JS with Popper.js for tooltips -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchButton').addEventListener('click', function() {
            var searchTerm = document.getElementById('searchInput').value.toLowerCase();
            var rows = document.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                var programName = row.cells[0].textContent.toLowerCase();
                if (programName.includes(searchTerm)) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        document.getElementById('printButton').addEventListener('click', function() {
            // Show the header only for printing
            document.getElementById('printHeader').style.display = 'block';
            
            // Trigger the print dialog
            window.print();
            
            // Hide the header after printing
            document.getElementById('printHeader').style.display = 'none';
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