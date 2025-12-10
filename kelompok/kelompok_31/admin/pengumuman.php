<?php
/**
 * CRUD Pengumuman
 * Dikerjakan oleh: Anggota 4
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Manajemen Pengumuman";
include '../components/header.php';
include '../components/navbar.php';
?>

<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-bullhorn me-2"></i>Manajemen Pengumuman</h2>
                <p class="mb-0 text-light">Kelola pengumuman untuk seluruh pengguna sistem</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="fas fa-plus-circle me-2"></i>Tambah Pengumuman
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Memuat data...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="text-center py-5" style="display: none;">
        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Belum ada pengumuman</h5>
        <p class="text-muted">Klik tombol "Tambah Pengumuman" untuk membuat pengumuman pertama</p>
    </div>

    <!-- Daftar Pengumuman -->
    <div id="daftarPengumuman" class="row g-4">
        <!-- Data akan di-load via AJAX -->
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Pengumuman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPengumuman">
                    <input type="hidden" id="pengumumanId" name="id">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Pengumuman <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="judul" name="judul" required maxlength="200" placeholder="Masukkan judul pengumuman">
                        <small class="text-muted">Maksimal 200 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label for="isi" class="form-label">Isi Pengumuman <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="isi" name="isi" rows="6" required placeholder="Masukkan isi pengumuman"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Pengumuman akan langsung terlihat oleh semua pengguna setelah dibuat.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpan">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const API = '../api/pengumuman_crud.php';
let editMode = false;

// Load data saat halaman dimuat
$(document).ready(function() {
    loadData();
    
    // Handle form submit
    $('#btnSimpan').click(function() {
        savePengumuman();
    });
    
    // Reset form saat modal ditutup
    $('#tambahModal').on('hidden.bs.modal', function() {
        resetForm();
    });
});

// Load data pengumuman
function loadData() {
    $('#loadingSpinner').show();
    $('#daftarPengumuman').html('');
    $('#emptyState').hide();
    
    $.ajax({
        url: API,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#loadingSpinner').hide();
            
            if (response.status === 'success' && response.data.length > 0) {
                renderPengumuman(response.data);
            } else {
                $('#emptyState').show();
            }
        },
        error: function(xhr) {
            $('#loadingSpinner').hide();
            let errorMsg = 'Gagal memuat data';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.message || errorMsg;
            } catch(e) {}
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg
            });
        }
    });
}

// Render daftar pengumuman
function renderPengumuman(data) {
    let html = '';
    
    data.forEach(function(item) {
        const createdDate = new Date(item.created_at).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        html += `
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">${escapeHtml(item.judul)}</h6>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>${escapeHtml(item.created_by_name)}
                            </small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="editPengumuman(${item.id}); return false;">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deletePengumuman(${item.id}); return false;">
                                    <i class="fas fa-trash me-2"></i>Hapus
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted" style="max-height: 100px; overflow: hidden; text-overflow: ellipsis;">
                            ${escapeHtml(item.isi)}
                        </p>
                    </div>
                    <div class="card-footer bg-white text-muted small">
                        <i class="fas fa-calendar me-1"></i>${createdDate}
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#daftarPengumuman').html(html);
}

// Edit pengumuman
function editPengumuman(id) {
    editMode = true;
    $('#modalTitle').text('Edit Pengumuman');
    
    $.ajax({
        url: API + '?id=' + id,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data) {
                const item = response.data;
                $('#pengumumanId').val(item.id);
                $('#judul').val(item.judul);
                $('#isi').val(item.isi);
                $('#tambahModal').modal('show');
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat data pengumuman'
            });
        }
    });
}

// Save pengumuman (create/update)
function savePengumuman() {
    const formData = {
        id: $('#pengumumanId').val(),
        judul: $('#judul').val().trim(),
        isi: $('#isi').val().trim()
    };
    
    // Validasi
    if (!formData.judul || !formData.isi) {
        Swal.fire({
            icon: 'warning',
            title: 'Validasi',
            text: 'Judul dan isi pengumuman wajib diisi'
        });
        return;
    }
    
    const method = editMode ? 'PUT' : 'POST';
    const btn = $('#btnSimpan');
    const originalText = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
    
    $.ajax({
        url: API,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                $('#tambahModal').modal('hide');
                loadData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Gagal menyimpan pengumuman'
                });
            }
        },
        error: function(xhr) {
            let errorMsg = 'Gagal menyimpan pengumuman';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.message || errorMsg;
            } catch(e) {}
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg
            });
        },
        complete: function() {
            btn.prop('disabled', false).html(originalText);
        }
    });
}

// Delete pengumuman
function deletePengumuman(id) {
    Swal.fire({
        title: 'Hapus Pengumuman?',
        text: 'Pengumuman yang dihapus tidak dapat dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: API,
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({id: id}),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dihapus',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        loadData();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Gagal menghapus pengumuman'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Gagal menghapus pengumuman';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch(e) {}
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            });
        }
    });
}

// Reset form
function resetForm() {
    editMode = false;
    $('#modalTitle').text('Tambah Pengumuman');
    $('#formPengumuman')[0].reset();
    $('#pengumumanId').val('');
}

// Escape HTML untuk prevent XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>

<?php include '../components/footer.php'; ?>
