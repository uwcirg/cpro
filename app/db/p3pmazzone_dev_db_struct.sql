-- MySQL dump 10.13  Distrib 5.1.63, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: p3pmazzone_dev
-- ------------------------------------------------------
-- Server version	5.1.63-0+squeeze1

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
-- Table structure for table `Sheet1`
--

DROP TABLE IF EXISTS `Sheet1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Sheet1` (
  `location` int(4) DEFAULT NULL,
  `source` text,
  `target` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acos`
--

DROP TABLE IF EXISTS `acos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `acos_idx1` (`lft`,`rght`),
  KEY `acos_idx2` (`alias`)
) ENGINE=MyISAM AUTO_INCREMENT=257 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `activity_diary_entries`
--

DROP TABLE IF EXISTS `activity_diary_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_diary_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `dt_created` datetime NOT NULL,
  `dt_last_edit` datetime NOT NULL,
  `date` date NOT NULL,
  `fatigue` int(1) DEFAULT NULL,
  `type` enum('Walking','Biking','Running','Other','None') COLLATE utf8_unicode_ci DEFAULT NULL,
  `typeOther` text COLLATE utf8_unicode_ci,
  `minutes` int(4) unsigned DEFAULT NULL,
  `steps` int(6) unsigned DEFAULT NULL,
  `note` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_date_index` (`patient_id`,`date`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alerts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL,
  `target_type` enum('item','subscale','scale') NOT NULL,
  `target_id` int(10) unsigned NOT NULL,
  `comparison` enum('<','>','=') NOT NULL,
  `value` float NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `answers`
--

DROP TABLE IF EXISTS `answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_session_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `state` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body_text` text COLLATE utf8_unicode_ci,
  `value` text COLLATE utf8_unicode_ci,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `question_id` (`survey_session_id`,`question_id`,`option_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7697 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `created_staff_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=173 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aros`
--

DROP TABLE IF EXISTS `aros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aros` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aros_idx1` (`lft`,`rght`),
  KEY `aros_idx2` (`alias`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aros_acos`
--

DROP TABLE IF EXISTS `aros_acos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aros_acos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aro_id` int(10) NOT NULL,
  `aco_id` int(10) NOT NULL,
  `_create` varchar(2) NOT NULL DEFAULT '0',
  `_read` varchar(2) NOT NULL DEFAULT '0',
  `_update` varchar(2) NOT NULL DEFAULT '0',
  `_delete` varchar(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ARO_ACO_KEY` (`aro_id`,`aco_id`)
) ENGINE=MyISAM AUTO_INCREMENT=302 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `aros_acos_interpreted_view`
--

DROP TABLE IF EXISTS `aros_acos_interpreted_view`;
/*!50001 DROP VIEW IF EXISTS `aros_acos_interpreted_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `aros_acos_interpreted_view` (
  `id` int(10),
  `aro_id` int(10),
  `aros_alias` varchar(255),
  `aco_id` int(10),
  `acos_alias` varchar(255),
  `_create` varchar(2),
  `_read` varchar(2),
  `_update` varchar(2),
  `_delete` varchar(2)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `associates`
--

DROP TABLE IF EXISTS `associates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `associates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT 'REMOVE THIS JUNK',
  `verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'OBSOLETE, see patients_associates.has_entered_secret_phrase',
  `webkey` int(10) unsigned NOT NULL COMMENT 'OBSOLETE; SEE patients_associates',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cake_sessions`
--

DROP TABLE IF EXISTS `cake_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cake_sessions` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `expires` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `changed_questions_for_bryan`
--

DROP TABLE IF EXISTS `changed_questions_for_bryan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `changed_questions_for_bryan` (
  `id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) DEFAULT NULL,
  `ShortTitle` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `ShortTitle_es_MX` text CHARACTER SET utf8,
  `BodyText` text CHARACTER SET utf8,
  `BodyText_es_MX` text CHARACTER SET utf8,
  `BodyTextPosition` enum('above','left') CHARACTER SET utf8 NOT NULL DEFAULT 'left',
  `Orientation` enum('vertical','horizontal') CHARACTER SET utf8 NOT NULL DEFAULT 'vertical',
  `Groups` int(11) NOT NULL DEFAULT '1',
  `Style` enum('normal','hidden') CHARACTER SET utf8 NOT NULL DEFAULT 'normal',
  `Sequence` int(11) NOT NULL DEFAULT '0',
  `has_conditional_options` tinyint(4) NOT NULL DEFAULT '0',
  `ignore_skipped` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'If == 1, don''t include this in lists of skipped questions; for eg "if you drive a car, answer..."'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clinics`
--

DROP TABLE IF EXISTS `clinics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `friendly_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `support_email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `support_phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `one_usual_care_session` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true, after the 1st survey session the patient is either moved to "usual care" or "off-project"',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conditions`
--

DROP TABLE IF EXISTS `conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_type` enum('Page','Questionnaire','Question','Option') DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `condition` text,
  PRIMARY KEY (`id`),
  KEY `condition_id` (`id`),
  KEY `target_id` (`target_id`),
  KEY `target_type` (`target_type`)
) ENGINE=MyISAM AUTO_INCREMENT=419 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consents`
--

DROP TABLE IF EXISTS `consents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consents` (
  `id` int(11) NOT NULL COMMENT ' ',
  `patient_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='used for synchronization when assigning patients to control/';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `name_es_MX` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `question_id` int(11) NOT NULL,
  `subscale_id` int(11) NOT NULL,
  `base` smallint(6) NOT NULL DEFAULT '0' COMMENT 'NOT USED ANYMORE!',
  `range` float NOT NULL DEFAULT '4' COMMENT 'num options - 1',
  `sequence` smallint(6) DEFAULT NULL COMMENT 'sequence w/in subscale; only known use: default sequence w/in p3p factor group',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='this is a join on questions & subscales';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `itemsThroughScalesByProject`
--

DROP TABLE IF EXISTS `itemsThroughScalesByProject`;
/*!50001 DROP VIEW IF EXISTS `itemsThroughScalesByProject`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `itemsThroughScalesByProject` (
  `item_id` int(11),
  `question_id` int(11),
  `subscale_id` int(11),
  `subscale_name` varchar(60),
  `subscale_order` smallint(6),
  `scale_id` int(11),
  `scale_name` varchar(60),
  `scale_order` smallint(6),
  `questionnaire_id` int(11),
  `project_id` int(11)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `items_es_MX_export`
--

DROP TABLE IF EXISTS `items_es_MX_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items_es_MX_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `name_es_MX` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='this is a join on questions & subscales';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `patient_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `display` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locale_selections`
--

DROP TABLE IF EXISTS `locale_selections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locale_selections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `locale` enum('en_US','es_MX') COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=558 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `account_id` int(11) unsigned NOT NULL,
  `controller` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `params` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` datetime NOT NULL,
  `ip_address` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `user_agent` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=163203 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `logs_intervention_non_test`
--

DROP TABLE IF EXISTS `logs_intervention_non_test`;
/*!50001 DROP VIEW IF EXISTS `logs_intervention_non_test`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `logs_intervention_non_test` (
  `id` int(10) unsigned,
  `user_id` int(11) unsigned,
  `controller` varchar(20),
  `action` varchar(64),
  `params` varchar(512),
  `time` datetime
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `logs_intervention_non_test_count`
--

DROP TABLE IF EXISTS `logs_intervention_non_test_count`;
/*!50001 DROP VIEW IF EXISTS `logs_intervention_non_test_count`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `logs_intervention_non_test_count` (
  `COUNT(*)` bigint(21)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL COMMENT 'foreign key into patients table',
  `text` varchar(10000) COLLATE utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL COMMENT 'foreign key into users table',
  `created` datetime NOT NULL,
  `flagged` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if flagged, 0 if not',
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `author_id` (`author_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) DEFAULT NULL,
  `OptionType` enum('radio','checkbox','dropdown','text','textbox','combo-radio','combo-check','button-yes','button-no','imagemap','none') NOT NULL DEFAULT 'radio',
  `Height` int(11) NOT NULL DEFAULT '0',
  `Width` int(11) NOT NULL DEFAULT '0',
  `MaxCharacters` int(11) NOT NULL DEFAULT '0',
  `AnalysisValue` varchar(32) DEFAULT NULL,
  `ValueRestriction` varchar(128) DEFAULT NULL,
  `BodyText` text,
  `BodyText_es_MX` text,
  `BodyTextType` enum('visible','invisible') NOT NULL DEFAULT 'visible',
  `Sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `DATA_OUTPUT` (`question_id`,`Sequence`)
) ENGINE=MyISAM AUTO_INCREMENT=7115 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `options_es_export`
--

DROP TABLE IF EXISTS `options_es_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options_es_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) DEFAULT NULL,
  `BodyText` text,
  `BodyText_es_MX` text,
  PRIMARY KEY (`id`),
  KEY `DATA_OUTPUT` (`question_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7115 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `options_no_es`
--

DROP TABLE IF EXISTS `options_no_es`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options_no_es` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) DEFAULT NULL,
  `OptionType` enum('radio','checkbox','dropdown','text','textbox','combo-radio','combo-check','button-yes','button-no','imagemap','none') NOT NULL DEFAULT 'radio',
  `Height` int(11) NOT NULL DEFAULT '0',
  `Width` int(11) NOT NULL DEFAULT '0',
  `MaxCharacters` int(11) NOT NULL DEFAULT '0',
  `AnalysisValue` varchar(32) DEFAULT NULL,
  `ValueRestriction` varchar(128) DEFAULT NULL,
  `BodyText` text,
  `BodyText_es_MX` text,
  `BodyTextType` enum('visible','invisible') NOT NULL DEFAULT 'visible',
  `Sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `DATA_OUTPUT` (`question_id`,`Sequence`)
) ENGINE=MyISAM AUTO_INCREMENT=7115 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `p3p_teachings`
--

DROP TABLE IF EXISTS `p3p_teachings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `p3p_teachings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'if null, items.name will be used',
  `label_es_MX` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `action` enum('statistics','factors','control','next_steps') COLLATE utf8_unicode_ci NOT NULL,
  `intervention_text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `intervention_text_es_MX` longtext COLLATE utf8_unicode_ci,
  `video` int(3) DEFAULT NULL,
  `Sequence` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `p3p_teachings_bak012912`
--

DROP TABLE IF EXISTS `p3p_teachings_bak012912`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `p3p_teachings_bak012912` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'if null, items.name will be used',
  `item_id` int(11) DEFAULT NULL,
  `action` enum('statistics','factors','control','next_steps') COLLATE utf8_unicode_ci NOT NULL,
  `intervention_text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `video` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `p3p_teachings_csv2po`
--

DROP TABLE IF EXISTS `p3p_teachings_csv2po`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `p3p_teachings_csv2po` (
  `id` int(11) NOT NULL DEFAULT '0',
  `intervention_text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `intervention_text_es_MX` longtext COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `p3p_teachings_es_export`
--

DROP TABLE IF EXISTS `p3p_teachings_es_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `p3p_teachings_es_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'if null, items.name will be used',
  `label_es_MX` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `intervention_text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `intervention_text_es_MX` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `p3p_teachings_for_bryan`
--

DROP TABLE IF EXISTS `p3p_teachings_for_bryan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `p3p_teachings_for_bryan` (
  `id` int(11) NOT NULL DEFAULT '0',
  `intervention_text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `intervention_text_es_MX` longtext COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `p3p_teachings_no_es`
--

DROP TABLE IF EXISTS `p3p_teachings_no_es`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `p3p_teachings_no_es` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'if null, items.name will be used',
  `label_es_MX` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `action` enum('statistics','factors','control','next_steps') COLLATE utf8_unicode_ci NOT NULL,
  `intervention_text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `intervention_text_es_MX` longtext COLLATE utf8_unicode_ci,
  `video` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `p3p_teachings_rev2013.02.19`
--

DROP TABLE IF EXISTS `p3p_teachings_rev2013.02.19`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `p3p_teachings_rev2013.02.19` (
  `location` int(11) DEFAULT NULL,
  `source` text COLLATE utf8_unicode_ci,
  `target` text COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionnaire_id` int(11) DEFAULT NULL,
  `Title` varchar(64) DEFAULT NULL,
  `Title_es_MX` text,
  `Header` text,
  `Header_es_MX` text,
  `BodyText` text,
  `BodyText_es_MX` text,
  `NavigationType` enum('prev-next','next','none','prev') NOT NULL DEFAULT 'prev-next',
  `TargetType` varchar(32) DEFAULT NULL,
  `ProgressType` enum('text','graphical','none') NOT NULL DEFAULT 'graphical',
  `LayoutType` enum('basic','embedded') NOT NULL DEFAULT 'basic',
  `Sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1475 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_backup`
--

DROP TABLE IF EXISTS `pages_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_backup` (
  `id` int(11) NOT NULL DEFAULT '0',
  `questionnaire_id` int(11) DEFAULT NULL,
  `Title` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `Title_es_MX` text CHARACTER SET utf8,
  `Header` text CHARACTER SET utf8,
  `Header_es_MX` text CHARACTER SET utf8,
  `BodyText` text CHARACTER SET utf8,
  `BodyText_es_MX` text CHARACTER SET utf8,
  `NavigationType` enum('prev-next','next','none','prev') CHARACTER SET utf8 NOT NULL DEFAULT 'prev-next',
  `TargetType` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `ProgressType` enum('text','graphical','none') CHARACTER SET utf8 NOT NULL DEFAULT 'graphical',
  `LayoutType` enum('basic','embedded') CHARACTER SET utf8 NOT NULL DEFAULT 'basic',
  `Sequence` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_es_export`
--

DROP TABLE IF EXISTS `pages_es_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_es_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionnaire_id` int(11) DEFAULT NULL,
  `Title` varchar(64) DEFAULT NULL,
  `Title_es_MX` text,
  `Header` text,
  `Header_es_MX` text,
  `BodyText` text,
  `BodyText_es_MX` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1473 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_for_bryan`
--

DROP TABLE IF EXISTS `pages_for_bryan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_for_bryan` (
  `id` int(11) NOT NULL DEFAULT '0',
  `questionnaire_id` int(11) DEFAULT NULL,
  `Title` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `Title_es_MX` text CHARACTER SET utf8,
  `Header` text CHARACTER SET utf8,
  `Header_es_MX` text CHARACTER SET utf8,
  `BodyText` text CHARACTER SET utf8,
  `BodyText_es_MX` text CHARACTER SET utf8,
  `NavigationType` enum('prev-next','next','none','prev') CHARACTER SET utf8 NOT NULL DEFAULT 'prev-next',
  `TargetType` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `ProgressType` enum('text','graphical','none') CHARACTER SET utf8 NOT NULL DEFAULT 'graphical',
  `LayoutType` enum('basic','embedded') CHARACTER SET utf8 NOT NULL DEFAULT 'basic',
  `Sequence` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_no_es`
--

DROP TABLE IF EXISTS `pages_no_es`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_no_es` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionnaire_id` int(11) DEFAULT NULL,
  `Title` varchar(64) DEFAULT NULL,
  `Title_es_MX` text,
  `Header` text,
  `Header_es_MX` text,
  `BodyText` text,
  `BodyText_es_MX` text,
  `NavigationType` enum('prev-next','next','none','prev') NOT NULL DEFAULT 'prev-next',
  `TargetType` varchar(32) DEFAULT NULL,
  `ProgressType` enum('text','graphical','none') NOT NULL DEFAULT 'graphical',
  `LayoutType` enum('basic','embedded') NOT NULL DEFAULT 'basic',
  `Sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1474 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `participant_demographics`
--

DROP TABLE IF EXISTS `participant_demographics`;
/*!50001 DROP VIEW IF EXISTS `participant_demographics`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `participant_demographics` (
  `patient id` int(11),
  `gender` enum('male','female'),
  `clinic` varchar(40),
  `survey_session` int(11)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `patient_T1s`
--

DROP TABLE IF EXISTS `patient_T1s`;
/*!50001 DROP VIEW IF EXISTS `patient_T1s`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `patient_T1s` (
  `patient` int(11),
  `clinic` varchar(40),
  `survey_session` int(11)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `patient_view_notes`
--

DROP TABLE IF EXISTS `patient_view_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_view_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL COMMENT 'foreign key into patients table',
  `text` varchar(10000) COLLATE utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL COMMENT 'foreign key into users table',
  `lastmod` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `author_id` (`author_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `MRN` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `test_flag` tinyint(1) NOT NULL,
  `phone1` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone2` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mailing_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `study_participation_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if they requested to learn more about the study',
  `user_type` enum('Home/Independent','Clinic/Assisted') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'used in randomization algorithm; should not be null if consent_status=consented',
  `consent_status` enum('usual care','pre-consent','consented','declined','ineligible','off-project') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'usual care' COMMENT 'consented indicates that the patient is a participant',
  `consent_date` date DEFAULT NULL,
  `consenter_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `consent_checked` tinyint(1) NOT NULL DEFAULT '0',
  `hipaa_consent_checked` tinyint(1) NOT NULL DEFAULT '0',
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery') COLLATE utf8_unicode_ci DEFAULT 'MedOnc',
  `treatment_start_date` date DEFAULT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('male','female') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Settable by survey or patient editor',
  `ethnicity` varchar(15) COLLATE utf8_unicode_ci NOT NULL COMMENT 'apparently not used',
  `eligible_flag` tinyint(1) NOT NULL COMMENT 'apparently not used',
  `study_group` enum('Control','Treatment') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field is redundant given user_acl_leafs table, but still used; only set if patient has consented',
  `secret_phrase` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t1` datetime NOT NULL,
  `t2` datetime DEFAULT NULL,
  `t3` datetime DEFAULT NULL,
  `t4` datetime DEFAULT NULL,
  `t1_location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t2_location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t3_location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t4_location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t1_staff_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `t2_staff_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `t3_staff_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `t4_staff_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `check_again_date` date DEFAULT NULL COMMENT 'date when someone should view this patient record and take action',
  `no_more_check_agains` tinyint(1) NOT NULL DEFAULT '0',
  `t2a_subscale_id` int(11) DEFAULT NULL,
  `t2b_subscale_id` int(11) DEFAULT NULL,
  `off_study_status` enum('Completed all study requirements','Ineligible','Voluntary withdrawal','Lost to follow-up','Adverse effects','Other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `off_study_reason` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `off_study_timestamp` timestamp NULL DEFAULT NULL,
  `72_hr_follow_up` tinyint(1) NOT NULL DEFAULT '0',
  `farthestStepInIntervention` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ie ''priorities'', or ''factors.46''',
  `requestedTopicHomeCare` tinyint(1) DEFAULT NULL,
  `requestedTopicFamilyImpact` tinyint(1) DEFAULT NULL,
  `requestedTopicFamilyRisk` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `birthdate` (`birthdate`),
  KEY `off_study_status` (`off_study_status`),
  KEY `off_study_timestamp` (`off_study_timestamp`),
  KEY `consent_checked` (`consent_checked`),
  KEY `consent_status` (`consent_status`),
  KEY `hipaa_consent_checked` (`hipaa_consent_checked`)
) ENGINE=MyISAM AUTO_INCREMENT=198 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patients_associates`
--

DROP TABLE IF EXISTS `patients_associates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients_associates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `webkey` int(10) NOT NULL,
  `has_entered_secret_phrase` tinyint(1) NOT NULL,
  `share_journal` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patients_associates_deleted`
--

DROP TABLE IF EXISTS `patients_associates_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients_associates_deleted` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `webkey` int(10) NOT NULL,
  `has_entered_secret_phrase` tinyint(1) NOT NULL,
  `share_journal` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patients_associates_subscales`
--

DROP TABLE IF EXISTS `patients_associates_subscales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients_associates_subscales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_associate_id` int(11) NOT NULL,
  `subscale_id` int(11) NOT NULL,
  `shared` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_associate_id` (`patient_associate_id`,`subscale_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `patients_clinics_view`
--

DROP TABLE IF EXISTS `patients_clinics_view`;
/*!50001 DROP VIEW IF EXISTS `patients_clinics_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `patients_clinics_view` (
  `name` varchar(40),
  `id` int(11),
  `MRN` varchar(10),
  `test_flag` tinyint(1),
  `phone1` varchar(20),
  `phone2` varchar(20),
  `mailing_address` varchar(255),
  `study_participation_flag` tinyint(1),
  `user_type` enum('Home/Independent','Clinic/Assisted'),
  `consent_status` enum('usual care','pre-consent','consented','declined','ineligible','off-project'),
  `consent_date` date,
  `consenter_id` int(11),
  `consent_checked` tinyint(1),
  `hipaa_consent_checked` tinyint(1),
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery'),
  `treatment_start_date` date,
  `birthdate` date,
  `gender` enum('male','female'),
  `ethnicity` varchar(15),
  `eligible_flag` tinyint(1),
  `study_group` enum('Control','Treatment'),
  `secret_phrase` varchar(50),
  `t1` datetime,
  `t2` datetime,
  `t3` datetime,
  `t4` datetime,
  `t1_location` varchar(100),
  `t2_location` varchar(100),
  `t3_location` varchar(100),
  `t4_location` varchar(100),
  `t1_staff_id` int(11),
  `t2_staff_id` int(11),
  `t3_staff_id` int(11),
  `t4_staff_id` int(11),
  `check_again_date` date,
  `no_more_check_agains` tinyint(1),
  `t2a_subscale_id` int(11),
  `t2b_subscale_id` int(11),
  `off_study_status` enum('Completed all study requirements','Ineligible','Voluntary withdrawal','Lost to follow-up','Adverse effects','Other'),
  `off_study_reason` varchar(500),
  `off_study_timestamp` timestamp
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `patients_clinics_view_count`
--

DROP TABLE IF EXISTS `patients_clinics_view_count`;
/*!50001 DROP VIEW IF EXISTS `patients_clinics_view_count`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `patients_clinics_view_count` (
  `COUNT(*)` bigint(21)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `patients_deleted`
--

DROP TABLE IF EXISTS `patients_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients_deleted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `MRN` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `test_flag` tinyint(1) NOT NULL,
  `phone1` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone2` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mailing_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `study_participation_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if they requested to learn more about the study',
  `user_type` enum('Home/Independent','Clinic/Assisted') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'used in randomization algorithm; should not be null if consent_status=consented',
  `consent_status` enum('usual care','pre-consent','consented','declined','ineligible','off-project') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'usual care' COMMENT 'consented indicates that the patient is a participant',
  `consent_date` date DEFAULT NULL,
  `consenter_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `consent_checked` tinyint(1) NOT NULL DEFAULT '0',
  `hipaa_consent_checked` tinyint(1) NOT NULL DEFAULT '0',
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery') COLLATE utf8_unicode_ci DEFAULT 'MedOnc',
  `treatment_start_date` date DEFAULT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('male','female') COLLATE utf8_unicode_ci NOT NULL COMMENT 'should be set by survey',
  `ethnicity` varchar(15) COLLATE utf8_unicode_ci NOT NULL COMMENT 'apparently not used',
  `eligible_flag` tinyint(1) NOT NULL COMMENT 'apparently not used',
  `study_group` enum('Control','Treatment') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field is redundant given user_acl_leafs table, but still used; only set if patient has consented',
  `secret_phrase` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t1` datetime NOT NULL,
  `t2` datetime DEFAULT NULL,
  `t3` datetime DEFAULT NULL,
  `t4` datetime DEFAULT NULL,
  `t1_location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t2_location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t3_location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t4_location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t1_staff_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `t2_staff_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `t3_staff_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `t4_staff_id` int(11) DEFAULT NULL COMMENT 'foreign key into users table',
  `check_again_date` date DEFAULT NULL COMMENT 'date when someone should view this patient record and take action',
  `no_more_check_agains` tinyint(1) NOT NULL DEFAULT '0',
  `t2a_subscale_id` int(11) DEFAULT NULL,
  `t2b_subscale_id` int(11) DEFAULT NULL,
  `off_study_status` enum('Completed all study requirements','Ineligible','Voluntary withdrawal','Lost to follow-up','Adverse effects','Other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `off_study_reason` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `off_study_timestamp` timestamp NULL DEFAULT NULL,
  `72_hr_follow_up` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `birthdate` (`birthdate`),
  KEY `off_study_status` (`off_study_status`),
  KEY `off_study_timestamp` (`off_study_timestamp`),
  KEY `consent_checked` (`consent_checked`),
  KEY `consent_status` (`consent_status`),
  KEY `hipaa_consent_checked` (`hipaa_consent_checked`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patients_p3p_teachings`
--

DROP TABLE IF EXISTS `patients_p3p_teachings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients_p3p_teachings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `p3p_teaching_id` int(11) NOT NULL,
  `visited` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3255 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) DEFAULT NULL,
  `OwnerName` varchar(255) DEFAULT NULL,
  `OwnerEmail` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projects_questionnaires`
--

DROP TABLE IF EXISTS `projects_questionnaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects_questionnaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `questionnaire_id` int(11) DEFAULT NULL,
  `Sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `project_questionnaire_id` (`id`,`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionnaires`
--

DROP TABLE IF EXISTS `questionnaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionnaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(128) DEFAULT NULL,
  `Title_es_MX` text,
  `BodyText` text,
  `FriendlyTitle` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `FriendlyTitle_es_MX` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionnaires_es_export`
--

DROP TABLE IF EXISTS `questionnaires_es_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionnaires_es_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(128) DEFAULT NULL,
  `Title_es_MX` text,
  `BodyText` text,
  `FriendlyTitle` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `FriendlyTitle_es_MX` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionnaires_for_bryan`
--

DROP TABLE IF EXISTS `questionnaires_for_bryan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionnaires_for_bryan` (
  `id` int(11) NOT NULL DEFAULT '0',
  `Title` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `Title_es_MX` text CHARACTER SET utf8,
  `BodyText` text CHARACTER SET utf8,
  `FriendlyTitle` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FriendlyTitle_es_MX` text CHARACTER SET utf8
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionnaires_no_es`
--

DROP TABLE IF EXISTS `questionnaires_no_es`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionnaires_no_es` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(128) DEFAULT NULL,
  `Title_es_MX` text,
  `BodyText` text,
  `FriendlyTitle` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `FriendlyTitle_es_MX` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) DEFAULT NULL,
  `ShortTitle` varchar(64) DEFAULT NULL,
  `ShortTitle_es_MX` text,
  `BodyText` text,
  `BodyText_es_MX` text,
  `BodyTextPosition` enum('above','left') NOT NULL DEFAULT 'left',
  `Orientation` enum('vertical','horizontal') NOT NULL DEFAULT 'vertical',
  `Groups` int(11) NOT NULL DEFAULT '1',
  `Style` enum('normal','hidden') NOT NULL DEFAULT 'normal',
  `Sequence` int(11) NOT NULL DEFAULT '0',
  `has_conditional_options` tinyint(4) NOT NULL DEFAULT '0',
  `ignore_skipped` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'If == 1, don''t include this in lists of skipped questions; for eg "if you drive a car, answer..."',
  PRIMARY KEY (`id`),
  KEY `DATA_OUTPUT` (`page_id`,`Sequence`)
) ENGINE=MyISAM AUTO_INCREMENT=2055 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questions_bak`
--

DROP TABLE IF EXISTS `questions_bak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions_bak` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) DEFAULT NULL,
  `ShortTitle` varchar(64) DEFAULT NULL,
  `ShortTitle_es_MX` text,
  `BodyText` text,
  `BodyText_es_MX` text,
  `BodyTextPosition` enum('above','left') NOT NULL DEFAULT 'left',
  `Orientation` enum('vertical','horizontal') NOT NULL DEFAULT 'vertical',
  `Groups` int(11) NOT NULL DEFAULT '1',
  `Style` enum('normal','hidden') NOT NULL DEFAULT 'normal',
  `Sequence` int(11) NOT NULL DEFAULT '0',
  `has_conditional_options` tinyint(4) NOT NULL DEFAULT '0',
  `ignore_skipped` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'If == 1, don''t include this in lists of skipped questions; for eg "if you drive a car, answer..."',
  PRIMARY KEY (`id`),
  KEY `DATA_OUTPUT` (`page_id`,`Sequence`)
) ENGINE=MyISAM AUTO_INCREMENT=2055 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questions_es_export`
--

DROP TABLE IF EXISTS `questions_es_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions_es_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) DEFAULT NULL,
  `ShortTitle` varchar(64) DEFAULT NULL,
  `ShortTitle_es_MX` text,
  `BodyText` text,
  `BodyText_es_MX` text,
  PRIMARY KEY (`id`),
  KEY `DATA_OUTPUT` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2056 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questions_no_es`
--

DROP TABLE IF EXISTS `questions_no_es`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions_no_es` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) DEFAULT NULL,
  `ShortTitle` varchar(64) DEFAULT NULL,
  `ShortTitle_es_MX` text,
  `BodyText` text,
  `BodyText_es_MX` text,
  `BodyTextPosition` enum('above','left') NOT NULL DEFAULT 'left',
  `Orientation` enum('vertical','horizontal') NOT NULL DEFAULT 'vertical',
  `Groups` int(11) NOT NULL DEFAULT '1',
  `Style` enum('normal','hidden') NOT NULL DEFAULT 'normal',
  `Sequence` int(11) NOT NULL DEFAULT '0',
  `has_conditional_options` tinyint(4) NOT NULL DEFAULT '0',
  `ignore_skipped` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'If == 1, don''t include this in lists of skipped questions; for eg "if you drive a car, answer..."',
  PRIMARY KEY (`id`),
  KEY `DATA_OUTPUT` (`page_id`,`Sequence`)
) ENGINE=MyISAM AUTO_INCREMENT=2055 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scales`
--

DROP TABLE IF EXISTS `scales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invert` tinyint(1) DEFAULT NULL,
  `combination` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `questionnaire_id` int(11) NOT NULL COMMENT 'Used to test inclusion in project; note: scales do not always map 1:1 questionnaires',
  `range` smallint(6) DEFAULT NULL COMMENT 'not being used, see subscales',
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `order` smallint(6) NOT NULL COMMENT 'Only used in results controller: > 0 for inclusion there and to designate order',
  `base` smallint(6) DEFAULT '1' COMMENT 'not being used, see subscales',
  `critical` smallint(5) unsigned DEFAULT NULL COMMENT 'used by sparklines in view my reports',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `scales_subscales_view`
--

DROP TABLE IF EXISTS `scales_subscales_view`;
/*!50001 DROP VIEW IF EXISTS `scales_subscales_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `scales_subscales_view` (
  `subscale` varchar(60),
  `scale` varchar(60)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `session_items`
--

DROP TABLE IF EXISTS `session_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `survey_session_id` int(11) NOT NULL,
  `value` float DEFAULT NULL,
  `session_subscale_id` int(11) NOT NULL COMMENT 'Not populated... exists to allow SessionSubscale => SessionItem relationship',
  `subscale_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_session_id` (`survey_session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2471 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session_scales`
--

DROP TABLE IF EXISTS `session_scales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_scales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_session_id` int(11) NOT NULL,
  `scale_id` int(11) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_session_id` (`survey_session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=381 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session_subscales`
--

DROP TABLE IF EXISTS `session_subscales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_subscales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscale_id` int(11) NOT NULL,
  `value` float DEFAULT NULL,
  `session_scale_id` int(11) NOT NULL COMMENT 'JM asks: junk?',
  `survey_session_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_session_id` (`survey_session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=761 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sites`
--

DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `timezone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `research_staff_email_alias` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'alias to email all associated research staff for the site',
  `research_staff_signature` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'signature to attach to email from the research staff',
  `new_aim_consent_mod_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subscales`
--

DROP TABLE IF EXISTS `subscales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scale_id` int(11) NOT NULL,
  `range` smallint(6) NOT NULL,
  `invert` tinyint(1) NOT NULL,
  `base` smallint(6) NOT NULL DEFAULT '0',
  `critical` smallint(6) NOT NULL DEFAULT '2' COMMENT 'for results/show, at least',
  `combination` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `internal_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order` smallint(6) NOT NULL COMMENT 'within the scale, as displayed in "View My/Others Reports"',
  `category_id` int(11) DEFAULT NULL COMMENT 'Which category represents this on the coding form',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `survey_sessions`
--

DROP TABLE IF EXISTS `survey_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL COMMENT 'The user who initially launched the survey session',
  `project_id` int(11) NOT NULL,
  `started` datetime NOT NULL,
  `patient_id` int(11) NOT NULL,
  `type` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `partial_finalization` tinyint(1) NOT NULL DEFAULT '0',
  `finished` tinyint(1) NOT NULL DEFAULT '0',
  `reportable_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM AUTO_INCREMENT=134 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `survey_sessions_appt_dt`
--

DROP TABLE IF EXISTS `survey_sessions_appt_dt`;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_appt_dt`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `survey_sessions_appt_dt` (
  `id` int(11),
  `modified` timestamp,
  `user_id` int(11),
  `project_id` int(11),
  `started` datetime,
  `patient_id` int(11),
  `type` varchar(30),
  `appointment_id` int(11),
  `partial_finalization` tinyint(1),
  `finished` tinyint(1),
  `appt_dt` datetime
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `survey_sessions_deleted`
--

DROP TABLE IF EXISTS `survey_sessions_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_sessions_deleted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL COMMENT 'Don''t use anymore!',
  `project_id` int(11) NOT NULL,
  `started` datetime NOT NULL,
  `patient_id` int(11) NOT NULL,
  `type` enum('T1','T2','T3','T4','nonT','errantT') COLLATE utf8_unicode_ci DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `partial_finalization` tinyint(1) NOT NULL DEFAULT '0',
  `finished` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `survey_sessions_non_test_view`
--

DROP TABLE IF EXISTS `survey_sessions_non_test_view`;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_non_test_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `survey_sessions_non_test_view` (
  `id` int(11),
  `modified` timestamp,
  `user_id` int(11),
  `project_id` int(11),
  `started` datetime,
  `patient_id` int(11),
  `finished` tinyint(1),
  `type` varchar(30),
  `partial_finalization` tinyint(1)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `survey_sessions_non_test_view_count`
--

DROP TABLE IF EXISTS `survey_sessions_non_test_view_count`;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_non_test_view_count`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `survey_sessions_non_test_view_count` (
  `COUNT(*)` bigint(21)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `targets`
--

DROP TABLE IF EXISTS `targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('T1','T2') COLLATE utf8_unicode_ci NOT NULL,
  `month` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `target` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`month`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teaching_tips`
--

DROP TABLE IF EXISTS `teaching_tips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teaching_tips` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `subscale_id` smallint(6) NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If this is set, it will be used instead of subscales.name when displaying',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teaching_tips_percentages`
--

DROP TABLE IF EXISTS `teaching_tips_percentages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teaching_tips_percentages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clinic_id` int(11) NOT NULL,
  `teaching_tip_id` int(11) NOT NULL,
  `after_treatment` tinyint(1) NOT NULL,
  `percentage` tinyint(4) NOT NULL,
  `clinical_service` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Transplant',
  PRIMARY KEY (`id`),
  KEY `site_id` (`clinic_id`,`teaching_tip_id`)
) ENGINE=MyISAM AUTO_INCREMENT=486 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `teaching_tips_subs_scales_view`
--

DROP TABLE IF EXISTS `teaching_tips_subs_scales_view`;
/*!50001 DROP VIEW IF EXISTS `teaching_tips_subs_scales_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `teaching_tips_subs_scales_view` (
  `subscale_id` int(11),
  `subscale_name` varchar(60),
  `scale_id` int(11),
  `scale_name` varchar(60),
  `teaching_tips_id` smallint(6),
  `text` longtext
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_acl_leafs`
--

DROP TABLE IF EXISTS `user_acl_leafs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_acl_leafs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `acl_alias` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=204 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `change_pw_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If 1, must change password after next login',
  `clinic_id` int(11) NOT NULL DEFAULT '1',
  `language` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clinic_id` (`clinic_id`),
  KEY `last_name` (`last_name`)
) ENGINE=MyISAM AUTO_INCREMENT=199 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `users_acl_easy_to_read`
--

DROP TABLE IF EXISTS `users_acl_easy_to_read`;
/*!50001 DROP VIEW IF EXISTS `users_acl_easy_to_read`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `users_acl_easy_to_read` (
  `id` int(11),
  `username` varchar(255),
  `first_name` varchar(64),
  `last_name` varchar(64),
  `email` varchar(40),
  `clinic_id` int(11),
  `acl_alias` varchar(255)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users_deleted`
--

DROP TABLE IF EXISTS `users_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_deleted` (
  `id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `change_pw_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If 1, must change password after next login',
  `clinic_id` int(11) NOT NULL DEFAULT '1',
  `language` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clinic_id` (`clinic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `aros_acos_interpreted_view`
--

/*!50001 DROP TABLE IF EXISTS `aros_acos_interpreted_view`*/;
/*!50001 DROP VIEW IF EXISTS `aros_acos_interpreted_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `aros_acos_interpreted_view` AS select `aros_acos`.`id` AS `id`,`aros_acos`.`aro_id` AS `aro_id`,`aros`.`alias` AS `aros_alias`,`aros_acos`.`aco_id` AS `aco_id`,`acos`.`alias` AS `acos_alias`,`aros_acos`.`_create` AS `_create`,`aros_acos`.`_read` AS `_read`,`aros_acos`.`_update` AS `_update`,`aros_acos`.`_delete` AS `_delete` from ((`aros_acos` join `aros` on((`aros_acos`.`aro_id` = `aros`.`id`))) join `acos` on((`aros_acos`.`aco_id` = `acos`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `itemsThroughScalesByProject`
--

/*!50001 DROP TABLE IF EXISTS `itemsThroughScalesByProject`*/;
/*!50001 DROP VIEW IF EXISTS `itemsThroughScalesByProject`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `itemsThroughScalesByProject` AS select `items`.`id` AS `item_id`,`items`.`question_id` AS `question_id`,`items`.`subscale_id` AS `subscale_id`,`subscales`.`name` AS `subscale_name`,`subscales`.`order` AS `subscale_order`,`scales`.`id` AS `scale_id`,`scales`.`name` AS `scale_name`,`scales`.`order` AS `scale_order`,`scales`.`questionnaire_id` AS `questionnaire_id`,`projects_questionnaires`.`project_id` AS `project_id` from (((`items` join `subscales` on((`items`.`subscale_id` = `subscales`.`id`))) join `scales` on((`subscales`.`scale_id` = `scales`.`id`))) join `projects_questionnaires` on((`projects_questionnaires`.`questionnaire_id` = `scales`.`questionnaire_id`))) order by `scales`.`order`,`subscales`.`order` limit 0,999 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `logs_intervention_non_test`
--

/*!50001 DROP TABLE IF EXISTS `logs_intervention_non_test`*/;
/*!50001 DROP VIEW IF EXISTS `logs_intervention_non_test`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `logs_intervention_non_test` AS select `logs`.`id` AS `id`,`logs`.`user_id` AS `user_id`,`logs`.`controller` AS `controller`,`logs`.`action` AS `action`,`logs`.`params` AS `params`,`logs`.`time` AS `time` from (`logs` join `patients` on((`logs`.`user_id` = `patients`.`id`))) where ((`patients`.`test_flag` = 0) and ((`logs`.`controller` = _utf8'results') or (`logs`.`controller` = _utf8'teaching') or (`logs`.`controller` = _utf8'journals') or (`logs`.`controller` = _utf8'associates')) and (not((`logs`.`action` like _utf8'%end%')))) order by `logs`.`user_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `logs_intervention_non_test_count`
--

/*!50001 DROP TABLE IF EXISTS `logs_intervention_non_test_count`*/;
/*!50001 DROP VIEW IF EXISTS `logs_intervention_non_test_count`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `logs_intervention_non_test_count` AS select count(0) AS `COUNT(*)` from `logs_intervention_non_test` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `participant_demographics`
--

/*!50001 DROP TABLE IF EXISTS `participant_demographics`*/;
/*!50001 DROP VIEW IF EXISTS `participant_demographics`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `participant_demographics` AS select `patients`.`id` AS `patient id`,`patients`.`gender` AS `gender`,`clinics`.`name` AS `clinic`,`survey_sessions`.`id` AS `survey_session` from (((`patients` join `users` on((`patients`.`id` = `users`.`id`))) join `survey_sessions` on((`patients`.`id` = `survey_sessions`.`patient_id`))) join `clinics` on((`clinics`.`id` = `users`.`clinic_id`))) where ((`patients`.`consent_status` = _utf8'consented') and (`patients`.`test_flag` <> 1) and (`survey_sessions`.`type` = _utf8'T1')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `patient_T1s`
--

/*!50001 DROP TABLE IF EXISTS `patient_T1s`*/;
/*!50001 DROP VIEW IF EXISTS `patient_T1s`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `patient_T1s` AS select `patients`.`id` AS `patient`,`clinics`.`name` AS `clinic`,`survey_sessions`.`id` AS `survey_session` from (((`patients` join `users` on((`patients`.`id` = `users`.`id`))) join `survey_sessions` on((`patients`.`id` = `survey_sessions`.`patient_id`))) join `clinics` on((`clinics`.`id` = `users`.`clinic_id`))) where ((`patients`.`test_flag` <> 1) and (`survey_sessions`.`type` = _utf8'T1')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `patients_clinics_view`
--

/*!50001 DROP TABLE IF EXISTS `patients_clinics_view`*/;
/*!50001 DROP VIEW IF EXISTS `patients_clinics_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `patients_clinics_view` AS select `clinics`.`name` AS `name`,`patients`.`id` AS `id`,`patients`.`MRN` AS `MRN`,`patients`.`test_flag` AS `test_flag`,`patients`.`phone1` AS `phone1`,`patients`.`phone2` AS `phone2`,`patients`.`mailing_address` AS `mailing_address`,`patients`.`study_participation_flag` AS `study_participation_flag`,`patients`.`user_type` AS `user_type`,`patients`.`consent_status` AS `consent_status`,`patients`.`consent_date` AS `consent_date`,`patients`.`consenter_id` AS `consenter_id`,`patients`.`consent_checked` AS `consent_checked`,`patients`.`hipaa_consent_checked` AS `hipaa_consent_checked`,`patients`.`clinical_service` AS `clinical_service`,`patients`.`treatment_start_date` AS `treatment_start_date`,`patients`.`birthdate` AS `birthdate`,`patients`.`gender` AS `gender`,`patients`.`ethnicity` AS `ethnicity`,`patients`.`eligible_flag` AS `eligible_flag`,`patients`.`study_group` AS `study_group`,`patients`.`secret_phrase` AS `secret_phrase`,`patients`.`t1` AS `t1`,`patients`.`t2` AS `t2`,`patients`.`t3` AS `t3`,`patients`.`t4` AS `t4`,`patients`.`t1_location` AS `t1_location`,`patients`.`t2_location` AS `t2_location`,`patients`.`t3_location` AS `t3_location`,`patients`.`t4_location` AS `t4_location`,`patients`.`t1_staff_id` AS `t1_staff_id`,`patients`.`t2_staff_id` AS `t2_staff_id`,`patients`.`t3_staff_id` AS `t3_staff_id`,`patients`.`t4_staff_id` AS `t4_staff_id`,`patients`.`check_again_date` AS `check_again_date`,`patients`.`no_more_check_agains` AS `no_more_check_agains`,`patients`.`t2a_subscale_id` AS `t2a_subscale_id`,`patients`.`t2b_subscale_id` AS `t2b_subscale_id`,`patients`.`off_study_status` AS `off_study_status`,`patients`.`off_study_reason` AS `off_study_reason`,`patients`.`off_study_timestamp` AS `off_study_timestamp` from ((`patients` join `users` on((`patients`.`id` = `users`.`id`))) join `clinics` on((`users`.`clinic_id` = `clinics`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `patients_clinics_view_count`
--

/*!50001 DROP TABLE IF EXISTS `patients_clinics_view_count`*/;
/*!50001 DROP VIEW IF EXISTS `patients_clinics_view_count`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `patients_clinics_view_count` AS select count(0) AS `COUNT(*)` from `patients_clinics_view` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `scales_subscales_view`
--

/*!50001 DROP TABLE IF EXISTS `scales_subscales_view`*/;
/*!50001 DROP VIEW IF EXISTS `scales_subscales_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `scales_subscales_view` AS select `subscales`.`name` AS `subscale`,`scales`.`name` AS `scale` from (`subscales` join `scales` on((`subscales`.`scale_id` = `scales`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `survey_sessions_appt_dt`
--

/*!50001 DROP TABLE IF EXISTS `survey_sessions_appt_dt`*/;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_appt_dt`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `survey_sessions_appt_dt` AS select `survey_sessions`.`id` AS `id`,`survey_sessions`.`modified` AS `modified`,`survey_sessions`.`user_id` AS `user_id`,`survey_sessions`.`project_id` AS `project_id`,`survey_sessions`.`started` AS `started`,`survey_sessions`.`patient_id` AS `patient_id`,`survey_sessions`.`type` AS `type`,`survey_sessions`.`appointment_id` AS `appointment_id`,`survey_sessions`.`partial_finalization` AS `partial_finalization`,`survey_sessions`.`finished` AS `finished`,`appointments`.`datetime` AS `appt_dt` from (`survey_sessions` join `appointments` on((`survey_sessions`.`appointment_id` = `appointments`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `survey_sessions_non_test_view`
--

/*!50001 DROP TABLE IF EXISTS `survey_sessions_non_test_view`*/;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_non_test_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `survey_sessions_non_test_view` AS select `survey_sessions`.`id` AS `id`,`survey_sessions`.`modified` AS `modified`,`survey_sessions`.`user_id` AS `user_id`,`survey_sessions`.`project_id` AS `project_id`,`survey_sessions`.`started` AS `started`,`survey_sessions`.`patient_id` AS `patient_id`,`survey_sessions`.`finished` AS `finished`,`survey_sessions`.`type` AS `type`,`survey_sessions`.`partial_finalization` AS `partial_finalization` from (`survey_sessions` join `patients` on((`survey_sessions`.`patient_id` = `patients`.`id`))) where (`patients`.`test_flag` = 0) order by `survey_sessions`.`patient_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `survey_sessions_non_test_view_count`
--

/*!50001 DROP TABLE IF EXISTS `survey_sessions_non_test_view_count`*/;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_non_test_view_count`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `survey_sessions_non_test_view_count` AS select count(0) AS `COUNT(*)` from `survey_sessions_non_test_view` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `teaching_tips_subs_scales_view`
--

/*!50001 DROP TABLE IF EXISTS `teaching_tips_subs_scales_view`*/;
/*!50001 DROP VIEW IF EXISTS `teaching_tips_subs_scales_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `teaching_tips_subs_scales_view` AS select `subscales`.`id` AS `subscale_id`,`subscales`.`name` AS `subscale_name`,`subscales`.`scale_id` AS `scale_id`,`scales`.`name` AS `scale_name`,`teaching_tips`.`id` AS `teaching_tips_id`,`teaching_tips`.`text` AS `text` from ((`subscales` left join `teaching_tips` on((`subscales`.`id` = `teaching_tips`.`subscale_id`))) join `scales` on((`subscales`.`scale_id` = `scales`.`id`))) order by `subscales`.`scale_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `users_acl_easy_to_read`
--

/*!50001 DROP TABLE IF EXISTS `users_acl_easy_to_read`*/;
/*!50001 DROP VIEW IF EXISTS `users_acl_easy_to_read`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `users_acl_easy_to_read` AS select `users`.`id` AS `id`,`users`.`username` AS `username`,`users`.`first_name` AS `first_name`,`users`.`last_name` AS `last_name`,`users`.`email` AS `email`,`users`.`clinic_id` AS `clinic_id`,`user_acl_leafs`.`acl_alias` AS `acl_alias` from (`users` join `user_acl_leafs` on((`users`.`id` = `user_acl_leafs`.`user_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-02-27 17:10:41
