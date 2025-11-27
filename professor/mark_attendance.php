<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Professor');
$professor_id = get_current_user_id();

$professor_id = get_current_user_id();

// Get session details
if (!isset($_GET['session_id']) || !is_numeric($_GET['session_id'])) {
    header('Location: index.php');
    exit;
}

$session_id = (int)$_GET['session_id'];

// Get session info
$stmt = mysqli_prepare($conn, "SELECT s.*, c.code, c.title FROM sessions s INNER JOIN courses c ON s.course_id = c.id WHERE s.id = ?");
mysqli_stmt_bind_param($stmt, 'i', $session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$session = mysqli_fetch_assoc($result);

if (!$session) {
    die('Session not found');
}

// Get enrolled students
$students = get_course_students($session['course_id']);

// Get existing attendance records
$existing_attendance = get_session_attendance($session_id);
$attendance_map = [];
foreach ($existing_attendance as $record) {
    $attendance_map[$record['student_id']] = $record['status'];
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $marked_by = get_current_user_id();
    
    foreach ($_POST['attendance'] as $student_id => $status) {
        $student_id = (int)$student_id;
        
        // Check if attendance already exists
        $check_stmt = mysqli_prepare($conn, "SELECT id FROM attendance WHERE session_id = ? AND student_id = ?");
        mysqli_stmt_bind_param($check_stmt, 'ii', $session_id, $student_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing record
            $update_stmt = mysqli_prepare($conn, "UPDATE attendance SET status = ?, marked_by = ?, marked_at = NOW() WHERE session_id = ? AND student_id = ?");
            mysqli_stmt_bind_param($update_stmt, 'siii', $status, $marked_by, $session_id, $student_id);
            mysqli_stmt_execute($update_stmt);
        } else {
            // Insert new record
            $insert_stmt = mysqli_prepare($conn, "INSERT INTO attendance (session_id, student_id, status, marked_by) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($insert_stmt, 'iisi', $session_id, $student_id, $status, $marked_by);
            mysqli_stmt_execute($insert_stmt);
        }
    }
    
    $success = 'Attendance marked successfully';
    
    // Refresh attendance data
    $existing_attendance = get_session_attendance($session_id);
    $attendance_map = [];
    foreach ($existing_attendance as $record) {
        $attendance_map[$record['student_id']] = $record['status'];
    }
}

$page_title = 'Mark Attendance';
include __DIR__ . '/../templates/header.php';
?>

<div id="notification-area"></div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-clipboard-check"></i> Mark Attendance</h1>
    <div>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Session Details</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <strong>Course:</strong> <?php echo htmlspecialchars($session['code']) . ' - ' . htmlspecialchars($session['title']); ?>
            </div>
            <div class="col-md-4">
                <strong>Date:</strong> <?php echo format_date($session['session_date']); ?>
            </div>
            <div class="col-md-4">
                <strong>Time:</strong> 
                <?php 
                if ($session['start_time'] && $session['end_time']) {
                    echo format_time($session['start_time']) . ' - ' . format_time($session['end_time']);
                } else {
                    echo 'All day';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Student List (<?php echo count($students); ?> students)</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $index = 1; ?>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo $index++; ?></td>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td>
                                <?php $current_status = $attendance_map[$student['id']] ?? 'present'; ?>
                                <select name="attendance[<?php echo $student['id']; ?>]" class="form-select form-select-sm">
                                    <option value="present" <?php echo $current_status === 'present' ? 'selected' : ''; ?>>Present</option>
                                    <option value="absent" <?php echo $current_status === 'absent' ? 'selected' : ''; ?>>Absent</option>
                                    <option value="late" <?php echo $current_status === 'late' ? 'selected' : ''; ?>>Late</option>
                                    <option value="excused" <?php echo $current_status === 'excused' ? 'selected' : ''; ?>>Excused</option>
                                </select>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
