-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 05, 2024 at 05:00 PM
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
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`) VALUES
(1, 'Test', 'Announcement Test!', '2024-12-03 19:31:29'),
(5, 'Test3', 'Announcement Test 3!', '2024-12-03 20:48:45');

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `admin_username` varchar(50) NOT NULL,
  `feedback` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `admin_username`, `feedback`, `created_at`) VALUES
(1, 'admin', 'Great service!', '2024-12-04 15:40:57'),
(2, 'admin', 'Could improve response time.', '2024-12-04 15:40:57');

-- --------------------------------------------------------

--
-- Table structure for table `found_reports`
--

CREATE TABLE `found_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `owner_notification` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `gps_location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `found_reports`
--

INSERT INTO `found_reports` (`id`, `user_id`, `report_id`, `owner_notification`, `image_path`, `gps_location`, `created_at`) VALUES
(1, 3, 7, 'Pick-Up', 'uploads/Scottish Fold.2.jpg', '', '2024-11-21 17:37:18'),
(2, 3, 7, 'Pick-up at my place.', 'uploads/Scottish Fold.2.jpg', '', '2024-11-21 19:15:49');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `action` text NOT NULL,
  `admin_username` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `action`, `admin_username`, `created_at`, `user_id`) VALUES
(1, 'Logged in', 'admin', '2024-12-04 15:40:57', NULL),
(2, 'Created a new announcement', 'admin', '2024-12-04 15:40:57', NULL);

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
  `last_seen_location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_reports`
--

INSERT INTO `lost_reports` (`id`, `user_id`, `cat_name`, `breed`, `gender`, `age`, `color`, `description`, `last_seen_date`, `last_seen_time`, `owner_name`, `phone_number`, `created_at`, `last_seen_location`) VALUES
(1, 1, 'Gold', 'British Shorthair', 'male', 1, 'Yellow', 'Goofy, has a mark on the noes.', '2024-11-02', '15:37:00', 'Jaika Remina Madrid', '09189258041', '2024-11-19 07:38:09', NULL),
(2, 1, 'Silver', 'British Shorthair', 'male', 1, 'Gray', 'Silent cat, prefers to be alone', '2024-11-16', '12:43:00', 'Jaika Remina Madrid', '09189258041', '2024-11-19 07:43:33', NULL),
(3, 1, 'Blackie', 'American Shorthair', 'female', 2, 'Black', 'Sweet cat', '2024-06-04', '15:56:00', 'Jaika Remina Madrid', '09189258041', '2024-11-19 07:56:48', NULL),
(6, 1, 'Whitney', 'Persian', 'female', 3, 'White', 'n/a', '2024-11-17', '08:48:00', 'Raymond Jerard Madrid', '09189258041', '2024-11-19 12:48:42', NULL),
(7, 3, 'Olivia', 'Scottish Fold', 'female', 5, 'Grey', 'Short n\' sweet', '2024-11-21', '01:34:00', 'John Lorcan', '09265231560', '2024-11-21 17:35:00', NULL),
(8, 3, 'Leo', 'Munchkin', 'male', 3, 'Gold', 'Fluffy', '2024-11-23', '23:56:00', 'John Lorcan', '09265231560', '2024-11-30 15:56:58', NULL);

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
(8, 4, 'Thank you for submitting a found report for the cat \'Whitney\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-05 00:25:57');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `status` enum('Lost','Found') NOT NULL,
  `date_created` datetime DEFAULT current_timestamp()
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
(1, 1, 'uploads/673c4061881c5_6276326033263278916.jpg', '2024-11-19 07:38:09'),
(2, 2, 'uploads/673c41a548b04_6276326033263278921.jpg', '2024-11-19 07:43:33'),
(3, 3, 'uploads/673c44c03d107_blackcat-lede.jpeg', '2024-11-19 07:56:48'),
(5, 6, 'uploads/673c892ae6736_Whitepersiancatoncouch-aba536ea9760403dac2042cc4c47144d.jpg', '2024-11-19 12:48:42'),
(6, 7, 'uploads/673f6f44ca2bf_Scottish Fold.2.jpg', '2024-11-21 17:35:00'),
(7, 8, 'uploads/674b35cae2328_Scottish Fold.2.jpg', '2024-11-30 15:56:58');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Jaika', 'jaikajeon', 'jaikajeon@gmail.com', '$2y$10$CrmkoWVkrwAHFmPln3n0e.uJ/qarpeHQVCIXuWBv.nVyebnkCS4pC', '2024-11-17 03:17:06'),
(2, 'Jaika Remina Madrid', 'remwina', 'remwina@gmail.com', '$2y$10$HPVp4NCM/oyW1wbzyNSmHervpn4JMvoOtcg1uF/4geEHinMmJAfd2', '2024-11-17 09:45:29'),
(3, 'John Lorcan Paraiso', 'Lorx', 'johnlorcparadise@gmail.com', '$2y$10$wHL1KYlrtAWz3H1TXH5vSu.1XQFk5B/yS748Z06P.bpjJry8v.Gka', '2024-11-21 15:02:22');

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
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `found_reports`
--
ALTER TABLE `found_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`);

--
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `found_reports`
--
ALTER TABLE `found_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lost_reports`
--
ALTER TABLE `lost_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_images`
--
ALTER TABLE `report_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `found_reports`
--
ALTER TABLE `found_reports`
  ADD CONSTRAINT `found_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `found_reports_ibfk_2` FOREIGN KEY (`report_id`) REFERENCES `lost_reports` (`id`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lost_reports`
--
ALTER TABLE `lost_reports`
  ADD CONSTRAINT `lost_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_images`
--
ALTER TABLE `report_images`
  ADD CONSTRAINT `report_images_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `lost_reports` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
