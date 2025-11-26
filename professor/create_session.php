<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('professor');

$professor_id = get_current_user_id();

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header('Location: index.php');
    exit;
}

$course_id = (int)$_GET['course_id'];

// Get course info
$stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $course_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$course = mysqli_fetch_assoc($result);

if (!$course) {
    die('Course not found');
}

// Handle session creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_date = $_POST['session_date'];
    $start_time = $_POST['start_time'] ?: null;
    $end_time = $_POST['end_time'] ?: null;
    $created_by = get_current_user_id();
    
    $insert_stmt = mysqli_prepare($conn, "INSERT INTO sessions (course_id, session_date, start_time, end_time, created_by) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($insert_stmt, 'isssi', $course_id, $session_date, $start_time, $end_time, $created_by);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        $new_session_id = mysqli_insert_id($conn);
        header('Location: mark_attendance.php?session_id=' . $new_session_id);
        exit;
    } else {
        $error = 'Error creating session';
    }
}

$page_title = 'Create Session';
include __DIR__ . '/../templates/header.php';
?>

<div id="notification-area"></div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-calendar-plus"></i> Create New Session</h1>
    <div>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Session Details</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong>Course:</strong> <?php echo htmlspecialchars($course['code']) . ' - ' . htmlspecialchars($course['title']); ?>
        </div>
        
        <form method="POST">
            <div class="mb-3">
                <label for="session_date" class="form-label">Session Date *</label>
                <input type="date" class="form-control" id="session_date" name="session_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_time" class="form-label">Start Time (Optional)</label>
                    <input type="time" class="form-control" id="start_time" name="start_time">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="end_time" class="form-label">End Time (Optional)</label>
                    <input type="time" class="form-control" id="end_time" name="end_time">
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Create Session & Mark Attendance
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
