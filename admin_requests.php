<?php
$pageTitle  = 'Medicine Requests';
$activePage = 'requests';
require_once __DIR__ . '/config/session.php';
requireLogin();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

header('Cache-Control: no store, no cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id  = (int)($_POST['request_id']  ?? 0);
    $action      = $_POST['action']             ?? '';
    $admin_notes = trim($_POST['admin_notes']   ?? '');

    if ($request_id && in_array($action, ['Approved', 'Rejected'])) {
        // If approving, reduce medicine quantity
        if ($action === 'Approved') {
            // Get request details
            $r = $conn->prepare("SELECT medicine_id, quantity FROM medicine_requests WHERE request_id = ?");
            $r->bind_param("i", $request_id);
            $r->execute();
            $req = $r->get_result()->fetch_assoc();
            $r->close();

            if ($req) {
                // Reduce stock
                $upd = $conn->prepare("UPDATE medicines SET quantity = quantity - ? WHERE medicine_id = ? AND quantity >= ?");
                $upd->bind_param("iii", $req['quantity'], $req['medicine_id'], $req['quantity']);
                $upd->execute();
                $upd->close();

                // Update medicine status if stock is low
                $conn->query("UPDATE medicines SET status = 'Out of Stock' WHERE medicine_id = {$req['medicine_id']} AND quantity = 0");
                $conn->query("UPDATE medicines SET status = 'Low Stock' WHERE medicine_id = {$req['medicine_id']} AND quantity > 0 AND quantity <= 10");
            }
        }

        // Update request status
        $stmt = $conn->prepare("UPDATE medicine_requests SET status=?, admin_notes=?, acted_at=NOW() WHERE request_id=?");
        $stmt->bind_param("ssi", $action, $admin_notes, $request_id);
        $stmt->execute();
        $stmt->close();

        setFlash('success', "Request #{$request_id} has been {$action}.");
        redirect('admin_requests.php');
    }
}

// Filter
$filter    = $_GET['filter']  ?? 'all';
$studentId = (int)($_GET['student'] ?? 0);
$where     = '';
$conditions = [];

if ($filter === 'pending')  $conditions[] = "mr.status = 'Pending'";
if ($filter === 'approved') $conditions[] = "mr.status = 'Approved'";
if ($filter === 'rejected') $conditions[] = "mr.status = 'Rejected'";
if ($studentId)             $conditions[] = "mr.user_id = {$studentId}";

if ($conditions) $where = "WHERE " . implode(" AND ", $conditions);

$requests = $conn->query("
    SELECT mr.*, 
           u.full_name, u.student_id, u.grade, u.section,
           m.medicine_name, m.unit, m.image, m.quantity AS stock
    FROM medicine_requests mr
    JOIN users u ON mr.user_id = u.user_id
    JOIN medicines m ON mr.medicine_id = m.medicine_id
    {$where}
    ORDER BY 
        CASE mr.status WHEN 'Pending' THEN 1 WHEN 'Approved' THEN 2 ELSE 3 END,
        mr.requested_at DESC
");

// Stats
$totalReq    = $conn->query("SELECT COUNT(*) AS c FROM medicine_requests")->fetch_assoc()['c'];
$pendingReq  = $conn->query("SELECT COUNT(*) AS c FROM medicine_requests WHERE status='Pending'")->fetch_assoc()['c'];
$approvedReq = $conn->query("SELECT COUNT(*) AS c FROM medicine_requests WHERE status='Approved'")->fetch_assoc()['c'];
$rejectedReq = $conn->query("SELECT COUNT(*) AS c FROM medicine_requests WHERE status='Rejected'")->fetch_assoc()['c'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>Medicine Requests</h1>
    <p>Manage and approve student medicine requests</p>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:24px;">
  <div class="stat-card blue">
    <div class="stat-label">Total Requests</div>
    <div class="stat-value"><?= $totalReq ?></div>
    <div class="stat-sub">all requests</div>
    <div class="stat-icon">📋</div>
  </div>
  <div class="stat-card yellow">
    <div class="stat-label">Pending</div>
    <div class="stat-value"><?= $pendingReq ?></div>
    <div class="stat-sub">awaiting action</div>
    <div class="stat-icon">⏳</div>
  </div>
  <div class="stat-card green">
    <div class="stat-label">Approved</div>
    <div class="stat-value"><?= $approvedReq ?></div>
    <div class="stat-sub">approved</div>
    <div class="stat-icon">✅</div>
  </div>
  <div class="stat-card red">
    <div class="stat-label">Rejected</div>
    <div class="stat-value"><?= $rejectedReq ?></div>
    <div class="stat-sub">rejected</div>
    <div class="stat-icon">❌</div>
  </div>
</div>

<!-- Filter Tabs -->
<div style="display:flex;gap:8px;margin-bottom:20px;">
  <a href="admin_requests.php?filter=all"
     class="btn <?= $filter==='all'?'btn-primary':'btn-secondary' ?> btn-sm">
    All (<?= $totalReq ?>)
  </a>
  <a href="admin_requests.php?filter=pending"
     class="btn <?= $filter==='pending'?'btn-primary':'btn-secondary' ?> btn-sm">
    ⏳ Pending (<?= $pendingReq ?>)
  </a>
  <a href="admin_requests.php?filter=approved"
     class="btn <?= $filter==='approved'?'btn-primary':'btn-secondary' ?> btn-sm">
    ✅ Approved (<?= $approvedReq ?>)
  </a>
  <a href="admin_requests.php?filter=rejected"
     class="btn <?= $filter==='rejected'?'btn-primary':'btn-secondary' ?> btn-sm">
    ❌ Rejected (<?= $rejectedReq ?>)
  </a>
</div>

<!-- Requests Table -->
<div class="table-card">
  <table>
    <thead><tr>
      <th>ID</th>
      <th>Student</th>
      <th>Medicine</th>
      <th>Qty</th>
      <th>Reason</th>
      <th>Date</th>
      <th>Status</th>
      <th>Actions</th>
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
        <td><span class="med-id">#<?= str_pad($r['request_id'],3,'0',STR_PAD_LEFT) ?></span></td>
        <td>
          <div class="med-name"><?= htmlspecialchars($r['full_name']) ?></div>
          <div style="font-size:11px;color:var(--text3);font-family:'DM Mono',monospace"><?= htmlspecialchars($r['student_id']) ?></div>
          <div style="font-size:11px;color:var(--text3)"><?= htmlspecialchars($r['grade'] ?: '') ?> <?= htmlspecialchars($r['section'] ?: '') ?></div>
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:8px;">
            <?php if (!empty($r['image'])): ?>
              <img src="assets/uploads/medicines/<?= htmlspecialchars($r['image']) ?>"
                   style="width:32px;height:32px;object-fit:cover;border-radius:6px;border:1px solid var(--border);"
                   onerror="this.style.display='none'">
            <?php else: ?>
              <div style="width:32px;height:32px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;">💊</div>
            <?php endif; ?>
            <div>
              <div class="med-name" style="font-size:13px;"><?= htmlspecialchars($r['medicine_name']) ?></div>
              <div style="font-size:11px;color:var(--text3)">Stock: <?= $r['stock'] ?></div>
            </div>
          </div>
        </td>
        <td><span class="qty-num"><?= $r['quantity'] ?></span> <?= htmlspecialchars($r['unit'] ?: '') ?></td>
        <td style="color:var(--text2);font-size:12px;max-width:140px;">
          <?= $r['reason'] ? htmlspecialchars($r['reason']) : '—' ?>
        </td>
        <td class="mono" style="font-size:11px;"><?= date('m/d/Y h:i A', strtotime($r['requested_at'])) ?></td>
        <td>
          <span class="badge <?= $sc ?>"><?= htmlspecialchars($r['status']) ?></span>
          <?php if ($r['admin_notes']): ?>
            <div style="font-size:11px;color:var(--text3);margin-top:4px;"><?= htmlspecialchars($r['admin_notes']) ?></div>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($r['status'] === 'Pending'): ?>
            <button onclick="openActionModal(<?= $r['request_id'] ?>, '<?= htmlspecialchars($r['full_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['medicine_name'], ENT_QUOTES) ?>', <?= $r['quantity'] ?>)"
                    class="btn btn-edit btn-sm">
              ⚡ Act
            </button>
          <?php else: ?>
            <span style="font-size:11px;color:var(--text3)">
              <?= $r['acted_at'] ? date('m/d/Y', strtotime($r['acted_at'])) : '—' ?>
            </span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; else: ?>
      <tr><td colspan="8">
        <div class="empty-state">
          <span class="empty-icon">📋</span>
          <p>No <?= $filter !== 'all' ? $filter : '' ?> requests found</p>
        </div>
      </td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ACTION MODAL -->
<div id="actionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#161b22;border:1px solid #30363d;border-radius:14px;width:100%;max-width:480px;padding:32px;box-shadow:0 8px 32px rgba(0,0,0,0.5);margin:20px;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
      <h2 style="font-family:'Playfair Display',serif;font-size:20px;">Review Request</h2>
      <button onclick="closeActionModal()"
              style="background:#1c2333;border:1px solid #30363d;border-radius:6px;width:32px;height:32px;color:#8b949e;font-size:18px;cursor:pointer;">✕</button>
    </div>

    <!-- Request Summary -->
    <div style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:14px;margin-bottom:20px;">
      <div style="font-size:12px;color:var(--text3);margin-bottom:8px;">REQUEST DETAILS</div>
      <div style="font-size:14px;font-weight:600;" id="modal_student"></div>
      <div style="font-size:13px;color:var(--text2);margin-top:4px;" id="modal_medicine"></div>
    </div>

    <form method="POST" action="admin_requests.php">
      <input type="hidden" name="request_id" id="modal_request_id">

      <div class="field" style="margin-bottom:16px;">
        <label>Admin Notes <span style="color:var(--text3)">(optional)</span></label>
        <textarea name="admin_notes"
                  placeholder="Add notes for the student (reason for rejection, instructions, etc.)..."
                  style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:13px;color:var(--text);outline:none;min-height:80px;resize:vertical;"></textarea>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid #30363d;">
        <button type="button" onclick="closeActionModal()" class="btn btn-secondary">Cancel</button>
        <button type="submit" name="action" value="Rejected"
                class="btn btn-danger">❌ Reject</button>
        <button type="submit" name="action" value="Approved"
                class="btn btn-primary">✅ Approve</button>
      </div>
    </form>

  </div>
</div>

<script>
function openActionModal(id, student, medicine, qty) {
  document.getElementById('modal_request_id').value = id;
  document.getElementById('modal_student').textContent  = '👤 ' + student;
  document.getElementById('modal_medicine').textContent = '💊 ' + medicine + ' × ' + qty;
  document.getElementById('actionModal').style.display  = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeActionModal() {
  document.getElementById('actionModal').style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('actionModal').addEventListener('click', function(e) {
  if (e.target === this) closeActionModal();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>