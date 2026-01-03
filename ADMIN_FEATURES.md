# Organization Accreditation System

## Admin Features Documentation

This document describes the admin functionalities implemented for managing organization accreditation.

### Features Implemented

#### 1. Create Organization President Accounts
**Location:** `/frontend/views/admin/create-accounts.php`

**Features:**
- Create president accounts for existing organizations
- Create new organization and president account simultaneously
- Auto-generate temporary passwords
- Display all registered organizations with their presidents
- View organization details including email, creation date, and status

**API Endpoint:** `/backend/api/organization_api.php`
- POST with `action: create_org_president` - Create president for existing org
- POST with `action: create_new_org_and_president` - Create both org and president

#### 2. Organization Progress Tracking
**Location:** `/frontend/views/admin/organization.php`

**Features:**
- View all registered organizations
- Track document submission progress (total, verified, pending, returned)
- Monitor organization status (accredited, pending, active, inactive)
- Display president information for each organization

**API Endpoint:** `/backend/api/organization_api.php`
- GET - Retrieve all organizations with document statistics

#### 3. Document Review System
**Location:** `/frontend/views/admin/documents.php` and `/frontend/views/admin/review-documents.php`

**Features:**
- View documents grouped by organization
- See submission statistics (verified, pending, returned counts)
- Progress bar showing completion percentage
- Detailed document review interface per organization
- Verify, return, or view individual documents
- Add remarks when returning documents

**API Endpoint:** `/backend/api/document_api.php`
- GET with `grouped=true` - Get documents grouped by organization
- GET with `org_id` - Get all documents for specific organization
- PUT - Update document status with remarks

#### 4. Requirements Management
**Location:** `/frontend/views/admin/requirements.php`

**Features:**
- Add new accreditation requirements
- Edit existing requirements
- Delete (soft delete) requirements
- Specify requirement type (Document, Financial, Membership, Activity, Other)
- Add descriptions to requirements
- Track who created each requirement

**API Endpoint:** `/backend/api/requirement_api.php`
- GET - Retrieve all active requirements
- POST - Create new requirement
- PUT - Update existing requirement
- DELETE - Soft delete requirement (sets is_active to 0)

#### 5. Academic Year Archives
**Location:** `/frontend/views/admin/archive.php`

**Features:**
- View all academic years with semester information
- Select academic year to view archived data
- See organization accreditation status for past years
- View completion percentages and final statuses
- Each academic year shows two semesters

**API Endpoint:** `/backend/api/academic_year_api.php`
- GET - Retrieve all academic years
- GET with `academic_year_id` - Get archive data for specific year

### Database Schema

The following tables are required (see `database_schema.sql`):

1. **organizations** - Stores organization information
2. **users** - Extended to include org_id and temp_password
3. **requirements** - Stores accreditation requirements
4. **academic_years** - Stores academic year and semester information
5. **documents** - Stores submitted documents and their review status

### Installation

1. Import the database schema:
```bash
mysql -u root -p org-accre-system < database_schema.sql
```

2. Ensure proper file permissions for uploaded documents

3. Update database credentials in `/backend/api/database.php` if needed

### Navigation

The admin sidebar has been updated to include:
- Dashboard
- Organizations (progress tracking)
- Documents (review system)
- Create Accounts
- Requirements
- History (archives)
- Settings

### Security Notes

- All pages require authentication (check for `$_SESSION['user_id']`)
- Passwords are hashed using `PASSWORD_BCRYPT`
- Temporary passwords are auto-generated securely using `random_bytes()`
- SQL injection protection via PDO prepared statements
- XSS protection via `htmlspecialchars()` on all output

### File Structure

```
backend/
├── api/
│   ├── academic_year_api.php
│   ├── document_api.php
│   ├── organization_api.php
│   ├── requirement_api.php
│   └── user_api.php
└── classes/
    ├── academic_year_class.php
    ├── document_class.php
    ├── organization_class.php
    ├── requirement_class.php
    └── user_class.php

frontend/
├── components/
│   └── admin-sidebar.php (updated)
└── views/
    └── admin/
        ├── admin.js
        ├── archive.php
        ├── create-accounts.php
        ├── dashboard.php
        ├── documents.php
        ├── organization.php
        ├── requirements.php
        ├── requirements.js
        ├── review-documents.php
        └── review-documents.js
```

### Future Enhancements

- Document file upload functionality
- Document viewer integration
- Email notifications for account creation
- Bulk operations for documents
- Export archive data to PDF/Excel
- Advanced filtering and search
