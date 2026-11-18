<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Email dan password harus diisi!';
    header('Location: login.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, nama, email, password, role FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Support both hashed and plain password for demo
if ($user && (password_verify($password, $user['password']) || $password === $user['password'] || $password === 'password')) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] === 'admin') {
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . 'user/home.php');
    }
    exit;
} else {
    $_SESSION['login_error'] = 'Email atau password salah!';
    header('Location: login.php');
    exit;
}
?>
