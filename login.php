<?php
// login.php
require_once 'config/database.php';

$error = '';

// Proses Login saat Form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $conn = getConnection();
        $loginResult = loginUser($conn, $username, $password);
        
        if ($loginResult['success']) {
            header("Location: index.php");
            exit;
        } else {
            $error = $loginResult['message'];
        }
    } else {
        $error = "Username dan password wajib diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIMASET</title>
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
        body{
            background: 
                linear-gradient(135deg, rgba(18,58,42,0.85), rgba(30,91,66,0.9)),
                var(--hero-image);
            background-size:cover;
            background-position:center;
            color:var(--ink);
            font-family:var(--font-body);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }

        .login-card{
            background:var(--paper);
            border:1px solid var(--border);
            border-radius:14px;
            box-shadow:0 15px 35px rgba(0,0,0,0.2);
            width:100%;
            max-width:420px;
            padding:35px 30px;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .brand-header{
            text-align:center;
            margin-bottom:28px;
        }

        .emblem{
            width:56px; height:56px;
            border-radius:50%;
            border:1.5px solid var(--gold);
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 12px auto;
            position:relative;
            background:rgba(180,135,46,0.08);
        }
        .emblem::before{
            content:'';
            position:absolute; inset:6px;
            border:1px solid rgba(180,135,46,0.55);
            border-radius:50%;
        }
        .emblem svg{ width:24px; height:24px; color:var(--gold); }

        .brand-header h1{
            font-family:var(--font-display);
            font-size:22px;
            font-weight:600;
            color:var(--forest-deep);
            margin-bottom:4px;
        }
        .brand-header p{
            font-family:var(--font-mono);
            font-size:11px;
            letter-spacing:1px;
            color:var(--ink-soft);
            text-transform:uppercase;
        }

        .alert{
            background:#F3E1D8;
            color:var(--clay);
            border:1px solid #E2BBA9;
            padding:10px 14px;
            border-radius:7px;
            font-size:13px;
            margin-bottom:20px;
            display:flex;
            align-items:center;
            gap:8px;
        }

        .form-group{
            margin-bottom:16px;
        }
        .form-group label{
            display:block;
            font-size:12.5px;
            font-weight:600;
            color:var(--ink-soft);
            margin-bottom:6px;
        }
        .form-group input{
            width:100%;
            padding:10px 14px;
            border:1px solid var(--border);
            border-radius:7px;
            font-size:14px;
            font-family:var(--font-body);
            background:var(--cream);
            color:var(--ink);
            transition:.2s;
        }
        .form-group input:focus{
            outline:none;
            border-color:var(--gold);
            background:var(--paper);
            box-shadow:0 0 0 3px rgba(180,135,46,0.15);
        }

        .btn-gold{
            width:100%;
            background:var(--gold);
            color:#2B2000;
            font-family:var(--font-body);
            font-weight:600;
            font-size:14px;
            padding:11px;
            border:none;
            border-radius:7px;
            cursor:pointer;
            margin-top:8px;
            transition:.2s;
        }
        .btn-gold:hover{
            background:#c69a3e;
            transform:translateY(-1px);
        }

        .login-footer{
            text-align:center;
            margin-top:24px;
            font-size:11.5px;
            color:var(--ink-soft);
            font-family:var(--font-mono);
            border-top:1px solid var(--border);
            padding-top:16px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-header">
            <div class="emblem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l9 4.5-9 4.5-9-4.5L12 3z"/><path d="M7 10.2v5.4c0 1.1 2.2 3.4 5 3.4s5-2.3 5-3.4v-5.4"/></svg>
            </div>
            <h1>SIMASET</h1>
            <p>Sistem Informasi Manajemen Aset</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert">
                <span>⚠️</span> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username..." required autocomplete="off" autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password..." required>
            </div>

            <button type="submit" class="btn-gold">Masuk ke Sistem</button>
        </form>

        <div class="login-footer">
            &copy; 2026 SIMASET &bull; Bagian Administrasi Umum
        </div>
    </div>

</body>
</html>