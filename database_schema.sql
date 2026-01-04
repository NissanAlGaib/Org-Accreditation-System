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

-- ============================================
-- ADVANCED SQL FEATURES
-- ============================================

-- INDEXES for Performance Optimization
-- These indexes improve query performance on frequently searched columns

-- Index on users table for faster email lookup during login
CREATE INDEX idx_users_email ON users(email);

-- Index on users table for faster filtering by status and role
CREATE INDEX idx_users_status_role ON users(status, role_id);

-- Index on organizations table for faster status filtering
CREATE INDEX idx_organizations_status ON organizations(status);

-- Index on documents table for faster filtering by status and organization
CREATE INDEX idx_documents_status_org ON documents(status, org_id);

-- Index on documents table for faster filtering by academic year
CREATE INDEX idx_documents_academic_year ON documents(academic_year_id);

-- Index on documents table for faster date-based queries
CREATE INDEX idx_documents_submitted_at ON documents(submitted_at);

-- Composite index for common document queries (org + requirement + year)
CREATE INDEX idx_documents_org_req_year ON documents(org_id, requirement_id, academic_year_id);

-- ============================================
-- VIEWS for Commonly Used Data Queries
-- ============================================

-- View: Organization Dashboard Summary
-- This view provides a comprehensive overview of each organization's accreditation status
CREATE OR REPLACE VIEW vw_organization_dashboard AS
SELECT 
    o.org_id,
    o.org_name,
    o.status AS org_status,
    o.created_at AS org_created_at,
    CONCAT(u.first_name, ' ', u.last_name) AS president_name,
    u.email AS president_email,
    COUNT(DISTINCT d.document_id) AS total_documents,
    SUM(CASE WHEN d.status = 'verified' THEN 1 ELSE 0 END) AS verified_documents,
    SUM(CASE WHEN d.status = 'pending' THEN 1 ELSE 0 END) AS pending_documents,
    SUM(CASE WHEN d.status = 'returned' THEN 1 ELSE 0 END) AS returned_documents,
    ROUND((SUM(CASE WHEN d.status = 'verified' THEN 1 ELSE 0 END) / 
           NULLIF(COUNT(DISTINCT d.document_id), 0)) * 100, 2) AS completion_percentage
FROM organizations o
LEFT JOIN users u ON o.president_id = u.user_id
LEFT JOIN documents d ON o.org_id = d.org_id
GROUP BY o.org_id, o.org_name, o.status, o.created_at, u.first_name, u.last_name, u.email;

-- View: Document Review Queue
-- This view shows all pending documents that need review
CREATE OR REPLACE VIEW vw_document_review_queue AS
SELECT 
    d.document_id,
    d.file_name,
    d.submitted_at,
    o.org_name,
    o.org_id,
    r.requirement_name,
    r.requirement_type,
    CONCAT(ay.year_start, '-', ay.year_end) AS academic_year,
    DATEDIFF(NOW(), d.submitted_at) AS days_pending
FROM documents d
INNER JOIN organizations o ON d.org_id = o.org_id
INNER JOIN requirements r ON d.requirement_id = r.requirement_id
INNER JOIN academic_years ay ON d.academic_year_id = ay.academic_year_id
WHERE d.status = 'pending'
ORDER BY d.submitted_at ASC;

-- View: Active Organizations with Contact Info
-- This view shows all active organizations with their current president details
CREATE OR REPLACE VIEW vw_active_organizations AS
SELECT 
    o.org_id,
    o.org_name,
    o.org_description,
    o.org_logo,
    o.status,
    CONCAT(u.first_name, ' ', u.last_name) AS president_name,
    u.email AS president_email,
    u.user_id AS president_id,
    o.created_at
FROM organizations o
LEFT JOIN users u ON o.president_id = u.user_id
WHERE o.status IN ('active', 'accredited')
ORDER BY o.org_name;

-- ============================================
-- STORED FUNCTIONS
-- ============================================

-- Function: Calculate Organization Compliance Score
-- Returns a compliance score (0-100) based on verified documents
DELIMITER $$
CREATE FUNCTION fn_calculate_compliance_score(p_org_id INT, p_academic_year_id INT)
RETURNS DECIMAL(5,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE total_requirements INT DEFAULT 0;
    DECLARE verified_count INT DEFAULT 0;
    DECLARE compliance_score DECIMAL(5,2) DEFAULT 0.00;
    
    -- Count total active requirements
    SELECT COUNT(*) INTO total_requirements
    FROM requirements
    WHERE is_active = 1;
    
    -- Count verified documents for the organization in the academic year
    SELECT COUNT(DISTINCT requirement_id) INTO verified_count
    FROM documents
    WHERE org_id = p_org_id 
      AND academic_year_id = p_academic_year_id
      AND status = 'verified';
    
    -- Calculate compliance score
    IF total_requirements > 0 THEN
        SET compliance_score = (verified_count / total_requirements) * 100;
    END IF;
    
    RETURN compliance_score;
END$$
DELIMITER ;

-- Function: Get Organization Accreditation Status
-- Returns a text status based on compliance score
DELIMITER $$
CREATE FUNCTION fn_get_accreditation_status(p_org_id INT, p_academic_year_id INT)
RETURNS VARCHAR(50)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE compliance DECIMAL(5,2);
    DECLARE accred_status VARCHAR(50);
    
    SET compliance = fn_calculate_compliance_score(p_org_id, p_academic_year_id);
    
    IF compliance >= 100 THEN
        SET accred_status = 'Fully Accredited';
    ELSEIF compliance >= 80 THEN
        SET accred_status = 'Conditionally Accredited';
    ELSEIF compliance >= 50 THEN
        SET accred_status = 'Partially Compliant';
    ELSE
        SET accred_status = 'Non-Compliant';
    END IF;
    
    RETURN accred_status;
END$$
DELIMITER ;

-- ============================================
-- STORED PROCEDURES
-- ============================================

-- Procedure: Generate Accreditation Report for an Organization
-- Generates a comprehensive report of an organization's accreditation status
DELIMITER $$
CREATE PROCEDURE sp_generate_accreditation_report(
    IN p_org_id INT,
    IN p_academic_year_id INT
)
BEGIN
    -- Organization summary
    SELECT 
        o.org_id,
        o.org_name,
        o.status,
        CONCAT(u.first_name, ' ', u.last_name) AS president_name,
        fn_calculate_compliance_score(p_org_id, p_academic_year_id) AS compliance_score,
        fn_get_accreditation_status(p_org_id, p_academic_year_id) AS accreditation_status
    FROM organizations o
    LEFT JOIN users u ON o.president_id = u.user_id
    WHERE o.org_id = p_org_id;
    
    -- Requirements checklist
    SELECT 
        r.requirement_id,
        r.requirement_name,
        r.requirement_type,
        d.status AS document_status,
        d.file_name,
        d.submitted_at,
        d.reviewed_at,
        CONCAT(reviewer.first_name, ' ', reviewer.last_name) AS reviewed_by
    FROM requirements r
    LEFT JOIN documents d ON r.requirement_id = d.requirement_id 
        AND d.org_id = p_org_id 
        AND d.academic_year_id = p_academic_year_id
    LEFT JOIN users reviewer ON d.reviewed_by = reviewer.user_id
    WHERE r.is_active = 1
    ORDER BY r.requirement_type, r.requirement_name;
END$$
DELIMITER ;

-- Procedure: Archive Old Academic Year Documents
-- Archives documents from completed academic years
DELIMITER $$
CREATE PROCEDURE sp_archive_academic_year(
    IN p_academic_year_id INT
)
BEGIN
    DECLARE v_year_start YEAR;
    DECLARE v_year_end YEAR;
    
    -- Get academic year details
    SELECT year_start, year_end INTO v_year_start, v_year_end
    FROM academic_years
    WHERE academic_year_id = p_academic_year_id;
    
    -- Deactivate the academic year
    UPDATE academic_years
    SET is_active = 0
    WHERE academic_year_id = p_academic_year_id;
    
    -- Return summary of archived data
    SELECT 
        p_academic_year_id AS archived_year_id,
        CONCAT(v_year_start, '-', v_year_end) AS academic_year,
        COUNT(DISTINCT org_id) AS organizations_count,
        COUNT(document_id) AS total_documents,
        SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) AS verified_documents,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_documents,
        SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) AS returned_documents
    FROM documents
    WHERE academic_year_id = p_academic_year_id;
END$$
DELIMITER ;

-- Procedure: Bulk Update Document Status
-- Updates multiple documents status at once
DELIMITER $$
CREATE PROCEDURE sp_bulk_update_document_status(
    IN p_org_id INT,
    IN p_requirement_id INT,
    IN p_new_status VARCHAR(20),
    IN p_reviewed_by INT,
    IN p_remarks TEXT
)
BEGIN
    DECLARE affected_rows INT DEFAULT 0;
    
    UPDATE documents
    SET 
        status = p_new_status,
        reviewed_by = p_reviewed_by,
        reviewed_at = NOW(),
        remarks = p_remarks
    WHERE org_id = p_org_id
      AND requirement_id = p_requirement_id
      AND status = 'pending';
    
    SET affected_rows = ROW_COUNT();
    
    SELECT affected_rows AS documents_updated;
END$$
DELIMITER ;

-- ============================================
-- TRIGGERS
-- ============================================

-- Trigger: Auto-update organization status based on document verification
-- When all required documents are verified, set organization status to 'accredited'
DELIMITER $$
CREATE TRIGGER trg_update_org_status_after_document_update
AFTER UPDATE ON documents
FOR EACH ROW
BEGIN
    DECLARE total_requirements INT DEFAULT 0;
    DECLARE verified_count INT DEFAULT 0;
    
    -- Only process if status changed to verified
    IF NEW.status = 'verified' AND OLD.status != 'verified' THEN
        -- Count total active requirements
        SELECT COUNT(*) INTO total_requirements
        FROM requirements
        WHERE is_active = 1;
        
        -- Count verified documents for this organization in current academic year
        SELECT COUNT(DISTINCT requirement_id) INTO verified_count
        FROM documents
        WHERE org_id = NEW.org_id 
          AND academic_year_id = NEW.academic_year_id
          AND status = 'verified';
        
        -- If all requirements are met, update organization status to accredited
        IF verified_count >= total_requirements THEN
            UPDATE organizations
            SET status = 'accredited'
            WHERE org_id = NEW.org_id 
              AND status != 'accredited';
        END IF;
    END IF;
END$$
DELIMITER ;

-- Trigger: Log document status changes
-- This would require a separate audit table, but we'll create a simple version
-- First, create the audit table
CREATE TABLE IF NOT EXISTS `document_audit_log` (
    `audit_id` INT(11) NOT NULL AUTO_INCREMENT,
    `document_id` INT(11) NOT NULL,
    `old_status` VARCHAR(20),
    `new_status` VARCHAR(20),
    `changed_by` INT(11),
    `change_timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`audit_id`),
    KEY `idx_document_id` (`document_id`),
    KEY `idx_change_timestamp` (`change_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER $$
CREATE TRIGGER trg_log_document_status_change
AFTER UPDATE ON documents
FOR EACH ROW
BEGIN
    -- Only log if status actually changed
    IF OLD.status != NEW.status THEN
        INSERT INTO document_audit_log (document_id, old_status, new_status, changed_by)
        VALUES (NEW.document_id, OLD.status, NEW.status, NEW.reviewed_by);
    END IF;
END$$
DELIMITER ;

-- Trigger: Prevent deletion of verified documents
DELIMITER $$
CREATE TRIGGER trg_prevent_verified_document_deletion
BEFORE DELETE ON documents
FOR EACH ROW
BEGIN
    IF OLD.status = 'verified' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete verified documents. Please return or archive instead.';
    END IF;
END$$
DELIMITER ;

-- ============================================
-- EXAMPLE QUERIES WITH SUBQUERIES
-- ============================================

-- Example 1: Find organizations with above-average document submission rates
-- This query uses a subquery to calculate the average and compare
/*
SELECT 
    o.org_id,
    o.org_name,
    COUNT(d.document_id) AS total_submissions,
    (SELECT AVG(doc_count) 
     FROM (SELECT COUNT(document_id) AS doc_count 
           FROM documents 
           GROUP BY org_id) AS avg_calc
    ) AS system_average
FROM organizations o
LEFT JOIN documents d ON o.org_id = d.org_id
GROUP BY o.org_id, o.org_name
HAVING COUNT(d.document_id) > (
    SELECT AVG(doc_count) 
    FROM (SELECT COUNT(document_id) AS doc_count 
          FROM documents 
          GROUP BY org_id) AS avg_calc
)
ORDER BY total_submissions DESC;
*/

-- Example 2: Organizations with incomplete requirements
-- Uses subquery to find orgs missing at least one requirement
/*
SELECT 
    o.org_id,
    o.org_name,
    o.status,
    (SELECT COUNT(*) FROM requirements WHERE is_active = 1) AS total_requirements,
    (SELECT COUNT(DISTINCT requirement_id) 
     FROM documents 
     WHERE org_id = o.org_id 
       AND status = 'verified' 
       AND academic_year_id = (SELECT academic_year_id FROM academic_years WHERE is_active = 1)
    ) AS completed_requirements
FROM organizations o
WHERE o.status IN ('active', 'pending')
  AND (SELECT COUNT(DISTINCT requirement_id) 
       FROM documents 
       WHERE org_id = o.org_id 
         AND status = 'verified'
         AND academic_year_id = (SELECT academic_year_id FROM academic_years WHERE is_active = 1)
      ) < (SELECT COUNT(*) FROM requirements WHERE is_active = 1)
ORDER BY completed_requirements DESC;
*/

-- Example 3: Most recent document submission for each organization
-- Uses correlated subquery to get the latest submission
/*
SELECT 
    d.document_id,
    d.org_id,
    o.org_name,
    d.file_name,
    d.submitted_at,
    r.requirement_name
FROM documents d
INNER JOIN organizations o ON d.org_id = o.org_id
INNER JOIN requirements r ON d.requirement_id = r.requirement_id
WHERE d.submitted_at = (
    SELECT MAX(d2.submitted_at)
    FROM documents d2
    WHERE d2.org_id = d.org_id
)
ORDER BY d.submitted_at DESC;
*/

-- Example 4: Organizations that haven't submitted any documents in the current academic year
-- Uses NOT EXISTS subquery
/*
SELECT 
    o.org_id,
    o.org_name,
    o.status,
    CONCAT(u.first_name, ' ', u.last_name) AS president_name
FROM organizations o
LEFT JOIN users u ON o.president_id = u.user_id
WHERE o.status = 'active'
  AND NOT EXISTS (
    SELECT 1 
    FROM documents d 
    WHERE d.org_id = o.org_id 
      AND d.academic_year_id = (SELECT academic_year_id FROM academic_years WHERE is_active = 1)
  )
ORDER BY o.org_name;
*/
