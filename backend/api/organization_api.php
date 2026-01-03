<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'database.php';
include '../classes/organization_class.php';
include '../classes/user_class.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["status" => "error", "message" => "Database Connection Failed"]);
    exit;
}

$organization = new Organization($db);
$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['org_id'])) {
            $org_data = $organization->getOrganizationById($_GET['org_id']);
            if ($org_data) {
                echo json_encode(["status" => "success", "data" => $org_data]);
            } else {
                echo json_encode(["status" => "error", "message" => "Organization Not Found"]);
            }
        } else {
            $organizations = $organization->getOrganizations();
            echo json_encode(["status" => "success", "data" => $organizations]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        $action = isset($data->action) ? strtolower(trim($data->action)) : '';

        if ($action === 'create_new_org_and_president') {
            if (!empty($data->new_org_name) && !empty($data->first_name) && !empty($data->last_name) && !empty($data->email)) {
                
                $temp_password = 'TMP_' . bin2hex(random_bytes(4));
                
                $hashed_password = password_hash($temp_password, PASSWORD_BCRYPT);
                
                $created_by = $_SESSION['user_id'] ?? 1;
                
                $org_id = $organization->createOrganization($data->new_org_name, $created_by);
                
                if ($org_id) {
                    $query = "INSERT INTO users (first_name, last_name, email, password, role_id, org_id, temp_password, status, created_at) 
                              VALUES (:first_name, :last_name, :email, :password, 2, :org_id, :temp_password, 'active', NOW())";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':first_name', $data->first_name);
                    $stmt->bindParam(':last_name', $data->last_name);
                    $stmt->bindParam(':email', $data->email);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':org_id', $org_id);
                    $stmt->bindParam(':temp_password', $temp_password);
                    
                    if ($stmt->execute()) {
                        $user_id = $db->lastInsertId();
                        $organization->updatePresident($org_id, $user_id);
                        
                        echo json_encode([
                            "status" => "success", 
                            "message" => "Organization and President Account Created", 
                            "temp_password" => $temp_password,
                            "org_id" => $org_id,
                            "user_id" => $user_id
                        ]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Failed to create president account"]);
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to create organization"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Incomplete Data"]);
            }
        } elseif ($action === 'create_org_president') {
            if (!empty($data->org_id) && !empty($data->first_name) && !empty($data->last_name) && !empty($data->email)) {
                
                $temp_password = 'TMP_' . bin2hex(random_bytes(4));
                $hashed_password = password_hash($temp_password, PASSWORD_BCRYPT);
                
                $query = "INSERT INTO users (first_name, last_name, email, password, role_id, org_id, temp_password, status, created_at) 
                          VALUES (:first_name, :last_name, :email, :password, 2, :org_id, :temp_password, 'active', NOW())";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':first_name', $data->first_name);
                $stmt->bindParam(':last_name', $data->last_name);
                $stmt->bindParam(':email', $data->email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':org_id', $data->org_id);
                $stmt->bindParam(':temp_password', $temp_password);
                
                if ($stmt->execute()) {
                    $user_id = $db->lastInsertId();
                    $organization->updatePresident($data->org_id, $user_id);
                    
                    echo json_encode([
                        "status" => "success", 
                        "message" => "President Account Created", 
                        "temp_password" => $temp_password,
                        "user_id" => $user_id
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to create president account"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Incomplete Data"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid action"]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        if (!empty($data->org_id) && !empty($data->status)) {
            if ($organization->updateStatus($data->org_id, $data->status)) {
                echo json_encode(["status" => "success", "message" => "Organization Status Updated"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Update Failed"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Incomplete Data"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
