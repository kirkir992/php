<?php
// auth_check.php - Include this in pages that require authentication
session_start();

function checkAuth() {
    if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
        header('Location: login.php');
        exit;
    }
    
    // Check session timeout
    if (time() - $_SESSION['login_time'] > LOGIN_TIMEOUT) {
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
    
    // Update login time
    $_SESSION['login_time'] = time();
}

// Call this function at the top of protected pages
checkAuth();
?>
