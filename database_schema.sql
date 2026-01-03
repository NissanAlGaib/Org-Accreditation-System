-- Database schema for Organization Accreditation System
-- This file contains the SQL statements to create all necessary tables

-- Users table (base table - must be created first)
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `org_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `temp_password` varchar(50) DEFAULT NULL,
  `must_change_password` tinyint(1) DEFAULT 1,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  KEY `role_id` (`role_id`),
  KEY `org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Organizations table
CREATE TABLE IF NOT EXISTS `organizations` (
  `org_id` int(11) NOT NULL AUTO_INCREMENT,
  `org_name` varchar(255) NOT NULL,
  `org_description` text DEFAULT NULL,
  `org_logo` varchar(500) DEFAULT NULL,
  `president_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','pending','accredited') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`org_id`),
  KEY `president_id` (`president_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Requirements table
CREATE TABLE IF NOT EXISTS `requirements` (
  `requirement_id` int(11) NOT NULL AUTO_INCREMENT,
  `requirement_name` varchar(255) NOT NULL,
  `requirement_type` varchar(100) NOT NULL,
  `description` text,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`requirement_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Academic years table
CREATE TABLE IF NOT EXISTS `academic_years` (
  `academic_year_id` int(11) NOT NULL AUTO_INCREMENT,
  `year_start` year NOT NULL,
  `year_end` year NOT NULL,
  `semester1_start` date DEFAULT NULL,
  `semester1_end` date DEFAULT NULL,
  `semester2_start` date DEFAULT NULL,
  `semester2_end` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`academic_year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Documents table
CREATE TABLE IF NOT EXISTS `documents` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `org_id` int(11) NOT NULL,
  `requirement_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `status` enum('pending','verified','returned') DEFAULT 'pending',
  `remarks` text,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `academic_year_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`document_id`),
  KEY `org_id` (`org_id`),
  KEY `requirement_id` (`requirement_id`),
  KEY `reviewed_by` (`reviewed_by`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `fk_documents_org` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`org_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_documents_requirement` FOREIGN KEY (`requirement_id`) REFERENCES `requirements` (`requirement_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_documents_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`academic_year_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add foreign key constraints to organizations table
ALTER TABLE `organizations`
  ADD CONSTRAINT `fk_organizations_president` FOREIGN KEY (`president_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_organizations_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

-- Add foreign key constraint from users to organizations
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_organization` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`org_id`) ON DELETE SET NULL;

-- Sample academic years data
INSERT INTO `academic_years` (`year_start`, `year_end`, `semester1_start`, `semester1_end`, `semester2_start`, `semester2_end`, `is_active`) VALUES
(2023, 2024, '2023-08-01', '2023-12-15', '2024-01-08', '2024-05-31', 0),
(2024, 2025, '2024-08-01', '2024-12-15', '2025-01-06', '2025-05-31', 0),
(2025, 2026, '2025-08-01', '2025-12-15', '2026-01-05', '2026-05-31', 1);

-- Sample requirements data
INSERT INTO `requirements` (`requirement_name`, `requirement_type`, `description`, `created_by`) VALUES
('Constitution and By-Laws', 'Document', 'Official constitution and by-laws of the organization', 1),
('List of Officers', 'Document', 'Complete list of organization officers with positions', 1),
('Financial Report', 'Financial', 'Financial report for the academic year', 1),
('Activity Proposal', 'Activity', 'Detailed proposal of planned activities', 1),
('Membership List', 'Membership', 'Complete list of active members', 1);
