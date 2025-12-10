<?php
/**
 * Contoh Konsumsi API
 * Dikerjakan oleh: Anggota 4
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Web Service API - EduPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        pre { 
            max-height: 400px; 
            overflow-y: auto; 
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        .endpoint-card {
            transition: transform 0.2s;
        }
        .endpoint-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-code me-2"></i>EduPortal REST API Tester
                </h1>
                <p class="text-muted">Test dan konsumsi REST API endpoint EduPortal</p>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- GET Request -->
            <div class="col-md-6">
                <div class="card shadow-sm endpoint-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-download me-2"></i>GET Request</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Resource</label>
                            <select id="getResource" class="form-select">
                                <option value="mata-kuliah">Mata Kuliah</option>
                                <option value="materi">Materi</option>
                                <option value="tugas">Tugas</option>
                                <option value="pengumuman">Pengumuman</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ID (Optional)</label>
                            <input type="number" id="getResourceId" class="form-control" placeholder="Kosongkan untuk list semua">
                        </div>
                        <button class="btn btn-primary w-100" onclick="testGET()">
                            <i class="fas fa-play me-2"></i>Test GET Request
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- POST Request -->
            <div class="col-md-6">
                <div class="card shadow-sm endpoint-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-upload me-2"></i>POST Request</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Resource</label>
                            <select id="postResource" class="form-select">
                                <option value="pengumuman">Pengumuman</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">JSON Data</label>
                            <textarea id="postData" class="form-control" rows="5" placeholder='{"judul":"Judul Pengumuman","isi":"Isi pengumuman..."}'></textarea>
                        </div>
                        <button class="btn btn-success w-100" onclick="testPOST()">
                            <i class="fas fa-play me-2"></i>Test POST Request
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Response Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-code me-2"></i>Response</h5>
                        <button class="btn btn-sm btn-light" onclick="clearResponse()">
                            <i class="fas fa-trash me-1"></i>Clear
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Memproses request...</p>
                        </div>
                        <pre id="result" class="p-3 mb-0" style="display: none;"></pre>
                        <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- API Documentation -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>API Documentation</h5>
                    </div>
                    <div class="card-body">
                        <h6>Available Endpoints:</h6>
                        <ul class="list-unstyled">
                            <li><code>GET /webservice/api.php/mata-kuliah</code> - List semua mata kuliah</li>
                            <li><code>GET /webservice/api.php/mata-kuliah/{id}</code> - Detail mata kuliah</li>
                            <li><code>GET /webservice/api.php/materi</code> - List semua materi</li>
                            <li><code>GET /webservice/api.php/materi/{id}</code> - Detail materi</li>
                            <li><code>GET /webservice/api.php/tugas</code> - List semua tugas</li>
                            <li><code>GET /webservice/api.php/tugas/{id}</code> - Detail tugas</li>
                            <li><code>GET /webservice/api.php/pengumuman</code> - List semua pengumuman</li>
                            <li><code>GET /webservice/api.php/pengumuman/{id}</code> - Detail pengumuman</li>
                        </ul>
                        <h6 class="mt-3">Response Format:</h6>
                        <pre class="bg-light p-2"><code>{
  "status": "success|error",
  "message": "Pesan response",
  "data": [...],
  "error_code": 400 (jika error)
}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = 'api.php';
        
        async function testGET() {
            const resource = document.getElementById('getResource').value;
            const id = document.getElementById('getResourceId').value;
            const url = id ? `${API_BASE}/${resource}/${id}` : `${API_BASE}/${resource}`;
            
            showLoading();
            hideError();
            
            try {
                const response = await fetch(url);
                const data = await response.json();
                
                hideLoading();
                showResponse(data, response.status);
            } catch (error) {
                hideLoading();
                showError('Error: ' + error.message);
            }
        }
        
        async function testPOST() {
            const resource = document.getElementById('postResource').value;
            const postData = document.getElementById('postData').value;
            
            if (!postData.trim()) {
                showError('JSON data tidak boleh kosong');
                return;
            }
            
            let jsonData;
            try {
                jsonData = JSON.parse(postData);
            } catch (e) {
                showError('Invalid JSON format: ' + e.message);
                return;
            }
            
            showLoading();
            hideError();
            
            try {
                const response = await fetch(`${API_BASE}/${resource}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(jsonData)
                });
                const data = await response.json();
                
                hideLoading();
                showResponse(data, response.status);
            } catch (error) {
                hideLoading();
                showError('Error: ' + error.message);
            }
        }
        
        function showResponse(data, statusCode) {
            const resultEl = document.getElementById('result');
            resultEl.style.display = 'block';
            resultEl.textContent = JSON.stringify(data, null, 2);
            
            // Color code berdasarkan status
            if (statusCode >= 200 && statusCode < 300) {
                resultEl.style.borderLeft = '4px solid #28a745';
            } else if (statusCode >= 400) {
                resultEl.style.borderLeft = '4px solid #dc3545';
            } else {
                resultEl.style.borderLeft = '4px solid #007bff';
            }
        }
        
        function showError(message) {
            const errorEl = document.getElementById('errorMessage');
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }
        
        function hideError() {
            document.getElementById('errorMessage').style.display = 'none';
        }
        
        function showLoading() {
            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('result').style.display = 'none';
        }
        
        function hideLoading() {
            document.getElementById('loadingIndicator').style.display = 'none';
        }
        
        function clearResponse() {
            document.getElementById('result').style.display = 'none';
            document.getElementById('errorMessage').style.display = 'none';
        }
    </script>
</body>
</html>
