-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: takeoff_toktm
-- ------------------------------------------------------
-- Server version	8.0.42-0ubuntu0.20.04.1

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
-- Table structure for table `admin_permissions`
--

DROP TABLE IF EXISTS `admin_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `page_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_admin_page` (`admin_id`,`page_key`),
  CONSTRAINT `fk_admin_permissions_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_permissions`
--

LOCK TABLES `admin_permissions` WRITE;
/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` VALUES (17,1,'dashboard',1),(18,1,'devices',1),(19,1,'checkin',1),(20,1,'send_notification',1),(21,1,'facilities',1),(22,1,'amenities',1),(23,1,'information',1),(24,1,'dining',1),(25,1,'dining_orders',1),(26,1,'amenity_requests',1),(27,1,'app_control',1),(28,1,'running_text',1),(29,1,'update',1),(30,1,'flashscreen',1),(31,1,'server_config',1),(32,1,'users',1),(65,1,'iptv',1),(67,11,'dashboard',1),(68,11,'devices',1),(69,11,'checkin',1),(70,11,'send_notification',1),(71,11,'facilities',1),(72,11,'amenities',1),(73,11,'information',1),(74,11,'dining',1),(75,11,'dining_orders',1),(76,11,'amenity_requests',1),(77,11,'app_control',1),(78,11,'running_text',1),(79,11,'update',1),(80,11,'flashscreen',1),(81,11,'server_config',1),(82,11,'users',1),(83,11,'iptv',1);
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `display_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('superadmin','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `idx_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'rizal','rizal','$2y$10$EhDVx1DdZwoL3N.3dzQFfOmFBCVM.Txe1TUMtUMoaOZRKkVh8A98K','superadmin','2025-10-27 10:44:50'),(11,'admin','admin','$2y$10$lJ9L.nV4DaaDUybQIIQNkuXsIpIT6E3MIqhLGtEnkh4Oj/.fgWd5W','superadmin','2026-06-10 14:15:57');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amenity_requests`
--

DROP TABLE IF EXISTS `amenity_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `amenity_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `items` text COMMENT 'JSON array of requested items',
  `status` enum('Pending','Delivered','Cancelled') DEFAULT 'Pending',
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amenity_requests`
--

LOCK TABLES `amenity_requests` WRITE;
/*!40000 ALTER TABLE `amenity_requests` DISABLE KEYS */;
INSERT INTO `amenity_requests` VALUES (1,'102','Guest','[{\"id\":14,\"name\":\"Sajadah\",\"description\":\"Alat sholat (1 set)\",\"icon_path\":\"http://192.168.1.169/AHFix/uploads/amenities/amenity_1762854461_1137.jpg\",\"qty\":1}]','Pending','2026-03-15 11:03:36'),(2,'101','Taji','[{\"id\":13,\"name\":\"Perlengkapan Mandi\",\"description\":\"Sabun, Shampoo, Sikat Gigi\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/amenity_1762854476_7748.jpg\",\"qty\":1},{\"id\":14,\"name\":\"Sajadah\",\"description\":\"Alat sholat (1 set)\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/amenity_1762854461_1137.jpg\",\"qty\":1},{\"id\":17,\"name\":\"Sajadah\",\"description\":\"Alat Shalat 1 set\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/am_1764212513.jpg\",\"qty\":1}]','Pending','2026-03-16 08:00:25'),(3,'101','Taji','[{\"id\":13,\"name\":\"Perlengkapan Mandi\",\"description\":\"Sabun, Shampoo, Sikat Gigi\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/amenity_1762854476_7748.jpg\",\"qty\":1}]','Pending','2026-03-16 08:00:26'),(4,'101','Taji','[{\"id\":12,\"name\":\"Bantal Tambahan\",\"description\":\"Bantal tidur ekstra (1 buah)\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/amenity_1762854489_6848.jpg\",\"qty\":1}]','Pending','2026-03-16 08:00:28'),(5,'101','Taji','[{\"id\":15,\"name\":\"Air Mineral\",\"description\":\"Air mineral botol (2 botol)\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/amenity_1762854450_2040.jpg\",\"qty\":1}]','Pending','2026-03-16 08:00:29'),(6,'888','Guest','[{\"id\":12,\"name\":\"Bantal Tambahan\",\"description\":\"Bantal tidur ekstra (1 buah)\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854489_6848.jpg\",\"qty\":1},{\"id\":13,\"name\":\"Perlengkapan Mandi\",\"description\":\"Sabun, Shampoo, Sikat Gigi\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854476_7748.jpg\",\"qty\":1},{\"id\":15,\"name\":\"Air Mineral\",\"description\":\"Air mineral botol (2 botol)\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854450_2040.jpg\",\"qty\":1},{\"id\":16,\"name\":\"Teko Kopi\",\"description\":\"Kopi, teh, susu\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762855317_8455.jpg\",\"qty\":1}]','Pending','2026-04-24 16:45:23'),(7,'888','Guest','[{\"id\":12,\"name\":\"Bantal Tambahan\",\"description\":\"Bantal tidur ekstra (1 buah)\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854489_6848.jpg\",\"qty\":4},{\"id\":15,\"name\":\"Air Mineral\",\"description\":\"Air mineral botol (2 botol)\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854450_2040.jpg\",\"qty\":1},{\"id\":16,\"name\":\"Teko Kopi\",\"description\":\"Kopi, teh, susu\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762855317_8455.jpg\",\"qty\":1}]','Pending','2026-04-24 16:46:27'),(8,'999','Guest','[{\"id\":11,\"name\":\"Handuk Tambahan\",\"description\":\"Handuk mandi ekstra (1 buah)\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854506_5026.jpg\",\"qty\":1},{\"id\":12,\"name\":\"Bantal Tambahan\",\"description\":\"Bantal tidur ekstra (1 buah)\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854489_6848.jpg\",\"qty\":1},{\"id\":13,\"name\":\"Perlengkapan Mandi\",\"description\":\"Sabun, Shampoo, Sikat Gigi\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854476_7748.jpg\",\"qty\":1},{\"id\":15,\"name\":\"Air Mineral\",\"description\":\"Air mineral botol (2 botol)\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854450_2040.jpg\",\"qty\":1}]','Pending','2026-04-25 05:49:23'),(9,'999','Guest','[{\"id\":13,\"name\":\"Perlengkapan Mandi\",\"description\":\"Sabun, Shampoo, Sikat Gigi\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854476_7748.jpg\",\"qty\":1},{\"id\":15,\"name\":\"Air Mineral\",\"description\":\"Air mineral botol (2 botol)\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/amenity_1762854450_2040.jpg\",\"qty\":1}]','Pending','2026-04-25 05:57:12'),(10,'202','Guest','[{\"id\":20,\"name\":\"Handuk\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265431.jpg\",\"qty\":1},{\"id\":21,\"name\":\"Sajadah\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265458.jpg\",\"qty\":1}]','Pending','2026-04-27 08:17:53'),(11,'999','Guest','[{\"id\":19,\"name\":\"Perlengkapan Mandi\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265421.jpg\",\"qty\":1},{\"id\":20,\"name\":\"Handuk\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265431.jpg\",\"qty\":2}]','Pending','2026-05-04 03:04:02'),(12,'999','Guest','[{\"id\":20,\"name\":\"Towel\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265431.jpg\",\"qty\":2}]','Pending','2026-05-04 03:06:49'),(13,'999','Guest','[{\"id\":20,\"name\":\"Towel\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265431.jpg\",\"qty\":2},{\"id\":21,\"name\":\"Prayer Mat\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265458.jpg\",\"qty\":1}]','Pending','2026-05-06 04:01:42'),(14,'Bos','Guest','[{\"id\":19,\"name\":\"Perlengkapan Mandi\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265421.jpg\",\"qty\":1},{\"id\":20,\"name\":\"Handuk\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265431.jpg\",\"qty\":3}]','Pending','2026-05-09 12:40:26'),(15,'Bos','Guest','[{\"id\":19,\"name\":\"Perlengkapan Mandi\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265421.jpg\",\"qty\":1},{\"id\":20,\"name\":\"Handuk\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265431.jpg\",\"qty\":3}]','Pending','2026-05-09 12:40:26'),(16,'999','Guest','[{\"id\":18,\"name\":\"Mineral Water\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265400.jpg\",\"qty\":1},{\"id\":20,\"name\":\"Towel\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265431.jpg\",\"qty\":1},{\"id\":21,\"name\":\"Prayer Mat\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265458.jpg\",\"qty\":1}]','Pending','2026-05-10 00:28:14'),(17,'MMA','Guest','[{\"id\":20,\"name\":\"Towel\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265431.jpg\",\"qty\":1}]','Pending','2026-05-12 05:18:28'),(18,'Bos','Guest','[{\"id\":21,\"name\":\"Sajadah\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265458.jpg\",\"qty\":4}]','Pending','2026-05-12 06:41:30'),(19,'999','Guest','[{\"id\":18,\"name\":\"Air Mineral\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265400.jpg\",\"qty\":1},{\"id\":19,\"name\":\"Perlengkapan Mandi\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265421.jpg\",\"qty\":1},{\"id\":20,\"name\":\"Handuk\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265431.jpg\",\"qty\":1}]','Pending','2026-06-08 04:34:28'),(20,'120','Guest','[{\"id\":19,\"name\":\"Toileteries\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265421.jpg\",\"qty\":1}]','Pending','2026-06-10 08:22:25'),(21,'211','Guest','[{\"id\":21,\"name\":\"Sajadah\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265458.jpg\",\"qty\":1}]','Pending','2026-06-10 14:29:24'),(22,'999','Guest','[{\"id\":20,\"name\":\"Handuk\",\"description\":\"\",\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/amenities/am_1777265431.jpg\",\"qty\":1}]','Pending','2026-06-10 17:26:57');
/*!40000 ALTER TABLE `amenity_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `package` varchar(100) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
INSERT INTO `app_settings` VALUES (1,'com.google.android.youtube.tv',1,'2025-11-05 08:32:40'),(2,'com.netflix.ninja',1,'2025-11-05 08:32:40'),(3,'in.startv.hotstar.dplus.tv',1,'2025-11-05 08:32:40'),(4,'com.vidio.android.tv',1,'2025-11-05 08:32:40'),(5,'com.spotify.tv.android',1,'2025-11-05 08:32:40');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `channels`
--

DROP TABLE IF EXISTS `channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `channels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lcn` int NOT NULL DEFAULT '0' COMMENT 'Logical Channel Number (urutan)',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Umum',
  `stream_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `logo_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` enum('enabled','disabled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'enabled',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lcn` (`lcn`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `channels`
--

LOCK TABLES `channels` WRITE;
/*!40000 ALTER TABLE `channels` DISABLE KEYS */;
INSERT INTO `channels` VALUES (3,3,'TRANSTV','Channel TV','https://video.detik.com/transtv/smil:transtv.smil/chunklist_w2114898498_b744100_sleng.m3u8','https://www.transtv.co.id/themes/v25.7/src/assets/logo/transtv-white.png','enabled','2026-03-05 05:39:19'),(4,4,'TRANS 7','Channel TV','https://video.detik.com/trans7/smil:trans7.smil/chunklist_w964486842_b744100_sleng.m3u8','http://202.8.28.198/takeoff_demo/uploads/iptv/ch_6a11274babb0a.png','enabled','2026-03-05 05:39:19'),(86,86,'TVRI','Channel TV','https://ogietv.biz.id:443/Livetv/234/1335.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvPIdO7JUOvSIiiql7nxnaF0l3Hv43CO1tCc1DxK9RnhxMR7jmm8PKxZ-r_RFSFRSWeqJo4kWnz44ELWtiDnHS78.png','enabled','2026-03-05 05:39:19'),(91,91,'TV ONE','Channel TV','https://ogietv.biz.id:443/Livetv/234/1345.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvPIdO7JUOvSIiiql7nxnaF0l3Hv43CO1tCc1DxK9Rnhxidx12F2y1zESgddR2oeQZ_4TGU7h2RO_rZIA_wSjtsFgkg9sQiYN5eRPP3zaZ0DRTAHMdV5WMHU12ILb9g684w.png','enabled','2026-03-05 05:39:19'),(94,94,'METROTV','Channel TV','https://ogietv.biz.id:443/Livetv/234/1328.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJAM8J_hkScWYN9JYVAL03KFp-K022xty5ORam2X0ZYiIXR0CubKJIFD6SsSfzmo364cSpX2lLrsMA6KVDYvxnuZWAQl5Pvo2XZFzXcw3iF82cE34Ct2YBEMiPm8jTxqlQ.png','enabled','2026-03-05 05:39:19'),(99,99,'CNN Indonesia','Channel TV','https://ogietv.biz.id:443/Livetv/234/1496.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvF8cUK5Jxtwj1NJ_7LBWZBmpUCBVTq6i6fYYwbyDLckgfoawZa4nKpuiQtrWH_Ds2SkyOrC-n4-S4xkzrNyIy3lbwk76uDdgeG9xhKDqGC1uU5ygyE8tfJbLll95A7-yQA.png','enabled','2026-03-05 05:39:19'),(111,111,'HBO Family','Channel TV','https://ogietv.biz.id:443/Livetv/234/1370.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGwbSS-HAO-xicYK_sU6BsRO5DmYuv3Rbu7fsp52m3ElJMRXEVFUBruJa7hOabcaLVA0u_0O5rpMstXQkE70P7HpQRApUJoBPKcs4y1VUaO4L6_-vPyJLPBJoJ7N9RA2oA.png','enabled','2026-03-05 05:39:19'),(123,123,'Thrill','Channel TV','https://ogietv.biz.id:443/Livetv/234/1386.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvOQxg-Y5LD1E7ybrkrYtdLg4gkLVRAKUTXm4bvr_VNxK1yOWvy58soSkSYFQl_jI2IA3iBYgVehSMtA8JoKV5OBCQ79GbzsH0WVPSD15TBZk6LjolGsp6nlSXuH-tH3YQg.png','enabled','2026-03-05 05:39:19'),(159,159,'BBC News','Channel TV','https://ogietv.biz.id:443/Livetv/234/708.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvAPf96nLVrEt4TOZIjZXa4uw8egpVwi4-J7u2PAD-ROyLyGA05tIqvsOljwCa-RntW2bNfImgyFXK_TMYQWRFlDKJ5Xl7dAGrdWHE5DKkeaZTaYZy3HxaTQLmxB2aSeiIA.png','enabled','2026-03-05 05:39:19'),(181,181,'Channel News Asia','Channel TV','https://ogietv.biz.id:443/Livetv/234/1491.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvG5N4h063eZc_EQFEzXSo5JkKtk63QQd29VfzXgexRBFbmizf4UtxpG1h3lwOLOQwnKxApsXfkMCg2cGoffic8s.png','enabled','2026-03-05 05:39:19'),(184,184,'CNN International','Channel TV','https://ogietv.biz.id:443/Livetv/234/1498.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvG5N4h063eZc_EQFEzXSo5JkKtk63QQd29VfzXgexRBF9G1DbpLt1UjwpjIfAfsDkM_IldO4xUkYvL2ehwV9xQH5sADSawlrZWSnFvxsVgwQkD5rmAjGbNepOUY84kw8QQ.png','enabled','2026-03-05 05:39:19');
/*!40000 ALTER TABLE `channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_units`
--

DROP TABLE IF EXISTS `device_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_units` (
  `id` int NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `launcher_script` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `restore_script` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `clear_script` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_units`
--

LOCK TABLES `device_units` WRITE;
/*!40000 ALTER TABLE `device_units` DISABLE KEYS */;
INSERT INTO `device_units` VALUES (1,'TCL','shell cmd package set-home-activity com.takeoff.launcher/.MainActivity\r\nshell am start -a android.intent.action.MAIN -c android.intent.category.HOME\r\nshell pm disable-user --user 0 com.google.android.tv.launcherx\r\nshell pm disable-user --user 0 com.google.android.apps.tv.launcherx\r\nshell pm disable-user --user 0 com.google.android.tvlauncher\r\nshell pm disable-user --user 0 com.google.android.leanbacklauncher\r\nshell pm uninstall -k --user 0 com.google.android.tv.launcherx\r\nshell pm uninstall -k --user 0 com.google.android.apps.tv.launcherx\r\nshell pm disable-user --user 0 com.google.android.tv.setupwraith\r\nshell pm disable-user --user 0 com.google.android.tungsten.setupwraith\r\nshell cmd package set-home-activity com.takeoff.launcher/.MainActivity','shell pm enable com.google.android.apps.tv.launcherx\r\nshell pm enable com.google.android.tvlauncher\r\nshell pm enable com.google.android.tv.launcherx\r\nshell pm enable com.google.android.leanbacklauncher\r\nshell cmd package install-existing --user 0 com.google.android.tv.launcherx\r\nshell cmd package install-existing --user 0 com.google.android.apps.tv.launcherx\r\nshell pm enable com.google.android.tv.setupwraith\r\nshell pm enable com.google.android.tungsten.setupwraith\r\nshell cmd package set-home-activity com.google.android.apps.tv.launcherx/.MainActivity\r\nshell am start -a android.intent.action.MAIN -c android.intent.category.HOME','com.google.android.youtube.tv\r\ncom.netflix.ninja\r\ncom.spotify.tv.android\r\ncom.vidio.android.tv','2026-03-09 10:00:36'),(3,'STB ZTE ZXV10 B66F','shell am start -n com.takeoff.launcher/.MainActivity','shell am start -n com.google.android.tvlauncher/.MainActivity','com.google.android.youtube.tv\r\ncom.netflix.ninja\r\ncom.spotify.tv.android\r\ncom.vidio.android.tv','2026-03-09 20:36:16'),(4,'STB ZTE ZXV10 B860H','shell pm disable-user --user 0 com.google.android.tvlauncher\r\nshell cmd package set-home-activity com.takeoff.launcher/.MainActivity\r\nshell am start -n com.takeoff.launcher/.MainActivity','shell pm enable com.google.android.tvlauncher\r\nshell cmd package set-home-activity com.google.android.tvlauncher/.MainActivity\r\nshell am start -n com.google.android.tvlauncher/.MainActivity','com.google.android.youtube.tv\r\ncom.netflix.ninja\r\ncom.spotify.tv.android\r\ncom.vidio.android.tv','2026-03-10 10:53:01');
/*!40000 ALTER TABLE `device_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dining_menu`
--

DROP TABLE IF EXISTS `dining_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dining_menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kat_dining` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `price` int NOT NULL DEFAULT '0',
  `image_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dining_menu`
--

LOCK TABLES `dining_menu` WRITE;
/*!40000 ALTER TABLE `dining_menu` DISABLE KEYS */;
INSERT INTO `dining_menu` VALUES (1,3,'Nasi Goreng Spesial','Special Fried Rice',NULL,25000,'uploads/dining/menu_1772371852_1849.jpg','active'),(2,3,'Mie Goreng Seafood','Seafood Fried Noodles',NULL,28000,'uploads/dining/menu_1772371845_2555.jpg','active'),(3,3,'Sate Ayam Madura','Madura Chicken Satay',NULL,32000,'uploads/dining/menu_1772371838_9425.jpg','active'),(4,1,'Soto Ayam Lamongan','Lamongan Chicken Soup',NULL,27000,'uploads/dining/menu_1777262437_9547.jpg','active'),(5,2,'Ayam Penyet Sambal Ijo','Smashed Chicken (Green Chili)',NULL,30000,'uploads/dining/menu_1772371824_8410.jpg','active'),(6,2,'Capcay Kuah','Capcay Soup',NULL,26000,'uploads/dining/menu_1772371817_8370.jpg','active'),(7,2,'Teh Manis Dingin','Iced Sweet Tea',NULL,8000,'uploads/dining/menu_1772371810_5717.jpg','active'),(9,1,'Jus Alpukat','Avocado Juice',NULL,15000,'uploads/dining/menu_1777262326_5769.jpg','active'),(10,1,'Pisang Goreng Keju','Fried Banana w/ Cheese',NULL,18000,'uploads/dining/menu_1777262320_4651.jpg','active'),(11,1,'Nasi Goreng Terasi','Shrimp Paste Fried Rice',NULL,35000,'uploads/dining/menu_1777262308_7561.jpg','active');
/*!40000 ALTER TABLE `dining_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `general_info`
--

DROP TABLE IF EXISTS `general_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `general_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kat_general_info` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `icon_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_description` tinyint(1) DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `general_info`
--

LOCK TABLES `general_info` WRITE;
/*!40000 ALTER TABLE `general_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `general_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `global_settings`
--

DROP TABLE IF EXISTS `global_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `global_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=298 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `global_settings`
--

LOCK TABLES `global_settings` WRITE;
/*!40000 ALTER TABLE `global_settings` DISABLE KEYS */;
INSERT INTO `global_settings` VALUES (1,'launcher_enabled','1'),(4,'default_volume','10'),(10,'system_version','v251227-034321'),(13,'splash_enabled','1'),(16,'launcher_bg',''),(17,'launcher_home_bg','uploads/homebg/launcher_home_bg.png?v=1777102643'),(26,'loading_logo_url','uploads/logo/loading_logo.png?v=1762812096'),(29,'custom_greeting_title','Selamat Datang'),(30,'custom_welcome_greeting','Selamat datang di hotel kami.\r\nKami sangat senang menyambut Anda sebagai tamu istimewa.\r\nJika Anda membutuhkan bantuan kapan saja, tim kami selalu siap melayani dengan sepenuh hati.\r\n\r\nSelamat beristirahat & enjoy your stay!\r\n\r\n— Branch Manager'),(31,'custom_greeting_image','uploads/greeting/greeting_img.png?v=1777256402'),(140,'custom_greeting_title_en','Welcome'),(141,'custom_welcome_greeting_en','Welcome to our hotel...\r\nWe are delighted to welcome you as our special guest.\r\nIf you need any assistance at any time, our team is always ready to serve you wholeheartedly.\r\n\r\nHave a good rest and enjoy your stay!\r\n\r\n— Branch Manager'),(170,'greeting_title_id_enabled','1'),(171,'greeting_title_en_enabled','1'),(172,'greeting_content_id_enabled','1'),(173,'greeting_content_en_enabled','1'),(174,'greeting_title_color','#ffffff'),(175,'greeting_content_color','#ffffff'),(176,'greeting_btn_color','#f7872b'),(177,'greeting_btn_text_color','#ffffff');
/*!40000 ALTER TABLE `global_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_checkin`
--

DROP TABLE IF EXISTS `guest_checkin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_checkin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_number` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `guest_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `checkin_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `checkout_time` datetime DEFAULT NULL,
  `status` enum('checked_in','checked_out') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'checked_in',
  PRIMARY KEY (`id`),
  KEY `room_number` (`room_number`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_checkin`
--

LOCK TABLES `guest_checkin` WRITE;
/*!40000 ALTER TABLE `guest_checkin` DISABLE KEYS */;
INSERT INTO `guest_checkin` VALUES (1,'101','JOKO P','2026-03-10 03:00:45','2026-03-10 04:05:14','checked_out'),(2,'101','JOKO P','2026-03-10 04:55:57','2026-03-10 15:34:10','checked_out'),(3,'101','Rizal','2026-03-10 15:50:10','2026-03-10 16:05:37','checked_out'),(4,'101','Rizal','2026-03-10 16:05:59','2026-03-10 16:38:04','checked_out'),(5,'101','Rizal','2026-03-10 16:38:17','2026-03-10 22:33:26','checked_out'),(6,'102','Rizal','2026-03-11 01:09:27','2026-03-13 01:22:16','checked_out'),(7,'102','Rizal','2026-03-13 01:39:05','2026-03-13 01:39:12','checked_out'),(8,'101','Taji','2026-03-16 14:54:18',NULL,'checked_in');
/*!40000 ALTER TABLE `guest_checkin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotel_facilities`
--

DROP TABLE IF EXISTS `hotel_facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_facilities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kat_facilities` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `description` text,
  `description_en` text,
  `is_active` tinyint(1) DEFAULT '1',
  `show_description` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotel_facilities`
--

LOCK TABLES `hotel_facilities` WRITE;
/*!40000 ALTER TABLE `hotel_facilities` DISABLE KEYS */;
INSERT INTO `hotel_facilities` VALUES (17,2,'Restoran','Restaurant','uploads/facilities/fac_1777101309.jpg','','',1,1),(18,3,'GYM','GYM','uploads/facilities/fac_1777101322.jpg','','',1,1),(19,4,'Rooftop Bar','Rooftop Bar','uploads/facilities/fac_1777101344.jpg','','',1,1),(22,1,'Kolam Renang','Swimming Pool','uploads/facilities/fac_1777102800.jpg','','',1,1);
/*!40000 ALTER TABLE `hotel_facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotel_info`
--

DROP TABLE IF EXISTS `hotel_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kat_info` int DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `title_en` varchar(150) DEFAULT NULL,
  `description` text,
  `description_en` text,
  `icon_path` varchar(255) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `show_description` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotel_info`
--

LOCK TABLES `hotel_info` WRITE;
/*!40000 ALTER TABLE `hotel_info` DISABLE KEYS */;
INSERT INTO `hotel_info` VALUES (11,1,'Tentang Kami','About Us','','','uploads/info/info_1777102519.jpg',0,'2026-04-25 07:35:19',0),(12,2,'Sejarah Kami','Our History','','','uploads/info/info_1777102545.jpg',0,'2026-04-25 07:35:46',0);
/*!40000 ALTER TABLE `hotel_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotel_orders`
--

DROP TABLE IF EXISTS `hotel_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `items` text,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Delivered','Cancelled') DEFAULT 'Pending',
  `ordered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotel_orders`
--

LOCK TABLES `hotel_orders` WRITE;
/*!40000 ALTER TABLE `hotel_orders` DISABLE KEYS */;
INSERT INTO `hotel_orders` VALUES (1,'888','Guest','[{\"id\":4,\"id_kat_dining\":1,\"name\":\"Soto Ayam Lamongan\",\"price\":27000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371831_4362.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":9,\"id_kat_dining\":1,\"name\":\"Jus Alpukat\",\"price\":15000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371795_3214.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":10,\"id_kat_dining\":1,\"name\":\"Pisang Goreng Keju\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371788_2092.jpg\",\"status\":\"active\",\"qty\":1}]',60000.00,'Pending','2026-04-24 16:46:15'),(2,'999','Guest','[{\"id\":4,\"id_kat_dining\":1,\"name\":\"Soto Ayam Lamongan\",\"price\":27000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371831_4362.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":8,\"id_kat_dining\":1,\"name\":\"Kopi Hitam Tubruk\",\"price\":10000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371803_2895.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":9,\"id_kat_dining\":1,\"name\":\"Jus Alpukat\",\"price\":15000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371795_3214.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":10,\"id_kat_dining\":1,\"name\":\"Pisang Goreng Keju\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371788_2092.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":11,\"id_kat_dining\":1,\"name\":\"Nasi Goreng Terasi\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371780_9870.jpg\",\"status\":\"active\",\"qty\":1}]',105000.00,'Pending','2026-04-25 05:47:50'),(3,'999','Guest','[{\"id\":9,\"id_kat_dining\":1,\"name\":\"Jus Alpukat\",\"price\":15000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371795_3214.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":10,\"id_kat_dining\":1,\"name\":\"Pisang Goreng Keju\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371788_2092.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":11,\"id_kat_dining\":1,\"name\":\"Nasi Goreng Terasi\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371780_9870.jpg\",\"status\":\"active\",\"qty\":1}]',68000.00,'Pending','2026-04-25 05:53:14'),(4,'999','Guest','[{\"id\":4,\"id_kat_dining\":1,\"name\":\"Soto Ayam Lamongan\",\"price\":27000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371831_4362.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":10,\"id_kat_dining\":1,\"name\":\"Pisang Goreng Keju\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371788_2092.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":11,\"id_kat_dining\":1,\"name\":\"Nasi Goreng Terasi\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371780_9870.jpg\",\"status\":\"active\",\"qty\":1}]',80000.00,'Pending','2026-04-25 05:55:53'),(5,'202','Guest','[{\"id\":11,\"id_kat_dining\":1,\"name\":\"Shrimp Paste Fried Rice\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371780_9870.jpg\",\"status\":\"active\",\"qty\":1}]',35000.00,'Pending','2026-04-27 01:58:42'),(6,'Bos','Guest','[{\"id\":10,\"id_kat_dining\":1,\"name\":\"Pisang Goreng Keju\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262320_4651.jpg\",\"status\":\"active\",\"qty\":3}]',54000.00,'Pending','2026-05-09 12:39:07'),(7,'999','Guest','[{\"id\":9,\"id_kat_dining\":1,\"name\":\"Avocado Juice\",\"price\":15000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262326_5769.jpg\",\"status\":\"active\",\"qty\":3}]',45000.00,'Pending','2026-05-10 00:25:31'),(8,'999','Guest','[{\"id\":9,\"id_kat_dining\":1,\"name\":\"Avocado Juice\",\"price\":15000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262326_5769.jpg\",\"status\":\"active\",\"qty\":3}]',45000.00,'Pending','2026-05-10 00:25:33'),(9,'999','Guest','[{\"id\":9,\"id_kat_dining\":1,\"name\":\"Avocado Juice\",\"price\":15000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262326_5769.jpg\",\"status\":\"active\",\"qty\":2}]',30000.00,'Pending','2026-05-10 01:16:50'),(10,'Bos','Guest','[{\"id\":4,\"id_kat_dining\":1,\"name\":\"Soto Ayam Lamongan\",\"price\":27000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262437_9547.jpg\",\"status\":\"active\",\"qty\":2}]',54000.00,'Pending','2026-05-13 05:41:31'),(11,'601','Guest','[{\"id\":2,\"id_kat_dining\":3,\"name\":\"Seafood Fried Noodles\",\"price\":28000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371845_2555.jpg\",\"status\":\"active\",\"qty\":2},{\"id\":11,\"id_kat_dining\":1,\"name\":\"Shrimp Paste Fried Rice\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262308_7561.jpg\",\"status\":\"active\",\"qty\":2}]',126000.00,'Pending','2026-05-23 04:52:18'),(12,'MMA03','Guest','[{\"id\":9,\"id_kat_dining\":1,\"name\":\"Jus Alpukat\",\"price\":15000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262326_5769.jpg\",\"status\":\"active\",\"qty\":1},{\"id\":10,\"id_kat_dining\":1,\"name\":\"Pisang Goreng Keju\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262320_4651.jpg\",\"status\":\"active\",\"qty\":1}]',33000.00,'Pending','2026-06-02 09:56:25'),(13,'Tv','Guest','[{\"id\":3,\"id_kat_dining\":3,\"name\":\"Madura Chicken Satay\",\"price\":32000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1772371838_9425.jpg\",\"status\":\"active\",\"qty\":1}]',32000.00,'Pending','2026-06-06 07:08:25'),(14,'999','Guest','[{\"id\":11,\"id_kat_dining\":1,\"name\":\"Nasi Goreng Terasi\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262308_7561.jpg\",\"status\":\"active\",\"qty\":1}]',35000.00,'Pending','2026-06-08 04:30:02'),(15,'999','Guest','[{\"id\":10,\"id_kat_dining\":1,\"name\":\"Pisang Goreng Keju\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262320_4651.jpg\",\"status\":\"active\",\"qty\":1}]',18000.00,'Pending','2026-06-08 04:31:12'),(16,'999','Guest','[{\"id\":11,\"id_kat_dining\":1,\"name\":\"Shrimp Paste Fried Rice\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262308_7561.jpg\",\"status\":\"active\",\"qty\":1}]',35000.00,'Pending','2026-06-08 05:10:27'),(17,'999','Guest','[{\"id\":11,\"id_kat_dining\":1,\"name\":\"Shrimp Paste Fried Rice\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262308_7561.jpg\",\"status\":\"active\",\"qty\":1}]',35000.00,'Pending','2026-06-08 05:31:51'),(18,'999','Guest','[{\"id\":10,\"id_kat_dining\":1,\"name\":\"Fried Banana w/ Cheese\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262320_4651.jpg\",\"status\":\"active\",\"qty\":1}]',18000.00,'Pending','2026-06-08 05:32:24'),(19,'999','Guest','[{\"id\":10,\"id_kat_dining\":1,\"name\":\"Fried Banana w/ Cheese\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262320_4651.jpg\",\"status\":\"active\",\"qty\":1}]',18000.00,'Pending','2026-06-08 05:32:58'),(20,'999','Guest','[{\"id\":10,\"id_kat_dining\":1,\"name\":\"Fried Banana w/ Cheese\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262320_4651.jpg\",\"status\":\"active\",\"qty\":1}]',18000.00,'Pending','2026-06-08 05:33:57'),(21,'999','Guest','[{\"id\":11,\"id_kat_dining\":1,\"name\":\"Shrimp Paste Fried Rice\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262308_7561.jpg\",\"status\":\"active\",\"qty\":1}]',35000.00,'Pending','2026-06-08 05:39:56'),(22,'MMA03','Guest','[{\"id\":11,\"id_kat_dining\":1,\"name\":\"Nasi Goreng Terasi\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262308_7561.jpg\",\"status\":\"active\",\"qty\":1}]',35000.00,'Pending','2026-06-08 06:24:37'),(23,'120','Guest','[{\"id\":11,\"id_kat_dining\":1,\"name\":\"Shrimp Paste Fried Rice\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262308_7561.jpg\",\"status\":\"active\",\"qty\":1}]',35000.00,'Pending','2026-06-10 07:28:29'),(24,'120','Guest','[{\"id\":10,\"id_kat_dining\":1,\"name\":\"Fried Banana w/ Cheese\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262320_4651.jpg\",\"status\":\"active\",\"qty\":1}]',18000.00,'Pending','2026-06-10 08:21:19'),(25,'211','Guest','[{\"id\":11,\"id_kat_dining\":1,\"name\":\"Nasi Goreng Terasi\",\"price\":35000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262308_7561.jpg\",\"status\":\"active\",\"qty\":1}]',35000.00,'Pending','2026-06-10 14:29:10'),(26,'999','Guest','[{\"id\":4,\"id_kat_dining\":1,\"name\":\"Soto Ayam Lamongan\",\"price\":27000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262437_9547.jpg\",\"status\":\"active\",\"qty\":1}]',27000.00,'Pending','2026-06-10 17:27:20'),(27,'999','Guest','[{\"id\":10,\"id_kat_dining\":1,\"name\":\"Pisang Goreng Keju\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262320_4651.jpg\",\"status\":\"active\",\"qty\":1}]',18000.00,'Pending','2026-06-11 04:28:23'),(28,'999','Guest','[{\"id\":10,\"id_kat_dining\":1,\"name\":\"Pisang Goreng Keju\",\"price\":18000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262320_4651.jpg\",\"status\":\"active\",\"qty\":1}]',18000.00,'Pending','2026-06-11 06:12:39'),(29,'999','Guest','[{\"id\":9,\"id_kat_dining\":1,\"name\":\"Jus Alpukat\",\"price\":15000,\"icon_path\":\"http://202.8.28.198/takeoff_demo/uploads/dining/menu_1777262326_5769.jpg\",\"status\":\"active\",\"qty\":1}]',15000.00,'Pending','2026-06-11 09:58:46');
/*!40000 ALTER TABLE `hotel_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kat_dining`
--

DROP TABLE IF EXISTS `kat_dining`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kat_dining` (
  `id_kat_dining` int NOT NULL AUTO_INCREMENT,
  `nm_kat_dining` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_kat_dining` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_kat_dining`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kat_dining`
--

LOCK TABLES `kat_dining` WRITE;
/*!40000 ALTER TABLE `kat_dining` DISABLE KEYS */;
INSERT INTO `kat_dining` VALUES (1,'Indonesian Food','uploads/kat_dining/kat_dining_1773566226_9678.jpg'),(2,'Chinese Food','uploads/kat_dining/kat_dining_1773566254_5472.jpg'),(3,'Arabic Food','uploads/kat_dining/kat_dining_1773566307_8076.jpg');
/*!40000 ALTER TABLE `kat_dining` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kat_facilities`
--

DROP TABLE IF EXISTS `kat_facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kat_facilities` (
  `id_kat_facilities` int NOT NULL AUTO_INCREMENT,
  `nm_kat_facilities` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_kat_facilities` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_kat_facilities`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kat_facilities`
--

LOCK TABLES `kat_facilities` WRITE;
/*!40000 ALTER TABLE `kat_facilities` DISABLE KEYS */;
INSERT INTO `kat_facilities` VALUES (1,'Swimming Pool','uploads/kat_facilities/kat_1777104103_8524.png'),(2,'Restaurant','uploads/kat_facilities/kat_1777101087_2268.png'),(3,'GYM','uploads/kat_facilities/kat_1777101114_1817.png'),(4,'Rooftop Bar','uploads/kat_facilities/kat_1777101270_5091.png');
/*!40000 ALTER TABLE `kat_facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kat_general_info`
--

DROP TABLE IF EXISTS `kat_general_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kat_general_info` (
  `id_kat_general_info` int NOT NULL AUTO_INCREMENT,
  `nm_kat_general_info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_kat_general_info` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_kat_general_info`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kat_general_info`
--

LOCK TABLES `kat_general_info` WRITE;
/*!40000 ALTER TABLE `kat_general_info` DISABLE KEYS */;
INSERT INTO `kat_general_info` VALUES (1,'Tentang kami','uploads/kat_general_info/kat_gen_info_1773355019_6260.jpg'),(2,'Fasilitas','uploads/kat_general_info/kat_gen_info_1773355069_1144.jpg');
/*!40000 ALTER TABLE `kat_general_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kat_info`
--

DROP TABLE IF EXISTS `kat_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kat_info` (
  `id_kat_info` int NOT NULL AUTO_INCREMENT,
  `nm_kat_info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_kat_info` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_kat_info`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kat_info`
--

LOCK TABLES `kat_info` WRITE;
/*!40000 ALTER TABLE `kat_info` DISABLE KEYS */;
INSERT INTO `kat_info` VALUES (1,'About Us','uploads/kat_info/kat_info_1777101765_7221.png'),(2,'Hotel History','uploads/kat_info/kat_info_1777101848_1379.png');
/*!40000 ALTER TABLE `kat_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kat_promotion`
--

DROP TABLE IF EXISTS `kat_promotion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kat_promotion` (
  `id_kat_promotion` int NOT NULL AUTO_INCREMENT,
  `nm_kat_promotion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_kat_promotion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_kat_promotion`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kat_promotion`
--

LOCK TABLES `kat_promotion` WRITE;
/*!40000 ALTER TABLE `kat_promotion` DISABLE KEYS */;
INSERT INTO `kat_promotion` VALUES (7,'Promotion','uploads/kat_promotion/kat_promo_1781197343_6654.png'),(8,'Price List','uploads/kat_promotion/kat_promo_1781197478_5194.jpeg'),(9,'Chanel TV Pricelist','uploads/kat_promotion/kat_promo_1781197554_1837.png'),(10,'Takeoff Streaming Channel','uploads/kat_promotion/kat_promo_1781197597_1429.jpeg');
/*!40000 ALTER TABLE `kat_promotion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `managed_devices`
--

DROP TABLE IF EXISTS `managed_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `managed_devices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `device_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `device_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `room_number` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `device_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unit_id` int DEFAULT NULL,
  `pending_clear` tinyint(1) NOT NULL DEFAULT '0',
  `pending_start_launcher` tinyint(1) NOT NULL DEFAULT '0',
  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_seen` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `managed_devices`
--

LOCK TABLES `managed_devices` WRITE;
/*!40000 ALTER TABLE `managed_devices` DISABLE KEYS */;
INSERT INTO `managed_devices` VALUES (15,'TV-VH72RS','Joko TV','101','192.168.1.62',4,0,1,'2026-03-09 19:50:07',1,'2026-03-20 21:58:19'),(18,'TV-SZL818','ANDROID ABBA','777','1.1.1.1',1,0,0,'2026-04-24 16:04:40',1,'2026-04-24 16:04:40'),(19,'TV-GSV44S','TEST','888','1.1.1.1',1,0,0,'2026-04-24 16:31:11',1,'2026-04-24 16:31:11'),(20,'TV-HIEKAP','MECOOL_DEMO','999','1.1.1.1',4,0,0,'2026-04-25 05:44:11',1,'2026-04-25 05:44:11'),(21,'TV-NF2451','MANTAP','202','1.1.1.1',1,0,0,'2026-04-27 01:51:49',1,'2026-04-27 01:51:49'),(22,'TV-QNPGYS','MAN','299','1.1.1.1',1,0,0,'2026-04-27 02:06:08',1,'2026-04-27 02:06:08'),(23,'TV-M9P1N8','kimel','office','192.1.1.1',4,0,0,'2026-05-08 15:41:38',1,'2026-05-08 15:41:38'),(24,'TV-HZJFMZ','barelang','1','192.168.0.231',3,0,0,'2026-05-09 05:31:04',1,'2026-05-09 05:31:04'),(25,'TV-QUEFJF','barelang','2','192.168.0.168',3,0,0,'2026-05-09 07:00:30',1,'2026-05-09 07:00:30'),(26,'TV-YP9XYD','barelang','3','192.168.0.245',3,0,0,'2026-05-09 08:06:58',1,'2026-05-09 08:06:58'),(27,'TV-Q4FXXA','Bos','Bos','172.17.96.28',3,0,0,'2026-05-09 11:43:13',1,'2026-05-09 11:43:13'),(28,'TV-MANFZB','Sales','MMA','192.168.0.39',4,0,0,'2026-05-12 04:36:16',1,'2026-05-12 04:36:16'),(29,'TV-4UTHVC','MMA','MMA03','192.168.220.121',4,0,0,'2026-05-13 05:51:45',1,'2026-05-13 05:51:45'),(30,'TV-D5SRAG','office','office','192.168.0.49',3,0,0,'2026-05-16 13:45:13',1,'2026-05-16 13:45:13'),(31,'TV-AP4VO6','KTM','601','192.168.20.18',4,0,0,'2026-05-23 03:52:54',1,'2026-05-23 03:52:54'),(32,'TV-9HXZ9W','kamuela','A6','192.168.13.214',4,0,0,'2026-05-26 16:13:41',1,'2026-05-26 16:13:41'),(33,'TV-KQLAUM','Tv','Tv','192.168.0.244',4,0,0,'2026-06-06 06:09:19',1,'2026-06-06 06:09:19'),(34,'TV-DGVAQE','IPTV8JUNI2026','999','192.168.0.173',3,0,0,'2026-06-08 04:28:21',1,'2026-06-08 04:28:21'),(35,'TV-Y0K0KG','BROWSER','999','1.1.1.1',1,0,0,'2026-06-09 02:36:24',1,'2026-06-09 02:36:24'),(36,'TV-KXRB77','Ios Abba','909','1.1.1.1',1,0,0,'2026-06-09 02:38:34',1,'2026-06-09 02:38:34'),(37,'TV-84469N','android abba','90909','1.1.1.1',1,0,0,'2026-06-09 02:41:06',1,'2026-06-09 02:41:06'),(38,'TV-5TLQ49','Android Abba','239','1.1.1.1',1,0,0,'2026-06-09 03:06:19',1,'2026-06-09 03:06:19'),(39,'TV-5TL4Q9','TV','678','1.1.1.1',1,0,0,'2026-06-09 03:07:20',1,'2026-06-09 03:07:20'),(40,'TV-5RU38Y','KTM','120','1.1.1.1',1,0,0,'2026-06-10 07:24:04',1,'2026-06-10 07:24:04'),(41,'TV-PBKDJD','TvKTMLast','120','1.1.1.1',1,0,0,'2026-06-10 08:58:11',1,'2026-06-10 08:58:11'),(42,'TV-OQ1ZKX','Ponti','120','1.1.1.1',1,0,0,'2026-06-10 14:25:22',1,'2026-06-10 14:25:22'),(43,'TV-FCSF5H','Tvv','120','1.1.1.1',1,0,0,'2026-06-10 14:26:26',1,'2026-06-10 14:26:26'),(44,'TV-H789FI','App','211','1.1.1.1',1,0,0,'2026-06-10 14:28:40',1,'2026-06-10 14:28:40'),(45,'TV-CNC80W','Sales','999','1.1.1.1',1,0,0,'2026-06-10 14:54:42',1,'2026-06-10 14:54:42'),(50,'TV-RWWACI','Marketing','999','1.1.1.1',1,0,0,'2026-06-10 14:57:32',1,'2026-06-10 14:57:32'),(51,'TV-E6D7N5','TV-E6D7N5','TV-E6D7N5','10.2.254.131',1,0,0,'2026-06-11 18:58:40',1,'2026-06-11 19:11:59');
/*!40000 ALTER TABLE `managed_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `rooms` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playlists`
--

DROP TABLE IF EXISTS `playlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `playlists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nama provider',
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'URL file M3U',
  `default_category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Playlist',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playlists`
--

LOCK TABLES `playlists` WRITE;
/*!40000 ALTER TABLE `playlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `playlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `popup_notifications`
--

DROP TABLE IF EXISTS `popup_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `popup_notifications` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `device_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `room_number` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','delivered','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delivered_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `sound_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_device_status_created` (`device_id`,`status`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `popup_notifications`
--

LOCK TABLES `popup_notifications` WRITE;
/*!40000 ALTER TABLE `popup_notifications` DISABLE KEYS */;
INSERT INTO `popup_notifications` VALUES (1,'TV-KQLAUM','Tv','Selamat','Yanto Yanto tobat lah','delivered','2026-06-06 07:21:02','2026-06-06 07:21:03','2026-06-07 07:21:02',NULL,NULL);
/*!40000 ALTER TABLE `popup_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotion`
--

DROP TABLE IF EXISTS `promotion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promotion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kat_promotion` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `icon_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_description` tinyint(1) DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotion`
--

LOCK TABLES `promotion` WRITE;
/*!40000 ALTER TABLE `promotion` DISABLE KEYS */;
INSERT INTO `promotion` VALUES (11,7,'Price List','Price List','','','uploads/promotion/promo_1781197363.jpg',0,1),(13,8,'Price List','Price List','','','uploads/promotion/promo_1781197504.jpg',0,1),(14,9,'Channel TV Pricelist','Channel TV Pricelist','','','uploads/promotion/promo_1781197571.jpg',0,1),(15,10,'Takeoff Streaming Channel','Takeoff Streaming Channel','','','uploads/promotion/promo_1781197616.jpg',0,1);
/*!40000 ALTER TABLE `promotion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_amenities`
--

DROP TABLE IF EXISTS `room_amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_amenities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `description` text,
  `description_en` text,
  `icon_path` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_amenities`
--

LOCK TABLES `room_amenities` WRITE;
/*!40000 ALTER TABLE `room_amenities` DISABLE KEYS */;
INSERT INTO `room_amenities` VALUES (18,'Air Mineral','Mineral Water','','','uploads/amenities/am_1777265400.jpg','general','2026-04-27 04:50:00'),(19,'Perlengkapan Mandi','Toileteries','','','uploads/amenities/am_1777265421.jpg','general','2026-04-27 04:50:21'),(20,'Handuk','Towel','','','uploads/amenities/am_1777265431.jpg','general','2026-04-27 04:50:31'),(21,'Sajadah','Prayer Mat','','','uploads/amenities/am_1777265458.jpg','general','2026-04-27 04:50:58');
/*!40000 ALTER TABLE `room_amenities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_apps`
--

DROP TABLE IF EXISTS `system_apps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_apps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `app_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `app_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `app_name_en` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `icon_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `android_package` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_key` (`app_key`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_apps`
--

LOCK TABLES `system_apps` WRITE;
/*!40000 ALTER TABLE `system_apps` DISABLE KEYS */;
INSERT INTO `system_apps` VALUES (1,'information','Information','Information','img/information.png',1,1,NULL),(3,'amenities','Amenities','Amenities','img/amenities.png',1,8,NULL),(4,'facilities','Facilities','Facilities','img/facilities.png',1,7,NULL),(6,'youtube','YouTube',NULL,'img/youtube.png',1,9,'com.google.android.youtube.tv'),(7,'netflix','Netflix',NULL,'uploads/icons/icon_1777264810.png',1,4,'com.netflix.ninja'),(8,'spotify','Spotify',NULL,'img/spotify.png',1,10,'com.spotify.tv.android'),(10,'vidio','Vidio',NULL,'img/vidio.png',1,11,'com.vidio.android.tv'),(23,'clear_data_guest','Clear Data Guest',NULL,'uploads/icons/icon_1777099547.png',1,6,'clear.data'),(25,'tv_channel','TV Channel',NULL,'uploads/icons/icon_1777100159.png',0,5,'com.mmaplay.iptv'),(29,'promotion','promotion','promotion','uploads/icons/icon_1777254800.png',1,3,'internal.promotion'),(30,'general_info','General Information',NULL,'uploads/icons/icon_1773563755.png',0,0,'internal.general_info'),(31,'channel_tv','Channel TV',NULL,'uploads/icons/icon_1779812646.png',1,5,'com.ctcorp.hospitality'),(32,'dining','Dining Room',NULL,'uploads/icons/icon_1778258378.png',1,2,'internal.dining_room');
/*!40000 ALTER TABLE `system_apps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_marquee`
--

DROP TABLE IF EXISTS `system_marquee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_marquee` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_marquee`
--

LOCK TABLES `system_marquee` WRITE;
/*!40000 ALTER TABLE `system_marquee` DISABLE KEYS */;
INSERT INTO `system_marquee` VALUES (1,'Warmest greetings from Best Hotel. Your comfort and satisfaction are our highest priority. Should you need any assistance, please dial Ext. 0 or reach us via WhatsApp at +62 811 666 777. We wish you a truly wonderful experience with us',1,'2026-05-26 13:40:43');
/*!40000 ALTER TABLE `system_marquee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES ('amenities_request_card_enabled','1','2026-03-16 15:00:04'),('dining_cart_enabled','1','2026-05-08 15:05:12'),('scheduled_clear_enabled','1','2026-02-28 02:34:20'),('scheduled_clear_time','02:15','2026-02-28 02:34:20'),('wa_fonnte_token','zJcA48nMSsNs6rMTWBth','2026-06-10 14:20:33'),('wa_gateway_enabled','1','2026-06-10 14:20:33'),('wa_recipient_amenities','628117774884,62811882383','2026-06-10 14:20:33'),('wa_recipient_dining','628117774884,62811882383','2026-06-10 14:20:33');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-12  5:11:22
