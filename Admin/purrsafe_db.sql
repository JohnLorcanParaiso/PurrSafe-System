-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 21, 2024 at 06:17 PM
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
(6, 1, 'Whitney', 'Persian', 'female', 3, 'White', 'n/a', '2024-11-17', '08:48:00', 'Raymond Jerard Madrid', '09189258041', '2024-11-19 12:48:42', NULL);

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
(5, 6, 'uploads/673c892ae6736_Whitepersiancatoncouch-aba536ea9760403dac2042cc4c47144d.jpg', '2024-11-19 12:48:42');

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
-- AUTO_INCREMENT for table `found_reports`
--
ALTER TABLE `found_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lost_reports`
--
ALTER TABLE `lost_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `report_images`
--
ALTER TABLE `report_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Constraints for table `lost_reports`
--
ALTER TABLE `lost_reports`
  ADD CONSTRAINT `lost_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `report_images`
--
ALTER TABLE `report_images`
  ADD CONSTRAINT `report_images_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `lost_reports` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
