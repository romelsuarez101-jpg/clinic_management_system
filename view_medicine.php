<?php
$pageTitle  = 'View Medicine';
$activePage = 'medicines';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: medicines.php'); exit(); }

$stmt = $conn->prepare("SELECT * FROM medicines WHERE medicine_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$m = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$m) { header('Location: medicines.php'); exit(); }

$days = daysLeft($m['expiration_date']);

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div>
    <h1><?= htmlspecialchars($m['medicine_name']) ?></h1>
    <p>ID: <span class="mono"><?= medId($m['medicine_id']) ?></span></p>
  </div>
  <div style="display:flex;gap:8px">
    <a href="edit_medicine.php?id=<?= $id ?>" class="btn btn-edit">✏️ Edit</a>
    <a href="delete_medicine.php?id=<?= $id ?>" class="btn btn-danger delete-confirm" data-name="<?= htmlspecialchars($m['medicine_name']) ?>">🗑 Delete</a>
    <a href="medicines.php" class="btn btn-secondary">← Back</a>
  </div>
</div>

<div class="form-card">
  <div class="detail-grid">
    <div class="detail-item">
      <div class="detail-label">Status</div>
      <div class="detail-value"><span class="badge <?= statusClass($m['status']) ?>"><?= htmlspecialchars($m['status']) ?></span></div>
    </div>
    <div class="detail-item">
      <div class="detail-label">Category</div>
      <div class="detail-value"><?= $m['category'] ? '<span class="badge cat">'.htmlspecialchars($m['category']).'</span>' : '—' ?></div>
    </div>
    <div class="detail-item full">
      <div class="detail-label">Medicine Name</div>
      <div class="detail-value" style="font-size:18px;font-weight:600"><?= htmlspecialchars($m['medicine_name']) ?></div>
    </div>
    <div class="detail-item">
      <div class="detail-label">Quantity</div>
      <div class="detail-value" style="font-size:26px;font-family:'Playfair Display',serif;color:<?= qtyColor((int)$m['quantity']) ?>">
        <?= $m['quantity'] ?> <span style="font-size:13px;font-family:'DM Sans',sans-serif;color:var(--text2)"><?= htmlspecialchars($m['unit'] ?: '') ?></span>
      </div>
    </div>
    <div class="detail-item">
      <div class="detail-label">Unit</div>
      <div class="detail-value"><?= htmlspecialchars($m['unit'] ?: '—') ?></div>
    </div>
    <div class="detail-item">
      <div class="detail-label">Expiration Date</div>
      <div class="detail-value">
        <?= fmtDateLong($m['expiration_date']) ?>
        <?php if ($days !== null): ?>
          <div style="font-size:12px;margin-top:4px;color:<?= daysColor($days) ?>">
            <?= $days < 0 ? '⚠ Expired '.abs($days).' days ago' : "⏳ {$days} days remaining" ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="detail-item">
      <div class="detail-label">Date Added</div>
      <div class="detail-value"><?= fmtDateLong($m['date_added']) ?></div>
    </div>
    <div class="detail-item full">
      <div class="detail-label">Remarks</div>
      <div class="detail-value"><?= $m['remarks'] ? htmlspecialchars($m['remarks']) : '—' ?></div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
