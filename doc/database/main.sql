-- Time: 13.03.2025, 15:52
-- Server: 11.4.5-MariaDB-1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

--
-- User profiles
--

-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1,'demo','$2y$10$tLCLvgYu91gf/zBoc58Am.iVls/SOMcIXO3ykGfgFFei9yneZTrb2'),
(2,'demo1','$2y$10$LtV9m.rMCJ.K/g45e6tzDexZ8C/9xxu3qFCkvz92pUYa7Jg06np0i');

-- --------------------------------------------------------
CREATE TABLE `users_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  PRIMARY KEY (`id`,`user_id`) USING BTREE,
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_meta_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `users_meta` (`id`, `user_id`, `name`, `email`, `timezone`, `avatar`, `bio`) VALUES
(1,1,'demo admin user','admin@example.com',NULL,NULL,'This is a demo user of the demo install of Jilo Web'),
(2,2,'demo user','demo@example.com',NULL,NULL,'This is a demo user of the demo install of Jilo Web');

-- --------------------------------------------------------
CREATE TABLE `rights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `rights` (`id`, `name`) VALUES
(1, 'superuser'),
(2, 'edit users'),
(3, 'view config file'),
(4, 'edit config file'),
(5, 'view own profile'),
(6, 'edit own profile'),
(7, 'view all profiles'),
(8, 'edit all profiles'),
(9, 'view app logs'),
(10, 'manage plugins'),
(11,'view all platforms'),
(12,'edit all platforms'),
(13,'view all agents'),
(14,'edit all agents'),
(15,'view jilo config');

-- --------------------------------------------------------
CREATE TABLE `users_right` (
  `user_id` int(11) NOT NULL,
  `right_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`right_id`),
  KEY `fk_right_id` (`right_id`),
  CONSTRAINT `fk_right_id` FOREIGN KEY (`right_id`) REFERENCES `rights` (`id`),
  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------
CREATE TABLE `user_2fa` (
  `user_id` int(11) NOT NULL,
  `secret_key` varchar(64) NOT NULL,
  `backup_codes` text,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `last_used` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_2fa_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------
CREATE TABLE `user_2fa_temp` (
  `user_id` int(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`, `code`),
  CONSTRAINT `fk_user_2fa_temp_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------
CREATE TABLE `user_password_reset` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` int(11) NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_password_reset` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  UNIQUE KEY `token_idx` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Login security
--

-- --------------------------------------------------------
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(255) NOT NULL,
  `attempted_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_username` (`ip_address`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------
CREATE TABLE `pages_rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `request_time` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_endpoint` (`ip_address`,`endpoint`),
  KEY `idx_request_time` (`request_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------
CREATE TABLE `ip_blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `is_network` tinyint(1) DEFAULT 0,
  `reason` varchar(255) DEFAULT NULL,
  `expiry_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `ip_blacklist` (`id`, `ip_address`, `is_network`, `reason`, `expiry_time`, `created_at`, `created_by`) VALUES
(1, '0.0.0.0/8', 1, 'Reserved address space - RFC 1122', NULL, '2025-01-03 16:40:15', 'system'),
(2, '100.64.0.0/10', 1, 'Carrier-grade NAT space - RFC 6598', NULL, '2025-01-03 16:40:15', 'system'),
(3, '192.0.2.0/24', 1, 'TEST-NET-1 Documentation space - RFC 5737', NULL, '2025-01-03 16:40:15', 'system'),
(4, '198.51.100.0/24', 1, 'TEST-NET-2 Documentation space - RFC 5737', NULL, '2025-01-03 16:40:15', 'system'),
(5, '203.0.113.0/24', 1, 'TEST-NET-3 Documentation space - RFC 5737', NULL, '2025-01-03 16:40:15', 'system');

-- --------------------------------------------------------
CREATE TABLE `ip_whitelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `is_network` tinyint(1) DEFAULT 0,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

INSERT INTO `ip_whitelist` (`id`, `ip_address`, `is_network`, `description`, `created_at`, `created_by`) VALUES
(1, '127.0.0.1', 0, 'localhost IPv4', '2025-01-03 16:40:15', 'system'),
(2, '::1', 0, 'localhost IPv6', '2025-01-03 16:40:15', 'system'),
(3, '10.0.0.0/8', 1, 'Private network (Class A)', '2025-01-03 16:40:15', 'system'),
(4, '172.16.0.0/12', 1, 'Private network (Class B)', '2025-01-03 16:40:15', 'system'),
(5, '192.168.0.0/16', 1, 'Private network (Class C)', '2025-01-03 16:40:15', 'system');

--
-- Logs
--

-- --------------------------------------------------------
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `scope` set('user','system') NOT NULL,
  `message` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Jilo
--

-- --------------------------------------------------------
CREATE TABLE `jilo_agent_types` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `description` varchar(255),
    `endpoint` varchar(255),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
INSERT INTO `jilo_agent_types` (`id`, `description`, `endpoint`) VALUES
(1,'jvb','/jvb'),
(2,'jicofo','/jicofo'),
(3,'prosody','/prosody'),
(4,'nginx','/nginx'),
(5,'jibri','/jibri');

-- --------------------------------------------------------
CREATE TABLE `platforms` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `jitsi_url` varchar(255) NOT NULL,
    `jilo_database` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
INSERT INTO `platforms` (`id`, `name`, `jitsi_url`, `jilo_database`) VALUES
(1,'example.com','https://meet.example.com','../../jilo/jilo.db');

-- --------------------------------------------------------
CREATE TABLE `hosts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `address` varchar(255) NOT NULL,
    `platform_id` int(11) NOT NULL,
    `name` varchar(255),
    PRIMARY KEY (`id`),
    CONSTRAINT `hosts_ibfk_1` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------
CREATE TABLE `jilo_agents` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `host_id` int(11) NOT NULL,
    `agent_type_id` int(11) NOT NULL,
    `url` varchar(255) NOT NULL,
    `secret_key` varchar(255),
    `check_period` int(11) DEFAULT 0,
    PRIMARY KEY (`id`),
    CONSTRAINT `jilo_agents_ibfk_1` FOREIGN KEY (`agent_type_id`) REFERENCES `jilo_agent_types` (`id`),
    CONSTRAINT `jilo_agents_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------
CREATE TABLE jilo_agent_checks (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `agent_id` int(11),
    `timestamp` datetime DEFAULT current_timestamp(),
    `status_code` int(11),
    `response_time_ms` int(11),
    `response_content` varchar(255),
    PRIMARY KEY (`id`),
    CONSTRAINT `jilo_agent_checks_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `jilo_agents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;



COMMIT;
