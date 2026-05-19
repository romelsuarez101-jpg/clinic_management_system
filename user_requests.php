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

$user_id    = $_SESSION['user_id'];
$user_name  = $_SESSION['user_name'];
$student_id = $_SESSION['student_id'];

// Get all requests for this user
$stmt = $conn->prepare("
    SELECT mr.*, m.medicine_name, m.unit, m.category, m.image
    FROM medicine_requests mr
    JOIN medicines m ON mr.medicine_id = m.medicine_id
    WHERE mr.user_id = ?
    ORDER BY mr.requested_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requests = $stmt->get_result();

// Count stats
$total    = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id = ?");
$total->bind_param("i", $user_id); $total->execute();
$totalCount = $total->get_result()->fetch_assoc()['c'];

$pending  = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id = ? AND status='Pending'");
$pending->bind_param("i", $user_id); $pending->execute();
$pendingCount = $pending->get_result()->fetch_assoc()['c'];

$approved = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id = ? AND status='Approved'");
$approved->bind_param("i", $user_id); $approved->execute();
$approvedCount = $approved->get_result()->fetch_assoc()['c'];

$rejected = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id = ? AND status='Rejected'");
$rejected->bind_param("i", $user_id); $rejected->execute();
$rejectedCount = $rejected->get_result()->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Requests — Clinic</title>
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
      <a href="user_medicines.php" class="nav-item">
        <span class="nav-icon">💊</span> View Medicines
      </a>
      <a href="user_requests.php" class="nav-item active">
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
        <h1>My Requests</h1>
        <p>Track all your medicine requests</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="margin-bottom:28px;">
      <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value"><?= $totalCount ?></div>
        <div class="stat-sub">all requests</div>
        <div class="stat-icon">📋</div>
      </div>
      <div class="stat-card yellow">
        <div class="stat-label">Pending</div>
        <div class="stat-value"><?= $pendingCount ?></div>
        <div class="stat-sub">awaiting approval</div>
        <div class="stat-icon">⏳</div>
      </div>
      <div class="stat-card green">
        <div class="stat-label">Approved</div>
        <div class="stat-value"><?= $approvedCount ?></div>
        <div class="stat-sub">approved</div>
        <div class="stat-icon">✅</div>
      </div>
      <div class="stat-card red">
        <div class="stat-label">Rejected</div>
        <div class="stat-value"><?= $rejectedCount ?></div>
        <div class="stat-sub">rejected</div>
        <div class="stat-icon">❌</div>
      </div>
    </div>

    <!-- Requests Table -->
    <div class="table-card">
      <div class="table-card-header">
        <div>
          <h3>All Medicine Requests</h3>
          <p>Complete history of your requests</p>
        </div>
        <a href="user_medicines.php" class="btn btn-primary btn-sm">➕ New Request</a>
      </div>
      <table>
        <thead><tr>
          <th>Photo</th>
          <th>Medicine</th>
          <th>Category</th>
          <th>Qty</th>
          <th>Reason</th>
          <th>Date Requested</th>
          <th>Status</th>
          <th>Admin Notes</th>
        </tr></thead>
        <tbody>
        <?php if ($requests->num_rows > 0): while ($r = $requests->fetch_assoc()):
          $sc = match($r['status']) {
            'Approved' => 'in-stock',
            'Pending'  => 'low-stock',
            'Rejected' => 'out-stock',
            default    => ''
          };
        ?>
          <tr>
            <td>
              <?php if (!empty($r['image'])): ?>
                <img src="assets/uploads/medicines/<?= htmlspecialchars($r['image']) ?>"
                     style="width:38px;height:38px;object-fit:cover;border-radius:8px;border:1px solid var(--border);"
                     onerror="this.style.display='none'">
              <?php else: ?>
                <div style="width:38px;height:38px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;">💊</div>
              <?php endif; ?>
            </td>
            <td><div class="med-name"><?= htmlspecialchars($r['medicine_name']) ?></div></td>
            <td><span class="badge cat"><?= htmlspecialchars($r['category'] ?: '—') ?></span></td>
            <td><span class="qty-num"><?= $r['quantity'] ?></span> <?= htmlspecialchars($r['unit'] ?: '') ?></td>
            <td style="color:var(--text2);font-size:12px;max-width:150px;">
              <?= $r['reason'] ? htmlspecialchars($r['reason']) : '—' ?>
            </td>
            <td class="mono"><?= date('m/d/Y h:i A', strtotime($r['requested_at'])) ?></td>
            <td><span class="badge <?= $sc ?>"><?= htmlspecialchars($r['status']) ?></span></td>
            <td style="color:var(--text2);font-size:12px;max-width:150px;">
              <?= $r['admin_notes'] ? htmlspecialchars($r['admin_notes']) : '—' ?>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="8">
            <div class="empty-state">
              <span class="empty-icon">📋</span>
              <p>No requests yet. <a href="user_medicines.php" style="color:var(--accent)">Request a medicine →</a></p>
            </div>
          </td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

<div id="toast" class="toast"></div>
<script src="assets/js/main.js"></script>
</body>
</html>