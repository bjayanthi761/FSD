<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// If already logged in, go to dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

// Otherwise, redirect to login page
header("Location: login.php");
exit();
?>
