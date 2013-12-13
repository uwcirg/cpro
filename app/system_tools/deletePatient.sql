-- Delete a patient record and it's associations
-- Records from these tables are backed up first
INSERT INTO users_deleted SELECT * FROM users WHERE users.id = 12345;
DELETE FROM users WHERE users.id = 12345 LIMIT 1;
INSERT INTO patient_extensions_deleted SELECT * FROM patient_extensions WHERE patient_id = 12345;
INSERT INTO patients_deleted SELECT * FROM patients WHERE patients.id = 12345;
DELETE FROM patient_extensions WHERE patient_id = 12345 LIMIT 1;
DELETE FROM patients WHERE patients.id = 12345 LIMIT 1;
INSERT INTO survey_sessions_deleted SELECT * FROM survey_sessions WHERE survey_sessions.patient_id = 12345;
DELETE FROM survey_sessions WHERE survey_sessions.patient_id = 12345;
INSERT INTO appointments_deleted SELECT * FROM appointments WHERE appointments.patient_id = 12345;
DELETE FROM appointments WHERE appointments.patient_id = 12345;
INSERT INTO patients_associates_deleted SELECT * FROM patients_associates WHERE patients_associates.patient_id = 12345;
DELETE FROM patients_associates WHERE patients_associates.patient_id = 12345;
-- Records from these tables are NOT backed up first - USE THIS BLOCK W/ CAUTION!!!
DELETE FROM user_acl_leafs WHERE user_acl_leafs.user_id = 12345;
DELETE FROM notes WHERE notes.patient_id = 12345;
DELETE FROM meddays WHERE meddays.patient_id = 12345;
