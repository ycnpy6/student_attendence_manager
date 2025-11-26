# Attendance Manager 2.0

A complete web-based student attendance management system for Algiers University built with PHP, MySQL, jQuery, and Bootstrap.

## 🎯 Features

### For Administrators
- Dashboard with system statistics
- Student list management (Add/Remove/Import/Export)
- Import students from CSV files
- Export student lists to Excel (Progres format)
- View comprehensive statistics with charts
- Manage users and courses

### For Professors
- View all assigned courses and sessions
- Create new class sessions
- Mark attendance for students (Present/Absent/Late/Excused)
- View attendance summary tables per course
- Track student participation rates

### For Students
- View enrolled courses
- Check attendance status for each course
- View detailed attendance history
- Submit justifications for absences with supporting documents
- Track attendance percentage

## 🛠️ Technologies Used

- **Backend:** PHP 7.4+ with MySQLi
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, Bootstrap 5 (responsive, mobile-first)
- **JavaScript:** jQuery (as required)
- **Charts:** Chart.js for statistics visualization

## 📋 Prerequisites

- XAMPP (or any LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

## 🚀 Installation & Setup

### Step 1: Database Setup

1. Start XAMPP and ensure Apache and MySQL are running
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Create database: `attendence_manager` (or run the SQL file which creates it)
4. Import the database schema:
   - Click on the `attendence_manager` database
   - Go to Import tab
   - Choose `database/attendance_db.sql`
   - Click "Go"

### Step 2: Configure Database Connection

The database is already configured in `includes/config.php`:

```php
DB_HOST: 127.0.0.1
DB_USER: root
DB_PASS: (empty for XAMPP)
DB_NAME: attendence_manager
```

### Step 3: Initialize Data

1. Open in browser: http://localhost/attendence_manager2.0/setup_initial_data.php
2. This will create:
   - User roles (admin, professor, student)
   - Test user accounts
   - Sample courses
   - Course enrollments

### Step 4: Login

Visit: http://localhost/attendence_manager2.0/

**Test Accounts:**
- **Admin:** admin@university.edu / password123
- **Professor:** professor@university.edu / password123
- **Student:** student@university.edu / password123

## 📁 Project Structure

```
attendence_manager2.0/
├── admin/                      # Admin panel pages
│   ├── index.php              # Dashboard
│   ├── students.php           # Student management
│   ├── statistics.php         # Statistics & charts
│   └── export_students.php    # Excel export
├── professor/                  # Professor panel pages
│   ├── index.php              # Dashboard with courses/sessions
│   ├── create_session.php     # Create new session
│   ├── mark_attendance.php    # Mark attendance page
│   └── attendance_summary.php # Course attendance summary
├── student/                    # Student panel pages
│   ├── index.php              # Dashboard with courses
│   └── attendance.php         # View attendance & submit justifications
├── includes/                   # Core PHP files
│   ├── config.php             # Database configuration
│   ├── db_connect.php         # Database connection
│   ├── auth.php               # Authentication functions
│   └── functions.php          # Helper functions
├── templates/                  # Shared templates
│   ├── header.php             # Header with navigation
│   └── footer.php             # Footer
├── assets/                     # Static assets
│   ├── css/
│   │   └── style.css          # Custom styles
│   └── js/
│       └── main.js            # jQuery scripts
├── database/
│   └── attendance_db.sql      # Database schema
├── uploads/                    # File uploads directory
│   └── justifications/        # Absence justification documents
├── index.php                   # Entry point
├── login.php                   # Login page
├── logout.php                  # Logout handler
├── test_db.php                # Database connection test
└── setup_initial_data.php     # Initial data setup
```

## 💡 Usage Guide

### Admin Workflow
1. Login as admin
2. Navigate to "Manage Students"
3. Add students manually or import from CSV
4. Export student lists to Excel
5. View system statistics and charts

### Professor Workflow
1. Login as professor
2. View assigned courses
3. Create a new session for a course
4. Mark attendance for each student
5. View attendance summary tables

### Student Workflow
1. Login as student
2. View enrolled courses and attendance rates
3. Check detailed attendance history
4. Submit justifications for absences with documents

## 📊 Database Schema

The system uses 9 main tables:
- `roles` - User roles (admin, professor, student)
- `users` - All system users
- `courses` - Course information
- `course_professors` - Professor-course assignments
- `course_students` - Student enrollments
- `sessions` - Class sessions
- `attendance` - Attendance records
- `justifications` - Absence justifications

## 🔒 Security Features

- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- Role-based access control
- Session management
- Input sanitization
- CSRF protection ready

## 📱 Mobile Responsive

The system is built with a mobile-first approach using Bootstrap 5, ensuring it works perfectly on:
- Smartphones
- Tablets
- Desktop computers

## 🎨 Customization

### Change Database Name
Edit `includes/config.php` and update `DB_NAME`

### Add More Roles
Insert into `roles` table and update authentication logic in `includes/auth.php`

### Modify Attendance Statuses
Edit the ENUM values in `attendance` table and update display logic

## 🐛 Troubleshooting

**Database Connection Error:**
- Verify MySQL is running in XAMPP
- Check credentials in `includes/config.php`
- Ensure database exists

**Login Issues:**
- Run `setup_initial_data.php` to create users
- Clear browser cache and cookies
- Check session configuration in php.ini

**File Upload Issues:**
- Check `uploads/` directory has write permissions
- Verify `upload_max_filesize` in php.ini

## 📝 Future Enhancements

- Email notifications for absences
- QR code attendance marking
- Mobile app integration
- Advanced reporting (PDF generation)
- Multi-language support
- Academic calendar integration

## 👥 Credits

Developed for Algiers University as part of the Attendance Management System project.

## 📄 License

This project is developed for educational purposes.

---

**Need Help?** Check the code comments or create an issue in the project repository.
