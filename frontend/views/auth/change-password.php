<?php 
    session_start();
    if (empty($_SESSION['user_id'])) {
        header('Location: /Org-Accreditation-System/frontend/views/auth/login.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - CampusConnect</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <script defer src="change-password.js"></script>
</head>

<body>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="flex items-center gap-3 mb-6">
            <img class="size-10" src="/Org-Accreditation-System/frontend/src/imgs/app-logo.png" alt="">
            <p class="manrope-bold text-2xl">Campus<span class="text-[#940505]">Connect</span></p>
        </div>
        <div class="bg-white p-8 rounded shadow-md w-full max-w-md flex flex-col justify-center">
            <div class="mb-6">
                <div class="items-center flex flex-col">
                    <p class="text-3xl manrope-bold">Change Password</p>
                    <p class="manrope-regular text-sm text-center mt-2">For security reasons, you must change your temporary password before accessing the dashboard.</p>
                </div>
            </div>

            <form id="changePasswordForm" method="POST" class="space-y-4">
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <button id="changePasswordButton" type="submit" class="w-full border bg-[#940505] text-white py-2 px-4 rounded-md hover:bg-white hover:text-[#940505] hover:border hover:border-black focus:outline-none focus:ring-2 focus:ring-indigo-500">Change Password</button>
                </div>
            </form>
            <p id="changePasswordMessage" class="w-full text-center mt-3"></p>
        </div>
    </div>

</body>

</html>
