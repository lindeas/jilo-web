-- Create test database if not exists
CREATE DATABASE IF NOT EXISTS jilo_test;
USE jilo_test;

-- Create rate limiter table if not exists
CREATE TABLE IF NOT EXISTS security_rate_page (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create security_ip_whitelist table if not exists
CREATE TABLE IF NOT EXISTS security_ip_whitelist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    is_network BOOLEAN DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(255)
);

-- Create security_ip_blacklist table if not exists
CREATE TABLE IF NOT EXISTS security_ip_blacklist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    is_network BOOLEAN DEFAULT 0,
    reason TEXT,
    expiry_time TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(255)
);

-- Grant permissions to root user
GRANT ALL PRIVILEGES ON jilo_test.* TO 'root'@'localhost';
