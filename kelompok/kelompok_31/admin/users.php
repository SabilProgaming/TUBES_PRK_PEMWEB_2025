<?php
/**
 * CRUD Users
 * Manajemen User untuk Admin
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Manajemen User";
include '../components/header.php';
include '../components/navbar.php';

// Koneksi database
require_once '../config/database.php';
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    die('Error: Koneksi database gagal');
}

// Ambil data users dari database
$users_list = [];
try {
    $stmt = $pdo->prepare("SELECT id, username, nama, role, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users_list = [];
}

// Hitung statistik
$total_users = count($users_list);
$total_admin = count(array_filter($users_list, fn($u) => $u['role'] == 'admin'));
$total_dosen = count(array_filter($users_list, fn($u) => $u['role'] == 'dosen'));
$total_mahasiswa = count(array_filter($users_list, fn($u) => $u['role'] == 'mahasiswa'));
?>

<!-- Alert Messages -->
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo ($_SESSION['message_type'] == 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show mt-3" role="alert" style="margin-top: 20px;">
        <i class="fas fa-<?php echo ($_SESSION['message_type'] == 'success') ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
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
                    <i class="fas fa-users me-2"></i>Manajemen User
                </h2>
                <p class="mb-0 text-light">Kelola data pengguna sistem EduPortal</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-1"></i>Tambah User Baru
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mt-4 mb-5">
    <!-- Statistik Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h2 class="text-primary mb-1"><?php echo $total_users; ?></h2>
                    <p class="text-muted mb-0">Total User</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h2 class="text-danger mb-1"><?php echo $total_admin; ?></h2>
                    <p class="text-muted mb-0">Admin</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h2 class="text-info mb-1"><?php echo $total_dosen; ?></h2>
                    <p class="text-muted mb-0">Dosen</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h2 class="text-success mb-1"><?php echo $total_mahasiswa; ?></h2>
                    <p class="text-muted mb-0">Mahasiswa</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar User -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Daftar User
                            </h5>
                        </div>
                        <div class="col-md-4 text-end">
                            <input type="text" class="form-control form-control-sm" placeholder="Cari user..." id="searchInput" style="max-width: 250px;">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Role</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-inbox me-2"></i>Belum ada data user
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($users_list as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['nama']); ?></td>
                                    <td>
                                        <?php
                                        $role_badges = [
                                            'admin' => 'danger',
                                            'dosen' => 'info',
                                            'mahasiswa' => 'success'
                                        ];
                                        $role_names = [
                                            'admin' => 'Admin',
                                            'dosen' => 'Dosen',
                                            'mahasiswa' => 'Mahasiswa'
                                        ];
                                        $badge = $role_badges[$user['role']] ?? 'secondary';
                                        $name = $role_names[$user['role']] ?? ucfirst($user['role']);
                                        ?>
                                        <span class="badge bg-<?php echo $badge; ?>"><?php echo $name; ?></span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')" title="Hapus">
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
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Tambah User Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../api/users_crud.php" id="addUserForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="Masukkan username">
                        <small class="text-muted">Username harus unik dan tidak boleh ada spasi</small>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password">
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama lengkap">
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="dosen">Dosen</option>
                            <option value="mahasiswa">Mahasiswa</option>
                        </select>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Semua field yang ditandai (*) wajib diisi.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, username) {
    if (confirm('Apakah Anda yakin ingin menghapus user "' + username + '"?\n\nTindakan ini tidak dapat dibatalkan!')) {
        window.location.href = '../api/users_crud.php?action=delete&id=' + id;
    }
}

// Search function
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    let searchValue = this.value.toLowerCase();
    let rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});

// Handle form submission dengan AJAX
document.getElementById('addUserForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    
    fetch('../api/users_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
            modal.hide();
            
            // Reload page
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Gagal menambah user'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan. Silakan coba lagi.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>

<?php include '../components/footer.php'; ?>

