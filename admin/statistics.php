<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Admin');

// Get overall statistics
$stats = get_system_stats();
$overall_presence_rate = get_overall_presence_rate();

// Get monthly trend data (last 6 months)
$monthly_query = "SELECT 
    DATE_FORMAT(s.session_date, '%Y-%m') as month,
    DATE_FORMAT(s.session_date, '%b %Y') as month_label,
    COUNT(DISTINCT s.id) as total_sessions,
    COUNT(a.id) as total_records,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
    SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused_count,
    ROUND(SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(a.id), 0), 1) as presence_rate
FROM sessions s
LEFT JOIN attendance a ON s.id = a.session_id
WHERE s.session_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY month, month_label
ORDER BY month ASC";

$monthly_result = mysqli_query($conn, $monthly_query);
$monthly_stats = [];
if ($monthly_result) {
    $monthly_stats = mysqli_fetch_all($monthly_result, MYSQLI_ASSOC);
}

// Get course-wise statistics
$course_query = "SELECT 
    c.id, c.code, c.title,
    COUNT(DISTINCT s.id) as session_count,
    COUNT(DISTINCT cs.student_id) as enrolled_students,
    COUNT(DISTINCT cp.professor_id) as professor_count,
    COUNT(a.id) as total_attendance,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
    ROUND(SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(a.id), 0), 1) as attendance_rate
FROM courses c
LEFT JOIN sessions s ON c.id = s.course_id
LEFT JOIN course_students cs ON c.id = cs.course_id
LEFT JOIN course_professors cp ON c.id = cp.course_id
LEFT JOIN attendance a ON s.id = a.session_id
GROUP BY c.id, c.code, c.title
HAVING session_count > 0
ORDER BY attendance_rate DESC";

$course_result = mysqli_query($conn, $course_query);
$course_stats = [];
if ($course_result) {
    $course_stats = mysqli_fetch_all($course_result, MYSQLI_ASSOC);
}

// Get top performing students
$top_students_query = "SELECT 
    u.id, u.first_name, u.last_name, u.email,
    COUNT(a.id) as total_sessions,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
    ROUND(SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(a.id), 0), 1) as attendance_rate
FROM users u
INNER JOIN attendance a ON u.id = a.student_id
WHERE u.role_id = (SELECT id FROM roles WHERE name = 'Student')
GROUP BY u.id
HAVING total_sessions >= 10
ORDER BY attendance_rate DESC, total_sessions DESC
LIMIT 10";

$top_students_result = mysqli_query($conn, $top_students_query);
$top_students = [];
if ($top_students_result) {
    $top_students = mysqli_fetch_all($top_students_result, MYSQLI_ASSOC);
}

// Get students needing attention (low attendance)
$low_attendance_query = "SELECT 
    u.id, u.first_name, u.last_name, u.email,
    COUNT(a.id) as total_sessions,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
    ROUND(SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(a.id), 0), 1) as attendance_rate
FROM users u
INNER JOIN attendance a ON u.id = a.student_id
WHERE u.role_id = (SELECT id FROM roles WHERE name = 'Student')
GROUP BY u.id
HAVING total_sessions >= 10 AND attendance_rate < 70
ORDER BY attendance_rate ASC
LIMIT 10";

$low_attendance_result = mysqli_query($conn, $low_attendance_query);
$low_attendance_students = [];
if ($low_attendance_result) {
    $low_attendance_students = mysqli_fetch_all($low_attendance_result, MYSQLI_ASSOC);
}

// Get professor statistics
$professor_stats_query = "SELECT 
    u.id, u.first_name, u.last_name,
    COUNT(DISTINCT s.id) as sessions_held,
    COUNT(DISTINCT s.course_id) as courses_taught,
    COUNT(a.id) as attendance_records,
    ROUND(AVG(CASE WHEN a.status = 'present' THEN 100 ELSE 0 END), 1) as avg_attendance_rate
FROM users u
INNER JOIN sessions s ON u.id = s.created_by
LEFT JOIN attendance a ON s.id = a.session_id
WHERE u.role_id = (SELECT id FROM roles WHERE name = 'Professor')
GROUP BY u.id
ORDER BY sessions_held DESC";

$professor_result = mysqli_query($conn, $professor_stats_query);
$professor_stats = [];
if ($professor_result) {
    $professor_stats = mysqli_fetch_all($professor_result, MYSQLI_ASSOC);
}

$page_title = 'Analytics & Reports';
include __DIR__ . '/../templates/header.php';
?>

<div id="notification-area"></div>

<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Analytics & Reports</h1>
    <div class="page-actions">
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<!-- Overall Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo number_format($stats['total_students']); ?></div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo number_format($stats['total_professors']); ?></div>
                <div class="stat-label">Professors</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo number_format($stats['total_courses']); ?></div>
                <div class="stat-label">Active Courses</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon <?php echo $overall_presence_rate >= 75 ? 'bg-success' : ($overall_presence_rate >= 60 ? 'bg-warning' : 'bg-danger'); ?>">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo number_format($overall_presence_rate, 1); ?>%</div>
                <div class="stat-label">Overall Presence Rate</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- Monthly Trend Chart -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Monthly Attendance Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendChart" height="80"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Attendance Status Distribution -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Course Performance -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Course Performance</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Title</th>
                                <th>Students</th>
                                <th>Professors</th>
                                <th>Sessions</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Attendance Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($course_stats)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No course data available yet
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($course_stats as $course): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($course['code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $course['enrolled_students']; ?></span></td>
                                    <td><span class="badge bg-info"><?php echo $course['professor_count']; ?></span></td>
                                    <td><?php echo $course['session_count']; ?></td>
                                    <td><span class="badge bg-success"><?php echo $course['present_count']; ?></span></td>
                                    <td><span class="badge bg-danger"><?php echo $course['absent_count']; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo $course['late_count']; ?></span></td>
                                    <td>
                                        <?php 
                                        $rate = $course['attendance_rate'] ?? 0;
                                        $badge_class = $rate >= 75 ? 'success' : ($rate >= 60 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>"><?php echo number_format($rate, 1); ?>%</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Students & Low Attendance -->
<div class="row g-3 mb-4">
    <!-- Top Performing Students -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-trophy"></i> Top Performing Students</h5>
            </div>
            <div class="card-body">
                <?php if (empty($top_students)): ?>
                    <p class="text-muted text-center py-3">No student data available yet</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($top_students as $index => $student): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo ($index + 1); ?>. <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                <br>
                                <small><?php echo $student['present_count']; ?>/<?php echo $student['total_sessions']; ?> sessions</small>
                            </div>
                            <span class="badge bg-success fs-6"><?php echo number_format($student['attendance_rate'], 1); ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Students Needing Attention -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Students Needing Attention</h5>
            </div>
            <div class="card-body">
                <?php if (empty($low_attendance_students)): ?>
                    <p class="text-success text-center py-3"><i class="fas fa-check-circle"></i> All students maintaining good attendance!</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($low_attendance_students as $index => $student): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                <br>
                                <small class="text-danger"><?php echo $student['absent_count']; ?> absences</small>
                            </div>
                            <span class="badge bg-danger fs-6"><?php echo number_format($student['attendance_rate'], 1); ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Professor Statistics -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chalkboard-teacher"></i> Professor Activity</h5>
            </div>
            <div class="card-body">
                <?php if (empty($professor_stats)): ?>
                    <p class="text-muted text-center py-3">No professor activity data available yet</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Professor</th>
                                    <th>Courses Taught</th>
                                    <th>Sessions Held</th>
                                    <th>Attendance Records</th>
                                    <th>Avg. Attendance Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($professor_stats as $prof): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']); ?></strong></td>
                                    <td><span class="badge bg-primary"><?php echo $prof['courses_taught']; ?></span></td>
                                    <td><?php echo $prof['sessions_held']; ?></td>
                                    <td><?php echo number_format($prof['attendance_records']); ?></td>
                                    <td>
                                        <?php 
                                        $rate = $prof['avg_attendance_rate'] ?? 0;
                                        $badge_class = $rate >= 75 ? 'success' : ($rate >= 60 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>"><?php echo number_format($rate, 1); ?>%</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Monthly Trend Chart
const monthlyCtx = document.getElementById('monthlyTrendChart');
if (monthlyCtx) {
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($monthly_stats, 'month_label')); ?>,
            datasets: [
                {
                    label: 'Present',
                    data: <?php echo json_encode(array_column($monthly_stats, 'present_count')); ?>,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Absent',
                    data: <?php echo json_encode(array_column($monthly_stats, 'absent_count')); ?>,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Late',
                    data: <?php echo json_encode(array_column($monthly_stats, 'late_count')); ?>,
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Status Distribution Pie Chart
<?php
// Calculate total status counts
$total_present = array_sum(array_column($monthly_stats, 'present_count'));
$total_absent = array_sum(array_column($monthly_stats, 'absent_count'));
$total_late = array_sum(array_column($monthly_stats, 'late_count'));
$total_excused = array_sum(array_column($monthly_stats, 'excused_count'));
?>

const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Excused'],
            datasets: [{
                data: [<?php echo $total_present; ?>, <?php echo $total_absent; ?>, <?php echo $total_late; ?>, <?php echo $total_excused; ?>],
                backgroundColor: [
                    'rgb(16, 185, 129)',
                    'rgb(239, 68, 68)',
                    'rgb(245, 158, 11)',
                    'rgb(99, 102, 241)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}
</script>

    </div>
</div>

<!-- Chart.js for charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    // Monthly attendance chart
    const monthlyData = <?php echo json_encode(array_reverse($monthly_stats)); ?>;
    
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [
                {
                    label: 'Present',
                    data: monthlyData.map(d => d.present_count),
                    borderColor: 'rgb(25, 135, 84)',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.1
                },
                {
                    label: 'Absent',
                    data: monthlyData.map(d => d.absent_count),
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.1
                },
                {
                    label: 'Late',
                    data: monthlyData.map(d => d.late_count),
                    borderColor: 'rgb(255, 193, 7)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Last 6 Months Attendance Overview'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
