<?php
// Authentication and session management
if (!defined('DB_HOST')) {
    die('Direct access not allowed');
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user has specific role
function has_role($role_name) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role_name;
}

// Require login - redirect to login page if not authenticated
function require_login() {
    if (!is_logged_in()) {
        header('Location: /attendence_manager2.0/login.php');
        exit;
    }
}

// Require specific role - show error if user doesn't have permission
function require_role($role_name) {
    require_login();
    if (!has_role($role_name)) {
        http_response_code(403);
        die('Access denied. You do not have permission to view this page.');
    }
}

// Login user - validate credentials and set session
function login_user($email, $password) {
    global $conn;
    
    $email = mysqli_real_escape_string($conn, trim($email));
    
    $query = "SELECT u.id, u.first_name, u.last_name, u.email, u.password_hash, r.name as role_name
              FROM users u
              INNER JOIN roles r ON u.role_id = r.id
              WHERE u.email = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log('MySQL prepare error: ' . mysqli_error($conn));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role_name'];
            
            // Update last login time (optional)
            $update_query = "UPDATE users SET updated_at = NOW() WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, 'i', $user['id']);
                mysqli_stmt_execute($update_stmt);
            }
            
            return true;
        }
    }
    
    return false;
}

// Logout user - destroy session
function logout_user() {
    $_SESSION = array();
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    
    session_destroy();
}

// Get redirect URL based on user role
function get_dashboard_url() {
    if (has_role('Admin')) {
        return '/attendence_manager2.0/admin/index.php';
    } elseif (has_role('Professor')) {
        return '/attendence_manager2.0/professor/index.php';
    } elseif (has_role('Student')) {
        return '/attendence_manager2.0/student/index.php';
    }
    return '/attendence_manager2.0/index.php';
}

// Get current user ID
function get_current_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Get current user full name
function get_current_user_name() {
    if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
        return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    }
    return 'Guest';
}
?>
