<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Cek apakah user memiliki peran Admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Proteksi halaman khusus Admin (Cegah diakses langsung via URL)
function requireAdmin() {
    checkAuth();
    if (!isAdmin()) {
        http_response_code(403);
        die("<h3>403 Forbidden: Anda tidak memiliki akses untuk tindakan ini.</h3><a href='index.php'>Kembali ke Dashboard</a>");
    }
}