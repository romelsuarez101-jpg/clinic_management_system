<?php
$pageTitle  = 'Expiring Soon';
$activePage = 'expiring';
require_once __DIR__ . '/config/session.php';
requireLogin();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

$result = $conn->query("SELECT *, DATEDIFF(expiration_date, CURDATE()) AS days_left FROM medicines WHERE expiration_date IS NOT NULL AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY) ORDER BY expiration_date ASC");

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div><h1>⏳ Expiring Soon</h1><p>Medicines expiring within the next 60 days</p></div>
  <a href="medicines.php" class="btn btn-secondary">← All Medicines</a>
</div>
<div class="table-card">
  <table><thead><tr><th>ID</th><th>Medicine Name</th><th>Category</th><th>Qty</th><th>Expiration</th><th>Days Left</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php if ($result->num_rows > 0): while ($m = $result->fetch_assoc()):
    $dl = (int)$m['days_left'];
    $dlLabel = $dl < 0 ? '⛔ Expired' : ($dl < 7 ? "🔴 {$dl} days" : ($dl < 15 ? "🟡 {$dl} days" : "🟢 {$dl} days"));
    $dlColor = $dl < 0 ? 'var(--red)' : ($dl < 7 ? 'var(--red)' : ($dl < 15 ? 'var(--yellow)' : 'var(--text2)'));
  ?>
  <tr>
    <td><span class="med-id"><?= medId($m['medicine_id']) ?></span></td>
    <td><div class="med-name"><?= htmlspecialchars($m['medicine_name']) ?></div></td>
    <td><span class="badge cat"><?= htmlspecialchars($m['category'] ?: '—') ?></span></td>
    <td><?= $m['quantity'] ?> <?= htmlspecialchars($m['unit'] ?: '') ?></td>
    <td class="mono"><?= fmtDate($m['expiration_date']) ?></td>
    <td><strong style="color:<?= $dlColor ?>"><?= $dlLabel ?></strong></td>
    <td><span class="badge <?= statusClass($m['status']) ?>"><?= htmlspecialchars($m['status']) ?></span></td>
    <td><div class="actions-cell">
      <a href="view_medicine.php?id=<?= $m['medicine_id'] ?>" class="btn btn-view btn-sm">👁</a>
      <a href="edit_medicine.php?id=<?= $m['medicine_id'] ?>" class="btn btn-edit btn-sm">✏️</a>
    </div></td>
  </tr>
  <?php endwhile; else: ?>
  <tr><td colspan="8"><div class="empty-state"><span class="empty-icon">✅</span><p>No medicines expiring within 60 days!</p></div></td></tr>
  <?php endif; ?>
  </tbody></table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
