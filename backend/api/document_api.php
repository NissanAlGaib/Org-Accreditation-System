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
            $document_id = isset($_GET['document_id']) ? intval($_GET['document_id']) : null;
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
                $reviewed_by = $_SESSION['user_id'] ?? 1;
                $remarks = isset($data->remarks) ? $data->remarks : null;
                
                $updated_count = $document->bulkUpdateDocumentStatus(
                    $data->org_id, 
                    $data->requirement_id, 
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
            $reviewed_by = $_SESSION['user_id'] ?? 1;
            $remarks = isset($data->remarks) ? $data->remarks : null;
            
            if ($document->updateDocumentStatus($data->document_id, $data->status, $reviewed_by, $remarks)) {
                echo json_encode(["status" => "success", "message" => "Document Status Updated"]);
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
