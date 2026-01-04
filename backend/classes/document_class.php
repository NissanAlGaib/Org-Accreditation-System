<?php
class Document
{
    private $conn;
    public $table = "documents";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getDocumentsByOrganization($org_id)
    {
        try {
            $query = "SELECT d.*, r.requirement_name, r.requirement_type
                      FROM " . $this->table . " d
                      LEFT JOIN requirements r ON d.requirement_id = r.requirement_id
                      WHERE d.org_id = :org_id
                      ORDER BY d.submitted_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':org_id', $org_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getDocumentsGroupedByOrg()
    {
        try {
            $query = "SELECT o.org_id, o.org_name,
                      COUNT(d.document_id) as total_documents,
                      SUM(CASE WHEN d.status = 'verified' THEN 1 ELSE 0 END) as verified_count,
                      SUM(CASE WHEN d.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                      SUM(CASE WHEN d.status = 'returned' THEN 1 ELSE 0 END) as returned_count,
                      (SELECT COUNT(*) FROM requirements WHERE is_active = 1) as total_requirements
                      FROM organizations o
                      LEFT JOIN " . $this->table . " d ON o.org_id = d.org_id
                      GROUP BY o.org_id, o.org_name
                      ORDER BY o.org_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function updateDocumentStatus($document_id, $status, $reviewed_by, $remarks = null)
    {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW(), remarks = :remarks
                      WHERE document_id = :document_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':reviewed_by', $reviewed_by);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->bindParam(':document_id', $document_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function uploadDocument($org_id, $requirement_id, $file_name, $file_path, $academic_year_id)
    {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (org_id, requirement_id, file_name, file_path, academic_year_id, status, submitted_at)
                      VALUES (:org_id, :requirement_id, :file_name, :file_path, :academic_year_id, 'pending', NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':org_id', $org_id);
            $stmt->bindParam(':requirement_id', $requirement_id);
            $stmt->bindParam(':file_name', $file_name);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->bindParam(':academic_year_id', $academic_year_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getDocumentById($document_id)
    {
        try {
            $query = "SELECT d.*, r.requirement_name, r.requirement_type, o.org_name
                      FROM " . $this->table . " d
                      LEFT JOIN requirements r ON d.requirement_id = r.requirement_id
                      LEFT JOIN organizations o ON d.org_id = o.org_id
                      WHERE d.document_id = :document_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':document_id', $document_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function getActiveAcademicYear()
    {
        try {
            $query = "SELECT academic_year_id FROM academic_years WHERE is_active = 1 LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['academic_year_id'] : null;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}
