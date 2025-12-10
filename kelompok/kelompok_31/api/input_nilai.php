<?php
/**
 * API Input Nilai
 * Dikerjakan oleh: Anggota 4
 * 
 * Format Response:
 * Success: {"status":"success", "message":"...", "data":[...]}
 * Error: {"status":"error", "message":"...", "error_code":xxx}
 */

session_start();
header('Content-Type: application/json');

// Check session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'dosen') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak. Hanya dosen yang dapat memberikan nilai.',
        'error_code' => 403
    ]);
    exit;
}

require_once '../config/database.php';

if (!$pdo) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal',
        'error_code' => 500
    ]);
    exit;
}

$dosen_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($pdo, $dosen_id);
            break;
        case 'POST':
            handlePost($pdo, $dosen_id);
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

// GET: List submission tugas yang perlu dinilai
function handleGet($pdo, $dosen_id) {
    $mkId = isset($_GET['mata_kuliah_id']) ? intval($_GET['mata_kuliah_id']) : null;
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    
    $sql = "
        SELECT 
            s.id,
            s.tugas_id,
            s.mahasiswa_id,
            s.file_path,
            s.file_name,
            s.submitted_at,
            s.nilai,
            s.feedback,
            t.judul as judul_tugas,
            t.deadline,
            mk.id as mata_kuliah_id,
            mk.nama as nama_mk,
            u.nama as nama_mahasiswa
        FROM submission s
        JOIN tugas t ON s.tugas_id = t.id
        JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id
        JOIN users u ON s.mahasiswa_id = u.id
        WHERE t.created_by = ?
    ";
    
    $params = [$dosen_id];
    
    if ($mkId) {
        $sql .= " AND mk.id = ?";
        $params[] = $mkId;
    }
    
    if ($status === 'belum') {
        $sql .= " AND s.nilai IS NULL";
    } elseif ($status === 'sudah') {
        $sql .= " AND s.nilai IS NOT NULL";
    }
    
    $sql .= " ORDER BY s.submitted_at DESC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Data submission berhasil diambil',
            'data' => $data
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengambil data: ' . $e->getMessage(),
            'error_code' => 500
        ]);
    }
}

// POST: Input/Update nilai
function handlePost($pdo, $dosen_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validasi input
    if (empty($input['submission_id']) || !isset($input['nilai'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Submission ID dan nilai wajib diisi',
            'error_code' => 400
        ]);
        return;
    }
    
    $submission_id = intval($input['submission_id']);
    $nilai = floatval($input['nilai']);
    $feedback = isset($input['feedback']) ? trim($input['feedback']) : '';
    
    // Validasi nilai range
    if ($nilai < 0 || $nilai > 100) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Nilai harus antara 0-100',
            'error_code' => 400
        ]);
        return;
    }
    
    // Check if submission exists and tugas belongs to dosen
    $stmt = $pdo->prepare("
        SELECT s.id 
        FROM submission s
        JOIN tugas t ON s.tugas_id = t.id
        WHERE s.id = ? AND t.created_by = ?
    ");
    $stmt->execute([$submission_id, $dosen_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Submission tidak ditemukan atau bukan tugas Anda',
            'error_code' => 404
        ]);
        return;
    }
    
    try {
        // Update nilai
        $stmt = $pdo->prepare("
            UPDATE submission 
            SET nilai = ?, 
                feedback = ?, 
                dinilai_oleh = ?, 
                dinilai_pada = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$nilai, $feedback, $dosen_id, $submission_id]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Nilai berhasil disimpan'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan nilai: ' . $e->getMessage(),
            'error_code' => 500
        ]);
    }
}
?>

