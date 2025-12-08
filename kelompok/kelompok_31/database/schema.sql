-- Schema database EduPortal
-- Dikerjakan oleh: Anggota 1 (Ketua)
-- Database lengkap sesuai spesifikasi README

-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS eduportal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eduportal;

-- Tabel Users untuk autentikasi
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'dosen', 'mahasiswa') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Mata Kuliah
CREATE TABLE IF NOT EXISTS mata_kuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    sks INT NOT NULL DEFAULT 3,
    dosen_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dosen_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_kode (kode),
    INDEX idx_dosen (dosen_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Materi (Upload materi oleh dosen)
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

-- Tabel Tugas (Buat tugas oleh dosen)
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

-- Tabel Submission (Submit tugas oleh mahasiswa)
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

-- Tabel Pengumuman
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

-- Tabel Nilai (Input nilai oleh dosen)
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
