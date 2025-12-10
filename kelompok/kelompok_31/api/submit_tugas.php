<?php
/**
 * API Submit Tugas
 * Dikerjakan oleh: Anggota 3
 */

session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    http_response_code(403);
    echo json_encode(['status'=>'error', 'message'=>'Akses khusus mahasiswa.']); 
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

$mhs_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $sql = "SELECT t.*, mk.nama as nama_mk, u.nama as nama_dosen,
                CASE WHEN st.id IS NOT NULL THEN 'submitted' ELSE 'pending' END as status_kumpul, 
                st.file_path as file_kumpul, st.submitted_at
                FROM tugas t 
                JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id 
                JOIN users u ON t.created_by = u.id 
                LEFT JOIN submission st ON t.id = st.tugas_id AND st.mahasiswa_id = ? 
                ORDER BY t.deadline ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$mhs_id]);
        echo json_encode(['status'=>'success', 'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) { 
        error_log("Error loading tugas: " . $e->getMessage());
        echo json_encode(['status'=>'error', 'message'=>$e->getMessage()]); 
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['tugas_id'])) {
        http_response_code(400);
        echo json_encode(['status'=>'error', 'message'=>'Tugas ID wajib diisi']);
        exit;
    }
    
    $tid = intval($_POST['tugas_id']);
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
        http_response_code(400);
        echo json_encode(['status'=>'error', 'message'=>'File wajib dipilih.']);
        exit;
    }
    
    $file_name = $_FILES['file']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar'];
    
    if (!in_array($file_ext, $allowed_ext)) {
        http_response_code(400);
        echo json_encode(['status'=>'error', 'message'=>'Format file tidak diizinkan. Gunakan: PDF, DOC, DOCX, TXT, ZIP, atau RAR']);
        exit;
    }
    
    $file = "jawaban_".$mhs_id."_".uniqid().".".$file_ext;
    $file_size = $_FILES['file']['size'];
    
    if (!file_exists('../uploads/tugas/')) {
        mkdir('../uploads/tugas/', 0777, true);
    }
        
    if(move_uploaded_file($_FILES['file']['tmp_name'], "../uploads/tugas/".$file)) {
        try {
            $cek = $pdo->prepare("SELECT id FROM submission WHERE tugas_id=? AND mahasiswa_id=?");
            $cek->execute([$tid, $mhs_id]);
            
            if($cek->rowCount() > 0) {
                // Update existing submission
                $pdo->prepare("UPDATE submission SET file_path=?, file_name=?, file_size=?, submitted_at=NOW() WHERE tugas_id=? AND mahasiswa_id=?")->execute([$file, $file_name, $file_size, $tid, $mhs_id]);
            } else {
                // Insert new submission
                $pdo->prepare("INSERT INTO submission (tugas_id, mahasiswa_id, file_path, file_name, file_size) VALUES (?,?,?,?,?)")->execute([$tid, $mhs_id, $file, $file_name, $file_size]);
            }
            echo json_encode(['status'=>'success', 'message'=>'Jawaban berhasil dikirim!']);
        } catch (PDOException $e) {
            // Hapus file jika insert gagal
            if (file_exists("../uploads/tugas/".$file)) {
                unlink("../uploads/tugas/".$file);
            }
            error_log("Error submitting tugas: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status'=>'error', 'message'=>'Gagal menyimpan data ke database']);
        }
    } else { 
        echo json_encode(['status'=>'error', 'message'=>'Gagal upload ke server.']); 
    }
    exit;
}
?>
