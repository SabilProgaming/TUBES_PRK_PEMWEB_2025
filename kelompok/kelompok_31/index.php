<?php
/**
 * Landing Page EduPortal
 * Halaman utama untuk memperkenalkan EduPortal
 */

session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/" . $_SESSION['role'] . ".php");
exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduPortal - Sistem Manajemen Pembelajaran Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="landing-page-body">
    <!-- Hero Section -->
    <section class="landing-hero" id="hero">
        <div class="hero-background"></div>
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content fade-in-up">
                        <h1 class="hero-title">Kelola Pembelajaran Akademik dengan Mudah</h1>
                        <p class="hero-subtitle">Platform terintegrasi untuk manajemen mata kuliah, materi, tugas, dan penilaian. Didesain untuk Admin, Dosen, dan Mahasiswa dalam satu sistem yang efisien.</p>
                        <div class="hero-cta">
                            <a href="login.php" class="btn btn-primary btn-lg btn-hero">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk ke EduPortal
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image fade-in-right">
                        <img src="assets/img/upscalemedia-transformed-Photoroom.png" alt="EduPortal" class="hero-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works py-5" id="how-it-works">
        <div class="container">
            <div class="text-center mb-5 fade-in-up">
                <h2 class="section-title">Cara Kerja</h2>
                <p class="section-subtitle">Tiga langkah sederhana untuk memulai menggunakan EduPortal</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card fade-in-up" data-delay="0">
                        <div class="step-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="step-number">01</div>
                        <h3 class="step-title">Login</h3>
                        <p class="step-description">Masuk dengan akun Anda sebagai Admin, Dosen, atau Mahasiswa sesuai dengan peran Anda di sistem.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card fade-in-up" data-delay="200">
                        <div class="step-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="step-number">02</div>
                        <h3 class="step-title">Pilih Fitur</h3>
                        <p class="step-description">Akses materi pembelajaran, tugas, nilai, atau pengumuman sesuai dengan role dan kebutuhan Anda.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card fade-in-up" data-delay="400">
                        <div class="step-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <div class="step-number">03</div>
                        <h3 class="step-title">Mulai Belajar</h3>
                        <p class="step-description">Kelola pembelajaran dengan mudah dan efisien. Upload materi, submit tugas, dan pantau nilai secara real-time.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features py-5 bg-light" id="features">
        <div class="container">
            <div class="text-center mb-5 fade-in-up">
                <h2 class="section-title">Fitur-Fitur Utama</h2>
                <p class="section-subtitle">Temukan fitur-fitur powerful yang dirancang untuk membantu Anda mengelola pembelajaran dengan mudah</p>
            </div>
            <div class="row mb-5">
                <div class="col-12 text-center fade-in-up">
                    <img src="assets/img/34841405377dc225f182f4328fe3372f.jpg" alt="Features" class="features-img">
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up" data-delay="0">
                        <div class="feature-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h4 class="feature-title">Manajemen Mata Kuliah</h4>
                        <p class="feature-description">Kelola mata kuliah dengan mudah. Tambah, edit, dan atur mata kuliah beserta dosen pengampu.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up" data-delay="100">
                        <div class="feature-icon">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <h4 class="feature-title">Upload & Download Materi</h4>
                        <p class="feature-description">Dosen dapat mengupload materi pembelajaran, mahasiswa dapat mengakses dan mendownload kapan saja.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up" data-delay="200">
                        <div class="feature-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h4 class="feature-title">Sistem Tugas & Penilaian</h4>
                        <p class="feature-description">Buat tugas, submit jawaban, dan lihat nilai secara real-time dengan feedback dari dosen.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up" data-delay="300">
                        <div class="feature-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h4 class="feature-title">Pengumuman Terpusat</h4>
                        <p class="feature-description">Informasi penting dan pengumuman dalam satu tempat yang mudah diakses oleh semua pengguna.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up" data-delay="400">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="feature-title">Dashboard Interaktif</h4>
                        <p class="feature-description">Pantau statistik dan aktivitas pembelajaran melalui dashboard yang informatif dan mudah dipahami.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card fade-in-up" data-delay="500">
                        <div class="feature-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h4 class="feature-title">RESTful API</h4>
                        <p class="feature-description">Integrasikan EduPortal dengan sistem lain melalui RESTful API yang lengkap dan terstruktur.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Role Overview Section -->
    <section class="roles py-5" id="roles">
        <div class="container">
            <div class="text-center mb-5 fade-in-up">
                <h2 class="section-title">Untuk Semua Peran</h2>
                <p class="section-subtitle">EduPortal dirancang untuk memenuhi kebutuhan Admin, Dosen, dan Mahasiswa</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="role-card fade-in-up" data-delay="0">
                        <div class="role-icon admin">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3 class="role-title">Admin</h3>
                        <p class="role-description">Kelola sistem secara menyeluruh. Tambah dan atur mata kuliah, kelola pengumuman, dan pantau statistik sistem.</p>
                        <ul class="role-features">
                            <li><i class="fas fa-check"></i> Manajemen Mata Kuliah</li>
                            <li><i class="fas fa-check"></i> CRUD Pengumuman</li>
                            <li><i class="fas fa-check"></i> Dashboard Statistik</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="role-card fade-in-up" data-delay="200">
                        <div class="role-icon dosen">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3 class="role-title">Dosen</h3>
                        <p class="role-description">Upload materi pembelajaran, buat tugas, dan input nilai untuk mahasiswa dengan mudah dan efisien.</p>
                        <ul class="role-features">
                            <li><i class="fas fa-check"></i> Upload Materi</li>
                            <li><i class="fas fa-check"></i> Buat & Kelola Tugas</li>
                            <li><i class="fas fa-check"></i> Input Nilai & Feedback</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="role-card fade-in-up" data-delay="400">
                        <div class="role-icon mahasiswa">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3 class="role-title">Mahasiswa</h3>
                        <p class="role-description">Akses materi pembelajaran, submit tugas, dan lihat nilai serta feedback dari dosen secara real-time.</p>
                        <ul class="role-features">
                            <li><i class="fas fa-check"></i> Akses Materi</li>
                            <li><i class="fas fa-check"></i> Submit Tugas</li>
                            <li><i class="fas fa-check"></i> Lihat Nilai & Feedback</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Additional CTA Section -->
    <section class="cta-section py-5 bg-primary text-white" id="cta">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 fade-in-left">
                    <h2 class="cta-title">Siap Memulai?</h2>
                    <p class="cta-description">Bergabunglah dengan EduPortal sekarang dan rasakan kemudahan dalam mengelola pembelajaran akademik. Platform yang aman, cepat, dan mudah digunakan.</p>
                    <a href="login.php" class="btn btn-light btn-lg mt-3">
                        <i class="fas fa-arrow-right me-2"></i>Mulai Sekarang
                    </a>
                </div>
                <div class="col-lg-6 fade-in-right">
                    <div class="cta-illustration">
                        <img src="assets/img/6ff2ff906514fe69292da4019c64e568.jpg" alt="EduPortal CTA" class="cta-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq py-5 bg-light" id="faq">
        <div class="container">
            <div class="text-center mb-5 fade-in-up">
                <h2 class="section-title">Pertanyaan Umum</h2>
                <p class="section-subtitle">Temukan jawaban untuk pertanyaan yang sering diajukan tentang EduPortal</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item fade-in-up" data-delay="0">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Apa itu EduPortal?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    EduPortal adalah sistem manajemen pembelajaran akademik yang dirancang untuk memudahkan Admin, Dosen, dan Mahasiswa dalam mengelola aktivitas pembelajaran. Platform ini menyediakan fitur untuk manajemen mata kuliah, upload materi, sistem tugas dan penilaian, serta pengumuman terpusat.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item fade-in-up" data-delay="100">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Bagaimana cara masuk ke EduPortal?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Untuk masuk ke EduPortal, klik tombol "Masuk ke EduPortal" di halaman ini atau akses halaman login. Masukkan username dan password yang telah diberikan oleh administrator. Setelah login, Anda akan diarahkan ke dashboard sesuai dengan role Anda (Admin, Dosen, atau Mahasiswa).
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item fade-in-up" data-delay="200">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Fitur apa saja yang tersedia di EduPortal?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    EduPortal menyediakan berbagai fitur termasuk: Manajemen Mata Kuliah (Admin), Upload & Download Materi (Dosen/Mahasiswa), Sistem Tugas & Penilaian, Pengumuman Terpusat, Dashboard Interaktif, dan RESTful API untuk integrasi dengan sistem lain.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item fade-in-up" data-delay="300">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Apakah ada dokumentasi API?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, EduPortal menyediakan RESTful API yang lengkap. Dokumentasi API dapat diakses melalui halaman <a href="webservice/README.md" target="_blank">webservice/README.md</a> atau melalui endpoint API yang tersedia. API mendukung operasi CRUD untuk berbagai resource seperti mata kuliah, materi, tugas, dan pengumuman.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-5 bg-dark text-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="footer-brand">
                        <h3 class="footer-logo">
                            <i class="fas fa-graduation-cap me-2"></i>EduPortal
                        </h3>
                        <p class="footer-tagline">Sistem Manajemen Pembelajaran Akademik</p>
                        <p class="footer-description">Platform terintegrasi untuk mengelola pembelajaran akademik dengan mudah dan efisien.</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#hero">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#roles">Roles</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="footer-title">Fitur</h5>
                    <ul class="footer-links">
                        <li><a href="#features">Manajemen Mata Kuliah</a></li>
                        <li><a href="#features">Upload Materi</a></li>
                        <li><a href="#features">Sistem Tugas</a></li>
                        <li><a href="#features">RESTful API</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="footer-title">Akses</h5>
                    <ul class="footer-links">
                        <li><a href="login.php">Login</a></li>
                        <li><a href="webservice/README.md" target="_blank">API Documentation</a></li>
                    </ul>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="footer-copyright">&copy; 2025 EduPortal - All Rights Reserved</p>
                    <p class="footer-credits">Dibuat dengan <i class="fas fa-heart text-danger"></i> oleh Tim EduPortal</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Smooth scroll untuk anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
