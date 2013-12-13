# Use with extreme caution!
DELETE FROM projects WHERE id <> [PROJECT_TO_KEEP] 
DELETE FROM projects_questionnaires WHERE project_id <> [PROJECT_TO_KEEP]
DELETE FROM pages WHERE questionnaire_id NOT IN (SELECT id FROM questionnaires)
DELETE FROM questions WHERE page_id NOT IN (SELECT id FROM pages)
DELETE FROM options WHERE question_id NOT IN (SELECT id FROM questions)

