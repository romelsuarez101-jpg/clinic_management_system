<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
requireLogin();

$expCount = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['c'];
$lowCount = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE quantity <= 10 OR status IN ('Low Stock','Out of Stock')")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Clinic' ?> — School Clinic</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-left">
    <div class="topbar-logo">
      <img src="assets/images/logo.png" alt="Logo" style="width:52px;height:52px;object-fit:cover;border-radius:50%;border:2px solid #00f5ff;">
    </div>
    <div class="topbar-title">Clinic Inventory System</div>
  </div>
  <div class="topbar-right">
    <div class="topbar-user">
      <div class="user-dot"></div>
      <span><?= htmlspecialchars(getCurrentUser()) ?></span>
      <span class="user-role">· Nurse Admin</span>
    </div>
    <a href="profile.php" class="btn btn-secondary btn-sm">
      <i class="fas fa-gear"></i> Profile
    </a>
    <a href="logout.php" class="btn btn-secondary btn-sm">
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
      <a href="dashboard.php" class="nav-item <?= ($activePage==='dashboard')?'active':'' ?>">
        <span class="nav-icon"><i class="fas fa-chart-line"></i></span> Dashboard
      </a>
      <a href="medicines.php" class="nav-item <?= ($activePage==='medicines')?'active':'' ?>">
        <span class="nav-icon"><i class="fas fa-pills"></i></span> Medicines
      </a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label">Alerts</div>
      <a href="expiring.php" class="nav-item <?= ($activePage==='expiring')?'active':'' ?>">
        <span class="nav-icon"><i class="fas fa-clock"></i></span> Expiring Soon
        <?php if ($expCount > 0): ?>
          <span class="badge-pill"><?= $expCount ?></span>
        <?php endif; ?>
      </a>
      <a href="lowstock.php" class="nav-item <?= ($activePage==='lowstock')?'active':'' ?>">
        <span class="nav-icon"><i class="fas fa-triangle-exclamation"></i></span> Low Stock
        <?php if ($lowCount > 0): ?>
          <span class="badge-pill badge-pill-yellow"><?= $lowCount ?></span>
        <?php endif; ?>
      </a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label">Students</div>
      <a href="admin_requests.php" class="nav-item <?= ($activePage==='requests')?'active':'' ?>">
        <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span> Medicine Requests
        <?php
        $pendingCount = $conn->query("SELECT COUNT(*) AS c FROM medicine_requests WHERE status='Pending'")->fetch_assoc()['c'];
        if ($pendingCount > 0):
        ?>
          <span class="badge-pill"><?= $pendingCount ?></span>
        <?php endif; ?>
      </a>
      <a href="admin_students.php" class="nav-item <?= ($activePage==='students')?'active':'' ?>">
        <span class="nav-icon"><i class="fas fa-users"></i></span> Students
      </a>
      <a href="admin_health.php" class="nav-item <?= ($activePage==='health')?'active':'' ?>">
        <span class="nav-icon"><i class="fas fa-heart-pulse"></i></span> Health Records
      </a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label">Reports</div>
      <a href="print_inventory.php" class="nav-item <?= ($activePage==='print')?'active':'' ?>" target="_blank">
        <span class="nav-icon"><i class="fas fa-print"></i></span> Print Inventory
      </a>
    </div>

   <div class="sidebar-footer">
  <a href="profile.php" class="sidebar-profile">
    <div class="sidebar-avatar">
      <i class="fas fa-user-nurse"></i>
    </div>
    <div>
      <div class="sidebar-profile-name">
        <?= htmlspecialchars(getCurrentUser()) ?>
      </div>
      <div class="sidebar-profile-role">Nurse Admin</div>
    </div>
  </a>
  <div class="sidebar-build">Clinic Inventory System v1.0</div>
  <div class="sidebar-build">© <?= date('Y') ?> ICAS School Clinic</div>
</div>

  </aside>
  <!-- END SIDEBAR -->

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" data-auto-dismiss>
        <?= htmlspecialchars($flash['msg']) ?>
      </div>
    <?php endif; ?>