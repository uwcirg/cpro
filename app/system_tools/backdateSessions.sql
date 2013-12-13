-- FIXME 123456
UPDATE `patients` SET `consent_date` = SUBTIME(`consent_date`, '7 0:0:0') WHERE `id` = 123456;
UPDATE `appointments` SET `datetime` = SUBTIME(`datetime`, '7 0:0:0') WHERE `patient_id` = 123456;
UPDATE `survey_sessions` SET `modified` = SUBTIME(`modified`, '7 0:0:0') WHERE `patient_id` = 123456;
UPDATE `survey_sessions` SET `started` = SUBTIME(`started`, '7 0:0:0') WHERE `patient_id` = 123456;
UPDATE `survey_sessions` SET `reportable_datetime` = SUBTIME(`reportable_datetime`, '7 0:0:0') WHERE `patient_id` = 123456;
UPDATE `answers` SET `modified` = SUBTIME(`modified`, '7 0:0:0') WHERE `survey_session_id` IN (SELECT id FROM `survey_sessions` WHERE patient_id = 123456);

