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

// Koneksi database menggunakan Database class
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal',
        'error_code' => 500
    ]);
    exit;
}

$mahasiswa_id = intval($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Query untuk mengambil semua submission mahasiswa beserta nilai dan feedback
        // Mengambil dari tabel submission yang sudah dinilai oleh dosen
        $sql = "
            SELECT 
                s.id,
                s.tugas_id,
                s.file_path,
                s.file_name,
                s.file_size,
                s.submitted_at,
                s.nilai,
                s.feedback,
                s.dinilai_oleh,
                s.dinilai_pada,
                t.judul as judul_tugas,
                t.deadline,
                mk.id as mata_kuliah_id,
                mk.nama as nama_mk,
                mk.kode as kode_mk
            FROM submission s
            INNER JOIN tugas t ON s.tugas_id = t.id
            INNER JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id
            WHERE s.mahasiswa_id = ?
            ORDER BY s.submitted_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$mahasiswa_id]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug logging
        error_log("Nilai Mahasiswa API - Mahasiswa ID: $mahasiswa_id, Found " . count($data) . " submissions");
        if (count($data) > 0) {
            $withNilai = array_filter($data, function($item) {
                return $item['nilai'] !== null;
            });
            error_log("Submissions with nilai: " . count($withNilai));
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Data nilai berhasil diambil',
            'data' => $data,
            'count' => count($data)
        ]);
    } catch (PDOException $e) {
        error_log("Nilai Mahasiswa API Error: " . $e->getMessage());
        error_log("SQL: " . $sql);
        error_log("Stack trace: " . $e->getTraceAsString());
        
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

