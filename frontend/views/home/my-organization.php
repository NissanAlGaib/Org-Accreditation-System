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
    <title>My Organization - CampusConnect</title>
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
                    <h1 class="text-3xl manrope-bold text-gray-800">My Organization</h1>
                    <p class="text-gray-600 mt-2">View your organization's profile and information</p>
                </div>

                <!-- Organization Profile Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 mb-6">
                    <div class="flex items-center gap-6 mb-6 pb-6 border-b border-gray-200">
                        <div class="bg-[#940505] rounded-full p-6">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 id="orgName" class="text-2xl manrope-bold text-gray-800">Loading...</h2>
                            <p class="text-gray-600 mt-1">Student Organization</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Organization ID</p>
                            <p id="orgId" class="text-lg font-semibold text-gray-800">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Status</p>
                            <span id="orgStatus" class="inline-block bg-blue-500 text-white text-sm font-semibold px-4 py-1 rounded-full">-</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-2">President</p>
                            <p id="presidentName" class="text-lg font-semibold text-gray-800">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Email</p>
                            <p id="presidentEmail" class="text-lg font-semibold text-gray-800">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Created Date</p>
                            <p id="createdDate" class="text-lg font-semibold text-gray-800">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Last Updated</p>
                            <p id="updatedDate" class="text-lg font-semibold text-gray-800">-</p>
                        </div>
                    </div>
                </div>

                <!-- Document Statistics -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
                    <h3 class="text-xl manrope-bold text-gray-800 mb-6">Document Statistics</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-2">Total Documents</p>
                            <p id="totalDocs" class="text-3xl manrope-bold text-gray-800">-</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-2">Verified</p>
                            <p id="verifiedDocs" class="text-3xl manrope-bold text-green-600">-</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-2">Pending</p>
                            <p id="pendingDocs" class="text-3xl manrope-bold text-yellow-600">-</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-2">Returned</p>
                            <p id="returnedDocs" class="text-3xl manrope-bold text-red-600">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', loadOrganizationData);

        async function loadOrganizationData() {
            try {
                const response = await fetch('/Org-Accreditation-System/backend/api/organization_api.php', {
                    method: 'GET'
                });

                if (!response.ok) throw new Error('Network error');
                const result = await response.json();

                if (result.status === 'success' && result.data) {
                    const organizations = result.data;
                    const userOrg = organizations.find(org => org.org_id == <?php echo $_SESSION['org_id']; ?>);

                    if (userOrg) {
                        document.getElementById('orgName').textContent = userOrg.org_name || 'N/A';
                        document.getElementById('orgId').textContent = userOrg.org_id || 'N/A';
                        
                        const status = userOrg.status || 'pending';
                        const statusColors = {
                            'accredited': 'bg-green-500',
                            'active': 'bg-blue-500',
                            'pending': 'bg-yellow-500',
                            'inactive': 'bg-gray-500'
                        };
                        const statusElement = document.getElementById('orgStatus');
                        statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        statusElement.className = `inline-block ${statusColors[status] || 'bg-blue-500'} text-white text-sm font-semibold px-4 py-1 rounded-full`;

                        const presidentName = userOrg.first_name && userOrg.last_name 
                            ? `${userOrg.first_name} ${userOrg.last_name}` 
                            : 'Not assigned';
                        document.getElementById('presidentName').textContent = presidentName;
                        document.getElementById('presidentEmail').textContent = userOrg.email || 'N/A';

                        const createdDate = userOrg.created_at 
                            ? new Date(userOrg.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
                            : 'N/A';
                        document.getElementById('createdDate').textContent = createdDate;

                        const updatedDate = userOrg.updated_at 
                            ? new Date(userOrg.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
                            : 'N/A';
                        document.getElementById('updatedDate').textContent = updatedDate;

                        // Document statistics
                        document.getElementById('totalDocs').textContent = userOrg.total_documents || 0;
                        document.getElementById('verifiedDocs').textContent = userOrg.verified_documents || 0;
                        document.getElementById('pendingDocs').textContent = userOrg.pending_documents || 0;
                        document.getElementById('returnedDocs').textContent = userOrg.returned_documents || 0;
                    }
                }
            } catch (error) {
                console.error('Error loading organization data:', error);
            }
        }
    </script>
</body>

</html>
