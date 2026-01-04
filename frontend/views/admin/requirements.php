<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /Org-Accreditation-System/frontend/views/auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requirements Management</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script defer src="requirements.js"></script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/admin-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex justify-between">
                <div class="flex flex-col gap-2">
                    <p class="manrope-bold text-4xl">Requirements Management</p>
                    <p class="text-md">Specify and manage accreditation requirements</p>
                </div>
                <div class="flex justify-center items-center">
                    <button onclick="openModal()" class="bg-[#940505] hover:bg-red-800 text-white font-medium py-2 px-4 rounded-lg shadow-sm flex items-center gap-2 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Requirement
                    </button>
                </div>
            </div>
            
            <div class="flex flex-col w-full min-h-60 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="manrope-bold text-xl">Active Requirements</p>
                        <p class="text-sm">All requirements that organizations must fulfill</p>
                    </div>
                    <div class="flex gap-3">
                        <!-- Items per page selector -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Show:</label>
                            <select id="itemsPerPageSelect" onchange="handleItemsPerPageChange()" 
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#940505] focus:border-[#940505] outline-none text-sm">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <!-- Search Box -->
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search requirements..." 
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#940505] focus:border-[#940505] outline-none text-sm"
                                   oninput="handleSearch()">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <!-- Type Filter -->
                        <select id="typeFilter" onchange="handleFilter()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#940505] focus:border-[#940505] outline-none text-sm">
                            <option value="">All Types</option>
                            <option value="Document">Document</option>
                            <option value="Form">Form</option>
                            <option value="Certificate">Certificate</option>
                            <option value="Report">Report</option>
                        </select>
                    </div>
                </div>
                
                <div class="overflow-x-auto bg-white rounded-lg">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Requirement Name</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Type</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Description</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Created By</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Created Date</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requirementsTableBody" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    Loading requirements...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Controls -->
                <div id="paginationControls"></div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Requirement Modal -->
    <div id="requirementModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative bg-white rounded-xl shadow-2xl border border-gray-200 w-full max-w-md mx-4">
            <div class="flex justify-between items-center p-5 border-b border-gray-100">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Add Requirement</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <form id="requirementForm" class="space-y-4">
                    <input type="hidden" id="requirementId" name="requirement_id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Requirement Name</label>
                        <input type="text" id="requirementName" name="requirement_name" required 
                               class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none"
                               placeholder="e.g., Constitution and By-Laws">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select id="requirementType" name="requirement_type" required 
                                class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none">
                            <option value="">Select type...</option>
                            <option value="Document">Document</option>
                            <option value="Financial">Financial</option>
                            <option value="Membership">Membership</option>
                            <option value="Activity">Activity</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea id="requirementDesc" name="description" rows="3" 
                                  class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                  placeholder="Provide additional details..."></textarea>
                    </div>
                    <button type="submit" id="submitBtn" class="w-full bg-[#940505] hover:bg-red-800 text-white font-medium py-2.5 rounded-lg shadow-sm transition-all">
                        Add Requirement
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php include_once '../../components/modal.php'; ?>
</body>

</html>