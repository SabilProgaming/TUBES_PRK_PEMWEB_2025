<?php
/**
 * Dashboard Mahasiswa
 * Dikerjakan oleh: Anggota 2
 * 
 * Dashboard mahasiswa menampilkan statistik akademik dan akses ke materi, tugas, dan nilai
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

// Data dummy untuk statistik mahasiswa (akan diganti dengan data real nanti)
$total_mata_kuliah = 6;
$total_materi_downloaded = 18;
$total_tugas = 8;
$tugas_dikumpulkan = 7;
$rata_nilai = 81.5;
$ips = 3.45;
$kehadiran = 88;
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
        <!-- Card IPS -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-primary">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-star fa-3x text-primary"></i>
                    </div>
                    <h2 class="text-primary mb-1"><?php echo $ips; ?></h2>
                    <p class="text-muted mb-3">Indeks Prestasi Semester</p>
                    <small class="badge bg-primary">Sangat Memuaskan</small>
                </div>
            </div>
        </div>

        <!-- Card Mata Kuliah -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-info">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-book fa-3x text-info"></i>
                    </div>
                    <h2 class="text-info mb-1"><?php echo $total_mata_kuliah; ?></h2>
                    <p class="text-muted mb-3">Mata Kuliah Diambil</p>
                    <a href="../mahasiswa/materi.php" class="btn btn-outline-info btn-sm w-100">
                        <i class="fas fa-download me-1"></i>Materi
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Tugas -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-tasks fa-3x text-success"></i>
                    </div>
                    <h2 class="text-success mb-1"><?php echo $tugas_dikumpulkan; ?>/<?php echo $total_tugas; ?></h2>
                    <p class="text-muted mb-3">Tugas Dikumpulkan</p>
                    <a href="../mahasiswa/tugas.php" class="btn btn-outline-success btn-sm w-100">
                        <i class="fas fa-list me-1"></i>Lihat Tugas
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Nilai -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100 border-warning">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-3x text-warning"></i>
                    </div>
                    <h2 class="text-warning mb-1"><?php echo $rata_nilai; ?></h2>
                    <p class="text-muted mb-3">Rata-rata Nilai</p>
                    <a href="../mahasiswa/nilai.php" class="btn btn-outline-warning btn-sm w-100">
                        <i class="fas fa-eye me-1"></i>Lihat Nilai
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Cards Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Card Akademik -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>Performa Akademik
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="nilaiChart" style="max-height: 200px;"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>IPK:</strong>
                                        <strong class="text-primary"><?php echo $ips; ?></strong>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2"><strong>Nilai per Mata Kuliah</strong></small>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Pemrograman Web</span>
                                            <span class="badge bg-success">A</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 95%;"></div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Basis Data</span>
                                            <span class="badge bg-success">A</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 92%;"></div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Algoritma</span>
                                            <span class="badge bg-info">B+</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 88%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="../mahasiswa/nilai.php" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail Nilai
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Kehadiran dan Progress -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2 text-success"></i>Kehadiran & Progress Tugas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <canvas id="kehadiranChart" style="max-width: 150px; max-height: 150px; margin: 0 auto;"></canvas>
                            </div>
                            <h4 class="text-success"><?php echo $kehadiran; ?>%</h4>
                            <p class="text-muted mb-0">Kehadiran Semester Ini</p>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <canvas id="tugasChart" style="max-width: 150px; max-height: 150px; margin: 0 auto;"></canvas>
                            </div>
                            <h4 class="text-info"><?php echo round(($tugas_dikumpulkan / $total_tugas) * 100); ?>%</h4>
                            <p class="text-muted mb-0">Progress Tugas</p>
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        <small><strong>Tips:</strong> Tingkatkan kehadiran dan kumpulkan semua tugas tepat waktu untuk mendapatkan nilai terbaik.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tugas Mendatang -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-hourglass-end me-2 text-warning"></i>Tugas Mendatang
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mata Kuliah</th>
                                    <th>Judul Tugas</th>
                                    <th>Dosen</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Pemrograman Web</strong></td>
                                    <td>Project Akhir Semester</td>
                                    <td>Dr. Budi Santoso, S.Kom</td>
                                    <td>15 Jan 2025</td>
                                    <td><span class="badge bg-warning">Belum Dikumpulkan</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="fas fa-upload me-1"></i>Submit
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Basis Data</strong></td>
                                    <td>Desain Database UML</td>
                                    <td>Hendra Wijaya, S.Kom, M.T.</td>
                                    <td>18 Jan 2025</td>
                                    <td><span class="badge bg-warning">Belum Dikumpulkan</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="fas fa-upload me-1"></i>Submit
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Algoritma</strong></td>
                                    <td>Analisis Kompleksitas</td>
                                    <td>Prof. Ir. Bambang Riyanto</td>
                                    <td>10 Jan 2025</td>
                                    <td><span class="badge bg-success">Sudah Dikumpulkan</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-check me-1"></i>Selesai
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                            <a href="../mahasiswa/materi.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-download me-2"></i>Download Materi
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="../mahasiswa/tugas.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-tasks me-2"></i>Daftar Tugas
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="../mahasiswa/nilai.php" class="btn btn-outline-warning w-100">
                                <i class="fas fa-chart-line me-2"></i>Lihat Nilai
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary w-100">
                                <i class="fas fa-file-pdf me-2"></i>Cetak Transkrip
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
            labels: ['Pemrog. Web', 'Basis Data', 'Algoritma', 'Sistem Operasi', 'Jaringan'],
            datasets: [{
                label: 'Nilai',
                data: [95, 92, 88, 85, 90],
                backgroundColor: [
                    '#198754',
                    '#198754',
                    '#0dcaf0',
                    '#0dcaf0',
                    '#198754'
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
                data: [<?php echo $kehadiran; ?>, <?php echo 100 - $kehadiran; ?>],
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

    // Progress Gauge - Tugas
    const ctxTugas = document.getElementById('tugasChart').getContext('2d');
    new Chart(ctxTugas, {
        type: 'doughnut',
        data: {
            labels: ['Dikumpulkan', 'Belum'],
            datasets: [{
                data: [<?php echo $tugas_dikumpulkan; ?>, <?php echo $total_tugas - $tugas_dikumpulkan; ?>],
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
