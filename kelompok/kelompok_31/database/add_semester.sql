-- Add Semester Column to mata_kuliah table
ALTER TABLE mata_kuliah ADD COLUMN semester INT DEFAULT 1 AFTER sks;

-- Add Semester Column to users table (untuk mahasiswa)
ALTER TABLE users ADD COLUMN semester INT DEFAULT 1 AFTER role;

-- Update seed data mata kuliah dengan semester
UPDATE mata_kuliah SET semester = 3 WHERE kode IN ('PEMWEB01', 'PEMWEB02', 'BASDAT01', 'BASDAT02');
UPDATE mata_kuliah SET semester = 2 WHERE kode IN ('ALPRO01', 'ALPRO02');
UPDATE mata_kuliah SET semester = 4 WHERE kode IN ('JARKOM01', 'SISINF01', 'RPL01');
UPDATE mata_kuliah SET semester = 2 WHERE kode = 'IMK01';
