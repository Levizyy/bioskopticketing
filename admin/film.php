<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$msg = '';
// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'tambah' || $action === 'edit') {
        $judul = trim($_POST['judul'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $durasi = intval($_POST['durasi'] ?? 0);
        $rating = trim($_POST['rating'] ?? '');
        $sinopsis = trim($_POST['sinopsis'] ?? '');
        $status = $_POST['status'] ?? 'tayang';
        
        // Handle poster upload
        $poster = $_POST['poster_lama'] ?? '';
        if (!empty($_FILES['poster']['name'])) {
            $ext = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array(strtolower($ext), $allowed)) {
                $newname = 'film_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['poster']['tmp_name'], '../assets/images/' . $newname);
                $poster = $newname;
            }
        }
        
        if ($action === 'tambah') {
            $stmt = mysqli_prepare($conn, "INSERT INTO film (judul, genre, durasi, rating, sinopsis, poster, status) VALUES (?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'ssissss', $judul, $genre, $durasi, $rating, $sinopsis, $poster, $status);
            mysqli_stmt_execute($stmt);
            $msg = 'success|Film berhasil ditambahkan!';
        } else {
            $id = intval($_POST['id']);
            $stmt = mysqli_prepare($conn, "UPDATE film SET judul=?,genre=?,durasi=?,rating=?,sinopsis=?,poster=?,status=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'ssissssi', $judul, $genre, $durasi, $rating, $sinopsis, $poster, $status, $id);
            mysqli_stmt_execute($stmt);
            $msg = 'success|Film berhasil diperbarui!';
        }
    } elseif ($action === 'hapus') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM film WHERE id=$id");
        $msg = 'success|Film berhasil dihapus!';
    }
}

$films = mysqli_query($conn, "SELECT * FROM film ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Film - Admin <?= SITE_NAME ?></title>
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
        .admin-avatar { width:38px; height:38px; background:var(--red); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; }
        .main-content { margin-left:260px; flex:1; padding:30px; }
        .page-title { font-size:1.8rem; font-weight:900; margin-bottom:5px; }
        .section-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:25px; }
        .table-dark-custom { color:#ccc; }
        .table-dark-custom thead th { background:rgba(255,255,255,.05); border-color:var(--border); color:#888; font-size:.8rem; text-transform:uppercase; padding:12px 16px; }
        .table-dark-custom tbody td { border-color:var(--border); padding:12px 16px; vertical-align:middle; }
        .table-dark-custom tbody tr:hover td { background:rgba(255,255,255,.02); }
        .btn-add { background:linear-gradient(135deg,#e94560,#c02942); border:none; color:#fff; padding:10px 22px; border-radius:10px; font-weight:600; cursor:pointer; }
        .btn-edit { background:rgba(59,130,246,.15); color:#3b82f6; border:1px solid rgba(59,130,246,.2); padding:5px 12px; border-radius:8px; font-size:.8rem; cursor:pointer; }
        .btn-del { background:rgba(233,69,96,.15); color:#e94560; border:1px solid rgba(233,69,96,.2); padding:5px 12px; border-radius:8px; font-size:.8rem; cursor:pointer; }
        /* Modal */
        .modal-dark .modal-content { background:#12122a; border:1px solid rgba(255,255,255,.1); color:#fff; }
        .modal-dark .modal-header { border-bottom:1px solid rgba(255,255,255,.08); }
        .modal-dark .modal-footer { border-top:1px solid rgba(255,255,255,.08); }
        .modal-dark .form-label { color:#ccc; font-size:.875rem; }
        .modal-dark .form-control, .modal-dark .form-select {
            background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); color:#fff;
        }
        .modal-dark .form-control:focus, .modal-dark .form-select:focus {
            background:rgba(255,255,255,.08); border-color:var(--red); box-shadow:0 0 0 3px rgba(233,69,96,.15); color:#fff;
        }
        .modal-dark .form-control::placeholder { color:#555; }
        .modal-dark .btn-close { filter:invert(1); }
        .badge-tayang { background:rgba(16,185,129,.15); color:#10b981; padding:3px 10px; border-radius:15px; font-size:.75rem; border:1px solid rgba(16,185,129,.2); }
        .badge-tidak { background:rgba(233,69,96,.15); color:#e94560; padding:3px 10px; border-radius:15px; font-size:.75rem; border:1px solid rgba(233,69,96,.2); }
        .alert-success-custom { background:rgba(16,185,129,.15); border:1px solid rgba(16,185,129,.3); color:#10b981; border-radius:10px; padding:12px 18px; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <h2><i class="fas fa-film me-2" style="color:var(--red)"></i><?= SITE_NAME ?><span>.</span></h2>
        <p>Admin Panel</p>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="film.php" class="sidebar-link active"><i class="fas fa-film"></i>Kelola Film</a>
        <a href="jadwal.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i>Kelola Jadwal</a>
        <div class="nav-section">Management</div>
        <a href="user.php" class="sidebar-link"><i class="fas fa-users"></i>Kelola User</a>
        <a href="transaksi.php" class="sidebar-link"><i class="fas fa-receipt"></i>Transaksi</a>
    </nav>
    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-10 mb-2">
            <div class="admin-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
            <div class="ms-2"><div style="font-size:.85rem;font-weight:600"><?= htmlspecialchars($_SESSION['nama']) ?></div></div>
        </div>
        <a href="../auth/logout.php" style="color:#888;text-decoration:none;font-size:.85rem"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
    </div>
</aside>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="page-title">Kelola <span style="color:var(--red)">Film</span></div>
            <div style="color:#666;font-size:.9rem">Manajemen data film bioskop</div>
        </div>
        <button class="btn-add" onclick="openModal()"><i class="fas fa-plus me-2"></i>Tambah Film</button>
    </div>

    <?php if ($msg): 
        [$type, $text] = explode('|', $msg, 2); ?>
    <div class="alert-success-custom mb-4"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($text) ?></div>
    <?php endif; ?>

    <div class="section-card">
        <div class="table-responsive">
            <table class="table table-dark-custom">
                <thead><tr><th>#</th><th>Judul Film</th><th>Genre</th><th>Durasi</th><th>Rating</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php $no=1; mysqli_data_seek($films,0); while ($f = mysqli_fetch_assoc($films)): ?>
                    <tr>
                        <td style="color:#666"><?= $no++ ?></td>
                        <td style="font-weight:600"><?= htmlspecialchars($f['judul']) ?></td>
                        <td style="color:#888;font-size:.85rem"><?= htmlspecialchars($f['genre']) ?></td>
                        <td style="color:#888"><?= $f['durasi'] ?> min</td>
                        <td><?= htmlspecialchars($f['rating']) ?></td>
                        <td><span class="badge-<?= $f['status'] === 'tayang' ? 'tayang' : 'tidak' ?>"><?= $f['status'] === 'tayang' ? '🟢 Tayang' : '🔴 Tidak' ?></span></td>
                        <td>
                            <button class="btn-edit me-1" onclick="editFilm(<?= htmlspecialchars(json_encode($f)) ?>)"><i class="fas fa-edit"></i> Edit</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus film ini?')">
                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                <button type="submit" class="btn-del"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Film -->
<div class="modal fade modal-dark" id="filmModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Film</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="tambah">
                    <input type="hidden" name="id" id="filmId">
                    <input type="hidden" name="poster_lama" id="posterLama">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Judul Film</label>
                            <input type="text" name="judul" id="filmJudul" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Genre</label>
                            <input type="text" name="genre" id="filmGenre" class="form-control" placeholder="Action, Drama, ...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Durasi (menit)</label>
                            <input type="number" name="durasi" id="filmDurasi" class="form-control" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rating</label>
                            <select name="rating" id="filmRating" class="form-select">
                                <option value="G">G - General</option>
                                <option value="PG">PG</option>
                                <option value="PG-13">PG-13</option>
                                <option value="R">R</option>
                                <option value="NC-17">NC-17</option>
                                <option value="SU">SU</option>
                                <option value="13+">13+</option>
                                <option value="17+">17+</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Sinopsis</label>
                            <textarea name="sinopsis" id="filmSinopsis" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Poster (gambar)</label>
                            <input type="file" name="poster" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" id="filmStatus" class="form-select">
                                <option value="tayang">Tayang</option>
                                <option value="tidak">Tidak Tayang</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-add"><i class="fas fa-save me-2"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
const modal = new bootstrap.Modal(document.getElementById('filmModal'));

function openModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Film Baru';
    document.getElementById('formAction').value = 'tambah';
    document.getElementById('filmId').value = '';
    document.getElementById('posterLama').value = '';
    document.querySelector('form').reset();
    modal.show();
}

function editFilm(f) {
    document.getElementById('modalTitle').textContent = 'Edit Film';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('filmId').value = f.id;
    document.getElementById('filmJudul').value = f.judul;
    document.getElementById('filmGenre').value = f.genre;
    document.getElementById('filmDurasi').value = f.durasi;
    document.getElementById('filmRating').value = f.rating;
    document.getElementById('filmSinopsis').value = f.sinopsis;
    document.getElementById('filmStatus').value = f.status;
    document.getElementById('posterLama').value = f.poster;
    modal.show();
}
</script>
</body>
</html>
