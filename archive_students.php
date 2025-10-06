<?php
// Include database connection and any necessary logic for retrieving archived students
include 'dbconfig.php';  // Update this to your actual database connection file

// Retrieve archived students from the database
$archivedStudents = []; // Replace with actual query logic to fetch archived students

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Students</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Archived Students</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($archivedStudents as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['student_id']) ?></td>
                        <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['surname']) ?></td>
                        <td><?= htmlspecialchars($student['program_name']) ?></td>
                        <td>
                            <form action="unarchive_student.php" method="POST" style="display: inline;">
                                <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to unarchive this student?');">Unarchive</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS, jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
