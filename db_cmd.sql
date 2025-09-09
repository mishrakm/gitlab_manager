CREATE DATABASE gitlab_manager;
CREATE USER 'gitlab_user'@'localhost' IDENTIFIED BY 'Plus@1234';
GRANT ALL PRIVILEGES ON gitlab_manager.* TO 'gitlab_user'@'localhost';
FLUSH PRIVILEGES;
CREATE TABLE projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    project_name VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    git_project_id INT,
    branch TEXT,
    trigger_token VARCHAR(255),
    create_date DATETIME DEFAULT CURRENT_TIMESTAMP
);


-- SQL to create the project_properties table
CREATE TABLE project_properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    property_name VARCHAR(255) NOT NULL,
    property_value TEXT NOT NULL,
    selection_type ENUM('single', 'multiselect') NOT NULL DEFAULT 'single',
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
);