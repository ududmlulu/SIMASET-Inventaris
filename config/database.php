<?php
// config/database.php

/* ==========================================================================
   PENGAMANAN SESI (SESSION HARDENING)
   ========================================================================== */
ini_set('session.cookie_httponly', 1); // Mencegah pencurian cookie via serangan XSS
ini_set('session.use_only_cookies', 1); // Memaksa penggunaan cookie untuk ID sesi
// Catatan: Jika aplikasi sudah 100% menggunakan HTTPS di hosting, hapus tanda '//' pada baris di bawah:
// ini_set('session.cookie_secure', 1); 

// 1. Inisialisasi Session jika belum berjalan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==========================================================================
   PEMBUATAN TOKEN CSRF
   ========================================================================== */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inventory_db');

/**
 * Membuat koneksi ke database MySQL
 */
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

/* ==========================================================================
   FUNGSI AUTENTIKASI DAN OTORISASI (ADMIN & VIEW ONLY)
   ========================================================================== */

function loginUser($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT id, username, password, full_name, role, is_active FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['is_active'] == 1 && password_verify($password, $row['password'])) {
            // Mencegah Session Fixation Attack
            session_regenerate_id(true);

            $_SESSION['user_id']   = $row['id'];
            $_SESSION['username']  = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['role']      = $row['role'];

            return ['success' => true, 'message' => 'Login berhasil!'];
        }
    }
    return ['success' => false, 'message' => 'Username atau password salah / akun tidak aktif.'];
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin() {
    checkAuth();
    if (!isAdmin()) {
        http_response_code(403);
        die("<div style='font-family:sans-serif; text-align:center; padding:50px;'>
                <h2>403 Forbidden</h2>
                <p>Akses Ditolak. Anda hanya memiliki hak akses <strong>View Only</strong>.</p>
                <a href='index.php'>Kembali ke Dashboard</a>
             </div>");
    }
}

function logoutUser() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: login.php");
    exit;
}

/* ==========================================================================
   FUNGSI DATA INVENTARIS & MASTER DATA
   ========================================================================== */

function getDeviceTypes($conn) {
    $sql = "SELECT * FROM device_types ORDER BY type_name";
    return $conn->query($sql);
}

function getBrandsByCategory($conn, $category = null) {
    $sql = "SELECT * FROM brands WHERE is_active = 1";
    if ($category) {
        $sql .= " AND category = '" . $conn->real_escape_string($category) . "'";
    }
    $sql .= " ORDER BY category, brand_name";
    return $conn->query($sql);
}

function getBrandById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getDeviceTypeById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM device_types WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getBrandsGroupedByCategory($conn) {
    $sql = "SELECT category, GROUP_CONCAT(id) as brand_ids, 
            GROUP_CONCAT(brand_name SEPARATOR '|') as brand_names 
            FROM brands WHERE is_active = 1 
            GROUP BY category ORDER BY category";
    return $conn->query($sql);
}

function getAllDevices($conn) {
    $sql = "SELECT * FROM v_device_inventory ORDER BY id DESC";
    return $conn->query($sql);
}

function getDeviceById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM devices WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getDeviceStatistics($conn) {
    $stats = [];
    
    $sql = "SELECT COUNT(*) as total FROM devices";
    $result = $conn->query($sql);
    $stats['total'] = $result->fetch_assoc()['total'];
    
    $sql = "SELECT dt.type_name, SUM(d.quantity) as total 
            FROM devices d 
            JOIN device_types dt ON d.device_type_id = dt.id 
            GROUP BY dt.type_name";
    $result = $conn->query($sql);
    $stats['by_type'] = [];
    while($row = $result->fetch_assoc()) {
        $stats['by_type'][$row['type_name']] = $row['total'];
    }
    
    $sql = "SELECT status, COUNT(*) as total FROM devices GROUP BY status";
    $result = $conn->query($sql);
    $stats['by_status'] = [];
    while($row = $result->fetch_assoc()) {
        $stats['by_status'][$row['status']] = $row['total'];
    }
    
    $sql = "SELECT b.category, SUM(d.quantity) as total 
            FROM devices d 
            JOIN brands b ON d.brand_id = b.id 
            GROUP BY b.category";
    $result = $conn->query($sql);
    $stats['by_brand_category'] = [];
    while($row = $result->fetch_assoc()) {
        $stats['by_brand_category'][$row['category']] = $row['total'];
    }
    
    return $stats;
}
?>