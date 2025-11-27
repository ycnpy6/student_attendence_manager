<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('Student');
$student_id = get_current_user_id();

// Get my justifications
$query = "SELECT j.*, a.status as absence_status, s.session_date, s.start_time,
          c.code, c.title
          FROM justifications j
          JOIN attendance a ON j.attendance_id = a.id
          JOIN sessions s ON a.session_id = s.id
          JOIN courses c ON s.course_id = c.id
          WHERE j.student_id = ?
          ORDER BY j.submitted_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$justifications = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

$page_title = 'My Justifications';
include __DIR__ . '/../templates/header.php';
?>

<style>
.container-justs { max-width: 900px; margin: 40px auto; padding: 0 20px; }
.just-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.status-badge {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}
.status-pending { background: #fef3c7; color: #ca8a04; }
.status-approved { background: #dcfce7; color: #16a34a; }
.status-rejected { background: #fee2e2; color: #dc2626; }
</style>

<div class="container-justs">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="margin: 0 0 8px 0; color: #111827;">My Justifications</h2>
            <p style="margin: 0; color: #6b7280;">Track your submitted justifications</p>
        </div>
        <a href="justify_absence.php" style="background: #6366f1; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">
            <i class="fas fa-plus"></i> New Justification
        </a>
    </div>

    <?php if (empty($justifications)): ?>
        <div style="background: white; border-radius: 12px; padding: 60px 20px; text-align: center;">
            <i class="fas fa-file-alt" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
            <h3 style="color: #6b7280;">No Justifications</h3>
            <p style="color: #9ca3af;">You haven't submitted any justifications yet.</p>
        </div>
    <?php else: ?>

        <?php foreach ($justifications as $just): ?>
        <div class="just-item">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                <div>
                    <strong style="color: #111827; font-size: 16px; display: block; margin-bottom: 4px;">
                        <?php echo htmlspecialchars($just['code']); ?> - <?php echo htmlspecialchars($just['title']); ?>
                    </strong>
                    <span style="color: #6b7280; font-size: 14px;">
                        <i class="fas fa-calendar"></i> <?php echo date('F d, Y', strtotime($just['session_date'])); ?>
                        <i class="fas fa-clock" style="margin-left: 12px;"></i> <?php echo date('g:i A', strtotime($just['start_time'])); ?>
                    </span>
                </div>
                <span class="status-badge status-<?php echo $just['status']; ?>">
                    <?php echo $just['status']; ?>
                </span>
            </div>

            <div style="background: #f9fafb; padding: 12px; border-radius: 6px; margin-bottom: 12px;">
                <strong style="color: #374151; font-size: 13px; display: block; margin-bottom: 4px;">Reason:</strong>
                <p style="color: #4b5563; margin: 0; font-size: 14px;">
                    <?php echo nl2br(htmlspecialchars($just['reason'])); ?>
                </p>
            </div>

            <?php if ($just['evidence_path']): ?>
            <div style="margin-bottom: 8px;">
                <a href="../<?php echo htmlspecialchars($just['evidence_path']); ?>" 
                   target="_blank" 
                   style="color: #6366f1; text-decoration: none; font-weight: 600; font-size: 14px;">
                    <i class="fas fa-paperclip"></i> View Document
                </a>
            </div>
            <?php endif; ?>

            <div style="color: #9ca3af; font-size: 12px;">
                <i class="fas fa-clock"></i> Submitted <?php echo date('M d, Y', strtotime($just['submitted_at'])); ?>
                <?php if ($just['reviewed_at']): ?>
                    • Reviewed <?php echo date('M d, Y', strtotime($just['reviewed_at'])); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
