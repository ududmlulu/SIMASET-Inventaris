<?php if ($isAdmin): ?>
<div id="deviceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Tambah Data Perangkat</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="deviceForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="editId" value="">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Kategori Device *</label>
                    <select name="device_type_id" id="deviceType" required onchange="loadBrands()">
                        <option value="">Pilih Kategori</option>
                        <?php 
                        $types = getDeviceTypes($conn);
                        while($type = $types->fetch_assoc()): 
                        ?>
                        <option value="<?= $type['id'] ?>"><?= $type['icon'] ?> <?= $type['type_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Brand/Merk *</label>
                    <select name="brand_id" id="brandSelect" required>
                        <option value="">Pilih Brand</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Model *</label>
                    <input type="text" name="model" id="model" placeholder="Contoh: UniFi U6-LR" required>
                </div>
                
                <div class="form-group">
                    <label>Serial Number *</label>
                    <input type="text" name="serial_number" id="serialNumber" placeholder="Contoh: AP-UBI-2024-001" style="text-transform: uppercase;" required>
                </div>
                
                <div class="form-group">
                    <label>Kode Inventaris</label>
                    <input type="text" name="inventory_code" id="inventoryCode" placeholder="Contoh: INV-001" style="text-transform: uppercase;">
                </div>
                
                <div class="form-group">
                    <label>Kode Barang</label>
                    <input type="text" name="item_code" id="itemCode" placeholder="Contoh: BRG-001" style="text-transform: uppercase;">
                </div>
                
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" id="quantity" value="1" readonly style="background-color: var(--border); cursor: not-allowed;">
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" id="status" onchange="toggleLocation()" required>
                        <option value="idle">Idle</option>
                        <option value="terpasang">Terpasang</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
                
                <div class="form-group" id="locationGroup" style="display:none;">
                    <label>Lokasi Terpasang</label>
                    <input type="text" name="location" id="location" placeholder="Contoh: Ruang Server Lt.2">
                </div>
                
                <div class="form-group">
                    <label>Tanggal Pembelian</label>
                    <input type="date" name="purchase_date" id="purchaseDate" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Masa Berlaku Warranty</label>
                    <input type="date" name="warranty_expiry" id="warrantyExpiry" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="text" name="price" id="price" placeholder="Contoh: 2500000" onkeyup="formatPrice(this)">
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Catatan</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-danger-soft" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-gold">💾 Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>
<?php endif; ?>