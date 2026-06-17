/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: takeoff_local
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-0+deb13u1 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `admin_permissions`
--

DROP TABLE IF EXISTS `admin_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `page_key` varchar(50) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_admin_page` (`admin_id`,`page_key`),
  CONSTRAINT `fk_admin_permissions_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_permissions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `admin_permissions` WRITE;
/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` VALUES
(17,1,'dashboard',1),
(18,1,'devices',1),
(19,1,'checkin',1),
(20,1,'send_notification',1),
(21,1,'facilities',1),
(22,1,'amenities',1),
(23,1,'information',1),
(24,1,'dining',1),
(25,1,'dining_orders',1),
(26,1,'amenity_requests',1),
(27,1,'app_control',1),
(28,1,'running_text',1),
(29,1,'update',1),
(30,1,'flashscreen',1),
(31,1,'server_config',1),
(32,1,'users',1),
(65,1,'iptv',1);
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('superadmin','admin') NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `idx_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES
(1,'rizal','rizal','$2y$10$EhDVx1DdZwoL3N.3dzQFfOmFBCVM.Txe1TUMtUMoaOZRKkVh8A98K','superadmin','2025-10-27 10:44:50');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `amenity_requests`
--

DROP TABLE IF EXISTS `amenity_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `amenity_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `items` text DEFAULT NULL COMMENT 'JSON array of requested items',
  `status` enum('Pending','Delivered','Cancelled') DEFAULT 'Pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amenity_requests`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `amenity_requests` WRITE;
/*!40000 ALTER TABLE `amenity_requests` DISABLE KEYS */;
INSERT INTO `amenity_requests` VALUES
(1,'102','Guest','[{\"id\":14,\"name\":\"Sajadah\",\"description\":\"Alat sholat (1 set)\",\"icon_path\":\"http://192.168.1.169/AHFix/uploads/amenities/amenity_1762854461_1137.jpg\",\"qty\":1}]','Pending','2026-03-15 11:03:36');
/*!40000 ALTER TABLE `amenity_requests` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package` varchar(100) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
INSERT INTO `app_settings` VALUES
(1,'com.google.android.youtube.tv',1,'2025-11-05 08:32:40'),
(2,'com.netflix.ninja',1,'2025-11-05 08:32:40'),
(3,'in.startv.hotstar.dplus.tv',1,'2025-11-05 08:32:40'),
(4,'com.vidio.android.tv',1,'2025-11-05 08:32:40'),
(5,'com.spotify.tv.android',1,'2025-11-05 08:32:40');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `channels`
--

DROP TABLE IF EXISTS `channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lcn` int(11) NOT NULL DEFAULT 0 COMMENT 'Logical Channel Number (urutan)',
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'Umum',
  `stream_url` text NOT NULL,
  `logo_url` text DEFAULT NULL,
  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lcn` (`lcn`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `channels`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `channels` WRITE;
/*!40000 ALTER TABLE `channels` DISABLE KEYS */;
INSERT INTO `channels` VALUES
(3,3,'TRANSTV','Channel TV','https://video.detik.com/transtv/smil:transtv.smil/chunklist_w2114898498_b744100_sleng.m3u8','https://www.transtv.co.id/themes/v25.7/src/assets/logo/transtv-white.png','enabled','2026-03-05 05:39:19'),
(4,4,'TRANS 7','Channel TV','https://video.detik.com/trans7/smil:trans7.smil/chunklist_w964486842_b744100_sleng.m3u8','https://www.transtv.co.id/themes/v25.7/src/assets/logo/transtv-white.png','enabled','2026-03-05 05:39:19'),
(8,8,'MDTV','Channel TV','https://d3dlxh2qgfbmej.cloudfront.net/4b0d3ec5db06491983e7dcf493ad431c/index_2.m3u8?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly9kM2RseGgycWdmYm1lai5jbG91ZGZyb250Lm5ldC80YjBkM2VjNWRiMDY0OTE5ODNlN2RjZjQ5M2FkNDMxYy8qIiwiQ29uZGl0aW9uIjp7IkRhdGVMZXNzVGhhbiI6eyJBV1M6RXBvY2hUaW1lIjoxNzczNzY3ODQ0fX19XX0_&Signature=bzJrn2XOlj-%7E53UAy8MCb9FJj96EWjfZQIP8AZqkVIxLgjENShnk1X2J5a2crFSblVmF7LjYYTo%7En1C5YtZ4vvyy89hzxMuO9mG%7E7ZekrapptdOMO0hMu-dBWXVjpswln%7EqrG1woS5SrUxT6aLxMe1Pc9lDgc2FgGcJcHUIVk6LrTj0dr21HtPOXlAGpDHk75hDlvQHmEg%7E9uOHPJ-kTvYz6hIq5e3At4RCYvUbMiA1lbcEsaUgx-eaeHwEWlVsbVytn%7ET4jJkrI1YHYZYMPMfFowkocTYc7ts6SIWfTbpepKES4xTB7o%7Efv9uscmZrv2iEJwAMCTehl-pREaSlbXg__&Key-Pair-Id=K421JPZA23CPQ','https://thumbor.prod.vidiocdn.com/vkORCwK34mMDw2PbRGLcVLkux10=/filters:quality(70)/vidio-web-prod-livestreaming/uploads/livestreaming/image/875/mdtv-ff5756.jpg','enabled','2026-03-05 05:39:19'),
(25,25,'NBA','Channel TV','https://ogietv.biz.id:443/Livetv/234/436.m3u8','https://ogietv.biz.id:443/images/QCqX5p5x3Y181Q8jwb82JKQvyxSBi_9A4CJxTYwEdAHwiZummrErsqmlWRw-A5GP.png','enabled','2026-03-05 05:39:19'),
(29,29,'ESPN 1','Channel TV','https://ogietv.biz.id:443/Livetv/234/1674.m3u8','https://ogietv.biz.id:443/images/QCqX5p5x3Y181Q8jwb82JCzHbGZkymHrQoDxAajCK5kw1L0at3yITHUd1Nzfju2t.png','enabled','2026-03-05 05:39:19'),
(61,61,'Soccer Channel','Channel TV','https://ogietv.biz.id:443/Livetv/234/31853.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvG_9e68Rb4PUYlM7Ob1Ek2xgPzv6Qz0v4_id4_XJYM7-iVfn5QKaEFhNvXsobN1DDb1drcntnDauf9OyvtJRMhpMp1cHB_Opu8Ovys4fewUGK-quhlNAhIAkuCbLVNBhzg.png','enabled','2026-03-05 05:39:19'),
(86,86,'TVRI','Channel TV','https://ogietv.biz.id:443/Livetv/234/1335.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvPIdO7JUOvSIiiql7nxnaF0l3Hv43CO1tCc1DxK9RnhxMR7jmm8PKxZ-r_RFSFRSWeqJo4kWnz44ELWtiDnHS78.png','enabled','2026-03-05 05:39:19'),
(87,87,'SCTV','Channel TV','https://ogietv.biz.id:443/Livetv/234/1322.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGigRZG-YjkkFaSEwKFkXscYQHBYKDx-KXUdwl86CuN61urXGOthgTlJ5rhY6GKWaOm2ffoRNZJXE0ZB1Jx5jrU.png','enabled','2026-03-05 05:39:19'),
(91,91,'TV ONE','Channel TV','https://ogietv.biz.id:443/Livetv/234/1345.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvPIdO7JUOvSIiiql7nxnaF0l3Hv43CO1tCc1DxK9Rnhxidx12F2y1zESgddR2oeQZ_4TGU7h2RO_rZIA_wSjtsFgkg9sQiYN5eRPP3zaZ0DRTAHMdV5WMHU12ILb9g684w.png','enabled','2026-03-05 05:39:19'),
(94,94,'METROTV','Channel TV','https://ogietv.biz.id:443/Livetv/234/1328.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJAM8J_hkScWYN9JYVAL03KFp-K022xty5ORam2X0ZYiIXR0CubKJIFD6SsSfzmo364cSpX2lLrsMA6KVDYvxnuZWAQl5Pvo2XZFzXcw3iF82cE34Ct2YBEMiPm8jTxqlQ.png','enabled','2026-03-05 05:39:19'),
(96,96,'KOMPASTV','Channel TV','https://ogietv.biz.id:443/Livetv/234/1331.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvClnLSRcZSCAJ_yyi46pjdsA4xccqyFB6gqv-MpHs9hY3LucTlPv913qLL2jCpyAxchkrTFoFg5Eut9FkP9Zb0g4wobw1nLTm-9atfS7bmKyXAVoKDAdG3Wbaqbtr0TxkA.png','enabled','2026-03-05 05:39:19'),
(98,98,'JTV','Channel TV','https://ogietv.biz.id:443/Livetv/234/33501.m3u8','https://ogietv.biz.id:443/images/QCqX5p5x3Y181Q8jwb82JGskTN3IXNGos_y1zb3pUP2Bqkz4jH8gRV68SsxnTUih.png','enabled','2026-03-05 05:39:19'),
(99,99,'CNN Indonesia','Channel TV','https://ogietv.biz.id:443/Livetv/234/1496.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvF8cUK5Jxtwj1NJ_7LBWZBmpUCBVTq6i6fYYwbyDLckgfoawZa4nKpuiQtrWH_Ds2SkyOrC-n4-S4xkzrNyIy3lbwk76uDdgeG9xhKDqGC1uU5ygyE8tfJbLll95A7-yQA.png','enabled','2026-03-05 05:39:19'),
(105,105,'HBO','Channel TV','https://ogietv.biz.id:443/Livetv/234/1364.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGwbSS-HAO-xicYK_sU6BsRO5DmYuv3Rbu7fsp52m3El6TXeIRDoSxM9ayVU1Ajeh0BRnkv8ivcl6r59cq9N2nQ.png','enabled','2026-03-05 05:39:19'),
(111,111,'HBO Family','Channel TV','https://ogietv.biz.id:443/Livetv/234/1370.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGwbSS-HAO-xicYK_sU6BsRO5DmYuv3Rbu7fsp52m3ElJMRXEVFUBruJa7hOabcaLVA0u_0O5rpMstXQkE70P7HpQRApUJoBPKcs4y1VUaO4L6_-vPyJLPBJoJ7N9RA2oA.png','enabled','2026-03-05 05:39:19'),
(123,123,'Thrill','Channel TV','https://ogietv.biz.id:443/Livetv/234/1386.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvOQxg-Y5LD1E7ybrkrYtdLg4gkLVRAKUTXm4bvr_VNxK1yOWvy58soSkSYFQl_jI2IA3iBYgVehSMtA8JoKV5OBCQ79GbzsH0WVPSD15TBZk6LjolGsp6nlSXuH-tH3YQg.png','enabled','2026-03-05 05:39:19'),
(126,126,'Galaxy','Channel TV','https://ogietv.biz.id:443/Livetv/234/1390.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvBEzlR0s4WXXyoV-k_dKmgACLmYnxL2sUaO_-RjhnQlLa6AnzLPtqXmmZ9ixEg7KvfTtKALSpr91mwuJzmLLZIdw1MM5JuUdZNMWn_yeT-ZFlGqAAtZrrQmgebin8oHYmA.png','enabled','2026-03-05 05:39:19'),
(129,129,'HITS','Channel TV','https://ogietv.biz.id:443/Livetv/234/1397.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGwbSS-HAO-xicYK_sU6BsRO5DmYuv3Rbu7fsp52m3EltWZl6k7_R_waNyNEc5lIBxOULB6TJlLRRvfe4XGkowY.png','enabled','2026-03-05 05:39:19'),
(131,131,'TVN','Channel TV','https://ogietv.biz.id:443/Livetv/234/1400.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJuVz0Vh2rNO2lypfG57MVtVTw6E-EVvk14pgiFVLwnu2dUnXIxMFlB8AbBJpTAS94Gb18M4h3RydamQltV4nrTcG8fWNvmJ0eTsPfvbC0e0fGX0rF-V1ZHvc1AAp4CmNw.png','enabled','2026-03-05 05:39:19'),
(135,135,'AXN','Channel TV','https://ogietv.biz.id:443/Livetv/234/1402.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGV1nsyv3Qm7N-OAVaJb2-f-z-SNlEgOZRoXZkkw0qQELZwuzG8m8RvnbbKZcTzCPuxnBxqVA4xOyttIIIV_IRo.png','enabled','2026-03-05 05:39:19'),
(137,137,'IMC','Channel TV','https://ogietv.biz.id:443/Livetv/234/1403.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGNX-ieeiu-ztsaEwUYdjfEI1AK5lVosDgtfxlYABK1m_iNOd96aCd3TNgS2tumTB_D3CaHvlpcTeCeAL3o5fKU.png','enabled','2026-03-05 05:39:19'),
(151,151,'KBSWorld','Channel TV','https://ogietv.biz.id:443/Livetv/234/1413.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJuVz0Vh2rNO2lypfG57MVtVTw6E-EVvk14pgiFVLwnu7MkDHoaVlp7b1OdpU0t70VwKT5BoiSFB-UYz_lAYSZietne3YJ_xUn7dRkxCThy8KlVp0xyLM7ESA0U5Cgig2Q.png','enabled','2026-03-05 05:39:19'),
(155,155,'AFN','Channel TV','https://ogietv.biz.id:443/Livetv/234/1423.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvOOsTdARvfNJXvHneAui8VT_7MQHg2EXQBq9fhRqbd7cCSYjJddBQBaMocJ_pRRZphiioLJXPH1BM5C-wqfPkV0lI3yBa2xYuZao_wz1LQsxQEXkTlB_TNlMBqYi6lU3CQ.png','enabled','2026-03-05 05:39:19'),
(156,156,'TLC','Channel TV','https://ogietv.biz.id:443/Livetv/234/1424.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvOOsTdARvfNJXvHneAui8VT_7MQHg2EXQBq9fhRqbd7cd6lswWlbEYFSpIbudyQ9VRWWxYzzX0InrrgUI4AjH3V7ei1AZH0F7a2bZenIfSmTs4BgXYVjicbrTF8zhactzg.png','enabled','2026-03-05 05:39:19'),
(159,159,'BBC News','Channel TV','https://ogietv.biz.id:443/Livetv/234/708.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvAPf96nLVrEt4TOZIjZXa4uw8egpVwi4-J7u2PAD-ROyLyGA05tIqvsOljwCa-RntW2bNfImgyFXK_TMYQWRFlDKJ5Xl7dAGrdWHE5DKkeaZTaYZy3HxaTQLmxB2aSeiIA.png','enabled','2026-03-05 05:39:19'),
(164,164,'Discovery Asia','Channel TV','https://ogietv.biz.id:443/Livetv/234/1438.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvBK_Rk-tg6-RvoRn8vKgFZ_q9szAuGR-Ib_288Me9Q_qeIt1vc8e96xSydhkPdgpF8dLwofOhkkoYcokIY9mBXTFV57tGXhixaTqw_IJa-7ftDmLvQo2AXqlOiLmNALVcA.png','enabled','2026-03-05 05:39:19'),
(165,165,'Discovery Channel','Channel TV','https://ogietv.biz.id:443/Livetv/234/1439.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvBK_Rk-tg6-RvoRn8vKgFZ_q9szAuGR-Ib_288Me9Q_qGOLBlggwXHwmLKt19YH0HLC_9gm9uEsr-lG9u3wHv7IfAWpN43hvDEJlnN1nCXl14gxYFy1SfWoVx4zAE9DBWg.png','enabled','2026-03-05 05:39:19'),
(171,171,'Dreamworks','Channel TV','https://ogietv.biz.id:443/Livetv/234/1457.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGwGkxhhOov0w5t8qcu-f62CmNUbSd1k34jHUqtzgCMiuz-LvIBZ0qe3NxhH5k8sXNunr8KaIC6BnnoBtdj3i8eud3jUzgs9y9juN1jcL3NWf9f225RqG3gJjF-x7-A3KQ.png','enabled','2026-03-05 05:39:19'),
(172,172,'Nick Junior','Channel TV','https://ogietv.biz.id:443/Livetv/234/601.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvO9_jZCw8CoP6gBmAjcZzwrMBS27_-F7eoezuNGKswsmwbpYZYRQiWGun33lgZ931YuFqeWoj73zLd8KtuKlDkAOK9i2wPpxxA-kPfSNYPAI2qA5dQxyfZjt2aFSYOFfjg.png','enabled','2026-03-05 05:39:19'),
(181,181,'Channel News Asia','Channel TV','https://ogietv.biz.id:443/Livetv/234/1491.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvG5N4h063eZc_EQFEzXSo5JkKtk63QQd29VfzXgexRBFbmizf4UtxpG1h3lwOLOQwnKxApsXfkMCg2cGoffic8s.png','enabled','2026-03-05 05:39:19'),
(182,182,'DW English','Channel TV','https://ogietv.biz.id:443/Livetv/234/1492.m3u8','https://ogietv.biz.id:443/images/QCqX5p5x3Y181Q8jwb82JFD38V--sV3R73RYLjtbsCOCUKwzROuycDtnHzZIAqwW.png','enabled','2026-03-05 05:39:19'),
(183,183,'CNBC Indonesia','Channel TV','https://ogietv.biz.id:443/Livetv/234/1497.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvG5N4h063eZc_EQFEzXSo5JkKtk63QQd29VfzXgexRBFvkhy70SugTUHga_mFS8_LP_EWl2yeP-nKYOjYaEn41_1S5iWeYEaNdvgNQCF55her_9lb9L6AEy39Qc1-z7lzQ.png','enabled','2026-03-05 05:39:19'),
(184,184,'CNN International','Channel TV','https://ogietv.biz.id:443/Livetv/234/1498.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvG5N4h063eZc_EQFEzXSo5JkKtk63QQd29VfzXgexRBF9G1DbpLt1UjwpjIfAfsDkM_IldO4xUkYvL2ehwV9xQH5sADSawlrZWSnFvxsVgwQkD5rmAjGbNepOUY84kw8QQ.png','enabled','2026-03-05 05:39:19'),
(213,213,'NHK World Japan','Channel TV','https://ogietv.biz.id:443/Livetv/234/12848.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJuVz0Vh2rNO2lypfG57MVtVTw6E-EVvk14pgiFVLwnuAS2sxdiW3LUIfzj-fsACV6A47ds7qA30zect1_qNuYYyU2sl_o8wTo80rRzBHrtDFWMkQi9ZwmABgf1VL_XWYA.png','enabled','2026-03-05 05:39:19'),
(230,230,'Kids Tv','Channel TV','https://ogietv.biz.id:443/Livetv/234/31900.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvKXSeS0bTp9NyDHxNmdfbsVOk9sLFF9HlwgchPhpCzo4tfDQJEc1fiENBBqv2ybeLFCye48jaWuLo0fXPPKfYV7KEqQNs_aMr94tID455nOAlswwzQnt1gH08FaHnqvTQQ.png','enabled','2026-03-05 05:39:19'),
(232,232,'Cinemachi','Channel TV','https://ogietv.biz.id:443/Livetv/234/63176.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvCwYMNr8HDJoefw3K5vYPlIR3OfEUt7vIXKFd6lMLNmRURygofgAi5OtRlY9p8lGnLEMV46-t2HYVJ4uaTMRShhCLp5FhZXQVmdOkn2YfLzvqnMxxyDPGBaJXULxbeBnrQ.png','enabled','2026-03-05 05:39:19'),
(235,235,'Cinemachi Kids','Channel TV','https://ogietv.biz.id:443/Livetv/234/63179.m3u8','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvICuwbx-ax6PRBT4esrRb4pC1eYje8wygtPXWkApaFOxlwIR8NfM0HQDcJaLucEjspYjf5LIWwhMgI2EI1j32Kde57QFN6uG1VxRkP-dIh7XBPgjFly63GvjG9CT0Yk8kA.png','enabled','2026-03-05 05:39:19'),
(260,260,'Cctv 4','Channel TV','https://ogietv.biz.id:443/Livetv/234/82983.m3u8','https://ogietv.biz.id:443/images/yaxZJ07eVLvcPbZCcPO7I4iawnf9xiQzmvMZv-uNJds.png','enabled','2026-03-05 05:39:19'),
(262,39,'KBS_World','Channel TV','http://172.31.15.1:80/kbs_world','https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJuVz0Vh2rNO2lypfG57MVtVTw6E-EVvk14pgiFVLwnu7MkDHoaVlp7b1OdpU0t70VwKT5BoiSFB-UYz_lAYSZietne3YJ_xUn7dRkxCThy8KlVp0xyLM7ESA0U5Cgig2Q.png','enabled','2026-03-05 07:27:18'),
(263,40,'Discovery','Channel TV','http://172.31.15.2:80/Discovery','','enabled','2026-03-05 08:26:13'),
(264,41,'nhk_world','Channel TV','http://172.31.15.3:80/nhk_world','','enabled','2026-03-05 08:26:33');
/*!40000 ALTER TABLE `channels` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `device_units`
--

DROP TABLE IF EXISTS `device_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(255) NOT NULL,
  `launcher_script` text NOT NULL,
  `restore_script` text NOT NULL,
  `clear_script` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_units`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `device_units` WRITE;
/*!40000 ALTER TABLE `device_units` DISABLE KEYS */;
INSERT INTO `device_units` VALUES
(1,'TCL','shell cmd package set-home-activity com.takeoff.launcher/.MainActivity\r\nshell am start -a android.intent.action.MAIN -c android.intent.category.HOME\r\nshell pm disable-user --user 0 com.google.android.tv.launcherx\r\nshell pm disable-user --user 0 com.google.android.apps.tv.launcherx\r\nshell pm disable-user --user 0 com.google.android.tvlauncher\r\nshell pm disable-user --user 0 com.google.android.leanbacklauncher\r\nshell pm uninstall -k --user 0 com.google.android.tv.launcherx\r\nshell pm uninstall -k --user 0 com.google.android.apps.tv.launcherx\r\nshell pm disable-user --user 0 com.google.android.tv.setupwraith\r\nshell pm disable-user --user 0 com.google.android.tungsten.setupwraith\r\nshell cmd package set-home-activity com.takeoff.launcher/.MainActivity','shell pm enable com.google.android.apps.tv.launcherx\r\nshell pm enable com.google.android.tvlauncher\r\nshell pm enable com.google.android.tv.launcherx\r\nshell pm enable com.google.android.leanbacklauncher\r\nshell cmd package install-existing --user 0 com.google.android.tv.launcherx\r\nshell cmd package install-existing --user 0 com.google.android.apps.tv.launcherx\r\nshell pm enable com.google.android.tv.setupwraith\r\nshell pm enable com.google.android.tungsten.setupwraith\r\nshell cmd package set-home-activity com.google.android.apps.tv.launcherx/.MainActivity\r\nshell am start -a android.intent.action.MAIN -c android.intent.category.HOME','com.google.android.youtube.tv\r\ncom.netflix.ninja\r\ncom.spotify.tv.android\r\ncom.vidio.android.tv','2026-03-09 10:00:36'),
(3,'STB ZTE ZXV10 B66F','shell am start -n com.takeoff.launcher/.MainActivity','shell am start -n com.google.android.tvlauncher/.MainActivity','com.google.android.youtube.tv\r\ncom.netflix.ninja\r\ncom.spotify.tv.android\r\ncom.vidio.android.tv','2026-03-09 20:36:16'),
(4,'STB ZTE ZXV10 B860H','shell pm disable-user --user 0 com.google.android.tvlauncher\r\nshell cmd package set-home-activity com.takeoff.launcher/.MainActivity\r\nshell am start -n com.takeoff.launcher/.MainActivity','shell pm enable com.google.android.tvlauncher\r\nshell cmd package set-home-activity com.google.android.tvlauncher/.MainActivity\r\nshell am start -n com.google.android.tvlauncher/.MainActivity','com.google.android.youtube.tv\r\ncom.netflix.ninja\r\ncom.spotify.tv.android\r\ncom.vidio.android.tv','2026-03-10 10:53:01');
/*!40000 ALTER TABLE `device_units` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `dining_menu`
--

DROP TABLE IF EXISTS `dining_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dining_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_kat_dining` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `image_url` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dining_menu`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `dining_menu` WRITE;
/*!40000 ALTER TABLE `dining_menu` DISABLE KEYS */;
INSERT INTO `dining_menu` VALUES
(1,3,'Nasi Goreng Spesial','Special Fried Rice',NULL,25000,'uploads/dining/menu_1772371852_1849.jpg','active'),
(2,3,'Mie Goreng Seafood','Seafood Fried Noodles',NULL,28000,'uploads/dining/menu_1772371845_2555.jpg','active'),
(3,3,'Sate Ayam Madura','Madura Chicken Satay',NULL,32000,'uploads/dining/menu_1772371838_9425.jpg','active'),
(4,1,'Soto Ayam Lamongan','Lamongan Chicken Soup',NULL,27000,'uploads/dining/menu_1772371831_4362.jpg','active'),
(5,2,'Ayam Penyet Sambal Ijo','Smashed Chicken (Green Chili)',NULL,30000,'uploads/dining/menu_1772371824_8410.jpg','active'),
(6,2,'Capcay Kuah','Capcay Soup',NULL,26000,'uploads/dining/menu_1772371817_8370.jpg','active'),
(7,2,'Teh Manis Dingin','Iced Sweet Tea',NULL,8000,'uploads/dining/menu_1772371810_5717.jpg','active'),
(8,1,'Kopi Hitam Tubruk','Black Coffee',NULL,10000,'uploads/dining/menu_1772371803_2895.jpg','active'),
(9,1,'Jus Alpukat','Avocado Juice',NULL,15000,'uploads/dining/menu_1772371795_3214.jpg','active'),
(10,1,'Pisang Goreng Keju','Fried Banana w/ Cheese',NULL,18000,'uploads/dining/menu_1772371788_2092.jpg','active'),
(11,1,'Nasi Goreng Terasi','Shrimp Paste Fried Rice',NULL,35000,'uploads/dining/menu_1772371780_9870.jpg','active');
/*!40000 ALTER TABLE `dining_menu` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `general_info`
--

DROP TABLE IF EXISTS `general_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `general_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_kat_general_info` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `show_description` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `general_info`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `general_info` WRITE;
/*!40000 ALTER TABLE `general_info` DISABLE KEYS */;
INSERT INTO `general_info` VALUES
(1,2,'tyrty','trytry','trytry','trytytry','uploads/general_info/gen_info_1773355096.jpg',1,1),
(2,1,'65776','657567','567567','657567','uploads/general_info/gen_info_1773355118.jpg',1,1),
(3,2,'ewrer','ewrewr','werewr','ewrewr','uploads/general_info/gen_info_1773563267.jpg',1,1);
/*!40000 ALTER TABLE `general_info` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `global_settings`
--

DROP TABLE IF EXISTS `global_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `global_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `global_settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `global_settings` WRITE;
/*!40000 ALTER TABLE `global_settings` DISABLE KEYS */;
INSERT INTO `global_settings` VALUES
(1,'launcher_enabled','1'),
(4,'default_volume','10'),
(10,'system_version','v251227-034321'),
(13,'splash_enabled','1'),
(16,'launcher_bg',''),
(17,'launcher_home_bg','uploads/homebg/launcher_home_bg.jpg?v=1766986021'),
(26,'loading_logo_url','uploads/logo/loading_logo.png?v=1762812096'),
(29,'custom_greeting_title','Welcome'),
(30,'custom_welcome_greeting','Selamat datang di Hotel Harris.\r\nKami sangat senang menyambut Anda sebagai tamu istimewa kami.\r\nNikmati kenyamanan kamar, layanan ramah, serta suasana modern dan kreatif yang telah kami siapkan untuk membuat masa inap Anda lebih berkesan.\r\nJika Anda membutuhkan bantuan kapan saja, tim kami selalu siap melayani dengan sepenuh hati.\r\nSelamat beristirahat & enjoy your stay!\r\n\r\n— Branch Manager, Hotel Harris'),
(31,'custom_greeting_image','uploads/greeting/greeting_img.jpg?v=1769756199');
/*!40000 ALTER TABLE `global_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `guest_checkin`
--

DROP TABLE IF EXISTS `guest_checkin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_checkin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(10) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `checkin_time` datetime NOT NULL DEFAULT current_timestamp(),
  `checkout_time` datetime DEFAULT NULL,
  `status` enum('checked_in','checked_out') NOT NULL DEFAULT 'checked_in',
  PRIMARY KEY (`id`),
  KEY `room_number` (`room_number`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_checkin`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `guest_checkin` WRITE;
/*!40000 ALTER TABLE `guest_checkin` DISABLE KEYS */;
INSERT INTO `guest_checkin` VALUES
(1,'101','JOKO P','2026-03-10 03:00:45','2026-03-10 04:05:14','checked_out'),
(2,'101','JOKO P','2026-03-10 04:55:57','2026-03-10 15:34:10','checked_out'),
(3,'101','Rizal','2026-03-10 15:50:10','2026-03-10 16:05:37','checked_out'),
(4,'101','Rizal','2026-03-10 16:05:59','2026-03-10 16:38:04','checked_out'),
(5,'101','Rizal','2026-03-10 16:38:17','2026-03-10 22:33:26','checked_out'),
(6,'102','Rizal','2026-03-11 01:09:27','2026-03-13 01:22:16','checked_out'),
(7,'102','Rizal','2026-03-13 01:39:05','2026-03-13 01:39:12','checked_out'),
(33,'101','Ian Quek, Mr.','2026-06-17 12:55:30','2026-06-17 12:56:56','checked_out'),
(34,'106','Kenneth Robert Suthren, Mr.','2026-06-17 12:55:33','2026-06-17 12:57:10','checked_out'),
(35,'107','Bobby Pratama, Mr.','2026-06-17 12:55:33','2026-06-17 12:57:13','checked_out'),
(36,'101','Ian Quek, Mr.','2026-06-17 12:57:48','2026-06-17 12:57:59','checked_out'),
(37,'106','Kenneth Robert Suthren, Mr.','2026-06-17 12:57:51',NULL,'checked_in'),
(38,'107','Bobby Pratama, Mr.','2026-06-17 12:57:51',NULL,'checked_in'),
(39,'101','Ian Quek, Mr.','2026-06-17 12:58:21',NULL,'checked_in');
/*!40000 ALTER TABLE `guest_checkin` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `hotel_facilities`
--

DROP TABLE IF EXISTS `hotel_facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_facilities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_kat_facilities` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `show_description` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotel_facilities`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `hotel_facilities` WRITE;
/*!40000 ALTER TABLE `hotel_facilities` DISABLE KEYS */;
INSERT INTO `hotel_facilities` VALUES
(1,1,'Classic',NULL,'uploads/facilities/facility_1762374139_7591.jpg','',NULL,1,0),
(2,3,'Hotel Service',NULL,'uploads/facilities/facility_1762374173_2294.jpg','',NULL,1,0),
(3,1,'Breakfast',NULL,'uploads/facilities/facility_1762374195_8430.jpg','',NULL,1,0),
(4,2,'Sales',NULL,'uploads/facilities/facility_1762374266_8271.jpg','',NULL,1,0),
(5,2,'Bedroom',NULL,'uploads/facilities/facility_1762374289_5827.jpg','',NULL,1,0),
(9,2,'TESTING','','uploads/facilities/facility_1762672461_6616.jpeg','test 123','',1,1),
(10,1,'TESTING','','uploads/facilities/fac_1781501774.jpg','ttt','',1,1);
/*!40000 ALTER TABLE `hotel_facilities` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `hotel_info`
--

DROP TABLE IF EXISTS `hotel_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_kat_info` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `title_en` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `show_description` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotel_info`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `hotel_info` WRITE;
/*!40000 ALTER TABLE `hotel_info` DISABLE KEYS */;
INSERT INTO `hotel_info` VALUES
(1,1,'Hotel Kami',NULL,'',NULL,'uploads/info/info_1762383411_1981.jpg',0,'2025-11-05 22:56:51',0),
(2,1,'Check in',NULL,'',NULL,'uploads/info/info_1762383434_1467.jpg',0,'2025-11-05 22:57:14',0),
(3,2,'Analitik',NULL,'',NULL,'uploads/info/info_1762383448_2523.jpg',0,'2025-11-05 22:57:28',0),
(4,2,'Selamat Datang',NULL,'',NULL,'uploads/info/info_1762383478_7320.jpg',0,'2025-11-05 22:57:58',0);
/*!40000 ALTER TABLE `hotel_info` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `hotel_orders`
--

DROP TABLE IF EXISTS `hotel_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `items` text DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Delivered','Cancelled') DEFAULT 'Pending',
  `ordered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotel_orders`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `hotel_orders` WRITE;
/*!40000 ALTER TABLE `hotel_orders` DISABLE KEYS */;
INSERT INTO `hotel_orders` VALUES
(1,'TV-8XAINJ','Guest','[{\"id\":11,\"id_kat_dining\":1,\"name\":\"Nasi Goreng Terasi\",\"price\":35000,\"icon_path\":\"http://localhost:8080/uploads/dining/menu_1772371780_9870.jpg\",\"status\":\"active\",\"qty\":1}]',35000.00,'Pending','2026-06-15 06:30:13');
/*!40000 ALTER TABLE `hotel_orders` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kat_dining`
--

DROP TABLE IF EXISTS `kat_dining`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kat_dining` (
  `id_kat_dining` int(11) NOT NULL AUTO_INCREMENT,
  `nm_kat_dining` varchar(255) NOT NULL,
  `foto_kat_dining` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id_kat_dining`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kat_dining`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kat_dining` WRITE;
/*!40000 ALTER TABLE `kat_dining` DISABLE KEYS */;
INSERT INTO `kat_dining` VALUES
(1,'Indonesian Food','uploads/kat_dining/kat_dining_1773566226_9678.jpg'),
(2,'Chinese Food','uploads/kat_dining/kat_dining_1773566254_5472.jpg'),
(3,'Arabic Food','uploads/kat_dining/kat_dining_1773566307_8076.jpg');
/*!40000 ALTER TABLE `kat_dining` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kat_facilities`
--

DROP TABLE IF EXISTS `kat_facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kat_facilities` (
  `id_kat_facilities` int(11) NOT NULL AUTO_INCREMENT,
  `nm_kat_facilities` varchar(255) NOT NULL,
  `foto_kat_facilities` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id_kat_facilities`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kat_facilities`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kat_facilities` WRITE;
/*!40000 ALTER TABLE `kat_facilities` DISABLE KEYS */;
INSERT INTO `kat_facilities` VALUES
(1,'Kategori 1','uploads/kat_facilities/kat_1773351945_4666.jpg'),
(2,'Kategori 2','uploads/kat_facilities/kat_1773351958_7609.jpg'),
(3,'Kategori 3','uploads/kat_facilities/kat_1773351971_5910.jpg'),
(4,'Kategori 4','uploads/kat_facilities/kat_1773351985_3802.jpg');
/*!40000 ALTER TABLE `kat_facilities` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kat_general_info`
--

DROP TABLE IF EXISTS `kat_general_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kat_general_info` (
  `id_kat_general_info` int(11) NOT NULL AUTO_INCREMENT,
  `nm_kat_general_info` varchar(255) NOT NULL,
  `foto_kat_general_info` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id_kat_general_info`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kat_general_info`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kat_general_info` WRITE;
/*!40000 ALTER TABLE `kat_general_info` DISABLE KEYS */;
INSERT INTO `kat_general_info` VALUES
(1,'Tentang kami','uploads/kat_general_info/kat_gen_info_1773355019_6260.jpg'),
(2,'Fasilitas','uploads/kat_general_info/kat_gen_info_1773355069_1144.jpg');
/*!40000 ALTER TABLE `kat_general_info` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kat_info`
--

DROP TABLE IF EXISTS `kat_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kat_info` (
  `id_kat_info` int(11) NOT NULL AUTO_INCREMENT,
  `nm_kat_info` varchar(255) NOT NULL,
  `foto_kat_info` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id_kat_info`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kat_info`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kat_info` WRITE;
/*!40000 ALTER TABLE `kat_info` DISABLE KEYS */;
INSERT INTO `kat_info` VALUES
(1,'Apa Itu Hotel','uploads/kat_info/kat_info_1773548334_5928.png'),
(2,'Sejarah','uploads/kat_info/kat_info_1773548354_1009.png'),
(3,'Hotel','uploads/kat_info/kat_info_1773548432_6802.jpg');
/*!40000 ALTER TABLE `kat_info` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kat_promotion`
--

DROP TABLE IF EXISTS `kat_promotion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kat_promotion` (
  `id_kat_promotion` int(11) NOT NULL AUTO_INCREMENT,
  `nm_kat_promotion` varchar(255) NOT NULL,
  `foto_kat_promotion` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id_kat_promotion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kat_promotion`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kat_promotion` WRITE;
/*!40000 ALTER TABLE `kat_promotion` DISABLE KEYS */;
INSERT INTO `kat_promotion` VALUES
(1,'promo 1','uploads/kat_promotion/kat_promo_1773566709_1406.jpg'),
(2,'promo 2','uploads/kat_promotion/kat_promo_1773566718_6852.jpg'),
(3,'promo 3','uploads/kat_promotion/kat_promo_1773566741_1101.png');
/*!40000 ALTER TABLE `kat_promotion` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `managed_devices`
--

DROP TABLE IF EXISTS `managed_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `managed_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(100) NOT NULL,
  `device_name` varchar(100) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `device_ip` varchar(45) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `pending_clear` tinyint(1) NOT NULL DEFAULT 0,
  `pending_start_launcher` tinyint(1) NOT NULL DEFAULT 0,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_seen` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `managed_devices`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `managed_devices` WRITE;
/*!40000 ALTER TABLE `managed_devices` DISABLE KEYS */;
INSERT INTO `managed_devices` VALUES
(15,'TV-VH72RS','Joko TV','101','192.168.1.62',4,1,0,'2026-03-09 19:50:07',1,'2026-03-20 21:58:19'),
(19,'TV-8XAINJ','TV-8XAINJ','TV-8XAINJ','192.168.0.217',NULL,0,0,'2026-06-11 17:58:46',1,'2026-06-15 20:00:11'),
(20,'TV-JIZMLB','TV-JIZMLB','TV-JIZMLB',NULL,NULL,0,0,'2026-06-11 18:01:21',1,'2026-06-12 02:01:21'),
(21,'TV-A8MIEJ','TV-A8MIEJ','TV-A8MIEJ',NULL,NULL,0,0,'2026-06-11 18:06:44',1,'2026-06-12 02:06:44'),
(25,'TV-RXBLOO','TV-RXBLOO','TV-RXBLOO','1.1.1.1',1,0,0,'2026-06-15 05:33:59',1,'2026-06-15 13:33:59'),
(27,'TV-IMPORT106','TV Kamar 106','106',NULL,NULL,1,0,'2026-06-17 04:19:43',1,'2026-06-17 12:19:43'),
(28,'TV-IMPORT107','TV Kamar 107','107',NULL,NULL,1,0,'2026-06-17 04:19:43',1,'2026-06-17 12:19:43'),
(29,'TV-Q3DWO6','TV-Q3DWO6','999','1.1.1.1',4,0,0,'2026-06-17 05:16:49',1,'2026-06-17 13:16:49');
/*!40000 ALTER TABLE `managed_devices` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `rooms` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `playlists`
--

DROP TABLE IF EXISTS `playlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `playlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Nama provider',
  `url` text NOT NULL COMMENT 'URL file M3U',
  `default_category` varchar(100) DEFAULT 'Playlist',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playlists`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `playlists` WRITE;
/*!40000 ALTER TABLE `playlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `playlists` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `popup_notifications`
--

DROP TABLE IF EXISTS `popup_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `popup_notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(128) NOT NULL,
  `room_number` varchar(32) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `status` enum('pending','delivered','expired') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `sound_url` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_device_status_created` (`device_id`,`status`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `popup_notifications`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `popup_notifications` WRITE;
/*!40000 ALTER TABLE `popup_notifications` DISABLE KEYS */;
INSERT INTO `popup_notifications` VALUES
(2,'TV-RXBLOO','TV-RXBLOO','TV-RXBLOO','TV-RXBLOO','delivered','2026-06-15 13:34:36','2026-06-15 13:34:37','2026-06-16 13:34:36',NULL,NULL),
(3,'TV-Q3DWO6','999','TV-Q3DWO6','TV-Q3DWO6','delivered','2026-06-17 13:17:21','2026-06-17 13:17:21','2026-06-18 13:17:21',NULL,NULL);
/*!40000 ALTER TABLE `popup_notifications` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `promotion`
--

DROP TABLE IF EXISTS `promotion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `promotion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_kat_promotion` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `show_description` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotion`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `promotion` WRITE;
/*!40000 ALTER TABLE `promotion` DISABLE KEYS */;
INSERT INTO `promotion` VALUES
(1,1,'adsadsd','asdsad','sadsadsa','sadsad','uploads/promotion/promo_1773566821.jpg',1,1),
(2,1,'sAS','ds','dsf','sdf','uploads/promotion/promo_1773566840.jpg',1,1),
(3,2,'dfsf','h','ghgh','gg','uploads/promotion/promo_1773566854.jpg',1,1),
(4,3,'Promo 3','Promo 3','Promo 3','Promo 3','uploads/promotion/promo_1781500713.jpg',1,1);
/*!40000 ALTER TABLE `promotion` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `room_amenities`
--

DROP TABLE IF EXISTS `room_amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_amenities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_amenities`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `room_amenities` WRITE;
/*!40000 ALTER TABLE `room_amenities` DISABLE KEYS */;
INSERT INTO `room_amenities` VALUES
(11,'Handuk Tambahan',NULL,'Handuk mandi ekstra (1 buah)',NULL,'uploads/amenities/amenity_1762854506_5026.jpg','general','2025-11-11 09:16:28'),
(12,'Bantal Tambahan',NULL,'Bantal tidur ekstra (1 buah)',NULL,'uploads/amenities/amenity_1762854489_6848.jpg','general','2025-11-11 09:16:28'),
(13,'Perlengkapan Mandi',NULL,'Sabun, Shampoo, Sikat Gigi',NULL,'uploads/amenities/amenity_1762854476_7748.jpg','general','2025-11-11 09:16:28'),
(14,'Sajadah',NULL,'Alat sholat (1 set)',NULL,'uploads/amenities/amenity_1762854461_1137.jpg','general','2025-11-11 09:16:28'),
(15,'Air Mineral',NULL,'Air mineral botol (2 botol)',NULL,'uploads/amenities/amenity_1762854450_2040.jpg','general','2025-11-11 09:16:28'),
(16,'Teko Kopi',NULL,'Kopi, teh, susu',NULL,'uploads/amenities/amenity_1762855317_8455.jpg','general','2025-11-11 10:01:57'),
(17,'Sajadah','Prayer Place','Alat Shalat 1 set','Prayer Set','uploads/amenities/am_1764212513.jpg','general','2025-11-27 03:01:53');
/*!40000 ALTER TABLE `room_amenities` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `system_apps`
--

DROP TABLE IF EXISTS `system_apps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_key` varchar(50) NOT NULL,
  `app_name` varchar(100) NOT NULL,
  `app_name_en` varchar(100) DEFAULT NULL,
  `icon_path` varchar(255) NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `android_package` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_key` (`app_key`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_apps`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `system_apps` WRITE;
/*!40000 ALTER TABLE `system_apps` DISABLE KEYS */;
INSERT INTO `system_apps` VALUES
(1,'information','Information','Information','img/information.png',1,1,NULL),
(2,'dining','Dining Room','Dining Room','img/diningroom.png',1,2,NULL),
(3,'amenities','Amenities','Amenities','img/amenities.png',1,8,NULL),
(4,'facilities','Facilities','Facilities','img/facilities.png',1,7,NULL),
(6,'youtube','YouTube',NULL,'img/youtube.png',1,9,'com.google.android.youtube.tv'),
(7,'netflix','Netflix',NULL,'img/netflix.png',1,4,'com.netflix.ninja'),
(8,'spotify','Spotify',NULL,'img/spotify.png',1,10,'com.spotify.tv.android'),
(10,'vidio','Vidio',NULL,'img/vidio.png',1,11,'com.vidio.android.tv'),
(23,'clear_data_guest','Clear Data Guest',NULL,'uploads/icons/icon_1769246765.png',1,6,'clear.data'),
(25,'tv_local','TV Channel',NULL,'uploads/icons/icon_1772552379.png',1,5,'com.mmaplay.iptv'),
(29,'promotion','promotion','promotion','uploads/icons/icon_1773353782.png',1,3,NULL),
(30,'general_info','General Information',NULL,'uploads/icons/icon_1773563755.png',1,0,'internal.general_info');
/*!40000 ALTER TABLE `system_apps` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `system_marquee`
--

DROP TABLE IF EXISTS `system_marquee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_marquee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_marquee`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `system_marquee` WRITE;
/*!40000 ALTER TABLE `system_marquee` DISABLE KEYS */;
INSERT INTO `system_marquee` VALUES
(1,'',1,'2026-02-27 15:10:31');
/*!40000 ALTER TABLE `system_marquee` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES
('amenities_request_card_enabled','1','2026-03-16 15:00:04'),
('dining_cart_enabled','1','2026-03-16 14:59:20'),
('scheduled_clear_enabled','1','2026-02-28 02:34:20'),
('scheduled_clear_time','02:15','2026-02-28 02:34:20'),
('wa_fonnte_token','3xpJ3q4kvKAehog6k14V','2026-02-28 02:32:34'),
('wa_gateway_enabled','1','2026-02-28 02:32:34');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `transportation_requests`
--

DROP TABLE IF EXISTS `transportation_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transportation_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `pickup_point` varchar(255) DEFAULT '',
  `destination` varchar(255) DEFAULT 'By Request',
  `num_passengers` int(11) DEFAULT 1,
  `preferred_time` varchar(50) DEFAULT 'NOW',
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
  `requested_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transportation_requests`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `transportation_requests` WRITE;
/*!40000 ALTER TABLE `transportation_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `transportation_requests` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-06-17 13:39:18
