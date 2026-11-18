<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$total_film = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM film"))['c'];
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='user'"))['c'];
$total_transaksi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transaksi"))['c'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as c FROM transaksi WHERE status='confirmed'"))['c'] ?? 0;
$films_tayang = mysqli_query($conn, "SELECT * FROM film WHERE status='tayang' ORDER BY created_at DESC LIMIT 6");
$recent_trans = mysqli_query($conn, "SELECT t.*, u.nama as user_nama, f.judul, j.tanggal, j.jam FROM transaksi t JOIN users u ON t.user_id=u.id JOIN jadwal j ON t.jadwal_id=j.id JOIN film f ON j.film_id=f.id ORDER BY t.tanggal_pesan DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#e94560; --dark:#080812; --sidebar:#0d0d22; --card:#12122a; --border:rgba(255,255,255,0.07); }
        * { box-sizing:border-box; }
        body { background:var(--dark); color:#fff; font-family:'Segoe UI',sans-serif; min-height:100vh; display:flex; }
        /* Sidebar */
        .sidebar { width:260px; min-height:100vh; background:var(--sidebar); border-right:1px solid var(--border); position:fixed; top:0; left:0; z-index:100; display:flex; flex-direction:column; }
        .sidebar-brand { padding:25px 20px; border-bottom:1px solid var(--border); }
        .sidebar-brand h2 { font-size:1.5rem; font-weight:900; letter-spacing:2px; margin:0; }
        .sidebar-brand h2 span { color:var(--red); }
        .sidebar-brand p { color:#666; font-size:.75rem; margin:4px 0 0; }
        .sidebar-nav { padding:15px 0; flex:1; }
        .nav-section { padding:8px 20px 5px; color:#444; font-size:.7rem; text-transform:uppercase; letter-spacing:2px; font-weight:600; }
        .sidebar-link { display:flex; align-items:center; gap:12px; padding:12px 20px; color:#888; text-decoration:none; transition:.2s; font-size:.9rem; }
        .sidebar-link:hover { color:#fff; background:rgba(255,255,255,.04); }
        .sidebar-link.active { color:#fff; background:rgba(233,69,96,.1); border-right:3px solid var(--red); }
        .sidebar-link i { width:18px; color:inherit; }
        .sidebar-footer { padding:15px 20px; border-top:1px solid var(--border); }
        .admin-info { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
        .admin-avatar { width:38px; height:38px; background:var(--red); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.9rem; }
        .admin-name { font-size:.9rem; font-weight:600; }
        .admin-role { font-size:.75rem; color:#666; }
        .btn-logout-sm { display:flex; align-items:center; gap:8px; color:#888; text-decoration:none; font-size:.85rem; transition:.2s; }
        .btn-logout-sm:hover { color:var(--red); }
        /* Main */
        .main-content { margin-left:260px; flex:1; padding:30px; }
        .page-title { font-size:1.8rem; font-weight:900; margin-bottom:5px; }
        .page-subtitle { color:#666; font-size:.9rem; margin-bottom:30px; }
        /* Stats */
        .stat-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:25px; position:relative; overflow:hidden; transition:.2s; }
        .stat-card:hover { border-color:rgba(255,255,255,.12); transform:translateY(-2px); }
        .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
        .stat-red::before { background:linear-gradient(to right,#e94560,#c02942); }
        .stat-blue::before { background:linear-gradient(to right,#3b82f6,#1d4ed8); }
        .stat-green::before { background:linear-gradient(to right,#10b981,#059669); }
        .stat-purple::before { background:linear-gradient(to right,#8b5cf6,#6d28d9); }
        .stat-icon { width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; margin-bottom:15px; }
        .stat-icon.red { background:rgba(233,69,96,.15); color:var(--red); }
        .stat-icon.blue { background:rgba(59,130,246,.15); color:#3b82f6; }
        .stat-icon.green { background:rgba(16,185,129,.15); color:#10b981; }
        .stat-icon.purple { background:rgba(139,92,246,.15); color:#8b5cf6; }
        .stat-value { font-size:2rem; font-weight:900; margin:0; }
        .stat-label { color:#888; font-size:.85rem; }
        /* Section */
        .section-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:25px; margin-bottom:25px; }
        .section-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .section-header h5 { font-weight:700; margin:0; }
        .section-header a { color:var(--red); text-decoration:none; font-size:.85rem; }
        /* Film grid */
        .film-mini { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px; }
        .film-mini-poster { width:45px; height:60px; background:linear-gradient(135deg,#1e1e3f,#2d1b4e); border-radius:8px; display:flex; align-items:center; justify-content:center; color:rgba(233,69,96,.4); font-size:1.2rem; flex-shrink:0; }
        .film-mini-title { font-weight:600; font-size:.9rem; }
        .film-mini-genre { color:#888; font-size:.8rem; }
        /* Table */
        .table-dark-custom { color:#ccc; }
        .table-dark-custom thead th { background:rgba(255,255,255,.05); border-color:var(--border); color:#888; font-size:.8rem; text-transform:uppercase; letter-spacing:1px; padding:12px 16px; }
        .table-dark-custom tbody td { border-color:var(--border); padding:12px 16px; vertical-align:middle; font-size:.875rem; }
        .table-dark-custom tbody tr:hover td { background:rgba(255,255,255,.02); }
        .status-badge { padding:4px 12px; border-radius:20px; font-size:.75rem; font-weight:700; }
        .s-confirmed { background:rgba(16,185,129,.15); color:#10b981; }
        .s-pending { background:rgba(255,193,7,.15); color:#ffc107; }
        .s-cancelled { background:rgba(233,69,96,.15); color:#e94560; }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <h2><i class="fas fa-film me-2" style="color:var(--red)"></i><?= SITE_NAME ?><span>.</span></h2>
        <p>Admin Panel</p>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a href="dashboard.php" class="sidebar-link active"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="film.php" class="sidebar-link"><i class="fas fa-film"></i>Kelola Film</a>
        <a href="jadwal.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i>Kelola Jadwal</a>
        <div class="nav-section">Management</div>
        <a href="user.php" class="sidebar-link"><i class="fas fa-users"></i>Kelola User</a>
        <a href="transaksi.php" class="sidebar-link"><i class="fas fa-receipt"></i>Transaksi</a>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-info">
            <div class="admin-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
            <div><div class="admin-name"><?= htmlspecialchars($_SESSION['nama']) ?></div><div class="admin-role">Administrator</div></div>
        </div>
        <a href="../auth/logout.php" class="btn-logout-sm"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>
</aside>

<!-- Main -->
<main class="main-content">
    <div class="page-title">Dashboard <span style="color:var(--red)">Admin</span></div>
    <div class="page-subtitle">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>! 👋 &nbsp;—&nbsp; <?= date('l, d F Y') ?></div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-red">
                <div class="stat-icon red"><i class="fas fa-film"></i></div>
                <div class="stat-value"><?= $total_film ?></div>
                <div class="stat-label">Total Film</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-blue">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?= $total_user ?></div>
                <div class="stat-label">Total User</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-green">
                <div class="stat-icon green"><i class="fas fa-ticket-alt"></i></div>
                <div class="stat-value"><?= $total_transaksi ?></div>
                <div class="stat-label">Total Transaksi</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-purple">
                <div class="stat-icon purple"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-value" style="font-size:1.4rem"><?= formatRupiah($total_revenue) ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Films -->
        <div class="col-lg-5">
            <div class="section-card">
                <div class="section-header">
                    <h5><i class="fas fa-fire text-danger me-2"></i>Film Tayang</h5>
                    <a href="film.php">Lihat Semua →</a>
                </div>
                <div class="d-flex flex-column gap-3">
                    <?php while ($f = mysqli_fetch_assoc($films_tayang)): ?>
                    <div class="film-mini">
                        <div class="film-mini-poster"><i class="fas fa-film"></i></div>
                        <div class="flex-1">
                            <div class="film-mini-title"><?= htmlspecialchars($f['judul']) ?></div>
                            <div class="film-mini-genre"><?= htmlspecialchars($f['genre']) ?> • <?= $f['durasi'] ?> min</div>
                        </div>
                        <span style="background:rgba(16,185,129,.15);color:#10b981;font-size:.75rem;padding:3px 10px;border-radius:15px;border:1px solid rgba(16,185,129,.2)">Tayang</span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-lg-7">
            <div class="section-card">
                <div class="section-header">
                    <h5><i class="fas fa-receipt text-danger me-2"></i>Transaksi Terbaru</h5>
                    <a href="transaksi.php">Lihat Semua →</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark-custom">
                        <thead>
                            <tr><th>User</th><th>Film</th><th>Jadwal</th><th>Total</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($t = mysqli_fetch_assoc($recent_trans)): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['user_nama']) ?></td>
                                <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($t['judul']) ?></td>
                                <td style="font-size:.8rem;color:#888"><?= date('d/m', strtotime($t['tanggal'])) ?> <?= date('H:i', strtotime($t['jam'])) ?></td>
                                <td style="color:#10b981"><?= formatRupiah($t['total_harga']) ?></td>
                                <td><span class="status-badge s-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
