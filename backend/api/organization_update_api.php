<?php
session_start();
header('Content-Type: application/json');

// Check if user is authenticated
if (empty($_SESSION['user_id']) || empty($_SESSION['org_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config/database.php';
require_once '../classes/organization_class.php';

$database = new Database();
$db = $database->getConnection();
$organization = new Organization($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Update organization details
    $org_id = $_SESSION['org_id'];
    $org_description = isset($_POST['org_description']) ? trim($_POST['org_description']) : '';
    
    // Handle logo upload
    $org_logo = null;
    if (isset($_FILES['org_logo']) && $_FILES['org_logo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['org_logo']['type'];
        $file_size = $_FILES['org_logo']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, JPEG, and PNG are allowed.']);
            exit;
        }
        
        if ($file_size > $max_size) {
            echo json_encode(['status' => 'error', 'message' => 'File size exceeds 5MB limit.']);
            exit;
        }
        
        // Create uploads directory if it doesn't exist
        $upload_dir = '../../uploads/logos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['org_logo']['name'], PATHINFO_EXTENSION);
        $unique_name = 'org_' . $org_id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
        $file_path = $upload_dir . $unique_name;
        
        if (move_uploaded_file($_FILES['org_logo']['tmp_name'], $file_path)) {
            $org_logo = '/Org-Accreditation-System/uploads/logos/' . $unique_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload logo.']);
            exit;
        }
    }
    
    // Update organization
    $result = $organization->updateOrganization($org_id, $org_description, $org_logo);
    
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Organization updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update organization.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>