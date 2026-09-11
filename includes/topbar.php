<div class="hero">
  <div class="hero-eyebrow">Sistem Informasi Manajemen Aset</div>
  <h1>Inventaris Perangkat Jaringan & Sarana IT</h1>
  <p>Pantau kondisi, lokasi, kode inventaris, serta masa garansi seluruh unit fisik secara terpusat.</p>

  <div class="topbar-row">
    <div class="search-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      <input id="searchInput" type="text" placeholder="Cari model, serial, kode, atau lokasi..." onkeyup="filterTable()">
    </div>
    <div style="display:flex; gap:8px;">
      <a href="logout.php" class="btn btn-outline" style="border-color:rgba(255,255,255,0.4);">
        🚪 Logout
      </a>
      <?php if ($isAdmin): ?>
      <button class="btn btn-gold" onclick="showAddModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Perangkat
      </button>
      <?php endif; ?>
    </div>
  </div>
</div>