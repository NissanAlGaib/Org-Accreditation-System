<?php
class AcademicYear
{
    private $conn;
    public $table = "academic_years";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAcademicYears()
    {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY year_start DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getArchiveByYear($academic_year_id)
    {
        try {
            $query = "SELECT o.org_id, o.org_name, o.status,
                      COUNT(d.document_id) as total_documents,
                      SUM(CASE WHEN d.status = 'verified' THEN 1 ELSE 0 END) as verified_count,
                      (SELECT COUNT(*) FROM requirements WHERE is_active = 1) as total_requirements
                      FROM organizations o
                      INNER JOIN documents d ON o.org_id = d.org_id AND d.academic_year_id = :academic_year_id
                      GROUP BY o.org_id, o.org_name, o.status
                      ORDER BY o.org_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':academic_year_id', $academic_year_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // ============================================
    // Methods using Advanced SQL Features
    // ============================================

    /**
     * Archive an academic year using stored procedure
     * @param int $academic_year_id Academic Year ID to archive
     * @return array Archive summary
     */
    public function archiveAcademicYear($academic_year_id)
    {
        try {
            $query = "CALL sp_archive_academic_year(:academic_year_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':academic_year_id', $academic_year_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Get the active academic year
     * @return array|null Active academic year data
     */
    public function getActiveAcademicYear()
    {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}
