-- SQL to alter the projects table to add new fields
ALTER TABLE projects 
ADD COLUMN git_project_id INT,
ADD COLUMN branch TEXT,
ADD COLUMN trigger_token VARCHAR(255);
