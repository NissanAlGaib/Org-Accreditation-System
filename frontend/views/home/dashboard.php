<?php
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['role_id'])) {
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
    <title>Dashboard - CampusConnect</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/user-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</p>
                <p class="text-md">Here's an overview of your organization's accreditation progress</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Documents -->
                <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Documents</p>
                                <p id="totalDocuments" class="text-3xl manrope-bold text-gray-800 mt-2">-</p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                <!-- Verified Documents -->
                <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Verified</p>
                                <p id="verifiedDocuments" class="text-3xl manrope-bold text-green-600 mt-2">-</p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                <!-- Pending Documents -->
                <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Pending</p>
                                <p id="pendingDocuments" class="text-3xl manrope-bold text-yellow-600 mt-2">-</p>
                            </div>
                            <div class="bg-yellow-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                <!-- Returned Documents -->
                <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Returned</p>
                                <p id="returnedDocuments" class="text-3xl manrope-bold text-red-600 mt-2">-</p>
                            </div>
                            <div class="bg-red-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accreditation Progress -->
            <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                <p class="manrope-bold text-xl mb-4">Accreditation Progress</p>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div id="progressBar" class="bg-green-600 h-4 rounded-full transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>
                        <div>
                            <p id="progressPercentage" class="text-2xl manrope-bold text-gray-800">0%</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Completion rate based on verified vs total required documents</p>
                </div>

            <!-- Organization Status -->
            <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                <p class="manrope-bold text-xl mb-4">Organization Status</p>
                    <div class="flex items-center gap-3">
                        <p class="text-gray-700">Current Status:</p>
                        <span id="orgStatus" class="bg-blue-500 text-white text-sm font-semibold px-4 py-2 rounded-full">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', loadDashboardData);

        async function loadDashboardData() {
            try {
                // Fetch organization data
                const orgResponse = await fetch('/Org-Accreditation-System/backend/api/organization_api.php', {
                    method: 'GET'
                });

                if (!orgResponse.ok) throw new Error('Network error');
                const orgResult = await orgResponse.json();

                // Fetch requirements data to get total count
                const reqResponse = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php', {
                    method: 'GET'
                });

                let totalRequirements = 0;
                if (reqResponse.ok) {
                    const reqResult = await reqResponse.json();
                    if (reqResult.status === 'success' && reqResult.data) {
                        // Count only active requirements
                        totalRequirements = reqResult.data.filter(req => req.is_active == 1).length;
                    }
                }

                if (orgResult.status === 'success' && orgResult.data) {
                    // Find the current user's organization
                    const organizations = orgResult.data;
                    const userOrg = organizations.find(org => org.org_id == <?php echo $_SESSION['org_id'] ?? 0; ?>);

                    if (userOrg) {
                        const total = parseInt(userOrg.total_documents) || 0;
                        const verified = parseInt(userOrg.verified_documents) || 0;
                        const pending = parseInt(userOrg.pending_documents) || 0;
                        const returned = parseInt(userOrg.returned_documents) || 0;
                        
                        // Calculate completion based on verified documents out of total required documents
                        const completion = totalRequirements > 0 ? Math.round((verified / totalRequirements) * 100) : 0;

                        document.getElementById('totalDocuments').textContent = total;
                        document.getElementById('verifiedDocuments').textContent = verified;
                        document.getElementById('pendingDocuments').textContent = pending;
                        document.getElementById('returnedDocuments').textContent = returned;
                        document.getElementById('progressBar').style.width = completion + '%';
                        document.getElementById('progressPercentage').textContent = completion + '%';

                        const status = userOrg.status || 'pending';
                        const statusColors = {
                            'accredited': 'bg-green-500',
                            'active': 'bg-blue-500',
                            'pending': 'bg-yellow-500',
                            'inactive': 'bg-gray-500'
                        };
                        const statusElement = document.getElementById('orgStatus');
                        statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        statusElement.className = `${statusColors[status] || 'bg-blue-500'} text-white text-sm font-semibold px-4 py-2 rounded-full`;
                    }
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }
    </script>
</body>

</html>
