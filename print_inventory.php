<?php
require_once 'config/session.php';
requireLogin();
require_once 'config/db.php';
require_once 'config/helpers.php';

// Filter support
$filterStatus   = $_GET['status']   ?? '';
$filterCategory = $_GET['category'] ?? '';

$where  = [];
$params = [];
$types  = '';

if ($filterStatus)   { $where[] = "status = ?";   $params[] = $filterStatus;   $types .= 's'; }
if ($filterCategory) { $where[] = "category = ?"; $params[] = $filterCategory; $types .= 's'; }

$sql = "SELECT * FROM medicines";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY category ASC, medicine_name ASC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Stats for report header
$total   = $conn->query("SELECT COUNT(*) AS c FROM medicines")->fetch_assoc()['c'];
$inStock = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE status='In Stock'")->fetch_assoc()['c'];
$low     = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE status IN ('Low Stock','Out of Stock')")->fetch_assoc()['c'];
$expired = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE status='Expired' OR expiration_date < CURDATE()")->fetch_assoc()['c'];

// Categories for filter
$cats = $conn->query("SELECT DISTINCT category FROM medicines WHERE category IS NOT NULL AND category != '' ORDER BY category");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inventory Report — MedVault</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg:#0d1117;--surface:#161b22;--surface2:#1c2333;--border:#30363d;
      --accent:#3fb950;--text:#e6edf3;--text2:#8b949e;--text3:#6e7681;
      --red:#f85149;--green:#3fb950;--yellow:#e3b341;--blue:#58a6ff;--purple:#bc8cff;
    }
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);padding:32px;font-size:13px}
    .print-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;padding-bottom:20px;border-bottom:2px solid var(--accent)}
    .print-logo{display:flex;align-items:center;gap:12px}
    .print-logo-icon{width:44px;height:44px;background:linear-gradient(135deg,#3fb950,#58a6ff);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px}
    .print-logo-text{font-family:'Playfair Display',serif;font-size:22px}
    .print-logo-sub{font-size:12px;color:var(--text2);margin-top:2px}
    .print-meta{text-align:right;font-size:12px;color:var(--text2)}
    .print-meta strong{display:block;font-size:14px;color:var(--text)}
    .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
    .stat-mini{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:14px 16px;position:relative;overflow:hidden}
    .stat-mini::before{content:'';position:absolute;top:0;left:0;right:0;height:2px}
    .stat-mini.g::before{background:var(--green)}.stat-mini.b::before{background:var(--blue)}
    .stat-mini.y::before{background:var(--yellow)}.stat-mini.r::before{background:var(--red)}
    .stat-mini-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text2)}
    .stat-mini-value{font-family:'Playfair Display',serif;font-size:28px;margin-top:4px}
    .toolbar{display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap}
    .toolbar select{background:var(--surface2);border:1px solid var(--border);border-radius:7px;padding:8px 12px;font-family:'DM Sans',sans-serif;font-size:13px;color:var(--text2);outline:none;cursor:pointer}
    .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .2s;text-decoration:none}
    .btn-primary{background:var(--accent);color:#0d1117}.btn-primary:hover{background:#45c957}
    .btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border)}.btn-secondary:hover{border-color:#3d4450}
    table{width:100%;border-collapse:collapse;background:var(--surface);border:1px solid var(--border);border-radius:8px;overflow:hidden}
    thead th{background:var(--surface2);padding:10px 13px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text2);white-space:nowrap}
    tbody tr{border-bottom:1px solid var(--border)}
    tbody tr:last-child{border:none}
    tbody tr:hover{background:rgba(255,255,255,.02)}
    td{padding:11px 13px;font-size:12px;color:var(--text);vertical-align:middle}
    .med-name{font-weight:600}
    .mono{font-family:'DM Mono',monospace;font-size:11px}
    .badge{display:inline-flex;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700}
    .in-stock{background:rgba(63,185,80,.15);color:var(--green)}
    .low-stock{background:rgba(227,179,65,.15);color:var(--yellow)}
    .out-stock{background:rgba(248,81,73,.15);color:var(--red)}
    .expired{background:rgba(188,140,255,.15);color:var(--purple)}
    .cat{background:rgba(88,166,255,.1);color:var(--blue)}
    .no-print{} 
    @media print{
      body{background:#fff;color:#111;padding:20px}
      .no-print{display:none!important}
      table{border:1px solid #ccc}
      thead th{background:#f5f5f5;color:#444;border-bottom:1px solid #ccc}
      tbody tr{border-bottom:1px solid #eee}
      td{color:#111}
      .badge{border:1px solid #ccc}
      .in-stock{background:#e6f4ea;color:#2d7a3a}
      .low-stock{background:#fef9e7;color:#856404}
      .out-stock{background:#fce8e6;color:#c0392b}
      .expired{background:#f3e8ff;color:#6d28d9}
      .cat{background:#e8f0fe;color:#1a56db}
      .stat-mini{background:#f9f9f9;border:1px solid #ddd}
      .stat-mini-value,.print-logo-text{color:#111}
      .stat-mini-label,.print-meta,.print-logo-sub{color:#555}
      .print-header{border-bottom:2px solid #333}
    }
  </style>
</head>
<body>

<!-- Header -->
<div class="print-header">
  <div class="print-logo">
    <div class="print-logo-icon">💊</div>
    <div>
      <div class="print-logo-text">MedVault</div>
      <div class="print-logo-sub">School Clinic Inventory System</div>
    </div>
  </div>
  <div class="print-meta">
    <strong>Inventory Report</strong>
    Generated: <?= date('F d, Y — h:i A') ?><br>
    Prepared by: <?= htmlspecialchars(getCurrentUser()) ?>
  </div>
</div>

<!-- Stats -->
<div class="stats-row">
  <div class="stat-mini g"><div class="stat-mini-label">Total Items</div><div class="stat-mini-value"><?= $total ?></div></div>
  <div class="stat-mini b"><div class="stat-mini-label">In Stock</div><div class="stat-mini-value"><?= $inStock ?></div></div>
  <div class="stat-mini y"><div class="stat-mini-label">Low / Out</div><div class="stat-mini-value"><?= $low ?></div></div>
  <div class="stat-mini r"><div class="stat-mini-label">Expired</div><div class="stat-mini-value"><?= $expired ?></div></div>
</div>

<!-- Toolbar -->
<div class="toolbar no-print">
  <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <select name="status" onchange="this.form.submit()">
      <option value="">All Status</option>
      <?php foreach(['In Stock','Low Stock','Out of Stock','Expired'] as $s): ?>
        <option <?= $filterStatus===$s?'selected':'' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
    <select name="category" onchange="this.form.submit()">
      <option value="">All Categories</option>
      <?php while($c=$cats->fetch_assoc()): ?>
        <option <?= $filterCategory===$c['category']?'selected':'' ?>><?= htmlspecialchars($c['category']) ?></option>
      <?php endwhile; ?>
    </select>
    <?php if ($filterStatus || $filterCategory): ?>
      <a href="print_inventory.php" class="btn btn-secondary" style="font-size:12px">✕ Clear</a>
    <?php endif; ?>
  </form>
  <div style="margin-left:auto;display:flex;gap:8px">
    <a href="medicines.php" class="btn btn-secondary">← Back</a>
    <button class="btn btn-primary" onclick="window.print()">🖨 Print / Save PDF</button>
  </div>
</div>

<!-- Table -->
<table>
  <thead><tr>
    <th>ID</th><th>Medicine Name</th><th>Category</th>
    <th>Qty</th><th>Unit</th><th>Expiration</th><th>Date Added</th>
    <th>Status</th><th>Remarks</th>
  </tr></thead>
  <tbody>
  <?php if ($result->num_rows > 0): ?>
    <?php while ($m = $result->fetch_assoc()):
      $sc = match($m['status']) {
        'In Stock'     => 'in-stock',
        'Low Stock'    => 'low-stock',
        'Out of Stock' => 'out-stock',
        'Expired'      => 'expired',
        default        => ''
      };
    ?>
    <tr>
      <td class="mono"><?= medId((int)$m['medicine_id']) ?></td>
      <td><div class="med-name"><?= htmlspecialchars($m['medicine_name']) ?></div></td>
      <td><span class="badge cat"><?= htmlspecialchars($m['category'] ?: '—') ?></span></td>
      <td style="font-weight:700;color:<?= $m['quantity']==0?'var(--red)':($m['quantity']<=10?'var(--yellow)':'var(--text)') ?>">
        <?= $m['quantity'] ?>
      </td>
      <td><?= htmlspecialchars($m['unit'] ?: '—') ?></td>
      <td class="mono"><?= fmtDate($m['expiration_date']) ?></td>
      <td class="mono"><?= fmtDate($m['date_added']) ?></td>
      <td><span class="badge <?= $sc ?>"><?= htmlspecialchars($m['status']) ?></span></td>
      <td style="color:var(--text2);max-width:180px;font-size:11px"><?= htmlspecialchars($m['remarks'] ?: '—') ?></td>
    </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text3)">No records found</td></tr>
  <?php endif; ?>
  </tbody>
</table>

<div style="margin-top:20px;font-size:11px;color:var(--text3);text-align:center" class="no-print">
  Tip: Use <strong>Ctrl+P</strong> or the Print button above to save as PDF
</div>

</body>
</html>
