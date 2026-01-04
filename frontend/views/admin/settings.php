<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: /Org-Accreditation-System/frontend/views/auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script>
        const userId = <?php echo $_SESSION['user_id']; ?>;
    </script>
    <script defer src="settings.js"></script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/admin-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">Settings</p>
                <p class="text-md text-gray-600">Manage your account and system preferences</p>
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
                        <input type="text" value="Administrator" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
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

            <!-- System Settings -->
            <div class="bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-6">
                <h2 class="manrope-bold text-2xl mb-6">System Settings</h2>
                
                <div class="space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <div>
                            <p class="font-semibold text-gray-800">Database Backups</p>
                            <p class="text-sm text-gray-600">Manage system database backups</p>
                        </div>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                            View Backups
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <div>
                            <p class="font-semibold text-gray-800">Audit Logs</p>
                            <p class="text-sm text-gray-600">View document status change logs</p>
                        </div>
                        <button onclick="viewAuditLogs()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                            View Logs
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <div>
                            <p class="font-semibold text-gray-800">Deletion Attempts</p>
                            <p class="text-sm text-gray-600">Security logs for deletion attempts</p>
                        </div>
                        <button onclick="viewDeletionAttempts()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                            View Logs
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">Active Academic Year</p>
                            <p class="text-sm text-gray-600" id="activeYearText">Loading...</p>
                        </div>
                        <button class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg cursor-not-allowed" disabled>
                            Manage Years
                        </button>
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
                            <p class="text-sm text-gray-600">Receive email alerts for new submissions</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="emailNotifications" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#940505]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#940505]"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">Document Review Reminders</p>
                            <p class="text-sm text-gray-600">Get reminded about pending documents</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="reviewReminders" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#940505]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#940505]"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Log Modal -->
    <div id="auditLogModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl manrope-bold">Document Audit Logs</h3>
                <button onclick="closeAuditModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="auditLogContent" class="space-y-2">
                Loading...
            </div>
        </div>
    </div>

    <!-- Deletion Attempts Modal -->
    <div id="deletionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl manrope-bold">Deletion Attempt Logs</h3>
                <button onclick="closeDeletionModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="deletionLogContent" class="space-y-2">
                Loading...
            </div>
        </div>
    </div>

    <?php include_once '../../components/modal.php'; ?>
</body>

</html>