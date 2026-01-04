<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'database.php';
include '../classes/document_class.php';

// Check if user is logged in
if (empty($_SESSION['user_id']) || empty($_SESSION['org_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["status" => "error", "message" => "Database Connection Failed"]);
    exit;
}

$document = new Document($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (!isset($_FILES['file']) || !isset($_POST['requirement_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    $file = $_FILES['file'];
    $requirement_id = $_POST['requirement_id'];
    $org_id = $_SESSION['org_id'];

    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "File upload error"]);
        exit;
    }

    // Validate file size (max 10MB)
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxFileSize) {
        echo json_encode(["status" => "error", "message" => "File size exceeds 10MB limit"]);
        exit;
    }

    // Validate file type
    $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode(["status" => "error", "message" => "Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG"]);
        exit;
    }

    // Get active academic year
    $academic_year_id = $document->getActiveAcademicYear();
    if (!$academic_year_id) {
        echo json_encode(["status" => "error", "message" => "No active academic year found"]);
        exit;
    }

    // Create upload directory if it doesn't exist
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Org-Accreditation-System/uploads/documents/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $timestamp = time();
    $uniqueId = uniqid();
    $newFileName = "doc_{$org_id}_{$requirement_id}_{$timestamp}_{$uniqueId}.{$fileExtension}";
    $uploadPath = $uploadDir . $newFileName;
    $relativePath = '/Org-Accreditation-System/uploads/documents/' . $newFileName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        echo json_encode(["status" => "error", "message" => "Failed to save file"]);
        exit;
    }

    // Save to database
    $originalFileName = $file['name'];
    if ($document->uploadDocument($org_id, $requirement_id, $originalFileName, $relativePath, $academic_year_id)) {
        echo json_encode([
            "status" => "success",
            "message" => "Document uploaded successfully",
            "file_name" => $originalFileName
        ]);
    } else {
        // Delete file if database insert fails
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        echo json_encode(["status" => "error", "message" => "Failed to save document to database"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
