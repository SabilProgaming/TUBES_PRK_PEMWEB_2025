<?php
/**
 * Upload Materi
 * Dikerjakan oleh: Anggota 3
 */
session_start();
$page_title = "Bank Materi";
include '../components/header.php';
include '../components/navbar.php';
require_once '../config/database.php';

$dosen_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE dosen_id = ? ORDER BY nama ASC");
    $stmt->execute([$dosen_id]);
    $mata_kuliah = $stmt->fetchAll();
} catch (PDOException $e) {
    $mata_kuliah = [];
}
?>

<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-book-open me-2"></i>Bank Materi</h2>
                <p class="mb-0 text-light">Upload dan kelola bahan ajar digital</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-light btn-sm"><i class="fas fa-layer-group me-1"></i>Dosen Panel</button>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-cloud-upload-alt me-2 text-primary"></i>Upload Materi</h5>
                </div>
                <div class="card-body">
                    <form id="formUpload">
                        <div class="mb-3">
                            <label class="form-label">Mata Kuliah</label>
                            <select class="form-select" name="mata_kuliah_id" required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach($mata_kuliah as $mk): ?>
                            <option value="<?= $mk['id'] ?>">
                            <?= $mk['kode'] . ' - ' . $mk['nama'] ?>
                            </option>
                            <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Judul Materi</label>
                            <input type="text" class="form-control" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File (PDF/PPT)</label>
                            <input type="file" class="form-control" name="file" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Simpan Materi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-archive me-2 text-primary"></i>Arsip Materi</h5>
                    <input type="text" id="search" placeholder="Cari materi..." class="form-control form-control-sm w-25">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Materi</th>
                                    <th>Tanggal</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyMateri"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API = '../api/upload_materi.php';
document.getElementById('formUpload').addEventListener('submit', function(e){
    e.preventDefault(); fetch(API, {method:'POST', body:new FormData(this)}).then(r=>r.json()).then(d=>{ Swal.fire('Sukses','Materi diupload','success'); loadData(); this.reset(); });
});
function loadData(){
    fetch(API).then(r=>r.json()).then(res=>{
        let h=''; res.data.forEach(i=>{
            h+=`<tr>
                <td class="ps-3">
                    <span class="badge bg-info text-dark mb-1">${i.nama_mk}</span>
                    <div class="fw-bold text-dark">${i.judul}</div>
                </td>
                <td><small class="text-muted">${new Date(i.uploaded_at).toLocaleDateString()}</small></td>
                <td class="text-end pe-3">
                    <button onclick="hapus(${i.id})" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }); document.getElementById('tbodyMateri').innerHTML=h;
    });
}
function hapus(id){ Swal.fire({title:'Hapus?', icon:'warning', showCancelButton:true}).then(r=>{ if(r.isConfirmed){ let fd=new FormData(); fd.append('action','delete'); fd.append('id',id); fetch(API,{method:'POST', body:fd}).then(()=>{loadData()}); } }); }
document.getElementById('search').addEventListener('keyup', function(){
    let v = this.value.toLowerCase(); document.querySelectorAll('#tbodyMateri tr').forEach(r => r.style.display = r.innerText.toLowerCase().includes(v)?'':'none');
});
loadData();
</script>

<?php include '../components/footer.php'; ?>