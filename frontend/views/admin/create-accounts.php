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
    <script defer src="/Org-Accreditation-System/frontend/views/admin/admin.js"></script>
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/admin-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex justify-between">
                <div class="flex flex-col gap-2">
                    <p class="manrope-bold text-4xl">Create Organization Accounts</p>
                    <p class="text-md">Register new student organizations and their presidents</p>
                </div>
                <div class="flex justify-center items-center">
                    <button onclick="openModal()" class="bg-[#940505] hover:bg-red-800 text-white font-medium py-2 px-4 rounded-lg shadow-sm flex items-center gap-2 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Account
                    </button>
                </div>
            </div>
            <div class="flex flex-col w-full min-h-60 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20 p-7 gap-4">
                <div>
                    <p class="manrope-bold text-xl">Registered Organizations</p>
                    <p class="text-sm">All organizations and their president accounts</p>
                </div>
                <div>
                    <div class="overflow-x-auto bg-white  rounded-lg">
                        <table class="w-full text-sm text-left text-gray-600">

                            <thead class="text-xs text-gray-700 uppercase border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-semibold">Organization Name</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">President Name</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Email</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Created Date</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Temp Password</th>
                                    <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                                    <th scope="col" class="px-6 py-4 font-semibold text-center">Actions</th>
                                </tr>
                            </thead>

                            <tbody id="organizationsTableBody" class="divide-y divide-gray-200">
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        Loading organizations...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

</body>
<div id="createAccountModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">

    <div class="relative bg-white rounded-xl shadow-2xl border border-gray-200 w-full max-w-md mx-4">

        <div class="flex justify-between items-center p-5 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Provision New Account</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div id="modalFormState" class="p-6">
            <form id="createAccountForm" class="space-y-4">

                <div class="mb-4">
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-medium text-gray-700">Organization</label>

                        <button type="button" id="toggleOrgModeBtn" class="text-xs text-[#940505] hover:underline font-semibold">
                            Register New Organization?
                        </button>
                    </div>

                    <div id="orgSelectContainer">
                        <select name="org_id" id="orgIdInput" class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                            <option value="">Loading organizations...</option>
                        </select>
                    </div>

                    <div id="orgNewContainer" class="hidden">
                        <input type="text" name="new_org_name" id="newOrgInput" placeholder="Enter full organization name..." class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none">
                        <p class="text-xs text-gray-500 mt-1">This will create a new organization record automatically.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" required class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" required class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" required class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:ring-red-500 focus:border-red-500 outline-none">
                </div>

                <button type="submit" id="submitBtn" class="w-full bg-[#940505] hover:bg-red-800 text-white font-medium py-2.5 rounded-lg shadow-sm transition-all mt-2">
                    Create Account
                </button>
            </form>
        </div>

        <div id="modalSuccessState" class="hidden p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">Account Created!</h3>
            <p class="text-sm text-gray-500 mt-2">Please copy this temporary password and give it to the user.</p>

            <div class="mt-4 bg-gray-50 p-3 rounded-lg border border-gray-200 flex justify-between items-center">
                <code id="tempPasswordDisplay" class="text-lg font-mono text-red-600 font-bold tracking-wider"></code>
                <button onclick="copyPassword()" class="text-gray-400 hover:text-gray-600" title="Copy">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>

            <button onclick="closeModal()" class="mt-6 w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 rounded-lg transition-all">
                Close & Refresh
            </button>
        </div>

    </div>
</div>

</html>