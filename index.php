
<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'SIMS';
$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'vision_statement'");
$stmt->execute();
$visionStatement = $stmt->fetchColumn(); 


$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mission_statement'");
$stmt->execute();
$missionStatement = $stmt->fetchColumn(); 

// Fetch the quality policy
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'quality_policy'");
$stmt->execute();
$qualityPolicy = $stmt->fetchColumn(); 


$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'services_paragraph'");
$stmt->execute();
$servicesParagraph = $stmt->fetchColumn();


$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'career_services_paragraph'");
$stmt->execute();
$careerServicesParagraph = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'job_fair_paragraph'");
$stmt->execute();
$jobFairParagraph = $stmt->fetchColumn();

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
$healthSeminarsParagraph = $stmt->fetchColumn();




?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>oserve</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Oservefavicon -->
  <link href="assets/img/oserve-favicon.png" rel="icon">
  <!-- <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon"> -->

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Lato:300,300i,400,400i,700,700i" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap">
  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Butterfly
  * Template URL: https://bootstrapmade.com/butterfly-free-bootstrap-theme/
  * Updated: Mar 17 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
  <style>
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: transparent;
        }

        .carousel-control-prev-icon::before,
        .carousel-control-next-icon::before {
            content: '';
            display: inline-block;
            width: 100%;
            height: 100%;
            background-size: 100% 100%;
        }

        .carousel-control-prev-icon::before {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='red' viewBox='0 0 8 8'%3E%3Cpath d='M3.5 0L4.5 1 1.5 4 4.5 7 3.5 8 0 4 3.5 0z'/%3E%3C/svg%3E");
        }

        .carousel-control-next-icon::before {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='red' viewBox='0 0 8 8'%3E%3Cpath d='M4.5 0L3.5 1 6.5 4 3.5 7 4.5 8 8 4 4.5 0z'/%3E%3C/svg%3E");
        }

        #hover-button {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease-in-out;
    }

        /* Custom button styles */
        .btn-success {
        background-color: #4f8f1e;
        color: #d8f0c6;
        border: none;
        border-radius: 20px;
      }
      .btn-success:hover {
          background-color: #43771c;
          color: #d8f0c6;
      }

      .btn-success:active {
          background-color: #43771c !important;
      }
      .btn-success {
          font-size: 14px;
          padding: 0.375rem 0.75rem;
          line-height: 1.5;
      }

    
    </style>
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top">
    <div class="container d-flex align-items-center justify-content-between">
      <a class="navbar-brand" href="https://cvsu.edu.ph/carmona/" style="display: flex; align-items: center;">
        <img src="assets/img/cvsulogo.png" width="40" height="40" class="d-inline-block align-top" alt="Cavite State University Logo">
        <div style="display: flex; flex-direction: column; line-height: 1;">
            <span style="font-size: smaller; color: white; font-weight: 350;">Cavite State University -</span>
            <span style="font-size: smaller; color: white; font-weight: 350;">Carmona</span>
        </div>
    </a>
      <nav id="navbar" class="navbar">
        <ul>
          <li><a class="nav-link scrollto active" href="#hero">Home</a></li>
          <li><a class="nav-link scrollto " href="#portfolio">Login</a></li>
          <li><a class="nav-link scrollto" href="#services">Services</a></li>
          
          <li><a class="nav-link scrollto" href="#contact">Contact</a></li>
          <!-- <li><a class="nav-link scrollto" href="#team">About Us</a></li> -->
          <!-- <li><a class="nav-link scrollto" href="#about">About</a></li> -->
        </ul>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->
    </div>
  </header><!-- End Header -->

        <!-- JavaScript to handle the scroll event -->
        <script>
            window.addEventListener('scroll', function() {
                var header = document.getElementById('header');
                if (window.scrollY > 50) { // Change 50 to the number of pixels you want before the background changes
                    header.classList.add('header-scrolled');
                } else {
                    header.classList.remove('header-scrolled');
                }
            });
        </script>


  <!-- ======= Hero Section ======= -->
  <section id="hero" class="d-flex align-items-center">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 pt-4 pt-lg-0 order-2 order-lg-1 d-flex flex-column justify-content-center">
                <!-- <a href="#portfolio"> <img src="assets/img/oserve-hero.png" alt="Oserve logo" width="260" height="100" style="background: rgba(255, 255, 255, 0.8); border-radius: 5px; margin-top:10px;"> </a> -->
                    <?php
                        // Include the database connection (assuming it's in setting.php)
                      
                        // Fetch the system description from the database
                        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'system_description'");
                        $stmt->execute();
                        $systemDescription = $stmt->fetchColumn(); 

                        // Display the system description
                        if ($systemDescription) {
                            echo "<p class='wew' style='font-weight:800; font-size:80px; margin-top: -80px; line-height:150px; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);'>" . htmlspecialchars($systemDescription) . "</p>";
                        } else {
                            echo "<p class='wew' style='font-weight:800; font-size:80px; margin-top: -80px; line-height:150px; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);'>Default System Description</p>";
                        }
                    ?>
                    <!-- <div><a href="#portfolio" class="btn-get-started scrollto">Login</a></div> -->
                </div>
                
            </div>
        </div>
    </section>
<!-- Include Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- End Hero -->

  <main id="main">

    <!-- ======= Login Section ======= -->
    <section id="portfolio" class="portfolio">
      <div class="row justify-content-center align-items-center">
          <section id="hero1" class="d-flex align-items-center">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 pt-4 pt-lg-0 order-2 order-lg-1 d-flex flex-column justify-content-center">
                        <div class="login-card mx-auto">
                            <div class="card-body">
                                <div class="logo text-center mb-4">
                                  <h1>Login</h1>
                                    <!-- <img src="assets/img/oserve1.png" alt="oservelogo" class="img-fluid" style="max-width: 150px;"> -->
                                </div>
                                <?php if (isset($_GET['error'])): ?>
                                    <div class="alert alert-danger"><?= urldecode($_GET['error']) ?></div>
                                <?php endif; ?>
                                <form action="login.php" method="post">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                    </div>
                                    <div class="form-group text-right">
                                    <a href="#" class="forgot-password-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" style="font-size:14px;">Forgot Password?</a>
                                    </div>
                                    <button type="submit" class="btn btn-login btn-block">LOGIN</button>
                                    <!-- <div class="text-center mt-3">
                                      <a id="hover-button" href="attendance_form.php" class="btn btn-outline-success btn-block">
                                          <span class="button-content">ATTENDANCE</span>
                                      </a>
                                  </div> -->
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Forgot Password Modal -->
                    <!-- <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="forgotPasswordForm" action="forgot_password.php" method="post">
                                        <div class="form-group">
                                            <label for="email">Enter your email address:</label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                        </div>
                                        <div class="alert alert-success mt-3 d-none" id="successAlert" role="alert">
                                            A reset link has been sent to your email address.
                                        </div>
                                        <div class="alert alert-danger mt-3 d-none" id="errorAlert" role="alert">
                                            An error occurred. Please try again.
                                        </div>
                                        <div class="text-end mt-3">
                                            <button type="submit" class="btn btn-success">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <!-- Modal -->
  <div class="modal fade" id="forgotPasswordModal" tabindex="-1" data-bs-keyboard="false" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content" >
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Enter Registered Email</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="authentication.php" method="POST">
        <div class="modal-body" style="color: white;">
          <div class="form-floating mb-3">
              <input type="email" class="form-control" id="email" name="email" placeholder="email" required autofocus>
              <label for="email" style="color: black;">Email Address: (Ex. Juan@gmail.com)</label>
            </div>
          </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" name="authsubmit">Submit</button>
        </div>
        </form>
      </div>
    </div>
  </div>


	<!-- validate authentication code pass modal -->
	<div class="modal fade" id="authenCodeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="">Authentication Code</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="" method="post" action="authentication.php">
          <div class="col mb-3">
          <label for="authenCode" class="col-md col-form-label">Enter recieved Code from your Email:</label>
          <div class="col-md">
              <input type="text" class="form-control" id="authenCode" name="authenCode" placeholder="Enter Code..." required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <input type="submit" class="btn btn-primary" id="submitAuthenCode" name="submitAuthenCode" value="Submit">
      </div>
        </form>
      </div>
    </div>
</div>



<!-- enter new pass modal -->
<div class="modal fade" id="enterNewPassModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="">Update Password</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="" method="post" action="authentication.php">
        <div class="col mb-3">
        <label for="newPassword" class="col-md col-form-label">Enter New Password:</label>
        <div class="col-md input-group">
            <input type='password' class="form-control" id='newPassword' name='newPassword' placeholder="Enter Password..." aria-describedby="passwordReminders" required>
        </div>
            <span id="passwordReminders" class="form-text" style="color: var(--light-secondary-color);">
          Do not forget your Password, you will use it to Log in later!
        </span>
      </div>
      <div class="col mb-3">
        <label for="confirmNewPassword" class="col-md col-form-label">Confirm New Password:</label>
        <div id="confirmNewPasswordAlertPlaceholder" class="col-md col-form-label"></div>
        <div class="col-md input-group">
            <input type='password' class="form-control" id='confirmNewPassword' name='confirmNewPassword' placeholder="Confirm New Password..." required>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      <input type="submit" class="btn btn-primary" id="submitNewPass" name="submitNewPass" value="Submit" onclick="return validatePassword('confirmNewPasswordAlertPlaceholder','newPassword', 'confirmNewPassword')">
    </div>
      </form>
    </div>
  </div>
</div>


                      <script>
                          document.addEventListener('DOMContentLoaded', function () {
                              var button = document.getElementById('hover-button');

                              button.addEventListener('mouseover', function () {
                                button.innerHTML = `
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16">
                                        <path d="M2 2h2v2H2z"/>
                                        <path d="M6 0v6H0V0zM5 1H1v4h4zM4 12H2v2h2z"/>
                                        <path d="M6 10v6H0v-6zm-5 1v4h4v-4zm11-9h2v2h-2z"/>
                                        <path d="M10 0v6h6V0zm5 1v4h-4V1zM8 1V0h1v2H8v2H7V1zm0 5V4h1v2zM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8zm0 0v1H2V8H1v1H0V7h3v1zm10 1h-1V7h1zm-1 0h-1v2h2v-1h-1zm-4 0h2v1h-1v1h-1zm2 3v-1h-1v1h-1v1H9v1h3v-2zm0 0h3v1h-2v1h-1zm-4-1v1h1v-2H7v1z"/>
                                        <path d="M7 12h1v3h4v1H7zm9 2v2h-3v-1h2v-1z"/>
                                    </svg>
                                `;
                            });


                              button.addEventListener('mouseout', function () {
                                  button.innerHTML = '<span>ATTENDANCE</span>';
                              });
                          });
                      </script>

                   
                    <div class="col-lg-6 order-1 order-lg-2 d-flex align-items-center">
                    <div id="textCarousel" class="carousel slide w-100" data-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <div class="text-center">
                                        <h3>VISION</h3>
                                    <p id="visionText"><?php echo htmlspecialchars($visionStatement); ?></p>
                                </div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <div class="text-center">
                                    <h3>MISSION</h3>
                                    <p id="missionText"><?php echo htmlspecialchars($missionStatement); ?></p>
                                </div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <div class="text-center">
                                    <h3>QUALITY POLICY</h3>
                                    <p id="qualityPolicyText"><?php echo htmlspecialchars($qualityPolicy); ?></p>
                                </div>
                                </div>
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#textCarousel" role="button" data-slide="prev" style="left: -40px;">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#textCarousel" role="button" data-slide="next" style="right: -40px;">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                </div>
                </div>
            </div>
        </section>
        
        
        
    </section> 
    <section id="services" class="services section-bg">
      <div class="container">

      <div class="section-title">
    <h2>Services</h2> <p><?php echo htmlspecialchars($servicesParagraph); ?></p>
</div>


        <div class="row justify-content-center">
          <div class="col-lg-4 col-md-6">
            <div class="icon-box">
            <div class="icon"><i class="bi bi-mortarboard" style="color: #ff689b;"></i></div>
            <h4 class="title">Career Services</h4>
            <p class="description"><?php echo htmlspecialchars($careerServicesParagraph); ?></p> 
        </div>
          </div>
          <!-- <div class="col-lg-4 col-md-6">
            <div class="icon-box">
              <div class="icon"><i class="bi bi-briefcase" style="color: #e9bf06;"></i></div>
              <div class="icon"><i class="bi bi-briefcase" style="color: #e9bf06;"></i></div>
            <h4 class="title">Job Fair</h4>
            <p class="description"><?php echo htmlspecialchars($jobFairParagraph); ?></p> 
        </div>
          </div> -->

          <div class="col-lg-4 col-md-6" data-wow-delay="0.1s">
            <div class="icon-box">
              <div class="icon"><i class="bi bi-chat-dots" style="color: #3fcdc7; display: inline-block; transform: scaleX(-1);"></i></div>
              <h4 class="title">Counseling</h4>
        <p class="description"><?php echo htmlspecialchars($counselingParagraph); ?></p> 
    </div>
          </div>
          <div class="col-lg-4 col-md-6" data-wow-delay="0.1s">
            <div class="icon-box">
            <div class="icon"><i class="bi bi-person-raised-hand" style="color:#41cf2e; display: inline-block; transform: scaleX(-1);"></i></div>
        <h4 class="title">Student's Active Participation</h4>
        <p class="description"><?php echo htmlspecialchars($studentParticipationParagraph); ?></p> 
    </div>
          </div>

          <div class="col-lg-4 col-md-6" data-wow-delay="0.2s">
            <div class="icon-box">
              <div class="icon"><i class="bi bi-graph-up-arrow" style="color: #d6ff22;"></i></div>
              <h4 class="title">High Passing Rate</h4>
        <p class="description"><?php echo htmlspecialchars($highPassingRateParagraph); ?></p> 
    </div>
          </div>
          <div class="col-lg-4 col-md-6" data-wow-delay="0.2s">
            <div class="icon-box">
              <div class="icon"><i class="bi bi-gender-trans" style="color: #4680ff;"></i></div>
              <h4 class="title">Gender and Development Services</h4>
        <p class="description"><?php echo htmlspecialchars($genderDevelopmentParagraph); ?></p> 
    </div>
          </div>
          <div class="col-lg-4 col-md-6" data-wow-delay="0.2s">
            <div class="icon-box">
              <div class="icon"><i class="bi bi-lungs" style="color: pink;"></i></div>
              <h4 class="title">Student's Health Seminars</h4>
        <p class="description"><?php echo htmlspecialchars($healthSeminarsParagraph); ?></p> 
    </div>
          </div>
        </div>

      </div>
    </section>
    <section id="contact" class="contact">
  <div class="container">

    <div class="section-title">
      <h2>Contact</h2>
      <p>Get in touch with us for inquiries, support, or more information. Our Contact section provides various ways to reach out and ensures you receive prompt assistance for any questions or concerns you may have.</p>
    </div>

    <div>
      <iframe style="border:0; width: 100%; height: 270px;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3865.9041689076876!2d121.06314321357002!3d14.316998875209096!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d771a8ee1419%3A0x8ba33fccb95a7b40!2sCavite%20State%20University%20-%20Carmona!5e0!3m2!1sen!2sph!4v1720241241984!5m2!1sen!2sph" frameborder="0" allowfullscreen></iframe>
    </div>

    <div class="row mt-5">
      <div class="col-lg-4 text-center">
        <div class="info">
          <i class="bi bi-geo-alt"></i>
          <h4>Location:</h4>
          <p>Market Road, Carmona, Philippines, 4116</p>
        </div>
      </div>
      <div class="col-lg-4 text-center">
        <div class="info">
          <i class="bi bi-envelope"></i>
          <h4>Email:</h4>
          <p>cvsucarmona@cvsu.edu.ph</p>
        </div>
      </div>
      <div class="col-lg-4 text-center">
        <div class="info">
          <i class="bi bi-phone"></i>
          <h4>Call:</h4>
          <p>(046) 487 6328</p>
        </div>
      </div>
    </div>

  </div>
</section>

    <section id="team" class="team section-bg">
      <div class="container">

        <div class="section-title">
          <h2>Developer Team</h2>
          <p>
          We’re a group of four BSIT students from Cavite State University - Carmona Campus working together on this capstone project. This system is something we built as a team to help make things easier and more organized. It reflects what we’ve learned and how we’ve grown throughout our journey in college.
          </p>
        </div>

        <div class="row">

          <div class="col-lg-3 col-md-6 d-flex align-items-stretch">
            <div class="member">
              <div class="member-img">
                <img src="assets/img/team/team-1.jpg" class="img-fluid" alt="">
                <!-- <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href=""><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div> -->
              </div>
              <div class="member-info">
                <h4>Mark Lester Taas</h4>
                <span>Developer</span>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 d-flex align-items-stretch">
            <div class="member">
              <div class="member-img">
                <img src="assets/img/team/team-2.jpg" class="img-fluid" alt="">
                <!-- <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href=""><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div> -->
              </div>
              <div class="member-info">
                <h4>Alyssa Torres</h4>
                <span>Designer</span>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 d-flex align-items-stretch">
            <div class="member">
              <div class="member-img">
                <img src="assets/img/team/team-3.jpg" class="img-fluid" alt="">
                <!-- <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href=""><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div> -->
              </div>
              <div class="member-info">
                <h4>Fairy Ross Narito</h4>
                <span>Designer</span>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 d-flex align-items-stretch">
            <div class="member">
              <div class="member-img">
                <img src="assets/img/team/team-4.jpg" class="img-fluid" alt="">
                <!-- <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href=""><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div> -->
              </div>
              <div class="member-info">
                <h4>Chrizel Solomon</h4>
                <span>Developer</span>
              </div>
            </div>
          </div>

        </div>

      </div>
    </section><!-- End Team Section -->
  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer">
    <div class="footer-top">
      <div class="container">
        <div class="row">

        <div class="col-lg-3 col-md-6 footer-contact">
          <a href="https://www.gov.ph/" target="_blank">
            <img src="../Oserve/assets/img/govphlogo.png" alt="gov ph logo" style="width: 70%; height: auto;">
          </a>
        </div>

          <div class="col-lg-3 col-md-6 footer-links">
            <h4>Other Links</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="https://ched.gov.ph/" target="_blank" class="-link">CHED</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://www.gov.ph/" target="_blank" class="-link">GovPH</a></li>
              <!-- <li><i class="bx bx-chevron-right"></i> <a href="http://tuaf.edu.vn/" target="_blank" class="-link">THAI NGUYEN UNIVERSITY</a></li> -->
              <li><i class="bx bx-chevron-right"></i> <a href="https://www.tesda.gov.ph/" target="_blank" class="-link">TESDA</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://www.dost.gov.ph/" target="_blank" class="-link">DOST</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 footer-links">
            <h4>Quick Links</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="https://cvsu.edu.ph/citizens-charter-page/" target="_blank" class="-link">Citizen's Charter</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://myportal.cvsu.edu.ph/" target="_blank" class="-link">Student Portal</a></li>
              <!-- <li><i class="bx bx-chevron-right"></i> <a href="#">List of Enrolled Students</a></li> -->
              <!-- <li><i class="bx bx-chevron-right"></i> <a href="#">Library</a></li> -->
              <li><i class="bx bx-chevron-right"></i> <a href="https://elearning.cvsu.edu.ph/" target="_blank" class="-link">E-Learning</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://cvsu.edu.ph/invitation-to-bid/" target="_blank" class="-link">Invitation to Bid</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://cvsu.edu.ph/request-for-quotation/" target="_blank" class="-link">Request for Quotation</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://cvsu.edu.ph/jobs/" target="_blank" class="-link">Job Portal</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 footer-links">
          
            <div class="social-links mt-3">
              <a href="https://cvsu.edu.ph/transparency-seal/" target="_blank">
                <img src="../Oserve/assets/img/transparency-seal.png" alt="gov ph logo" style="width: 40%; height: auto; margin-bottom: 20px;">
              </a>
              <a href="https://cvsu.edu.ph/citizens-charter-page/" target="_blank">
                <img src="../Oserve/assets/img/Citizen-Charter.png" alt="gov ph logo" style="width: 40%; height: auto; margin-bottom: 20px; margin-left: 20px;">
              </a>
              <a href="https://www.foi.gov.ph/agencies/cvsu/" target="_blank">
                <img src="../Oserve/assets/img/foi_logo.png" alt="gov ph logo" style="width: 40%; height: auto; margin-right: 20px;">
              </a>
              <!-- <a href=""> -->
                <img src="../Oserve/assets/img/Bagong-Pilipinas.png" alt="gov ph logo" style="width: 40%; height: auto;">
                
              <!-- </a> -->
            </div>
            <img src="../Oserve/assets/img/dpo.png" alt="gov ph logo" style="width: 90%; height: auto; margin-left:15px;">
          </div>
          

        </div>
      </div>
    </div>

    <div class="container py-4 text-center mx-auto">
  <div class="copyright">
    &copy; 2024 Cavite State University - Carmona Campus | All Rights Reserved
  </div>
</div><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  
  <!-- FORGOT PASSWORD -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
      $(document).ready(function() {
          $('#forgotPasswordForm').on('submit', function(e) {
              e.preventDefault(); // Prevent default form submission
              var email = $('#email').val();

              $.ajax({
                  type: 'POST',
                  url: 'forgot_password.php',
                  data: { email: email },
                  success: function(response) {
                      // Display success message and hide the form
                      $('#successAlert').removeClass('d-none');
                      $('#forgotPasswordForm').find('input, button').prop('disabled', true);
                  },
                  error: function() {
                      // Display error message
                      $('#errorAlert').removeClass('d-none');
                  }
              });
          });
      });
  </script>

  <script>
    
// confirm password validation
function validatePassword(id, first_pass, sec_pass) {
      const alertPlaceholder = document.getElementById(id)
      const appendAlert = (message, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
          `<div class="alert alert-${type} alert-dismissible" role="alert">`,
          `   <div>${message}</div>`,
          '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
          '</div>'
        ].join('')

        alertPlaceholder.append(wrapper)
      }

        var password = document.getElementById(first_pass).value;
        var confirmPassword = document.getElementById(sec_pass).value;

        if (password !== confirmPassword) {
            appendAlert('Password did not match!', 'danger')
            return false;
        }
        return true;
    }


document.addEventListener('DOMContentLoaded', function() {
    <?php if(isset($_SESSION['login_error']) || isset($_GET['scroll_to_login'])): ?>
    var loginSection = document.getElementById('portfolio');
    if(loginSection) {
        loginSection.scrollIntoView({behavior: 'smooth'});
    }
    <?php endif; ?>

    <?php if(isset($_SESSION['login_error'])): ?>
    var errorAlert = document.createElement('div');
    errorAlert.className = 'alert alert-danger';
    errorAlert.textContent = '<?php echo $_SESSION['login_error']; ?>';
    var loginForm = document.querySelector('#portfolio form');
    if(loginForm) {
        loginForm.insertBefore(errorAlert, loginForm.firstChild);
    }
    <?php 
    // Clear the error message after displaying it
    unset($_SESSION['login_error']);
    endif; 
    ?>
});

</script>
</body>

</html>


<?php 

if (isset($_SESSION['okToEnterAuthenticationCode']) OR isset($_SESSION['notOkToEnterPassword'])) {
		    
  unset($_SESSION['okToEnterAuthenticationCode']);
  unset($_SESSION['notOkToEnterPassword']);

  echo '<script>';
  echo 'document.addEventListener("DOMContentLoaded", function() {';
  echo '    var authenCodeModal = new bootstrap.Modal(document.getElementById("authenCodeModal"));';
  echo '    authenCodeModal.show();';
  echo '});';
  echo '</script>';
}elseif (isset($_SESSION['okToEnterPassword'])) {

unset($_SESSION['okToEnterPassword']);

  echo '<script>';
  echo 'document.addEventListener("DOMContentLoaded", function() {';
  echo '    var authenCodeModal = new bootstrap.Modal(document.getElementById("enterNewPassModal"));';
  echo '    authenCodeModal.show();';
  echo '});';
  echo '</script>';
}


?>