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
    <title>Organizations - Accreditation Progress</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script defer src="organization.js"></script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/admin-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">Organization Progress</p>
                <p class="text-md">Track accreditation progress and status of all organizations</p>
            </div>
            
            <div class="flex flex-col w-full min-h-60 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="manrope-bold text-xl">All Organizations</p>
                        <p class="text-sm">View detailed progress of each organization</p>
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
                        <!-- Status Filter -->
                        <select id="statusFilter" onchange="handleFilter()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#940505] focus:border-[#940505] outline-none text-sm">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="accredited">Accredited</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="overflow-x-auto bg-white rounded-lg">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Organization Name</th>
                                <th scope="col" class="px-6 py-4 font-semibold">President</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Email</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Total Docs</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Verified</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Pending</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Returned</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Completion Rate</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody id="organizationsTableBody" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                    Loading organizations...
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