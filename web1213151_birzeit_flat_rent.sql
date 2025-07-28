-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 13, 2025 at 04:08 PM
-- Server version: 8.0.42
-- PHP Version: 8.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `web1213151_birzeit_flat_rent`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int NOT NULL,
  `flat_ref_no` varchar(10) NOT NULL,
  `customer_id` varchar(10) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `status` enum('available','booked','completed','cancelled') DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `flat_ref_no`, `customer_id`, `appointment_date`, `time_from`, `time_to`, `status`, `created_at`) VALUES
(1, 'F001', NULL, '2025-06-10', '10:00:00', '11:00:00', 'available', '2025-06-05 13:47:25'),
(2, 'F001', NULL, '2025-06-10', '14:00:00', '15:00:00', 'available', '2025-06-05 13:47:25'),
(3, 'F001', NULL, '2025-06-11', '11:00:00', '12:00:00', 'available', '2025-06-05 13:47:25'),
(4, 'F002', 'C002', '2025-06-12', '10:00:00', '11:00:00', 'booked', '2025-06-05 13:47:25'),
(5, 'F002', NULL, '2025-06-12', '15:00:00', '16:00:00', 'available', '2025-06-05 13:47:25'),
(6, 'F003', 'C003', '2025-06-15', '13:00:00', '14:00:00', 'booked', '2025-06-05 13:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `flats`
--

CREATE TABLE `flats` (
  `flat_ref_no` varchar(10) NOT NULL,
  `owner_id` varchar(10) NOT NULL,
  `location` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `rent_cost` decimal(10,2) NOT NULL,
  `available_from` date NOT NULL,
  `available_to` date NOT NULL,
  `bedrooms` int NOT NULL,
  `bathrooms` int NOT NULL,
  `size_sqm` decimal(10,2) NOT NULL,
  `rental_conditions` text,
  `heating` tinyint(1) DEFAULT '0',
  `ac` tinyint(1) DEFAULT '0',
  `access_control` varchar(50) DEFAULT NULL,
  `status` enum('available','rented','pending_approval','unavailable') DEFAULT 'pending_approval',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `flats`
--

INSERT INTO `flats` (`flat_ref_no`, `owner_id`, `location`, `address`, `rent_cost`, `available_from`, `available_to`, `bedrooms`, `bathrooms`, `size_sqm`, `rental_conditions`, `heating`, `ac`, `access_control`, `status`, `created_at`) VALUES
('F001', 'O001', 'Birzeit University Area', '123 University Street, Birzeit', 650.00, '2025-06-01', '2026-05-31', 3, 2, 120.00, 'Minimum 1 year lease, no pets.', 1, 1, 'Key Card', 'available', '2025-06-05 13:47:25'),
('F002', 'O001', 'Birzeit Downtown', '45 Main Street, Birzeit', 500.00, '2025-06-15', '2026-06-14', 2, 1, 85.00, 'Minimum 6 months lease.', 1, 0, 'Key', 'available', '2025-06-05 13:47:25'),
('F003', 'O002', 'Ramallah Al-Masyoun', '78 Al-Nahda Street, Ramallah', 800.00, '2025-07-01', '2026-06-30', 4, 2, 150.00, 'Minimum 1 year lease, no smoking.', 1, 1, 'Security Code', 'available', '2025-06-05 13:47:25'),
('F004', 'O002', 'Birzeit University Area', '56 College Road, Birzeit', 550.00, '2025-06-10', '2026-06-09', 2, 1, 90.00, 'Students preferred, minimum 9 months lease.', 0, 1, 'Key', 'rented', '2025-06-05 13:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `flat_features`
--

CREATE TABLE `flat_features` (
  `flat_ref_no` varchar(10) NOT NULL,
  `feature_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `flat_features`
--

INSERT INTO `flat_features` (`flat_ref_no`, `feature_name`) VALUES
('F001', 'backyard_shared'),
('F001', 'parking'),
('F001', 'storage'),
('F002', 'parking'),
('F003', 'backyard_individual'),
('F003', 'parking'),
('F003', 'playground'),
('F003', 'storage'),
('F004', 'backyard_shared');

-- --------------------------------------------------------

--
-- Table structure for table `flat_marketing`
--

CREATE TABLE `flat_marketing` (
  `marketing_id` int NOT NULL,
  `flat_ref_no` varchar(10) NOT NULL,
  `info_type` enum('landmark','important_place') NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `flat_marketing`
--

INSERT INTO `flat_marketing` (`marketing_id`, `flat_ref_no`, `info_type`, `name`, `url`) VALUES
(1, 'F001', 'landmark', 'Birzeit University', 'https://www.birzeit.edu/'),
(2, 'F001', 'important_place', 'Local Market', 'https://maps.example.com/local-market'),
(3, 'F001', 'important_place', 'Pharmacy', 'https://maps.example.com/pharmacy'),
(4, 'F002', 'landmark', 'Birzeit Old City', 'https://maps.example.com/old-city'),
(5, 'F002', 'important_place', 'Supermarket', 'https://maps.example.com/supermarket'),
(6, 'F003', 'landmark', 'Ramallah Cultural Palace', 'https://maps.example.com/cultural-palace'),
(7, 'F003', 'important_place', 'Shopping Mall', 'https://maps.example.com/shopping-mall'),
(8, 'F004', 'landmark', 'Birzeit University', 'https://www.birzeit.edu/'),
(9, 'F004', 'important_place', 'Cafeteria', 'https://maps.example.com/cafeteria');

-- --------------------------------------------------------

--
-- Table structure for table `flat_photos`
--

CREATE TABLE `flat_photos` (
  `photo_id` int NOT NULL,
  `flat_ref_no` varchar(10) NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `photo_description` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `flat_photos`
--

INSERT INTO `flat_photos` (`photo_id`, `flat_ref_no`, `photo_path`, `photo_description`, `is_primary`) VALUES
(1, 'F001', 'images/flats/F001/photo1.jpg', 'Living Room', 1),
(2, 'F001', 'images/flats/F001/photo2.jpg', 'Kitchen', 0),
(3, 'F001', 'images/flats/F001/photo3.jpg', 'Master Bedroom', 0),
(4, 'F002', 'images/flats/F002/photo1.jpg', 'Front View', 1),
(5, 'F002', 'images/flats/F002/photo2.jpg', 'Living Area', 0),
(6, 'F002', 'images/flats/F002/photo3.jpg', 'Bathroom', 0),
(7, 'F003', 'images/flats/F003/photo1.jpg', 'Building Exterior', 1),
(8, 'F003', 'images/flats/F003/photo2.jpg', 'Living Room', 0),
(9, 'F003', 'images/flats/F003/photo3.jpg', 'Kitchen', 0),
(10, 'F003', 'images/flats/F003/photo4.jpg', 'Master Bedroom', 0),
(11, 'F004', 'images/flats/F004/photo1.jpg', 'Front View', 1),
(12, 'F004', 'images/flats/F004/photo2.jpg', 'Living Area', 0),
(13, 'F004', 'images/flats/F004/photo3.jpg', 'Bedroom', 0);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int NOT NULL,
  `recipient_id` varchar(10) NOT NULL,
  `sender_id` varchar(10) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `related_link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `recipient_id`, `sender_id`, `subject`, `content`, `related_link`, `is_read`, `created_at`) VALUES
(1, 'O001', 'C002', 'Appointment Request', 'Customer C002 requested viewing for Flat F002 on 2025-06-12.', 'appointments.php?flat=F002', 0, '2025-06-05 13:47:25'),
(2, 'C002', 'O001', 'Appointment Confirmation', 'Your appointment request for Flat F002 on 2025-06-12 has been confirmed.', 'appointments.php', 0, '2025-06-05 13:47:25'),
(3, 'O002', 'C003', 'Appointment Request', 'Customer C003 requested viewing for Flat F003 on 2025-06-15.', 'appointments.php?flat=F003', 1, '2025-06-05 13:47:25'),
(4, 'C003', 'O002', 'Appointment Confirmation', 'Your appointment request for Flat F003 on 2025-06-15 has been confirmed.', 'appointments.php', 0, '2025-06-05 13:47:25'),
(5, 'O002', 'C001', 'Rental Agreement', 'Customer C001 has rented Flat F004 from 2025-06-10 to 2026-06-09.', 'rentals.php?ref=F004', 1, '2025-06-05 13:47:25'),
(6, 'C001', 'O002', 'Rental Confirmation', 'Your rental of Flat F004 has been confirmed. You can collect the keys on 2025-06-10.', 'rentals.php', 0, '2025-06-05 13:47:25'),
(7, 'M001', NULL, 'New Flat Pending Approval', 'Flat F001 offered by Owner O001 needs approval.', 'admin_approve_flat.php?ref=F001', 0, '2025-06-05 13:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int NOT NULL,
  `flat_ref_no` varchar(10) NOT NULL,
  `customer_id` varchar(10) NOT NULL,
  `rental_start_date` date NOT NULL,
  `rental_end_date` date NOT NULL,
  `total_rent` decimal(10,2) NOT NULL,
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_method` varchar(50) DEFAULT 'credit_card',
  `payment_reference` varchar(50) DEFAULT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`rental_id`, `flat_ref_no`, `customer_id`, `rental_start_date`, `rental_end_date`, `total_rent`, `payment_date`, `payment_method`, `payment_reference`, `status`) VALUES
(1, 'F004', 'C001', '2025-06-10', '2026-06-09', 6600.00, '2025-06-05 07:30:00', 'credit_card', 'TXN123456', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(10) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('customer','owner','manager') NOT NULL,
  `national_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `address_flat` varchar(50) DEFAULT NULL,
  `address_street` varchar(100) DEFAULT NULL,
  `address_city` varchar(50) DEFAULT NULL,
  `address_postal` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive','pending') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `user_type`, `national_id`, `full_name`, `address_flat`, `address_street`, `address_city`, `address_postal`, `date_of_birth`, `email`, `mobile`, `telephone`, `registration_date`, `status`) VALUES
('C001', 'customer1@example.com', 'Customer1Pass', 'customer', '123456789', 'Ahmad Sumary', '15', 'Al-Nahda Street', 'Ramallah', '00970', '1990-05-15', 'customer1@example.com', '0599123456', '02987654', '2025-06-05 13:47:25', 'active'),
('C002', 'customer2@example.com', 'Customer2Pass', 'customer', '234567890', 'Fatima Abuleil', '23', 'Al-Irsal Street', 'Birzeit', '00970', '1988-09-22', 'customer2@example.com', '0598234567', '02876543', '2025-06-05 13:47:25', 'active'),
('C003', 'customer3@example.com', 'Customer3Pass', 'customer', '345678901', 'Mohammed Ali', '7', 'University Street', 'Birzeit', '00970', '1995-03-10', 'customer3@example.com', '0597345678', '02765432', '2025-06-05 13:47:25', 'active'),
('M001', 'manager@example.com', 'Manager1Pass', 'manager', '678901234', 'Hind Sumary', '5', 'Al-Tireh', 'Ramallah', '00970', '1985-12-18', 'manager@example.com', '0594678901', '02432109', '2025-06-05 13:47:25', 'active'),
('O001', 'owner1@example.com', 'Owner1Pass', 'owner', '456789012', 'Amin Sumary', '42', 'Al-Masyoun', 'Ramallah', '00970', '1975-11-30', 'owner1@example.com', '0596456789', '02654321', '2025-06-05 13:47:25', 'active'),
('O002', 'owner2@example.com', 'Owner2Pass', 'owner', '567890123', 'Fadi Khalil', '18', 'Ein Misbah', 'Ramallah', '00970', '1982-07-25', 'owner2@example.com', '0595567890', '02543210', '2025-06-05 13:47:25', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `flat_ref_no` (`flat_ref_no`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `flats`
--
ALTER TABLE `flats`
  ADD PRIMARY KEY (`flat_ref_no`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `flat_features`
--
ALTER TABLE `flat_features`
  ADD PRIMARY KEY (`flat_ref_no`,`feature_name`);

--
-- Indexes for table `flat_marketing`
--
ALTER TABLE `flat_marketing`
  ADD PRIMARY KEY (`marketing_id`),
  ADD KEY `flat_ref_no` (`flat_ref_no`);

--
-- Indexes for table `flat_photos`
--
ALTER TABLE `flat_photos`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `flat_ref_no` (`flat_ref_no`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `flat_ref_no` (`flat_ref_no`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `flat_marketing`
--
ALTER TABLE `flat_marketing`
  MODIFY `marketing_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `flat_photos`
--
ALTER TABLE `flat_photos`
  MODIFY `photo_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`flat_ref_no`) REFERENCES `flats` (`flat_ref_no`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `flats`
--
ALTER TABLE `flats`
  ADD CONSTRAINT `flats_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `flat_features`
--
ALTER TABLE `flat_features`
  ADD CONSTRAINT `flat_features_ibfk_1` FOREIGN KEY (`flat_ref_no`) REFERENCES `flats` (`flat_ref_no`) ON DELETE CASCADE;

--
-- Constraints for table `flat_marketing`
--
ALTER TABLE `flat_marketing`
  ADD CONSTRAINT `flat_marketing_ibfk_1` FOREIGN KEY (`flat_ref_no`) REFERENCES `flats` (`flat_ref_no`) ON DELETE CASCADE;

--
-- Constraints for table `flat_photos`
--
ALTER TABLE `flat_photos`
  ADD CONSTRAINT `flat_photos_ibfk_1` FOREIGN KEY (`flat_ref_no`) REFERENCES `flats` (`flat_ref_no`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`flat_ref_no`) REFERENCES `flats` (`flat_ref_no`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
