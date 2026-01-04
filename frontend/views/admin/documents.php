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
    <title>Document Review</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script defer src="documents.js"></script>
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
                <div class="flex items-center justify-between">
                    <div>
                        <p class="manrope-bold text-xl">Documents by Organization</p>
                        <p class="text-sm">View submission status grouped by organization</p>
                    </div>
                    <div class="flex gap-3">
                        <!-- Search Box -->
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search organizations..." 
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#940505] focus:border-[#940505] outline-none text-sm"
                                   oninput="handleSearch()">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <!-- Progress Filter -->
                        <select id="progressFilter" onchange="handleFilter()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#940505] focus:border-[#940505] outline-none text-sm">
                            <option value="">All Progress</option>
                            <option value="complete">100% Complete</option>
                            <option value="high">80-99% Complete</option>
                            <option value="medium">40-79% Complete</option>
                            <option value="low">0-39% Complete</option>
                        </select>
                    </div>
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
                        <tbody id="documentsTableBody" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    Loading documents...
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
</body>

</html>
