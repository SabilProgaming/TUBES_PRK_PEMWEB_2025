<?php
/**
 * API CRUD Mata Kuliah
 * Dikerjakan oleh: Anggota 2
 * 
 * Endpoint untuk CREATE, READ, UPDATE, DELETE mata kuliah
 */

session_start();

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database configuration (inline - sesuai struktur yang ada)
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=eduportal;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    redirectWithMessage('../admin/mata_kuliah.php', 'Error: Koneksi database gagal', 'error');
}

// Get action dari parameter
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    switch ($action) {
        case 'create':
            createMataKuliah($pdo);
            break;
        
        case 'update':
            updateMataKuliah($pdo);
            break;
        
        case 'delete':
            deleteMataKuliah($pdo);
            break;
        
        case 'get':
            getMataKuliah($pdo);
            break;
        
        case 'getAll':
            getAllMataKuliah($pdo);
            break;
        
        default:
            redirectWithMessage('../admin/mata_kuliah.php', 'Aksi tidak valid', 'error');
    }
} catch (Exception $e) {
    redirectWithMessage('../admin/mata_kuliah.php', 'Error: ' . $e->getMessage(), 'error');
}

/**
 * Fungsi CREATE - Tambah mata kuliah baru
 */
function createMataKuliah($pdo) {
    // Validasi input
    if (empty($_POST['kode']) || empty($_POST['nama']) || empty($_POST['sks'])) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=add', 'Semua field wajib diisi', 'error');
        return;
    }

    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $sks = intval($_POST['sks']);

    // Validasi SKS (1-6)
    if ($sks < 1 || $sks > 6) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=add', 'SKS harus antara 1-6', 'error');
        return;
    }

    try {
        // Check if kode sudah ada
        $stmt = $pdo->prepare("SELECT id FROM mata_kuliah WHERE kode = ?");
        $stmt->execute([$kode]);
        if ($stmt->rowCount() > 0) {
            redirectWithMessage('../admin/mata_kuliah.php?mode=add', 'Kode mata kuliah sudah ada', 'error');
            return;
        }

        // Insert ke database
        $sql = "INSERT INTO mata_kuliah (kode, nama, sks, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kode, $nama, $sks]);
        
        redirectWithMessage('../admin/mata_kuliah.php', 'Mata kuliah berhasil ditambahkan', 'success');
    } catch (PDOException $e) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=add', 'Error: ' . $e->getMessage(), 'error');
    }
}

/**
 * Fungsi UPDATE - Edit mata kuliah
 */
function updateMataKuliah($pdo) {
    // Validasi input
    if (empty($_POST['id']) || empty($_POST['kode']) || empty($_POST['nama']) || 
        empty($_POST['sks'])) {
        redirectWithMessage('../admin/mata_kuliah.php', 'Semua field wajib diisi', 'error');
        return;
    }

    $id = intval($_POST['id']);
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $sks = intval($_POST['sks']);

    // Validasi SKS (1-6)
    if ($sks < 1 || $sks > 6) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=edit&id=' . $id, 'SKS harus antara 1-6', 'error');
        return;
    }

    try {
        // Check if kode sudah dipakai mata kuliah lain
        $stmt = $pdo->prepare("SELECT id FROM mata_kuliah WHERE kode = ? AND id != ?");
        $stmt->execute([$kode, $id]);
        if ($stmt->rowCount() > 0) {
            redirectWithMessage('../admin/mata_kuliah.php?mode=edit&id=' . $id, 'Kode mata kuliah sudah dipakai', 'error');
            return;
        }

        // Update database
        $sql = "UPDATE mata_kuliah 
                SET kode = ?, nama = ?, sks = ?, updated_at = NOW() 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kode, $nama, $sks, $id]);

        redirectWithMessage('../admin/mata_kuliah.php', 'Mata kuliah berhasil diperbarui', 'success');
    } catch (PDOException $e) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=edit&id=' . $id, 'Error: ' . $e->getMessage(), 'error');
    }
}

/**
 * Fungsi DELETE - Hapus mata kuliah
 */
function deleteMataKuliah($pdo) {
    if (empty($_GET['id'])) {
        redirectWithMessage('../admin/mata_kuliah.php', 'ID tidak valid', 'error');
        return;
    }

    $id = intval($_GET['id']);

    try {
        // Check if mata kuliah exists
        $stmt = $pdo->prepare("SELECT id FROM mata_kuliah WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() == 0) {
            redirectWithMessage('../admin/mata_kuliah.php', 'Mata kuliah tidak ditemukan', 'error');
            return;
        }

        // Delete mata kuliah
        $stmt = $pdo->prepare("DELETE FROM mata_kuliah WHERE id = ?");
        $stmt->execute([$id]);

        redirectWithMessage('../admin/mata_kuliah.php', 'Mata kuliah berhasil dihapus', 'success');
    } catch (PDOException $e) {
        redirectWithMessage('../admin/mata_kuliah.php', 'Error: ' . $e->getMessage(), 'error');
    }
}

/**
 * Fungsi GET - Ambil satu mata kuliah berdasarkan ID
 */
function getMataKuliah($pdo) {
    if (empty($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        return;
    }

    $id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE id = ?");
        $stmt->execute([$id]);
        $mk = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($mk) {
            echo json_encode(['success' => true, 'data' => $mk]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mata kuliah tidak ditemukan']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

/**
 * Fungsi GET ALL - Ambil semua mata kuliah
 */
function getAllMataKuliah($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM mata_kuliah ORDER BY kode ASC");
        $stmt->execute();
        $mata_kuliah = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $mata_kuliah, 'count' => count($mata_kuliah)]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

/**
 * Helper function untuk redirect dengan pesan
 */
function redirectWithMessage($location, $message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $location");
    exit();
}
?>

