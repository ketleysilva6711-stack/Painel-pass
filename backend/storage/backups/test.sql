mysqldump: Deprecated program name. It will be removed in a future release, use '/usr/bin/mariadb-dump' instead
/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.8-MariaDB, for Linux (x86_64)
--
-- Host: mysql    Database: the_vault
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `bank_injects`
--

DROP TABLE IF EXISTS `bank_injects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_injects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inject_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enabled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_injects_user_id_foreign` (`user_id`),
  CONSTRAINT `bank_injects_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_injects`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `bank_injects` WRITE;
/*!40000 ALTER TABLE `bank_injects` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_injects` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `bypass_jobs`
--

DROP TABLE IF EXISTS `bypass_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bypass_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `apk_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `output_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','processing','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `progress` int NOT NULL DEFAULT '0',
  `phase` enum('decompile','patch','recompile','sign') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log` text COLLATE utf8mb4_unicode_ci,
  `target_package` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apk_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bypass_jobs_user_id_foreign` (`user_id`),
  CONSTRAINT `bypass_jobs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bypass_jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `bypass_jobs` WRITE;
/*!40000 ALTER TABLE `bypass_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `bypass_jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `bypass_rules`
--

DROP TABLE IF EXISTS `bypass_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bypass_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rule_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_package` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apk_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `output_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `progress` int NOT NULL DEFAULT '0',
  `phase` enum('decompile','patch','recompile','sign','completed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log` text COLLATE utf8mb4_unicode_ci,
  `apk_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `apk_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bypass_rules_user_id_foreign` (`user_id`),
  KEY `bypass_rules_apk_id_foreign` (`apk_id`),
  CONSTRAINT `bypass_rules_apk_id_foreign` FOREIGN KEY (`apk_id`) REFERENCES `play_fake_apps` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bypass_rules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bypass_rules`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `bypass_rules` WRITE;
/*!40000 ALTER TABLE `bypass_rules` DISABLE KEYS */;
INSERT INTO `bypass_rules` VALUES
(2,NULL,1,NULL,'com.example.app',NULL,NULL,'failed',0,'decompile','APK original não encontrado',NULL,'2026-09-27 20:41:01','2026-09-27 20:41:05',4),
(3,NULL,1,NULL,'com.example.app',NULL,NULL,'failed',0,'decompile','APK original não encontrado',NULL,'2026-09-27 20:42:23','2026-09-27 20:42:26',4),
(4,NULL,1,NULL,'com.example.app',NULL,NULL,'failed',0,'decompile','APK original não encontrado',NULL,'2026-09-27 20:45:38','2026-09-27 20:45:39',4),
(5,NULL,1,NULL,'com.example.app','apks/1790531211_ffffffffff.apk',NULL,'failed',0,'decompile','APK original não encontrado','1790531211_ffffffffff.apk','2026-09-27 20:46:09','2026-09-27 20:46:14',4),
(6,NULL,1,NULL,'com.example.app','apks/1790531211_ffffffffff.apk',NULL,'failed',0,'decompile','APK original não encontrado: apks/1790531211_ffffffffff.apk','1790531211_ffffffffff.apk','2026-09-27 20:47:35','2026-09-27 20:47:38',4),
(7,NULL,1,NULL,'com.example.app','apks/1790531211_ffffffffff.apk','/var/www/html/storage/app/public/bypass_output/test_signed.apk','completed',100,'completed','Erro: Falha ao recompilar APK - arquivo de saída não gerado','1790531211_ffffffffff.apk','2026-09-27 20:49:00','2026-09-27 20:53:14',4);
/*!40000 ALTER TABLE `bypass_rules` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `consultas`
--

DROP TABLE IF EXISTS `consultas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resultado` json DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consultas_user_id_foreign` (`user_id`),
  CONSTRAINT `consultas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consultas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `consultas` WRITE;
/*!40000 ALTER TABLE `consultas` DISABLE KEYS */;
INSERT INTO `consultas` VALUES
(1,3,'CPF/CNPJ','123.456.789-00','{\"data\": \"2026-09-27\", \"nome\": \"CONSULTA REALIZADA COM SUCESSO\", \"status\": \"REGULAR\"}','172.18.0.1','2026-09-27 19:05:45','2026-09-27 19:05:45'),
(2,3,'CPF/CNPJ','987.654.321-00','{\"data\": \"2026-09-27\", \"nome\": \"CONSULTA REALIZADA COM SUCESSO\", \"status\": \"REGULAR\"}','172.18.0.1','2026-09-27 19:12:50','2026-09-27 19:12:50');
/*!40000 ALTER TABLE `consultas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
INSERT INTO `failed_jobs` VALUES
(1,'f1629d2c-690e-4fa9-9b16-9c4358fcd97f','database','default','{\"uuid\":\"f1629d2c-690e-4fa9-9b16-9c4358fcd97f\",\"displayName\":\"App\\\\Jobs\\\\ProcessBypassJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcessBypassJob\",\"command\":\"O:25:\\\"App\\\\Jobs\\\\ProcessBypassJob\\\":3:{s:13:\\\"\\u0000*\\u0000bypassRule\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:21:\\\"App\\\\Models\\\\BypassRule\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:15:\\\"\\u0000*\\u0000keystorePath\\\";s:31:\\\"\\/app\\/keystore\\/thevault.keystore\\\";s:5:\\\"queue\\\";s:7:\\\"default\\\";}\",\"batchId\":null},\"createdAt\":1790542140,\"delay\":null}','Error: Class \"App\\Jobs\\RecursiveIteratorIterator\" not found in /var/www/html/app/Jobs/ProcessBypassJob.php:190\nStack trace:\n#0 /var/www/html/app/Jobs/ProcessBypassJob.php(140): App\\Jobs\\ProcessBypassJob->cleanupDirectory(\'/var/www/html/s...\')\n#1 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): App\\Jobs\\ProcessBypassJob->handle()\n#2 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#3 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#4 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#5 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/Container.php(803): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#6 /var/www/html/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(136): Illuminate\\Container\\Container->call(Array)\n#7 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\ProcessBypassJob))\n#8 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessBypassJob))\n#9 /var/www/html/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(140): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#10 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(159): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\ProcessBypassJob), false)\n#11 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\ProcessBypassJob))\n#12 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\ProcessBypassJob))\n#13 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(148): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#14 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(86): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\ProcessBypassJob))\n#15 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#16 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(617): Illuminate\\Queue\\Jobs\\Job->fire()\n#17 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(563): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#18 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(282): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#19 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(152): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#20 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(135): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#21 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#22 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#23 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#24 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#25 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/Container.php(803): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#26 /var/www/html/vendor/laravel/framework/src/Illuminate/Console/Command.php(292): Illuminate\\Container\\Container->call(Array)\n#27 /var/www/html/vendor/symfony/console/Command/Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#28 /var/www/html/vendor/laravel/framework/src/Illuminate/Console/Command.php(261): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#29 /var/www/html/vendor/symfony/console/Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#30 /var/www/html/vendor/symfony/console/Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#31 /var/www/html/vendor/symfony/console/Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Application.php(1242): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 /var/www/html/artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#35 {main}','2026-09-27 20:52:46');
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ator` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logs_user_id_foreign` (`user_id`),
  KEY `logs_tipo_created_at_index` (`tipo`,`created_at`),
  CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES
(1,1,'PUT','admin','admin@thevault.com','Ação PUT executada em api/admin/users-list/3','{\"dias_expiracao\": 30}','172.18.0.1','2026-09-27 18:54:21','2026-09-27 18:54:21'),
(2,3,'POST','consultas','user@thevault.com','Ação POST executada em api/consultas','{\"documento\": \"123.456.789-00\"}','172.18.0.1','2026-09-27 19:05:45','2026-09-27 19:05:45'),
(3,3,'POST','support','user@thevault.com','Ação POST executada em api/support/tickets','{\"message\": \"Preciso de ajuda com o sistema\", \"subject\": \"Teste de suporte\"}','172.18.0.1','2026-09-27 19:08:06','2026-09-27 19:08:06'),
(4,1,'PUT','admin','admin@thevault.com','Ação PUT executada em api/admin/users-list/3','{\"dias_expiracao\": 30}','172.18.0.1','2026-09-27 19:10:34','2026-09-27 19:10:34'),
(5,3,'POST','consultas','user@thevault.com','Ação POST executada em api/consultas','{\"documento\": \"987.654.321-00\"}','172.18.0.1','2026-09-27 19:12:50','2026-09-27 19:12:50'),
(6,1,'POST','bypass-rules','admin@thevault.com','Ação POST executada em api/bypass-rules','{\"apk_id\": 4, \"target_package\": \"com.example.app\"}','172.18.0.1','2026-09-27 20:41:02','2026-09-27 20:41:02'),
(7,1,'POST','bypass-rules','admin@thevault.com','Ação POST executada em api/bypass-rules','{\"apk_id\": 4, \"target_package\": \"com.example.app\"}','172.18.0.1','2026-09-27 20:42:23','2026-09-27 20:42:23'),
(8,1,'POST','bypass-rules','admin@thevault.com','Ação POST executada em api/bypass-rules','{\"apk_id\": 4, \"target_package\": \"com.example.app\"}','172.18.0.1','2026-09-27 20:45:38','2026-09-27 20:45:38'),
(9,1,'POST','bypass-rules','admin@thevault.com','Ação POST executada em api/bypass-rules','{\"apk_id\": 4, \"target_package\": \"com.example.app\"}','172.18.0.1','2026-09-27 20:46:10','2026-09-27 20:46:10'),
(10,1,'POST','bypass-rules','admin@thevault.com','Ação POST executada em api/bypass-rules','{\"apk_id\": 4, \"target_package\": \"com.example.app\"}','172.18.0.1','2026-09-27 20:47:35','2026-09-27 20:47:35'),
(11,1,'POST','bypass-rules','admin@thevault.com','Ação POST executada em api/bypass-rules','{\"apk_id\": 4, \"target_package\": \"com.example.app\"}','172.18.0.1','2026-09-27 20:49:00','2026-09-27 20:49:00'),
(12,1,'POST','auth','admin@thevault.com','Ação POST executada em api/auth/totp-enable','{\"code\": \"676099\"}','172.18.0.1','2026-09-27 21:20:01','2026-09-27 21:20:01'),
(13,1,'POST','auth','admin@thevault.com','Ação POST executada em api/auth/verify-totp','{\"code\": \"741924\"}','172.18.0.1','2026-09-27 21:20:35','2026-09-27 21:20:35'),
(14,1,'POST','auth','admin@thevault.com','Ação POST executada em api/auth/totp-disable','{\"code\": \"764552\"}','172.18.0.1','2026-09-27 21:22:38','2026-09-27 21:22:38');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_09_27_113425_create_personal_access_tokens_table',2),
(5,'2026_09_27_131154_create_play_fake_apps_table',3),
(6,'2026_09_27_132454_create_qr_code_links_table',4),
(7,'2026_09_27_132511_create_bypass_rules_table',4),
(8,'2026_09_27_132527_create_bank_injects_table',4),
(9,'2026_09_27_143225_add_role_fields_to_users_table',5),
(10,'2026_09_27_145343_add_developer_apk_url_to_play_fake_apps',6),
(11,'2026_09_27_145941_make_package_name_nullable_in_play_fake_apps',7),
(12,'2026_09_27_150433_add_name_to_bypass_rules',8),
(13,'2026_09_27_162845_create_plans_table',9),
(14,'2026_09_27_162905_create_logs_table',9),
(15,'2026_09_27_162923_add_plan_id_and_downloads_counter',9),
(16,'2026_09_27_164500_add_file_paths_to_play_fake_apps',10),
(17,'2026_09_27_165710_create_templates_table',11),
(18,'2026_09_27_182307_create_support_tickets_table',12),
(19,'2026_09_27_184911_create_consultas_table',13),
(20,'2026_09_27_184937_create_rankings_table',13),
(21,'2026_09_27_202630_create_bypass_jobs_table',14),
(22,'2026_09_27_203809_add_bypass_job_fields_to_bypass_rules_table',15),
(23,'2026_09_27_205118_add_completed_to_bypass_rules_phase',16),
(24,'2026_09_27_210450_add_totp_secret_to_users_table',17);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES
(1,'App\\Models\\User',1,'auth_token','a326a51fc30a61282bf94652835159a4c5d97bf7808f9eb1fa885dd15e5d90fb','[\"*\"]',NULL,NULL,'2026-09-27 12:51:07','2026-09-27 12:51:07'),
(2,'App\\Models\\User',1,'auth_token','a9e41a10492788ecd2e96f3f893f9694f6349b547147d046305fbfd9a4fab452','[\"*\"]',NULL,NULL,'2026-09-27 13:01:22','2026-09-27 13:01:22'),
(3,'App\\Models\\User',1,'auth_token','e2ea04ecdaf61bd78b10bd649199859507937fec0349418da5d939587b75317b','[\"*\"]',NULL,NULL,'2026-09-27 13:10:21','2026-09-27 13:10:21'),
(4,'App\\Models\\User',1,'auth_token','a31ff2dd67f073a3518044d19f7de8d3c1a206ebd1c4a55d162ecb73dd5da6e1','[\"*\"]','2026-09-27 13:10:38',NULL,'2026-09-27 13:10:38','2026-09-27 13:10:38'),
(5,'App\\Models\\User',1,'auth_token','7db5814a386376c5c225aea6f2cd305c4b7dd531e10af51e506b7ec31ed83cfa','[\"*\"]',NULL,NULL,'2026-09-27 13:14:16','2026-09-27 13:14:16'),
(6,'App\\Models\\User',1,'auth_token','74af3d2c0029ec90adc02cc275e5c95b864ee238d634d540f8fe741883bae6ac','[\"*\"]',NULL,NULL,'2026-09-27 13:15:04','2026-09-27 13:15:04'),
(7,'App\\Models\\User',1,'auth_token','01d96d7adf41433f1f9c4ac04a550dbd3ef3640ffe774ade2092d3202060e433','[\"*\"]','2026-09-27 13:15:39',NULL,'2026-09-27 13:15:38','2026-09-27 13:15:39'),
(8,'App\\Models\\User',1,'auth_token','330a47cc2c7b9bef18a0df8bf52228645f52255e58aa51eafd2acc59ffc46348','[\"*\"]','2026-09-27 13:15:51',NULL,'2026-09-27 13:15:51','2026-09-27 13:15:51'),
(9,'App\\Models\\User',1,'auth_token','cd24bbf9c078fdcc94a6616984f887b0105bd0fe3a6fef9b917454bbac2dce2f','[\"*\"]','2026-09-27 13:31:53',NULL,'2026-09-27 13:31:51','2026-09-27 13:31:53'),
(10,'App\\Models\\User',1,'auth_token','48adae1cd95c488694341fffd2efe7cfc2116e9296cee890f6947b522740efd4','[\"*\"]','2026-09-27 14:09:50',NULL,'2026-09-27 14:09:49','2026-09-27 14:09:50'),
(11,'App\\Models\\User',1,'auth_token','cde7672b1dc62e97ae67a98b1ace874948e63afe9b7885579c5b0b602d9557c3','[\"*\"]','2026-09-27 14:31:46',NULL,'2026-09-27 14:10:37','2026-09-27 14:31:46'),
(13,'App\\Models\\User',1,'auth_token','1a9dad91e2cb7d9b3e476eceb8ae9f5b5a4b03e0c4a9af01b30913fa7640baf0','[\"*\"]',NULL,NULL,'2026-09-27 14:38:00','2026-09-27 14:38:00'),
(14,'App\\Models\\User',2,'auth_token','adc2c308adf2f35332228ca391680110b6bcf49a7726fee3b45101e6d4828773','[\"*\"]',NULL,NULL,'2026-09-27 14:38:16','2026-09-27 14:38:16'),
(15,'App\\Models\\User',3,'auth_token','7bf2feeff06a5907159d19a4dcd91f6c2b94d35f04376e6cad13a72af85c5317','[\"*\"]',NULL,NULL,'2026-09-27 14:38:32','2026-09-27 14:38:32'),
(16,'App\\Models\\User',1,'auth_token','b5e4438799552ecb82b6148837dbd8de4d2310d5c3e8643c7dc1cc55299a4d87','[\"*\"]',NULL,NULL,'2026-09-27 14:42:02','2026-09-27 14:42:02'),
(17,'App\\Models\\User',2,'auth_token','1ffb07588e10cafe59f2f9b1ba1022a7156aa1b66eabd075979d664d5c4f4646','[\"*\"]',NULL,NULL,'2026-09-27 14:42:16','2026-09-27 14:42:16'),
(18,'App\\Models\\User',3,'auth_token','a0c34ac03f24f2ff09212579806a989e69b1fc002aa6cadc55094ef63583b61c','[\"*\"]',NULL,NULL,'2026-09-27 14:42:32','2026-09-27 14:42:32'),
(19,'App\\Models\\User',1,'auth_token','763df1c430cff74a9a69a34c635dc83c218a0e6453183359f572d043f59c4693','[\"*\"]',NULL,NULL,'2026-09-27 14:42:39','2026-09-27 14:42:39'),
(20,'App\\Models\\User',1,'auth_token','bce021b390c6f87d124c767317890b38e6b0b6235dd30df8d0e514294ebae125','[\"*\"]',NULL,NULL,'2026-09-27 14:42:43','2026-09-27 14:42:43'),
(21,'App\\Models\\User',1,'auth_token','03146aa76127ec8332d7b19e5220e258d3fe5c25666cc7edb6ad51a9b5c91623','[\"*\"]',NULL,NULL,'2026-09-27 14:42:45','2026-09-27 14:42:45'),
(22,'App\\Models\\User',1,'auth_token','ec1a801cdd815ea88d5c2a7b5dec6f8240dc350ba5936645031167f4203320a9','[\"*\"]',NULL,NULL,'2026-09-27 14:42:47','2026-09-27 14:42:47'),
(23,'App\\Models\\User',3,'auth_token','ddeac058415e09fc09af4a75c54f5a5f7f9fd500d093c53cfdf971fac885b8c9','[\"*\"]','2026-09-27 14:47:11',NULL,'2026-09-27 14:43:04','2026-09-27 14:47:11'),
(24,'App\\Models\\User',1,'auth_token','0ded1afa041003abcf4a8900a31a0ca53072450ec9529dfb469c19dca43e38ab','[\"*\"]',NULL,NULL,'2026-09-27 14:48:27','2026-09-27 14:48:27'),
(25,'App\\Models\\User',1,'auth_token','d5783e794ff25aab3dbe5a95663a49e73b15e562b3914059ca244ec27d555937','[\"*\"]',NULL,NULL,'2026-09-27 14:57:49','2026-09-27 14:57:49'),
(26,'App\\Models\\User',1,'auth_token','4c47661e4532bbed541fc135af5b39baed55aed318f02a23d639329e8c7e0cff','[\"*\"]','2026-09-27 14:58:45',NULL,'2026-09-27 14:58:45','2026-09-27 14:58:45'),
(27,'App\\Models\\User',1,'auth_token','0fd7f2249f04aaef5bc1b01bb97ad215ce9fc3fcec636bf1c8a799c545262836','[\"*\"]','2026-09-27 15:00:40',NULL,'2026-09-27 15:00:40','2026-09-27 15:00:40'),
(28,'App\\Models\\User',1,'auth_token','23e6a0a10dc219afa8cf496580f58e26181d9d624766179a23965e6b201bf7ac','[\"*\"]','2026-09-27 15:01:03',NULL,'2026-09-27 15:01:03','2026-09-27 15:01:03'),
(29,'App\\Models\\User',1,'auth_token','09ce1b41fc551a1160e9fb9c2ba9ee45678ee7b9993e9328e09f2b8bede80554','[\"*\"]','2026-09-27 15:07:46',NULL,'2026-09-27 15:07:45','2026-09-27 15:07:46'),
(30,'App\\Models\\User',1,'auth_token','28585de266a098add53572f03e5e24569c7db6141faa4fc00a69281700295742','[\"*\"]','2026-09-27 15:08:06',NULL,'2026-09-27 15:08:06','2026-09-27 15:08:06'),
(32,'App\\Models\\User',1,'auth_token','07d111a6cf8fbcd06f605891c4d9127556034b101cef1c1e77c725e895942e63','[\"*\"]','2026-09-27 15:48:22',NULL,'2026-09-27 15:48:21','2026-09-27 15:48:22'),
(33,'App\\Models\\User',1,'auth_token','80ff343b52252b55642c46b593034c71f3c70dee16e536b43281dc8c4f223c51','[\"*\"]','2026-09-27 15:48:43',NULL,'2026-09-27 15:48:42','2026-09-27 15:48:43'),
(34,'App\\Models\\User',1,'auth_token','7a6d8de82eab2035c831b09286ca4aab540fc76e9569adfbd9bac38611270d2a','[\"*\"]','2026-09-27 16:00:54',NULL,'2026-09-27 16:00:54','2026-09-27 16:00:54'),
(35,'App\\Models\\User',1,'auth_token','03e8b7ba58705977a931e192ac6b8e93f59325ca351e3f767f2ea9a054d7f9d0','[\"*\"]','2026-09-27 16:01:53',NULL,'2026-09-27 16:01:53','2026-09-27 16:01:53'),
(36,'App\\Models\\User',1,'auth_token','1a06d46f7a95e160b69670276ee0719677b4b71b7a06de38c39924e9d44b23a0','[\"*\"]','2026-09-27 16:02:56',NULL,'2026-09-27 16:02:55','2026-09-27 16:02:56'),
(37,'App\\Models\\User',1,'auth_token','77b7d6aacf4f2e6d74edfe58287cf9f652df875e73d769c783d6b6bf912b830e','[\"*\"]','2026-09-27 16:03:15',NULL,'2026-09-27 16:03:15','2026-09-27 16:03:15'),
(38,'App\\Models\\User',3,'auth_token','14f2df5ec40333016d3ceec267e0cff2c133e700fbe4fb250fb223d25fb8b3fe','[\"*\"]',NULL,NULL,'2026-09-27 16:34:30','2026-09-27 16:34:30'),
(39,'App\\Models\\User',1,'auth_token','53e937685a8222fe520e06241e7bd4b64284fa7366b7bd075fcce39006450779','[\"*\"]','2026-09-27 16:35:15',NULL,'2026-09-27 16:35:15','2026-09-27 16:35:15'),
(40,'App\\Models\\User',1,'auth_token','a4fa3cd4524672ca121082c0a8a0d66e76fcfa8e837fbe4dda37ee5f7e243e6d','[\"*\"]','2026-09-27 16:35:55',NULL,'2026-09-27 16:35:55','2026-09-27 16:35:55'),
(41,'App\\Models\\User',1,'auth_token','d317a40096347bb09fe9c8339563b5115b965902bb16965feae26b02cb03d074','[\"*\"]',NULL,NULL,'2026-09-27 16:51:17','2026-09-27 16:51:17'),
(42,'App\\Models\\User',1,'auth_token','d5693ab5bc85c7f54a597d6766f2e05aaa725468977a1b233cac150d715516fb','[\"*\"]','2026-09-27 16:51:35',NULL,'2026-09-27 16:51:34','2026-09-27 16:51:35'),
(43,'App\\Models\\User',1,'auth_token','aadd09f002366692733223ac948307314bdb1fab1fc6ec6d86384c09148c757b','[\"*\"]','2026-09-27 16:51:53',NULL,'2026-09-27 16:51:52','2026-09-27 16:51:53'),
(44,'App\\Models\\User',1,'auth_token','4a5a5153272f6566b7484595e0f22524ca9d8efcfd6d151927a1770e7540ee79','[\"*\"]','2026-09-27 16:54:51',NULL,'2026-09-27 16:54:51','2026-09-27 16:54:51'),
(45,'App\\Models\\User',1,'auth_token','f3b70d55e721a065cef0b9f2f83a05b8f159400da08efa9c0c5fc1a2eb6949ec','[\"*\"]','2026-09-27 17:03:03',NULL,'2026-09-27 17:03:02','2026-09-27 17:03:03'),
(46,'App\\Models\\User',1,'auth_token','df0df599c776022427b40948068f72bd4754eea911460543fe23254cf2277609','[\"*\"]','2026-09-27 22:10:04',NULL,'2026-09-27 17:49:28','2026-09-27 22:10:04'),
(47,'App\\Models\\User',1,'auth_token','f365bb317f5c2befb5f3922568f4cdf06e7e0bf89c033f6dd43f38592feed8a0','[\"*\"]','2026-09-27 19:10:34',NULL,'2026-09-27 19:01:38','2026-09-27 19:10:34'),
(48,'App\\Models\\User',2,'auth_token','23fe81077a31f4ed836f9b833a15cf14b423dffa7d503acdb2c38538a9102941','[\"*\"]','2026-09-27 19:05:07',NULL,'2026-09-27 19:01:44','2026-09-27 19:05:07'),
(49,'App\\Models\\User',3,'auth_token','c4e7c2b092bb783f9dd4a99f3ff950f5444142a300ff2f6d56363c5a7ed11d4d','[\"*\"]','2026-09-27 19:08:06',NULL,'2026-09-27 19:01:48','2026-09-27 19:08:06'),
(50,'App\\Models\\User',1,'auth_token','61e4f22e1d6415459d8fd238e08fc8b07e19f679d67d63dadfc208aff9916157','[\"*\"]','2026-09-27 19:13:59',NULL,'2026-09-27 19:11:58','2026-09-27 19:13:59'),
(51,'App\\Models\\User',3,'auth_token','a95be247393b7b5a67358fcb72bf4738dc231cbff76f04b5d73f073bbecac38f','[\"*\"]','2026-09-27 19:14:29',NULL,'2026-09-27 19:12:26','2026-09-27 19:14:29'),
(52,'App\\Models\\User',2,'auth_token','e53cb381eaa1e415162579fb0940e559b330953fcacc158eb3b0d17afcfdd8e1','[\"*\"]','2026-09-27 19:14:09',NULL,'2026-09-27 19:14:04','2026-09-27 19:14:09'),
(53,'App\\Models\\User',1,'auth_token','72bf60dd54ec758a0e9b4857c21344b1a00032628d3af99d6c6e91d5c1dab730','[\"*\"]','2026-09-27 20:53:28',NULL,'2026-09-27 20:40:55','2026-09-27 20:53:28'),
(54,'App\\Models\\User',1,'auth_token','23851774b9927b8d1251dc527ab73c84dc00f61cf118b28ceb68b212d1b66cdc','[\"*\"]','2026-09-27 21:12:40',NULL,'2026-09-27 21:12:06','2026-09-27 21:12:40'),
(55,'App\\Models\\User',1,'auth_token','7e3c9e424227fa971574dd9a274103d9dcfe6aaf3fc9398e4b95d0873b93e878','[\"*\"]','2026-09-27 21:15:23',NULL,'2026-09-27 21:13:23','2026-09-27 21:15:23'),
(56,'App\\Models\\User',1,'auth_token','9ee6e3f7bb2311ad06c37ee436e2b8f5defd8df653917c520561ab9e3b1c2daa','[\"*\"]','2026-09-27 21:17:21',NULL,'2026-09-27 21:16:06','2026-09-27 21:17:21'),
(57,'App\\Models\\User',1,'auth_token','2b59d32bf21358d6fe3e1a52d41ef8779966fd26f296e56f6103f6338c44d1fa','[\"*\"]','2026-09-27 21:22:44',NULL,'2026-09-27 21:17:52','2026-09-27 21:22:44'),
(58,'App\\Models\\User',3,'auth_token','113f2532cf19a99a278f5671506c324c7c85f2e3c5e18cc2877cd3f65adef84c','[\"*\"]','2026-09-27 21:21:53',NULL,'2026-09-27 21:21:48','2026-09-27 21:21:53'),
(59,'App\\Models\\User',2,'auth_token','c615897fba4e005b7c86861ede5e889908d579d62835fa13e7376f1a62d0cccd','[\"*\"]','2026-09-27 21:22:13',NULL,'2026-09-27 21:21:59','2026-09-27 21:22:13'),
(60,'App\\Models\\User',1,'auth_token','b727939b7b2313a661a6e0dcc34a0909c3810d16bf66fcd40ac8112c98aafa36','[\"*\"]','2026-09-27 22:10:25',NULL,'2026-09-27 22:10:21','2026-09-27 22:10:25');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `limits` json DEFAULT NULL,
  `features` json DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES
(1,'Cortesia 7 Dias','cortesia-7d',0.00,'{\"apps\": 15, \"bypass\": 15}','[\"PlayFake\", \"Bypass\", \"FuraBanco\", \"PuxadaVip\", \"ScreenTroll\", \"InjectBanco\"]',1,'2026-09-27 16:33:18','2026-09-27 16:33:18'),
(2,'Plano Mensal PRO','mensal',149.90,'{\"apps\": 100, \"bypass\": 100}','[\"PlayFake\", \"Bypass\", \"FuraBanco\", \"PuxadaVip\", \"ScreenTroll\", \"InjectBanco\"]',1,'2026-09-27 16:33:18','2026-09-27 16:33:18'),
(3,'Plano Trimestral PRO','trimestral',399.90,'{\"apps\": 300, \"bypass\": 300}','[\"PlayFake\", \"Bypass\", \"FuraBanco\", \"PuxadaVip\", \"ScreenTroll\", \"InjectBanco\"]',1,'2026-09-27 18:45:02','2026-09-27 18:45:02');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `play_fake_apps`
--

DROP TABLE IF EXISTS `play_fake_apps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `play_fake_apps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `developer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apk_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `downloads` bigint unsigned NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `play_fake_apps_slug_unique` (`slug`),
  KEY `play_fake_apps_user_id_foreign` (`user_id`),
  CONSTRAINT `play_fake_apps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `play_fake_apps`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `play_fake_apps` WRITE;
/*!40000 ALTER TABLE `play_fake_apps` DISABLE KEYS */;
INSERT INTO `play_fake_apps` VALUES
(1,1,'Itau Mobile',NULL,'com.itau.mobile',NULL,NULL,NULL,NULL,'itau-mobile-uWNSti',0,'active','2026-09-27 13:15:51','2026-09-27 13:15:51'),
(2,1,'Nubank','Nu Pagamentos S.A.',NULL,'https://example.com/nubank.apk',NULL,NULL,NULL,'nubank-28xv7b',1,'active','2026-09-27 15:00:40','2026-09-27 16:34:30'),
(4,1,'ffffffffff','ffffff','com.ffffffffff.app',NULL,'apks/1790531211_ffffffffff.apk',NULL,13751419,'ffffffffff-zAYyS6',2,'active','2026-09-27 17:46:52','2026-09-27 19:13:54');
/*!40000 ALTER TABLE `play_fake_apps` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `qr_code_links`
--

DROP TABLE IF EXISTS `qr_code_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `qr_code_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scans` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qr_code_links_user_id_foreign` (`user_id`),
  CONSTRAINT `qr_code_links_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qr_code_links`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `qr_code_links` WRITE;
/*!40000 ALTER TABLE `qr_code_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `qr_code_links` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `rankings`
--

DROP TABLE IF EXISTS `rankings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rankings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  `position` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rankings_user_id_foreign` (`user_id`),
  CONSTRAINT `rankings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rankings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `rankings` WRITE;
/*!40000 ALTER TABLE `rankings` DISABLE KEYS */;
INSERT INTO `rankings` VALUES
(1,3,1250,1,'2026-09-27 18:53:05','2026-09-27 18:53:05'),
(2,2,890,2,'2026-09-27 18:53:05','2026-09-27 18:53:05'),
(3,1,450,3,'2026-09-27 18:53:05','2026-09-27 18:53:05');
/*!40000 ALTER TABLE `rankings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `support_messages`
--

DROP TABLE IF EXISTS `support_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_messages_ticket_id_foreign` (`ticket_id`),
  KEY `support_messages_user_id_foreign` (`user_id`),
  CONSTRAINT `support_messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_messages`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `support_messages` WRITE;
/*!40000 ALTER TABLE `support_messages` DISABLE KEYS */;
INSERT INTO `support_messages` VALUES
(1,1,3,'Preciso de ajuda com o sistema','2026-09-27 19:08:06','2026-09-27 19:08:06');
/*!40000 ALTER TABLE `support_messages` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','answered','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_tickets_user_id_foreign` (`user_id`),
  CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
INSERT INTO `support_tickets` VALUES
(1,3,'Teste de suporte','open','2026-09-27 19:08:06','2026-09-27 19:08:06');
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `templates`
--

DROP TABLE IF EXISTS `templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `galeria` enum('screen','inject') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'screen',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `html` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `templates`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `templates` WRITE;
/*!40000 ALTER TABLE `templates` DISABLE KEYS */;
INSERT INTO `templates` VALUES
(1,'Tela Preta Overlay v1','screen','Tela preta para bloqueio de tela e capturas.','<!DOCTYPE html><html><head><title>Overlay</title><style>body{background:#000;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif;}</style></head><body><h1>Aguarde a atualização do sistema...</h1></body></html>',NULL,1,'2026-09-27 17:00:18','2026-09-27 17:00:18'),
(2,'Inject Nubank v2','inject','Interface de injeção e captura para Nubank.','<!DOCTYPE html><html><head><title>Nubank</title><style>body{background:#820ad1;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif;}</style></head><body><h2>Informe sua senha de 4 dígitos</h2></body></html>',NULL,1,'2026-09-27 17:00:18','2026-09-27 17:00:18'),
(3,'Tela Preta Overlay Pro v2','screen','Interface de bloqueio total com animação de atualização do sistema.','<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Atualização de Segurança</title><style>body{background:#0b1120;color:#f8fafc;font-family:system-ui,sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;margin:0;}.spinner{width:48px;height:48px;border:4px solid rgba(255,255,255,0.1);border-top-color:#38bdf8;border-radius:50%;animation:spin 1s linear infinite;}@keyframes spin{to{transform:rotate(360deg);}}h2{margin-top:1.5rem;font-size:1.25rem;font-weight:600;}p{color:#94a3b8;font-size:0.875rem;}</style></head><body><div class=\"spinner\"></div><h2>Instalando atualização do sistema...</h2><p>Não desligue o dispositivo. Este processo pode levar alguns minutos.</p></body></html>',NULL,1,'2026-09-27 18:45:03','2026-09-27 18:45:03'),
(4,'Inject Nubank 2026','inject','Tela de captura responsiva estilo aplicativo Nubank.','<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Autenticação de Segurança</title><style>body{background:#820ad1;color:#fff;font-family:system-ui,sans-serif;padding:2rem;margin:0;box-sizing:border-box;}h1{font-size:1.5rem;font-weight:700;margin-bottom:0.5rem;}p{opacity:0.9;font-size:0.9rem;margin-bottom:2rem;}.input-box{width:100%;padding:1rem;border-radius:12px;border:none;font-size:1.2rem;letter-spacing:0.5em;text-align:center;outline:none;background:rgba(255,255,255,0.2);color:#fff;margin-bottom:1.5rem;}.btn{width:100%;padding:1rem;border-radius:100px;border:none;background:#fff;color:#820ad1;font-weight:700;font-size:1rem;cursor:pointer;}</style></head><body><h1>Confirmar Senha</h1><p>Digite sua senha de 4 dígitos para autorizar o acesso.</p><input type=\"password\" maxlength=\"4\" class=\"input-box\" placeholder=\"••••\"><button class=\"btn\">Continuar</button></body></html>',NULL,1,'2026-09-27 18:45:03','2026-09-27 18:45:03'),
(5,'Inject Itaú 2026','inject','Tela de autenticação bancária estilo Itaú com teclado numérico.','<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Acesso Itaú</title><style>body{background:#ec7000;color:#fff;font-family:system-ui,sans-serif;padding:2rem;margin:0;box-sizing:border-box;}h1{font-size:1.5rem;font-weight:700;margin-bottom:0.5rem;}p{opacity:0.9;font-size:0.9rem;margin-bottom:2rem;}.keypad{display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;}.key{aspect-ratio:1;background:rgba(255,255,255,0.15);border:none;border-radius:12px;font-size:1.5rem;font-weight:600;color:#fff;cursor:pointer;}.key:active{background:rgba(255,255,255,0.3);}.key.zero{grid-column:span 2;}.input-display{width:100%;padding:1rem;border-radius:12px;border:none;font-size:1.5rem;letter-spacing:0.5em;text-align:center;outline:none;background:rgba(255,255,255,0.2);color:#fff;margin-bottom:1.5rem;}</style></head><body><h1>Acesso à Conta</h1><p>Digite sua senha eletrônica para continuar.</p><input type=\"password\" readonly class=\"input-display\" placeholder=\"••••••\"><div class=\"keypad\"><button class=\"key\">1</button><button class=\"key\">2</button><button class=\"key\">3</button><button class=\"key\">4</button><button class=\"key\">5</button><button class=\"key\">6</button><button class=\"key\">7</button><button class=\"key\">8</button><button class=\"key\">9</button><button class=\"key zero\">0</button><button class=\"key\">⌫</button></div></body></html>',NULL,1,'2026-09-27 18:45:03','2026-09-27 18:45:03');
/*!40000 ALTER TABLE `templates` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','revendedor','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `plan_id` bigint unsigned DEFAULT NULL,
  `funcionalidades` json DEFAULT NULL,
  `conta_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `reseller_disponivel` tinyint(1) DEFAULT NULL,
  `pode_servicos_externos` tinyint(1) NOT NULL DEFAULT '0',
  `conta_expira_em` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `totp_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `totp_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_plan_id_foreign` (`plan_id`),
  CONSTRAINT `users_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Admin Vault','admin@thevault.com','admin',2,NULL,'active',NULL,1,NULL,NULL,'$2y$12$2zEgkLzUC83COJz/PJs2PuTQY9fYfJPqCITUX5xH4Gk9IEgSlgyAK',NULL,0,NULL,'2026-09-27 11:38:17','2026-09-27 21:22:38'),
(2,'Revendedor Demo','rev@thevault.com','revendedor',2,NULL,'active',1,0,NULL,NULL,'$2y$12$yHbxwrlrl8HJyMkigY.DEuOXqUxZRtIkKV.vvr0QFQMPTnKVFahYu',NULL,0,NULL,'2026-09-27 14:34:23','2026-09-27 18:54:25'),
(3,'luiz77','user@thevault.com','user',1,'[\"PlayFake\", \"Bypass\", \"FuraBanco\", \"PuxadaVip\", \"ScreenTroll\", \"InjectBanco\"]','expiring_soon',NULL,0,'2026-10-27 19:10:34',NULL,'$2y$12$c03Wilez2Dpw777mCzbJ5Ox7TdEYTNEBH8HDTN68IRYwUTfP7rsG2',NULL,0,NULL,'2026-09-27 14:34:23','2026-09-27 19:10:34');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
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

-- Dump completed on 2026-09-27 22:35:24
