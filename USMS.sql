-- University Sports Management System (USMS)
-- Database Dump with Clean Schema & Seed Accounts

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE="+00:00" */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE="NO_AUTO_VALUE_ON_ZERO" */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `USMS` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `USMS`;

/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: usms
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Athletic`
--

DROP TABLE IF EXISTS `Athletic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Athletic` (
  `athlete_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `index_number` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `academic_year` int(11) NOT NULL,
  `faculty` varchar(255) NOT NULL,
  `height` float(5,2) DEFAULT 0.00,
  `weight` float(5,2) DEFAULT 0.00,
  `emergency_Contact` varchar(20) DEFAULT '',
  `blood_group` varchar(10) DEFAULT 'O+',
  `medical_status` enum('Fit to Compete','Under Review','Temporarily Unfit') NOT NULL DEFAULT 'Fit to Compete',
  `allergies` varchar(255) DEFAULT 'None',
  `medical_notes` text DEFAULT NULL,
  `last_examined_date` date DEFAULT NULL,
  PRIMARY KEY (`athlete_id`),
  UNIQUE KEY `index_number` (`index_number`),
  KEY `userid` (`user_id`),
  CONSTRAINT `athletic_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Athletic`
--

LOCK TABLES `Athletic` WRITE;
/*!40000 ALTER TABLE `Athletic` DISABLE KEYS */;
INSERT INTO `Athletic` VALUES
(1,10,'SC/2022/1042','Kasun Perera',3,'Faculty of Science',178.50,72.00,'+94771234567','O+','Fit to Compete','None','Under concussion recovery protocol. Restricting contact drills.','2026-09-20'),
(2,11,'AR/2021/8821','Sarah Jenkins',4,'Faculty of Arts',169.00,58.50,'+94779876543','A+','Fit to Compete','None','Annual baseline medical screening completed.','2026-08-12'),
(3,12,'MG/2023/4420','Marcus Thorne',2,'Faculty of Management & Finance',185.00,84.00,'+94715551234','B+','Under Review','Asthma (mild)','Requires salbutamol inhaler on bench. Ankle rehab ongoing.','2026-09-14'),
(4,13,'MD/2022/1928','Elena Rodriguez',3,'Faculty of Medicine',165.00,55.00,'+94762223344','O+','Under Review','Penicillin','Cleared for inter-university aquatic meets.','2026-09-20'),
(5,14,'TC/2023/3391','Tom Aris',2,'Faculty of Technology',180.00,76.50,'+94784445566','AB+','Under Review','None','Shoulder strain undergoing physiotherapy sessions.','2026-09-16'),
(6,15,'SC/2021/2049','Dinuka Fernando',4,'Faculty of Science',175.00,68.00,'+94701112233','A-','Fit to Compete','Dust allergy','Cardiac fitness optimal. Passed sprint stress test.','2026-08-04'),
(7,16,'AR/2023/5021','Amali Wickramasinghe',2,'Faculty of Arts',162.00,52.00,'+94718889900','B-','Fit to Compete','None','Normal ECG, lung capacity normal.','2026-08-18'),
(8,6,'SC/2023/3305','Malith Perera',2,'Faculty of Science',172.00,66.50,'+94759012345','O+','Temporarily Unfit','None','Patellar tendinitis, rest advised for 7 days.','2026-09-17'),
(9,23,'AR/2022/6630','Dilshan Pathirana',3,'Faculty of Arts',180.00,74.00,'+94774433221','A+','Fit to Compete','None','Fully recovered from wrist fracture.','2026-07-29'),
(10,21,'MG/2022/2204','Tamasha Sevindi',3,'Faculty of Management & Finance',166.00,56.00,'+94776789012','O-','Fit to Compete','None','Hydration screening passed.','2026-07-21'),
(11,24,'MG/2021/7701','Ruwan Weerasinghe',4,'Faculty of Management & Finance',177.00,71.00,'+94763322110','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(12,7,'MD/2023/4019','Chamara Bandara',2,'Faculty of Medicine',173.00,69.00,'+94787890123','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(13,8,'TC/2022/1908','Nadeesha Dilhani',3,'Faculty of Technology',163.00,54.00,'+94708901234','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(14,3,'CS/2022/9012','Thilina Madushanka',3,'UCSC',176.00,70.00,'+94773456789','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(15,5,'CS/2023/1102','Hasitha Eranga',2,'UCSC',174.00,67.00,'+94765678901','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(16,25,'CS/2021/8044','Kusal Mendis',4,'UCSC',178.00,73.00,'+94712233445','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(17,4,'LW/2021/3015','Kamal Gunaratne',4,'Faculty of Law',181.00,78.00,'+94714567890','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(18,26,'LW/2023/2004','Sanath Jayasuriya Jr',2,'Faculty of Law',179.00,75.00,'+94775566778','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(19,27,'ED/2022/4410','Suresh Silva',3,'Faculty of Education',170.00,65.00,'+94781122334','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(20,28,'NU/2023/1020','Kaveesha Wijesinghe',2,'Faculty of Nursing',164.00,53.00,'+94778899001','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(21,29,'IM/2022/5510','Sanduni Jayawardena',3,'Faculty of Indigenous Medicine',160.00,50.00,'+94769988776','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),
(22,30,'IS/200/423','yumal perera',1,'UCSC',0.00,0.00,'0781280908','O+','Fit to Compete','None',NULL,NULL),
(24,39,'IS/200/424','mikas samn',1,'UCSC',0.00,0.00,'0712224571','O+','Fit to Compete','None',NULL,NULL);
/*!40000 ALTER TABLE `Athletic` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT,
  `published_by_user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL,
  `target_audience` enum('All Users','Student Athletes','Coaches & Staff','Medical Staff','TIC') NOT NULL DEFAULT 'All Users',
  `priority` enum('Normal','Important','Urgent') NOT NULL DEFAULT 'Normal',
  `status` enum('Active','Archived') NOT NULL DEFAULT 'Active',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`announcement_id`),
  KEY `published_by_user_id` (`published_by_user_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`published_by_user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES
(13,1,'Inter-Faculty Championship 2026 Schedule Announced','The annual Inter-Faculty Championship matches will commence on September 24th, 2026. Faculty teams in Football, Cricket, Basketball, and Badminton must finalize their squads with the TIC office before match day.','2026-09-18 10:00:00','All Users','Normal','Active',NULL),
(14,9,'Varsity Sports Equipment Restocked for Season 2026','The Amalgamated Club has procured new official match gear including Yonex shuttlecocks, Kookaburra cricket turf balls, and Nike match footballs. Coaches and athletes may submit equipment checkout requests online.','2026-09-19 14:30:00','All Users','Normal','Archived','2026-09-27 07:00:08'),
(15,1,'Facility Booking Guidelines for Colombo University Grounds & Gym','All ground and gymnasium allocations for varsity and faculty practices must be submitted at least 48 hours in advance through the USMS portal for TIC review and approval.','2026-09-20 09:15:00','All Users','Normal','Active',NULL),
(16,1,'Mandatory Coaching Tactical Briefing','Coaches are requested to submit finalized tournament squads.','2026-09-23 22:55:26','Coaches & Staff','Important','Active',NULL),
(17,1,'Athlete Biometric Protocol Updates','Medical officers must record cardiovascular screening logs.','2026-09-23 22:55:26','Medical Staff','Urgent','Active',NULL),
(18,9,'test','test notice','2026-09-26 21:47:39','Student Athletes','Normal','Active',NULL);
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL AUTO_INCREMENT,
  `sport_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity_in_stock` int(11) NOT NULL,
  `condition_status` enum('New','Good','Needs Repair') NOT NULL DEFAULT 'Good',
  PRIMARY KEY (`equipment_id`),
  KEY `sport_id` (`sport_id`),
  CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment`
--

LOCK TABLES `equipment` WRITE;
/*!40000 ALTER TABLE `equipment` DISABLE KEYS */;
INSERT INTO `equipment` VALUES
(1,1,'Elite GPS Trackers (Set of 10)',10,'Good'),
(2,1,'Nike Strike Training Footballs',35,'New'),
(3,1,'Agility Training Hurdles Set',18,'Good'),
(4,2,'Spalding TF-1000 Basketballs',28,'New'),
(5,2,'Pro-Grade Rebounder Net',4,'Good'),
(6,3,'SS Ton Reserve Cricket Bats',14,'Good'),
(7,3,'Kookaburra Turf Match Balls',55,'New'),
(8,5,'High-Jump Pit Measurement Kit',6,'Good'),
(9,5,'Starting Blocks (Olympic Standard)',8,'Good'),
(10,4,'Yonex Nanoflare Rackets',22,'Good'),
(11,4,'Yonex Aerosensa Shuttlecock Tubes',75,'New'),
(12,6,'Digital Stopwatch & Pace Clocks',10,'Good'),
(13,9,'Wilson Pro Staff Tennis Rackets',14,'Good'),
(14,9,'Wilson Championship Ball Tubes',45,'New'),
(15,4,'Badminton Shuttlecocks Pack',20,'Good'),
(16,1,'Corner Flags & Spring Poles (Set 4)',4,'Needs Repair'),
(17,2,'Heavy Duty Basketball Rim & Net',3,'Needs Repair'),
(18,3,'Spring-Loaded Cricket Stumps Set',5,'Good'),
(19,4,'Badminton Tournament Net',2,'Needs Repair'),
(20,5,'Aluminum Relay Batons (Set of 8)',12,'New'),
(21,6,'Speedo Training Kickboards',25,'Good'),
(22,10,'Stiga Pro Carbon TT Bats',16,'Good'),
(23,10,'Nittaku 3-Star TT Balls (Pack 72)',25,'New'),
(24,8,'Mikasa V200W Official Volleyballs',20,'New'),
(25,8,'Volleyball Antenna & Net Straps',3,'Needs Repair'),
(28,4,'qwe',10,'Good');
/*!40000 ALTER TABLE `equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_issue`
--

DROP TABLE IF EXISTS `equipment_issue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment_issue` (
  `issue_id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `athletic_id` int(11) DEFAULT NULL,
  `recipient_type` enum('Athlete','Coach') NOT NULL DEFAULT 'Athlete',
  `recipient_name` varchar(150) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `expected_return_date` date NOT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status` enum('Pending','Issued','Returned','Rejected') NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`issue_id`),
  KEY `athletic_id` (`athletic_id`),
  KEY `equipment_id` (`equipment_id`),
  CONSTRAINT `equipment_issue_ibfk_1` FOREIGN KEY (`athletic_id`) REFERENCES `Athletic` (`athlete_id`),
  CONSTRAINT `equipment_issue_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_issue`
--

LOCK TABLES `equipment_issue` WRITE;
/*!40000 ALTER TABLE `equipment_issue` DISABLE KEYS */;
INSERT INTO `equipment_issue` VALUES
(51,7,6,2,'Athlete',NULL,'2026-09-18','2026-09-24',NULL,'Pending'),
(52,10,2,14,'Athlete',NULL,'2026-09-19','2026-09-26',NULL,'Pending'),
(53,2,4,1,'Athlete',NULL,'2026-09-19','2026-09-25',NULL,'Pending'),
(54,4,3,NULL,'Coach','coach_pradeep','2026-09-20','2026-09-27',NULL,'Pending'),
(55,13,2,4,'Athlete',NULL,'2026-09-20','2026-09-25',NULL,'Pending'),
(56,12,2,5,'Athlete',NULL,'2026-09-20','2026-09-28',NULL,'Pending'),
(57,1,2,NULL,'Coach','coach_sunil','2026-09-15','2026-09-23',NULL,'Issued'),
(58,3,1,6,'Athlete',NULL,'2026-09-16','2026-09-24',NULL,'Issued'),
(59,4,4,3,'Athlete',NULL,'2026-09-17','2026-09-24',NULL,'Issued'),
(60,6,2,NULL,'Coach','coach_anura','2026-09-18','2026-09-25',NULL,'Issued'),
(61,9,2,7,'Athlete',NULL,'2026-09-18','2026-09-26',NULL,'Issued'),
(62,10,3,17,'Athlete',NULL,'2026-09-19','2026-09-25',NULL,'Issued'),
(63,22,4,10,'Athlete',NULL,'2026-09-19','2026-09-26',NULL,'Issued'),
(64,5,1,15,'Athlete',NULL,'2026-09-20','2026-09-27',NULL,'Issued'),
(65,14,2,NULL,'Coach','coach_pradeep','2026-09-10','2026-09-15','2026-09-14','Returned'),
(66,2,5,1,'Athlete',NULL,'2026-09-11','2026-09-16','2026-09-15','Returned');
/*!40000 ALTER TABLE `equipment_issue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facilities`
--

DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `facilities` (
  `facility_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_name` varchar(100) NOT NULL,
  `type` enum('Ground','Hall') NOT NULL,
  `capacity` int(11) NOT NULL,
  `available_time_windows` varchar(255) NOT NULL,
  PRIMARY KEY (`facility_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facilities`
--

LOCK TABLES `facilities` WRITE;
/*!40000 ALTER TABLE `facilities` DISABLE KEYS */;
INSERT INTO `facilities` VALUES
(1,'Main Gymnasium','Hall',500,'06:00 - 21:00'),
(2,'Colombo University Grounds','Ground',2000,'06:00 - 18:30'),
(3,'Indoor Sports Arena','Hall',350,'07:00 - 22:00'),
(4,'University Tennis Courts','Ground',150,'06:30 - 19:00'),
(5,'Swimming Pool Complex','Hall',300,'06:00 - 20:00');
/*!40000 ALTER TABLE `facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facility_bookings`
--

DROP TABLE IF EXISTS `facility_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `facility_bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `approval_status` enum('Pending','Approved','Rejected') NOT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `facility_id` (`facility_id`),
  KEY `sport_id` (`sport_id`),
  CONSTRAINT `facility_bookings_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`),
  CONSTRAINT `facility_bookings_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facility_bookings`
--

LOCK TABLES `facility_bookings` WRITE;
/*!40000 ALTER TABLE `facility_bookings` DISABLE KEYS */;
INSERT INTO `facility_bookings` VALUES
(20,2,1,'2026-09-22','15:30:00',120,'Pending'),
(21,1,2,'2026-09-23','17:00:00',90,'Pending'),
(22,3,4,'2026-09-24','08:30:00',120,'Pending'),
(23,5,6,'2026-09-25','07:00:00',90,'Pending'),
(24,2,3,'2026-09-26','14:00:00',180,'Approved'),
(25,4,9,'2026-09-27','16:30:00',90,'Approved');
/*!40000 ALTER TABLE `facility_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fixtures`
--

DROP TABLE IF EXISTS `fixtures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fixtures` (
  `fixture_id` int(11) NOT NULL AUTO_INCREMENT,
  `sport_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `opponent` varchar(255) NOT NULL,
  `fixture_type` enum('Inter-University','Inter-Faculty','Internal') NOT NULL DEFAULT 'Inter-University',
  `home_team` varchar(100) DEFAULT 'University of Colombo',
  `fixture_date` date NOT NULL,
  `fixture_time` time NOT NULL,
  PRIMARY KEY (`fixture_id`),
  KEY `sport_id` (`sport_id`),
  CONSTRAINT `fixtures_ibfk_1` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fixtures`
--

LOCK TABLES `fixtures` WRITE;
/*!40000 ALTER TABLE `fixtures` DISABLE KEYS */;
INSERT INTO `fixtures` VALUES
(33,1,2,'University of Peradeniya','Inter-University','University of Colombo','2026-09-25','15:30:00'),
(34,2,1,'University of Moratuwa','Inter-University','University of Colombo','2026-09-28','17:00:00'),
(35,3,2,'University of Kelaniya','Inter-University','University of Colombo','2026-10-02','09:30:00'),
(36,4,3,'Univ. of Sri Jayewardenepura','Inter-University','University of Colombo','2026-10-05','14:00:00'),
(37,5,2,'University of Ruhuna','Inter-University','University of Colombo','2026-10-08','08:00:00'),
(38,1,2,'Faculty of Arts','Inter-Faculty','Faculty of Science','2026-09-24','16:00:00'),
(39,3,2,'Faculty of Management & Finance','Inter-Faculty','UCSC','2026-09-27','14:30:00'),
(40,2,1,'Faculty of Technology','Inter-Faculty','Faculty of Medicine','2026-09-29','16:30:00'),
(41,4,3,'Faculty of Science','Inter-Faculty','Faculty of Law','2026-10-01','15:00:00'),
(42,10,3,'UCSC','Inter-Faculty','Faculty of Technology','2026-10-03','16:00:00'),
(43,1,2,'UOC Varsity B (Development Squad)','Internal','UOC Varsity A','2026-09-26','07:30:00');
/*!40000 ALTER TABLE `fixtures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `injuries`
--

DROP TABLE IF EXISTS `injuries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `injuries` (
  `injury_id` int(11) NOT NULL AUTO_INCREMENT,
  `athletic_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `injury_type` varchar(255) NOT NULL,
  `severity` enum('Minor','Moderate','Major') NOT NULL,
  `date_of_injury` date NOT NULL,
  `estimated_recovery_date` date NOT NULL,
  `active_flag` tinyint(4) NOT NULL,
  `notes` text DEFAULT NULL,
  `reported_by` varchar(100) DEFAULT 'Medical Officer',
  PRIMARY KEY (`injury_id`),
  KEY `athletic_id` (`athletic_id`),
  KEY `sport_id` (`sport_id`),
  CONSTRAINT `injuries_ibfk_1` FOREIGN KEY (`athletic_id`) REFERENCES `Athletic` (`athlete_id`),
  CONSTRAINT `injuries_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `injuries`
--

LOCK TABLES `injuries` WRITE;
/*!40000 ALTER TABLE `injuries` DISABLE KEYS */;
INSERT INTO `injuries` VALUES
(1,1,7,'Concussion Protocol (Stage 5)','Major','2026-09-12','2026-09-26',0,'Head collision during rugby scrimmage. Cognitive review pending.','Dr. A. Fernando'),
(2,3,2,'Grade II Ankle Sprain','Moderate','2026-09-14','2026-09-28',1,'Lateral inversion sprain during basketball rebound. Ice & elevation therapy.','Dr. A. Fernando'),
(3,5,1,'AC Joint Shoulder Strain','Moderate','2026-09-16','2026-09-30',1,'Subluxation during tackle drill in football.','Dr. N. Silva'),
(4,8,5,'Patellar Tendinopathy (Knee)','Minor','2026-09-17','2026-09-24',1,'Overuse inflammation on right patellar tendon in track training.','Dr. A. Fernando'),
(5,4,6,'Shoulder Impingement Syndrome','Moderate','2026-09-20','2026-10-04',1,'Mild bursitis during butterfly stroke practice','Dr. A. Fernando');
/*!40000 ALTER TABLE `injuries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_results`
--

DROP TABLE IF EXISTS `match_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_results` (
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
  `fixture_id` int(11) NOT NULL,
  `scores` varchar(10) NOT NULL,
  `scorers` text DEFAULT NULL,
  `match_report` text DEFAULT NULL,
  `player_ratings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`result_id`),
  UNIQUE KEY `fixture_id` (`fixture_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_results`
--

LOCK TABLES `match_results` WRITE;
/*!40000 ALTER TABLE `match_results` DISABLE KEYS */;
INSERT INTO `match_results` VALUES
(2,33,'2 - 1','Kavindu Perera (34\'), Sadeera Samarawickrama (78\')','Dominant second-half performance with aggressive pressing secured victory in the Inter-Faculty quarter-final match.',NULL);
/*!40000 ALTER TABLE `match_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_squads`
--

DROP TABLE IF EXISTS `match_squads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_squads` (
  `squad_id` int(11) NOT NULL AUTO_INCREMENT,
  `fixture_id` int(11) NOT NULL,
  `athlete_id` int(11) NOT NULL,
  `tic_approval_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`squad_id`),
  KEY `athlete_id` (`athlete_id`),
  KEY `fixture_id` (`fixture_id`),
  CONSTRAINT `match_squads_ibfk_1` FOREIGN KEY (`athlete_id`) REFERENCES `Athletic` (`athlete_id`),
  CONSTRAINT `match_squads_ibfk_2` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`fixture_id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_squads`
--

LOCK TABLES `match_squads` WRITE;
/*!40000 ALTER TABLE `match_squads` DISABLE KEYS */;
INSERT INTO `match_squads` VALUES
(93,33,1,'Pending'),
(94,33,2,'Pending'),
(95,33,3,'Pending'),
(96,33,4,'Pending'),
(97,33,5,'Pending'),
(98,33,14,'Approved'),
(99,33,17,'Approved'),
(100,34,3,'Approved'),
(101,34,5,'Approved'),
(102,34,15,'Pending'),
(103,34,6,'Pending'),
(104,35,16,'Pending'),
(105,35,9,'Pending'),
(106,35,11,'Pending'),
(107,36,14,'Pending'),
(108,36,17,'Pending'),
(109,37,7,'Approved'),
(110,37,5,'Approved'),
(111,37,1,'Approved'),
(112,38,1,'Pending'),
(113,38,6,'Pending'),
(114,38,8,'Pending'),
(115,39,14,'Pending'),
(116,39,15,'Pending'),
(117,39,16,'Pending'),
(118,40,4,'Pending'),
(119,40,12,'Pending'),
(120,41,17,'Pending'),
(121,41,18,'Pending'),
(122,42,5,'Pending'),
(123,42,13,'Pending'),
(124,43,1,'Approved'),
(125,43,2,'Approved');
/*!40000 ALTER TABLE `match_squads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_clearances`
--

DROP TABLE IF EXISTS `medical_clearances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `medical_clearances` (
  `clearance_id` int(11) NOT NULL AUTO_INCREMENT,
  `athletic_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('Cleared','Expired','Blocked') NOT NULL,
  `cleared_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `certifying_doctor` varchar(100) DEFAULT 'Dr. A. Fernando',
  PRIMARY KEY (`clearance_id`),
  KEY `athletic_id` (`athletic_id`),
  KEY `sport_id` (`sport_id`),
  KEY `season_id` (`season_id`),
  CONSTRAINT `medical_clearances_ibfk_1` FOREIGN KEY (`athletic_id`) REFERENCES `Athletic` (`athlete_id`),
  CONSTRAINT `medical_clearances_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`),
  CONSTRAINT `medical_clearances_ibfk_3` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_clearances`
--

LOCK TABLES `medical_clearances` WRITE;
/*!40000 ALTER TABLE `medical_clearances` DISABLE KEYS */;
INSERT INTO `medical_clearances` VALUES
(2,4,6,1,'2027-08-10','Cleared','2026-08-08','Rotator Cuff Tendinitis fully resolved. Full swim clearance.','Dr. A. Fernando'),
(3,6,5,1,'2027-08-05','Cleared','2026-08-04','Hamstring Strain recovery completed. Cleared for sprinting.','Dr. N. Silva'),
(4,9,1,1,'2027-07-30','Cleared','2026-07-29','Wrist Fracture healed, radiographic bone union confirmed.','Dr. A. Fernando'),
(5,10,3,1,'2027-07-25','Cleared','2026-07-21','Heat exhaustion protocol completed and electrolyte stability verified.','Dr. N. Silva'),
(7,2,1,1,'2027-09-20','Cleared','2026-08-12','Annual baseline medical screening completed.','Dr. A. Fernando');
/*!40000 ALTER TABLE `medical_clearances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notfications`
--

DROP TABLE IF EXISTS `notfications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notfications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notfications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notfications`
--

LOCK TABLES `notfications` WRITE;
/*!40000 ALTER TABLE `notfications` DISABLE KEYS */;
INSERT INTO `notfications` VALUES
(7,1,'New equipment request pending: Kookaburra Turf Match Balls requested by Sarah Jenkins.',0,'2026-09-19 11:20:00'),
(8,1,'New squad nominated for Inter-Faculty Football: Faculty of Science vs Faculty of Arts.',0,'2026-09-20 08:45:00'),
(9,1,'New ground reservation request pending for Colombo University Grounds (Football training).',0,'2026-09-20 10:30:00');
/*!40000 ALTER TABLE `notfications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `physiotherapy_treatments`
--

DROP TABLE IF EXISTS `physiotherapy_treatments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `physiotherapy_treatments` (
  `treatment_id` int(11) NOT NULL,
  `injury_id` int(11) NOT NULL,
  `treatment_date` date NOT NULL,
  `treatment_type` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`treatment_id`),
  KEY `injury_id` (`injury_id`),
  CONSTRAINT `physiotherapy_treatments_ibfk_1` FOREIGN KEY (`injury_id`) REFERENCES `injuries` (`injury_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `physiotherapy_treatments`
--

LOCK TABLES `physiotherapy_treatments` WRITE;
/*!40000 ALTER TABLE `physiotherapy_treatments` DISABLE KEYS */;
/*!40000 ALTER TABLE `physiotherapy_treatments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seasons`
--

DROP TABLE IF EXISTS `seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `seasons` (
  `season_id` int(11) NOT NULL AUTO_INCREMENT,
  `season_year` int(11) NOT NULL,
  `academic_year` int(11) NOT NULL,
  `status` enum('Active','Archived') NOT NULL,
  PRIMARY KEY (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seasons`
--

LOCK TABLES `seasons` WRITE;
/*!40000 ALTER TABLE `seasons` DISABLE KEYS */;
INSERT INTO `seasons` VALUES
(1,2026,3,'Active');
/*!40000 ALTER TABLE `seasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sport_application`
--

DROP TABLE IF EXISTS `sport_application`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sport_application` (
  `application_id` int(11) NOT NULL AUTO_INCREMENT,
  `athletic_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `status` enum('Pending','Shortlisted','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `rejection_reason` text DEFAULT NULL,
  `statement_of_interest` text DEFAULT NULL,
  `applied_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`application_id`),
  KEY `athletic_application` (`athletic_id`),
  KEY `sport_application` (`sport_id`),
  KEY `season_application` (`season_id`),
  CONSTRAINT `athletic_application` FOREIGN KEY (`athletic_id`) REFERENCES `Athletic` (`athlete_id`),
  CONSTRAINT `season_application` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`season_id`),
  CONSTRAINT `sport_application` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sport_application`
--

LOCK TABLES `sport_application` WRITE;
/*!40000 ALTER TABLE `sport_application` DISABLE KEYS */;
INSERT INTO `sport_application` VALUES
(1,14,4,1,'Approved',NULL,'no','2026-09-23 23:42:24');
/*!40000 ALTER TABLE `sport_application` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sports`
--

DROP TABLE IF EXISTS `sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sports` (
  `sport_id` int(11) NOT NULL AUTO_INCREMENT,
  `sport_name` varchar(255) NOT NULL,
  PRIMARY KEY (`sport_id`),
  UNIQUE KEY `sport_name` (`sport_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sports`
--

LOCK TABLES `sports` WRITE;
/*!40000 ALTER TABLE `sports` DISABLE KEYS */;
INSERT INTO `sports` VALUES
(4,'Badminton'),
(2,'Basketball'),
(3,'Cricket'),
(1,'Football'),
(7,'Rugby'),
(6,'Swimming'),
(10,'Table Tennis'),
(9,'Tennis'),
(5,'Track & Field'),
(8,'Volleyball');
/*!40000 ALTER TABLE `sports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_attendance`
--

DROP TABLE IF EXISTS `training_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `athlete_id` int(11) NOT NULL,
  `status` enum('Present','Absent','Excused') NOT NULL DEFAULT 'Present',
  PRIMARY KEY (`attendance_id`),
  KEY `session_id` (`session_id`),
  KEY `athlete_id` (`athlete_id`),
  CONSTRAINT `training_attendance_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `training_sessions` (`session_id`),
  CONSTRAINT `training_attendance_ibfk_2` FOREIGN KEY (`athlete_id`) REFERENCES `Athletic` (`athlete_id`)
) ENGINE=InnoDB AUTO_INCREMENT=264 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_attendance`
--

LOCK TABLES `training_attendance` WRITE;
/*!40000 ALTER TABLE `training_attendance` DISABLE KEYS */;
INSERT INTO `training_attendance` VALUES
(101,1,14,'Present'),
(102,1,17,'Present'),
(103,1,15,'Present'),
(104,1,8,'Excused'),
(105,1,12,'Absent'),
(106,1,13,'Present'),
(107,1,1,'Present'),
(108,1,2,'Present'),
(109,1,3,'Present'),
(110,1,4,'Excused'),
(111,1,5,'Absent'),
(112,1,6,'Present'),
(113,1,7,'Present'),
(114,1,10,'Present'),
(115,1,9,'Present'),
(116,1,11,'Excused'),
(117,1,16,'Absent'),
(118,1,18,'Present'),
(119,1,19,'Present'),
(120,1,20,'Present'),
(121,2,14,'Present'),
(122,2,17,'Present'),
(123,2,15,'Excused'),
(124,2,8,'Absent'),
(125,2,12,'Present'),
(126,2,13,'Present'),
(127,2,1,'Present'),
(128,2,2,'Present'),
(129,2,3,'Excused'),
(130,2,4,'Absent'),
(131,2,5,'Present'),
(132,2,6,'Present'),
(133,2,7,'Present'),
(134,2,10,'Present'),
(135,2,9,'Excused'),
(136,2,11,'Absent'),
(137,2,16,'Present'),
(138,2,18,'Present'),
(139,2,19,'Present'),
(140,2,20,'Present'),
(161,4,14,'Excused'),
(162,4,17,'Absent'),
(163,4,15,'Present'),
(164,4,8,'Present'),
(165,4,12,'Present'),
(166,4,13,'Present'),
(167,4,1,'Excused'),
(168,4,2,'Absent'),
(169,4,3,'Present'),
(170,4,4,'Present'),
(171,4,5,'Present'),
(172,4,6,'Present'),
(173,4,7,'Excused'),
(174,4,10,'Absent'),
(175,4,9,'Present'),
(176,4,11,'Present'),
(177,4,16,'Present'),
(178,4,18,'Present'),
(179,4,19,'Excused'),
(180,4,20,'Absent'),
(181,5,14,'Absent'),
(182,5,17,'Present'),
(183,5,15,'Present'),
(184,5,8,'Present'),
(185,5,12,'Present'),
(186,5,13,'Excused'),
(187,5,1,'Absent'),
(188,5,2,'Present'),
(189,5,3,'Present'),
(190,5,4,'Present'),
(191,5,5,'Present'),
(192,5,6,'Excused'),
(193,5,7,'Absent'),
(194,5,10,'Present'),
(195,5,9,'Present'),
(196,5,11,'Present'),
(197,5,16,'Present'),
(198,5,18,'Excused'),
(199,5,19,'Absent'),
(200,5,20,'Present'),
(221,8,8,'Present'),
(222,8,1,'Present'),
(223,8,2,'Present'),
(224,8,3,'Present'),
(225,8,4,'Present'),
(226,8,5,'Present'),
(227,8,6,'Present'),
(228,8,7,'Present'),
(229,8,10,'Present'),
(230,8,9,'Present'),
(241,10,8,'Present'),
(242,10,1,'Present'),
(243,10,2,'Present'),
(244,10,3,'Present'),
(245,10,4,'Present'),
(246,10,5,'Present'),
(247,10,6,'Excused'),
(248,10,7,'Excused'),
(249,10,10,'Present'),
(250,10,9,'Absent');
/*!40000 ALTER TABLE `training_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_sessions`
--

DROP TABLE IF EXISTS `training_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `sport_id` int(11) NOT NULL,
  `facility_id` int(11) DEFAULT NULL,
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `duration_minutes` time NOT NULL,
  `session_type` enum('Practice','Conditioning','Tactical') NOT NULL,
  `focus_area` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `sport_id` (`sport_id`),
  CONSTRAINT `training_sessions_ibfk_1` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_sessions`
--

LOCK TABLES `training_sessions` WRITE;
/*!40000 ALTER TABLE `training_sessions` DISABLE KEYS */;
INSERT INTO `training_sessions` VALUES
(1,1,2,'2026-09-23','16:30:00','01:30:00','Practice','High-Press & Set Pieces','Match simulation with referee rehearsal before SLUG qualifier.'),
(2,1,1,'2026-09-22','06:00:00','01:30:00','Conditioning','Plyometrics & Conditioning','Early morning core mobility, resistance band drills, and squad weigh-in.'),
(4,3,2,'2026-09-19','16:00:00','02:00:00','Practice','Spin Bowling & Death Overs','Target yorker practice and middle-order acceleration under game scenarios.'),
(5,1,2,'2026-09-17','17:00:00','01:30:00','Conditioning','Counter-Attack & Crossing','Wing play combinations and finishing from edge-of-the-box cutbacks.'),
(8,2,2,'2026-09-24','10:00:00','01:30:00','Practice','Defensive Transition & Set-Piece Drills','ASD'),
(10,1,2,'2026-09-25','16:25:00','01:30:00','Practice','Defensive Transition & Set-Piece Drills','Bring full match kit, shin guards, and hydration fluids. Video tactical review starts 15 mins prior.');
/*!40000 ALTER TABLE `training_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `university_colors`
--

DROP TABLE IF EXISTS `university_colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `university_colors` (
  `award_id` int(11) NOT NULL AUTO_INCREMENT,
  `award_name` varchar(150) NOT NULL DEFAULT 'University Colours',
  `athelet_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `status` enum('Nominated','Approved','Awarded') NOT NULL,
  PRIMARY KEY (`award_id`),
  KEY `athelet_id` (`athelet_id`),
  KEY `season_id` (`season_id`),
  KEY `sport_id` (`sport_id`),
  CONSTRAINT `university_colors_ibfk_1` FOREIGN KEY (`athelet_id`) REFERENCES `Athletic` (`athlete_id`),
  CONSTRAINT `university_colors_ibfk_2` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`season_id`),
  CONSTRAINT `university_colors_ibfk_3` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `university_colors`
--

LOCK TABLES `university_colors` WRITE;
/*!40000 ALTER TABLE `university_colors` DISABLE KEYS */;
INSERT INTO `university_colors` VALUES
(1,'University Full Colours',1,1,1,'Awarded'),
(2,'University Half Colours',2,1,4,'Approved'),
(3,'University Full Colours',3,1,2,'Nominated'),
(4,'Special Sports Merit Honour',4,1,5,'Nominated');
/*!40000 ALTER TABLE `university_colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(75) NOT NULL,
  `phone_number` varchar(20) DEFAULT '+94 11 258 1835',
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Athlete','Coach','TIC','Medical_Officer','System_Admin','President','Physiotherapist','Facility_Manager') NOT NULL,
  `security_question` varchar(255) NOT NULL,
  `security_answere_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES
(1,'tic_admin','tic@uoc.ac.lk','+94 11 258 1835','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','TIC','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(2,'med_officer','medical@uoc.ac.lk','+94 11 258 5500','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Medical_Officer','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(3,'athlete_user','athlete@uoc.ac.lk','+94 77 345 6789','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(4,'kamal_gunaratne','kamal@uoc.ac.lk','+94 71 456 7890','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(5,'hasith_eranga','eranga.hasitha04@gmail.com','+94 76 567 8901','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(6,'hasith_eranga1','ranga.hasitha04@gmail.com','+94 75 901 2345','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(7,'test_user','test@uoc.ac.lk','+94 78 789 0123','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(8,'new_user','newuser@uoc.ac.lk','+94 70 890 1234','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(9,'pres_admin','president@uoc.ac.lk','+94 11 258 1836','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','President','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(10,'athlete_kasun','kasun@uoc.ac.lk','+94 77 123 4567','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(11,'athlete_sarah','sarah@uoc.ac.lk','+94 77 987 6543','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(12,'athlete_marcus','marcus@uoc.ac.lk','+94 71 555 1234','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(13,'athlete_elena','elena@uoc.ac.lk','+94 76 222 3344','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(14,'athlete_tom','tom@uoc.ac.lk','+94 78 444 5566','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(15,'athlete_dinuka','dinuka@uoc.ac.lk','+94 70 111 2233','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(16,'athlete_amali','amali@uoc.ac.lk','+94 71 888 9900','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(17,'saman_kusum','saman@uoc.ac.lk','+94 11 258 1837','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','TIC','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(18,'coach_sunil','sunil.coach@uoc.ac.lk','+94 77 441 2389','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Coach','What is your favorite sport?','$2y$10$ITMqqY7/Sos7GWD3d2hWFO8MFoxyPRhbYmxO./Fi279WuylHCbtxq'),
(19,'coach_anura','anura.coach@uoc.ac.lk','+94 71 882 3490','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Coach','What is your favorite sport?','$2y$10$sgBlJK92ei3LV7pQ9OVkMO8GG.71rc8sDY5XFIdc87uOuEzgtf05a'),
(20,'coach_pradeep','pradeep.coach@uoc.ac.lk','+94 76 993 4501','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Coach','What is your favorite sport?','$2y$10$ZDzVfZ3yjaz1oucfl.a6n.OaeN3HIEpfZxLltpzPKvOplWAtts5mC'),
(21,'tamasha_sevindi','tamasha@uoc.ac.lk','+94 77 678 9012','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),
(23,'dilshan_pathirana','dilshan@uoc.ac.lk','+94 77 443 3221','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),
(24,'ruwan_weerasinghe','ruwan@uoc.ac.lk','+94 76 332 2110','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),
(25,'kusal_mendis','kusal@uoc.ac.lk','+94 71 223 3445','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),
(26,'sanath_jayasuriya','sanath@uoc.ac.lk','+94 77 556 6778','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),
(27,'suresh_silva','suresh@uoc.ac.lk','+94 78 112 2334','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),
(28,'kaveesha_w','kaveesha@uoc.ac.lk','+94 77 889 9001','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),
(29,'sanduni_j','sanduni@uoc.ac.lk','+94 76 998 8776','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),
(30,'yumal_perera','yumal@uoc.ac.lk','+94 77 123 4567','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Athlete','What is your favorite sport?','$2y$10$9aJxQF6yREa0K4DevKMa/Ors76LpLM9dvde7ttY6jjS7L9Go7aO1S'),
(31,'coach_silva','coach.silva@uoc.ac.lk','+94 77 112 2334','$2y$10$1jtwhRnmqbDhA4u1ECj8kOG3.sDvHfTyMjyj7Qt1QUGmik3LrtLaq','Coach','What is your favorite sport?','$2y$10$1xPfjRwsk/cUy0uAW2uRI.Mf8C3.R6Bm5oI.oz.VMOwt0.apGWAS2'),
(32,'admin','admin@uoc.ac.lk','+94 11 258 1835','$2y$10$5ZwkeSL2IFV5TOyGBlhNlOKObdC1UhbIgYmxofOoh/Va4A8Mqwk.a','System_Admin','What is the system campus?','$2y$10$1yrVoXEiv.4IPwniFNYfneeJOBiT5T0V5LyM5KuAE3/UacoDAzibS'),
(39,'mikas_samn','mika@uoc.ac.lk','0712224571','$2y$10$r2p9R8cz/YwvdXmIpDXoh.FJyofb2l15tdkJV567tAtrvbcT6T2sO','Athlete','What is your favorite sport?','$2y$10$uPp21hahHe9skHxdAQqHVu0TdL.ZrdOBXTHyaMsxxQE70kfiLYZi2');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-27 11:22:09
