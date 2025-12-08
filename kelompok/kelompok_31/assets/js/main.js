// Custom JavaScript - EduPortal
// Semua anggota bisa tambahkan function di sini

/**
 * Helper function untuk AJAX error handling
 */
function handleAjaxError(xhr, status, error) {
    console.error('AJAX Error:', error);
    console.error('Status:', status);
    console.error('Response:', xhr.responseText);
    
    let errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
    
    if (xhr.status === 0) {
        errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
    } else if (xhr.status === 404) {
        errorMessage = 'Endpoint tidak ditemukan.';
    } else if (xhr.status === 500) {
        errorMessage = 'Terjadi kesalahan server. Silakan hubungi administrator.';
    }
    
    // Tampilkan alert atau toast notification
    if (typeof showAlert === 'function') {
        showAlert(errorMessage, 'danger');
    } else {
        alert(errorMessage);
    }
}

/**
 * Helper function untuk format tanggal Indonesia
 */
function formatTanggalIndo(dateString) {
    const bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    const date = new Date(dateString);
    const hari = date.getDate();
    const bulanIndex = date.getMonth();
    const tahun = date.getFullYear();
    
    return `${hari} ${bulan[bulanIndex]} ${tahun}`;
}

/**
 * Helper function untuk format angka dengan separator
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/**
 * Helper function untuk show/hide loading spinner
 */
function showLoading(element) {
    if (typeof element === 'string') {
        element = $(element);
    }
    element.prop('disabled', true);
    const originalHtml = element.html();
    element.data('original-html', originalHtml);
    element.html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');
}

function hideLoading(element) {
    if (typeof element === 'string') {
        element = $(element);
    }
    element.prop('disabled', false);
    const originalHtml = element.data('original-html');
    if (originalHtml) {
        element.html(originalHtml);
    }
}

/**
 * Helper function untuk show alert/toast
 */
function showAlert(message, type = 'info') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Cek jika ada container untuk alert
    let alertContainer = $('#alertContainer');
    if (alertContainer.length === 0) {
        // Buat container jika belum ada
        $('body').prepend('<div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;"></div>');
        alertContainer = $('#alertContainer');
    }
    
    alertContainer.append(alertHtml);
    
    // Auto dismiss setelah 5 detik
    setTimeout(function() {
        alertContainer.find('.alert').last().fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Helper function untuk AJAX request dengan error handling
 */
function ajaxRequest(url, method, data, successCallback, errorCallback) {
    $.ajax({
        url: url,
        method: method,
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (successCallback) successCallback(response);
            } else {
                if (errorCallback) {
                    errorCallback(response);
                } else {
                    showAlert(response.message || 'Operasi gagal', 'danger');
                }
            }
        },
        error: function(xhr, status, error) {
            handleAjaxError(xhr, status, error);
            if (errorCallback) {
                errorCallback({ success: false, message: 'Terjadi kesalahan' });
            }
        }
    });
}

/**
 * Initialize tooltips Bootstrap
 */
$(document).ready(function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});
