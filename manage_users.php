<?php
// manage_users.php
require_once 'config/database.php';

// Proteksi halaman: Hanya Admin yang bisa mengakses
requireAdmin();

$conn = getConnection();
$error = '';
$success = '';

// Pemrosesan Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Keamanan Backend: Validasi Token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Akses Ditolak: Token keamanan tidak valid (CSRF). Silakan muat ulang halaman.";
    } else {
        $action = $_POST['action'];

        // --- TAMBAH USER BARU ---
        if ($action === 'add_user') {
            $username  = trim($_POST['username'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $password  = $_POST['password'] ?? '';
            $role      = $_POST['role'] ?? 'view_only';

            if (!empty($username) && !empty($full_name) && !empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                try {
                    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, 1)");
                    $stmt->bind_param("ssss", $username, $hashed_password, $full_name, $role);
                    
                    if ($stmt->execute()) {
                        $success = "Pengguna baru <strong>" . htmlspecialchars($username) . "</strong> berhasil ditambahkan!";
                    } else {
                        $error = "Gagal menambahkan user: " . $stmt->error;
                    }
                    $stmt->close();
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() === 1062) {
                        $error = "Gagal: Username <strong>" . htmlspecialchars($username) . "</strong> sudah digunakan!";
                    } else {
                        $error = "Terjadi kesalahan database: " . $e->getMessage();
                    }
                }
            } else {
                $error = "Semua bidang (Username, Nama Lengkap, Password, Role) wajib diisi!";
            }
        }

        // --- TOGGLE STATUS AKTIF/NONAKTIF USER ---
        if ($action === 'toggle_status') {
            $id = (int)$_POST['id'];
            $current_status = (int)$_POST['current_status'];
            $new_status = ($current_status === 1) ? 0 : 1;

            if ($id == $_SESSION['user_id']) {
                $error = "Anda tidak dapat menonaktifkan akun Anda sendiri saat sedang login!";
            } else {
                $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_status, $id);
                if ($stmt->execute()) {
                    $success = "Status pengguna berhasil diperbarui!";
                } else {
                    $error = "Gagal mengubah status pengguna.";
                }
                $stmt->close();
            }
        }

        // --- RESET PASSWORD USER ---
        if ($action === 'reset_password') {
            $id = (int)$_POST['id'];
            $new_password = $_POST['new_password'] ?? '';

            if ($id == $_SESSION['user_id']) {
                $error = "Anda tidak dapat mereset password akun Anda sendiri dari sini!";
            } elseif (empty($new_password)) {
                $error = "Password baru tidak boleh kosong!";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $id);
                if ($stmt->execute()) {
                    $success = "Password pengguna berhasil direset/diubah!";
                } else {
                    $error = "Gagal mereset password pengguna.";
                }
                $stmt->close();
            }
        }

        // --- HAPUS USER ---
        if ($action === 'delete_user') {
            $id = (int)$_POST['id'];

            if ($id == $_SESSION['user_id']) {
                $error = "Anda tidak dapat menghapus akun Anda sendiri!";
            } else {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $success = "Pengguna berhasil dihapus!";
                } else {
                    $error = "Gagal menghapus pengguna.";
                }
                $stmt->close();
            }
        }
    }
}

// Ambil Seluruh Data User
$users = $conn->query("SELECT id, username, full_name, role, is_active, created_at FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna — SIMASET</title>
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
        body{ background:var(--cream); color:var(--ink); font-family:var(--font-body); line-height:1.5; }
        
        .app{ display:grid; grid-template-columns:252px 1fr; min-height:100vh; }

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

        /* MAIN CONTENT */
        main{ max-width:1400px; width:100%; }
        .hero{
            padding:34px 40px; background: linear-gradient(100deg, rgba(30,91,66,0.93) 0%, rgba(30,91,66,0.82) 45%, rgba(30,91,66,0.55) 100%), var(--hero-image);
            background-size:cover; background-position:center; color:#F5F1E3;
        }
        .hero h1{ font-family:var(--font-display); font-size:28px; font-weight:600; margin-bottom:6px; }
        .hero p{ color:#DCE3D9; font-size:13.5px; }

        .content{ padding:28px 40px; }

        .alert{
            padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:13.5px; display:flex; align-items:center; gap:10px;
        }
        .alert-success{ background:var(--forest-mist); color:var(--forest-deep); border:1px solid var(--sage); }
        .alert-danger{ background:#F3E1D8; color:var(--clay); border:1px solid #E2BBA9; }

        .grid-section{ display:grid; grid-template-columns:1fr 2fr; gap:24px; }
        .card{ background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:22px; box-shadow:0 1px 2px rgba(43,42,36,0.04); }
        .card h2{ font-family:var(--font-display); font-size:18px; color:var(--forest-deep); margin-bottom:16px; }

        .form-group{ margin-bottom:14px; }
        .form-group label{ display:block; font-size:12px; font-weight:600; margin-bottom:5px; color:var(--ink-soft); }
        .form-group input, .form-group select{
            width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:7px; font-size:13.5px; font-family:var(--font-body); background:var(--cream);
        }
        .form-group input:focus, .form-group select:focus{ outline:2px solid var(--gold); }

        .btn{
            font-family:var(--font-body); font-weight:600; font-size:13px; border-radius:7px; padding:9px 15px; cursor:pointer;
            border:1px solid transparent; display:inline-flex; align-items:center; gap:6px; text-decoration:none; transition:.2s;
        }
        .btn-gold{ background:var(--gold); color:#2B2000; width:100%; justify-content:center; }
        .btn-gold:hover{ background:#c69a3e; }
        .btn-warning{ background:var(--gold-soft); color:#5C4712; border:1px solid #E2C787; }
        .btn-danger{ background:#F3E1D8; color:var(--clay); border:1px solid #E2BBA9; }
        .btn-info{ background:var(--forest-mist); color:var(--forest); border:1px solid var(--sage); }
        .btn-secondary{ background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.35); color:#fff; }
        .btn-sm{ padding:5px 10px; font-size:11.5px; border-radius:5px; }

        table{ width:100%; border-collapse:collapse; margin-top:10px; }
        th{
            text-align:left; font-family:var(--font-mono); font-size:10.5px; letter-spacing:1px; text-transform:uppercase;
            color:#8E8B7A; padding:10px 14px; border-bottom:1px solid var(--border); font-weight:500; background:#FBF9F3;
        }
        td{ padding:12px 14px; font-size:13px; border-bottom:1px solid var(--border); vertical-align:middle; }
        tr:hover{ background:#FBF9F3; }

        .role-badge{ font-size:11px; font-weight:600; padding:3px 9px; border-radius:999px; text-transform:uppercase; display:inline-block; }
        .role-admin{ background:var(--forest-mist); color:var(--forest); }
        .role-view_only{ background:var(--gold-soft); color:#5C4712; }

        .status-badge{ font-size:11px; font-weight:600; padding:3px 9px; border-radius:999px; display:inline-block; }
        .status-active{ background:var(--forest-mist); color:var(--forest); }
        .status-inactive{ background:#F3E1D8; color:var(--clay); }

        /* Modal Styles */
        .modal{ display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(27,38,31,0.6); backdrop-filter:blur(3px); }
        .modal-content{ background:var(--paper); margin:10% auto; padding:25px; width:90%; max-width:400px; border-radius:12px; border:1px solid var(--border); }
        .modal-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid var(--border); padding-bottom:10px; }
        .modal-header h3{ font-family:var(--font-display); color:var(--forest-deep); font-size:18px; }
        .close{ font-size:22px; cursor:pointer; color:var(--ink-soft); }

        @media (max-width: 900px) { .grid-section { grid-template-columns:1fr; } .sidebar{ display:none; } .content, .hero{ padding:20px; } }
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
                <span>Manajemen Pengguna</span>
            </div>
        </div>
        
        <div class="nav-label">Menu Utama</div>
        <nav>
            <a href="index.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard Data
            </a>
            <a href="manage_master.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
                Kelola Master Data
            </a>
            <a href="manage_users.php" class="nav-item active">
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
            <h1>👥 Kelola Hak Akses Pengguna</h1>
            <p>Tambah pengguna sistem baru, atur status aktif, atau lakukan reset sandi akun secara aman.</p>
        </div>

        <div class="content">
            <?php if ($success): ?>
                <div class="alert alert-success"><span>✓</span> <?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><span>⚠️</span> <?= $error ?></div>
            <?php endif; ?>

            <div class="grid-section">
                <!-- FORM TAMBAH USER -->
                <div class="card">
                    <h2>➕ Tambah User Baru</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="add_user">
                        
                        <div class="form-group">
                            <label>Username *</label>
                            <input type="text" name="username" placeholder="Contoh: admin_pusat" required>
                        </div>

                        <div class="form-group">
                            <label>Nama Lengkap *</label>
                            <input type="text" name="full_name" placeholder="Contoh: Budi Santoso" required>
                        </div>

                        <div class="form-group">
                            <label>Password *</label>
                            <input type="password" name="password" placeholder="Masukkan password..." required>
                        </div>

                        <div class="form-group">
                            <label>Role / Hak Akses *</label>
                            <select name="role" required>
                                <option value="view_only">View Only (Hanya Lihat)</option>
                                <option value="admin">Admin (Akses Penuh)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-gold" style="margin-top:5px;">💾 Simpan Pengguna</button>
                    </form>
                </div>

                <!-- TABEL DAFTAR USER -->
                <div class="card">
                    <h2>📋 Daftar Pengguna Sistem</h2>
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username & Nama</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th style="text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users && $users->num_rows > 0): ?>
                                    <?php while($u = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($u['username']) ?></strong>
                                            <div style="font-size:11.5px; color:var(--ink-soft);"><?= htmlspecialchars($u['full_name']) ?></div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-<?= $u['role'] ?>">
                                                <?= $u['role'] === 'admin' ? 'Admin' : 'View Only' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= $u['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                                <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                            </span>
                                        </td>
                                        <td style="text-align:right;">
                                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                                <div style="display:inline-flex; gap:4px;">
                                                    <button type="button" class="btn btn-info btn-sm" onclick="openResetModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')" title="Reset Password">
                                                        🔑 Reset
                                                    </button>

                                                    <form method="POST" action="" style="display:inline;">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                        <input type="hidden" name="current_status" value="<?= $u['is_active'] ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm" title="Ubah Status">
                                                            <?= $u['is_active'] ? '🔒 Nonaktif' : '🔓 Aktif' ?>
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');" style="display:inline;">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus User">🗑️</button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <small style="color:var(--ink-soft); font-style:italic;">(Akun Anda)</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align: center; padding:20px;">Belum ada user terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Reset Password -->
<div id="resetModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🔑 Reset Password</h3>
            <span class="close" onclick="closeResetModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="id" id="resetUserId">
            
            <div class="form-group">
                <label>Target Username</label>
                <input type="text" id="resetUsername" readonly style="background-color: var(--border); cursor: not-allowed;">
            </div>
            
            <div class="form-group">
                <label>Password Baru *</label>
                <input type="password" name="new_password" placeholder="Masukkan password baru..." required>
            </div>
            
            <div style="text-align: right; margin-top: 15px;">
                <button type="button" class="btn btn-secondary" style="color:var(--ink);" onclick="closeResetModal()">Batal</button>
                <button type="submit" class="btn btn-gold" style="width:auto; display:inline-flex;">Simpan Sandi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openResetModal(id, username) {
        document.getElementById('resetUserId').value = id;
        document.getElementById('resetUsername').value = username;
        document.getElementById('resetModal').style.display = 'block';
    }
    function closeResetModal() {
        document.getElementById('resetModal').style.display = 'none';
    }
    window.onclick = function(event) {
        const modal = document.getElementById('resetModal');
        if (event.target === modal) modal.style.display = 'none';
    }
</script>
</body>
</html>