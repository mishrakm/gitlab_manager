-- SQL to create the project_properties table
CREATE TABLE project_properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    property_name VARCHAR(255) NOT NULL,
    property_value TEXT NOT NULL,
    selection_type ENUM('single', 'multiselect') NOT NULL DEFAULT 'single',
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
);
