<div class="wrapper">
        <!-- Sidebar  -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <!-- <h3 style="color: black;">Logo</h3> -->
                <img src="../Oserve/assets/img/oserve.png" alt="logo">
            </div>

            <ul class="list-unstyled components">
                <li>
                <a href="main.php" class="nav-link">Dashboard</a>
                </li>
                <li>
                    <a href="student.php" data-toggle="collapse" aria-expanded="false" class="nav-link active dropdown-toggle">Students</a>
                        <ul class="collapse list-unstyled" id="homeSubmenu">
                            <li>
                                <a href="#">sub-menu1</a>
                            </li>
                            <li>
                                <a href="#">sub-menu2</a>
                            </li>
                            <li>
                                <a href="#">sub-menu3</a>
                            </li>
                        </ul>
                </li>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <li>
                <a href="attendance.php" class="nav-link">Attendance</a>
                </li>
                <li>
                <a href="counseling.php" class="nav-link active">Counseling</a>
                </li>
                <li>
                <a href="requirements.php" class="nav-link">Compliance</a>
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
                <a href="requirements.php" class="nav-link">Compliance</a>
                </li>
                <?php endif; ?>
                <li>
                <a href="logout.php" class="nav-link">Logout</a>
                </li>


            </ul>
        </nav>
                </div>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <!-- Popper.JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
    <!-- jQuery Custom Scroller CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>
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