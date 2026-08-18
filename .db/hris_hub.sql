-- MySQL dump 10.13  Distrib 8.0.44, for macos15 (arm64)
--
-- Host: localhost    Database: hris_hub
-- ------------------------------------------------------
-- Server version	9.4.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_modules`
--

DROP TABLE IF EXISTS `access_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_modules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_modules`
--

LOCK TABLES `access_modules` WRITE;
/*!40000 ALTER TABLE `access_modules` DISABLE KEYS */;
INSERT INTO `access_modules` VALUES (1,'Dashboard','dashboard','fas fa-th-large',1,0),(2,'Tickets','tickets','fas fa-ticket',2,0),(3,'Improvements','improvements','fas fa-rocket',3,0),(4,'Need Approvals','approvals','fas fa-check-circle',4,0),(5,'Master Projects','master_projects','fas fa-folder-tree',5,0),(6,'Users','users','fas fa-users',6,0),(7,'Roles','roles','fas fa-user-shield',7,0),(8,'Blueprints','blueprints','fas fa-drafting-compass',8,0);
/*!40000 ALTER TABLE `access_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_keys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `api_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
INSERT INTO `api_keys` VALUES (1,'0a99ba4084fbfd7c59188477d177f8daaf45b742a8dfadf47aecb114aace8341','Web App Service Key',1,'2026-07-01 13:56:00',0);
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_requests`
--

DROP TABLE IF EXISTS `approval_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `ticket_id` bigint unsigned DEFAULT NULL,
  `requester_id` bigint unsigned NOT NULL,
  `approver_id` bigint unsigned DEFAULT NULL,
  `stage_sequence` int NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `approver_id` (`approver_id`),
  KEY `requester_id` (`requester_id`),
  CONSTRAINT `approval_requests_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `approval_requests_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `approval_requests_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_requests`
--

LOCK TABLES `approval_requests` WRITE;
/*!40000 ALTER TABLE `approval_requests` DISABLE KEYS */;
INSERT INTO `approval_requests` VALUES (9,9,NULL,6,6,1,1,NULL,'2026-07-14 10:13:50','2026-07-14 10:13:50',0),(10,9,NULL,6,6,2,1,NULL,'2026-07-14 10:14:00','2026-07-14 10:14:00',0);
/*!40000 ALTER TABLE `approval_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `entity_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `entity_id` bigint unsigned NOT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `entity_type_entity_id` (`entity_type`,`entity_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=237 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (75,7,'ticket',22,'create_ticket',NULL,'{\"type\": 0, \"title\": \"UX tidak tampil dengan tepat\", \"page_id\": 9, \"priority\": 1}','2026-07-13 02:41:54'),(76,6,'ticket',22,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-13 02:43:06'),(77,6,'ticket_comment',12,'add_comment',NULL,'{\"content\": \"data sudah diperbaiki\", \"ticket_id\": 22}','2026-07-13 02:43:27'),(78,6,'ticket',22,'resolve_ticket',NULL,'{\"resolution_note\": \"silahkan di testing\"}','2026-07-13 02:43:52'),(79,7,'ticket',23,'create_ticket',NULL,'{\"type\": 0, \"title\": \"Error di halaman fpk\", \"page_id\": 11, \"priority\": 1}','2026-07-13 21:32:01'),(80,6,'ticket_comment',13,'add_comment',NULL,'{\"content\": \"tolong perbaiki\", \"ticket_id\": 23}','2026-07-13 21:34:16'),(81,6,'ticket_comment',14,'add_comment',NULL,'{\"content\": \"lalalal\", \"ticket_id\": 23}','2026-07-13 21:34:34'),(82,6,'ticket',23,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-13 21:34:47'),(83,6,'ticket_comment',15,'add_comment',NULL,'{\"content\": \"halaman sudah di perbaiki\", \"ticket_id\": 23}','2026-07-13 21:35:04'),(84,6,'ticket',23,'resolve_ticket',NULL,'{\"resolution_note\": \"sudah selesai tolong dicoba kembali\"}','2026-07-13 21:35:15'),(85,6,'ticket',22,'move_ticket','{\"status\": \"3\"}','{\"status\": 4}','2026-07-14 02:22:09'),(86,6,'project',9,'create_improvement',NULL,'{\"name\": \"testing dan implementasi\", \"priority\": 1}','2026-07-14 10:13:12'),(87,6,'project',9,'approve_it','{\"status\": 0}','{\"status\": 1}','2026-07-14 10:13:50'),(88,6,'project',9,'approve_dept','{\"status\": 1}','{\"status\": 2}','2026-07-14 10:14:00'),(89,6,'project_comment',1,'add_comment',NULL,'{\"project_id\": 9}','2026-07-14 10:14:22'),(90,6,'blueprint',1,'create',NULL,'{\"name\": \"testing\", \"improvement_id\": 9}','2026-07-14 15:51:54'),(91,6,'project',10,'create_improvement',NULL,'{\"name\": \"tstinalskdjsaldjalskdj\", \"priority\": 2}','2026-07-15 03:17:51'),(92,6,'blueprint',2,'create',NULL,'{\"name\": \"testing\", \"improvement_id\": 10}','2026-07-15 03:18:16'),(93,6,'blueprint_module',1,'create',NULL,'{\"name\": \"testing\", \"blueprint_id\": 2}','2026-07-15 03:24:56'),(94,6,'blueprint_module',2,'create',NULL,'{\"name\": \"testing\", \"blueprint_id\": 1}','2026-07-15 03:49:45'),(95,6,'blueprint_module',3,'create',NULL,'{\"name\": \"lalaal\", \"blueprint_id\": 1}','2026-07-15 03:49:53'),(96,6,'blueprint_scenario',1,'create',NULL,'{\"title\": \"lala\", \"module_id\": 3}','2026-07-15 03:50:11'),(97,6,'blueprint_scenario',2,'create',NULL,'{\"title\": \"Memo Karyawan Masuk (Option A)\", \"module_id\": 2}','2026-07-15 03:51:26'),(98,6,'blueprint_design_page',1,'create',NULL,'{\"title\": \"testing\", \"module_id\": 2}','2026-07-15 04:12:57'),(99,6,'blueprint_design_page',2,'create',NULL,'{\"title\": \"Login pages\", \"module_id\": 1}','2026-07-15 06:56:43'),(100,6,'blueprint_scenario',3,'create',NULL,'{\"title\": \"testing\", \"module_id\": 1}','2026-07-15 12:15:54'),(101,6,'blueprint_scenario',4,'create',NULL,'{\"title\": \"haahah\", \"module_id\": 1}','2026-07-15 12:43:14'),(102,6,'blueprint_scenario',3,'update',NULL,'{\"title\": \"testing\", \"updated_at\": \"2026-07-15 19:48:18\", \"description\": \"lalala\"}','2026-07-15 12:48:18'),(103,6,'blueprint_scenario',3,'update',NULL,'{\"title\": \"testing\", \"updated_at\": \"2026-07-15 19:48:35\", \"description\": \"lololo\"}','2026-07-15 12:48:35'),(104,6,'blueprint_scenario',3,'update',NULL,'{\"title\": \"testing\", \"updated_at\": \"2026-07-15 19:48:59\", \"description\": \"lololo\"}','2026-07-15 12:48:59'),(105,6,'blueprint_scenario',3,'update',NULL,'{\"title\": \"testing\", \"updated_at\": \"2026-07-15 19:53:43\", \"description\": \"lololo\"}','2026-07-15 12:53:43'),(106,6,'ticket',23,'move_ticket','{\"status\": \"3\"}','{\"status\": 4}','2026-07-15 12:54:41'),(107,6,'blueprint_scenario',3,'update',NULL,'{\"title\": \"testing\", \"updated_at\": \"2026-07-15 20:03:05\", \"description\": \"lololo\"}','2026-07-15 13:03:05'),(108,6,'blueprint_scenario',5,'create',NULL,'{\"title\": \"haha\", \"module_id\": 1}','2026-07-15 13:03:32'),(109,6,'blueprint_scenario',3,'update',NULL,'{\"title\": \"testing\", \"updated_at\": \"2026-07-15 20:17:08\", \"description\": \"lololo\"}','2026-07-15 13:17:08'),(110,6,'blueprint_scenario',3,'update',NULL,'{\"title\": \"testing\", \"updated_at\": \"2026-07-15 20:17:31\", \"description\": \"lololo\"}','2026-07-15 13:17:31'),(111,6,'blueprint_scenario',3,'update',NULL,'{\"title\": \"testing\", \"updated_at\": \"2026-07-15 20:17:45\", \"description\": \"lololo\"}','2026-07-15 13:17:45'),(112,6,'blueprint_scenario',3,'update',NULL,'{\"title\": \"testing\", \"updated_at\": \"2026-07-15 20:18:02\", \"description\": \"lololo\"}','2026-07-15 13:18:02'),(113,6,'blueprint_scenario',3,'delete',NULL,'{\"title\": \"testing\"}','2026-07-15 13:18:14'),(114,6,'blueprint_scenario',4,'update',NULL,'{\"title\": \"haahah\", \"updated_at\": \"2026-07-15 20:18:21\", \"description\": \"testing\"}','2026-07-15 13:18:21'),(115,6,'blueprint_scenario',4,'update',NULL,'{\"title\": \"haahah\", \"updated_at\": \"2026-07-15 20:42:11\", \"description\": \"testing\"}','2026-07-15 13:42:11'),(116,6,'blueprint_scenario',4,'update',NULL,'{\"title\": \"haahah\", \"updated_at\": \"2026-07-15 20:56:23\", \"description\": \"testing\"}','2026-07-15 13:56:23'),(117,6,'blueprint_scenario',4,'update',NULL,'{\"title\": \"haahah\", \"updated_at\": \"2026-07-15 20:56:34\", \"description\": \"testing\"}','2026-07-15 13:56:34'),(118,6,'blueprint_scenario',4,'update',NULL,'{\"title\": \"haahah\", \"updated_at\": \"2026-07-15 20:57:57\", \"description\": \"testing\"}','2026-07-15 13:57:57'),(119,6,'blueprint_scenario',5,'update',NULL,'{\"title\": \"haha\", \"updated_at\": \"2026-07-15 20:59:23\", \"description\": \"hihi\"}','2026-07-15 13:59:23'),(120,6,'blueprint_scenario',4,'update',NULL,'{\"title\": \"haahah\", \"updated_at\": \"2026-07-15 20:59:34\", \"description\": \"testing\"}','2026-07-15 13:59:34'),(121,6,'blueprint_scenario',4,'update',NULL,'{\"title\": \"haahah\", \"updated_at\": \"2026-07-15 21:00:18\", \"description\": \"testing\"}','2026-07-15 14:00:18'),(122,6,'blueprint_scenario',4,'update',NULL,'{\"title\": \"haahah\", \"updated_at\": \"2026-07-15 21:00:25\", \"description\": \"testing\"}','2026-07-15 14:00:25'),(123,6,'blueprint_scenario',4,'update',NULL,'{\"title\": \"haahah\", \"updated_at\": \"2026-07-15 21:06:12\", \"description\": \"testing\"}','2026-07-15 14:06:12'),(124,6,'blueprint_scenario',4,'update',NULL,'{\"title\": \"haahah\", \"updated_at\": \"2026-07-15 21:06:18\", \"description\": \"testing\"}','2026-07-15 14:06:18'),(125,6,'blueprint_design_page',3,'create',NULL,'{\"title\": \"tesitng\", \"module_id\": 1}','2026-07-15 14:07:23'),(126,6,'blueprint_design_page',3,'update',NULL,'{\"title\": \"tesitng\", \"updated_at\": \"2026-07-15 21:07:31\", \"description\": \"\"}','2026-07-15 14:07:31'),(127,6,'project_comment',2,'add_comment',NULL,'{\"project_id\": 10}','2026-07-15 21:01:38'),(128,6,'blueprint',3,'create',NULL,'{\"name\": \"HRIS OS\", \"improvement_id\": null}','2026-07-16 02:37:08'),(129,6,'blueprint_module',4,'create',NULL,'{\"name\": \"Kontrak\", \"blueprint_id\": 3}','2026-07-16 02:43:35'),(130,6,'blueprint_module',5,'create',NULL,'{\"name\": \"Update Pegawai\", \"blueprint_id\": 3}','2026-07-16 02:43:48'),(131,6,'blueprint_module',6,'create',NULL,'{\"name\": \"Surat Peringatan\", \"blueprint_id\": 3}','2026-07-16 02:43:55'),(132,6,'blueprint_module',7,'create',NULL,'{\"name\": \"Memo Mutasi\", \"blueprint_id\": 3}','2026-07-16 02:44:01'),(133,6,'blueprint_module',8,'create',NULL,'{\"name\": \"Memo Karyawan Keluar\", \"blueprint_id\": 3}','2026-07-16 02:44:10'),(134,6,'blueprint_scenario',6,'create',NULL,'{\"title\": \"Input Kontrak Kerja Pertama\", \"module_id\": 4}','2026-07-16 02:53:39'),(135,6,'blueprint_scenario',7,'create',NULL,'{\"title\": \"Tambah Kontrak Kerja Pegawai\", \"module_id\": 4}','2026-07-16 02:54:44'),(136,6,'blueprint_scenario',8,'create',NULL,'{\"title\": \"Import Kontrak Kerja\", \"module_id\": 4}','2026-07-16 02:59:54'),(137,6,'blueprint_scenario',9,'create',NULL,'{\"title\": \"Update Kontrak Kerja\", \"module_id\": 4}','2026-07-16 03:00:46'),(138,6,'blueprint_design_page',4,'create',NULL,'{\"title\": \"Halaman Kontrak Kerja Pegawai\", \"module_id\": 4}','2026-07-16 03:02:24'),(139,6,'ticket',24,'create_ticket',NULL,'{\"type\": 1, \"title\": \"Test ticket diagnostic\", \"page_id\": null, \"priority\": 1}','2026-07-16 12:14:13'),(140,6,'ticket',25,'create_ticket',NULL,'{\"type\": 0, \"title\": \"halalman error\", \"page_id\": 12, \"priority\": 1}','2026-07-16 12:15:03'),(141,6,'ticket',25,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-16 12:15:31'),(142,6,'ticket',25,'resolve_ticket',NULL,'{\"resolution_note\": \"oke sip\"}','2026-07-16 12:18:40'),(143,6,'ticket',25,'close_ticket',NULL,'{\"closed_at\": \"2026-07-16 19:54:13\"}','2026-07-16 12:54:13'),(144,6,'ticket',25,'reopen_ticket',NULL,'{\"rejection_note\": \"masih gagal\"}','2026-07-16 12:54:34'),(145,6,'ticket',25,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-16 12:54:42'),(146,6,'ticket',25,'resolve_ticket',NULL,'{\"resolution_note\": \"sudah oke sip mantap jiwa\"}','2026-07-16 12:55:01'),(147,6,'blueprint_scenario',6,'update',NULL,'{\"title\": \"Input Kontrak Kerja Pertama\", \"updated_at\": \"2026-07-17 03:51:33\", \"description\": \"\"}','2026-07-16 20:51:33'),(148,6,'ticket',26,'create_ticket',NULL,'{\"type\": 3, \"title\": \"Penambahan Field\", \"page_id\": 12, \"priority\": 1}','2026-07-17 02:16:59'),(149,6,'ticket',26,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-17 02:17:14'),(150,6,'blueprint_page_spec',1,'create',NULL,'{\"module_id\": \"4\", \"field_name\": \"Nama\", \"design_page_id\": 4}','2026-07-17 03:25:59'),(151,6,'ticket',26,'resolve_ticket',NULL,'{\"resolution_note\": \"akljdoalsd\"}','2026-07-17 03:34:36'),(152,6,'ticket',26,'close_ticket',NULL,'{\"closed_at\": \"2026-07-17 10:34:58\"}','2026-07-17 03:34:58'),(153,6,'ticket',25,'close_ticket',NULL,'{\"closed_at\": \"2026-07-17 10:36:08\"}','2026-07-17 03:36:08'),(154,6,'blueprint_design_page',4,'update',NULL,'{\"title\": \"Halaman Kontrak Kerja Pegawai\", \"updated_at\": \"2026-07-17 23:35:45\", \"description\": \"\"}','2026-07-17 16:35:45'),(155,6,'blueprint_design_page',4,'update',NULL,'{\"title\": \"Halaman Kontrak Kerja Pegawai\", \"updated_at\": \"2026-07-17 23:36:25\", \"description\": \"\"}','2026-07-17 16:36:25'),(156,6,'blueprint_design_page',4,'update',NULL,'{\"title\": \"Halaman Kontrak Kerja Pegawai\", \"updated_at\": \"2026-07-18 00:12:23\", \"description\": \"<p>Testing Dan implementasi</p>\"}','2026-07-17 17:12:23'),(157,6,'blueprint_page_spec',1,'update',NULL,'{\"field_name\": \"\", \"updated_at\": \"2026-07-18 00:12:39\"}','2026-07-17 17:12:39'),(158,6,'blueprint_page_spec',1,'update',NULL,'{\"data\": \"\", \"datatype\": \"text\", \"condition\": \"kosong\", \"objective\": \"digunakan untuk input nama\", \"field_name\": \"nama\", \"updated_at\": \"2026-07-18 01:22:33\", \"validation\": \"kosong\", \"control_type\": \"text\", \"initial_data\": \"\", \"input_display\": \"Input\"}','2026-07-17 18:22:33'),(159,6,'ticket',27,'create_ticket',NULL,'{\"type\": 4, \"title\": \"Data surat peringatan\", \"page_id\": 12, \"priority\": 1}','2026-07-17 18:32:19'),(160,6,'ticket',27,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-17 18:32:29'),(161,6,'ticket',27,'resolve_ticket',NULL,'{\"resolution_note\": \"Berikut data yg dibutuhkan\"}','2026-07-17 18:33:49'),(162,6,'ticket',27,'close_ticket',NULL,'{\"closed_at\": \"2026-07-18 01:34:08\"}','2026-07-17 18:34:08'),(163,6,'blueprint',4,'create',NULL,'{\"name\": \"Assignment [PAF]\", \"improvement_id\": null}','2026-07-17 20:58:17'),(164,6,'project',11,'create_improvement',NULL,'{\"name\": \"Penambahan Fitur Upload File Excel pada Memo Mutasi\", \"priority\": 1}','2026-07-18 01:52:28'),(165,6,'blueprint',5,'create',NULL,'{\"name\": \"Import Upload file pada Memo Mutasi\", \"improvement_id\": 11}','2026-07-18 02:35:44'),(166,6,'blueprint_module',9,'create',NULL,'{\"name\": \"Import Memo Mutasi\", \"blueprint_id\": 5}','2026-07-18 02:35:58'),(167,6,'blueprint_design_page',5,'create',NULL,'{\"title\": \"Halaman Upload\", \"module_id\": 9}','2026-07-18 02:36:36'),(168,6,'blueprint_page_spec',2,'create',NULL,'{\"module_id\": \"9\", \"field_name\": \"Upload file\", \"design_page_id\": 5}','2026-07-18 02:37:22'),(169,6,'blueprint_scenario',10,'create',NULL,'{\"title\": \"Upload Template Memo Mutasi\", \"module_id\": 9}','2026-07-19 18:16:03'),(170,6,'ticket',28,'create_ticket',NULL,'{\"type\": 0, \"title\": \"Bug CI pengajuan\", \"page_id\": 19, \"priority\": 1}','2026-07-20 00:59:47'),(171,6,'ticket',28,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-20 01:00:12'),(172,6,'ticket',28,'move_ticket','{\"status\": \"2\"}','{\"status\": 3}','2026-07-20 01:01:26'),(173,6,'ticket_comment',16,'add_comment',NULL,'{\"content\": \"oke\", \"ticket_id\": 28}','2026-07-20 01:02:15'),(174,6,'ticket',28,'close_ticket',NULL,'{\"closed_at\": \"2026-07-20 08:02:19\"}','2026-07-20 01:02:19'),(175,6,'ticket',28,'reopen_ticket',NULL,'{\"rejection_note\": \"ulang\"}','2026-07-20 01:02:50'),(176,6,'ticket',28,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-20 01:02:59'),(177,6,'ticket',28,'resolve_ticket',NULL,'{\"resolution_note\": \"bug di pengjuan ss pada saat memilih SDG karean sdg tidak sesuai\"}','2026-07-20 01:03:48'),(178,6,'ticket',28,'close_ticket',NULL,'{\"closed_at\": \"2026-07-20 08:03:57\"}','2026-07-20 01:03:57'),(179,6,'blueprint',6,'create',NULL,'{\"name\": \"ci-ss\", \"improvement_id\": null}','2026-07-20 01:07:32'),(180,6,'blueprint_module',10,'create',NULL,'{\"name\": \"ss\", \"blueprint_id\": 6}','2026-07-20 01:08:28'),(181,6,'blueprint_scenario',11,'create',NULL,'{\"title\": \"create ss\", \"module_id\": 10}','2026-07-20 01:08:39'),(182,6,'blueprint_design_page',6,'create',NULL,'{\"title\": \"create ss\", \"module_id\": 10}','2026-07-20 01:09:06'),(183,6,'ticket',29,'create_ticket',NULL,'{\"type\": 0, \"title\": \"Uji Coba Error\", \"page_id\": 12, \"priority\": 1}','2026-07-20 01:28:25'),(184,6,'ticket',29,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-20 01:28:54'),(185,6,'ticket',29,'resolve_ticket',NULL,'{\"resolution_note\": \"sudah works\"}','2026-07-20 01:32:03'),(186,6,'ticket',29,'reopen_ticket',NULL,'{\"rejection_note\": \"masih gagal\"}','2026-07-20 01:32:44'),(187,6,'ticket',30,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-20 19:22:28'),(188,6,'ticket',30,'resolve_ticket',NULL,'{\"resolution_note\": \"Sudah selesai, silahkan dicoba kembali\"}','2026-07-20 19:22:48'),(189,6,'blueprint',7,'create',NULL,'{\"name\": \"Ocha\", \"improvement_id\": null}','2026-07-22 02:40:57'),(190,6,'blueprint_module',11,'create',NULL,'{\"name\": \"Jobdesc\", \"blueprint_id\": 7}','2026-07-22 02:41:04'),(191,6,'blueprint_scenario',12,'create',NULL,'{\"title\": \"Input Jobdesc\", \"module_id\": 11}','2026-07-22 02:41:28'),(192,6,'blueprint_scenario',13,'create',NULL,'{\"title\": \"Lock Jobdesc\", \"module_id\": 11}','2026-07-22 02:41:52'),(193,6,'blueprint_design_page',7,'create',NULL,'{\"title\": \"Create (General)\", \"module_id\": 11}','2026-07-22 02:42:23'),(194,6,'blueprint_page_spec',3,'create',NULL,'{\"module_id\": \"11\", \"field_name\": \"Jobcode\", \"design_page_id\": 7}','2026-07-22 02:43:10'),(195,6,'ticket',31,'create_ticket',NULL,'{\"type\": 0, \"title\": \"Rubah rumus jobcode\", \"page_id\": 20, \"priority\": 1}','2026-07-22 02:45:01'),(196,6,'ticket',31,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-22 02:45:40'),(197,6,'ticket',31,'resolve_ticket',NULL,'{\"resolution_note\": \"sudah sesuai rumus\"}','2026-07-22 02:46:29'),(198,6,'ticket',31,'reopen_ticket',NULL,'{\"rejection_note\": \"tidak sesuai\"}','2026-07-22 02:47:12'),(199,6,'ticket',31,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-22 02:47:27'),(200,6,'ticket',31,'resolve_ticket',NULL,'{\"resolution_note\": \"sudah oko\"}','2026-07-22 02:47:38'),(201,6,'ticket_comment',17,'add_comment',NULL,'{\"content\": \"rumus nya seperti apa\", \"ticket_id\": 31}','2026-07-22 02:47:54'),(202,6,'ticket',31,'close_ticket',NULL,'{\"closed_at\": \"2026-07-22 09:48:03\"}','2026-07-22 02:48:03'),(203,NULL,'ticket',37,'public_create',NULL,'{\"type\": 1, \"title\": \"Test after migration fix\", \"tracking_code\": \"TKT-20260728-807F\"}','2026-07-28 08:40:04'),(204,6,'ticket',36,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-28 09:02:52'),(205,6,'ticket_comment',18,'add_comment',NULL,'{\"content\": \"Lalalaa testing\", \"ticket_id\": 36}','2026-07-28 09:03:00'),(206,6,'ticket',36,'resolve_ticket',NULL,'{\"resolution_note\": \"Sudah oke\"}','2026-07-28 09:04:19'),(207,NULL,'ticket',36,'close_ticket',NULL,'{\"status\": 4}','2026-07-28 09:48:14'),(208,6,'blueprint',7,'update',NULL,'{\"name\": \"Ocha\", \"updated_at\": \"2026-07-28 23:48:36\", \"description\": \"Aplikasi untuk create jobdesc, update SO Dan lain-lain\", \"improvement_id\": null}','2026-07-28 16:48:36'),(209,6,'ticket',38,'create_ticket',NULL,'{\"type\": 0, \"title\": \"testing dan implementasi\", \"page_id\": 18, \"priority\": 1}','2026-07-29 01:39:47'),(210,6,'ticket',38,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-29 01:40:00'),(211,6,'ticket',37,'take_ticket','{\"assignee_id\": null}','{\"assignee_id\": 6}','2026-07-29 02:32:58'),(212,6,'blueprint',6,'update',NULL,'{\"name\": \"CI - SS\", \"updated_at\": \"2026-07-29 10:02:28\", \"description\": \"Aplikasi yang digunakan untuk input Sumbang Saran Pegawai\", \"improvement_id\": null}','2026-07-29 03:02:28'),(213,6,'blueprint',7,'update',NULL,'{\"name\": \"OCHA (Organization Charts)\", \"updated_at\": \"2026-07-29 10:02:59\", \"description\": \"Aplikasi untuk create jobdesc, update SO Dan lain-lain\", \"improvement_id\": null}','2026-07-29 03:02:59'),(214,6,'blueprint_scenario',14,'create',NULL,'{\"title\": \"Assign Jobdesc\", \"module_id\": 11}','2026-07-29 03:04:50'),(215,6,'blueprint_design_page',8,'create',NULL,'{\"title\": \"Records\", \"module_id\": 11}','2026-07-29 03:05:59'),(216,6,'blueprint_design_page',9,'create',NULL,'{\"title\": \"Update\", \"module_id\": 11}','2026-07-29 03:06:35'),(217,6,'blueprint_design_page',7,'update',NULL,'{\"title\": \"Create (General)\", \"updated_at\": \"2026-07-29 10:07:19\", \"description\": \"<p>Input data <b>jobcode</b> secara general</p>\"}','2026-07-29 03:07:19'),(218,6,'blueprint',8,'create',NULL,'{\"name\": \"Talent  Review (9box)\", \"improvement_id\": null}','2026-07-29 03:08:39'),(219,6,'blueprint_module',12,'create',NULL,'{\"name\": \"9box\", \"blueprint_id\": 8}','2026-07-29 03:08:52'),(220,6,'blueprint_scenario',15,'create',NULL,'{\"title\": \"Assessment Manager\", \"module_id\": 12}','2026-07-29 03:09:26'),(221,6,'blueprint_scenario',16,'create',NULL,'{\"title\": \"Assessment Vendor\", \"module_id\": 12}','2026-07-29 03:09:52'),(222,6,'blueprint_module',12,'update',NULL,'{\"name\": \"Assessment\", \"updated_at\": \"2026-07-29 10:10:09\"}','2026-07-29 03:10:09'),(223,6,'blueprint_module',13,'create',NULL,'{\"name\": \"Kalibrasi\", \"blueprint_id\": 8}','2026-07-29 03:10:16'),(224,6,'blueprint_design_page',10,'create',NULL,'{\"title\": \"Penilaian (Manager)\", \"module_id\": 12}','2026-07-29 03:11:33'),(225,6,'blueprint_design_page',11,'create',NULL,'{\"title\": \"Penilaian (Vendor)\", \"module_id\": 12}','2026-07-29 03:12:13'),(226,6,'blueprint_design_page',12,'create',NULL,'{\"title\": \"Create Assessment\", \"module_id\": 12}','2026-07-29 03:12:53'),(227,6,'blueprint_design_page',13,'create',NULL,'{\"title\": \"Create Assessment\", \"module_id\": 12}','2026-07-29 03:13:07'),(228,6,'blueprint_scenario',15,'update',NULL,'{\"title\": \"Assessment Manager\", \"updated_at\": \"2026-07-29 10:14:01\", \"description\": \"Proses assessment manager kepada bawahannya .<br>Untuk saat ini penilaian pegawai menggunakan indikator dari career stages\"}','2026-07-29 03:14:01'),(229,6,'blueprint',9,'create',NULL,'{\"name\": \"Succession Planning\", \"improvement_id\": null}','2026-07-29 03:26:39'),(230,6,'blueprint_module',14,'create',NULL,'{\"name\": \"Replacement Table Chart\", \"blueprint_id\": 9}','2026-07-29 03:26:50'),(231,6,'blueprint_scenario',17,'create',NULL,'{\"title\": \"Add or remove successor\", \"module_id\": 14}','2026-07-29 03:29:54'),(232,6,'blueprint_design_page',14,'create',NULL,'{\"title\": \"Replacement Table Chart\", \"module_id\": 14}','2026-07-29 03:30:30'),(233,6,'ticket',39,'create_ticket',NULL,'{\"type\": 3, \"title\": \"Update rumus Rumus\", \"page_id\": 21, \"priority\": 2}','2026-07-29 20:10:12'),(234,6,'ticket',40,'create_ticket',NULL,'{\"type\": 3, \"title\": \"Perubahan pada halaman penilaian vendor\", \"page_id\": 22, \"priority\": 1}','2026-07-30 01:16:59'),(235,6,'project',12,'create_improvement',NULL,'{\"name\": \"perubahan struktur organisasi\", \"priority\": 2}','2026-07-30 18:19:53'),(236,6,'ticket',41,'create_ticket',NULL,'{\"type\": 4, \"title\": \"update penilaian kalibrasi\", \"page_id\": 21, \"priority\": 1}','2026-07-31 02:55:47');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blueprint_approval_requests`
--

DROP TABLE IF EXISTS `blueprint_approval_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blueprint_approval_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `blueprint_id` bigint unsigned NOT NULL,
  `requester_id` bigint unsigned NOT NULL,
  `approver_id` bigint unsigned DEFAULT NULL,
  `stage_sequence` int NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `blueprint_approval_requests_requester_id_foreign` (`requester_id`),
  KEY `blueprint_approval_requests_approver_id_foreign` (`approver_id`),
  KEY `blueprint_id` (`blueprint_id`),
  CONSTRAINT `blueprint_approval_requests_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `blueprint_approval_requests_blueprint_id_foreign` FOREIGN KEY (`blueprint_id`) REFERENCES `blueprints` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blueprint_approval_requests_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blueprint_approval_requests`
--

LOCK TABLES `blueprint_approval_requests` WRITE;
/*!40000 ALTER TABLE `blueprint_approval_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `blueprint_approval_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blueprint_attachments`
--

DROP TABLE IF EXISTS `blueprint_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blueprint_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `blueprint_id` bigint unsigned NOT NULL,
  `module_id` bigint unsigned DEFAULT NULL,
  `section_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `section_id` bigint unsigned NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `stored_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_size` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `blueprint_attachments_uploaded_by_foreign` (`uploaded_by`),
  KEY `blueprint_id` (`blueprint_id`),
  KEY `module_id` (`module_id`),
  KEY `section_type` (`section_type`),
  CONSTRAINT `blueprint_attachments_blueprint_id_foreign` FOREIGN KEY (`blueprint_id`) REFERENCES `blueprints` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blueprint_attachments_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `blueprint_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blueprint_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blueprint_attachments`
--

LOCK TABLES `blueprint_attachments` WRITE;
/*!40000 ALTER TABLE `blueprint_attachments` DISABLE KEYS */;
INSERT INTO `blueprint_attachments` VALUES (1,2,NULL,'business_scenario',4,6,'1-4.webp','1784149218_f6b1d078f7d58f3d.webp','image/webp',73666,'2026-07-15 14:00:18',0),(3,2,NULL,'business_scenario',4,6,'1-6.png','1784149572_12a327a92cf8243d.png','image/png',85384,'2026-07-15 14:06:12',0),(4,2,1,'design_page',3,6,'1-4.webp','1784149643_4839a3fd2aca1911.webp','image/webp',73666,'2026-07-15 14:07:23',0),(6,2,NULL,'design_page',3,6,'1-6.webp','1784149651_e68f87fdb9ea82b3.webp','image/webp',35144,'2026-07-15 14:07:31',0),(7,3,4,'business_scenario',6,6,'Screenshot 2026-07-16 at 16.52.24.png','1784195619_0396472aff5cdcfc.png','image/png',251940,'2026-07-16 02:53:39',0),(8,3,4,'business_scenario',7,6,'Screenshot 2026-07-16 at 16.54.15.png','1784195684_b1d4a8935f1d4b31.png','image/png',366013,'2026-07-16 02:54:44',0),(9,3,4,'business_scenario',8,6,'Screenshot 2026-07-16 at 16.59.24.png','1784195994_84bd824da28afb49.png','image/png',136299,'2026-07-16 02:59:54',0),(10,3,4,'business_scenario',9,6,'Screenshot 2026-07-16 at 17.00.17.png','1784196046_2cff985534c58b8d.png','image/png',191981,'2026-07-16 03:00:46',0),(11,3,4,'design_page',4,6,'Screenshot 2026-07-16 at 17.01.29.png','1784196144_eaae9c68ac294c67.png','image/png',313396,'2026-07-16 03:02:24',0),(12,6,10,'design_page',6,6,'pngegg.png','1784534946_fefc4cd71f72818e.png','image/png',35855,'2026-07-20 01:09:06',0);
/*!40000 ALTER TABLE `blueprint_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blueprint_business_scenarios`
--

DROP TABLE IF EXISTS `blueprint_business_scenarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blueprint_business_scenarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `actors` text COLLATE utf8mb4_general_ci,
  `pre_condition` text COLLATE utf8mb4_general_ci,
  `post_condition` text COLLATE utf8mb4_general_ci,
  `normal_course` text COLLATE utf8mb4_general_ci,
  `exception` text COLLATE utf8mb4_general_ci,
  `frequency` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `issue` text COLLATE utf8mb4_general_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `blueprint_business_scenarios_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `blueprint_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blueprint_business_scenarios`
--

LOCK TABLES `blueprint_business_scenarios` WRITE;
/*!40000 ALTER TABLE `blueprint_business_scenarios` DISABLE KEYS */;
INSERT INTO `blueprint_business_scenarios` VALUES (1,3,'lala','adassd',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-15 03:50:11','2026-07-15 03:50:11',0),(2,2,'Memo Karyawan Masuk (Option A)','lalala',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-15 03:51:26','2026-07-15 03:51:26',0),(4,1,'haahah','testing',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-07-15 12:43:14','2026-07-15 14:06:18',0),(5,1,'haha','hihi',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,'2026-07-15 13:03:32','2026-07-15 13:59:23',0),(6,4,'Input Kontrak Kerja Pertama','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-16 02:53:39','2026-07-16 20:51:33',0),(7,4,'Tambah Kontrak Kerja Pegawai','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-07-16 02:54:44','2026-07-16 02:54:44',0),(8,4,'Import Kontrak Kerja','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,'2026-07-16 02:59:54','2026-07-16 02:59:54',0),(9,4,'Update Kontrak Kerja','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,'2026-07-16 03:00:46','2026-07-16 03:00:46',0),(10,9,'Upload Template Memo Mutasi','<p>Admin os mengupload file sesuai dengan contoh template</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-19 18:16:03','2026-07-19 18:16:03',0),(11,10,'create ss','<p><br></p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-20 01:08:39','2026-07-20 01:08:39',0),(12,11,'Input Jobdesc','<p><br></p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-22 02:41:28','2026-07-22 02:41:28',0),(13,11,'Lock Jobdesc','<p><br></p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-07-22 02:41:52','2026-07-22 02:41:52',0),(14,11,'Assign Jobdesc','<p>Jobcode yang sudah dibuat, dapat di assign ke beberapa jabatan id (HRIS), hal itu bertujuan agar kita bisa menghitung Year Of Position pegawai yang memiliki jabatanid yang berbeda (karena perubahan struktur departemen) walaupun memiliki jobcode yang sama</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,'2026-07-29 03:04:50','2026-07-29 03:04:50',0),(15,12,'Assessment Manager','Proses assessment manager kepada bawahannya .<br>Untuk saat ini penilaian pegawai menggunakan indikator dari career stages',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-29 03:09:26','2026-07-29 03:14:01',0),(16,12,'Assessment Vendor','Input data hasil assessment vendor ke dalam sistem',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-07-29 03:09:52','2026-07-29 03:09:52',0),(17,14,'Add or remove successor','<p>Melakukan perubahan terhadap successor jabatan</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-29 03:29:54','2026-07-29 03:29:54',0);
/*!40000 ALTER TABLE `blueprint_business_scenarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blueprint_comments`
--

DROP TABLE IF EXISTS `blueprint_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blueprint_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `blueprint_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `blueprint_comments_user_id_foreign` (`user_id`),
  KEY `blueprint_id` (`blueprint_id`),
  CONSTRAINT `blueprint_comments_blueprint_id_foreign` FOREIGN KEY (`blueprint_id`) REFERENCES `blueprints` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blueprint_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blueprint_comments`
--

LOCK TABLES `blueprint_comments` WRITE;
/*!40000 ALTER TABLE `blueprint_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `blueprint_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blueprint_design_pages`
--

DROP TABLE IF EXISTS `blueprint_design_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blueprint_design_pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `blueprint_design_pages_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `blueprint_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blueprint_design_pages`
--

LOCK TABLES `blueprint_design_pages` WRITE;
/*!40000 ALTER TABLE `blueprint_design_pages` DISABLE KEYS */;
INSERT INTO `blueprint_design_pages` VALUES (1,2,'testing','laksdlsakd',0,'2026-07-15 04:12:57','2026-07-15 04:12:57',0),(2,1,'Login pages','testing',0,'2026-07-15 06:56:43','2026-07-15 06:56:43',0),(3,1,'tesitng','',1,'2026-07-15 14:07:23','2026-07-15 14:07:31',0),(4,4,'Halaman Kontrak Kerja Pegawai','<p>Testing Dan implementasi</p>',0,'2026-07-16 03:02:24','2026-07-17 17:12:23',0),(5,9,'Halaman Upload','<p>Halaman ini digunakan untuk upload file Dan melakukan checking data</p>',0,'2026-07-18 02:36:36','2026-07-18 02:36:36',0),(6,10,'create ss','<p>create sss</p>',0,'2026-07-20 01:09:06','2026-07-20 01:09:06',0),(7,11,'Create (General)','<p>Input data <b>jobcode</b> secara general</p>',0,'2026-07-22 02:42:23','2026-07-29 03:07:19',0),(8,11,'Records','<p>Halaman yang digunakan untuk menampilkan list jobcode yang sudah di input</p>',1,'2026-07-29 03:05:59','2026-07-29 03:05:59',0),(9,11,'Update','<p>Halaman yang digunakan untuk mengupdate data jobdesc (sebelum di lock) dan melakukan lock jika jobdesc sudah rilis</p>',2,'2026-07-29 03:06:35','2026-07-29 03:06:35',0),(10,12,'Penilaian (Manager)','<p>Halaman yang digunakan untuk menampilkan list pegawai yang \"akan\" dinilai oleh manager, serta daftar pegawai bawahan (tidak langsung)</p>',0,'2026-07-29 03:11:33','2026-07-29 03:11:33',0),(11,12,'Penilaian (Vendor)','<p>Halaman yang digunakan untuk menampilkan list pegawai yang akan di inputkan data hasil penilaian dari vendor</p>',1,'2026-07-29 03:12:13','2026-07-29 03:12:13',0),(12,12,'Create Assessment','<p>Halaman yang digunakan untuk menginput penilaian manager</p>',2,'2026-07-29 03:12:53','2026-07-29 03:12:53',0),(13,12,'Create Assessment','<p>Halaman yang digunakan untuk menginput penilaian vendor</p>',3,'2026-07-29 03:13:07','2026-07-29 03:13:07',0),(14,14,'Replacement Table Chart','<p>Halaman ini digunakan untuk melakukan maintenance (add/remove) successor</p>',0,'2026-07-29 03:30:30','2026-07-29 03:30:30',0);
/*!40000 ALTER TABLE `blueprint_design_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blueprint_modules`
--

DROP TABLE IF EXISTS `blueprint_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blueprint_modules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `blueprint_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `blueprint_id` (`blueprint_id`),
  CONSTRAINT `blueprint_modules_blueprint_id_foreign` FOREIGN KEY (`blueprint_id`) REFERENCES `blueprints` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blueprint_modules`
--

LOCK TABLES `blueprint_modules` WRITE;
/*!40000 ALTER TABLE `blueprint_modules` DISABLE KEYS */;
INSERT INTO `blueprint_modules` VALUES (1,2,'testing','',0,'2026-07-15 03:24:56','2026-07-15 03:24:56',0),(2,1,'testing','',0,'2026-07-15 03:49:45','2026-07-15 03:49:45',0),(3,1,'lalaal','',1,'2026-07-15 03:49:53','2026-07-15 03:49:53',0),(4,3,'Kontrak','',0,'2026-07-16 02:43:35','2026-07-16 02:43:35',0),(5,3,'Update Pegawai','',1,'2026-07-16 02:43:48','2026-07-16 02:43:48',0),(6,3,'Surat Peringatan','',2,'2026-07-16 02:43:55','2026-07-16 02:43:55',0),(7,3,'Memo Mutasi','',3,'2026-07-16 02:44:01','2026-07-16 02:44:01',0),(8,3,'Memo Karyawan Keluar','',4,'2026-07-16 02:44:10','2026-07-16 02:44:10',0),(9,5,'Import Memo Mutasi','',0,'2026-07-18 02:35:58','2026-07-18 02:35:58',0),(10,6,'ss','',0,'2026-07-20 01:08:28','2026-07-20 01:08:28',0),(11,7,'Jobdesc','',0,'2026-07-22 02:41:04','2026-07-22 02:41:04',0),(12,8,'Assessment','',0,'2026-07-29 03:08:52','2026-07-29 03:10:09',0),(13,8,'Kalibrasi','',1,'2026-07-29 03:10:16','2026-07-29 03:10:16',0),(14,9,'Replacement Table Chart','',0,'2026-07-29 03:26:50','2026-07-29 03:26:50',0);
/*!40000 ALTER TABLE `blueprint_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blueprint_page_specifications`
--

DROP TABLE IF EXISTS `blueprint_page_specifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blueprint_page_specifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint unsigned NOT NULL,
  `design_page_id` bigint unsigned NOT NULL,
  `field_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `objective` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `initial_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `condition` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `validation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `input_display` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `datatype` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `control_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ux` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `blueprint_page_specifications_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `blueprint_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blueprint_page_specifications`
--

LOCK TABLES `blueprint_page_specifications` WRITE;
/*!40000 ALTER TABLE `blueprint_page_specifications` DISABLE KEYS */;
INSERT INTO `blueprint_page_specifications` VALUES (1,4,4,'nama','','digunakan untuk input nama','','kosong','kosong','Input','text','text','',0,'2026-07-17 03:25:59','2026-07-17 18:22:33',0),(2,9,5,'Upload file','upload','Digunakan untuk memilih file yang akan di upload','kosong','','Sesuai dengan master data','Input','excel','uploadfile',NULL,0,'2026-07-18 02:37:22','2026-07-18 02:37:22',0),(3,11,7,'Jobcode','varchar','jobcode bisa terisi','','','','Input','text','text',NULL,0,'2026-07-22 02:43:10','2026-07-22 02:43:10',0);
/*!40000 ALTER TABLE `blueprint_page_specifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blueprints`
--

DROP TABLE IF EXISTS `blueprints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blueprints` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `improvement_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `actors` text COLLATE utf8mb4_general_ci,
  `pre_condition` text COLLATE utf8mb4_general_ci,
  `post_condition` text COLLATE utf8mb4_general_ci,
  `normal_course` text COLLATE utf8mb4_general_ci,
  `exception` text COLLATE utf8mb4_general_ci,
  `frequency` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `issue` text COLLATE utf8mb4_general_ci,
  `status` tinyint NOT NULL DEFAULT '0',
  `approver_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `blueprints_approver_id_foreign` (`approver_id`),
  KEY `blueprints_created_by_foreign` (`created_by`),
  KEY `status` (`status`),
  KEY `improvement_id` (`improvement_id`),
  CONSTRAINT `blueprints_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `blueprints_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `blueprints_improvement_id_foreign` FOREIGN KEY (`improvement_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blueprints`
--

LOCK TABLES `blueprints` WRITE;
/*!40000 ALTER TABLE `blueprints` DISABLE KEYS */;
INSERT INTO `blueprints` VALUES (1,9,'testing','testing Dan implementasi',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,99,NULL,6,'2026-07-14 15:51:54','2026-07-14 15:51:54',1),(2,10,'testing','asdlasdlaskdjnsakd',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,99,NULL,6,'2026-07-15 03:18:16','2026-07-15 03:18:16',1),(3,NULL,'HRIS OS','Untuk administrasi sistem HRIS',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,6,'2026-07-16 02:37:08','2026-07-16 02:37:08',0),(4,NULL,'Assignment [PAF]','Sistem penugasan pegawai',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,6,'2026-07-17 20:58:17','2026-07-17 20:58:17',0),(5,11,'Import Upload file pada Memo Mutasi','Digunakan untuk memudahkan admin OS untuk untut memo mutasi berisi perpindahan regu Dan nomor urut',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,6,'2026-07-18 02:35:44','2026-07-18 02:35:44',0),(6,NULL,'CI - SS','Aplikasi yang digunakan untuk input Sumbang Saran Pegawai',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,6,'2026-07-20 01:07:32','2026-07-29 03:02:28',0),(7,NULL,'OCHA (Organization Charts)','Aplikasi untuk create jobdesc, update SO Dan lain-lain',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,6,'2026-07-22 02:40:57','2026-07-29 03:02:59',0),(8,NULL,'Talent  Review (9box)','Digunakan untuk melakukan talent review pegawai',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,6,'2026-07-29 03:08:39','2026-07-29 03:08:39',0),(9,NULL,'Succession Planning','Digunakan untuk assign succession plan jabatan',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,6,'2026-07-29 03:26:39','2026-07-29 03:26:39',0);
/*!40000 ALTER TABLE `blueprints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_projects`
--

DROP TABLE IF EXISTS `master_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `master_projects_created_by_foreign` (`created_by`),
  CONSTRAINT `master_projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_projects`
--

LOCK TABLES `master_projects` WRITE;
/*!40000 ALTER TABLE `master_projects` DISABLE KEYS */;
INSERT INTO `master_projects` VALUES (7,'Career Backend','Aplikasi backend aplikasi',1,6,'2026-07-13 01:12:57','2026-07-13 01:12:57',1),(8,'Personnel Administration Form','',1,6,'2026-07-15 23:07:55','2026-07-15 23:07:55',0),(9,'Hiring Management','Proses Recruitment',1,6,'2026-07-15 23:08:07','2026-07-16 02:26:34',0),(10,'Development &amp; Learning Management','',1,6,'2026-07-15 23:41:53','2026-07-16 02:25:48',0),(11,'Performance Evaluation','',1,6,'2026-07-16 02:26:52','2026-07-16 02:26:52',0),(12,'Self Service','',1,6,'2026-07-16 02:27:05','2026-07-16 02:27:05',0),(13,'HRIS OS','',1,6,'2026-07-16 02:27:21','2026-07-16 02:27:21',0),(14,'CI-SS','CI-SS',1,6,'2026-07-20 00:56:42','2026-07-20 00:56:42',0),(15,'Ocha (Organization Chart)','',1,6,'2026-07-22 02:37:49','2026-07-22 02:37:49',0);
/*!40000 ALTER TABLE `master_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_user_types`
--

DROP TABLE IF EXISTS `master_user_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_user_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `active` tinyint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_master_user_types_name` (`name`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_user_types`
--

LOCK TABLES `master_user_types` WRITE;
/*!40000 ALTER TABLE `master_user_types` DISABLE KEYS */;
INSERT INTO `master_user_types` VALUES (1,'Admin Departemen','Administrator departemen yang mengelola sistem',1,'2026-07-28 17:23:24','2026-07-28 17:23:24'),(2,'Kepala Departemen','Kepala departemen yang melakukan approval',1,'2026-07-28 17:23:24','2026-07-28 17:23:24'),(3,'User (Sistem)','Pengguna sistem yang menggunakan aplikasi',1,'2026-07-28 17:23:24','2026-07-28 17:23:24');
/*!40000 ALTER TABLE `master_user_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026-07-02-000001','App\\Database\\Migrations\\CreateUsers','default','App',1782939354,1),(2,'2026-07-02-000002','App\\Database\\Migrations\\CreateProjects','default','App',1782939354,1),(3,'2026-07-02-000003','App\\Database\\Migrations\\CreateApprovalRequests','default','App',1782939354,1),(4,'2026-07-02-000004','App\\Database\\Migrations\\CreateProjectComments','default','App',1782939354,1),(5,'2026-07-02-000005','App\\Database\\Migrations\\CreateTickets','default','App',1782939354,1),(6,'2026-07-02-000006','App\\Database\\Migrations\\CreateTicketComments','default','App',1782939354,1),(7,'2026-07-02-000007','App\\Database\\Migrations\\CreateAuditLogs','default','App',1782939354,1),(8,'2026-07-02-000008','App\\Database\\Migrations\\CreateApiKeys','default','App',1782939354,1),(9,'2026-07-02-000009','App\\Database\\Migrations\\CreateTicketAttachments','default','App',1783007695,2),(10,'2026-07-02-000010','App\\Database\\Migrations\\CreateMasterProjects','default','App',1783010739,3),(11,'2026-07-02-000011','App\\Database\\Migrations\\AddPageIdToTickets','default','App',1783010739,3),(12,'2026-07-02-000012','App\\Database\\Migrations\\AddProjectsFields','default','App',1783725194,4),(13,'2026-07-02-000013','App\\Database\\Migrations\\CreateProjectAttachments','default','App',1783725194,4),(14,'2026-07-12-000001','App\\Database\\Migrations\\CreateRoles','default','App',1783811701,5),(15,'2026-07-12-000002','App\\Database\\Migrations\\CreateAccessModules','default','App',1783811701,5),(16,'2026-07-12-000003','App\\Database\\Migrations\\CreateRolePermissions','default','App',1783811701,5),(17,'2026-07-12-000004','App\\Database\\Migrations\\AddRoleIdToUsers','default','App',1783811701,5),(18,'2026-07-12-000005','App\\Database\\Migrations\\AddTrackingCodeToTickets','default','App',1783854279,6),(19,'2026-07-12-000006','App\\Database\\Migrations\\DropLegacyRoleColumn','default','App',1783878345,7),(20,'2026-07-13-000001','App\\Database\\Migrations\\RestructureUsersForExternalAuth','default','App',1783912001,8),(21,'2026-07-15-000001','App\\Database\\Migrations\\CreateBlueprints','default','App',1784050859,9),(22,'2026-07-15-000002','App\\Database\\Migrations\\CreateBlueprintModules','default','App',1784050859,9),(23,'2026-07-15-000003','App\\Database\\Migrations\\CreateBlueprintBusinessScenarios','default','App',1784050859,9),(24,'2026-07-15-000004','App\\Database\\Migrations\\CreateBlueprintDesignPages','default','App',1784050859,9),(25,'2026-07-15-000005','App\\Database\\Migrations\\CreateBlueprintPageSpecifications','default','App',1784050859,9),(26,'2026-07-15-000006','App\\Database\\Migrations\\CreateBlueprintAttachments','default','App',1784050859,9),(27,'2026-07-15-000007','App\\Database\\Migrations\\CreateBlueprintApprovalRequests','default','App',1784050859,9),(28,'2026-07-15-000008','App\\Database\\Migrations\\CreateBlueprintComments','default','App',1784050859,9),(29,'2026-07-15-000001','App\\Database\\Migrations\\AddApprovalToTickets','default','App',1784113922,10),(30,'2026-07-15-000009','App\\Database\\Migrations\\AddDesignPageIdToPageSpecifications','default','App',1784142397,11),(31,'2026-07-15-000010','App\\Database\\Migrations\\FixModuleIdNullableInAttachments','default','App',1784145644,12),(32,'2026-07-17-000001','App\\Database\\Migrations\\AddActiveToAllTables','default','App',1784177316,13),(33,'2026-07-17-000002','App\\Database\\Migrations\\BlueprintImprovementOptional','default','App',1784194543,14),(34,'2026-07-17-000003','App\\Database\\Migrations\\AddBlueprintModuleIdToModules','default','App',1784195907,15),(35,'2026-07-15-000002','App\\Database\\Migrations\\AddApproverIdToTickets','default','App',1784227931,16),(37,'2026-07-17-000004','App\\Database\\Migrations\\AddBlueprintDesignPageIdToPages','default','App',1784253954,17),(38,'2026-07-18-000001','App\\Database\\Migration\\CreateModuleBlueprintModules','default','App',1784374182,18),(39,'2026-07-21-000001','App\\Database\\Migrations\\AddIsAnonymousToTickets','default','App',1784590849,19),(40,'2026-07-28-000001','App\\Database\\Migrations\\FixAuditLogsUserIdNullable','default','App',1785253171,20),(41,'2026-07-29-000001','App\\Database\\Migrations\\CreateMasterUserTypes','default','App',1785284604,21);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_blueprint_modules`
--

DROP TABLE IF EXISTS `module_blueprint_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_blueprint_modules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint unsigned NOT NULL,
  `blueprint_module_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  KEY `blueprint_module_id` (`blueprint_module_id`),
  CONSTRAINT `module_blueprint_modules_blueprint_module_id_foreign` FOREIGN KEY (`blueprint_module_id`) REFERENCES `blueprint_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `module_blueprint_modules_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_blueprint_modules`
--

LOCK TABLES `module_blueprint_modules` WRITE;
/*!40000 ALTER TABLE `module_blueprint_modules` DISABLE KEYS */;
INSERT INTO `module_blueprint_modules` VALUES (1,9,5,'2026-07-18 11:29:42'),(2,10,6,'2026-07-18 11:29:42'),(3,11,7,'2026-07-18 11:29:42'),(4,8,9,'2026-07-18 11:29:42'),(8,8,4,'2026-07-18 04:33:48'),(9,14,10,'2026-07-20 01:09:37'),(10,16,11,'2026-07-22 02:43:38'),(11,17,12,'2026-07-29 03:31:43'),(12,17,13,'2026-07-29 03:32:08');
/*!40000 ALTER TABLE `module_blueprint_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `master_project_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `blueprint_module_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `modules_master_project_id_foreign` (`master_project_id`),
  KEY `modules_blueprint_module_id_foreign` (`blueprint_module_id`),
  CONSTRAINT `modules_blueprint_module_id_foreign` FOREIGN KEY (`blueprint_module_id`) REFERENCES `blueprint_modules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `modules_master_project_id_foreign` FOREIGN KEY (`master_project_id`) REFERENCES `master_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (7,7,'Posting Jobs','',1,NULL,'2026-07-13 01:19:36','2026-07-14 09:21:58',0),(8,13,'Kontrak Pegawai','',1,9,'2026-07-16 02:27:35','2026-07-18 03:39:27',0),(9,13,'Update Data Pegawai','',2,5,'2026-07-16 02:28:13','2026-07-17 03:33:41',0),(10,13,'Surat Peringatan','',3,6,'2026-07-16 02:28:21','2026-07-17 03:33:45',0),(11,13,'Memo Mutasi','',4,7,'2026-07-16 02:28:29','2026-07-17 03:33:31',0),(12,13,'Memo Karyawan Keluar','',5,NULL,'2026-07-16 02:28:39','2026-07-18 03:40:43',0),(13,8,'Penugasan','',1,NULL,'2026-07-19 23:15:04','2026-07-19 23:15:04',0),(14,14,'Sumbang Saran','Sumbang Saran',1,NULL,'2026-07-20 00:57:27','2026-07-20 00:57:27',0),(15,9,'Form Penilaian Karyawan Tetap','',1,NULL,'2026-07-22 02:37:17','2026-07-22 02:37:17',0),(16,15,'Jobdesc','',1,NULL,'2026-07-22 02:38:19','2026-07-22 02:38:19',0),(17,10,'Talent Review','Untuk melakukan talent review pegawai',1,NULL,'2026-07-29 03:31:22','2026-07-29 03:31:22',0);
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint unsigned NOT NULL,
  `blueprint_design_page_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `url_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pages_module_id_foreign` (`module_id`),
  KEY `pages_blueprint_design_page_id_foreign` (`blueprint_design_page_id`),
  CONSTRAINT `pages_blueprint_design_page_id_foreign` FOREIGN KEY (`blueprint_design_page_id`) REFERENCES `blueprint_design_pages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pages_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (9,7,NULL,'TMS (Admin)','ninebox/dashboard','testing',1,'2026-07-13 02:00:45','2026-07-13 02:00:45',0),(10,7,NULL,'Dashboard','ninebox/dashboard','',2,'2026-07-13 02:06:50','2026-07-13 02:06:50',0),(11,7,NULL,'Penilaian','ninebox/penilaian','',3,'2026-07-13 02:22:15','2026-07-13 02:22:15',0),(12,8,4,'Input Kontrak Pegawai','/kontrak','',1,'2026-07-16 03:46:25','2026-07-17 02:30:50',0),(13,13,NULL,'Create Penugasan','assignment/create','',1,'2026-07-19 23:44:35','2026-07-19 23:44:35',0),(14,13,NULL,'List Posted','assignment/posted','',2,'2026-07-19 23:44:57','2026-07-19 23:44:57',0),(15,13,NULL,'List Need Approval','assignment/need-approval','',3,'2026-07-19 23:45:27','2026-07-19 23:45:27',0),(16,13,NULL,'List Completed','assignment/approved','',4,'2026-07-19 23:45:54','2026-07-19 23:45:54',0),(17,13,NULL,'Display Penugasan','assignment/display','',5,'2026-07-19 23:46:07','2026-07-19 23:46:07',0),(18,13,NULL,'Approval','assignment/approval','',6,'2026-07-19 23:46:21','2026-07-19 23:46:21',0),(19,14,6,'create ss','/ss/create','',1,'2026-07-20 00:57:57','2026-07-20 01:10:00',0),(20,16,7,'Create (General)','/jobdesc/create','',1,'2026-07-22 02:38:38','2026-07-22 02:43:49',0),(21,17,10,'Penilaian (Manager)',NULL,'<p>Halaman yang digunakan untuk menampilkan list pegawai yang \"akan\" dinilai oleh manager, serta daftar pegawai bawahan (tidak langsung)</p>',0,'2026-07-29 04:05:30','2026-07-29 04:05:30',0),(22,17,11,'Penilaian (Vendor)',NULL,'<p>Halaman yang digunakan untuk menampilkan list pegawai yang akan di inputkan data hasil penilaian dari vendor</p>',1,'2026-07-29 04:05:30','2026-07-29 04:05:30',0),(23,17,12,'Create Assessment',NULL,'<p>Halaman yang digunakan untuk menginput penilaian manager</p>',2,'2026-07-29 04:05:30','2026-07-29 04:05:30',0),(24,17,13,'Create Assessment',NULL,'<p>Halaman yang digunakan untuk menginput penilaian vendor</p>',3,'2026-07-29 04:05:30','2026-07-29 04:05:30',0);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_attachments`
--

DROP TABLE IF EXISTS `project_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `stored_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_size` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `project_attachments_project_id_foreign` (`project_id`),
  KEY `project_attachments_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `project_attachments_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_attachments`
--

LOCK TABLES `project_attachments` WRITE;
/*!40000 ALTER TABLE `project_attachments` DISABLE KEYS */;
INSERT INTO `project_attachments` VALUES (3,9,6,'1-2.webp','1784049192_956f475c37c78208.webp','image/webp',74638,'2026-07-14 10:13:12',0),(4,10,6,'1-5.webp','1784110671_86c4c71d25fda558.webp','image/webp',43192,'2026-07-15 03:17:51',0);
/*!40000 ALTER TABLE `project_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_comments`
--

DROP TABLE IF EXISTS `project_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `project_comments_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_comments`
--

LOCK TABLES `project_comments` WRITE;
/*!40000 ALTER TABLE `project_comments` DISABLE KEYS */;
INSERT INTO `project_comments` VALUES (1,9,6,'oke sip','2026-07-14 10:14:22',0),(2,10,6,'testing','2026-07-15 21:01:38',0);
/*!40000 ALTER TABLE `project_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_user_types`
--

DROP TABLE IF EXISTS `project_user_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_user_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `user_type_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_put_project_type` (`project_id`,`user_type_id`),
  KEY `fk_put_user_type` (`user_type_id`),
  CONSTRAINT `fk_put_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_put_user_type` FOREIGN KEY (`user_type_id`) REFERENCES `master_user_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_user_types`
--

LOCK TABLES `project_user_types` WRITE;
/*!40000 ALTER TABLE `project_user_types` DISABLE KEYS */;
INSERT INTO `project_user_types` VALUES (1,12,2,'2026-07-30 18:19:52');
/*!40000 ALTER TABLE `project_user_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `business_case` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `priority` tinyint NOT NULL DEFAULT '1',
  `assignee_id` bigint unsigned DEFAULT NULL,
  `approver_id` bigint unsigned DEFAULT NULL,
  `page_id` bigint unsigned DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `frekuensi_penggunaan` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `situasi_terkini` text COLLATE utf8mb4_general_ci,
  `ada_data_dianalisa` tinyint unsigned DEFAULT '0',
  `jenis_data_analisa` text COLLATE utf8mb4_general_ci,
  `tujuan_analisa` text COLLATE utf8mb4_general_ci,
  `dampak_manfaat` text COLLATE utf8mb4_general_ci,
  `status` tinyint NOT NULL DEFAULT '0',
  `approval_workflow` json DEFAULT NULL,
  `dept_head_id` bigint unsigned DEFAULT NULL,
  `it_manager_id` bigint unsigned DEFAULT NULL,
  `estimated_start` timestamp NULL DEFAULT NULL,
  `estimated_end` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `projects_created_by_foreign` (`created_by`),
  KEY `status` (`status`),
  KEY `priority` (`priority`),
  CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (9,'testing dan implementasi','alalalaskdskaldjasd','popolsakdjsladjsakldas','Performance',1,NULL,NULL,NULL,'2026-07-15',NULL,NULL,0,NULL,NULL,NULL,99,'{\"stages\": [\"it_manager\", \"dept_head\"]}',NULL,NULL,NULL,NULL,6,'2026-07-14 10:13:12','2026-07-14 10:14:00',1),(10,'tstinalskdjsaldjalskdj','lakjdlajdlsakdj','alksdslkadjsad','Performance',2,NULL,NULL,NULL,'2026-07-15',NULL,NULL,0,NULL,NULL,NULL,99,'{\"stages\": [\"it_manager\", \"dept_head\"]}',NULL,NULL,NULL,NULL,6,'2026-07-15 03:17:51','2026-07-15 03:17:51',1),(11,'Penambahan Fitur Upload File Excel pada Memo Mutasi','Digunakan untuk Admin OS','Admin OS mengupload file Execel, hasil upload akan di verifikasi oleh tim PW','New Feature',1,NULL,NULL,NULL,'2026-07-31',NULL,NULL,0,NULL,NULL,NULL,0,'{\"stages\": [\"it_manager\", \"dept_head\"]}',NULL,NULL,NULL,NULL,6,'2026-07-18 01:52:28','2026-07-18 01:52:28',0),(12,'perubahan struktur organisasi','testing dan implementasi saja','perubahan struktur organisasi dan impelmentasi','Other',2,NULL,8,NULL,'2026-08-03','Harian','lala lilili',1,'data karyawan','optimasi','xxxx',0,'{\"stages\": [\"it_manager\", \"dept_head\"]}',NULL,NULL,NULL,NULL,6,'2026-07-30 18:19:52','2026-07-30 18:19:52',0);
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `module_slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT '0',
  `can_create` tinyint(1) NOT NULL DEFAULT '0',
  `can_update` tinyint(1) NOT NULL DEFAULT '0',
  `can_delete` tinyint(1) NOT NULL DEFAULT '0',
  `can_approve` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id_module_slug` (`role_id`,`module_slug`),
  KEY `role_id` (`role_id`),
  KEY `module_slug` (`module_slug`),
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,5,'dashboard',1,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(2,5,'tickets',1,1,1,1,1,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(3,5,'improvements',1,1,1,1,1,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(4,5,'approvals',1,0,0,0,1,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(5,5,'master_projects',1,1,1,1,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(6,5,'users',1,1,1,1,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(7,5,'roles',1,1,1,1,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(8,4,'dashboard',1,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(9,4,'tickets',1,1,1,0,1,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(10,4,'improvements',1,1,1,0,1,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(11,4,'approvals',1,0,0,0,1,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(12,4,'master_projects',1,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(13,4,'users',1,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(14,4,'roles',1,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(15,3,'dashboard',1,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(16,3,'tickets',1,1,0,0,1,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(17,3,'improvements',1,1,0,0,1,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(18,3,'approvals',1,0,0,0,1,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(19,3,'master_projects',0,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(20,3,'users',0,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(21,3,'roles',0,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(22,1,'dashboard',1,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(23,1,'tickets',1,1,1,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(24,1,'improvements',1,1,1,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(25,1,'approvals',0,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(26,1,'master_projects',1,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(27,1,'users',0,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(28,1,'roles',0,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(29,2,'dashboard',1,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(30,2,'tickets',1,1,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(31,2,'improvements',1,1,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(32,2,'approvals',0,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(33,2,'master_projects',0,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(34,2,'users',0,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(35,2,'roles',0,0,0,0,0,'2026-07-11 16:15:19','2026-07-11 16:15:19',0),(36,5,'blueprints',1,1,1,1,1,'2026-07-14 10:41:15','2026-07-14 10:41:15',0),(37,4,'blueprints',1,0,0,0,1,'2026-07-14 10:41:15','2026-07-14 10:41:15',0),(38,3,'blueprints',1,0,0,0,1,'2026-07-14 10:41:15','2026-07-14 10:41:15',0),(39,1,'blueprints',1,1,1,0,0,'2026-07-14 10:41:15','2026-07-14 10:41:15',0),(40,2,'blueprints',1,0,0,0,0,'2026-07-14 10:41:15','2026-07-14 10:41:15',0);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Developer','developer','Development team member',1,1,'2026-07-11 16:15:13','2026-07-11 16:15:13',0),(2,'Requester','requester','End user who submits tickets',1,1,'2026-07-11 16:15:13','2026-07-11 16:15:13',0),(3,'Dept Head','dept_head','Department head approver',1,1,'2026-07-11 16:15:13','2026-07-11 16:15:13',0),(4,'IT Manager','it_manager','IT department manager',1,1,'2026-07-11 16:15:13','2026-07-11 16:15:13',0),(5,'Admin','admin','Full system administrator',1,1,'2026-07-11 16:15:13','2026-07-11 16:15:13',0);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_attachments`
--

DROP TABLE IF EXISTS `ticket_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `comment_id` bigint unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `stored_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_size` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ticket_attachments_ticket_id_foreign` (`ticket_id`),
  KEY `ticket_attachments_comment_id_foreign` (`comment_id`),
  KEY `ticket_attachments_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `ticket_attachments_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `ticket_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ticket_attachments_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ticket_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_attachments`
--

LOCK TABLES `ticket_attachments` WRITE;
/*!40000 ALTER TABLE `ticket_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_comments`
--

DROP TABLE IF EXISTS `ticket_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  CONSTRAINT `ticket_comments_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_comments`
--

LOCK TABLES `ticket_comments` WRITE;
/*!40000 ALTER TABLE `ticket_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tracking_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `referral` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `type` tinyint NOT NULL DEFAULT '0',
  `needs_approval` tinyint DEFAULT '0',
  `priority` tinyint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '0',
  `closed_at` timestamp NULL DEFAULT NULL,
  `assignee_id` bigint unsigned DEFAULT NULL,
  `creator_id` bigint unsigned DEFAULT NULL,
  `is_anonymous` tinyint unsigned NOT NULL DEFAULT '0',
  `approver_id` bigint unsigned DEFAULT NULL,
  `resolution_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `rejection_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `due_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `page_id` bigint unsigned DEFAULT NULL,
  `active` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_code` (`tracking_code`),
  KEY `tickets_creator_id_foreign` (`creator_id`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `priority` (`priority`),
  KEY `closed_at` (`closed_at`),
  KEY `assignee_id` (`assignee_id`),
  KEY `idx_anon_active` (`is_anonymous`,`active`),
  CONSTRAINT `tickets_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `tickets_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (39,'TKT-20260730-1B72',NULL,'Update rumus Rumus','Perubahan dari menggunakan career stages menjadi agility',3,0,2,0,NULL,NULL,6,0,NULL,NULL,NULL,'2026-07-29 17:00:00','2026-07-29 20:10:12','2026-07-29 20:10:12',21,0),(40,'TKT-20260730-393B',NULL,'Perubahan pada halaman penilaian vendor','menyesuaikan rumus',3,0,1,0,NULL,NULL,6,0,NULL,NULL,NULL,'2026-07-29 17:00:00','2026-07-30 01:16:59','2026-07-30 01:16:59',22,0),(41,'TKT-20260731-604C',NULL,'update penilaian kalibrasi','testing dan implementasi',4,0,1,0,NULL,NULL,6,0,NULL,NULL,NULL,'2026-07-30 17:00:00','2026-07-31 02:55:47','2026-07-31 02:55:47',21,0);
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (6,'155','Andrian Chandra Irawan','andrian.chandra@external.local',5,1,'2026-07-13 03:35:59','2026-08-12 20:48:47'),(7,'225','recruitment.executive','recruitment.executive@external.local',2,1,'2026-07-13 00:38:36','2026-07-19 07:51:31'),(8,'199','haidar.muhammad','haidar.muhammad@external.local',2,1,'2026-07-13 01:09:37','2026-07-30 18:20:09'),(9,'14','Oktrilia Frida','14@external.local',3,1,'2026-07-13 01:10:02','2026-07-13 01:10:02'),(10,'207','Amartha Anindita','207@external.local',2,1,'2026-07-13 21:51:31','2026-07-13 21:52:49'),(11,'4','Alvionita Yeremia','4@external.local',2,1,'2026-07-20 01:36:25','2026-07-20 01:36:25'),(12,'1','Yuana Natalia Wijaya','yuana.natalia@external.local',4,1,'2026-07-30 18:21:52','2026-07-30 18:22:11');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-18  9:58:00
