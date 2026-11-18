<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: #0a0a1a;
            background-image: 
                radial-gradient(ellipse at 20% 50%, rgba(233,69,96,0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(16,185,129,0.1) 0%, transparent 50%);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            overflow: hidden;
        }
        .bg-animated {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 0; overflow: hidden;
        }
        .bg-animated span {
            position: absolute; display: block; width: 20px; height: 20px;
            background: rgba(233,69,96,0.1); animation: animate 25s linear infinite;
            bottom: -150px; border-radius: 4px;
        }
        .bg-animated span:nth-child(1) { left:25%; width:80px; height:80px; animation-delay:0s; }
        .bg-animated span:nth-child(2) { left:10%; width:20px; height:20px; animation-delay:2s; animation-duration:12s; }
        .bg-animated span:nth-child(3) { left:70%; width:20px; height:20px; animation-delay:4s; }
        .bg-animated span:nth-child(4) { left:40%; width:60px; height:60px; animation-delay:0s; animation-duration:18s; }
        .bg-animated span:nth-child(5) { left:65%; width:20px; height:20px; animation-delay:0s; }
        @keyframes animate {
            0% { transform: translateY(0) rotate(0deg); opacity:1; border-radius:0; }
            100% { transform: translateY(-1000px) rotate(720deg); opacity:0; border-radius:50%; }
        }
        .login-wrapper { position: relative; z-index: 1; width: 100%; max-width: 440px; padding: 20px; }
        .brand { text-align: center; margin-bottom: 35px; }
        .brand-logo {
            width: 70px; height: 70px; background: linear-gradient(135deg, #e94560, #c02942);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px; font-size: 28px; color: white;
            box-shadow: 0 0 30px rgba(233,69,96,0.5);
        }
        .brand h1 { color: #fff; font-size: 2rem; font-weight: 800; letter-spacing: 2px; }
        .brand h1 span { color: #e94560; }
        .brand p { color: #888; font-size: 0.9rem; margin-top: 5px; }
        .login-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px; padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .login-card h2 { color: #fff; font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; }
        .login-card p { color: #888; font-size: 0.9rem; margin-bottom: 30px; }
        .form-label { color: #ccc; font-size: 0.875rem; font-weight: 500; margin-bottom: 8px; }
        .input-group-text {
            background: rgba(233,69,96,0.15); border: 1px solid rgba(255,255,255,0.1);
            color: #e94560; border-right: none;
        }
        .form-control {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: #fff; padding: 12px 16px; border-left: none;
            transition: all 0.3s;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.08); border-color: #e94560;
            box-shadow: 0 0 0 3px rgba(233,69,96,0.15); color: #fff;
        }
        .form-control::placeholder { color: #555; }
        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #e94560, #c02942);
            border: none; border-radius: 10px; color: #fff;
            font-size: 1rem; font-weight: 600; letter-spacing: 0.5px;
            transition: all 0.3s; cursor: pointer;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(233,69,96,0.4); }
        .divider { text-align: center; margin: 20px 0; color: #555; position: relative; }
        .divider::before, .divider::after {
            content: ''; position: absolute; top: 50%; width: 45%; height: 1px;
            background: rgba(255,255,255,0.1);
        }
        .divider::before { left: 0; } .divider::after { right: 0; }
        .demo-accounts { background: rgba(233,69,96,0.08); border: 1px solid rgba(233,69,96,0.2); border-radius: 10px; padding: 15px; }
        .demo-accounts h6 { color: #e94560; font-size: 0.8rem; font-weight: 600; margin-bottom: 8px; }
        .demo-item { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .demo-item:last-child { border: none; }
        .demo-item span { color: #ccc; font-size: 0.8rem; }
        .demo-item strong { color: #fff; font-size: 0.8rem; }
        .alert-danger { background: rgba(233,69,96,0.15); border: 1px solid rgba(233,69,96,0.3); color: #ff6b7a; border-radius: 10px; }
        .register-link { text-align: center; margin-top: 20px; color: #888; font-size: 0.875rem; }
        .register-link a { color: #e94560; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
<div class="bg-animated">
    <span></span><span></span><span></span><span></span><span></span>
</div>

<div class="login-wrapper">
    <div class="brand">
        <div class="brand-logo"><i class="fas fa-film"></i></div>
        <h1><?= SITE_NAME ?><span>.</span></h1>
        <p>Premium Cinema Experience</p>
    </div>

    <div class="login-card">
        <h2>Selamat Datang 👋</h2>
        <p>Masuk untuk memesan tiket bioskop favoritmu</p>

        <?php if ($error): ?>
        <div class="alert alert-danger py-2">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form action="login_proses.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" required
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <span class="input-group-text" style="border-left:none;cursor:pointer" onclick="togglePass()">
                        <i class="fas fa-eye" id="eye-icon" style="color:#888"></i>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Masuk Sekarang
            </button>
        </form>

        <div class="divider">Demo Akun</div>
        <div class="demo-accounts">
            <h6><i class="fas fa-info-circle me-1"></i>AKUN DEMO</h6>
            <div class="demo-item">
                <span>👤 Admin</span>
                <strong>admin@bioskop.com / password</strong>
            </div>
            <div class="demo-item">
                <span>🎬 User</span>
                <strong>user@bioskop.com / password</strong>
            </div>
        </div>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar Sekarang</a>
        </div>
    </div>
</div>

<script>
function togglePass() {
    const p = document.getElementById('password');
    const i = document.getElementById('eye-icon');
    if (p.type === 'password') { p.type = 'text'; i.className = 'fas fa-eye-slash'; }
    else { p.type = 'password'; i.className = 'fas fa-eye'; }
}
</script>
</body>
</html>
