# Git Push Instructions for Homework Submission

## What to Push to GitHub

✅ **PUSH THESE:**
- All PHP files (except test files)
- Database schema (database/attendance_db.sql)
- Assets (CSS, JS, images)
- Templates (header, footer)
- Configuration example (includes/config.example.php)
- README.md
- .gitignore
- seed_database_new.php (for professor to populate database)

❌ **DO NOT PUSH:**
- includes/config.php (professor will create their own)
- Test files (test_*.php, debug_*.php, check_*.php)
- Uploads folder content
- Log files
- Personal database dumps

## Step-by-Step Git Commands

### 1. First Time Setup (if not already done)
```powershell
cd C:\xampp\htdocs\attendence_manager2.0

# Initialize git (if needed)
git init

# Set your name and email
git config user.name "Your Name"
git config user.email "your.email@example.com"

# Create repository on GitHub first, then:
git remote add origin https://github.com/YOUR_USERNAME/attendance-manager.git
```

### 2. Stage All Important Files
```powershell
# Add all files except those in .gitignore
git add .

# Remove test files from staging (they're in .gitignore now)
git reset HEAD test_login.php
git reset HEAD test_professor.php
git reset HEAD check_students.php
git reset HEAD check_database_status.php
git reset HEAD debug_login.php
git reset HEAD clear_session.php
git reset HEAD quick_login.php
git reset HEAD test_system.php
git reset HEAD check_database.php
git reset HEAD debug_access.php

# Also remove documentation files (optional, or keep them)
git reset HEAD ANALYTICS_FIX.md
git reset HEAD DATABASE_SETUP.md
git reset HEAD FEATURES.md
git reset HEAD FIXES_APPLIED.md
git reset HEAD fix_complete.html
git reset HEAD start_here.html
```

### 3. Commit Your Changes
```powershell
git commit -m "Complete attendance management system for Algerian universities

Features:
- Admin: Course management, professor assignment, analytics dashboard
- Professor: Session creation, attendance marking, justification review
- Student: Attendance view, absence justification with file upload
- 15 CS courses from Algerian curriculum
- Modern Bootstrap 5 interface with Chart.js analytics
- Role-based access control
- MySQL database with sample data seeding"
```

### 4. Push to GitHub
```powershell
# First push (if new repository)
git push -u origin master

# Or if repository exists
git push origin master
```

## What Your Professor Needs to Do

### Setup Instructions for Professor:

1. **Clone the repository**
   ```powershell
   cd C:\xampp\htdocs
   git clone https://github.com/YOUR_USERNAME/attendance-manager.git attendence_manager2.0
   ```

2. **Create config.php**
   ```powershell
   cd attendence_manager2.0\includes
   copy config.example.php config.php
   ```
   (No changes needed if using XAMPP defaults)

3. **Import database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create database: `attendence_manager`
   - Import file: `database/attendance_db.sql`

4. **Seed database with sample data**
   - Open browser: http://localhost/attendence_manager2.0/seed_database_new.php
   - Wait for completion

5. **Login**
   - Go to: http://localhost/attendence_manager2.0/
   - Admin: `admin@university.edu` / `password123`
   - Professor: `yache@professor.university.edu` / `password123`
   - Student: `yacine.adjaout@student.university.edu` / `password123`

## Quick Command Summary

```powershell
# Navigate to project
cd C:\xampp\htdocs\attendence_manager2.0

# Stage all files
git add .

# Commit
git commit -m "Attendance management system - Final submission"

# Push
git push origin master
```

## Verification Checklist

Before pushing, verify:
- [ ] README.md is complete with setup instructions
- [ ] .gitignore excludes test files and config.php
- [ ] config.example.php exists
- [ ] database/attendance_db.sql is included
- [ ] seed_database_new.php is included
- [ ] All admin/professor/student features are present
- [ ] No sensitive data (passwords, personal info) in code

## Alternative: Create ZIP File

If not using GitHub, you can create a ZIP:

```powershell
# Exclude test files and uploads
Compress-Archive -Path `
  admin,professor,student,assets,database,includes,templates,`
  index.php,login.php,logout.php,seed_database_new.php,README.md,`
  .gitignore `
  -DestinationPath attendance_manager_submission.zip
```

Then submit the ZIP file to your professor.
