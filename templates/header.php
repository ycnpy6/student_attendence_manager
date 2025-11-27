<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/attendence_manager2.0/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); box-shadow: var(--shadow-md);">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/attendence_manager2.0/">
                <?php echo APP_NAME; ?>
            </a>
            
            <?php if (is_logged_in()): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto ms-4">
                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/admin/index.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/admin/courses.php">Courses</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/admin/students.php">Students</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/admin/statistics.php">Analytics</a>
                        </li>
                    <?php elseif ($_SESSION['role'] === 'Professor'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/professor/index.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/professor/sessions.php">Sessions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/professor/justifications.php">Justifications</a>
                        </li>
                    <?php elseif ($_SESSION['role'] === 'Student'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/student/index.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/student/my_attendance.php">My Attendance</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/student/justify_absence.php">Justify Absence</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/attendence_manager2.0/student/my_justifications.php">My Justifications</a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link text-white">
                            <?php echo get_current_user_name(); ?> 
                            <span class="badge bg-light text-primary ms-2"><?php echo ucfirst($_SESSION['role']); ?></span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm ms-2" href="/attendence_manager2.0/logout.php">
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    
    <main class="container-fluid px-4 py-4">
