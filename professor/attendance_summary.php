<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('professor');

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

// Get all students enrolled in course
$students = get_course_students($course_id);

// Get attendance summary for each student
$summary_data = [];
foreach ($students as $student) {
    $summary = get_student_attendance_summary($student['id'], $course_id);
    $summary_data[] = [
        'student' => $student,
        'stats' => $summary
    ];
}

$page_title = 'Attendance Summary';
include __DIR__ . '/../templates/header.php';
?>

<div id="notification-area"></div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-table"></i> Attendance Summary</h1>
    <div>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Course Details</h5>
    </div>
    <div class="card-body">
        <h4><?php echo htmlspecialchars($course['code']) . ' - ' . htmlspecialchars($course['title']); ?></h4>
        <?php if ($course['description']): ?>
            <p class="text-muted"><?php echo htmlspecialchars($course['description']); ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Student Attendance Summary (<?php echo count($students); ?> students)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Total Sessions</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Excused</th>
                        <th>Attendance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $index = 1; ?>
                    <?php foreach ($summary_data as $data): ?>
                    <?php
                        $student = $data['student'];
                        $stats = $data['stats'];
                        $rate = calculate_attendance_percentage($stats['present_count'], $stats['total_sessions']);
                        $badge_class = $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                    ?>
                    <tr>
                        <td><?php echo $index++; ?></td>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo $stats['total_sessions']; ?></td>
                        <td><span class="badge bg-success"><?php echo $stats['present_count']; ?></span></td>
                        <td><span class="badge bg-danger"><?php echo $stats['absent_count']; ?></span></td>
                        <td><span class="badge bg-warning"><?php echo $stats['late_count']; ?></span></td>
                        <td><span class="badge bg-secondary"><?php echo $stats['excused_count']; ?></span></td>
                        <td>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-<?php echo $badge_class; ?>" role="progressbar" style="width: <?php echo $rate; ?>%">
                                    <?php echo $rate; ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
