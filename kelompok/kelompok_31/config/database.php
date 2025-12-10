<?php
/**
 * Database Configuration & PDO Connection Class
 * Dikerjakan oleh: Anggota 1 (Ketua)
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'eduportal';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            return null;
        }

        return $this->conn;
    }

    /**
     * Execute a query
     * @param string $query
     * @return PDOStatement|false
     */
    public function query($query) {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                return $conn->query($query);
            }
            return false;
        } catch(PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prepare a statement
     * @param string $query
     * @return PDOStatement|false
     */
    public function prepare($query) {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                return $conn->prepare($query);
            }
            return false;
        } catch(PDOException $e) {
            error_log("Prepare Error: " . $e->getMessage());
            return false;
        }
    }
}
// --- INISIALISASI UNTUK API ---
$database = new Database();
$pdo = $database->getConnection();

// Cek koneksi
if ($pdo === null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error', 
        'message' => 'Koneksi Gagal ke eduportal_local. Pastikan database ada di phpMyAdmin.'
    ]);
    exit();
}

?>

