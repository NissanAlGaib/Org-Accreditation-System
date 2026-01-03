<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /Org-Accreditation-System/frontend/views/auth/login.php");
    exit();
}

if (!isset($_GET['org_id'])) {
    header("Location: documents.php");
    exit();
}

include_once '../../backend/api/database.php';
include_once '../../backend/classes/document_class.php';
include_once '../../backend/classes/organization_class.php';

$database = new Database();
$db = $database->getConnection();
$document = new Document($db);
$organization = new Organization($db);

$org_id = $_GET['org_id'];
$org_data = $organization->getOrganizationById($org_id);
$documents = $document->getDocumentsByOrganization($org_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Documents - <?php echo htmlspecialchars($org_data['org_name'] ?? 'Organization'); ?></title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script defer src="review-documents.js"></script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/admin-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5 overflow-y-auto">
            <div class="flex justify-between items-center">
                <div class="flex flex-col gap-2">
                    <p class="manrope-bold text-4xl"><?php echo htmlspecialchars($org_data['org_name'] ?? 'Organization'); ?></p>
                    <p class="text-md">Review submitted documents</p>
                </div>
                <a href="documents.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-all">
                    ← Back to All Organizations
                </a>
            </div>
            
            <div class="flex flex-col w-full bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div>
                    <p class="manrope-bold text-xl">Submitted Documents</p>
                    <p class="text-sm">Review each document and update its status</p>
                </div>
                
                <div class="overflow-x-auto bg-white rounded-lg">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Requirement</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Type</th>
                                <th scope="col" class="px-6 py-4 font-semibold">File Name</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Submitted</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Remarks</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($documents)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        No documents submitted yet
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($documents as $doc): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            <?php echo htmlspecialchars($doc['requirement_name'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">
                                                <?php echo htmlspecialchars($doc['requirement_type'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php echo htmlspecialchars($doc['file_name'] ?? 'document.pdf'); ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            <?php echo isset($doc['submitted_at']) ? date('M d, Y', strtotime($doc['submitted_at'])) : 'N/A'; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                            $status = $doc['status'] ?? 'pending';
                                            $statusColors = [
                                                'verified' => 'bg-green-100 text-green-700',
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'returned' => 'bg-red-100 text-red-700'
                                            ];
                                            $statusColor = $statusColors[$status] ?? 'bg-gray-100 text-gray-700';
                                            ?>
                                            <span class="<?php echo $statusColor; ?> px-3 py-1 rounded-full text-xs font-semibold">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 max-w-xs truncate">
                                            <?php echo htmlspecialchars($doc['remarks'] ?? '-'); ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button onclick="updateStatus(<?php echo $doc['document_id']; ?>, 'verified')" 
                                                        class="text-green-600 hover:text-green-800 transition-colors" 
                                                        title="Verify">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                                <button onclick="openReturnModal(<?php echo $doc['document_id']; ?>)" 
                                                        class="text-red-600 hover:text-red-800 transition-colors" 
                                                        title="Return for revision">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                    </svg>
                                                </button>
                                                <button onclick="viewDocument(<?php echo $doc['document_id']; ?>)" 
                                                        class="text-blue-600 hover:text-blue-800 transition-colors" 
                                                        title="View document">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Modal -->
    <div id="returnModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative bg-white rounded-xl shadow-2xl border border-gray-200 w-full max-w-md mx-4">
            <div class="flex justify-between items-center p-5 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Return Document</h3>
                <button onclick="closeReturnModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <form id="returnForm" class="space-y-4">
                    <input type="hidden" id="documentIdInput" name="document_id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                        <textarea id="remarksInput" name="remarks" rows="4" required 
                                  class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                  placeholder="Provide feedback or reason for returning this document..."></textarea>
                    </div>
                    <button type="submit" class="w-full bg-[#940505] hover:bg-red-800 text-white font-medium py-2.5 rounded-lg shadow-sm transition-all">
                        Return Document
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
