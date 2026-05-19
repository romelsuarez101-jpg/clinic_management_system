<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

header('Cache-Control: no store, no cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$user_name  = $_SESSION['user_name'];
$student_id = $_SESSION['student_id'];

// Search and filter
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');

$where  = ["status = 'In Stock'", "quantity > 0"];
$params = [];
$types  = '';

if ($search) {
    $where[]  = "medicine_name LIKE ?";
    $params[] = "%{$search}%";
    $types   .= 's';
}
if ($category) {
    $where[]  = "category = ?";
    $params[] = $category;
    $types   .= 's';
}

$sql = "SELECT * FROM medicines WHERE " . implode(" AND ", $where) . " ORDER BY medicine_name ASC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $medicines = $stmt->get_result();
} else {
    $medicines = $conn->query($sql);
}

// Get categories
$cats = $conn->query("SELECT DISTINCT category FROM medicines WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categories = [];
while ($r = $cats->fetch_assoc()) $categories[] = $r['category'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Available Medicines — Clinic</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-left">
    <div class="topbar-logo">
      <img src="assets/images/logo.png" alt="Logo"
           style="width:52px;height:52px;object-fit:cover;border-radius:50%;border:2px solid #3fb950;">
    </div>
    <div class="topbar-title">Clinic Inventory System</div>
  </div>
  <div class="topbar-right">
    <div class="topbar-user">
      <div class="user-dot"></div>
      <span><?= htmlspecialchars($user_name) ?></span>
      <span class="user-role">· Student</span>
    </div>
    <a href="user_profile.php" class="btn btn-secondary btn-sm">⚙ Profile</a>
    <a href="user_logout.php"  class="btn btn-secondary btn-sm">Sign Out</a>
  </div>
</div>

<div class="layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="user_dashboard.php" class="nav-item">
        <span class="nav-icon">🏠</span> Dashboard
      </a>
      <a href="user_medicines.php" class="nav-item active">
        <span class="nav-icon">💊</span> View Medicines
      </a>
      <a href="user_requests.php" class="nav-item">
        <span class="nav-icon">📋</span> My Requests
      </a>
      <a href="health_check.php" class="nav-item">
  <span class="nav-icon">❤️</span> Health Check
</a>
    </div>
    <div class="sidebar-footer">
      <div class="sidebar-build">Student Portal v1.0</div>
      <div class="sidebar-build">© <?= date('Y') ?> ICAS School Clinic</div>
    </div>
  </aside>

  <main class="main-content">

    <div class="page-header">
      <div>
        <h1>Available Medicines</h1>
        <p>Browse medicines currently available at the clinic</p>
      </div>
    </div>

    <!-- Search and Filter -->
    <form method="GET" action="user_medicines.php">
      <div class="table-toolbar" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:20px;">
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input type="text" name="search"
                 value="<?= htmlspecialchars($search) ?>"
                 placeholder="Search medicines…"
                 onchange="this.form.submit()">
        </div>
        <div class="filter-group">
          <select class="filter-select" name="category" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option <?= $category===$cat?'selected':'' ?>>
                <?= htmlspecialchars($cat) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if ($search || $category): ?>
            <a href="user_medicines.php" class="btn btn-secondary btn-sm">✕ Clear</a>
          <?php endif; ?>
        </div>
      </div>
    </form>

    <!-- Medicine Cards -->
    <?php if ($medicines->num_rows > 0): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:24px;">
      <?php while ($m = $medicines->fetch_assoc()): ?>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:transform .2s,box-shadow .2s;"
           onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.4)'"
           onmouseout="this.style.transform='';this.style.boxShadow=''">

        <!-- Image -->
        <div style="height:150px;background:var(--surface2);display:flex;align-items:center;justify-content:center;overflow:hidden;">
          <?php if (!empty($m['image'])): ?>
            <img src="assets/uploads/medicines/<?= htmlspecialchars($m['image']) ?>"
                 style="width:100%;height:100%;object-fit:cover;"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-size:48px;opacity:.3">💊</div>
          <?php else: ?>
            <span style="font-size:48px;opacity:0.3">💊</span>
          <?php endif; ?>
        </div>

        <!-- Info -->
        <div style="padding:16px;">
          <div style="font-weight:600;font-size:15px;margin-bottom:6px;color:var(--text)">
            <?= htmlspecialchars($m['medicine_name']) ?>
          </div>
          <div style="margin-bottom:10px;">
            <span class="badge cat"><?= htmlspecialchars($m['category'] ?: '—') ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
            <span style="font-size:12px;color:var(--text2)">Available:</span>
            <span style="font-weight:700;color:<?= qtyColor((int)$m['quantity']) ?>;font-family:'DM Mono',monospace">
              <?= $m['quantity'] ?> <?= htmlspecialchars($m['unit'] ?: '') ?>
            </span>
          </div>
          <?php if ($m['expiration_date']): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <span style="font-size:12px;color:var(--text2)">Expires:</span>
            <span style="font-size:12px;font-family:'DM Mono',monospace;color:var(--text3)">
              <?= fmtDate($m['expiration_date']) ?>
            </span>
          </div>
          <?php endif; ?>
          <button onclick="openRequestModal(<?= $m['medicine_id'] ?>, '<?= htmlspecialchars($m['medicine_name'], ENT_QUOTES) ?>', <?= $m['quantity'] ?>)"
                  class="btn btn-primary btn-sm"
                  style="width:100%;justify-content:center;">
            📨 Request
          </button>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php else: ?>
      <div class="empty-state">
        <span class="empty-icon">💊</span>
        <p>No medicines available at the moment.</p>
      </div>
    <?php endif; ?>

  </main>
</div>

<!-- REQUEST MODAL -->
<div id="requestModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#161b22;border:1px solid #30363d;border-radius:14px;width:100%;max-width:500px;padding:32px;box-shadow:0 8px 32px rgba(0,0,0,0.5);margin:20px;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
      <h2 style="font-family:'Playfair Display',serif;font-size:20px;">Request Medicine</h2>
      <button onclick="closeRequestModal()"
              style="background:#1c2333;border:1px solid #30363d;border-radius:6px;width:32px;height:32px;color:#8b949e;font-size:18px;cursor:pointer;">✕</button>
    </div>

    <form method="POST" action="user_dashboard.php">
      <input type="hidden" name="form_action" value="request_medicine">
      <input type="hidden" name="medicine_id" id="modal_medicine_id">

      <div class="field" style="margin-bottom:16px;">
        <label>Medicine</label>
        <div id="modal_medicine_name"
             style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--accent);font-weight:600;">
        </div>
      </div>

      <div class="field" style="margin-bottom:16px;">
        <label>Quantity *</label>
        <input type="number" name="quantity" id="modal_quantity"
               min="1" value="1" required
               style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--text);outline:none;">
        <div id="modal_stock_hint" class="hint"></div>
      </div>

      <div class="field" style="margin-bottom:20px;">
        <label>Reason / Symptoms</label>
        <textarea name="reason"
                  placeholder="Describe your symptoms or reason..."
                  style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--text);outline:none;min-height:90px;resize:vertical;"></textarea>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid #30363d;">
        <button type="button" onclick="closeRequestModal()" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary">📨 Submit Request</button>
      </div>
    </form>
  </div>
</div>

<div id="toast" class="toast"></div>
<script src="assets/js/main.js"></script>
<script>
function openRequestModal(id, name, stock) {
  document.getElementById('modal_medicine_id').value  = id;
  document.getElementById('modal_medicine_name').textContent = name;
  document.getElementById('modal_quantity').max       = stock;
  document.getElementById('modal_stock_hint').textContent   = 'Max available: ' + stock;
  document.getElementById('requestModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeRequestModal() {
  document.getElementById('requestModal').style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('requestModal').addEventListener('click', function(e) {
  if (e.target === this) closeRequestModal();
});
</script>
</body>
</html>