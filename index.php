<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . 'user/home.php');
    }
} else {
    header('Location: ' . BASE_URL . 'auth/login.php');
}
exit;
?>
