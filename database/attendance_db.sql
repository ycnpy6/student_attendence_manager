-- Attendance Manager SQL schema
-- Database name used in includes/config.php: attendance_manager
-- Export/import this file via phpMyAdmin or mysql CLI

-- DROP DB and recreate (optional)
CREATE DATABASE IF NOT EXISTS `attendance_manager` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `attendance_manager`;

-- Roles table (student, professor, admin)
CREATE TABLE IF NOT EXISTS `roles` (
  `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Users table: students, professors, admins
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` TINYINT UNSIGNED NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Courses table
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Link professors to courses (many-to-many if needed)
CREATE TABLE IF NOT EXISTS `course_professors` (
  `course_id` INT UNSIGNED NOT NULL,
  `professor_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`course_id`,`professor_id`),
  CONSTRAINT `fk_cp_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cp_prof` FOREIGN KEY (`professor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Students enrolled in courses
CREATE TABLE IF NOT EXISTS `course_students` (
  `course_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`course_id`,`student_id`),
  CONSTRAINT `fk_cs_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cs_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Sessions (a class occurrence for a course)
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `session_date` DATE NOT NULL,
  `start_time` TIME NULL,
  `end_time` TIME NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`course_id`),
  CONSTRAINT `fk_sessions_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Attendance records: mark each student present/absent for a session
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `status` ENUM('present','absent','late','excused') NOT NULL DEFAULT 'present',
  `marked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `marked_by` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_session_student` (`session_id`,`student_id`),
  CONSTRAINT `fk_att_session` FOREIGN KEY (`session_id`) REFERENCES `sessions`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Justifications for absences
CREATE TABLE IF NOT EXISTS `justifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attendance_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `reason` TEXT NOT NULL,
  `evidence_path` VARCHAR(512) NULL,
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` INT UNSIGNED NULL,
  `reviewed_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_just_attendance` FOREIGN KEY (`attendance_id`) REFERENCES `attendance`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_just_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Simple seed data for roles and a demo admin/professor/student (password: 'password')
INSERT IGNORE INTO `roles` (`id`, `name`) VALUES
(1,'admin'), (2,'professor'), (3,'student');

-- Password hashes generated with password_hash('password', PASSWORD_DEFAULT) on PHP 7+ may vary per system.
-- We'll leave password_hash empty here as placeholders; update with real hashes after import.
INSERT IGNORE INTO `users` (`id`,`role_id`,`first_name`,`last_name`,`email`,`password_hash`) VALUES
(1,1,'Admin','User','admin@example.com',''),
(2,2,'Prof','Smith','prof.smith@example.com',''),
(3,3,'Student','Jones','student.jones@example.com','');

-- Example course and enrollment
INSERT IGNORE INTO `courses` (`id`,`code`,`title`) VALUES (1,'CS101','Intro to Computer Science');
INSERT IGNORE INTO `course_professors` (`course_id`,`professor_id`) VALUES (1,2);
INSERT IGNORE INTO `course_students` (`course_id`,`student_id`) VALUES (1,3);

-- Example session and attendance
INSERT IGNORE INTO `sessions` (`id`,`course_id`,`session_date`,`start_time`) VALUES (1,1,'2025-11-23','09:00:00');
INSERT IGNORE INTO `attendance` (`id`,`session_id`,`student_id`,`status`) VALUES (1,1,3,'absent');

-- End of schema
