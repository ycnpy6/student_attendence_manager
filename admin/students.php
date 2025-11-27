<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Admin');

// Handle student deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (delete_user($_GET['delete'])) {
        $success = 'Student deleted successfully';
    } else {
        $error = 'Error deleting student';
    }
}

// Handle CSV import
if (isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK && pathinfo($file['name'], PATHINFO_EXTENSION) === 'csv') {
        $handle = fopen($file['tmp_name'], 'r');
        $headers = fgetcsv($handle); // Skip header row
        $imported = 0;
        $student_role_id = get_role_id('student');
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 4) {
                // CSV format: first_name,last_name,email,password
                $first_name = $data[0];
                $last_name = $data[1];
                $email = $data[2];
                $password = isset($data[3]) && !empty($data[3]) ? $data[3] : 'password123';
                
                if (create_user($first_name, $last_name, $email, $password, $student_role_id)) {
                    $imported++;
                }
            }
        }
        fclose($handle);
        $success = "Imported $imported students successfully";
    } else {
        $error = 'Invalid file format. Please upload a CSV file.';
    }
}

// Handle student addition
if (isset($_POST['add_student'])) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $student_role_id = get_role_id('student');
    
    if (create_user($first_name, $last_name, $email, $password, $student_role_id)) {
        $success = 'Student added successfully';
    } else {
        $error = 'Error adding student. Email may already exist.';
    }
}

$students = get_all_students();
$page_title = 'Student Management';
include __DIR__ . '/../templates/header.php';
?>

<div id="notification-area"></div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-users"></i> Student List Management</h1>
    <div>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<!-- Action buttons -->
<div class="card mb-4">
    <div class="card-body">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fas fa-user-plus"></i> Add Student
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-import"></i> Import CSV
        </button>
        <a href="export_students.php" class="btn btn-info">
            <i class="fas fa-file-export"></i> Export to Excel
        </a>
    </div>
</div>

<!-- Students table -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">All Students (<?php echo count($students); ?>)</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <input type="text" id="tableSearch" class="form-control" placeholder="Search students...">
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover" id="dataTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['id']; ?></td>
                        <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo format_date($student['created_at']); ?></td>
                        <td>
                            <a href="?delete=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import CSV Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Import Students from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>CSV Format:</strong><br>
                        first_name,last_name,email,password<br>
                        <small>First row should contain headers. Password column is optional (default: password123)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="import_csv" class="btn btn-success">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
