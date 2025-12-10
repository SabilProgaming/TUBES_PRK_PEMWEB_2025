<?php
/**
 * CRUD Mata Kuliah
 * Dikerjakan oleh: Anggota 2
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Manajemen Mata Kuliah";
include '../components/header.php';
include '../components/navbar.php';

// Variabel untuk mode form
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'view';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Koneksi database menggunakan Database class
require_once '../config/database.php';
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    die('Error: Koneksi database gagal');
}

// Ambil data mata kuliah dari database
$mata_kuliah_list = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM mata_kuliah ORDER BY kode ASC");
    $stmt->execute();
    $mata_kuliah_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mata_kuliah_list = [];
}

// Cari mata kuliah berdasarkan ID untuk edit
$current_mk = null;
if ($mode == 'edit' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE id = ?");
        $stmt->execute([$id]);
        $current_mk = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $current_mk = null;
    }
}
?>

<!-- Alert Messages -->
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo ($_SESSION['message_type'] == 'success') ? 'success' : ($_SESSION['message_type'] == 'error' ? 'danger' : 'info'); ?> alert-dismissible fade show mt-3" role="alert" style="margin-top: 20px;">
        <i class="fas fa-<?php echo ($_SESSION['message_type'] == 'success') ? 'check-circle' : ($_SESSION['message_type'] == 'error' ? 'exclamation-circle' : 'info-circle'); ?> me-2"></i>
        <?php echo htmlspecialchars($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
    unset($_SESSION['message']); 
    unset($_SESSION['message_type']); 
    ?>
<?php endif; ?>

<!-- Dashboard Header -->
<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">
                    <i class="fas fa-book me-2"></i>
                    <?php echo ($mode == 'add') ? 'Tambah Mata Kuliah Baru' : (($mode == 'edit') ? 'Edit Mata Kuliah' : 'Kelola Mata Kuliah'); ?>
                </h2>
                <p class="mb-0 text-light">Manajemen data mata kuliah di sistem EduPortal</p>
            </div>
            <?php if ($mode == 'view'): ?>
            <div class="col-md-4 text-end">
                <a href="?mode=add" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i>Tambah Mata Kuliah
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mt-4 mb-5">
    <?php if ($mode == 'view'): ?>
        <!-- View Mode - Tabel Daftar Mata Kuliah -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-0">
                                    <i class="fas fa-list me-2"></i>Daftar Mata Kuliah
                                </h5>
                            </div>
                            <div class="col-md-4 text-end">
                                <input type="text" class="form-control form-control-sm" placeholder="Cari mata kuliah..." id="searchInput" style="max-width: 250px;">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="mataKuliahTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Mata Kuliah</th>
                                        <th>SKS</th>
                                        <th>Dosen</th>
                                        <th>Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($mata_kuliah_list)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-inbox me-2"></i>Belum ada data mata kuliah
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($mata_kuliah_list as $mk): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($mk['kode']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($mk['nama']); ?></td>
                                        <td><span class="badge bg-info"><?php echo $mk['sks']; ?> SKS</span></td>
                                        <td><?php echo (!empty($mk['dosen_id'])) ? 'Dosen ID: ' . $mk['dosen_id'] : '<span class="text-muted">-</span>'; ?></td>
                                        <td><?php echo date('d M Y', strtotime($mk['created_at'])); ?></td>
                                        <td>
                                            <a href="?mode=edit&id=<?php echo $mk['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $mk['id']; ?>)" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="text-primary mb-1"><?php echo count($mata_kuliah_list); ?></h2>
                        <p class="text-muted">Total Mata Kuliah</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="text-info mb-1">5</h2>
                        <p class="text-muted">Dosen Pengampu</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="text-success mb-1">16</h2>
                        <p class="text-muted">Total SKS</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="text-warning mb-1">2024/2025</h2>
                        <p class="text-muted">Tahun Ajaran Aktif</p>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($mode == 'add' || $mode == 'edit'): ?>
        <!-- Add/Edit Mode - Form -->
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo ($mode == 'add') ? 'plus' : 'edit'; ?> me-2"></i>
                            <?php echo ($mode == 'add') ? 'Tambah Mata Kuliah Baru' : 'Edit Data Mata Kuliah'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="../api/mata_kuliah_crud.php" class="needs-validation">
                            <input type="hidden" name="action" value="<?php echo ($mode == 'add') ? 'create' : 'update'; ?>">
                            <?php if ($mode == 'edit' && $current_mk): ?>
                            <input type="hidden" name="id" value="<?php echo $current_mk['id']; ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kode" class="form-label">Kode Mata Kuliah <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="kode" name="kode" placeholder="Contoh: PW001" required
                                           value="<?php echo $current_mk ? htmlspecialchars($current_mk['kode']) : ''; ?>">
                                    <small class="text-muted">Kode unik untuk setiap mata kuliah</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama Mata Kuliah <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: Pemrograman Web" required
                                           value="<?php echo $current_mk ? htmlspecialchars($current_mk['nama']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="sks" class="form-label">SKS (Satuan Kredit Semester) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="sks" name="sks" min="1" max="6" required
                                           value="<?php echo $current_mk ? $current_mk['sks'] : ''; ?>">
                                </div>
                            </div>

                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <small><strong>Catatan:</strong> Semua field yang ditandai dengan (*) wajib diisi. Data akan disimpan ke database setelah submit.</small>
                            </div>

                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <a href="mata_kuliah.php" class="btn btn-secondary" type="button">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i><?php echo ($mode == 'add') ? 'Simpan Baru' : 'Update'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')) {
        window.location.href = '../api/mata_kuliah_crud.php?action=delete&id=' + id;
    }
}

// Search function
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    let searchValue = this.value.toLowerCase();
    let rows = document.querySelectorAll('#mataKuliahTable tbody tr');
    
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});
</script>

<?php include '../components/footer.php'; ?>
