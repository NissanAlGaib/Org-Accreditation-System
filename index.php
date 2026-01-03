<?php
session_start();

if (!empty($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? '';

    if ($role === 'admin') {
        header('Location: /Org-Accreditation-System/admin/dashboard.php');
        exit;
    }

    if ($role === 'user') {
        header('Location: /Org-Accreditation-System/user/dashboard.php');
        exit;
    }

    // default logged-in destination
    header('Location: /Org-Accreditation-System/dashboard.php');
    exit;
}

header('Location: /Org-Accreditation-System/frontend/views/auth/login.php');
exit;