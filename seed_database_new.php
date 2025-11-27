<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Seeding - Algerian CS Courses</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
    </style>
</head>
<body>
<div class='container'>
<h1>Database Seeding - Algerian Computer Science</h1>
<p class='text-muted'>Populating database with Algerian university data...</p>
<hr>";

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Clear existing data
    echo "<h3>Clearing Existing Data...</h3>";
    mysqli_query($conn, "DELETE FROM justifications");
    mysqli_query($conn, "DELETE FROM attendance");
    mysqli_query($conn, "DELETE FROM sessions");
    mysqli_query($conn, "DELETE FROM course_students");
    mysqli_query($conn, "DELETE FROM course_professors");
    mysqli_query($conn, "DELETE FROM courses");
    mysqli_query($conn, "DELETE FROM users WHERE role_id != (SELECT id FROM roles WHERE name = 'Admin')");
    echo "<p class='success'>✓ Database cleared</p>";
    
    // Get role IDs
    $professor_role_id = get_role_id('Professor');
    $student_role_id = get_role_id('Student');
    
    // ============================================
    // 1. CREATE PROFESSORS
    // ============================================
    echo "<h3>1. Creating Professors...</h3>";
    
    $professors = [
        ['Yache', 'Yache'],
        ['Hemili', 'Hemili'],
        ['Benhadid', 'Benhadid'],
        ['Zairi', 'Zairi'],
        ['Ghoul', 'Ghoul'],
        ['Madi', 'Madi'],
        ['Abdelalim', 'Abdelalim'],
        ['Kara', 'Kara'],
        ['Berkane', 'Berkane'],
        ['Salhi', 'Salhi']
    ];
    
    $professor_ids = [];
    foreach ($professors as $prof) {
        $email = strtolower($prof[0]) . '@professor.university.edu';
        $result = create_user($prof[0], $prof[1], $email, 'password123', $professor_role_id);
        if ($result) {
            $professor_ids[] = mysqli_insert_id($conn);
            echo "<p class='success'>✓ Created professor: {$prof[0]} {$prof[1]} ({$email})</p>";
        }
    }
    
    // ============================================
    // 2. CREATE STUDENTS
    // ============================================
    echo "<h3>2. Creating Students...</h3>";
    
    $students = [
        ['Yacine', 'Adjaout'],
        ['Houssam', 'Admane'],
        ['Abderrahmane', 'Baaziz'],
        ['Youcef', 'Djelouah'],
        ['Mohamed', 'Bouaboub']
    ];
    
    $student_ids = [];
    foreach ($students as $student) {
        $email = strtolower($student[0] . '.' . $student[1]) . '@student.university.edu';
        $result = create_user($student[0], $student[1], $email, 'password123', $student_role_id);
        if ($result) {
            $student_ids[] = mysqli_insert_id($conn);
            echo "<p class='success'>✓ Created student: {$student[0]} {$student[1]} ({$email})</p>";
        }
    }
    
    // ============================================
    // 3. CREATE COURSES - Algerian CS Curriculum
    // ============================================
    echo "<h3>3. Creating Computer Science Courses...</h3>";
    
    $courses = [
        ['ASD', 'Algorithmique et Structures de Données', 'Introduction aux algorithmes et structures de données'],
        ['POO', 'Programmation Orientée Objet', 'Java, C++ et principes de POO'],
        ['BD', 'Bases de Données', 'Systèmes de gestion de bases de données relationnelles'],
        ['SE', 'Systèmes d\'Exploitation', 'Architecture et gestion des systèmes'],
        ['RI', 'Réseaux Informatiques', 'Protocoles et architecture réseau'],
        ['GL', 'Génie Logiciel', 'Conception et développement logiciel'],
        ['ARCHI', 'Architecture des Ordinateurs', 'Organisation et fonctionnement des ordinateurs'],
        ['COMP', 'Compilation', 'Théorie des langages et compilation'],
        ['IA', 'Intelligence Artificielle', 'Algorithmes d\'IA et apprentissage automatique'],
        ['SECU', 'Sécurité Informatique', 'Cryptographie et sécurité des systèmes'],
        ['WEB', 'Développement Web', 'Technologies web modernes'],
        ['MDISC', 'Mathématiques Discrètes', 'Logique, graphes et combinatoire'],
        ['ANUM', 'Analyse Numérique', 'Méthodes numériques et calcul scientifique'],
        ['TG', 'Théorie des Graphes', 'Graphes et applications algorithmiques'],
        ['PFE', 'Projet de Fin d\'Études', 'Projet intégrateur de fin de cycle']
    ];
    
    $course_ids = [];
    foreach ($courses as $course) {
        $course_id = create_course($course[0], $course[1], $course[2]);
        if ($course_id) {
            $course_ids[] = $course_id;
            echo "<p class='success'>✓ Created: {$course[0]} - {$course[1]}</p>";
        }
    }
    
    // ============================================
    // 4. ASSIGN PROFESSORS TO COURSES
    // ============================================
    echo "<h3>4. Assigning Professors to Courses...</h3>";
    
    $assignments = 0;
    foreach ($course_ids as $index => $course_id) {
        // Assign 1 professor per course
        $prof_id = $professor_ids[$index % count($professor_ids)];
        if (assign_professor_to_course($prof_id, $course_id)) {
            $assignments++;
        }
    }
    echo "<p class='info'>✓ Created {$assignments} professor-course assignments</p>";
    
    // ============================================
    // 5. ENROLL ALL STUDENTS IN ALL COURSES
    // ============================================
    echo "<h3>5. Enrolling Students in Courses...</h3>";
    
    $enrollments = 0;
    foreach ($student_ids as $student_id) {
        foreach ($course_ids as $course_id) {
            if (enroll_student_in_course($student_id, $course_id)) {
                $enrollments++;
            }
        }
    }
    echo "<p class='info'>✓ Enrolled all {$enrollments} student-course combinations</p>";
    
    // ============================================
    // 6. CREATE SESSIONS (16 sessions per course over 4 months)
    // ============================================
    echo "<h3>6. Creating Class Sessions...</h3>";
    
    $total_sessions = 0;
    $start_date = strtotime('-3 months');
    
    foreach ($course_ids as $course_id) {
        // Create 16 sessions per course (once per week for 4 months)
        for ($week = 0; $week < 16; $week++) {
            $session_date = date('Y-m-d', strtotime("+$week weeks", $start_date));
            $session_time = ['08:00:00', '10:00:00', '13:00:00', '15:00:00'][rand(0, 3)];
            
            $query = "INSERT INTO sessions (course_id, session_date, session_time) 
                     VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'iss', $course_id, $session_date, $session_time);
                
                if (mysqli_stmt_execute($stmt)) {
                    $total_sessions++;
                }
            }
        }
    }
    echo "<p class='info'>✓ Created {$total_sessions} class sessions</p>";
    
    // ============================================
    // 7. CREATE ATTENDANCE RECORDS
    // ============================================
    echo "<h3>7. Generating Attendance Records...</h3>";
    
    $query = "SELECT id FROM sessions";
    $result = mysqli_query($conn, $query);
    $session_ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $session_ids[] = $row['id'];
    }
    
    $total_attendance = 0;
    $statuses = ['present', 'present', 'present', 'present', 'present', 'present', 'present', 'absent', 'late', 'excused'];
    
    foreach ($session_ids as $session_id) {
        foreach ($student_ids as $student_id) {
            $status = $statuses[array_rand($statuses)];
            
            $query = "INSERT INTO attendance (session_id, student_id, status) 
                     VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'iis', $session_id, $student_id, $status);
                
                if (mysqli_stmt_execute($stmt)) {
                    $total_attendance++;
                }
            }
        }
    }
    echo "<p class='info'>✓ Generated {$total_attendance} attendance records</p>";
    
    // ============================================
    // 8. CREATE SOME JUSTIFICATIONS
    // ============================================
    echo "<h3>8. Creating Sample Justifications...</h3>";
    
    $query = "SELECT a.id FROM attendance a WHERE a.status = 'absent' LIMIT 10";
    $result = mysqli_query($conn, $query);
    
    $justifications = 0;
    $reasons = ['Maladie', 'Rendez-vous médical', 'Urgence familiale', 'Transport en panne'];
    $statuses_just = ['pending', 'approved', 'rejected'];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $reason = $reasons[array_rand($reasons)];
        $status_just = $statuses_just[array_rand($statuses_just)];
        
        $query2 = "INSERT INTO justifications (attendance_id, reason, status) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query2);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'iss', $row['id'], $reason, $status_just);
            
            if (mysqli_stmt_execute($stmt)) {
                $justifications++;
            }
        }
    }
    echo "<p class='info'>✓ Created {$justifications} justification requests</p>";
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo "<hr>";
    echo "<div class='alert alert-success'>";
    echo "<h3>✓ Database Seeding Completed Successfully!</h3>";
    echo "<p><strong>Summary:</strong></p>";
    echo "<ul>";
    echo "<li>Professors: " . count($professor_ids) . "</li>";
    echo "<li>Students: " . count($student_ids) . "</li>";
    echo "<li>Courses: " . count($course_ids) . " (CS curriculum)</li>";
    echo "<li>Sessions: {$total_sessions}</li>";
    echo "<li>Attendance Records: {$total_attendance}</li>";
    echo "<li>Justifications: {$justifications}</li>";
    echo "</ul>";
    echo "<p><strong>Login Credentials:</strong></p>";
    echo "<ul>";
    echo "<li>All emails: [firstname]@professor.university.edu or [firstname].[lastname]@student.university.edu</li>";
    echo "<li>Password for all users: <code>password123</code></li>";
    echo "</ul>";
    echo "<p><a href='index.php' class='btn btn-primary'>Go to Login</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "<div class='alert alert-danger'>";
    echo "<h3>✗ Error During Seeding</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>
