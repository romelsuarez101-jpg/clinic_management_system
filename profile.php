<?php
$pageTitle  = 'Profile';
$activePage = 'profile';
require_once __DIR__ . '/config/session.php';
requireLogin();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPw = $_POST['current_password'] ?? '';
    $newPw     = $_POST['new_password']     ?? '';
    $confirmPw = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $row  = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $dbPw  = $row['password'] ?? '';
    $valid = ($dbPw === $currentPw) || password_verify($currentPw, $dbPw);

    if (!$valid)               $errors[] = 'Current password is incorrect.';
    if (strlen($newPw) < 6)   $errors[] = 'New password must be at least 6 characters.';
    if ($newPw !== $confirmPw) $errors[] = 'New passwords do not match.';
    if ($newPw === $currentPw) $errors[] = 'New password must differ from the current one.';

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $newPw, $_SESSION['admin_id']);
        $success = $stmt->execute();
        $stmt->close();
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div><h1>Profile</h1><p>Manage your account settings</p></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:860px">
  <div class="form-card">
    <h3 style="font-family:'Playfair Display',serif;font-size:18px;margin-bottom:20px">Account Info</h3>
    <div style="display:flex;align-items:center;gap:18px;padding:20px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;margin-bottom:20px">
      <div style="width:56px;height:56px;background:linear-gradient(135deg,#3fb950,#58a6ff);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0">👤</div>
      <div>
        <div style="font-size:18px;font-weight:600"><?= htmlspecialchars(getCurrentUser()) ?></div>
        <div style="font-size:13px;color:var(--text2);margin-top:2px">Nurse Administrator</div>
        <div style="font-size:11px;color:var(--text3);margin-top:4px"><span style="width:6px;height:6px;background:var(--green);border-radius:50%;display:inline-block;margin-right:4px"></span>Active session</div>
      </div>
    </div>
    <div class="detail-grid">
      <div class="detail-item"><div class="detail-label">Role</div><div class="detail-value">Nurse Admin</div></div>
      <div class="detail-item"><div class="detail-label">Access Level</div><div class="detail-value">Full Access</div></div>
      <div class="detail-item full"><div class="detail-label">System</div><div class="detail-value">School Clinic Inventory</div></div>
    </div>
  </div>
  <div class="form-card">
    <h3 style="font-family:'Playfair Display',serif;font-size:18px;margin-bottom:20px">Change Password</h3>
    <?php if ($success): ?>
      <div class="alert alert-success" data-auto-dismiss>✅ Password changed successfully!</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error" data-auto-dismiss><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>
    <form method="POST" action="profile.php">
      <div class="field" style="margin-bottom:16px">
        <label>Current Password</label>
        <input type="password" name="current_password" placeholder="Enter current password" required>
      </div>
      <div class="field" style="margin-bottom:16px">
        <label>New Password</label>
        <input type="password" name="new_password" placeholder="Min. 6 characters" required id="new-pw" oninput="checkStrength(this.value)">
        <div id="pw-strength" style="margin-top:6px;height:4px;border-radius:2px;background:var(--border);overflow:hidden"><div id="pw-bar" style="height:100%;width:0;transition:width .3s,background .3s;border-radius:2px"></div></div>
        <div id="pw-label" class="hint" style="margin-top:4px"></div>
      </div>
      <div class="field" style="margin-bottom:24px">
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" placeholder="Repeat new password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">🔐 Update Password</button>
    </form>
  </div>
</div>
<script>
function checkStrength(pw) {
  let score = 0;
  if (pw.length >= 6)  score++;
  if (pw.length >= 10) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;
  const pct    = (score / 5) * 100;
  const colors = ['var(--red)','var(--red)','var(--yellow)','var(--yellow)','var(--green)'];
  const labels = ['','Very Weak','Weak','Fair','Strong','Very Strong'];
  document.getElementById('pw-bar').style.width      = pct + '%';
  document.getElementById('pw-bar').style.background = colors[score-1] || 'var(--border)';
  document.getElementById('pw-label').textContent    = pw.length ? labels[score] : '';
  document.getElementById('pw-label').style.color    = colors[score-1] || 'var(--text3)';
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
