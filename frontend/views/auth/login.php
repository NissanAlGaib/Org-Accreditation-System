<?php 
    session_start();
    if (!empty($_SESSION['user'])) {
        header('Location: /Org-Accreditation-System/index.php');
        exit;
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
    <script src="auth.js" defer></script>
</head>

<body>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="flex items-center gap-3 mb-6">
            <img class="size-10" src="/Org-Accreditation-System/frontend/src/imgs/app-logo.png" alt="">
            <p class="manrope-bold text-2xl">Campus<span class="text-[#940505]">Connect</span></p>
        </div>
        <div class="bg-white p-8 rounded shadow-md w-full h-120 max-w-md flex flex-col justify-center">
            <div class="mb-6">
                <div class="items-center flex flex-col">
                    <p class="text-4xl manrope-bold">Welcome Back!</p>
                    <p class="manrope-regular text-sm">Enter your email and password to continue</p>
                </div>
            </div>

            <form id="loginForm" method="POST" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="text" id="email" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <button id="loginButton" type="submit" class="w-full border bg-[#940505] text-white py-2 px-4 rounded-md hover:bg-white hover:text-[#940505] hover:border hover:border-black focus:outline-none focus:ring-2 focus:ring-indigo-500">Login</button>
                </div>
            </form>
            <p id="loginMessage" class="w-full text-center mt-3"></p>
        </div>
    </div>
</body>

</html>