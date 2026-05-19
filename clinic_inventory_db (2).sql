-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2026 at 10:56 AM
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
-- Database: `clinic_inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `record_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `height` decimal(5,1) DEFAULT NULL,
  `weight` decimal(5,1) DEFAULT NULL,
  `bmi` decimal(4,1) DEFAULT NULL,
  `bmi_category` varchar(20) DEFAULT NULL,
  `systolic_bp` int(11) DEFAULT NULL,
  `diastolic_bp` int(11) DEFAULT NULL,
  `bp_category` varchar(30) DEFAULT NULL,
  `total_cholesterol` decimal(6,1) DEFAULT NULL,
  `hdl_cholesterol` decimal(6,1) DEFAULT NULL,
  `smoking` tinyint(1) DEFAULT 0,
  `diabetes` tinyint(1) DEFAULT 0,
  `bp_treatment` tinyint(1) DEFAULT 0,
  `cvd_risk_percent` decimal(4,1) DEFAULT NULL,
  `cvd_category` varchar(20) DEFAULT NULL,
  `health_score` int(11) DEFAULT NULL,
  `health_category` varchar(20) DEFAULT NULL,
  `source` varchar(10) DEFAULT 'self',
  `notes` text DEFAULT NULL,
  `checked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `health_records`
--

INSERT INTO `health_records` (`record_id`, `user_id`, `age`, `sex`, `height`, `weight`, `bmi`, `bmi_category`, `systolic_bp`, `diastolic_bp`, `bp_category`, `total_cholesterol`, `hdl_cholesterol`, `smoking`, `diabetes`, `bp_treatment`, `cvd_risk_percent`, `cvd_category`, `health_score`, `health_category`, `source`, `notes`, `checked_at`) VALUES
(1, 2, 20, 'male', 170.0, 80.0, 27.7, 'Overweight', 120, 80, '0', 180.0, 50.0, 0, 0, 0, 3.0, '0', 66, 'Good', 'self', NULL, '2026-05-07 14:41:55'),
(2, 2, 20, 'male', 170.0, 80.0, 27.7, 'Overweight', 120, 80, '0', 180.0, 50.0, 0, 0, 0, 3.0, '0', 66, 'Good', 'self', NULL, '2026-05-07 14:42:09'),
(3, 2, 20, 'male', 170.0, 80.0, 27.7, 'Overweight', 120, 80, '0', 180.0, 50.0, 0, 0, 0, 3.0, '0', 66, 'Good', 'self', NULL, '2026-05-07 14:42:15'),
(4, 2, 20, 'male', 170.0, 80.0, 27.7, 'Overweight', 120, 80, '0', 180.0, 50.0, 0, 0, 0, 3.0, '0', 66, 'Good', 'self', NULL, '2026-05-07 14:43:01'),
(5, 2, 25, 'female', 150.0, 40.0, 17.8, 'Underweight', 130, 90, '0', 170.0, 40.0, 1, 0, 0, 8.0, '0', 56, 'Fair', 'self', NULL, '2026-05-07 14:43:36'),
(6, 2, 25, 'female', 150.0, 40.0, 17.8, 'Underweight', 130, 90, '0', 170.0, 40.0, 1, 0, 0, 8.0, '0', 56, 'Fair', 'self', NULL, '2026-05-07 14:43:41'),
(7, 2, 25, 'female', 150.0, 40.0, 17.8, 'Underweight', 130, 90, '0', 170.0, 40.0, 1, 0, 0, 8.0, '0', 56, 'Fair', 'self', NULL, '2026-05-07 14:44:07'),
(8, 2, 25, 'female', 150.0, 40.0, 17.8, 'Underweight', 130, 90, '0', 170.0, 40.0, 1, 0, 0, 8.0, '0', 56, 'Fair', 'self', NULL, '2026-05-07 14:44:08'),
(9, 2, 20, 'male', 120.0, 68.0, 47.2, 'Obese', 80, 68, '0', 120.0, 60.0, 0, 0, 0, 2.0, '0', 73, 'Good', 'self', NULL, '2026-05-07 14:52:04'),
(10, 2, 20, 'male', 120.0, 68.0, 47.2, 'Obese', 80, 68, '0', 120.0, 60.0, 0, 0, 0, 2.0, '0', 73, 'Good', 'self', NULL, '2026-05-07 14:52:07'),
(11, 2, 20, 'male', 120.0, 68.0, 47.2, 'Obese', 80, 68, '0', 120.0, 60.0, 0, 0, 0, 2.0, '0', 73, 'Good', 'self', NULL, '2026-05-07 14:52:14'),
(12, 2, 20, 'male', 160.0, 60.0, 23.4, 'Normal', 120, 80, '0', 60.0, 60.0, 0, 0, 0, 2.0, '0', 83, '0', '0', '', '2026-05-14 09:41:57'),
(13, 1, 20, 'male', 160.0, 60.0, 23.4, 'Normal', 120, 80, '0', 80.0, 90.0, 0, 0, 0, 2.0, '0', 83, '0', '0', '', '2026-05-14 09:42:53'),
(14, 2, 20, 'male', 165.0, 60.0, 22.0, 'Normal', 120, 80, '0', 80.0, 90.0, 0, 0, 0, 2.0, '0', 83, '0', '0', '', '2026-05-18 16:19:27');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `medicine_id` int(11) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit` varchar(20) DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `date_added` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'In Stock',
  `remarks` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`medicine_id`, `medicine_name`, `category`, `quantity`, `unit`, `expiration_date`, `date_added`, `status`, `remarks`, `image`) VALUES
(21, 'Paracetamol', '', 88, '', '2028-01-03', '2026-04-27', 'In Stock', '', 'med_69eec9f62c6b6.jpg'),
(23, 'Paracetamol 500mg', 'Analgesic', 96, 'tablets', '2027-12-06', '2026-05-06', 'In Stock', 'For fever and mild pain', 'med_69faaa9bdc98a.png'),
(25, 'romel', 'Analgesic', 1, 'bottles', '2026-05-30', '2026-05-07', 'Low Stock', 'yujghjujyt', 'med_69fc3740b2ae2.jpg'),
(26, 'me', 'First Aid', 0, 'boxes', '2026-05-07', '2026-05-07', 'Out of Stock', 'fgyuhtufg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medicine_requests`
--

CREATE TABLE `medicine_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `reason` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `acted_at` datetime DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medicine_requests`
--

INSERT INTO `medicine_requests` (`request_id`, `user_id`, `medicine_id`, `quantity`, `reason`, `status`, `requested_at`, `acted_at`, `admin_notes`) VALUES
(1, 1, 21, 1, 'headache', 'Approved', '2026-05-06 10:30:35', '2026-05-07 14:53:50', 'dfgrddddddddddf'),
(2, 1, 21, 1, 'sdkfdsg', 'Approved', '2026-05-07 08:37:00', '2026-05-07 08:39:06', ''),
(3, 2, 23, 4, 'gdgdf', 'Approved', '2026-05-07 14:14:52', '2026-05-07 14:15:09', ''),
(4, 1, 21, 10, '', 'Approved', '2026-05-07 15:03:13', '2026-05-07 15:03:30', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `grade` varchar(20) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `student_id`, `full_name`, `grade`, `section`, `email`, `password`, `status`, `created_at`) VALUES
(1, '2024-001', 'Test Student', 'Grade 11', 'Rizal', 'test@gmail.com', 'test123', 'Active', '2026-05-06 10:10:15'),
(2, '2024-002', 'romel', '3rd Year', '3A', 'romelsuarez@gmail.com', 'romel123', 'Active', '2026-05-07 14:14:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`medicine_id`);

--
-- Indexes for table `medicine_requests`
--
ALTER TABLE `medicine_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `medicine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `medicine_requests`
--
ALTER TABLE `medicine_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `medicine_requests`
--
ALTER TABLE `medicine_requests`
  ADD CONSTRAINT `medicine_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `medicine_requests_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
