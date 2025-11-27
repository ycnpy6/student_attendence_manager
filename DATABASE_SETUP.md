# Database Setup & Sample Data

## 🎯 Quick Start

### 1. Seed the Database
Visit: `http://localhost/attendence_manager2.0/seed_database.php`

This will populate your database with:
- **10 Professors** with realistic names and emails
- **50 Students** with university email addresses
- **15 Courses** across CS, Math, Physics, Business departments
- **Professor-Course Assignments** (1-2 professors per course)
- **Student Enrollments** (each student in 3-6 courses)
- **Class Sessions** (16 sessions per course over 8 weeks)
- **Attendance Records** with realistic distribution:
  - 70% Present
  - 15% Absent
  - 10% Late
  - 5% Excused
- **Justification Requests** for some absences

### 2. Login Credentials

#### Admin Access
- **Email:** `admin@university.edu`
- **Password:** `password123`

#### Professor Access (Any of these)
- `john.smith@university.edu` / `password123`
- `sarah.johnson@university.edu` / `password123`
- `michael.williams@university.edu` / `password123`
- `emily.brown@university.edu` / `password123`
- `david.jones@university.edu` / `password123`
- `lisa.garcia@university.edu` / `password123`
- `robert.martinez@university.edu` / `password123`
- `jennifer.rodriguez@university.edu` / `password123`
- `william.davis@university.edu` / `password123`
- `maria.lopez@university.edu` / `password123`

#### Student Access (Sample)
- `ahmed.benali@student.university.edu` / `password123`
- `fatima.boumediene@student.university.edu` / `password123`
- `mohamed.hamidi@student.university.edu` / `password123`
- `amina.kaddour@student.university.edu` / `password123`
- ... (50 students total, format: firstname.lastname@student.university.edu)

## 📊 Sample Data Overview

### Courses Created
1. **CS101** - Introduction to Programming
2. **CS102** - Data Structures
3. **CS201** - Database Systems
4. **CS202** - Web Development
5. **CS301** - Software Engineering
6. **CS303** - Artificial Intelligence
7. **CS304** - Computer Networks
8. **CS401** - Mobile Development
9. **CS402** - Cloud Computing
10. **CS403** - Cybersecurity
11. **MATH101** - Calculus I
12. **MATH102** - Linear Algebra
13. **PHYS101** - Physics I
14. **ENG101** - English Composition
15. **BUS101** - Introduction to Business

### Students Created (50 total)
All students have realistic North African names with university email addresses:
- Ahmed Benali, Fatima Boumediene, Mohamed Hamidi, etc.
- Email format: `firstname.lastname@student.university.edu`
- Each enrolled in 3-6 random courses
- Each has ~16 attendance records per course

### Attendance Data
- **Time Period:** Last 60 days
- **Sessions:** 2 per week per course (8 weeks)
- **Total Sessions:** ~240 sessions across all courses
- **Attendance Records:** ~3,000+ individual attendance marks
- **Realistic Distribution:**
  - Most students have good attendance (70% present)
  - Some absences (15%)
  - Occasional tardiness (10%)
  - Few excused absences (5%)

## 🔧 Features Available After Seeding

### Admin Dashboard
- View overall system statistics
- See total students, professors, courses
- Overall presence rate calculation
- Quick links to all management pages

### Course Management
- View all 15 courses in card layout
- See enrollment and professor counts
- Create new courses
- Edit course details
- Delete courses (with cascade)
- Assign professors to courses
- View course details with student lists

### Analytics & Reports
- **Monthly Trends:** 6-month attendance trend chart
- **Status Distribution:** Pie chart showing present/absent/late/excused
- **Course Performance:** Table with attendance rates per course
- **Top Students:** Leaderboard of best-performing students
- **Students Needing Attention:** Low attendance alerts (<70%)
- **Professor Activity:** Sessions held and courses taught

### Professor Dashboard
- View assigned courses
- Create new sessions
- Mark attendance for students
- View course attendance reports
- Review justifications

### Student Dashboard
- View enrolled courses
- See personal attendance records
- Submit absence justifications
- Track attendance percentage per course

## 🎨 Modern UI Features

### Design System
- **Color Palette:** Purple/Indigo (#6366f1)
- **Typography:** Inter font family
- **Components:** Modern cards, gradients, shadows
- **Responsive:** Mobile-friendly layouts
- **Animations:** Smooth transitions and hover effects

### Navigation
- Role-based menus
- Avatar circles with user initials
- Gradient navbar
- Breadcrumbs on detail pages

## 📈 Analytics Capabilities

After seeding, you can:
1. **View Trends:** See how attendance changes over months
2. **Compare Courses:** Identify which courses have better attendance
3. **Monitor Students:** Track top performers and those struggling
4. **Evaluate Professors:** See teaching activity and student engagement
5. **Generate Reports:** Print-friendly analytics pages

## 🚀 Next Steps

1. Run `seed_database.php` to populate data
2. Login as admin to explore all features
3. Navigate to **Analytics** to see charts and reports
4. Visit **Courses** to manage course assignments
5. Test professor login to mark attendance
6. Test student login to view records

## 🔄 Re-seeding

To start fresh:
1. Clear the database (or run the SQL setup again)
2. Re-run `seed_database.php`
3. All sample data will be recreated with new random distributions

---

**All passwords are:** `password123`

**Important:** This is sample data for testing. In production, use secure passwords and real user data!
