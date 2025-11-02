// Button click handlers
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const dataType = this.getAttribute('data-type');
            
            if (dataType === 's') {
                // Session buttons (S1-S6) - toggle present class (green)
                this.classList.toggle('present');
                if (this.classList.contains('present')) {
                    this.textContent = '✓';
                } else {
                    this.textContent = '';
                }
            } else if (dataType === 'p') {
                // Participation buttons (P1-P6) - toggle participate class (blue)
                this.classList.toggle('participate');
                if (this.classList.contains('participate')) {
                    this.textContent = '✓';
                } else {
                    this.textContent = '';
                }
            }
        });
    });
});

function calculateResults() {
    const rows = document.querySelectorAll('#table tbody tr');
    
    rows.forEach(row => {
        // Count absences (S1-S6 buttons without present class)
        const sessionBtns = row.querySelectorAll('button[data-type="s"]');
        let absences = 0;
        sessionBtns.forEach(btn => {
            if (!btn.classList.contains('present')) {
                absences++;
            }
        });
        
        // Count participations (P1-P6 buttons with participate class)
        const partBtns = row.querySelectorAll('button[data-type="p"]');
        let participations = 0;
        partBtns.forEach(btn => {
            if (btn.classList.contains('participate')) {
                participations++;
            }
        });
        
        // Update absences and participations
        const absCell = row.querySelector('.abs-count');
        const partCell = row.querySelector('.part-count');
        absCell.textContent = absences + ' Abs';
        partCell.textContent = participations + ' Par';
        
        // Clear previous row highlighting
        row.classList.remove('row-green', 'row-yellow', 'row-red');
        
        // Highlight row based on absences
        let message = '';
        if (absences < 3) {
            row.classList.add('row-green');
            message = 'Good attendance – Excellent participation';
        } else if (absences >= 3 && absences < 5) {
            row.classList.add('row-yellow');
            message = 'Warning – attendance low – You need to participate more';
        } else {
            row.classList.add('row-red');
            message = 'Excluded – too many absences – You need to participate more';
        }
        
        // Update message
        const msgCell = row.querySelector('.msg');
        msgCell.textContent = message;
    });
}

