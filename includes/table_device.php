<!-- includes/table_device.php -->
<div class="table-responsive">
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Kategori</th>
        <th>Brand & Model</th>
        <th>Serial Number</th>
        <th>Kode Inv</th>
        <th>Kode Brg</th>
        <th>Status</th>
        <th>Lokasi</th>
        <th>Warranty</th>
        <th>Harga</th>
        <?php if ($isAdmin): ?>
          <th style="text-align:right;">Aksi</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody id="tableBody">
      <?php 
      $no = 1;
      if ($result && $result->num_rows > 0):
          while($row = $result->fetch_assoc()): 
              $warrantyStatus = '-';
              if (!empty($row['warranty_expiry']) && $row['warranty_expiry'] !== '0000-00-00') {
                  $expiryTime = strtotime($row['warranty_expiry']);
                  $todayTime = strtotime(date('Y-m-d'));
                  $days = ($expiryTime - $todayTime) / (60 * 60 * 24);
                  if ($days < 0) $warrantyStatus = '<span style="color:var(--clay);font-weight:600;">Expired</span>';
                  elseif ($days <= 30) $warrantyStatus = '<span style="color:var(--gold);font-weight:600;">⚠️ ' . ceil($days) . ' hr</span>';
                  else $warrantyStatus = '<span style="color:var(--forest);font-weight:600;">✓ Active (' . date('Y', $expiryTime) . ')</span>';
              }
      ?>
      <tr data-category="<?= htmlspecialchars($row['brand_category']) ?>" 
          data-brand="<?= htmlspecialchars($row['brand_name']) ?>" 
          data-status="<?= htmlspecialchars($row['status']) ?>"
          data-purchase="<?= htmlspecialchars($row['purchase_date'] ?? '') ?>"
          data-warranty="<?= htmlspecialchars($row['warranty_expiry'] ?? '') ?>"
          data-price="<?= htmlspecialchars($row['price'] ?? '') ?>"
          data-notes="<?= htmlspecialchars($row['notes'] ?? '') ?>">
          <td style="text-align:center;" class="code"><?= $no++ ?></td>
          <td><span class="cat-tag"><?= htmlspecialchars($row['brand_category']) ?></span></td>
          <td class="name"><?= htmlspecialchars($row['brand_name']) ?> - <?= htmlspecialchars($row['model']) ?></td>
          <td class="code"><?= htmlspecialchars($row['serial_number']) ?></td>
          <td class="code"><?= htmlspecialchars($row['inventory_code'] ?? '-') ?></td>
          <td class="code"><?= htmlspecialchars($row['item_code'] ?? '-') ?></td>
          <td><span class="badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
          <td style="font-size:12px;"><?= !empty($row['location']) ? htmlspecialchars($row['location']) : '-' ?></td>
          <td style="font-size:12px;"><?= $warrantyStatus ?></td>
          <td style="font-size:12px; font-family:var(--font-mono);"><?= $row['price'] ? 'Rp ' . number_format($row['price'], 0, ',', '.') : '-' ?></td>
          <?php if ($isAdmin): ?>
          <td>
              <div class="row-actions">
                  <button class="icon-btn" onclick="editDevice(<?= $row['id'] ?>)" title="Ubah">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4z"/></svg>
                  </button>
                  <button class="icon-btn" onclick="deleteDevice(<?= $row['id'] ?>)" title="Hapus">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z"/></svg>
                  </button>
              </div>
          </td>
          <?php endif; ?>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="<?= $isAdmin ? '11' : '10' ?>" style="text-align:center;padding:30px;color:var(--ink-soft);">📭 Belum ada data perangkat.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>