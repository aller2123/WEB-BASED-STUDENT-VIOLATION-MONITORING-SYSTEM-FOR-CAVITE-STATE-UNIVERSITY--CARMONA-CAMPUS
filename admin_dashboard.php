<?php
session_start();

// Redirect non-admins to the main dashboard
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: main.php');
    exit();
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'No username set';
?>
