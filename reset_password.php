<?php
// reset_password.php
require_once 'config/database.php';

$conn = getConnection();

// Password plain yang ingin digunakan
$admin_pass  = password_hash('admin123', PASSWORD_DEFAULT);
$viewer_pass = password_hash('user123', PASSWORD_DEFAULT);

// Update/Insert Akun Admin & Viewer
$sql = "INSERT INTO users (id, username, password, full_name, role, is_active) 
        VALUES 
        (1, 'admin', '$admin_pass', 'Administrator', 'admin', 1),
        (2, 'viewer', '$viewer_pass', 'Guest Viewer', 'view_only', 1)
        ON DUPLICATE KEY UPDATE 
        password = VALUES(password),
        is_active = 1";

if ($conn->query($sql)) {
    echo "<h2 style='color:green;'>✅ Reset Password Berhasil!</h2>";
    echo "<p>Akun yang siap digunakan:</p>";
    echo "<ul>
            <li><strong>Admin:</strong> Username: <code>admin</code> | Password: <code>admin123</code></li>
            <li><strong>Viewer:</strong> Username: <code>viewer</code> | Password: <code>user123</code></li>
          </ul>";
    echo "<a href='login.php' style='padding:10px 15px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Ke Halaman Login</a>";
} else {
    echo "<h2 style='color:red;'>❌ Gagal: " . $conn->error . "</h2>";
}
?>