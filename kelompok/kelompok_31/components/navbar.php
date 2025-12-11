<?php
/**
 * Navbar Template
 * Dikerjakan oleh: Anggota 2
 * 
 * Navbar dinamis sesuai role (Admin/Dosen/Mahasiswa)
 */
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$current_nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'User';
$role_display = [
    'admin' => 'Administrator',
    'dosen' => 'Dosen',
    'mahasiswa' => 'Mahasiswa'
];
$role_title = isset($role_display[$current_role]) ? $role_display[$current_role] : 'User';
?>
<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="fas fa-graduation-cap me-2"></i>EduPortal
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if ($current_role == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard/admin.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/mata_kuliah.php">
                            <i class="fas fa-book me-1"></i>Mata Kuliah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/pengumuman.php">
                            <i class="fas fa-bullhorn me-1"></i>Pengumuman
                        </a>
                    </li>
                <?php elseif ($current_role == 'dosen'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard/dosen.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../dosen/upload_materi.php">
                            <i class="fas fa-upload me-1"></i>Upload Materi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../dosen/buat_tugas.php">
                            <i class="fas fa-tasks me-1"></i>Buat Tugas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../dosen/input_nilai.php">
                            <i class="fas fa-edit me-1"></i>Input Nilai
                        </a>
                    </li>
                <?php elseif ($current_role == 'mahasiswa'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard/mahasiswa.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../mahasiswa/materi.php">
                            <i class="fas fa-download me-1"></i>Materi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../mahasiswa/tugas.php">
                            <i class="fas fa-tasks me-1"></i>Tugas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../mahasiswa/nilai.php">
                            <i class="fas fa-chart-line me-1"></i>Nilai
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown" style="position: relative; z-index: 1050;">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer; pointer-events: auto;">
                        <i class="fas fa-user-circle me-1"></i>
                        <strong><?php echo htmlspecialchars($current_nama); ?></strong>
                        <small class="text-light ms-2">(<?php echo $role_title; ?>)</small>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" style="z-index: 1051;">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
