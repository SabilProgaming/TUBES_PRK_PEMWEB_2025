<?php
/**
 * API CRUD Pengumuman
 * Dikerjakan oleh: Anggota 4
 * 
 * Format Response:
 * Success: {"status":"success", "message":"...", "data":[...]}
 * Error: {"status":"error", "message":"...", "error_code":xxx}
 */

session_start();
header('Content-Type: application/json');

// Check session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak. Hanya admin yang dapat mengelola pengumuman.',
        'error_code' => 403
    ]);
    exit;
}

require_once '../config/database.php';

// Koneksi database sudah diinisialisasi di config/database.php
if (!$pdo) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal',
        'error_code' => 500
    ]);
    exit;
}

$admin_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Handle different HTTP methods
try {
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            handlePost($pdo, $admin_id);
            break;
        case 'PUT':
            handlePut($pdo, $admin_id);
            break;
        case 'DELETE':
            handleDelete($pdo, $admin_id);
            break;
        default:
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Method not allowed',
                'error_code' => 405
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'error_code' => 500
    ]);
}

// GET: List semua pengumuman atau detail pengumuman
function handleGet($pdo) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if ($id) {
        // Get single pengumuman
        $stmt = $pdo->prepare("
            SELECT p.*, u.nama as created_by_name 
            FROM pengumuman p 
            JOIN users u ON p.created_by = u.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Pengumuman ditemukan',
                'data' => $data
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Pengumuman tidak ditemukan',
                'error_code' => 404
            ]);
        }
    } else {
        // Get all pengumuman
        $stmt = $pdo->query("
            SELECT p.*, u.nama as created_by_name 
            FROM pengumuman p 
            JOIN users u ON p.created_by = u.id 
            ORDER BY p.created_at DESC
        ");
        $data = $stmt->fetchAll();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Data pengumuman berhasil diambil',
            'data' => $data
        ]);
    }
}

// POST: Create pengumuman baru
function handlePost($pdo, $admin_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validasi input
    if (empty($input['judul']) || empty($input['isi'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Judul dan isi pengumuman wajib diisi',
            'error_code' => 400
        ]);
        return;
    }
    
    $judul = trim($input['judul']);
    $isi = trim($input['isi']);
    
    // Validasi panjang
    if (strlen($judul) > 200) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Judul maksimal 200 karakter',
            'error_code' => 400
        ]);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO pengumuman (judul, isi, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$judul, $isi, $admin_id]);
        
        $id = $pdo->lastInsertId();
        
        // Get created pengumuman
        $stmt = $pdo->prepare("
            SELECT p.*, u.nama as created_by_name 
            FROM pengumuman p 
            JOIN users u ON p.created_by = u.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Pengumuman berhasil dibuat',
            'data' => $data
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan pengumuman: ' . $e->getMessage(),
            'error_code' => 500
        ]);
    }
}

// PUT: Update pengumuman
function handlePut($pdo, $admin_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'ID pengumuman wajib diisi',
            'error_code' => 400
        ]);
        return;
    }
    
    // Validasi input
    if (empty($input['judul']) || empty($input['isi'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Judul dan isi pengumuman wajib diisi',
            'error_code' => 400
        ]);
        return;
    }
    
    // Check if pengumuman exists and belongs to admin
    $stmt = $pdo->prepare("SELECT id FROM pengumuman WHERE id = ? AND created_by = ?");
    $stmt->execute([$id, $admin_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Pengumuman tidak ditemukan atau bukan milik Anda',
            'error_code' => 404
        ]);
        return;
    }
    
    $judul = trim($input['judul']);
    $isi = trim($input['isi']);
    
    try {
        $stmt = $pdo->prepare("UPDATE pengumuman SET judul = ?, isi = ? WHERE id = ?");
        $stmt->execute([$judul, $isi, $id]);
        
        // Get updated pengumuman
        $stmt = $pdo->prepare("
            SELECT p.*, u.nama as created_by_name 
            FROM pengumuman p 
            JOIN users u ON p.created_by = u.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Pengumuman berhasil diupdate',
            'data' => $data
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengupdate pengumuman: ' . $e->getMessage(),
            'error_code' => 500
        ]);
    }
}

// DELETE: Hapus pengumuman
function handleDelete($pdo, $admin_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'ID pengumuman wajib diisi',
            'error_code' => 400
        ]);
        return;
    }
    
    // Check if pengumuman exists and belongs to admin
    $stmt = $pdo->prepare("SELECT id FROM pengumuman WHERE id = ? AND created_by = ?");
    $stmt->execute([$id, $admin_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Pengumuman tidak ditemukan atau bukan milik Anda',
            'error_code' => 404
        ]);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM pengumuman WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Pengumuman berhasil dihapus'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menghapus pengumuman: ' . $e->getMessage(),
            'error_code' => 500
        ]);
    }
}
?>

