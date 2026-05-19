<?php
$pageTitle  = 'Add Medicine';
$activePage = 'add';

require_once __DIR__ . '/config/session.php';
requireLogin();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

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

    // Handle image upload
    $imageName = null;
    if (!empty($_FILES['medicine_image']['name'])) {
        $file    = $_FILES['medicine_image'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $maxSize = 2 * 1024 * 1024;
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Invalid image format. Use JPG, PNG, or GIF.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Image too large. Max size is 2MB.';
        } else {
            $imageName  = uniqid('med_') . '.' . $ext;
            $uploadPath = __DIR__ . '/assets/uploads/medicines/' . $imageName;
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to upload image. Check folder permissions.';
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

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>Add Medicine</h1>
    <p>Add a new medicine to the clinic inventory</p>
  </div>
  <a href="medicines.php" class="btn btn-secondary">← Back to List</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-error" data-auto-dismiss>
    ⚠ <?= implode(' &nbsp;|&nbsp; ', array_map('htmlspecialchars', $errors)) ?>
  </div>
<?php endif; ?>

<div class="form-card">
  <form method="POST" action="add_medicine.php" enctype="multipart/form-data">
    <div class="form-grid">

      <div class="field span2">
        <label>Medicine Name *</label>
        <input type="text" name="medicine_name"
               value="<?= htmlspecialchars($old['medicine_name'] ?? '') ?>"
               placeholder="e.g. Paracetamol 500mg" required autofocus>
      </div>

      <div class="field">
        <label>Category</label>
        <select name="category">
          <?= categoryOptions($old['category'] ?? '') ?>
        </select>
      </div>

      <div class="field">
        <label>Unit</label>
        <select name="unit">
          <?= unitOptions($old['unit'] ?? '') ?>
        </select>
      </div>

      <div class="field">
        <label>Quantity *</label>
        <input type="number" name="quantity" min="0"
               value="<?= htmlspecialchars($old['quantity'] ?? '') ?>"
               placeholder="0" required>
        <div class="hint">Enter 0 if currently out of stock</div>
      </div>

      <div class="field">
        <label>Status</label>
        <select name="status">
          <?= statusOptions($old['status'] ?? 'In Stock') ?>
        </select>
      </div>

      <div class="field">
        <label>Expiration Date</label>
        <input type="date" name="expiration_date"
               value="<?= htmlspecialchars($old['expiration_date'] ?? '') ?>">
        <div class="hint">Leave blank if no expiration</div>
      </div>

      <div class="field">
        <label>Date Added</label>
        <input type="date" name="date_added"
               value="<?= htmlspecialchars($old['date_added'] ?? date('Y-m-d')) ?>">
      </div>

      <div class="field span2">
        <label>Remarks</label>
        <textarea name="remarks"
                  placeholder="Optional: storage instructions, precautions, notes…"><?= htmlspecialchars($old['remarks'] ?? '') ?></textarea>
      </div>

      <div class="field span2">
        <label>Medicine Photo</label>
        <div style="border:2px dashed var(--border);border-radius:10px;padding:20px;text-align:center;background:var(--surface2);cursor:pointer;transition:border-color .2s;"
             onclick="document.getElementById('imageInput').click()"
             ondragover="event.preventDefault();this.style.borderColor='var(--accent)'"
             ondragleave="this.style.borderColor='var(--border)'"
             ondrop="handleDrop(event)">
          <div id="imagePreview">
            <div style="font-size:36px;margin-bottom:8px;">📷</div>
            <div style="font-size:13px;color:var(--text2)">Click to upload or drag & drop</div>
            <div style="font-size:11px;color:var(--text3);margin-top:4px">JPG, PNG, GIF — Max 2MB</div>
          </div>
          <input type="file" id="imageInput" name="medicine_image" accept="image/*"
                 style="display:none;" onchange="previewImage(event)">
        </div>
      </div>

    </div>

    <div class="form-actions">
      <a href="medicines.php" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">💾 Save Medicine</button>
    </div>
  </form>
</div>

<script>
function previewImage(event) {
  const file = event.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('imagePreview').innerHTML = `
      <img src="${e.target.result}"
           style="max-height:160px;max-width:100%;border-radius:8px;object-fit:contain;">
      <div style="font-size:11px;color:var(--accent);margin-top:8px;">✓ ${file.name}</div>
    `;
  };
  reader.readAsDataURL(file);
}
function handleDrop(event) {
  event.preventDefault();
  event.currentTarget.style.borderColor = 'var(--border)';
  const file = event.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    const input = document.getElementById('imageInput');
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    previewImage({ target: { files: [file] } });
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>