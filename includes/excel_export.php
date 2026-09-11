<?php
// includes/excel_export.php
require_once '../config/database.php';

// Memastikan hanya user terautentikasi yang bisa download
checkAuth();

$conn = getConnection();

// Query data menggunakan JOIN (bukan View) agar kolom baru otomatis terbaca dari tabel
$sql = "SELECT d.*, 
        dt.type_name, dt.type_code, dt.icon,
        b.brand_name, b.brand_code, b.category as brand_category
        FROM devices d
        JOIN device_types dt ON d.device_type_id = dt.id
        JOIN brands b ON d.brand_id = b.id
        ORDER BY b.category, b.brand_name, d.model";
$result = $conn->query($sql);

// Set nama file dengan ekstensi .csv
$filename = "Inventaris_Barang_" . date('Y-m-d_H-i-s') . ".csv";

// Header HTTP untuk download file CSV Excel
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Buka output stream PHP
$output = fopen('php://output', 'w');

// Masukkan UTF-8 BOM agar Excel membaca karakter khusus, spasi, dan format tanggal dengan benar
fputs($output, "\xEF\xBB\xBF");

// Header Kolom Tabel (Ditambahkan Kode Inventaris & Kode Barang)
fputcsv($output, [
    'No', 
    'Kategori', 
    'Brand', 
    'Model', 
    'Serial Number', 
    'Kode Inventaris',
    'Kode Barang',
    'Qty', 
    'Status', 
    'Lokasi', 
    'Tanggal Beli', 
    'Warranty Expiry', 
    'Status Garansi', 
    'Harga (Rp)', 
    'Catatan'
]);

/**
 * Helper function untuk merapikan tanggal menjadi DD-MM-YYYY secara seragam
 */
function formatDateClean($dateString) {
    if (empty($dateString) || $dateString === '0000-00-00' || $dateString === '-') {
        return '-';
    }
    $timestamp = strtotime($dateString);
    if ($timestamp === false) {
        return '-';
    }
    return date('d-m-Y', $timestamp);
}

// Data Iterasi
$no = 1;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $purchaseDate = formatDateClean($row['purchase_date']);
        $warrantyDate = formatDateClean($row['warranty_expiry']);
        $price = !empty($row['price']) ? 'Rp ' . number_format($row['price'], 0, ',', '.') : '-';

        // Hitung status garansi secara dinamis
        $warrantyStatus = '-';
        if (!empty($row['warranty_expiry'])) {
            $days = (strtotime($row['warranty_expiry']) - time()) / (60 * 60 * 24);
            if ($days < 0) {
                $warrantyStatus = 'Expired';
            } elseif ($days <= 30) {
                $warrantyStatus = 'Warning (' . ceil($days) . ' days)';
            } else {
                $warrantyStatus = 'Active';
            }
        }

        // Tulis baris data
        fputcsv($output, [
            $no++,
            $row['brand_category'] ?? '',
            $row['brand_name'] ?? '',
            $row['model'] ?? '',
            $row['serial_number'] ?? '',
            
            // Output kolom baru
            $row['inventory_code'] ?? '-',
            $row['item_code'] ?? '-',
            
            $row['quantity'] ?? 1,
            ucfirst($row['status'] ?? ''),
            !empty($row['location']) ? $row['location'] : '-',
            // Gunakan petik tunggal ' di awal agar Excel membacanya sebagai Teks Murni
            "'" . $purchaseDate,
            "'" . $warrantyDate,
            $warrantyStatus,
            $price,
            $row['notes'] ?? '-'
        ]);
    }
}

fclose($output);
exit;