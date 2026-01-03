<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /Org-Accreditation-System/frontend/views/auth/login.php");
    exit();
}

include_once '../../backend/api/database.php';
include_once '../../backend/classes/academic_year_class.php';

$database = new Database();
$db = $database->getConnection();
$academicYear = new AcademicYear($db);
$academic_years = $academicYear->getAcademicYears();

$selected_year = null;
$archive_data = [];
if (isset($_GET['year_id'])) {
    $selected_year = $_GET['year_id'];
    $archive_data = $academicYear->getArchiveByYear($selected_year);
}
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
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if (empty($academic_years)): ?>
                        <div class="col-span-full text-center py-8 text-gray-500">
                            No archived academic years available
                        </div>
                    <?php else: ?>
                        <?php foreach ($academic_years as $year): ?>
                            <?php
                            $isSelected = $selected_year == $year['academic_year_id'];
                            $borderClass = $isSelected ? 'border-[#940505] border-2' : 'border-gray-200';
                            ?>
                            <a href="?year_id=<?php echo $year['academic_year_id']; ?>" 
                               class="block p-6 bg-white border <?php echo $borderClass; ?> rounded-lg shadow-sm hover:shadow-md transition-all">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xl font-bold text-gray-900">
                                        S.Y. <?php echo htmlspecialchars($year['year_start']); ?>-<?php echo htmlspecialchars($year['year_end']); ?>
                                    </h3>
                                    <?php if ($isSelected): ?>
                                        <svg class="w-6 h-6 text-[#940505]" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <p class="mb-1">
                                        <span class="font-semibold">Semester 1:</span> 
                                        <?php echo isset($year['semester1_start']) ? date('M Y', strtotime($year['semester1_start'])) : 'N/A'; ?> - 
                                        <?php echo isset($year['semester1_end']) ? date('M Y', strtotime($year['semester1_end'])) : 'N/A'; ?>
                                    </p>
                                    <p>
                                        <span class="font-semibold">Semester 2:</span> 
                                        <?php echo isset($year['semester2_start']) ? date('M Y', strtotime($year['semester2_start'])) : 'N/A'; ?> - 
                                        <?php echo isset($year['semester2_end']) ? date('M Y', strtotime($year['semester2_end'])) : 'N/A'; ?>
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($selected_year): ?>
                <div class="flex flex-col w-full bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
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
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($archive_data)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            No data available for this academic year
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($archive_data as $org): ?>
                                        <?php
                                        $total = $org['total_documents'] ?? 0;
                                        $verified = $org['verified_count'] ?? 0;
                                        $completion = $total > 0 ? round(($verified / $total) * 100) : 0;
                                        ?>
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-6 py-4 font-medium text-gray-900">
                                                <div class="flex items-center gap-3">
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                    <?php echo htmlspecialchars($org['org_name']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                                                    <?php echo $total; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                                    <?php echo $verified; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                        <div class="bg-green-600 h-2.5 rounded-full" style="width: <?php echo $completion; ?>%"></div>
                                                    </div>
                                                    <span class="text-xs font-semibold text-gray-600 whitespace-nowrap"><?php echo $completion; ?>%</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $status = $org['status'] ?? 'pending';
                                                $statusColors = [
                                                    'accredited' => 'bg-[#0e4b68] text-white',
                                                    'pending' => 'bg-yellow-500 text-white',
                                                    'active' => 'bg-blue-500 text-white',
                                                    'inactive' => 'bg-gray-500 text-white'
                                                ];
                                                $statusColor = $statusColors[$status] ?? 'bg-gray-500 text-white';
                                                ?>
                                                <span class="<?php echo $statusColor; ?> text-xs font-semibold px-3 py-1 rounded-full">
                                                    <?php echo ucfirst($status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>