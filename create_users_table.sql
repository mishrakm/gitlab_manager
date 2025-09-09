-- SQL to create the users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    usertype ENUM('admin', 'developer', 'deployer') NOT NULL DEFAULT 'developer'
);
