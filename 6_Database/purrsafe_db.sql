-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 07, 2024 at 06:18 AM
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
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `feedback_text` text NOT NULL,
  `feedback_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','returned') DEFAULT 'pending',
  `return_confirmed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `found_reports`
--

INSERT INTO `found_reports` (`id`, `user_id`, `report_id`, `owner_notification`, `founder_name`, `contact_number`, `image_path`, `created_at`, `status`, `return_confirmed_at`) VALUES
(7, 3, 19, 'Good', 'John Lorcan Paraiso', '09265231560', NULL, '2024-12-06 11:31:06', 'pending', NULL),
(11, 3, 24, 'Safe', 'John Lorcan Paraiso', '09265231560', '67539d301d0fb_1733532976.jpg', '2024-12-07 00:56:16', 'pending', NULL);

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
(17, 4, 'Chrys', 'Munchkin', 'male', 2, 'Black with yellow and white', 'The cat is very clingy, friendly and outgoing. Please feed him once u saw him, he\'s always hungry.', '2024-11-13', '09:40:00', 'Raymond Jerard Madrid', '09123456789', '2024-12-04 07:41:23', 'Brgy. San Agustin Alaminos Laguna', 'lost', NULL, NULL),
(19, 1, 'Tabby', 'American Shorthair', 'male', 2, 'Tricolors of black yellow and white', 'Always hungry, feed him when found.', '2024-12-04', '09:00:00', 'Jaika Remina Madrid', '09189258041', '2024-12-06 01:00:31', 'SM Lipa', 'found', NULL, NULL),
(20, 3, 'Layla', 'Birman', 'female', 2, 'Grey', 'Cute', '2024-12-06', '18:35:00', 'John Lorcan Paraiso', '09265231560', '2024-12-06 09:36:25', 'West Park', 'missing', NULL, '2024-12-06 12:16:42'),
(24, 2, 'Robin', 'Siamese', 'male', 4, 'Grey', 'Last seen this morning', '2024-12-04', '08:53:00', 'Jaika Remina Madrid', '09265231560', '2024-12-07 00:53:26', 'At the restaurant', 'found', NULL, NULL),
(25, 6, 'Justout', 'Exotic Shorthair', 'male', 4, 'Grey', 'Healthy', '2024-12-04', '22:15:00', 'Justin Kyle', '09265231560', '2024-12-07 02:14:54', 'BSU Lipa', 'missing', NULL, NULL),
(26, 6, 'Akali', 'Ragdoll', 'female', 6, 'White', 'Healthy', '2024-12-02', '22:18:00', 'Justin Kyle', '09265231560', '2024-12-07 02:18:12', 'At the restaurant', 'missing', NULL, NULL),
(27, 6, 'Tommy', 'Persian', 'male', 2, 'Gold', 'He loves to cuddle.', '2024-12-03', '10:25:00', 'Justin Kyle', '09265231560', '2024-12-07 02:23:01', 'At the woods', 'missing', NULL, NULL);

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
(1, 2, 'Someone has found your cat! Check the found reports for details.', 1, '2024-12-04 23:37:53'),
(2, 4, 'Thank you for finding the cat! The owner has been notified and will contact you soon.', 0, '2024-12-04 23:37:53'),
(3, 4, 'Good news! Your cat \'Chrys\' has been found! Someone has submitted a found report. Please check your found reports section for contact details of the person who found your cat.', 0, '2024-12-04 23:54:55'),
(4, 2, 'Thank you for submitting a found report for the cat \'Chrys\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 1, '2024-12-04 23:54:55'),
(5, 1, 'Good news! Your cat \'Whitney\' has been found! Someone has submitted a found report. Please check your found reports section for contact details of the person who found your cat.', 0, '2024-12-05 00:25:09'),
(6, 4, 'Thank you for submitting a found report for the cat \'Whitney\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-05 00:25:09'),
(7, 1, 'Good news! Your cat \'Whitney\' has been found! Someone has submitted a found report. Please check your found reports section for contact details of the person who found your cat.', 0, '2024-12-05 00:25:57'),
(8, 4, 'Thank you for submitting a found report for the cat \'Whitney\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-05 00:25:57'),
(9, 1, 'Good news! Your cat \'Tabby\' has been found! Check your found reports section for contact details of the person who found your cat.', 0, '2024-12-06 11:31:06'),
(10, 3, 'Thank you for submitting a found report for the cat \'Tabby\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 1, '2024-12-06 11:31:06'),
(11, 2, 'Good news! Your cat \'Serena\' has been found! Check your found reports section for contact details of the person who found your cat.', 1, '2024-12-06 13:05:54'),
(12, 3, 'Thank you for submitting a found report for the cat \'Serena\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 1, '2024-12-06 13:05:54'),
(13, 3, 'Good news! Your cat \'Fiona\' has been found! Check your found reports section for contact details of the person who found your cat.', 1, '2024-12-06 17:03:13'),
(14, 1, 'Thank you for submitting a found report for the cat \'Fiona\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-06 17:03:13'),
(15, 3, 'Good news! Your cat \'Clark\' has been found! Check your found reports section for contact details of the person who found your cat.', 1, '2024-12-06 17:23:40'),
(16, 5, 'Thank you for submitting a found report for the cat \'Clark\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 0, '2024-12-06 17:23:40'),
(17, 2, 'Good news! Your cat \'Robin\' has been found! Check your found reports section for contact details of the person who found your cat.', 1, '2024-12-07 00:56:16'),
(18, 3, 'Thank you for submitting a found report for the cat \'Robin\'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.', 1, '2024-12-07 00:56:16');

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
(20, 17, 'uploads/675007a38c8fb_cute-adorable-playfull-munchkin-kitten_MDavidova_Shutterstock.jpg', '2024-12-04 07:41:23'),
(22, 19, '../../5_Uploads/67524cafec88c_brown-tabby-cat-1103904.jpg', '2024-12-06 01:00:31'),
(23, 20, '../../5_Uploads/6752c59921942_birman.jpg', '2024-12-06 09:36:25'),
(28, 24, '../../5_Uploads/67539c86d0478_images (2).jpg', '2024-12-07 00:53:26'),
(29, 25, '../../5_Uploads/6753af9e127d8_CAT HERO_0010_Exotic_shorthair.jpg', '2024-12-07 02:14:54'),
(30, 26, '../../5_Uploads/6753b06470cd3_800px-Ragdoll_from_Gatil_Ragbelas.jpg', '2024-12-07 02:18:12'),
(31, 27, '../../5_Uploads/6753b185a96ac_Persialainen.jpg', '2024-12-07 02:23:01');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `issue_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in_progress','resolved','closed') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `admin_response` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `issue_type`, `description`, `status`, `created_at`, `updated_at`, `admin_response`, `admin_id`) VALUES
(1, 2, 'technical', 'Slow page', 'pending', '2024-12-07 13:05:31', '2024-12-07 13:05:31', NULL, NULL),
(2, 2, 'technical', 'Slow page', 'pending', '2024-12-07 13:06:28', '2024-12-07 13:06:28', NULL, NULL);

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
  `profile_pic` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `created_at`, `profile_pic`, `profile_image`) VALUES
(1, 'Jaika', 'jaikajeon', 'jaikajeon@gmail.com', '$2y$10$CrmkoWVkrwAHFmPln3n0e.uJ/qarpeHQVCIXuWBv.nVyebnkCS4pC', '2024-11-17 03:17:06', NULL, NULL),
(2, 'Jaika Remina Madrid', 'remwina', 'remwina@gmail.com', '$2y$10$HPVp4NCM/oyW1wbzyNSmHervpn4JMvoOtcg1uF/4geEHinMmJAfd2', '2024-11-17 09:45:29', NULL, 'profile_2_1733532732.png'),
(3, 'John Lorcan Paraiso', 'Lorx', 'johnlorcparadise@gmail.com', '$2y$10$wHL1KYlrtAWz3H1TXH5vSu.1XQFk5B/yS748Z06P.bpjJry8v.Gka', '2024-11-21 15:02:22', NULL, 'profile_3_1733531717.png'),
(4, 'Raymond Madrid', 'reirei', 'rjmadrid@gmail.com', '$2y$10$KbZ6n/zMI/IjZWymXmjvMOecF5mveWqNdrfyrTRAw.E0uLPbT1yUa', '2024-12-04 06:14:16', NULL, NULL),
(5, 'Taylor', 'Swift', 'taylor@example.com', '$2y$10$anLrT5cxVsX4j/FA27Nz9eZ27Y3GukChqx8aXOWuS9mUEzhD1sGqe', '2024-12-06 14:13:47', NULL, NULL),
(6, 'Justin Kyle', 'Katigbak', 'example@gmail.com', '$2y$10$61NGpEV4EwdvnQ5F0Yilp.OB1XQin712LGmpZ0.9frk0wlVibQw..', '2024-12-07 02:09:15', NULL, 'profile_6_1733537453.png');

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
-- Indexes for table `report_images`
--
ALTER TABLE `report_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

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
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `found_reports`
--
ALTER TABLE `found_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `lost_reports`
--
ALTER TABLE `lost_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `report_images`
--
ALTER TABLE `report_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `found_reports` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `report_images`
--
ALTER TABLE `report_images`
  ADD CONSTRAINT `report_images_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `lost_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
