<?php
/**
 * REST API Web Service
 * Dikerjakan oleh: Anggota 4
 * 
 * Format Response:
 * Success: {"status":"success", "message":"...", "data":[...]}
 * Error: {"status":"error", "message":"...", "error_code":xxx}
 * 
 * Endpoints:
 * GET /webservice/api.php/mata-kuliah - List semua mata kuliah
 * GET /webservice/api.php/mata-kuliah/{id} - Detail mata kuliah
 * GET /webservice/api.php/materi - List semua materi
 * GET /webservice/api.php/materi/{id} - Detail materi
 * GET /webservice/api.php/tugas - List semua tugas
 * GET /webservice/api.php/tugas/{id} - Detail tugas
 * GET /webservice/api.php/pengumuman - List semua pengumuman
 * GET /webservice/api.php/pengumuman/{id} - Detail pengumuman
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
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

// Parse request path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/kelompok/kelompok_31/webservice/api.php', '', $path);
$segments = array_filter(explode('/', $path));
$segments = array_values($segments);

$method = $_SERVER['REQUEST_METHOD'];
$resource = $segments[0] ?? 'index';
$id = isset($segments[1]) ? intval($segments[1]) : null;

// Route handler
try {
    switch ($resource) {
        case 'mata-kuliah':
            handleMataKuliah($method, $id, $pdo);
            break;
        case 'materi':
            handleMateri($method, $id, $pdo);
            break;
        case 'tugas':
            handleTugas($method, $id, $pdo);
            break;
        case 'pengumuman':
            handlePengumuman($method, $id, $pdo);
            break;
        case 'index':
            echo json_encode([
                'status' => 'success',
                'message' => 'EduPortal REST API',
                'endpoints' => [
                    'GET /mata-kuliah' => 'List semua mata kuliah',
                    'GET /mata-kuliah/{id}' => 'Detail mata kuliah',
                    'GET /materi' => 'List semua materi',
                    'GET /materi/{id}' => 'Detail materi',
                    'GET /tugas' => 'List semua tugas',
                    'GET /tugas/{id}' => 'Detail tugas',
                    'GET /pengumuman' => 'List semua pengumuman',
                    'GET /pengumuman/{id}' => 'Detail pengumuman'
                ]
            ]);
            break;
        default:
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Resource not found',
                'error_code' => 404
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

// Handler: Mata Kuliah
function handleMataKuliah($method, $id, $pdo) {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed',
            'error_code' => 405
        ]);
        return;
    }
    
    try {
        if ($id) {
            $stmt = $pdo->prepare("
                SELECT mk.*, u.nama as dosen_nama 
                FROM mata_kuliah mk 
                LEFT JOIN users u ON mk.dosen_id = u.id 
                WHERE mk.id = ?
            ");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            
            if ($data) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Mata kuliah ditemukan',
                    'data' => $data
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Mata kuliah tidak ditemukan',
                    'error_code' => 404
                ]);
            }
        } else {
            $stmt = $pdo->query("
                SELECT mk.*, u.nama as dosen_nama 
                FROM mata_kuliah mk 
                LEFT JOIN users u ON mk.dosen_id = u.id 
                ORDER BY mk.kode ASC
            ");
            $data = $stmt->fetchAll();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Data mata kuliah berhasil diambil',
                'data' => $data
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengambil data: ' . $e->getMessage(),
            'error_code' => 500
        ]);
    }
}

// Handler: Materi
function handleMateri($method, $id, $pdo) {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed',
            'error_code' => 405
        ]);
        return;
    }
    
    try {
        if ($id) {
            $stmt = $pdo->prepare("
                SELECT m.*, mk.nama as nama_mk, mk.kode as kode_mk, u.nama as dosen_nama 
                FROM materi m 
                JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id 
                JOIN users u ON m.uploaded_by = u.id 
                WHERE m.id = ?
            ");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            
            if ($data) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Materi ditemukan',
                    'data' => $data
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Materi tidak ditemukan',
                    'error_code' => 404
                ]);
            }
        } else {
            $stmt = $pdo->query("
                SELECT m.*, mk.nama as nama_mk, mk.kode as kode_mk, u.nama as dosen_nama 
                FROM materi m 
                JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id 
                JOIN users u ON m.uploaded_by = u.id 
                ORDER BY m.created_at DESC
            ");
            $data = $stmt->fetchAll();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Data materi berhasil diambil',
                'data' => $data
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengambil data: ' . $e->getMessage(),
            'error_code' => 500
        ]);
    }
}

// Handler: Tugas
function handleTugas($method, $id, $pdo) {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed',
            'error_code' => 405
        ]);
        return;
    }
    
    try {
        if ($id) {
            $stmt = $pdo->prepare("
                SELECT t.*, mk.nama as nama_mk, mk.kode as kode_mk, u.nama as dosen_nama 
                FROM tugas t 
                JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id 
                JOIN users u ON t.created_by = u.id 
                WHERE t.id = ?
            ");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            
            if ($data) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Tugas ditemukan',
                    'data' => $data
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Tugas tidak ditemukan',
                    'error_code' => 404
                ]);
            }
        } else {
            $stmt = $pdo->query("
                SELECT t.*, mk.nama as nama_mk, mk.kode as kode_mk, u.nama as dosen_nama 
                FROM tugas t 
                JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id 
                JOIN users u ON t.created_by = u.id 
                ORDER BY t.deadline ASC
            ");
            $data = $stmt->fetchAll();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Data tugas berhasil diambil',
                'data' => $data
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengambil data: ' . $e->getMessage(),
            'error_code' => 500
        ]);
    }
}

// Handler: Pengumuman
function handlePengumuman($method, $id, $pdo) {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed',
            'error_code' => 405
        ]);
        return;
    }
    
    try {
        if ($id) {
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
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengambil data: ' . $e->getMessage(),
            'error_code' => 500
        ]);
    }
}
?>
