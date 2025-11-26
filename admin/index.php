<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

$stats = get_system_stats();
$page_title = 'Admin Dashboard';
include __DIR__ . '/../templates/header.php';
?>

<div id="notification-area"></div>

<h1><i class="fas fa-dashboard"></i> Admin Dashboard</h1>
<p class="text-muted">Welcome, <?php echo get_current_user_name(); ?>! Manage the attendance system here.</p>

<div class="row mt-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user-graduate"></i> Students</h5>
                <h2 class="mb-0"><?php echo $stats['total_students']; ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chalkboard-teacher"></i> Professors</h5>
                <h2 class="mb-0"><?php echo $stats['total_professors']; ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-book"></i> Courses</h5>
                <h2 class="mb-0"><?php echo $stats['total_courses']; ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-percentage"></i> Attendance Rate</h5>
                <h2 class="mb-0"><?php echo $stats['attendance_rate']; ?>%</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-tasks"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="students.php" class="btn btn-outline-primary btn-lg w-100">
                            <i class="fas fa-users"></i> Manage Students
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="statistics.php" class="btn btn-outline-success btn-lg w-100">
                            <i class="fas fa-chart-bar"></i> View Statistics
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="/attendence_manager2.0/logout.php" class="btn btn-outline-danger btn-lg w-100">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
