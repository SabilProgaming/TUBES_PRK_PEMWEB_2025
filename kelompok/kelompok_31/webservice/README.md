# EduPortal REST API Documentation

**Dikerjakan oleh: Anggota 4**

## Base URL
```
http://localhost/kelompok/kelompok_31/webservice/api.php
```

## Response Format

Semua response mengikuti format JSON konsisten:

### Success Response
```json
{
  "status": "success",
  "message": "Pesan sukses",
  "data": [...]
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Pesan error",
  "error_code": 400
}
```

## HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request (validation error)
- `403` - Forbidden (unauthorized)
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

## Endpoints

### 1. Mata Kuliah

#### GET /mata-kuliah
Mendapatkan list semua mata kuliah.

**Response:**
```json
{
  "status": "success",
  "message": "Data mata kuliah berhasil diambil",
  "data": [
    {
      "id": 1,
      "kode": "IF101",
      "nama": "Pemrograman Web",
      "sks": 3,
      "dosen_id": 2,
      "dosen_nama": "Dr. Ahmad",
      "created_at": "2024-01-01 00:00:00"
    }
  ]
}
```

#### GET /mata-kuliah/{id}
Mendapatkan detail mata kuliah berdasarkan ID.

**Example:**
```
GET /webservice/api.php/mata-kuliah/1
```

### 2. Materi

#### GET /materi
Mendapatkan list semua materi pembelajaran.

**Response:**
```json
{
  "status": "success",
  "message": "Data materi berhasil diambil",
  "data": [
    {
      "id": 1,
      "mata_kuliah_id": 1,
      "judul": "Pengenalan HTML",
      "deskripsi": "Materi dasar HTML",
      "file_path": "materi_xxx.pdf",
      "file_name": "HTML_Basics.pdf",
      "nama_mk": "Pemrograman Web",
      "kode_mk": "IF101",
      "dosen_nama": "Dr. Ahmad",
      "created_at": "2024-01-01 00:00:00"
    }
  ]
}
```

#### GET /materi/{id}
Mendapatkan detail materi berdasarkan ID.

### 3. Tugas

#### GET /tugas
Mendapatkan list semua tugas.

**Response:**
```json
{
  "status": "success",
  "message": "Data tugas berhasil diambil",
  "data": [
    {
      "id": 1,
      "mata_kuliah_id": 1,
      "judul": "Tugas 1: Membuat Website",
      "deskripsi": "Buat website portfolio sederhana",
      "deadline": "2024-02-15 23:59:00",
      "nama_mk": "Pemrograman Web",
      "kode_mk": "IF101",
      "dosen_nama": "Dr. Ahmad",
      "created_at": "2024-01-01 00:00:00"
    }
  ]
}
```

#### GET /tugas/{id}
Mendapatkan detail tugas berdasarkan ID.

### 4. Pengumuman

#### GET /pengumuman
Mendapatkan list semua pengumuman.

**Response:**
```json
{
  "status": "success",
  "message": "Data pengumuman berhasil diambil",
  "data": [
    {
      "id": 1,
      "judul": "Pengumuman UTS",
      "isi": "Ujian Tengah Semester akan dilaksanakan...",
      "created_by": 1,
      "created_by_name": "Admin",
      "created_at": "2024-01-01 00:00:00",
      "updated_at": "2024-01-01 00:00:00"
    }
  ]
}
```

#### GET /pengumuman/{id}
Mendapatkan detail pengumuman berdasarkan ID.

## Usage Examples

### JavaScript (Fetch API)

```javascript
// GET Request
fetch('http://localhost/kelompok/kelompok_31/webservice/api.php/mata-kuliah')
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      console.log(data.data);
    } else {
      console.error(data.message);
    }
  })
  .catch(error => console.error('Error:', error));

// GET dengan ID
fetch('http://localhost/kelompok/kelompok_31/webservice/api.php/mata-kuliah/1')
  .then(response => response.json())
  .then(data => console.log(data));
```

### jQuery

```javascript
$.ajax({
  url: 'webservice/api.php/mata-kuliah',
  method: 'GET',
  dataType: 'json',
  success: function(response) {
    if (response.status === 'success') {
      console.log(response.data);
    }
  },
  error: function(xhr) {
    console.error('Error:', xhr.responseText);
  }
});
```

### cURL

```bash
# GET semua mata kuliah
curl http://localhost/kelompok/kelompok_31/webservice/api.php/mata-kuliah

# GET detail mata kuliah
curl http://localhost/kelompok/kelompok_31/webservice/api.php/mata-kuliah/1
```

## CORS

API mendukung CORS (Cross-Origin Resource Sharing) untuk memungkinkan request dari domain lain.

## Testing

Gunakan halaman `consume.php` untuk testing API secara interaktif:
```
http://localhost/kelompok/kelompok_31/webservice/consume.php
```

## Notes

- Semua endpoint saat ini hanya mendukung GET method
- POST, PUT, DELETE methods akan ditambahkan di versi selanjutnya
- API tidak memerlukan authentication untuk GET requests (public access)
- Untuk operasi write (POST/PUT/DELETE), authentication akan diperlukan

