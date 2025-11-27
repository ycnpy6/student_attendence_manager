<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Student');
$student_id = get_current_user_id();
$user = get_user_by_id($student_id);

// Get quick stats
$stats_query = "SELECT 
    COUNT(DISTINCT cs.course_id) as courses,
    COUNT(a.id) as total_sessions,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent
    FROM course_students cs
    LEFT JOIN sessions s ON cs.course_id = s.course_id
    LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
    WHERE cs.student_id = ?";
$stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stmt, 'ii', $student_id, $student_id);
mysqli_stmt_execute($stmt);
$stats = mysqli_stmt_get_result($stmt)->fetch_assoc();

$attendance_rate = $stats['total_sessions'] > 0 ? round(($stats['present'] / $stats['total_sessions']) * 100) : 0;

$page_title = 'Dashboard';
include __DIR__ . '/../templates/header.php';
?>

<style>
.dash-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
.welcome-card {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
    padding: 32px;
    border-radius: 16px;
    margin-bottom: 30px;
}
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.stat-box {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    text-align: center;
}
.stat-number {
    font-size: 36px;
    font-weight: 700;
    display: block;
    margin-bottom: 4px;
}
.stat-text {
    color: #6b7280;
    font-size: 14px;
    text-transform: uppercase;
    font-weight: 600;
}
.quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}
.link-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
    border-left: 4px solid #6366f1;
}
.link-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    color: inherit;
}
.link-icon {
    width: 48px;
    height: 48px;
    background: #ede9fe;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #6366f1;
    margin-bottom: 16px;
}
</style>

<div class="dash-container">

    <div class="welcome-card">
        <h1 style="margin: 0 0 8px 0; font-size: 32px;">
            Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>! 👋
        </h1>
        <p style="margin: 0; opacity: 0.9; font-size: 16px;">
            Here's your attendance overview
        </p>
    </div>

    <div class="stat-grid">
        <div class="stat-box">
            <span class="stat-number" style="color: #6366f1;"><?php echo $stats['courses']; ?></span>
            <span class="stat-text">Enrolled Courses</span>
        </div>
        <div class="stat-box">
            <span class="stat-number" style="color: #10b981;"><?php echo $stats['present']; ?></span>
            <span class="stat-text">Sessions Attended</span>
        </div>
        <div class="stat-box">
            <span class="stat-number" style="color: #ef4444;"><?php echo $stats['absent']; ?></span>
            <span class="stat-text">Absences</span>
        </div>
        <div class="stat-box">
            <span class="stat-number" style="color: <?php echo $attendance_rate >= 75 ? '#10b981' : ($attendance_rate >= 60 ? '#f59e0b' : '#ef4444'); ?>">
                <?php echo $attendance_rate; ?>%
            </span>
            <span class="stat-text">Attendance Rate</span>
        </div>
    </div>

    <h3 style="margin: 0 0 20px 0; color: #111827; font-size: 20px;">Quick Actions</h3>
    
    <div class="quick-links">
        <a href="my_attendance.php" class="link-card">
            <div class="link-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h4 style="margin: 0 0 8px 0; color: #111827; font-size: 18px;">My Attendance</h4>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">
                View your attendance records for all courses
            </p>
        </a>

        <a href="justify_absence.php" class="link-card" style="border-left-color: #f59e0b;">
            <div class="link-icon" style="background: #fef3c7; color: #f59e0b;">
                <i class="fas fa-file-upload"></i>
            </div>
            <h4 style="margin: 0 0 8px 0; color: #111827; font-size: 18px;">Justify Absence</h4>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">
                Submit justification for your absences
            </p>
        </a>

        <a href="my_justifications.php" class="link-card" style="border-left-color: #10b981;">
            <div class="link-icon" style="background: #dcfce7; color: #10b981;">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h4 style="margin: 0 0 8px 0; color: #111827; font-size: 18px;">My Justifications</h4>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">
                Track status of submitted justifications
            </p>
        </a>
    </div>

</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
                                        
                                        <div class="mt-3">
                                            <strong>Attendance Rate:</strong>
                                            <div class="progress mt-2" style="height: 25px;">
                                                <div class="progress-bar bg-<?php echo $badge_class; ?>" role="progressbar" style="width: <?php echo $rate; ?>%">
                                                    <?php echo $rate; ?>%
                                                </div>
                                            </div>
                                        </div>
                                        

<?php include __DIR__ . '/../templates/footer.php'; ?>
