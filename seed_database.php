<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Seeding</title>
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
<h1><i class='fas fa-database'></i> Database Seeding Script</h1>
<p class='text-muted'>Populating database with realistic sample data...</p>
<hr>";

// Start transaction
mysqli_begin_transaction($conn);

try {
    // ============================================
    // 1. CREATE PROFESSORS (10 professors)
    // ============================================
    echo "<h3>1. Creating Professors...</h3>";
    
    $professors = [
        ['John', 'Smith', 'john.smith@university.edu'],
        ['Sarah', 'Johnson', 'sarah.johnson@university.edu'],
        ['Michael', 'Williams', 'michael.williams@university.edu'],
        ['Emily', 'Brown', 'emily.brown@university.edu'],
        ['David', 'Jones', 'david.jones@university.edu'],
        ['Lisa', 'Garcia', 'lisa.garcia@university.edu'],
        ['Robert', 'Martinez', 'robert.martinez@university.edu'],
        ['Jennifer', 'Rodriguez', 'jennifer.rodriguez@university.edu'],
        ['William', 'Davis', 'william.davis@university.edu'],
        ['Maria', 'Lopez', 'maria.lopez@university.edu']
    ];
    
    $professor_role_id = get_role_id('Professor');
    $professor_ids = [];
    
    foreach ($professors as $prof) {
        $result = create_user($prof[0], $prof[1], $prof[2], 'password123', $professor_role_id);
        if ($result) {
            $professor_ids[] = mysqli_insert_id($conn);
            echo "<p class='success'>✓ Created professor: {$prof[0]} {$prof[1]} ({$prof[2]})</p>";
        }
    }
    
    // ============================================
    // 2. CREATE STUDENTS (50 students)
    // ============================================
    echo "<h3>2. Creating Students...</h3>";
    
    $first_names = ['Ahmed', 'Fatima', 'Mohamed', 'Amina', 'Karim', 'Yasmine', 'Omar', 'Leila', 'Rachid', 'Samira',
                    'Mehdi', 'Nadia', 'Amine', 'Sofia', 'Youssef', 'Lina', 'Hamza', 'Rania', 'Bilal', 'Meriem',
                    'Sofiane', 'Asma', 'Walid', 'Kenza', 'Rayan', 'Salma', 'Ayoub', 'Imane', 'Samir', 'Aicha',
                    'Nassim', 'Chaima', 'Farid', 'Nesrine', 'Tayeb', 'Houda', 'Adel', 'Hanane', 'Nabil', 'Karima',
                    'Ilyas', 'Djamila', 'Mourad', 'Soraya', 'Cherif', 'Nabila', 'Hicham', 'Zahra', 'Bachir', 'Lynda'];
    
    $last_names = ['Benali', 'Boumediene', 'Hamidi', 'Kaddour', 'Mansouri', 'Ouardi', 'Rachedi', 'Saidi', 'Taleb', 'Ziani',
                   'Amrani', 'Benzema', 'Cherif', 'Djebar', 'Fertani', 'Ghanem', 'Hadji', 'Idris', 'Khalifa', 'Larbi',
                   'Mahdi', 'Nasri', 'Osman', 'Rahmani', 'Slimani', 'Toumi', 'Wahab', 'Yahia', 'Zaidi', 'Atmani',
                   'Bouzid', 'Chaoui', 'Derradji', 'Fellah', 'Guerroudj', 'Hamdani', 'Kacem', 'Mehenni', 'Nouri', 'Ould',
                   'Rezki', 'Smati', 'Tadjer', 'Zerhouni', 'Amar', 'Brahim', 'Charef', 'Djamel', 'Farouk', 'Ghazi'];
    
    $student_role_id = get_role_id('Student');
    $student_ids = [];
    
    for ($i = 0; $i < 50; $i++) {
        $first_name = $first_names[$i];
        $last_name = $last_names[$i];
        $email = strtolower($first_name . '.' . $last_name . '@student.university.edu');
        
        $result = create_user($first_name, $last_name, $email, 'password123', $student_role_id);
        if ($result) {
            $student_ids[] = mysqli_insert_id($conn);
            if ($i < 5 || $i % 10 == 0) {
                echo "<p class='success'>✓ Created student: {$first_name} {$last_name} ({$email})</p>";
            }
        }
    }
    echo "<p class='info'>... Created " . count($student_ids) . " students total</p>";
    
    // ============================================
    // 3. CREATE COURSES (15 courses)
    // ============================================
    echo "<h3>3. Creating Courses...</h3>";
    
    $courses = [
        ['CS101', 'Introduction to Programming', 'Learn the basics of programming using Python'],
        ['CS102', 'Data Structures', 'Study of fundamental data structures and algorithms'],
        ['CS201', 'Database Systems', 'Design and implementation of database systems'],
        ['CS202', 'Web Development', 'Modern web development with HTML, CSS, JavaScript'],
        ['CS301', 'Software Engineering', 'Principles and practices of software development'],
        ['MATH101', 'Calculus I', 'Differential and integral calculus'],
        ['MATH102', 'Linear Algebra', 'Vectors, matrices, and linear transformations'],
        ['PHYS101', 'Physics I', 'Mechanics and thermodynamics'],
        ['ENG101', 'English Composition', 'Academic writing and composition'],
        ['BUS101', 'Introduction to Business', 'Fundamentals of business management'],
        ['CS303', 'Artificial Intelligence', 'Introduction to AI and machine learning'],
        ['CS304', 'Computer Networks', 'Network protocols and architectures'],
        ['CS401', 'Mobile Development', 'iOS and Android app development'],
        ['CS402', 'Cloud Computing', 'Cloud platforms and distributed systems'],
        ['CS403', 'Cybersecurity', 'Security principles and cryptography']
    ];
    
    $course_ids = [];
    foreach ($courses as $course) {
        $course_id = create_course($course[0], $course[1], $course[2]);
        if ($course_id) {
            $course_ids[] = $course_id;
            echo "<p class='success'>✓ Created course: {$course[0]} - {$course[1]}</p>";
        }
    }
    
    // ============================================
    // 4. ASSIGN PROFESSORS TO COURSES
    // ============================================
    echo "<h3>4. Assigning Professors to Courses...</h3>";
    
    $assignments = 0;
    foreach ($course_ids as $index => $course_id) {
        // Assign 1-2 professors per course
        $num_profs = rand(1, 2);
        $assigned_profs = array_rand(array_flip($professor_ids), $num_profs);
        if (!is_array($assigned_profs)) {
            $assigned_profs = [$assigned_profs];
        }
        
        foreach ($assigned_profs as $prof_id) {
            if (assign_professor_to_course($prof_id, $course_id)) {
                $assignments++;
            }
        }
    }
    echo "<p class='info'>✓ Created {$assignments} professor-course assignments</p>";
    
    // ============================================
    // 5. ENROLL STUDENTS IN COURSES
    // ============================================
    echo "<h3>5. Enrolling Students in Courses...</h3>";
    
    $enrollments = 0;
    foreach ($student_ids as $student_id) {
        // Each student enrolls in 3-6 courses
        $num_courses = rand(3, 6);
        $enrolled_courses = array_rand(array_flip($course_ids), $num_courses);
        if (!is_array($enrolled_courses)) {
            $enrolled_courses = [$enrolled_courses];
        }
        
        foreach ($enrolled_courses as $course_id) {
            if (enroll_student_in_course($student_id, $course_id)) {
                $enrollments++;
            }
        }
    }
    echo "<p class='info'>✓ Created {$enrollments} student enrollments</p>";
    
    // ============================================
    // 6. CREATE SESSIONS (past 2 months)
    // ============================================
    echo "<h3>6. Creating Class Sessions...</h3>";
    
    $sessions_created = 0;
    $start_date = strtotime('-60 days');
    $end_date = strtotime('today');
    
    foreach ($course_ids as $course_id) {
        // Get a professor for this course
        $profs = get_course_professors($course_id);
        if (empty($profs)) continue;
        $prof_id = $profs[0]['id'];
        
        // Create 2 sessions per week for 8 weeks (16 sessions per course)
        for ($week = 0; $week < 8; $week++) {
            for ($day = 0; $day < 2; $day++) {
                $session_date = date('Y-m-d', strtotime("+{$week} weeks +{$day} days", $start_date));
                
                // Skip future dates
                if (strtotime($session_date) > $end_date) continue;
                
                $start_time = ($day == 0) ? '09:00:00' : '14:00:00';
                $end_time = ($day == 0) ? '11:00:00' : '16:00:00';
                
                $stmt = mysqli_prepare($conn, "INSERT INTO sessions (course_id, session_date, start_time, end_time, created_by) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'isssi', $course_id, $session_date, $start_time, $end_time, $prof_id);
                if (mysqli_stmt_execute($stmt)) {
                    $session_id = mysqli_insert_id($conn);
                    $sessions_created++;
                    
                    // Mark attendance for this session
                    $enrolled_students = get_course_students($course_id);
                    foreach ($enrolled_students as $student) {
                        // Random attendance: 70% present, 15% absent, 10% late, 5% excused
                        $rand = rand(1, 100);
                        if ($rand <= 70) {
                            $status = 'present';
                        } elseif ($rand <= 85) {
                            $status = 'absent';
                        } elseif ($rand <= 95) {
                            $status = 'late';
                        } else {
                            $status = 'excused';
                        }
                        
                        $att_stmt = mysqli_prepare($conn, "INSERT INTO attendance (session_id, student_id, status, marked_by) VALUES (?, ?, ?, ?)");
                        mysqli_stmt_bind_param($att_stmt, 'iisi', $session_id, $student['id'], $status, $prof_id);
                        mysqli_stmt_execute($att_stmt);
                    }
                }
            }
        }
    }
    echo "<p class='info'>✓ Created {$sessions_created} class sessions with attendance records</p>";
    
    // ============================================
    // 7. CREATE SOME JUSTIFICATIONS
    // ============================================
    echo "<h3>7. Creating Absence Justifications...</h3>";
    
    // Get some absent attendance records
    $absent_query = "SELECT a.id, a.student_id FROM attendance a WHERE a.status = 'absent' ORDER BY RAND() LIMIT 20";
    $absent_result = mysqli_query($conn, $absent_query);
    
    $justifications_created = 0;
    $reasons = [
        'Medical appointment',
        'Family emergency',
        'Illness - doctor\'s note attached',
        'University event participation',
        'Transportation issues',
        'Technical difficulties'
    ];
    
    while ($row = mysqli_fetch_assoc($absent_result)) {
        $reason = $reasons[array_rand($reasons)];
        $status_rand = rand(1, 100);
        $status = ($status_rand <= 50) ? 'approved' : (($status_rand <= 80) ? 'pending' : 'rejected');
        
        $just_stmt = mysqli_prepare($conn, "INSERT INTO justifications (attendance_id, student_id, reason, status, submitted_at) VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($just_stmt, 'iiss', $row['id'], $row['student_id'], $reason, $status);
        if (mysqli_stmt_execute($just_stmt)) {
            $justifications_created++;
        }
    }
    echo "<p class='info'>✓ Created {$justifications_created} justification requests</p>";
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo "<hr>
    <div class='alert alert-success'>
        <h4>✓ Database Seeding Completed Successfully!</h4>
        <p><strong>Summary:</strong></p>
        <ul>
            <li>" . count($professor_ids) . " Professors created</li>
            <li>" . count($student_ids) . " Students created</li>
            <li>" . count($course_ids) . " Courses created</li>
            <li>{$assignments} Professor assignments</li>
            <li>{$enrollments} Student enrollments</li>
            <li>{$sessions_created} Class sessions with attendance</li>
            <li>{$justifications_created} Justification requests</li>
        </ul>
        <p><strong>Login Credentials:</strong></p>
        <ul>
            <li><strong>Admin:</strong> admin@university.edu / password123</li>
            <li><strong>Any Professor:</strong> [firstname].[lastname]@university.edu / password123</li>
            <li><strong>Any Student:</strong> [firstname].[lastname]@student.university.edu / password123</li>
        </ul>
        <a href='admin/index.php' class='btn btn-primary'>Go to Admin Dashboard</a>
        <a href='login.php' class='btn btn-secondary'>Go to Login</a>
    </div>";
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "<div class='alert alert-danger'>
        <h4>✗ Error during database seeding</h4>
        <p>{$e->getMessage()}</p>
    </div>";
}

echo "</div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js'></script>
</body>
</html>";

mysqli_close($conn);
?>
