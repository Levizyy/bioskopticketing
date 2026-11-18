<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'tambah' || $action === 'edit') {
        $film_id = intval($_POST['film_id']);
        $studio = trim($_POST['studio']);
        $tanggal = $_POST['tanggal'];
        $jam = $_POST['jam'];
        $harga = floatval($_POST['harga']);
        
        if ($action === 'tambah') {
            $stmt = mysqli_prepare($conn, "INSERT INTO jadwal (film_id, studio, tanggal, jam, harga) VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'isssd', $film_id, $studio, $tanggal, $jam, $harga);
            mysqli_stmt_execute($stmt);
            $jadwal_id = mysqli_insert_id($conn);
            // Auto-generate kursi
            $rows = ['A','B','C','D','E'];
            for ($c=1; $c<=8; $c++) {
                foreach ($rows as $r) {
                    $nomor = $r.$c;
                    mysqli_query($conn, "INSERT INTO kursi (jadwal_id, nomor_kursi) VALUES ($jadwal_id, '$nomor')");
                }
            }
            $msg = 'success|Jadwal berhasil ditambahkan! Kursi otomatis dibuat (40 kursi).';
        } else {
            $id = intval($_POST['id']);
            $stmt = mysqli_prepare($conn, "UPDATE jadwal SET film_id=?,studio=?,tanggal=?,jam=?,harga=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'isssdi', $film_id, $studio, $tanggal, $jam, $harga, $id);
            mysqli_stmt_execute($stmt);
            $msg = 'success|Jadwal berhasil diperbarui!';
        }
    } elseif ($action === 'hapus') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM jadwal WHERE id=$id");
        $msg = 'success|Jadwal berhasil dihapus!';
    }
}

$jadwals = mysqli_query($conn, "SELECT j.*, f.judul FROM jadwal j JOIN film f ON j.film_id=f.id ORDER BY j.tanggal DESC, j.jam ASC");
$films = mysqli_query($conn, "SELECT id, judul FROM film ORDER BY judul");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal - Admin <?= SITE_NAME ?></title>
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
        .btn-add { background:linear-gradient(135deg,#e94560,#c02942); border:none; color:#fff; padding:10px 22px; border-radius:10px; font-weight:600; cursor:pointer; }
        .btn-edit { background:rgba(59,130,246,.15); color:#3b82f6; border:1px solid rgba(59,130,246,.2); padding:5px 12px; border-radius:8px; font-size:.8rem; cursor:pointer; }
        .btn-del { background:rgba(233,69,96,.15); color:#e94560; border:1px solid rgba(233,69,96,.2); padding:5px 12px; border-radius:8px; font-size:.8rem; cursor:pointer; }
        .modal-dark .modal-content { background:#12122a; border:1px solid rgba(255,255,255,.1); color:#fff; }
        .modal-dark .modal-header { border-bottom:1px solid rgba(255,255,255,.08); }
        .modal-dark .modal-footer { border-top:1px solid rgba(255,255,255,.08); }
        .modal-dark .form-label { color:#ccc; font-size:.875rem; }
        .modal-dark .form-control, .modal-dark .form-select { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); color:#fff; }
        .modal-dark .form-control:focus, .modal-dark .form-select:focus { background:rgba(255,255,255,.08); border-color:var(--red); box-shadow:none; color:#fff; }
        .modal-dark .btn-close { filter:invert(1); }
        .alert-success-custom { background:rgba(16,185,129,.15); border:1px solid rgba(16,185,129,.3); color:#10b981; border-radius:10px; padding:12px 18px; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand"><h2><i class="fas fa-film me-2" style="color:var(--red)"></i><?= SITE_NAME ?><span>.</span></h2><p>Admin Panel</p></div>
    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="film.php" class="sidebar-link"><i class="fas fa-film"></i>Kelola Film</a>
        <a href="jadwal.php" class="sidebar-link active"><i class="fas fa-calendar-alt"></i>Kelola Jadwal</a>
        <div class="nav-section">Management</div>
        <a href="user.php" class="sidebar-link"><i class="fas fa-users"></i>Kelola User</a>
        <a href="transaksi.php" class="sidebar-link"><i class="fas fa-receipt"></i>Transaksi</a>
    </nav>
    <div class="sidebar-footer">
        <a href="../auth/logout.php" style="color:#888;text-decoration:none;font-size:.85rem"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
    </div>
</aside>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="page-title">Kelola <span style="color:var(--red)">Jadwal</span></div>
            <div style="color:#666;font-size:.9rem">Atur jadwal tayang film</div>
        </div>
        <button class="btn-add" onclick="openModal()"><i class="fas fa-plus me-2"></i>Tambah Jadwal</button>
    </div>

    <?php if ($msg): [$t, $m] = explode('|', $msg, 2); ?>
    <div class="alert-success-custom mb-4"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($m) ?></div>
    <?php endif; ?>

    <div class="section-card">
        <div class="table-responsive">
            <table class="table table-dark-custom">
                <thead><tr><th>#</th><th>Film</th><th>Studio</th><th>Tanggal</th><th>Jam</th><th>Harga</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php $no=1; while ($j = mysqli_fetch_assoc($jadwals)): ?>
                    <tr>
                        <td style="color:#666"><?= $no++ ?></td>
                        <td style="font-weight:600"><?= htmlspecialchars($j['judul']) ?></td>
                        <td style="color:#888"><?= htmlspecialchars($j['studio']) ?></td>
                        <td><?= date('d M Y', strtotime($j['tanggal'])) ?></td>
                        <td><?= date('H:i', strtotime($j['jam'])) ?> WIB</td>
                        <td style="color:#10b981;font-weight:600"><?= formatRupiah($j['harga']) ?></td>
                        <td>
                            <button class="btn-edit me-1" onclick='editJadwal(<?= json_encode($j) ?>)'><i class="fas fa-edit"></i> Edit</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus jadwal ini?')">
                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="id" value="<?= $j['id'] ?>">
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

<div class="modal fade modal-dark" id="jadwalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="tambah">
                    <input type="hidden" name="id" id="jadwalId">
                    <div class="mb-3">
                        <label class="form-label">Film</label>
                        <select name="film_id" id="filmId" class="form-select" required>
                            <option value="">-- Pilih Film --</option>
                            <?php mysqli_data_seek($films,0); while ($f = mysqli_fetch_assoc($films)): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['judul']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Studio</label>
                        <select name="studio" id="studioSel" class="form-select">
                            <option>Studio 1</option><option>Studio 2</option><option>Studio 3</option><option>Studio 4</option><option>Studio 5</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggalIn" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam Tayang</label>
                            <input type="time" name="jam" id="jamIn" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Harga Tiket (Rp)</label>
                        <input type="number" name="harga" id="hargaIn" class="form-control" min="0" step="1000">
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
const modal = new bootstrap.Modal(document.getElementById('jadwalModal'));
function openModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Jadwal Baru';
    document.getElementById('formAction').value = 'tambah';
    document.getElementById('jadwalId').value = '';
    document.getElementById('filmId').value = '';
    document.getElementById('tanggalIn').value = '';
    document.getElementById('jamIn').value = '';
    document.getElementById('hargaIn').value = '';
    modal.show();
}
function editJadwal(j) {
    document.getElementById('modalTitle').textContent = 'Edit Jadwal';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('jadwalId').value = j.id;
    document.getElementById('filmId').value = j.film_id;
    document.getElementById('studioSel').value = j.studio;
    document.getElementById('tanggalIn').value = j.tanggal;
    document.getElementById('jamIn').value = j.jam;
    document.getElementById('hargaIn').value = j.harga;
    modal.show();
}
</script>
</body>
</html>
