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
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;

// Handle professor assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign') {
    $course_id = (int)$_POST['course_id'];
    $professor_id = (int)$_POST['professor_id'];
    
    if (assign_professor_to_course($professor_id, $course_id)) {
        $success = 'Professor assigned successfully!';
    } else {
        $error = 'Failed to assign professor';
    }
}

// Handle professor removal
if (isset($_GET['remove']) && isset($_GET['course'])) {
    $professor_id = (int)$_GET['remove'];
    $course_id = (int)$_GET['course'];
    
    if (remove_professor_from_course($professor_id, $course_id)) {
        $success = 'Professor removed successfully!';
    } else {
        $error = 'Failed to remove professor';
    }
}

// Get all professors and courses
$all_professors = get_all_professors();
$all_courses = get_all_courses();

// If specific course, get its assigned professors
$assigned_professors = [];
$selected_course = null;
if ($course_id) {
    $assigned_professors = get_course_professors($course_id);
    $selected_course = get_course_by_id($course_id);
}

$page_title = 'Assign Professors to Courses';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Professor Assignment</h1>
                    <p class="page-subtitle">Manage professor-course assignments</p>
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

    <div class="row">
        <!-- Course Selection -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book"></i> Select Course</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course</label>
                            <select class="form-select" id="course_id" name="course_id" onchange="this.form.submit()">
                                <option value="">-- Select a Course --</option>
                                <?php foreach ($all_courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" <?php echo ($course_id == $course['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['code'] . ' - ' . $course['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <?php if ($selected_course): ?>
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="fw-bold"><?php echo htmlspecialchars($selected_course['code']); ?></h6>
                            <p class="mb-2 small"><?php echo htmlspecialchars($selected_course['title']); ?></p>
                            <div class="d-flex justify-content-between text-secondary small">
                                <span><i class="fas fa-users"></i> <?php echo $selected_course['student_count'] ?? 0; ?> Students</span>
                                <span><i class="fas fa-user-tie"></i> <?php echo count($assigned_professors); ?> Professors</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($course_id): ?>
                <!-- Assign New Professor -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus"></i> Assign Professor</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="assign">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            <div class="mb-3">
                                <label for="professor_id" class="form-label">Select Professor</label>
                                <select class="form-select" id="professor_id" name="professor_id" required>
                                    <option value="">-- Choose Professor --</option>
                                    <?php foreach ($all_professors as $prof): ?>
                                        <?php
                                        // Check if already assigned
                                        $is_assigned = false;
                                        foreach ($assigned_professors as $assigned) {
                                            if ($assigned['id'] == $prof['id']) {
                                                $is_assigned = true;
                                                break;
                                            }
                                        }
                                        ?>
                                        <option value="<?php echo $prof['id']; ?>" <?php echo $is_assigned ? 'disabled' : ''; ?>>
                                            <?php echo htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']); ?>
                                            <?php echo $is_assigned ? '(Already assigned)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Assign Professor
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Assigned Professors List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chalkboard-teacher"></i> Assigned Professors</h5>
                </div>
                <div class="card-body">
                    <?php if (!$course_id): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-arrow-left fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Select a course to view assigned professors</h5>
                        </div>
                    <?php elseif (empty($assigned_professors)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No professors assigned yet</h5>
                            <p class="text-secondary">Assign a professor using the form on the left</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Professor Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assigned_professors as $professor): ?>
                                        <?php $prof_stats = get_professor_stats($professor['id']); ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-primary text-white me-2">
                                                        <?php echo strtoupper(substr($professor['first_name'], 0, 1) . substr($professor['last_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($professor['first_name'] . ' ' . $professor['last_name']); ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($professor['email']); ?></td>
                                            <td>
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                            <td>
                                                <a href="?remove=<?php echo $professor['id']; ?>&course=<?php echo $course_id; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Remove this professor from the course?')">
                                                    <i class="fas fa-times"></i> Remove
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

            <!-- All Professors Reference -->
            <?php if (!$course_id): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users"></i> All Professors</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($all_professors)): ?>
                            <div class="text-center py-3">
                                <p class="text-muted">No professors found in the system</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($all_professors as $prof): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-secondary text-white me-3">
                                                    <?php echo strtoupper(substr($prof['first_name'], 0, 1) . substr($prof['last_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']); ?></strong>
                                                    <br>
                                                    <small class="text-secondary"><?php echo htmlspecialchars($prof['email']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
