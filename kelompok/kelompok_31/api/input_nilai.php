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

$dosen_id = intval($_SESSION['user_id']);
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
    
    // Pastikan dosen_id adalah integer
    $dosen_id = intval($dosen_id);
    
    // Query: Ambil SEMUA submission dari tugas yang dibuat oleh dosen ini
    // Termasuk submission yang sudah ada sebelumnya (dari seed data atau upload lama)
    // Pastikan relasi benar: submission -> tugas -> dosen (created_by)
    $sql = "
        SELECT 
            s.id,
            s.tugas_id,
            s.mahasiswa_id,
            s.file_path,
            s.file_name,
            s.file_size,
            s.submitted_at,
            s.nilai,
            s.feedback,
            s.dinilai_oleh,
            s.dinilai_pada,
            t.id as tugas_id_full,
            t.judul as judul_tugas,
            t.deadline,
            t.created_by as tugas_created_by,
            mk.id as mata_kuliah_id,
            mk.nama as nama_mk,
            mk.kode as kode_mk,
            u.nama as nama_mahasiswa,
            u.id as mahasiswa_user_id
        FROM submission s
        INNER JOIN tugas t ON s.tugas_id = t.id
        INNER JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id
        INNER JOIN users u ON s.mahasiswa_id = u.id
        WHERE t.created_by = ?
    ";
    
    $params = [$dosen_id];
    
    // Filter berdasarkan mata kuliah
    if ($mkId && $mkId > 0) {
        $sql .= " AND mk.id = ?";
        $params[] = $mkId;
    }
    
    // Filter berdasarkan status penilaian
    if ($status === 'belum') {
        $sql .= " AND s.nilai IS NULL";
    } elseif ($status === 'sudah') {
        $sql .= " AND s.nilai IS NOT NULL";
    }
    
    $sql .= " ORDER BY s.submitted_at DESC";
    
    try {
        // Validasi: Pastikan dosen_id benar-benar integer dan tidak kosong
        if (empty($dosen_id) || !is_numeric($dosen_id)) {
            error_log("Invalid dosen_id: " . var_export($dosen_id, true));
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Dosen ID tidak valid',
                'error_code' => 400
            ]);
            return;
        }
        
        // Debug: Cek apakah ada tugas yang dibuat dosen ini (SEMUA tugas, tidak hanya 5)
        // STRICT: Hanya tugas yang created_by benar-benar sama dengan dosen_id
        $checkTugasSql = "SELECT id, judul, created_by FROM tugas WHERE created_by = ?";
        $checkTugasStmt = $pdo->prepare($checkTugasSql);
        $checkTugasStmt->execute([$dosen_id]);
        $tugasList = $checkTugasStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Cek semua submission yang ada (tanpa filter dosen)
        $allSubmissions = $pdo->query("SELECT COUNT(*) as total FROM submission")->fetch(PDO::FETCH_ASSOC);
        
        // Debug: Cek submission untuk tugas dosen ini (SEMUA tugas, termasuk yang lama)
        $submissionCount = 0;
        $tugasIds = [];
        if (count($tugasList) > 0) {
            foreach ($tugasList as $t) {
                // Verifikasi bahwa tugas benar-benar milik dosen ini
                if (intval($t['created_by']) === intval($dosen_id)) {
                    $tugasIds[] = intval($t['id']);
                } else {
                    error_log("WARNING: Tugas ID " . $t['id'] . " created_by (" . $t['created_by'] . ") tidak match dengan dosen_id (" . $dosen_id . ")");
                }
            }
            if (count($tugasIds) > 0) {
                $placeholders = implode(',', array_fill(0, count($tugasIds), '?'));
                $checkSubSql = "SELECT COUNT(*) as total FROM submission WHERE tugas_id IN ($placeholders)";
                $checkSubStmt = $pdo->prepare($checkSubSql);
                $checkSubStmt->execute($tugasIds);
                $checkSubResult = $checkSubStmt->fetch(PDO::FETCH_ASSOC);
                $submissionCount = $checkSubResult['total'];
            }
        }
        
        // Execute main query - STRICT: Hanya submission dari tugas yang dibuat oleh dosen ini
        // Pastikan menggunakan prepared statement dengan parameter yang benar
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verifikasi: Pastikan semua data yang dikembalikan benar-benar dari tugas dosen ini
        $filteredData = [];
        foreach ($data as $row) {
            // Double check: Pastikan tugas_created_by benar-benar match dengan dosen_id
            if (isset($row['tugas_created_by']) && intval($row['tugas_created_by']) === intval($dosen_id)) {
                $filteredData[] = $row;
            } else {
                error_log("WARNING: Filtering out submission ID " . $row['id'] . " karena tugas_created_by (" . ($row['tugas_created_by'] ?? 'NULL') . ") tidak match dengan dosen_id (" . $dosen_id . ")");
            }
        }
        $data = $filteredData;
        
        // Debug logging - selalu log untuk troubleshooting
        error_log("=== Input Nilai Debug ===");
        error_log("Dosen ID: $dosen_id (type: " . gettype($dosen_id) . ")");
        error_log("Total tugas dibuat dosen: " . count($tugasList));
        if (count($tugasList) > 0) {
            error_log("Sample tugas: ID=" . $tugasList[0]['id'] . ", Judul=" . $tugasList[0]['judul'] . ", Created_by=" . $tugasList[0]['created_by']);
            // Verifikasi bahwa semua tugas benar-benar milik dosen ini
            foreach ($tugasList as $t) {
                if (intval($t['created_by']) !== intval($dosen_id)) {
                    error_log("WARNING: Tugas ID " . $t['id'] . " created_by (" . $t['created_by'] . ") tidak match dengan dosen_id (" . $dosen_id . ")");
                }
            }
        }
        error_log("Total semua submission di DB: " . $allSubmissions['total']);
        error_log("Total submission untuk tugas dosen: " . $submissionCount);
        error_log("Mata Kuliah Filter: " . ($mkId ? $mkId : 'Semua'));
        error_log("Status Filter: " . ($status ? $status : 'Semua'));
        error_log("Query result count (before filter): " . count($data) . ", After strict filter: " . count($filteredData));
        
        // Jika ada submission di database tapi tidak muncul di hasil query, cek masalahnya
        if (count($data) == 0 && $submissionCount > 0) {
            // Ada submission tapi tidak muncul - kemungkinan masalah dengan query atau JOIN
            error_log("WARNING: Ada $submissionCount submission untuk tugas dosen, tapi query mengembalikan 0 hasil!");
            error_log("SQL Query: " . $sql);
            error_log("Params: " . print_r($params, true));
            
            // Coba query alternatif untuk debug - tanpa JOIN yang kompleks
            $altSql = "SELECT s.*, t.created_by, t.judul, mk.id as mk_id, u.nama as mhs_nama 
                       FROM submission s 
                       JOIN tugas t ON s.tugas_id = t.id 
                       LEFT JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id
                       LEFT JOIN users u ON s.mahasiswa_id = u.id
                       WHERE t.created_by = ?";
            $altStmt = $pdo->prepare($altSql);
            $altStmt->execute([$dosen_id]);
            $altData = $altStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Alternative query result: " . count($altData) . " rows");
            
            // Jika query alternatif berhasil, gunakan data tersebut
            if (count($altData) > 0) {
                error_log("Using alternative query result");
                // Rebuild data dengan format yang sama
                $data = [];
                foreach ($altData as $row) {
                    $data[] = [
                        'id' => $row['id'],
                        'tugas_id' => $row['tugas_id'],
                        'mahasiswa_id' => $row['mahasiswa_id'],
                        'file_path' => $row['file_path'],
                        'file_name' => $row['file_name'],
                        'file_size' => $row['file_size'],
                        'submitted_at' => $row['submitted_at'],
                        'nilai' => $row['nilai'],
                        'feedback' => $row['feedback'],
                        'dinilai_oleh' => $row['dinilai_oleh'],
                        'dinilai_pada' => $row['dinilai_pada'],
                        'judul_tugas' => $row['judul'],
                        'tugas_created_by' => $row['created_by'],
                        'nama_mahasiswa' => $row['mhs_nama'] ?? 'Unknown'
                    ];
                }
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Data submission berhasil diambil',
            'data' => $data,
            'count' => count($data),
            'debug_info' => [
                'dosen_id' => $dosen_id,
                'total_tugas_dosen' => count($tugasList),
                'total_submission_all' => $allSubmissions['total'],
                'total_submission_tugas_dosen' => $submissionCount
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Input Nilai Error: " . $e->getMessage());
        error_log("SQL: " . $sql);
        error_log("Params: " . print_r($params, true));
        error_log("Stack trace: " . $e->getTraceAsString());
        
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
    
    // STRICT CHECK: Pastikan submission benar-benar dari tugas yang dibuat oleh dosen ini
    // Ini penting untuk mencegah dosen lain menginput nilai untuk tugas yang bukan miliknya
    $stmt = $pdo->prepare("
        SELECT s.id, s.mahasiswa_id, t.mata_kuliah_id, t.created_by as tugas_created_by
        FROM submission s
        INNER JOIN tugas t ON s.tugas_id = t.id
        WHERE s.id = ? AND t.created_by = ?
    ");
    $stmt->execute([$submission_id, $dosen_id]);
    $submissionData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submissionData) {
        error_log("Input Nilai POST - Submission ID $submission_id tidak ditemukan atau bukan tugas dosen ID $dosen_id");
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Submission tidak ditemukan atau bukan tugas Anda',
            'error_code' => 404
        ]);
        return;
    }
    
    // Double check: Pastikan tugas benar-benar dibuat oleh dosen ini
    if (intval($submissionData['tugas_created_by']) !== intval($dosen_id)) {
        error_log("SECURITY WARNING: Dosen ID $dosen_id mencoba input nilai untuk tugas yang dibuat oleh dosen ID " . $submissionData['tugas_created_by']);
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Anda tidak memiliki akses untuk memberikan nilai pada tugas ini',
            'error_code' => 403
        ]);
        return;
    }
    
    $mahasiswa_id = $submissionData['mahasiswa_id'];
    $mata_kuliah_id = $submissionData['mata_kuliah_id'];
    
    try {
        // Mulai transaction
        $pdo->beginTransaction();
        
        // 1. Update nilai di tabel submission
        $stmt = $pdo->prepare("
            UPDATE submission 
            SET nilai = ?, 
                feedback = ?, 
                dinilai_oleh = ?, 
                dinilai_pada = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$nilai, $feedback, $dosen_id, $submission_id]);
        
        // 2. Insert atau Update nilai di tabel nilai (untuk nilai akhir mahasiswa)
        // Cek apakah sudah ada nilai untuk mata kuliah dan mahasiswa ini
        $checkNilai = $pdo->prepare("
            SELECT id FROM nilai 
            WHERE mata_kuliah_id = ? AND mahasiswa_id = ?
        ");
        $checkNilai->execute([$mata_kuliah_id, $mahasiswa_id]);
        $existingNilai = $checkNilai->fetch(PDO::FETCH_ASSOC);
        
        if ($existingNilai) {
            // Update nilai yang sudah ada (bisa diupdate jika ada tugas baru yang dinilai)
            // Atau bisa juga dihitung rata-rata dari semua submission
            // Untuk sekarang, kita update dengan nilai terbaru
            $updateNilai = $pdo->prepare("
                UPDATE nilai 
                SET nilai = ?, 
                    feedback = ?,
                    created_by = ?,
                    updated_at = NOW()
                WHERE mata_kuliah_id = ? AND mahasiswa_id = ?
            ");
            $updateNilai->execute([$nilai, $feedback, $dosen_id, $mata_kuliah_id, $mahasiswa_id]);
        } else {
            // Insert nilai baru
            $insertNilai = $pdo->prepare("
                INSERT INTO nilai (mata_kuliah_id, mahasiswa_id, nilai, feedback, created_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insertNilai->execute([$mata_kuliah_id, $mahasiswa_id, $nilai, $feedback, $dosen_id]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Nilai berhasil disimpan dan tersimpan di nilai mahasiswa'
        ]);
    } catch (PDOException $e) {
        // Rollback transaction jika ada error
        $pdo->rollBack();
        
        error_log("Error saving nilai: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan nilai: ' . $e->getMessage(),
            'error_code' => 500
        ]);
    }
}
?>
