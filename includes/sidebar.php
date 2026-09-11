<aside class="sidebar">
  <div class="emblem-wrap">
    <div class="emblem">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l9 4.5-9 4.5-9-4.5L12 3z"/><path d="M7 10.2v5.4c0 1.1 2.2 3.4 5 3.4s5-2.3 5-3.4v-5.4"/></svg>
    </div>
    <div class="emblem-text">SIMASET
      <span>Inventaris Perangkat</span>
    </div>
  </div>
  
  <div class="nav-label">Menu Utama</div>
  <nav>
    <a href="index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard Data
    </a>
    <?php if ($isAdmin): ?>
    <a href="manage_master.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'manage_master.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
      Kelola Master Data
    </a>
    <a href="manage_users.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      Kelola Pengguna
    </a>
    <?php endif; ?>
    <a href="includes/excel_export.php?export=excel" class="nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      Export Excel
    </a>
  </nav>

  <div class="sidebar-foot">
    <span>v2.0</span>
    <span><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?> (<?= strtoupper($_SESSION['role']); ?>)</span>
  </div>
</aside>