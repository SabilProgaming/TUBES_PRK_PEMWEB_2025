# Testing Checklist - Feature 4 (Anggota 4)

**Dikerjakan oleh: Anggota 4**

## Pre-requisites

- [ ] Jalankan migration: `database/migrations/004_add_nilai_pengumuman.sql`
- [ ] Pastikan database `eduportal` sudah ada dan terisi data
- [ ] Pastikan ada user dengan role: admin, dosen, mahasiswa

## 1. Testing Integration (P1)

### 1.1 Test Ambil Data Submission (Dependensi: Anggota 3)
- [ ] Login sebagai dosen
- [ ] Buka halaman `dosen/input_nilai.php`
- [ ] Pastikan data submission tugas dari mahasiswa muncul
- [ ] Test filter berdasarkan mata kuliah
- [ ] Test filter berdasarkan status (sudah/belum dinilai)
- [ ] Pastikan file submission bisa di-download

**Expected:** Data submission dari tabel `submission` (yang dibuat oleh Anggota 3) muncul dengan benar.

### 1.2 Test Ambil Data Mata Kuliah (Dependensi: Anggota 2)
- [ ] Login sebagai dosen
- [ ] Buka halaman `dosen/input_nilai.php`
- [ ] Pastikan dropdown "Filter Mata Kuliah" terisi dengan mata kuliah yang diampu dosen
- [ ] Pastikan data mata kuliah berasal dari tabel `mata_kuliah` (yang dibuat oleh Anggota 2)

**Expected:** Dropdown mata kuliah terisi dengan benar dari database.

### 1.3 Test Session Validation (Dependensi: Anggota 1)
- [ ] Buka `dosen/input_nilai.php` tanpa login → harus redirect ke login
- [ ] Login sebagai admin → buka `dosen/input_nilai.php` → harus redirect (bukan dosen)
- [ ] Login sebagai mahasiswa → buka `dosen/input_nilai.php` → harus redirect (bukan dosen)
- [ ] Login sebagai dosen → buka `dosen/input_nilai.php` → harus bisa akses
- [ ] Test hal yang sama untuk `mahasiswa/nilai.php` dan `admin/pengumuman.php`

**Expected:** Session validation bekerja dengan benar untuk semua role.

### 1.4 Test Flow End-to-End: Submit Tugas → Input Nilai → Lihat Nilai
- [ ] Login sebagai mahasiswa
- [ ] Submit tugas melalui `mahasiswa/tugas.php` (Anggota 3)
- [ ] Logout, login sebagai dosen
- [ ] Buka `dosen/input_nilai.php`
- [ ] Pastikan submission tugas mahasiswa muncul
- [ ] Input nilai dan feedback untuk tugas tersebut
- [ ] Pastikan nilai tersimpan di database
- [ ] Logout, login sebagai mahasiswa
- [ ] Buka `mahasiswa/nilai.php`
- [ ] Pastikan nilai yang sudah diinput dosen muncul
- [ ] Pastikan feedback muncul
- [ ] Pastikan statistik (rata-rata nilai) terhitung dengan benar

**Expected:** Flow lengkap dari submit tugas sampai lihat nilai bekerja dengan sempurna.

## 2. Error Handling Standard (P1)

### 2.1 Test Format JSON Response
Test semua API endpoint dan pastikan format konsisten:

**Success:**
```json
{
  "status": "success",
  "message": "...",
  "data": [...]
}
```

**Error:**
```json
{
  "status": "error",
  "message": "...",
  "error_code": 400
}
```

- [ ] `api/pengumuman_crud.php` - GET, POST, PUT, DELETE
- [ ] `api/input_nilai.php` - GET, POST
- [ ] `api/nilai.php` - GET
- [ ] `webservice/api.php` - Semua endpoint

### 2.2 Test HTTP Status Codes
- [ ] 200 - Success responses
- [ ] 201 - Created (POST success)
- [ ] 400 - Bad Request (validation error)
- [ ] 403 - Forbidden (unauthorized)
- [ ] 404 - Not Found
- [ ] 500 - Internal Server Error

## 3. UI/UX Features

### 3.1 SweetAlert2 untuk Konfirmasi Delete
- [ ] `admin/pengumuman.php` - Test delete pengumuman dengan konfirmasi
- [ ] Pastikan konfirmasi muncul sebelum delete
- [ ] Pastikan cancel tidak menghapus data

### 3.2 Loading Spinner saat AJAX Request
- [ ] `admin/pengumuman.php` - Loading saat load data, save, delete
- [ ] `dosen/input_nilai.php` - Loading saat load data, save nilai
- [ ] `mahasiswa/nilai.php` - Loading saat load data

**Expected:** Loading spinner muncul saat AJAX request dan hilang setelah selesai.

### 3.3 Empty State ("Belum ada data")
- [ ] `admin/pengumuman.php` - Tampilkan empty state jika belum ada pengumuman
- [ ] `dosen/input_nilai.php` - Tampilkan empty state jika belum ada submission
- [ ] `mahasiswa/nilai.php` - Tampilkan empty state jika belum ada nilai

**Expected:** UI yang informatif ketika tidak ada data, bukan hanya blank page.

## 4. REST API Testing

### 4.1 Test Endpoint GET
- [ ] `GET /webservice/api.php/mata-kuliah` - List semua
- [ ] `GET /webservice/api.php/mata-kuliah/1` - Detail dengan ID
- [ ] `GET /webservice/api.php/materi` - List semua
- [ ] `GET /webservice/api.php/materi/1` - Detail dengan ID
- [ ] `GET /webservice/api.php/tugas` - List semua
- [ ] `GET /webservice/api.php/tugas/1` - Detail dengan ID
- [ ] `GET /webservice/api.php/pengumuman` - List semua
- [ ] `GET /webservice/api.php/pengumuman/1` - Detail dengan ID

### 4.2 Test Consume API Page
- [ ] Buka `webservice/consume.php`
- [ ] Test GET request untuk semua resource
- [ ] Test dengan ID dan tanpa ID
- [ ] Pastikan response ditampilkan dengan format yang readable
- [ ] Pastikan error handling bekerja

### 4.3 Test CORS
- [ ] Test API dari domain berbeda (jika perlu)
- [ ] Pastikan CORS headers ada

## 5. Database Testing

### 5.1 Test Migration
- [ ] Jalankan `database/migrations/004_add_nilai_pengumuman.sql`
- [ ] Pastikan kolom `nilai`, `feedback`, `dinilai_oleh`, `dinilai_pada` ada di tabel `submission`
- [ ] Pastikan tabel `pengumuman` ada dengan struktur yang benar

### 5.2 Test Data Integrity
- [ ] Input nilai → pastikan tersimpan di `submission.nilai`
- [ ] Input feedback → pastikan tersimpan di `submission.feedback`
- [ ] Pastikan `dinilai_oleh` terisi dengan ID dosen
- [ ] Pastikan `dinilai_pada` terisi dengan timestamp
- [ ] Create pengumuman → pastikan tersimpan di tabel `pengumuman`
- [ ] Update pengumuman → pastikan `updated_at` berubah
- [ ] Delete pengumuman → pastikan data terhapus

## 6. Security Testing

### 6.1 SQL Injection Prevention
- [ ] Pastikan semua query menggunakan prepared statements
- [ ] Test input dengan karakter khusus (', ", ;, --, etc.)

### 6.2 XSS Prevention
- [ ] Test input dengan script tags
- [ ] Pastikan output di-escape dengan benar
- [ ] Test di semua form input

### 6.3 Authorization
- [ ] Pastikan dosen hanya bisa input nilai untuk tugas mereka sendiri
- [ ] Pastikan mahasiswa hanya bisa lihat nilai mereka sendiri
- [ ] Pastikan admin hanya bisa CRUD pengumuman

## 7. Performance Testing

### 7.1 Load Time
- [ ] Test load time untuk halaman dengan banyak data
- [ ] Pastikan pagination atau limit query jika perlu

### 7.2 AJAX Performance
- [ ] Test response time untuk AJAX requests
- [ ] Pastikan tidak ada request yang hang

## Notes

- Jika ada error, catat di bagian ini
- Jika ada improvement yang diperlukan, catat di sini
- Pastikan semua test case di atas sudah dicentang sebelum merge ke master

