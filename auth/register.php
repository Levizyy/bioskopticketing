<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (isLoggedIn()) { header('Location: ' . BASE_URL . 'index.php'); exit; }

$error = ''; $success = '';
if (isset($_SESSION['reg_error'])) { $error = $_SESSION['reg_error']; unset($_SESSION['reg_error']); }
if (isset($_SESSION['reg_success'])) { $success = $_SESSION['reg_success']; unset($_SESSION['reg_success']); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $konfirmasi) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($cek, 's', $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, role) VALUES (?,?,?,'user')");
            mysqli_stmt_bind_param($ins, 'sss', $nama, $email, $hash);
            if (mysqli_stmt_execute($ins)) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan, coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { min-height:100vh; background:#0a0a1a; background-image:radial-gradient(ellipse at 20% 50%,rgba(233,69,96,.15) 0%,transparent 50%); display:flex; align-items:center; justify-content:center; font-family:'Segoe UI',sans-serif; }
        .login-wrapper { width:100%; max-width:440px; padding:20px; }
        .brand { text-align:center; margin-bottom:25px; }
        .brand-logo { width:60px;height:60px;background:linear-gradient(135deg,#e94560,#c02942);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;color:#fff;box-shadow:0 0 30px rgba(233,69,96,.5); }
        .brand h1 { color:#fff;font-size:1.8rem;font-weight:800;letter-spacing:2px; }
        .brand h1 span { color:#e94560; }
        .login-card { background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:35px;box-shadow:0 20px 60px rgba(0,0,0,.5); }
        .login-card h2 { color:#fff;font-size:1.4rem;font-weight:700;margin-bottom:5px; }
        .login-card p { color:#888;font-size:.875rem;margin-bottom:25px; }
        .form-label { color:#ccc;font-size:.875rem;font-weight:500; }
        .input-group-text { background:rgba(233,69,96,.15);border:1px solid rgba(255,255,255,.1);color:#e94560;border-right:none; }
        .form-control { background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;border-left:none; }
        .form-control:focus { background:rgba(255,255,255,.08);border-color:#e94560;box-shadow:0 0 0 3px rgba(233,69,96,.15);color:#fff; }
        .form-control::placeholder { color:#555; }
        .btn-login { width:100%;padding:13px;background:linear-gradient(135deg,#e94560,#c02942);border:none;border-radius:10px;color:#fff;font-size:1rem;font-weight:600;transition:all .3s; }
        .btn-login:hover { transform:translateY(-2px);box-shadow:0 8px 25px rgba(233,69,96,.4); }
        .alert-danger { background:rgba(233,69,96,.15);border:1px solid rgba(233,69,96,.3);color:#ff6b7a;border-radius:10px; }
        .alert-success { background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#10b981;border-radius:10px; }
        .login-link { text-align:center;margin-top:18px;color:#888;font-size:.875rem; }
        .login-link a { color:#e94560;text-decoration:none;font-weight:600; }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="brand">
        <div class="brand-logo"><i class="fas fa-film"></i></div>
        <h1><?= SITE_NAME ?><span>.</span></h1>
    </div>
    <div class="login-card">
        <h2>Buat Akun Baru 🎬</h2>
        <p>Daftar untuk menikmati kemudahan pesan tiket</p>
        <?php if ($error): ?><div class="alert alert-danger py-2"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2"><i class="fas fa-check-circle me-2"></i><?= $success ?> <a href="login.php" class="alert-link">Login di sini</a></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Konfirmasi Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password" required>
                </div>
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-user-plus me-2"></i>Daftar Sekarang</button>
        </form>
        <div class="login-link">Sudah punya akun? <a href="login.php">Login di sini</a></div>
    </div>
</div>
</body>
</html>
