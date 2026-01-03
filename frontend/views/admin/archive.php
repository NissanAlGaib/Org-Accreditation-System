<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /Org-Accreditation-System/frontend/views/auth/login.php");
    exit();
}

$selected_year = isset($_GET['year_id']) ? $_GET['year_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accreditation Archives</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script defer src="archive.js"></script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/admin-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">Accreditation Archives</p>
                <p class="text-md">View historical accreditation data by academic year</p>
            </div>
            
            <div class="flex flex-col w-full bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="manrope-bold text-xl">Academic Years</p>
                        <p class="text-sm">Select an academic year to view archived data</p>
                    </div>
                </div>
                
                <div id="academicYearsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="col-span-full text-center py-8 text-gray-500">
                        Loading academic years...
                    </div>
                </div>
            </div>
            
            <div id="archiveDataContainer" class="hidden flex-col w-full bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div>
                    <p class="manrope-bold text-xl">Archived Organizations</p>
                    <p class="text-sm">Accreditation status for selected academic year</p>
                </div>
                
                <div class="overflow-x-auto bg-white rounded-lg">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Organization Name</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Total Documents</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Verified</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Completion %</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Final Status</th>
                            </tr>
                        </thead>
                        <tbody id="archiveTableBody" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    Select an academic year to view data
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        const selectedYear = <?php echo json_encode($selected_year); ?>;
    </script>
</body>

</html>