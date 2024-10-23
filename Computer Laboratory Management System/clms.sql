-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2024 at 01:36 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clms`
--

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `serial_number` varchar(255) NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `date_purchased` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `serial_number`, `lab_id`, `name`, `status`, `brand`, `picture`, `quantity`, `date_purchased`) VALUES
(1, 'SN001', 1, 'High-end PC', 'Under Maintenance', 'BrandA', 'path/to/image1.jpg', 10, '2023-01-15'),
(2, 'SN002', 2, 'Mid-range PC', 'Functional', 'BrandB', 'path/to/image2.jpg', 5, '2022-08-10'),
(3, 'SN003', 3, 'Laser Printer', 'Functional', 'BrandC', 'path/to/image3.jpg', 3, '2021-05-20'),
(4, 'SN004', 4, 'High-speed Scanner', 'Functional', 'BrandD', 'path/to/image4.jpg', 2, '2020-11-25'),
(5, 'SN005', 1, 'Projector', 'Under Maintenance', 'BrandE', 'path/to/image5.jpg', 1, '2019-03-30'),
(6, 'SN006', 2, 'Desktop Monitor', 'Functional', 'BrandF', 'path/to/image6.jpg', 8, '2021-09-12'),
(7, 'SN007', 3, '3D Printer', 'Functional', 'BrandG', 'path/to/image7.jpg', 1, '2020-06-18'),
(8, 'SN008', 4, 'Webcam', 'Functional', 'BrandH', 'path/to/image8.jpg', 20, '2022-04-05');

-- --------------------------------------------------------

--
-- Table structure for table `laboratories`
--

CREATE TABLE `laboratories` (
  `id` int(11) NOT NULL,
  `lab_name` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratories`
--

INSERT INTO `laboratories` (`id`, `lab_name`) VALUES
(1, 'HF202'),
(2, 'HF203'),
(3, 'HF204'),
(4, 'HF304');

-- --------------------------------------------------------

--
-- Table structure for table `labs`
--

CREATE TABLE `labs` (
  `lab_id` int(11) NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `assistant_id` int(11) DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `labs`
--

INSERT INTO `labs` (`lab_id`, `room_name`, `assistant_id`, `inserted_on`, `updated_on`, `capacity`) VALUES
(1, 'HF202', 13, '2024-06-07 09:00:00', '2024-06-07 09:00:00', 35),
(2, 'HF203', 13, '2024-06-07 09:05:00', '2024-06-07 09:05:00', 23),
(3, 'HF204', 13, '2024-06-07 09:10:00', '2024-06-07 09:10:00', 41),
(4, 'HF304', 13, '2024-06-07 09:15:00', '2024-06-07 09:15:00', 55);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','assistant') NOT NULL,
  `picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `password`, `role`, `picture`) VALUES
(13, 'Anne', 'Assistant Anne', 'Anne', 'assistant', 'dance-like-a-chamiya-shoulder-dance.gif'),
(16, 'admin', 'admin', 'admin', 'admin', 'qwerty.jpg'),
(31, 'Joe', 'Assistant Joe', 'Joe', 'assistant', 'ae2f6e4003a94359025495045528c5c8.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `laboratories`
--
ALTER TABLE `laboratories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_name` (`lab_name`);

--
-- Indexes for table `labs`
--
ALTER TABLE `labs`
  ADD PRIMARY KEY (`lab_id`),
  ADD KEY `assistant_id` (`assistant_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `laboratories`
--
ALTER TABLE `laboratories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `labs`
--
ALTER TABLE `labs`
  MODIFY `lab_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`lab_id`);

--
-- Constraints for table `labs`
--
ALTER TABLE `labs`
  ADD CONSTRAINT `labs_ibfk_1` FOREIGN KEY (`assistant_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
