<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

include 'database.php';
include '../classes/academic_year_class.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["status" => "error", "message" => "Database Connection Failed"]);
    exit;
}

$academicYear = new AcademicYear($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['academic_year_id'])) {
            $archive = $academicYear->getArchiveByYear($_GET['academic_year_id']);
            echo json_encode(["status" => "success", "data" => $archive]);
        } elseif (isset($_GET['active'])) {
            // Get active academic year
            $active_year = $academicYear->getActiveAcademicYear();
            echo json_encode(["status" => "success", "data" => $active_year]);
        } else {
            $years = $academicYear->getAcademicYears();
            echo json_encode(["status" => "success", "data" => $years]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        if (isset($data->action) && $data->action === 'archive' && !empty($data->academic_year_id)) {
            // Validate academic_year_id
            $academic_year_id = filter_var($data->academic_year_id, FILTER_VALIDATE_INT);
            
            if ($academic_year_id === false || $academic_year_id <= 0) {
                echo json_encode(["status" => "error", "message" => "Invalid academic_year_id"]);
                break;
            }
            
            // Use stored procedure to archive academic year
            $archive_summary = $academicYear->archiveAcademicYear($academic_year_id);
            if ($archive_summary) {
                echo json_encode([
                    "status" => "success", 
                    "message" => "Academic year archived successfully",
                    "data" => $archive_summary
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Archive failed"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid action or missing data"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
