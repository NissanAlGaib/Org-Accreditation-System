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
    <script>
        const orgId = <?php echo $_SESSION['org_id']; ?>;
    </script>
    <script defer src="my-organization.js"></script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/user-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5 overflow-auto">
            <!-- Header Section -->
            <div class="flex justify-between items-center flex-wrap gap-4">
                <div class="flex flex-col gap-2">
                    <p class="manrope-bold text-4xl">My Organization</p>
                    <p class="text-md text-gray-600">Manage your organization's profile and information</p>
                </div>
                <button id="editBtn" class="bg-[#940505] hover:bg-[#7a0404] text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Profile
                </button>
            </div>

            <!-- Organization Profile Card -->
            <div class="bg-white rounded-xl shadow-xl/20 border-[0.1px]">
                <!-- Header with Logo -->
                <div class="relative bg-gradient-to-r from-[#940505] to-[#bb0606] rounded-t-xl p-8">
                    <div class="flex items-center gap-6">
                        <div id="logoContainer" class="bg-white rounded-xl border-4 border-white shadow-xl w-24 h-24 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img id="orgLogoImg" src="" alt="Organization Logo" class="hidden w-full h-full object-cover">
                            <div id="orgLogoPlaceholder" class="bg-gradient-to-br from-[#940505] to-[#7a0404] w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-white flex-1 min-w-0">
                            <h2 id="orgName" class="text-3xl manrope-bold mb-2 truncate">Loading...</h2>
                            <div class="flex items-center gap-3 flex-wrap">
                                <span id="orgStatus" class="inline-block bg-white bg-opacity-20 text-white text-sm font-semibold px-4 py-1.5 rounded-full">-</span>
                                <span class="text-white opacity-75">•</span>
                                <span class="text-white opacity-90">ID: <span id="orgId" class="font-semibold">-</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8">
                    <!-- Description Section -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">About Organization</h3>
                        <p id="orgDescription" class="text-gray-700 leading-relaxed">No description available yet. Click "Edit Profile" to add one.</p>
                    </div>

                    <!-- Information Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div class="p-5 bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-lg border border-blue-200">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="bg-blue-500 p-2 rounded-lg flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-600">President</p>
                            </div>
                            <p id="presidentName" class="text-base font-semibold text-gray-800 truncate">-</p>
                            <p id="presidentEmail" class="text-xs text-gray-600 mt-1 truncate">-</p>
                        </div>

                        <div class="p-5 bg-gradient-to-br from-green-50 to-green-100/50 rounded-lg border border-green-200">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="bg-green-500 p-2 rounded-lg flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-600">Created Date</p>
                            </div>
                            <p id="createdDate" class="text-base font-semibold text-gray-800">-</p>
                        </div>

                        <div class="p-5 bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-lg border border-purple-200">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="bg-purple-500 p-2 rounded-lg flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-600">Last Updated</p>
                            </div>
                            <p id="updatedDate" class="text-base font-semibold text-gray-800">-</p>
                        </div>
                    </div>

                    <!-- Document Statistics -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg manrope-bold text-gray-800 mb-5">Document Statistics</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                                <p class="text-xs text-gray-600 mb-2">Total</p>
                                <p id="totalDocs" class="text-2xl manrope-bold text-gray-800">-</p>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200 hover:shadow-md transition-shadow">
                                <p class="text-xs text-gray-600 mb-2">Verified</p>
                                <p id="verifiedDocs" class="text-2xl manrope-bold text-green-600">-</p>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 rounded-lg border border-yellow-200 hover:shadow-md transition-shadow">
                                <p class="text-xs text-gray-600 mb-2">Pending</p>
                                <p id="pendingDocs" class="text-2xl manrope-bold text-yellow-600">-</p>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200 hover:shadow-md transition-shadow">
                                <p class="text-xs text-gray-600 mb-2">Returned</p>
                                <p id="returnedDocs" class="text-2xl manrope-bold text-red-600">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Organization Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h3 class="text-2xl manrope-bold text-gray-800">Edit Organization Profile</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="editForm" class="p-6" enctype="multipart/form-data">
                <!-- Logo Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Organization Logo</label>
                    <div class="flex items-center gap-6">
                        <div id="previewContainer" class="w-32 h-32 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden bg-gray-50">
                            <img id="logoPreview" src="" alt="Logo Preview" class="hidden w-full h-full object-cover">
                            <div id="logoPlaceholderPreview" class="text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-xs text-gray-500">No image</p>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="file" id="logoInput" name="org_logo" accept="image/jpeg,image/jpg,image/png" class="hidden">
                            <button type="button" id="uploadLogoBtn" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold transition-colors mb-2 block">
                                Choose Image
                            </button>
                            <p class="text-xs text-gray-500">JPG, JPEG, or PNG. Max 5MB.</p>
                            <button type="button" id="removeLogoBtn" class="hidden text-red-600 hover:text-red-700 text-sm mt-2">Remove Logo</button>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="descriptionInput" class="block text-sm font-semibold text-gray-700 mb-3">Organization Description</label>
                    <textarea id="descriptionInput" name="org_description" rows="6" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#940505] focus:border-transparent resize-none"
                              placeholder="Tell us about your organization..."></textarea>
                    <p class="text-xs text-gray-500 mt-2">Share your organization's mission, goals, and activities.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" id="cancelBtn" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="saveBtn" class="bg-[#940505] hover:bg-[#7a0404] text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php include_once '../../components/modal.php'; ?>
</body>

</html>
