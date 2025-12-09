<?php
session_start();
$page_title = "Daftar Tugas";
include '../components/header.php';
include '../components/navbar.php';
?>

<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-tasks me-2"></i>Daftar Tugas</h2>
                <p class="mb-0 text-light">Pantau deadline dan kumpulkan tugas tepat waktu</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-light btn-sm"><i class="fas fa-filter me-1"></i>Filter Tugas</button>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3 text-primary">
                        <i class="fas fa-clipboard-list fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Total Tugas</h6>
                        <h3 class="fw-bold mb-0" id="statTotal">-</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3 text-warning">
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Pending</h6>
                        <h3 class="fw-bold mb-0 text-dark" id="statPending">-</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-start border-success border-3">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3 text-success">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Selesai</h6>
                        <h3 class="fw-bold mb-0 text-success" id="statDone">-</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0 text-primary"><i class="fas fa-list me-2"></i>List Tugas Kuliah</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Mata Kuliah</th>
                            <th>Detail</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyTugas">
                        <tr><td colspan="5" class="text-center py-5">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSubmit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Jawaban</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formSubmitTugas">
                    <input type="hidden" name="tugas_id" id="tugasId">
                    <div class="mb-3 text-center">
                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                        <p class="text-muted">Pilih file jawaban (PDF/DOCX)</p>
                    </div>
                    <input type="file" class="form-control mb-3" name="file" required>
                    <button type="submit" class="btn btn-primary w-100">Kirim Jawaban</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function loadTugas() {
    fetch('../api/submit_tugas.php?t='+Date.now()).then(r=>r.json()).then(res => {
        let total = res.data.length, done = res.data.filter(i => i.status_kumpul === 'submitted').length;
        document.getElementById('statTotal').innerText = total; document.getElementById('statDone').innerText = done; document.getElementById('statPending').innerText = total - done;

        let h = '';
        res.data.forEach(i => {
            let status = `<span class="badge bg-warning text-dark">Pending</span>`, btn = `<button onclick="bukaModal(${i.id})" class="btn btn-primary btn-sm px-3">Upload</button>`;
            if(i.status_kumpul == 'submitted') { status = `<span class="badge bg-success">Terkirim</span>`; btn = `<button onclick="bukaModal(${i.id})" class="btn btn-outline-success btn-sm px-3">Update</button>`; }
            
            h += `<tr>
                <td class="ps-4 fw-bold text-dark">${i.nama_mk}</td>
                <td><div class="fw-bold text-dark">${i.judul}</div><small class="text-muted">${i.deskripsi}</small></td>
                <td><span class="text-danger small fw-bold">${i.deadline.replace('T', ' ')}</span></td>
                <td>${status}</td>
                <td class="text-end pe-4">${btn}</td>
            </tr>`;
        });
        document.querySelector('#tbodyTugas').innerHTML = h;
    });
}
function bukaModal(id){ document.getElementById('tugasId').value=id; new bootstrap.Modal(document.getElementById('modalSubmit')).show(); }
document.getElementById('formSubmitTugas').addEventListener('submit', function(e){
    e.preventDefault(); fetch('../api/submit_tugas.php', {method:'POST', body:new FormData(this)}).then(r=>r.json()).then(d=>{ Swal.fire('Sukses',d.message,'success'); loadTugas(); });
});
loadTugas();
</script>

<?php include '../components/footer.php'; ?>