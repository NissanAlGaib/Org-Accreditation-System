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

        if (!empty($data->document_id) && !empty($data->status)) {
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
