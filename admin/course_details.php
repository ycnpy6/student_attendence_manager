<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin role
require_role('Admin');

$success = '';
$error = '';
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$course_id) {
    header('Location: courses.php');
    exit;
}

// Handle student enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enroll') {
    $student_id = (int)$_POST['student_id'];
    if (enroll_student_in_course($student_id, $course_id)) {
        $success = 'Student enrolled successfully!';
    } else {
        $error = 'Failed to enroll student';
    }
}

// Handle student removal
if (isset($_GET['remove_student'])) {
    $student_id = (int)$_GET['remove_student'];
    if (remove_student_from_course($student_id, $course_id)) {
        $success = 'Student removed from course!';
    } else {
        $error = 'Failed to remove student';
    }
}

// Get course details
$course = get_course_by_id($course_id);
if (!$course) {
    header('Location: courses.php');
    exit;
}

// Get enrolled students and all students
$enrolled_students = get_course_students($course_id);
$all_students = get_all_students();
$assigned_professors = get_course_professors($course_id);
$attendance_rate = get_course_attendance_rate($course_id);

$page_title = 'Course Details - ' . $course['code'];
require_once __DIR__ . '/../templates/header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <span class="badge bg-primary me-2"><?php echo htmlspecialchars($course['code']); ?></span>
                        <?php echo htmlspecialchars($course['title']); ?>
                    </h1>
                    <p class="page-subtitle"><?php echo htmlspecialchars($course['description'] ?: 'No description'); ?></p>
                </div>
                <a href="courses.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Course Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="stat-icon success">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-value"><?php echo count($enrolled_students); ?></div>
                    <div class="stat-label">Enrolled Students</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card info">
                <div class="card-body">
                    <div class="stat-icon primary">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-value"><?php echo count($assigned_professors); ?></div>
                    <div class="stat-label">Assigned Professors</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="stat-icon warning">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($attendance_rate, 1); ?>%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon primary">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-value"><?php echo count(get_course_sessions($course_id)); ?></div>
                    <div class="stat-label">Total Sessions</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Enrolled Students List -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Enrolled Students</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#enrollStudentModal">
                        <i class="fas fa-user-plus"></i> Enroll Student
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($enrolled_students)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No students enrolled</h5>
                            <p class="text-secondary">Click "Enroll Student" to add students to this course</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student Name</th>
                                        <th>Email</th>
                                        <th>Attendance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrolled_students as $index => $student): ?>
                                        <?php $student_attendance = get_student_course_attendance($student['id'], $course_id); ?>
                                        <?php $student_rate = calculate_attendance_percentage($student_attendance['present'] ?? 0, $student_attendance['total'] ?? 0); ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-primary text-white me-2">
                                                        <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                                                    </div>
                                                    <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px; max-width: 100px;">
                                                        <div class="progress-bar bg-<?php echo $student_rate >= 75 ? 'success' : ($student_rate >= 50 ? 'warning' : 'danger'); ?>" 
                                                             style="width: <?php echo $student_rate; ?>%"></div>
                                                    </div>
                                                    <span class="text-secondary small"><?php echo number_format($student_rate, 1); ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="?id=<?php echo $course_id; ?>&remove_student=<?php echo $student['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Remove this student from the course?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
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

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Assigned Professors -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-chalkboard-teacher"></i> Professors</h6>
                    <a href="assign_professor.php?course_id=<?php echo $course_id; ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit"></i> Manage
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($assigned_professors)): ?>
                        <p class="text-muted small mb-0">No professors assigned</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($assigned_professors as $prof): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-secondary text-white me-2" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            <?php echo strtoupper(substr($prof['first_name'], 0, 1) . substr($prof['last_name'], 0, 1)); ?>
                                        </div>
                                        <div class="small">
                                            <strong><?php echo htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']); ?></strong>
                                            <br>
                                            <span class="text-secondary"><?php echo htmlspecialchars($prof['email']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="assign_professor.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-plus"></i> Assign Professor
                        </a>
                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#enrollStudentModal">
                            <i class="fas fa-user-graduate"></i> Enroll Students
                        </a>
                        <a href="../professor/create_session.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-calendar-plus"></i> Create Session
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enroll Student Modal -->
<div class="modal fade" id="enrollStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enroll Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="enroll">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Select Student</label>
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="">-- Choose Student --</option>
                            <?php foreach ($all_students as $student): ?>
                                <?php
                                // Check if already enrolled
                                $is_enrolled = false;
                                foreach ($enrolled_students as $enrolled) {
                                    if ($enrolled['id'] == $student['id']) {
                                        $is_enrolled = true;
                                        break;
                                    }
                                }
                                ?>
                                <option value="<?php echo $student['id']; ?>" <?php echo $is_enrolled ? 'disabled' : ''; ?>>
                                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['email'] . ')'); ?>
                                    <?php echo $is_enrolled ? '(Already enrolled)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Enroll Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid var(--border-color);
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
