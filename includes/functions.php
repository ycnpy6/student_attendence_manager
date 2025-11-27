<?php
// Common utility functions

// Get user by ID
function get_user_by_id($user_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    if (!$stmt) {
        error_log('MySQL prepare error in get_user_by_id: ' . mysqli_error($conn));
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

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
    $query = "SELECT c.*, 
              (SELECT COUNT(*) FROM course_students WHERE course_id = c.id) as student_count,
              (SELECT COUNT(*) FROM course_professors WHERE course_id = c.id) as professor_count
              FROM courses c 
              ORDER BY c.code";
    $result = mysqli_query($conn, $query);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
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

// ============================================
// ADDITIONAL COURSE MANAGEMENT FUNCTIONS
// ============================================

// Get course by ID
function get_course_by_id($course_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ?");
    if (!$stmt) {
        error_log('MySQL prepare error in get_course_by_id: ' . mysqli_error($conn));
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'i', $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Create new course
function create_course($code, $title, $description = '') {
    global $conn;
    $stmt = mysqli_prepare($conn, "INSERT INTO courses (code, title, description) VALUES (?, ?, ?)");
    if (!$stmt) {
        error_log('MySQL prepare error in create_course: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'sss', $code, $title, $description);
    $result = mysqli_stmt_execute($stmt);
    return $result ? mysqli_insert_id($conn) : false;
}

// Update course
function update_course($course_id, $code, $title, $description = '') {
    global $conn;
    $stmt = mysqli_prepare($conn, "UPDATE courses SET code = ?, title = ?, description = ? WHERE id = ?");
    if (!$stmt) {
        error_log('MySQL prepare error in update_course: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'sssi', $code, $title, $description, $course_id);
    return mysqli_stmt_execute($stmt);
}

// Delete course
function delete_course($course_id) {
    global $conn;
    // First delete related records
    mysqli_query($conn, "DELETE FROM course_professors WHERE course_id = $course_id");
    mysqli_query($conn, "DELETE FROM course_students WHERE course_id = $course_id");
    
    $stmt = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ?");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'i', $course_id);
    return mysqli_stmt_execute($stmt);
}

// Get professors assigned to a course
function get_course_professors($course_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT u.* FROM users u
                                   INNER JOIN course_professors cp ON u.id = cp.professor_id
                                   WHERE cp.course_id = ?
                                   ORDER BY u.last_name, u.first_name");
    if (!$stmt) {
        error_log('MySQL prepare error in get_course_professors: ' . mysqli_error($conn));
        return [];
    }
    mysqli_stmt_bind_param($stmt, 'i', $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Assign professor to course
function assign_professor_to_course($professor_id, $course_id) {
    global $conn;
    // Check if already assigned
    $check = mysqli_prepare($conn, "SELECT * FROM course_professors WHERE professor_id = ? AND course_id = ?");
    mysqli_stmt_bind_param($check, 'ii', $professor_id, $course_id);
    mysqli_stmt_execute($check);
    $result = mysqli_stmt_get_result($check);
    
    if (mysqli_num_rows($result) > 0) {
        return true; // Already assigned
    }
    
    $stmt = mysqli_prepare($conn, "INSERT INTO course_professors (professor_id, course_id) VALUES (?, ?)");
    if (!$stmt) {
        error_log('MySQL prepare error in assign_professor_to_course: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $professor_id, $course_id);
    return mysqli_stmt_execute($stmt);
}

// Remove professor from course
function remove_professor_from_course($professor_id, $course_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "DELETE FROM course_professors WHERE professor_id = ? AND course_id = ?");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $professor_id, $course_id);
    return mysqli_stmt_execute($stmt);
}

// Enroll student in course
function enroll_student_in_course($student_id, $course_id) {
    global $conn;
    // Check if already enrolled
    $check = mysqli_prepare($conn, "SELECT * FROM course_students WHERE student_id = ? AND course_id = ?");
    mysqli_stmt_bind_param($check, 'ii', $student_id, $course_id);
    mysqli_stmt_execute($check);
    $result = mysqli_stmt_get_result($check);
    
    if (mysqli_num_rows($result) > 0) {
        return true; // Already enrolled
    }
    
    $stmt = mysqli_prepare($conn, "INSERT INTO course_students (student_id, course_id) VALUES (?, ?)");
    if (!$stmt) {
        error_log('MySQL prepare error in enroll_student_in_course: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $student_id, $course_id);
    return mysqli_stmt_execute($stmt);
}

// Remove student from course
function remove_student_from_course($student_id, $course_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "DELETE FROM course_students WHERE student_id = ? AND course_id = ?");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $student_id, $course_id);
    return mysqli_stmt_execute($stmt);
}

// ============================================
// ANALYTICS FUNCTIONS
// ============================================

// Get attendance rate for a course
function get_course_attendance_rate($course_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT 
                                    COUNT(*) as total,
                                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present
                                   FROM attendance a
                                   INNER JOIN sessions s ON a.session_id = s.id
                                   WHERE s.course_id = ?");
    if (!$stmt) {
        return 0;
    }
    mysqli_stmt_bind_param($stmt, 'i', $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    return calculate_attendance_percentage($data['present'], $data['total']);
}

// Get student attendance rate for a specific course
function get_student_course_attendance($student_id, $course_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT 
                                    COUNT(*) as total,
                                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
                                    SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused
                                   FROM attendance a
                                   INNER JOIN sessions s ON a.session_id = s.id
                                   WHERE a.student_id = ? AND s.course_id = ?");
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $student_id, $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get attendance summary by course
function get_attendance_by_course() {
    global $conn;
    $query = "SELECT c.id, c.code, c.title,
              COUNT(DISTINCT s.id) as total_sessions,
              COUNT(a.id) as total_records,
              SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
              SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
              (SELECT COUNT(*) FROM course_students WHERE course_id = c.id) as student_count
              FROM courses c
              LEFT JOIN sessions s ON c.id = s.course_id
              LEFT JOIN attendance a ON s.id = a.session_id
              GROUP BY c.id, c.code, c.title
              ORDER BY c.code";
    $result = mysqli_query($conn, $query);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

// Get professor performance stats
function get_professor_stats($professor_id) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT 
                                    COUNT(DISTINCT cp.course_id) as courses_taught,
                                    COUNT(DISTINCT s.id) as sessions_created,
                                    COUNT(a.id) as attendance_marked,
                                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count
                                   FROM course_professors cp
                                   LEFT JOIN sessions s ON cp.course_id = s.course_id AND s.created_by = ?
                                   LEFT JOIN attendance a ON s.id = a.session_id
                                   WHERE cp.professor_id = ?");
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $professor_id, $professor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get overall presence rate for dashboard
function get_overall_presence_rate() {
    global $conn;
    $query = "SELECT 
              COUNT(*) as total,
              SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
              FROM attendance";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        if ($row['total'] > 0) {
            return round(($row['present'] / $row['total']) * 100, 1);
        }
    }
    return 0;
}
?>
