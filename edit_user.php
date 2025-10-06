<?php
session_start();

// Check if the 'id' GET parameter is set
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Database connection variables - adjust these to your environment
    $host = 'localhost';
    $dbUsername = 'root'; // Use the root username for MySQL
    $password = ''; // The password for the MySQL root user
    $dbname = 'SIMS'; // The database name

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch the user's data from the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // No user found with the given ID
            $_SESSION['error_message'] = "No user found with ID: $userId";
            header('Location: users.php');
            exit();
        }

        // If the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the updated user data from the form
            $username = $_POST['username'];
            $email = $_POST['email'];
            $role = $_POST['role'];

            // Update user details in the database
            $updateStmt = $conn->prepare("UPDATE users SET username = :username, email = :email, role = :role WHERE user_id = :user_id");
            $updateStmt->execute(['username' => $username, 'email' => $email, 'role' => $role, 'user_id' => $userId]);

            // Set success message in the session
            $_SESSION['successMessage'] = "User account updated successfully!";
            header('Location: users.php'); // Redirect to users.php to prevent form resubmission
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header('Location: users.php');
        exit();
    }
} else {
    $_SESSION['error_message'] = "No user ID provided for editing.";
    header('Location: users.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <!-- Oservefavicon -->
    <link href="assets/img/oserve-favicon.png" rel="icon">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
    <style>
    body {
        background-color: #f4f4f4;
    }
    .card {
        background-color: #ffffff;
        padding: 20px;
        margin-bottom: 40px;
        margin-top: -10px;
        border-radius: 10px;
        box-shadow: 0 8px 32px 0 rgba(10, 82, 25, 0.37);
    }
    .card-header {
        background-color: transparent;
        color: #000;
        border-bottom: none;
    }
    .align-items-center {
    min-height: 100vh; /* Center vertically */
    }
    h2, label{
            color: #444444;
        }
        h2{
            text-align: center;
            margin-top: -10px;
        }
        label{
            margin-bottom: 5px;
            text-transform: uppercase;
            color: #444444;
            font-weight: 600;
        }
        .card-title {
            text-transform: uppercase;
        }

    </style>
<body>
    <!-- Check for an error message in the session -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="container-fluid">
    <div class="row justify-content-center align-items-center" style="height: 100vh;">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-center">Update User</h3>
                </div>
                <div class="card-body">
                    <form action="update_user.php" method="POST">
                        <input type="hidden" class="form-control form-control-lg" name="user_id" value="<?php echo $userId; ?>" />

                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control form-control-lg" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
    <label for="role">Role:</label>
    <select class="form-control form-control-lg" id="role" name="role">
        <!-- <option value="Admin" <?= (isset($user['role']) && $user['role'] == 'Admin') ? 'selected' : '' ?>>Admin</option> -->
        <option value="Staff" <?= (isset($user['role']) && $user['role'] == 'Staff') ? 'selected' : '' ?>>Staff</option>
        <!-- <option value="Admin CS" <?= (isset($user['role']) && $user['role'] == 'Admin CS') ? 'selected' : '' ?>>Admin CS</option> -->
        <option value="Admin CSD" <?= (isset($user['role']) && $user['role'] == 'Admin CSD') ? 'selected' : '' ?>>Admin CSD</option>
        <option value="Admin PC" <?= (isset($user['role']) && $user['role'] == 'Admin PC') ? 'selected' : '' ?>>Admin PC</option>
    </select>
</div>


                        <!-- buttons -->
                        <div class="text-right mt-5"> 
                            <a href="users.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="update" class="btn btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



    <!-- Your footer content here -->

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
