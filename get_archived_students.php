<?php
require 'dbconfig.php';

$sql = "SELECT students.*, program.program_name FROM students 
LEFT JOIN program ON students.program_id = program.program_id
WHERE students.is_archived = 1";

$stmt = $pdo->query($sql);
$archivedStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

