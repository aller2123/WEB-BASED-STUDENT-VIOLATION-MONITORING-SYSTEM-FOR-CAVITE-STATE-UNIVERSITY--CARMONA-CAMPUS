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

// Redirect only non-admin and non-staff users (if there are other roles)
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff') {
    header('Location: main.php'); // Redirect them to the main dashboard or a general access denied page
    exit();
}

// Database connection settings
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$dsn = "mysql:host=$host;dbname=$database";
// Create a new PDO instance
try {
    // Here $user was replaced by $username, which is the correct variable name
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // The error message should also reference the correct database variable
    die("Could not connect to the database $database :" . $e->getMessage());
}

// Fetch counseling sessions
$sql = "SELECT cs.*, c.counselors_name
FROM counseling_sessions AS cs
LEFT JOIN counselors AS c ON cs.counselors_id = c.counselors_id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$counselingSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch violation details for students with violations
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



if (isset($_SESSION['success_message'])) {
    echo '<p class="success">' . $_SESSION['success_message'] . '</p>';
    unset($_SESSION['success_message']);
}

// Fetch counselor data
$counselorsStmt = $pdo->query("SELECT counselors_id, counselors_name FROM counselors");
$counselors = $counselorsStmt->fetchAll(PDO::FETCH_ASSOC)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Counseling</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
    <!-- Our Custom CSS -->
    <link rel="stylesheet" href="css/navigation.css">
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .btn-success{
            float:right;
            background-color: #4f8f1e;
            color: #d8f0c6;
            border: none;
            border-radius: 20px;
            margin-right: 1px; /* Set margin between submit button and cancel button to 5px */
        }
        .btn-success:hover{
            background-color: #43771c;
            color: #d8f0c6;
        }
        .btn-success:active {
            background-color: #43771c !important; /* Change the background color to red when the button is active */
        }
        /* .btn-outline-danger{
            float: right;
        } */
        .btn-danger{
            float:right;
            color: #d8f0c6;
            border: none;
            border-radius: 20px;
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
            background-color: #452235;
            color: white; /* Change text color to white for better visibility */
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
                        <a href="main.php" class="nav-link">Dashboard</a>
                    </li>
                    <li>
                        <a href="student.php" class="nav-link">Students</a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li>
                            <a href="attendance.php" class="nav-link ">Attendance</a>
                        </li>
                        <li>
                            <a href="counseling.php" class="nav-link active">Counseling</a>
                        </li>
                        <li>
                            <a href="violation.php" class="nav-link">Violation</a>
                        </li>
                        <li>
                            <a href="requirements.php" class="nav-link">Requirements</a>
                        </li>
                        <li>
                            <a href="users.php" class="nav-link">Users</a>
                        </li>
                        <li>
                            <a href="reports.php" class="nav-link">Reports</a>
                        </li>
                        <li>
                            <a href="history.php" class="nav-link ">History</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="attendance.php" class="nav-link">Attendance</a>
                        </li>
                        <li>
                            <a href="counseling.php" class="nav-link">Counseling</a>
                        </li>
                        <li>
                            <a href="violation.php" class="nav-link">Violation</a>
                        </li>
                        <li>
                            <a href="requirements.php" class="nav-link">Requirements</a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="logout.php" class="nav-link">Logout</a>
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
                <span class="menu-text">Counseling</span>
                <div class="col py-3">
                    <div id="students-section" style="padding-top:15px; padding-left:10px">
                        <a href="add_counseling.php" class="btn btn-outline-success mb-3">
                            <span class="d-flex align-items-center justify-content-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                                </svg> Add Schedule
                            </span>
                        </a>
                        <!-- Add Counselor Button and Modal -->
                        <button type="button" class="btn btn-outline-primary mb-3" data-toggle="modal" data-target="#addCounselorModal">
                            <span class="d-flex align-items-center justify-content-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                                </svg>
                                Add Counselor
                            </span>
                        </button>

                        <!-- Add Counselor Modal -->
                        <div class="modal fade" id="addCounselorModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel">Add Counselor</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="addCounselorForm">
                                            <div class="form-group">
                                                <label for="counselorName">Counselor Name</label>
                                                <input type="text" class="form-control" id="counselorName" name="counselor_name" required>
                                            </div>
                                            <button type="submit" class="btn btn-success">Add Counselor</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Counselor Button triggers Modal -->
                        <button type="button" class="btn btn-outline-danger mb-3" data-toggle="modal" data-target="#deleteCounselorModal">
                            Remove Counselor
                        </button>

                        <!-- Delete Counselor Modal -->
                        <div class="modal fade" id="deleteCounselorModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel">Delete Counselor</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="deleteCounselorForm">
                                            <div class="form-group">
                                                <label for="deleteCounselorSelect">Select Counselor to Delete</label>
                                                <select class="form-control" id="deleteCounselorSelect" name="counselor_id" required>
                                                    <option value="">-- Select Counselor --</option>
                                                    <?php foreach ($counselors as $counselor): ?>
                                                        <option value="<?= htmlspecialchars($counselor['counselors_id']); ?>">
                                                            <?= htmlspecialchars($counselor['counselors_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-danger">Delete Counselor</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Year and Section</th>
                                    <th>With Violation</th>
                                    <th>Counselor</th>
                                    <th>what kind of violation.</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
    <?php $rowColor = true; ?>
    <?php foreach ($counselingSessions as $session): ?>
        <tr>
            <?php $rowColor = !$rowColor; ?>
            <td><?= htmlspecialchars($session['student_full_name']); ?></td>
            <td><?= htmlspecialchars($session['year_and_section']); ?></td>
            <td>
    <?php if ($session['with_violation']): ?>
        <span class="text-success">
            <i class="fas fa-check-circle"></i>
        </span>
    <?php else: ?>
        No
    <?php endif; ?>
</td>

            <td><?= htmlspecialchars($session['counselors_name']); ?></td>
            <td>
            <?php
if ($session['with_violation']) {
    $studentViolationDetails = array_filter($violationDetails, function ($detail) use ($session) {
        return $detail['full_name'] === $session['student_full_name'] && $detail['year_and_section'] === $session['year_and_section'];
    });

    if (!empty($studentViolationDetails)) {
        echo '<strong>Violation Types:</strong><br>';
        foreach ($studentViolationDetails as $detail) {
            echo '- ' . htmlspecialchars($detail['violation_types']) . '<br>';
        }

        echo '<strong>Violation Details:</strong><br>';
        foreach ($studentViolationDetails as $detail) {
            echo '- ' . htmlspecialchars($detail['violation_details']) . '<br>';
        }
    } else {
        echo 'No violation details found.';
    }
}
?>

</td>


            <td class="status-<?php echo strtolower(htmlspecialchars($session['status'])); ?>">
                <?php echo htmlspecialchars($session['status']); ?>
            </td>
            <td class="action-buttons" style="margin-top: 10px; display: flex; justify-content: center; border:none;">
                <a href="edit_counseling.php?id=<?= $session['counseling_id']; ?>" class="edit-btn" style="margin-right: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="green" class="bi bi-pencil-square" viewBox="0 0 18 18">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                    </svg>
                </a>
                <a href="delete_counseling.php?id=<?= $session['counseling_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this session?');" style="margin-right: 5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="red" class="bi bi-trash" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                    </svg>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
</table>


            </section>
        </div>
        </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

        <script>
            // jQuery for handling the form submission
            $(document).ready(function() {
                $('#addCounselorForm').on('submit', function(e) {
                    e.preventDefault();
                    var counselorName = $('#counselorName').val();
                    // Perform the AJAX request to add the counselor
                    $.ajax({
                        type: "POST",
                        url: "add_counselor_handler.php", // Update this to your PHP script location
                        data: { counselor_name: counselorName },
                        success: function(response) {
                            // Handle success: you can close the modal and refresh the page or update the UI accordingly
                            $('#addCounselorModal').modal('hide');
                            // Optionally refresh the page to show the new counselor
                            location.reload();
                        },
                        error: function() {
                            // Handle error
                            alert("Error adding counselor.");
                        }
                    });
                });
            });

            $(document).ready(function() {
        $('#deleteCounselorForm').submit(function(event) {
            event.preventDefault();
            var counselorId = $('#deleteCounselorSelect').val();

            if (counselorId) {
                // Confirm before deleting
                if (confirm('Are you sure you want to delete this counselor?')) {
                    $.ajax({
                        type: 'POST',
                        url: 'delete_counselor.php', // Your server-side script
                        data: { 'counselor_id': counselorId },
                        success: function(response) {
                            // Assuming your response is a JSON object that has a boolean `success` property.
                            if (response.success) {
                                alert('Counselor deleted successfully.');
                                // Refresh the page or remove the deleted counselor from the dropdown.
                                location.reload();
                            } else {
                                alert('Failed to delete counselor.');
                            }
                        },
                        error: function() {
                            alert('An error occurred. Please try again later.');
                        }
                    });
                }
            } else {
                alert('Please select a counselor to delete.');
            }
        });
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

        </script>
            
          
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
    <!-- jQuery Custom Scroller CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>
    <!-- Our Custom JS -->
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
        </script>
    <!-- Code injected by live-server -->
<script>
	// <![CDATA[  <-- For SVG support
	if ('WebSocket' in window) {
		(function () {
			function refreshCSS() {
				var sheets = [].slice.call(document.getElementsByTagName("link"));
				var head = document.getElementsByTagName("head")[0];
				for (var i = 0; i < sheets.length; ++i) {
					var elem = sheets[i];
					var parent = elem.parentElement || head;
					parent.removeChild(elem);
					var rel = elem.rel;
					if (elem.href && typeof rel != "string" || rel.length == 0 || rel.toLowerCase() == "stylesheet") {
						var url = elem.href.replace(/(&|\?)_cacheOverride=\d+/, '');
						elem.href = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_cacheOverride=' + (new Date().valueOf());
					}
					parent.appendChild(elem);
				}
			}
			var protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://';
			var address = protocol + window.location.host + window.location.pathname + '/ws';
			var socket = new WebSocket(address);
			socket.onmessage = function (msg) {
				if (msg.data == 'reload') window.location.reload();
				else if (msg.data == 'refreshcss') refreshCSS();
			};
			if (sessionStorage && !sessionStorage.getItem('IsThisFirstTime_Log_From_LiveServer')) {
				console.log('Live reload enabled.');
				sessionStorage.setItem('IsThisFirstTime_Log_From_LiveServer', true);
			}
		})();
	}
	else {
		console.error('Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading.');
	}
	// ]]>
</script>
</body>
    </html>