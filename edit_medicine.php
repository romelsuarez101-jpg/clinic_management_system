<?php
$pageTitle  = 'Edit Medicine';
$activePage = 'medicines';
require_once __DIR__ . '/config/session.php';
requireLogin();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: medicines.php'); exit(); }

$stmt = $conn->prepare("SELECT * FROM medicines WHERE medicine_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$med = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$med) { header('Location: medicines.php'); exit(); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['medicine_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $quantity = $_POST['quantity'] ?? '';
    $unit     = trim($_POST['unit'] ?? '');
    $status   = $_POST['status'] ?? 'In Stock';
    $remarks  = trim($_POST['remarks'] ?? '');
    $exp      = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null;
    $added    = !empty($_POST['date_added'])       ? $_POST['date_added']       : null;

    if (empty($name))     $errors[] = 'Medicine name is required.';
    if ($quantity === '') $errors[] = 'Quantity is required.';
    elseif (!is_numeric($quantity) || (int)$quantity < 0) $errors[] = 'Quantity must be a non-negative number.';

    if (empty($errors)) {
    $qty       = (int)$quantity;
    $imageName = $med['image']; // keep existing image by default

    if (!empty($_FILES['medicine_image']['name'])) {
        $file    = $_FILES['medicine_image'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Invalid image format.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Image too large. Max 2MB.';
        } else {
            // Delete old image if exists
            if ($med['image'] && file_exists(__DIR__ . '/assets/uploads/medicines/' . $med['image'])) {
                unlink(__DIR__ . '/assets/uploads/medicines/' . $med['image']);
            }
            $imageName  = uniqid('med_') . '.' . $ext;
            $uploadPath = __DIR__ . '/assets/uploads/medicines/' . $imageName;
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to upload image.';
                $imageName = $med['image'];
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "UPDATE medicines SET medicine_name=?, category=?, quantity=?, unit=?,
             expiration_date=?, date_added=?, status=?, remarks=?, image=? WHERE medicine_id=?"
        );
        $stmt->bind_param("ssissssssi", $name, $category, $qty, $unit, $exp, $added, $status, $remarks, $imageName, $id);

        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "Medicine '{$name}' updated successfully!"];
            header('Location: medicines.php');
            exit();
        } else {
            $errors[] = 'Database error: ' . $stmt->error;
            $stmt->close();
        }
    }
    $med = array_merge($med, $_POST);
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div><h1>Edit Medicine</h1><p>Updating: <strong><?= htmlspecialchars($med['medicine_name']) ?></strong></p></div>
  <div style="display:flex;gap:8px">
    <a href="view_medicine.php?id=<?= $id ?>" class="btn btn-secondary">👁 View</a>
    <a href="medicines.php" class="btn btn-secondary">← Back</a>
  </div>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-error" data-auto-dismiss>⚠ <?= implode(' | ', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="form-card">
  <form method="POST" action="edit_medicine.php?id=<?= $id ?>" enctype="multipart/form-data">
    <div class="form-grid">
      <div class="field span2">
        <label>Medicine Name *</label>
        <input type="text" name="medicine_name" value="<?= htmlspecialchars($med['medicine_name']) ?>" required>
      </div>
      <div class="field">
        <label>Category</label>
        <select name="category"><?= categoryOptions($med['category'] ?? '') ?></select>
      </div>
      <div class="field">
        <label>Unit</label>
        <select name="unit"><?= unitOptions($med['unit'] ?? '') ?></select>
      </div>
      <div class="field">
        <label>Quantity *</label>
        <input type="number" name="quantity" min="0" value="<?= htmlspecialchars($med['quantity']) ?>" required>
      </div>
      <div class="field">
        <label>Status</label>
        <select name="status"><?= statusOptions($med['status'] ?? 'In Stock') ?></select>
      </div>
      <div class="field">
        <label>Expiration Date</label>
        <input type="date" name="expiration_date" value="<?= htmlspecialchars($med['expiration_date'] ?? '') ?>">
      </div>
      <div class="field">
        <label>Date Added</label>
        <input type="date" name="date_added" value="<?= htmlspecialchars($med['date_added'] ?? '') ?>">
      </div>
      <div class="field span2">
        <label>Remarks</label>
        <textarea name="remarks"><?= htmlspecialchars($med['remarks'] ?? '') ?></textarea>
      </div>

      <div class="field span2">
  <label>Medicine Photo</label>
  <?php if ($med['image']): ?>
    <div style="margin-bottom:10px;display:flex;align-items:center;gap:12px;">
      <img src="assets/uploads/medicines/<?= htmlspecialchars($med['image']) ?>"
           style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
      <span style="font-size:12px;color:var(--text2)">Current photo — upload a new one to replace it</span>
    </div>
  <?php endif; ?>
  <input type="file" name="medicine_image" accept="image/*"
         style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;width:100%;color:var(--text2);cursor:pointer;">
  <div class="hint">Optional — JPG, PNG, max 2MB</div>
</div>
    </div>
    <div class="form-actions">
      <a href="medicines.php" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">💾 Update Medicine</button>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
