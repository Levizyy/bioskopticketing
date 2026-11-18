<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
requireLogin();

$jadwal_id = intval($_GET['jadwal'] ?? 0);
if (!$jadwal_id) { header('Location: home.php'); exit; }

$jadwal = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT j.*, f.judul, f.id as film_id, f.genre, f.rating 
     FROM jadwal j JOIN film f ON j.film_id=f.id 
     WHERE j.id=$jadwal_id"));
if (!$jadwal) { header('Location: home.php'); exit; }

$kursi_list = mysqli_query($conn, "SELECT * FROM kursi WHERE jadwal_id=$jadwal_id ORDER BY nomor_kursi");
$kursi_data = [];
while ($k = mysqli_fetch_assoc($kursi_list)) {
    $kursi_data[$k['nomor_kursi']] = $k['status'];
}

$rows = ['A','B','C','D','E'];
$cols = range(1, 8);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Kursi - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#e94560; --dark:#0a0a1a; --card:#12122a; --border:rgba(255,255,255,0.08); }
        body { background:var(--dark); color:#fff; font-family:'Segoe UI',sans-serif; min-height:100vh; }
        .navbar { background:rgba(10,10,26,.95); backdrop-filter:blur(20px); border-bottom:1px solid var(--border); padding:15px 0; position:sticky; top:0; z-index:1000; }
        .navbar-brand { font-size:1.6rem; font-weight:900; color:#fff !important; letter-spacing:2px; }
        .navbar-brand span { color:var(--red); }
        .nav-link { color:#ccc !important; }
        .nav-link:hover { color:var(--red) !important; }
        .btn-logout { background:transparent; border:1px solid var(--red); color:var(--red); padding:6px 18px; border-radius:20px; font-size:.85rem; }
        .btn-logout:hover { background:var(--red); color:#fff; }
        .section-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:25px; }
        /* Screen */
        .screen-wrap { text-align:center; margin-bottom:35px; }
        .screen {
            width:80%; max-width:500px; height:12px; margin:0 auto 8px;
            background:linear-gradient(to bottom,rgba(255,255,255,.3),rgba(255,255,255,.05));
            border-radius:50% / 100%;
            box-shadow:0 4px 20px rgba(255,255,255,.1);
        }
        .screen-label { color:#666; font-size:.8rem; letter-spacing:4px; text-transform:uppercase; }
        /* Seats */
        .seat-grid { display:flex; flex-direction:column; align-items:center; gap:10px; }
        .seat-row { display:flex; align-items:center; gap:8px; }
        .row-label { width:22px; text-align:center; color:#666; font-size:.8rem; font-weight:700; }
        .seat {
            width:38px; height:38px; border-radius:8px 8px 4px 4px; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            font-size:.7rem; font-weight:700; transition:all .2s; position:relative;
            border:2px solid transparent; user-select:none;
        }
        .seat.available { background:rgba(16,185,129,.2); border-color:rgba(16,185,129,.4); color:#10b981; }
        .seat.available:hover { background:rgba(16,185,129,.4); transform:scale(1.1); }
        .seat.taken { background:rgba(233,69,96,.2); border-color:rgba(233,69,96,.3); color:#e94560; cursor:not-allowed; }
        .seat.selected { background:linear-gradient(135deg,#e94560,#c02942); border-color:#e94560; color:#fff; transform:scale(1.05); box-shadow:0 4px 15px rgba(233,69,96,.4); }
        .seat-gap { width:20px; }
        /* Legend */
        .legend { display:flex; gap:20px; justify-content:center; margin-top:20px; flex-wrap:wrap; }
        .legend-item { display:flex; align-items:center; gap:8px; font-size:.85rem; color:#ccc; }
        .legend-box { width:20px; height:20px; border-radius:4px; }
        .legend-box.av { background:rgba(16,185,129,.3); border:2px solid rgba(16,185,129,.5); }
        .legend-box.tk { background:rgba(233,69,96,.2); border:2px solid rgba(233,69,96,.3); }
        .legend-box.sl { background:var(--red); border:2px solid var(--red); }
        /* Summary */
        .summary-card { background:rgba(233,69,96,.08); border:1px solid rgba(233,69,96,.2); border-radius:16px; padding:25px; position:sticky; top:80px; }
        .summary-card h5 { color:#e94560; font-weight:700; margin-bottom:20px; }
        .summary-item { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid rgba(255,255,255,.05); }
        .summary-item:last-child { border:none; }
        .summary-item span:first-child { color:#888; font-size:.875rem; }
        .summary-item span:last-child { color:#fff; font-weight:600; font-size:.875rem; }
        .total-row { background:rgba(233,69,96,.1); border-radius:10px; padding:12px; margin-top:15px; display:flex; justify-content:space-between; }
        .total-row span:first-child { color:#ccc; font-weight:600; }
        .total-row span:last-child { color:#e94560; font-weight:800; font-size:1.1rem; }
        .btn-confirm { width:100%; padding:14px; background:linear-gradient(135deg,#e94560,#c02942); border:none; border-radius:12px; color:#fff; font-weight:700; font-size:1rem; transition:.3s; margin-top:15px; }
        .btn-confirm:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(233,69,96,.4); }
        .btn-confirm:disabled { opacity:.5; cursor:not-allowed; transform:none; box-shadow:none; }
        .selected-seats-list { min-height:40px; background:rgba(255,255,255,.04); border-radius:8px; padding:10px; display:flex; flex-wrap:wrap; gap:6px; }
        .seat-tag { background:var(--red); color:#fff; padding:3px 10px; border-radius:15px; font-size:.8rem; font-weight:700; }
        .info-row { background:rgba(255,255,255,.04); border-radius:10px; padding:12px 15px; margin-bottom:8px; }
        .user-badge { background:rgba(233,69,96,.1); border:1px solid rgba(233,69,96,.2); color:#e94560; padding:4px 12px; border-radius:20px; font-size:.8rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="home.php"><i class="fas fa-film me-2"></i><?= SITE_NAME ?><span>.</span></a>
        <div class="d-flex align-items-center gap-3">
            <span class="user-badge"><i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
            <a href="../auth/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="background:transparent;padding:0">
            <li class="breadcrumb-item"><a href="home.php" style="color:var(--red)">Home</a></li>
            <li class="breadcrumb-item"><a href="detail_film.php?id=<?= $jadwal['film_id'] ?>" style="color:var(--red)"><?= htmlspecialchars($jadwal['judul']) ?></a></li>
            <li class="breadcrumb-item active" style="color:#888">Pilih Kursi</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Seat Map -->
        <div class="col-lg-8">
            <div class="section-card">
                <h4 class="mb-4"><i class="fas fa-chair text-danger me-2"></i>Pilih Kursi</h4>
                
                <div class="info-row mb-3">
                    <div class="row text-center">
                        <div class="col-3"><div style="color:#888;font-size:.75rem">FILM</div><div style="font-weight:700;font-size:.9rem"><?= htmlspecialchars($jadwal['judul']) ?></div></div>
                        <div class="col-3"><div style="color:#888;font-size:.75rem">STUDIO</div><div style="font-weight:700;font-size:.9rem"><?= htmlspecialchars($jadwal['studio']) ?></div></div>
                        <div class="col-3"><div style="color:#888;font-size:.75rem">TANGGAL</div><div style="font-weight:700;font-size:.9rem"><?= date('d M Y', strtotime($jadwal['tanggal'])) ?></div></div>
                        <div class="col-3"><div style="color:#888;font-size:.75rem">JAM</div><div style="font-weight:700;font-size:.9rem"><?= date('H:i', strtotime($jadwal['jam'])) ?></div></div>
                    </div>
                </div>

                <div class="screen-wrap">
                    <div class="screen"></div>
                    <div class="screen-label">Layar</div>
                </div>

                <div class="seat-grid">
                    <?php foreach ($rows as $r): ?>
                    <div class="seat-row">
                        <div class="row-label"><?= $r ?></div>
                        <?php foreach ($cols as $c): ?>
                        <?php if ($c == 5): ?><div class="seat-gap"></div><?php endif; ?>
                        <?php 
                        $nomor = $r.$c;
                        $status = $kursi_data[$nomor] ?? 'tersedia';
                        $cls = $status === 'terisi' ? 'taken' : 'available';
                        $onclick = $status === 'tersedia' ? "toggleSeat(this,'$nomor')" : '';
                        ?>
                        <div class="seat <?= $cls ?>" 
                             onclick="<?= $onclick ?>" 
                             data-nomor="<?= $nomor ?>"
                             title="Kursi <?= $nomor ?> - <?= ucfirst($status) ?>">
                            <?= $nomor ?>
                        </div>
                        <?php endforeach; ?>
                        <div class="row-label"><?= $r ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="legend">
                    <div class="legend-item"><div class="legend-box av"></div>Tersedia</div>
                    <div class="legend-item"><div class="legend-box tk"></div>Terisi</div>
                    <div class="legend-item"><div class="legend-box sl"></div>Dipilih</div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="col-lg-4">
            <div class="summary-card">
                <h5><i class="fas fa-receipt me-2"></i>Ringkasan Pesanan</h5>
                
                <div class="summary-item"><span>Film</span><span><?= htmlspecialchars($jadwal['judul']) ?></span></div>
                <div class="summary-item"><span>Studio</span><span><?= htmlspecialchars($jadwal['studio']) ?></span></div>
                <div class="summary-item"><span>Tanggal</span><span><?= date('d M Y', strtotime($jadwal['tanggal'])) ?></span></div>
                <div class="summary-item"><span>Jam</span><span><?= date('H:i', strtotime($jadwal['jam'])) ?></span></div>
                <div class="summary-item"><span>Harga/kursi</span><span><?= formatRupiah($jadwal['harga']) ?></span></div>
                
                <div class="mt-3 mb-2" style="color:#888;font-size:.85rem">Kursi Dipilih:</div>
                <div class="selected-seats-list" id="selectedList">
                    <span style="color:#555;font-size:.8rem">Belum ada kursi dipilih</span>
                </div>

                <div class="total-row mt-3">
                    <span>Total Pembayaran</span>
                    <span id="totalHarga"><?= formatRupiah(0) ?></span>
                </div>

                <form action="pesan_tiket.php" method="POST">
                    <input type="hidden" name="jadwal_id" value="<?= $jadwal_id ?>">
                    <input type="hidden" name="kursi_dipilih" id="kursiInput" value="">
                    <input type="hidden" name="total" id="totalInput" value="0">
                    <button type="submit" class="btn-confirm" id="btnConfirm" disabled>
                        <i class="fas fa-check-circle me-2"></i>Lanjut ke Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
const harga = <?= $jadwal['harga'] ?>;
let selected = [];

function toggleSeat(el, nomor) {
    if (el.classList.contains('taken')) return;
    if (el.classList.contains('selected')) {
        el.classList.remove('selected');
        el.classList.add('available');
        selected = selected.filter(s => s !== nomor);
    } else {
        if (selected.length >= 8) { alert('Maksimal 8 kursi per pemesanan!'); return; }
        el.classList.remove('available');
        el.classList.add('selected');
        selected.push(nomor);
    }
    updateSummary();
}

function updateSummary() {
    const list = document.getElementById('selectedList');
    const total = selected.length * harga;
    
    if (selected.length === 0) {
        list.innerHTML = '<span style="color:#555;font-size:.8rem">Belum ada kursi dipilih</span>';
    } else {
        list.innerHTML = selected.map(s => `<span class="seat-tag">${s}</span>`).join('');
    }
    
    document.getElementById('totalHarga').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('kursiInput').value = selected.join(',');
    document.getElementById('totalInput').value = total;
    document.getElementById('btnConfirm').disabled = selected.length === 0;
}
</script>
</body>
</html>
