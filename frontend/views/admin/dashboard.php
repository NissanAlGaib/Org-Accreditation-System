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
    <script defer src="dashboard.js"></script>
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
            <div class="w-full bg-white rounded-xl border-[0.1px] border-black shadow-xl/20">
                <div class="p-7 w-full flex flex-col">
                    <div class="mb-5">
                        <p class="dm-sans-bold text-2xl">Recent Submissions</p>
                        <p class="">Latest document submissions requiring review</p>
                    </div>
                    <div id="recentSubmissionsContainer" class="overflow-y-auto max-h-96">
                        <div class="p-5 text-center text-gray-400">Loading recent submissions...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../../components/modal.php'; ?>
</body>

</html>