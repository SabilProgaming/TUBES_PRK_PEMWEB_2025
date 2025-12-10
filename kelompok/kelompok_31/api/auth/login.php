<?php
session_start();
header("Content-Type: application/json");

// Jika sudah login
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'message' => 'Sudah login',
        'role' => $_SESSION['role']
    ]);
    exit();
}

// Method harus POST
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
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=eduportal;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal'
    ]);
    exit();
}

try {
    // Query ke database
    $stmt = $pdo->prepare("SELECT id, username, password, nama, role FROM users WHERE username = ?");
    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Set session
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
        echo json_encode([
            'success' => false,
            'message' => 'Username atau password salah'
        ]);
    }

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    error_log("Database Error Details: " . $e->getCode());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
    ]);
}

?>