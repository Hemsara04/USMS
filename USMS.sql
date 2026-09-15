-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 28, 2026 at 01:34 PM
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
-- Database: `USMS`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `published_by_user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Athletic`
--

CREATE TABLE `Athletic` (
  `athlete_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `index_number` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `academic_year` int(11) NOT NULL,
  `faculty` varchar(255) NOT NULL,
  `height` float(5,2) NOT NULL,
  `weight` float(5,2) NOT NULL,
  `emergency_Contact` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity_in_stock` int(11) NOT NULL,
  `condition_status` enum('New','Good','Needs Repair') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_issue`
--

CREATE TABLE `equipment_issue` (
  `issue_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `athletic_id` int(11) DEFAULT NULL,
  `recipient_type` enum('Athlete','Coach') NOT NULL DEFAULT 'Athlete',
  `recipient_name` varchar(150) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `expected_return_date` date NOT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status` enum('Pending','Issued','Returned','Rejected') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `facility_id` int(11) NOT NULL,
  `facility_name` varchar(100) NOT NULL,
  `type` enum('Ground','Hall') NOT NULL,
  `capacity` int(11) NOT NULL,
  `available_time_windows` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facility_bookings`
--

CREATE TABLE `facility_bookings` (
  `booking_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `approval_status` enum('Pending','Approved','Rejected') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fixtures`
--

CREATE TABLE `fixtures` (
  `fixture_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `opponent` varchar(255) NOT NULL,
  `fixture_type` enum('Internal','Inter-University') NOT NULL,
  `fixture_date` date NOT NULL,
  `fixture_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `injuries`
--

CREATE TABLE `injuries` (
  `injury_id` int(11) NOT NULL,
  `athletic_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `injury_type` varchar(255) NOT NULL,
  `severity` enum('Minor','Moderate','Major') NOT NULL,
  `date_of_injury` date NOT NULL,
  `estimated_recovery_date` date NOT NULL,
  `active_flag` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `match_results`
--

CREATE TABLE `match_results` (
  `result_id` int(11) NOT NULL,
  `fixture_id` int(11) NOT NULL,
  `scores` varchar(10) NOT NULL,
  `scorers` text NOT NULL,
  `player_ratings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`player_ratings`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `match_squads`
--

CREATE TABLE `match_squads` (
  `squad_id` int(11) NOT NULL,
  `fixture_id` int(11) NOT NULL,
  `athlete_id` int(11) NOT NULL,
  `tic_approval_status` enum('Pending','Approved') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_clearances`
--

CREATE TABLE `medical_clearances` (
  `clearance_id` int(11) NOT NULL,
  `athletic_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('Cleared','Expired','Blocked') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notfications`
--

CREATE TABLE `notfications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `physiotherapy_treatments`
--

CREATE TABLE `physiotherapy_treatments` (
  `treatment_id` int(11) NOT NULL,
  `injury_id` int(11) NOT NULL,
  `treatment_date` date NOT NULL,
  `treatment_type` varchar(255) NOT NULL,
  `notes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

CREATE TABLE `seasons` (
  `season_id` int(11) NOT NULL,
  `season_year` int(11) NOT NULL,
  `academic_year` int(11) NOT NULL,
  `status` enum('Active','Archived') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sports`
--

CREATE TABLE `sports` (
  `sport_id` int(11) NOT NULL,
  `sport_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sport_application`
--

CREATE TABLE `sport_application` (
  `application_id` int(11) NOT NULL,
  `athletic_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `status` enum('Pending','Shortlisted','Approved','Rejected') NOT NULL,
  `rejection_reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_attendance`
--

CREATE TABLE `training_attendance` (
  `attendance_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `athlete_id` int(11) NOT NULL,
  `status` enum('Present','Absent') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_sessions`
--

CREATE TABLE `training_sessions` (
  `session_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `duration_minutes` time NOT NULL,
  `session_type` enum('Practice','Conditioning','Tactical') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `university_colors`
--

CREATE TABLE `university_colors` (
  `award_id` int(11) NOT NULL,
  `award_name` int(11) NOT NULL,
  `athelet_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `status` enum('Nominated','Approved','Awarded') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(75) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Athlete','Coach','TIC','Medical_Officer','System_Admin','President','Physiotherapist','Facility_Manager') NOT NULL,
  `security_question` varchar(255) NOT NULL,
  `security_answere_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `published_by_user_id` (`published_by_user_id`);

--
-- Indexes for table `Athletic`
--
ALTER TABLE `Athletic`
  ADD PRIMARY KEY (`athlete_id`),
  ADD UNIQUE KEY `index_number` (`index_number`),
  ADD KEY `userid` (`user_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`),
  ADD KEY `sport_id` (`sport_id`);

--
-- Indexes for table `equipment_issue`
--
ALTER TABLE `equipment_issue`
  ADD PRIMARY KEY (`issue_id`),
  ADD KEY `athletic_id` (`athletic_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`facility_id`);

--
-- Indexes for table `facility_bookings`
--
ALTER TABLE `facility_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `sport_id` (`sport_id`);

--
-- Indexes for table `fixtures`
--
ALTER TABLE `fixtures`
  ADD PRIMARY KEY (`fixture_id`),
  ADD KEY `sport_id` (`sport_id`);

--
-- Indexes for table `injuries`
--
ALTER TABLE `injuries`
  ADD PRIMARY KEY (`injury_id`),
  ADD KEY `athletic_id` (`athletic_id`),
  ADD KEY `sport_id` (`sport_id`);

--
-- Indexes for table `match_results`
--
ALTER TABLE `match_results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `fixture_id` (`fixture_id`);

--
-- Indexes for table `match_squads`
--
ALTER TABLE `match_squads`
  ADD PRIMARY KEY (`squad_id`),
  ADD KEY `athlete_id` (`athlete_id`),
  ADD KEY `fixture_id` (`fixture_id`);

--
-- Indexes for table `medical_clearances`
--
ALTER TABLE `medical_clearances`
  ADD PRIMARY KEY (`clearance_id`),
  ADD KEY `athletic_id` (`athletic_id`),
  ADD KEY `sport_id` (`sport_id`),
  ADD KEY `season_id` (`season_id`);

--
-- Indexes for table `notfications`
--
ALTER TABLE `notfications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `physiotherapy_treatments`
--
ALTER TABLE `physiotherapy_treatments`
  ADD PRIMARY KEY (`treatment_id`),
  ADD KEY `injury_id` (`injury_id`);

--
-- Indexes for table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`season_id`);

--
-- Indexes for table `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`sport_id`),
  ADD UNIQUE KEY `sport_name` (`sport_name`);

--
-- Indexes for table `sport_application`
--
ALTER TABLE `sport_application`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `athletic_application` (`athletic_id`),
  ADD KEY `sport_application` (`sport_id`),
  ADD KEY `season_application` (`season_id`);

--
-- Indexes for table `training_attendance`
--
ALTER TABLE `training_attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `athlete_id` (`athlete_id`);

--
-- Indexes for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `sport_id` (`sport_id`);

--
-- Indexes for table `university_colors`
--
ALTER TABLE `university_colors`
  ADD PRIMARY KEY (`award_id`),
  ADD KEY `athelet_id` (`athelet_id`),
  ADD KEY `season_id` (`season_id`),
  ADD KEY `sport_id` (`sport_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`,`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sports`
--
ALTER TABLE `sports`
  MODIFY `sport_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Athletic`
--
ALTER TABLE `Athletic`
  MODIFY `athlete_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_issue`
--
ALTER TABLE `equipment_issue`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `facility_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facility_bookings`
--
ALTER TABLE `facility_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fixtures`
--
ALTER TABLE `fixtures`
  MODIFY `fixture_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `match_squads`
--
ALTER TABLE `match_squads`
  MODIFY `squad_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `season_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notfications`
--
ALTER TABLE `notfications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `injuries`
--
ALTER TABLE `injuries`
  MODIFY `injury_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_clearances`
--
ALTER TABLE `medical_clearances`
  MODIFY `clearance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `university_colors`
--
ALTER TABLE `university_colors`
  MODIFY `award_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`published_by_user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `Athletic`
--
ALTER TABLE `Athletic`
  ADD CONSTRAINT `athletic_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`);

--
-- Constraints for table `equipment_issue`
--
ALTER TABLE `equipment_issue`
  ADD CONSTRAINT `equipment_issue_ibfk_1` FOREIGN KEY (`athletic_id`) REFERENCES `Athletic` (`athlete_id`),
  ADD CONSTRAINT `equipment_issue_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`);

--
-- Constraints for table `facility_bookings`
--
ALTER TABLE `facility_bookings`
  ADD CONSTRAINT `facility_bookings_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`),
  ADD CONSTRAINT `facility_bookings_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`);

--
-- Constraints for table `fixtures`
--
ALTER TABLE `fixtures`
  ADD CONSTRAINT `fixtures_ibfk_1` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`);

--
-- Constraints for table `injuries`
--
ALTER TABLE `injuries`
  ADD CONSTRAINT `injuries_ibfk_1` FOREIGN KEY (`athletic_id`) REFERENCES `Athletic` (`athlete_id`),
  ADD CONSTRAINT `injuries_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`);

--
-- Constraints for table `match_squads`
--
ALTER TABLE `match_squads`
  ADD CONSTRAINT `match_squads_ibfk_1` FOREIGN KEY (`athlete_id`) REFERENCES `Athletic` (`athlete_id`),
  ADD CONSTRAINT `match_squads_ibfk_2` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`fixture_id`);

--
-- Constraints for table `medical_clearances`
--
ALTER TABLE `medical_clearances`
  ADD CONSTRAINT `medical_clearances_ibfk_1` FOREIGN KEY (`athletic_id`) REFERENCES `Athletic` (`athlete_id`),
  ADD CONSTRAINT `medical_clearances_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`),
  ADD CONSTRAINT `medical_clearances_ibfk_3` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`season_id`);

--
-- Constraints for table `notfications`
--
ALTER TABLE `notfications`
  ADD CONSTRAINT `notfications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `physiotherapy_treatments`
--
ALTER TABLE `physiotherapy_treatments`
  ADD CONSTRAINT `physiotherapy_treatments_ibfk_1` FOREIGN KEY (`injury_id`) REFERENCES `injuries` (`injury_id`);

--
-- Constraints for table `sport_application`
--
ALTER TABLE `sport_application`
  ADD CONSTRAINT `athletic_application` FOREIGN KEY (`athletic_id`) REFERENCES `Athletic` (`athlete_id`),
  ADD CONSTRAINT `season_application` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`season_id`),
  ADD CONSTRAINT `sport_application` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`);

--
-- Constraints for table `training_attendance`
--
ALTER TABLE `training_attendance`
  ADD CONSTRAINT `training_attendance_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `training_sessions` (`session_id`),
  ADD CONSTRAINT `training_attendance_ibfk_2` FOREIGN KEY (`athlete_id`) REFERENCES `Athletic` (`athlete_id`);

--
-- Constraints for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD CONSTRAINT `training_sessions_ibfk_1` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`);

--
-- Constraints for table `university_colors`
--
ALTER TABLE `university_colors`
  ADD CONSTRAINT `university_colors_ibfk_1` FOREIGN KEY (`athelet_id`) REFERENCES `Athletic` (`athlete_id`),
  ADD CONSTRAINT `university_colors_ibfk_2` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`season_id`),
  ADD CONSTRAINT `university_colors_ibfk_3` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
