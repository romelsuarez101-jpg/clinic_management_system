<?php
$pageTitle  = 'Medicines';
$activePage = 'medicines';
require_once __DIR__ . '/config/session.php';
requireLogin();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'add_medicine') {
    $old      = $_POST;
    $name     = trim($_POST['medicine_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $quantity = $_POST['quantity'] ?? '';
    $unit     = trim($_POST['unit'] ?? '');
    $status   = $_POST['status'] ?? 'In Stock';
    $remarks  = trim($_POST['remarks'] ?? '');
    $exp      = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null;
    $added    = !empty($_POST['date_added'])       ? $_POST['date_added']       : date('Y-m-d');

    if (empty($name))     $errors[] = 'Medicine name is required.';
    if ($quantity === '') $errors[] = 'Quantity is required.';
    elseif (!is_numeric($quantity) || (int)$quantity < 0) $errors[] = 'Quantity must be a non-negative number.';

    $imageName = null;
    if (!empty($_FILES['medicine_image']['name'])) {
        $file    = $_FILES['medicine_image'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $maxSize = 2 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload error code: ' . $file['error'];
        } elseif (!in_array($ext, $allowed)) {
            $errors[] = 'Invalid image format. Use JPG, PNG, or GIF.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Image too large. Max size is 2MB.';
        } else {
            $uploadDir = __DIR__ . '/assets/uploads/medicines/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $imageName  = uniqid('med_') . '.' . $ext;
            $uploadPath = $uploadDir . $imageName;
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to move uploaded file.';
                $imageName = null;
            }
        }
    }

    if (empty($errors)) {
        $qty  = (int)$quantity;
        $stmt = $conn->prepare(
            "INSERT INTO medicines
               (medicine_name, category, quantity, unit, expiration_date, date_added, status, remarks, image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssissssss",
            $name, $category, $qty, $unit,
            $exp, $added, $status, $remarks, $imageName
        );
        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "Medicine '{$name}' added successfully!"];
            header('Location: medicines.php');
            exit();
        } else {
            $errors[] = 'Database error: ' . $stmt->error;
            $stmt->close();
        }
    }
}

$medicines  = $conn->query("SELECT * FROM medicines ORDER BY medicine_id DESC");
$catResult  = $conn->query("SELECT DISTINCT category FROM medicines WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categories = [];
while ($r = $catResult->fetch_assoc()) $categories[] = $r['category'];
$totalCount = $medicines->num_rows;

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div><h1>Medicine Inventory</h1><p>Manage all clinic medicines and supplies</p></div>
  <div style="display:flex;gap:8px;">
    <button id="toggleViewBtn" onclick="toggleView()" class="btn btn-secondary">🃏 Card View</button>
    <button class="btn btn-primary" onclick="openAddModal()">➕ Add Medicine</button>
  </div>
</div>

<div class="table-card">
  <div class="table-toolbar">
    <div class="search-box">
      <span class="search-icon">🔍</span>
      <input type="text" id="search-input" placeholder="Search medicines…">
    </div>
    <div class="filter-group">
      <select class="filter-select" id="filter-status">
        <option value="">All Status</option>
        <option>In Stock</option><option>Low Stock</option>
        <option>Out of Stock</option><option>Expired</option>
      </select>
      <select class="filter-select" id="filter-category">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
      </select>
      <span id="result-count" style="font-size:12px;color:var(--text3);white-space:nowrap"><?= $totalCount ?> results</span>
    </div>
  </div>
  <table id="medicine-table">
    <thead><tr>
  <th style="width:50px">ID</th>
  <th style="width:60px">Photo</th>
  <th style="width:150px">Medicine Name</th>
  <th style="width:120px">Category</th>
  <th style="width:55px">Qty</th>
  <th style="width:75px">Unit</th>
  <th style="width:95px">Expiration</th>
  <th style="width:95px">Date Added</th>
  <th style="width:100px">Status</th>
  <th style="width:105px">Actions</th>
</tr></thead>
    <tbody>
    <?php if ($medicines->num_rows > 0): while ($m = $medicines->fetch_assoc()): ?>
   <tr data-name="<?= strtolower(htmlspecialchars($m['medicine_name'])) ?>"
    data-status="<?= htmlspecialchars($m['status']) ?>"
    data-category="<?= htmlspecialchars($m['category'] ?? '') ?>">
      <td><span class="med-id"><?= medId($m['medicine_id']) ?></span></td>
      <td>
        <?php if (!empty($m['image'])): ?>
          <img src="assets/uploads/medicines/<?= htmlspecialchars($m['image']) ?>"
     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
               style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
        <?php else: ?>
          <div style="width:44px;height:44px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px;">💊</div>
        <?php endif; ?>
      </td>
      <td><div class="med-name"><?= htmlspecialchars($m['medicine_name']) ?></div></td>
      <td><span class="badge cat"><?= htmlspecialchars($m['category'] ?: '—') ?></span></td>
      <td><span class="qty-num" style="color:<?= qtyColor((int)$m['quantity']) ?>"><?= $m['quantity'] ?></span></td>
      <td><?= htmlspecialchars($m['unit'] ?: '—') ?></td>
      <td class="mono"><?= fmtDate($m['expiration_date']) ?></td>
      <td class="mono"><?= fmtDate($m['date_added']) ?></td>
      <td><span class="badge <?= statusClass($m['status']) ?>"><?= htmlspecialchars($m['status']) ?></span></td>
      <td><div class="actions-cell">
        <a href="view_medicine.php?id=<?= $m['medicine_id'] ?>" class="btn btn-view btn-sm">👁</a>
        <a href="edit_medicine.php?id=<?= $m['medicine_id'] ?>" class="btn btn-edit btn-sm">✏️</a>
        <button onclick="confirmDelete(<?= $m['medicine_id'] ?>, '<?= htmlspecialchars($m['medicine_name'], ENT_QUOTES) ?>')" class="btn btn-danger btn-sm">🗑</button>
      </div></td>
</tr>
    <?php endwhile; ?>
   <tr id="empty-row" style="display:none"><td colspan="10"><div class="empty-state"><span class="empty-icon">🔍</span><p>No medicines match your search</p></div></td></tr>
    <?php else: ?>
    <tr><td colspan="10"><div class="empty-state"><span class="empty-icon">💊</span><p>No medicines yet. <a href="add_medicine.php" style="color:var(--accent)">Add one →</a></p></div></td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ADD MEDICINE MODAL -->
<div id="addModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#161b22;border:1px solid #30363d;border-radius:14px;width:100%;max-width:600px;max-height:90vh;overflow-y:auto;padding:32px;box-shadow:0 8px 32px rgba(0,0,0,0.5);margin:20px;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
      <h2 style="font-family:'Playfair Display',serif;font-size:22px;">Add Medicine</h2>
      <button onclick="closeAddModal()" style="background:#1c2333;border:1px solid #30363d;border-radius:6px;width:32px;height:32px;color:#8b949e;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;">✕</button>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error" style="margin-bottom:16px;">
        ⚠ <?= implode(' | ', array_map('htmlspecialchars', $errors)) ?>
      </div>
    <?php endif; ?>

   <form method="POST" action="medicines.php" enctype="multipart/form-data">
      <input type="hidden" name="form_action" value="add_medicine">
      <div class="form-grid">

        <div class="field span2">
          <label>Medicine Name *</label>
          <input type="text" name="medicine_name"
                 value="<?= htmlspecialchars($old['medicine_name'] ?? '') ?>"
                 placeholder="e.g. Paracetamol 500mg" required>
        </div>

        <div class="field">
          <label>Category</label>
          <select name="category"><?= categoryOptions($old['category'] ?? '') ?></select>
        </div>

        <div class="field">
          <label>Unit</label>
          <select name="unit"><?= unitOptions($old['unit'] ?? '') ?></select>
        </div>

        <div class="field">
          <label>Quantity *</label>
          <input type="number" name="quantity" min="0"
                 value="<?= htmlspecialchars($old['quantity'] ?? '') ?>"
                 placeholder="0" required>
        </div>

        <div class="field">
          <label>Status</label>
          <select name="status"><?= statusOptions($old['status'] ?? 'In Stock') ?></select>
        </div>

        <div class="field">
          <label>Expiration Date</label>
          <input type="date" name="expiration_date"
                 value="<?= htmlspecialchars($old['expiration_date'] ?? '') ?>">
        </div>

        <div class="field">
          <label>Date Added</label>
          <input type="date" name="date_added"
                 value="<?= htmlspecialchars($old['date_added'] ?? date('Y-m-d')) ?>">
        </div>

        <div class="field span2">
          <label>Remarks</label>
          <textarea name="remarks" placeholder="Optional notes..."><?= htmlspecialchars($old['remarks'] ?? '') ?></textarea>
        </div>
        
        <div class="field span2">
  <label>Medicine Photo</label>
  <div style="border:2px dashed var(--border);border-radius:10px;padding:20px;text-align:center;background:var(--surface2);cursor:pointer;transition:border-color .2s;"
       onclick="document.getElementById('modalImageInput').click()"
       ondragover="event.preventDefault();this.style.borderColor='var(--accent)'"
       ondragleave="this.style.borderColor='var(--border)'"
       ondrop="handleModalDrop(event)">
    <div id="modalImagePreview">
      <div style="font-size:32px;margin-bottom:8px;">📷</div>
      <div style="font-size:13px;color:var(--text2)">Click to upload or drag & drop</div>
      <div style="font-size:11px;color:var(--text3);margin-top:4px">JPG, PNG, GIF — Max 2MB</div>
    </div>
    <input type="file" id="modalImageInput" name="medicine_image" accept="image/*"
           style="display:none;" onchange="previewModalImage(event)">
  </div>
</div>

      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:20px;border-top:1px solid #30363d;margin-top:8px;">
        <button type="button" onclick="closeAddModal()" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Save Medicine</button>
      </div>
    </form>

  </div>
</div>

<!-- CARD VIEW -->
<div id="cardView" style="display:none;">
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
    <?php
    // Re-run query for cards
    $allMeds = $conn->query("SELECT * FROM medicines ORDER BY medicine_id DESC");
    while ($m = $allMeds->fetch_assoc()):
    ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:transform .2s,box-shadow .2s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.4)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
      <!-- Image -->
      <div style="height:140px;background:var(--surface2);display:flex;align-items:center;justify-content:center;overflow:hidden;">
        <?php if ($m['image']): ?>
          <img src="assets/uploads/medicines/<?= htmlspecialchars($m['image']) ?>"
     style="width:100%;height:100%;object-fit:cover;"
     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
<div style="width:38px;height:38px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;display:none;align-items:center;justify-content:center;font-size:16px;">💊</div>
        <?php else: ?>
          <span style="font-size:48px;opacity:0.4">💊</span>
        <?php endif; ?>
      </div>
      <!-- Info -->
      <div style="padding:14px;">
        <div style="font-weight:600;font-size:14px;margin-bottom:6px;color:var(--text)"><?= htmlspecialchars($m['medicine_name']) ?></div>
        <div style="margin-bottom:8px;"><span class="badge cat"><?= htmlspecialchars($m['category'] ?: '—') ?></span></div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
          <span style="font-size:12px;color:var(--text2)">Qty: <strong style="color:<?= qtyColor((int)$m['quantity']) ?>"><?= $m['quantity'] ?></strong> <?= htmlspecialchars($m['unit'] ?: '') ?></span>
          <span class="badge <?= statusClass($m['status']) ?>" style="font-size:10px;"><?= htmlspecialchars($m['status']) ?></span>
        </div>
        <div style="display:flex;gap:6px;">
          <a href="view_medicine.php?id=<?= $m['medicine_id'] ?>" class="btn btn-view btn-sm" style="flex:1;justify-content:center;">👁</a>
          <a href="edit_medicine.php?id=<?= $m['medicine_id'] ?>" class="btn btn-edit btn-sm" style="flex:1;justify-content:center;">✏️</a>
          <button onclick="confirmDelete(<?= $m['medicine_id'] ?>, '<?= htmlspecialchars($m['medicine_name'], ENT_QUOTES) ?>')" class="btn btn-danger btn-sm" style="flex:1;justify-content:center;">🗑</button>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#161b22;border:1px solid #30363d;border-radius:14px;width:100%;max-width:420px;padding:32px;box-shadow:0 8px 32px rgba(0,0,0,0.5);margin:20px;text-align:center;">

    <div style="width:64px;height:64px;background:rgba(248,81,73,0.15);border:2px solid rgba(248,81,73,0.4);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 16px;">🗑️</div>

    <h2 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px;color:#e6edf3;">Delete Medicine?</h2>
    <p style="color:#8b949e;font-size:14px;margin-bottom:6px;">You are about to delete:</p>
    <p id="deleteMedName" style="color:#f85149;font-size:15px;font-weight:600;margin-bottom:20px;padding:10px;background:rgba(248,81,73,0.08);border-radius:8px;border:1px solid rgba(248,81,73,0.2);">—</p>
    <p style="color:#6e7681;font-size:12px;margin-bottom:24px;">⚠ This action cannot be undone. The record will be permanently removed.</p>

    <div style="display:flex;gap:10px;justify-content:center;">
      <button onclick="closeDeleteModal()" class="btn btn-secondary" style="min-width:100px;">Cancel</button>
      <a id="deleteConfirmBtn" href="#" class="btn btn-danger" style="min-width:100px;">Yes, Delete</a>
    </div>

  </div>
</div>

<script>
function openAddModal() {
  const modal = document.getElementById('addModal');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
  <?php if (!empty($errors)): ?>
  // Keep modal open if there were errors
  <?php endif; ?>
}
function closeAddModal() {
  document.getElementById('addModal').style.display = 'none';
  document.body.style.overflow = '';
  // Reset image preview
  document.getElementById('modalImagePreview').innerHTML = `
    <div style="font-size:32px;margin-bottom:8px;">📷</div>
    <div style="font-size:13px;color:var(--text2)">Click to upload or drag & drop</div>
    <div style="font-size:11px;color:var(--text3);margin-top:4px">JPG, PNG, GIF — Max 2MB</div>
  `;
  document.getElementById('modalImageInput').value = '';
}
// Close when clicking outside
document.getElementById('addModal').addEventListener('click', function(e) {
  if (e.target === this) closeAddModal();
});
<?php if (!empty($errors)): ?>
// Auto-reopen if form had errors
window.addEventListener('DOMContentLoaded', () => openAddModal());
<?php endif; ?>

// ── DELETE MODAL ──
function confirmDelete(id, name) {
  document.getElementById('deleteMedName').textContent = name;
  document.getElementById('deleteConfirmBtn').href = 'delete_medicine.php?id=' + id;
  const modal = document.getElementById('deleteModal');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
  document.getElementById('deleteModal').style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) closeDeleteModal();
});

// ── VIEW TOGGLE ──
let isCardView = false;
function toggleView() {
  isCardView = !isCardView;
  const table    = document.querySelector('.table-card');
  const cardView = document.getElementById('cardView');
  const btn      = document.getElementById('toggleViewBtn');
  if (isCardView) {
    table.style.display    = 'none';
    cardView.style.display = 'block';
    btn.textContent        = '📋 Table View';
  } else {
    table.style.display    = '';
    cardView.style.display = 'none';
    btn.textContent        = '🃏 Card View';
  }
}

function previewModalImage(event) {
  const file = event.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('modalImagePreview').innerHTML = `
      <img src="${e.target.result}"
           style="max-height:120px;max-width:100%;border-radius:8px;object-fit:contain;">
      <div style="font-size:11px;color:var(--accent);margin-top:8px;">✓ ${file.name}</div>
    `;
  };
  reader.readAsDataURL(file);
}

function handleModalDrop(event) {
  event.preventDefault();
  event.currentTarget.style.borderColor = 'var(--border)';
  const file = event.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    const input = document.getElementById('modalImageInput');
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    const fakeEvent = { target: { files: [file] } };
    previewModalImage(fakeEvent);
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
