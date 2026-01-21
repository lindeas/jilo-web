-- Migration: Creates tables that were missing in main.sql and were created by the code
-- This is needed for tests db auto-install
-- Created: 2026-01-21

CREATE TABLE IF NOT EXISTS `settings` (
  `key` VARCHAR(191) NOT NULL PRIMARY KEY,
  `value` TEXT NULL,
  `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `migration` VARCHAR(255) NOT NULL UNIQUE,
  `applied_at` DATETIME NOT NULL,
  `batch` INT NOT NULL,
  `content` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
