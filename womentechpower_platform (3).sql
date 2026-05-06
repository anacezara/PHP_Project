-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2024 at 09:43 PM
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
-- Database: `womentechpower platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `event_type` enum('workshop','mentoring','networking','conference') DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `location`, `event_type`, `max_participants`, `created_by`, `created_at`, `image`) VALUES
(7, 'EmpowerHer: Networking for Women in Power Platform', 'Join us for a networking evening designed to connect and empower women in the Power Platform ecosystem. Share insights, experiences, and build lasting professional relationships.', '2024-12-15 18:00:00', 'TechHub Conference Center, New York', 'networking', 40, 5, '2024-12-01 17:07:40', 'uploads/events/674cba4bd4cf3.png'),
(8, ' Women Innovators: Power Platform Workshop', 'A hands-on workshop where women technologists explore how to create innovative solutions using the Power Platform. Learn about Power BI, Power Automate, and Power Apps.', '2024-01-10 21:35:00', 'CodeTogether Labs, London', 'workshop', 30, 5, '2024-12-01 19:36:43', 'uploads/events/674cbacb2f945.png'),
(9, 'Mentoring Circle: Women in Tech and Power Platform', 'Be part of an exclusive mentoring session where leading women in tech share their journeys and insights into using Power Platform to drive business transformation. At link: https://www.zoom.com/', '2024-02-15 13:00:00', 'Virtual (Zoom) ', 'mentoring', 20, 5, '2024-12-01 19:38:22', 'uploads/events/674cbc038619e.png'),
(10, 'Women in Tech Power Platform Conference 2025', 'The annual conference celebrating women in tech! Explore the latest trends in Power Platform, hear keynote speeches from industry leaders, and join panel discussions.', '2024-02-20 10:00:00', ' International Convention Center, Bruxelles', 'conference', 500, 5, '2024-12-01 19:41:07', 'uploads/events/674cbc2af2bd2.png');

-- --------------------------------------------------------

--
-- Table structure for table `event_feedback`
--

CREATE TABLE `event_feedback` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comments` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_feedback`
--

INSERT INTO `event_feedback` (`id`, `event_id`, `member_id`, `rating`, `comments`, `submitted_at`) VALUES
(5, 7, 7, 5, 'Amazing experience!', '2024-12-01 20:41:56');

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('confirmed','waiting','cancelled') DEFAULT 'confirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `member_id`, `event_id`, `registration_date`, `status`) VALUES
(11, 5, 7, '2024-12-01 17:07:56', 'confirmed'),
(12, 7, 7, '2024-12-01 20:42:25', 'confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `job_type` enum('full-time','part-time','internship','freelance') DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `description`, `company`, `location`, `job_type`, `posted_by`, `created_at`) VALUES
(2, 'Technical Consultant', 'Provide expert advice and solutions for enterprise-level IT projects. Collaborate with development teams and clients to ensure optimal performance of systems and applications.', 'GoIT', 'Cluj-Napoca, Romania', 'part-time', NULL, '2024-11-29 22:35:42'),
(3, 'Software engeneer', 'Design, develop, and maintain robust software systems with a focus on scalability and security. Collaborate within an agile team.', 'Alfa Software', 'Bucharest, Romania', 'full-time', NULL, '2024-11-29 22:36:24'),
(6, 'Web Designer', 'Create visually appealing and user-friendly websites, focusing on responsive design and seamless user experiences.', 'WomenTechConsulting', 'London, England', 'internship', 5, '2024-12-01 19:50:09'),
(7, 'Data Scientist', 'tilize advanced machine learning models and algorithms to solve complex business problems, focusing on data-driven solutions.', '-', '-', 'freelance', 5, '2024-12-01 19:50:56');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('applied','under_review','accepted','rejected') DEFAULT 'applied'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `job_id`, `member_id`, `application_date`, `status`) VALUES
(2, 3, 5, '2024-11-29 23:21:17', ''),
(3, 2, 5, '2024-11-29 23:21:19', ''),
(4, 2, 5, '2024-11-29 23:22:13', ''),
(6, 2, 2, '2024-11-29 23:23:30', ''),
(7, 3, 2, '2024-12-01 14:12:11', 'applied'),
(8, 7, 5, '2024-12-01 20:40:45', 'applied');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profession` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `expertise` text DEFAULT NULL,
  `linkedin_profile` varchar(255) DEFAULT NULL,
  `status` enum('active','pending','mentor') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('member','mentor','admin') DEFAULT 'member',
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `first_name`, `last_name`, `email`, `password`, `profession`, `company`, `expertise`, `linkedin_profile`, `status`, `created_at`, `role`, `profile_picture`, `bio`) VALUES
(2, 'Admin', 'User', 'admin@example.com', '$2y$10$FDpPDCVI3J.A9YpVwZC0UeaaXFpelzCf9KT7MNgy/rYhSHTNmUGju', '', '', '', '', 'active', '2024-11-25 19:55:52', 'admin', NULL, ''),
(5, 'Ana', 'Caprar', 'acezara13@gmail.com', '$2y$10$xgPSucWByIu80tuniJKIwOqg/JxoVTd9JkQ3OlCDo4EDpzhRNSEgW', 'Web Designer', 'Alfa Software ', 'Design tools, Front-end development', 'https://www.linkedin.com/in/ana-cezara-c%C4%83prar-s%C4%83l%C4%83jan-b5696b308/', 'active', '2024-11-26 13:51:51', 'admin', 'uploads/pp1.jpg', 'Hello! I love making cute designs!'),
(7, 'Antonia', 'Salajan', 'antosal@gmail.com', '$2y$10$AnCFZBzn7pgVowFLcmnCz.m9/NhCYH6F9bH5rHt8CThTPYPyVAl2G', 'Software Engeneer', 'Bit Defender', 'Designing, developing, testing, and maintaining software systems.', 'https://www.linkedin.com/in/antonia', 'active', '2024-11-26 21:00:21', 'member', 'uploads/Anto.jpg', 'I like learning new things everyday!'),
(8, 'Daniela', 'Canta', 'danican@outlook.com', '$2y$10$n3ca1AoC1QRUx.ZRTdXAaOZUxEFaI.lwzNGHRoCxdkcycLcirqQRC', 'Technical Consultant', 'Alfa Software ', 'IT Strategy Consulting, Digital Transformation, Mentorship,  Cybersecurity, System integration', 'https://www.linkedin.com/danielacanta', 'mentor', '2024-11-26 22:30:12', 'mentor', 'uploads/Daniela.jpg', 'I help people learn!'),
(9, 'Adela', 'Ortan', 'ortanadela@yahoo.com', '$2y$10$4NCsE9HTl3dImR70GZHFbuRS72U/pgWxep.ZzQAx0E8GSvTxP0ZCW', 'Technical Consultant', 'GoIT', 'IT Infrastructure, Database Management, Cybersecurity, System integration, Programming Languages: Python, Java, C++, JavaScript, Software Architecture', 'https://www.linkedin.com/adell', 'mentor', '2024-12-01 16:43:23', 'mentor', 'uploads/Bia.png', 'I love IT\r\n'),
(10, 'Maria', 'Radu', 'mariaradu@gmail.com', '$2y$10$CimcvFTLnTmt2GEY1ctdC.fPMVY/2Zt5q9kW.Ih1Ko5fNXgB6El/O', 'Software Engeneer', 'Zitec', 'Full-Stack Development, Cybersecurity, Agile Methodologies', ' https://linkedin.com/in/mariaradu', 'active', '2024-12-01 18:02:55', 'member', 'uploads/success1.png', 'I am grateful I met such wonderful people!'),
(11, 'Amelia', 'Constantinescu', 'ameliaconst@yahoo.com', '$2y$10$11DcMkqPF9AnrKiFfKST2uCzdvGY.nTSXIjvI8lsSMmnyYjVZgNAy', 'Technical Consultant', 'WomenTechConsulting', 'IT Strategy Consulting, Digital Transformation, Mentorship', 'https://www.linkedin.com/in/amalia', 'mentor', '2024-12-01 18:06:55', 'mentor', 'uploads/success2.png', 'I strive to help as many women to find their path in tech!'),
(12, 'Sara', 'Jianu', 'sarajianu@outlook.com', '$2y$10$0C/EdkSIHZeHbGR0xKx1kuGsJHQ78.WI5d.4rXoq6ZMM14ZkdrLmK', 'Data Scientist ', 'GoIT', 'Machine Learning, Data Analysis, Predictive Modeling', 'https://www.linkedin.com/sara', 'active', '2024-12-01 18:34:57', 'member', 'uploads/success3.png', ''),
(13, 'Teona', 'Artene', 'teonaartene@yahoo.com', '$2y$10$KOp6AfXw2O/0jB2fMy/e8upR3ZvMsUr/uLsaH8J6.NfpNrJjpDhyi', 'Data Analyst', 'Pentalog', 'Data Analysis Tools, Database management, Statistics', 'https://www.linkedin.com/in/teona-raluca-artene-931402299/', 'active', '2024-12-01 18:42:19', 'admin', 'uploads/WhatsApp Image 2024-12-01 at 17.23.52_848a911f.jpg', 'Hi! I am Teona and I love to learn!'),
(14, 'Calina', 'Borzan', 'calina@gmail.com', '$2y$10$yZLkIO674e2e5bk7BLnJ8ezGk2adw7H.7EAdpIxGFg14ZSGf3lO0W', 'Assistant', 'GoIT', 'Technical Support, Project Coordination, Administrative tasks', 'https://www.linkedin.com/calina', 'active', '2024-12-01 19:08:09', 'member', 'uploads/Calu.jpg', 'I help people overcome their hardships!');

-- --------------------------------------------------------

--
-- Table structure for table `mentorship_matches`
--

CREATE TABLE `mentorship_matches` (
  `id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `mentee_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','completed','pending','declined') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mentorship_matches`
--

INSERT INTO `mentorship_matches` (`id`, `mentor_id`, `mentee_id`, `created_at`, `status`) VALUES
(5, 11, 12, '2024-12-01 19:53:06', 'active'),
(6, 9, 12, '2024-12-01 19:53:22', 'active'),
(7, 8, 7, '2024-12-01 19:58:30', 'active'),
(8, 9, 7, '2024-12-01 19:58:32', 'declined'),
(9, 11, 7, '2024-12-01 19:58:34', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `mentorship_progress`
--

CREATE TABLE `mentorship_progress` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `progress_percentage` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mentorship_progress`
--

INSERT INTO `mentorship_progress` (`id`, `session_id`, `progress_percentage`, `notes`, `is_completed`) VALUES
(5, 5, 25, 'Good job! ', 0),
(6, 5, 30, 'You need to work more on this.', 0);

-- --------------------------------------------------------

--
-- Table structure for table `mentorship_sessions`
--

CREATE TABLE `mentorship_sessions` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `session_date` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mentorship_sessions`
--

INSERT INTO `mentorship_sessions` (`id`, `match_id`, `session_date`, `notes`, `feedback`) VALUES
(4, 5, '2024-12-12 09:00:00', 'In our first session we will get to know each other better and make plans about what we want to do further.', NULL),
(5, 7, '2024-12-14 10:00:00', 'Session about cybersecurity.', 'I loved it, I learned so much');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('article','video','podcast','downloadable') NOT NULL,
  `resource_url` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `title`, `description`, `type`, `resource_url`, `created_by`, `created_at`, `file_path`) VALUES
(3, 'How do we get women into tech?', 'The representation of women in important IT and technology roles has always been dramatically lower than that of men. In this podcast, we\'re joined by our very own Business Process Consultant, Aimee Le Deaut-Griffin, as she discusses her years of experience in the tech industry as a woman.', 'video', 'https://youtu.be/C-Qhb9OUCQs', NULL, '2024-11-27 11:17:25', NULL),
(4, 'Leading Women in Tech', 'A podcast discussing how to create the next generation of women leaders in the tech industry, helping you succeed on your terms and being the leader you want to be.', 'podcast', 'https://youtu.be/m8Uhm_GsPRQ?list=PLblApEREO6AglvC9oUfWrfTjFllXWRTIy', NULL, '2024-11-28 16:28:21', 'uploads/Exemplu Matrice S4.docx'),
(5, 'Women Tech Presentation', 'This is a presentation about us and what we support!', 'downloadable', '', 5, '2024-11-29 13:58:00', 'uploads/WomenTechPower_Presentation.pptx'),
(6, 'The magic of Power Platform', 'Progressing in a Career in Power Platform as a Woman', 'video', 'https://youtu.be/FbhzcD3ZiXE', 5, '2024-12-01 19:18:17', NULL),
(7, 'Women in Tech', 'What advice would you give to women in tech as they pursue leadership roles?', 'video', 'https://youtu.be/8VZTtRX4HIk', 5, '2024-12-01 19:18:55', NULL),
(8, 'Women in Tech Global Awards', 'The 7th edition of the Women in Tech® Global Awards, held on November 14, 2024, in Paris, France, celebrated the remarkable achievements of women in the technology sector and beyond. This prestigious event, hosted at the Hôtel de Lassay, the palace of the French National Assembly, served as a platform to recognize the pioneering efforts of female leaders who have made significant contributions to their respective fields.', 'article', 'https://women-in-tech.org/women-in-tech-global-awards-empowering-female-pioneers/', 5, '2024-12-01 19:19:39', NULL),
(9, 'Women in Tech European Awards 2024', 'Celebrates Innovation and Leadership in Technology                                                  The prestigious Women in Tech® Europe Awards 2024, held on October 1st, 2024, marked a significant milestone in recognizing and celebrating women’s achievements in the technology sector. The ceremony, which took place in Brussels, brought together industry leaders, innovators, and changemakers from across Europe, highlighting the growing influence of women in shaping the future of technology.', 'article', 'https://women-in-tech.org/women-in-tech-europe-awards-2024-celebrates-innovation-and-leadership-in-technology/', 5, '2024-12-01 19:20:34', NULL),
(10, 'Women TechEU program', 'The Women TechEU program, funded by the EU, supports women-led deep tech startups with grants of up to €75,000. The document details the InfoDay event, including program objectives, application guidelines, and eligibility criteria, aiming to foster innovation and diversity in tech entrepreneurship.', 'downloadable', '', 5, '2024-12-01 19:21:27', 'uploads/Women TechEU program.pdf');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `event_feedback`
--
ALTER TABLE `event_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `mentorship_matches`
--
ALTER TABLE `mentorship_matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mentor_id` (`mentor_id`),
  ADD KEY `mentee_id` (`mentee_id`);

--
-- Indexes for table `mentorship_progress`
--
ALTER TABLE `mentorship_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `mentorship_sessions`
--
ALTER TABLE `mentorship_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `event_feedback`
--
ALTER TABLE `event_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `mentorship_matches`
--
ALTER TABLE `mentorship_matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mentorship_progress`
--
ALTER TABLE `mentorship_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mentorship_sessions`
--
ALTER TABLE `mentorship_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `members` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `event_feedback`
--
ALTER TABLE `event_feedback`
  ADD CONSTRAINT `event_feedback_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_feedback_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `members` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mentorship_matches`
--
ALTER TABLE `mentorship_matches`
  ADD CONSTRAINT `mentorship_matches_ibfk_1` FOREIGN KEY (`mentor_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mentorship_matches_ibfk_2` FOREIGN KEY (`mentee_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mentorship_progress`
--
ALTER TABLE `mentorship_progress`
  ADD CONSTRAINT `mentorship_progress_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `mentorship_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mentorship_sessions`
--
ALTER TABLE `mentorship_sessions`
  ADD CONSTRAINT `mentorship_sessions_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `mentorship_matches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `members` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
