<?php
// Start session at the top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isProduction = false;

if ($isProduction) {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
}

// Set timezone
date_default_timezone_set('America/Puerto_Rico');

// -----------------------------
// 🔐 Database connection
// -----------------------------
//$db = new mysqli("localhost", "root", "", "ticket_system");
$host = 'localhost';
$database   = 'ticket_system';
$user = 'root';
$pass = '';

$db = new mysqli($host, $user, $pass, $database);


if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}

// -----------------------------
// 🔐 LDAP Configuration
// -----------------------------
$ldap_host     = "ldap://192.168.70.14";
$ldap_domain   = "IslandDialysis.local";
$ldap_base_dn  = "DC=IslandDialysis,DC=local";

// Service account for LDAP search
$service_user = "IslandDialysis\\HelpDesk";
$service_pass = "ahcgAHCG01";

// Group role map
$group_role_map = [
    'EMR_Admin'  => 'Admin',
    'EMR_Editor'   => 'Editor',
    'EMR_Viewer' => 'Viewer'
];


// -------- Role-Based Page Access Control --------

// Define public pages that do not require login
$public_pages = ['index', 'login'];  // Add more if needed
$current_page = basename($_SERVER['PHP_SELF'], ".php");

// If the page is public, allow access
if (in_array($current_page, $public_pages)) {
    return; // Skip the rest of config.php
}

// List of allowed pages per role
$role_permissions = [
    'admin' => ['main', 'login','dashboard','feedback','users','create_ticket', 'view_tickets','ticket_detail'],
    'editor' => ['main', 'login','dashboard', 'feedback','create_ticket', 'view_tickets','ticket_detail'],
    'viewer' => ['main', 'login', 'feedback','create_ticket', 'view_tickets','ticket_detail'],
];

// Get the user's role
$role = strtolower($_SESSION['role'] ?? '');

// If role is not set or not allowed on this page
if (!$role || !in_array($current_page, $role_permissions[$role] ?? [])) {
    session_destroy();
    header("Location: access_denied.php");
    exit;
}
    
?>

