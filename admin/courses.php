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

// Handle course creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $code = sanitize_input($_POST['code']);
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    
    if (create_course($code, $title, $description)) {
        $success = 'Course created successfully!';
    } else {
        $error = 'Failed to create course';
    }
}

// Handle course update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $course_id = (int)$_POST['course_id'];
    $code = sanitize_input($_POST['code']);
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    
    if (update_course($course_id, $code, $title, $description)) {
        $success = 'Course updated successfully!';
    } else {
        $error = 'Failed to update course';
    }
}

// Handle course deletion
if (isset($_GET['delete'])) {
    $course_id = (int)$_GET['delete'];
    if (delete_course($course_id)) {
        $success = 'Course deleted successfully!';
    } else {
        $error = 'Failed to delete course';
    }
}

// Get all courses
$courses = get_all_courses();

$page_title = 'Course Management';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Course Management</h1>
                    <p class="page-subtitle">Manage all courses and their enrollments</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                    <i class="fas fa-plus"></i> Add New Course
                </button>
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

    <!-- Courses Grid -->
    <div class="row">
        <?php if (empty($courses)): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No courses found</h5>
                        <p class="text-secondary">Create your first course to get started</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 fade-in">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($course['code']); ?></span>
                                </h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="course_details.php?id=<?php echo $course['id']; ?>">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="assign_professor.php?course_id=<?php echo $course['id']; ?>">
                                                <i class="fas fa-user-tie"></i> Assign Professor
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="?delete=<?php echo $course['id']; ?>" onclick="return confirm('Are you sure you want to delete this course?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h6 class="mb-3"><?php echo htmlspecialchars($course['title']); ?></h6>
                            <p class="text-secondary small mb-3"><?php echo htmlspecialchars($course['description'] ?: 'No description provided'); ?></p>
                            
                            <div class="row g-2 mt-auto">
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded text-center">
                                        <div class="text-primary fw-bold"><?php echo $course['student_count']; ?></div>
                                        <small class="text-secondary">Students</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-light rounded text-center">
                                        <div class="text-success fw-bold"><?php echo $course['professor_count']; ?></div>
                                        <small class="text-secondary">Professors</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="course_details.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                                View Enrolled Students <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create Course Modal -->
<div class="modal fade" id="createCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="code" name="code" required placeholder="e.g., CS101">
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="e.g., Introduction to Programming">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Course description (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="course_id" id="edit_course_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="edit_code" name="code" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCourse(course) {
    document.getElementById('edit_course_id').value = course.id;
    document.getElementById('edit_code').value = course.code;
    document.getElementById('edit_title').value = course.title;
    document.getElementById('edit_description').value = course.description || '';
    
    const modal = new bootstrap.Modal(document.getElementById('editCourseModal'));
    modal.show();
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
