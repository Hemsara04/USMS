-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: usms
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
-- Create and select the USMS database
--
CREATE DATABASE IF NOT EXISTS `USMS` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `USMS`;

--
-- Table structure for table `Athletic`
--

DROP TABLE IF EXISTS `Athletic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Athletic`
--

LOCK TABLES `Athletic` WRITE;
/*!40000 ALTER TABLE `Athletic` DISABLE KEYS */;
INSERT INTO `Athletic` VALUES (1,10,'SC/2022/1042','Kasun Perera',3,'Faculty of Science',178.50,72.00,'+94771234567','O+','Fit to Compete','None','Under concussion recovery protocol. Restricting contact drills.','2026-09-20'),(2,11,'AR/2021/8821','Sarah Jenkins',4,'Faculty of Arts',169.00,58.50,'+94779876543','A+','Fit to Compete','None','Annual baseline medical screening completed.','2026-08-12'),(3,12,'MG/2023/4420','Marcus Thorne',2,'Faculty of Management & Finance',185.00,84.00,'+94715551234','B+','Under Review','Asthma (mild)','Requires salbutamol inhaler on bench. Ankle rehab ongoing.','2026-09-14'),(4,13,'MD/2022/1928','Elena Rodriguez',3,'Faculty of Medicine',165.00,55.00,'+94762223344','O+','Under Review','Penicillin','Cleared for inter-university aquatic meets.','2026-09-20'),(5,14,'TC/2023/3391','Tom Aris',2,'Faculty of Technology',180.00,76.50,'+94784445566','AB+','Under Review','None','Shoulder strain undergoing physiotherapy sessions.','2026-09-16'),(6,15,'SC/2021/2049','Dinuka Fernando',4,'Faculty of Science',175.00,68.00,'+94701112233','A-','Fit to Compete','Dust allergy','Cardiac fitness optimal. Passed sprint stress test.','2026-08-04'),(7,16,'AR/2023/5021','Amali Wickramasinghe',2,'Faculty of Arts',162.00,52.00,'+94718889900','B-','Fit to Compete','None','Normal ECG, lung capacity normal.','2026-08-18'),(8,6,'SC/2023/3305','Malith Perera',2,'Faculty of Science',172.00,66.50,'+94759012345','O+','Temporarily Unfit','None','Patellar tendinitis, rest advised for 7 days.','2026-09-17'),(9,23,'AR/2022/6630','Dilshan Pathirana',3,'Faculty of Arts',180.00,74.00,'+94774433221','A+','Fit to Compete','None','Fully recovered from wrist fracture.','2026-07-29'),(10,21,'MG/2022/2204','Tamasha Sevindi',3,'Faculty of Management & Finance',166.00,56.00,'+94776789012','O-','Fit to Compete','None','Hydration screening passed.','2026-07-21'),(11,24,'MG/2021/7701','Ruwan Weerasinghe',4,'Faculty of Management & Finance',177.00,71.00,'+94763322110','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),(12,7,'MD/2023/4019','Chamara Bandara',2,'Faculty of Medicine',173.00,69.00,'+94787890123','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),(13,8,'TC/2022/1908','Nadeesha Dilhani',3,'Faculty of Technology',163.00,54.00,'+94708901234','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),(14,3,'CS/2022/9012','Thilina Madushanka',3,'UCSC',176.00,70.00,'+94773456789','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),(15,5,'CS/2023/1102','Hasitha Eranga',2,'UCSC',174.00,67.00,'+94765678901','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),(16,25,'CS/2021/8044','Kusal Mendis',4,'UCSC',178.00,73.00,'+94712233445','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),(17,4,'LW/2021/3015','Kamal Gunaratne',4,'Faculty of Law',181.00,78.00,'+94714567890','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),(18,26,'LW/2023/2004','Sanath Jayasuriya Jr',2,'Faculty of Law',179.00,75.00,'+94775566778','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),(19,27,'ED/2022/4410','Suresh Silva',3,'Faculty of Education',170.00,65.00,'+94781122334','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),(20,28,'NU/2023/1020','Kaveesha Wijesinghe',2,'Faculty of Nursing',164.00,53.00,'+94778899001','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20'),(21,29,'IM/2022/5510','Sanduni Jayawardena',3,'Faculty of Indigenous Medicine',160.00,50.00,'+94769988776','B+','Fit to Compete','None','All routine physical parameters within healthy athletic limits.','2026-08-20');
/*!40000 ALTER TABLE `Athletic` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (2,9,'Varsity Sports Equipment Restocked for Season 2026','The Amalgamated Club has procured new official match gear including Yonex shuttlecocks, Kookaburra cricket turf balls, and Nike match footballs. Coaches and athletes may submit equipment checkout requests online.','2026-09-19 14:30:00','Coaches & Staff','Normal','Active',NULL),(5,1,'Practice courts 3 and 4 are closed for maintenance','Court resurfacing underway until Tuesday morning. All squad training sessions must relocate to Main Gym Court 1 or outdoor hardcourt.','2026-09-20 08:00:00','All Users','Urgent','Archived','2026-09-20 22:32:25'),(6,1,'Annual Colors Night nominations are now open - Final Call','Submit nominations through the Sports Council portal by October 15th. Athletes with national or inter-university medals are eligible for university colors.','2026-09-15 11:20:00','Student Athletes','Normal','Active','2026-09-20 22:37:01'),(9,9,'medical report','imedeatly update all report','2026-09-20 23:23:34','Medical Staff','Normal','Active',NULL);
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL AUTO_INCREMENT,
  `sport_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity_in_stock` int(11) NOT NULL,
  `condition_status` enum('New','Good','Needs Repair') NOT NULL DEFAULT 'Good',
  PRIMARY KEY (`equipment_id`),
  KEY `sport_id` (`sport_id`),
  CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment`
--

LOCK TABLES `equipment` WRITE;
/*!40000 ALTER TABLE `equipment` DISABLE KEYS */;
INSERT INTO `equipment` VALUES (1,1,'Elite GPS Trackers (Set of 10)',10,'Good'),(2,1,'Nike Strike Training Footballs',35,'New'),(3,1,'Agility Training Hurdles Set',18,'Good'),(4,2,'Spalding TF-1000 Basketballs',28,'New'),(5,2,'Pro-Grade Rebounder Net',4,'Good'),(6,3,'SS Ton Reserve Cricket Bats',14,'Good'),(7,3,'Kookaburra Turf Match Balls',55,'New'),(8,5,'High-Jump Pit Measurement Kit',6,'Good'),(9,5,'Starting Blocks (Olympic Standard)',8,'Good'),(10,4,'Yonex Nanoflare Rackets',22,'Good'),(11,4,'Yonex Aerosensa Shuttlecock Tubes',75,'New'),(12,6,'Digital Stopwatch & Pace Clocks',10,'Good'),(13,9,'Wilson Pro Staff Tennis Rackets',14,'Good'),(14,9,'Wilson Championship Ball Tubes',45,'New'),(15,4,'Badminton Shuttlecocks Pack',20,'Good'),(16,1,'Corner Flags & Spring Poles (Set 4)',4,'Needs Repair'),(17,2,'Heavy Duty Basketball Rim & Net',3,'Needs Repair'),(18,3,'Spring-Loaded Cricket Stumps Set',5,'Good'),(19,4,'Badminton Tournament Net',2,'Needs Repair'),(20,5,'Aluminum Relay Batons (Set of 8)',12,'New'),(21,6,'Speedo Training Kickboards',25,'Good'),(22,10,'Stiga Pro Carbon TT Bats',16,'Good'),(23,10,'Nittaku 3-Star TT Balls (Pack 72)',25,'New'),(24,8,'Mikasa V200W Official Volleyballs',20,'New'),(25,8,'Volleyball Antenna & Net Straps',3,'Needs Repair');
/*!40000 ALTER TABLE `equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_issue`
--

DROP TABLE IF EXISTS `equipment_issue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_issue`
--

LOCK TABLES `equipment_issue` WRITE;
/*!40000 ALTER TABLE `equipment_issue` DISABLE KEYS */;
INSERT INTO `equipment_issue` VALUES (19,7,6,2,'Athlete',NULL,'2026-09-18','2026-09-24',NULL,'Pending'),(20,10,2,14,'Athlete',NULL,'2026-09-19','2026-09-26',NULL,'Pending'),(21,2,4,1,'Athlete',NULL,'2026-09-19','2026-09-25',NULL,'Pending'),(22,4,3,NULL,'Coach','coach_pradeep','2026-09-20','2026-09-27',NULL,'Pending'),(23,13,2,4,'Athlete',NULL,'2026-09-20','2026-09-25',NULL,'Pending'),(24,12,2,5,'Athlete',NULL,'2026-09-20','2026-09-28',NULL,'Pending'),(25,1,2,NULL,'Coach','coach_sunil','2026-09-15','2026-09-23',NULL,'Issued'),(26,3,1,6,'Athlete',NULL,'2026-09-16','2026-09-24',NULL,'Issued'),(27,4,4,3,'Athlete',NULL,'2026-09-17','2026-09-24',NULL,'Issued'),(28,6,2,NULL,'Coach','coach_anura','2026-09-18','2026-09-25',NULL,'Issued'),(29,9,2,7,'Athlete',NULL,'2026-09-18','2026-09-26',NULL,'Issued'),(30,10,3,17,'Athlete',NULL,'2026-09-19','2026-09-25',NULL,'Issued'),(31,22,4,10,'Athlete',NULL,'2026-09-19','2026-09-26',NULL,'Issued'),(32,5,1,15,'Athlete',NULL,'2026-09-20','2026-09-27',NULL,'Issued'),(33,14,2,NULL,'Coach','coach_pradeep','2026-09-10','2026-09-15','2026-09-14','Returned'),(34,2,5,1,'Athlete',NULL,'2026-09-11','2026-09-16','2026-09-15','Returned');
/*!40000 ALTER TABLE `equipment_issue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facilities`
--

DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
INSERT INTO `facilities` VALUES (1,'Main Gymnasium','Hall',500,'06:00 - 21:00'),(2,'Colombo University Grounds','Ground',2000,'06:00 - 18:30'),(3,'Indoor Sports Arena','Hall',350,'07:00 - 22:00'),(4,'University Tennis Courts','Ground',150,'06:30 - 19:00'),(5,'Swimming Pool Complex','Hall',300,'06:00 - 20:00');
/*!40000 ALTER TABLE `facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facility_bookings`
--

DROP TABLE IF EXISTS `facility_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facility_bookings`
--

LOCK TABLES `facility_bookings` WRITE;
/*!40000 ALTER TABLE `facility_bookings` DISABLE KEYS */;
INSERT INTO `facility_bookings` VALUES (8,2,1,'2026-09-22','15:30:00',120,'Pending'),(9,1,2,'2026-09-23','17:00:00',90,'Pending'),(10,3,4,'2026-09-24','08:30:00',120,'Pending'),(11,5,6,'2026-09-25','07:00:00',90,'Pending'),(12,2,3,'2026-09-26','14:00:00',180,'Approved'),(13,4,9,'2026-09-27','16:30:00',90,'Approved');
/*!40000 ALTER TABLE `facility_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fixtures`
--

DROP TABLE IF EXISTS `fixtures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fixtures`
--

LOCK TABLES `fixtures` WRITE;
/*!40000 ALTER TABLE `fixtures` DISABLE KEYS */;
INSERT INTO `fixtures` VALUES (11,1,2,'University of Peradeniya','Inter-University','University of Colombo','2026-09-25','15:30:00'),(12,2,1,'University of Moratuwa','Inter-University','University of Colombo','2026-09-28','17:00:00'),(13,3,2,'University of Kelaniya','Inter-University','University of Colombo','2026-10-02','09:30:00'),(14,4,3,'Univ. of Sri Jayewardenepura','Inter-University','University of Colombo','2026-10-05','14:00:00'),(15,5,2,'University of Ruhuna','Inter-University','University of Colombo','2026-10-08','08:00:00'),(16,1,2,'Faculty of Arts','Inter-Faculty','Faculty of Science','2026-09-24','16:00:00'),(17,3,2,'Faculty of Management & Finance','Inter-Faculty','UCSC','2026-09-27','14:30:00'),(18,2,1,'Faculty of Technology','Inter-Faculty','Faculty of Medicine','2026-09-29','16:30:00'),(19,4,3,'Faculty of Science','Inter-Faculty','Faculty of Law','2026-10-01','15:00:00'),(20,10,3,'UCSC','Inter-Faculty','Faculty of Technology','2026-10-03','16:00:00'),(21,1,2,'UOC Varsity B (Development Squad)','Internal','UOC Varsity A','2026-09-26','07:30:00');
/*!40000 ALTER TABLE `fixtures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `injuries`
--

DROP TABLE IF EXISTS `injuries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
INSERT INTO `injuries` VALUES (1,1,7,'Concussion Protocol (Stage 5)','Major','2026-09-12','2026-09-26',0,'Head collision during rugby scrimmage. Cognitive review pending.','Dr. A. Fernando'),(2,3,2,'Grade II Ankle Sprain','Moderate','2026-09-14','2026-09-28',1,'Lateral inversion sprain during basketball rebound. Ice & elevation therapy.','Dr. A. Fernando'),(3,5,1,'AC Joint Shoulder Strain','Moderate','2026-09-16','2026-09-30',1,'Subluxation during tackle drill in football.','Dr. N. Silva'),(4,8,5,'Patellar Tendinopathy (Knee)','Minor','2026-09-17','2026-09-24',1,'Overuse inflammation on right patellar tendon in track training.','Dr. A. Fernando'),(5,4,6,'Shoulder Impingement Syndrome','Moderate','2026-09-20','2026-10-04',1,'Mild bursitis during butterfly stroke practice','Dr. A. Fernando');
/*!40000 ALTER TABLE `injuries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_results`
--

DROP TABLE IF EXISTS `match_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `match_results` (
  `result_id` int(11) NOT NULL,
  `fixture_id` int(11) NOT NULL,
  `scores` varchar(10) NOT NULL,
  `scorers` text NOT NULL,
  `player_ratings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`player_ratings`)),
  PRIMARY KEY (`result_id`),
  UNIQUE KEY `fixture_id` (`fixture_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_results`
--

LOCK TABLES `match_results` WRITE;
/*!40000 ALTER TABLE `match_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `match_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_squads`
--

DROP TABLE IF EXISTS `match_squads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_squads`
--

LOCK TABLES `match_squads` WRITE;
/*!40000 ALTER TABLE `match_squads` DISABLE KEYS */;
INSERT INTO `match_squads` VALUES (27,11,1,'Pending'),(28,11,2,'Pending'),(29,11,3,'Pending'),(30,11,4,'Pending'),(31,11,5,'Pending'),(32,11,14,'Approved'),(33,11,17,'Approved'),(34,12,3,'Approved'),(35,12,5,'Approved'),(36,12,15,'Pending'),(37,12,6,'Pending'),(38,13,16,'Pending'),(39,13,9,'Pending'),(40,13,11,'Pending'),(41,14,14,'Pending'),(42,14,17,'Pending'),(43,15,7,'Approved'),(44,15,5,'Approved'),(45,15,1,'Approved'),(46,16,1,'Pending'),(47,16,6,'Pending'),(48,16,8,'Pending'),(49,17,14,'Pending'),(50,17,15,'Pending'),(51,17,16,'Pending'),(52,18,4,'Pending'),(53,18,12,'Pending'),(54,19,17,'Pending'),(55,19,18,'Pending'),(56,20,5,'Pending'),(57,20,13,'Pending'),(58,21,1,'Approved'),(59,21,2,'Approved');
/*!40000 ALTER TABLE `match_squads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_clearances`
--

DROP TABLE IF EXISTS `medical_clearances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
INSERT INTO `medical_clearances` VALUES (2,4,6,1,'2027-08-10','Cleared','2026-08-08','Rotator Cuff Tendinitis fully resolved. Full swim clearance.','Dr. A. Fernando'),(3,6,5,1,'2027-08-05','Cleared','2026-08-04','Hamstring Strain recovery completed. Cleared for sprinting.','Dr. N. Silva'),(4,9,1,1,'2027-07-30','Cleared','2026-07-29','Wrist Fracture healed, radiographic bone union confirmed.','Dr. A. Fernando'),(5,10,3,1,'2027-07-25','Cleared','2026-07-21','Heat exhaustion protocol completed and electrolyte stability verified.','Dr. N. Silva'),(7,2,1,1,'2027-09-20','Cleared','2026-08-12','Annual baseline medical screening completed.','Dr. A. Fernando');
/*!40000 ALTER TABLE `medical_clearances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notfications`
--

DROP TABLE IF EXISTS `notfications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notfications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notfications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notfications`
--

LOCK TABLES `notfications` WRITE;
/*!40000 ALTER TABLE `notfications` DISABLE KEYS */;
INSERT INTO `notfications` VALUES (1,1,'New equipment request pending: Kookaburra Turf Match Balls requested by Sarah Jenkins.',0,'2026-09-19 11:20:00'),(2,1,'New squad nominated for Inter-Faculty Football: Faculty of Science vs Faculty of Arts.',0,'2026-09-20 08:45:00'),(3,1,'New ground reservation request pending for Colombo University Grounds (Football training).',0,'2026-09-20 10:30:00');
/*!40000 ALTER TABLE `notfications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `physiotherapy_treatments`
--

DROP TABLE IF EXISTS `physiotherapy_treatments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seasons` (
  `season_id` int(11) NOT NULL AUTO_INCREMENT,
  `season_year` int(11) NOT NULL,
  `academic_year` int(11) NOT NULL,
  `status` enum('Active','Archived') NOT NULL,
  PRIMARY KEY (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seasons`
--

LOCK TABLES `seasons` WRITE;
/*!40000 ALTER TABLE `seasons` DISABLE KEYS */;
INSERT INTO `seasons` VALUES (1,2026,3,'Active');
/*!40000 ALTER TABLE `seasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sport_application`
--

DROP TABLE IF EXISTS `sport_application`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sport_application` (
  `application_id` int(11) NOT NULL AUTO_INCREMENT,
  `athletic_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `status` enum('Pending','Shortlisted','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `rejection_reason` text DEFAULT NULL,
  `statement_of_interest` text DEFAULT NULL,
  `applied_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`application_id`),
  KEY `athletic_application` (`athletic_id`),
  KEY `sport_application` (`sport_id`),
  KEY `season_application` (`season_id`),
  CONSTRAINT `athletic_application` FOREIGN KEY (`athletic_id`) REFERENCES `Athletic` (`athlete_id`),
  CONSTRAINT `season_application` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`season_id`),
  CONSTRAINT `sport_application` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sport_application`
--

LOCK TABLES `sport_application` WRITE;
/*!40000 ALTER TABLE `sport_application` DISABLE KEYS */;
/*!40000 ALTER TABLE `sport_application` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sports`
--

DROP TABLE IF EXISTS `sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
INSERT INTO `sports` VALUES (4,'Badminton'),(2,'Basketball'),(3,'Cricket'),(1,'Football'),(7,'Rugby'),(6,'Swimming'),(10,'Table Tennis'),(9,'Tennis'),(5,'Track & Field'),(8,'Volleyball');
/*!40000 ALTER TABLE `sports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_attendance`
--

DROP TABLE IF EXISTS `training_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `training_attendance` (
  `attendance_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `athlete_id` int(11) NOT NULL,
  `status` enum('Present','Absent') NOT NULL,
  PRIMARY KEY (`attendance_id`),
  KEY `session_id` (`session_id`),
  KEY `athlete_id` (`athlete_id`),
  CONSTRAINT `training_attendance_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `training_sessions` (`session_id`),
  CONSTRAINT `training_attendance_ibfk_2` FOREIGN KEY (`athlete_id`) REFERENCES `Athletic` (`athlete_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_attendance`
--

LOCK TABLES `training_attendance` WRITE;
/*!40000 ALTER TABLE `training_attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_sessions`
--

DROP TABLE IF EXISTS `training_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `training_sessions` (
  `session_id` int(11) NOT NULL,
  `sport_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `duration_minutes` time NOT NULL,
  `session_type` enum('Practice','Conditioning','Tactical') NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `sport_id` (`sport_id`),
  CONSTRAINT `training_sessions_ibfk_1` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_sessions`
--

LOCK TABLES `training_sessions` WRITE;
/*!40000 ALTER TABLE `training_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `university_colors`
--

DROP TABLE IF EXISTS `university_colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `university_colors` (
  `award_id` int(11) NOT NULL AUTO_INCREMENT,
  `award_name` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `university_colors`
--

LOCK TABLES `university_colors` WRITE;
/*!40000 ALTER TABLE `university_colors` DISABLE KEYS */;
/*!40000 ALTER TABLE `university_colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'tic_admin','tic@uoc.ac.lk','+94 11 258 1835','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','TIC','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(2,'med_officer','medical@uoc.ac.lk','+94 11 258 5500','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Medical_Officer','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(3,'athlete_user','athlete@uoc.ac.lk','+94 77 345 6789','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(4,'kamal_gunaratne','kamal@uoc.ac.lk','+94 71 456 7890','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(5,'hasith_eranga','eranga.hasitha04@gmail.com','+94 76 567 8901','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(6,'hasith_eranga1','ranga.hasitha04@gmail.com','+94 75 901 2345','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(7,'test_user','test@uoc.ac.lk','+94 78 789 0123','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(8,'new_user','newuser@uoc.ac.lk','+94 70 890 1234','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(9,'pres_admin','president@uoc.ac.lk','+94 11 258 1836','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','President','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(10,'athlete_kasun','kasun@uoc.ac.lk','+94 77 123 4567','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(11,'athlete_sarah','sarah@uoc.ac.lk','+94 77 987 6543','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(12,'athlete_marcus','marcus@uoc.ac.lk','+94 71 555 1234','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(13,'athlete_elena','elena@uoc.ac.lk','+94 76 222 3344','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(14,'athlete_tom','tom@uoc.ac.lk','+94 78 444 5566','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(15,'athlete_dinuka','dinuka@uoc.ac.lk','+94 70 111 2233','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(16,'athlete_amali','amali@uoc.ac.lk','+94 71 888 9900','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(17,'saman_kusum','saman@uoc.ac.lk','+94 11 258 1837','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','TIC','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(18,'coach_sunil','sunil.coach@uoc.ac.lk','+94 77 441 2389','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Coach','What is your favorite sport?','$2y$10$ITMqqY7/Sos7GWD3d2hWFO8MFoxyPRhbYmxO./Fi279WuylHCbtxq'),(19,'coach_anura','anura.coach@uoc.ac.lk','+94 71 882 3490','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Coach','What is your favorite sport?','$2y$10$sgBlJK92ei3LV7pQ9OVkMO8GG.71rc8sDY5XFIdc87uOuEzgtf05a'),(20,'coach_pradeep','pradeep.coach@uoc.ac.lk','+94 76 993 4501','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Coach','What is your favorite sport?','$2y$10$ZDzVfZ3yjaz1oucfl.a6n.OaeN3HIEpfZxLltpzPKvOplWAtts5mC'),(21,'tamasha_sevindi','tamasha@uoc.ac.lk','+94 77 678 9012','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(22,'killwe_efd','dsds@uoc.ac.lk','+94 11 258 1835','$2y$10$XK2lAPUEM1mJvmR/NOXNg.XNzAzkfqnvWxVlKOmywc/INOgkg/iAC','Medical_Officer','What is your favorite sport?','$2y$10$.E5DVoFjNl9egKaYENZhpu/kaWW0UfPCdnZmXMwPCgKo4frngISFS'),(23,'dilshan_pathirana','dilshan@uoc.ac.lk','+94 77 443 3221','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),(24,'ruwan_weerasinghe','ruwan@uoc.ac.lk','+94 76 332 2110','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),(25,'kusal_mendis','kusal@uoc.ac.lk','+94 71 223 3445','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),(26,'sanath_jayasuriya','sanath@uoc.ac.lk','+94 77 556 6778','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),(27,'suresh_silva','suresh@uoc.ac.lk','+94 78 112 2334','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),(28,'kaveesha_w','kaveesha@uoc.ac.lk','+94 77 889 9001','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW'),(29,'sanduni_j','sanduni@uoc.ac.lk','+94 76 998 8776','$2y$10$YzEu0TLBP7ImwyY2sRUyT.KPqq7am2o5RJ.uTUYmQz.NfzrFYM4Ke','Athlete','What is your primary sport?','$2y$10$/Rr2XZlSTkmtLS.3bJDhtuTbHOtd3W/A8KZR9zecBgGOUJXVtfJgW');
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

-- Dump completed on 2026-09-20 23:39:30
