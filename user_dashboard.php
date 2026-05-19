<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit();
}

header('Cache-Control: no store, no cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$user_id    = $_SESSION['user_id'];
$user_name  = $_SESSION['user_name'];
$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$medicines = $conn->query("SELECT * FROM medicines WHERE status = 'In Stock' AND quantity > 0 ORDER BY medicine_name ASC");

$requests = $conn->prepare("
    SELECT mr.*, m.medicine_name, m.unit, m.image
    FROM medicine_requests mr
    JOIN medicines m ON mr.medicine_id = m.medicine_id
    WHERE mr.user_id = ?
    ORDER BY mr.requested_at DESC LIMIT 5
");
$requests->bind_param("i", $user_id);
$requests->execute();
$myRequests = $requests->get_result();

$totalReq = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id = ?");
$totalReq->bind_param("i", $user_id); $totalReq->execute();
$totalCount = $totalReq->get_result()->fetch_assoc()['c'];

$pendingReq = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id = ? AND status = 'Pending'");
$pendingReq->bind_param("i", $user_id); $pendingReq->execute();
$pendingCount = $pendingReq->get_result()->fetch_assoc()['c'];

$approvedReq = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id = ? AND status = 'Approved'");
$approvedReq->bind_param("i", $user_id); $approvedReq->execute();
$approvedCount = $approvedReq->get_result()->fetch_assoc()['c'];

$reqError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'request_medicine') {
    $medicine_id = (int)($_POST['medicine_id'] ?? 0);
    $quantity    = (int)($_POST['quantity']    ?? 1);
    $reason      = trim($_POST['reason']       ?? '');

    if (!$medicine_id) {
        $reqError = 'Please select a medicine.';
    } elseif ($quantity < 1) {
        $reqError = 'Quantity must be at least 1.';
    } else {
        $chk = $conn->prepare("SELECT quantity FROM medicines WHERE medicine_id = ? AND status = 'In Stock'");
        $chk->bind_param("i", $medicine_id); $chk->execute();
        $med = $chk->get_result()->fetch_assoc(); $chk->close();
        if (!$med) {
            $reqError = 'Medicine is not available.';
        } elseif ($quantity > $med['quantity']) {
            $reqError = 'Requested quantity exceeds available stock.';
        } else {
            $stmt = $conn->prepare("INSERT INTO medicine_requests (user_id, medicine_id, quantity, reason) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $user_id, $medicine_id, $quantity, $reason);
            if ($stmt->execute()) {
                $stmt->close();
                header('Location: user_dashboard.php?success=1'); exit();
            } else {
                $reqError = 'Failed to submit request.';
                $stmt->close();
            }
        }
    }
}

$flashSuccess = isset($_GET['success']) ? 'Medicine request submitted successfully!' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Student Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Exo+2:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* ── RESET & BASE ── */
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: 'Exo 2', sans-serif;
      background: #020e1e;
      background-image:
        radial-gradient(ellipse at 15% 15%, rgba(0,245,255,0.06) 0%, transparent 50%),
        radial-gradient(ellipse at 85% 85%, rgba(0,100,200,0.05) 0%, transparent 50%);
      background-attachment: fixed;
      color: #e6edf3; min-height: 100vh;
      font-size: 14px; line-height: 1.5;
    }
    a { text-decoration: none; color: inherit; }
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-thumb { background: rgba(0,245,255,0.2); border-radius: 4px; }

    /* ── TOPBAR ── */
    .topbar {
      position: sticky; top: 0; z-index: 100;
      height: 62px;
      background: rgba(2,14,30,0.95);
      border-bottom: 1px solid rgba(0,245,255,0.1);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 24px;
      backdrop-filter: blur(20px);
      box-shadow: 0 4px 24px rgba(0,0,0,0.4);
    }
    .topbar-left { display: flex; align-items: center; gap: 12px; }
    .topbar-logo img {
      width: 48px; height: 48px;
      object-fit: cover; border-radius: 50%;
      border: 2px solid #00f5ff;
      box-shadow: 0 0 12px rgba(0,245,255,0.3);
    }
    .topbar-title {
      font-family: 'Syne', sans-serif;
      font-size: 16px; font-weight: 700; color: #e6edf3;
    }
    .topbar-right { display: flex; align-items: center; gap: 10px; }
    .topbar-user {
      display: flex; align-items: center; gap: 8px;
      background: rgba(0,245,255,0.05);
      border: 1px solid rgba(0,245,255,0.1);
      border-radius: 8px; padding: 6px 12px;
      font-size: 13px;
    }
    .topbar-user span { color: #e6edf3; font-weight: 600; }
    .user-role { color: #3d5266 !important; font-weight: 400 !important; }
    .user-dot {
      width: 7px; height: 7px; background: #00f5ff;
      border-radius: 50%; box-shadow: 0 0 6px #00f5ff;
    }
    .btn {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 8px 14px; border-radius: 8px;
      font-size: 12px; font-weight: 600;
      cursor: pointer; border: none;
      transition: all .2s; white-space: nowrap;
      text-decoration: none; font-family: 'Exo 2', sans-serif;
    }
    .btn-secondary {
      background: rgba(0,245,255,0.05); color: #7a8fa6;
      border: 1px solid rgba(0,245,255,0.1);
    }
    .btn-secondary:hover { border-color: rgba(0,245,255,0.25); color: #e6edf3; }
    .btn-primary {
      background: linear-gradient(135deg, #00b4d8, #00f5ff);
      color: #020e1e; font-weight: 700;
      box-shadow: 0 4px 15px rgba(0,245,255,0.25);
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,245,255,0.4); }

    /* ── LAYOUT ── */
    .layout { display: flex; min-height: calc(100vh - 62px); }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 230px; flex-shrink: 0;
      background: rgba(2,14,30,0.98);
      border-right: 1px solid rgba(0,245,255,0.08);
      display: flex; flex-direction: column;
      min-height: calc(100vh - 62px);
      position: sticky; top: 62px;
      height: calc(100vh - 62px);
      overflow-y: auto;
    }
    .sidebar-section { padding: 16px 12px 8px; }
    .sidebar-label {
      font-size: 9px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .15em;
      color: rgba(0,245,255,0.25);
      padding: 0 10px; margin-bottom: 6px;
    }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 12px; border-radius: 10px;
      font-size: 13px; font-weight: 500;
      color: #7a8fa6;
      transition: all .2s; margin-bottom: 2px;
      cursor: pointer;
    }
    .nav-item:hover { background: rgba(0,245,255,0.05); color: #e6edf3; }
    .nav-item.active {
      background: rgba(0,245,255,0.08); color: #00f5ff;
      border-left: 2px solid #00f5ff;
    }
    .nav-icon { font-size: 14px; width: 18px; text-align: center; flex-shrink: 0; }

    /* Sidebar footer profile */
    .sidebar-footer {
      margin-top: auto;
      padding: 16px 12px;
      border-top: 1px solid rgba(0,245,255,0.08);
    }
    .sidebar-profile {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 12px;
      background: rgba(0,245,255,0.04);
      border: 1px solid rgba(0,245,255,0.08);
      border-radius: 12px; margin-bottom: 10px;
      cursor: pointer; transition: all .2s;
      text-decoration: none;
    }
    .sidebar-profile:hover {
      background: rgba(0,245,255,0.08);
      border-color: rgba(0,245,255,0.15);
    }
    .sidebar-avatar {
      width: 36px; height: 36px;
      background: linear-gradient(135deg, #00b4d8, #00f5ff);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px; flex-shrink: 0; color: #020e1e;
    }
    .sidebar-profile-name { font-size: 13px; font-weight: 600; color: #e6edf3; line-height: 1.2; }
    .sidebar-profile-role { font-size: 10px; color: #00f5ff; text-transform: uppercase; letter-spacing: .08em; }
    .sidebar-build { font-size: 10px; color: #3d5266; line-height: 1.7; text-align: center; }

    /* ── MAIN CONTENT ── */
    .main-content { flex: 1; padding: 28px; overflow-y: auto; overflow-x: hidden; min-width: 0; }

    /* ── ALERTS ── */
    .alert {
      padding: 12px 16px; border-radius: 10px;
      font-size: 13px; font-weight: 500; margin-bottom: 20px;
      display: flex; align-items: center; gap: 8px;
    }
    .alert-success { background: rgba(63,185,80,.1); color: #3fb950; border: 1px solid rgba(63,185,80,.25); }
    .alert-error   { background: rgba(248,81,73,.1);  color: #f85149; border: 1px solid rgba(248,81,73,.25); }

    /* ── PAGE HEADER ── */
    .page-header { margin-bottom: 28px; }
    .page-header h1 {
      font-family: 'Syne', sans-serif;
      font-size: 26px; font-weight: 700;
    }
    .page-header p { color: #7a8fa6; font-size: 13px; margin-top: 4px; }

    /* ── STAT CARDS ── */
    .stats-grid {
      display: grid; grid-template-columns: repeat(3,1fr);
      gap: 16px; margin-bottom: 28px;
    }
    .stat-card {
      background: rgba(255,255,255,0.02);
      border: 1px solid rgba(0,245,255,0.08);
      border-radius: 14px; padding: 20px 22px;
      position: relative; overflow: hidden;
      transition: transform .2s, box-shadow .2s;
      backdrop-filter: blur(10px);
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,245,255,0.1); }
    .stat-card::before {
      content: ''; position: absolute;
      top: 0; left: 0; right: 0; height: 2px;
    }
    .stat-card.blue::before   { background: linear-gradient(90deg, #00f5ff, #58a6ff); }
    .stat-card.yellow::before { background: linear-gradient(90deg, #e3b341, #f78166); }
    .stat-card.green::before  { background: linear-gradient(90deg, #3fb950, #00f5ff); }
    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #7a8fa6; }
    .stat-value { font-family: 'Syne', sans-serif; font-size: 36px; color: #e6edf3; margin: 6px 0 4px; }
    .stat-sub   { font-size: 12px; color: #3d5266; }
    .stat-icon  { position: absolute; top: 18px; right: 18px; font-size: 24px; opacity: .15; color: #00f5ff; }

    /* ── CARDS ── */
    .card {
      background: rgba(255,255,255,0.02);
      border: 1px solid rgba(0,245,255,0.08);
      border-radius: 14px; margin-bottom: 24px;
      backdrop-filter: blur(10px); overflow: hidden;
    }
    .card-header {
      padding: 16px 20px;
      border-bottom: 1px solid rgba(0,245,255,0.06);
      display: flex; align-items: center; justify-content: space-between;
    }
    .card-header h3 { font-size: 15px; font-weight: 600; color: #e6edf3; }
    .card-header p  { font-size: 12px; color: #3d5266; margin-top: 2px; }
    .card-body { padding: 20px; }

    /* ── FORM ── */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .span2 { grid-column: 1/-1; }
    .field label {
      display: block; font-size: 10px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .08em;
      color: rgba(0,245,255,0.4); margin-bottom: 7px;
    }
    .field input, .field select, .field textarea {
      width: 100%;
      background: rgba(0,245,255,0.03);
      border: 1px solid rgba(0,245,255,0.1);
      border-radius: 8px; padding: 11px 14px;
      font-size: 14px; color: #e6edf3;
      outline: none; transition: border-color .2s;
      font-family: 'Exo 2', sans-serif;
    }
    .field input:focus, .field select:focus, .field textarea:focus {
      border-color: rgba(0,245,255,0.35);
      background: rgba(0,245,255,0.05);
    }
    .field select option { background: #071428; }
    .field textarea { min-height: 90px; resize: vertical; }
    .field input::placeholder, .field textarea::placeholder { color: #3d5266; }

    /* ── TABLE ── */
    table { width: 100%; border-collapse: collapse; }
    thead th {
      background: rgba(0,245,255,0.04);
      padding: 10px 16px; text-align: left;
      font-size: 10px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .08em;
      color: rgba(0,245,255,0.5);
      border-bottom: 1px solid rgba(0,245,255,0.08);
    }
    tbody tr { border-bottom: 1px solid rgba(0,245,255,0.04); transition: background .15s; }
    tbody tr:last-child { border: none; }
    tbody tr:hover { background: rgba(0,245,255,0.02); }
    td { padding: 13px 16px; font-size: 13px; color: #e6edf3; vertical-align: middle; }
    .med-name { font-weight: 600; }
    .mono { font-family: 'DM Mono', monospace; font-size: 12px; }

    /* ── BADGES ── */
    .badge {
      display: inline-flex; padding: 3px 9px; border-radius: 20px;
      font-size: 11px; font-weight: 600; white-space: nowrap;
    }
    .badge.in-stock   { background: rgba(63,185,80,.12);  color: #3fb950; border: 1px solid rgba(63,185,80,.2); }
    .badge.low-stock  { background: rgba(227,179,65,.12); color: #e3b341; border: 1px solid rgba(227,179,65,.2); }
    .badge.out-stock  { background: rgba(248,81,73,.12);  color: #f85149; border: 1px solid rgba(248,81,73,.2); }
    .badge.approved   { background: rgba(63,185,80,.12);  color: #3fb950; border: 1px solid rgba(63,185,80,.2); }
    .badge.pending    { background: rgba(227,179,65,.12); color: #e3b341; border: 1px solid rgba(227,179,65,.2); }
    .badge.rejected   { background: rgba(248,81,73,.12);  color: #f85149; border: 1px solid rgba(248,81,73,.2); }
    .badge.cat        { background: rgba(0,245,255,.08);  color: #00f5ff; border: 1px solid rgba(0,245,255,.15); font-size: 10px; }

    /* ── TOAST ── */
    .toast {
      position: fixed; bottom: 24px; right: 24px;
      background: rgba(7,20,40,0.95);
      border: 1px solid rgba(0,245,255,0.2);
      border-radius: 10px; padding: 14px 18px;
      font-size: 13px; font-weight: 500;
      box-shadow: 0 8px 32px rgba(0,0,0,0.6);
      z-index: 9999; opacity: 0; transform: translateY(10px);
      transition: all .3s; max-width: 320px;
      pointer-events: none; backdrop-filter: blur(20px);
    }
    .toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
    .toast.toast-success { border-color: rgba(63,185,80,.4); color: #3fb950; }
    .toast.toast-error   { border-color: rgba(248,81,73,.4); color: #f85149; }

    @media (max-width: 768px) {
      .sidebar { display: none; }
      .stats-grid { grid-template-columns: 1fr; }
      .form-grid { grid-template-columns: 1fr; }
      .main-content { padding: 16px; }
    }
  </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-left">
    <div class="topbar-logo">
      <img src="assets/images/logo.png" alt="Logo">
    </div>
    <div class="topbar-title">Clinic Inventory System</div>
  </div>
  <div class="topbar-right">
    <div class="topbar-user">
      <div class="user-dot"></div>
      <span><?= htmlspecialchars($user_name) ?></span>
      <span class="user-role">· Student</span>
    </div>
    <a href="user_profile.php" class="btn btn-secondary">
      <i class="fas fa-gear"></i> Profile
    </a>
    <a href="user_logout.php" class="btn btn-secondary">
      <i class="fas fa-right-from-bracket"></i> Sign Out
    </a>
  </div>
</div>

<!-- LAYOUT -->
<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="user_dashboard.php" class="nav-item active">
        <span class="nav-icon"><i class="fas fa-house"></i></span> Dashboard
      </a>
      <a href="user_medicines.php" class="nav-item">
        <span class="nav-icon"><i class="fas fa-pills"></i></span> View Medicines
      </a>
      <a href="user_requests.php" class="nav-item">
        <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span> My Requests
      </a>
      <a href="health_check.php" class="nav-item">
        <span class="nav-icon"><i class="fas fa-heart-pulse"></i></span> Health Check
      </a>
    </div>

    <!-- Profile at bottom -->
    <div class="sidebar-footer">
      <a href="user_profile.php" class="sidebar-profile">
        <div class="sidebar-avatar">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <div>
          <div class="sidebar-profile-name">
            <?= htmlspecialchars($user_name) ?>
          </div>
          <div class="sidebar-profile-role">
            <?= htmlspecialchars($user['grade'] ?: 'Student') ?>
          </div>
        </div>
      </a>
      <div class="sidebar-build">Student Portal v1.0</div>
      <div class="sidebar-build">© <?= date('Y') ?> ICAS School Clinic</div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main-content">

    <?php if ($flashSuccess): ?>
      <div class="alert alert-success">
        <i class="fas fa-circle-check"></i> <?= htmlspecialchars($flashSuccess) ?>
      </div>
    <?php endif; ?>
    <?php if ($reqError): ?>
      <div class="alert alert-error">
        <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($reqError) ?>
      </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
      <h1>Welcome, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>! 👋</h1>
      <p>Student ID: <span style="font-family:'DM Mono',monospace;color:#00f5ff"><?= htmlspecialchars($student_id) ?></span> — <?= date('F d, Y') ?></p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card blue">
        <div class="stat-label">Total Requests</div>
        <div class="stat-value"><?= $totalCount ?></div>
        <div class="stat-sub">all time</div>
        <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
      </div>
      <div class="stat-card yellow">
        <div class="stat-label">Pending</div>
        <div class="stat-value"><?= $pendingCount ?></div>
        <div class="stat-sub">awaiting approval</div>
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
      </div>
      <div class="stat-card green">
        <div class="stat-label">Approved</div>
        <div class="stat-value"><?= $approvedCount ?></div>
        <div class="stat-sub">requests approved</div>
        <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
      </div>
    </div>

    <!-- Request Medicine -->
    <div class="card">
      <div class="card-header">
        <div>
          <h3><i class="fas fa-pills" style="color:#00f5ff;margin-right:8px;"></i>Request a Medicine</h3>
          <p>Fill out the form below to request medicine from the clinic</p>
        </div>
      </div>
      <div class="card-body">
        <form method="POST" action="user_dashboard.php">
          <input type="hidden" name="form_action" value="request_medicine">
          <div class="form-grid">
            <div class="field">
              <label>Select Medicine *</label>
              <select name="medicine_id" required>
                <option value="">— Select Medicine —</option>
                <?php
                $medicines->data_seek(0);
                while ($med = $medicines->fetch_assoc()):
                ?>
                  <option value="<?= $med['medicine_id'] ?>">
                    <?= htmlspecialchars($med['medicine_name']) ?>
                    (<?= $med['quantity'] ?> <?= htmlspecialchars($med['unit'] ?: '') ?> available)
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="field">
              <label>Quantity *</label>
              <input type="number" name="quantity" min="1" value="1">
            </div>
            <div class="field span2">
              <label>Reason / Symptoms</label>
              <textarea name="reason" placeholder="Describe your symptoms or reason..."></textarea>
            </div>
          </div>
          <div style="margin-top:16px;">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane"></i> Submit Request
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Recent Requests -->
    <div class="card">
      <div class="card-header">
        <div>
          <h3>My Recent Requests</h3>
          <p>Your last 5 medicine requests</p>
        </div>
        <a href="user_requests.php" class="btn btn-secondary" style="font-size:12px;">View All →</a>
      </div>
      <table>
        <thead><tr>
          <th>Medicine</th><th>Qty</th><th>Reason</th><th>Date</th><th>Status</th>
        </tr></thead>
        <tbody>
        <?php if ($myRequests->num_rows > 0): while ($r = $myRequests->fetch_assoc()):
          $sc = match($r['status']) {
            'Approved' => 'approved',
            'Pending'  => 'pending',
            'Rejected' => 'rejected',
            default    => ''
          };
        ?>
          <tr>
            <td><div class="med-name"><?= htmlspecialchars($r['medicine_name']) ?></div></td>
            <td><?= $r['quantity'] ?> <?= htmlspecialchars($r['unit'] ?: '') ?></td>
            <td style="color:#7a8fa6;font-size:12px;max-width:200px;">
              <?= $r['reason'] ? htmlspecialchars($r['reason']) : '—' ?>
            </td>
            <td class="mono"><?= date('m/d/Y', strtotime($r['requested_at'])) ?></td>
            <td><span class="badge <?= $sc ?>"><?= htmlspecialchars($r['status']) ?></span></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="5" style="text-align:center;padding:40px;color:#3d5266;">
            <i class="fas fa-clipboard-list" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>
            No requests yet. Submit your first request above!
          </td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

<div id="toast" class="toast"></div>
<script>
let toastTimer;
function showToast(msg, type='info') {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className = `toast show toast-${type}`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.className = 'toast'; }, 3500);
}
</script>
</body>
</html>