<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus') {
    $id = intval($_POST['id']);
    if ($id !== $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='user'");
        $msg = 'success|User berhasil dihapus!';
    } else {
        $msg = 'error|Tidak bisa menghapus akun sendiri!';
    }
}

$users = mysqli_query($conn, "SELECT u.*, (SELECT COUNT(*) FROM transaksi t WHERE t.user_id=u.id) as total_transaksi FROM users u ORDER BY u.role, u.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#e94560; --dark:#080812; --sidebar:#0d0d22; --card:#12122a; --border:rgba(255,255,255,0.07); }
        body { background:var(--dark); color:#fff; font-family:'Segoe UI',sans-serif; min-height:100vh; display:flex; }
        .sidebar { width:260px; min-height:100vh; background:var(--sidebar); border-right:1px solid var(--border); position:fixed; top:0; left:0; z-index:100; display:flex; flex-direction:column; }
        .sidebar-brand { padding:25px 20px; border-bottom:1px solid var(--border); }
        .sidebar-brand h2 { font-size:1.5rem; font-weight:900; letter-spacing:2px; margin:0; }
        .sidebar-brand h2 span { color:var(--red); }
        .sidebar-brand p { color:#666; font-size:.75rem; margin:4px 0 0; }
        .sidebar-nav { padding:15px 0; flex:1; }
        .nav-section { padding:8px 20px 5px; color:#444; font-size:.7rem; text-transform:uppercase; letter-spacing:2px; }
        .sidebar-link { display:flex; align-items:center; gap:12px; padding:12px 20px; color:#888; text-decoration:none; transition:.2s; font-size:.9rem; }
        .sidebar-link:hover { color:#fff; background:rgba(255,255,255,.04); }
        .sidebar-link.active { color:#fff; background:rgba(233,69,96,.1); border-right:3px solid var(--red); }
        .sidebar-link i { width:18px; }
        .sidebar-footer { padding:15px 20px; border-top:1px solid var(--border); }
        .main-content { margin-left:260px; flex:1; padding:30px; }
        .page-title { font-size:1.8rem; font-weight:900; margin-bottom:5px; }
        .section-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:25px; }
        .table-dark-custom { color:#ccc; }
        .table-dark-custom thead th { background:rgba(255,255,255,.05); border-color:var(--border); color:#888; font-size:.8rem; text-transform:uppercase; padding:12px 16px; }
        .table-dark-custom tbody td { border-color:var(--border); padding:12px 16px; vertical-align:middle; }
        .user-avatar { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.9rem; }
        .role-admin { background:rgba(233,69,96,.15); color:#e94560; padding:3px 12px; border-radius:15px; font-size:.75rem; font-weight:700; }
        .role-user { background:rgba(59,130,246,.15); color:#3b82f6; padding:3px 12px; border-radius:15px; font-size:.75rem; font-weight:700; }
        .btn-del { background:rgba(233,69,96,.15); color:#e94560; border:1px solid rgba(233,69,96,.2); padding:5px 12px; border-radius:8px; font-size:.8rem; cursor:pointer; }
        .alert-success-custom { background:rgba(16,185,129,.15); border:1px solid rgba(16,185,129,.3); color:#10b981; border-radius:10px; padding:12px 18px; }
        .alert-error-custom { background:rgba(233,69,96,.15); border:1px solid rgba(233,69,96,.3); color:#e94560; border-radius:10px; padding:12px 18px; }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand"><h2><i class="fas fa-film me-2" style="color:var(--red)"></i><?= SITE_NAME ?><span>.</span></h2><p>Admin Panel</p></div>
    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="film.php" class="sidebar-link"><i class="fas fa-film"></i>Kelola Film</a>
        <a href="jadwal.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i>Kelola Jadwal</a>
        <div class="nav-section">Management</div>
        <a href="user.php" class="sidebar-link active"><i class="fas fa-users"></i>Kelola User</a>
        <a href="transaksi.php" class="sidebar-link"><i class="fas fa-receipt"></i>Transaksi</a>
    </nav>
    <div class="sidebar-footer">
        <a href="../auth/logout.php" style="color:#888;text-decoration:none;font-size:.85rem"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
    </div>
</aside>

<main class="main-content">
    <div class="mb-4">
        <div class="page-title">Kelola <span style="color:var(--red)">User</span></div>
        <div style="color:#666;font-size:.9rem">Manajemen akun pengguna</div>
    </div>

    <?php if ($msg): [$t, $m] = explode('|', $msg, 2); ?>
    <div class="alert-<?= $t ?>-custom mb-4"><i class="fas fa-<?= $t==='success'?'check':'exclamation' ?>-circle me-2"></i><?= htmlspecialchars($m) ?></div>
    <?php endif; ?>

    <div class="section-card">
        <div class="table-responsive">
            <table class="table table-dark-custom">
                <thead><tr><th>#</th><th>User</th><th>Email</th><th>Role</th><th>Transaksi</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php $no=1; while ($u = mysqli_fetch_assoc($users)): 
                        $colors = ['#e94560','#3b82f6','#10b981','#8b5cf6','#f59e0b'];
                        $color = $colors[$u['id'] % count($colors)];
                    ?>
                    <tr>
                        <td style="color:#666"><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar" style="background:<?= $color ?>22;color:<?= $color ?>">
                                    <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                                </div>
                                <div style="font-weight:600"><?= htmlspecialchars($u['nama']) ?></div>
                            </div>
                        </td>
                        <td style="color:#888"><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td style="color:#10b981;font-weight:600"><?= $u['total_transaksi'] ?> pesanan</td>
                        <td style="color:#888;font-size:.85rem"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['role'] === 'user' && $u['id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus user ini beserta semua datanya?')">
                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn-del"><i class="fas fa-trash me-1"></i>Hapus</button>
                            </form>
                            <?php else: ?>
                            <span style="color:#444;font-size:.8rem">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
