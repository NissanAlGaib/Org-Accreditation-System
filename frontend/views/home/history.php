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
    <script>
        const orgId = <?php echo $_SESSION['org_id']; ?>;
    </script>
    <script defer src="history.js"></script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/user-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">History</p>
                <p class="text-md">View previous presidents and accreditation records</p>
            </div>

            <!-- Previous Presidents -->
            <div class="flex flex-col w-full min-h-60 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div>
                    <p class="manrope-bold text-xl">Previous Presidents</p>
                    <p class="text-sm">Former leaders of your organization</p>
                </div>
                
                <div class="overflow-x-auto bg-white rounded-lg">
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

            <!-- Previous Accreditation Records -->
            <div class="flex flex-col w-full min-h-60 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div>
                    <p class="manrope-bold text-xl">Previous Accreditation Records</p>
                    <p class="text-sm">Historical accreditation data from previous academic years</p>
                </div>
                
                <!-- Academic Year Selector -->
                <div class="flex items-center gap-4">
                    <label for="academicYearSelect" class="font-semibold">Select Academic Year:</label>
                    <select id="academicYearSelect" class="border border-gray-300 rounded-md px-4 py-2" onchange="loadAccreditationRecords()">
                        <option value="">Loading...</option>
                    </select>
                </div>

                <!-- Semester Tabs -->
                <div id="semesterTabs" class="border-b border-gray-200 hidden">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                        <li class="mr-2">
                            <button onclick="switchSemester(1)" id="semester1Tab" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-[#940505] hover:border-[#940505]">
                                Semester 1
                            </button>
                        </li>
                        <li class="mr-2">
                            <button onclick="switchSemester(2)" id="semester2Tab" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-[#940505] hover:border-[#940505]">
                                Semester 2
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Records Table -->
                <div id="recordsContainer" class="overflow-x-auto bg-white rounded-lg">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Requirement</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Type</th>
                                <th scope="col" class="px-6 py-4 font-semibold">File Name</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Submitted</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="recordsTableBody" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    Please select an academic year to view records
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php include_once '../../components/modal.php'; ?>
</body>

</html>
