-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2025 at 06:24 PM
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
-- Database: `vehicle_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `region` enum('North','South','East','West') NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `profil_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `region`, `status`, `profil_id`) VALUES
(1, 'South', 'Active', 2);

-- --------------------------------------------------------

--
-- Table structure for table `bill`
--

CREATE TABLE `bill` (
  `bill_id` int(11) NOT NULL,
  `status` enum('Pending','Paid','Ready to Paid') DEFAULT 'Pending',
  `totalAmount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bill`
--

INSERT INTO `bill` (`bill_id`, `status`, `totalAmount`) VALUES
(3, 'Paid', 108.00),
(5, 'Pending', 24.00);

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `contract_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `tenant_phone` varchar(15) NOT NULL,
  `tenant_email` varchar(255) DEFAULT NULL,
  `vehicle_name` varchar(50) NOT NULL,
  `vehicle_year` year(4) NOT NULL,
  `vehicle_matricule` varchar(50) NOT NULL,
  `vehicle_descreption` text DEFAULT NULL,
  `rental_start` datetime NOT NULL,
  `rental_end` datetime NOT NULL,
  `deposit` decimal(10,2) NOT NULL,
  `contract_status` enum('Active','Expert','Cancelled') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mechanic`
--

CREATE TABLE `mechanic` (
  `mechanic_id` int(11) NOT NULL,
  `region` enum('North','South','East','West') NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `profil_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owner`
--

CREATE TABLE `owner` (
  `owner_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `profil_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner`
--

INSERT INTO `owner` (`owner_id`, `status`, `profil_id`) VALUES
(1, 'Active', 3);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('Cash','Credit Card','Bank Transfer','Mobile Payment','Cheque') DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `bill_id`, `tenant_id`, `creationDate`, `amount`, `type`) VALUES
(1, 3, 1, '2025-01-11 13:26:10', 108.00, 'Credit Card'),
(2, 3, 1, '2025-01-11 13:56:39', 108.00, 'Credit Card');

-- --------------------------------------------------------

--
-- Table structure for table `profil`
--

CREATE TABLE `profil` (
  `profil_id` int(11) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `userName` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `type` enum('tenant','secretary','admin','owner','mechanic') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profil`
--

INSERT INTO `profil` (`profil_id`, `firstName`, `lastName`, `phone`, `email`, `userName`, `password`, `status`, `type`) VALUES
(1, '', '', NULL, 'sa@hs.com', 'houssem', '$2y$10$AnHnivebUkdjHXGWi3MwSuX04J.NGXPV68zvJBE6kPi5ZYjfXemX6', 'Inactive', 'tenant'),
(2, 'hs', 'aa', '02930', 'ds@ga.com', 'js', '$2y$10$xGnizoVuMK3163koMVF.4uR22Le3iTH6B3U8dVmOcz3ZpnEFO29vm', 'Active', 'admin'),
(3, 'ert', 'we', NULL, 'ss@ss.com', '', '$2y$10$LvsjGHliSfoXhzxVG9b4auHx4xpxyMZ2QdGa8vao95TgiAgyBAUre', 'Active', 'owner');

-- --------------------------------------------------------

--
-- Table structure for table `reclamation`
--

CREATE TABLE `reclamation` (
  `reclamation_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `owner_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `reply` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reclamation`
--

INSERT INTO `reclamation` (`reclamation_id`, `subject`, `content`, `owner_id`, `tenant_id`, `vehicle_id`, `reply`) VALUES
(1, '345', 'gegwe', 1, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `contract_id` int(11) DEFAULT NULL,
  `pickupDate` date NOT NULL,
  `returnDate` date NOT NULL,
  `returnTime` time DEFAULT NULL,
  `status` enum('inactive','active','completed','cancelled') NOT NULL DEFAULT 'inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `tenant_id`, `vehicle_id`, `bill_id`, `contract_id`, `pickupDate`, `returnDate`, `returnTime`, `status`) VALUES
(3, 1, 1, 5, NULL, '2025-01-12', '2025-01-14', NULL, 'inactive');

-- --------------------------------------------------------

--
-- Table structure for table `secretary`
--

CREATE TABLE `secretary` (
  `secretary_id` int(11) NOT NULL,
  `region` enum('North','South','East','West') NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `profil_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant`
--

CREATE TABLE `tenant` (
  `tenant_id` int(11) NOT NULL,
  `profil_id` int(11) NOT NULL,
  `isExcluded` tinyint(1) DEFAULT 0,
  `lienceNumber` int(11) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `isValidated` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant`
--

INSERT INTO `tenant` (`tenant_id`, `profil_id`, `isExcluded`, `lienceNumber`, `status`, `isValidated`) VALUES
(1, 1, 0, 123, 'Inactive', 0);

-- --------------------------------------------------------

--
-- Table structure for table `vehicle`
--

CREATE TABLE `vehicle` (
  `vehicle_id` int(11) NOT NULL,
  `matricule` varchar(20) NOT NULL,
  `name_vehicle` varchar(50) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `fuelType` varchar(50) DEFAULT NULL,
  `releaseYear` year(4) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `isAutomatic` tinyint(1) DEFAULT NULL,
  `pricePerDay` decimal(10,2) DEFAULT NULL,
  `pricePerHour` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('available','rented','maintenance','lost') NOT NULL DEFAULT 'available',
  `statu` enum('Active','Inactive') DEFAULT 'Active',
  `picture` varchar(255) DEFAULT NULL,
  `hasAirConditioning` tinyint(1) DEFAULT NULL,
  `hasBluetooth` tinyint(1) DEFAULT NULL,
  `hasCruiseControl` tinyint(1) DEFAULT NULL,
  `hasAMFMStereoRadio` tinyint(1) DEFAULT NULL,
  `hasLeatherInterior` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle`
--

INSERT INTO `vehicle` (`vehicle_id`, `matricule`, `name_vehicle`, `owner_id`, `fuelType`, `releaseYear`, `color`, `isAutomatic`, `pricePerDay`, `pricePerHour`, `description`, `status`, `statu`, `picture`, `hasAirConditioning`, `hasBluetooth`, `hasCruiseControl`, `hasAMFMStereoRadio`, `hasLeatherInterior`) VALUES
(1, '34251', 'Peugeot 408', 1, 'Hybrid', '2024', 'Blue', 1, 12.00, 2.00, 'new', '', 'Active', 'https://cdn.automobile-propre.com/uploads/2022/11/Essai-Peugeot-408-Hybrid-225-024.jpg', 1, 1, 1, 1, 1),
(2, '', 'p1', 1, 'Electric', '2021', 'white', 1, 12.00, 2.00, 'old', '', 'Active', 'https://pictures.lacentrale.fr/classifieds/E115350328_STANDARD_0.jpg', 1, 1, 1, 1, 1),
(3, '6757', 'pp', 1, 'Gasoline', '2020', ',ij', 1, 11.00, 11.00, 'fgffh', 'available', 'Inactive', '4387nm,m', 1, 1, 1, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `profil_id` (`profil_id`);

--
-- Indexes for table `bill`
--
ALTER TABLE `bill`
  ADD PRIMARY KEY (`bill_id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`contract_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `mechanic`
--
ALTER TABLE `mechanic`
  ADD PRIMARY KEY (`mechanic_id`),
  ADD KEY `profil_id` (`profil_id`);

--
-- Indexes for table `owner`
--
ALTER TABLE `owner`
  ADD PRIMARY KEY (`owner_id`),
  ADD KEY `profil_id` (`profil_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `profil`
--
ALTER TABLE `profil`
  ADD PRIMARY KEY (`profil_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `reclamation`
--
ALTER TABLE `reclamation`
  ADD PRIMARY KEY (`reclamation_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `contract_id` (`contract_id`);

--
-- Indexes for table `secretary`
--
ALTER TABLE `secretary`
  ADD PRIMARY KEY (`secretary_id`),
  ADD KEY `profil_id` (`profil_id`);

--
-- Indexes for table `tenant`
--
ALTER TABLE `tenant`
  ADD PRIMARY KEY (`tenant_id`),
  ADD KEY `profil_id` (`profil_id`);

--
-- Indexes for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `matricule` (`matricule`),
  ADD KEY `owner_id` (`owner_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bill`
--
ALTER TABLE `bill`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mechanic`
--
ALTER TABLE `mechanic`
  MODIFY `mechanic_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owner`
--
ALTER TABLE `owner`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `profil`
--
ALTER TABLE `profil`
  MODIFY `profil_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reclamation`
--
ALTER TABLE `reclamation`
  MODIFY `reclamation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `secretary`
--
ALTER TABLE `secretary`
  MODIFY `secretary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tenant`
--
ALTER TABLE `tenant`
  MODIFY `tenant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicle`
--
ALTER TABLE `vehicle`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`profil_id`) REFERENCES `profil` (`profil_id`);

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`tenant_id`);

--
-- Constraints for table `mechanic`
--
ALTER TABLE `mechanic`
  ADD CONSTRAINT `mechanic_ibfk_1` FOREIGN KEY (`profil_id`) REFERENCES `profil` (`profil_id`);

--
-- Constraints for table `owner`
--
ALTER TABLE `owner`
  ADD CONSTRAINT `owner_ibfk_1` FOREIGN KEY (`profil_id`) REFERENCES `profil` (`profil_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`tenant_id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`bill_id`) REFERENCES `bill` (`bill_id`);

--
-- Constraints for table `reclamation`
--
ALTER TABLE `reclamation`
  ADD CONSTRAINT `reclamation_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owner` (`owner_id`),
  ADD CONSTRAINT `reclamation_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`tenant_id`),
  ADD CONSTRAINT `reclamation_ibfk_3` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`vehicle_id`);

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`tenant_id`),
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`vehicle_id`),
  ADD CONSTRAINT `reservation_ibfk_3` FOREIGN KEY (`bill_id`) REFERENCES `bill` (`bill_id`),
  ADD CONSTRAINT `reservation_ibfk_4` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`);

--
-- Constraints for table `secretary`
--
ALTER TABLE `secretary`
  ADD CONSTRAINT `secretary_ibfk_1` FOREIGN KEY (`profil_id`) REFERENCES `profil` (`profil_id`);

--
-- Constraints for table `tenant`
--
ALTER TABLE `tenant`
  ADD CONSTRAINT `tenant_ibfk_1` FOREIGN KEY (`profil_id`) REFERENCES `profil` (`profil_id`);

--
-- Constraints for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD CONSTRAINT `vehicle_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owner` (`owner_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
