<?php
class Requirement
{
    private $conn;
    public $table = "requirements";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createRequirement($requirement_name, $requirement_type, $description, $created_by)
    {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (requirement_name, requirement_type, description, created_by, created_at) 
                      VALUES (:requirement_name, :requirement_type, :description, :created_by, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':requirement_name', $requirement_name);
            $stmt->bindParam(':requirement_type', $requirement_type);
            $stmt->bindParam(':description', $description);
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

    public function getRequirements()
    {
        try {
            $query = "SELECT r.*, u.first_name, u.last_name
                      FROM " . $this->table . " r
                      LEFT JOIN users u ON r.created_by = u.user_id
                      WHERE r.is_active = 1
                      ORDER BY r.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function updateRequirement($requirement_id, $requirement_name, $requirement_type, $description)
    {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET requirement_name = :requirement_name, 
                          requirement_type = :requirement_type, 
                          description = :description,
                          updated_at = NOW()
                      WHERE requirement_id = :requirement_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':requirement_name', $requirement_name);
            $stmt->bindParam(':requirement_type', $requirement_type);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':requirement_id', $requirement_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function deleteRequirement($requirement_id)
    {
        try {
            $query = "UPDATE " . $this->table . " SET is_active = 0 WHERE requirement_id = :requirement_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':requirement_id', $requirement_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
