<?php
$pageTitle  = 'Low Stock';
$activePage = 'lowstock';
require_once __DIR__ . '/config/session.php';
requireLogin();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

$result = $conn->query("SELECT * FROM medicines WHERE quantity <= 10 OR status IN ('Low Stock','Out of Stock') ORDER BY quantity ASC, medicine_name ASC");

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div><h1>⚠️ Low Stock Alerts</h1><p>Medicines with 10 or fewer units remaining</p></div>
  <a href="medicines.php" class="btn btn-secondary">← All Medicines</a>
</div>
<div class="table-card">
  <table><thead><tr><th>ID</th><th>Medicine Name</th><th>Category</th><th>Qty Remaining</th><th>Unit</th><th>Status</th><th>Remarks</th><th>Actions</th></tr></thead>
  <tbody>
  <?php if ($result->num_rows > 0): while ($m = $result->fetch_assoc()): ?>
  <tr>
    <td><span class="med-id"><?= medId($m['medicine_id']) ?></span></td>
    <td><div class="med-name"><?= htmlspecialchars($m['medicine_name']) ?></div></td>
    <td><span class="badge cat"><?= htmlspecialchars($m['category'] ?: '—') ?></span></td>
    <td><span class="qty-num" style="color:<?= qtyColor((int)$m['quantity']) ?>;font-size:20px"><?= $m['quantity'] ?></span><?= $m['quantity']==0 ? ' <span style="font-size:11px;color:var(--red)">OUT</span>' : '' ?></td>
    <td><?= htmlspecialchars($m['unit'] ?: '—') ?></td>
    <td><span class="badge <?= statusClass($m['status']) ?>"><?= htmlspecialchars($m['status']) ?></span></td>
    <td style="color:var(--text2);font-size:12px;max-width:160px"><?= htmlspecialchars($m['remarks'] ?: '—') ?></td>
    <td><a href="edit_medicine.php?id=<?= $m['medicine_id'] ?>" class="btn btn-edit btn-sm">✏️ Update</a></td>
  </tr>
  <?php endwhile; else: ?>
  <tr><td colspan="8"><div class="empty-state"><span class="empty-icon">✅</span><p>All stocks are sufficient!</p></div></td></tr>
  <?php endif; ?>
  </tbody></table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
