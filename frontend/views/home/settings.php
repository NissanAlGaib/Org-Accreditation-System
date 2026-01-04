<?php
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['role_id'])) {
    header('Location: /Org-Accreditation-System/frontend/views/auth/login.php');
    exit;
}

// Redirect admin to admin settings
if ($_SESSION['role_id'] == 1) {
    header('Location: /Org-Accreditation-System/frontend/views/admin/settings.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CampusConnect</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script>
        const orgId = <?php echo $_SESSION['org_id'] ?? 0; ?>;
        const userId = <?php echo $_SESSION['user_id'] ?? 0; ?>;
    </script>
    <script defer src="settings.js"></script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/user-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">Settings</p>
                <p class="text-md text-gray-600">Manage your account and preferences</p>
            </div>

            <!-- Account Information -->
            <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="manrope-bold text-2xl">Account Information</h2>
                    <button id="editProfileBtn" class="bg-[#940505] text-white px-4 py-2 rounded-lg hover:bg-[#7a0404] transition duration-300">
                        Edit Profile
                    </button>
                </div>
                
                <form id="profileForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-gray-700">First Name</label>
                        <input type="text" id="firstName" value="<?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#940505] bg-gray-50" readonly>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Last Name</label>
                        <input type="text" id="lastName" value="<?php echo htmlspecialchars($_SESSION['last_name'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#940505] bg-gray-50" readonly>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Email</label>
                        <input type="email" id="email" value="" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#940505] bg-gray-50" readonly>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Role</label>
                        <input type="text" value="Organization President" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Organization</label>
                        <input type="text" id="orgName" value="" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                    </div>
                    
                    <div id="editButtons" class="md:col-span-2 hidden">
                        <div class="flex gap-4">
                            <button type="button" id="saveProfileBtn" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-300">
                                Save Changes
                            </button>
                            <button type="button" id="cancelEditBtn" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition duration-300">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                <h2 class="manrope-bold text-2xl mb-6">Change Password</h2>
                
                <form id="passwordForm" class="grid grid-cols-1 gap-6 max-w-2xl">
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Current Password</label>
                        <input type="password" id="currentPassword" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#940505]" placeholder="Enter current password">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-gray-700">New Password</label>
                        <input type="password" id="newPassword" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#940505]" placeholder="Enter new password">
                        <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Confirm New Password</label>
                        <input type="password" id="confirmPassword" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#940505]" placeholder="Confirm new password">
                    </div>
                    
                    <div>
                        <button type="submit" class="bg-[#940505] text-white px-6 py-2 rounded-lg hover:bg-[#7a0404] transition duration-300">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Organization Info -->
            <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                <h2 class="manrope-bold text-2xl mb-6">Organization Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Organization Status</p>
                        <span id="orgStatusBadge" class="inline-block bg-blue-500 text-white text-sm font-semibold px-4 py-2 rounded-full">Loading...</span>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Academic Year</p>
                        <p id="academicYear" class="text-lg font-semibold">Loading...</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Compliance Score</p>
                        <p id="complianceScore" class="text-2xl font-bold text-green-600">-</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Accreditation Status</p>
                        <p id="accreditationStatus" class="text-lg font-semibold">Loading...</p>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                <h2 class="manrope-bold text-2xl mb-6">Notification Preferences</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">Email Notifications</p>
                            <p class="text-sm text-gray-600">Receive email alerts for document reviews</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="emailNotifications" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#940505]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#940505]"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">Deadline Reminders</p>
                            <p class="text-sm text-gray-600">Get reminded about upcoming deadlines</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="deadlineReminders" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#940505]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#940505]"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                <h2 class="manrope-bold text-2xl mb-6">Quick Actions</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="requirements.php" class="flex items-center justify-between p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-300">
                        <div>
                            <p class="font-semibold text-gray-800">View Requirements</p>
                            <p class="text-sm text-gray-600">Check all requirements</p>
                        </div>
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    
                    <a href="my-organization.php" class="flex items-center justify-between p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-300">
                        <div>
                            <p class="font-semibold text-gray-800">My Organization</p>
                            <p class="text-sm text-gray-600">Update organization info</p>
                        </div>
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    
                    <a href="history.php" class="flex items-center justify-between p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-300">
                        <div>
                            <p class="font-semibold text-gray-800">Document History</p>
                            <p class="text-sm text-gray-600">View submission history</p>
                        </div>
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    
                    <a href="dashboard.php" class="flex items-center justify-between p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition duration-300">
                        <div>
                            <p class="font-semibold text-gray-800">Dashboard</p>
                            <p class="text-sm text-gray-600">View progress overview</p>
                        </div>
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php include_once '../../components/modal.php'; ?>
</body>

</html>
