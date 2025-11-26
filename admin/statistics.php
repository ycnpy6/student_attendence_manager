<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

// Get attendance statistics
$query = "SELECT 
            DATE_FORMAT(s.session_date, '%Y-%m') as month,
            COUNT(DISTINCT s.id) as total_sessions,
            COUNT(a.id) as total_records,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
            SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count
          FROM sessions s
          LEFT JOIN attendance a ON s.id = a.session_id
          GROUP BY month
          ORDER BY month DESC
          LIMIT 6";

$result = mysqli_query($conn, $query);
$monthly_stats = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get course-wise statistics
$course_query = "SELECT 
                    c.code, c.title,
                    COUNT(DISTINCT s.id) as session_count,
                    COUNT(DISTINCT cs.student_id) as enrolled_students,
                    COUNT(a.id) as total_attendance,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count
                 FROM courses c
                 LEFT JOIN sessions s ON c.id = s.course_id
                 LEFT JOIN course_students cs ON c.id = cs.course_id
                 LEFT JOIN attendance a ON s.id = a.session_id
                 GROUP BY c.id
                 ORDER BY c.code";

$course_result = mysqli_query($conn, $course_query);
$course_stats = mysqli_fetch_all($course_result, MYSQLI_ASSOC);

$stats = get_system_stats();
$page_title = 'Statistics & Reports';
include __DIR__ . '/../templates/header.php';
?>

<div id="notification-area"></div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-chart-bar"></i> Statistics & Reports</h1>
    <div>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<!-- Overall Stats -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card success">
            <div class="card-body text-center">
                <i class="fas fa-user-graduate fa-2x text-success mb-2"></i>
                <h3><?php echo $stats['total_students']; ?></h3>
                <p class="text-muted mb-0">Total Students</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card warning">
            <div class="card-body text-center">
                <i class="fas fa-chalkboard-teacher fa-2x text-warning mb-2"></i>
                <h3><?php echo $stats['total_professors']; ?></h3>
                <p class="text-muted mb-0">Total Professors</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-book fa-2x text-primary mb-2"></i>
                <h3><?php echo $stats['total_courses']; ?></h3>
                <p class="text-muted mb-0">Total Courses</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card success">
            <div class="card-body text-center">
                <i class="fas fa-percentage fa-2x text-success mb-2"></i>
                <h3><?php echo $stats['attendance_rate']; ?>%</h3>
                <p class="text-muted mb-0">Attendance Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Attendance Chart -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Monthly Attendance Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Course Statistics Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-table"></i> Course-wise Statistics</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th>Sessions</th>
                                <th>Enrolled Students</th>
                                <th>Total Attendance</th>
                                <th>Present</th>
                                <th>Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($course_stats as $course): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($course['code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo $course['session_count']; ?></td>
                                <td><?php echo $course['enrolled_students']; ?></td>
                                <td><?php echo $course['total_attendance']; ?></td>
                                <td><?php echo $course['present_count']; ?></td>
                                <td>
                                    <?php 
                                    $rate = calculate_attendance_percentage($course['present_count'], $course['total_attendance']);
                                    $badge_class = $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>"><?php echo $rate; ?>%</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
