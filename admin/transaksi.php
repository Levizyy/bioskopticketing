<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$trans = mysqli_query($conn,
    "SELECT t.*, u.nama as user_nama, u.email, f.judul, j.studio, j.tanggal, j.jam, j.harga as harga_tiket
     FROM transaksi t
     JOIN users u ON t.user_id=u.id
     JOIN jadwal j ON t.jadwal_id=j.id
     JOIN film f ON j.film_id=f.id
     ORDER BY t.tanggal_pesan DESC");

$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total, SUM(total_harga) as revenue, SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END) as confirmed FROM transaksi"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Admin <?= SITE_NAME ?></title>
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
        .stat-sm { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:18px 20px; display:flex; align-items:center; gap:15px; }
        .stat-sm-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
        .stat-sm-value { font-size:1.4rem; font-weight:900; }
        .stat-sm-label { color:#888; font-size:.8rem; }
        .section-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:25px; }
        .table-dark-custom { color:#ccc; }
        .table-dark-custom thead th { background:rgba(255,255,255,.05); border-color:var(--border); color:#888; font-size:.75rem; text-transform:uppercase; padding:10px 14px; }
        .table-dark-custom tbody td { border-color:var(--border); padding:10px 14px; vertical-align:middle; font-size:.875rem; }
        .status-badge { padding:4px 12px; border-radius:20px; font-size:.75rem; font-weight:700; }
        .s-confirmed { background:rgba(16,185,129,.15); color:#10b981; }
        .s-pending { background:rgba(255,193,7,.15); color:#ffc107; }
        .s-cancelled { background:rgba(233,69,96,.15); color:#e94560; }
        .seat-chip { background:rgba(233,69,96,.15); color:#e94560; border:1px solid rgba(233,69,96,.2); padding:2px 8px; border-radius:12px; font-size:.75rem; font-weight:700; display:inline-block; margin:1px; }
        .search-bar { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:10px; padding:10px 16px; color:#fff; width:280px; }
        .search-bar::placeholder { color:#555; }
        .search-bar:focus { outline:none; border-color:var(--red); }
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
        <a href="user.php" class="sidebar-link"><i class="fas fa-users"></i>Kelola User</a>
        <a href="transaksi.php" class="sidebar-link active"><i class="fas fa-receipt"></i>Transaksi</a>
    </nav>
    <div class="sidebar-footer">
        <a href="../auth/logout.php" style="color:#888;text-decoration:none;font-size:.85rem"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
    </div>
</aside>

<main class="main-content">
    <div class="mb-4">
        <div class="page-title">Data <span style="color:var(--red)">Transaksi</span></div>
        <div style="color:#666;font-size:.9rem">Semua riwayat pemesanan tiket</div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-sm">
                <div class="stat-sm-icon" style="background:rgba(233,69,96,.15);color:var(--red)"><i class="fas fa-receipt"></i></div>
                <div><div class="stat-sm-value"><?= $stats['total'] ?></div><div class="stat-sm-label">Total Transaksi</div></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-sm">
                <div class="stat-sm-icon" style="background:rgba(16,185,129,.15);color:#10b981"><i class="fas fa-check-circle"></i></div>
                <div><div class="stat-sm-value"><?= $stats['confirmed'] ?></div><div class="stat-sm-label">Confirmed</div></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-sm">
                <div class="stat-sm-icon" style="background:rgba(139,92,246,.15);color:#8b5cf6"><i class="fas fa-money-bill-wave"></i></div>
                <div><div class="stat-sm-value" style="font-size:1.1rem"><?= formatRupiah($stats['revenue'] ?? 0) ?></div><div class="stat-sm-label">Total Pendapatan</div></div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 style="margin:0;font-weight:700"><i class="fas fa-list text-danger me-2"></i>Semua Transaksi</h5>
            <input type="text" class="search-bar" id="searchInput" placeholder="🔍 Cari nama, film, kode..." onkeyup="filterTable()">
        </div>
        <div class="table-responsive">
            <table class="table table-dark-custom" id="transTable">
                <thead>
                    <tr><th>Kode</th><th>User</th><th>Film</th><th>Studio</th><th>Jadwal</th><th>Kursi</th><th>Total</th><th>Status</th><th>Tanggal Pesan</th></tr>
                </thead>
                <tbody>
                    <?php while ($t = mysqli_fetch_assoc($trans)): ?>
                    <tr>
                        <td style="font-family:monospace;color:#888;font-size:.8rem"><?= htmlspecialchars($t['kode_booking']) ?></td>
                        <td>
                            <div style="font-weight:600"><?= htmlspecialchars($t['user_nama']) ?></div>
                            <div style="color:#666;font-size:.75rem"><?= htmlspecialchars($t['email']) ?></div>
                        </td>
                        <td style="font-weight:600;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($t['judul']) ?></td>
                        <td style="color:#888"><?= htmlspecialchars($t['studio']) ?></td>
                        <td style="color:#888;font-size:.85rem">
                            <?= date('d/m/Y', strtotime($t['tanggal'])) ?><br>
                            <span style="color:#e94560"><?= date('H:i', strtotime($t['jam'])) ?></span>
                        </td>
                        <td>
                            <?php foreach (explode(',', $t['kursi']) as $s): ?>
                            <span class="seat-chip"><?= trim(htmlspecialchars($s)) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td style="color:#10b981;font-weight:700"><?= formatRupiah($t['total_harga']) ?></td>
                        <td><span class="status-badge s-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
                        <td style="color:#888;font-size:.8rem"><?= date('d M H:i', strtotime($t['tanggal_pesan'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#transTable tbody tr');
    rows.forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
</body>
</html>
