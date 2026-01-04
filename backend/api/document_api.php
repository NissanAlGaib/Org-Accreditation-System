<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'database.php';
include '../classes/document_class.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["status" => "error", "message" => "Database Connection Failed"]);
    exit;
}

$document = new Document($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['org_id'])) {
            $documents = $document->getDocumentsByOrganization($_GET['org_id']);
            echo json_encode(["status" => "success", "data" => $documents]);
        } elseif (isset($_GET['grouped'])) {
            $documents = $document->getDocumentsGroupedByOrg();
            echo json_encode(["status" => "success", "data" => $documents]);
        } elseif (isset($_GET['recent'])) {
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
            $documents = $document->getRecentSubmissions($limit);
            echo json_encode(["status" => "success", "data" => $documents]);
        } elseif (isset($_GET['review_queue'])) {
            // Use the new SQL view for document review queue
            $queue = $document->getDocumentReviewQueue();
            echo json_encode(["status" => "success", "data" => $queue]);
        } elseif (isset($_GET['audit_log'])) {
            // Get document audit log
            $document_id = null;
            if (isset($_GET['document_id'])) {
                $document_id = filter_var($_GET['document_id'], FILTER_VALIDATE_INT);
                if ($document_id === false || $document_id <= 0) {
                    echo json_encode(["status" => "error", "message" => "Invalid document_id"]);
                    break;
                }
            }
            $audit_log = $document->getDocumentAuditLog($document_id);
            echo json_encode(["status" => "success", "data" => $audit_log]);
        } elseif (isset($_GET['deletion_attempts'])) {
            // Get deletion attempt logs
            $deletion_attempts = $document->getDeletionAttempts();
            echo json_encode(["status" => "success", "data" => $deletion_attempts]);
        } else {
            echo json_encode(["status" => "error", "message" => "Missing parameters"]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        if (isset($data->bulk_update) && $data->bulk_update === true) {
            // Use stored procedure for bulk update
            if (!empty($data->org_id) && !empty($data->requirement_id) && !empty($data->status)) {
                // Validate inputs
                $org_id = filter_var($data->org_id, FILTER_VALIDATE_INT);
                $requirement_id = filter_var($data->requirement_id, FILTER_VALIDATE_INT);
                $allowed_statuses = ['pending', 'verified', 'returned'];
                
                if ($org_id === false || $org_id <= 0 || $requirement_id === false || $requirement_id <= 0) {
                    echo json_encode(["status" => "error", "message" => "Invalid org_id or requirement_id"]);
                    break;
                }
                
                if (!in_array($data->status, $allowed_statuses)) {
                    echo json_encode(["status" => "error", "message" => "Invalid status value"]);
                    break;
                }
                
                $reviewed_by = $_SESSION['user_id'] ?? 1;
                $remarks = isset($data->remarks) ? $data->remarks : null;
                
                $updated_count = $document->bulkUpdateDocumentStatus(
                    $org_id, 
                    $requirement_id, 
                    $data->status, 
                    $reviewed_by, 
                    $remarks
                );
                
                echo json_encode([
                    "status" => "success", 
                    "message" => "Bulk update completed",
                    "documents_updated" => $updated_count
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Incomplete Data"]);
            }
        } elseif (!empty($data->document_id) && !empty($data->status)) {
            // Single document update
            // Validate status
            $allowed_statuses = ['pending', 'verified', 'returned'];
            if (!in_array($data->status, $allowed_statuses)) {
                echo json_encode(["status" => "error", "message" => "Invalid status value. Allowed: " . implode(", ", $allowed_statuses)]);
                break;
            }
            
            // Validate document_id
            $document_id = filter_var($data->document_id, FILTER_VALIDATE_INT);
            if ($document_id === false || $document_id <= 0) {
                echo json_encode(["status" => "error", "message" => "Invalid document_id"]);
                break;
            }
            
            $reviewed_by = $_SESSION['user_id'] ?? 1;
            $remarks = isset($data->remarks) ? $data->remarks : null;
            
            error_log("Updating document - ID: $document_id, Status: {$data->status}, Reviewer: $reviewed_by");
            
            $result = $document->updateDocumentStatus($document_id, $data->status, $reviewed_by, $remarks);
            
            if ($result) {
                echo json_encode([
                    "status" => "success", 
                    "message" => "Document Status Updated",
                    "document_id" => $document_id,
                    "new_status" => $data->status
                ]);
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "Update Failed - Please check if document exists and database is accessible"
                ]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Incomplete Data - document_id and status are required"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
