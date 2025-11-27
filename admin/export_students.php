<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Admin');

$students = get_all_students();

// Set headers for Excel download (CSV format compatible with Excel)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Output CSV headers
echo "ID,First Name,Last Name,Email,Created Date\n";

// Output student data
foreach ($students as $student) {
    echo $student['id'] . ',';
    echo '"' . $student['first_name'] . '",';
    echo '"' . $student['last_name'] . '",';
    echo '"' . $student['email'] . '",';
    echo '"' . date('Y-m-d', strtotime($student['created_at'])) . '"' . "\n";
}

exit;
?>
