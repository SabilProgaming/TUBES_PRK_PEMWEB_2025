<?php
/**
 * CRUD Mata Kuliah
 * Dikerjakan oleh: Anggota 2
 * 
 * Admin dapat Create, Read, Update, Delete data mata kuliah
 */

session_start();

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Kelola Mata Kuliah";
include '../components/header.php';
include '../components/navbar.php';

// Variabel untuk mode form
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'view';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Data dummy mata kuliah (akan diganti dengan database nanti)
$mata_kuliah_list = [
    [
        'id' => 1,
        'kode' => 'PW001',
        'nama' => 'Pemrograman Web',
        'sks' => 3,
        'semester' => 3,
        'dosen' => 'Dr. Budi Santoso, S.Kom',
        'tahun_ajaran' => '2024/2025'
    ],
    [
        'id' => 2,
        'kode' => 'BD002',
        'nama' => 'Basis Data',
        'sks' => 3,
        'semester' => 3,
        'dosen' => 'Hendra Wijaya, S.Kom, M.T.',
        'tahun_ajaran' => '2024/2025'
    ],
    [
        'id' => 3,
        'kode' => 'ALG003',
        'nama' => 'Algoritma dan Struktur Data',
        'sks' => 4,
        'semester' => 2,
        'dosen' => 'Prof. Ir. Bambang Riyanto',
        'tahun_ajaran' => '2024/2025'
    ],
    [
        'id' => 4,
        'kode' => 'SO004',
        'nama' => 'Sistem Operasi',
        'sks' => 3,
        'semester' => 3,
        'dosen' => 'Adi Wibowo, S.Kom',
        'tahun_ajaran' => '2024/2025'
    ],
    [
        'id' => 5,
        'kode' => 'JK005',
        'nama' => 'Jaringan Komputer',
        'sks' => 3,
        'semester' => 4,
        'dosen' => 'Rini Suryawati, S.Kom, M.T.',
        'tahun_ajaran' => '2024/2025'
    ]
];

// Cari mata kuliah berdasarkan ID untuk edit
$current_mk = null;
if ($mode == 'edit' && $id) {
    foreach ($mata_kuliah_list as $mk) {
        if ($mk['id'] == $id) {
            $current_mk = $mk;
            break;
        }
    }
}
?>

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
            <div class="col-md-4 text-end">
                <?php if ($mode == 'view'): ?>
                <a href="?mode=add" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i>Tambah Baru
                </a>
                <?php endif; ?>
            </div>
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
                                        <th>Semester</th>
                                        <th>Dosen Pengampu</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mata_kuliah_list as $mk): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($mk['kode']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($mk['nama']); ?></td>
                                        <td><span class="badge bg-info"><?php echo $mk['sks']; ?> SKS</span></td>
                                        <td><span class="badge bg-primary">Semester <?php echo $mk['semester']; ?></span></td>
                                        <td><?php echo htmlspecialchars($mk['dosen']); ?></td>
                                        <td><?php echo $mk['tahun_ajaran']; ?></td>
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
                                <div class="col-md-4 mb-3">
                                    <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                                    <select class="form-select" id="semester" name="semester" required>
                                        <option value="">-- Pilih Semester --</option>
                                        <?php for ($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($current_mk && $current_mk['semester'] == $i) ? 'selected' : ''; ?>>
                                            Semester <?php echo $i; ?>
                                        </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tahun_ajaran" name="tahun_ajaran" required>
                                        <option value="">-- Pilih Tahun --</option>
                                        <option value="2023/2024" <?php echo ($current_mk && $current_mk['tahun_ajaran'] == '2023/2024') ? 'selected' : ''; ?>>
                                            2023/2024
                                        </option>
                                        <option value="2024/2025" <?php echo ($current_mk && $current_mk['tahun_ajaran'] == '2024/2025') ? 'selected' : ''; ?>>
                                            2024/2025
                                        </option>
                                        <option value="2025/2026" <?php echo ($current_mk && $current_mk['tahun_ajaran'] == '2025/2026') ? 'selected' : ''; ?>>
                                            2025/2026
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="dosen" class="form-label">Dosen Pengampu <span class="text-danger">*</span></label>
                                <select class="form-select" id="dosen" name="dosen" required>
                                    <option value="">-- Pilih Dosen --</option>
                                    <option value="Dr. Budi Santoso, S.Kom" <?php echo ($current_mk && $current_mk['dosen'] == 'Dr. Budi Santoso, S.Kom') ? 'selected' : ''; ?>>
                                        Dr. Budi Santoso, S.Kom
                                    </option>
                                    <option value="Hendra Wijaya, S.Kom, M.T." <?php echo ($current_mk && $current_mk['dosen'] == 'Hendra Wijaya, S.Kom, M.T.') ? 'selected' : ''; ?>>
                                        Hendra Wijaya, S.Kom, M.T.
                                    </option>
                                    <option value="Prof. Ir. Bambang Riyanto" <?php echo ($current_mk && $current_mk['dosen'] == 'Prof. Ir. Bambang Riyanto') ? 'selected' : ''; ?>>
                                        Prof. Ir. Bambang Riyanto
                                    </option>
                                    <option value="Adi Wibowo, S.Kom" <?php echo ($current_mk && $current_mk['dosen'] == 'Adi Wibowo, S.Kom') ? 'selected' : ''; ?>>
                                        Adi Wibowo, S.Kom
                                    </option>
                                    <option value="Rini Suryawati, S.Kom, M.T." <?php echo ($current_mk && $current_mk['dosen'] == 'Rini Suryawati, S.Kom, M.T.') ? 'selected' : ''; ?>>
                                        Rini Suryawati, S.Kom, M.T.
                                    </option>
                                </select>
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
</script></div>

<?php include '../components/footer.php'; ?>
