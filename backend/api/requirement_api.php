<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'database.php';
include '../classes/requirement_class.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["status" => "error", "message" => "Database Connection Failed"]);
    exit;
}

$requirement = new Requirement($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $requirements = $requirement->getRequirements();
        echo json_encode(["status" => "success", "data" => $requirements]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        if (!empty($data->requirement_name) && !empty($data->requirement_type)) {
            $created_by = $_SESSION['user_id'] ?? 1;
            $description = isset($data->description) ? $data->description : '';
            
            $requirement_id = $requirement->createRequirement(
                $data->requirement_name, 
                $data->requirement_type, 
                $description, 
                $created_by
            );
            
            if ($requirement_id) {
                echo json_encode(["status" => "success", "message" => "Requirement Created", "requirement_id" => $requirement_id]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to create requirement"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Incomplete Data"]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        if (!empty($data->requirement_id) && !empty($data->requirement_name) && !empty($data->requirement_type)) {
            $description = isset($data->description) ? $data->description : '';
            
            if ($requirement->updateRequirement($data->requirement_id, $data->requirement_name, $data->requirement_type, $description)) {
                echo json_encode(["status" => "success", "message" => "Requirement Updated"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Update Failed"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Incomplete Data"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['requirement_id'])) {
            if ($requirement->deleteRequirement($_GET['requirement_id'])) {
                echo json_encode(["status" => "success", "message" => "Requirement Deleted"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Deletion Failed"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Missing requirement_id"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
