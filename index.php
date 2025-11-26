

<?php
// Entry point — redirect to login or dashboard
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';

// If logged in, redirect to appropriate dashboard
if (is_logged_in()) {
    header('Location: ' . get_dashboard_url());
    exit;
}

// Otherwise, show login page
header('Location: /attendence_manager2.0/login.php');
exit;
?>