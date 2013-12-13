-- MySQL dump 10.13  Distrib 5.1.72, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: p3p_dev
-- ------------------------------------------------------
-- Server version	5.1.72-2

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
) ENGINE=MyISAM AUTO_INCREMENT=286 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acos`
--

LOCK TABLES `acos` WRITE;
/*!40000 ALTER TABLE `acos` DISABLE KEYS */;
INSERT INTO `acos` VALUES (1,NULL,NULL,NULL,'Controllers',1,376);
INSERT INTO `acos` VALUES (2,1,NULL,NULL,'Users',2,29);
INSERT INTO `acos` VALUES (3,1,NULL,NULL,'Logs',30,37);
INSERT INTO `acos` VALUES (4,1,NULL,NULL,'Admin',38,55);
INSERT INTO `acos` VALUES (5,1,NULL,NULL,'Surveys',56,101);
INSERT INTO `acos` VALUES (6,1,NULL,NULL,'Results',102,131);
INSERT INTO `acos` VALUES (7,1,NULL,NULL,'Teaching',132,141);
INSERT INTO `acos` VALUES (8,1,NULL,NULL,'Patients',142,217);
INSERT INTO `acos` VALUES (9,1,NULL,NULL,'TicketManager',218,219);
INSERT INTO `acos` VALUES (68,57,NULL,NULL,'acoUsersParticipantAssociate',410,411);
INSERT INTO `acos` VALUES (82,81,NULL,NULL,'acoUsersPatient',406,407);
INSERT INTO `acos` VALUES (65,57,NULL,NULL,'acoUsersAudioCoder',400,401);
INSERT INTO `acos` VALUES (66,57,NULL,NULL,'acoUsersParticipantTreatment',402,409);
INSERT INTO `acos` VALUES (62,61,NULL,NULL,'acoUsersFrontDeskStaff',382,383);
INSERT INTO `acos` VALUES (63,57,NULL,NULL,'acoUsersSurveyEditor',396,397);
INSERT INTO `acos` VALUES (64,57,NULL,NULL,'acoUsersResearcher',398,399);
INSERT INTO `acos` VALUES (61,60,NULL,NULL,'acoUsersClinicStaff',381,386);
INSERT INTO `acos` VALUES (60,59,NULL,NULL,'acoUsersResearchStaff',380,389);
INSERT INTO `acos` VALUES (59,58,NULL,NULL,'acoUsersCentralSupport',379,392);
INSERT INTO `acos` VALUES (57,NULL,NULL,NULL,'acoUsers',377,424);
INSERT INTO `acos` VALUES (58,57,NULL,NULL,'acoUsersAdmin',378,395);
INSERT INTO `acos` VALUES (83,1,NULL,NULL,'Journals',220,233);
INSERT INTO `acos` VALUES (81,66,NULL,NULL,'acoUsersParticipantControl',405,408);
INSERT INTO `acos` VALUES (84,2,NULL,NULL,'index',3,4);
INSERT INTO `acos` VALUES (85,5,NULL,NULL,'index',57,58);
INSERT INTO `acos` VALUES (86,2,NULL,NULL,'about',5,6);
INSERT INTO `acos` VALUES (87,2,NULL,NULL,'contact',7,8);
INSERT INTO `acos` VALUES (88,2,NULL,NULL,'help',9,10);
INSERT INTO `acos` VALUES (89,2,NULL,NULL,'edit',11,12);
INSERT INTO `acos` VALUES (90,2,NULL,NULL,'logout',13,14);
INSERT INTO `acos` VALUES (91,2,NULL,NULL,'changePasswordOfAuthdUser',15,16);
INSERT INTO `acos` VALUES (92,5,NULL,NULL,'new_session',59,60);
INSERT INTO `acos` VALUES (93,5,NULL,NULL,'show',61,62);
INSERT INTO `acos` VALUES (94,5,NULL,NULL,'restart',63,64);
INSERT INTO `acos` VALUES (95,5,NULL,NULL,'answer',65,66);
INSERT INTO `acos` VALUES (96,6,NULL,NULL,'index',103,104);
INSERT INTO `acos` VALUES (97,6,NULL,NULL,'show',105,106);
INSERT INTO `acos` VALUES (98,6,NULL,NULL,'showJournals',107,108);
INSERT INTO `acos` VALUES (99,7,NULL,NULL,'index',133,134);
INSERT INTO `acos` VALUES (100,83,NULL,NULL,'index',221,222);
INSERT INTO `acos` VALUES (101,83,NULL,NULL,'create',223,224);
INSERT INTO `acos` VALUES (102,83,NULL,NULL,'edit',225,226);
INSERT INTO `acos` VALUES (103,83,NULL,NULL,'delete',227,228);
INSERT INTO `acos` VALUES (104,6,NULL,NULL,'others',109,110);
INSERT INTO `acos` VALUES (105,4,NULL,NULL,'index',39,40);
INSERT INTO `acos` VALUES (106,4,NULL,NULL,'kiosk',41,42);
INSERT INTO `acos` VALUES (161,4,NULL,NULL,'viewNonAdminUsers',51,52);
INSERT INTO `acos` VALUES (108,4,NULL,NULL,'createStaff',43,44);
INSERT INTO `acos` VALUES (109,8,NULL,NULL,'index',143,144);
INSERT INTO `acos` VALUES (110,8,NULL,NULL,'add',145,146);
INSERT INTO `acos` VALUES (111,8,NULL,NULL,'edit',147,148);
INSERT INTO `acos` VALUES (112,8,NULL,NULL,'viewAll',149,150);
INSERT INTO `acos` VALUES (113,3,NULL,NULL,'index',31,32);
INSERT INTO `acos` VALUES (114,6,NULL,NULL,'othersReportsList',111,112);
INSERT INTO `acos` VALUES (115,6,NULL,NULL,'showToOthers',113,114);
INSERT INTO `acos` VALUES (116,6,NULL,NULL,'showJournalsToOthers',115,116);
INSERT INTO `acos` VALUES (118,1,NULL,NULL,'Associates',234,245);
INSERT INTO `acos` VALUES (119,118,NULL,NULL,'create',235,236);
INSERT INTO `acos` VALUES (120,118,NULL,NULL,'edit',237,238);
INSERT INTO `acos` VALUES (121,118,NULL,NULL,'phraseEntry',239,240);
INSERT INTO `acos` VALUES (122,83,NULL,NULL,'listForReadOnly',229,230);
INSERT INTO `acos` VALUES (124,83,NULL,NULL,'updateText',231,232);
INSERT INTO `acos` VALUES (125,5,NULL,NULL,'break_session',67,68);
INSERT INTO `acos` VALUES (126,2,NULL,NULL,'settings',17,18);
INSERT INTO `acos` VALUES (128,118,NULL,NULL,'edit_list',241,242);
INSERT INTO `acos` VALUES (129,5,NULL,NULL,'next_page',69,70);
INSERT INTO `acos` VALUES (130,5,NULL,NULL,'previous_page',71,72);
INSERT INTO `acos` VALUES (131,5,NULL,NULL,'summary',73,74);
INSERT INTO `acos` VALUES (132,5,NULL,NULL,'complete',75,76);
INSERT INTO `acos` VALUES (134,5,NULL,NULL,'questionnaires',77,78);
INSERT INTO `acos` VALUES (135,5,NULL,NULL,'questionnaire',79,80);
INSERT INTO `acos` VALUES (211,210,NULL,NULL,'clinic_report',319,320);
INSERT INTO `acos` VALUES (137,8,NULL,NULL,'resetPassword',151,152);
INSERT INTO `acos` VALUES (138,8,NULL,NULL,'changeUsername',153,154);
INSERT INTO `acos` VALUES (139,8,NULL,NULL,'view',155,156);
INSERT INTO `acos` VALUES (140,5,NULL,NULL,'generate_se_test',81,82);
INSERT INTO `acos` VALUES (141,8,NULL,NULL,'calendar',157,158);
INSERT INTO `acos` VALUES (142,8,NULL,NULL,'toggleNoteFlag',159,160);
INSERT INTO `acos` VALUES (143,8,NULL,NULL,'deleteNote',161,162);
INSERT INTO `acos` VALUES (144,8,NULL,NULL,'editNote',163,164);
INSERT INTO `acos` VALUES (145,8,NULL,NULL,'checkAgainCalendar',165,166);
INSERT INTO `acos` VALUES (146,8,NULL,NULL,'delete',167,168);
INSERT INTO `acos` VALUES (147,1,NULL,NULL,'Clinicians',246,265);
INSERT INTO `acos` VALUES (148,147,NULL,NULL,'add',247,248);
INSERT INTO `acos` VALUES (149,147,NULL,NULL,'edit',249,250);
INSERT INTO `acos` VALUES (150,147,NULL,NULL,'view',251,252);
INSERT INTO `acos` VALUES (151,147,NULL,NULL,'viewAll',253,254);
INSERT INTO `acos` VALUES (152,147,NULL,NULL,'toggleNoteFlag',255,256);
INSERT INTO `acos` VALUES (153,8,NULL,NULL,'changeDates',169,170);
INSERT INTO `acos` VALUES (154,4,NULL,NULL,'saveDatabase',45,46);
INSERT INTO `acos` VALUES (155,4,NULL,NULL,'viewDatabaseSnapshots',47,48);
INSERT INTO `acos` VALUES (156,4,NULL,NULL,'reloadDatabase',49,50);
INSERT INTO `acos` VALUES (157,118,NULL,NULL,'delete',243,244);
INSERT INTO `acos` VALUES (158,6,NULL,NULL,'data_export',117,118);
INSERT INTO `acos` VALUES (159,5,NULL,NULL,'summary_csv',83,84);
INSERT INTO `acos` VALUES (160,2,NULL,NULL,'updateTimeout',19,20);
INSERT INTO `acos` VALUES (162,4,NULL,NULL,'resetPassword',53,54);
INSERT INTO `acos` VALUES (163,147,NULL,NULL,'changePriority',257,258);
INSERT INTO `acos` VALUES (164,147,NULL,NULL,'generateWebkeys',259,260);
INSERT INTO `acos` VALUES (165,8,NULL,NULL,'noCheckAgain',171,172);
INSERT INTO `acos` VALUES (167,147,NULL,NULL,'emailSurveyLink',261,262);
INSERT INTO `acos` VALUES (168,1,NULL,NULL,'AudioFiles',266,285);
INSERT INTO `acos` VALUES (169,168,NULL,NULL,'index',267,268);
INSERT INTO `acos` VALUES (170,168,NULL,NULL,'upload',269,270);
INSERT INTO `acos` VALUES (210,1,NULL,NULL,'MedicalRecords',318,327);
INSERT INTO `acos` VALUES (172,168,NULL,NULL,'download',271,272);
INSERT INTO `acos` VALUES (173,168,NULL,NULL,'code',273,274);
INSERT INTO `acos` VALUES (174,168,NULL,NULL,'assignCoders',275,276);
INSERT INTO `acos` VALUES (175,168,NULL,NULL,'viewAll',277,278);
INSERT INTO `acos` VALUES (176,168,NULL,NULL,'viewMine',279,280);
INSERT INTO `acos` VALUES (177,8,NULL,NULL,'accrualReport',173,174);
INSERT INTO `acos` VALUES (178,8,NULL,NULL,'interested_report',175,176);
INSERT INTO `acos` VALUES (179,8,NULL,NULL,'search',177,178);
INSERT INTO `acos` VALUES (180,8,NULL,NULL,'offStudy',179,180);
INSERT INTO `acos` VALUES (181,168,NULL,NULL,'edit',281,282);
INSERT INTO `acos` VALUES (182,147,NULL,NULL,'consents',263,264);
INSERT INTO `acos` VALUES (183,8,NULL,NULL,'consents',181,182);
INSERT INTO `acos` VALUES (184,6,NULL,NULL,'options_export',119,120);
INSERT INTO `acos` VALUES (185,6,NULL,NULL,'questions_export',121,122);
INSERT INTO `acos` VALUES (186,6,NULL,NULL,'time_submitted_export',123,124);
INSERT INTO `acos` VALUES (196,1,NULL,NULL,'ChartCodings',306,311);
INSERT INTO `acos` VALUES (188,1,NULL,NULL,'DataAccess',286,305);
INSERT INTO `acos` VALUES (189,188,NULL,NULL,'index',287,288);
INSERT INTO `acos` VALUES (190,188,NULL,NULL,'index',289,290);
INSERT INTO `acos` VALUES (191,188,NULL,NULL,'data_export',291,292);
INSERT INTO `acos` VALUES (192,188,NULL,NULL,'options_export',293,294);
INSERT INTO `acos` VALUES (193,188,NULL,NULL,'questions_export',295,296);
INSERT INTO `acos` VALUES (194,188,NULL,NULL,'time_submitted_export',297,298);
INSERT INTO `acos` VALUES (197,196,NULL,NULL,'code',307,308);
INSERT INTO `acos` VALUES (198,1,NULL,NULL,'Charts',312,317);
INSERT INTO `acos` VALUES (199,198,NULL,NULL,'assignCoders',313,314);
INSERT INTO `acos` VALUES (200,198,NULL,NULL,'viewAll',315,316);
INSERT INTO `acos` VALUES (204,188,NULL,NULL,'demographics_export',299,300);
INSERT INTO `acos` VALUES (205,196,NULL,NULL,'review',309,310);
INSERT INTO `acos` VALUES (206,168,NULL,NULL,'review',283,284);
INSERT INTO `acos` VALUES (231,8,NULL,NULL,'activityDiary',183,184);
INSERT INTO `acos` VALUES (208,5,NULL,NULL,'edit',85,86);
INSERT INTO `acos` VALUES (209,5,NULL,NULL,'edit_project',87,88);
INSERT INTO `acos` VALUES (212,210,NULL,NULL,'clinic_report_pdf',321,322);
INSERT INTO `acos` VALUES (213,188,NULL,NULL,'scores_export',301,302);
INSERT INTO `acos` VALUES (214,188,NULL,NULL,'intervention_dose_export',303,304);
INSERT INTO `acos` VALUES (216,7,NULL,NULL,'log_click_to_external_resource',135,136);
INSERT INTO `acos` VALUES (218,7,NULL,NULL,'log_teaching_tip_expansion',137,138);
INSERT INTO `acos` VALUES (220,1,NULL,NULL,'ActivityDiaries',328,337);
INSERT INTO `acos` VALUES (221,220,NULL,NULL,'index',329,330);
INSERT INTO `acos` VALUES (229,6,NULL,NULL,'show_activity_diary_data',125,126);
INSERT INTO `acos` VALUES (223,220,NULL,NULL,'edit',331,332);
INSERT INTO `acos` VALUES (224,220,NULL,NULL,'test',333,334);
INSERT INTO `acos` VALUES (228,220,NULL,NULL,'read',335,336);
INSERT INTO `acos` VALUES (232,6,NULL,NULL,'log_click_to_external_resource',127,128);
INSERT INTO `acos` VALUES (230,7,NULL,NULL,'manage_fatigue',139,140);
INSERT INTO `acos` VALUES (233,6,NULL,NULL,'log_teaching_tip_expansion',129,130);
INSERT INTO `acos` VALUES (234,238,NULL,NULL,'index',339,340);
INSERT INTO `acos` VALUES (235,238,NULL,NULL,'statistics',341,342);
INSERT INTO `acos` VALUES (236,238,NULL,NULL,'factors',343,344);
INSERT INTO `acos` VALUES (237,238,NULL,NULL,'control',345,346);
INSERT INTO `acos` VALUES (238,1,NULL,NULL,'P3p',338,363);
INSERT INTO `acos` VALUES (239,238,NULL,NULL,'next_steps',347,348);
INSERT INTO `acos` VALUES (240,5,NULL,NULL,'reopen_test_session',89,90);
INSERT INTO `acos` VALUES (241,5,NULL,NULL,'finish_test_session',91,92);
INSERT INTO `acos` VALUES (242,8,NULL,NULL,'reset_step_last_visited',185,186);
INSERT INTO `acos` VALUES (243,8,NULL,NULL,'loginAs',187,188);
INSERT INTO `acos` VALUES (245,238,NULL,NULL,'log_next_step_view',349,350);
INSERT INTO `acos` VALUES (246,238,NULL,NULL,'log_statistic_view',351,352);
INSERT INTO `acos` VALUES (247,8,NULL,NULL,'takeSurveyAs',189,190);
INSERT INTO `acos` VALUES (248,238,NULL,NULL,'print_links',353,354);
INSERT INTO `acos` VALUES (249,5,NULL,NULL,'log_teaching_tip_expansion',93,94);
INSERT INTO `acos` VALUES (250,5,NULL,NULL,'log_click_to_external_resource',95,96);
INSERT INTO `acos` VALUES (251,8,NULL,NULL,'dashboard',191,192);
INSERT INTO `acos` VALUES (252,8,NULL,NULL,'dashboard_pdf',193,194);
INSERT INTO `acos` VALUES (253,238,NULL,NULL,'create_patient_p3p_teaching_entry',355,356);
INSERT INTO `acos` VALUES (254,8,NULL,NULL,'setLanguage',195,196);
INSERT INTO `acos` VALUES (255,2,NULL,NULL,'setLanguageForSelf',21,22);
INSERT INTO `acos` VALUES (256,8,NULL,NULL,'createAppt',197,198);
INSERT INTO `acos` VALUES (257,5,NULL,NULL,'overview',97,98);
INSERT INTO `acos` VALUES (258,2,NULL,NULL,'login_assist',23,24);
INSERT INTO `acos` VALUES (259,8,NULL,NULL,'dashboardForSelf',199,200);
INSERT INTO `acos` VALUES (260,8,NULL,NULL,'dashboardPdfForSelf',201,202);
INSERT INTO `acos` VALUES (261,1,NULL,NULL,'Medications',364,369);
INSERT INTO `acos` VALUES (262,261,NULL,NULL,'index',365,366);
INSERT INTO `acos` VALUES (263,261,NULL,NULL,'edit',367,368);
INSERT INTO `acos` VALUES (264,8,NULL,NULL,'medications',203,204);
INSERT INTO `acos` VALUES (265,5,NULL,NULL,'overview',99,100);
INSERT INTO `acos` VALUES (266,238,NULL,NULL,'overview',357,358);
INSERT INTO `acos` VALUES (267,238,NULL,NULL,'edit',359,360);
INSERT INTO `acos` VALUES (268,2,NULL,NULL,'selfRegister',25,26);
INSERT INTO `acos` VALUES (269,2,NULL,NULL,'registerEdit',27,28);
INSERT INTO `acos` VALUES (270,1,NULL,NULL,'Appointments',370,375);
INSERT INTO `acos` VALUES (271,270,NULL,NULL,'add',371,372);
INSERT INTO `acos` VALUES (272,270,NULL,NULL,'edit',373,374);
INSERT INTO `acos` VALUES (273,8,NULL,NULL,'sendEmail',205,206);
INSERT INTO `acos` VALUES (274,3,NULL,NULL,'saveEntry',33,34);
INSERT INTO `acos` VALUES (275,3,NULL,NULL,'add',35,36);
INSERT INTO `acos` VALUES (278,238,NULL,NULL,'whatdoyouthink',361,362);
INSERT INTO `acos` VALUES (279,210,NULL,NULL,'clinic_report_p3p',323,324);
INSERT INTO `acos` VALUES (280,210,NULL,NULL,'clinic_report_p3p_pdf',325,326);
INSERT INTO `acos` VALUES (281,8,NULL,NULL,'addStaffNote',207,208);
INSERT INTO `acos` VALUES (282,8,NULL,NULL,'flagStaffNote',209,210);
INSERT INTO `acos` VALUES (283,8,NULL,NULL,'oneMonthFollowup',211,212);
INSERT INTO `acos` VALUES (284,8,NULL,NULL,'sixMonthFollowup',213,214);
INSERT INTO `acos` VALUES (285,8,NULL,NULL,'oneWeekFollowup',215,216);
/*!40000 ALTER TABLE `acos` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aros`
--

LOCK TABLES `aros` WRITE;
/*!40000 ALTER TABLE `aros` DISABLE KEYS */;
INSERT INTO `aros` VALUES (1,NULL,'User',NULL,'aroNonStaff',2,15);
INSERT INTO `aros` VALUES (2,25,'User',NULL,'aroPatient',4,11);
INSERT INTO `aros` VALUES (3,2,'User',NULL,'aroParticipant',5,10);
INSERT INTO `aros` VALUES (4,1,'User',NULL,'aroParticipantAssociate',13,14);
INSERT INTO `aros` VALUES (5,NULL,'User',NULL,'aroStaff',16,33);
INSERT INTO `aros` VALUES (6,5,'User',NULL,'aroFrontDeskStaff',17,26);
INSERT INTO `aros` VALUES (7,6,'User',NULL,'aroClinicStaff',18,25);
INSERT INTO `aros` VALUES (8,7,'User',NULL,'aroResearchStaff',19,24);
INSERT INTO `aros` VALUES (9,8,'User',NULL,'aroCentralSupport',20,23);
INSERT INTO `aros` VALUES (10,9,'User',NULL,'aroAdmin',21,22);
INSERT INTO `aros` VALUES (11,5,'User',NULL,'aroSurveyEditor',27,28);
INSERT INTO `aros` VALUES (12,5,'User',NULL,'aroResearcher',29,30);
INSERT INTO `aros` VALUES (13,5,'User',NULL,'aroAudioCoder',31,32);
INSERT INTO `aros` VALUES (23,3,'User',NULL,'aroParticipantControl',6,9);
INSERT INTO `aros` VALUES (24,23,'User',NULL,'aroParticipantTreatment',7,8);
INSERT INTO `aros` VALUES (25,1,'User',NULL,'aroPatientIneligible',3,12);
/*!40000 ALTER TABLE `aros` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=331 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aros_acos`
--

LOCK TABLES `aros_acos` WRITE;
/*!40000 ALTER TABLE `aros_acos` DISABLE KEYS */;
INSERT INTO `aros_acos` VALUES (184,2,96,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (183,2,95,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (182,2,94,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (181,2,93,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (180,2,92,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (179,2,85,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (178,5,91,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (177,5,89,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (176,5,84,'-1','-1','-1','-1');
INSERT INTO `aros_acos` VALUES (175,1,91,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (280,2,232,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (185,2,97,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (186,2,98,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (281,2,233,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (276,2,99,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (188,2,100,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (189,2,101,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (190,2,102,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (191,2,103,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (192,4,104,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (193,4,114,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (194,4,116,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (173,1,84,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (174,1,89,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (195,4,115,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (197,7,106,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (198,10,113,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (199,6,105,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (200,2,119,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (201,2,120,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (202,4,121,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (203,4,122,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (206,2,124,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (241,9,161,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (208,9,108,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (209,2,125,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (210,1,126,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (211,5,126,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (212,2,128,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (213,2,129,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (214,2,130,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (215,7,131,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (233,9,155,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (217,3,132,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (218,2,134,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (219,2,135,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (275,24,229,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (268,12,188,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (227,7,8,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (229,2,132,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (230,8,140,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (234,9,154,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (235,9,156,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (236,2,157,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (237,9,158,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (238,8,159,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (239,1,160,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (240,5,160,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (242,9,162,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (267,7,210,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (246,10,185,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (247,10,184,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (248,10,186,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (249,9,188,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (274,7,228,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (256,9,174,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (259,9,199,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (278,2,218,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (263,7,113,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (264,11,208,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (265,11,131,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (266,11,209,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (269,12,131,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (277,2,216,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (271,24,220,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (279,24,7,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (282,24,238,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (283,7,240,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (284,7,241,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (285,7,92,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (286,7,93,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (287,7,94,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (288,7,95,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (289,7,132,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (290,7,129,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (291,7,130,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (292,7,125,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (293,7,135,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (294,7,134,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (295,2,249,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (296,2,250,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (298,10,141,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (300,2,255,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (299,10,190,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (301,7,254,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (302,11,257,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (303,7,259,'-1','-1','-1','-1');
INSERT INTO `aros_acos` VALUES (304,7,260,'-1','-1','-1','-1');
INSERT INTO `aros_acos` VALUES (305,7,261,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (306,2,256,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (307,11,265,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (308,11,266,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (309,11,267,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (310,2,268,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (311,2,269,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (312,7,270,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (313,2,271,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (314,2,272,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (315,7,273,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (316,23,239,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (317,1,274,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (318,1,275,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (321,24,278,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (322,23,248,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (323,7,281,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (324,25,253,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (325,7,282,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (326,5,275,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (327,7,145,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (328,7,283,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (329,7,284,'1','1','1','1');
INSERT INTO `aros_acos` VALUES (330,7,285,'1','1','1','1');
/*!40000 ALTER TABLE `aros_acos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-12-10 12:20:20
