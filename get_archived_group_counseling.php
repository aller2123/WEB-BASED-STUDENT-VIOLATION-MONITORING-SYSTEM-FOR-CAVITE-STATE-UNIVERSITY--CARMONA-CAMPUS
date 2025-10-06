<?php
session_start();
require 'dbconfig.php';

$pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);

$stmt = $pdo->prepare("
    SELECT * FROM multiple_counseling_sessions 
    WHERE is_archived = 1 
    ORDER BY created_at DESC
");
$stmt->execute();
$archivedGroupSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($archivedGroupSessions) > 0) {
    echo '<table class="table table-hover table-bordered">';
    echo '<thead><tr><th>Student Names</th><th>Year & Section</th><th>Program</th><th>Violation Type</th><th>Details</th><th>Assigned Team</th><th>Status</th><th>Action</th></tr></thead>';
    echo '<tbody>';
    foreach ($archivedGroupSessions as $session) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($session['student_names']) . '</td>';
        echo '<td>' . htmlspecialchars($session['year_section']) . '</td>';
        echo '<td>' . htmlspecialchars($session['program']) . '</td>';
        echo '<td>' . htmlspecialchars($session['violation_type']) . '</td>';
        echo '<td><button type="button" class="btn btn-link" onclick="showInfo(\'' . htmlspecialchars($session['violation_details']) . '\')">View Details</button></td>';
        echo '<td>' . htmlspecialchars($session['assigned_team']) . '</td>';
        echo '<td>' . htmlspecialchars($session['status']) . '</td>';
        echo '<td>
        <a href="unarchive_group_counseling.php?id=' . $session['id'] . '" class="btn btn-outline-info btn-sm">Unarchive</a>
        <a href="delete_group_counseling.php?id=' . $session['id'] . '" class="btn btn-outline-danger btn-sm" onclick="return confirm(\'Are you sure you want to permanently delete this session?\')">Delete</a>
    </td>';
            echo '</tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<p>No archived group counseling sessions found.</p>';
}
?>

<script>
function showInfo(info) {
    alert(info); // You can replace this with a more sophisticated modal if desired
}
</script>
