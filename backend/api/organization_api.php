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
        if (isset($_GET['previous_presidents']) && isset($_GET['org_id'])) {
            $previous_presidents = $organization->getPreviousPresidents($_GET['org_id']);
            echo json_encode(["status" => "success", "data" => $previous_presidents]);
        } elseif (isset($_GET['dashboard'])) {
            // Use the new SQL view for organization dashboard
            $dashboard_data = $organization->getOrganizationDashboard();
            echo json_encode(["status" => "success", "data" => $dashboard_data]);
        } elseif (isset($_GET['active_orgs'])) {
            // Use the new SQL view for active organizations
            $active_orgs = $organization->getActiveOrganizations();
            echo json_encode(["status" => "success", "data" => $active_orgs]);
        } elseif (isset($_GET['compliance_score']) && isset($_GET['org_id']) && isset($_GET['academic_year_id'])) {
            // Use the stored function for compliance score
            $org_id = filter_var($_GET['org_id'], FILTER_VALIDATE_INT);
            $academic_year_id = filter_var($_GET['academic_year_id'], FILTER_VALIDATE_INT);
            
            if ($org_id === false || $org_id <= 0 || $academic_year_id === false || $academic_year_id <= 0) {
                echo json_encode(["status" => "error", "message" => "Invalid parameters"]);
                break;
            }
            
            $score = $organization->getComplianceScore($org_id, $academic_year_id);
            echo json_encode(["status" => "success", "compliance_score" => $score]);
        } elseif (isset($_GET['accreditation_status']) && isset($_GET['org_id']) && isset($_GET['academic_year_id'])) {
            // Use the stored function for accreditation status
            $org_id = filter_var($_GET['org_id'], FILTER_VALIDATE_INT);
            $academic_year_id = filter_var($_GET['academic_year_id'], FILTER_VALIDATE_INT);
            
            if ($org_id === false || $org_id <= 0 || $academic_year_id === false || $academic_year_id <= 0) {
                echo json_encode(["status" => "error", "message" => "Invalid parameters"]);
                break;
            }
            
            $status = $organization->getAccreditationStatus($org_id, $academic_year_id);
            echo json_encode(["status" => "success", "accreditation_status" => $status]);
        } elseif (isset($_GET['accreditation_report']) && isset($_GET['org_id']) && isset($_GET['academic_year_id'])) {
            // Use the stored procedure for accreditation report
            $org_id = filter_var($_GET['org_id'], FILTER_VALIDATE_INT);
            $academic_year_id = filter_var($_GET['academic_year_id'], FILTER_VALIDATE_INT);
            
            if ($org_id === false || $org_id <= 0 || $academic_year_id === false || $academic_year_id <= 0) {
                echo json_encode(["status" => "error", "message" => "Invalid parameters"]);
                break;
            }
            
            $report = $organization->generateAccreditationReport($org_id, $academic_year_id);
            echo json_encode(["status" => "success", "data" => $report]);
        } elseif (isset($_GET['org_id'])) {
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
                
                $temp_password = 'TMP_' . bin2hex(random_bytes(8));
                
                $hashed_password = password_hash($temp_password, PASSWORD_BCRYPT);
                
                $created_by = $_SESSION['user_id'] ?? 1;
                
                // Start transaction
                $db->beginTransaction();
                
                try {
                    // Create organization
                    $org_id = $organization->createOrganization($data->new_org_name, $created_by);
                    
                    if (!$org_id) {
                        throw new Exception("Failed to create organization");
                    }
                    
                    // Create president account with must_change_password = 1
                    $query = "INSERT INTO users (first_name, last_name, email, password, role_id, org_id, temp_password, must_change_password, status, created_at) 
                              VALUES (:first_name, :last_name, :email, :password, 2, :org_id, :temp_password, 1, 'active', NOW())";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':first_name', $data->first_name);
                    $stmt->bindParam(':last_name', $data->last_name);
                    $stmt->bindParam(':email', $data->email);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':org_id', $org_id);
                    $stmt->bindParam(':temp_password', $temp_password);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to create president account");
                    }
                    
                    $user_id = $db->lastInsertId();
                    
                    // Update organization with president_id
                    $organization->updatePresident($org_id, $user_id);
                    
                    $db->commit();
                    
                    echo json_encode([
                        "status" => "success", 
                        "message" => "Organization and President Account Created", 
                        "temp_password" => $temp_password,
                        "org_id" => $org_id,
                        "user_id" => $user_id
                    ]);
                } catch (Exception $e) {
                    $db->rollBack();
                    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Incomplete Data"]);
            }
        } elseif ($action === 'create_org_president') {
            if (!empty($data->org_id) && !empty($data->first_name) && !empty($data->last_name) && !empty($data->email)) {
                
                $temp_password = 'TMP_' . bin2hex(random_bytes(8));
                $hashed_password = password_hash($temp_password, PASSWORD_BCRYPT);
                
                // Start transaction
                $db->beginTransaction();
                
                try {
                    // First, archive the current president if exists
                    $archiveQuery = "UPDATE users SET status = 'archived' 
                                    WHERE org_id = :org_id AND role_id = 2 AND status = 'active'";
                    $archiveStmt = $db->prepare($archiveQuery);
                    $archiveStmt->bindParam(':org_id', $data->org_id);
                    $archiveStmt->execute();
                    
                    // Create new president account with must_change_password = 1
                    $query = "INSERT INTO users (first_name, last_name, email, password, role_id, org_id, temp_password, must_change_password, status, created_at) 
                              VALUES (:first_name, :last_name, :email, :password, 2, :org_id, :temp_password, 1, 'active', NOW())";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':first_name', $data->first_name);
                    $stmt->bindParam(':last_name', $data->last_name);
                    $stmt->bindParam(':email', $data->email);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':org_id', $data->org_id);
                    $stmt->bindParam(':temp_password', $temp_password);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to create president account");
                    }
                    
                    $user_id = $db->lastInsertId();
                    
                    // Update organization with new president_id
                    $organization->updatePresident($data->org_id, $user_id);
                    
                    $db->commit();
                    
                    echo json_encode([
                        "status" => "success", 
                        "message" => "President Account Created", 
                        "temp_password" => $temp_password,
                        "user_id" => $user_id
                    ]);
                } catch (Exception $e) {
                    $db->rollBack();
                    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
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
