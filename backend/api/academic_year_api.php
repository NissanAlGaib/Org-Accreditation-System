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
        } else {
            $years = $academicYear->getAcademicYears();
            echo json_encode(["status" => "success", "data" => $years]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
