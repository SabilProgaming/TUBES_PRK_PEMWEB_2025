-- Seed data EduPortal
-- Dikerjakan oleh: Anggota 1 (Ketua)
-- Password untuk semua akun: "password" (bcrypt hash)

USE eduportal;

-- Hapus data lama jika ada (untuk testing)
-- Hapus dalam urutan yang benar untuk menghindari foreign key constraint
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE nilai;
TRUNCATE TABLE submission;
TRUNCATE TABLE tugas;
TRUNCATE TABLE materi;
TRUNCATE TABLE pengumuman;
TRUNCATE TABLE mata_kuliah;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- INSERT DATA USERS
-- ============================================
-- Password: password (bcrypt hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)

-- Insert data admin
INSERT INTO users (username, password, nama, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Insert data dosen
INSERT INTO users (username, password, nama, role) VALUES
('dosen1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Ahmad Fauzi, M.Kom', 'dosen'),
('dosen2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Dr. Siti Nurhaliza, M.T', 'dosen'),
('dosen3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Budi Santoso, S.Kom., M.Kom', 'dosen');

-- Insert data mahasiswa
INSERT INTO users (username, password, nama, role) VALUES
('mhs1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Muhammad Zaki Zain', 'mahasiswa'),
('mhs2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alfikri Deo Putra', 'mahasiswa'),
('mhs3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sony Kurniawan', 'mahasiswa'),
('mhs4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sabilillah Irdo', 'mahasiswa'),
('mhs5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Rizki Pratama', 'mahasiswa');

-- ============================================
-- INSERT DATA MATA KULIAH
-- ============================================
-- Ambil ID dosen (dosen1 = 2, dosen2 = 3, dosen3 = 4)
INSERT INTO mata_kuliah (kode, nama, sks, dosen_id) VALUES
('PEMWEB01', 'Pemrograman Web', 3, 2),
('PEMWEB02', 'Praktikum Pemrograman Web', 1, 2),
('BASDAT01', 'Basis Data', 3, 3),
('BASDAT02', 'Praktikum Basis Data', 1, 3),
('ALPRO01', 'Algoritma dan Pemrograman', 3, 4),
('ALPRO02', 'Praktikum Algoritma dan Pemrograman', 1, 4),
('JARKOM01', 'Jaringan Komputer', 3, 2),
('SISINF01', 'Sistem Informasi', 3, 3),
('RPL01', 'Rekayasa Perangkat Lunak', 3, 4),
('IMK01', 'Interaksi Manusia dan Komputer', 2, 2);

-- ============================================
-- INSERT DATA MATERI (Dummy - file_path akan diisi saat upload real)
-- ============================================
-- Materi untuk Pemrograman Web
INSERT INTO materi (mata_kuliah_id, judul, deskripsi, file_path, file_name, file_size, uploaded_by) VALUES
(1, 'Pengenalan HTML dan CSS', 'Materi pengenalan dasar HTML dan CSS untuk pemrograman web', 'uploads/materi/pengenalan-html-css.pdf', 'pengenalan-html-css.pdf', 1024000, 2),
(1, 'JavaScript Dasar', 'Materi JavaScript dasar untuk interaktivitas web', 'uploads/materi/javascript-dasar.pdf', 'javascript-dasar.pdf', 1536000, 2),
(1, 'PHP dan MySQL', 'Materi backend development dengan PHP dan MySQL', 'uploads/materi/php-mysql.pdf', 'php-mysql.pdf', 2048000, 2),
(3, 'Pengenalan Basis Data', 'Konsep dasar basis data dan normalisasi', 'uploads/materi/pengenalan-basis-data.pdf', 'pengenalan-basis-data.pdf', 1280000, 3),
(3, 'SQL Dasar', 'Materi SQL untuk query dan manipulasi data', 'uploads/materi/sql-dasar.pdf', 'sql-dasar.pdf', 1792000, 3);

-- ============================================
-- INSERT DATA TUGAS
-- ============================================
-- Tugas untuk Pemrograman Web
INSERT INTO tugas (mata_kuliah_id, judul, deskripsi, deadline, created_by) VALUES
(1, 'Tugas 1: Membuat Website Portfolio', 'Buat website portfolio sederhana menggunakan HTML, CSS, dan JavaScript. Deadline 2 minggu dari sekarang.', DATE_ADD(NOW(), INTERVAL 14 DAY), 2),
(1, 'Tugas 2: Sistem Login dengan PHP', 'Implementasikan sistem login dengan PHP dan MySQL. Wajib menggunakan PDO dan password hashing.', DATE_ADD(NOW(), INTERVAL 21 DAY), 2),
(3, 'Tugas 1: Desain Database', 'Desain database untuk sistem perpustakaan. Sertakan ERD dan normalisasi.', DATE_ADD(NOW(), INTERVAL 10 DAY), 3),
(3, 'Tugas 2: Implementasi CRUD', 'Buat aplikasi CRUD sederhana dengan PHP dan MySQL untuk manajemen buku.', DATE_ADD(NOW(), INTERVAL 17 DAY), 3),
(5, 'Tugas 1: Algoritma Sorting', 'Implementasikan algoritma bubble sort, selection sort, dan insertion sort dalam bahasa pemrograman pilihan.', DATE_ADD(NOW(), INTERVAL 7 DAY), 4);

-- ============================================
-- INSERT DATA SUBMISSION (Beberapa mahasiswa sudah submit)
-- ============================================
-- Submission untuk Tugas 1 Pemrograman Web (mhs1 sudah submit)
INSERT INTO submission (tugas_id, mahasiswa_id, file_path, file_name, file_size, nilai, feedback, dinilai_oleh, dinilai_pada) VALUES
(1, 5, 'uploads/tugas/tugas1-mhs1.zip', 'tugas1-mhs1.zip', 5120000, 85.50, 'Bagus, tapi perlu perbaiki responsive design.', 2, NOW()),
(1, 6, 'uploads/tugas/tugas1-mhs2.zip', 'tugas1-mhs2.zip', 4860000, NULL, NULL, NULL, NULL),
(3, 5, 'uploads/tugas/tugas1-db-mhs1.pdf', 'tugas1-db-mhs1.pdf', 2048000, 90.00, 'ERD sangat baik dan normalisasi sudah benar.', 3, NOW());

-- ============================================
-- INSERT DATA PENGUMUMAN
-- ============================================
INSERT INTO pengumuman (judul, isi, created_by) VALUES
('Selamat Datang di EduPortal', 'Selamat datang di sistem manajemen pembelajaran EduPortal. Silakan gunakan sistem ini untuk mengakses materi, mengumpulkan tugas, dan melihat nilai Anda.', 1),
('Jadwal Ujian Tengah Semester', 'Diberitahukan kepada seluruh mahasiswa bahwa Ujian Tengah Semester akan dilaksanakan pada tanggal 15-20 Maret 2025. Silakan cek jadwal lengkap di dashboard masing-masing.', 1),
('Pengumuman Penting: Perpanjangan Deadline Tugas', 'Dengan ini diberitahukan bahwa deadline Tugas 1 Pemrograman Web diperpanjang hingga tanggal 25 Februari 2025. Harap segera menyelesaikan tugas Anda.', 2),
('Workshop Pemrograman Web', 'Akan diadakan workshop pemrograman web untuk mahasiswa semester 3-4. Pendaftaran dibuka mulai tanggal 1 Maret 2025. Informasi lebih lanjut hubungi admin.', 1),
('Perbaikan Sistem EduPortal', 'Pada tanggal 28 Februari 2025, sistem akan mengalami maintenance dari pukul 00:00 - 06:00 WIB. Mohon maaf atas ketidaknyamanannya.', 1);

-- ============================================
-- INSERT DATA NILAI
-- ============================================
-- Nilai untuk beberapa mata kuliah
INSERT INTO nilai (mata_kuliah_id, mahasiswa_id, nilai, feedback, created_by) VALUES
(1, 5, 85.50, 'Nilai akhir berdasarkan tugas dan kehadiran. Pertahankan!', 2),
(1, 6, 78.00, 'Perlu lebih banyak latihan untuk meningkatkan pemahaman.', 2),
(3, 5, 90.00, 'Sangat baik! Pertahankan prestasi ini.', 3),
(3, 6, 82.50, 'Bagus, tapi masih bisa ditingkatkan lagi.', 3),
(5, 5, 88.00, 'Implementasi algoritma sudah benar dan efisien.', 4);

-- ============================================
-- CATATAN PENTING
-- ============================================
-- 1. Password untuk semua akun: "password"
-- 2. Hash password menggunakan bcrypt: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- 3. Untuk generate hash baru, gunakan: password_hash('password', PASSWORD_BCRYPT)
-- 4. File path untuk materi dan tugas adalah dummy, akan diisi saat upload real
-- 5. Pastikan folder uploads/materi dan uploads/tugas sudah ada dengan permission write
