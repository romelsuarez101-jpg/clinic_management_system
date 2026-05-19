<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

// Only allow logged in users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$student_id = $_SESSION['student_id'];

// Get available medicines (In Stock only)
$medicines = $conn->query("SELECT * FROM medicines WHERE status = 'In Stock' AND quantity > 0 ORDER BY medicine_name ASC");

// Get user's recent requests
$requests = $conn->prepare("
    SELECT mr.*, m.medicine_name, m.unit, m.image
    FROM medicine_requests mr
    JOIN medicines m ON mr.medicine_id = m.medicine_id
    WHERE mr.user_id = ?
    ORDER BY mr.requested_at DESC
    LIMIT 5
");
$requests->bind_param("i", $user_id);
$requests->execute();
$myRequests = $requests->get_result();

// Count stats
$totalReq   = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id = ?");
$totalReq->bind_param("i", $user_id);
$totalReq->execute();
$totalCount = $totalReq->get_result()->fetch_assoc()['c'];

$pendingReq = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id = ? AND status = 'Pending'");
$pendingReq->bind_param("i", $user_id);
$pendingReq->execute();
$pendingCount = $pendingReq->get_result()->fetch_assoc()['c'];

$approvedReq = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id = ? AND status = 'Approved'");
$approvedReq->bind_param("i", $user_id);
$approvedReq->execute();
$approvedCount = $approvedReq->get_result()->fetch_assoc()['c'];

// Handle medicine request submission
$reqError   = '';
$reqSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'request_medicine') {
    $medicine_id = (int)($_POST['medicine_id'] ?? 0);
    $quantity    = (int)($_POST['quantity']    ?? 1);
    $reason      = trim($_POST['reason']       ?? '');

    if (!$medicine_id) {
        $reqError = 'Please select a medicine.';
    } elseif ($quantity < 1) {
        $reqError = 'Quantity must be at least 1.';
    } else {
        // Check if medicine is available
        $chk = $conn->prepare("SELECT quantity FROM medicines WHERE medicine_id = ? AND status = 'In Stock'");
        $chk->bind_param("i", $medicine_id);
        $chk->execute();
        $med = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$med) {
            $reqError = 'Medicine is not available.';
        } elseif ($quantity > $med['quantity']) {
            $reqError = 'Requested quantity exceeds available stock.';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO medicine_requests (user_id, medicine_id, quantity, reason)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiis", $user_id, $medicine_id, $quantity, $reason);
            if ($stmt->execute()) {
                $reqSuccess = 'Medicine request submitted successfully!';
                // Refresh page to update counts
                header('Location: user_dashboard.php?success=1');
                exit();
            } else {
                $reqError = 'Failed to submit request.';
            }
            $stmt->close();
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
  <title>Student Dashboard — Clinic Inventory System</title>
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

<!-- LAYOUT -->
<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="user_dashboard.php" class="nav-item active">
        <span class="nav-icon">🏠</span> Dashboard
      </a>
      <a href="user_medicines.php" class="nav-item">
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

  <!-- MAIN CONTENT -->
  <main class="main-content">

    <?php if ($flashSuccess): ?>
      <div class="alert alert-success" data-auto-dismiss>✅ <?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($reqError): ?>
      <div class="alert alert-error" data-auto-dismiss>⚠ <?= htmlspecialchars($reqError) ?></div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1>Welcome, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>! 👋</h1>
        <p>Student ID: <span style="font-family:'DM Mono',monospace;color:var(--accent)"><?= htmlspecialchars($student_id) ?></span> — <?= date('F d, Y') ?></p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:28px;">
      <div class="stat-card blue">
        <div class="stat-label">Total Requests</div>
        <div class="stat-value"><?= $totalCount ?></div>
        <div class="stat-sub">all time</div>
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
        <div class="stat-sub">requests approved</div>
        <div class="stat-icon">✅</div>
      </div>
    </div>

    <!-- Request Medicine Form -->
    <div class="table-card" style="padding:24px;margin-bottom:24px;">
      <div style="margin-bottom:20px;">
        <div style="font-family:'Playfair Display',serif;font-size:18px;margin-bottom:4px;">💊 Request a Medicine</div>
        <div style="font-size:13px;color:var(--text2)">Fill out the form below to request medicine from the clinic</div>
      </div>

      <form method="POST" action="user_dashboard.php">
        <input type="hidden" name="form_action" value="request_medicine">
        <div class="form-grid">

          <div class="field">
            <label>Select Medicine *</label>
            <select name="medicine_id" required style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--text);outline:none;">
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
            <input type="number" name="quantity" min="1" value="1"
                   style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--text);outline:none;">
          </div>

          <div class="field span2">
            <label>Reason / Symptoms</label>
            <textarea name="reason"
                      placeholder="Describe your symptoms or reason for requesting this medicine..."
                      style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--text);outline:none;min-height:80px;resize:vertical;"></textarea>
          </div>

        </div>
        <div style="margin-top:16px;">
          <button type="submit" class="btn btn-primary">📨 Submit Request</button>
        </div>
      </form>
    </div>

    <!-- Recent Requests -->
    <div class="table-card">
      <div class="table-card-header">
        <div>
          <h3>My Recent Requests</h3>
          <p>Your last 5 medicine requests</p>
        </div>
        <a href="user_requests.php" class="btn btn-secondary btn-sm">View All →</a>
      </div>
      <table>
        <thead><tr>
          <th>Medicine</th>
          <th>Qty</th>
          <th>Reason</th>
          <th>Date</th>
          <th>Status</th>
        </tr></thead>
        <tbody>
        <?php if ($myRequests->num_rows > 0): while ($r = $myRequests->fetch_assoc()):
          $statusColor = match($r['status']) {
            'Approved' => 'in-stock',
            'Pending'  => 'low-stock',
            'Rejected' => 'out-stock',
            default    => ''
          };
        ?>
          <tr>
            <td><div class="med-name"><?= htmlspecialchars($r['medicine_name']) ?></div></td>
            <td><?= $r['quantity'] ?> <?= htmlspecialchars($r['unit'] ?: '') ?></td>
            <td style="color:var(--text2);font-size:12px;max-width:200px;">
              <?= $r['reason'] ? htmlspecialchars($r['reason']) : '—' ?>
            </td>
            <td class="mono"><?= date('m/d/Y', strtotime($r['requested_at'])) ?></td>
            <td><span class="badge <?= $statusColor ?>"><?= htmlspecialchars($r['status']) ?></span></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="5">
            <div class="empty-state">
              <span class="empty-icon">📋</span>
              <p>No requests yet. Submit your first request above!</p>
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