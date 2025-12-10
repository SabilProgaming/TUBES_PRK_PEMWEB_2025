<?php
/**
 * API Enrollment Mahasiswa ke Kelas
 * Dikerjakan oleh: Anggota 2
 * 
 * Handle bergabung mahasiswa ke kelas mata kuliah
 */

session_start();

// Check if logged in and is mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak'
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

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    switch ($action) {
        case 'join':
            joinKelas($pdo);
            break;
        
        case 'getMyClasses':
            getMyClasses($pdo);
            break;
        
        case 'getAvailableClasses':
            getAvailableClasses($pdo);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

/**
 * Bergabung ke kelas
 */
function joinKelas($pdo) {
    if (empty($_POST['mata_kuliah_id'])) {
        echo json_encode(['success' => false, 'message' => 'Mata kuliah ID tidak valid']);
        return;
    }

    $mata_kuliah_id = intval($_POST['mata_kuliah_id']);
    $mahasiswa_id = $_SESSION['user_id'];

    try {
        // Check if mata kuliah exists
        $stmt = $pdo->prepare("SELECT id FROM mata_kuliah WHERE id = ?");
        $stmt->execute([$mata_kuliah_id]);
        if ($stmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Mata kuliah tidak ditemukan']);
            return;
        }

        // Check if already enrolled
        $stmt = $pdo->prepare("SELECT id FROM enrollment WHERE mata_kuliah_id = ? AND mahasiswa_id = ?");
        $stmt->execute([$mata_kuliah_id, $mahasiswa_id]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Anda sudah bergabung ke kelas ini']);
            return;
        }

        // Insert enrollment
        $stmt = $pdo->prepare("INSERT INTO enrollment (mata_kuliah_id, mahasiswa_id, joined_at) VALUES (?, ?, NOW())");
        $stmt->execute([$mata_kuliah_id, $mahasiswa_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Berhasil bergabung ke kelas'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

/**
 * Ambil kelas yang sudah diambil mahasiswa
 */
function getMyClasses($pdo) {
    $mahasiswa_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("
            SELECT mk.id, mk.kode, mk.nama, mk.sks, mk.dosen_id, u.nama as dosen_nama
            FROM enrollment e
            JOIN mata_kuliah mk ON e.mata_kuliah_id = mk.id
            LEFT JOIN users u ON mk.dosen_id = u.id
            WHERE e.mahasiswa_id = ?
            ORDER BY mk.kode
        ");
        $stmt->execute([$mahasiswa_id]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $classes,
            'count' => count($classes)
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

/**
 * Ambil kelas yang tersedia (belum diambil)
 */
function getAvailableClasses($pdo) {
    $mahasiswa_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("
            SELECT mk.id, mk.kode, mk.nama, mk.sks, mk.dosen_id, u.nama as dosen_nama
            FROM mata_kuliah mk
            LEFT JOIN users u ON mk.dosen_id = u.id
            WHERE mk.id NOT IN (
                SELECT mata_kuliah_id FROM enrollment WHERE mahasiswa_id = ?
            )
            ORDER BY mk.kode
            LIMIT 5
        ");
        $stmt->execute([$mahasiswa_id]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $classes,
            'count' => count($classes)
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
