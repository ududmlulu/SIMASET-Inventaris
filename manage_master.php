<?php
// manage_master.php
require_once 'config/database.php';

// Memastikan hanya Admin yang bisa mengakses halaman ini
requireAdmin();

$conn = getConnection();
$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- TAMBAH KATEGORI DEVICE ---
    if ($action === 'add_category') {
        $type_name   = trim($_POST['type_name'] ?? '');
        $type_code   = trim($_POST['type_code'] ?? '');
        $icon        = trim($_POST['icon'] ?? '') ?: '📦';
        $description = trim($_POST['description'] ?? '');
        $description = !empty($description) ? $description : NULL;

        if (!empty($type_name) && !empty($type_code)) {
            $stmt = $conn->prepare("INSERT INTO device_types (type_name, type_code, icon, description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $type_name, $type_code, $icon, $description);
            if ($stmt->execute()) {
                $success = "Kategori device berhasil ditambahkan!";
            } else {
                $error = "Gagal menambah kategori: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Nama Kategori dan Kode Kategori wajib diisi!";
        }
    }

    // --- HAPUS KATEGORI DEVICE ---
    if ($action === 'delete_category') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM device_types WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Kategori berhasil dihapus!";
        } else {
            $error = "Gagal menghapus kategori. Pastikan tidak ada data terkait!";
        }
        $stmt->close();
    }

    // --- TAMBAH BRAND ---
    if ($action === 'add_brand') {
        $brand_name  = trim($_POST['brand_name'] ?? '');
        $brand_code  = trim($_POST['brand_code'] ?? '');
        $category    = $_POST['category'] ?? '';
        $website     = trim($_POST['website'] ?? '');
        $website     = !empty($website) ? $website : NULL;
        $description = trim($_POST['description'] ?? '');
        $description = !empty($description) ? $description : NULL;

        if (!empty($brand_name) && !empty($brand_code) && !empty($category)) {
            try {
                $stmt = $conn->prepare("INSERT INTO brands (brand_name, brand_code, category, website, description, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->bind_param("sssss", $brand_name, $brand_code, $category, $website, $description);
                if ($stmt->execute()) {
                    $success = "Brand/Merk berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambah brand: " . $stmt->error;
                }
                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    $error = "Gagal: Kode Brand <strong>" . htmlspecialchars($brand_code) . "</strong> sudah digunakan! Gunakan kode brand lain.";
                } else {
                    $error = "Terjadi kesalahan database: " . $e->getMessage();
                }
            }
        } else {
            $error = "Nama Brand, Kode Brand, dan Kategori wajib diisi!";
        }
    }

    // --- HAPUS BRAND ---
    if ($action === 'delete_brand') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Brand berhasil dihapus!";
        } else {
            $error = "Gagal menghapus brand. Pastikan tidak ada perangkat yang menggunakan brand ini!";
        }
        $stmt->close();
    }
}

// Fetch Data Master
$categories = $conn->query("SELECT * FROM device_types ORDER BY type_name ASC");
$brands     = $conn->query("SELECT * FROM brands ORDER BY category ASC, brand_name ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Master Data — SIMASET</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --cream:#F7F3E9;
            --paper:#FFFFFF;
            --ink:#2B2A24;
            --ink-soft:#5C5A4E;
            --forest:#1E5B42;
            --forest-deep:#123A2A;
            --forest-mist:#E7EFE8;
            --gold:#B4872E;
            --gold-soft:#F1E4C4;
            --clay:#B25B3E;
            --border:#E4DDC9;
            --font-display:'Fraunces', serif;
            --font-body:'Inter', sans-serif;
            --font-mono:'IBM Plex Mono', monospace;
            --hero-image: url('https://picsum.photos/id/1076/1600/700');
        }
        *{box-sizing:border-box; margin:0; padding:0;}
        html, body{ 
            width:100%;
            max-width:100%;
            overflow-x:hidden;
            background:var(--cream); 
            color:var(--ink); 
            font-family:var(--font-body); 
            line-height:1.5; 
        }
        
        .app{ display:grid; grid-template-columns:240px minmax(0, 1fr); min-height:100vh; width:100%; }

        /* SIDEBAR */
        .sidebar{
            background: linear-gradient(180deg, rgba(18,58,42,0.94), rgba(18,58,42,0.97) 55%, var(--forest-deep) 100%), var(--hero-image);
            background-size:cover; background-position:center; color:#EFEAD8;
            display:flex; flex-direction:column; padding:26px 0 20px 0; position:relative;
        }
        .emblem-wrap{
            display:flex; align-items:center; gap:12px; padding:0 22px 22px 22px;
            border-bottom:1px solid rgba(239,234,216,0.18); margin-bottom:6px;
        }
        .emblem{
            width:46px; height:46px; border-radius:50%; border:1.5px solid var(--gold);
            display:flex; align-items:center; justify-content:center; position:relative; flex-shrink:0; background:rgba(180,135,46,0.08);
        }
        .emblem::before{ content:''; position:absolute; inset:5px; border:1px solid rgba(180,135,46,0.55); border-radius:50%; }
        .emblem svg{ width:20px; height:20px; color:var(--gold); }
        .emblem-text{ font-family:var(--font-display); font-size:16px; font-weight:600; line-height:1.2; }
        .emblem-text span{ display:block; font-family:var(--font-mono); font-size:10px; letter-spacing:1.2px; color:#C9BFA0; text-transform:uppercase; margin-top:3px; }

        .nav-label{ font-family:var(--font-mono); font-size:10px; letter-spacing:1.6px; text-transform:uppercase; color:#9BAF9F; padding:20px 24px 8px 24px; }
        nav{ display:flex; flex-direction:column; gap:2px; padding:0 14px; }
        .nav-item{
            display:flex; align-items:center; gap:11px; padding:10px 12px; border-radius:6px;
            font-size:14px; font-weight:500; color:#D9D4C1; text-decoration:none; cursor:pointer; border-left:3px solid transparent; transition:.15s;
        }
        .nav-item svg{ width:17px; height:17px; opacity:.85; flex-shrink:0; }
        .nav-item:hover{ background:rgba(239,234,216,0.08); color:#fff; }
        .nav-item.active{ background:rgba(239,234,216,0.1); color:var(--gold); border-left:3px solid var(--gold); }

        .sidebar-foot{
            margin-top:auto; padding:16px 22px 2px 22px; border-top:1px solid rgba(239,234,216,0.18);
            font-family:var(--font-mono); font-size:10.5px; color:#9BAF9F; display:flex; justify-content:space-between;
        }

        /* MAIN RESPONSIVE CONTAINER */
        main{ width:100%; min-width:0; }
        .hero{
            padding:24px 24px; background: linear-gradient(100deg, rgba(30,91,66,0.93) 0%, rgba(30,91,66,0.82) 45%, rgba(30,91,66,0.55) 100%), var(--hero-image);
            background-size:cover; background-position:center; color:#F5F1E3;
        }
        .hero h1{ font-family:var(--font-display); font-size:26px; font-weight:600; margin-bottom:6px; }
        .hero p{ color:#DCE3D9; font-size:13px; }

        .content{ padding:20px 24px; }

        .alert{ padding:10px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; display:flex; align-items:center; gap:8px; }
        .alert-success{ background:var(--forest-mist); color:var(--forest-deep); border:1px solid var(--sage); }
        .alert-danger{ background:#F3E1D8; color:var(--clay); border:1px solid #E2BBA9; }

        .grid-section{ display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px; }
        .card{ background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:20px; box-shadow:0 1px 2px rgba(43,42,36,0.04); }
        .card h2{ font-family:var(--font-display); font-size:17px; color:var(--forest-deep); margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .card h3{ font-size:13.5px; color:var(--ink); margin:14px 0 8px 0; font-family:var(--font-body); }

        .form-group{ margin-bottom:12px; }
        .form-group label{ display:block; font-size:11.5px; font-weight:600; margin-bottom:4px; color:var(--ink-soft); }
        .form-group input, .form-group select, .form-group textarea{
            width:100%; padding:8px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px; font-family:var(--font-body); background:var(--cream);
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus{ outline:2px solid var(--gold); }

        .btn{
            font-family:var(--font-body); font-weight:600; font-size:12.5px; border-radius:6px; padding:8px 14px; cursor:pointer;
            border:1px solid transparent; display:inline-flex; align-items:center; gap:6px; text-decoration:none; transition:.2s;
        }
        .btn-gold{ background:var(--gold); color:#2B2000; width:100%; justify-content:center; }
        .btn-gold:hover{ background:#c69a3e; }
        .btn-danger{ background:#F3E1D8; color:var(--clay); border:1px solid #E2BBA9; }
        .btn-sm{ padding:4px 8px; font-size:11px; border-radius:5px; }

        table{ width:100%; border-collapse:collapse; background:white; font-size:12.5px; }
        th{
            text-align:left; font-family:var(--font-mono); font-size:10px; letter-spacing:1px; text-transform:uppercase;
            color:#8E8B7A; padding:9px 10px; border-bottom:1px solid var(--border); font-weight:500; background:#FBF9F3;
        }
        td{ padding:9px 10px; border-bottom:1px solid var(--border); vertical-align:middle; }
        tr:hover{ background:#FBF9F3; }

        @media (max-width: 900px) { .sidebar{ display:none; } .content, .hero{ padding:16px; } }
    </style>
</head>
<body>
<div class="app">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="emblem-wrap">
            <div class="emblem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l9 4.5-9 4.5-9-4.5L12 3z"/><path d="M7 10.2v5.4c0 1.1 2.2 3.4 5 3.4s5-2.3 5-3.4v-5.4"/></svg>
            </div>
            <div class="emblem-text">SIMASET
                <span>Master Data</span>
            </div>
        </div>
        
        <div class="nav-label">Menu Utama</div>
        <nav>
            <a href="index.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard Data
            </a>
            <a href="manage_master.php" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
                Kelola Master Data
            </a>
            <a href="manage_users.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                Kelola Pengguna
            </a>
        </nav>

        <div class="sidebar-foot">
            <span>v2.0</span>
            <span>Admin Panel</span>
        </div>
    </aside>

    <main>
        <div class="hero">
            <h1>⚙️ Konfigurasi Master Data</h1>
            <p>Kelola klasifikasi kategori perangkat keras dan daftar merek/pabrikan secara terpusat.</p>
        </div>

        <div class="content">
            <?php if ($success): ?>
                <div class="alert alert-success"><span>✓</span> <?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><span>⚠️</span> <?= $error ?></div>
            <?php endif; ?>

            <div class="grid-section">
                <!-- SECTION KATEGORI DEVICE -->
                <div class="card">
                    <h2>📶 Master Kategori</h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_category">
                        <div class="form-group">
                            <label>Nama Kategori *</label>
                            <input type="text" name="type_name" placeholder="Contoh: Access Point" required>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Kode Kategori *</label>
                                <input type="text" name="type_code" placeholder="Contoh: AP" required>
                            </div>
                            <div class="form-group" style="width: 80px;">
                                <label>Icon</label>
                                <input type="text" name="icon" placeholder="📶" value="📶">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" rows="2" placeholder="Deskripsi singkat..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-gold">➕ Tambah Kategori</button>
                    </form>

                    <hr style="margin: 16px 0; border: 0; border-top: 1px solid var(--border);">

                    <h3>Daftar Kategori</h3>
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Icon</th>
                                    <th>Nama</th>
                                    <th>Kode</th>
                                    <th style="text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($categories && $categories->num_rows > 0): ?>
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                    <tr>
                                        <td style="font-size:15px;"><?= $cat['icon'] ?></td>
                                        <td><strong><?= htmlspecialchars($cat['type_name']) ?></strong></td>
                                        <td><code><?= htmlspecialchars($cat['type_code']) ?></code></td>
                                        <td style="text-align:right;">
                                            <form method="POST" action="" onsubmit="return confirm('Hapus kategori ini?');" style="display:inline;">
                                                <input type="hidden" name="action" value="delete_category">
                                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">🗑️</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align: center; padding:15px;">Belum ada kategori.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SECTION BRAND / MERK -->
                <div class="card">
                    <h2>🏷️ Master Brand / Merk</h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_brand">
                        <div class="form-group">
                            <label>Kategori Terkait *</label>
                            <select name="category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Access Point">Access Point</option>
                                <option value="Switch">Switch</option>
                                <option value="Router">Router</option>
                            </select>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <div class="form-group" style="flex: 2;">
                                <label>Nama Brand *</label>
                                <input type="text" name="brand_name" placeholder="Contoh: Mikrotik" required>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Kode Brand *</label>
                                <input type="text" name="brand_code" placeholder="Contoh: MIK-AP" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Website</label>
                            <input type="url" name="website" placeholder="https://mikrotik.com">
                        </div>
                        <button type="submit" class="btn btn-gold">➕ Tambah Brand</button>
                    </form>

                    <hr style="margin: 16px 0; border: 0; border-top: 1px solid var(--border);">

                    <h3>Daftar Brand</h3>
                    <div style="max-height: 280px; overflow-y: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Brand</th>
                                    <th>Kode</th>
                                    <th style="text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($brands && $brands->num_rows > 0): ?>
                                    <?php while($b = $brands->fetch_assoc()): ?>
                                    <tr>
                                        <td><span style="font-size:11px; color:var(--ink-soft);"><?= htmlspecialchars($b['category']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($b['brand_name']) ?></strong></td>
                                        <td><code><?= htmlspecialchars($b['brand_code']) ?></code></td>
                                        <td style="text-align:right;">
                                            <form method="POST" action="" onsubmit="return confirm('Hapus brand ini?');" style="display:inline;">
                                                <input type="hidden" name="action" value="delete_brand">
                                                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">🗑️</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align: center; padding:15px;">Belum ada brand.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>