<?php
/**
 * Input Nilai
 * Dikerjakan oleh: Anggota 4
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'dosen') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$dosen_id = $_SESSION['user_id'];
$database = new Database();
$pdo = $database->getConnection();

// Get mata kuliah yang diampu dosen
$mata_kuliah_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, kode, nama FROM mata_kuliah WHERE dosen_id = ? ORDER BY kode ASC");
        $stmt->execute([$dosen_id]);
        $mata_kuliah_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error loading mata kuliah: " . $e->getMessage());
    }
}

$page_title = "Input Nilai";
include '../components/header.php';
include '../components/navbar.php';
?>

<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-edit me-2"></i>Input Nilai Tugas</h2>
                <p class="mb-0 text-light">Berikan nilai dan feedback untuk tugas mahasiswa</p>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <!-- Filter Mata Kuliah -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Mata Kuliah</label>
                    <select class="form-select" id="filterMataKuliah">
                        <option value="">Semua Mata Kuliah</option>
                        <?php foreach ($mata_kuliah_list as $mk): ?>
                            <option value="<?php echo $mk['id']; ?>">
                                <?php echo htmlspecialchars($mk['kode'] . ' - ' . $mk['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="belum">Belum Dinilai</option>
                        <option value="sudah">Sudah Dinilai</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary w-100" onclick="loadData()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Memuat data...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="text-center py-5" style="display: none;">
        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Belum ada tugas yang perlu dinilai</h5>
        <p class="text-muted">Tidak ada submission tugas dari mahasiswa untuk mata kuliah yang Anda ampu</p>
    </div>

    <!-- Daftar Submission -->
    <div id="daftarSubmission" class="row g-4">
        <!-- Data akan di-load via AJAX -->
    </div>
</div>

<!-- Modal Input Nilai -->
<div class="modal fade" id="inputNilaiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Nilai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNilai">
                    <input type="hidden" id="submissionId" name="submission_id">
                    <div class="mb-3">
                        <label class="form-label">Mahasiswa</label>
                        <input type="text" class="form-control" id="mahasiswaNama" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tugas</label>
                        <input type="text" class="form-control" id="tugasJudul" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Submission</label>
                        <div id="fileSubmission"></div>
                    </div>
                    <div class="mb-3">
                        <label for="nilai" class="form-label">Nilai (0-100) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="nilai" name="nilai" min="0" max="100" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="4" placeholder="Berikan feedback untuk mahasiswa..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveNilai()">
                    <i class="fas fa-save me-2"></i>Simpan Nilai
                </button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const API = '../api/input_nilai.php';

// Auto-load data saat halaman dibuka
document.addEventListener('DOMContentLoaded', function() {
    loadData();
    
    // Event listener untuk filter
    document.getElementById('filterMataKuliah').addEventListener('change', function() {
        loadData();
    });
    
    document.getElementById('filterStatus').addEventListener('change', function() {
        loadData();
    });
});

// Juga support jQuery jika sudah ada
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        loadData();
        
        $('#filterMataKuliah, #filterStatus').on('change', function() {
            loadData();
        });
    });
}

// Load data submission
function loadData() {
    const mkId = document.getElementById('filterMataKuliah') ? document.getElementById('filterMataKuliah').value : '';
    const status = document.getElementById('filterStatus') ? document.getElementById('filterStatus').value : '';
    
    const loadingSpinner = document.getElementById('loadingSpinner');
    const daftarSubmission = document.getElementById('daftarSubmission');
    const emptyState = document.getElementById('emptyState');
    
    if (loadingSpinner) loadingSpinner.style.display = 'block';
    if (daftarSubmission) daftarSubmission.innerHTML = '';
    if (emptyState) emptyState.style.display = 'none';
    
    // Build URL dengan query parameters
    let url = API + '?';
    if (mkId) url += 'mata_kuliah_id=' + encodeURIComponent(mkId) + '&';
    if (status) url += 'status=' + encodeURIComponent(status) + '&';
    url = url.replace(/[&?]$/, ''); // Remove trailing & or ?
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (loadingSpinner) loadingSpinner.style.display = 'none';
            
            console.log('Response dari API:', data);
            
            if (data.status === 'success') {
                if (data.data && data.data.length > 0) {
                    console.log('Data submission ditemukan:', data.data.length, 'item');
                    renderSubmission(data.data);
                } else {
                    console.log('Tidak ada data submission');
                    if (emptyState) emptyState.style.display = 'block';
                }
            } else {
                console.error('Response error:', data);
                if (emptyState) emptyState.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (loadingSpinner) loadingSpinner.style.display = 'none';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat data: ' + error.message
                });
            } else {
                alert('Gagal memuat data: ' + error.message);
            }
        });
}

// Render daftar submission
function renderSubmission(data) {
    let html = '';
    
    data.forEach(function(item) {
        const submittedDate = new Date(item.submitted_at).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const deadlineDate = new Date(item.deadline);
        const isLate = new Date(item.submitted_at) > deadlineDate;
        const statusBadge = item.nilai !== null ? 
            '<span class="badge bg-success">Sudah Dinilai</span>' : 
            '<span class="badge bg-warning">Belum Dinilai</span>';
        
        html += `
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold">${escapeHtml(item.judul_tugas)}</h6>
                            <small class="text-muted">
                                <i class="fas fa-book me-1"></i>${escapeHtml(item.nama_mk)}
                            </small>
                        </div>
                        ${statusBadge}
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Mahasiswa:</strong> ${escapeHtml(item.nama_mahasiswa)}<br>
                            <strong>File:</strong> 
                            <a href="../uploads/tugas/${escapeHtml(item.file_path)}" target="_blank" class="text-decoration-none">
                                <i class="fas fa-file me-1"></i>${escapeHtml(item.file_name)}
                            </a>
                        </p>
                        <p class="mb-2 small text-muted">
                            <i class="fas fa-calendar me-1"></i>Submitted: ${submittedDate}
                            ${isLate ? '<span class="badge bg-danger ms-2">Terlambat</span>' : ''}
                        </p>
                        ${item.nilai !== null ? `
                            <div class="alert alert-info mb-0">
                                <strong>Nilai:</strong> ${item.nilai}<br>
                                ${item.feedback ? `<strong>Feedback:</strong> ${escapeHtml(item.feedback)}` : ''}
                            </div>
                        ` : ''}
                    </div>
                    <div class="card-footer bg-white">
                        <button class="btn btn-sm btn-primary w-100" onclick="openInputNilai(${item.id}, '${escapeHtml(item.nama_mahasiswa)}', '${escapeHtml(item.judul_tugas)}', '${escapeHtml(item.file_name)}', '${escapeHtml(item.file_path)}', ${item.nilai ? item.nilai : 'null'}, '${escapeHtml(item.feedback || '')}')">
                            <i class="fas fa-edit me-2"></i>${item.nilai !== null ? 'Edit Nilai' : 'Input Nilai'}
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    const daftarSubmission = document.getElementById('daftarSubmission');
    if (daftarSubmission) {
        daftarSubmission.innerHTML = html;
    }
}

// Open modal input nilai
function openInputNilai(id, nama, judul, fileName, filePath, nilai, feedback) {
    document.getElementById('submissionId').value = id;
    document.getElementById('mahasiswaNama').value = nama;
    document.getElementById('tugasJudul').value = judul;
    document.getElementById('fileSubmission').innerHTML = `
        <a href="../uploads/tugas/${escapeHtml(filePath)}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-download me-1"></i>Download: ${escapeHtml(fileName)}
        </a>
    `;
    document.getElementById('nilai').value = nilai || '';
    document.getElementById('feedback').value = feedback || '';
    
    // Show modal menggunakan Bootstrap
    const modal = new bootstrap.Modal(document.getElementById('inputNilaiModal'));
    modal.show();
}

// Save nilai
function saveNilai() {
    const submissionId = document.getElementById('submissionId').value;
    const nilai = parseFloat(document.getElementById('nilai').value);
    const feedback = document.getElementById('feedback').value.trim();
    
    // Validasi
    if (!nilai || nilai < 0 || nilai > 100) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Validasi',
                text: 'Nilai harus antara 0-100'
            });
        } else {
            alert('Nilai harus antara 0-100');
        }
        return;
    }
    
    const formData = {
        submission_id: parseInt(submissionId),
        nilai: nilai,
        feedback: feedback
    };
    
    const btn = document.querySelector('button[onclick="saveNilai()"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    
    fetch(API, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                alert('Nilai berhasil disimpan');
            }
            
            // Tutup modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('inputNilaiModal'));
            if (modal) {
                modal.hide();
            }
            
            // Reload data
            loadData();
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Gagal menyimpan nilai'
                });
            } else {
                alert('Error: ' + (data.message || 'Gagal menyimpan nilai'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal menyimpan nilai: ' + error.message
            });
        } else {
            alert('Gagal menyimpan nilai: ' + error.message);
        }
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>

<?php include '../components/footer.php'; ?>
