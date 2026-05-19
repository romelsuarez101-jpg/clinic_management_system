<?php
$pageTitle  = 'Students';
$activePage = 'students';
require_once __DIR__ . '/config/session.php';
requireLogin();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

header('Cache-Control: no store, no cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Handle activate/deactivate/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $action  = $_POST['action'] ?? '';

    if ($user_id) {
        if ($action === 'activate') {
            $stmt = $conn->prepare("UPDATE users SET status='Active' WHERE user_id=?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            setFlash('success', 'Student account activated.');
        } elseif ($action === 'deactivate') {
            $stmt = $conn->prepare("UPDATE users SET status='Inactive' WHERE user_id=?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            setFlash('success', 'Student account deactivated.');
        } elseif ($action === 'delete') {
            // Delete requests first
            $stmt = $conn->prepare("DELETE FROM medicine_requests WHERE user_id=?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            // Delete user
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            setFlash('success', 'Student account deleted.');
        }
        redirect('admin_students.php');
    }
}

// Search
$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE full_name LIKE ? OR student_id LIKE ? ORDER BY created_at DESC");
    $like = "%{$search}%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $students = $stmt->get_result();
} else {
    $students = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
}

$totalStudents  = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$activeStudents = $conn->query("SELECT COUNT(*) AS c FROM users WHERE status='Active'")->fetch_assoc()['c'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>Students & Faculty</h1>
    <p>Manage registered student and faculty accounts</p>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(2,1fr);max-width:500px;margin-bottom:24px;">
  <div class="stat-card green">
    <div class="stat-label">Total Users</div>
    <div class="stat-value"><?= $totalStudents ?></div>
    <div class="stat-sub">registered</div>
    <div class="stat-icon">👥</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-label">Active</div>
    <div class="stat-value"><?= $activeStudents ?></div>
    <div class="stat-sub">active accounts</div>
    <div class="stat-icon">✅</div>
  </div>
</div>

<!-- Table -->
<div class="table-card">
  <div class="table-toolbar">
    <form method="GET" style="display:flex;gap:8px;align-items:center;flex:1;">
      <div class="search-box" style="max-width:300px;">
        <span class="search-icon">🔍</span>
        <input type="text" name="search"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Search by name or ID…"
               onchange="this.form.submit()">
      </div>
      <?php if ($search): ?>
        <a href="admin_students.php" class="btn btn-secondary btn-sm">✕ Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <table>
    <thead><tr>
      <th>ID</th>
      <th>Full Name</th>
      <th>Student ID</th>
      <th>Grade</th>
      <th>Section</th>
      <th>Email</th>
      <th>Requests</th>
      <th>Joined</th>
      <th>Status</th>
      <th>Actions</th>
    </tr></thead>
    <tbody>
    <?php if ($students->num_rows > 0): while ($s = $students->fetch_assoc()):
      // Count requests per student
      $reqStmt = $conn->prepare("SELECT COUNT(*) AS c FROM medicine_requests WHERE user_id=?");
      $reqStmt->bind_param("i", $s['user_id']);
      $reqStmt->execute();
      $reqCount = $reqStmt->get_result()->fetch_assoc()['c'];
      $reqStmt->close();
    ?>
      <tr>
        <td><span class="med-id">#<?= str_pad($s['user_id'],3,'0',STR_PAD_LEFT) ?></span></td>
        <td><div class="med-name"><?= htmlspecialchars($s['full_name']) ?></div></td>
        <td style="font-family:'DM Mono',monospace;font-size:12px;color:var(--accent)">
          <?= htmlspecialchars($s['student_id']) ?>
        </td>
        <td><?= htmlspecialchars($s['grade'] ?: '—') ?></td>
        <td><?= htmlspecialchars($s['section'] ?: '—') ?></td>
        <td style="font-size:12px;color:var(--text2)">
          <?= $s['email'] ? htmlspecialchars($s['email']) : '—' ?>
        </td>
        <td>
          <span style="font-family:'DM Mono',monospace;font-weight:600;color:var(--blue)">
            <?= $reqCount ?>
          </span>
        </td>
        <td class="mono"><?= date('m/d/Y', strtotime($s['created_at'])) ?></td>
        <td>
          <?php if ($s['status'] === 'Active'): ?>
            <span class="badge in-stock">Active</span>
          <?php else: ?>
            <span class="badge out-stock">Inactive</span>
          <?php endif; ?>
        </td>
        <td>
          <div class="actions-cell">
            <!-- View Requests -->
            <a href="admin_requests.php?student=<?= $s['user_id'] ?>"
               class="btn btn-view btn-sm" title="View Requests">📋</a>

            <!-- Activate / Deactivate -->
            <form method="POST" style="display:inline;">
              <input type="hidden" name="user_id" value="<?= $s['user_id'] ?>">
              <?php if ($s['status'] === 'Active'): ?>
                <button type="submit" name="action" value="deactivate"
                        class="btn btn-secondary btn-sm" title="Deactivate">🔒</button>
              <?php else: ?>
                <button type="submit" name="action" value="activate"
                        class="btn btn-edit btn-sm" title="Activate">🔓</button>
              <?php endif; ?>
            </form>

            <!-- Delete -->
            <form method="POST" style="display:inline;"
                  onsubmit="return confirm('Delete <?= htmlspecialchars($s['full_name'], ENT_QUOTES) ?>?\n\nThis will also delete all their requests.')">
              <input type="hidden" name="user_id" value="<?= $s['user_id'] ?>">
              <button type="submit" name="action" value="delete"
                      class="btn btn-danger btn-sm" title="Delete">🗑</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endwhile; else: ?>
      <tr><td colspan="10">
        <div class="empty-state">
          <span class="empty-icon">👥</span>
          <p>No students registered yet</p>
        </div>
      </td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>