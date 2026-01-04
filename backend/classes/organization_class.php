<?php
class Organization
{
    private $conn;
    public $table = "organizations";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createOrganization($org_name, $created_by)
    {
        try {
            $query = "INSERT INTO " . $this->table . " (org_name, created_by, created_at) 
                      VALUES (:org_name, :created_by, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':org_name', $org_name);
            $stmt->bindParam(':created_by', $created_by);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getOrganizations()
    {
        try {
            $query = "SELECT o.*, 
                      u.first_name, u.last_name, u.email, u.temp_password, u.status as user_status,
                      (SELECT COUNT(*) FROM documents WHERE org_id = o.org_id) as total_documents,
                      (SELECT COUNT(*) FROM documents WHERE org_id = o.org_id AND status = 'verified') as verified_documents,
                      (SELECT COUNT(*) FROM documents WHERE org_id = o.org_id AND status = 'pending') as pending_documents,
                      (SELECT COUNT(*) FROM documents WHERE org_id = o.org_id AND status = 'returned') as returned_documents
                      FROM " . $this->table . " o
                      LEFT JOIN users u ON o.president_id = u.user_id
                      ORDER BY o.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getOrganizationById($org_id)
    {
        try {
            $query = "SELECT o.*, u.first_name, u.last_name, u.email 
                      FROM " . $this->table . " o
                      LEFT JOIN users u ON o.president_id = u.user_id
                      WHERE o.org_id = :org_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':org_id', $org_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function updatePresident($org_id, $president_id)
    {
        try {
            $query = "UPDATE " . $this->table . " SET president_id = :president_id WHERE org_id = :org_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':president_id', $president_id);
            $stmt->bindParam(':org_id', $org_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateStatus($org_id, $status)
    {
        try {
            $query = "UPDATE " . $this->table . " SET status = :status WHERE org_id = :org_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':org_id', $org_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateOrganization($org_id, $org_description, $org_logo = null)
    {
        try {
            if ($org_logo !== null) {
                $query = "UPDATE " . $this->table . " 
                          SET org_description = :org_description, org_logo = :org_logo, updated_at = NOW()
                          WHERE org_id = :org_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':org_logo', $org_logo);
            } else {
                $query = "UPDATE " . $this->table . " 
                          SET org_description = :org_description, updated_at = NOW()
                          WHERE org_id = :org_id";
                $stmt = $this->conn->prepare($query);
            }
            
            $stmt->bindParam(':org_description', $org_description);
            $stmt->bindParam(':org_id', $org_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getPreviousPresidents($org_id)
    {
        try {
            // Get all users who are presidents (role_id = 2) of this organization
            // but exclude the current president (based on organizations.president_id)
            $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.status, u.created_at
                      FROM users u
                      WHERE u.org_id = :org_id 
                      AND u.role_id = 2
                      AND u.user_id != (
                          SELECT president_id 
                          FROM " . $this->table . " 
                          WHERE org_id = :org_id
                      )
                      ORDER BY u.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':org_id', $org_id);
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
     * Get organization dashboard using the SQL view
     * @return array Organization dashboard data with compliance metrics
     */
    public function getOrganizationDashboard()
    {
        try {
            $query = "SELECT * FROM vw_organization_dashboard ORDER BY org_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Get active organizations using the SQL view
     * @return array Active organizations with contact info
     */
    public function getActiveOrganizations()
    {
        try {
            $query = "SELECT * FROM vw_active_organizations";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Calculate compliance score for an organization using stored function
     * @param int $org_id Organization ID
     * @param int $academic_year_id Academic Year ID
     * @return float Compliance score (0-100)
     */
    public function getComplianceScore($org_id, $academic_year_id)
    {
        try {
            $query = "SELECT fn_calculate_compliance_score(:org_id, :academic_year_id) AS compliance_score";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':org_id', $org_id, PDO::PARAM_INT);
            $stmt->bindParam(':academic_year_id', $academic_year_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? floatval($result['compliance_score']) : 0.0;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get accreditation status for an organization using stored function
     * @param int $org_id Organization ID
     * @param int $academic_year_id Academic Year ID
     * @return string Accreditation status text
     */
    public function getAccreditationStatus($org_id, $academic_year_id)
    {
        try {
            $query = "SELECT fn_get_accreditation_status(:org_id, :academic_year_id) AS accreditation_status";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':org_id', $org_id, PDO::PARAM_INT);
            $stmt->bindParam(':academic_year_id', $academic_year_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['accreditation_status'] : 'Non-Compliant';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 'Non-Compliant';
        }
    }

    /**
     * Generate accreditation report using stored procedure
     * @param int $org_id Organization ID
     * @param int $academic_year_id Academic Year ID
     * @return array Report data with two result sets
     */
    public function generateAccreditationReport($org_id, $academic_year_id)
    {
        try {
            $query = "CALL sp_generate_accreditation_report(:org_id, :academic_year_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':org_id', $org_id, PDO::PARAM_INT);
            $stmt->bindParam(':academic_year_id', $academic_year_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Get organization summary (first result set)
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Move to next result set (requirements checklist)
            $stmt->nextRowset();
            $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'summary' => $summary,
                'requirements' => $requirements
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [
                'summary' => null,
                'requirements' => []
            ];
        }
    }
}
