# Advanced SQL Features Documentation

This document describes the advanced SQL features implemented in the database schema for the Organization Accreditation System.

## Table of Contents
1. [Indexes](#indexes)
2. [Views](#views)
3. [Stored Functions](#stored-functions)
4. [Stored Procedures](#stored-procedures)
5. [Triggers](#triggers)
6. [Example Queries with Subqueries](#example-queries-with-subqueries)

---

## Indexes

Indexes have been added to improve query performance on frequently accessed columns:

### Users Table
- **idx_users_status_role**: Fast filtering by status and role
- Note: The email field has a UNIQUE constraint which automatically creates an index

### Organizations Table
- **idx_organizations_status**: Fast filtering by organization status

### Documents Table
- **idx_documents_status_org**: Fast filtering by status and organization
- **idx_documents_academic_year**: Fast filtering by academic year
- **idx_documents_submitted_at**: Fast date-based queries
- **idx_documents_org_req_year**: Composite index for common queries (org + requirement + year)

---

## Views

### vw_organization_dashboard
Provides a comprehensive overview of each organization's accreditation status.

**Columns:**
- org_id, org_name, org_status
- president_name, president_email
- total_documents, verified_documents, pending_documents, returned_documents
- completion_percentage

**Usage:**
```sql
SELECT * FROM vw_organization_dashboard;
SELECT * FROM vw_organization_dashboard WHERE org_id = 1;
```

### vw_document_review_queue
Shows all pending documents that need review.

**Columns:**
- document_id, file_name, submitted_at
- org_name, org_id
- requirement_name, requirement_type
- academic_year, days_pending

**Usage:**
```sql
SELECT * FROM vw_document_review_queue ORDER BY days_pending DESC;
```

### vw_active_organizations
Shows all active organizations with their current president details.

**Columns:**
- org_id, org_name, org_description, org_logo, status
- president_name, president_email, president_id
- created_at

**Usage:**
```sql
SELECT * FROM vw_active_organizations;
```

---

## Stored Functions

### fn_calculate_compliance_score(org_id, academic_year_id)
Calculates an organization's compliance score (0-100) based on verified documents.

**Parameters:**
- `p_org_id` (INT): Organization ID
- `p_academic_year_id` (INT): Academic Year ID

**Returns:** DECIMAL(5,2) - Compliance score percentage

**Usage:**
```sql
SELECT fn_calculate_compliance_score(1, 3) AS compliance_score;
```

### fn_get_accreditation_status(org_id, academic_year_id)
Returns a text status based on the compliance score.

**Parameters:**
- `p_org_id` (INT): Organization ID
- `p_academic_year_id` (INT): Academic Year ID

**Returns:** VARCHAR(50) - Status text
- "Fully Accredited" (100%)
- "Conditionally Accredited" (80-99%)
- "Partially Compliant" (50-79%)
- "Non-Compliant" (<50%)

**Usage:**
```sql
SELECT fn_get_accreditation_status(1, 3) AS accreditation_status;
```

---

## Stored Procedures

### sp_generate_accreditation_report(org_id, academic_year_id)
Generates a comprehensive report of an organization's accreditation status.

**Parameters:**
- `p_org_id` (INT): Organization ID
- `p_academic_year_id` (INT): Academic Year ID

**Output:** Two result sets
1. Organization summary with compliance score
2. Requirements checklist with document status

**Usage:**
```sql
CALL sp_generate_accreditation_report(1, 3);
```

### sp_archive_academic_year(academic_year_id)
Archives documents from completed academic years.

**Parameters:**
- `p_academic_year_id` (INT): Academic Year ID to archive

**Output:** Summary of archived data
- archived_year_id, academic_year
- organizations_count, total_documents
- verified_documents, pending_documents, returned_documents

**Usage:**
```sql
CALL sp_archive_academic_year(1);
```

### sp_bulk_update_document_status(org_id, requirement_id, new_status, reviewed_by, remarks)
Updates multiple pending documents' status at once.

**Parameters:**
- `p_org_id` (INT): Organization ID
- `p_requirement_id` (INT): Requirement ID
- `p_new_status` (VARCHAR(20)): New status value
- `p_reviewed_by` (INT): Reviewer user ID
- `p_remarks` (TEXT): Review remarks

**Output:** Number of documents updated

**Usage:**
```sql
CALL sp_bulk_update_document_status(1, 2, 'verified', 1, 'All requirements met');
```

---

## Triggers

### trg_update_org_status_after_document_update
Automatically updates organization status to 'accredited' when all required documents are verified.

**Type:** AFTER UPDATE on documents
**Action:** Updates organizations.status to 'accredited' when all requirements are met

### trg_log_document_status_change
Logs all document status changes to an audit table for tracking purposes.

**Type:** AFTER UPDATE on documents
**Action:** Inserts a record into document_audit_log when status changes

**Audit Tables:**
```sql
-- View status change history
SELECT * FROM document_audit_log WHERE document_id = 1;

-- View deletion attempts (security monitoring)
SELECT * FROM document_deletion_attempts ORDER BY attempt_timestamp DESC;
```

### trg_prevent_verified_document_deletion
Prevents deletion of verified documents to maintain data integrity and logs all deletion attempts for security monitoring.

**Type:** BEFORE DELETE on documents
**Action:** 
1. Logs deletion attempt to document_deletion_attempts table
2. Raises an error if attempting to delete a verified document

---

## Example Queries with Subqueries

The schema includes several commented example queries demonstrating subquery usage:

### Example 1: Organizations with Above-Average Submission Rates
Finds organizations that have submitted more documents than the system average.

### Example 2: Organizations with Incomplete Requirements
Lists organizations that haven't completed all required documents.

### Example 3: Most Recent Document Submission per Organization
Uses a correlated subquery to find the latest submission for each organization.

### Example 4: Organizations Without Submissions
Identifies active organizations that haven't submitted any documents in the current academic year.

**Note:** These queries are included as comments in the SQL file for reference and can be uncommented for use.

---

## Best Practices

1. **Use Views** for complex queries that are frequently executed
2. **Use Stored Functions** for calculations that are reused across queries
3. **Use Stored Procedures** for complex operations involving multiple steps
4. **Use Triggers** sparingly and only for automated consistency checks
5. **Monitor Index Usage** to ensure they're improving performance
6. **Review Audit Logs** periodically to track document status changes

---

## Migration Notes

When applying this schema to an existing database:
1. Ensure all tables exist before creating indexes
2. Create views after all tables are populated
3. Create functions before procedures that use them
4. Test triggers on a staging environment first
5. Verify all foreign key constraints are in place

---

## Support

For issues or questions about these SQL features, please refer to the main database_schema.sql file or contact the development team.
