-- ============================================
-- SETUP DATABASE EDUPORTAL - ANGGOTA 2 JOBDESK
-- ============================================
-- File ini berisi semua SQL yang diperlukan untuk setup database
-- Jalankan file ini di MySQL untuk membuat database dan tabel

-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS eduportal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eduportal;

-- ============================================
-- 1. TABEL USERS
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'dosen', 'mahasiswa') NOT NULL,
    semester INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. TABEL MATA KULIAH
-- ============================================
CREATE TABLE IF NOT EXISTS mata_kuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    sks INT NOT NULL DEFAULT 3,
    semester INT DEFAULT 1,
    dosen_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dosen_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_kode (kode),
    INDEX idx_dosen (dosen_id),
    INDEX idx_semester (semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. TABEL ENROLLMENT (Mahasiswa bergabung ke Mata Kuliah)
-- ============================================
CREATE TABLE IF NOT EXISTS enrollment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT NOT NULL,
    mahasiswa_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
    FOREIGN KEY (mahasiswa_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (mata_kuliah_id, mahasiswa_id),
    INDEX idx_mata_kuliah (mata_kuliah_id),
    INDEX idx_mahasiswa (mahasiswa_id),
    INDEX idx_joined (joined_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. TABEL MATERI
-- ============================================
CREATE TABLE IF NOT EXISTS materi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_mata_kuliah (mata_kuliah_id),
    INDEX idx_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. TABEL TUGAS
-- ============================================
CREATE TABLE IF NOT EXISTS tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    deadline DATETIME NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_mata_kuliah (mata_kuliah_id),
    INDEX idx_created_by (created_by),
    INDEX idx_deadline (deadline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. TABEL SUBMISSION
-- ============================================
CREATE TABLE IF NOT EXISTS submission (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tugas_id INT NOT NULL,
    mahasiswa_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    nilai DECIMAL(5,2) DEFAULT NULL,
    feedback TEXT,
    dinilai_oleh INT DEFAULT NULL,
    dinilai_pada TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE,
    FOREIGN KEY (mahasiswa_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dinilai_oleh) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_submission (tugas_id, mahasiswa_id),
    INDEX idx_tugas (tugas_id),
    INDEX idx_mahasiswa (mahasiswa_id),
    INDEX idx_nilai (nilai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. TABEL PENGUMUMAN
-- ============================================
CREATE TABLE IF NOT EXISTS pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. TABEL NILAI
-- ============================================
CREATE TABLE IF NOT EXISTS nilai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT NOT NULL,
    mahasiswa_id INT NOT NULL,
    nilai DECIMAL(5,2) NOT NULL,
    feedback TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
    FOREIGN KEY (mahasiswa_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_nilai (mata_kuliah_id, mahasiswa_id),
    INDEX idx_mata_kuliah (mata_kuliah_id),
    INDEX idx_mahasiswa (mahasiswa_id),
    INDEX idx_nilai (nilai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================
-- Password untuk semua akun: "password" (bcrypt hash)

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE nilai;
TRUNCATE TABLE submission;
TRUNCATE TABLE tugas;
TRUNCATE TABLE materi;
TRUNCATE TABLE pengumuman;
TRUNCATE TABLE enrollment;
TRUNCATE TABLE mata_kuliah;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- INSERT DATA USERS
-- ============================================
-- Password: password (bcrypt hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)

-- Insert admin
INSERT INTO users (username, password, nama, role, semester) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 1);

-- Insert dosen
INSERT INTO users (username, password, nama, role, semester) VALUES
('dosen1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Ahmad Fauzi, M.Kom', 'dosen', 1),
('dosen2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Dr. Siti Nurhaliza, M.T', 'dosen', 1),
('dosen3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Budi Santoso, S.Kom., M.Kom', 'dosen', 1);

-- Insert mahasiswa (semester 2 dan 3)
INSERT INTO users (username, password, nama, role, semester) VALUES
('mhs1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Muhammad Zaki Zain', 'mahasiswa', 3),
('mhs2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alfikri Deo Putra', 'mahasiswa', 3),
('mhs3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sony Kurniawan', 'mahasiswa', 2),
('mhs4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sabilillah Irdo', 'mahasiswa', 2),
('mhs5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Rizki Pratama', 'mahasiswa', 3);

-- ============================================
-- INSERT DATA MATA KULIAH (dengan semester)
-- ============================================
-- Dosen IDs: dosen1=2, dosen2=3, dosen3=4
INSERT INTO mata_kuliah (kode, nama, sks, semester, dosen_id) VALUES
('PEMWEB01', 'Pemrograman Web', 3, 3, 2),
('PEMWEB02', 'Praktikum Pemrograman Web', 1, 3, 2),
('BASDAT01', 'Basis Data', 3, 3, 3),
('BASDAT02', 'Praktikum Basis Data', 1, 3, 3),
('ALPRO01', 'Algoritma dan Pemrograman', 3, 2, 4),
('ALPRO02', 'Praktikum Algoritma dan Pemrograman', 1, 2, 4),
('JARKOM01', 'Jaringan Komputer', 3, 4, 2),
('SISINF01', 'Sistem Informasi', 3, 4, 3),
('RPL01', 'Rekayasa Perangkat Lunak', 3, 4, 4),
('IMK01', 'Interaksi Manusia dan Komputer', 2, 2, 2);

-- ============================================
-- INSERT DATA ENROLLMENT (Mahasiswa bergabung ke kelas)
-- ============================================
-- mhs1, mhs2, mhs5 (semester 3) bergabung ke mata kuliah semester 3
INSERT INTO enrollment (mata_kuliah_id, mahasiswa_id) VALUES
(1, 1), -- mhs1 -> PEMWEB01
(2, 1), -- mhs1 -> PEMWEB02
(3, 1), -- mhs1 -> BASDAT01
(1, 2), -- mhs2 -> PEMWEB01
(4, 2), -- mhs2 -> BASDAT02
(1, 5), -- mhs5 -> PEMWEB01
(3, 5); -- mhs5 -> BASDAT01

-- mhs3, mhs4 (semester 2) bergabung ke mata kuliah semester 2
INSERT INTO enrollment (mata_kuliah_id, mahasiswa_id) VALUES
(5, 3), -- mhs3 -> ALPRO01
(6, 3), -- mhs3 -> ALPRO02
(10, 3), -- mhs3 -> IMK01
(5, 4), -- mhs4 -> ALPRO01
(6, 4); -- mhs4 -> ALPRO02

-- ============================================
-- SETUP SELESAI
-- ============================================
-- Database sudah siap digunakan!
-- Login credentials:
-- - Admin: username=admin, password=password
-- - Dosen: username=dosen1/dosen2/dosen3, password=password
-- - Mahasiswa: username=mhs1/mhs2/mhs3/mhs4/mhs5, password=password
