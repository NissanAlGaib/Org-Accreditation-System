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
                      u.first_name, u.last_name, u.email,
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
}
