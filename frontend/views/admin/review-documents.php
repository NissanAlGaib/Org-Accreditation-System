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

$org_id = $_GET['org_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Documents</title>
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
                    <p id="orgNameTitle" class="manrope-bold text-4xl">Organization</p>
                    <p class="text-md">Review submitted documents</p>
                </div>
                <a href="documents.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-all">
                    ← Back to All Organizations
                </a>
            </div>

            <!-- Quick Stats -->
            <div id="quickStats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Stats will be loaded dynamically -->
            </div>
            
            <!-- Search and Filters -->
            <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-5">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search requirements, files, or remarks..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#940505] focus:border-transparent outline-none">
                            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#940505] focus:border-transparent outline-none">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="returned">Returned</option>
                        </select>
                    </div>
                    
                    <!-- Sort -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                        <select id="sortSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#940505] focus:border-transparent outline-none">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="requirement">Requirement A-Z</option>
                            <option value="status">Status</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button id="clearFiltersBtn" class="text-sm text-gray-600 hover:text-[#940505] transition-colors">
                        Clear all filters
                    </button>
                </div>
            </div>
            
            <div class="flex flex-col w-full bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="manrope-bold text-xl">Submitted Documents</p>
                        <p id="resultsCount" class="text-sm text-gray-600"></p>
                    </div>
                    
                    <!-- Pagination Controls Top -->
                    <div class="flex items-center gap-3">
                        <label class="text-sm text-gray-600">Items per page:</label>
                        <select id="perPageSelect" class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#940505] focus:border-transparent outline-none">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">All</option>
                        </select>
                    </div>
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
                        <tbody id="documentsTableBody" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    Loading documents...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Controls Bottom -->
                <div id="paginationContainer" class="flex justify-between items-center mt-4">
                    <div id="paginationInfo" class="text-sm text-gray-600"></div>
                    <div id="paginationButtons" class="flex gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Viewer Modal -->
    <div id="viewerModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative bg-white rounded-xl shadow-2xl border border-gray-200 w-full max-w-6xl mx-4 max-h-[95vh] overflow-hidden">
            <div class="flex justify-between items-center p-5 border-b border-gray-100">
                <h3 id="viewerTitle" class="text-lg font-semibold text-gray-900">Document Viewer</h3>
                <button onclick="closeViewerModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Document Metadata -->
            <div id="viewerMetadata" class="p-5 bg-gray-50 border-b border-gray-200">
                <!-- Metadata will be loaded dynamically -->
            </div>
            
            <!-- Document Content -->
            <div id="viewerContent" class="p-5 overflow-y-auto" style="max-height: calc(95vh - 200px);">
                <div class="flex items-center justify-center h-96">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#940505]"></div>
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
    
    <script>
        const orgId = <?php echo json_encode($org_id); ?>;
    </script>
</body>

</html>
