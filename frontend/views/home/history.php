<?php
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['role_id']) || empty($_SESSION['org_id'])) {
    header('Location: /Org-Accreditation-System/frontend/views/auth/login.php');
    exit;
}

// Redirect admin to admin dashboard
if ($_SESSION['role_id'] == 1) {
    header('Location: /Org-Accreditation-System/frontend/views/admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - CampusConnect</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50">
    <div class="flex h-screen gap-4 p-4">
        <?php include '../../components/user-sidebar.php'; ?>
        
        <div class="w-full h-full flex flex-col gap-5">
            <?php include '../../components/header.php'; ?>
            
            <div class="flex-1 overflow-y-auto px-10 pb-10">
                <div class="mb-8">
                    <h1 class="text-3xl manrope-bold text-gray-800">History</h1>
                    <p class="text-gray-600 mt-2">Previous presidents of your organization</p>
                </div>

                <!-- Previous Presidents -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-xl manrope-bold text-gray-800 mb-6">Previous Presidents</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-semibold">Name</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Email</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Start Date</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody" class="divide-y divide-gray-200">
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        Loading history...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', loadPresidentHistory);

        async function loadPresidentHistory() {
            const tbody = document.getElementById('historyTableBody');

            try {
                const response = await fetch('/Org-Accreditation-System/backend/api/user_api.php', {
                    method: 'GET'
                });

                if (!response.ok) throw new Error('Network error');
                const result = await response.json();

                if (result.status === 'success' && result.data) {
                    // Filter for archived presidents of current organization
                    const archivedPresidents = result.data.filter(user => 
                        user.role_id == 2 && 
                        user.status === 'archived'
                    );

                    if (archivedPresidents.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    No previous presidents found
                                </td>
                            </tr>
                        `;
                    } else {
                        tbody.innerHTML = archivedPresidents.map(president => {
                            const createdDate = president.created_at 
                                ? new Date(president.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
                                : 'N/A';

                            return `
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            ${escapeHtml(president.first_name)} ${escapeHtml(president.last_name)}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            ${escapeHtml(president.email)}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        ${createdDate}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-gray-500 text-white text-xs font-semibold px-3 py-1 rounded-full">
                                            Archived
                                        </span>
                                    </td>
                                </tr>
                            `;
                        }).join('');
                    }
                }
            } catch (error) {
                console.error('Error loading president history:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-red-500">
                            Failed to load history. Please try again.
                        </td>
                    </tr>
                `;
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>
