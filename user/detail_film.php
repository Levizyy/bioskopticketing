<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: home.php'); exit; }

$film = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM film WHERE id=$id AND status='tayang'"));
if (!$film) { header('Location: home.php'); exit; }

$jadwals = mysqli_query($conn, "SELECT * FROM jadwal WHERE film_id=$id AND tanggal >= CURDATE() ORDER BY tanggal, jam");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($film['judul']) ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#e94560; --dark:#0a0a1a; --card:#12122a; --border:rgba(255,255,255,0.08); }
        body { background:var(--dark); color:#fff; font-family:'Segoe UI',sans-serif; min-height:100vh; }
        .navbar { background:rgba(10,10,26,.95); backdrop-filter:blur(20px); border-bottom:1px solid var(--border); padding:15px 0; position:sticky; top:0; z-index:1000; }
        .navbar-brand { font-size:1.6rem; font-weight:900; color:#fff !important; letter-spacing:2px; }
        .navbar-brand span { color:var(--red); }
        .nav-link { color:#ccc !important; font-weight:500; }
        .nav-link:hover { color:var(--red) !important; }
        .btn-logout { background:transparent; border:1px solid var(--red); color:var(--red); padding:6px 18px; border-radius:20px; font-size:.85rem; }
        .btn-logout:hover { background:var(--red); color:#fff; }
        .film-hero { background:linear-gradient(135deg,#0d0d22 0%,#1a0a1a 100%); padding:50px 0; position:relative; overflow:hidden; }
        .film-hero::before { content:''; position:absolute; top:0; left:0; right:0; bottom:0; background:linear-gradient(to right,rgba(10,10,26,.95) 0%,rgba(10,10,26,.7) 100%); z-index:1; }
        .film-hero .container { position:relative; z-index:2; }
        .poster-wrap { border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.7); max-width:250px; }
        .poster-wrap img { width:100%; display:block; }
        .poster-placeholder-lg { width:250px; height:370px; background:linear-gradient(135deg,#1e1e3f,#2d1b4e); border-radius:16px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:15px; }
        .poster-placeholder-lg i { font-size:4rem; color:rgba(233,69,96,.4); }
        .film-title-lg { font-size:2.2rem; font-weight:900; margin-bottom:15px; }
        .meta-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.08); border:1px solid var(--border); border-radius:20px; padding:5px 14px; font-size:.85rem; color:#ccc; }
        .meta-badge i { color:var(--red); }
        .synopsis { color:#bbb; line-height:1.8; font-size:.95rem; max-width:600px; }
        .section-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:25px; margin-bottom:25px; }
        .section-card h4 { color:#fff; font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:10px; }
        .section-card h4 i { color:var(--red); }
        /* Date tabs */
        .date-tabs { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
        .date-tab { background:rgba(255,255,255,.05); border:1px solid var(--border); border-radius:12px; padding:10px 18px; cursor:pointer; transition:.2s; text-align:center; min-width:90px; }
        .date-tab:hover { border-color:rgba(233,69,96,.4); }
        .date-tab.active { background:rgba(233,69,96,.15); border-color:var(--red); }
        .date-tab .day { font-size:.7rem; color:#888; text-transform:uppercase; }
        .date-tab .date { font-size:1rem; font-weight:700; color:#fff; }
        /* Showtime */
        .showtime-grid { display:flex; flex-wrap:wrap; gap:12px; }
        .showtime-btn {
            background:rgba(255,255,255,.05); border:1px solid var(--border);
            border-radius:12px; padding:12px 20px; cursor:pointer; transition:.2s;
            text-align:center; min-width:130px; text-decoration:none; color:inherit; display:block;
        }
        .showtime-btn:hover { border-color:rgba(233,69,96,.4); background:rgba(233,69,96,.05); color:#fff; }
        .showtime-btn.selected { background:rgba(233,69,96,.2); border-color:var(--red); }
        .showtime-btn .studio-name { font-size:.75rem; color:#888; }
        .showtime-btn .jam { font-size:1.1rem; font-weight:700; color:#fff; }
        .showtime-btn .harga { font-size:.8rem; color:#10b981; font-weight:600; }
        .no-schedule { text-align:center; padding:30px; color:#555; }
        .user-badge { background:rgba(233,69,96,.1); border:1px solid rgba(233,69,96,.2); color:#e94560; padding:4px 12px; border-radius:20px; font-size:.8rem; }
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
                <li class="nav-item"><a class="nav-link" href="tiket_saya.php"><i class="fas fa-ticket-alt me-1"></i>Tiket Saya</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="user-badge"><i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="../auth/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </div>
</nav>

<section class="film-hero">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb" style="background:transparent;padding:0">
                <li class="breadcrumb-item"><a href="home.php" style="color:var(--red)">Home</a></li>
                <li class="breadcrumb-item active" style="color:#888"><?= htmlspecialchars($film['judul']) ?></li>
            </ol>
        </nav>
        <div class="row g-4 align-items-start">
            <div class="col-md-auto">
                <div class="poster-wrap">
                    <?php if ($film['poster'] && file_exists('../assets/images/' . $film['poster'])): ?>
                        <img src="<?= BASE_URL ?>assets/images/<?= htmlspecialchars($film['poster']) ?>" alt="<?= htmlspecialchars($film['judul']) ?>">
                    <?php else: ?>
                        <div class="poster-placeholder-lg"><i class="fas fa-film"></i><span style="color:#555">No Poster</span></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col">
                <h1 class="film-title-lg"><?= htmlspecialchars($film['judul']) ?></h1>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="meta-badge"><i class="fas fa-tag"></i><?= htmlspecialchars($film['genre']) ?></span>
                    <span class="meta-badge"><i class="fas fa-clock"></i><?= $film['durasi'] ?> menit</span>
                    <span class="meta-badge"><i class="fas fa-shield-alt"></i><?= htmlspecialchars($film['rating']) ?></span>
                </div>
                <p class="synopsis"><?= nl2br(htmlspecialchars($film['sinopsis'])) ?></p>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">
    <?php
    // Group by date
    $dates = [];
    $scheduleByDate = [];
    mysqli_data_seek($jadwals, 0);
    while ($j = mysqli_fetch_assoc($jadwals)) {
        $d = $j['tanggal'];
        if (!in_array($d, $dates)) $dates[] = $d;
        $scheduleByDate[$d][] = $j;
    }
    ?>
    
    <?php if (empty($dates)): ?>
    <div class="section-card">
        <div class="no-schedule">
            <i class="fas fa-calendar-times fa-3x mb-3" style="color:#333"></i>
            <p>Belum ada jadwal tayang untuk film ini.</p>
            <a href="home.php" class="btn btn-outline-danger btn-sm mt-2">Kembali ke Home</a>
        </div>
    </div>
    <?php else: ?>
    <div class="section-card">
        <h4><i class="fas fa-calendar-alt"></i>Pilih Jadwal Tayang</h4>
        
        <div class="date-tabs" id="dateTabs">
            <?php foreach ($dates as $idx => $d): ?>
            <div class="date-tab <?= $idx===0?'active':'' ?>" onclick="showDate('<?= $d ?>')" data-date="<?= $d ?>">
                <div class="day"><?= date('D', strtotime($d)) ?></div>
                <div class="date"><?= date('d M', strtotime($d)) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php foreach ($dates as $idx => $d): ?>
        <div class="date-content" id="date-<?= $d ?>" style="<?= $idx!==0?'display:none':'' ?>">
            <div class="showtime-grid">
                <?php foreach ($scheduleByDate[$d] as $j): ?>
                <a href="pilih_kursi.php?jadwal=<?= $j['id'] ?>" class="showtime-btn">
                    <div class="studio-name"><i class="fas fa-door-open me-1"></i><?= htmlspecialchars($j['studio']) ?></div>
                    <div class="jam"><?= date('H:i', strtotime($j['jam'])) ?></div>
                    <div class="harga"><?= formatRupiah($j['harga']) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
function showDate(d) {
    document.querySelectorAll('.date-content').forEach(el => el.style.display='none');
    document.querySelectorAll('.date-tab').forEach(el => el.classList.remove('active'));
    document.getElementById('date-'+d).style.display='block';
    document.querySelector('.date-tab[data-date="'+d+'"]').classList.add('active');
}
</script>
</body>
</html>
