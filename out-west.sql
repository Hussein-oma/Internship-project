-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 07, 2025 at 09:43 AM
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
-- Database: `out-west`
--

-- --------------------------------------------------------

--
-- Table structure for table `application_dates`
--

CREATE TABLE `application_dates` (
  `id` int(11) NOT NULL,
  `status` enum('open','closed') NOT NULL,
  `open_date` date DEFAULT NULL,
  `close_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application_dates`
--

INSERT INTO `application_dates` (`id`, `status`, `open_date`, `close_date`) VALUES
(1, 'open', '2025-08-01', '2025-08-29');

-- --------------------------------------------------------

--
-- Table structure for table `internship_applications`
--

CREATE TABLE `internship_applications` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `institution` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `level` varchar(20) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `graduation` varchar(10) DEFAULT NULL,
  `department` text DEFAULT NULL,
  `other_department` varchar(100) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `duration_other` varchar(50) DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `accommodation` varchar(5) DEFAULT NULL,
  `paid` varchar(5) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `exp_duration` varchar(50) DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Pending',
  `cv_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internship_applications`
--

INSERT INTO `internship_applications` (`id`, `fullname`, `dob`, `phone`, `email`, `gender`, `nationality`, `institution`, `course`, `level`, `year`, `graduation`, `department`, `other_department`, `duration`, `duration_other`, `startdate`, `accommodation`, `paid`, `amount`, `skills`, `company`, `role`, `exp_duration`, `responsibilities`, `submitted_at`, `status`, `cv_file`) VALUES
(1, 'Hussein Adan', '1970-01-01', '0729779365', 'husseinadanomar18@gmail.com', 'Male', 'kenya', 'JKUAT', 'Bsc. Business Computing', 'Bachelor', '3rd', '2026', 'Software Development', '', '3', '', '1970-01-01', 'No', 'Yes', 45000.00, 'software development', 'Hallo Hallo Communication', 'it staff', '1year', 'user support', '2025-06-15 09:34:42', 'Pending', NULL),
(2, 'Stephen kariuki', '1970-01-01', '0729779041', 'steph1@gmal.com', 'Male', 'kenya', 'JKUAT', 'COMPUTER TECHNOLOGY', 'Bachelor', '3rd', '2026', 'Networking', '', '3', '', '1970-01-01', 'No', 'Yes', 10000.00, 'data engineering', 'out-west ltd', 'itstaff', '1year', '', '2025-06-15 11:06:22', 'Approved', NULL),
(5, 'Benedict Nderitu', '1970-01-01', '07144256327', 'bng@gmail.com', 'Male', 'kenya', 'jkuat', 'IT', 'Masters', '2nd', '2026', 'Software Development', '', '3', '', '1970-01-01', 'Yes', 'Yes', 50.00, 'all programming languages', '', '', '', '', '2025-06-16 09:43:47', 'approved', NULL),
(7, 'Amran adan', '1970-01-01', '0729779365', 'adanhalima970@gmail.com', 'Female', 'kenya', 'JKUAT', 'INFORMATION TECHNOLOGY', 'Diploma', '1yr', '2027', 'System Admin, Data Analysis', '', '4', '', '1970-01-01', 'No', 'Yes', 20000.00, 'software develop', 'Hallo Hallo Communication', 'itstaff', '1year', 'data analysis', '2025-06-16 12:02:56', 'approved', NULL),
(8, 'Abdallah stephen', '1970-01-01', '0729779365', 'abdallahsteve@gmail.com', 'Male', 'kenya', 'JKUAT', 'INFORMATION TECHNOLOGY', 'Masters', '5', '2029', 'Software Development', '', '2', '', '1970-01-01', 'No', 'Yes', 9000.00, 'football\r\nmanagerial\r\neating competition winner\r\nwalking race certificate', 'Njoroges garage', 'mechanics', '1year', '', '2025-06-18 10:03:38', 'approved', NULL),
(9, 'Mzee mwangi', '1998-12-10', '0110465350', 'hiram.karogo@stuents.jkuat.ac.ke', 'Male', 'kenyan', 'JKUAT', 'Bsc. Business Computing', 'Degree', '3rd', '2026', 'Software Development', '', '2', '', '1970-01-01', 'No', 'Yes', 100000.00, 'Programming', '', '', '', '', '2025-06-24 11:52:53', 'approved', NULL),
(10, 'Joseph Kamau', '1970-01-01', '0734436647', 'joseph@gmail.com', 'Male', 'kenyan', 'MKU', 'Computer science', 'Degree', '4th year', '2025', 'System Admin', '', '3', '', '1970-01-01', 'No', 'Yes', 25000.00, 'system analyst', 'SAFARICOM', 'analyst', '2 years', '', '2025-06-26 19:45:06', 'approved', NULL),
(11, 'Farid Al-haifi', '2003-07-01', '0769949164', 'faridawdhat@gmail.com', 'Male', 'Yemenese', 'JKUAT', 'COMPUTER TECHNOLOGY', 'Degree', '3rd', '2026', '', '', '2', '', '1970-01-01', 'Yes', 'Yes', 0.00, 'figma', 'Hallo Hallo Communication', 'itstaff', '1year', 'tech management', '2025-06-29 14:14:14', 'approved', NULL),
(12, 'Hadja Adan', '2006-05-16', '0790233295', 'haskidehuska@gmail.com', 'Female', 'Kenya', 'Isiolo ltd', 'COMPUTER TECHNOLOGY', 'Degree', 'First', '2029', 'System Admin', '', '3', '', '2025-07-14', 'Yes', 'Yes', 10000.00, 'programming', 'isiolo ltd', 'it staff', '1year', '', '2025-07-16 08:32:05', 'approved', 'uploads/cv/cv_68776385a518d1.73139322.docx'),
(13, 'Daniel wekesa', '2004-01-13', '0729779041', 'haskidehuska20@gmail.com', 'Male', 'Kenya', 'UON', 'Computer science', 'Degree', 'Second', '2027', 'Software Development', '', '3', '', '2025-07-29', 'Yes', 'Yes', 10000.00, 'Coding', 'bamburi ltd', 'analyst', '2months', 'Analyst', '2025-07-31 09:42:01', 'approved', 'uploads/cv/cv_688b3a69cd23a7.05862035.docx'),
(14, 'Joel Nganga', '2003-04-30', '0110465350', 'joelngangagathoni@gmail.com', 'Male', 'Kenya', 'MKU', 'COMPUTER TECHNOLOGY', 'Degree', 'Third', '2027', 'Networking', '', '3', '', '2025-08-04', 'No', 'Yes', 10000.00, 'It expert', 'SAFARICOM', 'it staff', '1year', '', '2025-08-01 08:05:21', 'approved', 'uploads/cv/cv_688c75415da527.22116501.docx');

-- --------------------------------------------------------

--
-- Table structure for table `internship_fields`
--

CREATE TABLE `internship_fields` (
  `id` int(11) NOT NULL,
  `field_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `internship_fields`
--

INSERT INTO `internship_fields` (`id`, `field_name`) VALUES
(3, 'Cyber security'),
(4, 'Data science'),
(5, 'Software Engineering'),
(8, 'Supply chain');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `user_role` enum('admin','supervisor','intern') NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `recipient_role` enum('admin','supervisor','intern') DEFAULT NULL,
  `recipient_group` enum('all_interns','all_supervisors','all_users') DEFAULT NULL,
  `type` enum('message','notification') NOT NULL,
  `reply_to` int(11) DEFAULT NULL,
  `group_id` varchar(255) DEFAULT NULL,
  `reply_to_message_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `content`, `created_at`, `user_id`, `user_role`, `recipient_id`, `recipient_role`, `recipient_group`, `type`, `reply_to`, `group_id`, `reply_to_message_id`) VALUES
(8, 'ON FRIDAY 27/06/2025 THERE IS  GRADUATION', '2025-07-08 11:59:39', 10, 'admin', NULL, NULL, 'all_users', 'notification', NULL, NULL, NULL),
(10, 'Hello   will you be available on 10/07/2025', '2025-07-08 12:04:44', 10, 'admin', 11, 'supervisor', NULL, 'message', NULL, NULL, NULL),
(11, 'ON FRIDAY 27/06/2025 THERE IS GRADUATION', '2025-07-08 12:10:40', 10, 'admin', NULL, NULL, 'all_users', 'notification', NULL, NULL, NULL),
(13, 'Yes, I will be available.', '2025-07-08 12:40:44', 11, 'supervisor', 10, 'admin', NULL, '', NULL, '', 10),
(15, 'Hello \r\nwhen will  we have the meeting', '2025-07-08 15:11:38', 11, 'supervisor', 10, 'admin', NULL, 'message', NULL, 'msg_686d0afa674479.33269910', NULL),
(16, 'Hello \r\nwill have meeting tomorrow in the morning', '2025-07-08 15:18:16', 11, 'supervisor', NULL, NULL, 'all_interns', 'notification', NULL, 'msg_686d0c88254ef2.00927186', NULL),
(17, 'on 15/07/2025', '2025-07-08 15:29:03', 10, 'admin', 11, 'supervisor', NULL, '', NULL, 'msg_686d0afa674479.33269910', 15),
(18, 'Hello ,did  you get the task I assigned you.', '2025-07-08 16:24:21', 11, 'supervisor', NULL, NULL, 'all_interns', 'message', NULL, 'msg_686d1c05302be8.82153419', NULL),
(19, 'yes sir', '2025-07-08 16:32:33', 12, 'intern', 11, 'supervisor', NULL, '', NULL, 'msg_686d1c05302be8.82153419', 18),
(20, 'yes sir ,I have already sent my work', '2025-07-08 16:35:16', 6, 'intern', 11, 'supervisor', NULL, '', NULL, 'msg_686d1c05302be8.82153419', 18),
(21, 'Tomorrow will have a general meeting', '2025-07-24 12:08:26', 10, 'admin', NULL, NULL, 'all_users', 'notification', NULL, 'msg_6881f80aa1d4b8.72103536', NULL),
(22, 'Hello,\r\nI won\'t be available for the general meeting', '2025-07-24 12:11:03', 11, 'supervisor', 10, 'admin', NULL, 'message', NULL, 'msg_6881f8a7d57158.97967857', NULL),
(23, 'Why won\'t you be available?', '2025-07-24 12:11:53', 10, 'admin', 11, 'supervisor', NULL, '', NULL, 'msg_6881f8a7d57158.97967857', 22),
(26, 'app password \r\nlwdf lwyr yeli rdnn', '2025-07-29 12:34:25', 10, 'admin', 6, 'intern', NULL, 'message', NULL, 'msg_688895a12d6c36.93104541', NULL),
(28, 'Hello,\r\nHave you been assessed ?', '2025-07-31 10:57:29', 10, 'admin', 6, 'intern', NULL, 'message', NULL, 'msg_688b21e9c10545.16412592', NULL),
(29, 'Hello, Have you been assessed ?', '2025-07-31 11:03:32', 10, 'admin', 6, 'intern', NULL, 'message', NULL, 'msg_688b2354d14074.18977815', NULL),
(30, 'Hello, Have you been assessed ?\r\nnrmv bqpz kmms pcia', '2025-07-31 11:24:48', 10, 'admin', 6, 'intern', NULL, 'message', NULL, 'msg_688b285047e910.87842686', NULL),
(31, 'hello,\r\nwill you be available  next week?', '2025-07-31 11:40:29', 10, 'admin', 11, 'supervisor', NULL, 'message', NULL, 'msg_688b2bfd9c65d8.51461762', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registration_tokens`
--

CREATE TABLE `registration_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `applicant_id` int(11) DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_tokens`
--

INSERT INTO `registration_tokens` (`id`, `user_id`, `applicant_id`, `token`, `expires_at`, `created_at`, `used`) VALUES
(11, NULL, NULL, '648713ba57cd3be7d64de8890c094421061463556f71748fe468665272f23132', '2025-08-03 10:39:17', '2025-08-01 08:39:17', 0),
(12, NULL, NULL, 'dc71868821a39b49dd6891c98d41a2260f897921fba1e738fe9059750323af3a', '2025-08-03 10:39:26', '2025-08-01 08:39:26', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `intern_id` int(11) DEFAULT NULL,
  `task_description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `date_issued` date DEFAULT NULL,
  `submit_date` date DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `submission_file` varchar(255) DEFAULT NULL,
  `submission_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `intern_id`, `task_description`, `file_path`, `date_issued`, `submit_date`, `supervisor_id`, `status`, `submission_file`, `submission_date`) VALUES
(7, 14, 'Assign in requirement gathering ,analyzing business process, designing  system model and document findings.', NULL, '2025-06-20', '2025-07-05', 8, 'completed', 'submissions/1750969328_internship project  db codes.docx', '2025-06-26 23:22:08'),
(8, 16, 'create a website', 'uploads/Summary of multimedia.docx', '2025-06-29', '2025-07-04', 18, 'completed', 'submissions/1751207199_water concervation documentation.pptx', '2025-06-29 17:26:39'),
(9, 6, 'Develop an internship portal', 'uploads/tasks/1752480185_1957_water_concervation_documentation.pptx', '2025-07-14', '2025-08-10', 11, 'completed', 'submissions/1752482139_internship project  db codes.docx', '2025-07-14 11:35:39'),
(10, 12, 'Develop an internship portal', 'uploads/tasks/1752480185_1957_water_concervation_documentation.pptx', '2025-07-14', '2025-08-10', 11, 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(50) DEFAULT 'interns',
  `supervisor_id` int(11) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_created_at` datetime DEFAULT NULL,
  `internship_end_date` date DEFAULT NULL,
  `account_status` enum('active','inactive','completed') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `role`, `supervisor_id`, `reset_token`, `token_created_at`, `internship_end_date`, `account_status`) VALUES
(6, 'amran adan', 'adanhalima970@gmail.com', '$2y$10$qXZKN3Q6ifwuRlziT0VT2udcYvpAH3ig9HCbQmyVSDYe2MujYFdJK', '2025-06-19 10:01:16', 'intern', 11, NULL, NULL, NULL, 'active'),
(8, 'Kipchumba', 'kip@gmail.com', '$2y$10$dMXLo4Z/kJCdT9i.Kx4m4Odnv/q.YyjC97U65UG7D9NvGtWlS5nmq', '2025-06-23 08:18:10', 'supervisor', NULL, NULL, NULL, NULL, 'active'),
(10, 'Hussein ADAN', 'omar.hussein2022@students.jkuat.ac.ke', '$2y$10$y0yLLx84tocujKtGwCVKae9aJES1tLJLfNqz2gND/ZTYiVmNh2UBi', '2025-06-24 07:29:52', 'admin', NULL, NULL, NULL, NULL, 'active'),
(11, 'ALI ABDI', 'adanomarhussein@gmail.com', '$2y$10$8zCzLDutcaXQ1LhUP3f7repR7ctV81bAz16hXXGfiTeDJU/8wkN.i', '2025-06-24 11:59:51', 'supervisor', NULL, NULL, NULL, NULL, 'active'),
(12, 'Mzee mwangi', 'hiram.karogo@stuents.jkuat.ac.ke', '$2y$10$d1zfXHPdI4vL2JH/CIaDTOUL0l/TKDSREqgsN33K.ttBp.4Q8CTii', '2025-06-24 12:15:26', 'intern', 11, NULL, NULL, NULL, 'active'),
(16, 'farid fgaa', 'farIdawdhat@gmail.com', '$2y$10$ouWaOskPw8zNBWcMXRRV3et8JH5QBfE4m12XG9BhxvqSUpnHzLfdG', '2025-06-29 14:17:05', 'intern', 19, NULL, NULL, NULL, 'active'),
(19, 'Daniel Mwariri', 'dan@gmail.com', '$2y$10$FFoWnlSdQruLDHH7NPqF5.LtMdrm4KgrtJghK7E5yVyN/SH/tSE1e', '2025-06-29 14:36:00', 'supervisor', NULL, NULL, NULL, NULL, 'active'),
(20, 'Janet', 'janet@gmail.com', '$2y$10$8R4AovVzPz7XzM8fQfSIxOo7YoLvoglFP9TbaeMCOasCfkSXE6FAS', '2025-07-10 07:20:17', 'supervisor', NULL, NULL, NULL, NULL, 'active'),
(21, 'Hadija adan', 'haskidehuska@gmail.com', '$2y$10$5bkP8qzog9Wymypl0qrnWe2IXNo/3HuNoqSk5B8FBV.W7mP8d60Yi', '2025-07-24 07:09:58', 'intern', 20, NULL, NULL, NULL, 'active'),
(24, 'Daniel wekesa', 'haskidehuska20@gmail.com', '$2y$10$.uIOtOZHiTjqmxABHvG/UeRxDoCd.xzOlsE7E7dzAk.sgwTu.c0GK', '2025-07-31 13:09:11', 'intern', NULL, NULL, NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `weekly_reports`
--

CREATE TABLE `weekly_reports` (
  `id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `week_ending` date NOT NULL,
  `monday` text DEFAULT NULL,
  `tuesday` text DEFAULT NULL,
  `wednesday` text DEFAULT NULL,
  `thursday` text DEFAULT NULL,
  `friday` text DEFAULT NULL,
  `weekly_summary` text DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `report_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `supervisor_comments` text DEFAULT NULL,
  `supervisor_signature` varchar(255) DEFAULT NULL,
  `supervisor_date` date DEFAULT NULL,
  `status` enum('pending','reviewed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weekly_reports`
--

INSERT INTO `weekly_reports` (`id`, `intern_id`, `week_ending`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `weekly_summary`, `signature`, `report_date`, `created_at`, `supervisor_comments`, `supervisor_signature`, `supervisor_date`, `status`) VALUES
(1, 12, '2025-06-20', 'intern page ', 'weekly report', 'supervisor dashboard', 'admin page', 'login page', 'intern page ,weekly report, supervisor dashboard, admin  page & login page', 'mwangi', '2025-06-20', '2025-06-26 06:30:17', 'Good start', 'abdi', '2025-06-21', 'reviewed'),
(2, 6, '2025-06-20', 'resetting portal password', 'resetting email password', 'Generating students email', 'Assisting with jkuat eservice', 'USER SUPPORT', 'resetting portal password, resetting email password, Generating students email, Assisting with jkuat eservice & USER SUPPORT', 'Amran', '2025-06-20', '2025-06-26 06:54:28', 'Great nice start', 'abdi', '2025-06-20', 'reviewed'),
(3, 14, '2025-06-22', 'Introduction & requirements gathering ', 'Requirements analysis', 'System designing &modelling', 'Validation with stakeholders', 'Documentation & development support', 'Introduction & requirements gathering  to Documentation & development support', 'ben', '2025-06-22', '2025-06-26 20:01:56', 'Excellent progress', 'kipchumba', '2025-06-22', 'reviewed'),
(4, 12, '2025-06-27', 'user support', 'email generation', 'password reset', 'portal issues ', 'Graduation day', 'busy week', 'mwangi', '2025-06-27', '2025-06-29 13:04:35', NULL, NULL, NULL, 'pending'),
(5, 18, '2025-06-27', 'php development', 'figma / design implementation', 'database connectivity', 'django ', 'php', 'amazing', 'farid', '0002-01-01', '2025-06-29 14:29:47', NULL, NULL, NULL, 'pending'),
(6, 16, '2025-06-29', 'pyhp', 'php', 'php', 'php', 'php', 'amazing', 'farid', '2025-03-31', '2025-06-29 14:38:46', NULL, NULL, NULL, 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application_dates`
--
ALTER TABLE `application_dates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `internship_applications`
--
ALTER TABLE `internship_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `internship_fields`
--
ALTER TABLE `internship_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `reply_to` (`reply_to`);

--
-- Indexes for table `registration_tokens`
--
ALTER TABLE `registration_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `weekly_reports`
--
ALTER TABLE `weekly_reports`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `internship_applications`
--
ALTER TABLE `internship_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `internship_fields`
--
ALTER TABLE `internship_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `registration_tokens`
--
ALTER TABLE `registration_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `weekly_reports`
--
ALTER TABLE `weekly_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`reply_to`) REFERENCES `messages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
