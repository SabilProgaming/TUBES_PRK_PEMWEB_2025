<?php
/**
 * Test Script untuk Debug Submission
 * Hapus file ini setelah selesai debugging
 */

session_start();
require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    die("Koneksi database gagal");
}

echo "<h2>Test Submission Data</h2>";

// 1. Cek semua tugas
echo "<h3>1. Semua Tugas di Database:</h3>";
$stmt = $pdo->query("SELECT id, judul, created_by, mata_kuliah_id FROM tugas ORDER BY id");
$allTugas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($allTugas);
echo "</pre>";

// 2. Cek semua submission
echo "<h3>2. Semua Submission di Database:</h3>";
$stmt = $pdo->query("SELECT id, tugas_id, mahasiswa_id, file_name, submitted_at FROM submission ORDER BY id");
$allSubmission = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($allSubmission);
echo "</pre>";

// 3. Cek relasi tugas dan submission
echo "<h3>3. Relasi Tugas dan Submission:</h3>";
$stmt = $pdo->query("
    SELECT 
        t.id as tugas_id,
        t.judul,
        t.created_by as dosen_id,
        COUNT(s.id) as jumlah_submission
    FROM tugas t
    LEFT JOIN submission s ON t.id = s.tugas_id
    GROUP BY t.id
    ORDER BY t.id
");
$relasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($relasi);
echo "</pre>";

// 4. Test query input nilai untuk dosen ID tertentu
if (isset($_GET['dosen_id'])) {
    $dosen_id = intval($_GET['dosen_id']);
    echo "<h3>4. Test Query Input Nilai untuk Dosen ID: $dosen_id</h3>";
    
    $sql = "
        SELECT 
            s.id,
            s.tugas_id,
            s.mahasiswa_id,
            s.file_name,
            s.submitted_at,
            t.judul as judul_tugas,
            t.created_by,
            u.nama as nama_mahasiswa
        FROM submission s
        INNER JOIN tugas t ON s.tugas_id = t.id
        INNER JOIN users u ON s.mahasiswa_id = u.id
        WHERE t.created_by = ?
        ORDER BY s.submitted_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dosen_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    echo "<p>Total: " . count($result) . " submission</p>";
}

echo "<hr>";
echo "<p>Untuk test dengan dosen ID tertentu, tambahkan ?dosen_id=X di URL</p>";
echo "<p>Contoh: test_submission.php?dosen_id=2</p>";
?>

