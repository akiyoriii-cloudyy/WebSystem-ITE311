-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 02:16 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lms_mansueto`
--

-- --------------------------------------------------------

--
-- Table structure for table `acad_years`
--

CREATE TABLE `acad_years` (
  `id` int(11) UNSIGNED NOT NULL,
  `acad_year` varchar(20) NOT NULL COMMENT 'Academic Year (e.g., 2024-2025)',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = Active, 0 = Inactive',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `acad_years`
--

INSERT INTO `acad_years` (`id`, `acad_year`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(3, '2025-2026', '2025-12-10', '2025-12-10', 1, '2025-12-10 08:53:11', '2025-12-10 08:53:11');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_by`, `created_at`) VALUES
(1, 'Welcome to the Laboratory Management System', 'All students and teachers are reminded to regularly check the dashboard for updates, announcements, and course-related information.', 1, '2025-11-20 05:50:11'),
(2, 'Laboratory Safety Guidelines', 'Please follow all laboratory safety protocols. Safety goggles and proper attire are mandatory for all lab sessions.', 1, '2025-11-20 05:50:11'),
(3, 'Upcoming Laboratory Schedule', 'The laboratory schedule for the next month has been published. Check your assigned courses and session timings to avoid conflicts.', 1, '2025-11-20 05:50:11'),
(4, 'Laboratory Assessment Reminder', 'All students are reminded that laboratory assessments and submissions are due according to the schedule. Late submissions will not be accepted.', 1, '2025-11-20 05:50:11');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL COMMENT 'Foreign key to courses',
  `grading_period_id` int(11) UNSIGNED NOT NULL COMMENT 'Foreign key to grading_periods',
  `assignment_type` varchar(50) NOT NULL COMMENT 'Assignment type (e.g., Quiz, Exam, Project, Lab, Homework)',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `max_score` decimal(10,2) NOT NULL DEFAULT 100.00 COMMENT 'Maximum possible score',
  `due_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `course_id`, `grading_period_id`, `assignment_type`, `title`, `description`, `max_score`, `due_date`, `created_at`, `updated_at`) VALUES
(9, 8, 1, 'Exam', 'Kakakroach', 'Identification and Multiple Choice', 100.00, '2025-12-11 10:44:00', '2025-12-11 00:44:27', '2025-12-11 00:44:27');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) UNSIGNED NOT NULL,
  `course_number` varchar(50) DEFAULT NULL COMMENT 'CN - Course Number/Code (e.g., CS101, IT311)',
  `acad_year_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to acad_years',
  `semester_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to semesters',
  `term_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to terms',
  `department_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to departments',
  `program_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to programs',
  `units` int(11) UNSIGNED DEFAULT NULL COMMENT 'Number of units/credits for the course',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `instructor_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_number`, `acad_year_id`, `semester_id`, `term_id`, `department_id`, `program_id`, `units`, `title`, `description`, `instructor_id`, `created_at`, `updated_at`) VALUES
(8, 'ITE 321', 3, 2, 2, NULL, NULL, 8, 'Web Application Development', '	Focuses on designing and building dynamic websites using modern frameworks.\r\n', 2, '2025-12-10 08:59:32', '2025-12-10 23:13:38'),
(9, 'ITE 322', 3, 2, 2, 7, 14, 9, 'Database Systems and Analytics', 'Explores data modeling, SQL queries, and database management principles.', 2, '2025-12-11 00:21:45', '2025-12-11 00:21:45'),
(10, 'ITE 323', 3, 2, 2, 7, 14, 7, 'Software Design and Development', 'Covers software lifecycle, project planning, and agile methodologies.', 2, '2025-12-11 00:23:04', '2025-12-11 00:23:04'),
(11, 'ITE311', 3, 4, NULL, 7, 15, 3, '\'x\'or\'1\'', 'ASDDFHGJKHL', 2, '2025-12-12 09:51:18', '2025-12-12 09:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `course_schedules`
--

CREATE TABLE `course_schedules` (
  `id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL COMMENT 'Foreign key to courses',
  `class_type` enum('online','face_to_face') NOT NULL DEFAULT 'face_to_face' COMMENT 'Type of class: online or face_to_face',
  `day_of_week` varchar(20) NOT NULL COMMENT 'Day of week (e.g., Monday, Tuesday)',
  `start_time` time NOT NULL COMMENT 'Class start time',
  `end_time` time NOT NULL COMMENT 'Class end time',
  `room` varchar(50) DEFAULT NULL COMMENT 'Room number or location',
  `meeting_link` varchar(500) DEFAULT NULL COMMENT 'Meeting link for online classes (Zoom, Google Meet, etc.)',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `course_schedules`
--

INSERT INTO `course_schedules` (`id`, `course_id`, `class_type`, `day_of_week`, `start_time`, `end_time`, `room`, `meeting_link`, `created_at`, `updated_at`) VALUES
(3, 9, 'face_to_face', 'Thursday', '14:00:00', '16:00:00', 'GE201', NULL, '2025-12-11 05:02:54', '2025-12-11 07:16:35'),
(4, 8, 'face_to_face', 'Thursday', '15:00:00', '17:00:00', 'FM101', NULL, '2025-12-11 05:04:41', '2025-12-11 05:04:41');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) UNSIGNED NOT NULL,
  `department_code` varchar(20) NOT NULL COMMENT 'Department code (e.g., CS, IT, ENG)',
  `department_name` varchar(255) NOT NULL COMMENT 'Full department name',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_code`, `department_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(5, 'CAS', 'College of Arts & Sciences', 'Offers BA in Communication, Psychology, Social Work, BS in Biology, Environmental Science, Math, etc.', 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(6, 'CBE', 'College of Business Education', 'Focuses on business-related courses', 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(7, 'CET', 'College of Engineering & Technology', 'Provides engineering and tech programs', 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(8, 'CCJ', 'College of Criminal Justice', 'For aspiring criminal justice professionals', 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(9, 'CTE', 'College of Teacher Education', 'For future educators', 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(10, 'COAHS', 'College of Allied Health Sciences', 'Includes Midwifery, Pharmacy, etc., aiming for CHED compliance', 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(11, 'SHS', 'Senior High School', 'Offers secondary education', 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(12, 'GSP', 'Graduate School', 'For advanced studies', 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `enrolled_at` datetime DEFAULT NULL,
  `enrollment_date` datetime DEFAULT NULL COMMENT 'Alternate name for enrolled_at',
  `completion_status` enum('ENROLLED','IN_PROGRESS','COMPLETED','FAILED','DROPPED') DEFAULT 'ENROLLED' COMMENT 'Student course completion status',
  `completed_at` datetime DEFAULT NULL COMMENT 'Date when course was completed',
  `final_grade` decimal(5,2) DEFAULT NULL COMMENT 'Final grade for the course'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `enrolled_at`, `enrollment_date`, `completion_status`, `completed_at`, `final_grade`) VALUES
(29, 11, 9, '2025-12-12 00:59:03', '2025-12-12 00:59:03', 'ENROLLED', NULL, NULL),
(31, 3, 10, '2025-12-12 00:59:15', '2025-12-12 00:59:15', 'ENROLLED', NULL, NULL),
(32, 3, 8, '2025-12-12 02:42:07', '2025-12-12 02:42:07', 'ENROLLED', NULL, NULL),
(33, 3, 9, '2025-12-12 02:42:17', '2025-12-12 02:42:17', 'ENROLLED', NULL, NULL),
(34, 11, 8, '2025-12-12 02:42:31', '2025-12-12 02:42:31', 'ENROLLED', NULL, NULL),
(35, 11, 10, '2025-12-12 02:42:53', '2025-12-12 02:42:53', 'ENROLLED', NULL, NULL),
(36, 13, 10, '2025-12-12 02:52:26', '2025-12-12 02:52:26', 'ENROLLED', NULL, NULL),
(37, 13, 8, '2025-12-12 02:52:58', '2025-12-12 02:52:58', 'ENROLLED', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) UNSIGNED NOT NULL,
  `enrollment_id` int(11) UNSIGNED NOT NULL COMMENT 'Foreign key to enrollments (student-course relationship)',
  `assignment_id` int(11) UNSIGNED NOT NULL COMMENT 'Foreign key to assignments',
  `score` decimal(10,2) DEFAULT NULL COMMENT 'Student score for this assignment',
  `percentage` decimal(5,2) DEFAULT NULL COMMENT 'Percentage score (calculated)',
  `remarks` varchar(50) DEFAULT NULL COMMENT 'Remarks (e.g., Passed, Failed)',
  `graded_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'User ID of teacher who graded',
  `graded_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grading_periods`
--

CREATE TABLE `grading_periods` (
  `id` int(11) UNSIGNED NOT NULL,
  `term_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to terms (for term-based grading)',
  `semester_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to semesters (for semester-based grading)',
  `period_name` varchar(100) NOT NULL COMMENT 'Grading period name',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `grading_periods`
--

INSERT INTO `grading_periods` (`id`, `term_id`, `semester_id`, `period_name`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'Default Period', '2025-12-10', '2026-03-10', 1, '2025-12-10 07:37:36', '2025-12-10 07:37:36');

-- --------------------------------------------------------

--
-- Table structure for table `grading_weights`
--

CREATE TABLE `grading_weights` (
  `id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL COMMENT 'Foreign key to courses',
  `term_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to terms (for term-based weights)',
  `assignment_type` varchar(50) NOT NULL COMMENT 'Assignment type (e.g., Quiz, Exam, Project)',
  `weight_percentage` decimal(5,2) NOT NULL COMMENT 'Weight percentage for this assignment type (e.g., 30.00 for 30%)',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'Material status: active or deleted',
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `course_id`, `file_name`, `file_path`, `status`, `created_at`) VALUES
(14, 10, 'MidtermExamination.pdf', 'uploads/materials/1765508651_30e2033b43b163b60b64.pdf', 'active', '2025-12-12 03:04:11'),
(15, 9, 'MidtermExamination.pdf', 'uploads/materials/1765508669_a365f0e4f57eb90504d4.pdf', 'active', '2025-12-12 03:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '20250912025006', 'App\\Database\\Migrations\\CreateUsersTable', 'default', 'App', 1763617589, 1),
(2, '20250912025008', 'App\\Database\\Migrations\\CreateCoursesTable', 'default', 'App', 1763617589, 1),
(3, '20250912025010', 'App\\Database\\Migrations\\CreateEnrollmentsTable', 'default', 'App', 1763617589, 1),
(4, '20250912025012', 'App\\Database\\Migrations\\CreateLessonsTable', 'default', 'App', 1763617589, 1),
(5, '20250912025014', 'App\\Database\\Migrations\\CreateQuizzesTable', 'default', 'App', 1763617589, 1),
(6, '20250912025016', 'App\\Database\\Migrations\\CreateSubmissionsTable', 'default', 'App', 1763617589, 1),
(7, '20251017071711', 'App\\Database\\Migrations\\CreateAnnouncementsTable', 'default', 'App', 1763617589, 1),
(8, '20251023095619', 'App\\Database\\Migrations\\CreateMaterialsTable', 'default', 'App', 1763617589, 1),
(9, '20251101124507', 'App\\Database\\Migrations\\CreateNotificationsTable', 'default', 'App', 1763617589, 1),
(10, '20251210023311', 'App\\Database\\Migrations\\CreateAcadYearsTable', 'default', 'App', 1765335314, 2),
(11, '20251210023330', 'App\\Database\\Migrations\\CreateSemestersTable', 'default', 'App', 1765335314, 2),
(12, '20251210023338', 'App\\Database\\Migrations\\CreateTermsTable', 'default', 'App', 1765335314, 2),
(13, '20251210023345', 'App\\Database\\Migrations\\CreateDepartmentsTable', 'default', 'App', 1765335314, 2),
(14, '20251210023352', 'App\\Database\\Migrations\\CreateProgramsTable', 'default', 'App', 1765335314, 2),
(15, '20251210023403', 'App\\Database\\Migrations\\CreateCourseSchedulesTable', 'default', 'App', 1765335314, 2),
(16, '20251210023416', 'App\\Database\\Migrations\\CreateGradingPeriodsTable', 'default', 'App', 1765335314, 2),
(17, '20251210023424', 'App\\Database\\Migrations\\CreateAssignmentsTable', 'default', 'App', 1765335314, 2),
(18, '20251210023435', 'App\\Database\\Migrations\\CreateGradesTable', 'default', 'App', 1765335314, 2),
(19, '20251210023441', 'App\\Database\\Migrations\\CreateGradingWeightsTable', 'default', 'App', 1765335314, 2),
(20, '20251210023447', 'App\\Database\\Migrations\\CreateOtpTokensTable', 'default', 'App', 1765335465, 3),
(21, '20251210023454', 'App\\Database\\Migrations\\AddAcademicFieldsToCourses', 'default', 'App', 1765335465, 3),
(22, '20251210023502', 'App\\Database\\Migrations\\AddStudentFieldsToUsers', 'default', 'App', 1765335465, 3),
(23, '20251210023508', 'App\\Database\\Migrations\\AddCompletionStatusToEnrollments', 'default', 'App', 1765335465, 3),
(24, '20251210041209', 'App\\Database\\Migrations\\AddCourseIdToQuizzes', 'default', 'App', 1765339997, 4),
(25, '20251210041216', 'App\\Database\\Migrations\\AddScoreFieldsToSubmissions', 'default', 'App', 1765339997, 4),
(26, '20251210042211', 'App\\Database\\Migrations\\LinkQuizzesToAssignments', 'default', 'App', 1765340555, 5),
(27, '20251210071759', 'App\\Database\\Migrations\\AddClassTypeToCourseSchedules', 'default', 'App', 1765351168, 6),
(28, '20251210074628', 'App\\Database\\Migrations\\MakeLessonIdNullableInQuizzes', 'default', 'App', 1765352843, 7),
(29, '20251210084914', 'App\\Database\\Migrations\\AddUnitsToCourses', 'default', 'App', 1765356584, 8),
(30, '20251210094019', 'App\\Database\\Migrations\\AddDepartmentAndProgramToCourses', 'default', 'App', 1765359668, 9),
(31, '20251220000000', 'App\\Database\\Migrations\\AddStatusToMaterials', 'default', 'App', 1765402096, 10);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(22, 1, 'You have successfully updated course \'Web Application Development\'.', 1, '2025-12-10 23:13:38'),
(23, 2, 'You have been assigned as instructor for \'Web Application Development\'!', 0, '2025-12-10 23:33:12'),
(24, 1, 'You have successfully assigned \'Teacher User\' as instructor for \'Web Application Development\'.', 1, '2025-12-10 23:33:12'),
(25, 3, 'You have been successfully enrolled in Web Application Development!', 1, '2025-12-10 23:34:54'),
(26, 3, 'You have been successfully enrolled in Web Application Development!', 1, '2025-12-11 00:05:01'),
(27, 3, 'You have been successfully enrolled in \'Web Application Development\'!', 1, '2025-12-11 00:10:56'),
(28, 2, 'You have successfully enrolled \'Student User\' in \'Web Application Development\'.', 0, '2025-12-11 00:10:56'),
(29, 2, 'You have successfully deleted quiz \'QuizBee\' and its associated assignment from \'Web Application Development\'.', 0, '2025-12-11 00:18:36'),
(30, 1, 'Course \'Database Systems and Analytics\' has been created successfully!', 1, '2025-12-11 00:21:45'),
(31, 1, 'Course \'Software Design and Development\' has been created successfully!', 1, '2025-12-11 00:23:04'),
(32, 3, 'You have been successfully enrolled in \'Web Application Development\'!', 0, '2025-12-11 00:34:04'),
(33, 2, 'You have successfully enrolled \'Student User\' in \'Web Application Development\'.', 0, '2025-12-11 00:34:04'),
(34, 2, 'You have successfully uploaded material \'MidtermExamination.pdf\' for \'Web Application Development\'.', 0, '2025-12-11 00:38:15'),
(35, 3, 'New material \'MidtermExamination.pdf\' has been uploaded for Web Application Development!', 0, '2025-12-11 00:38:15'),
(36, 2, 'You have successfully restored material \'MidtermExamination.pdf\' for \'Web Application Development\'.', 0, '2025-12-11 00:38:21'),
(37, 3, 'New assignment \'Kakakroach\' has been posted for Web Application Development!', 0, '2025-12-11 00:44:27'),
(38, 2, 'You have successfully created assignment \'Kakakroach\' for \'Web Application Development\'.', 0, '2025-12-11 00:44:27'),
(39, 2, 'You have been assigned as instructor for \'Software Design and Development\'!', 0, '2025-12-11 04:42:17'),
(40, 1, 'You have successfully assigned \'Teacher User\' as instructor for \'Software Design and Development\'.', 1, '2025-12-11 04:42:17'),
(41, 1, 'You have successfully created a Face to face schedule for \'Database Systems and Analytics\'.', 0, '2025-12-11 05:02:54'),
(42, 1, 'You have successfully created a Face to face schedule for \'Web Application Development\'.', 0, '2025-12-11 05:04:41'),
(43, 2, 'You have successfully removed \'Teacher User\' from \'Software Design and Development\'.', 0, '2025-12-11 05:07:19'),
(44, 3, 'You have been successfully enrolled in \'Software Design and Development\'!', 0, '2025-12-11 07:12:31'),
(45, 2, 'You have successfully enrolled \'Student User\' in \'Software Design and Development\'.', 0, '2025-12-11 07:12:31'),
(46, 3, 'You have been successfully enrolled in \'Database Systems and Analytics\'!', 0, '2025-12-11 08:19:01'),
(47, 2, 'You have successfully enrolled \'Student User\' in \'Database Systems and Analytics\'.', 0, '2025-12-11 08:19:01'),
(48, 12, 'Welcome! Your account has been created. You can now log in to the system.', 0, '2025-12-11 08:19:46'),
(49, 11, 'You have been successfully enrolled in Database Systems and Analytics!', 0, '2025-12-12 00:59:03'),
(50, 1, 'You have successfully enrolled Student \'mybelleee\' in \'Database Systems and Analytics\'.', 0, '2025-12-12 00:59:03'),
(51, 2, 'You have been assigned as instructor for \'Database Systems and Analytics\'!', 0, '2025-12-12 00:59:09'),
(52, 1, 'You have successfully assigned \'Teacher User\' as instructor for \'Database Systems and Analytics\'.', 0, '2025-12-12 00:59:09'),
(53, 3, 'You have been successfully enrolled in Software Design and Development!', 0, '2025-12-12 00:59:15'),
(54, 1, 'You have successfully enrolled Student \'Student User\' in \'Software Design and Development\'.', 0, '2025-12-12 00:59:15'),
(55, 3, 'You have been successfully enrolled in Web Application Development!', 0, '2025-12-12 02:42:07'),
(56, 1, 'You have successfully enrolled Student \'Student User\' in \'Web Application Development\'.', 0, '2025-12-12 02:42:07'),
(57, 3, 'You have been successfully enrolled in Database Systems and Analytics!', 0, '2025-12-12 02:42:17'),
(58, 1, 'You have successfully enrolled Student \'Student User\' in \'Database Systems and Analytics\'.', 0, '2025-12-12 02:42:17'),
(59, 11, 'You have been successfully enrolled in Web Application Development!', 0, '2025-12-12 02:42:31'),
(60, 1, 'You have successfully enrolled Student \'mybelleee\' in \'Web Application Development\'.', 0, '2025-12-12 02:42:31'),
(61, 11, 'You have been successfully enrolled in Software Design and Development!', 0, '2025-12-12 02:42:53'),
(62, 1, 'You have successfully enrolled Student \'mybelleee\' in \'Software Design and Development\'.', 0, '2025-12-12 02:42:53'),
(63, 1, 'You have successfully uploaded material \'MidtermExamination.pdf\' for \'Web Application Development\'.', 0, '2025-12-12 02:45:23'),
(64, 3, 'New material \'MidtermExamination.pdf\' has been uploaded for Web Application Development!', 0, '2025-12-12 02:45:23'),
(65, 11, 'New material \'MidtermExamination.pdf\' has been uploaded for Web Application Development!', 0, '2025-12-12 02:45:23'),
(66, 1, 'You have successfully restored material \'MidtermExamination.pdf\' for \'Web Application Development\'.', 0, '2025-12-12 02:46:52'),
(67, 13, 'Welcome! Your account has been created. You can now log in to the system.', 0, '2025-12-12 02:49:53'),
(68, 13, 'You have been successfully enrolled in \'Software Design and Development\'!', 0, '2025-12-12 02:52:26'),
(69, 2, 'You have successfully enrolled \'akoarakabelle\' in \'Software Design and Development\'.', 0, '2025-12-12 02:52:26'),
(70, 13, 'You have been successfully enrolled in \'Web Application Development\'!', 0, '2025-12-12 02:52:58'),
(71, 2, 'You have successfully enrolled \'akoarakabelle\' in \'Web Application Development\'.', 0, '2025-12-12 02:52:58'),
(72, 1, 'You have successfully uploaded material \'MidtermExamination.pdf\' for \'Software Design and Development\'.', 0, '2025-12-12 02:58:13'),
(73, 3, 'New material \'MidtermExamination.pdf\' has been uploaded for Software Design and Development!', 0, '2025-12-12 02:58:13'),
(74, 11, 'New material \'MidtermExamination.pdf\' has been uploaded for Software Design and Development!', 0, '2025-12-12 02:58:13'),
(75, 13, 'New material \'MidtermExamination.pdf\' has been uploaded for Software Design and Development!', 0, '2025-12-12 02:58:13'),
(76, 1, 'You have successfully uploaded material \'MidtermExamination.pdf\' for \'Web Application Development\'.', 0, '2025-12-12 03:01:14'),
(77, 3, 'New material \'MidtermExamination.pdf\' has been uploaded for Web Application Development!', 0, '2025-12-12 03:01:14'),
(78, 11, 'New material \'MidtermExamination.pdf\' has been uploaded for Web Application Development!', 0, '2025-12-12 03:01:14'),
(79, 13, 'New material \'MidtermExamination.pdf\' has been uploaded for Web Application Development!', 0, '2025-12-12 03:01:14'),
(80, 1, 'You have successfully uploaded material \'MidtermExamination.pdf\' for \'Software Design and Development\'.', 0, '2025-12-12 03:04:11'),
(81, 3, 'New material \'MidtermExamination.pdf\' has been uploaded for Software Design and Development!', 0, '2025-12-12 03:04:11'),
(82, 11, 'New material \'MidtermExamination.pdf\' has been uploaded for Software Design and Development!', 0, '2025-12-12 03:04:11'),
(83, 13, 'New material \'MidtermExamination.pdf\' has been uploaded for Software Design and Development!', 0, '2025-12-12 03:04:11'),
(84, 1, 'You have successfully uploaded material \'MidtermExamination.pdf\' for \'Database Systems and Analytics\'.', 0, '2025-12-12 03:04:29'),
(85, 11, 'New material \'MidtermExamination.pdf\' has been uploaded for Database Systems and Analytics!', 0, '2025-12-12 03:04:29'),
(86, 3, 'New material \'MidtermExamination.pdf\' has been uploaded for Database Systems and Analytics!', 0, '2025-12-12 03:04:29'),
(87, 1, 'You have successfully created Semester \'2nd\'.', 0, '2025-12-12 08:01:06'),
(88, 1, 'You have successfully created Semester \'Summer\'.', 0, '2025-12-12 08:02:57'),
(89, 1, 'You have successfully created Term \'Midterm\'.', 0, '2025-12-12 08:03:37'),
(90, 1, 'You have successfully created Term \'Final\'.', 0, '2025-12-12 08:04:22'),
(91, 1, 'Course \'\'x\'or\'1\'\' has been created successfully!', 0, '2025-12-12 09:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `otp_tokens`
--

CREATE TABLE `otp_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT 'Foreign key to users',
  `otp_code` varchar(6) NOT NULL COMMENT '6-digit OTP code',
  `email` varchar(255) NOT NULL COMMENT 'Email address where OTP was sent',
  `expires_at` datetime NOT NULL COMMENT 'OTP expiration time (typically 5-10 minutes)',
  `is_used` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = Used, 0 = Not used',
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `otp_tokens`
--

INSERT INTO `otp_tokens` (`id`, `user_id`, `otp_code`, `email`, `expires_at`, `is_used`, `created_at`) VALUES
(1, 1, '597597', 'admin@example.com', '2025-12-10 03:17:57', 1, '2025-12-10 03:07:57'),
(2, 2, '481508', 'teacher@example.com', '2025-12-10 03:19:04', 1, '2025-12-10 03:09:04'),
(3, 1, '696248', 'admin@example.com', '2025-12-10 03:19:46', 1, '2025-12-10 03:09:46'),
(4, 2, '889126', 'teacher@example.com', '2025-12-10 03:33:00', 1, '2025-12-10 03:23:00'),
(5, 3, '005221', 'student@example.com', '2025-12-10 03:36:29', 1, '2025-12-10 03:26:29'),
(6, 1, '498042', 'admin@example.com', '2025-12-10 03:37:05', 1, '2025-12-10 03:27:05'),
(7, 2, '482817', 'teacher@example.com', '2025-12-10 03:48:21', 1, '2025-12-10 03:38:21'),
(8, 3, '989736', 'student@example.com', '2025-12-10 03:48:50', 1, '2025-12-10 03:38:50'),
(9, 1, '937683', 'admin@example.com', '2025-12-10 03:49:04', 1, '2025-12-10 03:39:04'),
(10, 2, '173759', 'teacher@example.com', '2025-12-10 04:17:16', 1, '2025-12-10 04:07:16'),
(11, 1, '567082', 'admin@example.com', '2025-12-10 04:26:48', 1, '2025-12-10 04:16:49'),
(12, 2, '040529', 'teacher@example.com', '2025-12-10 04:27:35', 1, '2025-12-10 04:17:35'),
(13, 1, '523378', 'admin@example.com', '2025-12-10 04:36:17', 1, '2025-12-10 04:26:17'),
(14, 2, '686633', 'teacher@example.com', '2025-12-10 04:36:57', 1, '2025-12-10 04:26:57'),
(15, 2, '799951', 'teacher@example.com', '2025-12-10 07:15:56', 1, '2025-12-10 07:05:56'),
(16, 1, '089009', 'admin@example.com', '2025-12-10 07:20:25', 1, '2025-12-10 07:10:25'),
(17, 2, '096325', 'teacher@example.com', '2025-12-10 07:25:44', 1, '2025-12-10 07:15:44'),
(18, 1, '183778', 'admin@example.com', '2025-12-10 07:35:08', 1, '2025-12-10 07:25:08'),
(19, 2, '046724', 'teacher@example.com', '2025-12-10 07:37:04', 1, '2025-12-10 07:27:04'),
(20, 1, '326423', 'admin@example.com', '2025-12-10 07:38:21', 1, '2025-12-10 07:28:21'),
(21, 2, '611286', 'teacher@example.com', '2025-12-10 07:42:39', 1, '2025-12-10 07:32:39'),
(22, 3, '133410', 'student@example.com', '2025-12-10 07:58:36', 1, '2025-12-10 07:48:36'),
(23, 3, '618532', 'student@example.com', '2025-12-10 08:40:06', 1, '2025-12-10 08:30:07'),
(24, 1, '516175', 'admin@example.com', '2025-12-10 08:55:39', 1, '2025-12-10 08:45:39'),
(25, 3, '506968', 'student@example.com', '2025-12-10 09:11:09', 1, '2025-12-10 09:01:09'),
(26, 2, '623718', 'teacher@example.com', '2025-12-10 09:22:09', 1, '2025-12-10 09:12:09'),
(27, 1, '051490', 'admin@example.com', '2025-12-10 09:27:50', 1, '2025-12-10 09:17:50'),
(28, 3, '748779', 'student@example.com', '2025-12-10 09:36:01', 1, '2025-12-10 09:26:01'),
(29, 1, '507158', 'admin@example.com', '2025-12-10 09:40:57', 1, '2025-12-10 09:30:57'),
(30, 1, '970267', 'admin@example.com', '2025-12-10 16:04:02', 1, '2025-12-10 15:54:02'),
(31, 2, '156528', 'teacher@example.com', '2025-12-10 16:16:16', 1, '2025-12-10 16:06:16'),
(32, 3, '939667', 'student@example.com', '2025-12-10 16:16:59', 1, '2025-12-10 16:06:59'),
(33, 1, '411747', 'admin@example.com', '2025-12-10 16:24:27', 1, '2025-12-10 16:14:27'),
(34, 1, '002229', 'admin@example.com', '2025-12-10 18:36:30', 1, '2025-12-10 18:26:30'),
(35, 1, '053592', 'admin@example.com', '2025-12-10 20:39:57', 1, '2025-12-10 20:29:57'),
(36, 3, '027893', 'student@example.com', '2025-12-10 20:52:40', 1, '2025-12-10 20:42:40'),
(37, 1, '109834', 'admin@example.com', '2025-12-10 20:52:56', 1, '2025-12-10 20:42:56'),
(38, 2, '872137', 'teacher@example.com', '2025-12-10 20:55:06', 1, '2025-12-10 20:45:06'),
(39, 1, '736511', 'admin@example.com', '2025-12-10 20:55:26', 1, '2025-12-10 20:45:26'),
(40, 2, '109442', 'teacher@example.com', '2025-12-10 21:04:41', 1, '2025-12-10 20:54:41'),
(41, 1, '573965', 'admin@example.com', '2025-12-10 21:25:11', 1, '2025-12-10 21:15:11'),
(42, 3, '429841', 'student@example.com', '2025-12-10 21:43:01', 1, '2025-12-10 21:33:01'),
(43, 2, '546243', 'teacher@example.com', '2025-12-10 22:08:34', 1, '2025-12-10 21:58:34'),
(44, 1, '161437', 'admin@example.com', '2025-12-10 22:09:42', 1, '2025-12-10 21:59:42'),
(45, 3, '628074', 'student@example.com', '2025-12-10 22:28:24', 1, '2025-12-10 22:18:24'),
(46, 3, '591026', 'student@example.com', '2025-12-10 22:31:15', 1, '2025-12-10 22:21:15'),
(47, 1, '108131', 'admin@example.com', '2025-12-10 22:37:53', 1, '2025-12-10 22:27:53'),
(48, 2, '133937', 'teacher@example.com', '2025-12-10 23:43:26', 1, '2025-12-10 23:33:26'),
(49, 1, '338766', 'admin@example.com', '2025-12-11 00:00:37', 1, '2025-12-10 23:50:37'),
(50, 2, '486216', 'teacher@example.com', '2025-12-11 00:01:25', 1, '2025-12-10 23:51:25'),
(51, 1, '115079', 'admin@example.com', '2025-12-11 00:29:25', 1, '2025-12-11 00:19:25'),
(52, 2, '903509', 'teacher@example.com', '2025-12-11 00:34:27', 1, '2025-12-11 00:24:27'),
(53, 3, '973838', 'student@example.com', '2025-12-11 00:36:45', 1, '2025-12-11 00:26:45'),
(54, 2, '542849', 'teacher@example.com', '2025-12-11 00:43:33', 1, '2025-12-11 00:33:33'),
(55, 3, '318963', 'student@example.com', '2025-12-11 00:48:39', 1, '2025-12-11 00:38:39'),
(56, 2, '248885', 'teacher@example.com', '2025-12-11 00:53:44', 1, '2025-12-11 00:43:44'),
(57, 3, '715846', 'student@example.com', '2025-12-11 00:54:46', 1, '2025-12-11 00:44:46'),
(58, 2, '702967', 'teacher@example.com', '2025-12-11 04:46:43', 1, '2025-12-11 04:36:43'),
(59, 1, '760995', 'admin@example.com', '2025-12-11 04:51:47', 1, '2025-12-11 04:41:47'),
(60, 1, '430137', 'admin@example.com', '2025-12-11 05:06:46', 1, '2025-12-11 04:56:46'),
(61, 3, '235823', 'student@example.com', '2025-12-11 05:13:08', 1, '2025-12-11 05:03:08'),
(62, 1, '249273', 'admin@example.com', '2025-12-11 05:13:25', 1, '2025-12-11 05:03:25'),
(63, 3, '249375', 'student@example.com', '2025-12-11 05:14:49', 1, '2025-12-11 05:04:49'),
(64, 2, '837842', 'teacher@example.com', '2025-12-11 05:16:41', 1, '2025-12-11 05:06:41'),
(65, 1, '380271', 'admin@example.com', '2025-12-11 05:21:48', 1, '2025-12-11 05:11:48'),
(66, 2, '903068', 'teacher@example.com', '2025-12-11 05:22:20', 1, '2025-12-11 05:12:20'),
(67, 1, '150470', 'admin@example.com', '2025-12-11 05:24:22', 1, '2025-12-11 05:14:22'),
(68, 3, '251234', 'student@example.com', '2025-12-11 07:21:28', 1, '2025-12-11 07:11:28'),
(69, 2, '685757', 'teacher@example.com', '2025-12-11 07:22:15', 1, '2025-12-11 07:12:15'),
(70, 3, '182423', 'student@example.com', '2025-12-11 07:22:44', 1, '2025-12-11 07:12:44'),
(71, 1, '621669', 'admin@example.com', '2025-12-11 07:24:09', 1, '2025-12-11 07:14:09'),
(72, 2, '435595', 'teacher@example.com', '2025-12-11 07:59:13', 1, '2025-12-11 07:49:13'),
(73, 1, '274886', 'admin@example.com', '2025-12-11 08:00:08', 1, '2025-12-11 07:50:08'),
(74, 11, '612523', 'mybellee@gmail.com', '2025-12-11 08:26:43', 1, '2025-12-11 08:16:43'),
(75, 1, '286289', 'admin@example.com', '2025-12-11 08:27:08', 1, '2025-12-11 08:17:08'),
(76, 2, '215258', 'teacher@example.com', '2025-12-11 08:28:08', 1, '2025-12-11 08:18:08'),
(77, 1, '957373', 'admin@example.com', '2025-12-11 08:29:16', 1, '2025-12-11 08:19:16'),
(78, 1, '265361', 'admin@example.com', '2025-12-11 09:19:34', 1, '2025-12-11 09:09:34'),
(79, 1, '164954', 'admin@example.com', '2025-12-11 09:23:35', 1, '2025-12-11 09:13:35'),
(80, 2, '361802', 'teacher@example.com', '2025-12-11 09:43:18', 1, '2025-12-11 09:33:18'),
(81, 3, '493180', 'student@example.com', '2025-12-11 09:44:51', 1, '2025-12-11 09:34:51'),
(82, 1, '395213', 'admin@example.com', '2025-12-11 09:46:26', 1, '2025-12-11 09:36:26'),
(83, 2, '384635', 'teacher@example.com', '2025-12-11 09:47:17', 1, '2025-12-11 09:37:18'),
(84, 3, '126472', 'student@example.com', '2025-12-11 09:53:28', 1, '2025-12-11 09:43:28'),
(85, 1, '722778', 'admin@example.com', '2025-12-11 09:54:44', 1, '2025-12-11 09:44:44'),
(86, 3, '469028', 'student@example.com', '2025-12-11 09:55:37', 1, '2025-12-11 09:45:37'),
(87, 1, '457019', 'admin@example.com', '2025-12-11 10:05:34', 1, '2025-12-11 09:55:34'),
(88, 3, '158963', 'student@example.com', '2025-12-11 10:09:48', 1, '2025-12-11 09:59:48'),
(89, 1, '018576', 'admin@example.com', '2025-12-12 00:43:39', 1, '2025-12-12 00:33:39'),
(90, 2, '020813', 'teacher@example.com', '2025-12-12 02:42:55', 1, '2025-12-12 02:32:55'),
(91, 1, '148850', 'admin@example.com', '2025-12-12 02:46:02', 1, '2025-12-12 02:36:02'),
(92, 2, '772614', 'teacher@example.com', '2025-12-12 02:46:50', 1, '2025-12-12 02:36:50'),
(93, 3, '875953', 'student@example.com', '2025-12-12 02:49:40', 1, '2025-12-12 02:39:40'),
(94, 1, '848540', 'admin@example.com', '2025-12-12 02:51:32', 1, '2025-12-12 02:41:32'),
(95, 11, '886759', 'mybellee@gmail.com', '2025-12-12 02:53:35', 0, '2025-12-12 02:43:35'),
(96, 1, '866957', 'admin@example.com', '2025-12-12 02:55:08', 1, '2025-12-12 02:45:08'),
(97, 1, '222312', 'admin@example.com', '2025-12-12 03:00:53', 1, '2025-12-12 02:50:53'),
(98, 13, '717066', 'akoarakabelle@gmail.com', '2025-12-12 03:01:25', 1, '2025-12-12 02:51:25'),
(99, 2, '571097', 'teacher@example.com', '2025-12-12 03:01:56', 1, '2025-12-12 02:51:56'),
(100, 13, '812868', 'akoarakabelle@gmail.com', '2025-12-12 03:03:09', 1, '2025-12-12 02:53:09'),
(101, 1, '961418', 'admin@example.com', '2025-12-12 03:04:06', 1, '2025-12-12 02:54:06'),
(102, 1, '837551', 'admin@example.com', '2025-12-12 07:04:23', 1, '2025-12-12 06:54:23'),
(103, 1, '553567', 'admin@example.com', '2025-12-12 08:05:58', 0, '2025-12-12 07:55:58'),
(104, 2, '288397', 'teacher@example.com', '2025-12-12 08:08:24', 0, '2025-12-12 07:58:24'),
(105, 3, '928687', 'student@example.com', '2025-12-12 08:09:54', 1, '2025-12-12 07:59:54'),
(106, 3, '744887', 'student@example.com', '2025-12-12 09:55:39', 0, '2025-12-12 09:45:39'),
(107, 13, '761710', 'akoarakabelle@gmail.com', '2025-12-12 09:59:43', 0, '2025-12-12 09:49:43');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) UNSIGNED NOT NULL,
  `department_id` int(11) UNSIGNED NOT NULL COMMENT 'Foreign key to departments',
  `program_code` varchar(20) NOT NULL COMMENT 'Program code (e.g., BSIT, BSCS)',
  `program_name` varchar(255) NOT NULL COMMENT 'Full program name',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `department_id`, `program_code`, `program_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 5, 'BACOMM', 'Bachelor of Arts in Communication', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(5, 5, 'BAPSYC', 'Bachelor of Arts in Psychology', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(6, 5, 'BASW', 'Bachelor of Arts in Social Work', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(7, 5, 'BSBIO', 'Bachelor of Science in Biology', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(8, 5, 'BSES', 'Bachelor of Science in Environmental Science', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(9, 5, 'BSMATH', 'Bachelor of Science in Mathematics', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(10, 6, 'BSBA', 'Bachelor of Science in Business Administration', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(11, 6, 'BSA', 'Bachelor of Science in Accountancy', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(12, 6, 'BSHM', 'Bachelor of Science in Hospitality Management', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(13, 6, 'BSITM', 'Bachelor of Science in Information Technology Management', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(14, 7, 'BSIT', 'Bachelor of Science in Information Technology', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(15, 7, 'BSCS', 'Bachelor of Science in Computer Science', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(16, 7, 'BSCE', 'Bachelor of Science in Civil Engineering', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(17, 7, 'BSEE', 'Bachelor of Science in Electrical Engineering', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(18, 7, 'BSME', 'Bachelor of Science in Mechanical Engineering', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(19, 8, 'BSCRIM', 'Bachelor of Science in Criminology', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(20, 8, 'BSCJ', 'Bachelor of Science in Criminal Justice', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(21, 9, 'BSE', 'Bachelor of Science in Education', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(22, 9, 'BSEED', 'Bachelor of Science in Elementary Education', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(23, 9, 'BSEED-SEC', 'Bachelor of Science in Secondary Education', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(24, 10, 'BSMID', 'Bachelor of Science in Midwifery', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(25, 10, 'BSPHARM', 'Bachelor of Science in Pharmacy', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(26, 10, 'BSN', 'Bachelor of Science in Nursing', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(27, 11, 'SHS-STEM', 'Senior High School - Science, Technology, Engineering, and Mathematics', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(28, 11, 'SHS-ABM', 'Senior High School - Accountancy, Business and Management', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(29, 11, 'SHS-HUMSS', 'Senior High School - Humanities and Social Sciences', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(30, 11, 'SHS-TVL', 'Senior High School - Technical-Vocational-Livelihood', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(31, 12, 'MA', 'Master of Arts', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(32, 12, 'MS', 'Master of Science', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23'),
(33, 12, 'PhD', 'Doctor of Philosophy', NULL, 1, '2025-12-10 23:21:23', '2025-12-10 23:21:23');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) UNSIGNED NOT NULL,
  `lesson_id` int(11) UNSIGNED DEFAULT NULL,
  `course_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to courses (alternative to lesson_id)',
  `assignment_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to assignments (links quiz to assignment for grading)',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `max_score` decimal(10,2) DEFAULT 100.00,
  `due_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `lesson_id`, `course_id`, `assignment_id`, `title`, `description`, `max_score`, `due_date`, `created_at`, `updated_at`) VALUES
(6, NULL, 4, 7, 'QuizKakak', 'Multiple Choice and Identification', 80.00, '2025-12-17 12:59:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) UNSIGNED NOT NULL,
  `acad_year_id` int(11) UNSIGNED NOT NULL COMMENT 'Foreign key to acad_years',
  `semester` varchar(20) NOT NULL COMMENT 'Semester name (e.g., First Semester, Second Semester)',
  `semester_code` varchar(10) NOT NULL COMMENT 'Semester code (e.g., 1ST, 2ND)',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `acad_year_id`, `semester`, `semester_code`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 3, '1st', '58858', '2025-12-10', '2026-03-10', 1, '2025-12-10 08:53:39', '2025-12-11 09:45:10'),
(3, 3, '2nd', '48569', '2025-12-12', '2026-04-12', 1, '2025-12-12 08:01:06', '2025-12-12 08:01:06'),
(4, 3, 'Summer', '14859', '2026-06-17', '2026-10-17', 1, '2025-12-12 08:02:57', '2025-12-12 08:02:57');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) UNSIGNED NOT NULL,
  `quiz_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `answer` text NOT NULL,
  `score` decimal(10,2) DEFAULT NULL COMMENT 'Score given by teacher',
  `graded_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'User ID of teacher who graded',
  `graded_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE `terms` (
  `id` int(11) UNSIGNED NOT NULL,
  `semester_id` int(11) UNSIGNED NOT NULL COMMENT 'Foreign key to semesters',
  `term` varchar(50) NOT NULL COMMENT 'Term name (e.g., Prelim, Midterm, Finals)',
  `term_code` varchar(10) NOT NULL COMMENT 'Term code (e.g., PRELIM, MIDTERM, FINALS)',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `terms`
--

INSERT INTO `terms` (`id`, `semester_id`, `term`, `term_code`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 2, 'Prelim', '84587', '2025-12-10', '2026-03-10', 1, '2025-12-10 08:53:56', '2025-12-10 08:53:56'),
(3, 2, 'Midterm', '65895', '2026-04-12', '2026-06-12', 1, '2025-12-12 08:03:37', '2025-12-12 08:03:37'),
(4, 2, 'Final', '35685', '2026-07-12', '2026-10-12', 1, '2025-12-12 08:04:22', '2025-12-12 08:04:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL DEFAULT 'student',
  `department_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to departments (for students)',
  `program_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to programs (for students)',
  `student_id` varchar(50) DEFAULT NULL COMMENT 'Student ID number',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `department_id`, `program_id`, `student_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$5ZRUlHKv/rLiDCF96E7IZeozOFHegPnRjzOl1vixaVATswFg7a.jq', 'admin', NULL, NULL, NULL, 'active', '2025-11-20 05:52:41', '2025-12-07 07:22:13'),
(2, 'Teacher User', 'teacher@example.com', '$2y$10$UGyC/ASlc0JvJbbhjotLyuWSj8Lk3qvg/umZ7BzOdglhA1evn3NnW', 'teacher', NULL, NULL, NULL, 'active', '2025-11-20 05:52:41', '2025-12-10 20:36:35'),
(3, 'Student User', 'student@example.com', '$2y$10$m2tGHpJ8qQmUWDbgcfDHEeKlcF3nFjyNOcZHAtD7VQ.vKltk.hmZu', 'student', 7, 14, '2311600039', 'active', '2025-11-20 05:52:41', '2025-12-11 07:48:28'),
(11, 'mybelleee', 'mybellee@gmail.com', '$2y$10$OiGJw1Eb9Yy9YN1V8SNITO.Vsm4ef8QhuryTmURQVfMLbrhBgz4Mu', 'student', 7, 14, '2311600040', 'active', '2025-12-11 08:16:30', '2025-12-11 08:17:42'),
(12, 'markypadilla', 'markypadilla@gmail.com', '$2y$10$hy4rJoX7CwAyI2CUa4o6A.gEoiATPfH/DO8N/ctoeBJK/qOTbcev2', 'student', 7, 15, '2311600035', 'active', '2025-12-11 08:19:46', '2025-12-11 08:20:06'),
(13, 'akoarakabelle', 'akoarakabelle@gmail.com', '$2y$10$BJTPnCix.TLkagmXoff5NO/rdnwayBP6z0hAwTFLFCYzQVosliXuK', 'student', 7, 14, '2311600045', 'active', '2025-12-12 02:49:53', '2025-12-12 02:51:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acad_years`
--
ALTER TABLE `acad_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `acad_year` (`acad_year`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignments_course_id_foreign` (`course_id`),
  ADD KEY `assignments_grading_period_id_foreign` (`grading_period_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_instructor_id_foreign` (`instructor_id`);

--
-- Indexes for table `course_schedules`
--
ALTER TABLE `course_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_schedules_course_id_foreign` (`course_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_code` (`department_code`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enrollments_user_id_foreign` (`user_id`),
  ADD KEY `enrollments_course_id_foreign` (`course_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollment_id_assignment_id` (`enrollment_id`,`assignment_id`),
  ADD KEY `grades_assignment_id_foreign` (`assignment_id`),
  ADD KEY `grades_graded_by_foreign` (`graded_by`);

--
-- Indexes for table `grading_periods`
--
ALTER TABLE `grading_periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grading_periods_term_id_foreign` (`term_id`),
  ADD KEY `grading_periods_semester_id_foreign` (`semester_id`);

--
-- Indexes for table `grading_weights`
--
ALTER TABLE `grading_weights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grading_weights_course_id_foreign` (`course_id`),
  ADD KEY `grading_weights_term_id_foreign` (`term_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lessons_course_id_foreign` (`course_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `materials_course_id_foreign` (`course_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id_otp_code_is_used` (`user_id`,`otp_code`,`is_used`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_id_program_code` (`department_id`,`program_code`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizzes_lesson_id_foreign` (`lesson_id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `semesters_acad_year_id_foreign` (`acad_year_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submissions_quiz_id_foreign` (`quiz_id`),
  ADD KEY `submissions_user_id_foreign` (`user_id`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `terms_semester_id_foreign` (`semester_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acad_years`
--
ALTER TABLE `acad_years`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `course_schedules`
--
ALTER TABLE `course_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grading_periods`
--
ALTER TABLE `grading_periods`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `grading_weights`
--
ALTER TABLE `grading_weights`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assignments_grading_period_id_foreign` FOREIGN KEY (`grading_period_id`) REFERENCES `grading_periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_schedules`
--
ALTER TABLE `course_schedules`
  ADD CONSTRAINT `course_schedules_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `grades_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `grades_graded_by_foreign` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `grading_periods`
--
ALTER TABLE `grading_periods`
  ADD CONSTRAINT `grading_periods_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `grading_periods_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grading_weights`
--
ALTER TABLE `grading_weights`
  ADD CONSTRAINT `grading_weights_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `grading_weights_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  ADD CONSTRAINT `otp_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `semesters`
--
ALTER TABLE `semesters`
  ADD CONSTRAINT `semesters_acad_year_id_foreign` FOREIGN KEY (`acad_year_id`) REFERENCES `acad_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `terms`
--
ALTER TABLE `terms`
  ADD CONSTRAINT `terms_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
