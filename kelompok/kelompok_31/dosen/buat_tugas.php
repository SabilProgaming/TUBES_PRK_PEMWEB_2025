<?php
/**
 * Buat Tugas
 * Dikerjakan oleh: Anggota 3
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'dosen') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Kelola Tugas";
include '../components/header.php';
include '../components/navbar.php';
require_once '../config/database.php';

$dosen_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE dosen_id = ? ORDER BY nama ASC");
    $stmt->execute([$dosen_id]);
    $daftar_matkul = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $daftar_matkul = []; }
?>

<div class="dashboard-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><i class="fas fa-pencil-alt me-2"></i>Kelola Tugas</h2>
                <p class="mb-0 text-light">Buat dan distribusikan tugas kepada mahasiswa</p>
            </div>
            <div class="col-md-4 text-end">
                <p class="mb-1 small">
                    <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>Buat Tugas Baru</h5>
                </div>
                <div class="card-body">
                    <form id="formTugas">
                        <div class="mb-3">
                            <label class="form-label">Judul Tugas</label>
                            <input type="text" class="form-control" name="judul" required placeholder="Contoh: Laporan Modul 1">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Mata Kuliah</label>
                                <select class="form-select" name="mata_kuliah_id" required>
                                    <option value="">-- Pilih Mata Kuliah --</option>
                                    <?php foreach($daftar_matkul as $mk): ?>
                                        <option value="<?= $mk['id'] ?>">
                                            <?= $mk['kode'] . ' - ' . $mk['nama'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Deadline</label>
                                <input type="datetime-local" class="form-control" name="deadline" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instruksi</label>
                            <textarea class="form-control" name="deskripsi" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File Soal</label>
                            <input type="file" class="form-control" name="file">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i>Terbitkan Tugas
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Riwayat Tugas</h5>
                    <button onclick="loadData()" class="btn btn-sm btn-light"><i class="fas fa-sync-alt"></i></button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Detail Tugas</th>
                                    <th>Deadline</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyHistory"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API = '../api/buat_tugas.php';

function formatDateTime(dateString) {
    const date = new Date(dateString);
    if (isNaN(date)) {
        const parts = dateString.split(/[- :]/);
        if (parts.length >= 6) {
             const safeDate = new Date(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], parts[5]);
             if (!isNaN(safeDate)) return safeDate.toLocaleString('id-ID');
        }
        return 'Tanggal tidak valid'; 
    }
    return date.toLocaleString('id-ID'); 
}

document.getElementById('formTugas').addEventListener('submit', function(e){
    e.preventDefault(); 
    fetch(API, {method:'POST', body:new FormData(this)}).then(r=>r.json()).then(d=>{ 
        if(d.status === 'success'){
            Swal.fire('Sukses','Tugas diterbitkan','success'); 
            loadData(); 
            this.reset(); 
        } else {
            Swal.fire('Gagal', d.message || 'Terjadi kesalahan pada server.', 'error');
        }
    });
});
function loadData(){
    fetch(API).then(r=>r.json()).then(res=>{
        if(res.status !== 'success') {
             document.getElementById('tbodyHistory').innerHTML = `<tr><td colspan="3" class="text-center py-3 text-danger">API Error: ${res.message || 'Gagal mengambil data riwayat.'}</td></tr>`;
             return;
        }

        let h=''; 
        if(res.data && res.data.length > 0){
            res.data.forEach(i=>{
                let dateStr = formatDateTime(i.deadline);
                
                h+=`<tr>
                    <td class="ps-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary mb-1">${i.nama_mk}</span>
                        <div class="fw-bold text-dark">${i.judul}</div>
                    </td>
                    <td><span class="text-danger small fw-bold">${dateStr}</span></td>
                    <td class="text-end pe-3">
                        <button onclick="hapus(${i.id})" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            }); 
        } else {
             h = '<tr><td colspan="3" class="text-center py-3">Belum ada tugas dibuat.</td></tr>';
        }
        document.getElementById('tbodyHistory').innerHTML=h;
    });
}
function hapus(id){ Swal.fire({title:'Hapus?', icon:'warning', showCancelButton:true}).then(r=>{ if(r.isConfirmed){ let fd=new FormData(); fd.append('action','delete'); fd.append('id',id); fetch(API,{method:'POST', body:fd}).then(()=>{loadData()}); } }); }
loadData();
</script>

<?php include '../components/footer.php'; ?>