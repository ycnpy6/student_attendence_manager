<?php
// Example configuration file - Copy this to config.php and update with your settings

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set your database password
define('DB_NAME', 'attendance_manager'); // Database name

// Application Configuration
define('APP_NAME', 'Attendance Manager');
define('APP_VERSION', '2.0');

// Timezone
date_default_timezone_set('Africa/Algiers');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Environment (development/production)
define('ENVIRONMENT', 'development');

// Error Reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
