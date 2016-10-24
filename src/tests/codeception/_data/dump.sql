-- MySQL dump 10.13  Distrib 5.6.17, for Win64 (x86_64)
--
-- Host: localhost    Database: yii2
-- ------------------------------------------------------
-- Server version	5.6.17

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `podium_auth_assignment`
--

DROP TABLE IF EXISTS `podium_auth_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_auth_assignment` (
  `item_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`),
  CONSTRAINT `fk-auth_assignment-item_name` FOREIGN KEY (`item_name`) REFERENCES `podium_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_auth_assignment`
--

LOCK TABLES `podium_auth_assignment` WRITE;
/*!40000 ALTER TABLE `podium_auth_assignment` DISABLE KEYS */;
INSERT INTO `podium_auth_assignment` VALUES ('podiumAdmin','1',1449307991);
/*!40000 ALTER TABLE `podium_auth_assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_auth_item`
--

DROP TABLE IF EXISTS `podium_auth_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_auth_item` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `rule_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `fk-auth_item-rule_name` (`rule_name`),
  KEY `idx-auth_item-type` (`type`),
  CONSTRAINT `fk-auth_item-rule_name` FOREIGN KEY (`rule_name`) REFERENCES `podium_auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_auth_item`
--

LOCK TABLES `podium_auth_item` WRITE;
/*!40000 ALTER TABLE `podium_auth_item` DISABLE KEYS */;
INSERT INTO `podium_auth_item` VALUES ('banPodiumUser',2,'Ban Podium user',NULL,NULL,1449307986,1449307986),('changePodiumSettings',2,'Change Podium settings',NULL,NULL,1449307986,1449307986),('createPodiumCategory',2,'Create Podium category',NULL,NULL,1449307986,1449307986),('createPodiumForum',2,'Create Podium forum',NULL,NULL,1449307986,1449307986),('createPodiumPost',2,'Create Podium post',NULL,NULL,1449307985,1449307985),('createPodiumThread',2,'Create Podium thread',NULL,NULL,1449307985,1449307985),('deleteOwnPodiumPost',2,'Delete own Podium post','isPodiumAuthor',NULL,1449307986,1449307986),('deletePodiumCategory',2,'Delete Podium category',NULL,NULL,1449307986,1449307986),('deletePodiumForum',2,'Delete Podium forum',NULL,NULL,1449307986,1449307986),('deletePodiumPost',2,'Delete Podium post','isPodiumModerator',NULL,1449307986,1449307986),('deletePodiumThread',2,'Delete Podium thread','isPodiumModerator',NULL,1449307986,1449307986),('deletePodiumUser',2,'Delete Podium user',NULL,NULL,1449307986,1449307986),('lockPodiumThread',2,'Lock Podium thread','isPodiumModerator',NULL,1449307986,1449307986),('movePodiumPost',2,'Move Podium post','isPodiumModerator',NULL,1449307986,1449307986),('movePodiumThread',2,'Move Podium thread','isPodiumModerator',NULL,1449307986,1449307986),('pinPodiumThread',2,'Pin Podium thread','isPodiumModerator',NULL,1449307986,1449307986),('podiumAdmin',1,NULL,NULL,NULL,1449307986,1449307986),('podiumModerator',1,NULL,NULL,NULL,1449307986,1449307986),('podiumUser',1,NULL,NULL,NULL,1449307986,1449307986),('promotePodiumUser',2,'Promote Podium user',NULL,NULL,1449307986,1449307986),('updateOwnPodiumPost',2,'Update own Podium post','isPodiumAuthor',NULL,1449307986,1449307986),('updatePodiumCategory',2,'Update Podium category',NULL,NULL,1449307986,1449307986),('updatePodiumForum',2,'Update Podium forum',NULL,NULL,1449307986,1449307986),('updatePodiumPost',2,'Update Podium post','isPodiumModerator',NULL,1449307986,1449307986),('updatePodiumThread',2,'Update Podium thread','isPodiumModerator',NULL,1449307986,1449307986),('viewPodiumForum',2,'View Podium forum',NULL,NULL,1449307985,1449307985),('viewPodiumThread',2,'View Podium thread',NULL,NULL,1449307985,1449307985);
/*!40000 ALTER TABLE `podium_auth_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_auth_item_child`
--

DROP TABLE IF EXISTS `podium_auth_item_child`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_auth_item_child` (
  `parent` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `child` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `fk-auth_item_child-child` (`child`),
  CONSTRAINT `fk-auth_item_child-child` FOREIGN KEY (`child`) REFERENCES `podium_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-auth_item_child-parent` FOREIGN KEY (`parent`) REFERENCES `podium_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_auth_item_child`
--

LOCK TABLES `podium_auth_item_child` WRITE;
/*!40000 ALTER TABLE `podium_auth_item_child` DISABLE KEYS */;
INSERT INTO `podium_auth_item_child` VALUES ('podiumModerator','banPodiumUser'),('podiumAdmin','changePodiumSettings'),('podiumAdmin','createPodiumCategory'),('podiumAdmin','createPodiumForum'),('podiumUser','createPodiumPost'),('podiumUser','createPodiumThread'),('podiumUser','deleteOwnPodiumPost'),('podiumAdmin','deletePodiumCategory'),('podiumAdmin','deletePodiumForum'),('deleteOwnPodiumPost','deletePodiumPost'),('podiumModerator','deletePodiumPost'),('podiumModerator','deletePodiumThread'),('podiumAdmin','deletePodiumUser'),('podiumModerator','lockPodiumThread'),('podiumModerator','movePodiumPost'),('podiumModerator','movePodiumThread'),('podiumModerator','pinPodiumThread'),('podiumAdmin','podiumModerator'),('podiumModerator','podiumUser'),('podiumAdmin','promotePodiumUser'),('podiumUser','updateOwnPodiumPost'),('podiumAdmin','updatePodiumCategory'),('podiumAdmin','updatePodiumForum'),('podiumModerator','updatePodiumPost'),('updateOwnPodiumPost','updatePodiumPost'),('podiumModerator','updatePodiumThread'),('podiumUser','viewPodiumForum'),('podiumUser','viewPodiumThread');
/*!40000 ALTER TABLE `podium_auth_item_child` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_auth_rule`
--

DROP TABLE IF EXISTS `podium_auth_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_auth_rule` (
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_auth_rule`
--

LOCK TABLES `podium_auth_rule` WRITE;
/*!40000 ALTER TABLE `podium_auth_rule` DISABLE KEYS */;
INSERT INTO `podium_auth_rule` VALUES ('isPodiumAuthor','O:29:\"bizley\\podium\\rbac\\AuthorRule\":3:{s:4:\"name\";s:14:\"isPodiumAuthor\";s:9:\"createdAt\";i:1449307986;s:9:\"updatedAt\";i:1449307986;}',1449307986,1449307986),('isPodiumModerator','O:32:\"bizley\\podium\\rbac\\ModeratorRule\":3:{s:4:\"name\";s:17:\"isPodiumModerator\";s:9:\"createdAt\";i:1449307986;s:9:\"updatedAt\";i:1449307986;}',1449307986,1449307986);
/*!40000 ALTER TABLE `podium_auth_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_category`
--

DROP TABLE IF EXISTS `podium_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `visible` smallint(6) NOT NULL DEFAULT '1',
  `sort` smallint(6) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-category-sort` (`sort`,`id`),
  KEY `idx-category-name` (`name`),
  KEY `idx-category-display` (`id`,`slug`),
  KEY `idx-category-display_guest` (`id`,`slug`,`visible`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_category`
--

LOCK TABLES `podium_category` WRITE;
/*!40000 ALTER TABLE `podium_category` DISABLE KEYS */;
INSERT INTO `podium_category` VALUES (1,'First category','first-category','','',1,0,1450008024,1450008024);
/*!40000 ALTER TABLE `podium_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_config`
--

DROP TABLE IF EXISTS `podium_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_config` (
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_config`
--

LOCK TABLES `podium_config` WRITE;
/*!40000 ALTER TABLE `podium_config` DISABLE KEYS */;
INSERT INTO `podium_config` VALUES ('activation_token_expire','259200'),('email_token_expire','86400'),('from_email','no-reply@change.me'),('from_name','Podium'),('hot_minimum','20'),('maintenance_mode','0'),('max_attempts','5'),('members_visible','1'),('meta_description','Podium - Yii 2 Forum Module'),('meta_keywords','yii2, forum, podium'),('name','Podium'),('password_reset_token_expire','86400'),('recaptcha_secretkey',''),('recaptcha_sitekey',''),('use_captcha','1'),('version','0.1');
/*!40000 ALTER TABLE `podium_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_content`
--

DROP TABLE IF EXISTS `podium_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `topic` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_content`
--

LOCK TABLES `podium_content` WRITE;
/*!40000 ALTER TABLE `podium_content` DISABLE KEYS */;
INSERT INTO `podium_content` VALUES (1,'terms','Forum Terms and Conditions','Please remember that we are not responsible for any messages posted. We do not vouch for or warrant the accuracy, completeness or usefulness of any post, and are not responsible for the contents of any post.<br><br>The posts express the views of the author of the post, not necessarily the views of this forum. Any user who feels that a posted message is objectionable is encouraged to contact us immediately by email. We have the ability to remove objectionable posts and we will make every effort to do so, within a reasonable time frame, if we determine that removal is necessary.<br><br>You agree, through your use of this service, that you will not use this forum to post any material which is knowingly false and/or defamatory, inaccurate, abusive, vulgar, hateful, harassing, obscene, profane, sexually oriented, threatening, invasive of a person\'s privacy, or otherwise violative of any law.<br><br>You agree not to post any copyrighted material unless the copyright is owned by you or by this forum.'),(2,'email-reg','Welcome to {forum}! This is your activation link','<p>Thank you for registering at {forum}!</p><p>To activate you account open the following link in your Internet browser:<br>{link}<br></p><p>See you soon!<br>{forum}</p>'),(3,'email-pass','{forum} password reset link','<p>{forum} Password Reset</p><p>You are receiving this e-mail because someone has started the process of changing the account password at {forum}.<br>If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br>{forum}</p>'),(4,'email-react','{forum} account reactivation','<p>{forum} Account Activation</p><p>You are receiving this e-mail because someone has started the process of activating the account at {forum}.<br>If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br>{forum}</p>'),(5,'email-new','New e-mail activation link at {forum}','<p>{forum} New E-mail Address Activation</p><p>To activate your new e-mail address open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>Thank you<br>{forum}</p>'),(6,'email-sub','New post in subscribed thread at {forum}','<p>There has been new post added in the thread you are subscribing. Click the following link to read the thread.</p><p>{link}</p><p>See you soon!<br>{forum}</p>');
/*!40000 ALTER TABLE `podium_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_email`
--

DROP TABLE IF EXISTS `podium_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` text COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT '0',
  `attempt` smallint(6) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-email-status` (`status`),
  KEY `fk-email-user_id` (`user_id`),
  CONSTRAINT `fk-email-user_id` FOREIGN KEY (`user_id`) REFERENCES `podium_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_email`
--

LOCK TABLES `podium_email` WRITE;
/*!40000 ALTER TABLE `podium_email` DISABLE KEYS */;
INSERT INTO `podium_email` VALUES (1,2,'drugi@drugi.pl','Welcome to Podium! This is your activation link','<p>Thank you for registering at Podium!</p><p>To activate you account open the following link in your Internet browser:<br><a href=\"http://podium/podium/activate/uQH_5G2kUNpvCGEMDV4-75WjI0JAm9Sr_1449329241\">http://podium/podium/activate/uQH_5G2kUNpvCGEMDV4-75WjI0JAm9Sr_1449329241</a><br></p><p>See you soon!<br>Podium</p>',0,0,1449329241,1449329241);
/*!40000 ALTER TABLE `podium_email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_forum`
--

DROP TABLE IF EXISTS `podium_forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_forum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sub` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `visible` smallint(6) NOT NULL DEFAULT '1',
  `sort` smallint(6) NOT NULL DEFAULT '0',
  `threads` int(11) NOT NULL DEFAULT '0',
  `posts` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-forum-sort` (`sort`,`id`),
  KEY `idx-forum-name` (`name`),
  KEY `idx-forum-display` (`id`,`category_id`),
  KEY `idx-forum-display_slug` (`id`,`category_id`,`slug`),
  KEY `idx-forum-display_guest_slug` (`id`,`category_id`,`slug`,`visible`),
  KEY `fk-forum-category_id` (`category_id`),
  CONSTRAINT `fk-forum-category_id` FOREIGN KEY (`category_id`) REFERENCES `podium_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_forum`
--

LOCK TABLES `podium_forum` WRITE;
/*!40000 ALTER TABLE `podium_forum` DISABLE KEYS */;
INSERT INTO `podium_forum` VALUES (1,1,'First forum','','first-forum','','',1,0,1,1,1450008035,1450008035);
/*!40000 ALTER TABLE `podium_forum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_log`
--

DROP TABLE IF EXISTS `podium_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `level` int(11) DEFAULT NULL,
  `category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_time` double DEFAULT NULL,
  `prefix` text COLLATE utf8_unicode_ci,
  `message` text COLLATE utf8_unicode_ci,
  `model` int(11) DEFAULT NULL,
  `blame` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-log-level` (`level`),
  KEY `idx-log-category` (`category`),
  KEY `idx-log-model` (`model`),
  KEY `idx-log-blame` (`blame`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_log`
--

LOCK TABLES `podium_log` WRITE;
/*!40000 ALTER TABLE `podium_log` DISABLE KEYS */;
INSERT INTO `podium_log` VALUES (1,4,'bizley\\podium\\controllers\\AccountController::actionRegister',1449329241.9586,'[127.0.0.1][-][-]','Activation link queued',2,NULL),(2,1,'bizley\\podium\\models\\Message::remove',1449401262.8424,'[127.0.0.1][2][-]','Message status changing error!',1,2),(3,1,'bizley\\podium\\controllers\\MessagesController::actionDeleteSent',1449401262.8426,'[127.0.0.1][2][-]','Error while deleting sent message',1,2),(4,1,'bizley\\podium\\models\\MessageReceiver::remove',1449401853.175,'[127.0.0.1][1][-]','SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`yii2`.`podium_message_receiver`, CONSTRAINT `fk-message_receiver-message_id` FOREIGN KEY (`message_id`) REFERENCES `podium_message` (`id`) ON UPDATE CASCADE)\nThe SQL being executed was: DELETE FROM `podium_message` WHERE `id`=1',1,1),(5,1,'bizley\\podium\\controllers\\MessagesController::actionDeleteReceived',1449401853.1752,'[127.0.0.1][1][-]','Error while deleting received message',1,1),(6,1,'bizley\\podium\\models\\MessageReceiver::remove',1449405400.7758,'[127.0.0.1][1][-]','Getting unknown property: bizley\\podium\\models\\Message::status',6,1),(7,1,'bizley\\podium\\controllers\\MessagesController::actionDeleteReceived',1449405400.776,'[127.0.0.1][1][-]','Error while deleting received message',6,1),(8,1,'bizley\\podium\\models\\MessageReceiver::remove',1449405533.1068,'[127.0.0.1][1][-]','Getting unknown property: bizley\\podium\\models\\Message::status',6,1),(9,1,'bizley\\podium\\controllers\\MessagesController::actionDeleteReceived',1449405533.107,'[127.0.0.1][1][-]','Error while deleting received message',6,1),(10,1,'bizley\\podium\\models\\MessageReceiver::remove',1449405663.6106,'[127.0.0.1][1][-]','Message status changing error!',6,1),(11,1,'bizley\\podium\\controllers\\MessagesController::actionDeleteReceived',1449405663.6108,'[127.0.0.1][1][-]','Error while deleting received message',6,1),(12,1,'bizley\\podium\\models\\MessageReceiver::remove',1449405728.3507,'[127.0.0.1][1][-]','Message status changing error!',6,1),(13,1,'bizley\\podium\\controllers\\MessagesController::actionDeleteReceived',1449405728.3509,'[127.0.0.1][1][-]','Error while deleting received message',6,1),(14,4,'bizley\\podium\\controllers\\AdminController::actionNewCategory',1450008024.3778,'[127.0.0.1][1][-]','Category added',1,1),(15,4,'bizley\\podium\\controllers\\AdminController::actionNewForum',1450008035.543,'[127.0.0.1][1][-]','Forum added',1,1),(16,4,'bizley\\podium\\controllers\\DefaultController::actionNewThread',1450008147.0741,'[127.0.0.1][1][-]','Thread added',1,1);
/*!40000 ALTER TABLE `podium_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_message`
--

DROP TABLE IF EXISTS `podium_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `topic` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `sender_status` smallint(6) NOT NULL DEFAULT '1',
  `replyto` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-message-topic` (`topic`),
  KEY `idx-message-replyto` (`replyto`),
  KEY `idx-message-sent` (`sender_id`,`sender_status`),
  CONSTRAINT `fk-message-sender_id` FOREIGN KEY (`sender_id`) REFERENCES `podium_user` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_message`
--

LOCK TABLES `podium_message` WRITE;
/*!40000 ALTER TABLE `podium_message` DISABLE KEYS */;
INSERT INTO `podium_message` VALUES (3,1,'Re: sdsfdsf','<p>rrrrrrrrrrrrrrrrrrrrrrrrrrrrr<br /></p>',20,1,1449344616,1449407848),(4,2,'Re: Re: sdsfdsf','<p>Test test test<br /></p>',10,3,1449397252,1449397252),(6,2,'ttttttttttt','<p>tttttttttttttttt<br /></p>',10,0,1449405179,1449405179),(7,1,'sdsadasdasd','<p style=\"text-align:right;\"><strong>bbbb</strong></p>\r\n\r\n<p><em>iiiiii</em></p>\r\n\r\n<p><u>uuuu</u></p>\r\n\r\n<p><s>sssss</s></p>\r\n\r\n<ol><li style=\"text-align:right;\">sdasd</li>\r\n	<li>231231</li>\r\n</ol><ul><li>fgfdgfdg</li>\r\n	<li>dfgdg</li>\r\n</ul><p>sdaasdasda</p>\r\n\r\n<p style=\"text-align:center;\">assdadas</p>\r\n\r\n<p style=\"text-align:right;\">asdasdsd</p>\r\n\r\n<p style=\"text-align:right;\"><a href=\"http://aaaa.pl\">asdasdasd</a></p>\r\n\r\n<p>sdasd<br />\r\ndsfdsfsdfdsf</p>\r\n',10,0,1449598598,1449598598);
/*!40000 ALTER TABLE `podium_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_message_receiver`
--

DROP TABLE IF EXISTS `podium_message_receiver`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_message_receiver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_status` smallint(6) NOT NULL DEFAULT '1',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-message_receiver-inbox` (`receiver_id`,`receiver_status`),
  KEY `fk-message_receiver-message_id` (`message_id`),
  CONSTRAINT `fk-message_receiver-message_id` FOREIGN KEY (`message_id`) REFERENCES `podium_message` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk-message_receiver-receiver_id` FOREIGN KEY (`receiver_id`) REFERENCES `podium_user` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_message_receiver`
--

LOCK TABLES `podium_message_receiver` WRITE;
/*!40000 ALTER TABLE `podium_message_receiver` DISABLE KEYS */;
INSERT INTO `podium_message_receiver` VALUES (3,3,2,10,1449344616,1449397719),(4,4,1,10,1449397252,1449402436),(6,6,1,20,1449405179,1449407350),(7,7,2,1,1449598598,1449598598);
/*!40000 ALTER TABLE `podium_message_receiver` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_moderator`
--

DROP TABLE IF EXISTS `podium_moderator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_moderator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `forum_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-moderator-user_id` (`user_id`),
  KEY `fk-moderator-forum_id` (`forum_id`),
  CONSTRAINT `fk-moderator-forum_id` FOREIGN KEY (`forum_id`) REFERENCES `podium_forum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-moderator-user_id` FOREIGN KEY (`user_id`) REFERENCES `podium_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_moderator`
--

LOCK TABLES `podium_moderator` WRITE;
/*!40000 ALTER TABLE `podium_moderator` DISABLE KEYS */;
/*!40000 ALTER TABLE `podium_moderator` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_post`
--

DROP TABLE IF EXISTS `podium_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `thread_id` int(11) NOT NULL,
  `forum_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `edited` smallint(6) NOT NULL DEFAULT '0',
  `likes` smallint(6) NOT NULL DEFAULT '0',
  `dislikes` smallint(6) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `edited_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx-post-updated_at` (`updated_at`),
  KEY `idx-post-created_at` (`created_at`),
  KEY `idx-post-edited_at` (`edited_at`),
  KEY `idx-post-identify` (`id`,`thread_id`,`forum_id`),
  KEY `fk-post-thread_id` (`thread_id`),
  KEY `fk-post-forum_id` (`forum_id`),
  CONSTRAINT `fk-post-forum_id` FOREIGN KEY (`forum_id`) REFERENCES `podium_forum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-post-thread_id` FOREIGN KEY (`thread_id`) REFERENCES `podium_thread` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_post`
--

LOCK TABLES `podium_post` WRITE;
/*!40000 ALTER TABLE `podium_post` DISABLE KEYS */;
INSERT INTO `podium_post` VALUES (1,'<p>First post</p>\r\n',1,1,1,0,0,0,1450008147,1450008147,0);
/*!40000 ALTER TABLE `podium_post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_post_thumb`
--

DROP TABLE IF EXISTS `podium_post_thumb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_post_thumb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `thumb` smallint(6) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-post_thumb-user_id` (`user_id`),
  KEY `fk-post_thumb-post_id` (`post_id`),
  CONSTRAINT `fk-post_thumb-post_id` FOREIGN KEY (`post_id`) REFERENCES `podium_post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-post_thumb-user_id` FOREIGN KEY (`user_id`) REFERENCES `podium_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_post_thumb`
--

LOCK TABLES `podium_post_thumb` WRITE;
/*!40000 ALTER TABLE `podium_post_thumb` DISABLE KEYS */;
/*!40000 ALTER TABLE `podium_post_thumb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_subscription`
--

DROP TABLE IF EXISTS `podium_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `post_seen` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk-subscription-user_id` (`user_id`),
  KEY `fk-subscription-thread_id` (`thread_id`),
  CONSTRAINT `fk-subscription-thread_id` FOREIGN KEY (`thread_id`) REFERENCES `podium_thread` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-subscription-user_id` FOREIGN KEY (`user_id`) REFERENCES `podium_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_subscription`
--

LOCK TABLES `podium_subscription` WRITE;
/*!40000 ALTER TABLE `podium_subscription` DISABLE KEYS */;
INSERT INTO `podium_subscription` VALUES (1,1,1,1);
/*!40000 ALTER TABLE `podium_subscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_thread`
--

DROP TABLE IF EXISTS `podium_thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_thread` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) NOT NULL,
  `forum_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `pinned` smallint(6) NOT NULL DEFAULT '0',
  `locked` smallint(6) NOT NULL DEFAULT '0',
  `posts` int(11) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `new_post_at` int(11) NOT NULL,
  `edited_post_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-thread-name` (`name`),
  KEY `idx-thread-created_at` (`created_at`),
  KEY `idx-thread-display` (`id`,`category_id`,`forum_id`),
  KEY `idx-thread-display_slug` (`id`,`category_id`,`forum_id`,`slug`),
  KEY `idx-thread-sort` (`pinned`,`updated_at`,`id`),
  KEY `idx-thread-sort_author` (`updated_at`,`id`),
  KEY `fk-thread-category_id` (`category_id`),
  KEY `fk-thread-forum_id` (`forum_id`),
  CONSTRAINT `fk-thread-category_id` FOREIGN KEY (`category_id`) REFERENCES `podium_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-thread-forum_id` FOREIGN KEY (`forum_id`) REFERENCES `podium_forum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_thread`
--

LOCK TABLES `podium_thread` WRITE;
/*!40000 ALTER TABLE `podium_thread` DISABLE KEYS */;
INSERT INTO `podium_thread` VALUES (1,'First topic','first-topic',1,1,1,0,0,1,1,1450008147,1450008147,1450008147,1450008147);
/*!40000 ALTER TABLE `podium_thread` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_thread_view`
--

DROP TABLE IF EXISTS `podium_thread_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_thread_view` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `new_last_seen` int(11) NOT NULL,
  `edited_last_seen` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-thread_view-user_id` (`user_id`),
  KEY `fk-thread_view-thread_id` (`thread_id`),
  CONSTRAINT `fk-thread_view-thread_id` FOREIGN KEY (`thread_id`) REFERENCES `podium_thread` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-thread_view-user_id` FOREIGN KEY (`user_id`) REFERENCES `podium_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_thread_view`
--

LOCK TABLES `podium_thread_view` WRITE;
/*!40000 ALTER TABLE `podium_thread_view` DISABLE KEYS */;
INSERT INTO `podium_thread_view` VALUES (1,1,1,1450008147,1450008147);
/*!40000 ALTER TABLE `podium_thread_view` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_user`
--

DROP TABLE IF EXISTS `podium_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inherited_id` int(11) NOT NULL DEFAULT '0',
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `new_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `anonymous` smallint(6) NOT NULL DEFAULT '0',
  `status` smallint(6) NOT NULL DEFAULT '1',
  `role` smallint(6) NOT NULL DEFAULT '1',
  `timezone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-user-username` (`username`),
  KEY `idx-user-status` (`status`),
  KEY `idx-user-role` (`role`),
  KEY `idx-user-email` (`email`),
  KEY `idx-user-mod` (`status`,`role`),
  KEY `idx-user-find_email` (`status`,`email`),
  KEY `idx-user-find_username` (`status`,`username`),
  KEY `idx-user-password_reset_token` (`password_reset_token`),
  KEY `idx-user-activation_token` (`activation_token`),
  KEY `idx-user-email_token` (`email_token`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_user`
--

LOCK TABLES `podium_user` WRITE;
/*!40000 ALTER TABLE `podium_user` DISABLE KEYS */;
INSERT INTO `podium_user` VALUES (1,0,'admin','admin','MVYSutqITBSV-oMndtA7SxqF1SIYmigp','$2y$13$0nXNPD70SPDYGsC7F28nTO40IhzGS/hlO5499Qx/IZPuxOK3wzbvG',NULL,NULL,NULL,'',NULL,0,10,10,'UTC',1449307991,1449307991),(2,0,'','','K-Uih2DzC462-o8N-D9uOBWYooezr3VF','$2y$13$UWKN.Ivx6um31DfNpZ1uNeMuEFs68loRWJjq7qCaLheDQwc7TbXCG',NULL,'uQH_5G2kUNpvCGEMDV4-75WjI0JAm9Sr_1449329241',NULL,'drugi@drugi.pl',NULL,0,10,1,NULL,1449329241,1449329241);
/*!40000 ALTER TABLE `podium_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_user_activity`
--

DROP TABLE IF EXISTS `podium_user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_user_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_slug` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_role` int(11) DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `anonymous` smallint(6) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-user_activity-updated_at` (`updated_at`),
  KEY `idx-user_activity-members` (`updated_at`,`user_id`,`anonymous`),
  KEY `idx-user_activity-guests` (`updated_at`,`user_id`),
  KEY `fk-user_activity-user_id` (`user_id`),
  CONSTRAINT `fk-user_activity-user_id` FOREIGN KEY (`user_id`) REFERENCES `podium_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_user_activity`
--

LOCK TABLES `podium_user_activity` WRITE;
/*!40000 ALTER TABLE `podium_user_activity` DISABLE KEYS */;
INSERT INTO `podium_user_activity` VALUES (1,NULL,NULL,NULL,NULL,'/podium/login','127.0.0.1',0,1449310612,1450007990),(2,1,'admin','admin',10,'/','127.0.0.1',0,1449328263,1450008390),(3,2,'Member#2','member-2',1,'/podium/messages/inbox','127.0.0.1',0,1449397130,1449405179);
/*!40000 ALTER TABLE `podium_user_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_user_ignore`
--

DROP TABLE IF EXISTS `podium_user_ignore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_user_ignore` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ignored_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-user_ignore-user_id` (`user_id`),
  KEY `fk-user_ignore-ignored_id` (`ignored_id`),
  CONSTRAINT `fk-user_ignore-ignored_id` FOREIGN KEY (`ignored_id`) REFERENCES `podium_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-user_ignore-user_id` FOREIGN KEY (`user_id`) REFERENCES `podium_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_user_ignore`
--

LOCK TABLES `podium_user_ignore` WRITE;
/*!40000 ALTER TABLE `podium_user_ignore` DISABLE KEYS */;
/*!40000 ALTER TABLE `podium_user_ignore` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_user_meta`
--

DROP TABLE IF EXISTS `podium_user_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_user_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `location` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `signature` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `gravatar` smallint(6) NOT NULL DEFAULT '0',
  `avatar` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-user_meta-user_id` (`user_id`),
  CONSTRAINT `fk-user_meta-user_id` FOREIGN KEY (`user_id`) REFERENCES `podium_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_user_meta`
--

LOCK TABLES `podium_user_meta` WRITE;
/*!40000 ALTER TABLE `podium_user_meta` DISABLE KEYS */;
/*!40000 ALTER TABLE `podium_user_meta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_vocabulary`
--

DROP TABLE IF EXISTS `podium_vocabulary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_vocabulary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-vocabulary-word` (`word`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_vocabulary`
--

LOCK TABLES `podium_vocabulary` WRITE;
/*!40000 ALTER TABLE `podium_vocabulary` DISABLE KEYS */;
INSERT INTO `podium_vocabulary` VALUES (1,'First'),(2,'post');
/*!40000 ALTER TABLE `podium_vocabulary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `podium_vocabulary_junction`
--

DROP TABLE IF EXISTS `podium_vocabulary_junction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `podium_vocabulary_junction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-vocabulary_junction-word_id` (`word_id`),
  KEY `fk-vocabulary_junction-post_id` (`post_id`),
  CONSTRAINT `fk-vocabulary_junction-post_id` FOREIGN KEY (`post_id`) REFERENCES `podium_post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-vocabulary_junction-word_id` FOREIGN KEY (`word_id`) REFERENCES `podium_vocabulary` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `podium_vocabulary_junction`
--

LOCK TABLES `podium_vocabulary_junction` WRITE;
/*!40000 ALTER TABLE `podium_vocabulary_junction` DISABLE KEYS */;
INSERT INTO `podium_vocabulary_junction` VALUES (1,1,1),(2,2,1);
/*!40000 ALTER TABLE `podium_vocabulary_junction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `new_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT '1',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'testowy-user','lnPkTi6LodWhtF-ESCU9l0pdiTcqeTGK','$2y$13$AN4ifuk7wq4zeagBRw3.ZeWZ0A6/zJ/VJsnwvcczeEEA9qlvYWO0G',NULL,NULL,NULL,'paw@bizley.pl',NULL,10,1448991559,1448991559),(2,'drugi','C1m6pABWTJd_RPo5ustHlU2Ybw78M3NE','$2y$13$m9DgS/fFY12eaGY.lheViO2A3Hy.f2JnsPTJG2A6YHzkxOqyNvcKG',NULL,NULL,NULL,'drugi@drugi.pl',NULL,10,1449003126,1449003126);
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

-- Dump completed on 2015-12-13 13:23:14
