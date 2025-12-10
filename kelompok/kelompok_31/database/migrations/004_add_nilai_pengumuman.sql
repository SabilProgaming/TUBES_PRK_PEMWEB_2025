-- Migration 004: Add Nilai and Pengumuman Support
-- Dikerjakan oleh: Anggota 4
-- PENTING: Info ke grup WA sebelum menjalankan migration ini!
-- 
-- Migration ini memastikan:
-- 1. Tabel submission memiliki kolom untuk nilai dan feedback
-- 2. Tabel pengumuman tersedia untuk CRUD pengumuman

USE eduportal;

-- Pastikan kolom nilai, feedback, dinilai_oleh, dinilai_pada ada di tabel submission
-- (Jika sudah ada, query ini akan diabaikan dengan error yang aman)
ALTER TABLE submission 
ADD COLUMN IF NOT EXISTS nilai DECIMAL(5,2) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS feedback TEXT,
ADD COLUMN IF NOT EXISTS dinilai_oleh INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS dinilai_pada TIMESTAMP NULL DEFAULT NULL;

-- Tambah foreign key untuk dinilai_oleh jika belum ada
-- (Hanya jika kolom dinilai_oleh berhasil ditambahkan)
-- Note: MySQL tidak support IF NOT EXISTS untuk foreign key, jadi perlu manual check

-- Pastikan tabel pengumuman ada
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

-- Tambah index untuk performa query nilai
CREATE INDEX IF NOT EXISTS idx_nilai ON submission(nilai);
CREATE INDEX IF NOT EXISTS idx_dinilai_oleh ON submission(dinilai_oleh);

-- Catatan:
-- Jika migration ini dijalankan pada database yang sudah memiliki struktur lengkap,
-- beberapa ALTER TABLE mungkin akan error. Itu normal dan bisa diabaikan.
-- Yang penting adalah memastikan struktur akhir sesuai dengan kebutuhan.

