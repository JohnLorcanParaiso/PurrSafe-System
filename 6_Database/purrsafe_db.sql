-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
<<<<<<< HEAD
-- Generation Time: Dec 06, 2024 at 07:03 PM
=======
-- Generation Time: Dec 06, 2024 at 01:29 PM
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0
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
-- Database: `purrsafe_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `role`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$e0N1Z1Q1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1Z1', 'admin@example.com', 'admin', 'active', '2024-12-04 15:40:57');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`) VALUES
(1, 'Welcome to PurrSafe!', 'We are excited to have you here.', '2024-12-04 15:40:57'),
(2, 'Maintenance Notice', 'The site will be down for maintenance on Sunday.', '2024-12-04 15:40:57');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `feedback_text` text NOT NULL,
  `feedback_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_report_feedback` (`report_id`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `found_reports` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `found_reports`
--

CREATE TABLE `found_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `owner_notification` text NOT NULL,
  `founder_name` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
<<<<<<< HEAD
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','returned') DEFAULT 'pending',
  `return_confirmed_at` timestamp NULL DEFAULT NULL
=======
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `found_reports`
--

<<<<<<< HEAD
INSERT INTO `found_reports` (`id`, `user_id`, `report_id`, `owner_notification`, `founder_name`, `contact_number`, `image_path`, `created_at`, `status`, `return_confirmed_at`) VALUES
(7, 3, 19, 'Good', 'John Lorcan Paraiso', '09265231560', NULL, '2024-12-06 11:31:06', 'pending', NULL),
(8, 3, 18, 'Safe and sound', 'John Lorcan Paraiso', '09265231560', '6752f6b2e7cdc_1733490354.jpg', '2024-12-06 13:05:54', 'pending', NULL),
(9, 1, 21, 'Is this your cat? Dial me ASAP', 'Jaika', '0916339009', '67532e51d32ea_1733504593.jpg', '2024-12-06 17:03:13', 'pending', NULL),
(10, 5, 23, 'He\'s safe', 'Taylor', '09265231560', NULL, '2024-12-06 17:23:40', 'pending', NULL);
=======
INSERT INTO `found_reports` (`id`, `user_id`, `report_id`, `owner_notification`, `founder_name`, `contact_number`, `image_path`, `created_at`) VALUES
(7, 3, 19, 'Good', 'John Lorcan Paraiso', '09265231560', NULL, '2024-12-06 11:31:06');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `action` text NOT NULL,
  `admin_username` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `action`, `admin_username`, `created_at`) VALUES
(1, 'Logged in', 'admin', '2024-12-04 15:40:57'),
(2, 'Created a new announcement', 'admin', '2024-12-04 15:40:57');
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0

-- --------------------------------------------------------

--
-- Table structure for table `lost_reports`
--

CREATE TABLE `lost_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `breed` varchar(255) NOT NULL,
  `gender` enum('male','female','unknown') NOT NULL,
  `age` int(11) NOT NULL,
  `color` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `last_seen_date` date NOT NULL,
  `last_seen_time` time DEFAULT NULL,
  `owner_name` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen_location` varchar(255) NOT NULL,
  `status` varchar(20) DEFAULT 'missing',
  `found_date` date DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_reports`
--

INSERT INTO `lost_reports` (`id`, `user_id`, `cat_name`, `breed`, `gender`, `age`, `color`, `description`, `last_seen_date`, `last_seen_time`, `owner_name`, `phone_number`, `created_at`, `last_seen_location`, `status`, `found_date`, `edited_at`) VALUES
<<<<<<< HEAD
(17, 4, 'Chrys', 'Munchkin', 'male', 2, 'Black with yellow and white', 'The cat is very clingy, friendly and outgoing. Please feed him once u saw him, he\'s always hungry.', '2024-11-13', '09:40:00', 'Raymond Jerard Madrid', '09123456789', '2024-12-04 07:41:23', 'Brgy. San Agustin Alaminos Laguna', 'lost', NULL, NULL),
(18, 2, 'Serena', 'Sphynx', 'female', 1, 'Peach', 'Owning a Sphynx cat requires special attention to their grooming and health needs, including regular baths, protection from extreme temperatures, and frequent meals.', '2024-12-01', '20:12:00', 'Jaika Remina Madrid', '09189258041', '2024-12-06 00:12:20', 'Brgy. San Agustin Alaminos Laguna', 'found', NULL, NULL),
(19, 1, 'Tabby', 'American Shorthair', 'male', 2, 'Tricolors of black yellow and white', 'Always hungry, feed him when found.', '2024-12-04', '09:00:00', 'Jaika Remina Madrid', '09189258041', '2024-12-06 01:00:31', 'SM Lipa', 'found', NULL, NULL),
(20, 3, 'Layla', 'Birman', 'female', 2, 'Grey', 'Cute', '2024-12-06', '18:35:00', 'John Lorcan Paraiso', '09265231560', '2024-12-06 09:36:25', 'West Park', 'missing', NULL, '2024-12-06 12:16:42'),
(21, 3, 'Fiona', 'Sphynx', 'male', 2, 'Hairless', 'Last seen this morning', '2024-12-06', '00:00:00', 'John Lorcan Paraiso', '09265231560', '2024-12-06 14:05:35', 'West Park', 'found', NULL, NULL),
(22, 5, 'Karynn', 'Sphynx', 'female', 0, 'Orange Black', 'Last seen this morning', '2024-12-04', '00:00:00', 'John Lorcan Paraiso', '09265231560', '2024-12-06 14:44:53', 'At the streets', 'missing', NULL, NULL),
(23, 3, 'Clark', 'Ragamuffin', 'male', 3, 'Black', 'Handsome cat', '2024-12-06', '01:19:00', 'John Lorcan Paraiso', '09265231560', '2024-12-06 17:18:14', 'North Park', 'found', NULL, NULL);
=======
(1, 1, 'Gold', 'British Shorthair', 'male', 1, 'Yellow', 'Goofy, has a mark on the noes.', '2024-11-02', '15:37:00', 'Jaika Remina Madrid', '09189258041', '2024-11-19 07:38:09', '', 'missing', NULL, NULL),
(2, 1, 'Silver', 'British Shorthair', 'male', 1, 'Gray', 'Silent cat, prefers to be alone', '2024-11-16', '12:43:00', 'Jaika Remina Madrid', '09189258041', '2024-11-19 07:43:33', '', 'missing', NULL, NULL),
(9, 1, 'Blackie', 'American Shorthair', 'male', 4, 'Black', 'Grumpy. Likes to be alone. ', '2024-12-01', '16:43:00', 'Jaika Remina Madrid', '09189258041', '2024-12-02 06:44:13', '', 'missing', NULL, NULL),
(11, 1, 'Whitney', 'American Shorthair', 'female', 3, 'White', 'Cute, fluffy, likes humans', '2024-12-01', '15:03:00', 'Jaika Remina Madrid', '09189258041', '2024-12-02 07:03:46', 'Batangas State University - Alangilan Campus', 'lost', NULL, NULL),
(17, 4, 'Chrys', 'Munchkin', 'male', 2, 'Black with yellow and white', 'The cat is very clingy, friendly and outgoing. Please feed him once u saw him, he\'s always hungry.', '2024-11-13', '09:40:00', 'Raymond Jerard Madrid', '09123456789', '2024-12-04 07:41:23', 'Brgy. San Agustin Alaminos Laguna', 'lost', NULL, NULL),
(18, 2, 'Serena', 'Sphynx', 'female', 1, 'Peach', 'Owning a Sphynx cat requires special attention to their grooming and health needs, including regular baths, protection from extreme temperatures, and frequent meals.', '2024-12-01', '20:12:00', 'Jaika Remina Madrid', '09189258041', '2024-12-06 00:12:20', 'Brgy. San Agustin Alaminos Laguna', 'missing', NULL, NULL),
(19, 1, 'Tabby', 'American Shorthair', 'male', 2, 'Tricolors of black yellow and white', 'Always hungry, feed him when found.', '2024-12-04', '09:00:00', 'Jaika Remina Madrid', '09189258041', '2024-12-06 01:00:31', 'SM Lipa', 'found', NULL, NULL),
(20, 3, 'Layla', 'Birman', 'female', 2, 'Grey', 'Cute', '2024-12-06', '18:35:00', 'John Lorcan Paraiso', '09265231560', '2024-12-06 09:36:25', 'West Park', 'missing', NULL, '2024-12-06 12:16:42');
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'Someone has found your cat! Check the found reports for details.', 0, '2024-12-04 23:37:53'),
(2, 4, 'Thank you for finding the cat! The owner has been notified and will contact you soon.', 0, '2024-12-04 23:37:53'),
(3, 4, 'Good news! Your cat \'Chrys\' has been found! Someone has submitted a found report. Please check your found reports section for contact details of the person who found your cat.', 0, '2024-12-04 23:54:55'),
(4, 2, 'Thank you for submitting a found report for the cat \'Chrys\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-04 23:54:55'),
(5, 1, 'Good news! Your cat \'Whitney\' has been found! Someone has submitted a found report. Please check your found reports section for contact details of the person who found your cat.', 0, '2024-12-05 00:25:09'),
(6, 4, 'Thank you for submitting a found report for the cat \'Whitney\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-05 00:25:09'),
(7, 1, 'Good news! Your cat \'Whitney\' has been found! Someone has submitted a found report. Please check your found reports section for contact details of the person who found your cat.', 0, '2024-12-05 00:25:57'),
(8, 4, 'Thank you for submitting a found report for the cat \'Whitney\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-05 00:25:57'),
(9, 1, 'Good news! Your cat \'Tabby\' has been found! Check your found reports section for contact details of the person who found your cat.', 0, '2024-12-06 11:31:06'),
<<<<<<< HEAD
(10, 3, 'Thank you for submitting a found report for the cat \'Tabby\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-06 11:31:06'),
(11, 2, 'Good news! Your cat \'Serena\' has been found! Check your found reports section for contact details of the person who found your cat.', 0, '2024-12-06 13:05:54'),
(12, 3, 'Thank you for submitting a found report for the cat \'Serena\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-06 13:05:54'),
(13, 3, 'Good news! Your cat \'Fiona\' has been found! Check your found reports section for contact details of the person who found your cat.', 0, '2024-12-06 17:03:13'),
(14, 1, 'Thank you for submitting a found report for the cat \'Fiona\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-06 17:03:13'),
(15, 3, 'Good news! Your cat \'Clark\' has been found! Check your found reports section for contact details of the person who found your cat.', 0, '2024-12-06 17:23:40'),
(16, 5, 'Thank you for submitting a found report for the cat \'Clark\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-06 17:23:40');
=======
(10, 3, 'Thank you for submitting a found report for the cat \'Tabby\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-06 11:31:06');
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `type` enum('lost','found') NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_edit_history`
--

CREATE TABLE `report_edit_history` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `edited_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_images`
--

CREATE TABLE `report_images` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_images`
--

INSERT INTO `report_images` (`id`, `report_id`, `image_path`, `uploaded_at`) VALUES
<<<<<<< HEAD
(20, 17, 'uploads/675007a38c8fb_cute-adorable-playfull-munchkin-kitten_MDavidova_Shutterstock.jpg', '2024-12-04 07:41:23'),
(21, 18, '../../5_Uploads/675241648b1c0_1284076693de06bd79d61f69377d25d7.jpg', '2024-12-06 00:12:20'),
(22, 19, '../../5_Uploads/67524cafec88c_brown-tabby-cat-1103904.jpg', '2024-12-06 01:00:31'),
(23, 20, '../../5_Uploads/6752c59921942_birman.jpg', '2024-12-06 09:36:25'),
(24, 21, '../../5_Uploads/675304afe17f7_Sphynx-4-645mk062211.jpg', '2024-12-06 14:05:35'),
(25, 21, '../../5_Uploads/675304afe6b84_birman.jpg', '2024-12-06 14:05:35'),
(26, 22, '../../5_Uploads/67530de52decf_images (1).jpg', '2024-12-06 14:44:53'),
(27, 23, '../../5_Uploads/675331d6721d9_RagaMuffin.jpg', '2024-12-06 17:18:14');
=======
(1, 1, 'uploads/673c4061881c5_6276326033263278916.jpg', '2024-11-19 07:38:09'),
(2, 2, 'uploads/673c41a548b04_6276326033263278921.jpg', '2024-11-19 07:43:33'),
(8, 9, 'uploads/674d573d0350f_blackcat-lede.jpeg', '2024-12-02 06:44:13'),
(13, 11, 'uploads/674d7a2b2ceda_white-cat-08.jpg', '2024-12-02 09:13:15'),
(20, 17, 'uploads/675007a38c8fb_cute-adorable-playfull-munchkin-kitten_MDavidova_Shutterstock.jpg', '2024-12-04 07:41:23'),
(21, 18, '../../5_Uploads/675241648b1c0_1284076693de06bd79d61f69377d25d7.jpg', '2024-12-06 00:12:20'),
(22, 19, '../../5_Uploads/67524cafec88c_brown-tabby-cat-1103904.jpg', '2024-12-06 01:00:31'),
(23, 20, '../../5_Uploads/6752c59921942_birman.jpg', '2024-12-06 09:36:25');
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
<<<<<<< HEAD
  `profile_pic` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
=======
  `profile_pic` varchar(255) DEFAULT NULL
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

<<<<<<< HEAD
INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `created_at`, `profile_pic`, `profile_image`) VALUES
(1, 'Jaika', 'jaikajeon', 'jaikajeon@gmail.com', '$2y$10$CrmkoWVkrwAHFmPln3n0e.uJ/qarpeHQVCIXuWBv.nVyebnkCS4pC', '2024-11-17 03:17:06', NULL, NULL),
(2, 'Jaika Remina Madrid', 'remwina', 'remwina@gmail.com', '$2y$10$HPVp4NCM/oyW1wbzyNSmHervpn4JMvoOtcg1uF/4geEHinMmJAfd2', '2024-11-17 09:45:29', NULL, NULL),
(3, 'John Lorcan Paraiso', 'Lorx', 'johnlorcparadise@gmail.com', '$2y$10$wHL1KYlrtAWz3H1TXH5vSu.1XQFk5B/yS748Z06P.bpjJry8v.Gka', '2024-11-21 15:02:22', NULL, NULL),
(4, 'Raymond Madrid', 'reirei', 'rjmadrid@gmail.com', '$2y$10$KbZ6n/zMI/IjZWymXmjvMOecF5mveWqNdrfyrTRAw.E0uLPbT1yUa', '2024-12-04 06:14:16', NULL, NULL),
(5, 'Taylor', 'Swift', 'taylor@example.com', '$2y$10$anLrT5cxVsX4j/FA27Nz9eZ27Y3GukChqx8aXOWuS9mUEzhD1sGqe', '2024-12-06 14:13:47', NULL, NULL);
=======
INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `created_at`, `profile_pic`) VALUES
(1, 'Jaika', 'jaikajeon', 'jaikajeon@gmail.com', '$2y$10$CrmkoWVkrwAHFmPln3n0e.uJ/qarpeHQVCIXuWBv.nVyebnkCS4pC', '2024-11-17 03:17:06', NULL),
(2, 'Jaika Remina Madrid', 'remwina', 'remwina@gmail.com', '$2y$10$HPVp4NCM/oyW1wbzyNSmHervpn4JMvoOtcg1uF/4geEHinMmJAfd2', '2024-11-17 09:45:29', NULL),
(3, 'John Lorcan Paraiso', 'Lorx', 'johnlorcparadise@gmail.com', '$2y$10$wHL1KYlrtAWz3H1TXH5vSu.1XQFk5B/yS748Z06P.bpjJry8v.Gka', '2024-11-21 15:02:22', NULL),
(4, 'Raymond Madrid', 'reirei', 'rjmadrid@gmail.com', '$2y$10$KbZ6n/zMI/IjZWymXmjvMOecF5mveWqNdrfyrTRAw.E0uLPbT1yUa', '2024-12-04 06:14:16', NULL);
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `found_reports`
--
ALTER TABLE `found_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `report_id` (`report_id`);

--
<<<<<<< HEAD
=======
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0
-- Indexes for table `lost_reports`
--
ALTER TABLE `lost_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `report_edit_history`
--
ALTER TABLE `report_edit_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `report_images`
--
ALTER TABLE `report_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `found_reports`
--
ALTER TABLE `found_reports`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0

--
-- AUTO_INCREMENT for table `lost_reports`
--
ALTER TABLE `lost_reports`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_edit_history`
--
ALTER TABLE `report_edit_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_images`
--
ALTER TABLE `report_images`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `found_reports` (`id`);

--
-- Constraints for table `found_reports`
--
ALTER TABLE `found_reports`
  ADD CONSTRAINT `found_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `found_reports_ibfk_2` FOREIGN KEY (`report_id`) REFERENCES `lost_reports` (`id`);

--
-- Constraints for table `lost_reports`
--
ALTER TABLE `lost_reports`
  ADD CONSTRAINT `lost_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_edit_history`
--
ALTER TABLE `report_edit_history`
  ADD CONSTRAINT `report_edit_history_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `lost_reports` (`id`),
  ADD CONSTRAINT `report_edit_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `report_images`
--
ALTER TABLE `report_images`
  ADD CONSTRAINT `report_images_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `lost_reports` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
