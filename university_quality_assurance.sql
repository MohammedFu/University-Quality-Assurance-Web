-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 24, 2025 at 06:15 PM
-- Server version: 8.2.0
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `university_quality_assurance`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `generate_weeks`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_weeks` (IN `start_date` DATE, IN `academic_year_id` INT)   BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE current_start_date DATE;
    DECLARE current_end_date DATE;
    
    SET current_start_date = start_date;
    
    WHILE i <= 12 DO
        SET current_end_date = DATE_ADD(current_start_date, INTERVAL 6 DAY);
        
        INSERT INTO weeks (academic_year_id, week_number, start_date, end_date)
        VALUES (academic_year_id, i, current_start_date, current_end_date);
        
        SET current_start_date = DATE_ADD(current_end_date, INTERVAL 1 DAY);
        SET i = i + 1;
    END WHILE;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `academic_year`
--

DROP TABLE IF EXISTS `academic_year`;
CREATE TABLE IF NOT EXISTS `academic_year` (
  `academic_year_id` int NOT NULL AUTO_INCREMENT,
  `year` varchar(20) NOT NULL,
  `level` enum('1','2','3','4') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `semester_id` int DEFAULT NULL,
  `collage_id` int NOT NULL,
  `major_id` int NOT NULL,
  PRIMARY KEY (`academic_year_id`),
  KEY `fk_academic_years_semester` (`semester_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `academic_year`
--

INSERT INTO `academic_year` (`academic_year_id`, `year`, `level`, `start_date`, `end_date`, `semester_id`, `collage_id`, `major_id`) VALUES
(1, '2024/2025', '1', '2024-01-01', '2024-03-23', 1, 1, 1),
(2, '2024/2025', '1', '2024-03-30', '2024-06-15', 2, 2, 2),
(3, '2025/2026', '2', '2025-06-16', '2025-10-15', 3, 3, 3),
(4, '2025/2026', '2', '2025-12-22', '2026-03-22', 4, 4, 4),
(5, '2026/2027', '3', '2026-04-07', '2026-07-07', 5, 5, 5),
(6, '2026/2027', '3', '2027-07-21', '2027-10-21', 6, 6, 6),
(7, '2027/2028', '4', '2027-11-21', '2028-01-21', 7, 7, 7),
(8, '2027/2028', '4', '2028-02-07', '2028-05-07', 8, 8, 8);

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

DROP TABLE IF EXISTS `administrators`;
CREATE TABLE IF NOT EXISTS `administrators` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`admin_id`, `name`, `email`, `password`) VALUES
(1, 'John Doe', 'johndoe@example.com', '$2y$10$3Bjtmp3SYTQG80xfcVDCdOrO3SN7lxLtXyNAhDr9Gr2MMh8K9D4Ee'),
(2, 'Jane Smith', 'janesmith@example.com', 'securepassword'),
(3, 'Michael Brown', 'michaelb@example.com', 'admin123'),
(4, 'Emily Davis', 'emilyd@example.com', 'passw0rd'),
(5, 'Christopher Wilson', 'chrisw@example.com', 'letmein'),
(6, 'Sarah Johnson', 'sarahj@example.com', 'adminsecure'),
(7, 'David Lee', 'davidl@example.com', 'mypassword'),
(8, 'Sophia Martinez', 'sophiam@example.com', 'qwerty123'),
(9, 'Daniel Anderson', 'daniela@example.com', 'strongpass'),
(10, 'Olivia Taylor', 'oliviat@example.com', 'hello1234');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE IF NOT EXISTS `attendance` (
  `attendance_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `lecture_id` int NOT NULL,
  `date` date NOT NULL,
  `present` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `unique_attendance` (`student_id`,`lecture_id`,`date`),
  KEY `lecture_id` (`lecture_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `student_id`, `lecture_id`, `date`, `present`) VALUES
(1, 1, 2, '2025-01-10', 1),
(2, 2, 3, '2025-01-10', 1),
(3, 3, 4, '2025-01-10', 1),
(4, 4, 5, '2025-01-11', 1),
(5, 5, 6, '2025-01-11', 1),
(6, 6, 7, '2025-01-12', 1),
(7, 7, 8, '2025-01-12', 1),
(8, 8, 2, '2025-01-13', 1),
(11, 1, 8, '2025-02-15', 0),
(12, 8, 8, '2025-02-15', 1),
(15, 1, 3, '2025-02-15', 1),
(16, 8, 3, '2025-02-15', 0);

-- --------------------------------------------------------

--
-- Table structure for table `college`
--

DROP TABLE IF EXISTS `college`;
CREATE TABLE IF NOT EXISTS `college` (
  `college_id` int NOT NULL AUTO_INCREMENT,
  `college_name` varchar(100) NOT NULL,
  PRIMARY KEY (`college_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `college`
--

INSERT INTO `college` (`college_id`, `college_name`) VALUES
(1, 'College of Engineering'),
(2, 'College of Science'),
(3, 'College of Arts'),
(4, 'College of Business'),
(5, 'College of Education'),
(6, 'College of Law'),
(7, 'College of Medicine'),
(8, 'College of Information Technology'),
(9, 'College of Architecture'),
(10, 'College of Agriculture');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `course_id` int NOT NULL AUTO_INCREMENT,
  `course_name` varchar(100) NOT NULL,
  `major_id` int DEFAULT NULL,
  `academic_year_id` int DEFAULT NULL,
  `college_id` int DEFAULT NULL,
  PRIMARY KEY (`course_id`),
  KEY `major_id` (`major_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `college_id` (`college_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `major_id`, `academic_year_id`, `college_id`) VALUES
(1, 'Data Structures', 1, 8, 1),
(2, 'Thermodynamics', 2, 2, 1),
(3, 'Quantum Mechanics', 3, 3, 2),
(4, 'Organic Chemistry', 4, 4, 2),
(5, 'Modern Poetry', 5, 5, 3),
(6, 'Microeconomics', 6, 6, 4),
(7, 'Management Principles', 7, 7, 4),
(8, 'Constitutional Law', 8, 8, 6);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `feedback_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `lecture_id` int DEFAULT NULL,
  `feedback_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `rating` double DEFAULT NULL,
  `submitted_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`),
  KEY `student_id` (`student_id`),
  KEY `lecture_id` (`lecture_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `student_id`, `lecture_id`, `feedback_text`, `rating`, `submitted_on`) VALUES
(2, 2, 2, 'The lecturer was very engaging.', 0, '2024-10-07 16:13:04'),
(3, 3, 3, 'Interesting lecture with great insights.', 0, '2024-10-07 16:13:04'),
(4, 4, 4, 'Good lecture, but a bit fast-paced.', 0, '2024-10-07 16:13:04'),
(5, 5, 5, 'Materials were well-organized.', 0, '2024-10-07 16:13:04'),
(6, 6, 6, 'Lecture was interactive and fun.', 0, '2024-10-07 16:13:04'),
(7, 7, 7, 'Very helpful session.', 0, '2024-10-07 16:13:04'),
(8, 8, 8, 'Lecturer was very approachable.', 0, '2024-10-07 16:13:04'),
(13, 3, 3, 'Nice Lecture :)', 0, '2024-10-08 20:10:26'),
(14, 3, 4, 'It is nice lecture', 0, '2024-10-08 20:19:46'),
(15, 6, 6, NULL, 0, '2024-12-21 20:52:25'),
(17, 6, 6, 'great lesson', 4, '2024-12-21 20:59:48'),
(18, 6, 6, 'bad one', 1.5, '2024-12-21 21:18:37'),
(19, 2, 2, NULL, 3, '2024-12-22 09:16:12'),
(20, 2, 2, 'greate', 4, '2024-12-22 10:09:36'),
(21, 8, 8, 'jjn', 4, '2024-12-26 00:04:01'),
(22, 5, 6, 'the lecturer does not understand us very well', 3, '2025-01-15 22:11:30'),
(23, 5, 5, 'Wooooooooooooooooooow', 5, '2025-01-15 22:28:08'),
(24, 5, 7, 'nice Lecture :)', 4, '2025-01-15 22:37:21'),
(25, 8, 2, 'gfdjo', 3.5, '2025-01-16 07:50:56'),
(26, 4, 4, 'nice Lecture', 3.5, '2025-02-11 07:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `learning_outcome`
--

DROP TABLE IF EXISTS `learning_outcome`;
CREATE TABLE IF NOT EXISTS `learning_outcome` (
  `lo_id` int NOT NULL AUTO_INCREMENT,
  `topic_id` int NOT NULL,
  `lo_symbol` varchar(10) NOT NULL,
  `lo_description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`lo_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `learning_outcome`
--

INSERT INTO `learning_outcome` (`lo_id`, `topic_id`, `lo_symbol`, `lo_description`) VALUES
(1, 1, 'a2', 'Design/conduct experiments and analyze data'),
(2, 2, 'a3', 'Design systems under constraints'),
(3, 15, 'a6', 'Understand ethical responsibility'),
(4, 16, 'a7', 'Communicate effectively'),
(5, 22, 'a3', 'Design systems under constraints'),
(6, 29, 'a10', 'Use modern engineering tools'),
(7, 36, 'a7', 'Communicate effectively'),
(8, 43, 'a4', 'Function on multidisciplinary teams'),
(9, 50, 'a1', 'Apply math, science, and engineering principles'),
(10, 56, 'a7', 'Communicate effectively'),
(11, 63, 'a4', 'Function on multidisciplinary teams'),
(12, 70, 'a1', 'Apply math, science, and engineering principles'),
(13, 77, 'a8', 'Understand global/societal impacts'),
(14, 84, 'a5', 'Identify/formulate/solve engineering problems'),
(15, 91, 'a2', 'Design/conduct experiments and analyze data'),
(16, 9, 'a10', 'Use modern engineering tools'),
(17, 23, 'a4', 'Function on multidisciplinary teams'),
(18, 30, 'a1', 'Apply math, science, and engineering principles'),
(19, 37, 'a8', 'Understand global/societal impacts'),
(20, 44, 'a5', 'Identify/formulate/solve engineering problems'),
(21, 57, 'a8', 'Understand global/societal impacts'),
(22, 64, 'a5', 'Identify/formulate/solve engineering problems'),
(23, 71, 'a2', 'Design/conduct experiments and analyze data'),
(24, 78, 'a9', 'Engage in lifelong learning'),
(25, 85, 'a6', 'Understand ethical responsibility'),
(26, 92, 'a3', 'Design systems under constraints'),
(27, 3, 'a4', 'Function on multidisciplinary teams'),
(28, 10, 'a1', 'Apply math, science, and engineering principles'),
(29, 17, 'a8', 'Understand global/societal impacts'),
(30, 24, 'a5', 'Identify/formulate/solve engineering problems'),
(31, 31, 'a2', 'Design/conduct experiments and analyze data'),
(32, 38, 'a9', 'Engage in lifelong learning'),
(33, 45, 'a6', 'Understand ethical responsibility'),
(34, 51, 'a2', 'Design/conduct experiments and analyze data'),
(35, 58, 'a9', 'Engage in lifelong learning'),
(36, 65, 'a6', 'Understand ethical responsibility'),
(37, 72, 'a3', 'Design systems under constraints'),
(38, 79, 'a10', 'Use modern engineering tools'),
(39, 86, 'a7', 'Communicate effectively'),
(40, 93, 'a4', 'Function on multidisciplinary teams'),
(41, 4, 'a5', 'Identify/formulate/solve engineering problems'),
(42, 11, 'a2', 'Design/conduct experiments and analyze data'),
(43, 18, 'a9', 'Engage in lifelong learning'),
(44, 25, 'a6', 'Understand ethical responsibility'),
(45, 32, 'a3', 'Design systems under constraints'),
(46, 39, 'a10', 'Use modern engineering tools'),
(47, 46, 'a7', 'Communicate effectively'),
(48, 52, 'a3', 'Design systems under constraints'),
(49, 59, 'a10', 'Use modern engineering tools'),
(50, 66, 'a7', 'Communicate effectively'),
(51, 73, 'a4', 'Function on multidisciplinary teams'),
(52, 80, 'a1', 'Apply math, science, and engineering principles'),
(53, 87, 'a8', 'Understand global/societal impacts'),
(54, 94, 'a5', 'Identify/formulate/solve engineering problems'),
(55, 5, 'a6', 'Understand ethical responsibility'),
(56, 12, 'a3', 'Design systems under constraints'),
(57, 19, 'a10', 'Use modern engineering tools'),
(58, 26, 'a7', 'Communicate effectively'),
(59, 33, 'a4', 'Function on multidisciplinary teams'),
(60, 40, 'a1', 'Apply math, science, and engineering principles'),
(61, 47, 'a8', 'Understand global/societal impacts'),
(62, 53, 'a4', 'Function on multidisciplinary teams'),
(63, 60, 'a1', 'Apply math, science, and engineering principles'),
(64, 67, 'a8', 'Understand global/societal impacts'),
(65, 74, 'a5', 'Identify/formulate/solve engineering problems'),
(66, 81, 'a2', 'Design/conduct experiments and analyze data'),
(67, 88, 'a9', 'Engage in lifelong learning'),
(68, 95, 'a6', 'Understand ethical responsibility'),
(69, 6, 'a7', 'Communicate effectively'),
(70, 13, 'a4', 'Function on multidisciplinary teams'),
(71, 20, 'a1', 'Apply math, science, and engineering principles'),
(72, 27, 'a8', 'Understand global/societal impacts'),
(73, 34, 'a5', 'Identify/formulate/solve engineering problems'),
(74, 41, 'a2', 'Design/conduct experiments and analyze data'),
(75, 48, 'a9', 'Engage in lifelong learning'),
(76, 54, 'a5', 'Identify/formulate/solve engineering problems'),
(77, 61, 'a2', 'Design/conduct experiments and analyze data'),
(78, 68, 'a9', 'Engage in lifelong learning'),
(79, 75, 'a6', 'Understand ethical responsibility'),
(80, 82, 'a3', 'Design systems under constraints'),
(81, 89, 'a10', 'Use modern engineering tools'),
(82, 96, 'a7', 'Communicate effectively'),
(83, 7, 'a8', 'Understand global/societal impacts'),
(84, 8, 'a9', 'Engage in lifelong learning'),
(85, 14, 'a5', 'Identify/formulate/solve engineering problems'),
(86, 21, 'a2', 'Design/conduct experiments and analyze data'),
(87, 28, 'a9', 'Engage in lifelong learning'),
(88, 35, 'a6', 'Understand ethical responsibility'),
(89, 42, 'a3', 'Design systems under constraints'),
(90, 49, 'a10', 'Use modern engineering tools'),
(91, 55, 'a6', 'Understand ethical responsibility'),
(92, 62, 'a3', 'Design systems under constraints'),
(93, 69, 'a10', 'Use modern engineering tools'),
(94, 76, 'a7', 'Communicate effectively'),
(95, 83, 'a4', 'Function on multidisciplinary teams'),
(96, 90, 'a1', 'Apply math, science, and engineering principles'),
(128, 1, 'b2', 'Apply engineering design principles'),
(129, 2, 'b3', 'Develop sustainable solutions'),
(130, 15, 'b1', 'Analyze complex engineering problems'),
(131, 16, 'b2', 'Apply engineering design principles'),
(132, 22, 'b3', 'Develop sustainable solutions'),
(133, 29, 'b5', 'Integrate modern technologies'),
(134, 36, 'b2', 'Apply engineering design principles'),
(135, 43, 'b4', 'Evaluate engineering systems'),
(136, 50, 'b1', 'Analyze complex engineering problems'),
(137, 56, 'b2', 'Apply engineering design principles'),
(138, 63, 'b4', 'Evaluate engineering systems'),
(139, 70, 'b1', 'Analyze complex engineering problems'),
(140, 77, 'b3', 'Develop sustainable solutions'),
(141, 84, 'b5', 'Integrate modern technologies'),
(142, 91, 'b2', 'Apply engineering design principles'),
(143, 9, 'b5', 'Integrate modern technologies'),
(144, 23, 'b4', 'Evaluate engineering systems'),
(145, 30, 'b1', 'Analyze complex engineering problems'),
(146, 37, 'b3', 'Develop sustainable solutions'),
(147, 44, 'b5', 'Integrate modern technologies'),
(148, 57, 'b3', 'Develop sustainable solutions'),
(149, 64, 'b5', 'Integrate modern technologies'),
(150, 71, 'b2', 'Apply engineering design principles'),
(151, 78, 'b4', 'Evaluate engineering systems'),
(152, 85, 'b1', 'Analyze complex engineering problems'),
(153, 92, 'b3', 'Develop sustainable solutions'),
(154, 3, 'b4', 'Evaluate engineering systems'),
(155, 10, 'b1', 'Analyze complex engineering problems'),
(156, 17, 'b3', 'Develop sustainable solutions'),
(157, 24, 'b5', 'Integrate modern technologies'),
(158, 31, 'b2', 'Apply engineering design principles'),
(159, 38, 'b4', 'Evaluate engineering systems'),
(160, 45, 'b1', 'Analyze complex engineering problems'),
(161, 51, 'b2', 'Apply engineering design principles'),
(162, 58, 'b4', 'Evaluate engineering systems'),
(163, 65, 'b1', 'Analyze complex engineering problems'),
(164, 72, 'b3', 'Develop sustainable solutions'),
(165, 79, 'b5', 'Integrate modern technologies'),
(166, 86, 'b2', 'Apply engineering design principles'),
(167, 93, 'b4', 'Evaluate engineering systems'),
(168, 4, 'b5', 'Integrate modern technologies'),
(169, 11, 'b2', 'Apply engineering design principles'),
(170, 18, 'b4', 'Evaluate engineering systems'),
(171, 25, 'b1', 'Analyze complex engineering problems'),
(172, 32, 'b3', 'Develop sustainable solutions'),
(173, 39, 'b5', 'Integrate modern technologies'),
(174, 46, 'b2', 'Apply engineering design principles'),
(175, 52, 'b3', 'Develop sustainable solutions'),
(176, 59, 'b5', 'Integrate modern technologies'),
(177, 66, 'b2', 'Apply engineering design principles'),
(178, 73, 'b4', 'Evaluate engineering systems'),
(179, 80, 'b1', 'Analyze complex engineering problems'),
(180, 87, 'b3', 'Develop sustainable solutions'),
(181, 94, 'b5', 'Integrate modern technologies'),
(182, 5, 'b1', 'Analyze complex engineering problems'),
(183, 12, 'b3', 'Develop sustainable solutions'),
(184, 19, 'b5', 'Integrate modern technologies'),
(185, 26, 'b2', 'Apply engineering design principles'),
(186, 33, 'b4', 'Evaluate engineering systems'),
(187, 40, 'b1', 'Analyze complex engineering problems'),
(188, 47, 'b3', 'Develop sustainable solutions'),
(189, 53, 'b4', 'Evaluate engineering systems'),
(190, 60, 'b1', 'Analyze complex engineering problems'),
(191, 67, 'b3', 'Develop sustainable solutions'),
(192, 74, 'b5', 'Integrate modern technologies'),
(193, 81, 'b2', 'Apply engineering design principles'),
(194, 88, 'b4', 'Evaluate engineering systems'),
(195, 95, 'b1', 'Analyze complex engineering problems'),
(196, 6, 'b2', 'Apply engineering design principles'),
(197, 13, 'b4', 'Evaluate engineering systems'),
(198, 20, 'b1', 'Analyze complex engineering problems'),
(199, 27, 'b3', 'Develop sustainable solutions'),
(200, 34, 'b5', 'Integrate modern technologies'),
(201, 41, 'b2', 'Apply engineering design principles'),
(202, 48, 'b4', 'Evaluate engineering systems'),
(203, 54, 'b5', 'Integrate modern technologies'),
(204, 61, 'b2', 'Apply engineering design principles'),
(205, 68, 'b4', 'Evaluate engineering systems'),
(206, 75, 'b1', 'Analyze complex engineering problems'),
(207, 82, 'b3', 'Develop sustainable solutions'),
(208, 89, 'b5', 'Integrate modern technologies'),
(209, 96, 'b2', 'Apply engineering design principles'),
(210, 7, 'b3', 'Develop sustainable solutions'),
(211, 8, 'b4', 'Evaluate engineering systems'),
(212, 14, 'b5', 'Integrate modern technologies'),
(213, 21, 'b2', 'Apply engineering design principles'),
(214, 28, 'b4', 'Evaluate engineering systems'),
(215, 35, 'b1', 'Analyze complex engineering problems'),
(216, 42, 'b3', 'Develop sustainable solutions'),
(217, 49, 'b5', 'Integrate modern technologies'),
(218, 55, 'b1', 'Analyze complex engineering problems'),
(219, 62, 'b3', 'Develop sustainable solutions'),
(220, 69, 'b5', 'Integrate modern technologies'),
(221, 76, 'b2', 'Apply engineering design principles'),
(222, 83, 'b4', 'Evaluate engineering systems'),
(223, 90, 'b1', 'Analyze complex engineering problems');

-- --------------------------------------------------------

--
-- Table structure for table `lecturers`
--

DROP TABLE IF EXISTS `lecturers`;
CREATE TABLE IF NOT EXISTS `lecturers` (
  `lecturer_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `college_id` int DEFAULT NULL,
  PRIMARY KEY (`lecturer_id`),
  UNIQUE KEY `email` (`email`),
  KEY `college_id` (`college_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lecturers`
--

INSERT INTO `lecturers` (`lecturer_id`, `first_name`, `last_name`, `email`, `password`, `department`, `college_id`) VALUES
(1, 'Hamzah', 'Jamel', 'hamzahjamel@gmail.com', '$2y$10$k1YcBWL2hjavoAh7VNOopOUiCRm6DpcOcJFbit0yizGTGK5wsAOJ2', 'Computer Science', 1),
(2, 'naji', 'ali', 'najiali@gmail.com', 'hashed_password2', 'Mechanical Engineering', 1),
(3, 'fatma', 'zaid', 'fatmaziad@gmail.com', 'hashed_password3', 'Physics', 2),
(4, 'Zaynab ', 'Abdo', 'zaynabali@gmail.com', 'hashed_password4', 'Chemistry', 2),
(5, 'Eyman', 'naser', 'eymannaser@gmail.com', 'hashed_password5', 'English Literature', 3),
(6, 'Ramiz ', 'mohammed', 'ramzimohammed@gmail.com', 'hashed_password6', 'Economics', 4),
(7, 'sally', 'salah', 'salahsam@gmail.com', 'hashed_password7', 'Business Administration', 4),
(8, 'fatma', 'Ahmed', 'fatmaah@gmail.com', 'hashed_password8', 'Law', 6),
(9, 'Osama', 'abdo', 'osamaabdoo@gmail.com', 'hashed_password9', 'Medicine', 7),
(10, 'Susan', 'Zaid', 'susanzaid@gmail.com', 'hashed_password10', 'Architecture', 9),
(11, 'Ali', 'Salah', 'alisalah@gmail.com', '$2y$10$a42qztSQeZNjzp8eMAVPSuUREa675BBo6qnvP1BKrqTn.O8hmgCgS', 'Computer Science', 2);

-- --------------------------------------------------------

--
-- Table structure for table `lectures`
--

DROP TABLE IF EXISTS `lectures`;
CREATE TABLE IF NOT EXISTS `lectures` (
  `lecture_id` int NOT NULL AUTO_INCREMENT,
  `schedule_id` int DEFAULT NULL,
  `lecture_date` date NOT NULL,
  `week_number` int NOT NULL,
  `status` enum('Scheduled','Completed','Cancelled') NOT NULL,
  PRIMARY KEY (`lecture_id`),
  KEY `schedule_id` (`schedule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lectures`
--

INSERT INTO `lectures` (`lecture_id`, `schedule_id`, `lecture_date`, `week_number`, `status`) VALUES
(2, 8, '2025-01-16', 2, 'Scheduled'),
(3, 2, '2025-01-05', 1, 'Scheduled'),
(4, 4, '2025-02-11', 1, 'Scheduled'),
(5, 5, '2025-02-19', 1, 'Scheduled'),
(6, 6, '2024-01-08', 2, 'Scheduled'),
(7, 7, '2024-12-24', 2, 'Scheduled'),
(8, 2, '2025-02-09', 2, 'Scheduled');

-- --------------------------------------------------------

--
-- Stand-in structure for view `lo_question_mapping`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `lo_question_mapping`;
CREATE TABLE IF NOT EXISTS `lo_question_mapping` (
`course_id` int
,`learning_outcomes` text
,`question_id` int
,`question_text` text
,`topic_id` int
);

-- --------------------------------------------------------

--
-- Table structure for table `lo_weight`
--

DROP TABLE IF EXISTS `lo_weight`;
CREATE TABLE IF NOT EXISTS `lo_weight` (
  `low_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `lo_symbol` varchar(50) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  PRIMARY KEY (`low_id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lo_weight`
--

INSERT INTO `lo_weight` (`low_id`, `course_id`, `lo_symbol`, `weight`) VALUES
(1, 1, 'a1', 0.04),
(2, 1, 'a10', 0.04),
(3, 1, 'a2', 0.08),
(4, 1, 'a3', 0.08),
(5, 1, 'a4', 0.08),
(6, 1, 'a5', 0.04),
(7, 1, 'a6', 0.04),
(8, 1, 'a7', 0.04),
(9, 1, 'a8', 0.04),
(10, 1, 'a9', 0.04),
(11, 1, 'b1', 0.08),
(12, 1, 'b2', 0.12),
(13, 1, 'b3', 0.12),
(14, 1, 'b4', 0.12),
(15, 1, 'b5', 0.08),
(16, 2, 'a1', 0.05),
(17, 2, 'a10', 0.05),
(18, 2, 'a2', 0.05),
(19, 2, 'a3', 0.05),
(20, 2, 'a4', 0.05),
(21, 2, 'a5', 0.09),
(22, 2, 'a6', 0.05),
(23, 2, 'a7', 0.05),
(24, 2, 'a8', 0.05),
(25, 2, 'a9', 0.05),
(26, 2, 'b1', 0.09),
(27, 2, 'b2', 0.09),
(28, 2, 'b3', 0.09),
(29, 2, 'b4', 0.09),
(30, 2, 'b5', 0.14),
(31, 3, 'a1', 0.04),
(32, 3, 'a10', 0.04),
(33, 3, 'a2', 0.04),
(34, 3, 'a3', 0.04),
(35, 3, 'a4', 0.04),
(36, 3, 'a5', 0.04),
(37, 3, 'a6', 0.08),
(38, 3, 'a7', 0.08),
(39, 3, 'a8', 0.04),
(40, 3, 'a9', 0.04),
(41, 3, 'b1', 0.13),
(42, 3, 'b2', 0.13),
(43, 3, 'b3', 0.08),
(44, 3, 'b4', 0.08),
(45, 3, 'b5', 0.08),
(46, 4, 'a1', 0.04),
(47, 4, 'a10', 0.04),
(48, 4, 'a2', 0.04),
(49, 4, 'a3', 0.04),
(50, 4, 'a4', 0.04),
(51, 4, 'a5', 0.04),
(52, 4, 'a6', 0.04),
(53, 4, 'a7', 0.04),
(54, 4, 'a8', 0.08),
(55, 4, 'a9', 0.08),
(56, 4, 'b1', 0.08),
(57, 4, 'b2', 0.08),
(58, 4, 'b3', 0.13),
(59, 4, 'b4', 0.13),
(60, 4, 'b5', 0.08),
(61, 5, 'a1', 0.08),
(62, 5, 'a10', 0.08),
(63, 5, 'a2', 0.04),
(64, 5, 'a3', 0.04),
(65, 5, 'a4', 0.04),
(66, 5, 'a5', 0.04),
(67, 5, 'a6', 0.04),
(68, 5, 'a7', 0.04),
(69, 5, 'a8', 0.04),
(70, 5, 'a9', 0.04),
(71, 5, 'b1', 0.13),
(72, 5, 'b2', 0.08),
(73, 5, 'b3', 0.08),
(74, 5, 'b4', 0.08),
(75, 5, 'b5', 0.13),
(76, 6, 'a1', 0.04),
(77, 6, 'a10', 0.04),
(78, 6, 'a2', 0.08),
(79, 6, 'a3', 0.08),
(80, 6, 'a4', 0.04),
(81, 6, 'a5', 0.04),
(82, 6, 'a6', 0.04),
(83, 6, 'a7', 0.04),
(84, 6, 'a8', 0.04),
(85, 6, 'a9', 0.04),
(86, 6, 'b1', 0.08),
(87, 6, 'b2', 0.13),
(88, 6, 'b3', 0.13),
(89, 6, 'b4', 0.08),
(90, 6, 'b5', 0.08),
(91, 7, 'a1', 0.04),
(92, 7, 'a10', 0.04),
(93, 7, 'a2', 0.04),
(94, 7, 'a3', 0.04),
(95, 7, 'a4', 0.08),
(96, 7, 'a5', 0.08),
(97, 7, 'a6', 0.04),
(98, 7, 'a7', 0.04),
(99, 7, 'a8', 0.04),
(100, 7, 'a9', 0.04),
(101, 7, 'b1', 0.08),
(102, 7, 'b2', 0.08),
(103, 7, 'b3', 0.08),
(104, 7, 'b4', 0.13),
(105, 7, 'b5', 0.13),
(106, 8, 'a1', 0.04),
(107, 8, 'a10', 0.04),
(108, 8, 'a2', 0.04),
(109, 8, 'a3', 0.04),
(110, 8, 'a4', 0.04),
(111, 8, 'a5', 0.04),
(112, 8, 'a6', 0.08),
(113, 8, 'a7', 0.08),
(114, 8, 'a8', 0.04),
(115, 8, 'a9', 0.04),
(116, 8, 'b1', 0.13),
(117, 8, 'b2', 0.13),
(118, 8, 'b3', 0.08),
(119, 8, 'b4', 0.08),
(120, 8, 'b5', 0.08);

-- --------------------------------------------------------

--
-- Table structure for table `majors`
--

DROP TABLE IF EXISTS `majors`;
CREATE TABLE IF NOT EXISTS `majors` (
  `major_id` int NOT NULL AUTO_INCREMENT,
  `major_name` varchar(100) NOT NULL,
  `college_id` int DEFAULT NULL,
  PRIMARY KEY (`major_id`),
  KEY `college_id` (`college_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `majors`
--

INSERT INTO `majors` (`major_id`, `major_name`, `college_id`) VALUES
(1, 'Computer Science', 1),
(2, 'Mechanical Engineering', 1),
(3, 'Physics', 2),
(4, 'Chemistry', 2),
(5, 'English Literature', 3),
(6, 'Economics', 4),
(7, 'Business Administration', 4),
(8, 'Law', 6),
(9, 'Medicine', 7),
(10, 'Architecture', 9);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `student_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'Hello You Have to Answer Today\'s Questions and Give Your Feedback about The Lectures of Today', 1, '2024-11-30 00:17:11'),
(2, 5, '\'Reminder: Please evaluate today\'s lectures\'', 0, '2024-12-05 02:59:58'),
(3, 2, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 02:59:30'),
(4, 3, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 03:01:04'),
(5, 4, '\'Reminder: Please evaluate today\'s lectures.\'', 1, '2024-12-05 03:01:13'),
(6, 6, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 03:01:23'),
(7, 7, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 03:01:33'),
(8, 8, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 03:01:41'),
(9, 1, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 23:06:27'),
(10, 2, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 23:06:27'),
(11, 3, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 23:06:27'),
(13, 5, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 23:06:27'),
(14, 6, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 23:06:27'),
(15, 7, '\'Reminder: Please evaluate today\'s lectures.\'', 0, '2024-12-05 23:06:27'),
(19, 6, 'Hello, Fiona\r\nYou have to Answer the Questions for today\'s lecture.', 0, '2024-12-21 23:12:24'),
(20, 2, 'Hello, \r\nAnswer Today\'s Questions', 0, '2024-12-22 12:26:56');

-- --------------------------------------------------------

--
-- Table structure for table `plan`
--

DROP TABLE IF EXISTS `plan`;
CREATE TABLE IF NOT EXISTS `plan` (
  `plan_id` int NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(255) NOT NULL,
  `academic_year_id` int DEFAULT NULL,
  `major_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`plan_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `major_id` (`major_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `plan`
--

INSERT INTO `plan` (`plan_id`, `plan_name`, `academic_year_id`, `major_id`, `created_at`) VALUES
(1, 'Computer Science Curriculum Update', 1, 2, '2024-11-14 19:09:13'),
(2, 'Mathematics Syllabus Revision', 2, 3, '2024-11-14 19:09:13'),
(3, 'Engineering Project Development', 3, 4, '2024-11-14 19:09:13'),
(4, 'Business Administration Enhancement', 4, 5, '2024-11-14 19:09:13');

-- --------------------------------------------------------

--
-- Table structure for table `plan_details`
--

DROP TABLE IF EXISTS `plan_details`;
CREATE TABLE IF NOT EXISTS `plan_details` (
  `detail_id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `detail_description` text NOT NULL,
  `step_order` int NOT NULL,
  `expected_completion_date` date DEFAULT NULL,
  PRIMARY KEY (`detail_id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `plan_details`
--

INSERT INTO `plan_details` (`detail_id`, `plan_id`, `detail_description`, `step_order`, `expected_completion_date`) VALUES
(1, 1, 'Revise programming curriculum for Semester 1', 1, '2025-02-15'),
(2, 1, 'Introduce AI modules for Semester 3', 2, '2025-05-01'),
(3, 1, 'Expand lab sessions for advanced topics', 3, '2025-07-10'),
(4, 2, 'Enhance calculus content in Semester 2', 1, '2025-03-20'),
(5, 2, 'Integrate computational tools', 2, '2025-06-10'),
(6, 2, 'Introduce mathematical modeling', 3, '2025-08-05'),
(7, 3, 'Define capstone project topics', 1, '2025-04-15'),
(8, 3, 'Assign mentors', 2, '2025-06-01'),
(9, 3, 'Conduct project reviews', 3, '2025-09-20'),
(10, 4, 'Update marketing strategy content', 1, '2025-01-10'),
(11, 4, 'Incorporate data analytics', 2, '2025-04-05'),
(12, 4, 'Build internship partnerships', 3, '2025-07-15');

-- --------------------------------------------------------

--
-- Table structure for table `quality_reports`
--

DROP TABLE IF EXISTS `quality_reports`;
CREATE TABLE IF NOT EXISTS `quality_reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int DEFAULT NULL,
  `lecturer_id` int DEFAULT NULL,
  `academic_year_id` int DEFAULT NULL,
  `week_id` int DEFAULT NULL,
  `average_rating` decimal(3,2) NOT NULL,
  `report_date` date NOT NULL,
  PRIMARY KEY (`report_id`),
  KEY `course_id` (`course_id`),
  KEY `lecturer_id` (`lecturer_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `week_id` (`week_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quality_reports`
--

INSERT INTO `quality_reports` (`report_id`, `course_id`, `lecturer_id`, `academic_year_id`, `week_id`, `average_rating`, `report_date`) VALUES
(1, 1, 1, 1, 1, 4.50, '2024-01-07'),
(2, 2, 2, 2, 2, 4.30, '2024-01-14'),
(3, 3, 3, 3, 3, 4.60, '2024-01-21'),
(4, 4, 4, 4, 4, 4.20, '2024-01-28'),
(5, 5, 5, 5, 5, 4.70, '2024-02-04'),
(6, 6, 6, 6, 6, 4.40, '2024-02-11'),
(7, 7, 7, 7, 7, 4.80, '2024-02-18'),
(8, 8, 8, 8, 8, 4.10, '2024-02-25');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `question_id` int NOT NULL AUTO_INCREMENT,
  `topic_id` int NOT NULL,
  `lecture_id` int NOT NULL,
  `question_text` text NOT NULL,
  `choice_one` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `choice_two` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `choice_three` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `choice_four` varchar(100) DEFAULT NULL,
  `right_choice` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`question_id`),
  KEY `topic_id` (`topic_id`),
  KEY `lecture_id` (`lecture_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `topic_id`, `lecture_id`, `question_text`, `choice_one`, `choice_two`, `choice_three`, `choice_four`, `right_choice`) VALUES
(1, 1, 2, 'What is the main purpose of a firewall in a computer network?', 'To increase internet speed', 'To prevent unauthorized access', 'To manage power supply', 'To encrypt data', 'To prevent unauthorized access'),
(2, 2, 2, 'Which of the following is a type of database model?', 'Neural network', 'Relational', 'Blockchain', 'Multicast', 'Relational'),
(3, 2, 7, 'What is the principle of \"habeas corpus\"?', 'Right to a fair trial', 'Protection against unlawful imprisonment', 'Freedom of speech', 'Right to own property', 'Protection against unlawful imprisonment'),
(4, 4, 3, 'In contract law, what is \'consideration\'?', 'The legal obligation to act fairly', 'The promise made by one party', 'The price paid for the promise of the other', 'A verbal agreement between parties', 'The price paid for the promise of the other'),
(5, 5, 3, 'What does the term \"Big O\" notation describe?', 'The speed of the CPU', 'The efficiency of an algorithm', 'The size of a database', 'The format of an operating system', 'The efficiency of an algorithm'),
(6, 6, 2, 'Which programming paradigm focuses on objects and classes?', 'Procedural programming', 'Functional programming', 'Object-oriented programming', 'Logic programming', 'Object-oriented programming'),
(7, 7, 4, 'What is the primary function of red blood cells?', 'Fight infections', 'Transport oxygen', 'Coagulate blood', 'Maintain body temperature', 'Transport oxygen'),
(8, 8, 8, 'Which of the following is a viral disease?', 'Tuberculosis', 'Malaria', 'Influenza', 'Diabetes', 'Influenza'),
(9, 9, 4, 'Which law regulates how data is stored and processed in the European Union?', 'GDPR (General Data Protection Regulation)', 'HIPAA (Health Insurance Portability and Accountability Act)', 'DMCA (Digital Millennium Copyright Act)', 'CCPA (California Consumer Privacy Act)', 'GDPR (General Data Protection Regulation)'),
(10, 10, 2, 'What is the main purpose of machine learning in computer science?', 'To manually process large data sets', 'To enable systems to learn and improve from experience', 'To execute repetitive tasks faster', 'To reduce software development time', 'To enable systems to learn and improve from experience'),
(11, 11, 5, 'Which part of the brain is responsible for coordinating balance and movement?', 'Cerebrum', 'Cerebellum', 'Hypothalamus', 'Medulla oblongata', 'Cerebellum'),
(12, 8, 8, 'what is your name?', 'Ali', 'Hamood', 'Khalid', 'Mohammed', 'Mohammed'),
(13, 2, 8, 'What is the primary purpose of lectures?', 'To entertain', 'To educate', 'To confuse', 'To assess', 'To educate'),
(14, 2, 8, 'Which of these is a characteristic of quality education?', 'Irregular attendance', 'Engaged students', 'No feedback', 'No resources', 'Engaged students'),
(17, 7, 4, 'What is your address?', 'Hada St.', 'Al_Zubairi St.', 'Shouap St.', 'Taiz St.', 'Taiz St.'),
(18, 7, 4, 'What is your name?', 'Ali', 'Ali Ali', 'Hassan', 'Osama', 'Osama');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
CREATE TABLE IF NOT EXISTS `schedule` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int DEFAULT NULL,
  `lecturer_id` int DEFAULT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `course_id` (`course_id`),
  KEY `lecturer_id` (`lecturer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`schedule_id`, `course_id`, `lecturer_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(2, 1, 1, 'Sunday', '10:00:00', '12:00:00'),
(3, 3, 3, 'Monday', '08:00:00', '10:00:00'),
(4, 4, 4, 'Tuesday', '10:00:00', '12:00:00'),
(5, 5, 5, 'Wednesday', '09:00:00', '11:00:00'),
(6, 6, 6, 'Saturday', '13:00:00', '15:00:00'),
(7, 7, 7, 'Saturday', '14:00:00', '16:00:00'),
(8, 1, 8, 'Thursday', '13:00:00', '15:00:00'),
(12, 6, 1, 'Sunday', '08:00:00', '10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

DROP TABLE IF EXISTS `semesters`;
CREATE TABLE IF NOT EXISTS `semesters` (
  `semester_id` int NOT NULL AUTO_INCREMENT,
  `semester_name` enum('1','2','3','4','5','6','7','8') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`semester_id`),
  UNIQUE KEY `semester_name` (`semester_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`semester_id`, `semester_name`) VALUES
(1, '1'),
(2, '2'),
(3, '3'),
(4, '4'),
(5, '5'),
(6, '6'),
(7, '7'),
(8, '8');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `major_id` int DEFAULT NULL,
  `academic_year_id` int DEFAULT NULL,
  `college_id` int DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `email` (`email`),
  KEY `major_id` (`major_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `college_id` (`college_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `email`, `password`, `major_id`, `academic_year_id`, `college_id`, `profile_picture`) VALUES
(1, 'Norah', 'Ayuob', 'norah@gmail.com', 'hashed_password11', 1, 1, 1, NULL),
(2, 'Hani ', 'Salah ', 'hanisalah@gmail.com', 'hashed_password12', 2, 2, 1, NULL),
(3, 'Sosan', 'ali ', 'sosana@gmail.com', '1234512345', 3, 3, 2, NULL),
(4, 'Osama', 'Abdo', 'osamaabdo@gmail.com', '123456789987654321', 4, 4, 2, 'profile_pictures/student_1.jpg'),
(5, 'Momean', 'Amer ', 'momeanamer@gmail.com', '123456789', 5, 5, 3, NULL),
(6, 'Fuad', 'Ali', 'alifuad@gmail.com', 'hashed_password16', 6, 6, 4, NULL),
(7, 'Yaseen ', 'Yasser ', 'yassen@gmail.com', 'hashed_password17', 7, 7, 4, NULL),
(8, 'Mohammed', 'Fuad', 'mohammedalsanhani2@gmail.com', 'hashed_password18', 1, 8, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

DROP TABLE IF EXISTS `student_answers`;
CREATE TABLE IF NOT EXISTS `student_answers` (
  `answer_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `week_id` int DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `lecture_id` int DEFAULT NULL,
  `answer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `result` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`answer_id`),
  KEY `student_id` (`student_id`),
  KEY `week_id` (`week_id`),
  KEY `question_id` (`question_id`),
  KEY `lecture_id` (`lecture_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `student_answers`
--

INSERT INTO `student_answers` (`answer_id`, `student_id`, `week_id`, `question_id`, `lecture_id`, `answer`, `result`) VALUES
(38, 4, 10, 11, 5, 'Cerebellum', 1),
(39, 4, 8, 4, 6, 'A verbal agreement between parties', 0),
(42, 2, 4, 2, 7, 'Blockchain', 0),
(43, 1, NULL, 12, NULL, 'Hamood', 0),
(44, 1, NULL, 8, NULL, 'Diabetes', 0),
(45, 1, NULL, 8, 8, 'Influenza', 1),
(46, 1, NULL, 12, 8, 'Mohammed', 1),
(47, 1, NULL, 8, 8, 'Influenza', 1),
(48, 1, NULL, 12, 8, 'Khalid', 0),
(49, 1, NULL, 8, 8, 'Influenza', 1),
(50, 1, NULL, 12, 8, 'Hamood', 0),
(68, 8, NULL, 13, 8, 'To confuse', 0),
(69, 4, NULL, 7, 4, 'Fight infections', 0),
(70, 4, NULL, 9, 4, 'HIPAA (Health Insurance Portability and Accountability Act)', 0),
(71, 4, NULL, 17, 4, 'Shouap St.', 0),
(72, 4, NULL, 18, 4, 'Hassan', 0);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `feedback_deadline` date NOT NULL,
  `quality_threshold` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `feedback_deadline`, `quality_threshold`) VALUES
(1, '2024-12-15', 85.00),
(2, '2025-01-10', 90.00),
(3, '2025-02-05', 75.50),
(4, '2025-03-01', 88.25),
(5, '2025-04-20', 92.00);

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
CREATE TABLE IF NOT EXISTS `topics` (
  `topic_id` int NOT NULL AUTO_INCREMENT,
  `topic_name` varchar(100) NOT NULL,
  `course_id` int DEFAULT NULL,
  `lecture_id` int NOT NULL,
  PRIMARY KEY (`topic_id`),
  KEY `course_id` (`course_id`),
  KEY `lecture_id` (`lecture_id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`topic_id`, `topic_name`, `course_id`, `lecture_id`) VALUES
(1, 'Introduction to Linear and Non-Linear Data Structures', 1, 2),
(2, 'Array Manipulation and Optimization Techniques', 1, 2),
(3, 'Advanced Linked List Operations', 1, 4),
(4, 'Stack and Queue Applications in Real-World Problems', 1, 5),
(5, 'Hashing Techniques and Collision Resolution', 1, 6),
(6, 'Binary Search Trees and AVL Trees', 1, 7),
(7, 'Graph Traversal Algorithms: BFS and DFS', 1, 8),
(8, 'Heap Data Structures for Priority Queues', 1, 8),
(9, 'Trie Structures for Efficient String Searching', 1, 3),
(10, 'Dynamic Programming with Data Structures', 1, 4),
(11, 'Disjoint Set Union (Union-Find) Algorithms', 1, 5),
(12, 'Red-Black and B-Tree Structures', 1, 6),
(13, 'First Law of Thermodynamics and Energy Conservation', 1, 7),
(14, 'Second Law of Thermodynamics and Entropy', 2, 8),
(15, 'Thermodynamic Cycles in Power Generation', 2, 2),
(16, 'Properties of Pure Substances in Thermodynamics', 2, 2),
(17, 'Applications of the Ideal Gas Law', 2, 4),
(18, 'Heat Transfer Mechanisms in Thermodynamic Systems', 2, 5),
(19, 'Entropy Change in Closed and Open Systems', 2, 6),
(20, 'Refrigeration and Heat Pump Systems', 2, 7),
(21, 'Thermodynamic Equilibrium and State Functions', 2, 8),
(22, 'Energy Efficiency in Thermodynamic Systems', 2, 2),
(23, 'Chemical Thermodynamics and Reaction Energy', 2, 3),
(24, 'Exergy Analysis in Thermodynamic Systems', 2, 4),
(25, 'The Principles of Quantum Superposition', 3, 5),
(26, 'Wave-Particle Duality in Quantum Systems', 3, 6),
(27, 'The Schrödinger Equation and Its Applications', 3, 7),
(28, 'Quantum Entanglement and Non-Locality', 3, 8),
(29, 'Heisenberg’s Uncertainty Principle', 3, 2),
(30, 'Quantum Tunneling and Potential Barriers', 3, 3),
(31, 'The Role of Spin in Quantum Mechanics', 3, 4),
(32, 'Quantum States and Hilbert Space', 3, 5),
(33, 'Quantum Measurement Problem and Collapse of the Wavefunction', 3, 6),
(34, 'Applications of Quantum Mechanics in Modern Technology', 3, 7),
(35, 'Interpretations of Quantum Mechanics', 3, 8),
(36, 'The Path Integral Formulation by Richard Feynman', 3, 2),
(37, 'Fundamentals of Hydrocarbon Structures', 4, 3),
(38, 'Reaction Mechanisms of Alkenes and Alkynes', 4, 4),
(39, 'Stereochemistry and Chirality in Organic Molecules', 4, 5),
(40, 'Aromatic Compounds and Electrophilic Substitution', 4, 6),
(41, 'Alcohols, Phenols, and Ethers: Properties and Reactions', 4, 7),
(42, 'Carboxylic Acids and Their Derivatives', 4, 8),
(43, 'Amines: Structure, Properties, and Reactions', 4, 2),
(44, 'Spectroscopic Techniques in Organic Chemistry', 4, 3),
(45, 'Introduction to Organometallic Compounds', 4, 4),
(46, 'Biochemical Significance of Organic Molecules', 4, 5),
(47, 'Organic Polymers: Synthesis and Applications', 4, 6),
(48, 'Green Chemistry: Sustainable Organic Reactions', 4, 7),
(49, 'The Evolution of Modern Poetry', 5, 8),
(50, 'Symbolism in 20th Century Poetry', 5, 2),
(51, 'The Role of Free Verse in Modern Poetry', 5, 4),
(52, 'Modern Poetry and Social Change', 5, 5),
(53, 'Imagery in the Works of T.S. Eliot', 5, 6),
(54, 'Themes of Isolation in Modernist Poetry', 5, 7),
(55, 'Feminism in Sylvia Plath’s Poetry', 5, 8),
(56, 'War and Its Reflections in Modern Poetry', 5, 2),
(57, 'The Influence of Modernist Poets on Contemporary Literature', 5, 3),
(58, 'Experimentation in Modern Poetic Forms', 5, 4),
(59, 'The Connection Between Modern Poetry and Visual Arts', 5, 5),
(60, 'Exploring Identity in Postmodern Poetry', 5, 6),
(61, 'Demand and Supply Analysis', 6, 7),
(62, 'Elasticity of Demand and Supply', 6, 8),
(63, 'Consumer Behavior and Utility Theory', 6, 2),
(64, 'Production and Cost Analysis', 6, 3),
(65, 'Market Structures: Perfect Competition', 6, 4),
(66, 'Monopoly and Market Power', 6, 5),
(67, 'Oligopoly and Game Theory', 6, 6),
(68, 'Monopolistic Competition and Product Differentiation', 6, 7),
(69, 'Factor Markets and Income Distribution', 6, 8),
(70, 'Market Failures and Government Intervention', 6, 2),
(71, 'Public Goods and Externalities', 6, 3),
(72, 'Welfare Economics and Efficiency', 6, 4),
(73, 'Introduction to Management', 7, 5),
(74, 'Planning and Decision Making', 7, 6),
(75, 'Organizational Structure and Design', 7, 7),
(76, 'Leadership in Management', 7, 8),
(77, 'Motivation and Employee Performance', 7, 2),
(78, 'Team Building and Collaboration', 7, 3),
(79, 'Strategic Management and Competitive Advantage', 7, 4),
(80, 'Financial Management and Budgeting', 7, 5),
(81, 'Human Resource Management', 7, 6),
(82, 'Operations Management', 7, 7),
(83, 'Marketing and Business Development', 7, 8),
(84, 'Ethics and Social Responsibility in Management', 7, 2),
(85, 'Introduction to Constitutional Law', 8, 3),
(86, 'The Structure of the U.S. Constitution', 8, 4),
(87, 'Separation of Powers', 8, 5),
(88, 'Judicial Review and the Role of the Courts', 8, 6),
(89, 'Federalism: State vs. Federal Power', 8, 7),
(90, 'Individual Rights and Liberties', 8, 8),
(91, 'Equal Protection and Due Process Clauses', 8, 2),
(92, 'The Commerce Clause', 8, 3),
(93, 'Freedom of Speech and Expression', 8, 4),
(94, 'Religious Freedom in the U.S. Constitution', 8, 5),
(95, 'Voting Rights and Electoral Law', 8, 6),
(96, 'Amendments and Constitutional Change', 8, 7);

-- --------------------------------------------------------

--
-- Table structure for table `weeks`
--

DROP TABLE IF EXISTS `weeks`;
CREATE TABLE IF NOT EXISTS `weeks` (
  `week_id` int NOT NULL AUTO_INCREMENT,
  `academic_year_id` int DEFAULT NULL,
  `week_number` int DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `week_questions_id` int DEFAULT NULL,
  PRIMARY KEY (`week_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `week_questions_id` (`week_questions_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `weeks`
--

INSERT INTO `weeks` (`week_id`, `academic_year_id`, `week_number`, `start_date`, `end_date`, `week_questions_id`) VALUES
(1, 1, 1, '2024-01-01', '2024-01-06', 1),
(2, 1, 2, '2024-01-07', '2024-01-13', 2),
(3, 1, 3, '2024-01-14', '2024-01-20', 12),
(4, 1, 4, '2024-01-21', '2024-01-27', 4),
(5, 1, 5, '2024-01-28', '2024-02-03', 5),
(6, 1, 6, '2024-02-04', '2024-02-10', 6),
(7, 1, 7, '2024-02-11', '2024-02-17', 7),
(8, 1, 8, '2024-02-18', '2024-02-24', 8),
(9, 1, 9, '2024-02-25', '2024-03-02', 9),
(10, 1, 10, '2024-03-03', '2024-03-09', 10),
(11, 1, 11, '2024-03-10', '2024-03-16', 11),
(12, 1, 12, '2024-03-17', '2024-03-23', 1),
(13, 2, 1, '2024-03-30', '2024-04-06', 2),
(14, 2, 2, '2024-04-07', '2024-04-13', 3),
(15, 2, 3, '2024-04-14', '2024-04-20', 4);

-- --------------------------------------------------------

--
-- Structure for view `lo_question_mapping`
--
DROP TABLE IF EXISTS `lo_question_mapping`;

DROP VIEW IF EXISTS `lo_question_mapping`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `lo_question_mapping`  AS SELECT `t`.`course_id` AS `course_id`, `q`.`topic_id` AS `topic_id`, `q`.`question_id` AS `question_id`, group_concat(distinct concat(`lo`.`lo_symbol`,': ',`lo`.`lo_description`) separator '|') AS `learning_outcomes`, `q`.`question_text` AS `question_text` FROM ((`questions` `q` join `topics` `t` on((`q`.`topic_id` = `t`.`topic_id`))) left join `learning_outcome` `lo` on((`t`.`topic_id` = `lo`.`topic_id`))) GROUP BY `t`.`course_id`, `q`.`topic_id`, `q`.`question_id`, `q`.`question_text` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_year`
--
ALTER TABLE `academic_year`
  ADD CONSTRAINT `fk_academic_years_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`semester_id`);

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`lecture_id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`major_id`) REFERENCES `majors` (`major_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_year` (`academic_year_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `courses_ibfk_3` FOREIGN KEY (`college_id`) REFERENCES `college` (`college_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`lecture_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `learning_outcome`
--
ALTER TABLE `learning_outcome`
  ADD CONSTRAINT `learning_outcome_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`topic_id`);

--
-- Constraints for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD CONSTRAINT `lecturers_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `college` (`college_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lectures`
--
ALTER TABLE `lectures`
  ADD CONSTRAINT `lectures_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`schedule_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lo_weight`
--
ALTER TABLE `lo_weight`
  ADD CONSTRAINT `lo_weight_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `majors`
--
ALTER TABLE `majors`
  ADD CONSTRAINT `majors_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `college` (`college_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plan`
--
ALTER TABLE `plan`
  ADD CONSTRAINT `plan_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_year` (`academic_year_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `plan_ibfk_2` FOREIGN KEY (`major_id`) REFERENCES `majors` (`major_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plan_details`
--
ALTER TABLE `plan_details`
  ADD CONSTRAINT `plan_details_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plan` (`plan_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quality_reports`
--
ALTER TABLE `quality_reports`
  ADD CONSTRAINT `quality_reports_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quality_reports_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`lecturer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quality_reports_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_year` (`academic_year_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quality_reports_ibfk_4` FOREIGN KEY (`week_id`) REFERENCES `weeks` (`week_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`lecture_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`topic_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`lecturer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`major_id`) REFERENCES `majors` (`major_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_year` (`academic_year_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `students_ibfk_3` FOREIGN KEY (`college_id`) REFERENCES `college` (`college_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`week_id`) REFERENCES `weeks` (`week_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_3` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_4` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`lecture_id`);

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `topics_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `topics_ibfk_2` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`lecture_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `weeks`
--
ALTER TABLE `weeks`
  ADD CONSTRAINT `weeks_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_year` (`academic_year_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `weeks_ibfk_2` FOREIGN KEY (`week_questions_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
