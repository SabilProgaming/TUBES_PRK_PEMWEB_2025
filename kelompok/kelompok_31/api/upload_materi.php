<?php
/**
 * API Upload Materi
 * Dikerjakan oleh: Anggota 3
 */

session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) { 
    http_response_code(403);
    echo json_encode(['status'=>'error', 'message'=>'Unauthorized']); 
    exit; 
}

// Koneksi database
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['status'=>'error', 'message'=>'Koneksi database gagal']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role']; 

if (isset($_POST['action']) && $_POST['action'] == 'delete' && $role === 'dosen') {
    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("SELECT file_path FROM materi WHERE id = ? AND uploaded_by = ?"); 
        $stmt->execute([$id, $user_id]); 
        $d = $stmt->fetch();
        
        if($d) {
            if($d['file_path'] && file_exists("../uploads/materi/".$d['file_path'])) {
                unlink("../uploads/materi/".$d['file_path']);
            }
            $pdo->prepare("DELETE FROM materi WHERE id = ?")->execute([$id]);
            echo json_encode(['status'=>'success', 'message'=>'Materi dihapus.']);
        } else {
            echo json_encode(['status'=>'error', 'message'=>'Akses ditolak.']);
        }
    } catch (Exception $e) { 
        echo json_encode(['status'=>'error', 'message'=>$e->getMessage()]); 
    } 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $sql = "SELECT m.*, 
                m.created_at as uploaded_at, 
                mk.nama as nama_mk, 
                mk.kode as kode_mk,
                u.nama as nama_dosen 
                FROM materi m 
                JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id 
                JOIN users u ON m.uploaded_by = u.id 
                ORDER BY m.created_at DESC";
        $stmt = $pdo->query($sql);
        echo json_encode(['status'=>'success', 'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log("Error loading materi: " . $e->getMessage());
        echo json_encode(['status'=>'error', 'message'=>$e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'dosen') {
    // Validasi input
    if (empty($_POST['judul']) || empty($_POST['mata_kuliah_id'])) {
        http_response_code(400);
        echo json_encode(['status'=>'error', 'message'=>'Judul dan Mata Kuliah wajib diisi']);
        exit;
    }
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
        http_response_code(400);
        echo json_encode(['status'=>'error', 'message'=>'File wajib diupload']);
        exit;
    }
    
    $judul = trim($_POST['judul']);
    $mk = intval($_POST['mata_kuliah_id']);
    $desc = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
    
    $file_name = $_FILES['file']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar', 'pptx', 'xlsx'];
    
    if (!in_array($file_ext, $allowed_ext)) {
        http_response_code(400);
        echo json_encode(['status'=>'error', 'message'=>'Format file tidak diizinkan. Gunakan: PDF, DOC, DOCX, TXT, ZIP, RAR, PPTX, atau XLSX']);
        exit;
    }
    
    $file = "materi_".uniqid().".".$file_ext;
    $file_size = $_FILES['file']['size'];
    
    if (!file_exists('../uploads/materi/')) {
        mkdir('../uploads/materi/', 0777, true);
    }
    
    if(move_uploaded_file($_FILES['file']['tmp_name'], "../uploads/materi/".$file)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO materi (judul, mata_kuliah_id, deskripsi, file_path, file_name, file_size, uploaded_by) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$judul, $mk, $desc, $file, $file_name, $file_size, $user_id]);
            echo json_encode(['status'=>'success', 'message'=>'Materi berhasil diupload!']);
        } catch (PDOException $e) {
            // Hapus file jika insert gagal
            if (file_exists("../uploads/materi/".$file)) {
                unlink("../uploads/materi/".$file);
            }
            error_log("Error uploading materi: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status'=>'error', 'message'=>'Gagal menyimpan data ke database']);
        }
    } else {
        echo json_encode(['status'=>'error', 'message'=>'Gagal upload file ke server.']);
    }
    exit;
}
?>
