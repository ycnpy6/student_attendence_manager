<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Student');
$student_id = get_current_user_id();

// Get enrolled courses with attendance
$query = "SELECT c.*, 
          COUNT(DISTINCT a.id) as total_sessions,
          SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
          SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
          SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late
          FROM courses c
          JOIN course_students cs ON c.id = cs.course_id
          LEFT JOIN sessions s ON c.id = s.course_id
          LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
          WHERE cs.student_id = ?
          GROUP BY c.id
          ORDER BY c.code";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $student_id, $student_id);
mysqli_stmt_execute($stmt);
$courses = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

$page_title = 'My Attendance';
include __DIR__ . '/../templates/header.php';
?>

<style>
.container-simple { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
.course-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s;
}
.course-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.course-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 20px;
}
.course-title {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px 0;
}
.course-code {
    color: #6366f1;
    font-weight: 600;
    font-size: 14px;
}
.attendance-rate {
    font-size: 32px;
    font-weight: 700;
    text-align: center;
    padding: 16px;
    border-radius: 8px;
}
.rate-good { background: #dcfce7; color: #16a34a; }
.rate-ok { background: #fef3c7; color: #ca8a04; }
.rate-bad { background: #fee2e2; color: #dc2626; }
.stats-row {
    display: flex;
    gap: 16px;
    margin-top: 16px;
}
.stat-item {
    flex: 1;
    text-align: center;
    padding: 12px;
    border-radius: 8px;
    background: #f9fafb;
}
.stat-value {
    font-size: 24px;
    font-weight: 700;
    display: block;
}
.stat-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    margin-top: 4px;
}
.btn-simple {
    display: inline-block;
    padding: 8px 16px;
    background: #6366f1;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-simple:hover {
    background: #4f46e5;
    color: white;
}
</style>

<div class="container-simple">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="margin: 0 0 8px 0; color: #111827;">My Attendance</h2>
            <p style="margin: 0; color: #6b7280;">Track your attendance across all courses</p>
        </div>
        <a href="justify_absence.php" class="btn-simple">
            <i class="fas fa-file-upload"></i> Justify Absence
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="background: #dcfce7; border-left: 4px solid #16a34a; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
            <i class="fas fa-check-circle" style="color: #16a34a;"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($courses)): ?>
        <div class="course-card" style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-inbox" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
            <h3 style="color: #6b7280;">No Courses Found</h3>
            <p style="color: #9ca3af;">You're not enrolled in any courses yet.</p>
        </div>
    <?php else: ?>
        
        <?php foreach ($courses as $course): 
            $total = $course['total_sessions'];
            $present = $course['present'];
            $rate = $total > 0 ? round(($present / $total) * 100) : 0;
            
            if ($rate >= 75) {
                $rate_class = 'rate-good';
            } elseif ($rate >= 60) {
                $rate_class = 'rate-ok';
            } else {
                $rate_class = 'rate-bad';
            }
        ?>
        
        <div class="course-card">
            <div class="course-header">
                <div>
                    <div class="course-code"><?php echo htmlspecialchars($course['code']); ?></div>
                    <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                </div>
                <div class="attendance-rate <?php echo $rate_class; ?>" style="min-width: 100px;">
                    <?php echo $rate; ?>%
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-value" style="color: #6b7280;"><?php echo $total; ?></span>
                    <span class="stat-label">Total Sessions</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" style="color: #16a34a;"><?php echo $present; ?></span>
                    <span class="stat-label">Present</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" style="color: #dc2626;"><?php echo $course['absent']; ?></span>
                    <span class="stat-label">Absent</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value" style="color: #ca8a04;"><?php echo $course['late']; ?></span>
                    <span class="stat-label">Late</span>
                </div>
            </div>
        </div>
        
        <?php endforeach; ?>
        
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
