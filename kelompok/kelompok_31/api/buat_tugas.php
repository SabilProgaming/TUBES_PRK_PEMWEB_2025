<?php
/**
 * API Buat Tugas
 * Dikerjakan oleh: Anggota 3
 */
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    http_response_code(403);
    echo json_encode(['status'=>'error', 'message'=>'Akses ditolak. Silakan login sebagai dosen.']);
    exit;
}

$dosen_id = $_SESSION['user_id'];

if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("SELECT file_path FROM tugas WHERE id = ? AND created_by = ?");
        $stmt->execute([$id, $dosen_id]); 
        $d = $stmt->fetch();
        
        if($d) {
            if(isset($d['file_path']) && $d['file_path'] && file_exists("../uploads/tugas/".$d['file_path'])) {
                unlink("../uploads/tugas/".$d['file_path']);
            }

            $stmtMhs = $pdo->prepare("SELECT file_path FROM submission WHERE tugas_id = ?");
            $stmtMhs->execute([$id]);
            while($row = $stmtMhs->fetch()) {
                if($row['file_path'] && file_exists("../uploads/tugas/".$row['file_path'])) {
                    unlink("../uploads/tugas/".$row['file_path']);
                }
            }
            $pdo->prepare("DELETE FROM submission WHERE tugas_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM tugas WHERE id = ?")->execute([$id]);
            
            echo json_encode(['status'=>'success', 'message'=>'Tugas berhasil dihapus permanen.']);
        } else {
            echo json_encode(['status'=>'error', 'message'=>'Data tidak ditemukan atau bukan milik anda.']);
        }
    } catch (Exception $e) { 
        echo json_encode(['status'=>'error', 'message'=>$e->getMessage()]); 
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $sql = "SELECT t.*, mk.nama as nama_mk FROM tugas t 
                JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id 
                WHERE t.created_by = ? 
                ORDER BY t.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dosen_id]);
        echo json_encode(['status'=>'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['status'=>'error', 'message'=>"Query error GET: " . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $mk = $_POST['mata_kuliah_id'];
    $desc = $_POST['deskripsi'];
    $dl = $_POST['deadline'];

    $file_path = null;
    $file_name = null;
    $file_size = null;

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $file_name_original = $_FILES['file']['name'];
        $file_size_bytes = $_FILES['file']['size'];
        
        $file_path_system = "soal_".uniqid().".".$ext;
        
        if (!file_exists('../uploads/tugas/')) mkdir('../uploads/tugas/', 0777, true);
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], "../uploads/tugas/".$file_path_system)) {
            $file_path = $file_path_system;
            $file_name = $file_name_original;
            $file_size = $file_size_bytes;
        }
    }

    try {
        $sql = "INSERT INTO tugas (judul, mata_kuliah_id, deskripsi, deadline, file_path, file_name, file_size, created_by) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([$judul, $mk, $desc, $dl, $file_path, $file_name, $file_size, $dosen_id]);
        
        echo json_encode(['status'=>'success', 'message'=>'Tugas berhasil diterbitkan!']);
    } catch (Exception $e) {
        echo json_encode(['status'=>'error', 'message'=>"Query error POST: " . $e->getMessage()]);
    }
    exit;
}
?>