<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Professor');

$professor_id = get_current_user_id();
$courses = get_professor_courses($professor_id);

$page_title = 'Professor Dashboard';
include __DIR__ . '/../templates/header.php';
?>

<div id="notification-area"></div>

<h1><i class="fas fa-chalkboard-teacher"></i> Professor Dashboard</h1>
<p class="text-muted">Welcome, <?php echo get_current_user_name(); ?>!</p>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-book"></i> My Courses & Sessions</h5>
            </div>
            <div class="card-body">
                <?php if (empty($courses)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You are not assigned to any courses yet. Please contact the administrator.
                    </div>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <?php
                        $sessions = get_course_sessions($course['id']);
                        ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <strong><?php echo htmlspecialchars($course['code']); ?></strong> - <?php echo htmlspecialchars($course['title']); ?>
                                </h5>
                                <?php if ($course['description']): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($course['description']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <a href="create_session.php?course_id=<?php echo $course['id']; ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus"></i> Create New Session
                                    </a>
                                    <a href="attendance_summary.php?course_id=<?php echo $course['id']; ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-table"></i> View Attendance Summary
                                    </a>
                                </div>
                                
                                <h6>Recent Sessions:</h6>
                                <?php if (empty($sessions)): ?>
                                    <p class="text-muted">No sessions created yet.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($sessions, 0, 5) as $session): ?>
                                                <tr>
                                                    <td><?php echo format_date($session['session_date']); ?></td>
                                                    <td>
                                                        <?php 
                                                        if ($session['start_time'] && $session['end_time']) {
                                                            echo format_time($session['start_time']) . ' - ' . format_time($session['end_time']);
                                                        } else {
                                                            echo 'All day';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <a href="mark_attendance.php?session_id=<?php echo $session['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-check"></i> Mark Attendance
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php if (count($sessions) > 5): ?>
                                        <small class="text-muted">Showing 5 most recent sessions</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
