<?php
class User
{
    private $conn;
    public $table = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createUser($first_name, $last_name, $email, $password)
    {
        try {
            $status = 'Pending';
            $query = "INSERT INTO " . $this->table . " (first_name, last_name, email, password, status) 
                          VALUES (:first_name, :last_name, :email, :password, :status)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function editUser($user_id, $first_name, $last_name, $email)
    {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET first_name = :first_name, last_name = :last_name, email = :email 
                      WHERE user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_id', $user_id);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getUsers()
    {
        try {
            $query = "SELECT user_id, role_id, org_id, first_name, last_name, email, status, created_at FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function getUserById($user_id)
    {
        try {
            $query = "SELECT user_id, first_name, last_name, email, status FROM " . $this->table . " WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function deleteUser($user_id)
    {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function login($email, $password)
    {
        try {
            $query = "SELECT user_id, role_id, org_id, first_name, last_name, email, password, status, must_change_password FROM " . $this->table . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']);
                return $user;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
    
    public function changePassword($user_id, $new_password)
    {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $query = "UPDATE " . $this->table . " 
                      SET password = :password, must_change_password = 0, temp_password = NULL 
                      WHERE user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
