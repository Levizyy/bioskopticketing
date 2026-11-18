<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
requireLogin();

$films = mysqli_query($conn, "SELECT * FROM film WHERE status='tayang' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Film Tayang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#e94560; --dark:#0a0a1a; --card:#12122a; --border:rgba(255,255,255,0.08); }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:var(--dark); color:#fff; font-family:'Segoe UI',sans-serif; min-height:100vh; }
        /* Navbar */
        .navbar { background:rgba(10,10,26,.95); backdrop-filter:blur(20px); border-bottom:1px solid var(--border); padding:15px 0; position:sticky; top:0; z-index:1000; }
        .navbar-brand { font-size:1.6rem; font-weight:900; color:#fff !important; letter-spacing:2px; }
        .navbar-brand span { color:var(--red); }
        .nav-link { color:#ccc !important; font-weight:500; transition:.2s; }
        .nav-link:hover, .nav-link.active { color:var(--red) !important; }
        .btn-logout { background:transparent; border:1px solid var(--red); color:var(--red); padding:6px 18px; border-radius:20px; font-size:.85rem; transition:.3s; }
        .btn-logout:hover { background:var(--red); color:#fff; }
        /* Hero */
        .hero { background:linear-gradient(135deg,#12122a 0%,#1a0a1a 100%); padding:60px 0 40px; position:relative; overflow:hidden; }
        .hero::before { content:''; position:absolute; top:0; left:0; right:0; bottom:0; background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e94560' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
        .hero h1 { font-size:2.5rem; font-weight:900; margin-bottom:10px; position:relative; }
        .hero h1 span { color:var(--red); }
        .hero p { color:#aaa; font-size:1.05rem; position:relative; }
        /* Film Cards */
        .section-title { font-size:1.4rem; font-weight:700; margin-bottom:25px; display:flex; align-items:center; gap:10px; }
        .section-title::after { content:''; flex:1; height:2px; background:linear-gradient(to right,var(--red),transparent); }
        .film-card {
            background:var(--card); border:1px solid var(--border); border-radius:16px;
            overflow:hidden; transition:all .3s; cursor:pointer; height:100%;
        }
        .film-card:hover { transform:translateY(-8px); box-shadow:0 20px 50px rgba(233,69,96,.2); border-color:rgba(233,69,96,.3); }
        .film-poster { position:relative; padding-top:140%; overflow:hidden; }
        .film-poster img { position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; transition:.4s; }
        .film-card:hover .film-poster img { transform:scale(1.05); }
        .poster-placeholder {
            position:absolute; top:0; left:0; width:100%; height:100%;
            background:linear-gradient(135deg,#1e1e3f,#2d1b4e);
            display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px;
        }
        .poster-placeholder i { font-size:3rem; color:rgba(233,69,96,.5); }
        .poster-placeholder span { color:#666; font-size:.8rem; }
        .film-overlay {
            position:absolute; bottom:0; left:0; right:0; padding:20px 15px 15px;
            background:linear-gradient(to top,rgba(0,0,0,.9) 0%,transparent 100%);
            opacity:0; transition:.3s; display:flex; align-items:flex-end; justify-content:center;
        }
        .film-card:hover .film-overlay { opacity:1; }
        .btn-pesan {
            background:var(--red); color:#fff; border:none; padding:8px 24px;
            border-radius:20px; font-weight:600; font-size:.875rem;
            text-decoration:none; display:inline-block; transition:.2s;
        }
        .btn-pesan:hover { background:#c02942; color:#fff; transform:scale(1.05); }
        .film-info { padding:15px; }
        .film-title { font-weight:700; font-size:1rem; margin-bottom:8px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .badge-genre { background:rgba(233,69,96,.15); color:#e94560; font-size:.75rem; padding:3px 10px; border-radius:20px; border:1px solid rgba(233,69,96,.2); }
        .badge-rating { background:rgba(255,193,7,.15); color:#ffc107; font-size:.75rem; padding:3px 10px; border-radius:20px; border:1px solid rgba(255,193,7,.2); }
        .film-meta { color:#888; font-size:.8rem; margin-top:8px; display:flex; gap:12px; }
        /* Footer */
        footer { background:#080812; border-top:1px solid var(--border); padding:30px 0; margin-top:60px; text-align:center; color:#555; font-size:.875rem; }
        .user-badge { background:rgba(233,69,96,.1); border:1px solid rgba(233,69,96,.2); color:#e94560; padding:4px 12px; border-radius:20px; font-size:.8rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-film me-2"></i><?= SITE_NAME ?><span>.</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" style="border-color:rgba(255,255,255,.2);color:#fff">
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto ms-3">
                <li class="nav-item"><a class="nav-link active" href="home.php"><i class="fas fa-home me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link" href="tiket_saya.php"><i class="fas fa-ticket-alt me-1"></i>Tiket Saya</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="user-badge"><i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="../auth/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <h1>Film <span>Sedang Tayang</span> 🎬</h1>
        <p>Pilih film favoritmu dan pesan tiket sekarang dengan mudah</p>
    </div>
</section>

<div class="container py-5">
    <div class="section-title"><i class="fas fa-fire text-danger"></i> Film Tayang</div>
    
    <?php if (mysqli_num_rows($films) == 0): ?>
    <div class="text-center py-5">
        <i class="fas fa-film fa-4x mb-3" style="color:#333"></i>
        <p style="color:#666">Belum ada film yang tayang saat ini.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php while ($film = mysqli_fetch_assoc($films)): ?>
        <div class="col-6 col-md-4 col-lg-2" style="min-width:200px;max-width:220px">
            <div class="film-card">
                <div class="film-poster">
                    <?php if ($film['poster'] && file_exists('../assets/images/' . $film['poster'])): ?>
                        <img src="<?= BASE_URL ?>assets/images/<?= htmlspecialchars($film['poster']) ?>" alt="<?= htmlspecialchars($film['judul']) ?>">
                    <?php else: ?>
                        <div class="poster-placeholder">
                            <i class="fas fa-film"></i>
                            <span>No Poster</span>
                        </div>
                    <?php endif; ?>
                    <div class="film-overlay">
                        <a href="detail_film.php?id=<?= $film['id'] ?>" class="btn-pesan">
                            <i class="fas fa-ticket-alt me-1"></i>Pesan
                        </a>
                    </div>
                </div>
                <div class="film-info">
                    <div class="film-title" title="<?= htmlspecialchars($film['judul']) ?>"><?= htmlspecialchars($film['judul']) ?></div>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <span class="badge-genre"><?= htmlspecialchars(explode(',', $film['genre'])[0]) ?></span>
                        <span class="badge-rating"><?= htmlspecialchars($film['rating']) ?></span>
                    </div>
                    <div class="film-meta">
                        <span><i class="fas fa-clock me-1"></i><?= $film['durasi'] ?> min</span>
                    </div>
                    <a href="detail_film.php?id=<?= $film['id'] ?>" class="btn-pesan mt-2 d-block text-center">Pesan Tiket</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<footer>
    <p>© <?= date('Y') ?> <?= SITE_NAME ?> — Premium Cinema Experience</p>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
