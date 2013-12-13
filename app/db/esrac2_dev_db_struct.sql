-- MySQL dump 10.11
--
-- Host: localhost    Database: esrac2_dev
-- ------------------------------------------------------
-- Server version	5.0.51a-24+lenny4

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
-- Temporary table structure for view `T2_audio_file_view`
--

DROP TABLE IF EXISTS `T2_audio_file_view`;
/*!50001 DROP VIEW IF EXISTS `T2_audio_file_view`*/;
/*!50001 CREATE TABLE `T2_audio_file_view` (
  `patient_id` int(11),
  `status` enum('Raw','Downloaded for Scrubbing','Scrubbed','Assigned for Coding','Coder 1 Done','Coder 2 Done','To Be Recoded','All Coding Done','No Recording Made'),
  `present_during_recording` varchar(200),
  `questionnaire_completed` tinyint(1),
  `question_1` int(2),
  `question_2` int(2)
) */;

--
-- Temporary table structure for view `T2_audio_file_view_count`
--

DROP TABLE IF EXISTS `T2_audio_file_view_count`;
/*!50001 DROP VIEW IF EXISTS `T2_audio_file_view_count`*/;
/*!50001 CREATE TABLE `T2_audio_file_view_count` (
  `COUNT(*)` bigint(21)
) */;

--
-- Temporary table structure for view `accrual_view`
--

DROP TABLE IF EXISTS `accrual_view`;
/*!50001 DROP VIEW IF EXISTS `accrual_view`*/;
/*!50001 CREATE TABLE `accrual_view` (
  `patient_id` int(11),
  `consent_status` enum('pre-consent','consented','declined','ineligible'),
  `t1` datetime,
  `gender` enum('male','female'),
  `user_type` enum('Home/Independent','Clinic/Assisted'),
  `clinic_name` varchar(40),
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery'),
  `T1_session_id` int(11),
  `T1_session_started` datetime
) */;

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
) ENGINE=MyISAM AUTO_INCREMENT=216 DEFAULT CHARSET=utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=23101 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `answers_session_type`
--

DROP TABLE IF EXISTS `answers_session_type`;
/*!50001 DROP VIEW IF EXISTS `answers_session_type`*/;
/*!50001 CREATE TABLE `answers_session_type` (
  `id` int(11),
  `survey_session_id` int(11),
  `question_id` int(11),
  `option_id` int(11),
  `state` varchar(10),
  `body_text` text,
  `modified` timestamp,
  `type` enum('T1','T2','T3','T4','nonT','errantT')
) */;

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
) ENGINE=MyISAM AUTO_INCREMENT=270 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

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
) ENGINE=MyISAM AUTO_INCREMENT=580 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `audio_codings`
--

DROP TABLE IF EXISTS `audio_codings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `audio_codings` (
  `id` int(11) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL,
  `coder_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `ref_esrac` tinyint(1) default NULL,
  `ref_esrac_clinician_report` tinyint(1) default NULL,
  `ref_esrac_answer_qs` tinyint(1) default NULL,
  `ref_esrac_journal` tinyint(1) default NULL,
  `ref_esrac_graphs` tinyint(1) default NULL,
  `ref_esrac_teaching` tinyint(1) default NULL,
  `ref_esrac_external` tinyint(1) default NULL,
  `ref_esrac_share` tinyint(1) default NULL,
  `timestamp_start` time default NULL COMMENT 'beginning of recording is time = 0',
  `timestamp_end` time default NULL COMMENT 'beginning of recording is time = 0',
  `coding_complete` tinyint(1) NOT NULL default '0' COMMENT 'DATA NOT USED but field''s existence tells cake to gen a checkbox that we use',
  `recode` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `audio_codings_categories`
--

DROP TABLE IF EXISTS `audio_codings_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `audio_codings_categories` (
  `id` int(11) NOT NULL auto_increment,
  `audio_coding_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `non_category_desc` varchar(127) collate utf8_unicode_ci default NULL COMMENT 'junk?',
  `initiator` enum('None','Patient','Family','Female-RN','Female-ARNP','Female-MD/PA','Male-RN','Male-ARNP','Male-MD/PA','Unspec. clinician','Initiator Unclear') collate utf8_unicode_ci default NULL,
  `initiator_ts` varchar(50) collate utf8_unicode_ci default NULL,
  `problem` tinyint(4) default NULL,
  `problem_ts` varchar(50) collate utf8_unicode_ci default NULL,
  `severity` tinyint(1) default NULL,
  `severity_ts` varchar(50) collate utf8_unicode_ci default NULL,
  `pattern` tinyint(1) default NULL,
  `pattern_ts` varchar(50) collate utf8_unicode_ci default NULL,
  `allev_aggrav` tinyint(1) default NULL,
  `allev_aggrav_ts` varchar(50) collate utf8_unicode_ci default NULL,
  `request_help` tinyint(1) default NULL,
  `request_help_ts` varchar(50) collate utf8_unicode_ci default NULL,
  `treatment` enum('None','Pharm','Non-Pharm','Both') collate utf8_unicode_ci default NULL,
  `treatment_ts` varchar(50) collate utf8_unicode_ci default NULL,
  `referral` enum('None','Dietician','P.T.','Psych','Monitor/FU','Other','Pain/Palliative','Sexual Health','Social Work','Chaplain/Pastoral') collate utf8_unicode_ci default NULL,
  `referral_ts` varchar(50) collate utf8_unicode_ci default NULL,
  `cat_ref_to_esrac` tinyint(1) default NULL,
  `cat_ref_to_esrac_ts` varchar(50) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `audio_file_norecordingmade_view`
--

DROP TABLE IF EXISTS `audio_file_norecordingmade_view`;
/*!50001 DROP VIEW IF EXISTS `audio_file_norecordingmade_view`*/;
/*!50001 CREATE TABLE `audio_file_norecordingmade_view` (
  `patient_id` int(11),
  `status` enum('Raw','Downloaded for Scrubbing','Scrubbed','Assigned for Coding','Coder 1 Done','Coder 2 Done','To Be Recoded','All Coding Done','No Recording Made'),
  `present_during_recording` varchar(200),
  `questionnaire_completed` tinyint(1),
  `question_1` int(2),
  `question_2` int(2)
) */;

--
-- Table structure for table `audio_files`
--

DROP TABLE IF EXISTS `audio_files`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `audio_files` (
  `id` int(11) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL COMMENT 'foreign key in patients table',
  `file_number` int(10) default NULL COMMENT 'random number that forms base of filename',
  `status` enum('Raw','Downloaded for Scrubbing','Scrubbed','Assigned for Coding','Coder 1 Done','Coder 2 Done','To Be Recoded','All Coding Done','No Recording Made') collate utf8_unicode_ci default NULL,
  `present_during_recording` varchar(200) collate utf8_unicode_ci default NULL COMMENT 'freetext list of people present when recording was made',
  `double_coded_flag` tinyint(1) NOT NULL default '0' COMMENT 'if 1, file is assigned to two coders',
  `agreement` float default NULL COMMENT 'if double coded, Cohen''s kappa coefficient',
  `uploader_id` int(11) default NULL COMMENT 'foreign key into users table',
  `upload_timestamp` datetime default NULL,
  `original_name` varchar(100) collate utf8_unicode_ci default NULL COMMENT 'original name of the raw uploaded file',
  `scrubber_id` int(11) default NULL COMMENT 'foreign key into users table',
  `scrub_download_timestamp` datetime default NULL COMMENT 'last time raw file downloaded for scrubbing',
  `scrub_timestamp` datetime default NULL COMMENT 'time scrubbed file uploaded',
  `original_scrubbed_name` varchar(100) collate utf8_unicode_ci default NULL COMMENT 'original name of the scrubbed file',
  `coder1_id` int(11) default NULL COMMENT 'foreign key into users table',
  `coder2_id` int(11) default NULL COMMENT 'foreign key into users table',
  `assigned_timestamp` datetime default NULL COMMENT 'time coder(s) assigned',
  `coder1_timestamp` datetime default NULL COMMENT 'time coder 1 finished',
  `coder2_timestamp` datetime default NULL COMMENT 'time coder 2 finished',
  `recoding_timestamp` datetime default NULL,
  `questionnaire_completed` tinyint(1) NOT NULL default '0',
  `question_1` int(2) default NULL,
  `question_2` int(2) default NULL,
  PRIMARY KEY  (`id`),
  KEY `upload_timestamp` (`upload_timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=93 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `audio_files_deleted`
--

DROP TABLE IF EXISTS `audio_files_deleted`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `audio_files_deleted` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL COMMENT 'foreign key in patients table',
  `file_number` int(10) default NULL COMMENT 'random number that forms base of filename',
  `status` enum('Raw','Downloaded for Scrubbing','Scrubbed','Assigned for Coding','Coder 1 Done','Coder 2 Done','To Be Recoded','All Coding Done','No Recording Made') collate utf8_unicode_ci default NULL,
  `present_during_recording` varchar(200) collate utf8_unicode_ci default NULL COMMENT 'freetext list of people present when recording was made',
  `double_coded_flag` tinyint(1) NOT NULL default '0' COMMENT 'if 1, file is assigned to two coders',
  `agreement` float default NULL COMMENT 'if double coded, Cohen''s kappa coefficient',
  `uploader_id` int(11) default NULL COMMENT 'foreign key into users table',
  `upload_timestamp` datetime default NULL,
  `original_name` varchar(100) collate utf8_unicode_ci default NULL COMMENT 'original name of the raw uploaded file',
  `scrubber_id` int(11) default NULL COMMENT 'foreign key into users table',
  `scrub_download_timestamp` datetime default NULL COMMENT 'last time raw file downloaded for scrubbing',
  `scrub_timestamp` datetime default NULL COMMENT 'time scrubbed file uploaded',
  `original_scrubbed_name` varchar(100) collate utf8_unicode_ci default NULL COMMENT 'original name of the scrubbed file',
  `coder1_id` int(11) default NULL COMMENT 'foreign key into users table',
  `coder2_id` int(11) default NULL COMMENT 'foreign key into users table',
  `assigned_timestamp` datetime default NULL COMMENT 'time coder(s) assigned',
  `coder1_timestamp` datetime default NULL COMMENT 'time coder 1 finished',
  `coder2_timestamp` datetime default NULL COMMENT 'time coder 2 finished',
  `recoding_timestamp` datetime default NULL,
  `questionnaire_completed` tinyint(1) NOT NULL default '0',
  `question_1` int(2) default NULL,
  `question_2` int(2) default NULL,
  PRIMARY KEY  (`id`),
  KEY `upload_timestamp` (`upload_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `cake_sessions`
--

DROP TABLE IF EXISTS `cake_sessions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `cake_sessions` (
  `id` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `data` text collate utf8_unicode_ci,
  `expires` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `expires` (`expires`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(60) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Codable categories - rows in the audio and chart coding form';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `chart_codings`
--

DROP TABLE IF EXISTS `chart_codings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `chart_codings` (
  `id` int(11) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL,
  `coder_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `diagnosis` varchar(64) collate utf8_unicode_ci default NULL,
  `stage` enum('I','II','III','IV','N/A') collate utf8_unicode_ci default NULL,
  `oral_chemo` tinyint(1) default NULL,
  `treatment_start` date default NULL,
  `treatment_end` date default NULL,
  `coding_complete` tinyint(1) NOT NULL default '0',
  `recode` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `chart_codings_categories`
--

DROP TABLE IF EXISTS `chart_codings_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `chart_codings_categories` (
  `id` int(11) NOT NULL auto_increment,
  `chart_coding_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `non_category_desc` varchar(127) collate utf8_unicode_ci default NULL,
  `problem` enum('No','Yes','N/A') collate utf8_unicode_ci default NULL,
  `noted_by` enum('MD/PA','RN','ARNP','MD/PA, RN','MD/PA, ARNP','RN, ARNP','MD/PA, RN, ARNP') collate utf8_unicode_ci default NULL,
  `treatment` enum('Pharm','Non-Pharm','Both') collate utf8_unicode_ci default NULL,
  `reccmd_by` enum('MD/PA','RN','ARNP','MD/PA, RN','MD/PA, ARNP','RN, ARNP','MD/PA, RN, ARNP') collate utf8_unicode_ci default NULL,
  `referral` enum('Dietician','P.T.','Psych','Monitor/FU','Other','Pain/Palliative','Sexual Health','Social Work','Chaplain/Pastoral') collate utf8_unicode_ci default NULL,
  `dose_mod` date default NULL,
  `comments` varchar(255) collate utf8_unicode_ci default NULL,
  `phone` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `chart_coding_id` (`chart_coding_id`)
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `chart_codings_categories_deleted`
--

DROP TABLE IF EXISTS `chart_codings_categories_deleted`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `chart_codings_categories_deleted` (
  `id` int(11) NOT NULL,
  `chart_coding_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `non_category_desc` varchar(127) collate utf8_unicode_ci default NULL,
  `problem` enum('No','Yes') collate utf8_unicode_ci default NULL,
  `noted_by` enum('MD/PA','RN','ARNP','MD/PA, RN','MD/PA, ARNP','RN, ARNP','MD/PA, RN, ARNP') collate utf8_unicode_ci default NULL,
  `treatment` enum('Pharm','Non-Pharm','Both') collate utf8_unicode_ci default NULL,
  `reccmd_by` enum('MD/PA','RN','ARNP','MD/PA, RN','MD/PA, ARNP','RN, ARNP','MD/PA, RN, ARNP') collate utf8_unicode_ci default NULL,
  `referral` enum('Dietician','P.T.','Psych','Monitor/FU','Other','Pain/Palliative','Sexual Health','Social Work','Chaplain/Pastoral') collate utf8_unicode_ci default NULL,
  `dose_mod` date default NULL,
  `comments` varchar(255) collate utf8_unicode_ci default NULL,
  `phone` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `chart_coding_id` (`chart_coding_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `chart_codings_deleted`
--

DROP TABLE IF EXISTS `chart_codings_deleted`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `chart_codings_deleted` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `coder_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `diagnosis` varchar(64) collate utf8_unicode_ci default NULL,
  `stage` enum('I','II','III','IV','N/A') collate utf8_unicode_ci default NULL,
  `oral_chemo` tinyint(1) default NULL,
  `treatment_start` date default NULL,
  `treatment_end` date default NULL,
  `coding_complete` tinyint(1) NOT NULL default '0',
  `recode` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `charts`
--

DROP TABLE IF EXISTS `charts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `charts` (
  `id` int(11) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL,
  `status` enum('Assigned for Coding','Coder 1 Done','Coder 2 Done','To Be Recoded','All Coding Done') collate utf8_unicode_ci default NULL,
  `double_coded_flag` tinyint(1) NOT NULL default '0',
  `agreement` float default NULL,
  `coder1_id` int(11) default NULL,
  `coder2_id` int(11) default NULL,
  `assigned_timestamp` datetime default NULL,
  `coder1_timestamp` datetime default NULL,
  `coder2_timestamp` datetime default NULL,
  `recoding_timestamp` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `status` (`status`),
  KEY `coder1_id` (`coder1_id`),
  KEY `coder2_id` (`coder2_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `charts_deleted`
--

DROP TABLE IF EXISTS `charts_deleted`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `charts_deleted` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `status` enum('Assigned for Coding','Coder 1 Done','Coder 2 Done','To Be Recoded','All Coding Done') collate utf8_unicode_ci default NULL,
  `double_coded_flag` tinyint(1) NOT NULL default '0',
  `agreement` float default NULL,
  `coder1_id` int(11) default NULL,
  `coder2_id` int(11) default NULL,
  `assigned_timestamp` datetime default NULL,
  `coder1_timestamp` datetime default NULL,
  `coder2_timestamp` datetime default NULL,
  `recoding_timestamp` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `status` (`status`),
  KEY `coder1_id` (`coder1_id`),
  KEY `coder2_id` (`coder2_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `clinician_demographics_view`
--

DROP TABLE IF EXISTS `clinician_demographics_view`;
/*!50001 DROP VIEW IF EXISTS `clinician_demographics_view`*/;
/*!50001 CREATE TABLE `clinician_demographics_view` (
  `clinician_id` int(11),
  `clinic_id` int(11),
  `consent_date` date,
  `age_group` enum('20-29','30-39','40-49','50-59','60 and above'),
  `gender` enum('Male','Female'),
  `spanish/hispanic/latino` tinyint(1),
  `race` enum('White/Caucasian','Asian','Native Hawaiian or other Pacific Islander','Black/African-American','American Indian/Native American'),
  `demo_survey_complete_flag` tinyint(2)
) */;

--
-- Table structure for table `clinician_notes`
--

DROP TABLE IF EXISTS `clinician_notes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `clinician_notes` (
  `id` int(11) NOT NULL auto_increment,
  `clinician_id` int(11) NOT NULL,
  `text` varchar(10000) collate utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL COMMENT 'foreign key in the users table',
  `created` datetime NOT NULL,
  `flagged` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `clinician_races`
--

DROP TABLE IF EXISTS `clinician_races`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `clinician_races` (
  `id` int(11) NOT NULL auto_increment,
  `clinician_id` int(11) NOT NULL COMMENT 'foreign key in clinicians table',
  `race` enum('White/Caucasian','Asian','Native Hawaiian or other Pacific Islander','Black/African-American','American Indian/Native American') collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=124 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `clinicians`
--

DROP TABLE IF EXISTS `clinicians`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `clinicians` (
  `id` int(11) NOT NULL auto_increment,
  `first_name` varchar(64) collate utf8_unicode_ci NOT NULL,
  `last_name` varchar(64) collate utf8_unicode_ci NOT NULL,
  `email` varchar(40) collate utf8_unicode_ci default NULL,
  `mail_address` varchar(255) collate utf8_unicode_ci default NULL,
  `type` enum('MD-Attending','MD-Resident','MD-Fellow','PA','NP','RN','Other') collate utf8_unicode_ci default NULL,
  `clinic_id` int(11) NOT NULL,
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery') collate utf8_unicode_ci default NULL,
  `consent_status` enum('pre-consent','consented','declined') collate utf8_unicode_ci NOT NULL default 'pre-consent',
  `consent_date` date default NULL,
  `consent_checked` tinyint(1) NOT NULL default '0',
  `priority` enum('High','Med','Low') collate utf8_unicode_ci NOT NULL default 'Low',
  `check_again_date` date default NULL,
  `age_group` enum('20-29','30-39','40-49','50-59','60 and above') collate utf8_unicode_ci default NULL,
  `gender` enum('Male','Female') collate utf8_unicode_ci default NULL,
  `ethnicity_flag` tinyint(1) default NULL COMMENT 'self-identifies as Spanish/Hispanic/Latino',
  `specialty` enum('Heme/Stem Cell Transplant','Radiation Oncology','Medical Oncology','Surgical Oncology','Other') collate utf8_unicode_ci default NULL,
  `specialty_other` varchar(100) collate utf8_unicode_ci default NULL COMMENT 'Used only if specialty=''Other''',
  `position_title` enum('Attending MD','Resident/Fellow','ARNP or RN','Physician Assistant','Other') collate utf8_unicode_ci default NULL,
  `position_title_other` varchar(100) collate utf8_unicode_ci default NULL COMMENT 'Used only if position_title=''Other''',
  `webkey` int(10) NOT NULL,
  `demo_survey_complete_flag` tinyint(2) NOT NULL default '0' COMMENT '1 if demographic survey completed; -1 if it won''''t ever be',
  PRIMARY KEY  (`id`),
  KEY `consent_checked` (`consent_checked`)
) ENGINE=MyISAM AUTO_INCREMENT=339 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `clinicians_deleted`
--

DROP TABLE IF EXISTS `clinicians_deleted`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `clinicians_deleted` (
  `id` int(11) NOT NULL,
  `first_name` varchar(64) collate utf8_unicode_ci NOT NULL,
  `last_name` varchar(64) collate utf8_unicode_ci NOT NULL,
  `email` varchar(40) collate utf8_unicode_ci default NULL,
  `mail_address` varchar(255) collate utf8_unicode_ci default NULL,
  `type` enum('MD-Attending','MD-Resident','MD-Fellow','PA','NP','RN','Other') collate utf8_unicode_ci default NULL,
  `clinic_id` int(11) NOT NULL,
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery') collate utf8_unicode_ci default NULL,
  `consent_status` enum('pre-consent','consented','declined') collate utf8_unicode_ci NOT NULL default 'pre-consent',
  `consent_date` date default NULL,
  `consent_checked` tinyint(1) NOT NULL default '0',
  `priority` enum('High','Med','Low') collate utf8_unicode_ci NOT NULL default 'Low',
  `check_again_date` date default NULL,
  `age_group` enum('20-29','30-39','40-49','50-59','60 and above') collate utf8_unicode_ci default NULL,
  `gender` enum('Male','Female') collate utf8_unicode_ci default NULL,
  `ethnicity_flag` tinyint(1) NOT NULL default '0',
  `specialty` enum('Heme/Stem Cell Transplant','Radiation Oncology','Medical Oncology','Surgical Oncology','Other') collate utf8_unicode_ci default NULL,
  `specialty_other` varchar(100) collate utf8_unicode_ci default NULL COMMENT 'Used only if specialty=''Other''',
  `position_title` enum('Attending MD','Resident/Fellow','ARNP or RN','Physician Assistant','Other') collate utf8_unicode_ci default NULL,
  `position_title_other` varchar(100) collate utf8_unicode_ci default NULL COMMENT 'Used only if position_title=''Other''',
  `webkey` int(10) NOT NULL,
  `demo_survey_complete_flag` tinyint(1) NOT NULL default '0' COMMENT '1 if demographic survey completed',
  PRIMARY KEY  (`id`),
  KEY `consent_checked` (`consent_checked`)
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
) ENGINE=MyISAM AUTO_INCREMENT=161 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `consented_patients_T1s`
--

DROP TABLE IF EXISTS `consented_patients_T1s`;
/*!50001 DROP VIEW IF EXISTS `consented_patients_T1s`*/;
/*!50001 CREATE TABLE `consented_patients_T1s` (
  `id` int(11),
  `t1` datetime,
  `consent_date` date
) */;

--
-- Temporary table structure for view `consented_patients_T1s_count`
--

DROP TABLE IF EXISTS `consented_patients_T1s_count`;
/*!50001 DROP VIEW IF EXISTS `consented_patients_T1s_count`*/;
/*!50001 CREATE TABLE `consented_patients_T1s_count` (
  `COUNT(*)` bigint(21)
) */;

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
  `range` smallint(6) NOT NULL default '4' COMMENT 'num options - 1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='this is a join on questions & subscales';
SET character_set_client = @saved_cs_client;

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
) ENGINE=MyISAM AUTO_INCREMENT=364 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
  `action` varchar(20) collate utf8_unicode_ci NOT NULL,
  `params` varchar(512) collate utf8_unicode_ci default NULL,
  `time` datetime NOT NULL,
  `ip_address` varchar(20) collate utf8_unicode_ci NOT NULL,
  `user_agent` varchar(200) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id_index` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=192109 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
  `action` varchar(20),
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
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=MyISAM AUTO_INCREMENT=4333 DEFAULT CHARSET=utf8 PACK_KEYS=0;
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
) ENGINE=MyISAM AUTO_INCREMENT=353 DEFAULT CHARSET=utf8;
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
-- Temporary table structure for view `participants_T2_reportable_view`
--

DROP TABLE IF EXISTS `participants_T2_reportable_view`;
/*!50001 DROP VIEW IF EXISTS `participants_T2_reportable_view`*/;
/*!50001 CREATE TABLE `participants_T2_reportable_view` (
  `id` int(11),
  `MRN` varchar(10),
  `test_flag` tinyint(1),
  `phone1` varchar(20),
  `phone2` varchar(20),
  `mailing_address` varchar(255),
  `study_participation_flag` tinyint(1),
  `user_type` enum('Home/Independent','Clinic/Assisted'),
  `consent_status` enum('pre-consent','consented','declined','ineligible'),
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
-- Temporary table structure for view `participants_T2_reportable_view_count`
--

DROP TABLE IF EXISTS `participants_T2_reportable_view_count`;
/*!50001 DROP VIEW IF EXISTS `participants_T2_reportable_view_count`*/;
/*!50001 CREATE TABLE `participants_T2_reportable_view_count` (
  `COUNT(*)` bigint(21)
) */;

--
-- Temporary table structure for view `participants_T3_reportable_view`
--

DROP TABLE IF EXISTS `participants_T3_reportable_view`;
/*!50001 DROP VIEW IF EXISTS `participants_T3_reportable_view`*/;
/*!50001 CREATE TABLE `participants_T3_reportable_view` (
  `id` int(11),
  `MRN` varchar(10),
  `test_flag` tinyint(1),
  `phone1` varchar(20),
  `phone2` varchar(20),
  `mailing_address` varchar(255),
  `study_participation_flag` tinyint(1),
  `user_type` enum('Home/Independent','Clinic/Assisted'),
  `consent_status` enum('pre-consent','consented','declined','ineligible'),
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
-- Temporary table structure for view `participants_T4_reportable_view`
--

DROP TABLE IF EXISTS `participants_T4_reportable_view`;
/*!50001 DROP VIEW IF EXISTS `participants_T4_reportable_view`*/;
/*!50001 CREATE TABLE `participants_T4_reportable_view` (
  `id` int(11),
  `MRN` varchar(10),
  `test_flag` tinyint(1),
  `phone1` varchar(20),
  `phone2` varchar(20),
  `mailing_address` varchar(255),
  `study_participation_flag` tinyint(1),
  `user_type` enum('Home/Independent','Clinic/Assisted'),
  `consent_status` enum('pre-consent','consented','declined','ineligible'),
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
-- Temporary table structure for view `participants_T4_without_T3`
--

DROP TABLE IF EXISTS `participants_T4_without_T3`;
/*!50001 DROP VIEW IF EXISTS `participants_T4_without_T3`*/;
/*!50001 CREATE TABLE `participants_T4_without_T3` (
  `id` int(11),
  `MRN` varchar(10),
  `test_flag` tinyint(1),
  `phone1` varchar(20),
  `phone2` varchar(20),
  `mailing_address` varchar(255),
  `study_participation_flag` tinyint(1),
  `user_type` enum('Home/Independent','Clinic/Assisted'),
  `consent_status` enum('pre-consent','consented','declined','ineligible'),
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
-- Temporary table structure for view `patient_T1s_SCCA`
--

DROP TABLE IF EXISTS `patient_T1s_SCCA`;
/*!50001 DROP VIEW IF EXISTS `patient_T1s_SCCA`*/;
/*!50001 CREATE TABLE `patient_T1s_SCCA` (
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
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
  `consent_status` enum('pre-consent','consented','declined','ineligible') collate utf8_unicode_ci NOT NULL default 'pre-consent' COMMENT 'consented indicates that the patient is a participant',
  `consent_date` date default NULL,
  `consenter_id` int(11) default NULL COMMENT 'foreign key into users table',
  `consent_checked` tinyint(1) NOT NULL default '0',
  `hipaa_consent_checked` tinyint(1) NOT NULL default '0',
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery') collate utf8_unicode_ci default NULL,
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
  PRIMARY KEY  (`id`),
  KEY `birthdate` (`birthdate`),
  KEY `off_study_status` (`off_study_status`),
  KEY `off_study_timestamp` (`off_study_timestamp`),
  KEY `consent_checked` (`consent_checked`),
  KEY `consent_status` (`consent_status`),
  KEY `hipaa_consent_checked` (`hipaa_consent_checked`)
) ENGINE=MyISAM AUTO_INCREMENT=575 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=MyISAM AUTO_INCREMENT=192 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=MyISAM AUTO_INCREMENT=1833 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
  `consent_status` enum('pre-consent','consented','declined','ineligible'),
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
  `id` int(11) NOT NULL,
  `MRN` varchar(10) collate utf8_unicode_ci NOT NULL,
  `test_flag` tinyint(1) NOT NULL,
  `phone1` varchar(20) collate utf8_unicode_ci default NULL,
  `phone2` varchar(20) collate utf8_unicode_ci default NULL,
  `mailing_address` varchar(255) collate utf8_unicode_ci default NULL,
  `study_participation_flag` tinyint(1) NOT NULL default '0' COMMENT '1 if they requested to learn more about the study',
  `user_type` enum('Home/Independent','Clinic/Assisted') collate utf8_unicode_ci default NULL COMMENT 'used in randomization algorithm; should not be null if consent_status=consented',
  `consent_status` enum('pre-consent','consented','declined','ineligible') collate utf8_unicode_ci NOT NULL default 'pre-consent' COMMENT 'consented indicates that the patient is a participant',
  `consent_date` date default NULL,
  `consenter_id` int(11) default NULL COMMENT 'foreign key into users table',
  `clinical_service` enum('MedOnc','RadOnc','Transplant','Surgery') collate utf8_unicode_ci default NULL,
  `treatment_start_date` date default NULL,
  `birthdate` date NOT NULL,
  `gender` enum('male','female') collate utf8_unicode_ci NOT NULL COMMENT 'should be set by survey',
  `ethnicity` varchar(15) collate utf8_unicode_ci NOT NULL COMMENT 'apparently not used',
  `eligible_flag` tinyint(1) NOT NULL COMMENT 'apparently not used',
  `study_group` enum('Control','Treatment') collate utf8_unicode_ci default NULL COMMENT 'field is redundant given user_acl_leafs table, but still used; only set if patient has consented',
  `secret_phrase` varchar(50) collate utf8_unicode_ci default NULL,
  `t1` datetime default NULL,
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
  `t2a_subscale_id` int(11) default NULL,
  `t2b_subscale_id` int(11) default NULL,
  `consent_checked` tinyint(1) NOT NULL default '0',
  `off_study_status` enum('Completed all study requirements','Ineligible','Voluntary withdrawal','Lost to follow-up','Adverse effects','Other') collate utf8_unicode_ci default NULL,
  `off_study_reason` varchar(500) collate utf8_unicode_ci default NULL,
  `off_study_timestamp` timestamp NULL default NULL,
  `hipaa_consent_checked` tinyint(1) NOT NULL default '0',
  `no_more_check_agains` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `birthdate` (`birthdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
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
  PRIMARY KEY  (`id`),
  KEY `DATA_OUTPUT` (`page_id`,`Sequence`)
) ENGINE=MyISAM AUTO_INCREMENT=1029 DEFAULT CHARSET=utf8 PACK_KEYS=0;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `randomization_view`
--

DROP TABLE IF EXISTS `randomization_view`;
/*!50001 DROP VIEW IF EXISTS `randomization_view`*/;
/*!50001 CREATE TABLE `randomization_view` (
  `id` int(11),
  `consent_date` date,
  `user_type` enum('Home/Independent','Clinic/Assisted'),
  `study_group` enum('Control','Treatment'),
  `off_study_status` enum('Completed all study requirements','Ineligible','Voluntary withdrawal','Lost to follow-up','Adverse effects','Other')
) */;

--
-- Table structure for table `scales`
--

DROP TABLE IF EXISTS `scales`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `scales` (
  `id` int(11) NOT NULL auto_increment,
  `invert` tinyint(1) NOT NULL,
  `combination` varchar(20) collate utf8_unicode_ci NOT NULL,
  `questionnaire_id` int(11) NOT NULL,
  `range` smallint(6) NOT NULL COMMENT 'not being used, see subscales',
  `name` varchar(60) collate utf8_unicode_ci NOT NULL,
  `order` smallint(6) NOT NULL COMMENT 'as displayed in "View My/Others Reports" tabs',
  `base` smallint(6) NOT NULL default '1' COMMENT 'not being used, see subscales',
  `critical` smallint(5) unsigned default NULL COMMENT 'not being used, see subscales',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=MyISAM AUTO_INCREMENT=81363 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=MyISAM AUTO_INCREMENT=7989 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=MyISAM AUTO_INCREMENT=34005 DEFAULT CHARSET=latin1;
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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
  `critical` smallint(6) NOT NULL default '2',
  `combination` varchar(40) collate utf8_unicode_ci NOT NULL,
  `name` varchar(60) collate utf8_unicode_ci NOT NULL,
  `order` smallint(6) NOT NULL COMMENT 'as displayed in "View My/Others Reports" tabs',
  `category_id` int(11) default NULL COMMENT 'Which category represents this on the coding form',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
  `user_id` int(11) NOT NULL COMMENT 'Don''t use anymore!',
  `project_id` int(11) NOT NULL,
  `started` datetime NOT NULL,
  `patient_id` int(11) NOT NULL,
  `finished` tinyint(1) NOT NULL default '0',
  `type` enum('T1','T2','T3','T4','nonT','errantT') collate utf8_unicode_ci NOT NULL default 'nonT',
  `partial_finalization` tinyint(1) NOT NULL default '0' COMMENT 'only used for Tn sessions',
  PRIMARY KEY  (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM AUTO_INCREMENT=78900027 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `survey_sessions_deleted`
--

DROP TABLE IF EXISTS `survey_sessions_deleted`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `survey_sessions_deleted` (
  `id` int(11) NOT NULL,
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL COMMENT 'Don''t use anymore!',
  `project_id` int(11) NOT NULL,
  `started` datetime NOT NULL,
  `patient_id` int(11) NOT NULL,
  `finished` tinyint(1) NOT NULL default '0',
  `type` enum('T1','T2','T3','T4','nonT') collate utf8_unicode_ci NOT NULL default 'nonT',
  `partial_finalization` tinyint(1) NOT NULL default '0' COMMENT 'only used for Tn sessions',
  PRIMARY KEY  (`id`)
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `teaching_tips_bak100710`
--

DROP TABLE IF EXISTS `teaching_tips_bak100710`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `teaching_tips_bak100710` (
  `id` smallint(6) NOT NULL auto_increment,
  `subscale_id` smallint(6) NOT NULL,
  `text` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
) ENGINE=MyISAM AUTO_INCREMENT=388 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `target` datetime NOT NULL,
  `site_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='NO LONGER USED';
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `treatment_participants_wo_trtmnt_start`
--

DROP TABLE IF EXISTS `treatment_participants_wo_trtmnt_start`;
/*!50001 DROP VIEW IF EXISTS `treatment_participants_wo_trtmnt_start`*/;
/*!50001 CREATE TABLE `treatment_participants_wo_trtmnt_start` (
  `id` int(11),
  `MRN` varchar(10),
  `test_flag` tinyint(1),
  `phone1` varchar(20),
  `phone2` varchar(20),
  `mailing_address` varchar(255),
  `study_participation_flag` tinyint(1),
  `user_type` enum('Home/Independent','Clinic/Assisted'),
  `consent_status` enum('pre-consent','consented','declined','ineligible'),
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
-- Temporary table structure for view `treatment_participants_wo_trtmnt_start_count`
--

DROP TABLE IF EXISTS `treatment_participants_wo_trtmnt_start_count`;
/*!50001 DROP VIEW IF EXISTS `treatment_participants_wo_trtmnt_start_count`*/;
/*!50001 CREATE TABLE `treatment_participants_wo_trtmnt_start_count` (
  `COUNT(*)` bigint(21)
) */;

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
) ENGINE=MyISAM AUTO_INCREMENT=475 DEFAULT CHARSET=utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=580 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

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
-- Final view structure for view `T2_audio_file_view`
--

/*!50001 DROP TABLE `T2_audio_file_view`*/;
/*!50001 DROP VIEW IF EXISTS `T2_audio_file_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `T2_audio_file_view` AS select `audio_files`.`patient_id` AS `patient_id`,`audio_files`.`status` AS `status`,`audio_files`.`present_during_recording` AS `present_during_recording`,`audio_files`.`questionnaire_completed` AS `questionnaire_completed`,`audio_files`.`question_1` AS `question_1`,`audio_files`.`question_2` AS `question_2` from (`audio_files` join `participants_T2_reportable_view`) where (`participants_T2_reportable_view`.`id` = `audio_files`.`patient_id`) */;

--
-- Final view structure for view `T2_audio_file_view_count`
--

/*!50001 DROP TABLE `T2_audio_file_view_count`*/;
/*!50001 DROP VIEW IF EXISTS `T2_audio_file_view_count`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `T2_audio_file_view_count` AS select count(0) AS `COUNT(*)` from `T2_audio_file_view` */;

--
-- Final view structure for view `accrual_view`
--

/*!50001 DROP TABLE `accrual_view`*/;
/*!50001 DROP VIEW IF EXISTS `accrual_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `esrac2_dev`.`accrual_view` AS select `esrac2_dev`.`patients`.`id` AS `patient_id`,`esrac2_dev`.`patients`.`consent_status` AS `consent_status`,`esrac2_dev`.`patients`.`t1` AS `t1`,`esrac2_dev`.`patients`.`gender` AS `gender`,`esrac2_dev`.`patients`.`user_type` AS `user_type`,`esrac2_dev`.`clinics`.`name` AS `clinic_name`,`esrac2_dev`.`patients`.`clinical_service` AS `clinical_service`,`esrac2_dev`.`survey_sessions`.`id` AS `T1_session_id`,`esrac2_dev`.`survey_sessions`.`started` AS `T1_session_started` from (((`esrac2_dev`.`patients` join `esrac2_dev`.`users` on((`esrac2_dev`.`patients`.`id` = `esrac2_dev`.`users`.`id`))) join `esrac2_dev`.`clinics` on((`esrac2_dev`.`users`.`clinic_id` = `esrac2_dev`.`clinics`.`id`))) left join `esrac2_dev`.`survey_sessions` on(((`esrac2_dev`.`users`.`id` = `esrac2_dev`.`survey_sessions`.`patient_id`) and (`esrac2_dev`.`survey_sessions`.`type` = _utf8'T1')))) where ((`esrac2_dev`.`patients`.`t1` < convert_tz(now(),_utf8'-08:00',_utf8'+00:00')) and (`esrac2_dev`.`patients`.`test_flag` <> 1)) */;

--
-- Final view structure for view `answers_session_type`
--

/*!50001 DROP TABLE `answers_session_type`*/;
/*!50001 DROP VIEW IF EXISTS `answers_session_type`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `answers_session_type` AS select `answers`.`id` AS `id`,`answers`.`survey_session_id` AS `survey_session_id`,`answers`.`question_id` AS `question_id`,`answers`.`option_id` AS `option_id`,`answers`.`state` AS `state`,`answers`.`body_text` AS `body_text`,`answers`.`modified` AS `modified`,`survey_sessions`.`type` AS `type` from (`answers` join `survey_sessions` on((`answers`.`survey_session_id` = `survey_sessions`.`id`))) */;

--
-- Final view structure for view `audio_file_norecordingmade_view`
--

/*!50001 DROP TABLE `audio_file_norecordingmade_view`*/;
/*!50001 DROP VIEW IF EXISTS `audio_file_norecordingmade_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `audio_file_norecordingmade_view` AS select `audio_files`.`patient_id` AS `patient_id`,`audio_files`.`status` AS `status`,`audio_files`.`present_during_recording` AS `present_during_recording`,`audio_files`.`questionnaire_completed` AS `questionnaire_completed`,`audio_files`.`question_1` AS `question_1`,`audio_files`.`question_2` AS `question_2` from `audio_files` where (`audio_files`.`status` = _utf8'No Recording Made') */;

--
-- Final view structure for view `clinician_demographics_view`
--

/*!50001 DROP TABLE `clinician_demographics_view`*/;
/*!50001 DROP VIEW IF EXISTS `clinician_demographics_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `clinician_demographics_view` AS select `clinicians`.`id` AS `clinician_id`,`clinicians`.`clinic_id` AS `clinic_id`,`clinicians`.`consent_date` AS `consent_date`,`clinicians`.`age_group` AS `age_group`,`clinicians`.`gender` AS `gender`,`clinicians`.`ethnicity_flag` AS `spanish/hispanic/latino`,`clinician_races`.`race` AS `race`,`clinicians`.`demo_survey_complete_flag` AS `demo_survey_complete_flag` from (`clinicians` left join `clinician_races` on((`clinicians`.`id` = `clinician_races`.`clinician_id`))) order by `clinicians`.`id` */;

--
-- Final view structure for view `consented_patients_T1s`
--

/*!50001 DROP TABLE `consented_patients_T1s`*/;
/*!50001 DROP VIEW IF EXISTS `consented_patients_T1s`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `consented_patients_T1s` AS select `patients`.`id` AS `id`,`patients`.`t1` AS `t1`,`patients`.`consent_date` AS `consent_date` from `patients` where ((`patients`.`test_flag` = 0) and (`patients`.`consent_status` = _utf8'consented')) order by `patients`.`id` */;

--
-- Final view structure for view `consented_patients_T1s_count`
--

/*!50001 DROP TABLE `consented_patients_T1s_count`*/;
/*!50001 DROP VIEW IF EXISTS `consented_patients_T1s_count`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `consented_patients_T1s_count` AS select count(0) AS `COUNT(*)` from `consented_patients_T1s` */;

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
-- Final view structure for view `participants_T2_reportable_view`
--

/*!50001 DROP TABLE `participants_T2_reportable_view`*/;
/*!50001 DROP VIEW IF EXISTS `participants_T2_reportable_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `participants_T2_reportable_view` AS select `patients`.`id` AS `id`,`patients`.`MRN` AS `MRN`,`patients`.`test_flag` AS `test_flag`,`patients`.`phone1` AS `phone1`,`patients`.`phone2` AS `phone2`,`patients`.`mailing_address` AS `mailing_address`,`patients`.`study_participation_flag` AS `study_participation_flag`,`patients`.`user_type` AS `user_type`,`patients`.`consent_status` AS `consent_status`,`patients`.`consent_date` AS `consent_date`,`patients`.`consenter_id` AS `consenter_id`,`patients`.`consent_checked` AS `consent_checked`,`patients`.`hipaa_consent_checked` AS `hipaa_consent_checked`,`patients`.`clinical_service` AS `clinical_service`,`patients`.`treatment_start_date` AS `treatment_start_date`,`patients`.`birthdate` AS `birthdate`,`patients`.`gender` AS `gender`,`patients`.`ethnicity` AS `ethnicity`,`patients`.`eligible_flag` AS `eligible_flag`,`patients`.`study_group` AS `study_group`,`patients`.`secret_phrase` AS `secret_phrase`,`patients`.`t1` AS `t1`,`patients`.`t2` AS `t2`,`patients`.`t3` AS `t3`,`patients`.`t4` AS `t4`,`patients`.`t1_location` AS `t1_location`,`patients`.`t2_location` AS `t2_location`,`patients`.`t3_location` AS `t3_location`,`patients`.`t4_location` AS `t4_location`,`patients`.`t1_staff_id` AS `t1_staff_id`,`patients`.`t2_staff_id` AS `t2_staff_id`,`patients`.`t3_staff_id` AS `t3_staff_id`,`patients`.`t4_staff_id` AS `t4_staff_id`,`patients`.`check_again_date` AS `check_again_date`,`patients`.`no_more_check_agains` AS `no_more_check_agains`,`patients`.`t2a_subscale_id` AS `t2a_subscale_id`,`patients`.`t2b_subscale_id` AS `t2b_subscale_id`,`patients`.`off_study_status` AS `off_study_status`,`patients`.`off_study_reason` AS `off_study_reason`,`patients`.`off_study_timestamp` AS `off_study_timestamp` from (`patients` join `survey_sessions` on((`patients`.`id` = `survey_sessions`.`patient_id`))) where ((`patients`.`consent_status` = _utf8'consented') and (`survey_sessions`.`partial_finalization` = 1) and (`survey_sessions`.`type` = _utf8'T2')) */;

--
-- Final view structure for view `participants_T2_reportable_view_count`
--

/*!50001 DROP TABLE `participants_T2_reportable_view_count`*/;
/*!50001 DROP VIEW IF EXISTS `participants_T2_reportable_view_count`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `participants_T2_reportable_view_count` AS select count(0) AS `COUNT(*)` from `participants_T2_reportable_view` */;

--
-- Final view structure for view `participants_T3_reportable_view`
--

/*!50001 DROP TABLE `participants_T3_reportable_view`*/;
/*!50001 DROP VIEW IF EXISTS `participants_T3_reportable_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `participants_T3_reportable_view` AS select `patients`.`id` AS `id`,`patients`.`MRN` AS `MRN`,`patients`.`test_flag` AS `test_flag`,`patients`.`phone1` AS `phone1`,`patients`.`phone2` AS `phone2`,`patients`.`mailing_address` AS `mailing_address`,`patients`.`study_participation_flag` AS `study_participation_flag`,`patients`.`user_type` AS `user_type`,`patients`.`consent_status` AS `consent_status`,`patients`.`consent_date` AS `consent_date`,`patients`.`consenter_id` AS `consenter_id`,`patients`.`consent_checked` AS `consent_checked`,`patients`.`hipaa_consent_checked` AS `hipaa_consent_checked`,`patients`.`clinical_service` AS `clinical_service`,`patients`.`treatment_start_date` AS `treatment_start_date`,`patients`.`birthdate` AS `birthdate`,`patients`.`gender` AS `gender`,`patients`.`ethnicity` AS `ethnicity`,`patients`.`eligible_flag` AS `eligible_flag`,`patients`.`study_group` AS `study_group`,`patients`.`secret_phrase` AS `secret_phrase`,`patients`.`t1` AS `t1`,`patients`.`t2` AS `t2`,`patients`.`t3` AS `t3`,`patients`.`t4` AS `t4`,`patients`.`t1_location` AS `t1_location`,`patients`.`t2_location` AS `t2_location`,`patients`.`t3_location` AS `t3_location`,`patients`.`t4_location` AS `t4_location`,`patients`.`t1_staff_id` AS `t1_staff_id`,`patients`.`t2_staff_id` AS `t2_staff_id`,`patients`.`t3_staff_id` AS `t3_staff_id`,`patients`.`t4_staff_id` AS `t4_staff_id`,`patients`.`check_again_date` AS `check_again_date`,`patients`.`no_more_check_agains` AS `no_more_check_agains`,`patients`.`t2a_subscale_id` AS `t2a_subscale_id`,`patients`.`t2b_subscale_id` AS `t2b_subscale_id`,`patients`.`off_study_status` AS `off_study_status`,`patients`.`off_study_reason` AS `off_study_reason`,`patients`.`off_study_timestamp` AS `off_study_timestamp` from (`patients` join `survey_sessions` on((`patients`.`id` = `survey_sessions`.`patient_id`))) where ((`patients`.`consent_status` = _utf8'consented') and (`survey_sessions`.`partial_finalization` = 1) and (`survey_sessions`.`type` = _utf8'T3')) */;

--
-- Final view structure for view `participants_T4_reportable_view`
--

/*!50001 DROP TABLE `participants_T4_reportable_view`*/;
/*!50001 DROP VIEW IF EXISTS `participants_T4_reportable_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `participants_T4_reportable_view` AS select `patients`.`id` AS `id`,`patients`.`MRN` AS `MRN`,`patients`.`test_flag` AS `test_flag`,`patients`.`phone1` AS `phone1`,`patients`.`phone2` AS `phone2`,`patients`.`mailing_address` AS `mailing_address`,`patients`.`study_participation_flag` AS `study_participation_flag`,`patients`.`user_type` AS `user_type`,`patients`.`consent_status` AS `consent_status`,`patients`.`consent_date` AS `consent_date`,`patients`.`consenter_id` AS `consenter_id`,`patients`.`consent_checked` AS `consent_checked`,`patients`.`hipaa_consent_checked` AS `hipaa_consent_checked`,`patients`.`clinical_service` AS `clinical_service`,`patients`.`treatment_start_date` AS `treatment_start_date`,`patients`.`birthdate` AS `birthdate`,`patients`.`gender` AS `gender`,`patients`.`ethnicity` AS `ethnicity`,`patients`.`eligible_flag` AS `eligible_flag`,`patients`.`study_group` AS `study_group`,`patients`.`secret_phrase` AS `secret_phrase`,`patients`.`t1` AS `t1`,`patients`.`t2` AS `t2`,`patients`.`t3` AS `t3`,`patients`.`t4` AS `t4`,`patients`.`t1_location` AS `t1_location`,`patients`.`t2_location` AS `t2_location`,`patients`.`t3_location` AS `t3_location`,`patients`.`t4_location` AS `t4_location`,`patients`.`t1_staff_id` AS `t1_staff_id`,`patients`.`t2_staff_id` AS `t2_staff_id`,`patients`.`t3_staff_id` AS `t3_staff_id`,`patients`.`t4_staff_id` AS `t4_staff_id`,`patients`.`check_again_date` AS `check_again_date`,`patients`.`no_more_check_agains` AS `no_more_check_agains`,`patients`.`t2a_subscale_id` AS `t2a_subscale_id`,`patients`.`t2b_subscale_id` AS `t2b_subscale_id`,`patients`.`off_study_status` AS `off_study_status`,`patients`.`off_study_reason` AS `off_study_reason`,`patients`.`off_study_timestamp` AS `off_study_timestamp` from (`patients` join `survey_sessions` on((`patients`.`id` = `survey_sessions`.`patient_id`))) where ((`patients`.`consent_status` = _utf8'consented') and (`survey_sessions`.`partial_finalization` = 1) and (`survey_sessions`.`type` = _utf8'T4')) */;

--
-- Final view structure for view `participants_T4_without_T3`
--

/*!50001 DROP TABLE `participants_T4_without_T3`*/;
/*!50001 DROP VIEW IF EXISTS `participants_T4_without_T3`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `participants_T4_without_T3` AS select `participants_T4_reportable_view`.`id` AS `id`,`participants_T4_reportable_view`.`MRN` AS `MRN`,`participants_T4_reportable_view`.`test_flag` AS `test_flag`,`participants_T4_reportable_view`.`phone1` AS `phone1`,`participants_T4_reportable_view`.`phone2` AS `phone2`,`participants_T4_reportable_view`.`mailing_address` AS `mailing_address`,`participants_T4_reportable_view`.`study_participation_flag` AS `study_participation_flag`,`participants_T4_reportable_view`.`user_type` AS `user_type`,`participants_T4_reportable_view`.`consent_status` AS `consent_status`,`participants_T4_reportable_view`.`consent_date` AS `consent_date`,`participants_T4_reportable_view`.`consenter_id` AS `consenter_id`,`participants_T4_reportable_view`.`consent_checked` AS `consent_checked`,`participants_T4_reportable_view`.`hipaa_consent_checked` AS `hipaa_consent_checked`,`participants_T4_reportable_view`.`clinical_service` AS `clinical_service`,`participants_T4_reportable_view`.`treatment_start_date` AS `treatment_start_date`,`participants_T4_reportable_view`.`birthdate` AS `birthdate`,`participants_T4_reportable_view`.`gender` AS `gender`,`participants_T4_reportable_view`.`ethnicity` AS `ethnicity`,`participants_T4_reportable_view`.`eligible_flag` AS `eligible_flag`,`participants_T4_reportable_view`.`study_group` AS `study_group`,`participants_T4_reportable_view`.`secret_phrase` AS `secret_phrase`,`participants_T4_reportable_view`.`t1` AS `t1`,`participants_T4_reportable_view`.`t2` AS `t2`,`participants_T4_reportable_view`.`t3` AS `t3`,`participants_T4_reportable_view`.`t4` AS `t4`,`participants_T4_reportable_view`.`t1_location` AS `t1_location`,`participants_T4_reportable_view`.`t2_location` AS `t2_location`,`participants_T4_reportable_view`.`t3_location` AS `t3_location`,`participants_T4_reportable_view`.`t4_location` AS `t4_location`,`participants_T4_reportable_view`.`t1_staff_id` AS `t1_staff_id`,`participants_T4_reportable_view`.`t2_staff_id` AS `t2_staff_id`,`participants_T4_reportable_view`.`t3_staff_id` AS `t3_staff_id`,`participants_T4_reportable_view`.`t4_staff_id` AS `t4_staff_id`,`participants_T4_reportable_view`.`check_again_date` AS `check_again_date`,`participants_T4_reportable_view`.`no_more_check_agains` AS `no_more_check_agains`,`participants_T4_reportable_view`.`t2a_subscale_id` AS `t2a_subscale_id`,`participants_T4_reportable_view`.`t2b_subscale_id` AS `t2b_subscale_id`,`participants_T4_reportable_view`.`off_study_status` AS `off_study_status`,`participants_T4_reportable_view`.`off_study_reason` AS `off_study_reason`,`participants_T4_reportable_view`.`off_study_timestamp` AS `off_study_timestamp` from (`participants_T4_reportable_view` left join `participants_T3_reportable_view` on((`participants_T4_reportable_view`.`id` = `participants_T3_reportable_view`.`id`))) where isnull(`participants_T3_reportable_view`.`id`) */;

--
-- Final view structure for view `patient_T1s`
--

/*!50001 DROP TABLE `patient_T1s`*/;
/*!50001 DROP VIEW IF EXISTS `patient_T1s`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `patient_T1s` AS select `patients`.`id` AS `patient`,`clinics`.`name` AS `clinic`,`survey_sessions`.`id` AS `survey_session` from (((`patients` join `users` on((`patients`.`id` = `users`.`id`))) join `survey_sessions` on((`patients`.`id` = `survey_sessions`.`patient_id`))) join `clinics` on((`clinics`.`id` = `users`.`clinic_id`))) where ((`patients`.`test_flag` <> 1) and (`survey_sessions`.`type` = _utf8'T1')) */;

--
-- Final view structure for view `patient_T1s_SCCA`
--

/*!50001 DROP TABLE `patient_T1s_SCCA`*/;
/*!50001 DROP VIEW IF EXISTS `patient_T1s_SCCA`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `patient_T1s_SCCA` AS select `patients`.`id` AS `patient`,`clinics`.`name` AS `clinic`,`survey_sessions`.`id` AS `survey_session` from (((`patients` join `users` on((`patients`.`id` = `users`.`id`))) join `survey_sessions` on((`patients`.`id` = `survey_sessions`.`patient_id`))) join `clinics` on((`clinics`.`id` = `users`.`clinic_id`))) where ((`patients`.`test_flag` <> 1) and (`survey_sessions`.`type` = _utf8'T1') and (`clinics`.`name` = _utf8'SCCA-Transplant')) order by `survey_sessions`.`id` */;

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
-- Final view structure for view `randomization_view`
--

/*!50001 DROP TABLE `randomization_view`*/;
/*!50001 DROP VIEW IF EXISTS `randomization_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `randomization_view` AS select `patients`.`id` AS `id`,`patients`.`consent_date` AS `consent_date`,`patients`.`user_type` AS `user_type`,`patients`.`study_group` AS `study_group`,`patients`.`off_study_status` AS `off_study_status` from `patients` where ((`patients`.`test_flag` = 0) and (`patients`.`consent_status` = _utf8'consented')) order by `patients`.`consent_date` */;

--
-- Final view structure for view `scales_subscales_view`
--

/*!50001 DROP TABLE `scales_subscales_view`*/;
/*!50001 DROP VIEW IF EXISTS `scales_subscales_view`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `scales_subscales_view` AS select `subscales`.`name` AS `subscale`,`scales`.`name` AS `scale` from (`subscales` join `scales` on((`subscales`.`scale_id` = `scales`.`id`))) */;

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
-- Final view structure for view `treatment_participants_wo_trtmnt_start`
--

/*!50001 DROP TABLE `treatment_participants_wo_trtmnt_start`*/;
/*!50001 DROP VIEW IF EXISTS `treatment_participants_wo_trtmnt_start`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `treatment_participants_wo_trtmnt_start` AS select `patients`.`id` AS `id`,`patients`.`MRN` AS `MRN`,`patients`.`test_flag` AS `test_flag`,`patients`.`phone1` AS `phone1`,`patients`.`phone2` AS `phone2`,`patients`.`mailing_address` AS `mailing_address`,`patients`.`study_participation_flag` AS `study_participation_flag`,`patients`.`user_type` AS `user_type`,`patients`.`consent_status` AS `consent_status`,`patients`.`consent_date` AS `consent_date`,`patients`.`consenter_id` AS `consenter_id`,`patients`.`consent_checked` AS `consent_checked`,`patients`.`hipaa_consent_checked` AS `hipaa_consent_checked`,`patients`.`clinical_service` AS `clinical_service`,`patients`.`treatment_start_date` AS `treatment_start_date`,`patients`.`birthdate` AS `birthdate`,`patients`.`gender` AS `gender`,`patients`.`ethnicity` AS `ethnicity`,`patients`.`eligible_flag` AS `eligible_flag`,`patients`.`study_group` AS `study_group`,`patients`.`secret_phrase` AS `secret_phrase`,`patients`.`t1` AS `t1`,`patients`.`t2` AS `t2`,`patients`.`t3` AS `t3`,`patients`.`t4` AS `t4`,`patients`.`t1_location` AS `t1_location`,`patients`.`t2_location` AS `t2_location`,`patients`.`t3_location` AS `t3_location`,`patients`.`t4_location` AS `t4_location`,`patients`.`t1_staff_id` AS `t1_staff_id`,`patients`.`t2_staff_id` AS `t2_staff_id`,`patients`.`t3_staff_id` AS `t3_staff_id`,`patients`.`t4_staff_id` AS `t4_staff_id`,`patients`.`check_again_date` AS `check_again_date`,`patients`.`no_more_check_agains` AS `no_more_check_agains`,`patients`.`t2a_subscale_id` AS `t2a_subscale_id`,`patients`.`t2b_subscale_id` AS `t2b_subscale_id`,`patients`.`off_study_status` AS `off_study_status`,`patients`.`off_study_reason` AS `off_study_reason`,`patients`.`off_study_timestamp` AS `off_study_timestamp` from `patients` where ((`patients`.`study_group` = _utf8'Treatment') and isnull(`patients`.`treatment_start_date`)) */;

--
-- Final view structure for view `treatment_participants_wo_trtmnt_start_count`
--

/*!50001 DROP TABLE `treatment_participants_wo_trtmnt_start_count`*/;
/*!50001 DROP VIEW IF EXISTS `treatment_participants_wo_trtmnt_start_count`*/;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`mcjustin`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `treatment_participants_wo_trtmnt_start_count` AS select count(0) AS `COUNT(*)` from `treatment_participants_wo_trtmnt_start` */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-12-21  2:02:07
