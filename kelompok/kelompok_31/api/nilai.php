<?php
/**
 * API Nilai Mahasiswa
 * Dikerjakan oleh: Anggota 4
 * 
 * Format Response:
 * Success: {"status":"success", "message":"...", "data":[...]}
 * Error: {"status":"error", "message":"...", "error_code":xxx}
 */

session_start();
header('Content-Type: application/json');

// Check session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak. Hanya mahasiswa yang dapat melihat nilai.',
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

$mahasiswa_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $sql = "
            SELECT 
                s.id,
                s.tugas_id,
                s.file_path,
                s.file_name,
                s.submitted_at,
                s.nilai,
                s.feedback,
                t.judul as judul_tugas,
                t.deadline,
                mk.nama as nama_mk
            FROM submission s
            JOIN tugas t ON s.tugas_id = t.id
            JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id
            WHERE s.mahasiswa_id = ?
            ORDER BY s.submitted_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$mahasiswa_id]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Data nilai berhasil diambil',
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
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed',
        'error_code' => 405
    ]);
}
?>

