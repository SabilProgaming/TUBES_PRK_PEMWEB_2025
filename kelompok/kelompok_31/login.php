<?php
/**
 * Halaman Login
 * Dikerjakan oleh: Anggota 1 (Ketua)
 * 
 * TODO:
 * - Buat form login (username & password)
 * - Implementasi AJAX untuk login
 * - Redirect ke dashboard sesuai role
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
    <title>Login - EduPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <div class="login-container">
        <!-- Left Panel: Branding (Desktop Only) -->
        <div class="login-left-panel d-none d-lg-flex">
            <div class="branding-content">
                <div class="branding-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="branding-title">EduPortal</h1>
                <p class="branding-welcome">Selamat Datang</p>
                <p class="branding-tagline">Sistem Manajemen Pembelajaran Akademik</p>
                <div class="decorative-shapes">
                    <div class="shape shape-1"></div>
                    <div class="shape shape-2"></div>
                    <div class="shape shape-3"></div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Login Form -->
        <div class="login-right-panel">
            <div class="login-card-wrapper">
                <div class="login-card">
                    <div class="login-header">
                        <h2 class="login-title">Login</h2>
                        <p class="login-subtitle">Masuk ke akun Anda</p>
                    </div>
                    
                    <form id="loginForm" class="login-form">
                        <!-- Username Input -->
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" class="form-control" id="username" placeholder="Masukkan username" required>
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" class="form-control" id="password" placeholder="Masukkan password" required>
                                <button type="button" class="password-toggle" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-login w-100">
                            <span class="btn-text">Login</span>
                            <span class="btn-loader d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>Memproses...
                            </span>
                        </button>
                    </form>

                    <!-- Error Message -->
                    <div id="errorMessage" class="alert alert-danger mt-3 d-none" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span class="error-text"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Password Toggle Functionality
        $(document).ready(function() {
            $('#togglePassword').on('click', function() {
                const $passwordInput = $('#password');
                const $toggleIcon = $(this).find('i');
                
                if ($passwordInput.attr('type') === 'password') {
                    $passwordInput.attr('type', 'text');
                    $toggleIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    $passwordInput.attr('type', 'password');
                    $toggleIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });

        // AJAX Login Implementation
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const username = $('#username').val().trim();
            const password = $('#password').val();
            
            // Hide previous error message
            $('#errorMessage').addClass('d-none').slideUp();
            $('#errorMessage').find('.error-text').text('');
            
            // Disable submit button and show loading
            const $submitBtn = $(this).find('button[type="submit"]');
            const $btnText = $submitBtn.find('.btn-text');
            const $btnLoader = $submitBtn.find('.btn-loader');
            $submitBtn.prop('disabled', true);
            $btnText.addClass('d-none');
            $btnLoader.removeClass('d-none');
            
            // AJAX request
            $.ajax({
                url: 'api/auth/login.php',
                method: 'POST',
                data: {
                    username: username,
                    password: password
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Login berhasil, redirect ke dashboard sesuai role
                        const role = response.role;
                        window.location.href = 'dashboard/' + role + '.php';
                    } else {
                        // Login gagal, tampilkan error
                        const $errorMsg = $('#errorMessage');
                        $errorMsg.find('.error-text').text(response.message || 'Login gagal');
                        $errorMsg.removeClass('d-none').slideDown();
                        $submitBtn.prop('disabled', false);
                        $btnText.removeClass('d-none');
                        $btnLoader.addClass('d-none');
                    }
                },
                error: function(xhr, status, error) {
                    // Handle AJAX error
                    console.error('Login Error:', error);
                    const $errorMsg = $('#errorMessage');
                    $errorMsg.find('.error-text').text('Terjadi kesalahan. Silakan coba lagi.');
                    $errorMsg.removeClass('d-none').slideDown();
                    $submitBtn.prop('disabled', false);
                    $btnText.removeClass('d-none');
                    $btnLoader.addClass('d-none');
                }
            });
        });
    </script>
</body>
</html>
