-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2025 at 05:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sims`
--

-- --------------------------------------------------------

--
-- Table structure for table `counseling_sessions`
--

CREATE TABLE `counseling_sessions` (
  `counseling_id` int(11) NOT NULL,
  `student_full_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `with_violation` tinyint(1) DEFAULT NULL,
  `counselors_id` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `year_and_section` varchar(255) DEFAULT NULL,
  `schedule_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assigned_to` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `paragraph` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counseling_sessions`
--

INSERT INTO `counseling_sessions` (`counseling_id`, `student_full_name`, `phone_number`, `email`, `with_violation`, `counselors_id`, `status`, `details`, `year_and_section`, `schedule_time`, `assigned_to`, `file_name`, `file_path`, `is_archived`, `paragraph`, `timestamp`, `remarks`) VALUES
(282, 'INSORIO REDNEXX N/A', '1231231231', 'rednexx346@gmail.com', 0, 3, 'Scheduled', 'Possessing or using regulated drugs and/or related equipment', '2nd Year C', '2025-03-25 04:00:00', NULL, NULL, NULL, 0, NULL, '2025-03-24 04:29:07', NULL),
(284, 'MENDOZA RONEL AGUADO', '9061745931', 'rnlmndz14@gmail.com', 0, 3, 'Scheduled', 'Taking or withholding property without consent or dealing in stolen items', '4th Year A', '2025-04-08 19:00:00', NULL, NULL, NULL, 0, NULL, '2025-03-24 04:41:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `counselors`
--

CREATE TABLE `counselors` (
  `counselors_id` int(11) NOT NULL,
  `counselors_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counselors`
--

INSERT INTO `counselors` (`counselors_id`, `counselors_name`) VALUES
(3, 'Mr. Leonard V. Paunil'),
(6, 'Mr. Anje Espeleta'),
(20, 'Mr. Ron Erik Frontuna');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `recipient_email`, `recipient_name`, `subject`, `message`, `sent_at`, `status`) VALUES
(1, 'dildoseller503@gmail.com', 'JAMES BRONNY N/A', 'Counseling Session Reminder', 'Dear JAMES BRONNY N/A,\n\nThis is a reminder that you have a scheduled counseling session on 2025-03-19 12:00.\n\nPlease be on time. If you need to reschedule, please contact us as soon as possible.\n\nThank you,\nCounseling Office', '2025-03-18 05:19:43', 'sent'),
(2, 'benavides.robby16@gmail.com', 'ROBBY POGI JOKE', 'Counseling Session Reminder', 'Dear ROBBY POGI JOKE,\n\nThis is a reminder that you have a scheduled counseling session on 2025-03-19 12:00 PM. tangianmo \n\nPlease be on time. If you need to reschedule, please contact us as soon as possible.\n\nThank you,\nCounseling Office', '2025-03-18 05:55:45', 'sent'),
(3, 'rednexx346@gmail.com', 'INSORIO REDNEXX N/A', 'Counseling Session Reminder', 'Dear INSORIO REDNEXX N/A,\n\nThis is a reminder that you have a scheduled counseling session on 2025-03-25 12:00 PM.\n\nPlease be on time. If you need to reschedule, please contact us as soon as possible.\n\nThank you,\nCounseling Office', '2025-03-24 04:33:18', 'sent'),
(4, 'rednexx346@gmail.com', 'INSORIO REDNEXX N/A', 'Counseling Session Reminder', 'Dear INSORIO REDNEXX N/A,\n\nThis is a reminder that you have a scheduled counseling session on 2025-03-25 12:00 PM.\n\nPlease be on time. If you need to reschedule, please contact us as soon as possible.\n\nThank you,\nCounseling Office', '2025-03-24 04:37:00', 'sent'),
(5, 'rednexx346@gmail.com', 'INSORIO REDNEXX N/A', 'Counseling Session Reminder', 'Dear INSORIO REDNEXX N/A,\n\nThis is a reminder that you have a scheduled counseling session on 2025-03-25 12:00 PM.\n\nPlease be on time. If you need to reschedule, please contact us as soon as possible.\n\nThank you,\nCounseling Office', '2025-03-24 04:39:18', 'sent'),
(6, 'rnlmndz14@gmail.com', 'MENDOZA RONEL AGUADO', 'Counseling Session Reminder', 'Dear MENDOZA RONEL AGUADO,\n\nThis is a reminder that you have a scheduled counseling session on 2025-04-09 03:00 AM.\n\nPlease be on time. If you need to reschedule, please contact us as soon as possible.\n\nThank you,\nCounseling Office', '2025-03-24 04:42:00', 'sent');

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`id`, `user_id`, `action`, `timestamp`) VALUES
(34, 21, 'User admin1 deleted student with ID: 30', '2024-06-03 05:12:56'),
(35, 21, 'User admin1 deleted student with ID: 32', '2024-06-03 05:12:58'),
(36, 21, 'User admin1 deleted student with ID: 34', '2024-06-03 05:12:59'),
(37, 21, 'User admin1 deleted student with ID: 35', '2024-06-03 05:13:00'),
(38, 21, 'User admin1 deleted student with ID: 36', '2024-06-03 05:13:02'),
(39, 21, 'User admin1 added a new student with student_no: 1', '2024-06-03 05:13:25'),
(40, 21, 'User admin1 added a new student with student_no: 2', '2024-06-03 05:13:37'),
(41, 21, 'User admin1 added a new student with student_no: 234', '2024-06-03 05:13:51'),
(42, 21, 'User admin1 added a new student with student_no: 3', '2024-06-03 05:14:05'),
(43, 21, 'Updated counseling session for marklester ', '2024-06-03 05:38:10'),
(44, 21, 'User admin1 deleted counseling session for marklester taas (ID: 16)', '2024-06-03 05:38:14'),
(45, 21, 'Updated counseling session for test', '2024-06-03 05:38:21'),
(46, 21, 'Updated counseling session for test', '2024-06-03 05:38:24'),
(47, 21, 'User admin1 added a new counseling schedule for marklester taas', '2024-06-03 05:38:37'),
(48, 21, 'User admin1 added a new student with student_no: 2101-00426', '2024-06-03 05:41:03'),
(49, 21, 'User admin1 added a new counseling schedule for marklester ', '2024-06-03 05:41:56'),
(50, 21, 'User admin1 added a new student with student_no: 2101-1111', '2024-06-03 05:43:25'),
(51, 21, 'User admin1 deleted student with ID: 42', '2024-06-03 05:44:35'),
(52, 21, 'User admin1 added a new student with student_no: 2', '2024-06-03 05:54:30'),
(53, 21, 'User admin1 added a new student with student_no: 3', '2024-06-03 05:54:45'),
(54, 21, 'User admin1 added a new student with student_no: 23', '2024-06-03 05:55:00'),
(55, 21, 'User admin1 added a new student with student_no: 2101-1111', '2024-06-03 05:55:23'),
(56, 21, 'User admin1 added a new student with student_no: 231313', '2024-06-03 05:55:37'),
(57, 21, 'User admin1 added a new student with student_no: 12341', '2024-06-03 05:55:45'),
(58, 21, 'User admin1 added a new student with student_no: 123412', '2024-06-03 05:55:56'),
(59, 21, 'User admin1 added a new student with student_no: 213421412', '2024-06-03 05:56:11'),
(60, 21, 'User admin1 added a new student with student_no: 2134214', '2024-06-03 05:56:21'),
(61, 21, 'User admin1 added a new student with student_no: 43214214', '2024-06-03 05:56:30'),
(62, 21, 'User admin1 added a new student with student_no:  2101-12345', '2024-06-03 06:16:08'),
(63, 21, 'User admin1 added a new student with student_no: 2101-11112234', '2024-06-03 06:32:35'),
(64, 21, 'User admin1 added a new student with student_no: 2101-33333', '2024-06-03 06:37:08'),
(65, 21, 'User admin1 added a new student with student_no: 2101', '2024-06-03 07:03:55'),
(66, 21, 'User admin1 added a new student with student_no: 2101-111122', '2024-06-06 14:23:48'),
(67, 21, 'User admin1 deleted student with ID: 51', '2024-06-07 12:19:21'),
(68, 21, 'User admin1 deleted student with ID: 41', '2024-06-07 12:19:22'),
(69, 21, 'User admin1 deleted student with ID: 43', '2024-06-07 12:19:23'),
(70, 21, 'User admin1 deleted student with ID: 44', '2024-06-07 12:19:25'),
(71, 21, 'User admin1 deleted student with ID: 45', '2024-06-07 12:19:26'),
(72, 21, 'User admin1 deleted student with ID: 46', '2024-06-07 12:19:28'),
(73, 21, 'User admin1 deleted student with ID: 47', '2024-06-07 12:19:29'),
(74, 21, 'User admin1 deleted student with ID: 48', '2024-06-07 12:19:31'),
(75, 21, 'User admin1 deleted student with ID: 49', '2024-06-07 12:19:40'),
(76, 21, 'User admin1 deleted student with ID: 50', '2024-06-07 12:19:42'),
(77, 21, 'User admin1 deleted student with ID: 52', '2024-06-07 12:19:44'),
(78, 21, 'User admin1 deleted student with ID: 53', '2024-06-07 12:19:46'),
(79, 21, 'User admin1 deleted student with ID: 54', '2024-06-07 12:19:47'),
(80, 21, 'User admin1 deleted student with ID: 55', '2024-06-07 12:19:48'),
(81, 21, 'User admin1 deleted student with ID: 56', '2024-06-07 12:19:50'),
(82, 21, 'User admin1 deleted student with ID: 57', '2024-06-07 12:19:51'),
(83, 21, 'User admin1 deleted student with ID: 58', '2024-06-07 12:19:54'),
(84, 21, 'User admin1 deleted student with ID: 59', '2024-06-07 12:19:55'),
(85, 21, 'User admin1 deleted counseling session for Niggaasdawas sad 3213123 (ID: 22)', '2024-06-09 05:38:57'),
(86, 21, 'User admin1 deleted counseling session for Taas Mark Lester LUCBAN (ID: 23)', '2024-06-09 05:38:58'),
(87, 21, 'User admin1 deleted counseling session for Taas Mark Lester LUCBAN (ID: 24)', '2024-06-09 05:39:00'),
(88, 21, 'User admin1 deleted counseling session for marklester  (ID: 21)', '2024-06-09 05:56:45'),
(89, 21, 'User admin1 deleted counseling session for Niggaasdawas sad 3213123 (ID: 25)', '2024-06-09 06:36:37'),
(90, 21, 'Updated counseling session for Niggaasdawas sad 3213123', '2024-06-09 06:42:59'),
(91, 21, 'Updated counseling session for Niggaasdawas sad 3213123', '2024-06-09 06:44:00'),
(92, 21, 'Updated counseling session for Niggaasdawas sad 3213123', '2024-06-09 06:51:09'),
(93, 21, 'Updated counseling session for Niggaasdawas sad 3213123', '2024-06-09 06:54:31'),
(94, 21, 'Updated counseling session for Niggaasdawas sad 3213123', '2024-06-09 06:54:34'),
(95, 21, 'User admin1 deleted counseling session for Niggaasdawas sad 3213123 (ID: 26)', '2024-06-09 06:54:37'),
(96, 21, 'User admin1 deleted counseling session for Niggaasdawas sad 3213123 (ID: 27)', '2024-06-09 07:05:57'),
(97, 21, 'User admin1 deleted counseling session for Taas Mark Lester LUCBAN (ID: 28)', '2024-06-09 07:05:59'),
(98, 21, 'User admin1 deleted counseling session for testing test test (ID: 29)', '2024-06-09 07:06:00'),
(99, 21, 'Updated counseling session for solomon chrizel psdaw', '2024-06-09 11:44:42'),
(100, 21, 'User admin1 deleted counseling session for solomon chrizel psdaw (ID: 31)', '2024-06-09 11:45:35'),
(101, 21, 'Updated counseling session for solomon chrizel psdaw', '2024-06-09 11:46:21'),
(102, 21, 'Updated counseling session for solomon chrizel psdaw', '2024-06-09 11:46:28'),
(103, 21, 'User admin1 deleted counseling session for solomon chrizel psdaw (ID: 33)', '2024-06-09 11:48:06'),
(104, 21, 'User admin1 deleted counseling session for solomon chrizel psdaw (ID: 32)', '2024-06-09 11:48:09'),
(105, 21, 'User admin1 deleted counseling session for Taas Mark Lester LUCBAN (ID: 30)', '2024-06-09 11:48:12'),
(106, 21, 'Updated counseling session for Taas Mark Lester LUCBAN', '2024-06-09 12:45:28'),
(107, 21, 'User admin1 deleted counseling session for testing test test (ID: 35)', '2024-06-09 12:53:29'),
(108, 21, 'User admin1 deleted counseling session for testing test test (ID: 36)', '2024-06-09 13:05:48'),
(109, 21, 'User admin1 deleted counseling session for Taas Mark Lester LUCBAN (ID: 34)', '2024-06-09 13:07:29'),
(110, 21, 'Updated counseling session for testing test test', '2024-06-09 13:08:20'),
(111, 21, 'Updated counseling session for testing test test', '2024-06-10 06:39:08'),
(112, 21, 'Updated counseling session for testing test test', '2024-06-10 06:39:15'),
(113, 21, 'Updated counseling session for testing test test', '2024-06-10 06:42:15'),
(114, 21, 'Updated counseling session for testing test test', '2024-06-10 06:44:11'),
(115, 21, 'Updated counseling session for solomon chrizel psdaw', '2024-06-10 06:44:31'),
(116, 21, 'Updated counseling session for testing test test', '2024-06-10 06:51:33'),
(117, 21, 'Updated counseling session for testing test test', '2024-06-10 06:55:59'),
(118, 21, 'Updated counseling session for testing test test', '2024-06-10 06:59:38'),
(119, 21, 'Updated counseling session for testing test test', '2024-06-10 07:01:19'),
(120, 21, 'Updated counseling session for testing test test', '2024-06-10 07:03:43'),
(121, 21, 'Updated counseling session for testing test test', '2024-06-10 07:07:49'),
(122, 21, 'Updated counseling session for Niggaasdawas sad 3213123', '2024-06-10 07:09:56'),
(123, 21, 'Updated counseling session for Niggaasdawas sad 3213123', '2024-06-10 07:14:49'),
(124, 21, 'User admin1 deleted counseling session for Niggaasdawas sad 3213123 (ID: 40)', '2024-06-10 07:16:35'),
(125, 21, 'Updated counseling session for solomon chrizel psdaw', '2024-06-10 07:17:18'),
(126, 21, 'Updated counseling session for testing test test', '2024-06-10 07:26:22'),
(127, 21, 'Updated counseling session for testing test test', '2024-06-10 07:30:22'),
(128, 21, 'Updated counseling session for testing test test', '2024-06-10 07:30:31'),
(129, 21, 'Updated counseling session for solomon chrizel psdaw', '2024-06-10 07:32:45'),
(130, 21, 'Updated counseling session for testing test test', '2024-06-10 07:32:49'),
(131, 21, 'Updated counseling session for testing test test', '2024-06-10 07:32:55'),
(132, 21, 'Updated counseling session for solomon chrizel psdaw', '2024-06-10 07:35:39'),
(133, 21, 'User admin1 deleted student with ID: 61', '2024-06-11 03:40:47'),
(134, 21, 'User admin1 deleted student with ID: 62', '2024-06-11 03:40:51'),
(135, 21, 'User admin1 deleted student with ID: 63', '2024-06-11 03:40:53'),
(136, 21, 'User admin1 deleted student with ID: 64', '2024-06-11 03:41:02'),
(137, 21, 'Updated counseling session for Qt Test TESTING', '2024-06-11 12:57:07'),
(138, 21, 'User admin1 deleted counseling session for solomon chrizel psdaw (ID: 38)', '2024-06-12 06:34:46'),
(139, 21, 'User admin1 deleted counseling session for testing test test (ID: 37)', '2024-06-12 06:34:48'),
(140, 21, 'User admin1 deleted counseling session for Niggaasdawas sad 3213123 (ID: 39)', '2024-06-12 06:34:49'),
(141, 21, 'User admin1 deleted counseling session for Qt Test TESTING (ID: 41)', '2024-06-12 06:34:51'),
(142, 21, 'User admin1 deleted counseling session for Qt Test TESTING (ID: 42)', '2024-06-12 06:34:52'),
(143, 21, 'Updated counseling session for TEST QWERT QWEERTTYY', '2024-06-12 06:35:03'),
(144, 21, 'Updated counseling session for testing ting testr', '2024-06-13 12:08:19'),
(145, 21, 'Updated counseling session for testing ting testr', '2024-06-13 12:09:50'),
(146, 21, 'Updated counseling session for testing ting testr', '2024-06-13 12:34:14'),
(147, 21, 'User admin1 deleted counseling session for one two three (ID: 44)', '2024-06-14 11:50:16'),
(148, 21, 'User admin1 deleted counseling session for one two three (ID: 45)', '2024-06-14 11:50:23'),
(149, 21, 'User admin1 deleted student with ID: 60', '2024-06-16 07:22:50'),
(150, 21, 'User admin1 deleted student with ID: 65', '2024-06-16 07:22:52'),
(151, 21, 'User admin1 deleted student with ID: 66', '2024-06-16 07:22:53'),
(152, 21, 'User admin1 deleted student with ID: 67', '2024-06-16 07:22:54'),
(153, 21, 'User admin1 deleted student with ID: 68', '2024-06-16 07:22:55'),
(154, 21, 'User admin1 deleted student with ID: 69', '2024-06-16 07:22:57'),
(155, 21, 'User admin1 deleted student with ID: 70', '2024-06-16 07:22:58'),
(156, 21, 'User admin1 deleted counseling session for TEST QWERT QWEERTTYY (ID: 43)', '2024-06-16 07:23:04'),
(157, 21, 'User admin1 deleted counseling session for testing ting testr (ID: 46)', '2024-06-16 07:23:08'),
(158, 21, 'User admin1 deleted counseling session for testing ting testr (ID: 47)', '2024-06-16 07:23:09'),
(159, 21, 'User admin1 deleted counseling session for testing ting testr (ID: 48)', '2024-06-16 07:23:11'),
(160, 21, 'User admin1 deleted counseling session for TEST QWERT QWEERTTYY (ID: 49)', '2024-06-16 07:23:13'),
(161, 21, 'User admin1 deleted counseling session for Niggaasdawas sad 3213123 (ID: 50)', '2024-06-16 07:23:14'),
(162, 21, 'User admin1 deleted student with ID: 71', '2024-06-16 07:30:33'),
(163, 21, 'User admin1 deleted student with ID: 75', '2024-06-16 07:40:19'),
(164, 21, 'User admin1 deleted student with ID: 72', '2024-06-16 07:40:30'),
(165, 21, 'User admin1 deleted student with ID: 73', '2024-06-16 07:40:32'),
(166, 21, 'User admin1 deleted student with ID: 74', '2024-06-16 07:40:34'),
(167, 21, 'User admin1 deleted student with ID: 77', '2024-06-16 08:34:10'),
(168, 21, 'User admin1 deleted student with ID: 76', '2024-06-16 08:34:44'),
(169, 21, 'User admin1 deleted student with ID: 78', '2024-06-16 08:34:46'),
(170, 21, 'User admin1 deleted student with ID: 79', '2024-06-16 08:34:47'),
(171, 21, 'User admin1 deleted student with ID: 81', '2024-06-16 08:40:20'),
(172, 21, 'User admin1 deleted student with ID: 80', '2024-06-16 08:56:12'),
(173, 21, 'Updated counseling session for q w e', '2024-06-16 13:12:45'),
(174, 21, 'User admin1 deleted student with ID: 82', '2024-06-16 13:20:00'),
(175, 21, 'User admin1 deleted counseling session for q w e (ID: 51)', '2024-06-16 13:20:07'),
(176, 21, 'User admin1 deleted counseling session for Nigga Nigga LUCBAN (ID: 52)', '2024-06-16 13:33:10'),
(177, 21, 'User admin1 deleted student with ID: 83', '2024-06-16 13:46:39'),
(178, 21, 'User admin1 deleted student with ID: 84', '2024-06-16 13:46:41'),
(179, 21, 'User admin1 deleted student with ID: 85', '2024-06-16 13:46:42'),
(180, 21, 'User admin1 deleted counseling session for Nigga Nigga LUCBAN (ID: 53)', '2024-06-16 13:46:56'),
(181, 21, 'User admin1 deleted counseling session for mark  lester  luvna (ID: 54)', '2024-06-16 13:46:57'),
(182, 21, 'User admin1 deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 55)', '2024-06-16 13:46:59'),
(183, 21, 'User admin1 deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 56)', '2024-06-16 13:50:55'),
(184, 21, 'User admin1 deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 57)', '2024-06-17 06:23:54'),
(185, 21, 'User admin1 deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 58)', '2024-06-17 06:23:56'),
(186, 21, 'User admin1 deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 59)', '2024-06-17 06:23:57'),
(187, 21, 'User admin1 deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 60)', '2024-06-17 06:25:55'),
(188, 21, 'User admin1 deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 61)', '2024-06-17 12:20:28'),
(189, 29, 'User Staff2 deleted student with ID: 88', '2024-08-03 07:23:01'),
(190, 29, 'User Staff2 deleted student with ID: 93', '2024-08-03 07:38:20'),
(191, 29, 'User Staff2 deleted student with ID: 92', '2024-08-03 07:38:23'),
(192, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 62)', '2024-08-03 07:40:59'),
(193, 27, 'User admincs deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 63)', '2024-08-03 07:41:32'),
(194, 30, 'User Superadmin deleted student with ID: 90', '2024-08-03 07:54:44'),
(195, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 67)', '2024-08-20 05:52:56'),
(196, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 66)', '2024-08-20 05:52:59'),
(197, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 65)', '2024-08-20 05:53:00'),
(198, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 64)', '2024-08-20 05:53:02'),
(199, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 71)', '2024-08-20 06:13:33'),
(200, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 70)', '2024-08-20 06:13:35'),
(201, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 69)', '2024-08-20 06:13:36'),
(202, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 68)', '2024-08-20 06:13:38'),
(203, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 76)', '2024-08-20 06:26:14'),
(204, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 75)', '2024-08-20 06:26:16'),
(205, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 74)', '2024-08-20 06:26:18'),
(206, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 73)', '2024-08-20 06:26:19'),
(207, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 72)', '2024-08-20 06:26:21'),
(208, 30, 'User Superadmin deleted student with ID: 95', '2024-08-20 07:06:30'),
(209, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 78)', '2024-08-20 07:06:38'),
(210, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 77)', '2024-08-20 07:06:40'),
(211, 32, 'User adminpc deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 83)', '2024-08-20 07:57:20'),
(212, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 88)', '2024-08-20 08:18:38'),
(213, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 87)', '2024-08-20 08:18:40'),
(214, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 86)', '2024-08-20 08:18:42'),
(215, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 85)', '2024-08-20 08:18:43'),
(216, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 84)', '2024-08-20 08:18:44'),
(217, 30, 'User Superadmin deleted counseling session for swasd sdwsa wers (ID: 82)', '2024-08-20 08:18:46'),
(218, 30, 'User Superadmin deleted student with ID: 96', '2024-08-20 08:20:05'),
(219, 30, 'User Superadmin deleted student with ID: 94', '2024-08-20 08:20:07'),
(220, 30, 'User Superadmin deleted student with ID: 91', '2024-08-20 08:20:08'),
(221, 30, 'User Superadmin deleted student with ID: 89', '2024-08-20 08:20:10'),
(222, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 89)', '2024-08-20 08:23:15'),
(223, 32, 'User adminpc deleted counseling session for sunog ka  talaga (ID: 93)', '2024-08-20 08:27:50'),
(224, 27, 'User admincs deleted counseling session for sunog ka  talaga (ID: 92)', '2024-08-20 08:28:17'),
(225, 32, 'User adminpc deleted counseling session for alyana marie sunog (ID: 94)', '2024-08-20 08:29:21'),
(226, 32, 'User adminpc deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 90)', '2024-08-20 08:29:24'),
(227, 30, 'User Superadmin deleted counseling session for alyana marie sunog (ID: 91)', '2024-08-20 08:29:35'),
(228, 30, 'User Superadmin deleted student with ID: 97', '2024-08-22 06:48:53'),
(229, 30, 'User Superadmin deleted student with ID: 98', '2024-08-22 06:48:54'),
(230, 30, 'User Superadmin deleted student with ID: 99', '2024-08-22 06:48:56'),
(231, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 95)', '2024-08-22 06:49:02'),
(232, 30, 'User Superadmin deleted counseling session for alyana marie sunog (ID: 96)', '2024-08-22 06:49:03'),
(233, 30, 'User Superadmin deleted counseling session for sunog ka  talaga (ID: 97)', '2024-08-22 06:49:04'),
(234, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 98)', '2024-08-22 07:35:49'),
(235, 30, 'User Superadmin deleted student with ID: 100', '2024-08-22 07:36:05'),
(236, 30, 'User Superadmin deleted 1 violation(s) with IDs: 121', '2024-08-22 07:38:11'),
(237, 30, 'User Superadmin deleted student with ID: 110', '2024-08-22 07:39:41'),
(238, 30, 'User Superadmin deleted student with ID: 112', '2024-08-22 07:49:18'),
(239, 30, 'User Superadmin deleted student with ID: 113', '2024-08-22 07:57:44'),
(240, 30, 'Updated counseling session for TAAS MARKLESTER LUCBAN', '2024-10-16 05:00:47'),
(241, 30, 'User Superadmin updated student with ID: 102', '2024-10-16 06:17:56'),
(242, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 99)', '2024-10-16 06:19:04'),
(243, 30, 'Updated counseling session for GARDIOLA NIXXZEN N/A', '2024-10-16 06:34:52'),
(244, 30, 'Updated counseling session for GARDIOLA NIXXZEN N/A', '2024-10-16 06:36:36'),
(245, 30, 'Updated counseling session for Adoptante Rainny Rose N/A', '2024-10-16 06:38:22'),
(246, 30, 'Updated counseling session for DE JOSE SHAIRA N/A', '2024-10-16 06:47:01'),
(247, 30, 'Updated counseling session for DE JOSE SHAIRA N/A', '2024-10-16 06:47:20'),
(248, 30, 'Updated counseling session for DE JOSE SHAIRA N/A', '2024-10-16 06:48:32'),
(249, 30, 'Updated counseling session for DE JOSE SHAIRA N/A', '2024-10-16 06:48:39'),
(250, 30, 'Updated counseling session for DE JOSE SHAIRA N/A', '2024-10-16 06:49:47'),
(251, 30, 'Updated counseling session for DE JOSE SHAIRA N/A', '2024-10-16 06:50:08'),
(252, 30, 'Updated counseling session for DE JOSE SHAIRA N/A', '2024-10-16 06:50:22'),
(253, 30, 'Updated counseling session for DE JOSE SHAIRA N/A', '2024-10-16 06:54:24'),
(254, 30, 'Updated counseling session for DE JOSE SHAIRA N/A', '2024-10-16 06:54:34'),
(255, 30, 'Updated counseling session for DE JOSE SHAIRA N/A', '2024-10-16 06:59:38'),
(256, 30, 'Updated counseling session for GARDIOLA NIXXZEN N/A', '2024-10-16 11:21:00'),
(257, 30, 'Updated counseling session for GARDIOLA NIXXZEN N/A', '2024-10-16 11:21:22'),
(258, 30, 'Updated counseling session for GARDIOLA NIXXZEN N/A', '2024-10-16 11:21:28'),
(259, 30, 'Updated counseling session for GARDIOLA NIXXZEN N/A', '2024-10-16 11:21:39'),
(260, 30, 'Updated counseling session for GARDIOLA NIXXZEN N/A', '2024-10-16 11:21:46'),
(261, 30, 'Updated counseling session for GARDIOLA NIXXZEN N/A', '2024-10-16 11:21:54'),
(262, 30, 'Updated counseling session for GARDIOLA NIXXZEN N/A', '2024-10-16 11:22:00'),
(263, 30, 'Updated counseling session for Narito Ros N/A', '2024-10-16 11:22:59'),
(264, 30, 'Updated counseling session for Narito Ros N/A', '2024-10-16 11:23:36'),
(265, 30, 'Updated counseling session for TAAS MARKLESTER LUCBAN', '2024-10-16 11:24:40'),
(266, 30, 'Updated counseling session for Narito Ros N/A', '2024-10-18 04:59:56'),
(267, 30, 'Updated counseling session for Narito Ros N/A', '2024-10-18 05:00:14'),
(268, 30, 'Updated counseling session for Narito Ros N/A', '2024-10-18 05:00:28'),
(269, 30, 'Updated counseling session for Narito Ros N/A', '2024-10-18 05:00:33'),
(270, 30, 'User Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 117)', '2024-10-18 05:13:20'),
(271, 30, 'User Superadmin deleted counseling session for Narito Ros N/A (ID: 118)', '2024-10-18 05:13:23'),
(272, 30, 'User Superadmin deleted counseling session for GARDIOLA NIXXZEN N/A (ID: 123)', '2024-10-18 05:13:25'),
(273, 30, 'Updated counseling session for TAAS MARKLESTER LUCBAN', '2024-10-18 05:14:26'),
(274, 30, 'Updated counseling session for TAAS MARKLESTER LUCBAN', '2024-10-18 05:14:54'),
(275, 30, 'Updated counseling session for TAAS MARKLESTER LUCBAN', '2024-10-18 05:15:07'),
(276, 30, 'User Superadmin updated student with ID: 114', '2024-10-18 13:51:28'),
(277, 30, 'User Superadmin deleted student with ID: 121', '2024-10-18 13:54:06'),
(278, 30, 'Updated counseling session for Jayme Joshua David N/A', '2024-10-18 14:09:30'),
(279, 30, 'Updated counseling session for Jayme Joshua David N/A', '2024-10-18 14:09:49'),
(280, 30, 'User Superadmin updated student with ID: 135', '2024-10-18 14:49:15'),
(281, 30, 'User Superadmin updated student with ID: 135', '2024-10-18 14:49:51'),
(282, 30, 'User Superadmin updated student with ID: 135', '2024-10-18 14:53:05'),
(283, 30, 'User Superadmin updated student with ID: 135', '2024-10-18 14:57:29'),
(284, 30, 'User Superadmin updated student with ID: 135', '2024-10-18 15:01:15'),
(285, 30, 'User Superadmin updated student with ID: 135', '2024-10-18 15:11:48'),
(286, 30, 'User Superadmin updated student with ID: 135', '2024-10-18 15:11:51'),
(287, 30, 'User Superadmin updated student with ID: 135', '2024-10-18 15:12:59'),
(288, 30, 'Updated counseling session for Loisa Loisa N/A', '2024-10-19 14:14:18'),
(289, 30, 'Updated counseling session for Loisa Loisa N/A', '2024-10-19 14:14:31'),
(290, 30, 'Updated counseling session for LEXTER JIREH GALIDO', '2024-11-30 07:15:55'),
(291, 30, 'User  Superadmin deleted counseling session for Loisa Loisa N/A (ID: 130)', '2024-12-01 01:43:43'),
(292, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 140)', '2024-12-01 05:35:36'),
(293, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 139)', '2024-12-01 05:35:41'),
(294, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 138)', '2024-12-01 05:35:47'),
(295, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 145)', '2024-12-01 05:49:14'),
(296, 30, 'User Superadmin updated student with ID: 12', '2024-12-03 00:06:32'),
(297, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 148)', '2024-12-03 09:32:02'),
(298, 30, 'User  Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 125)', '2024-12-03 09:32:05'),
(299, 30, 'User  Superadmin deleted counseling session for LEXTER JIREH GALIDO (ID: 132)', '2024-12-03 09:32:07'),
(300, 30, 'User  Superadmin deleted counseling session for MARK RYAN AGUM\'O SARMIENTO (ID: 133)', '2024-12-03 09:32:10'),
(301, 30, 'User  Superadmin deleted counseling session for JOHN KEN GALAY (ID: 134)', '2024-12-03 09:32:13'),
(302, 30, 'User  Superadmin deleted counseling session for WILBERT SAPATIN (ID: 135)', '2024-12-03 09:32:15'),
(303, 30, 'User  Superadmin deleted counseling session for DANIEL SANTILLAR (ID: 136)', '2024-12-03 09:32:17'),
(304, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 137)', '2024-12-03 09:32:20'),
(305, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 141)', '2024-12-03 09:32:22'),
(306, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 142)', '2024-12-03 09:32:24'),
(307, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 143)', '2024-12-03 09:32:26'),
(308, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 144)', '2024-12-03 09:32:28'),
(309, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 146)', '2024-12-03 09:32:30'),
(310, 30, 'User  Superadmin deleted counseling session for HARDY ARANZANSO (ID: 147)', '2024-12-03 09:32:32'),
(311, 30, 'Updated counseling session for TAAS MARKLESTER LUCBAN', '2024-12-03 09:33:33'),
(312, 30, 'Updated counseling session for JUAN DELA CRUZ ', '2024-12-03 09:33:39'),
(313, 30, 'Updated counseling session for TAAS MARKLESTER LUCBAN', '2024-12-03 09:34:03'),
(314, 30, 'User  Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 116)', '2024-12-03 09:34:57'),
(315, 30, 'User  Superadmin deleted counseling session for TAAS MARKLESTER LUCBAN (ID: 126)', '2024-12-03 09:35:00'),
(316, 30, 'Updated counseling session for TAAS MARK LESTER LUCBAN', '2024-12-03 09:38:47'),
(317, 30, 'Updated counseling session for TAAS MARK LESTER LUCBAN', '2024-12-03 09:38:55'),
(318, 30, 'Updated counseling session for TAAS MARK LESTER LUCBAN', '2024-12-03 09:39:15'),
(319, 30, 'Updated counseling session for TAAS MARK LESTER LUCBAN', '2024-12-03 09:43:14'),
(320, 30, 'Updated counseling session for TAAS MARK LESTER LUCBAN', '2024-12-03 09:44:37'),
(321, 30, 'Updated counseling session for TAAS MARK LESTER LUCBAN', '2024-12-03 09:44:52'),
(322, 30, 'Updated counseling session for CREDO KAREN L', '2024-12-03 09:54:27'),
(323, 30, 'Updated counseling session for CREDO KAREN L', '2024-12-03 09:54:39'),
(324, 30, 'Updated counseling session for CAMPO MARY MAE BULAWAN', '2024-12-03 09:55:46'),
(325, 30, 'User Superadmin updated student with ID: 2', '2024-12-09 07:42:25'),
(326, 30, 'User Superadmin updated student with ID: 2', '2024-12-09 07:42:28'),
(327, 30, 'Updated counseling session for HAO ERYLSON SEPLON', '2024-12-11 05:37:22'),
(328, 44, 'Updated counseling session for DE GUIA JAMICA LIZANO', '2024-12-11 05:44:17'),
(329, 30, 'Updated counseling session for HAO ERYLSON SEPLON', '2025-01-10 13:56:30'),
(330, 30, 'Updated counseling session for DE GUIA JAMICA LIZANO', '2025-01-10 13:56:46'),
(331, 30, 'Updated counseling session for MEJORADA JOSEPHINE FLORES', '2025-01-10 13:59:57'),
(332, 44, 'Updated counseling session for MOLINA MARIANN ALMENDRAS', '2025-01-19 06:55:12'),
(333, 30, 'Updated counseling session for TEST', '2025-01-22 05:24:55'),
(334, 30, 'User  Superadmin deleted counseling session for MARK LESTER TAAS (ID: 167)', '2025-01-22 05:25:29'),
(335, 30, 'User  Superadmin deleted counseling session for TEST (ID: 169)', '2025-01-22 05:25:32'),
(336, 30, 'Updated counseling session for CABRERA RUSSEL JOICE ABECIA', '2025-01-22 05:59:02'),
(337, 30, 'Updated counseling session for CABRERA MARK JEFFERSON JORDAN', '2025-01-22 06:17:18'),
(338, 30, 'Updated counseling session for NACION MA. LOVELY DIANE LACRA', '2025-01-22 06:32:03'),
(339, 30, 'Updated counseling session for CABRERA REXIE SHYNE SOLIVEL', '2025-01-22 06:32:36'),
(340, 30, 'Updated counseling session for BABON STEVEN MALDECINO', '2025-02-25 05:24:23'),
(341, 30, 'Updated counseling session for test test N/A', '2025-02-25 05:28:57'),
(342, 30, 'Updated counseling session for LEE JHON MICHAEL LAOYON', '2025-02-25 05:30:14'),
(343, 30, 'Updated counseling session for ABARING JAMICAH MAE ABLANIA', '2025-02-25 05:30:26'),
(344, 30, 'Updated counseling session for MEJORADA JOSEPHINE FLORES', '2025-02-25 05:33:47'),
(345, 30, 'User  Superadmin deleted counseling session for BABON STEVEN MALDECINO (ID: 176)', '2025-02-25 05:33:58'),
(346, 30, 'Updated counseling session for CABRERA REXIE SHYNE SOLIVEL', '2025-02-25 05:34:12'),
(347, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 05:43:49'),
(348, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 05:44:02'),
(349, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 05:45:04'),
(350, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 05:47:58'),
(351, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 05:49:55'),
(352, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 05:50:43'),
(353, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 05:52:49'),
(354, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 05:52:49'),
(355, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 05:53:01'),
(356, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 05:58:16'),
(357, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:00:06'),
(358, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:00:20'),
(359, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:05:38'),
(360, 30, 'Updated counseling session for test test N/A', '2025-03-12 06:07:35'),
(361, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:07:44'),
(362, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:07:52'),
(363, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:09:56'),
(364, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:10:41'),
(365, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:12:19'),
(366, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:12:45'),
(367, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:13:33'),
(368, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:18:23'),
(369, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:19:51'),
(370, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:20:54'),
(371, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:23:23'),
(372, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:25:04'),
(373, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:27:05'),
(374, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:27:55'),
(375, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 06:29:11'),
(376, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:10:36'),
(377, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:13:49'),
(378, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:15:34'),
(379, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:22:33'),
(380, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:24:39'),
(381, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:25:38'),
(382, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:29:03'),
(383, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:29:54'),
(384, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:33:57'),
(385, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:34:05'),
(386, 30, 'Updated counseling session for CABRERA RUSSEL JOICE ABECIA', '2025-03-12 07:40:16'),
(387, 30, 'Updated counseling session for CABRERA RUSSEL JOICE ABECIA', '2025-03-12 07:40:26'),
(388, 30, 'Updated counseling session for BAANG PRENCIS LURIE MEA ARUTA', '2025-03-12 07:41:32'),
(389, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 07:44:12'),
(390, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 08:46:43'),
(391, 30, 'Updated counseling session for ZURITA MICHAELA FAYE BALSAMO', '2025-03-12 09:12:58'),
(392, 30, 'Updated counseling session for BABON STEVEN MALDECINO', '2025-03-12 09:17:08'),
(393, 30, 'Updated counseling session for GALAY JOHN KEN FLORA', '2025-03-12 09:37:41'),
(394, 30, 'Updated counseling session for test test N/A', '2025-03-12 09:40:14'),
(395, 30, 'Updated counseling session for TESTING TESTING N/A', '2025-03-12 09:51:11'),
(396, 30, 'Updated counseling session for TESTING TESTING N/A', '2025-03-13 03:55:42'),
(397, 30, 'Updated counseling session for TESTING TESTING N/A', '2025-03-13 04:23:10'),
(398, 30, 'Updated counseling session for TESTING TESTING N/A', '2025-03-13 04:25:57'),
(399, 30, 'Updated counseling session for TESTING TESTING N/A', '2025-03-13 04:29:02'),
(400, 30, 'Updated counseling session for TESTING TESTING N/A', '2025-03-13 04:35:31'),
(401, 30, 'Updated counseling session for TESTING  LANG  TO', '2025-03-13 05:21:59'),
(402, 30, 'Updated counseling session for TESTING  LANG  TO', '2025-03-13 05:35:12'),
(403, 30, 'Updated counseling session for RENELYN BADING N/A', '2025-03-13 05:45:46'),
(404, 30, 'Updated counseling session for GALAY LANG  N/A', '2025-03-13 07:34:45'),
(405, 30, 'User Superadmin updated student with ID: 2224', '2025-03-17 03:15:13'),
(406, 30, 'Updated counseling session for JAMES BRONNY N/A', '2025-03-18 04:52:46'),
(407, 30, 'Updated counseling session for JAMES BRONNY N/A', '2025-03-18 05:02:23'),
(408, 30, 'Updated counseling session for ROBBY POGI JOKE', '2025-03-18 05:55:26'),
(409, 30, 'User Superadmin updated student with ID: 13', '2025-03-20 03:19:56'),
(410, 30, 'Updated counseling session for ROBBY POGI JOKE', '2025-03-20 03:22:20'),
(411, 30, 'Updated counseling session for ROBBY POGI JOKE', '2025-03-20 03:25:12'),
(412, 30, 'User Superadmin updated student with ID: 13', '2025-03-24 03:30:59'),
(413, 30, 'Updated counseling session for ROBBY POGI JOKE', '2025-03-24 03:32:22'),
(414, 30, 'Updated counseling session for ROBBY POGI JOKE', '2025-03-24 03:32:48'),
(415, 30, 'Updated counseling session for ROBBY POGI JOKE', '2025-03-24 03:49:03'),
(416, 30, 'Updated counseling session for JAMES BRONNY N/A', '2025-03-24 04:00:17'),
(417, 30, 'Updated counseling session for JAMES BRONNY N/A', '2025-03-24 04:06:59'),
(418, 30, 'Updated counseling session for JAMES BRONNY N/A', '2025-03-24 04:08:24'),
(419, 30, 'Updated counseling session for INSORIO REDNEXX N/A', '2025-03-24 04:18:59'),
(420, 30, 'Updated counseling session for INSORIO REDNEXX N/A', '2025-03-24 04:20:27'),
(421, 30, 'Updated counseling session for INSORIO REDNEXX N/A', '2025-03-24 04:22:36'),
(422, 30, 'Updated counseling session for INSORIO REDNEXX N/A', '2025-03-24 04:22:56'),
(423, 30, 'Updated counseling session for INSORIO REDNEXX N/A', '2025-03-24 04:23:59'),
(424, 30, 'Updated counseling session for INSORIO REDNEXX N/A', '2025-03-24 04:28:06'),
(425, 30, 'Updated counseling session for INSORIO REDNEXX N/A', '2025-03-24 04:33:05'),
(426, 30, 'Updated counseling session for MENDOZA RONEL AGUADO', '2025-03-24 04:41:50');

-- --------------------------------------------------------

--
-- Table structure for table `multiple_counseling_sessions`
--

CREATE TABLE `multiple_counseling_sessions` (
  `id` int(11) NOT NULL,
  `student_names` text NOT NULL,
  `year_section` varchar(50) NOT NULL,
  `program` varchar(100) NOT NULL,
  `violation_type` varchar(50) NOT NULL,
  `violation_details` text DEFAULT NULL,
  `assigned_team` varchar(100) DEFAULT NULL,
  `counseling_date` date DEFAULT NULL,
  `status` enum('Scheduled','Ongoing','Completed') DEFAULT 'Scheduled',
  `is_archived` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `multiple_counseling_sessions`
--

INSERT INTO `multiple_counseling_sessions` (`id`, `student_names`, `year_section`, `program`, `violation_type`, `violation_details`, `assigned_team`, `counseling_date`, `status`, `is_archived`, `created_at`, `updated_at`) VALUES
(1, 'sdasdasd, asdasd', '123, 23c', '', 'Major', '<?php\r\nsession_start();\r\nrequire \'dbconfig.php\';\r\n\r\nif ($_SERVER[\"REQUEST_METHOD\"] == \"POST\") {\r\n    $program_ids = $_POST[\'program_id\'];\r\n    $y_and_s = $_POST[\'y_and_s\'];\r\n    $type = $_POST[\'type\'];\r\n    $info = $_POST[\'info\'];\r\n    $student_names = $_POST[\'student_names\'];\r\n    $assigned_to = isset($_POST[\'assigned_to\']) ? $_POST[\'assigned_to\'] : \'\';\r\n\r\n    $pdo = new PDO(\"mysql:host=$host;dbname=$database\", $user, $password);\r\n\r\n    // Insert into multiple_violations table\r\n    $stmt = $pdo->prepare(\"INSERT INTO multiple_violations (program_id, y_and_s, type, info, student_names, assigned_to) VALUES (?, ?, ?, ?, ?, ?)\");\r\n    \r\n    foreach ($program_ids as $program_id) {\r\n        $stmt->execute([$program_id, $y_and_s, $type, $info, $student_names, $assigned_to]);\r\n    }\r\n\r\n    // If it\'s a major violation, also insert into multiple_counseling_sessions\r\n    if ($type === \'Major\') {\r\n        $stmt = $pdo->prepare(\"INSERT INTO multiple_counseling_sessions (student_names, year_section, program, violation_type, violation_details, assigned_team) VALUES (?, ?, ?, ?, ?, ?)\");\r\n        \r\n        // Get program name\r\n        $programStmt = $pdo->prepare(\"SELECT program_name FROM program WHERE program_id = ?\");\r\n        $programStmt->execute([$program_ids[0]]);\r\n        $program = $programStmt->fetchColumn();\r\n\r\n        $stmt->execute([$student_names, $y_and_s, $program, $type, $info, $assigned_to]);\r\n    }\r\n\r\n    $_SESSION[\'success_message\'] = \"Multiple violations added successfully.\";\r\n    header(\"Location: violation.php\");\r\n    exit();\r\n} else {\r\n    $_SESSION[\'error_message\'] = \"Invalid request method.\";\r\n    header(\"Location: add_multiple_violations.php\");\r\n    exit();\r\n}\r\n', 'asdasd', NULL, 'Scheduled', 1, '2024-10-18 06:22:09', '2024-10-18 06:38:43'),
(2, 'asd', 'asd', '', 'Major', 'asd', 'asda', NULL, 'Scheduled', 0, '2024-10-18 06:23:09', '2024-10-18 06:23:09'),
(3, 'asdasdasd', 'asdasdad', 'BSIT, BSHM', 'Major', 'asdasd', 'mr,asdasd, asda,asd,asd', NULL, 'Completed', 0, '2024-10-18 06:24:09', '2024-10-18 06:53:54'),
(6, 'aas, as', '1c', 'BSHM', 'Major', 'a', 'aas\r\na', NULL, 'Scheduled', 1, '2024-10-18 06:31:39', '2024-10-18 06:37:52'),
(7, 'aas, as', '1c', 'BSHM', 'Major', 'a', 'aas\r\na', NULL, 'Scheduled', 1, '2024-10-18 06:31:39', '2024-10-18 06:38:52'),
(8, 'jayme, nesky', '1c, 2c', 'BSIT, BSCS', 'Major', 'suntukan', 'test\r\ntest', NULL, 'Scheduled', 0, '2024-10-18 14:11:27', '2024-10-18 14:11:27'),
(9, 'jayme, nesky', '1c, 2c', 'BSIT, BSCS', 'Major', 'suntukan', 'test\r\ntest', NULL, 'Scheduled', 0, '2024-10-18 14:11:27', '2024-10-18 14:11:27'),
(10, 'asda, asd', '123, 23c', 'BSIT, BSIndT, BSHM, BSCS', 'Major', 'asdasd', 'asdasd', NULL, 'Scheduled', 0, '2024-10-18 14:18:29', '2024-10-18 14:18:29'),
(11, 'asda, asd', '123, 23c', 'BSIT, BSIndT, BSHM, BSCS', 'Major', 'asdasd', 'asdasd', NULL, 'Scheduled', 0, '2024-10-18 14:18:29', '2024-10-18 14:18:29'),
(12, '123123123asasdasd, asdad', '123, 23c', 'BSIndT, BSHM, BSCS', 'Major', '123', '1123', NULL, 'Scheduled', 0, '2024-10-18 14:20:59', '2024-10-18 14:20:59'),
(13, '123123123asasdasd, asdad', '123, 23c', 'BSIndT, BSHM, BSCS', 'Major', '123', '1123', NULL, 'Scheduled', 0, '2024-10-18 14:20:59', '2024-10-18 14:20:59'),
(14, 'test, tsest, test', 't', 'BSIndT, BSHM', 'Major', 'test', 'test', NULL, 'Scheduled', 1, '2024-10-18 14:22:39', '2024-10-18 14:22:56'),
(15, 'test, tsest, test', 't', 'BSIndT, BSHM', 'Major', 'test', 'test', NULL, 'Scheduled', 0, '2024-10-18 14:22:39', '2024-10-18 14:22:39'),
(16, 'asd', '123, 23c', 'BSCS, BSCE', 'Major', 'asd', 'asda', NULL, 'Scheduled', 0, '2024-10-18 14:24:31', '2024-10-18 14:24:31'),
(17, 'asd', '123, 23c', 'BSCS, BSCE', 'Major', 'asd', 'asda', NULL, 'Scheduled', 0, '2024-10-18 14:24:31', '2024-10-18 14:24:31'),
(18, 'a', '123, 23c', 'BSIT, BSIndT', 'Major', 'a', 'a', NULL, 'Scheduled', 0, '2024-10-18 14:40:30', '2024-10-18 14:40:30'),
(19, 'a', '123, 23c', 'BSIT, BSIndT', 'Major', 'a', 'a', NULL, 'Scheduled', 0, '2024-10-18 14:40:30', '2024-10-18 14:40:30'),
(20, 'a', '123, 23c', 'BSIT, BSIndT', 'Major', 'a', 'a', NULL, 'Scheduled', 0, '2024-10-18 14:40:43', '2024-10-18 14:40:43'),
(21, 'a', '123, 23c', 'BSIT, BSIndT', 'Major', 'a', 'a', NULL, 'Scheduled', 0, '2024-10-18 14:40:43', '2024-10-18 14:40:43'),
(22, 'a', '123, 23c', 'BSIT, BSIndT, BSHM, BSCS', 'Major', 'a', 'a', NULL, 'Completed', 0, '2024-10-18 14:45:40', '2024-10-18 14:47:36'),
(23, 'a', '123, 23c', 'BSIT, BSIndT, BSHM, BSCS', 'Major', 'a', 'a', NULL, 'Scheduled', 0, '2024-10-18 14:45:40', '2024-10-18 14:45:40'),
(24, 'asdad123123asdas', '1c, 3c', 'BSIT, BSIndT, BSHM', 'Major', 'asda123123', 'asdadasda\r\nasdasda\r\nasdasd', NULL, 'Scheduled', 0, '2024-10-19 14:18:24', '2024-10-19 14:18:24'),
(25, 'asdad123123asdas', '1c, 3c', 'BSIT, BSIndT, BSHM', 'Major', 'asda123123', 'asdadasda\r\nasdasda\r\nasdasd', NULL, 'Scheduled', 0, '2024-10-19 14:18:24', '2024-10-19 14:18:24');

-- --------------------------------------------------------

--
-- Table structure for table `multiple_violations`
--

CREATE TABLE `multiple_violations` (
  `id` int(11) NOT NULL,
  `program_related` varchar(255) DEFAULT NULL,
  `y_and_s` varchar(50) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `info` text DEFAULT NULL,
  `student_names` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0,
  `assigned_to` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `multiple_violations`
--

INSERT INTO `multiple_violations` (`id`, `program_related`, `y_and_s`, `type`, `info`, `student_names`, `created_at`, `is_archived`, `assigned_to`, `status`) VALUES
(20, '0', '123, 23c', 'Minor', 'asd', 'asdasd, asd', '2024-10-17 15:22:25', 0, NULL, NULL),
(21, '15', '123, 23c', 'Minor', 'asdawa', 'awsd, asssss', '2024-10-17 15:32:05', 0, NULL, NULL),
(22, '0', '123, 23c', 'Minor', 'asda', 'asdawdasdasd, asd', '2024-10-17 15:35:12', 1, NULL, NULL),
(25, 'BSIT, BSIndT, BSHM, BSCS', '123, 23c', 'Minor', 'sdasdas', 'bobo, obo, oboboob', '2024-10-18 05:35:24', 0, NULL, NULL),
(26, 'BSHM, BSCS, BSCE, BSBM', '123, 23c', 'Major', 'asdawasdadad', 'asdawasasd, asdasd, asdasd', '2024-10-18 05:35:59', 0, NULL, NULL),
(27, 'BSIndT', '123, 23c', 'Major', 'test', 'test, testtesttesttesttesttest, test, test', '2024-10-18 05:47:38', 0, NULL, NULL),
(28, 'BSCS', '1c', 'Major', 'ALTER TABLE multiple_violations\r\nADD COLUMN assigned_to VARCHAR(255);\r\n', 'ALTER TABLE multiple_violations, ADD COLUMN assigned_to VARCHAR(255);', '2024-10-18 05:50:15', 0, 'ALTER TABLE multiple_violations\r\nADD COLUMN assigned_to VARCHAR(255);', NULL),
(29, '', '123, 23c', 'Major', '<?php\r\nsession_start();\r\nrequire \'dbconfig.php\';\r\n\r\nif ($_SERVER[\"REQUEST_METHOD\"] == \"POST\") {\r\n    $program_ids = $_POST[\'program_id\'];\r\n    $y_and_s = $_POST[\'y_and_s\'];\r\n    $type = $_POST[\'type\'];\r\n    $info = $_POST[\'info\'];\r\n    $student_names = $_POST[\'student_names\'];\r\n    $assigned_to = isset($_POST[\'assigned_to\']) ? $_POST[\'assigned_to\'] : \'\';\r\n\r\n    $pdo = new PDO(\"mysql:host=$host;dbname=$database\", $user, $password);\r\n\r\n    // Insert into multiple_violations table\r\n    $stmt = $pdo->prepare(\"INSERT INTO multiple_violations (program_id, y_and_s, type, info, student_names, assigned_to) VALUES (?, ?, ?, ?, ?, ?)\");\r\n    \r\n    foreach ($program_ids as $program_id) {\r\n        $stmt->execute([$program_id, $y_and_s, $type, $info, $student_names, $assigned_to]);\r\n    }\r\n\r\n    // If it\'s a major violation, also insert into multiple_counseling_sessions\r\n    if ($type === \'Major\') {\r\n        $stmt = $pdo->prepare(\"INSERT INTO multiple_counseling_sessions (student_names, year_section, program, violation_type, violation_details, assigned_team) VALUES (?, ?, ?, ?, ?, ?)\");\r\n        \r\n        // Get program name\r\n        $programStmt = $pdo->prepare(\"SELECT program_name FROM program WHERE program_id = ?\");\r\n        $programStmt->execute([$program_ids[0]]);\r\n        $program = $programStmt->fetchColumn();\r\n\r\n        $stmt->execute([$student_names, $y_and_s, $program, $type, $info, $assigned_to]);\r\n    }\r\n\r\n    $_SESSION[\'success_message\'] = \"Multiple violations added successfully.\";\r\n    header(\"Location: violation.php\");\r\n    exit();\r\n} else {\r\n    $_SESSION[\'error_message\'] = \"Invalid request method.\";\r\n    header(\"Location: add_multiple_violations.php\");\r\n    exit();\r\n}\r\n', 'sdasdasd, asdasd', '2024-10-18 06:22:09', 0, 'asdasd', NULL),
(30, '', 'asd', 'Major', 'asd', 'asd', '2024-10-18 06:23:09', 0, 'asda', NULL),
(31, 'BSIT, BSHM', 'asdasdad', 'Major', 'asdasd', 'asdasdasd', '2024-10-18 06:24:09', 0, 'asdasdad', NULL),
(33, 'BSIT', '1c', 'Minor', 'a', 'a', '2024-10-18 06:27:52', 0, '', NULL),
(34, 'BSIT', '1c', 'Minor', 'a', 'a', '2024-10-18 06:28:05', 0, '', NULL),
(35, 'BSHM', '1c', 'Major', 'a', 'aas, as', '2024-10-18 06:31:39', 1, 'aas\r\na', NULL),
(36, 'BSIT, BSCS', '1c, 2c', 'Major', 'suntukan', 'jayme, nesky', '2024-10-18 14:11:27', 0, 'test\r\ntest', NULL),
(37, 'BSIT, BSIndT, BSHM, BSCS', '123, 23c', 'Major', 'asdasd', 'asda, asd', '2024-10-18 14:18:29', 0, 'asdasd', NULL),
(38, 'BSIndT, BSHM, BSCS', '123, 23c', 'Major', '123', '123123123asasdasd, asdad', '2024-10-18 14:20:59', 0, '1123', NULL),
(39, 'BSIndT, BSHM', 't', 'Major', 'test', 'test, tsest, test', '2024-10-18 14:22:39', 0, 'test', NULL),
(40, 'BSCS, BSCE', '123, 23c', 'Major', 'asd', 'asd', '2024-10-18 14:24:31', 0, 'asda', NULL),
(41, 'BSIT, BSIndT', '123, 23c', 'Major', 'a', 'a', '2024-10-18 14:40:30', 0, 'a', NULL),
(42, 'BSIT, BSIndT', '123, 23c', 'Major', 'a', 'a', '2024-10-18 14:40:43', 0, 'a', NULL),
(43, 'BSIT, BSIndT, BSHM, BSCS', '123, 23c', 'Major', 'a', 'a', '2024-10-18 14:45:40', 0, 'a', 'Completed'),
(44, 'BSIT, BSIndT, BSHM', '1c, 3c', 'Major', 'asda123123', 'asdad123123asdas', '2024-10-19 14:18:24', 0, 'asdadasda\r\nasdasda\r\nasdasd', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `program`
--

CREATE TABLE `program` (
  `program_id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program`
--

INSERT INTO `program` (`program_id`, `program_name`) VALUES
(12, 'BSIT'),
(13, 'BSIndT'),
(14, 'BSHM'),
(15, 'BSCS'),
(16, 'BSCE'),
(17, 'BSBM'),
(18, 'BSED'),
(19, 'BSBM HRM'),
(20, 'BSBM MM'),
(21, 'BSE ENG'),
(22, 'BSE MATH'),
(23, 'BSE SCI'),
(24, 'BSBA HRM'),
(25, 'BSBA MM'),
(26, 'BSCPE');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'system_description', 'Truth\nExcellence\nService'),
(2, 'vision_statement', 'The premier university in historic Cavite globally recognized for excellence in character development, academics, research, innovation and sustainable community engagement. '),
(3, 'mission_statement', 'Cavite State University shall provide excellent, equitable and relevant educational opportunities in the arts, sciences and technology through quality instruction and responsive research and development activities. It shall produce professional, skilled and morally upright individuals for global competitiveness. '),
(4, 'quality_policy', 'We Commit to the highest standards of education, value our stakeholders, Strive for continual improvement of our products and services, and Uphold the University’s tenets of Truth, Excellence, and Service to produce globally competitive and morally upright individuals.\n\n'),
(7, 'services_paragraph', 'Discover a range of services designed to support your academic journey and personal growth. At Cavite State University - Carmona Campus, our comprehensive services include career guidance, health seminars, counseling, and more to help you succeed and thrive. '),
(8, 'career_services_paragraph', 'Offering personalized career guidance, job placement assistance, and professional development. We help students navigate their career path and achieve their goals.  '),
(9, 'job_fair_paragraph', 'Connecting students with top employers through networking opportunities. Our Job Fair helps students explore careers, engage with industry leaders, and secure internships and job placements. '),
(10, 'counseling_paragraph', 'Offering confidential support to help students manage personal challenges and succeed academically. Our services provide a safe space for discussing concerns and accessing mental health resources. '),
(11, 'student_participation_paragraph', 'Encouraging campus engagement through diverse activities and leadership opportunities. We foster personal development, teamwork, and a vibrant student community. '),
(12, 'high_passing_rate_paragraph', 'Showcasing our commitment to academic excellence with a consistently high passing rate. Our rigorous programs and supportive environment ensure students are well-prepared for exams and professional success. '),
(13, 'gender_development_paragraph', 'Promoting equality and empowering students with gender sensitivity programs. Our services foster an inclusive environment where all students can thrive and contribute to a diverse community. '),
(14, 'health_seminars_paragraph', 'Offering vital health education and resources to support student well-being. Our seminars provide essential information on physical and mental health, promoting a balanced lifestyle and addressing health concerns. ');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_no` varchar(255) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `surname` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `program_id` int(255) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `status` enum('Enrolled','Graduate','Not Enrolled') DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_no`, `gender`, `birthdate`, `phone_number`, `email`, `surname`, `first_name`, `middle_name`, `program_id`, `year_level`, `status`, `is_archived`, `created_at`) VALUES
(1, '230100049', 'FEMALE', '2004-09-27', '9457662935', 'marklestertaas1@gmail.com', 'ABARCA', 'ANGELINE NICOLE', 'PADUA', 14, '2', 'Enrolled', 0, '2025-03-18 03:57:13'),
(2, '240100280', 'FEMALE', '2006-08-16', '9926276517', NULL, 'ABARING', 'JAMICAH MAE', 'ABLANIA', 21, '1', 'Enrolled', 0, '2025-03-18 03:57:13'),
(3, '230100655', 'FEMALE', '2005-05-27', '9661327617', NULL, 'BAANG', 'PRENCIS LURIE MEA', 'ARUTA', 24, '2', 'Enrolled', 0, '2025-03-18 03:57:13'),
(4, '240101313', 'MALE', '2005-01-02', '9639472175', NULL, 'BABON', 'STEVEN', 'MALDECINO', 14, '1', 'Enrolled', 0, '2025-03-18 03:57:13'),
(5, '220100397', 'MALE', '2001-01-01', 'N/A', NULL, 'CABRERA', 'MARK JEFFERSON', 'JORDAN', 12, '3', 'Enrolled', 0, '2025-03-18 03:57:13'),
(6, '240100128', 'FEMALE', '2005-02-12', '9637949643', NULL, 'CABRERA', 'REXIE SHYNE', 'SOLIVEL', 25, '1', 'Enrolled', 0, '2025-03-18 03:57:13'),
(7, '220100156', 'MALE', '2003-07-29', 'N/A', NULL, 'CABRERA', 'RONALD JR.', 'LIGAD', 26, '3', 'Enrolled', 0, '2025-03-18 03:57:13'),
(8, '230100044', 'FEMALE', '2005-02-16', '9380853100', NULL, 'CABRERA', 'RUSSEL JOICE', 'ABECIA', 21, '2', 'Enrolled', 0, '2025-03-18 03:57:13'),
(9, '220100029', 'MALE', '2002-10-19', 'N/A', NULL, 'DURUMPILI', 'LIONEL', 'CEREZO', 25, '2', 'Enrolled', 0, '2025-03-18 03:57:13'),
(10, '210100840', 'MALE', '2001-01-01', 'N/A', NULL, 'GALAY', 'JOHN KEN', 'FLORA', 12, '4', 'Enrolled', 0, '2025-03-18 03:57:13'),
(12, '213000000', 'Male', '2025-04-14', '9117736362', 'dildoseller503@gmail.com', 'JAMES', 'BRONNY', 'N/A', 12, '1', 'Enrolled', 0, '2025-03-18 04:18:44'),
(13, '213000044', 'Male', '2025-02-25', '1231231231', 'test1@gmail.com', 'ROBBY', 'POGI', 'JOKE', 24, '2', 'Enrolled', 0, '2025-03-18 05:54:53'),
(14, '210100559', 'MALE', '2002-02-12', '1231231231', 'rednexx346@gmail.com', 'INSORIO', 'REDNEXX', 'N/A', 12, '4', 'Enrolled', 0, '2025-03-24 04:17:41'),
(15, '210100480', 'MALE', '2002-12-30', '9061745931', 'rnlmndz14@gmail.com', 'MENDOZA', 'RONEL', 'AGUADO', 12, '4', 'Enrolled', 0, '2025-03-24 04:41:05');

-- --------------------------------------------------------

--
-- Table structure for table `typeofviolation`
--

CREATE TABLE `typeofviolation` (
  `id` int(11) NOT NULL,
  `violation_type` enum('minor','major') NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `typeofviolation`
--

INSERT INTO `typeofviolation` (`id`, `violation_type`, `description`) VALUES
(8, 'minor', 'Non-wearing of official ID'),
(9, 'minor', 'Use another ID or allowing the use of ID by another'),
(10, 'minor', 'Using phone during classes'),
(11, 'minor', 'Using fictitious name'),
(12, 'minor', 'Smoking within the campus'),
(13, 'minor', 'Cursing / using foul languages'),
(14, 'minor', 'Indecent acts or exposure (ex. torrid kissing)'),
(16, 'minor', 'Attempting or having sexual intercourse with another person (using force or intimidation)'),
(23, 'minor', 'Cheating in any test or examination'),
(25, 'minor', 'Unauthorized connecting or disconnecting electric wires plumbing or damaging University walls or fences'),
(27, 'minor', 'Urinating or defecating outside comfort rooms or causing disturbances on campus'),
(30, 'minor', 'Littering or scattering trash within the campus'),
(31, 'minor', 'Violation of traffic and other posted signs and/or rules and regulations'),
(33, 'major', 'Possessing or using regulated drugs and/or related equipment'),
(34, 'major', 'Damaging property on campus whether intentional or not'),
(35, 'major', 'Bribing a person in authority or students to influence actions or duties'),
(36, 'major', 'Disrupting or inciting others to disturb peace and order on campus'),
(37, 'major', 'Erasing or altering figures letters words or signs'),
(38, 'major', 'Taking or withholding property without consent or dealing in stolen items'),
(39, 'major', 'Participating in any game or scheme involving stakes of money valuables or their equivalents'),
(40, 'minor', 'Participating in any game or scheme involving stakes of money valuables or their equivalents'),
(41, 'major', 'Fighting / rioting or resorting to physical force or violence'),
(42, 'major', 'Beverages within the University campus'),
(43, 'major', 'Physical imposition of sexual desire upon another person'),
(44, 'major', 'Indecent acts or exposure (ex. torrid kissing)'),
(45, 'major', 'Offering or selling of regulated drugs and/or paraphernalia'),
(46, 'major', 'Unauthorized possession of firearms'),
(47, 'major', 'Using deadly weapons or illegal firearms to harm other persons within the campus'),
(48, 'minor', 'Unauthorized keeping of pets livestock fowls fish or other animals on campus'),
(49, 'major', 'Unauthorized access or manipulation of computer files programs or systems'),
(50, 'major', 'Non-compliance with the terms of an \"Amicable Settlement\"'),
(51, 'major', 'Being accused in a criminal case in court'),
(52, 'major', 'Unauthorized assembly of at least five (5) members/students without permission from the Office of Student Affairs (OSAS) or other higher authorities'),
(53, 'minor', 'Violation of curfew hours from 9 PM to 5 AM the following day'),
(54, 'major', 'Violation of curfew hours from 9 PM to 5 AM the following day'),
(55, 'major', 'Trespassing through school premises'),
(56, 'major', 'Serious physical injury to another student on campus causing loss of a body part impairment or long-term incapacity'),
(57, 'major', 'Less serious physical injury to another student causing inability to attend classes for 10 to 30 days or requiring medical attention for that period'),
(58, 'major', 'Slight physical injury causing inability to attend classes for 1 to 9 days or requiring medical attention during that period'),
(59, 'minor', 'Unauthorized handling or distribution of seditious subversive or libelous materials within or outside University premises (Art. 139 Revised Penal Code PD 885 Art. 355 RA 4200)'),
(60, 'major', 'Unauthorized handling or distribution of seditious subversive or libelous materials within or outside University premises (Art. 139 Revised Penal Code PD 885 Art. 355 RA 4200)'),
(61, 'major', 'Plagiarism including copying / stealing / illegal use / or breach of copyright'),
(62, 'minor', 'Fabrication of data: thesis / case study / field study / entrepreneurial report / narrative report / dissertation'),
(63, 'major', 'Fabrication of data: thesis / case study / field study / entrepreneurial report / narrative report / dissertation'),
(64, 'major', 'Fabrication of data when done on any official document or document issued by a person in authority');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','staff','admin_cs','admin_csd','admin_pc') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(29, 'Staff2', 'sadwasd@Qgmail.com', '$2y$10$.UUcPdlsYZehLBuWh9YhfuRB1FA3ZDJtf3GQ0F9vV6YgI8AHSYdoC', 'staff', '2024-07-16 07:41:30'),
(30, 'Superadmin', 'test@gmail.com', '$2y$10$Hrpi3riyrWdTkX8JRPu2ceQqEvs2zcmJhTGyXhDgL4LOvBD3psAnK', 'superadmin', '2024-07-16 07:41:47'),
(31, 'Superadmintest', 'marklestertaas1@gmail.com', '$2y$10$Juh/brYcM5NPGQHXZw78Pe5PRzx/xqOoGx2MK/Q9.DDOZw57KORYG', 'superadmin', '2024-08-02 06:30:37'),
(32, 'adminpc', 'marklessdasdi@gmail.com', '$2y$10$qjseYuwqK8syfI4EI5Vv2OyWm36HLObWcYCt7WLpudhJic5.XGnn6', 'admin_pc', '2024-08-20 06:45:18'),
(42, 'test123', 'lebmacalintal1997@gmail.com', '$2y$10$ntcQaEheqpxywEZ0D4JE2.H8cU/TDG.ovaHFOixycDs2T/t3F0bS2', 'superadmin', '2024-12-01 07:01:22'),
(44, 'admincsd', 'testing123@gmail.com', '$2y$10$sWiix5vch2Ar55YfbaeRxOsXxqffMSUZazuf8WF1tvLbeekGkaUTu', 'admin_csd', '2024-12-11 05:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `violations`
--

CREATE TABLE `violations` (
  `id` int(11) NOT NULL,
  `student_no` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `year_and_section` varchar(255) NOT NULL,
  `program_id` int(11) DEFAULT NULL,
  `type_of_violation` varchar(255) NOT NULL,
  `full_info` text DEFAULT NULL,
  `offense_count` varchar(255) DEFAULT NULL,
  `case_offense` text DEFAULT NULL,
  `action_perform` text DEFAULT NULL,
  `status` enum('Ongoing','Scheduled','Completed') DEFAULT 'Ongoing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0,
  `cleared_date` date DEFAULT NULL,
  `ongoing_timestamp` timestamp NULL DEFAULT NULL,
  `scheduled_timestamp` timestamp NULL DEFAULT NULL,
  `completed_timestamp` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `violations`
--

INSERT INTO `violations` (`id`, `student_no`, `full_name`, `phone_number`, `email`, `year_and_section`, `program_id`, `type_of_violation`, `full_info`, `offense_count`, `case_offense`, `action_perform`, `status`, `created_at`, `updated_at`, `is_archived`, `cleared_date`, `ongoing_timestamp`, `scheduled_timestamp`, `completed_timestamp`) VALUES
(284, '210100559', 'INSORIO REDNEXX N/A', '1231231231', 'rednexx346@gmail.com', '2nd Year C', 12, 'major', 'Possessing or using regulated drugs and/or related equipment', '14', 'test', 'test', 'Scheduled', '2025-03-24 04:29:06', '2025-03-24 04:33:05', 0, NULL, NULL, NULL, NULL),
(285, '210100480', 'MENDOZA RONEL AGUADO', '9061745931', 'rnlmndz14@gmail.com', '4th Year A', 12, 'major', 'Taking or withholding property without consent or dealing in stolen items', '19', 'pogi', 'pogi', 'Scheduled', '2025-03-24 04:41:33', '2025-03-24 04:41:50', 0, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  ADD PRIMARY KEY (`counseling_id`);

--
-- Indexes for table `counselors`
--
ALTER TABLE `counselors`
  ADD PRIMARY KEY (`counselors_id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `multiple_counseling_sessions`
--
ALTER TABLE `multiple_counseling_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `multiple_violations`
--
ALTER TABLE `multiple_violations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`program_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `idx_student_no` (`student_no`),
  ADD KEY `fk_program` (`program_id`);

--
-- Indexes for table `typeofviolation`
--
ALTER TABLE `typeofviolation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `violations`
--
ALTER TABLE `violations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_violatios` (`program_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  MODIFY `counseling_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=285;

--
-- AUTO_INCREMENT for table `counselors`
--
ALTER TABLE `counselors`
  MODIFY `counselors_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=427;

--
-- AUTO_INCREMENT for table `multiple_counseling_sessions`
--
ALTER TABLE `multiple_counseling_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `multiple_violations`
--
ALTER TABLE `multiple_violations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `typeofviolation`
--
ALTER TABLE `typeofviolation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `violations`
--
ALTER TABLE `violations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=286;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_program` FOREIGN KEY (`program_id`) REFERENCES `program` (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `violations`
--
ALTER TABLE `violations`
  ADD CONSTRAINT `fk_violatios` FOREIGN KEY (`program_id`) REFERENCES `program` (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
