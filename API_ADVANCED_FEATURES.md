# Advanced SQL Features API Documentation

This document describes the new API endpoints that utilize the advanced SQL features (views, stored procedures, and functions).

## Organization API Enhancements

### Get Organization Dashboard (Using SQL View)
**Endpoint:** `GET /backend/api/organization_api.php?dashboard=1`

**Description:** Returns comprehensive organization dashboard with compliance metrics using `vw_organization_dashboard` view.

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "org_id": 1,
      "org_name": "Student Council",
      "org_status": "active",
      "org_created_at": "2025-01-01 10:00:00",
      "president_name": "John Doe",
      "president_email": "john@example.com",
      "total_documents": 10,
      "verified_documents": 8,
      "pending_documents": 2,
      "returned_documents": 0,
      "completion_percentage": 80.00
    }
  ]
}
```

---

### Get Active Organizations (Using SQL View)
**Endpoint:** `GET /backend/api/organization_api.php?active_orgs=1`

**Description:** Returns all active organizations with president details using `vw_active_organizations` view.

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "org_id": 1,
      "org_name": "Student Council",
      "org_description": "Main student governing body",
      "org_logo": "/path/to/logo.png",
      "status": "active",
      "president_name": "John Doe",
      "president_email": "john@example.com",
      "president_id": 5,
      "created_at": "2025-01-01 10:00:00"
    }
  ]
}
```

---

### Get Compliance Score (Using Stored Function)
**Endpoint:** `GET /backend/api/organization_api.php?compliance_score=1&org_id=1&academic_year_id=3`

**Description:** Calculates organization compliance score (0-100) using `fn_calculate_compliance_score` function.

**Response:**
```json
{
  "status": "success",
  "compliance_score": 85.50
}
```

---

### Get Accreditation Status (Using Stored Function)
**Endpoint:** `GET /backend/api/organization_api.php?accreditation_status=1&org_id=1&academic_year_id=3`

**Description:** Returns accreditation status text using `fn_get_accreditation_status` function.

**Response:**
```json
{
  "status": "success",
  "accreditation_status": "Conditionally Accredited"
}
```

**Status Levels:**
- "Fully Accredited" (100%)
- "Conditionally Accredited" (80-99%)
- "Partially Compliant" (50-79%)
- "Non-Compliant" (<50%)

---

### Generate Accreditation Report (Using Stored Procedure)
**Endpoint:** `GET /backend/api/organization_api.php?accreditation_report=1&org_id=1&academic_year_id=3`

**Description:** Generates comprehensive accreditation report using `sp_generate_accreditation_report` procedure.

**Response:**
```json
{
  "status": "success",
  "data": {
    "summary": {
      "org_id": 1,
      "org_name": "Student Council",
      "status": "active",
      "president_name": "John Doe",
      "compliance_score": 85.50,
      "accreditation_status": "Conditionally Accredited"
    },
    "requirements": [
      {
        "requirement_id": 1,
        "requirement_name": "Constitution and By-Laws",
        "requirement_type": "Document",
        "document_status": "verified",
        "file_name": "constitution.pdf",
        "submitted_at": "2025-01-10 14:30:00",
        "reviewed_at": "2025-01-11 09:15:00",
        "reviewed_by": "Admin User"
      }
    ]
  }
}
```

---

## Document API Enhancements

### Get Document Review Queue (Using SQL View)
**Endpoint:** `GET /backend/api/document_api.php?review_queue=1`

**Description:** Returns all pending documents with details using `vw_document_review_queue` view.

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "document_id": 15,
      "file_name": "financial_report.pdf",
      "submitted_at": "2025-01-10 14:30:00",
      "org_name": "Student Council",
      "org_id": 1,
      "requirement_name": "Financial Report",
      "requirement_type": "Financial",
      "academic_year": "2025-2026",
      "days_pending": 5
    }
  ]
}
```

---

### Get Document Audit Log
**Endpoint:** `GET /backend/api/document_api.php?audit_log=1&document_id=15` (optional document_id)

**Description:** Returns document status change audit log from `document_audit_log` table.

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "audit_id": 1,
      "document_id": 15,
      "old_status": "pending",
      "new_status": "verified",
      "changed_by": 1,
      "change_timestamp": "2025-01-15 10:30:00"
    }
  ]
}
```

---

### Get Deletion Attempts Log
**Endpoint:** `GET /backend/api/document_api.php?deletion_attempts=1`

**Description:** Returns logged deletion attempts of verified documents from `document_deletion_attempts` table.

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "attempt_id": 1,
      "document_id": 10,
      "document_status": "verified",
      "org_id": 1,
      "file_name": "constitution.pdf",
      "attempt_timestamp": "2025-01-15 16:45:00"
    }
  ]
}
```

---

### Bulk Update Document Status (Using Stored Procedure)
**Endpoint:** `PUT /backend/api/document_api.php`

**Description:** Bulk updates all pending documents for a specific organization and requirement using `sp_bulk_update_document_status` procedure.

**Request Body:**
```json
{
  "bulk_update": true,
  "org_id": 1,
  "requirement_id": 2,
  "status": "verified",
  "remarks": "All requirements met"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Bulk update completed",
  "documents_updated": 3
}
```

---

## Academic Year API Enhancements

### Get Active Academic Year
**Endpoint:** `GET /backend/api/academic_year_api.php?active=1`

**Description:** Returns the currently active academic year.

**Response:**
```json
{
  "status": "success",
  "data": {
    "academic_year_id": 3,
    "year_start": 2025,
    "year_end": 2026,
    "semester1_start": "2025-08-01",
    "semester1_end": "2025-12-15",
    "semester2_start": "2026-01-05",
    "semester2_end": "2026-05-31",
    "is_active": 1,
    "created_at": "2024-07-01 10:00:00"
  }
}
```

---

### Archive Academic Year (Using Stored Procedure)
**Endpoint:** `POST /backend/api/academic_year_api.php`

**Description:** Archives an academic year and returns summary using `sp_archive_academic_year` procedure.

**Request Body:**
```json
{
  "action": "archive",
  "academic_year_id": 2
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Academic year archived successfully",
  "data": {
    "archived_year_id": 2,
    "academic_year": "2024-2025",
    "organizations_count": 15,
    "total_documents": 75,
    "verified_documents": 60,
    "pending_documents": 10,
    "returned_documents": 5
  }
}
```

---

## Usage Examples

### Frontend JavaScript Example - Get Organization Dashboard

```javascript
// Fetch organization dashboard data
fetch('/backend/api/organization_api.php?dashboard=1')
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      data.data.forEach(org => {
        console.log(`${org.org_name}: ${org.completion_percentage}% complete`);
      });
    }
  });
```

### Frontend JavaScript Example - Get Compliance Score

```javascript
// Get compliance score for organization
const orgId = 1;
const academicYearId = 3;

fetch(`/backend/api/organization_api.php?compliance_score=1&org_id=${orgId}&academic_year_id=${academicYearId}`)
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      console.log(`Compliance Score: ${data.compliance_score}%`);
    }
  });
```

### Frontend JavaScript Example - Bulk Update Documents

```javascript
// Bulk update documents
const updateData = {
  bulk_update: true,
  org_id: 1,
  requirement_id: 2,
  status: 'verified',
  remarks: 'All requirements met'
};

fetch('/backend/api/document_api.php', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(updateData)
})
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      console.log(`Updated ${data.documents_updated} documents`);
    }
  });
```

---

## Benefits of Using Advanced SQL Features

1. **Performance:** Views are pre-computed and indexed for faster queries
2. **Consistency:** Stored functions ensure uniform calculations across the application
3. **Atomicity:** Stored procedures handle complex multi-step operations safely
4. **Audit Trail:** Triggers automatically log all changes without additional code
5. **Maintainability:** Business logic in database is easier to update centrally

---

## Migration Notes

To use these features, ensure:
1. Database schema is updated with the advanced SQL features
2. MySQL/MariaDB version supports stored procedures and functions
3. Database user has EXECUTE privileges for procedures/functions
4. Views are created before using view-based endpoints

---

## Error Handling

All endpoints return standard error responses:

```json
{
  "status": "error",
  "message": "Error description here"
}
```

Common errors:
- "Invalid JSON" - Malformed request body
- "Incomplete Data" - Missing required parameters
- "Database Connection Failed" - Database unavailable
- Internal errors are logged but return generic messages to users
