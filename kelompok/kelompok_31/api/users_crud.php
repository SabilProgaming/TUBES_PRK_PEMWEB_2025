<?php
/**
 * API CRUD Users
 * Endpoint untuk CREATE, READ, DELETE user
 */

session_start();
header('Content-Type: application/json');

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Hanya admin yang dapat mengakses.'
    ]);
    exit();
}

// Koneksi database
require_once '../config/database.php';
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal'
    ]);
    exit();
}

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    switch ($action) {
        case 'create':
            createUser($pdo);
            break;
        
        case 'delete':
            deleteUser($pdo);
            break;
        
        case 'getAll':
            getAllUsers($pdo);
            break;
        
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Action tidak valid'
            ]);
    }
} catch (Exception $e) {
    error_log("Users CRUD Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem'
    ]);
}

function createUser($pdo) {
    // Validasi input
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    if (empty($username) || empty($password) || empty($nama) || empty($role)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Semua field wajib diisi'
        ]);
        exit();
    }

    // Validasi role
    if (!in_array($role, ['admin', 'dosen', 'mahasiswa'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Role tidak valid'
        ]);
        exit();
    }

    // Validasi password minimal 6 karakter
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password minimal 6 karakter'
        ]);
        exit();
    }

    // Cek apakah username sudah ada
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username sudah digunakan'
        ]);
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user baru
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $hashed_password, $nama, $role]);

        echo json_encode([
            'success' => true,
            'message' => 'User berhasil ditambahkan'
        ]);
    } catch (PDOException $e) {
        error_log("Create User Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambahkan user: ' . $e->getMessage()
        ]);
    }
}

function deleteUser($pdo) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID user tidak valid'
        ]);
        exit();
    }

    // Cek apakah user yang dihapus adalah user yang sedang login
    if ($id == $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Tidak dapat menghapus akun sendiri'
        ]);
        exit();
    }

    // Cek apakah user ada
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'User tidak ditemukan'
        ]);
        exit();
    }

    // Hapus user (akan cascade ke foreign keys jika ada)
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        // Redirect dengan message (untuk non-AJAX request)
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $_SESSION['message'] = 'User "' . $user['username'] . '" berhasil dihapus';
            $_SESSION['message_type'] = 'success';
            header("Location: ../admin/users.php");
            exit();
        }

        echo json_encode([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    } catch (PDOException $e) {
        error_log("Delete User Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus user: ' . $e->getMessage()
        ]);
    }
}

function getAllUsers($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, nama, role, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $users
        ]);
    } catch (PDOException $e) {
        error_log("Get All Users Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mengambil data user'
        ]);
    }
}
?>

