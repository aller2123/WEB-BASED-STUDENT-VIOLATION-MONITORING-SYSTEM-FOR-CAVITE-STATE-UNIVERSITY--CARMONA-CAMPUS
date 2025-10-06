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
    $query = isset($_GET['query']) ? $_GET['query'] : '';

    // Prepare the search query
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS count,
            full_name,
            year_and_section,
            program.program_name,
            type_of_violation,
            GROUP_CONCAT(DISTINCT full_info SEPARATOR ' ') AS full_info,
            MAX(status) AS status,
            GROUP_CONCAT(DISTINCT violations.id) AS group_ids
        FROM violations
        JOIN program ON violations.program_id = program.program_id
        WHERE full_name LIKE :searchTerm OR program.program_name LIKE :searchTerm
        GROUP BY full_name, type_of_violation, violations.program_id
    ");

    // Bind parameters and execute
    $searchTerm = '%' . $query . '%'; // Add wildcard for partial matching
    $stmt->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the results as JSON
    header('Content-Type: application/json');
    echo json_encode($violations);
} catch (PDOException $e) {
    die("Could not connect to the database $database :" . $e->getMessage());
}
?>
