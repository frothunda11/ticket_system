<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Timeout after 5 minutes (300 seconds)
$timeout_duration = 3000;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();     // Remove session variables
    session_destroy();   // Destroy session
    header("Location: index.php?timeout=1");
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

// Enforce login
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || !isset($_SESSION['facilities'])) {
    header("Location: index.php");
    exit;
}

// Optional: enforce role-based access
if (isset($allowed_roles) && is_array($allowed_roles)) {
    if (!in_array(strtolower($_SESSION['role']), array_map('strtolower', $allowed_roles))) {
        echo "<h3>Access denied: insufficient permissions.</h3>";
        exit;
    }
}
