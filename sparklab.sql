-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2025 at 09:00 PM
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
-- Database: `sparklab`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `mpesa_receipt_number` varchar(50) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `client_id`, `service_id`, `quantity`, `created_at`, `status`, `admin_note`, `updated_at`, `payment_status`, `mpesa_receipt_number`, `phone_number`, `amount_paid`, `transaction_date`) VALUES
(9, 2, 26, 2, '2025-09-25 09:52:43', 'completed', '', '2025-09-29 18:57:05', 'pending', NULL, '254711741263', 10000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `get_in_touch`
--

CREATE TABLE `get_in_touch` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `get_in_touch`
--

INSERT INTO `get_in_touch` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`) VALUES
(1, 'Andrew Saitabau', 'saa143879@gmail.com', '', '', 'hhh', '2025-09-25 08:45:18'),
(2, 'Andrew Saitabau', 'saa143879@gmail.com', '0711741263', 'your system was completed', 'jjj', '2025-09-25 08:45:36');

-- --------------------------------------------------------

--
-- Table structure for table `mpesa_payments`
--

CREATE TABLE `mpesa_payments` (
  `id` int(11) NOT NULL,
  `merchant_request_id` varchar(100) DEFAULT NULL,
  `checkout_request_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `mpesa_receipt` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `client_id`, `total_amount`, `status`, `created_at`) VALUES
(1, 2, 1500.00, 'pending', '2025-09-18 13:13:54'),
(2, 2, 1500.00, 'pending', '2025-09-18 13:14:20'),
(3, 2, 13500.00, 'pending', '2025-09-18 13:31:41'),
(4, 2, 5000.00, 'pending', '2025-09-18 13:44:07'),
(5, 2, 4500.00, 'pending', '2025-09-25 09:08:21'),
(6, 2, 2000.00, 'pending', '2025-09-25 09:19:40');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `service_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 1500.00),
(2, 2, 1, 1, 1500.00),
(3, 3, 1, 1, 1500.00),
(4, 3, 20, 1, 10000.00),
(5, 3, 2, 1, 2000.00),
(6, 4, 26, 1, 5000.00),
(7, 5, 17, 1, 4500.00),
(8, 6, 2, 1, 2000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `mpesa_receipt_number` varchar(50) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cart_item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `cart_id`, `client_id`, `amount`, `phone_number`, `mpesa_receipt_number`, `transaction_date`, `status`, `created_at`, `cart_item_id`) VALUES
(1, 9, 2, 10000.00, '254711741263', NULL, NULL, 'pending', '2025-09-25 17:14:12', NULL),
(2, 9, 2, 10000.00, '254711741263', NULL, NULL, 'pending', '2025-09-25 17:14:35', NULL),
(3, 9, 2, 10000.00, '254711741263', NULL, NULL, 'pending', '2025-09-25 17:19:09', NULL),
(4, 9, 2, 10000.00, '254711741263', NULL, NULL, 'pending', '2025-09-25 17:19:45', NULL),
(5, 9, 2, 10000.00, '254711741263', NULL, NULL, 'pending', '2025-09-25 17:21:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_requests`
--

CREATE TABLE `project_requests` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `request_text` text NOT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `client_id`, `message`, `service_id`, `request_text`, `status`, `created_at`) VALUES
(1, 2, 'jjj', NULL, '', '', '2025-09-25 09:57:38');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `created_at`) VALUES
(1, 'Computer Hardware & Software Troubleshooting', 'Diagnosis and repair of computer hardware issues and software problems.', 1500.00, '2025-09-18 13:10:57'),
(2, 'Networking Setup & Configuration', 'Installation and configuration of LAN, WiFi, and internet connections.', 2000.00, '2025-09-18 13:10:57'),
(3, 'Data Backup & Recovery', 'Secure data backup solutions and recovery from damaged drives.', 2500.00, '2025-09-18 13:10:57'),
(4, 'IT Consultancy', 'Professional advice on ICT systems, security, and optimization.', 3000.00, '2025-09-18 13:10:57'),
(5, 'Website Development & Hosting', 'Custom website design, development, and hosting services.', 50000.00, '2025-09-18 13:10:57'),
(6, 'Computer Hardware & Software Troubleshooting', 'Diagnosis and repair of computer hardware issues and software problems.', 1500.00, '2025-09-18 13:22:33'),
(7, 'Networking Setup & Configuration', 'Installation and configuration of LAN, WiFi, and internet connections.', 2000.00, '2025-09-18 13:22:33'),
(8, 'Data Backup & Recovery', 'Secure data backup solutions and recovery from damaged drives.', 2500.00, '2025-09-18 13:22:33'),
(9, 'IT Consultancy', 'Professional advice on ICT systems, security, and optimization.', 3000.00, '2025-09-18 13:22:33'),
(10, 'Website Development & Hosting', 'Custom website design, development, and hosting services.', 5000.00, '2025-09-18 13:22:33'),
(11, 'Cybersecurity Solutions', 'Implementation of firewalls, antivirus, penetration testing, and secure access systems.', 4000.00, '2025-09-18 13:22:33'),
(12, 'Cloud Services & Migration', 'Setup and migration to cloud platforms like AWS, Azure, or Google Cloud.', 6000.00, '2025-09-18 13:22:33'),
(13, 'Software Installation & Licensing', 'Installation and configuration of licensed software for businesses and individuals.', 1200.00, '2025-09-18 13:22:33'),
(14, 'Mobile App Development', 'Custom Android and iOS applications tailored to client needs.', 8000.00, '2025-09-18 13:22:33'),
(15, 'CCTV Installation & Monitoring', 'Installation and configuration of CCTV cameras and monitoring systems.', 3500.00, '2025-09-18 13:22:33'),
(16, 'IT Training & Support', 'Comprehensive ICT training programs and ongoing tech support.', 2000.00, '2025-09-18 13:22:33'),
(17, 'Database Design & Management', 'Design, setup, and maintenance of secure databases.', 4500.00, '2025-09-18 13:22:33'),
(18, 'Email & Collaboration Tools Setup', 'Setup of professional email systems and collaboration platforms like Microsoft 365 or Google Workspace.', 2500.00, '2025-09-18 13:22:33'),
(19, 'VoIP & Telecommunication Systems', 'Deployment of VoIP solutions and telecommunication infrastructure.', 3000.00, '2025-09-18 13:22:33'),
(20, 'ERP & Business Systems Development', 'Custom enterprise resource planning and business management systems.', 10000.00, '2025-09-18 13:22:33'),
(21, 'Graphic Design & Branding', 'Professional graphic design, branding, and digital marketing materials.', 3000.00, '2025-09-18 13:22:33'),
(22, 'ICT Equipment Supply & Installation', 'Supply and installation of computers, printers, and other ICT hardware.', 7000.00, '2025-09-18 13:22:33'),
(23, 'Virtualization & Server Management', 'Setup and management of virtual servers and IT infrastructure.', 5500.00, '2025-09-18 13:22:33'),
(24, 'E-Learning Platform Development', 'Creation of custom online learning management systems.', 7500.00, '2025-09-18 13:22:33'),
(25, 'IoT Solutions & Smart Devices Integration', 'Setup of smart devices and IoT systems for automation.', 6500.00, '2025-09-18 13:22:33'),
(26, 'Digital Forensics & Investigation', 'Recovery and analysis of digital evidence for security and legal purposes.', 5000.00, '2025-09-18 13:22:33');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL CHECK (`id` = 1),
  `usd_to_kes` decimal(10,4) NOT NULL DEFAULT 130.0000,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `usd_to_kes`, `updated_at`) VALUES
(1, 130.0000, '2025-08-21 20:35:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','client') NOT NULL DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_code` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`, `reset_code`, `reset_expires`) VALUES
(2, 's', 's@gmail.com', '$2y$10$/yV/5OV.ukoeKTImijWRou3cewCb1XXVvWD/skzo2/XOVWVYKzBJG', 'client', '2025-08-20 10:30:47', NULL, NULL),
(6, 'Andrew', 'andrew@gmail.com', '$2y$10$wH7ChX/K5rNdx4hJZqkqMOMXkZ8zX5bT9Z9j0W5PqM2C3x7HjJ7e6', 'admin', '2025-08-20 10:42:45', NULL, NULL),
(7, 'Admin User', 'superadmin@sparklab.com', '$2y$10$W2lMm7pXZML28m4.85L/JuZJUB.bO2iaCr3pQG9luJz3drqMiZ5Gu', 'admin', '2025-08-20 10:47:51', NULL, NULL),
(8, 'Andrewb', 'saa143879@gmail.com', '$2y$10$FrgRLncDs.wFL8esvF5pk.ASAVEytJYOqJ2ZN7UyFY0WOvwVbUCcS', 'client', '2025-09-11 07:52:32', '$2y$10$iUGUD2yNpyVS55k64TsQxOtUPqs6PqhCvPu1RYza6fYZ/e0Se0sT.', '2025-09-30 12:53:43'),
(9, 'ass', 'andrewtirkolo@gmail.com', '$2y$10$aAxZQzsOBlu1fysatPfx8ORAuie4hM3lGv7e5UFMGz9DRzujP4xRq', 'client', '2025-09-11 09:39:48', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `get_in_touch`
--
ALTER TABLE `get_in_touch`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `project_requests`
--
ALTER TABLE `project_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `get_in_touch`
--
ALTER TABLE `get_in_touch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project_requests`
--
ALTER TABLE `project_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart_items` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
