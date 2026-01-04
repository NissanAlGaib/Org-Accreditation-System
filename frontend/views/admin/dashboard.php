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
    <title>Document</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script>
        // Load dashboard data on page load
        document.addEventListener('DOMContentLoaded', async () => {
            await loadDashboardData();
        });

        async function loadDashboardData() {
            try {
                // Fetch organizations data
                const orgResponse = await fetch('/Org-Accreditation-System/backend/api/organization_api.php');
                const orgResult = await orgResponse.json();
                
                // Fetch requirements data
                const reqResponse = await fetch('/Org-Accreditation-System/backend/api/requirement_api.php');
                const reqResult = await reqResponse.json();
                
                if (orgResult.status === 'success' && reqResult.status === 'success') {
                    const organizations = orgResult.data || [];
                    const requirements = reqResult.data || [];
                    const totalRequirements = requirements.filter(r => r.is_active == 1).length;
                    
                    // Calculate stats
                    let totalOrgs = organizations.length;
                    let fullyAccredited = 0;
                    let needsAttention = 0;
                    let totalPending = 0;
                    
                    organizations.forEach(org => {
                        const verified = parseInt(org.verified_documents) || 0;
                        const pending = parseInt(org.pending_documents) || 0;
                        const returned = parseInt(org.returned_documents) || 0;
                        
                        totalPending += pending;
                        
                        if (verified >= totalRequirements && totalRequirements > 0) {
                            fullyAccredited++;
                        }
                        
                        if (returned > 0) {
                            needsAttention++;
                        }
                    });
                    
                    const completionRate = totalOrgs > 0 ? Math.round((fullyAccredited / totalOrgs) * 100) : 0;
                    
                    // Update dashboard cards
                    document.getElementById('totalOrgs').textContent = totalOrgs;
                    document.getElementById('pendingReqs').textContent = totalPending;
                    document.getElementById('fullyAccredited').textContent = fullyAccredited;
                    document.getElementById('fullyAccreditedRate').textContent = `${completionRate}% completion rate`;
                    document.getElementById('needsAttention').textContent = needsAttention;
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }
    </script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/admin-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                <p class="text-md">Track all school organizations accreditation progress for S.Y. 2025-2026</p> <!--Academic Year should be fetched from db-->
            </div>
            <div class="flex gap-5">
                <div class="flex-1 w-full h-40 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20">
                    <div class="px-7 w-full h-full flex flex-col justify-center gap-3">
                        <p class="dm-sans-semibold text-xl">Total Organizations</p>
                        <div>
                            <p class="dm-sans-bold text-4xl text-green-500" id="totalOrgs">0</p>
                            <p class="text-green-500">Registered organizations</p>
                        </div>
                    </div>
                </div>
                <div class="flex-1 w-full h-40 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20">
                    <div class="px-7 w-full h-full flex flex-col justify-center gap-3">
                        <p class="dm-sans-semibold text-xl">Pending Requirements</p>
                        <div>
                            <p class="dm-sans-bold text-4xl text-orange-500" id="pendingReqs">0</p>
                            <p class="text-orange-500">Documents awaiting review</p>
                        </div>
                    </div>
                </div>
                <div class="flex-1 w-full h-40 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20">
                    <div class="px-7 w-full h-full flex flex-col justify-center gap-3">
                        <p class="dm-sans-semibold text-xl">Fully Accredited</p>
                        <div>
                            <p class="dm-sans-bold text-4xl text-green-500" id="fullyAccredited">0</p>
                            <p class="text-green-500" id="fullyAccreditedRate">0% completion rate</p>
                        </div>
                    </div>
                </div>
                <div class="flex-1 w-full h-40 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20">
                    <div class="px-7 w-full h-full flex flex-col justify-center gap-3">
                        <p class="dm-sans-semibold text-xl">Needs Attention</p>
                        <div>
                            <p class="dm-sans-bold text-4xl text-red-500" id="needsAttention">0</p>
                            <p class="text-red-500">Revisions requested</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-1 w-full h-50 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20">
                <div class="p-7 w-full h-full flex flex-col">
                    <div class="mb-5">
                        <p class="dm-sans-bold text-2xl">Recent Submissions</p>
                        <p class="">Latest document submissions requiring review</p>
                    </div>
                    <div>
                        <div class="border border-gray-400 w-full h-30 rounded-2xl">
                            <div class="p-5 px-8 flex justify-between">
                                <div>
                                    <p class="dm-sans-bold text-xl">Google Developer Groups on Campus Crimsons</p>
                                    <p class="text-md">Financial Report</p>
                                    <p class="text-sm">2 hours ago</p>
                                </div>
                                <div class="flex justify-center items-center">
                                    <button class="bg-[#940505] text-white hover:text-[#940505] px-8 py-2 rounded-lg hover:bg-white border hover:border-black ease-in-out duration-300">Review</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>