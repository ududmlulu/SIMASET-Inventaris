<?php
// index.php
require_once 'config/database.php';

checkAuth();
$conn = getConnection();
$isAdmin = isAdmin();

// 1. Panggil Proses Logika CRUD
require_once 'includes/device_process.php';

// 2. Query Data untuk View Dashboard
$stats = getDeviceStatistics($conn);
$sql = "SELECT d.*, dt.type_name, dt.type_code, dt.icon, b.brand_name, b.brand_code, b.category as brand_category
        FROM devices d
        JOIN device_types dt ON d.device_type_id = dt.id
        JOIN brands b ON d.brand_id = b.id
        ORDER BY b.category, b.brand_name, d.model";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SIMASET — Sistem Inventaris Perangkat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">

  <!-- Include Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <main>
    <!-- Include Topbar / Hero -->
    <?php include 'includes/topbar.php'; ?>

    <div class="content">
      <!-- Include Widget Statistik -->
      <?php include 'includes/stats_widget.php'; ?>

      <!-- Panel Tabel Data -->
      <div class="panel">
        <div class="panel-head">
          <h2>Daftar Inventaris</h2>
          <div class="filter-row">
            <select id="filterCategory" onchange="filterTable()">
              <option value="">Semua Kategori</option>
              <option value="Access Point">📶 Access Point</option>
              <option value="Switch">🔀 Switch</option>
              <option value="Router">🌐 Router</option>
            </select>
            <select id="filterBrand" onchange="filterTable()">
              <option value="">Semua Brand</option>
            </select>
            <select id="filterStatus" onchange="filterTable()">
              <option value="">Semua Status</option>
              <option value="idle">Idle</option>
              <option value="terpasang">Terpasang</option>
              <option value="maintenance">Maintenance</option>
              <option value="retired">Retired</option>
            </select>
          </div>
        </div>

        <!-- Include Komponen Tabel -->
        <?php include 'includes/table_device.php'; ?>

        <div class="panel-foot">
          <span id="rowCount">— perangkat ditampilkan</span>
          <span>Status Sistem: Aktif</span>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Include Modal Input/Edit -->
<?php include 'includes/modal_device.php'; ?>

<!-- Data Brands JSON untuk Filter Javascript -->
<script>
  const brandsData = <?php 
      $brandsByCategory = [];
      $brandsResult = getBrandsByCategory($conn);
      if ($brandsResult) {
          while($brand = $brandsResult->fetch_assoc()) {
              $brandsByCategory[$brand['category']][] = [
                  'id' => $brand['id'],
                  'name' => $brand['brand_name']
              ];
          }
      }
      echo json_encode($brandsByCategory, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
  ?>;
</script>

<!-- Script JS Utama -->
<script src="assets/js/app.js"></script>
</body>
</html>