<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /Org-Accreditation-System/frontend/views/auth/login.php');
    exit;
}

// Get document ID from URL
$document_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($document_id === 0) {
    echo "Invalid document ID";
    exit;
}

// Fetch document details
include_once '../../../backend/api/database.php';
include_once '../../../backend/classes/document_class.php';

$database = new Database();
$db = $database->getConnection();
$documentObj = new Document($db);

$document = $documentObj->getDocumentById($document_id);

if (!$document) {
    echo "Document not found";
    exit;
}

// Check if user has permission to view (admin or same organization)
$canView = false;
if ($_SESSION['role_id'] == 1) { // Admin
    $canView = true;
} elseif (isset($_SESSION['org_id']) && $_SESSION['org_id'] == $document['org_id']) { // Same organization
    $canView = true;
}

if (!$canView) {
    echo "Unauthorized access";
    exit;
}

$file_path = $_SERVER['DOCUMENT_ROOT'] . $document['file_path'];
$file_extension = strtolower(pathinfo($document['file_name'], PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document - <?php echo htmlspecialchars($document['file_name']); ?></title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
</head>
<body class="bg-[#F1ECEC]">
    <div class="container mx-auto p-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-4">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($document['file_name']); ?></h1>
                    <div class="space-y-1 text-sm text-gray-600">
                        <p><strong>Organization:</strong> <?php echo htmlspecialchars($document['org_name']); ?></p>
                        <p><strong>Requirement:</strong> <?php echo htmlspecialchars($document['requirement_name']); ?> (<?php echo htmlspecialchars($document['requirement_type']); ?>)</p>
                        <p><strong>Submitted:</strong> <?php echo date('F d, Y g:i A', strtotime($document['submitted_at'])); ?></p>
                        <p>
                            <strong>Status:</strong> 
                            <?php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'verified' => 'bg-green-100 text-green-800',
                                'returned' => 'bg-red-100 text-red-800'
                            ];
                            $statusColor = $statusColors[$document['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="<?php echo $statusColor; ?> text-xs font-semibold px-3 py-1 rounded-full">
                                <?php echo ucfirst($document['status']); ?>
                            </span>
                        </p>
                        <?php if ($document['remarks']): ?>
                            <p><strong>Remarks:</strong> <?php echo htmlspecialchars($document['remarks']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo htmlspecialchars($document['file_path']); ?>" 
                       download="<?php echo htmlspecialchars($document['file_name']); ?>"
                       class="bg-[#940505] text-white px-4 py-2 rounded-md hover:bg-white hover:text-[#940505] border border-transparent hover:border-[#940505] transition-colors">
                        Download
                    </a>
                    <button onclick="window.close()" 
                            class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-4">
            <?php if (in_array($file_extension, ['pdf'])): ?>
                <iframe src="<?php echo htmlspecialchars($document['file_path']); ?>" 
                        class="w-full h-screen border-0">
                </iframe>
            <?php elseif (in_array($file_extension, ['jpg', 'jpeg', 'png'])): ?>
                <img src="<?php echo htmlspecialchars($document['file_path']); ?>" 
                     alt="<?php echo htmlspecialchars($document['file_name']); ?>"
                     class="max-w-full h-auto mx-auto">
            <?php else: ?>
                <div class="text-center py-20">
                    <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-600 mb-4">Preview not available for this file type (.<?php echo $file_extension; ?>)</p>
                    <a href="<?php echo htmlspecialchars($document['file_path']); ?>" 
                       download="<?php echo htmlspecialchars($document['file_name']); ?>"
                       class="bg-[#940505] text-white px-6 py-3 rounded-md hover:bg-white hover:text-[#940505] border border-transparent hover:border-[#940505] transition-colors inline-block">
                        Download File
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
