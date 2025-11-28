# Attendance Management System

A web-based attendance management system designed for Algerian universities. The system provides separate interfaces for administrators, professors, and students to manage and track class attendance efficiently.

## Features

### Administrator Features
- Manage courses (create, edit, delete)
- Assign professors to courses
- Enroll students in courses
- View comprehensive analytics and attendance reports with interactive charts
- Manage system users

### Professor Features
- Create class sessions with specific dates and times
- Mark student attendance (Present, Absent, Late, Excused)
- Review and respond to student absence justifications

### Student Features
- View personal attendance records for all enrolled courses
- Submit justification requests for absences with optional file attachments
- Track the status of submitted justifications
- Access attendance statistics and summaries

## Technology Stack

- Backend: PHP 7.4 or higher
- Database: MySQL 5.7 or higher
- Frontend: Bootstrap 5, jQuery, Chart.js
- Server: Apache (included in XAMPP)

## Installation Guide

### Requirements
- XAMPP (or similar Apache + MySQL + PHP environment)
- Modern web browser
- Git (optional, for cloning)

### Installation Steps

**Step 1: Download the Project**

Clone the repository or download and extract the ZIP file:
```bash
cd C:\xampp\htdocs
git clone https://github.com/ycnpy6/student_attendence_manager.git attendence_manager2.0
```

**Step 2: Configure Database Connection**

1. Navigate to the `includes` folder
2. Copy `config.example.php` and rename it to `config.php`
3. Open `config.php` and verify the database settings (default XAMPP settings should work without changes):
   - DB_HOST: localhost
   - DB_USER: root
   - DB_PASS: (leave empty)
   - DB_NAME: attendence_manager

**Step 3: Create the Database**

1. Start XAMPP and ensure MySQL is running
2. Open phpMyAdmin by visiting http://localhost/phpmyadmin
3. Create a new database named `attendence_manager`
4. Click the Import tab
5. Choose the file `database/attendance_db.sql` from the project folder
6. Click Go to import the database structure

**Step 4: Populate the Database**

1. Open your web browser
2. Visit http://localhost/attendence_manager2.0/seed_database_new.php
3. Wait for the script to complete (this creates sample professors, students, courses, and attendance records)
4. You should see a success message when finished

**Step 5: Access the System**

Open http://localhost/attendence_manager2.0/ in your browser and login using the credentials below.

## Login Credentials

After running the seed script, you can login with these accounts:

**Administrator**
- Email: admin@university.edu
- Password: password123

**Professors** (10 accounts available)
- yache@professor.university.edu / password123
- hemili@professor.university.edu / password123
- benhadid@professor.university.edu / password123
- zairi@professor.university.edu / password123
- ghoul@professor.university.edu / password123
- madi@professor.university.edu / password123
- abdelalim@professor.university.edu / password123
- kara@professor.university.edu / password123
- berkane@professor.university.edu / password123
- salhi@professor.university.edu / password123

**Students** (5 accounts available)
- yacine.adjaout@student.university.edu / password123
- houssam.admane@student.university.edu / password123
- abderrahmane.baaziz@student.university.edu / password123
- youcef.djelouah@student.university.edu / password123
- mohamed.bouaboub@student.university.edu / password123

## Available Courses

The system includes 15 Computer Science courses based on the Algerian university curriculum:

| Code | Course Name |
|------|-------------|
| ASD | Algorithmique et Structures de Données |
| POO | Programmation Orientée Objet |
| BD | Bases de Données |
| SE | Systèmes d'Exploitation |
| RI | Réseaux Informatiques |
| GL | Génie Logiciel |
| ARCHI | Architecture des Ordinateurs |
| COMP | Compilation |
| IA | Intelligence Artificielle |
| SECU | Sécurité Informatique |
| WEB | Développement Web |
| MDISC | Mathématiques Discrètes |
| ANUM | Analyse Numérique |
| TG | Théorie des Graphes |
| PFE | Projet de Fin d'Études |

## Project Structure

```
attendence_manager2.0/
├── admin/                  # Admin dashboard and features
│   ├── index.php          # Admin dashboard
│   ├── courses.php        # Course management
│   ├── assign_professor.php
│   ├── statistics.php     # Analytics dashboard
│   └── students.php
├── professor/             # Professor features
│   ├── index.php         # Professor dashboard
│   ├── sessions.php      # Create sessions
│   ├── mark_attendance.php
│   └── justifications.php
├── student/              # Student features
│   ├── index.php        # Student dashboard
│   ├── my_attendance.php
│   ├── justify_absence.php
│   └── my_justifications.php
├── assets/
│   ├── css/style.css    # Modern minimal styling
│   └── js/main.js
├── database/
│   └── attendance_db.sql # Database schema
├── includes/
│   ├── config.php       # Database config (create from example)
│   ├── db_connect.php   # Database connection
│   ├── auth.php         # Authentication
│   └── functions.php    # Helper functions
├── templates/
│   ├── header.php       # Common header
│   └── footer.php       # Common footer
├── uploads/
│   └── justifications/  # Student uploaded files
├── index.php            # Landing page
├── login.php            # Login page
├── logout.php           # Logout handler
└── seed_database_new.php # Database seeding script
```

## Key Features Explained

**Attendance Justification System**
Students can submit justification requests when they miss a class. They can optionally attach supporting documents (PDF, JPG, PNG, DOC files up to 5MB). Professors can then review these requests and either approve or reject them. The system tracks three status types: Pending, Approved, and Rejected.

**Analytics Dashboard**
The administrator dashboard includes visual charts showing monthly attendance trends, course performance comparisons, and lists of top-performing students. It also highlights students with low attendance rates and tracks professor activity across the system.

**Role-Based Access**
The system uses three user roles with different permissions:
- Administrator: Full access to all system features including user management and analytics
- Professor: Can create sessions, mark attendance, and review student justifications
- Student: Can view their attendance records and submit absence justifications

## Troubleshooting Common Issues

**Database Connection Error**
- Make sure MySQL is running in XAMPP
- Check that the credentials in `includes/config.php` are correct
- Verify the database name is `attendence_manager`

**Login Problems**
- Run the `seed_database_new.php` script to create user accounts
- Ensure the roles in the database are capitalized: Admin, Professor, Student
- Try clearing your browser cookies and cache

**File Upload Issues**
- Check that the `uploads/justifications/` folder exists
- Ensure the folder has write permissions
- Verify PHP upload settings in your php.ini file

**Page Not Found Errors**
- Confirm the project is located in `C:\xampp\htdocs\attendence_manager2.0`
- Make sure Apache is running in XAMPP
- Access the site using `http://localhost/attendence_manager2.0/`

## Important Notes

**Security Reminder**
The default password for all accounts is "password123". This is fine for development and testing, but you should change these passwords if you deploy this system in a real environment.

**Database Settings**
The system is configured to work with default XAMPP settings:
- Database Host: localhost
- Database User: root
- Database Password: (empty)
- Database Name: attendence_manager

**File Uploads**
Students can upload the following file types as evidence for absence justifications:
- PDF documents
- Image files (JPG, PNG)
- Word documents (DOC, DOCX)
- Maximum file size: 5MB

**User Roles**
All role names in the database must be capitalized (Admin, Professor, Student) for the authentication system to work properly.

## Usage Guide

**For Professors:**
1. Login using your professor account
2. Navigate to "Sessions" to create a new class session
3. After the class, click "Mark Attendance" to record which students were present
4. Check "Justifications" to review and respond to student absence requests

**For Students:**
1. Login using your student account
2. Visit "My Attendance" to see your attendance records across all courses
3. If you missed a class, use "Justify Absence" to submit an explanation
4. Track your justification requests in "My Justifications"

**For Administrators:**
1. Login using the admin account
2. Use "Courses" to manage the course catalog
3. Assign professors to their respective courses
4. Review system-wide statistics in the "Analytics" section

## License and Credits

This project was developed as an educational project for managing attendance in Algerian universities.

## Author

Developed for academic purposes.
