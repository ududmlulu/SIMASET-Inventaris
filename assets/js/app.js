function loadBrands() {
  const typeSelect = document.getElementById('deviceType');
  if (!typeSelect) return;
  const brandSelect = document.getElementById('brandSelect');
  const selectedType = typeSelect.options[typeSelect.selectedIndex].text;
  
  brandSelect.innerHTML = '<option value="">Pilih Brand</option>';
  let category = '';
  if (selectedType.includes('Access Point')) category = 'Access Point';
  else if (selectedType.includes('Switch')) category = 'Switch';
  else if (selectedType.includes('Router')) category = 'Router';
  
  if (category && typeof brandsData !== 'undefined' && brandsData[category]) {
      brandsData[category].forEach(brand => {
          const option = document.createElement('option');
          option.value = brand.id;
          option.textContent = brand.name;
          brandSelect.appendChild(option);
      });
  }
}

function setFieldsReadOnly(isReadOnly) {
  const fields = ['serialNumber', 'inventoryCode', 'itemCode'];
  fields.forEach(id => {
      const input = document.getElementById(id);
      if (input) {
          input.readOnly = isReadOnly;
          input.style.backgroundColor = isReadOnly ? 'var(--border)' : 'var(--cream)';
          input.style.cursor = isReadOnly ? 'not-allowed' : 'text';
      }
  });
}

function toggleLocation() {
  const statusEl = document.getElementById('status');
  if (!statusEl) return;
  const status = statusEl.value;
  const locationGroup = document.getElementById('locationGroup');
  const locationInput = document.getElementById('location');
  
  if (status === 'terpasang') {
      locationGroup.style.display = 'block';
      locationInput.required = true;
  } else {
      locationGroup.style.display = 'none';
      locationInput.required = false;
      locationInput.value = '';
  }
}

function showAddModal() {
  document.getElementById('modalTitle').textContent = 'Tambah Data Perangkat';
  document.getElementById('formAction').value = 'add';
  document.getElementById('deviceForm').reset();
  document.getElementById('editId').value = '';
  document.getElementById('purchaseDate').value = '';
  document.getElementById('warrantyExpiry').value = '';
  document.getElementById('price').value = '';
  document.getElementById('notes').value = '';
  
  setFieldsReadOnly(false);
  
  document.getElementById('locationGroup').style.display = 'none';
  document.getElementById('brandSelect').innerHTML = '<option value="">Pilih Brand</option>';
  document.getElementById('deviceModal').style.display = 'block';
}

function editDevice(id) {
  const row = document.querySelector(`button[onclick="editDevice(${id})"]`).closest('tr');
  const cells = row.querySelectorAll('td');
  
  document.getElementById('modalTitle').textContent = 'Edit Data Perangkat';
  document.getElementById('formAction').value = 'update';
  document.getElementById('editId').value = id;
  
  const category = cells[1].textContent.trim();
  const brandModel = cells[2].textContent.trim().split(' - ');
  const brand = brandModel[0] || '';
  const model = brandModel[1] || '';
  
  const serial = cells[3].textContent.trim();
  const invCode = cells[4].textContent.trim();
  const itemCode = cells[5].textContent.trim();
  const status = cells[6].textContent.trim().toLowerCase();
  const location = cells[7].textContent.trim();
  
  const purchaseDate = row.dataset.purchase ? row.dataset.purchase.trim() : '';
  const warrantyExpiry = row.dataset.warranty ? row.dataset.warranty.trim() : '';
  const price = row.dataset.price ? row.dataset.price.trim() : '';
  const notes = row.dataset.notes ? row.dataset.notes.trim() : '';
  
  const typeSelect = document.getElementById('deviceType');
  for(let i = 0; i < typeSelect.options.length; i++) {
      if(typeSelect.options[i].text.includes(category)) {
          typeSelect.value = typeSelect.options[i].value;
          break;
      }
  }
  
  loadBrands();
  setTimeout(() => {
      const brandSelect = document.getElementById('brandSelect');
      for(let i = 0; i < brandSelect.options.length; i++) {
          if(brandSelect.options[i].text === brand) {
              brandSelect.value = brandSelect.options[i].value;
              break;
          }
      }
  }, 100);
  
  document.getElementById('model').value = model;
  document.getElementById('serialNumber').value = serial;
  document.getElementById('inventoryCode').value = invCode !== '-' ? invCode : '';
  document.getElementById('itemCode').value = itemCode !== '-' ? itemCode : '';
  document.getElementById('quantity').value = 1;
  document.getElementById('status').value = status;
  
  setFieldsReadOnly(true);
  
  if (purchaseDate && purchaseDate !== '0000-00-00' && purchaseDate !== 'null') {
      document.getElementById('purchaseDate').value = purchaseDate;
  } else {
      document.getElementById('purchaseDate').value = '';
  }

  if (warrantyExpiry && warrantyExpiry !== '0000-00-00' && warrantyExpiry !== 'null') {
      document.getElementById('warrantyExpiry').value = warrantyExpiry;
  } else {
      document.getElementById('warrantyExpiry').value = '';
  }

  document.getElementById('price').value = price;
  document.getElementById('notes').value = notes;
  
  if (status === 'terpasang') {
      document.getElementById('locationGroup').style.display = 'block';
      document.getElementById('location').value = location !== '-' ? location : '';
  } else {
      document.getElementById('locationGroup').style.display = 'none';
  }
  
  document.getElementById('deviceModal').style.display = 'block';
}

function deleteDevice(id) {
  if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
      document.getElementById('deleteId').value = id;
      document.getElementById('deleteForm').submit();
  }
}

function closeModal() {
  const modal = document.getElementById('deviceModal');
  if (modal) modal.style.display = 'none';
}

window.onclick = function(event) {
  const modal = document.getElementById('deviceModal');
  if (event.target === modal) {
      modal.style.display = 'none';
  }
}

function filterTable() {
  const searchInput = document.getElementById('searchInput');
  if (!searchInput) return;
  const search = searchInput.value.toLowerCase();
  const category = document.getElementById('filterCategory').value;
  const brand = document.getElementById('filterBrand').value;
  const status = document.getElementById('filterStatus').value;
  
  const rows = document.querySelectorAll('#tableBody tr[data-brand]');
  let visibleCount = 0;
  
  rows.forEach(row => {
      let show = true;
      const text = row.textContent.toLowerCase();
      
      if (search && !text.includes(search)) show = false;
      if (category && row.dataset.category !== category) show = false;
      if (brand && row.dataset.brand !== brand) show = false;
      if (status && row.dataset.status !== status) show = false;
      
      row.style.display = show ? '' : 'none';
      if (show) visibleCount++;
  });
  
  const rowCount = document.getElementById('rowCount');
  if (rowCount) rowCount.textContent = `${visibleCount} perangkat ditampilkan`;
}

function formatPrice(input) {
  let value = input.value.replace(/\D/g, '');
  if (value) {
      value = parseInt(value).toLocaleString('id-ID');
      input.value = value;
  }
}

function populateBrandFilter() {
  const brandFilter = document.getElementById('filterBrand');
  if (!brandFilter || typeof brandsData === 'undefined') return;

  brandFilter.innerHTML = '<option value="">Semua Brand</option>';
  const categoryFilter = document.getElementById('filterCategory').value;
  const brandsSet = new Set();

  if (categoryFilter && brandsData[categoryFilter]) {
      brandsData[categoryFilter].forEach(b => brandsSet.add(b.name));
  } else {
      Object.keys(brandsData).forEach(cat => {
          brandsData[cat].forEach(b => brandsSet.add(b.name));
      });
  }

  Array.from(brandsSet).sort().forEach(brandName => {
      const optionEl = document.createElement('option');
      optionEl.value = brandName;
      optionEl.textContent = brandName;
      brandFilter.appendChild(optionEl);
  });
}

document.addEventListener('DOMContentLoaded', function() {
  populateBrandFilter();
  
  const categoryFilter = document.getElementById('filterCategory');
  if (categoryFilter) {
      categoryFilter.addEventListener('change', function() {
          populateBrandFilter();
          filterTable();
      });
  }

  document.querySelectorAll('.filter-row select').forEach(el => {
      if (el.id !== 'filterCategory') {
          el.addEventListener('change', filterTable);
      }
  });
  
  filterTable();
});