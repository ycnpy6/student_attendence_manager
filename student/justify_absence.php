<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Student');
$student_id = get_current_user_id();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance_id = (int)$_POST['attendance_id'];
    $reason = trim($_POST['reason']);
    $file_path = null;
    
    // Handle file upload
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/justifications/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $filename = $_FILES['document']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['document']['size'] < 5000000) {
            $new_name = 'just_' . $student_id . '_' . time() . '.' . $ext;
            $target = $upload_dir . $new_name;
            
            if (move_uploaded_file($_FILES['document']['tmp_name'], $target)) {
                $file_path = 'uploads/justifications/' . $new_name;
            }
        }
    }
    
    // Insert justification
    $stmt = mysqli_prepare($conn, "INSERT INTO justifications (attendance_id, student_id, reason, evidence_path, status, submitted_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    mysqli_stmt_bind_param($stmt, 'iiss', $attendance_id, $student_id, $reason, $file_path);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Justification submitted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to submit justification.';
    }
    
    header('Location: my_attendance.php');
    exit;
}

// Get absence records
$query = "SELECT a.*, s.session_date, s.start_time, c.code, c.title 
          FROM attendance a 
          JOIN sessions s ON a.session_id = s.id 
          JOIN courses c ON s.course_id = c.id 
          WHERE a.student_id = ? AND a.status = 'absent'
          AND a.id NOT IN (SELECT attendance_id FROM justifications WHERE student_id = ?)
          ORDER BY s.session_date DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $student_id, $student_id);
mysqli_stmt_execute($stmt);
$absences = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Justify Absence';
include __DIR__ . '/../templates/header.php';
?>

<style>
.simple-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.absence-item {
    padding: 16px;
    border-left: 3px solid #ef4444;
    background: #fef2f2;
    border-radius: 6px;
    margin-bottom: 12px;
}
.form-group { margin-bottom: 16px; }
.form-label { 
    display: block; 
    font-weight: 600; 
    margin-bottom: 6px;
    color: #374151;
}
.form-input, .form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}
.form-input:focus, .form-textarea:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
.btn-submit {
    background: #6366f1;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
}
.btn-submit:hover {
    background: #4f46e5;
}
.file-hint {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}
</style>

<div class="container" style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    
    <h2 style="margin-bottom: 8px; color: #111827;">Justify Absence</h2>
    <p style="color: #6b7280; margin-bottom: 30px;">Submit a justification for your absences</p>

    <?php if (empty($absences)): ?>
        <div class="simple-card" style="text-align: center; padding: 60px 20px; color: #6b7280;">
            <i class="fas fa-check-circle" style="font-size: 48px; color: #10b981; margin-bottom: 16px;"></i>
            <h3 style="color: #374151; margin-bottom: 8px;">All Clear!</h3>
            <p>You have no unjustified absences.</p>
            <a href="my_attendance.php" style="color: #6366f1; text-decoration: none; font-weight: 600;">
                <i class="fas fa-arrow-left"></i> Back to Attendance
            </a>
        </div>
    <?php else: ?>
        
        <?php foreach ($absences as $absence): ?>
        <div class="simple-card">
            <div class="absence-item">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                    <div>
                        <strong style="color: #111827; font-size: 16px;">
                            <?php echo htmlspecialchars($absence['code']); ?> - <?php echo htmlspecialchars($absence['title']); ?>
                        </strong>
                    </div>
                    <span style="background: #ef4444; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        ABSENT
                    </span>
                </div>
                <p style="color: #6b7280; margin: 0; font-size: 14px;">
                    <i class="fas fa-calendar"></i> <?php echo date('F d, Y', strtotime($absence['session_date'])); ?>
                    <i class="fas fa-clock" style="margin-left: 12px;"></i> <?php echo date('g:i A', strtotime($absence['start_time'])); ?>
                </p>
            </div>

            <form method="POST" enctype="multipart/form-data" style="margin-top: 16px;">
                <input type="hidden" name="attendance_id" value="<?php echo $absence['id']; ?>">
                
                <div class="form-group">
                    <label class="form-label">Reason for Absence *</label>
                    <textarea name="reason" class="form-textarea" rows="3" required 
                              placeholder="Explain why you were absent..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Document (Optional)</label>
                    <input type="file" name="document" class="form-input" 
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <div class="file-hint">
                        <i class="fas fa-info-circle"></i> Accepted: PDF, JPG, PNG, DOC, DOCX (Max 5MB)
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Justification
                </button>
            </form>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
