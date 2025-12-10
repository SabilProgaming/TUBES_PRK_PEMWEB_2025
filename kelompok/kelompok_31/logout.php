<?php
/**
 * Logout Handler
 * Dikerjakan oleh: Anggota 1 (Ketua)
 *
 * Destroy session dan redirect ke login
 */

// Pastikan session aktif
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Hapus semua variable session
$_SESSION = [];

// Jika ada session cookie, hapus sesuai parameter cookie saat ini
$cookieParams = session_get_cookie_params();
if (isset($_COOKIE[session_name()])) {
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookieParams['path'] ?? '/',
        $cookieParams['domain'] ?? '',
        $cookieParams['secure'] ?? false,
        $cookieParams['httponly'] ?? true
    );
}

// Destroy session data on server
session_unset();
session_destroy();

// Pastikan buat session baru id untuk mencegah reuse
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    session_regenerate_id(true);
}

// Redirect ke halaman login. Gunakan path relatif yang aman berdasarkan lokasi script
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
// jika di root, dirname bisa mengembalikan "\" atau "/"; normalisasi
if ($scriptDir === '/' || $scriptDir === '\\') {
    $scriptDir = '';
}
$loginPath = $scriptDir . '/login.php';

header('Location: ' . $loginPath);
exit();
?>