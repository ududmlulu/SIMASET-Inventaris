<?php
// deploy.php — Jalankan via Terminal Localhost: php deploy.php nama_file.php

// KONFIGURASI SERVER SFTP
$SFTP_HOST = '10.132.3.13';
$SFTP_PORT = 5522; // Port SFTP Kustom
$SFTP_USER = 'root'; // User SSH/SFTP server Anda
$SFTP_PASS = 'password_server_anda'; // Isikan password SSH/SFTP server Anda
$REMOTE_DIR = '/var/www/html/inventaris/'; // Folder tujuan di server

// 1. Tangkap File yang Ingin Diunggah
$file_to_sync = $argv[1] ?? '';

if (empty($file_to_sync)) {
    die("⚠️  Penggunaan: php deploy.php <nama_file>\nContoh: php deploy.php index.php\n");
}

if (!file_exists($file_to_sync)) {
    die("❌ Error: File '{$file_to_sync}' tidak ditemukan di folder local!\n");
}

// 2. Tampilkan Konfirmasi Persetujuan (Approval System)
echo "\n======================================================\n";
echo "⚠️  PERSATUAN SINKRONISASI VIA SFTP ⚠️\n";
echo "Target Host : sftp://{$SFTP_HOST}:{$SFTP_PORT}\n";
echo "Target Dir  : {$REMOTE_DIR}\n";
echo "File Local  : {$file_to_sync}\n";
echo "======================================================\n";
echo "Apakah Anda yakin ingin mengunggah file ini ke Server? (y/n): ";

$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if (strtolower($line) !== 'y') {
    echo "❌ Upload DIBATALKAN oleh pengguna.\n";
    exit;
}

// 3. Eksekusi Upload SFTP via Command Line (System Native SFTP/SCP)
echo "\n🚀 Mengunggah '{$file_to_sync}' ke sftp://{$SFTP_HOST}:{$SFTP_PORT}{$REMOTE_DIR}...\n";

// Menggunakan perintah scp dengan Port 5522
$remote_target = "{$SFTP_USER}@{$SFTP_HOST}:{$REMOTE_DIR}";
$command = "scp -P {$SFTP_PORT} {$file_to_sync} {$remote_target}";

// Eksekusi perintah
system($command, $return_var);

if ($return_var === 0) {
    echo "\n✅ Berhasil! File '{$file_to_sync}' telah terupload ke server via SFTP Port {$SFTP_PORT}.\n";
} else {
    echo "\n❌ Gagal mengunggah file. Pastikan port {$SFTP_PORT} terbuka di firewall dan kredensial SSH benar.\n";
}