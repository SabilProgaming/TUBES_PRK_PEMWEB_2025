<?php
/**
 * Daftar Mata Kuliah Mahasiswa
 * Dikerjakan oleh: Anggota 2
 * 
 * Menampilkan semua mata kuliah yang tersedia dan yang sudah diambil
 */

session_start();

// Check if logged in and is mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Mata Kuliah";
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

// Ambil semua mata kuliah
$all_mata_kuliah = [];
$my_mata_kuliah = [];

try {
    // Ambil semester mahasiswa dari session user
    $stmt = $pdo->prepare("SELECT semester FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $semester_mahasiswa = $user['semester'] ?? 1;
    
    // Ambil semua mata kuliah sesuai semester mahasiswa
    $stmt = $pdo->prepare("SELECT id, kode, nama, sks, dosen_id, semester FROM mata_kuliah WHERE semester = ? ORDER BY kode");
    $stmt->execute([$semester_mahasiswa]);
    $all_mata_kuliah = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil mata kuliah yang sudah diambil mahasiswa
    $stmt = $pdo->prepare("SELECT mata_kuliah_id FROM enrollment WHERE mahasiswa_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $enrolled = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $enrolled_ids = array_column($enrolled, 'mata_kuliah_id');
    
} catch (PDOException $e) {
    $all_mata_kuliah = [];
}
?>

<!-- Dashboard Header -->
<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">
                    <i class="fas fa-book me-2"></i>Daftar Mata Kuliah
                </h2>
                <p class="mb-0 text-light">Semester <?php echo $semester_mahasiswa; ?> - Pilih dan bergabung dengan mata kuliah yang Anda inginkan</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mt-4 mb-5">
    <!-- Mata Kuliah yang Sudah Diambil -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2 text-success"></i>Mata Kuliah yang Sudah Diambil
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Mata Kuliah</th>
                                    <th>SKS</th>
                                    <th>Semester</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($enrolled_ids) > 0): ?>
                                    <?php foreach ($all_mata_kuliah as $mk): ?>
                                        <?php if (in_array($mk['id'], $enrolled_ids)): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($mk['kode']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($mk['nama']); ?></td>
                                            <td><span class="badge bg-info"><?php echo $mk['sks']; ?> SKS</span></td>
                                            <td><span class="badge bg-primary">Semester <?php echo $mk['semester']; ?></span></td>
                                            <td>
                                                <a href="../mahasiswa/materi.php" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-folder-open me-1"></i>Akses
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Anda belum mengambil mata kuliah apapun</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mata Kuliah Tersedia -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2 text-warning"></i>Mata Kuliah Tersedia untuk Bergabung
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($all_mata_kuliah as $mk): ?>
                            <?php if (!in_array($mk['id'], $enrolled_ids)): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border-info">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($mk['kode']); ?></h6>
                                        <p class="card-text small text-muted"><?php echo htmlspecialchars($mk['nama']); ?></p>
                                        <p class="card-text small"><i class="fas fa-book me-1"></i><?php echo $mk['sks']; ?> SKS</p>
                                        <p class="card-text small"><i class="fas fa-graduation-cap me-1"></i>Semester <?php echo $mk['semester']; ?></p>
                                        <button class="btn btn-sm btn-success w-100" onclick="bergabungKelas(<?php echo $mk['id']; ?>, '<?php echo htmlspecialchars($mk['kode']); ?>')">
                                            <i class="fas fa-plus me-1"></i>Bergabung
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function bergabungKelas(kelasId, kodesKelas) {
    if (confirm('Apakah Anda yakin ingin bergabung ke kelas ' + kodesKelas + '?')) {
        $.ajax({
            url: '../api/enrollment.php',
            method: 'POST',
            data: {
                action: 'join',
                mata_kuliah_id: kelasId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat bergabung ke kelas');
            }
        });
    }
}
</script>

<?php include '../components/footer.php'; ?>
