<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: home.php'); exit; }

$jadwal_id = intval($_POST['jadwal_id'] ?? 0);
$kursi_str = trim($_POST['kursi_dipilih'] ?? '');
$total = intval($_POST['total'] ?? 0);

if (!$jadwal_id || !$kursi_str || $total <= 0) {
    header('Location: home.php');
    exit;
}

$jadwal = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT j.*, f.judul, f.id as film_id, f.genre 
     FROM jadwal j JOIN film f ON j.film_id=f.id 
     WHERE j.id=$jadwal_id"));
if (!$jadwal) { header('Location: home.php'); exit; }

$kursi_arr = array_filter(array_map('trim', explode(',', $kursi_str)));

// Check seats still available
foreach ($kursi_arr as $k) {
    $k = mysqli_real_escape_string($conn, $k);
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM kursi WHERE jadwal_id=$jadwal_id AND nomor_kursi='$k'"));
    if (!$check || $check['status'] === 'terisi') {
        $_SESSION['error'] = "Kursi $k sudah terisi, silakan pilih ulang.";
        header("Location: pilih_kursi.php?jadwal=$jadwal_id");
        exit;
    }
}

// Process booking on confirm
$booked = false;
$kode = '';
if (isset($_POST['confirm'])) {
    mysqli_begin_transaction($conn);
    try {
        $kode = generateKodeBooking();
        $user_id = $_SESSION['user_id'];
        $kursi_db = mysqli_real_escape_string($conn, implode(',', $kursi_arr));
        
        $ins = mysqli_prepare($conn, "INSERT INTO transaksi (user_id, jadwal_id, kursi, total_harga, status, kode_booking) VALUES (?,?,?,?,'confirmed',?)");
        mysqli_stmt_bind_param($ins, 'iisds', $user_id, $jadwal_id, $kursi_db, $total, $kode);
        if (!mysqli_stmt_execute($ins)) throw new Exception('Insert transaksi gagal');
        
        foreach ($kursi_arr as $k) {
            $k = mysqli_real_escape_string($conn, $k);
            mysqli_query($conn, "UPDATE kursi SET status='terisi' WHERE jadwal_id=$jadwal_id AND nomor_kursi='$k'");
        }
        
        mysqli_commit($conn);
        $booked = true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = 'Pemesanan gagal: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#e94560; --dark:#0a0a1a; --card:#12122a; --border:rgba(255,255,255,0.08); }
        body { background:var(--dark); color:#fff; font-family:'Segoe UI',sans-serif; min-height:100vh; }
        .navbar { background:rgba(10,10,26,.95); backdrop-filter:blur(20px); border-bottom:1px solid var(--border); padding:15px 0; position:sticky; top:0; z-index:1000; }
        .navbar-brand { font-size:1.6rem; font-weight:900; color:#fff !important; letter-spacing:2px; }
        .navbar-brand span { color:var(--red); }
        .btn-logout { background:transparent; border:1px solid var(--red); color:var(--red); padding:6px 18px; border-radius:20px; font-size:.85rem; }
        .btn-logout:hover { background:var(--red); color:#fff; }
        .section-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:30px; }
        .ticket-card {
            background:linear-gradient(135deg,#12122a 0%,#1a0a2e 100%);
            border:2px solid rgba(233,69,96,.3); border-radius:20px;
            padding:30px; position:relative; overflow:hidden;
        }
        .ticket-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(to right,#e94560,#c02942,#e94560); }
        .ticket-row { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid rgba(255,255,255,.06); }
        .ticket-row:last-child { border:none; }
        .ticket-row .label { color:#888; font-size:.875rem; }
        .ticket-row .value { color:#fff; font-weight:600; font-size:.875rem; text-align:right; }
        .total-section { background:rgba(233,69,96,.1); border-radius:12px; padding:15px 20px; margin-top:15px; display:flex; justify-content:space-between; align-items:center; }
        .total-section .label { color:#ccc; font-size:1rem; font-weight:600; }
        .total-section .value { color:#e94560; font-size:1.4rem; font-weight:900; }
        .btn-confirm { padding:14px 35px; background:linear-gradient(135deg,#e94560,#c02942); border:none; border-radius:12px; color:#fff; font-weight:700; font-size:1rem; transition:.3s; }
        .btn-confirm:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(233,69,96,.4); color:#fff; }
        .btn-back { padding:14px 35px; background:transparent; border:2px solid rgba(255,255,255,.2); border-radius:12px; color:#ccc; font-weight:600; font-size:1rem; transition:.3s; }
        .btn-back:hover { border-color:rgba(255,255,255,.4); color:#fff; }
        .seat-badge { background:var(--red); color:#fff; padding:4px 12px; border-radius:20px; font-size:.85rem; font-weight:700; display:inline-block; margin:3px; }
        /* Success */
        .success-wrap { text-align:center; padding:40px 20px; }
        .success-icon { width:90px; height:90px; background:linear-gradient(135deg,#10b981,#059669); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:40px; margin:0 auto 25px; box-shadow:0 0 40px rgba(16,185,129,.4); animation:pulse .5s ease; }
        @keyframes pulse { 0%{transform:scale(0);opacity:0} 60%{transform:scale(1.2)} 100%{transform:scale(1);opacity:1} }
        .booking-code { font-size:2rem; font-weight:900; color:#e94560; letter-spacing:4px; margin:15px 0; }
        .user-badge { background:rgba(233,69,96,.1); border:1px solid rgba(233,69,96,.2); color:#e94560; padding:4px 12px; border-radius:20px; font-size:.8rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="home.php"><i class="fas fa-film me-2"></i><?= SITE_NAME ?><span>.</span></a>
        <div class="d-flex align-items-center gap-3">
            <span class="user-badge"><i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
            <a href="../auth/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width:700px">

<?php if ($booked): ?>
<!-- SUCCESS STATE -->
<div class="section-card">
    <div class="success-wrap">
        <div class="success-icon">🎉</div>
        <h2 style="color:#fff;font-weight:900;margin-bottom:8px">Pemesanan Berhasil!</h2>
        <p style="color:#888">Tiket kamu sudah berhasil dipesan. Nikmati filmnya!</p>
        <div class="booking-code"><?= htmlspecialchars($kode) ?></div>
        <p style="color:#666;font-size:.85rem">Kode Booking — simpan kode ini</p>
    </div>

    <div class="ticket-card">
        <div class="ticket-row"><span class="label">🎬 Film</span><span class="value"><?= htmlspecialchars($jadwal['judul']) ?></span></div>
        <div class="ticket-row"><span class="label">📅 Tanggal</span><span class="value"><?= date('l, d F Y', strtotime($jadwal['tanggal'])) ?></span></div>
        <div class="ticket-row"><span class="label">🕐 Jam</span><span class="value"><?= date('H:i', strtotime($jadwal['jam'])) ?> WIB</span></div>
        <div class="ticket-row"><span class="label">🎭 Studio</span><span class="value"><?= htmlspecialchars($jadwal['studio']) ?></span></div>
        <div class="ticket-row">
            <span class="label">💺 Kursi</span>
            <span class="value"><?php foreach ($kursi_arr as $k) echo "<span class='seat-badge'>$k</span>"; ?></span>
        </div>
        <div class="total-section">
            <span class="label">Total Dibayar</span>
            <span class="value"><?= formatRupiah($total) ?></span>
        </div>
    </div>

    <div class="text-center mt-4 d-flex gap-3 justify-content-center flex-wrap">
        <a href="tiket_saya.php" class="btn-confirm text-decoration-none"><i class="fas fa-ticket-alt me-2"></i>Lihat Tiket Saya</a>
        <a href="home.php" class="btn-back text-decoration-none"><i class="fas fa-home me-2"></i>Kembali ke Home</a>
    </div>
</div>

<?php else: ?>
<!-- CONFIRMATION STATE -->
<?php if (isset($error)): ?>
<div class="alert" style="background:rgba(233,69,96,.15);border:1px solid rgba(233,69,96,.3);color:#ff6b7a;border-radius:12px;padding:15px 20px;margin-bottom:20px">
    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="section-card">
    <h4 class="mb-4"><i class="fas fa-receipt text-danger me-2"></i>Konfirmasi Pesanan</h4>

    <div class="ticket-card mb-4">
        <div class="ticket-row"><span class="label">🎬 Film</span><span class="value"><?= htmlspecialchars($jadwal['judul']) ?></span></div>
        <div class="ticket-row"><span class="label">📅 Tanggal</span><span class="value"><?= date('l, d F Y', strtotime($jadwal['tanggal'])) ?></span></div>
        <div class="ticket-row"><span class="label">🕐 Jam</span><span class="value"><?= date('H:i', strtotime($jadwal['jam'])) ?> WIB</span></div>
        <div class="ticket-row"><span class="label">🎭 Studio</span><span class="value"><?= htmlspecialchars($jadwal['studio']) ?></span></div>
        <div class="ticket-row">
            <span class="label">💺 Kursi (<?= count($kursi_arr) ?> tiket)</span>
            <span class="value"><?php foreach ($kursi_arr as $k) echo "<span class='seat-badge'>$k</span>"; ?></span>
        </div>
        <div class="ticket-row"><span class="label">💰 Harga/tiket</span><span class="value"><?= formatRupiah($jadwal['harga']) ?></span></div>
        <div class="total-section">
            <span class="label">Total Pembayaran</span>
            <span class="value"><?= formatRupiah($total) ?></span>
        </div>
    </div>

    <form method="POST">
        <input type="hidden" name="jadwal_id" value="<?= $jadwal_id ?>">
        <input type="hidden" name="kursi_dipilih" value="<?= htmlspecialchars($kursi_str) ?>">
        <input type="hidden" name="total" value="<?= $total ?>">
        <input type="hidden" name="confirm" value="1">
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="pilih_kursi.php?jadwal=<?= $jadwal_id ?>" class="btn-back text-decoration-none"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
            <button type="submit" class="btn-confirm"><i class="fas fa-check-circle me-2"></i>Konfirmasi & Bayar</button>
        </div>
    </form>
</div>
<?php endif; ?>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
