<?php
/**
 * Logout Handler
 * Dikerjakan oleh: Anggota 1 (Ketua)
 * 
 * Destroy session dan redirect ke login
 */

session_start();

// Unset all session variables
$_SESSION = array();

// Destroy session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
