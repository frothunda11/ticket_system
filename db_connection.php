<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username   = $_SESSION['username'];
$role       = $_SESSION['role'];
$facilities = $_SESSION['facilities'];

$error = "";
$success = "";

// Connect to MySQL
$db = new mysqli("localhost", "root", "", "ticket_system");
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

?>