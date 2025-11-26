<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('student');

$student_id = get_current_user_id();
$courses = get_student_courses($student_id);

$page_title = 'Student Dashboard';
include __DIR__ . '/../templates/header.php';
?>

<div id="notification-area"></div>

<h1><i class="fas fa-user-graduate"></i> Student Dashboard</h1>
<p class="text-muted">Welcome, <?php echo get_current_user_name(); ?>!</p>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-book"></i> My Enrolled Courses</h5>
            </div>
            <div class="card-body">
                <?php if (empty($courses)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You are not enrolled in any courses yet. Please contact the administrator.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($courses as $course): ?>
                            <?php
                            $summary = get_student_attendance_summary($student_id, $course['id']);
                            $rate = calculate_attendance_percentage($summary['present_count'], $summary['total_sessions']);
                            $badge_class = $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($course['code']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <h6><?php echo htmlspecialchars($course['title']); ?></h6>
                                        <?php if ($course['description']): ?>
                                            <p class="text-muted small"><?php echo htmlspecialchars($course['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="mt-3">
                                            <strong>Attendance Rate:</strong>
                                            <div class="progress mt-2" style="height: 25px;">
                                                <div class="progress-bar bg-<?php echo $badge_class; ?>" role="progressbar" style="width: <?php echo $rate; ?>%">
                                                    <?php echo $rate; ?>%
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <div class="row text-center">
                                                <div class="col">
                                                    <small class="text-muted">Total</small><br>
                                                    <strong><?php echo $summary['total_sessions']; ?></strong>
                                                </div>
                                                <div class="col">
                                                    <small class="text-success">Present</small><br>
                                                    <strong><?php echo $summary['present_count']; ?></strong>
                                                </div>
                                                <div class="col">
                                                    <small class="text-danger">Absent</small><br>
                                                    <strong><?php echo $summary['absent_count']; ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <a href="attendance.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-eye"></i> View Detailed Attendance
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
