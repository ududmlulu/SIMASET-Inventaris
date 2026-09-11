<?php
// includes/device_process.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Validasi CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("<script>alert('Akses Ditolak: Token keamanan tidak valid!'); window.history.back();</script>");
    }

    // Validasi Akses Admin
    if (!$isAdmin) {
        http_response_code(403);
        echo "<script>alert('Akses Ditolak: Anda tidak memiliki izin!'); window.location.href='index.php';</script>";
        exit;
    }

    $action = $_POST['action'];
    
    if ($action === 'add' || $action === 'update') {
        $device_type_id = $_POST['device_type_id'];
        $brand_id = $_POST['brand_id'];
        $model = trim($_POST['model']);
        
        $quantity = 1;
        $status = $_POST['status'];
        $location = ($status === 'terpasang') ? $_POST['location'] : NULL;
        
        $purchase_date = (!empty($_POST['purchase_date']) && $_POST['purchase_date'] !== '0000-00-00') ? $_POST['purchase_date'] : NULL;
        $warranty_expiry = (!empty($_POST['warranty_expiry']) && $_POST['warranty_expiry'] !== '0000-00-00') ? $_POST['warranty_expiry'] : NULL;
        
        $price = !empty($_POST['price']) ? str_replace(',', '', str_replace('.', '', $_POST['price'])) : NULL;
        $notes = $_POST['notes'] ?? NULL;

        try {
            if ($action === 'add') {
                $serial_number = strtoupper(trim($_POST['serial_number']));
                $inv_input = trim($_POST['inventory_code'] ?? '');
                $inventory_code = ($inv_input !== '') ? strtoupper($inv_input) : NULL;
                $item_input = trim($_POST['item_code'] ?? '');
                $item_code = ($item_input !== '') ? strtoupper($item_input) : NULL;

                $stmt = $conn->prepare("INSERT INTO devices (device_type_id, brand_id, model, serial_number, inventory_code, item_code, quantity, status, location, purchase_date, warranty_expiry, price, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissssissssds", $device_type_id, $brand_id, $model, $serial_number, $inventory_code, $item_code, $quantity, $status, $location, $purchase_date, $warranty_expiry, $price, $notes);
            } else {
                $id = $_POST['id'];
                
                // Ambil data kunci asli dari DB agar tidak berubah saat mode edit
                $stmt_old = $conn->prepare("SELECT serial_number, inventory_code, item_code FROM devices WHERE id = ?");
                $stmt_old->bind_param("i", $id);
                $stmt_old->execute();
                $res_old = $stmt_old->get_result()->fetch_assoc();
                $stmt_old->close();

                $serial_number  = $res_old['serial_number'];
                $inventory_code = $res_old['inventory_code'];
                $item_code      = $res_old['item_code'];

                $stmt = $conn->prepare("UPDATE devices SET device_type_id=?, brand_id=?, model=?, serial_number=?, inventory_code=?, item_code=?, quantity=?, status=?, location=?, purchase_date=?, warranty_expiry=?, price=?, notes=? WHERE id=?");
                $stmt->bind_param("iissssissssdsi", $device_type_id, $brand_id, $model, $serial_number, $inventory_code, $item_code, $quantity, $status, $location, $purchase_date, $warranty_expiry, $price, $notes, $id);
            }
            
            if ($stmt->execute()) {
                $msg = ($action === 'add') ? 'Data berhasil ditambahkan!' : 'Data berhasil diupdate!';
                echo "<script>alert('$msg'); window.location.href='index.php';</script>";
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $err_msg = addslashes($e->getMessage());
            echo "<script>alert('Error Database: {$err_msg}'); window.history.back();</script>";
        }
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM devices WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>alert('Data berhasil dihapus!'); window.location.href='index.php';</script>";
        }
        $stmt->close();
    }
}