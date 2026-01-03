<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /Org-Accreditation-System/frontend/views/auth/login.php");
    exit();
}

include_once '../../backend/api/database.php';
include_once '../../backend/classes/requirement_class.php';

$database = new Database();
$db = $database->getConnection();
$requirement = new Requirement($db);
$requirements = $requirement->getRequirements();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requirements Management</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script defer src="requirements.js"></script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/admin-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex justify-between">
                <div class="flex flex-col gap-2">
                    <p class="manrope-bold text-4xl">Requirements Management</p>
                    <p class="text-md">Specify and manage accreditation requirements</p>
                </div>
                <div class="flex justify-center items-center">
                    <button onclick="openModal()" class="bg-[#940505] hover:bg-red-800 text-white font-medium py-2 px-4 rounded-lg shadow-sm flex items-center gap-2 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Requirement
                    </button>
                </div>
            </div>
            
            <div class="flex flex-col w-full min-h-60 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div>
                    <p class="manrope-bold text-xl">Active Requirements</p>
                    <p class="text-sm">All requirements that organizations must fulfill</p>
                </div>
                
                <div class="overflow-x-auto bg-white rounded-lg">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Requirement Name</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Type</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Description</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Created By</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Created Date</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($requirements)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        No requirements added yet
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($requirements as $req): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            <div class="flex items-center gap-3">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <?php echo htmlspecialchars($req['requirement_name']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-xs font-semibold">
                                                <?php echo htmlspecialchars($req['requirement_type']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 max-w-xs truncate">
                                            <?php echo htmlspecialchars($req['description'] ?? '-'); ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            <?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            <?php echo isset($req['created_at']) ? date('M d, Y', strtotime($req['created_at'])) : 'N/A'; ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <button onclick="editRequirement(<?php echo htmlspecialchars(json_encode($req)); ?>)" 
                                                        class="text-blue-600 hover:text-blue-800 transition-colors" 
                                                        title="Edit">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <button onclick="deleteRequirement(<?php echo $req['requirement_id']; ?>)" 
                                                        class="text-red-500 hover:text-red-700 transition-colors" 
                                                        title="Delete">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Requirement Modal -->
    <div id="requirementModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative bg-white rounded-xl shadow-2xl border border-gray-200 w-full max-w-md mx-4">
            <div class="flex justify-between items-center p-5 border-b border-gray-100">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Add Requirement</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <form id="requirementForm" class="space-y-4">
                    <input type="hidden" id="requirementId" name="requirement_id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Requirement Name</label>
                        <input type="text" id="requirementName" name="requirement_name" required 
                               class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none"
                               placeholder="e.g., Constitution and By-Laws">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select id="requirementType" name="requirement_type" required 
                                class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none">
                            <option value="">Select type...</option>
                            <option value="Document">Document</option>
                            <option value="Financial">Financial</option>
                            <option value="Membership">Membership</option>
                            <option value="Activity">Activity</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea id="requirementDesc" name="description" rows="3" 
                                  class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                  placeholder="Provide additional details..."></textarea>
                    </div>
                    <button type="submit" id="submitBtn" class="w-full bg-[#940505] hover:bg-red-800 text-white font-medium py-2.5 rounded-lg shadow-sm transition-all">
                        Add Requirement
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>