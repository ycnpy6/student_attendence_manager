<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Professor');
$professor_id = get_current_user_id();

// Handle justification action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $just_id = (int)$_POST['justification_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    $stmt = mysqli_prepare($conn, "UPDATE justifications SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sii', $status, $professor_id, $just_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Justification ' . $status . ' successfully!';
    }
    
    header('Location: justifications.php');
    exit;
}

// Get justifications from students in my courses
$query = "SELECT DISTINCT j.*, u.first_name, u.last_name, u.email,
          a.status as absence_status, s.session_date, s.start_time,
          c.code, c.title
          FROM justifications j
          JOIN users u ON j.student_id = u.id
          JOIN attendance a ON j.attendance_id = a.id
          JOIN sessions sess ON a.session_id = sess.id
          JOIN courses c ON sess.course_id = c.id
          JOIN course_professors cp ON c.id = cp.course_id
          JOIN sessions s ON a.session_id = s.id
          WHERE cp.professor_id = ?
          ORDER BY j.submitted_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $professor_id);
mysqli_stmt_execute($stmt);
$justifications = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Student Justifications';
include __DIR__ . '/../templates/header.php';
?>

<style>
.container-just { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
.just-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.just-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
}
.badge-status {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}
.badge-pending { background: #fef3c7; color: #ca8a04; }
.badge-approved { background: #dcfce7; color: #16a34a; }
.badge-rejected { background: #fee2e2; color: #dc2626; }
.action-btns {
    display: flex;
    gap: 8px;
    margin-top: 16px;
}
.btn-action {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
}
.btn-approve {
    background: #10b981;
    color: white;
}
.btn-approve:hover { background: #059669; }
.btn-reject {
    background: #ef4444;
    color: white;
}
.btn-reject:hover { background: #dc2626; }
.file-link {
    color: #6366f1;
    text-decoration: none;
    font-weight: 600;
}
.file-link:hover { text-decoration: underline; }
</style>

<div class="container-just">

    <h2 style="margin: 0 0 8px 0; color: #111827;">Student Justifications</h2>
    <p style="margin: 0 0 30px 0; color: #6b7280;">Review and approve/reject absence justifications</p>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="background: #dcfce7; border-left: 4px solid #16a34a; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($justifications)): ?>
        <div style="background: white; border-radius: 12px; padding: 60px 20px; text-align: center;">
            <i class="fas fa-inbox" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
            <h3 style="color: #6b7280;">No Justifications</h3>
            <p style="color: #9ca3af;">No student justifications to review.</p>
        </div>
    <?php else: ?>

        <?php foreach ($justifications as $just): ?>
        <div class="just-card">
            <div class="just-header">
                <div>
                    <strong style="color: #111827; font-size: 16px; display: block; margin-bottom: 4px;">
                        <?php echo htmlspecialchars($just['first_name'] . ' ' . $just['last_name']); ?>
                    </strong>
                    <span style="color: #6b7280; font-size: 14px;">
                        <?php echo htmlspecialchars($just['email']); ?>
                    </span>
                </div>
                <span class="badge-status badge-<?php echo $just['status']; ?>">
                    <?php echo $just['status']; ?>
                </span>
            </div>

            <div style="background: #f9fafb; padding: 12px; border-radius: 6px; margin-bottom: 12px;">
                <strong style="color: #374151; display: block; margin-bottom: 4px;">
                    <?php echo htmlspecialchars($just['code']); ?> - <?php echo htmlspecialchars($just['title']); ?>
                </strong>
                <span style="color: #6b7280; font-size: 13px;">
                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($just['session_date'])); ?>
                    <i class="fas fa-clock" style="margin-left: 12px;"></i> <?php echo date('g:i A', strtotime($just['start_time'])); ?>
                </span>
            </div>

            <div style="margin-bottom: 12px;">
                <strong style="color: #374151; display: block; margin-bottom: 6px; font-size: 14px;">Reason:</strong>
                <p style="color: #4b5563; margin: 0; line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($just['reason'])); ?>
                </p>
            </div>

            <?php if ($just['evidence_path']): ?>
            <div style="margin-bottom: 12px;">
                <a href="../<?php echo htmlspecialchars($just['evidence_path']); ?>" 
                   target="_blank" class="file-link">
                    <i class="fas fa-paperclip"></i> View Attached Document
                </a>
            </div>
            <?php endif; ?>

            <div style="color: #9ca3af; font-size: 12px; margin-bottom: 12px;">
                <i class="fas fa-clock"></i> Submitted <?php echo date('M d, Y g:i A', strtotime($just['submitted_at'])); ?>
            </div>

            <?php if ($just['status'] === 'pending'): ?>
            <div class="action-btns">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="justification_id" value="<?php echo $just['id']; ?>">
                    <button type="submit" name="action" value="approve" class="btn-action btn-approve">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="justification_id" value="<?php echo $just['id']; ?>">
                    <button type="submit" name="action" value="reject" class="btn-action btn-reject">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </form>
            </div>
            <?php elseif ($just['status'] === 'approved'): ?>
                <div style="color: #16a34a; font-weight: 600; font-size: 14px;">
                    <i class="fas fa-check-circle"></i> Approved on <?php echo date('M d, Y', strtotime($just['reviewed_at'])); ?>
                </div>
            <?php else: ?>
                <div style="color: #dc2626; font-weight: 600; font-size: 14px;">
                    <i class="fas fa-times-circle"></i> Rejected on <?php echo date('M d, Y', strtotime($just['reviewed_at'])); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
