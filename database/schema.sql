-- MySQL dump 10.13  Distrib 8.0.31, for Linux (x86_64)
--
-- Host: localhost    Database: pos
-- ------------------------------------------------------
-- Server version	8.0.31

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `catalog`
--

DROP TABLE IF EXISTS `catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalog` (
  `id` int NOT NULL AUTO_INCREMENT,
  `barcode` int DEFAULT NULL,
  `name` char(30) DEFAULT NULL,
  `dept_id` tinyint DEFAULT NULL,
  `vendor_name` char(20) DEFAULT NULL,
  `product_id` char(24) DEFAULT NULL,
  `manufacturer_id` char(24) DEFAULT NULL,
  `vendor_id` char(4) DEFAULT NULL,
  `price` float(7,2) DEFAULT NULL,
  `qty` int DEFAULT '0',
  `override_price` binary(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customer_jobs`
--

DROP TABLE IF EXISTS `customer_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_jobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `name` char(64) DEFAULT NULL,
  `active` binary(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company` char(64) DEFAULT NULL,
  `last_name` char(50) DEFAULT NULL,
  `first_name` char(50) DEFAULT NULL,
  `mi` char(3) DEFAULT NULL,
  `address` char(100) DEFAULT NULL,
  `address2` char(100) DEFAULT NULL,
  `city` char(50) DEFAULT NULL,
  `state` char(30) DEFAULT NULL,
  `zip` char(10) DEFAULT NULL,
  `phone` varchar(42) DEFAULT NULL,
  `credit` binary(1) DEFAULT NULL,
  `tax_exempt` binary(1) DEFAULT '0',
  `active` binary(1) DEFAULT '1',
  `use_company` binary(1) DEFAULT '0',
  `phone_ext` char(4) DEFAULT NULL,
  `print_statement` binary(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `last_name` (`last_name`,`first_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `drawer_balance` float(8,2) DEFAULT NULL,
  `action` char(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `ticket`
--

DROP TABLE IF EXISTS `ticket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket` (
  `display_id` int NOT NULL DEFAULT '0',
  `customer_id` int DEFAULT NULL,
  `job_id` int DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `subtotal` float(8,2) DEFAULT NULL,
  `tax` float(6,2) DEFAULT NULL,
  `total` float(8,2) DEFAULT NULL,
  `discount` float(8,2) DEFAULT NULL,
  `freight` float(8,2) DEFAULT NULL,
  `labor` float(8,2) DEFAULT NULL,
  `payment_type` char(15) DEFAULT NULL,
  `refund` binary(1) DEFAULT '0',
  `check_no` int DEFAULT NULL,
  `cc_trans_no` int DEFAULT NULL,
  `resale` binary(1) DEFAULT '0',
  `id` int NOT NULL AUTO_INCREMENT,
  `recv_by` varchar(24) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `display_id` (`display_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transaction_items`
--

DROP TABLE IF EXISTS `transaction_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `item_id` int DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `price` float(8,2) DEFAULT NULL,
  `amount` float(8,2) DEFAULT NULL,
  `name` char(30) DEFAULT NULL,
  `product_id` char(24) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Dump completed on 2023-01-04 22:21:04
