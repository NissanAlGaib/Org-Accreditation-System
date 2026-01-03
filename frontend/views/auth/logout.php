<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: /Org-Accreditation-System/frontend/views/auth/login.php"); 
exit();
?>