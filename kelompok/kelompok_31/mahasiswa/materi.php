<?php
/**
 * Materi Perkuliahan
 * Dikerjakan oleh: Anggota 3
 */
session_start();
$page_title = "Materi Perkuliahan";
include '../components/header.php';
include '../components/navbar.php';
?>

<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-book me-2"></i>Materi Perkuliahan</h2>
                <p class="mb-0 text-light">Akses semua sumber daya pembelajaran Anda</p>
            </div>
            <div class="col-md-4">
                <input type="text" id="search" class="form-control" placeholder="Cari judul materi...">
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <div class="row row-cols-1 row-cols-md-3 g-4" id="gridMateri">
        </div>
    
    <div id="empty" class="text-center py-5 d-none">
        <h5 class="fw-bold text-muted">Materi tidak ditemukan</h5>
    </div>
</div>

<script>
fetch('../api/upload_materi.php').then(r=>r.json()).then(res => {
    let h = '';
    res.data.forEach(i => {
        let date = new Date(i.uploaded_at).toLocaleDateString('id-ID', { day:'numeric', month:'long' });
        
        h += `<div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded text-primary"><i class="fas fa-file-alt fa-2x"></i></div>
                        <span class="badge bg-light text-dark border">${i.nama_mk}</span>
                    </div>
                    
                    <h5 class="fw-bold text-dark mb-2">${i.judul}</h5>
                    <p class="text-muted small mb-3" style="min-height: 40px;">${i.deskripsi || 'Tidak ada deskripsi.'}</p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <small class="text-muted"><i class="fas fa-user-circle me-1"></i> ${i.nama_dosen || 'Dosen'}</small>
                        <a href="../uploads/materi/${i.file_path}" class="btn btn-outline-primary btn-sm rounded-pill px-3" download>
                            Download <i class="fas fa-download ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>`;
    });
    document.getElementById('gridMateri').innerHTML = h;
});

document.getElementById('search').addEventListener('keyup', function(){
    let v = this.value.toLowerCase();
    document.querySelectorAll('#gridMateri .col').forEach(c => c.style.display = c.textContent.toLowerCase().includes(v)?'':'none');
});
</script>

<?php include '../components/footer.php'; ?>