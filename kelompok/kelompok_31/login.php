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
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="text-center mb-4">EduPortal Login</h3>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div id="errorMessage" class="alert alert-danger mt-3 d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // AJAX Login Implementation
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const username = $('#username').val().trim();
            const password = $('#password').val();
            
            // Hide previous error message
            $('#errorMessage').addClass('d-none').text('');
            
            // Disable submit button and show loading
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');
            
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
                        $('#errorMessage').removeClass('d-none').text(response.message || 'Login gagal');
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    // Handle AJAX error
                    console.error('Login Error:', error);
                    $('#errorMessage').removeClass('d-none').text('Terjadi kesalahan. Silakan coba lagi.');
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    </script>
</body>
</html>
