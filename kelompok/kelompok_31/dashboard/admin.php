<?php
/**
 * Dashboard Admin
 * Dikerjakan oleh: Anggota 2
 * 
 * Template dashboard sebagai patokan untuk tim
 */

session_start();

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Dashboard Admin";
include '../components/header.php';
include '../components/navbar.php';

// Koneksi database untuk pengumuman
require_once '../config/database.php';
$database = new Database();
$pdo = $database->getConnection();

// Get pengumuman terbaru
$pengumuman_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.nama as author_name 
            FROM pengumuman p
            JOIN users u ON p.created_by = u.id
            ORDER BY p.created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        $pengumuman_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error loading pengumuman: " . $e->getMessage());
    }
}

// Data dummy untuk statistik (akan diganti dengan data real nanti)
$total_mahasiswa = 5261;
$mahasiswa_aktif = 3802;
$mahasiswa_nonaktif = 1287;
$mahasiswa_cuti = 82;
$total_mata_kuliah = 45;
$total_dosen = 28;
$kehadiran_persen = 89;
$pelaporan_persen = 88;
$last_updated = date('d M Y, H:i') . ' WIB';
?>

<!-- Dashboard Header -->
<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</h2>
                <p class="mb-0 text-light">EduPortal - Sistem Manajemen Pembelajaran Akademik</p>
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
        <!-- Card Mahasiswa -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2 text-primary"></i>Mahasiswa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="mahasiswaChart" style="max-height: 200px;"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-circle text-primary me-2"></i>AKM Aktif</span>
                                    <strong><?php echo number_format($mahasiswa_aktif); ?> Mhs</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-circle text-info me-2"></i>AKM Non Aktif</span>
                                    <strong><?php echo number_format($mahasiswa_nonaktif); ?> Mhs</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-circle text-warning me-2"></i>AKM Cuti</span>
                                    <strong><?php echo number_format($mahasiswa_cuti); ?> Mhs</strong>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <strong>Mahasiswa Aktif:</strong> 
                                <span class="text-primary"><?php echo number_format($total_mahasiswa); ?></span> 
                                dari total <?php echo number_format($total_mahasiswa + $mahasiswa_nonaktif + $mahasiswa_cuti); ?> mhs
                            </div>
                            <div>
                                <strong>IPS Mahasiswa:</strong> 
                                <span class="text-success">3,86</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-eye me-1"></i>Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Mata Kuliah -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-primary">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-book fa-3x text-primary"></i>
                    </div>
                    <h2 class="text-primary mb-1"><?php echo $total_mata_kuliah; ?></h2>
                    <p class="text-muted mb-3">Mata Kuliah</p>
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>Lihat Semua
                    </button>
                </div>
            </div>
        </div>

        <!-- Card Dosen -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-chalkboard-teacher fa-3x text-success"></i>
                    </div>
                    <h2 class="text-success mb-1"><?php echo $total_dosen; ?></h2>
                    <p class="text-muted mb-3">Dosen</p>
                    <button class="btn btn-outline-success btn-sm">
                        <i class="fas fa-users me-1"></i>Lihat Semua
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Cards Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Card Kehadiran Mahasiswa -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check me-2 text-success"></i>Kehadiran Mahasiswa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <canvas id="kehadiranChart" style="max-width: 200px; max-height: 200px; margin: 0 auto;"></canvas>
                        <h3 class="mt-3 text-success"><?php echo $kehadiran_persen; ?>%</h3>
                        <p class="text-muted">sampai pekan ke-12</p>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-graduation-cap me-2 text-primary"></i>S1-Sistem Informasi</span>
                            <strong>80% perkuliahan terlaksana</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-graduation-cap me-2 text-info"></i>S1-Desain Komunikasi Visual</span>
                            <strong>82% perkuliahan terlaksana</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-graduation-cap me-2 text-warning"></i>S1-Multimedia</span>
                            <strong>84% perkuliahan terlaksana</strong>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-success btn-sm w-100">
                            <i class="fas fa-eye me-1"></i>Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Pelaporan PDDikti -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-warning">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2 text-warning"></i>Pelaporan PDDikti
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <canvas id="pelaporanChart" style="max-width: 200px; max-height: 200px; margin: 0 auto;"></canvas>
                        <h3 class="mt-3 text-warning"><?php echo $pelaporan_persen; ?>%</h3>
                        <p class="text-muted">Pelaporan</p>
                    </div>
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Penting:</strong> Segera selesaikan pelaporan sebelum 30 April 2025 agar Perguruan Tinggi Anda dapat terakreditasi.
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-warning btn-sm w-100">
                            <i class="fas fa-eye me-1"></i>Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
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
                            <a href="admin/mata_kuliah.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-book me-2"></i>Kelola Mata Kuliah
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="admin/pengumuman.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-bullhorn me-2"></i>Buat Pengumuman
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-success w-100">
                                <i class="fas fa-file-export me-2"></i>Export Data
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary w-100">
                                <i class="fas fa-cog me-2"></i>Pengaturan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengumuman Terbaru -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-bullhorn me-2"></i>Pengumuman Terbaru
                    </h5>
                    <a href="admin/pengumuman.php" class="btn btn-light btn-sm">
                        <i class="fas fa-eye me-1"></i>Lihat Semua
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($pengumuman_list)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Belum ada pengumuman</p>
                            <a href="admin/pengumuman.php" class="btn btn-info btn-sm mt-3">
                                <i class="fas fa-plus me-1"></i>Buat Pengumuman Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($pengumuman_list as $pengumuman): 
                                $created_date = date('d M Y, H:i', strtotime($pengumuman['created_at']));
                                $excerpt = strlen($pengumuman['isi']) > 150 ? substr($pengumuman['isi'], 0, 150) . '...' : $pengumuman['isi'];
                            ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($pengumuman['judul']); ?></h6>
                                            <p class="mb-2 text-muted small"><?php echo htmlspecialchars($excerpt); ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($pengumuman['author_name']); ?>
                                                <span class="ms-2">
                                                    <i class="fas fa-calendar me-1"></i><?php echo $created_date; ?>
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
    // Donut Chart - Mahasiswa
    const ctxMahasiswa = document.getElementById('mahasiswaChart').getContext('2d');
    new Chart(ctxMahasiswa, {
        type: 'doughnut',
        data: {
            labels: ['AKM Aktif', 'AKM Non Aktif', 'AKM Cuti'],
            datasets: [{
                data: [<?php echo $mahasiswa_aktif; ?>, <?php echo $mahasiswa_nonaktif; ?>, <?php echo $mahasiswa_cuti; ?>],
                backgroundColor: [
                    '#0d6efd',
                    '#0dcaf0',
                    '#ffc107'
                ],
                borderWidth: 0
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

    // Progress Gauge - Kehadiran
    const ctxKehadiran = document.getElementById('kehadiranChart').getContext('2d');
    new Chart(ctxKehadiran, {
        type: 'doughnut',
        data: {
            labels: ['Kehadiran', 'Tidak Hadir'],
            datasets: [{
                data: [<?php echo $kehadiran_persen; ?>, <?php echo 100 - $kehadiran_persen; ?>],
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

    // Progress Gauge - Pelaporan
    const ctxPelaporan = document.getElementById('pelaporanChart').getContext('2d');
    new Chart(ctxPelaporan, {
        type: 'doughnut',
        data: {
            labels: ['Pelaporan', 'Belum'],
            datasets: [{
                data: [<?php echo $pelaporan_persen; ?>, <?php echo 100 - $pelaporan_persen; ?>],
                backgroundColor: ['#ffc107', '#e9ecef'],
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
