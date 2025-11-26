// Main JavaScript file using jQuery (required by project specs)

$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Confirm delete actions
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Table search filter (basic client-side)
    $('#tableSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#dataTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Mark all checkboxes
    $('#selectAll').on('change', function() {
        $('.student-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // AJAX attendance marking (for faster UX)
    $('.attendance-btn').on('click', function() {
        var btn = $(this);
        var studentId = btn.data('student-id');
        var sessionId = btn.data('session-id');
        var status = btn.data('status');
        
        $.ajax({
            url: 'mark_attendance_ajax.php',
            method: 'POST',
            data: {
                student_id: studentId,
                session_id: sessionId,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    btn.closest('tr').find('.attendance-status').text(status).removeClass().addClass('badge status-' + status);
                    showNotification('Attendance marked as ' + status, 'success');
                } else {
                    showNotification('Error: ' + response.message, 'danger');
                }
            },
            error: function() {
                showNotification('Network error occurred', 'danger');
            }
        });
    });
});

// Show notification using Bootstrap toast
function showNotification(message, type) {
    var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
    
    $('#notification-area').html(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 3000);
}
