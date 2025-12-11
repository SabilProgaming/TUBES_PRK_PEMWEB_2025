<?php
/**
 * Daftar Nilai
 * Dikerjakan oleh: Anggota 4
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Nilai Saya";
include '../components/header.php';
include '../components/navbar.php';
?>

<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-chart-line me-2"></i>Nilai Saya</h2>
                <p class="mb-0 text-light">Lihat nilai dan feedback untuk tugas yang sudah Anda submit</p>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <!-- Statistik -->
    <div class="row g-4 mb-4" id="statistikContainer">
        <!-- Statistik akan di-load via AJAX -->
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
        <h5 class="text-muted">Belum ada nilai</h5>
        <p class="text-muted">Anda belum memiliki nilai untuk tugas yang sudah di-submit</p>
    </div>

    <!-- Daftar Nilai -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Nilai</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tugas</th>
                            <th>Mata Kuliah</th>
                            <th>Tanggal Submit</th>
                            <th>Nilai</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="daftarNilai">
                        <!-- Data akan di-load via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Nilai -->
<div class="modal fade" id="detailNilaiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Nilai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Tugas:</strong>
                    <p id="detailJudul" class="mb-0"></p>
                </div>
                <div class="mb-3">
                    <strong>Mata Kuliah:</strong>
                    <p id="detailMataKuliah" class="mb-0"></p>
                </div>
                <div class="mb-3">
                    <strong>Tanggal Submit:</strong>
                    <p id="detailTanggal" class="mb-0"></p>
                </div>
                <div class="mb-3">
                    <strong>File Submission:</strong>
                    <p id="detailFile" class="mb-0"></p>
                </div>
                <div class="mb-3">
                    <strong>Nilai:</strong>
                    <h4 id="detailNilai" class="text-primary mb-0"></h4>
                </div>
                <div class="mb-3">
                    <strong>Feedback:</strong>
                    <div id="detailFeedback" class="alert alert-info mb-0"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const API = '../api/nilai.php';

// Auto-load data saat halaman dibuka
document.addEventListener('DOMContentLoaded', function() {
    loadData();
});

// Juga support jQuery jika sudah ada
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        loadData();
    });
}

// Load data nilai
function loadData() {
    const loadingSpinner = document.getElementById('loadingSpinner');
    const daftarNilai = document.getElementById('daftarNilai');
    const emptyState = document.getElementById('emptyState');
    const statistikContainer = document.getElementById('statistikContainer');
    
    if (loadingSpinner) loadingSpinner.style.display = 'block';
    if (daftarNilai) daftarNilai.innerHTML = '';
    if (emptyState) emptyState.style.display = 'none';
    if (statistikContainer) statistikContainer.innerHTML = '';
    
    fetch(API)
        .then(response => response.json())
        .then(data => {
            if (loadingSpinner) loadingSpinner.style.display = 'none';
            
            console.log('Response dari API Nilai:', data);
            
            if (data.status === 'success') {
                if (data.data && data.data.length > 0) {
                    console.log('Data nilai ditemukan:', data.data.length, 'item');
                    renderStatistik(data.data);
                    renderNilai(data.data);
                } else {
                    console.log('Tidak ada data nilai');
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

// Render statistik
function renderStatistik(data) {
    const totalTugas = data.length;
    const tugasDinilai = data.filter(item => item.nilai !== null).length;
    const tugasBelumDinilai = totalTugas - tugasDinilai;
    
    let totalNilai = 0;
    let countNilai = 0;
    data.forEach(item => {
        if (item.nilai !== null) {
            totalNilai += parseFloat(item.nilai);
            countNilai++;
        }
    });
    const rataRata = countNilai > 0 ? (totalNilai / countNilai).toFixed(2) : 0;
    
    const html = `
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-primary mb-0">${totalTugas}</h3>
                    <p class="text-muted mb-0">Total Tugas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-success mb-0">${tugasDinilai}</h3>
                    <p class="text-muted mb-0">Sudah Dinilai</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-warning mb-0">${tugasBelumDinilai}</h3>
                    <p class="text-muted mb-0">Belum Dinilai</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-info mb-0">${rataRata}</h3>
                    <p class="text-muted mb-0">Rata-rata Nilai</p>
                </div>
            </div>
        </div>
    `;
    
    const statistikContainer = document.getElementById('statistikContainer');
    if (statistikContainer) {
        statistikContainer.innerHTML = html;
    }
}

// Render daftar nilai
function renderNilai(data) {
    let html = '';
    
    if (data.length === 0) {
        html = '<tr><td colspan="6" class="text-center text-muted py-4">Belum ada data nilai</td></tr>';
    } else {
        data.forEach(function(item) {
            const submittedDate = new Date(item.submitted_at).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            const nilaiDisplay = item.nilai !== null ? 
                `<span class="badge bg-primary">${item.nilai}</span>` : 
                '<span class="badge bg-secondary">Belum dinilai</span>';
            
            const statusBadge = item.nilai !== null ? 
                '<span class="badge bg-success">Sudah Dinilai</span>' : 
                '<span class="badge bg-warning">Belum Dinilai</span>';
            
            html += `
                <tr>
                    <td class="ps-3">
                        <strong>${escapeHtml(item.judul_tugas)}</strong>
                    </td>
                    <td>${escapeHtml(item.nama_mk)}</td>
                    <td>${submittedDate}</td>
                    <td>${nilaiDisplay}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end pe-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="showDetail(${item.id}, '${escapeHtml(item.judul_tugas)}', '${escapeHtml(item.nama_mk)}', '${item.submitted_at}', '${escapeHtml(item.file_name)}', '${escapeHtml(item.file_path)}', ${item.nilai ? item.nilai : 'null'}, '${escapeHtml(item.feedback || '')}')">
                            <i class="fas fa-eye me-1"></i>Detail
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    const daftarNilai = document.getElementById('daftarNilai');
    if (daftarNilai) {
        daftarNilai.innerHTML = html;
    }
}

// Show detail nilai
function showDetail(id, judul, mk, tanggal, fileName, filePath, nilai, feedback) {
    const tanggalFormatted = new Date(tanggal).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    document.getElementById('detailJudul').textContent = judul;
    document.getElementById('detailMataKuliah').textContent = mk;
    document.getElementById('detailTanggal').textContent = tanggalFormatted;
    document.getElementById('detailFile').innerHTML = `
        <a href="../uploads/tugas/${escapeHtml(filePath)}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-download me-1"></i>${escapeHtml(fileName)}
        </a>
    `;
    
    if (nilai !== null && nilai !== 'null') {
        document.getElementById('detailNilai').textContent = nilai;
        document.getElementById('detailFeedback').innerHTML = feedback || '<em class="text-muted">Tidak ada feedback</em>';
    } else {
        document.getElementById('detailNilai').innerHTML = '<span class="badge bg-secondary">Belum dinilai</span>';
        document.getElementById('detailFeedback').innerHTML = '<em class="text-muted">Nilai belum diberikan oleh dosen</em>';
    }
    
    // Show modal menggunakan Bootstrap
    const modal = new bootstrap.Modal(document.getElementById('detailNilaiModal'));
    modal.show();
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
