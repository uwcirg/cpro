-- MySQL dump 10.11
--
-- Host: localhost    Database: sme_dev
-- ------------------------------------------------------
-- Server version	5.0.51a-24+lenny5

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
-- Table structure for table `acos`
--

DROP TABLE IF EXISTS `acos`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `acos` (
  `id` int(10) NOT NULL auto_increment,
  `parent_id` int(10) default NULL,
  `model` varchar(255) default NULL,
  `foreign_key` int(10) default NULL,
  `alias` varchar(255) default NULL,
  `lft` int(10) default NULL,
  `rght` int(10) default NULL,
  PRIMARY KEY  (`id`),
  KEY `acos_idx1` (`lft`,`rght`),
  KEY `acos_idx2` (`alias`)
) ENGINE=MyISAM AUTO_INCREMENT=252 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `activity_diary_entries`
--

DROP TABLE IF EXISTS `activity_diary_entries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `activity_diary_entries` (
  `id` int(11) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL,
  `dt_created` datetime NOT NULL,
  `dt_last_edit` datetime NOT NULL,
  `date` date NOT NULL,
  `fatigue` int(1) default NULL,
  `type` enum('Walking','Biking','Running','Other','None') collate utf8_unicode_ci default NULL,
  `typeOther` text collate utf8_unicode_ci,
  `minutes` int(4) unsigned default NULL,
  `steps` int(6) unsigned default NULL,
  `note` longtext collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `patient_date_index` (`patient_id`,`date`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1148 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `alerts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `page_id` int(10) unsigned NOT NULL,
  `target_type` enum('item','subscale','scale') NOT NULL,
  `target_id` int(10) unsigned NOT NULL,
  `comparison` enum('<','>','=') NOT NULL,
  `value` float NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `answers`
--

DROP TABLE IF EXISTS `answers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `answers` (
  `id` int(11) NOT NULL auto_increment,
  `survey_session_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_id` int(11) default NULL,
  `state` varchar(10) collate utf8_unicode_ci default NULL,
  `body_text` text collate utf8_unicode_ci,
  `value` text collate utf8_unicode_ci,
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `question_id` (`survey_session_id`,`question_id`,`option_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10097 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL,
  `datetime` datetime default NULL,
  `location` varchar(100) collate utf8_unicode_ci default NULL,
  `staff_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=218 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `aros`
--

DROP TABLE IF EXISTS `aros`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `aros` (
  `id` int(10) NOT NULL auto_increment,
  `parent_id` int(10) default NULL,
  `model` varchar(255) default NULL,
  `foreign_key` int(10) default NULL,
  `alias` varchar(255) default NULL,
  `lft` int(10) default NULL,
  `rght` int(10) default NULL,
  PRIMARY KEY  (`id`),
  KEY `aros_idx1` (`lft`,`rght`),
  KEY `aros_idx2` (`alias`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `aros_acos`
--

DROP TABLE IF EXISTS `aros_acos`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `aros_acos` (
  `id` int(10) NOT NULL auto_increment,
  `aro_id` int(10) NOT NULL,
  `aco_id` int(10) NOT NULL,
  `_create` varchar(2) NOT NULL default '0',
  `_read` varchar(2) NOT NULL default '0',
  `_update` varchar(2) NOT NULL default '0',
  `_delete` varchar(2) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ARO_ACO_KEY` (`aro_id`,`aco_id`)
) ENGINE=MyISAM AUTO_INCREMENT=297 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `aros_acos_interpreted_view`
--

DROP TABLE IF EXISTS `aros_acos_interpreted_view`;
/*!50001 DROP VIEW IF EXISTS `aros_acos_interpreted_view`*/;
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
) */;

--
-- Table structure for table `associates`
--

DROP TABLE IF EXISTS `associates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `associates` (
  `id` int(11) NOT NULL auto_increment,
  `email` varchar(40) collate utf8_unicode_ci NOT NULL COMMENT 'REMOVE THIS JUNK',
  `verified` tinyint(1) NOT NULL default '0' COMMENT 'OBSOLETE, see patients_associates.has_entered_secret_phrase',
  `webkey` int(10) unsigned NOT NULL COMMENT 'OBSOLETE; SEE patients_associates',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `cake_sessions`
--

DROP TABLE IF EXISTS `cake_sessions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `cake_sessions` (
  `id` varchar(255) collate utf8_unicode_ci NOT NULL,
  `data` text collate utf8_unicode_ci,
  `expires` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `clinics`
--

DROP TABLE IF EXISTS `clinics`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `clinics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `site_id` int(10) unsigned NOT NULL,
  `name` varchar(40) collate utf8_unicode_ci NOT NULL,
  `friendly_name` varchar(128) collate utf8_unicode_ci default NULL,
  `support_email` varchar(100) collate utf8_unicode_ci default NULL,
  `support_phone` varchar(20) collate utf8_unicode_ci default NULL,
  `one_usual_care_session` tinyint(1) NOT NULL default '0' COMMENT 'If true, after the 1st survey session the patient is either moved to "usual care" or "off-project"',
  PRIMARY KEY  (`id`),
  KEY `site_id` (`site_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `conditions`
--

DROP TABLE IF EXISTS `conditions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `conditions` (
  `id` int(11) NOT NULL auto_increment,
  `target_type` enum('Page','Questionnaire','Question','Option') default NULL,
  `target_id` int(11) default NULL,
  `condition` text,
  PRIMARY KEY  (`id`),
  KEY `condition_id` (`id`),
  KEY `target_id` (`target_id`),
  KEY `target_type` (`target_type`)
) ENGINE=MyISAM AUTO_INCREMENT=168 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `consents`
--

DROP TABLE IF EXISTS `consents`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `consents` (
  `id` int(11) NOT NULL COMMENT ' ',
  `patient_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='used for synchronization when assigning patients to control/';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `items` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(40) collate utf8_unicode_ci NOT NULL,
  `question_id` int(11) NOT NULL,
  `subscale_id` int(11) NOT NULL,
  `base` smallint(6) NOT NULL default '0' COMMENT 'NOT USED ANYMORE!',
  `range` float NOT NULL default '4' COMMENT 'num options - 1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=89 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='this is a join on questions & subscales';
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `itemsThroughScalesByProject`
--

DROP TABLE IF EXISTS `itemsThroughScalesByProject`;
/*!50001 DROP VIEW IF EXISTS `itemsThroughScalesByProject`*/;
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
) */;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL auto_increment,
  `text` longtext collate utf8_unicode_ci NOT NULL,
  `patient_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `display` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `account_id` int(11) unsigned NOT NULL,
  `controller` varchar(20) collate utf8_unicode_ci NOT NULL,
  `action` varchar(64) collate utf8_unicode_ci NOT NULL,
  `params` varchar(512) collate utf8_unicode_ci default NULL,
  `time` datetime NOT NULL,
  `ip_address` varchar(20) collate utf8_unicode_ci NOT NULL,
  `user_agent` varchar(200) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id_index` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=95696 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `logs_intervention_non_test`
--

DROP TABLE IF EXISTS `logs_intervention_non_test`;
/*!50001 DROP VIEW IF EXISTS `logs_intervention_non_test`*/;
/*!50001 CREATE TABLE `logs_intervention_non_test` (
  `id` int(10) unsigned,
  `user_id` int(11) unsigned,
  `controller` varchar(20),
  `action` varchar(64),
  `params` varchar(512),
  `time` datetime
) */;

--
-- Temporary table structure for view `logs_intervention_non_test_count`
--

DROP TABLE IF EXISTS `logs_intervention_non_test_count`;
/*!50001 DROP VIEW IF EXISTS `logs_intervention_non_test_count`*/;
/*!50001 CREATE TABLE `logs_intervention_non_test_count` (
  `COUNT(*)` bigint(21)
) */;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL COMMENT 'foreign key into patients table',
  `text` varchar(10000) collate utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL COMMENT 'foreign key into users table',
  `created` datetime NOT NULL,
  `flagged` tinyint(1) NOT NULL default '0' COMMENT '1 if flagged, 0 if not',
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `author_id` (`author_id`)
) ENGINE=MyISAM AUTO_INCREMENT=107 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `options` (
  `id` int(11) NOT NULL auto_increment,
  `question_id` int(11) default NULL,
  `OptionType` enum('radio','checkbox','dropdown','text','textbox','combo-radio','combo-check','button-yes','button-no','imagemap','none') NOT NULL default 'radio',
  `Height` int(11) NOT NULL default '0',
  `Width` int(11) NOT NULL default '0',
  `MaxCharacters` int(11) NOT NULL default '0',
  `AnalysisValue` varchar(32) default NULL,
  `ValueRestriction` varchar(128) default NULL,
  `BodyText` text,
  `BodyTextType` enum('visible','invisible') NOT NULL default 'visible',
  `Sequence` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `DATA_OUTPUT` (`question_id`,`Sequence`)
) ENGINE=MyISAM AUTO_INCREMENT=4383 DEFAULT CHARSET=utf8 PACK_KEYS=0;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL auto_increment,
  `questionnaire_id` int(11) default NULL,
  `Title` varchar(64) default NULL,
  `Header` text,
  `BodyText` text,
  `NavigationType` enum('prev-next','next','none','prev') NOT NULL default 'prev-next',
  `TargetType` varchar(32) default NULL,
  `ProgressType` enum('text','graphical','none') NOT NULL default 'graphical',
  `LayoutType` enum('basic','embedded') NOT NULL default 'basic',
  `Sequence` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `page_id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=359 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `participant_demographics`
--

DROP TABLE IF EXISTS `participant_demographics`;
/*!50001 DROP VIEW IF EXISTS `participant_demographics`*/;
/*!50001 CREATE TABLE `participant_demographics` (
  `patient id` int(11),
  `gender` enum('male','female'),
  `clinic` varchar(40),
  `survey_session` int(11)
) */;

--
-- Temporary table structure for view `patient_T1s`
--

DROP TABLE IF EXISTS `patient_T1s`;
/*!50001 DROP VIEW IF EXISTS `patient_T1s`*/;
/*!50001 CREATE TABLE `patient_T1s` (
  `patient` int(11),
  `clinic` varchar(40),
  `survey_session` int(11)
) */;

--
-- Table structure for table `patient_view_notes`
--

DROP TABLE IF EXISTS `patient_view_notes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `patient_view_notes` (
  `id` int(11) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL COMMENT 'foreign key into patients table',
  `text` varchar(10000) collate utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL COMMENT 'foreign key into users table',
  `lastmod` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `author_id` (`author_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `patients` (
  `id` int(11) NOT NULL auto_increment,
  `MRN` varchar(10) collate utf8_unicode_ci NOT NULL,
  `test_flag` tinyint(1) NOT NULL,
  `phone1` varchar(20) collate utf8_unicode_ci default NULL,
  `phone2` varchar(20) collate utf8_unicode_ci default NULL,
  `mailing_address` varchar(255) collate utf8_unicode_ci default NULL,
  `study_participation_flag` tinyint(1) NOT NULL default '0' COMMENT '1 if they requested to learn more about the study',
  `user_type` enum('Home/Independent','Clinic/Assisted') collate utf8_unicode_ci default NULL COMMENT 'used in randomization algorithm; should not be null if consent_status=consented',
  `consent_status` enum('usual care','pre-consent','consented','declined','ineligible','off-project') collate utf8_unicode_ci NOT NULL default 'usual care' COMMENT 'consented indicates that the patient is a participant',
  `consent_date` date default NULL,
  `consenter_id` int(11) default NULL COMMENT 'foreign key into users table',
  `consent_checked` tinyint(1) NOT NULL default '0',
  `hipaa_consent_checked` tinyint(1) NOT NULL default '0',
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery') collate utf8_unicode_ci default 'MedOnc',
  `treatment_start_date` date default NULL,
  `birthdate` date NOT NULL,
  `gender` enum('male','female') collate utf8_unicode_ci default NULL COMMENT 'Settable by survey or patient editor',
  `ethnicity` varchar(15) collate utf8_unicode_ci NOT NULL COMMENT 'apparently not used',
  `eligible_flag` tinyint(1) NOT NULL COMMENT 'apparently not used',
  `study_group` enum('Control','Treatment') collate utf8_unicode_ci default NULL COMMENT 'field is redundant given user_acl_leafs table, but still used; only set if patient has consented',
  `secret_phrase` varchar(50) collate utf8_unicode_ci default NULL,
  `t1` datetime NOT NULL,
  `t2` datetime default NULL,
  `t3` datetime default NULL,
  `t4` datetime default NULL,
  `t1_location` varchar(100) collate utf8_unicode_ci default NULL,
  `t2_location` varchar(100) collate utf8_unicode_ci default NULL,
  `t3_location` varchar(100) collate utf8_unicode_ci default NULL,
  `t4_location` varchar(100) collate utf8_unicode_ci default NULL,
  `t1_staff_id` int(11) default NULL COMMENT 'foreign key into users table',
  `t2_staff_id` int(11) default NULL COMMENT 'foreign key into users table',
  `t3_staff_id` int(11) default NULL COMMENT 'foreign key into users table',
  `t4_staff_id` int(11) default NULL COMMENT 'foreign key into users table',
  `check_again_date` date default NULL COMMENT 'date when someone should view this patient record and take action',
  `no_more_check_agains` tinyint(1) NOT NULL default '0',
  `t2a_subscale_id` int(11) default NULL,
  `t2b_subscale_id` int(11) default NULL,
  `off_study_status` enum('Completed all study requirements','Ineligible','Voluntary withdrawal','Lost to follow-up','Adverse effects','Other') collate utf8_unicode_ci default NULL,
  `off_study_reason` varchar(500) collate utf8_unicode_ci default NULL,
  `off_study_timestamp` timestamp NULL default NULL,
  `72_hr_follow_up` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `birthdate` (`birthdate`),
  KEY `off_study_status` (`off_study_status`),
  KEY `off_study_timestamp` (`off_study_timestamp`),
  KEY `consent_checked` (`consent_checked`),
  KEY `consent_status` (`consent_status`),
  KEY `hipaa_consent_checked` (`hipaa_consent_checked`)
) ENGINE=MyISAM AUTO_INCREMENT=129 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `patients_associates`
--

DROP TABLE IF EXISTS `patients_associates`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `patients_associates` (
  `id` int(11) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `webkey` int(10) NOT NULL,
  `has_entered_secret_phrase` tinyint(1) NOT NULL,
  `share_journal` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `patients_associates_deleted`
--

DROP TABLE IF EXISTS `patients_associates_deleted`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `patients_associates_deleted` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `webkey` int(10) NOT NULL,
  `has_entered_secret_phrase` tinyint(1) NOT NULL,
  `share_journal` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `patients_associates_subscales`
--

DROP TABLE IF EXISTS `patients_associates_subscales`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `patients_associates_subscales` (
  `id` int(11) NOT NULL auto_increment,
  `patient_associate_id` int(11) NOT NULL,
  `subscale_id` int(11) NOT NULL,
  `shared` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `patient_associate_id` (`patient_associate_id`,`subscale_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `patients_clinics_view`
--

DROP TABLE IF EXISTS `patients_clinics_view`;
/*!50001 DROP VIEW IF EXISTS `patients_clinics_view`*/;
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
) */;

--
-- Temporary table structure for view `patients_clinics_view_count`
--

DROP TABLE IF EXISTS `patients_clinics_view_count`;
/*!50001 DROP VIEW IF EXISTS `patients_clinics_view_count`*/;
/*!50001 CREATE TABLE `patients_clinics_view_count` (
  `COUNT(*)` bigint(21)
) */;

--
-- Table structure for table `patients_deleted`
--

DROP TABLE IF EXISTS `patients_deleted`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `patients_deleted` (
  `id` int(11) NOT NULL auto_increment,
  `MRN` varchar(10) collate utf8_unicode_ci NOT NULL,
  `test_flag` tinyint(1) NOT NULL,
  `phone1` varchar(20) collate utf8_unicode_ci default NULL,
  `phone2` varchar(20) collate utf8_unicode_ci default NULL,
  `mailing_address` varchar(255) collate utf8_unicode_ci default NULL,
  `study_participation_flag` tinyint(1) NOT NULL default '0' COMMENT '1 if they requested to learn more about the study',
  `user_type` enum('Home/Independent','Clinic/Assisted') collate utf8_unicode_ci default NULL COMMENT 'used in randomization algorithm; should not be null if consent_status=consented',
  `consent_status` enum('usual care','pre-consent','consented','declined','ineligible','off-project') collate utf8_unicode_ci NOT NULL default 'usual care' COMMENT 'consented indicates that the patient is a participant',
  `consent_date` date default NULL,
  `consenter_id` int(11) default NULL COMMENT 'foreign key into users table',
  `consent_checked` tinyint(1) NOT NULL default '0',
  `hipaa_consent_checked` tinyint(1) NOT NULL default '0',
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery') collate utf8_unicode_ci default 'MedOnc',
  `treatment_start_date` date default NULL,
  `birthdate` date NOT NULL,
  `gender` enum('male','female') collate utf8_unicode_ci NOT NULL COMMENT 'should be set by survey',
  `ethnicity` varchar(15) collate utf8_unicode_ci NOT NULL COMMENT 'apparently not used',
  `eligible_flag` tinyint(1) NOT NULL COMMENT 'apparently not used',
  `study_group` enum('Control','Treatment') collate utf8_unicode_ci default NULL COMMENT 'field is redundant given user_acl_leafs table, but still used; only set if patient has consented',
  `secret_phrase` varchar(50) collate utf8_unicode_ci default NULL,
  `t1` datetime NOT NULL,
  `t2` datetime default NULL,
  `t3` datetime default NULL,
  `t4` datetime default NULL,
  `t1_location` varchar(100) collate utf8_unicode_ci default NULL,
  `t2_location` varchar(100) collate utf8_unicode_ci default NULL,
  `t3_location` varchar(100) collate utf8_unicode_ci default NULL,
  `t4_location` varchar(100) collate utf8_unicode_ci default NULL,
  `t1_staff_id` int(11) default NULL COMMENT 'foreign key into users table',
  `t2_staff_id` int(11) default NULL COMMENT 'foreign key into users table',
  `t3_staff_id` int(11) default NULL COMMENT 'foreign key into users table',
  `t4_staff_id` int(11) default NULL COMMENT 'foreign key into users table',
  `check_again_date` date default NULL COMMENT 'date when someone should view this patient record and take action',
  `no_more_check_agains` tinyint(1) NOT NULL default '0',
  `t2a_subscale_id` int(11) default NULL,
  `t2b_subscale_id` int(11) default NULL,
  `off_study_status` enum('Completed all study requirements','Ineligible','Voluntary withdrawal','Lost to follow-up','Adverse effects','Other') collate utf8_unicode_ci default NULL,
  `off_study_reason` varchar(500) collate utf8_unicode_ci default NULL,
  `off_study_timestamp` timestamp NULL default NULL,
  `72_hr_follow_up` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `birthdate` (`birthdate`),
  KEY `off_study_status` (`off_study_status`),
  KEY `off_study_timestamp` (`off_study_timestamp`),
  KEY `consent_checked` (`consent_checked`),
  KEY `consent_status` (`consent_status`),
  KEY `hipaa_consent_checked` (`hipaa_consent_checked`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL auto_increment,
  `Title` varchar(255) default NULL,
  `OwnerName` varchar(255) default NULL,
  `OwnerEmail` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `project_id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `projects_questionnaires`
--

DROP TABLE IF EXISTS `projects_questionnaires`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `projects_questionnaires` (
  `id` int(11) NOT NULL auto_increment,
  `project_id` int(11) default NULL,
  `questionnaire_id` int(11) default NULL,
  `Sequence` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `project_questionnaire_id` (`id`,`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `questionnaires`
--

DROP TABLE IF EXISTS `questionnaires`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `questionnaires` (
  `id` int(11) NOT NULL auto_increment,
  `Title` varchar(128) default NULL,
  `BodyText` text,
  `FriendlyTitle` varchar(128) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `questions` (
  `id` int(11) NOT NULL auto_increment,
  `page_id` int(11) default NULL,
  `ShortTitle` varchar(64) default NULL,
  `BodyText` text,
  `BodyTextPosition` enum('above','left') NOT NULL default 'left',
  `Orientation` enum('vertical','horizontal') NOT NULL default 'vertical',
  `Groups` int(11) NOT NULL default '1',
  `Style` enum('normal','hidden') NOT NULL default 'normal',
  `Sequence` int(11) NOT NULL default '0',
  `has_conditional_options` tinyint(4) NOT NULL default '0',
  `ignore_skipped` tinyint(4) NOT NULL default '0' COMMENT 'If == 1, don''t include this in lists of skipped questions; for eg "if you drive a car, answer..."',
  PRIMARY KEY  (`id`),
  KEY `DATA_OUTPUT` (`page_id`,`Sequence`)
) ENGINE=MyISAM AUTO_INCREMENT=1035 DEFAULT CHARSET=utf8 PACK_KEYS=0;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `scales`
--

DROP TABLE IF EXISTS `scales`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `scales` (
  `id` int(11) NOT NULL auto_increment,
  `invert` tinyint(1) default NULL,
  `combination` varchar(20) collate utf8_unicode_ci default NULL,
  `questionnaire_id` int(11) NOT NULL COMMENT 'Used to test inclusion in project; note: scales do not always map 1:1 questionnaires',
  `range` smallint(6) default NULL COMMENT 'not being used, see subscales',
  `name` varchar(60) collate utf8_unicode_ci NOT NULL,
  `order` smallint(6) NOT NULL COMMENT 'Only used in results controller: > 0 for inclusion there and to designate order',
  `base` smallint(6) default '1' COMMENT 'not being used, see subscales',
  `critical` smallint(5) unsigned default NULL COMMENT 'used by sparklines in view my reports',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `scales_subscales_view`
--

DROP TABLE IF EXISTS `scales_subscales_view`;
/*!50001 DROP VIEW IF EXISTS `scales_subscales_view`*/;
/*!50001 CREATE TABLE `scales_subscales_view` (
  `subscale` varchar(60),
  `scale` varchar(60)
) */;

--
-- Table structure for table `session_items`
--

DROP TABLE IF EXISTS `session_items`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `session_items` (
  `id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL,
  `survey_session_id` int(11) NOT NULL,
  `value` float default NULL,
  `session_subscale_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `survey_session_id` (`survey_session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30078 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `session_scales`
--

DROP TABLE IF EXISTS `session_scales`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `session_scales` (
  `id` int(11) NOT NULL auto_increment,
  `survey_session_id` int(11) NOT NULL,
  `scale_id` int(11) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `survey_session_id` (`survey_session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3401 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `session_subscales`
--

DROP TABLE IF EXISTS `session_subscales`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `session_subscales` (
  `id` int(11) NOT NULL auto_increment,
  `subscale_id` int(11) NOT NULL,
  `value` float default NULL,
  `session_scale_id` int(11) NOT NULL COMMENT 'JM asks: junk?',
  `survey_session_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `survey_session_id` (`survey_session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10379 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `sites`
--

DROP TABLE IF EXISTS `sites`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sites` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(40) collate utf8_unicode_ci NOT NULL,
  `timezone` varchar(20) collate utf8_unicode_ci NOT NULL,
  `research_staff_email_alias` varchar(40) collate utf8_unicode_ci default NULL COMMENT 'alias to email all associated research staff for the site',
  `research_staff_signature` varchar(200) collate utf8_unicode_ci default NULL COMMENT 'signature to attach to email from the research staff',
  `new_aim_consent_mod_date` date default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `subscales`
--

DROP TABLE IF EXISTS `subscales`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `subscales` (
  `id` int(11) NOT NULL auto_increment,
  `scale_id` int(11) NOT NULL,
  `range` smallint(6) NOT NULL,
  `invert` tinyint(1) NOT NULL,
  `base` smallint(6) NOT NULL default '0',
  `critical` smallint(6) NOT NULL default '2' COMMENT 'for results/show, at least',
  `combination` varchar(40) collate utf8_unicode_ci NOT NULL,
  `name` varchar(60) collate utf8_unicode_ci NOT NULL,
  `internal_note` varchar(255) collate utf8_unicode_ci default NULL,
  `order` smallint(6) NOT NULL COMMENT 'within the scale, as displayed in "View My/Others Reports"',
  `category_id` int(11) default NULL COMMENT 'Which category represents this on the coding form',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `survey_sessions`
--

DROP TABLE IF EXISTS `survey_sessions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `survey_sessions` (
  `id` int(11) NOT NULL auto_increment,
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL COMMENT 'The user who initially launched the survey session',
  `project_id` int(11) NOT NULL,
  `started` datetime NOT NULL,
  `patient_id` int(11) NOT NULL,
  `type` enum('T1','T2','T3','T4','nonT','errantT') collate utf8_unicode_ci default NULL,
  `appointment_id` int(11) default NULL,
  `partial_finalization` tinyint(1) NOT NULL default '0',
  `finished` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `survey_sessions_appt_dt`
--

DROP TABLE IF EXISTS `survey_sessions_appt_dt`;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_appt_dt`*/;
/*!50001 CREATE TABLE `survey_sessions_appt_dt` (
  `id` int(11),
  `modified` timestamp,
  `user_id` int(11),
  `project_id` int(11),
  `started` datetime,
  `patient_id` int(11),
  `type` enum('T1','T2','T3','T4','nonT','errantT'),
  `appointment_id` int(11),
  `partial_finalization` tinyint(1),
  `finished` tinyint(1),
  `appt_dt` datetime
) */;

--
-- Table structure for table `survey_sessions_deleted`
--

DROP TABLE IF EXISTS `survey_sessions_deleted`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `survey_sessions_deleted` (
  `id` int(11) NOT NULL auto_increment,
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL COMMENT 'Don''t use anymore!',
  `project_id` int(11) NOT NULL,
  `started` datetime NOT NULL,
  `patient_id` int(11) NOT NULL,
  `type` enum('T1','T2','T3','T4','nonT','errantT') collate utf8_unicode_ci default NULL,
  `appointment_id` int(11) default NULL,
  `partial_finalization` tinyint(1) NOT NULL default '0',
  `finished` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `survey_sessions_non_test_view`
--

DROP TABLE IF EXISTS `survey_sessions_non_test_view`;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_non_test_view`*/;
/*!50001 CREATE TABLE `survey_sessions_non_test_view` (
  `id` int(11),
  `modified` timestamp,
  `user_id` int(11),
  `project_id` int(11),
  `started` datetime,
  `patient_id` int(11),
  `finished` tinyint(1),
  `type` enum('T1','T2','T3','T4','nonT','errantT'),
  `partial_finalization` tinyint(1)
) */;

--
-- Temporary table structure for view `survey_sessions_non_test_view_count`
--

DROP TABLE IF EXISTS `survey_sessions_non_test_view_count`;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_non_test_view_count`*/;
/*!50001 CREATE TABLE `survey_sessions_non_test_view_count` (
  `COUNT(*)` bigint(21)
) */;

--
-- Table structure for table `targets`
--

DROP TABLE IF EXISTS `targets`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `targets` (
  `id` int(11) NOT NULL auto_increment,
  `type` enum('T1','T2') collate utf8_unicode_ci NOT NULL,
  `month` varchar(8) collate utf8_unicode_ci NOT NULL,
  `target` int(5) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`,`month`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `teaching_tips`
--

DROP TABLE IF EXISTS `teaching_tips`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `teaching_tips` (
  `id` smallint(6) NOT NULL auto_increment,
  `subscale_id` smallint(6) NOT NULL,
  `text` longtext collate utf8_unicode_ci NOT NULL,
  `title` varchar(60) collate utf8_unicode_ci default NULL COMMENT 'If this is set, it will be used instead of subscales.name when displaying',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `teaching_tips_percentages`
--

DROP TABLE IF EXISTS `teaching_tips_percentages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `teaching_tips_percentages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `clinic_id` int(11) NOT NULL,
  `teaching_tip_id` int(11) NOT NULL,
  `after_treatment` tinyint(1) NOT NULL,
  `percentage` tinyint(4) NOT NULL,
  `clinical_service` varchar(15) collate utf8_unicode_ci NOT NULL default 'Transplant',
  PRIMARY KEY  (`id`),
  KEY `site_id` (`clinic_id`,`teaching_tip_id`)
) ENGINE=MyISAM AUTO_INCREMENT=486 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_acl_leafs`
--

DROP TABLE IF EXISTS `user_acl_leafs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_acl_leafs` (
  `id` int(10) NOT NULL auto_increment,
  `user_id` int(10) NOT NULL,
  `acl_alias` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=127 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) collate utf8_unicode_ci NOT NULL,
  `password` varchar(64) collate utf8_unicode_ci NOT NULL,
  `first_name` varchar(64) collate utf8_unicode_ci NOT NULL,
  `last_name` varchar(64) collate utf8_unicode_ci NOT NULL,
  `email` varchar(40) collate utf8_unicode_ci default NULL,
  `change_pw_flag` tinyint(1) NOT NULL default '0' COMMENT 'If 1, must change password after next login',
  `clinic_id` int(11) NOT NULL default '1',
  `language` varchar(64) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `clinic_id` (`clinic_id`),
  KEY `last_name` (`last_name`)
) ENGINE=MyISAM AUTO_INCREMENT=129 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `users_acl_easy_to_read`
--

DROP TABLE IF EXISTS `users_acl_easy_to_read`;
/*!50001 DROP VIEW IF EXISTS `users_acl_easy_to_read`*/;
/*!50001 CREATE TABLE `users_acl_easy_to_read` (
  `id` int(11),
  `username` varchar(255),
  `first_name` varchar(64),
  `last_name` varchar(64),
  `email` varchar(40),
  `clinic_id` int(11),
  `acl_alias` varchar(255)
) */;

--
-- Table structure for table `users_deleted`
--

DROP TABLE IF EXISTS `users_deleted`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users_deleted` (
  `id` int(11) NOT NULL,
  `username` varchar(255) collate utf8_unicode_ci NOT NULL,
  `password` varchar(64) collate utf8_unicode_ci NOT NULL,
  `first_name` varchar(64) collate utf8_unicode_ci NOT NULL,
  `last_name` varchar(64) collate utf8_unicode_ci NOT NULL,
  `email` varchar(40) collate utf8_unicode_ci default NULL,
  `change_pw_flag` tinyint(1) NOT NULL default '0' COMMENT 'If 1, must change password after next login',
  `clinic_id` int(11) NOT NULL default '1',
  `language` varchar(64) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `clinic_id` (`clinic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `aros_acos_interpreted_view`
--

/*!50001 DROP TABLE `aros_acos_interpreted_view`*/;
/*!50001 DROP VIEW IF EXISTS `aros_acos_interpreted_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `aros_acos_interpreted_view` AS select `aros_acos`.`id` AS `id`,`aros_acos`.`aro_id` AS `aro_id`,`aros`.`alias` AS `aros_alias`,`aros_acos`.`aco_id` AS `aco_id`,`acos`.`alias` AS `acos_alias`,`aros_acos`.`_create` AS `_create`,`aros_acos`.`_read` AS `_read`,`aros_acos`.`_update` AS `_update`,`aros_acos`.`_delete` AS `_delete` from ((`aros_acos` join `aros` on((`aros_acos`.`aro_id` = `aros`.`id`))) join `acos` on((`aros_acos`.`aco_id` = `acos`.`id`))) */;

--
-- Final view structure for view `itemsThroughScalesByProject`
--

/*!50001 DROP TABLE `itemsThroughScalesByProject`*/;
/*!50001 DROP VIEW IF EXISTS `itemsThroughScalesByProject`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `itemsThroughScalesByProject` AS select `items`.`id` AS `item_id`,`items`.`question_id` AS `question_id`,`items`.`subscale_id` AS `subscale_id`,`subscales`.`name` AS `subscale_name`,`subscales`.`order` AS `subscale_order`,`scales`.`id` AS `scale_id`,`scales`.`name` AS `scale_name`,`scales`.`order` AS `scale_order`,`scales`.`questionnaire_id` AS `questionnaire_id`,`projects_questionnaires`.`project_id` AS `project_id` from (((`items` join `subscales` on((`items`.`subscale_id` = `subscales`.`id`))) join `scales` on((`subscales`.`scale_id` = `scales`.`id`))) join `projects_questionnaires` on((`projects_questionnaires`.`questionnaire_id` = `scales`.`questionnaire_id`))) order by `scales`.`order`,`subscales`.`order` limit 0,999 */;

--
-- Final view structure for view `logs_intervention_non_test`
--

/*!50001 DROP TABLE `logs_intervention_non_test`*/;
/*!50001 DROP VIEW IF EXISTS `logs_intervention_non_test`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `logs_intervention_non_test` AS select `logs`.`id` AS `id`,`logs`.`user_id` AS `user_id`,`logs`.`controller` AS `controller`,`logs`.`action` AS `action`,`logs`.`params` AS `params`,`logs`.`time` AS `time` from (`logs` join `patients` on((`logs`.`user_id` = `patients`.`id`))) where ((`patients`.`test_flag` = 0) and ((`logs`.`controller` = _utf8'results') or (`logs`.`controller` = _utf8'teaching') or (`logs`.`controller` = _utf8'journals') or (`logs`.`controller` = _utf8'associates')) and (not((`logs`.`action` like _utf8'%end%')))) order by `logs`.`user_id` */;

--
-- Final view structure for view `logs_intervention_non_test_count`
--

/*!50001 DROP TABLE `logs_intervention_non_test_count`*/;
/*!50001 DROP VIEW IF EXISTS `logs_intervention_non_test_count`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `logs_intervention_non_test_count` AS select count(0) AS `COUNT(*)` from `logs_intervention_non_test` */;

--
-- Final view structure for view `participant_demographics`
--

/*!50001 DROP TABLE `participant_demographics`*/;
/*!50001 DROP VIEW IF EXISTS `participant_demographics`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `participant_demographics` AS select `patients`.`id` AS `patient id`,`patients`.`gender` AS `gender`,`clinics`.`name` AS `clinic`,`survey_sessions`.`id` AS `survey_session` from (((`patients` join `users` on((`patients`.`id` = `users`.`id`))) join `survey_sessions` on((`patients`.`id` = `survey_sessions`.`patient_id`))) join `clinics` on((`clinics`.`id` = `users`.`clinic_id`))) where ((`patients`.`consent_status` = _utf8'consented') and (`patients`.`test_flag` <> 1) and (`survey_sessions`.`type` = _utf8'T1')) */;

--
-- Final view structure for view `patient_T1s`
--

/*!50001 DROP TABLE `patient_T1s`*/;
/*!50001 DROP VIEW IF EXISTS `patient_T1s`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `patient_T1s` AS select `patients`.`id` AS `patient`,`clinics`.`name` AS `clinic`,`survey_sessions`.`id` AS `survey_session` from (((`patients` join `users` on((`patients`.`id` = `users`.`id`))) join `survey_sessions` on((`patients`.`id` = `survey_sessions`.`patient_id`))) join `clinics` on((`clinics`.`id` = `users`.`clinic_id`))) where ((`patients`.`test_flag` <> 1) and (`survey_sessions`.`type` = _utf8'T1')) */;

--
-- Final view structure for view `patients_clinics_view`
--

/*!50001 DROP TABLE `patients_clinics_view`*/;
/*!50001 DROP VIEW IF EXISTS `patients_clinics_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `patients_clinics_view` AS select `clinics`.`name` AS `name`,`patients`.`id` AS `id`,`patients`.`MRN` AS `MRN`,`patients`.`test_flag` AS `test_flag`,`patients`.`phone1` AS `phone1`,`patients`.`phone2` AS `phone2`,`patients`.`mailing_address` AS `mailing_address`,`patients`.`study_participation_flag` AS `study_participation_flag`,`patients`.`user_type` AS `user_type`,`patients`.`consent_status` AS `consent_status`,`patients`.`consent_date` AS `consent_date`,`patients`.`consenter_id` AS `consenter_id`,`patients`.`consent_checked` AS `consent_checked`,`patients`.`hipaa_consent_checked` AS `hipaa_consent_checked`,`patients`.`clinical_service` AS `clinical_service`,`patients`.`treatment_start_date` AS `treatment_start_date`,`patients`.`birthdate` AS `birthdate`,`patients`.`gender` AS `gender`,`patients`.`ethnicity` AS `ethnicity`,`patients`.`eligible_flag` AS `eligible_flag`,`patients`.`study_group` AS `study_group`,`patients`.`secret_phrase` AS `secret_phrase`,`patients`.`t1` AS `t1`,`patients`.`t2` AS `t2`,`patients`.`t3` AS `t3`,`patients`.`t4` AS `t4`,`patients`.`t1_location` AS `t1_location`,`patients`.`t2_location` AS `t2_location`,`patients`.`t3_location` AS `t3_location`,`patients`.`t4_location` AS `t4_location`,`patients`.`t1_staff_id` AS `t1_staff_id`,`patients`.`t2_staff_id` AS `t2_staff_id`,`patients`.`t3_staff_id` AS `t3_staff_id`,`patients`.`t4_staff_id` AS `t4_staff_id`,`patients`.`check_again_date` AS `check_again_date`,`patients`.`no_more_check_agains` AS `no_more_check_agains`,`patients`.`t2a_subscale_id` AS `t2a_subscale_id`,`patients`.`t2b_subscale_id` AS `t2b_subscale_id`,`patients`.`off_study_status` AS `off_study_status`,`patients`.`off_study_reason` AS `off_study_reason`,`patients`.`off_study_timestamp` AS `off_study_timestamp` from ((`patients` join `users` on((`patients`.`id` = `users`.`id`))) join `clinics` on((`users`.`clinic_id` = `clinics`.`id`))) */;

--
-- Final view structure for view `patients_clinics_view_count`
--

/*!50001 DROP TABLE `patients_clinics_view_count`*/;
/*!50001 DROP VIEW IF EXISTS `patients_clinics_view_count`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `patients_clinics_view_count` AS select count(0) AS `COUNT(*)` from `patients_clinics_view` */;

--
-- Final view structure for view `scales_subscales_view`
--

/*!50001 DROP TABLE `scales_subscales_view`*/;
/*!50001 DROP VIEW IF EXISTS `scales_subscales_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `scales_subscales_view` AS select `subscales`.`name` AS `subscale`,`scales`.`name` AS `scale` from (`subscales` join `scales` on((`subscales`.`scale_id` = `scales`.`id`))) */;

--
-- Final view structure for view `survey_sessions_appt_dt`
--

/*!50001 DROP TABLE `survey_sessions_appt_dt`*/;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_appt_dt`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `survey_sessions_appt_dt` AS select `survey_sessions`.`id` AS `id`,`survey_sessions`.`modified` AS `modified`,`survey_sessions`.`user_id` AS `user_id`,`survey_sessions`.`project_id` AS `project_id`,`survey_sessions`.`started` AS `started`,`survey_sessions`.`patient_id` AS `patient_id`,`survey_sessions`.`type` AS `type`,`survey_sessions`.`appointment_id` AS `appointment_id`,`survey_sessions`.`partial_finalization` AS `partial_finalization`,`survey_sessions`.`finished` AS `finished`,`appointments`.`datetime` AS `appt_dt` from (`survey_sessions` join `appointments` on((`survey_sessions`.`appointment_id` = `appointments`.`id`))) */;

--
-- Final view structure for view `survey_sessions_non_test_view`
--

/*!50001 DROP TABLE `survey_sessions_non_test_view`*/;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_non_test_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `survey_sessions_non_test_view` AS select `survey_sessions`.`id` AS `id`,`survey_sessions`.`modified` AS `modified`,`survey_sessions`.`user_id` AS `user_id`,`survey_sessions`.`project_id` AS `project_id`,`survey_sessions`.`started` AS `started`,`survey_sessions`.`patient_id` AS `patient_id`,`survey_sessions`.`finished` AS `finished`,`survey_sessions`.`type` AS `type`,`survey_sessions`.`partial_finalization` AS `partial_finalization` from (`survey_sessions` join `patients` on((`survey_sessions`.`patient_id` = `patients`.`id`))) where (`patients`.`test_flag` = 0) order by `survey_sessions`.`patient_id` */;

--
-- Final view structure for view `survey_sessions_non_test_view_count`
--

/*!50001 DROP TABLE `survey_sessions_non_test_view_count`*/;
/*!50001 DROP VIEW IF EXISTS `survey_sessions_non_test_view_count`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `survey_sessions_non_test_view_count` AS select count(0) AS `COUNT(*)` from `survey_sessions_non_test_view` */;

--
-- Final view structure for view `users_acl_easy_to_read`
--

/*!50001 DROP TABLE `users_acl_easy_to_read`*/;
/*!50001 DROP VIEW IF EXISTS `users_acl_easy_to_read`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `users_acl_easy_to_read` AS select `users`.`id` AS `id`,`users`.`username` AS `username`,`users`.`first_name` AS `first_name`,`users`.`last_name` AS `last_name`,`users`.`email` AS `email`,`users`.`clinic_id` AS `clinic_id`,`user_acl_leafs`.`acl_alias` AS `acl_alias` from (`users` join `user_acl_leafs` on((`users`.`id` = `user_acl_leafs`.`user_id`))) */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-09-27 23:17:35
