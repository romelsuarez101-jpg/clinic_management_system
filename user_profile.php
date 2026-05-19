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

// Fetch full user info
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Update Profile ──
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $grade     = trim($_POST['grade']     ?? '');
        $section   = trim($_POST['section']   ?? '');
        $email     = trim($_POST['email']     ?? '');

        if (empty($full_name)) {
            $errors[] = 'Full name is required.';
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, grade=?, section=?, email=? WHERE user_id=?");
            $stmt->bind_param("ssssi", $full_name, $grade, $section, $email, $user_id);
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $full_name;
                $user_name = $full_name;
                $success = 'Profile updated successfully!';
                // Refresh user data
                $stmt2 = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt2->bind_param("i", $user_id);
                $stmt2->execute();
                $user = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();
            } else {
                $errors[] = 'Failed to update profile.';
            }
            $stmt->close();
        }
    }

    // ── Change Password ──
    if ($action === 'change_password') {
        $currentPw = $_POST['current_password'] ?? '';
        $newPw     = $_POST['new_password']     ?? '';
        $confirmPw = $_POST['confirm_password'] ?? '';

        $valid = ($user['password'] === $currentPw) || password_verify($currentPw, $user['password']);

        if (!$valid)               $errors[] = 'Current password is incorrect.';
        if (strlen($newPw) < 6)   $errors[] = 'New password must be at least 6 characters.';
        if ($newPw !== $confirmPw) $errors[] = 'Passwords do not match.';
        if ($newPw === $currentPw) $errors[] = 'New password must differ from current.';

        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $stmt->bind_param("si", $newPw, $user_id);
            if ($stmt->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $errors[] = 'Failed to change password.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile — Clinic</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
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
        <h1>My Profile</h1>
        <p>Manage your account information</p>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success" data-auto-dismiss>✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error" data-auto-dismiss>
        ⚠ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
      </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:900px;">

      <!-- Profile Info Card -->
      <div class="form-card">

        <!-- Avatar -->
        <div style="display:flex;align-items:center;gap:16px;padding:20px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;margin-bottom:24px;">
          <div style="width:60px;height:60px;background:linear-gradient(135deg,#3fb950,#58a6ff);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0;">
            👤
          </div>
          <div>
            <div style="font-size:18px;font-weight:600;"><?= htmlspecialchars($user['full_name']) ?></div>
            <div style="font-size:13px;color:var(--text2);margin-top:2px;">
              <?= htmlspecialchars($user['grade'] ?: '') ?>
              <?= $user['section'] ? ' — ' . htmlspecialchars($user['section']) : '' ?>
            </div>
            <div style="font-size:11px;color:var(--accent);margin-top:4px;font-family:'DM Mono',monospace;">
              <?= htmlspecialchars($user['student_id']) ?>
            </div>
          </div>
        </div>

        <h3 style="font-family:'Playfair Display',serif;font-size:17px;margin-bottom:18px;">Edit Profile</h3>

        <form method="POST" action="user_profile.php">
          <input type="hidden" name="action" value="update_profile">

          <div class="field" style="margin-bottom:14px;">
            <label>Full Name *</label>
            <input type="text" name="full_name"
                   value="<?= htmlspecialchars($user['full_name']) ?>" required>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
            <div class="field">
              <label>Grade / Year</label>
              <select name="grade" style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:13px;color:var(--text);outline:none;">
                <option value="">— Select —</option>
                <?php
                $grades = ['Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12','1st Year','2nd Year','3rd Year','4th Year'];
                foreach ($grades as $g):
                  $sel = ($user['grade'] ?? '') === $g ? 'selected' : '';
                ?>
                  <option <?= $sel ?>><?= $g ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>Section</label>
              <input type="text" name="section"
                     value="<?= htmlspecialchars($user['section'] ?? '') ?>"
                     placeholder="e.g. Rizal">
            </div>
          </div>

          <div class="field" style="margin-bottom:20px;">
            <label>Email</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                   placeholder="your@email.com">
          </div>

          <button type="submit" class="btn btn-primary btn-block">💾 Save Changes</button>
        </form>
      </div>

      <!-- Change Password Card -->
      <div class="form-card">
        <h3 style="font-family:'Playfair Display',serif;font-size:17px;margin-bottom:18px;">Change Password</h3>

        <form method="POST" action="user_profile.php">
          <input type="hidden" name="action" value="change_password">

          <div class="field" style="margin-bottom:14px;">
            <label>Current Password</label>
            <input type="password" name="current_password"
                   placeholder="Enter current password" required>
          </div>

          <div class="field" style="margin-bottom:14px;">
            <label>New Password</label>
            <input type="password" name="new_password"
                   placeholder="Min. 6 characters" required
                   id="new-pw" oninput="checkStrength(this.value)">
            <div id="pw-strength" style="margin-top:6px;height:4px;border-radius:2px;background:var(--border);overflow:hidden;">
              <div id="pw-bar" style="height:100%;width:0;transition:width .3s,background .3s;border-radius:2px;"></div>
            </div>
            <div id="pw-label" class="hint" style="margin-top:4px;"></div>
          </div>

          <div class="field" style="margin-bottom:24px;">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password"
                   placeholder="Repeat new password" required>
          </div>

          <button type="submit" class="btn btn-primary btn-block">🔐 Update Password</button>
        </form>

        <!-- Account Info -->
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
          <div style="font-size:12px;color:var(--text3);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;">Account Info</div>
          <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
            <span style="font-size:13px;color:var(--text2)">Student ID</span>
            <span style="font-size:13px;font-family:'DM Mono',monospace;color:var(--accent)"><?= htmlspecialchars($user['student_id']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
            <span style="font-size:13px;color:var(--text2)">Status</span>
            <span class="badge in-stock"><?= htmlspecialchars($user['status']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:8px 0;">
            <span style="font-size:13px;color:var(--text2)">Member Since</span>
            <span style="font-size:13px;font-family:'DM Mono',monospace;color:var(--text3)">
              <?= date('m/d/Y', strtotime($user['created_at'])) ?>
            </span>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<div id="toast" class="toast"></div>
<script src="assets/js/main.js"></script>
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
</body>
</html>