<?php
/**
 * API CRUD Mata Kuliah (FINAL FIX)
 * - Menyertakan dosen_id (mengambil dr users.role='dosen')
 * - CREATE / UPDATE mem-validasi dosen ada & role='dosen'
 * - getAll mengembalikan nama dosen sebagai dosen_name
 */

session_start();

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database configuration (PDO)
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

// Get action dari parameter (GET atau POST)
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

/* ---------------- CREATE ---------------- */
function createMataKuliah($pdo) {
    // Validasi input (kode, nama, sks, semester, dosen_id)
    if (empty($_POST['kode']) || empty($_POST['nama']) || !isset($_POST['sks']) || !isset($_POST['semester']) || !isset($_POST['dosen_id'])) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=add', 'Semua field wajib diisi', 'error');
        return;
    }

    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $sks = intval($_POST['sks']);
    $semester = intval($_POST['semester']);
    $dosen_id = intval($_POST['dosen_id']);

    // Validasi SKS (1-6)
    if ($sks < 1 || $sks > 6) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=add', 'SKS harus antara 1-6', 'error');
        return;
    }

    // Validasi Semester (1-8)
    if ($semester < 1 || $semester > 8) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=add', 'Semester harus antara 1-8', 'error');
        return;
    }

    try {
        // Cek kode sudah ada
        $stmt = $pdo->prepare("SELECT id FROM mata_kuliah WHERE kode = ?");
        $stmt->execute([$kode]);
        if ($stmt->rowCount() > 0) {
            redirectWithMessage('../admin/mata_kuliah.php?mode=add', 'Kode mata kuliah sudah ada', 'error');
            return;
        }

        // Validasi dosen_id -> harus ada dan role = 'dosen'
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'dosen'");
        $stmt->execute([$dosen_id]);
        if ($stmt->rowCount() == 0) {
            redirectWithMessage('../admin/mata_kuliah.php?mode=add', 'Dosen tidak valid', 'error');
            return;
        }

        // Insert
        $sql = "INSERT INTO mata_kuliah (kode, nama, sks, semester, dosen_id, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kode, $nama, $sks, $semester, $dosen_id]);

        redirectWithMessage('../admin/mata_kuliah.php', 'Mata kuliah berhasil ditambahkan', 'success');
    } catch (PDOException $e) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=add', 'Error: ' . $e->getMessage(), 'error');
    }
}

/* ---------------- UPDATE ---------------- */
function updateMataKuliah($pdo) {
    if (empty($_POST['id']) || empty($_POST['kode']) || empty($_POST['nama']) || !isset($_POST['sks']) || !isset($_POST['semester']) || !isset($_POST['dosen_id'])) {
        redirectWithMessage('../admin/mata_kuliah.php', 'Semua field wajib diisi', 'error');
        return;
    }

    $id = intval($_POST['id']);
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $sks = intval($_POST['sks']);
    $semester = intval($_POST['semester']);
    $dosen_id = intval($_POST['dosen_id']);

    if ($sks < 1 || $sks > 6) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=edit&id=' . $id, 'SKS harus antara 1-6', 'error');
        return;
    }

    // Validasi Semester (1-8)
    if ($semester < 1 || $semester > 8) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=edit&id=' . $id, 'Semester harus antara 1-8', 'error');
        return;
    }

    try {
        // Pastikan mk ada
        $stmt = $pdo->prepare("SELECT id FROM mata_kuliah WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() == 0) {
            redirectWithMessage('../admin/mata_kuliah.php', 'Mata kuliah tidak ditemukan', 'error');
            return;
        }

        // Cek kode tidak dipakai mk lain
        $stmt = $pdo->prepare("SELECT id FROM mata_kuliah WHERE kode = ? AND id != ?");
        $stmt->execute([$kode, $id]);
        if ($stmt->rowCount() > 0) {
            redirectWithMessage('../admin/mata_kuliah.php?mode=edit&id=' . $id, 'Kode mata kuliah sudah dipakai', 'error');
            return;
        }

        // Validasi dosen_id ada & role='dosen'
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'dosen'");
        $stmt->execute([$dosen_id]);
        if ($stmt->rowCount() == 0) {
            redirectWithMessage('../admin/mata_kuliah.php?mode=edit&id=' . $id, 'Dosen tidak valid', 'error');
            return;
        }

        // Update
        $sql = "UPDATE mata_kuliah
                SET kode = ?, nama = ?, sks = ?, semester = ?, dosen_id = ?, updated_at = NOW()
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kode, $nama, $sks, $semester, $dosen_id, $id]);

        redirectWithMessage('../admin/mata_kuliah.php', 'Mata kuliah berhasil diperbarui', 'success');
    } catch (PDOException $e) {
        redirectWithMessage('../admin/mata_kuliah.php?mode=edit&id=' . $id, 'Error: ' . $e->getMessage(), 'error');
    }
}

/* ---------------- DELETE ---------------- */
function deleteMataKuliah($pdo) {
    if (empty($_GET['id'])) {
        redirectWithMessage('../admin/mata_kuliah.php', 'ID tidak valid', 'error');
        return;
    }
    $id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("SELECT id FROM mata_kuliah WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() == 0) {
            redirectWithMessage('../admin/mata_kuliah.php', 'Mata kuliah tidak ditemukan', 'error');
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM mata_kuliah WHERE id = ?");
        $stmt->execute([$id]);

        redirectWithMessage('../admin/mata_kuliah.php', 'Mata kuliah berhasil dihapus', 'success');
    } catch (PDOException $e) {
        redirectWithMessage('../admin/mata_kuliah.php', 'Error: ' . $e->getMessage(), 'error');
    }
}

/* ---------------- GET (single) ---------------- */
function getMataKuliah($pdo) {
    if (empty($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        return;
    }
    $id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("
            SELECT mk.*, u.nama AS dosen_name
            FROM mata_kuliah mk
            LEFT JOIN users u ON mk.dosen_id = u.id
            WHERE mk.id = ?
        ");
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

/* ---------------- GET ALL ---------------- */
function getAllMataKuliah($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT mk.*, u.nama AS dosen_name
            FROM mata_kuliah mk
            LEFT JOIN users u ON mk.dosen_id = u.id
            ORDER BY mk.kode ASC
        ");
        $stmt->execute();
        $mata_kuliah = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $mata_kuliah, 'count' => count($mata_kuliah)]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

/* ---------------- Helper ---------------- */
function redirectWithMessage($location, $message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $location");
    exit();
}