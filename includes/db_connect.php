<?php
// creates a mysqli connection using includes/config.php constants
// Sets $conn as the global connection variable

// Prevent direct access
if (!defined('DB_HOST') && !getenv('DB_HOST')) {
    // try to include config.php automatically
    // but avoid infinite loops; assume config.php already loaded from index.php
}

$conn = null;
function db_connect()
{
    global $conn;
    if ($conn instanceof mysqli) {
        return $conn;
    }

    // Use constants defined in includes/config.php
    $host = defined('DB_HOST') ? DB_HOST : getenv('DB_HOST');
    $user = defined('DB_USER') ? DB_USER : getenv('DB_USER');
    $pass = defined('DB_PASS') ? DB_PASS : getenv('DB_PASS');
    $name = defined('DB_NAME') ? DB_NAME : getenv('DB_NAME');

    $conn = new mysqli($host, $user, $pass, $name);
    if ($conn->connect_errno) {
        // In development, show error; in production consider logging instead
        error_log('Database connection failed: ' . $conn->connect_error);
        die('Database connection failed: ' . htmlspecialchars($conn->connect_error));
    }

    // Use utf8mb4 by default
    if (! $conn->set_charset('utf8mb4')) {
        error_log('Error setting charset: ' . $conn->error);
    }

    return $conn;
}

// Initialize connection immediately when file included
db_connect();

?>
