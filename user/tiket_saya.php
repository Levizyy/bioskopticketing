<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$tikets = mysqli_query($conn,
    "SELECT t.*, f.judul, f.genre, j.studio, j.tanggal, j.jam
     FROM transaksi t
     JOIN jadwal j ON t.jadwal_id=j.id
     JOIN film f ON j.film_id=f.id
     WHERE t.user_id=$user_id
     ORDER BY t.tanggal_pesan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#e94560; --dark:#0a0a1a; --card:#12122a; --border:rgba(255,255,255,0.08); }
        body { background:var(--dark); color:#fff; font-family:'Segoe UI',sans-serif; min-height:100vh; }
        .navbar { background:rgba(10,10,26,.95); backdrop-filter:blur(20px); border-bottom:1px solid var(--border); padding:15px 0; position:sticky; top:0; z-index:1000; }
        .navbar-brand { font-size:1.6rem; font-weight:900; color:#fff !important; letter-spacing:2px; }
        .navbar-brand span { color:var(--red); }
        .nav-link { color:#ccc !important; }
        .nav-link:hover, .nav-link.active { color:var(--red) !important; }
        .btn-logout { background:transparent; border:1px solid var(--red); color:var(--red); padding:6px 18px; border-radius:20px; font-size:.85rem; }
        .btn-logout:hover { background:var(--red); color:#fff; }
        .page-header { background:linear-gradient(135deg,#12122a,#1a0a1a); padding:40px 0; border-bottom:1px solid var(--border); }
        .ticket-card {
            background:var(--card); border:1px solid var(--border); border-radius:16px;
            overflow:hidden; transition:.2s; margin-bottom:16px;
        }
        .ticket-card:hover { border-color:rgba(233,69,96,.3); transform:translateY(-2px); }
        .ticket-left { padding:20px 25px; flex:1; }
        .ticket-right { background:rgba(233,69,96,.06); border-left:1px solid var(--border); padding:20px 25px; min-width:180px; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; }
        .ticket-divider { width:1px; background:repeating-linear-gradient(to bottom,var(--border) 0,var(--border) 6px,transparent 6px,transparent 12px); }
        .film-name { font-size:1.2rem; font-weight:800; margin-bottom:8px; }
        .ticket-meta { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:12px; }
        .meta-item { display:flex; align-items:center; gap:6px; color:#888; font-size:.85rem; }
        .meta-item i { color:var(--red); width:14px; }
        .seat-badge { background:rgba(233,69,96,.15); color:#e94560; border:1px solid rgba(233,69,96,.2); padding:3px 10px; border-radius:15px; font-size:.8rem; font-weight:700; display:inline-block; margin:2px; }
        .status-badge { padding:5px 14px; border-radius:20px; font-size:.8rem; font-weight:700; }
        .status-confirmed { background:rgba(16,185,129,.15); color:#10b981; border:1px solid rgba(16,185,129,.2); }
        .status-pending { background:rgba(255,193,7,.15); color:#ffc107; border:1px solid rgba(255,193,7,.2); }
        .status-cancelled { background:rgba(233,69,96,.15); color:#e94560; border:1px solid rgba(233,69,96,.2); }
        .booking-code { font-size:.75rem; color:#666; font-family:monospace; letter-spacing:1px; }
        .total-price { font-size:1.3rem; font-weight:900; color:#e94560; }
        .user-badge { background:rgba(233,69,96,.1); border:1px solid rgba(233,69,96,.2); color:#e94560; padding:4px 12px; border-radius:20px; font-size:.8rem; }
        .empty-state { text-align:center; padding:80px 20px; }
        .empty-icon { font-size:5rem; margin-bottom:20px; opacity:.2; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="home.php"><i class="fas fa-film me-2"></i><?= SITE_NAME ?><span>.</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" style="border-color:rgba(255,255,255,.2);color:#fff">
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto ms-3">
                <li class="nav-item"><a class="nav-link" href="home.php"><i class="fas fa-home me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="tiket_saya.php"><i class="fas fa-ticket-alt me-1"></i>Tiket Saya</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="user-badge"><i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="../auth/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="page-header">
    <div class="container">
        <h1 style="font-size:2rem;font-weight:900"><i class="fas fa-ticket-alt text-danger me-3"></i>Tiket Saya</h1>
        <p style="color:#888;margin-top:8px">Riwayat pemesanan tiket bioskop kamu</p>
    </div>
</div>

<div class="container py-4">
    <?php if (mysqli_num_rows($tikets) === 0): ?>
    <div class="empty-state">
        <div class="empty-icon">🎬</div>
        <h3 style="color:#fff;font-weight:700">Belum Ada Tiket</h3>
        <p style="color:#666">Kamu belum pernah memesan tiket. Yuk pesan sekarang!</p>
        <a href="home.php" style="background:var(--red);color:#fff;padding:12px 30px;border-radius:12px;text-decoration:none;font-weight:600;display:inline-block;margin-top:15px">
            <i class="fas fa-film me-2"></i>Lihat Film Tayang
        </a>
    </div>
    <?php else: ?>
    <?php while ($t = mysqli_fetch_assoc($tikets)): 
        $seats = explode(',', $t['kursi']);
        $statusClass = 'status-' . $t['status'];
        $expired = strtotime($t['tanggal']) < strtotime(date('Y-m-d'));
    ?>
    <div class="ticket-card">
        <div class="d-flex flex-wrap">
            <div class="ticket-left">
                <div class="film-name"><?= htmlspecialchars($t['judul']) ?></div>
                <div class="ticket-meta">
                    <div class="meta-item"><i class="fas fa-calendar"></i><?= date('d M Y', strtotime($t['tanggal'])) ?></div>
                    <div class="meta-item"><i class="fas fa-clock"></i><?= date('H:i', strtotime($t['jam'])) ?> WIB</div>
                    <div class="meta-item"><i class="fas fa-door-open"></i><?= htmlspecialchars($t['studio']) ?></div>
                    <div class="meta-item"><i class="fas fa-tag"></i><?= htmlspecialchars($t['genre']) ?></div>
                </div>
                <div class="mb-2">
                    <?php foreach ($seats as $s): ?>
                    <span class="seat-badge"><?= htmlspecialchars(trim($s)) ?></span>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;align-items:center;gap:12px;margin-top:10px">
                    <span class="status-badge <?= $statusClass ?>">
                        <?php if ($t['status'] === 'confirmed'): ?>✓ Confirmed
                        <?php elseif ($t['status'] === 'pending'): ?>⏳ Pending
                        <?php else: ?>✗ Cancelled<?php endif; ?>
                    </span>
                    <?php if ($expired): ?><span style="color:#666;font-size:.8rem">🏁 Film Telah Tayang</span><?php endif; ?>
                </div>
            </div>
            <div class="ticket-right">
                <div class="booking-code"><?= htmlspecialchars($t['kode_booking']) ?></div>
                <div class="total-price mt-2"><?= formatRupiah($t['total_harga']) ?></div>
                <div style="color:#888;font-size:.75rem;margin-top:4px"><?= count($seats) ?> Tiket</div>
                <div style="color:#666;font-size:.75rem;margin-top:8px"><?= date('d M Y H:i', strtotime($t['tanggal_pesan'])) ?></div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
