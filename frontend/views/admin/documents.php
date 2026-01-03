<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /Org-Accreditation-System/frontend/views/auth/login.php");
    exit();
}

include_once '../../backend/api/database.php';
include_once '../../backend/classes/document_class.php';

$database = new Database();
$db = $database->getConnection();
$document = new Document($db);
$documents_by_org = $document->getDocumentsGroupedByOrg();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Review</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/admin-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">Document Review</p>
                <p class="text-md">Review and verify documents submitted by organizations</p>
            </div>
            
            <div class="flex flex-col w-full min-h-60 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div>
                    <p class="manrope-bold text-xl">Documents by Organization</p>
                    <p class="text-sm">View submission status grouped by organization</p>
                </div>
                
                <div class="overflow-x-auto bg-white rounded-lg">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Organization Name</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Total Documents</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Verified</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Pending Review</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Returned</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Progress</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($documents_by_org)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        No documents submitted yet
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($documents_by_org as $org): ?>
                                    <?php 
                                    $total = $org['total_documents'] ?? 0;
                                    $verified = $org['verified_count'] ?? 0;
                                    $pending = $org['pending_count'] ?? 0;
                                    $returned = $org['returned_count'] ?? 0;
                                    $progress = $total > 0 ? round(($verified / $total) * 100) : 0;
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            <div class="flex items-center gap-3">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                                <?php echo htmlspecialchars($org['org_name']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                                                <?php echo $total; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                                <?php echo $verified; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                                                <?php echo $pending; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                                                <?php echo $returned; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                                                </div>
                                                <span class="text-xs font-semibold text-gray-600 whitespace-nowrap"><?php echo $progress; ?>%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="review-documents.php?org_id=<?php echo $org['org_id']; ?>" 
                                               class="inline-flex items-center gap-2 bg-[#940505] hover:bg-red-800 text-white text-xs font-medium px-4 py-2 rounded-lg transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Review Documents
                                            </a>
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
</body>

</html>
