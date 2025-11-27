# Attendance Management System 🎓

A comprehensive attendance management system for Algerian universities with separate interfaces for Admins, Professors, and Students.

## 📋 Features

### Admin Features
- **Course Management**: Create, edit, and delete courses
- **Professor Assignment**: Assign professors to courses  
- **Student Enrollment**: Enroll students in courses
- **Analytics Dashboard**: View attendance statistics, trends, and reports with charts
- **User Management**: Manage professors and students

### Professor Features
- **Session Management**: Create class sessions with date and time
- **Attendance Marking**: Mark student attendance (Present/Absent/Late/Excused)
- **Justification Review**: Review and approve/reject student absence justifications

### Student Features
- **My Attendance**: View attendance records for all enrolled courses
- **Justify Absence**: Submit justification requests with optional file upload
- **My Justifications**: Track status of submitted justifications
- **Dashboard**: Overview of attendance statistics

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5, jQuery, Chart.js
- **Server**: Apache (XAMPP)

## 📥 Installation Instructions

### 1. Prerequisites
- XAMPP (or any Apache + MySQL + PHP stack)
- Web browser
- Git (optional)

### 2. Setup Steps

#### Step 1: Get the Code
```bash
cd C:\xampp\htdocs
git clone <your-repo-url> attendence_manager2.0
```
Or extract the ZIP file to `C:\xampp\htdocs\attendence_manager2.0`

#### Step 2: Configure Database Connection
1. Navigate to `includes/` folder
2. Copy `config.example.php` to `config.php`
3. Edit `config.php` if needed (default XAMPP settings work as-is):
   ```php
   DB_HOST: localhost
   DB_USER: root
   DB_PASS: (empty)
   DB_NAME: attendence_manager
   ```

#### Step 3: Create Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database named `attendence_manager`
3. Click "Import" tab
4. Select file: `database/attendance_db.sql`
5. Click "Go" to import

#### Step 4: Seed Database with Sample Data
1. Open browser: http://localhost/attendence_manager2.0/seed_database_new.php
2. Wait for script to complete (creates professors, students, courses, sessions, attendance)
3. You should see success message with summary

#### Step 5: Access the Application
- Open: http://localhost/attendence_manager2.0/
- Login with credentials below

## 🔑 Login Credentials

### Admin
- Email: `admin@university.edu`
- Password: `password123`

### Professors (10 total)
- `yache@professor.university.edu` / `password123`
- `hemili@professor.university.edu` / `password123`
- `benhadid@professor.university.edu` / `password123`
- `zairi@professor.university.edu` / `password123`
- `ghoul@professor.university.edu` / `password123`
- `madi@professor.university.edu` / `password123`
- `abdelalim@professor.university.edu` / `password123`
- `kara@professor.university.edu` / `password123`
- `berkane@professor.university.edu` / `password123`
- `salhi@professor.university.edu` / `password123`

### Students (5 total)
- `yacine.adjaout@student.university.edu` / `password123`
- `houssam.admane@student.university.edu` / `password123`
- `abderrahmane.baaziz@student.university.edu` / `password123`
- `youcef.djelouah@student.university.edu` / `password123`
- `mohamed.bouaboub@student.university.edu` / `password123`

## 📚 Courses (Computer Science Curriculum)

The system includes 15 CS courses from Algerian university curriculum:

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

## 📁 Project Structure

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

## 🎨 Features in Detail

### Attendance Justification System
- Students can submit justification requests for absences
- Optional file upload support (PDF, JPG, PNG, DOC - max 5MB)
- Professors can review and approve/reject justifications
- Status tracking: Pending, Approved, Rejected
- Files stored in `uploads/justifications/`

### Analytics Dashboard (Admin)
- Monthly attendance trends with Chart.js
- Course performance comparison
- Top students by attendance rate
- Low attendance alerts
- Professor activity tracking

### Role-Based Access Control
- **Admin**: Full system access, manage courses/users/analytics
- **Professor**: Manage sessions, mark attendance, review justifications
- **Student**: View attendance, submit justifications

## 🔧 Troubleshooting

### Database Connection Error
- ✅ Ensure XAMPP MySQL is running
- ✅ Check credentials in `includes/config.php`
- ✅ Verify database name is `attendence_manager`

### Login Not Working
- ✅ Run `seed_database_new.php` to populate users
- ✅ Check roles table has capitalized values: `Admin`, `Professor`, `Student`
- ✅ Clear browser cookies/session

### File Upload Issues
- ✅ Ensure `uploads/justifications/` directory exists
- ✅ Check directory permissions (writable)
- ✅ Verify PHP upload settings in `php.ini`

### Page Not Found (404)
- ✅ Ensure project is in `C:\xampp\htdocs\attendence_manager2.0`
- ✅ Check Apache is running in XAMPP
- ✅ Access via `http://localhost/attendence_manager2.0/`

## ⚙️ Configuration

### Database Configuration (`includes/config.php`)
```php
DB_HOST: localhost        # Database host
DB_USER: root            # Database username
DB_PASS:                 # Database password (empty for XAMPP)
DB_NAME: attendence_manager  # Database name
APP_NAME: Attendance Manager # Application name
```

### File Upload Settings
- Allowed types: PDF, JPG, PNG, DOC, DOCX
- Max file size: 5MB
- Upload directory: `uploads/justifications/`

## 🔒 Security Notes

- **Change default passwords in production!**
- Uses prepared statements to prevent SQL injection
- Session-based authentication
- File upload validation (type and size)
- Role-based access control

## 📝 Development Notes

- All role names are **capitalized**: `Admin`, `Professor`, `Student`
- Session management uses PHP `$_SESSION`
- Bootstrap 5 for responsive design
- Clean, minimal styling with purple/indigo theme
- Chart.js for analytics visualization

## 🎯 Usage Guide

### For Professors
1. Login with professor credentials
2. Go to "Sessions" to create a new class session
3. After session, click "Mark Attendance" to record student presence
4. Review student justifications in "Justifications" page

### For Students
1. Login with student credentials
2. View attendance in "My Attendance"
3. Submit absence justification in "Justify Absence"
4. Track justification status in "My Justifications"

### For Admins
1. Login with admin credentials
2. Manage courses in "Courses"
3. Assign professors to courses
4. View system analytics in "Analytics"

## 📄 License

This project is for educational purposes.

## 👨‍💻 Author

Developed for Algerian university attendance management system.
