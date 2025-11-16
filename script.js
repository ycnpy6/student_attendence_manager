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

function updateAttendance() {
  const rows = document.querySelectorAll("#attendanceTable tbody tr");

  rows.forEach(row => {
    const cells = row.querySelectorAll("td");
    const sessions = Array.from(cells).slice(2, 8);
    const participations = Array.from(cells).slice(8, 14);
    const messageCell = row.querySelector(".message");

    const absences = sessions.filter(td => !td.querySelector("input").checked).length;
    const parts = participations.filter(td => td.querySelector("input").checked).length;

    // Clear old classes
    row.classList.remove("green", "yellow", "red");

    // Determine color and message
    if (absences < 3) {
      row.classList.add("green");
      messageCell.textContent = parts >= 3
        ? " Excellent! Great attendance and participation."
        : " Good attendance – try to participate more.";
    } else if (absences <= 4) {
      row.classList.add("yellow");
      messageCell.textContent = " Warning – attendance average, please improve.";
    } else {
      row.classList.add("red");
      messageCell.textContent = " Excluded – too many absences!";
    }
  });
}

// Run once
updateAttendance();

// ======= Toast helper (replace native alerts) =======
function showToast(message, type = 'info', duration = 2000) {
  const container = document.getElementById('toast-container');
  if (!container) {
    // fallback to native alert if container missing
    alert(message);
    return;
  }
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<div class="msg">${message}</div><button class="close-toast" aria-label="close">×</button>`;
  const closeBtn = toast.querySelector('.close-toast');
  closeBtn.addEventListener('click', () => {
    if (toast.parentNode) toast.parentNode.removeChild(toast);
  });
  container.appendChild(toast);
  // auto remove with fade-out animation
  setTimeout(() => {
    // start fade
    toast.classList.add('fade-out');
    // remove after transition
    setTimeout(() => { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 260);
  }, duration);
}

// Recalculate whenever checkbox changes
document.querySelector("#attendanceTable").addEventListener("change", e => {
  if (e.target.type === "checkbox") updateAttendance();
});

// ========= FORM VALIDATION =========
function validateForm() {
  let valid = true;
  const id = studentId.value.trim();
  const ln = lastName.value.trim();
  const fn = firstName.value.trim();
  const em = email.value.trim();
  document.querySelectorAll("span.error").forEach(s => s.textContent = "");

  if (!/^[0-9]+$/.test(id)) { idError.textContent = "ID must be numeric"; valid = false; }
  if (!/^[A-Za-z]+$/.test(ln)) { lnError.textContent = "Last name letters only"; valid = false; }
  if (!/^[A-Za-z]+$/.test(fn)) { fnError.textContent = "First name letters only"; valid = false; }
  if (!/^[^@]+@[^@]+\.[a-z]{2,}$/i.test(em)) { emailError.textContent = "Invalid email"; valid = false; }
  return valid;
}

// ========= ADD STUDENT =========
studentForm.addEventListener("submit", e => {
  e.preventDefault();
  if (!validateForm()) return;

  const tbody = document.querySelector("#attendanceTable tbody");
  const newRow = tbody.insertRow();
  newRow.innerHTML = `
    <td>${lastName.value}</td>
    <td>${firstName.value}</td>
    ${"<td><input type='checkbox'></td>".repeat(6)}
    ${"<td><input type='checkbox'></td>".repeat(6)}
    <td class='message'></td>
  `;
  updateAttendance();
  showToast('✅ Student added successfully!', 'success', 2000);
  studentForm.reset();
});

// ========= REPORT =========
// Modal helpers
function openModal(id){
  const m = document.getElementById(id);
  if(!m) return;
  m.setAttribute('aria-hidden','false');
  document.body.style.overflow = 'hidden';
}
function closeModal(id){
  const m = document.getElementById(id);
  if(!m) return;
  m.setAttribute('aria-hidden','true');
  document.body.style.overflow = '';
}

// wire header buttons
const openAddBtn = document.getElementById('openAddBtn');
const showReportBtn = document.getElementById('showReport');
openAddBtn && openAddBtn.addEventListener('click', ()=> openModal('addModal'));

// Theme toggle: persist preference in localStorage and apply on load
const themeToggle = document.getElementById('themeToggle');
function applyTheme(theme){
  if(theme === 'dark') document.documentElement.setAttribute('data-theme','dark');
  else document.documentElement.removeAttribute('data-theme');
  if(themeToggle) {
    themeToggle.setAttribute('aria-pressed', theme === 'dark');
    themeToggle.textContent = theme === 'dark' ? '☀️ Light' : '🌙 Dark';
  }
}
// initialize from localStorage
const saved = localStorage.getItem('theme');
applyTheme(saved === 'dark' ? 'dark' : 'light');
themeToggle && themeToggle.addEventListener('click', ()=>{
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const next = isDark ? 'light' : 'dark';
  applyTheme(next);
  localStorage.setItem('theme', next);
});

// close buttons / overlay clicks / esc
document.querySelectorAll('.modal .close').forEach(b=> b.addEventListener('click', e=> closeModal(e.target.closest('.modal').id)));
document.querySelectorAll('.modal').forEach(m=> m.addEventListener('click', e=> { if(e.target === m) closeModal(m.id); }));
document.addEventListener('keydown', e=> { if(e.key === 'Escape') document.querySelectorAll('.modal[aria-hidden="false"]').forEach(m=> closeModal(m.id)); });

showReportBtn && showReportBtn.addEventListener("click", () => {
  // open modal first so canvas is available
  openModal('reportModal');
  const rows = document.querySelectorAll("#attendanceTable tbody tr");
  const totalStudents = rows.length;
  let totalPresents = 0;
  let totalParticipations = 0;

  rows.forEach(row => {
    const cells = Array.from(row.cells);
    const sessions = cells.slice(2, 8);   // 6 session cells
    const parts = cells.slice(8, 14);     // 6 participation cells

    // sum checked boxes across the row
    totalPresents += sessions.reduce((sum, td) => {
      const cb = td.querySelector('input[type="checkbox"]');
      return sum + (cb && cb.checked ? 1 : 0);
    }, 0);
    totalParticipations += parts.reduce((sum, td) => {
      const cb = td.querySelector('input[type="checkbox"]');
      return sum + (cb && cb.checked ? 1 : 0);
    }, 0);
  });

  // get canvas context safely
  const ctx = document.getElementById('reportChart').getContext('2d');

  // destroy previous chart if present to avoid overlaying multiple charts
  if (window._attendanceChart) window._attendanceChart.destroy();
  window._attendanceChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ["Total Students", "Total Presents", "Total Participations"],
      datasets: [{
        data: [totalStudents, totalPresents, totalParticipations],
        backgroundColor: ["#007bff","#28a745","#ffc107"]
      }]
    },
    options: { plugins: { legend: { display: false } } }
  });
});

// ========= JQUERY EFFECTS =========
// The code below uses jQuery for concise delegated event handling and small UI effects.
// We keep vanilla JS for data/calculation logic and use jQuery only where it simplifies
// event delegation and animation (hover, click, add/remove classes).
// jQuery: Hover highlight - delegated handlers for row enter/leave
$("#attendanceTable tbody").on("mouseenter", "tr", function() {
  $(this).addClass('row-hover');
}).on("mouseleave", "tr", function() {
  $(this).removeClass('row-hover');
});

// jQuery: Click a row -> show message with First Last and absences (Exercise 5)
$("#attendanceTable tbody").on("click", "tr", function() {
  const last = $(this).find('td').eq(0).text().trim();
  const first = $(this).find('td').eq(1).text().trim();
  const name = (first + ' ' + last).trim();
  const abs = $(this).find("td input[type=checkbox]").slice(0,6).filter((_,el)=>!el.checked).length;
  // use styled toast instead of native alert (2s duration)
  showToast(name + " has " + abs + " absences.", 'info', 2000);
});

// ========= HIGHLIGHT / RESET (Exercise 6) =========
// jQuery: Highlight and Reset buttons - use jQuery to iterate rows and toggle animation classes
$("#highlightBtn").click(() => {
  $("#attendanceTable tbody tr").each(function() {
    const abs = $(this).find("td input[type=checkbox]").slice(0,6)
                .filter((_,el)=>!el.checked).length;
    if (abs < 3) $(this).addClass('animate-highlight');
  });
});

// Reset: remove animation class, remove hover class, and reapply status colors
$("#resetBtn").click(() => {
  $("#attendanceTable tbody tr").removeClass('animate-highlight row-hover');
  updateAttendance();
});