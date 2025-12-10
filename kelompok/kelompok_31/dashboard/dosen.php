<?php
/**
 * Dashboard Dosen
 * Dikerjakan oleh: Anggota 2
 * 
 * Dashboard dosen menampilkan statistik dan manajemen materi, tugas, dan nilai
 */

session_start();

// Check if logged in and is dosen
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'dosen') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Dashboard Dosen";
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

// Query statistik dari database
try {
    // Total mata kuliah diampu oleh dosen ini
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM mata_kuliah WHERE dosen_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_mata_kuliah = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total mahasiswa (anggap semua mahasiswa di mata kuliah yang diampu)
    // TODO: Buat tabel enrollment jika diperlukan untuk tracking lebih detail
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'mahasiswa'");
    $total_mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total materi yang diupload
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM materi WHERE uploaded_by = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_materi_uploaded = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total tugas yang dibuat
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tugas WHERE created_by = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_tugas_dibuat = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Rata-rata nilai
    $stmt = $pdo->query("SELECT AVG(nilai) as rata FROM submission WHERE nilai IS NOT NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $rata_nilai = $result['rata'] ? round($result['rata'], 2) : 0;
    
} catch (PDOException $e) {
    $total_mata_kuliah = 0;
    $total_mahasiswa = 0;
    $total_materi_uploaded = 0;
    $total_tugas_dibuat = 0;
    $rata_nilai = 0;
}

// Data untuk lainnya
$kehadiran_rata = 85;
$submission_rate = 92;
$last_updated = date('d M Y, H:i') . ' WIB';
?>

<!-- Dashboard Header -->
<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-chalkboard-teacher me-2"></i>Dashboard Dosen</h2>
                <p class="mb-0 text-light">EduPortal - Kelola Mata Kuliah dan Penilaian Mahasiswa</p>
            </div>
            <div class="col-md-4 text-end">
                <p class="mb-1 small">
                    <i class="fas fa-clock me-1"></i>Terakhir diperbarui: <?php echo $last_updated; ?>
                </p>
                <button class="btn btn-light btn-sm">
                    <i class="fas fa-sync-alt me-1"></i>Update Data
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mt-4 mb-5">
    <!-- Statistik Cards Row 1 -->
    <div class="row g-4 mb-4">
        <!-- Card Mata Kuliah -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-primary">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-book fa-3x text-primary"></i>
                    </div>
                    <h2 class="text-primary mb-1"><?php echo $total_mata_kuliah; ?></h2>
                    <p class="text-muted mb-3">Mata Kuliah Diampu</p>
                    <button class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-list me-1"></i>Lihat Semua
                    </button>
                </div>
            </div>
        </div>

        <!-- Card Mahasiswa -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-info">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-info"></i>
                    </div>
                    <h2 class="text-info mb-1"><?php echo $total_mahasiswa; ?></h2>
                    <p class="text-muted mb-3">Total Mahasiswa</p>
                    <button class="btn btn-outline-info btn-sm w-100">
                        <i class="fas fa-eye me-1"></i>Lihat Detail
                    </button>
                </div>
            </div>
        </div>

        <!-- Card Materi -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-file-pdf fa-3x text-success"></i>
                    </div>
                    <h2 class="text-success mb-1"><?php echo $total_materi_uploaded; ?></h2>
                    <p class="text-muted mb-3">Materi Diupload</p>
                    <a href="../dosen/upload_materi.php" class="btn btn-outline-success btn-sm w-100">
                        <i class="fas fa-upload me-1"></i>Upload Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Tugas -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-warning">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-tasks fa-3x text-warning"></i>
                    </div>
                    <h2 class="text-warning mb-1"><?php echo $total_tugas_dibuat; ?></h2>
                    <p class="text-muted mb-3">Tugas Dibuat</p>
                    <a href="../dosen/buat_tugas.php" class="btn btn-outline-warning btn-sm w-100">
                        <i class="fas fa-plus me-1"></i>Buat Tugas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Cards Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Card Rata-rata Nilai -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>Rata-rata Nilai Mahasiswa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="nilaiChart" style="max-height: 200px;"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-3">
                                    <span><strong>Nilai Rata-rata:</strong></span>
                                    <strong class="text-primary"><?php echo $rata_nilai; ?></strong>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2">Distribusi Nilai</small>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><i class="fas fa-circle text-success me-2"></i>A (80-100)</span>
                                            <span>32 siswa</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 38%;"></div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><i class="fas fa-circle text-info me-2"></i>B (70-79)</span>
                                            <span>45 siswa</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 54%;"></div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><i class="fas fa-circle text-warning me-2"></i>C (60-69)</span>
                                            <span>8 siswa</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 8%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="../dosen/input_nilai.php" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-edit me-1"></i>Input Nilai
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Kehadiran dan Submission -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2 text-success"></i>Kehadiran & Pengumpulan Tugas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <canvas id="kehadiranChart" style="max-width: 150px; max-height: 150px; margin: 0 auto;"></canvas>
                            </div>
                            <h4 class="text-success"><?php echo $kehadiran_rata; ?>%</h4>
                            <p class="text-muted mb-0">Kehadiran Rata-rata</p>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <canvas id="submissionChart" style="max-width: 150px; max-height: 150px; margin: 0 auto;"></canvas>
                            </div>
                            <h4 class="text-info"><?php echo $submission_rate; ?>%</h4>
                            <p class="text-muted mb-0">Rate Pengumpulan Tugas</p>
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <small><strong>Tips:</strong> Monitor kehadiran dan pengumpulan tugas mahasiswa secara berkala untuk memastikan proses pembelajaran berjalan optimal.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>Aktivitas Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-4 pb-4 border-bottom">
                            <div class="d-flex gap-3">
                                <div>
                                    <span class="badge bg-success rounded-pill">
                                        <i class="fas fa-file-upload"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Materi "Bab 5 - Database" diupload</h6>
                                    <small class="text-muted">2 jam yang lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4 pb-4 border-bottom">
                            <div class="d-flex gap-3">
                                <div>
                                    <span class="badge bg-primary rounded-pill">
                                        <i class="fas fa-tasks"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Tugas "Project Akhir Semester" dibuat</h6>
                                    <small class="text-muted">1 hari yang lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-4 pb-4 border-bottom">
                            <div class="d-flex gap-3">
                                <div>
                                    <span class="badge bg-info rounded-pill">
                                        <i class="fas fa-check"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Nilai untuk 45 mahasiswa sudah diinput</h6>
                                    <small class="text-muted">3 hari yang lalu</small>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="d-flex gap-3">
                                <div>
                                    <span class="badge bg-warning rounded-pill">
                                        <i class="fas fa-exclamation"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">5 mahasiswa belum mengumpulkan tugas</h6>
                                    <small class="text-muted">4 hari yang lalu</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2 text-primary"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="../dosen/upload_materi.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-upload me-2"></i>Upload Materi
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="../dosen/buat_tugas.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus me-2"></i>Buat Tugas
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="../dosen/input_nilai.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-edit me-2"></i>Input Nilai
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary w-100">
                                <i class="fas fa-download me-2"></i>Export Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
    // Bar Chart - Nilai
    const ctxNilai = document.getElementById('nilaiChart').getContext('2d');
    new Chart(ctxNilai, {
        type: 'bar',
        data: {
            labels: ['A', 'B', 'C', 'D', 'E'],
            datasets: [{
                label: 'Jumlah Mahasiswa',
                data: [32, 45, 8, 2, 0],
                backgroundColor: [
                    '#198754',
                    '#0dcaf0',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Progress Gauge - Kehadiran
    const ctxKehadiran = document.getElementById('kehadiranChart').getContext('2d');
    new Chart(ctxKehadiran, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Tidak Hadir'],
            datasets: [{
                data: [<?php echo $kehadiran_rata; ?>, <?php echo 100 - $kehadiran_rata; ?>],
                backgroundColor: ['#198754', '#e9ecef'],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Progress Gauge - Submission
    const ctxSubmission = document.getElementById('submissionChart').getContext('2d');
    new Chart(ctxSubmission, {
        type: 'doughnut',
        data: {
            labels: ['Dikumpulkan', 'Belum Dikumpulkan'],
            datasets: [{
                data: [<?php echo $submission_rate; ?>, <?php echo 100 - $submission_rate; ?>],
                backgroundColor: ['#0dcaf0', '#e9ecef'],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php include '../components/footer.php'; ?>
