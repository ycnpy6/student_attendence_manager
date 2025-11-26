<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('student');

$student_id = get_current_user_id();

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

// Handle justification submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_justification'])) {
    $attendance_id = (int)$_POST['attendance_id'];
    $reason = sanitize_input($_POST['reason']);
    
    // Handle file upload if provided
    $evidence_path = null;
    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/justifications/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION);
        $new_filename = 'justification_' . $attendance_id . '_' . time() . '.' . $file_extension;
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['evidence']['tmp_name'], $target_path)) {
            $evidence_path = 'uploads/justifications/' . $new_filename;
        }
    }
    
    $insert_stmt = mysqli_prepare($conn, "INSERT INTO justifications (attendance_id, student_id, reason, evidence_path) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($insert_stmt, 'iiss', $attendance_id, $student_id, $reason, $evidence_path);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        $success = 'Justification submitted successfully. Waiting for review.';
    } else {
        $error = 'Error submitting justification';
    }
}

// Get attendance records for this student in this course
$query = "SELECT a.*, s.session_date, s.start_time, s.end_time,
                 j.id as justification_id, j.reason, j.status as just_status, j.reviewed_at
          FROM sessions s
          LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
          LEFT JOIN justifications j ON a.id = j.attendance_id
          WHERE s.course_id = ?
          ORDER BY s.session_date DESC, s.start_time DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $student_id, $course_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$attendance_records = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get summary
$summary = get_student_attendance_summary($student_id, $course_id);
$rate = calculate_attendance_percentage($summary['present_count'], $summary['total_sessions']);

$page_title = 'Attendance Details';
include __DIR__ . '/../templates/header.php';
?>

<div id="notification-area"></div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-calendar-check"></i> Attendance Details</h1>
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

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Course: <?php echo htmlspecialchars($course['code']) . ' - ' . htmlspecialchars($course['title']); ?></h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-2">
                <h5><?php echo $summary['total_sessions']; ?></h5>
                <small class="text-muted">Total Sessions</small>
            </div>
            <div class="col-md-2">
                <h5 class="text-success"><?php echo $summary['present_count']; ?></h5>
                <small class="text-muted">Present</small>
            </div>
            <div class="col-md-2">
                <h5 class="text-danger"><?php echo $summary['absent_count']; ?></h5>
                <small class="text-muted">Absent</small>
            </div>
            <div class="col-md-2">
                <h5 class="text-warning"><?php echo $summary['late_count']; ?></h5>
                <small class="text-muted">Late</small>
            </div>
            <div class="col-md-2">
                <h5 class="text-secondary"><?php echo $summary['excused_count']; ?></h5>
                <small class="text-muted">Excused</small>
            </div>
            <div class="col-md-2">
                <h5 class="text-<?php echo $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger'); ?>"><?php echo $rate; ?>%</h5>
                <small class="text-muted">Attendance Rate</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Session History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Justification</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?php echo format_date($record['session_date']); ?></td>
                        <td>
                            <?php 
                            if ($record['start_time'] && $record['end_time']) {
                                echo format_time($record['start_time']) . ' - ' . format_time($record['end_time']);
                            } else {
                                echo 'All day';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($record['status']): ?>
                                <span class="badge status-<?php echo $record['status']; ?>">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Not Marked</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($record['justification_id']): ?>
                                <span class="badge bg-<?php echo $record['just_status'] === 'approved' ? 'success' : ($record['just_status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                    <?php echo ucfirst($record['just_status']); ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($record['status'] === 'absent' && !$record['justification_id']): ?>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#justifyModal<?php echo $record['id']; ?>">
                                    <i class="fas fa-file-alt"></i> Submit Justification
                                </button>
                                
                                <!-- Justification Modal -->
                                <div class="modal fade" id="justifyModal<?php echo $record['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" enctype="multipart/form-data">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Submit Justification</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="attendance_id" value="<?php echo $record['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason for Absence *</label>
                                                        <textarea name="reason" class="form-control" rows="4" required placeholder="Explain why you were absent..."></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Evidence (Optional)</label>
                                                        <input type="file" name="evidence" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                                        <small class="text-muted">Upload medical certificate or other supporting documents</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="submit_justification" class="btn btn-primary">Submit</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($record['justification_id']): ?>
                                <small class="text-muted">
                                    <?php if ($record['reviewed_at']): ?>
                                        Reviewed on <?php echo format_date($record['reviewed_at']); ?>
                                    <?php else: ?>
                                        Under review
                                    <?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
