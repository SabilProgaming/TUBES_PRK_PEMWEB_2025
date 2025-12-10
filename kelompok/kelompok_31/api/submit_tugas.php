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
    echo json_encode(['status'=>'error', 'message'=>'Akses khusus mahasiswa.']); exit;
}

$mhs_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $sql = "SELECT t.*, mk.nama as nama_mk, u.nama as nama_dosen,
                CASE WHEN st.id IS NOT NULL THEN 'submitted' ELSE 'pending' END as status_kumpul, 
                st.file_path as file_kumpul 
                FROM tugas t 
                JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id 
                JOIN users u ON t.created_by = u.id 
                LEFT JOIN submission st ON t.id = st.tugas_id AND st.mahasiswa_id = ? 
                ORDER BY t.deadline ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$mhs_id]);
        echo json_encode(['status'=>'success', 'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) { echo json_encode(['status'=>'error', 'message'=>$e->getMessage()]); }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = $_POST['tugas_id'];
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = "jawaban_".$mhs_id."_".uniqid().".".pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        if (!file_exists('../uploads/tugas/')) mkdir('../uploads/tugas/', 0777, true);
        
        if(move_uploaded_file($_FILES['file']['tmp_name'], "../uploads/tugas/".$file)) {
            $fname = $_FILES['file']['name'];
            $fsize = $_FILES['file']['size'];
            
            $cek = $pdo->prepare("SELECT id FROM submission WHERE tugas_id=? AND mahasiswa_id=?");
            $cek->execute([$tid, $mhs_id]);
            
            if($cek->rowCount() > 0) {
                $pdo->prepare("UPDATE submission SET file_path=?, file_name=?, file_size=?, submitted_at=NOW() WHERE tugas_id=? AND mahasiswa_id=?")->execute([$file, $fname, $fsize, $tid, $mhs_id]);
            } else {
                $pdo->prepare("INSERT INTO submission (tugas_id, mahasiswa_id, file_path, file_name, file_size) VALUES (?,?,?,?,?)")->execute([$tid, $mhs_id, $file, $fname, $fsize]);
            }
            echo json_encode(['status'=>'success', 'message'=>'Jawaban berhasil dikirim!']);
        } else { echo json_encode(['status'=>'error', 'message'=>'Gagal upload ke server.']); }
    } else { echo json_encode(['status'=>'error', 'message'=>'File wajib dipilih.']); }
    exit;
}
?>