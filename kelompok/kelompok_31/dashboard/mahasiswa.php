<?php
/**
 * Dashboard Mahasiswa
 * Dikerjakan oleh: Anggota 2
 */

session_start();

// Check if logged in and is mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Dashboard Mahasiswa";
include '../components/header.php';
include '../components/navbar.php';

// Koneksi database
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=eduportal;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Error: Koneksi database gagal');
}

// Query statistik
try {
    // Total mata kuliah diambil
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM enrollment WHERE mahasiswa_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_mata_kuliah = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total materi
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM materi");
    $total_materi_downloaded = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total tugas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tugas");
    $total_tugas = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Dummy jika kosong
    if ($total_tugas == 0) $total_tugas = 5;
    if ($total_materi_downloaded == 0) $total_materi_downloaded = 10;

    // Tugas dikumpulkan
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM submission WHERE mahasiswa_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $tugas_dikumpulkan = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Rata-rata nilai
    $stmt = $pdo->prepare("SELECT AVG(nilai) as rata FROM submission WHERE mahasiswa_id = ? AND nilai IS NOT NULL");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $rata_nilai = $result['rata'] ? round($result['rata'], 2) : 0;

} catch (PDOException $e) {
    // Dummy data
    $total_mata_kuliah = 3;
    $total_materi_downloaded = 10;
    $total_tugas = 5;
    $tugas_dikumpulkan = 2;
    $rata_nilai = 85;
}

// Dummy lainnya
$ips = 3.45;
$kehadiran = 88;

// Fix division zero
$progress_tugas = ($total_tugas > 0) ? round(($tugas_dikumpulkan / $total_tugas) * 100) : 0;
$belum_tugas = max($total_tugas - $tugas_dikumpulkan, 0);

$last_updated = date('d M Y, H:i') . ' WIB';
?>

<!-- Dashboard Header -->
<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-graduation-cap me-2"></i>Dashboard Mahasiswa</h2>
                <p class="mb-0 text-light">EduPortal - Portal Akademik Mahasiswa</p>
            </div>
            <div class="col-md-4 text-end">
                <p class="mb-1 small"><i class="fas fa-clock me-1"></i>Terakhir diperbarui: <?= $last_updated ?></p>
                <button class="btn btn-light btn-sm"><i class="fas fa-sync-alt me-1"></i>Update Data</button>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mt-4 mb-5">

    <!-- Statistik Cards -->
    <div class="row g-4 mb-4">

        <!-- IPS -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-primary">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="fas fa-star fa-3x text-primary"></i></div>
                    <h2 class="text-primary mb-1"><?= $ips ?></h2>
                    <p class="text-muted mb-3">Indeks Prestasi Semester</p>
                    <small class="badge bg-primary">Sangat Memuaskan</small>
                </div>
            </div>
        </div>

        <!-- Mata Kuliah -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-info">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="fas fa-book fa-3x text-info"></i></div>
                    <h2 class="text-info mb-1"><?= $total_mata_kuliah ?></h2>
                    <p class="text-muted mb-3">Mata Kuliah Diambil</p>
                    <a class="btn btn-outline-info btn-sm w-100" href="../mahasiswa/materi.php">Materi</a>
                </div>
            </div>
        </div>

        <!-- Tugas -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="fas fa-tasks fa-3x text-success"></i></div>
                    <h2 class="text-success mb-1"><?= $tugas_dikumpulkan ?>/<?= $total_tugas ?></h2>
                    <p class="text-muted mb-3">Tugas Dikumpulkan</p>
                    <a class="btn btn-outline-success btn-sm w-100" href="../mahasiswa/tugas.php">Lihat Tugas</a>
                </div>
            </div>
        </div>

        <!-- Nilai -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-warning">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="fas fa-chart-line fa-3x text-warning"></i></div>
                    <h2 class="text-warning mb-1"><?= $rata_nilai ?></h2>
                    <p class="text-muted mb-3">Rata-rata Nilai</p>
                    <a class="btn btn-outline-warning btn-sm w-100" href="../mahasiswa/nilai.php">Lihat Nilai</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Classes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5><i class="fas fa-plus-circle me-2 text-success"></i>Bergabung ke Kelas Tersedia</h5></div>
                <div class="card-body">
                    <div id="availableClassesContainer" class="row g-3">
                        <div class="col-12 text-center"><p class="text-muted">Loading...</p></div>
                    </div>
                    <a href="../mahasiswa/matakuliah.php" class="btn btn-outline-primary btn-sm mt-3">
                        <i class="fas fa-eye me-1"></i> Lihat Semua Mata Kuliah
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Kehadiran & Progress -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><h5><i class="fas fa-check-circle me-2 text-success"></i>Kehadiran & Progress</h5></div>
                <div class="card-body text-center">
                    <h4 class="text-success"><?= $kehadiran ?>%</h4>
                    <p>Kehadiran Semester Ini</p>
                    <hr>
                    <h4 class="text-info"><?= $progress_tugas ?>%</h4>
                    <p>Progress Tugas</p>
                </div>
            </div>
        </div>

        <!-- Dummy Chart placeholder -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><h5><i class="fas fa-chart-bar me-2 text-primary"></i>Performa Akademik</h5></div>
                <div class="card-body">
                    <p class="text-muted text-center">Chart di-load dengan JavaScript</p>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Kelas load
function bergabungKelas(id, kode) {
    alert("Dummy: Bergabung ke kelas " + kode);
}

// Load kelas dummy
$(document).ready(function() {
    setTimeout(() => {
        $("#availableClassesContainer").html(`
            <div class="col-md-4">
                <div class="card border-info h-100">
                    <div class="card-body">
                        <h6>KLS101</h6>
                        <p class="small text-muted">Pemrograman Web</p>
                        <button class="btn btn-sm btn-success w-100" onclick="bergabungKelas(1,'KLS101')">Bergabung</button>
                    </div>
                </div>
            </div>
        `);
    }, 600);
});
</script>

<?php include '../components/footer.php'; ?>