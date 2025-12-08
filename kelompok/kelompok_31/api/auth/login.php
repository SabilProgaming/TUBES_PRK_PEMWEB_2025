<?php
/**
 * API Login
 * Dikerjakan oleh: Anggota 1 (Ketua)
 * 
 * Handle login request via AJAX
 * - Terima username & password dari POST
 * - Validasi credentials dengan database
 * - Set session jika valid
 * - Return JSON response
 */

session_start();
header("Content-Type: application/json");

// Cek jika sudah login
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'message' => 'Sudah login',
        'role' => $_SESSION['role']
    ]);
    exit();
}

// Cek method request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan'
    ]);
    exit();
}

// Ambil data dari POST
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validasi input
if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username dan password harus diisi'
    ]);
    exit();
}

// Koneksi database
require_once '../../config/database.php';
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal'
    ]);
    exit();
}

// Query user dari database
try {
    $stmt = $database->prepare("SELECT id, username, password, nama, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Login berhasil, set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil',
            'role' => $user['role'],
            'nama' => $user['nama']
        ]);
    } else {
        // Login gagal
        echo json_encode([
            'success' => false,
            'message' => 'Username atau password salah'
        ]);
    }
} catch(PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem'
    ]);
}
?>
