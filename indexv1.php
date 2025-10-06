<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap">
</head>
<style>
    .navbar {
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000; /* Ensure the navbar stays on top of other elements */
    }
    .navbar-brand {
        display: flex;
        align-items: center;
    }
    .navbar-brand img {
        margin-right: 10px;
    }
    .small-text {
        font-size:small;
        font-family:Verdana, Geneva, Tahoma, sans-serif;
    }
</style>
<body>
    <!-- NAVBAR -->
    <header>
        <div class="navcon">
            <nav class="navbar navbar-expand-lg navbar-light bg-light" style="padding-left:90px; padding-right:90px;">
                <a class="navbar-brand" href="https://cvsu.edu.ph/carmona/">
                    <img src="./img/cvsulogo.png" width="40" height="40" class="d-inline-block align-top" alt="Cavite State University Logo">
                    <span style="font-size: smaller;">
                        Cavite State University - <br>Carmona
                    </span>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav ml-auto">
                        <a class="nav-item nav-link active" href="#">Login <span class="sr-only">(current)</span></a>
                        <a class="nav-item nav-link" href="#">Services</a>
                        <a class="nav-item nav-link" href="#">Research</a>
                        <a class="nav-item nav-link" href="#">Faculties</a>
                        <a class="nav-item nav-link" href="#">Academics</a>
                        <a class="nav-item nav-link" href="#">Contact Us</a>
                        <a class="nav-item nav-link" href="#">About Us</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <!-- Login form -->
            <div class="col-md-6">
                <div class="login-card mx-auto">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Login</h3>
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= urldecode($_GET['error']) ?></div>
                        <?php endif; ?>
                        <form action="login.php" method="post">
                            <div class="form-group">
                                <label for="username" class="username-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password" class="password-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group text-right">
                                <a href="#" class="forgot-password-link">Forgot Password?</a>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">LOGIN</button>
                            <div class="text-center mt-3">
                                <a href="attendance_form.php" class="btn btn-success btn-block">ATTENDANCE</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- See Your Information form -->
            <div class="col-md-6">
                <div class="login-card mx-auto">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">See Your Information</h3>
                        <form id="student-info-form">
                            <div class="form-group">
                                <label for="student_no" class="student-no-label">Student No.</label>
                                <input type="text" class="form-control" id="student_no" name="student_no" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">VIEW</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Information Modal -->
    <div class="modal fade" id="studentInfoModal" tabindex="-1" role="dialog" aria-labelledby="studentInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentInfoModalLabel">Student Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="student-info"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
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
        var studentInfo = '<p><strong>Student No:</strong> ' + response.student_no + '</p>';
        studentInfo += '<p><strong>First Name:</strong> ' + response.first_name + '</p>';
        studentInfo += '<p><strong>Surname:</strong> ' + response.surname + '</p>';
        studentInfo += '<p><strong>Middle Name:</strong> ' + response.middle_name + '</p>';
        studentInfo += '<p><strong>Gender:</strong> ' + response.gender + '</p>';
        studentInfo += '<p><strong>Address:</strong> ' + response.address + '</p>';
        studentInfo += '<p><strong>Age:</strong> ' + response.age + '</p>';
        studentInfo += '<p><strong>Status:</strong> ' + response.status + '</p>';
        studentInfo += '<p><strong>Program:</strong> ' + (response.program_name ? response.program_name : 'N/A') + '</p>';
        studentInfo += '<p><strong>Total Attendance:</strong> ' + (response.total_attendance ? response.total_attendance : 'N/A') + '</p>';
        studentInfo += '<p><strong>Event Titles:</strong> ' + (response.event_titles ? response.event_titles : 'N/A') + '</p>';

        // Display violation details
        if (response.violations.length > 0) {
            studentInfo += '<p><strong>Violations:</strong></p>';
            studentInfo += '<ul>';
            response.violations.forEach(function(violation) {
                studentInfo += '<li>';
                studentInfo += '<p><strong>Violation Type:</strong> ' + violation.type_of_violation + '</p>';
                studentInfo += '<p><strong>Violation Details:</strong> ' + violation.full_info + '</p>';
                studentInfo += '</li>';
            });
            studentInfo += '</ul>';
        } else {
            studentInfo += '<p><strong>Violations:</strong> N/A</p>';
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
    </script>
</body>
</html>