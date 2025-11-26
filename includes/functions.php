<?php
// Common utility functions

// Sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Hash password using bcrypt
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Get all roles from database
function get_all_roles() {
    global $conn;
    $query = "SELECT * FROM roles ORDER BY name";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get role ID by name
function get_role_id($role_name) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT id FROM roles WHERE name = ?");
    if (!$stmt) {
        error_log('MySQL prepare error in get_role_id: ' . mysqli_error($conn));
        return null;
    }
    mysqli_stmt_bind_param($stmt, 's', $role_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['id'] : null;
}

// Create new user
function create_user($first_name, $last_name, $email, $password, $role_id) {
    global $conn;
    
    $password_hash = hash_password($password);
    
    $stmt = mysqli_prepare($conn, "INSERT INTO users (first_name, last_name, email, password_hash, role_id) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log('MySQL prepare error in create_user: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'ssssi', $first_name, $last_name, $email, $password_hash, $role_id);
    
    return mysqli_stmt_execute($stmt);
}

// Get all courses
function get_all_courses() {
    global $conn;
    $query = "SELECT * FROM courses ORDER BY code";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get courses for a professor
function get_professor_courses($professor_id) {
    global $conn;
    $query = "SELECT c.* FROM courses c
              INNER JOIN course_professors cp ON c.id = cp.course_id
              WHERE cp.professor_id = ?
              ORDER BY c.code";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        return [];
    }
    mysqli_stmt_bind_param($stmt, 'i', $professor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get courses for a student
function get_student_courses($student_id) {
    global $conn;
    $query = "SELECT c.* FROM courses c
              INNER JOIN course_students cs ON c.id = cs.course_id
              WHERE cs.student_id = ?
              ORDER BY c.code";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        return [];
    }
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get students enrolled in a course
function get_course_students($course_id) {
    global $conn;
    $query = "SELECT u.* FROM users u
              INNER JOIN course_students cs ON u.id = cs.student_id
              WHERE cs.course_id = ?
              ORDER BY u.last_name, u.first_name";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get sessions for a course
function get_course_sessions($course_id) {
    global $conn;
    $query = "SELECT * FROM sessions
              WHERE course_id = ?
              ORDER BY session_date DESC, start_time DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get attendance for a session
function get_session_attendance($session_id) {
    global $conn;
    $query = "SELECT a.*, u.first_name, u.last_name, u.email
              FROM attendance a
              INNER JOIN users u ON a.student_id = u.id
              WHERE a.session_id = ?
              ORDER BY u.last_name, u.first_name";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get student attendance summary for a course
function get_student_attendance_summary($student_id, $course_id) {
    global $conn;
    $query = "SELECT 
                COUNT(*) as total_sessions,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused_count
              FROM sessions s
              LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
              WHERE s.course_id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $student_id, $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Format date for display
function format_date($date) {
    return date('M d, Y', strtotime($date));
}

// Format time for display
function format_time($time) {
    return date('g:i A', strtotime($time));
}

// Calculate attendance percentage
function calculate_attendance_percentage($present, $total) {
    if ($total == 0) return 0;
    return round(($present / $total) * 100, 2);
}

// Get all students (admin use)
function get_all_students() {
    global $conn;
    $query = "SELECT u.* FROM users u
              INNER JOIN roles r ON u.role_id = r.id
              WHERE r.name = 'student'
              ORDER BY u.last_name, u.first_name";
    
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get all professors (admin use)
function get_all_professors() {
    global $conn;
    $query = "SELECT u.* FROM users u
              INNER JOIN roles r ON u.role_id = r.id
              WHERE r.name = 'professor'
              ORDER BY u.last_name, u.first_name";
    
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Delete user by ID
function delete_user($user_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    return mysqli_stmt_execute($stmt);
}

// Get overall system statistics (admin dashboard)
function get_system_stats() {
    global $conn;
    
    $stats = [];
    
    // Total students
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users u INNER JOIN roles r ON u.role_id = r.id WHERE r.name = 'student'");
    $stats['total_students'] = mysqli_fetch_assoc($result)['count'];
    
    // Total professors
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users u INNER JOIN roles r ON u.role_id = r.id WHERE r.name = 'professor'");
    $stats['total_professors'] = mysqli_fetch_assoc($result)['count'];
    
    // Total courses
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM courses");
    $stats['total_courses'] = mysqli_fetch_assoc($result)['count'];
    
    // Total sessions
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM sessions");
    $stats['total_sessions'] = mysqli_fetch_assoc($result)['count'];
    
    // Attendance rate
    $result = mysqli_query($conn, "SELECT 
                                    COUNT(*) as total,
                                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                                   FROM attendance");
    $attendance_data = mysqli_fetch_assoc($result);
    $stats['attendance_rate'] = calculate_attendance_percentage($attendance_data['present'], $attendance_data['total']);
    
    return $stats;
}
?>
