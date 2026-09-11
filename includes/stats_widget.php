<!-- includes/stats_widget.php -->
<div class="stats">
  <div class="stat-card">
    <div class="stat-top">
      <span class="stat-label">Total Device</span>
      <div class="stat-icon">📦</div>
    </div>
    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
    <div class="stat-delta">Semua jenis perangkat</div>
  </div>
  <?php foreach($stats['by_type'] ?? [] as $type => $total): ?>
  <div class="stat-card">
    <div class="stat-top">
      <span class="stat-label"><?= htmlspecialchars($type) ?></span>
      <div class="stat-icon"><?= $type == 'Access Point' ? '📶' : ($type == 'Switch' ? '🔀' : '🌐') ?></div>
    </div>
    <div class="stat-value"><?= $total ?></div>
    <div class="stat-delta">Unit fisik terdaftar</div>
  </div>
  <?php endforeach; ?>
</div>