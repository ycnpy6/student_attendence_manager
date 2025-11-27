<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Professor');
$professor_id = get_current_user_id();

// Handle new session creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_session'])) {
    $course_id = (int)$_POST['course_id'];
    $date = $_POST['session_date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    
    $stmt = mysqli_prepare($conn, "INSERT INTO sessions (course_id, session_date, start_time, end_time, created_by) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isssi', $course_id, $date, $start, $end, $professor_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Session created successfully!';
    } else {
        $_SESSION['error'] = 'Failed to create session.';
    }
    
    header('Location: sessions.php');
    exit;
}

// Get assigned courses
$courses_query = "SELECT DISTINCT c.* FROM courses c
                  JOIN course_professors cp ON c.id = cp.course_id
                  WHERE cp.professor_id = ?
                  ORDER BY c.code";
$stmt = mysqli_prepare($conn, $courses_query);
mysqli_stmt_bind_param($stmt, 'i', $professor_id);
mysqli_stmt_execute($stmt);
$courses = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

// Get recent sessions
$sessions_query = "SELECT s.*, c.code, c.title,
                   COUNT(a.id) as marked_count
                   FROM sessions s
                   JOIN courses c ON s.course_id = c.id
                   LEFT JOIN attendance a ON s.id = a.session_id
                   WHERE s.created_by = ?
                   GROUP BY s.id
                   ORDER BY s.session_date DESC, s.start_time DESC
                   LIMIT 10";
$stmt = mysqli_prepare($conn, $sessions_query);
mysqli_stmt_bind_param($stmt, 'i', $professor_id);
mysqli_stmt_execute($stmt);
$sessions = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

$page_title = 'My Sessions';
include __DIR__ . '/../templates/header.php';
?>

<style>
.simple-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
.card-white {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.form-group { margin-bottom: 16px; }
.label { 
    display: block; 
    font-weight: 600; 
    margin-bottom: 6px;
    color: #374151;
    font-size: 14px;
}
.input, .select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}
.input:focus, .select:focus {
    outline: none;
    border-color: #6366f1;
}
.btn-primary {
    background: #6366f1;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
}
.btn-primary:hover { background: #4f46e5; }
.session-item {
    padding: 16px;
    border-left: 3px solid #6366f1;
    background: #f9fafb;
    border-radius: 6px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
}
.btn-mark {
    background: #10b981;
    color: white;
}
.btn-mark:hover {
    background: #059669;
    color: white;
}
</style>

<div class="simple-container">

    <h2 style="margin: 0 0 8px 0; color: #111827;">My Sessions</h2>
    <p style="margin: 0 0 30px 0; color: #6b7280;">Create and manage your class sessions</p>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="background: #dcfce7; border-left: 4px solid #16a34a; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
        
        <!-- Create Session Form -->
        <div class="card-white">
            <h3 style="margin: 0 0 20px 0; font-size: 18px; color: #111827;">
                <i class="fas fa-plus-circle"></i> Create New Session
            </h3>

            <form method="POST">
                <div class="form-group">
                    <label class="label">Course</label>
                    <select name="course_id" class="select" required>
                        <option value="">Select course...</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>">
                                <?php echo htmlspecialchars($course['code'] . ' - ' . $course['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="label">Date</label>
                    <input type="date" name="session_date" class="input" required 
                           max="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="label">Start Time</label>
                        <input type="time" name="start_time" class="input" required>
                    </div>
                    <div class="form-group">
                        <label class="label">End Time</label>
                        <input type="time" name="end_time" class="input" required>
                    </div>
                </div>

                <button type="submit" name="create_session" class="btn-primary" style="width: 100%;">
                    <i class="fas fa-calendar-plus"></i> Create Session
                </button>
            </form>
        </div>

        <!-- Recent Sessions -->
        <div class="card-white">
            <h3 style="margin: 0 0 20px 0; font-size: 18px; color: #111827;">
                <i class="fas fa-history"></i> Recent Sessions
            </h3>

            <?php if (empty($sessions)): ?>
                <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                    <i class="fas fa-calendar-times" style="font-size: 36px; margin-bottom: 12px; display: block;"></i>
                    No sessions yet. Create your first session!
                </div>
            <?php else: ?>
                <?php foreach ($sessions as $session): 
                    // Count enrolled students
                    $count_query = "SELECT COUNT(*) as total FROM course_students WHERE course_id = ?";
                    $count_stmt = mysqli_prepare($conn, $count_query);
                    mysqli_stmt_bind_param($count_stmt, 'i', $session['course_id']);
                    mysqli_stmt_execute($count_stmt);
                    $count_result = mysqli_stmt_get_result($count_stmt);
                    $student_count = mysqli_fetch_assoc($count_result)['total'];
                ?>
                <div class="session-item">
                    <div>
                        <strong style="color: #111827; display: block; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($session['code']); ?> - 
                            <?php echo date('M d, Y', strtotime($session['session_date'])); ?>
                        </strong>
                        <span style="color: #6b7280; font-size: 13px;">
                            <i class="fas fa-clock"></i> 
                            <?php echo date('g:i A', strtotime($session['start_time'])); ?> - 
                            <?php echo date('g:i A', strtotime($session['end_time'])); ?>
                            <span style="margin-left: 12px;">
                                <i class="fas fa-users"></i> 
                                <?php echo $session['marked_count']; ?>/<?php echo $student_count; ?> marked
                            </span>
                        </span>
                    </div>
                    <a href="mark_attendance.php?session_id=<?php echo $session['id']; ?>" class="btn-sm btn-mark">
                        <i class="fas fa-check"></i> Mark Attendance
                    </a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
