<?php
// config/koneksi.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bioskop_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('<div style="font-family:sans-serif;padding:30px;text-align:center;background:#1a1a2e;color:#e94560;min-height:100vh;">
        <h2>⚠️ Koneksi Database Gagal</h2>
        <p>' . mysqli_connect_error() . '</p>
        <p>Pastikan MySQL sudah berjalan dan database <strong>bioskop_db</strong> sudah dibuat.</p>
        <p>Import file <code>bioskop_db.sql</code> ke phpMyAdmin terlebih dahulu.</p>
    </div>');
}

mysqli_set_charset($conn, 'utf8mb4');

// Session helper
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'user/home.php');
        exit;
    }
}

function formatRupiah(int|float|string $angka): string {
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function generateKodeBooking() {
    return 'BKP-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Y');
}

define('BASE_URL', '/bioskop-ticketing/');
define('SITE_NAME', 'CinemaX');
?>
