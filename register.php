<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (isset($_SESSION['user_id'])) { header('Location: user_dashboard.php'); exit(); }

$errors  = [];
$success = false;
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old        = $_POST;
    $student_id = trim($_POST['student_id'] ?? '');
    $full_name  = trim($_POST['full_name']  ?? '');
    $grade      = trim($_POST['grade']      ?? '');
    $section    = trim($_POST['section']    ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = $_POST['password']        ?? '';
    $confirm    = $_POST['confirm_password']?? '';

    if (empty($student_id)) $errors[] = 'Student ID is required.';
    if (empty($full_name))  $errors[] = 'Full name is required.';
    if (empty($password))   $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = 'Student ID already registered.';
        $stmt->close();
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO users (student_id, full_name, grade, section, email, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $student_id, $full_name, $grade, $section, $email, $password);
        if ($stmt->execute()) { $success = true; $old = []; }
        else { $errors[] = 'Registration failed: ' . $stmt->error; }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — Clinic Inventory System</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: 'Exo 2', sans-serif;
      background: #020e1e;
      background-image:
        radial-gradient(ellipse at 20% 20%, rgba(0,245,255,0.08) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 80%, rgba(0,150,255,0.06) 0%, transparent 50%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #e6edf3;
      padding: 20px;
    }
    .orb { position:fixed; border-radius:50%; filter:blur(80px); pointer-events:none; animation:floatOrb 8s ease-in-out infinite; }
    .orb1 { width:300px; height:300px; background:rgba(0,245,255,0.06); top:-50px; left:-50px; }
    .orb2 { width:200px; height:200px; background:rgba(0,100,255,0.08); bottom:-30px; right:-30px; animation-delay:3s; }
    @keyframes floatOrb { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-20px)} }

    .wrapper { width:100%; max-width:500px; position:relative; z-index:1; }

    .logo-section { text-align:center; margin-bottom:28px; }
    .logo-icon {
      width:70px; height:70px;
      background:linear-gradient(135deg,#00b4d8,#00f5ff);
      border-radius:20px;
      display:flex; align-items:center; justify-content:center;
      margin:0 auto 14px;
      box-shadow:0 0 40px rgba(0,245,255,0.3);
    }
    .logo-icon img { width:46px; height:46px; object-fit:contain; border-radius:8px; }
    .logo-title { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; letter-spacing:3px; color:#00f5ff; text-shadow:0 0 20px rgba(0,245,255,0.5); }
    .logo-sub { font-size:10px; letter-spacing:4px; color:rgba(0,245,255,0.4); text-transform:uppercase; margin-top:4px; }

    .card {
      background:rgba(255,255,255,0.03);
      backdrop-filter:blur(24px);
      -webkit-backdrop-filter:blur(24px);
      border:1px solid rgba(0,245,255,0.12);
      border-radius:24px;
      padding:32px 28px;
      box-shadow:0 24px 80px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.05);
    }

    .card-title { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:4px; }
    .card-sub { font-size:12px; color:rgba(255,255,255,0.35); margin-bottom:24px; }

    .alert-success {
      background:rgba(63,185,80,0.1); border:1px solid rgba(63,185,80,0.3);
      border-radius:10px; padding:12px 14px; font-size:13px; color:#3fb950;
      margin-bottom:16px; display:flex; align-items:center; gap:8px;
    }
    .alert-error {
      background:rgba(248,81,73,0.1); border:1px solid rgba(248,81,73,0.3);
      border-radius:10px; padding:12px 14px; font-size:13px; color:#f85149;
      margin-bottom:16px;
    }

    .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .span2 { grid-column:1/-1; }

    .field { margin-bottom:0; }
    .field label {
      display:block; font-size:10px; font-weight:700;
      text-transform:uppercase; letter-spacing:.1em;
      color:rgba(0,245,255,0.5); margin-bottom:7px;
    }
    .field input, .field select {
      width:100%;
      background:rgba(0,245,255,0.04);
      border:1px solid rgba(0,245,255,0.12);
      border-radius:10px; padding:11px 14px;
      font-size:13px; color:#e6edf3;
      transition:all 0.2s;
      font-family:'Exo 2',sans-serif;
    }
    .field input:focus, .field select:focus {
      outline:none;
      border-color:rgba(0,245,255,0.4);
      background:rgba(0,245,255,0.06);
      box-shadow:0 0 0 3px rgba(0,245,255,0.07);
    }
    .field input::placeholder { color:rgba(255,255,255,0.2); }
    .field select { cursor:pointer; }
    .field select option { background:#0a1628; color:#e6edf3; }

    .btn-register {
      width:100%; padding:14px;
      background:linear-gradient(135deg,#00b4d8,#00f5ff);
      border:none; border-radius:12px;
      color:#020e1e; font-family:'Syne',sans-serif;
      font-size:14px; font-weight:700; letter-spacing:1px;
      cursor:pointer; transition:all 0.2s;
      box-shadow:0 4px 24px rgba(0,245,255,0.25);
      margin-top:20px;
    }
    .btn-register:hover { transform:translateY(-2px); box-shadow:0 8px 32px rgba(0,245,255,0.4); }

    .login-link { text-align:center; margin-top:20px; font-size:12px; color:rgba(255,255,255,0.3); }
    .login-link a { color:#00f5ff; text-decoration:none; font-weight:600; }
    .login-link a:hover { opacity:0.7; }

    .footer-note { text-align:center; margin-top:24px; font-size:11px; color:rgba(255,255,255,0.15); letter-spacing:1px; }

    .divider {
      border:none; border-top:1px solid rgba(0,245,255,0.08);
      margin:20px 0;
    }
  </style>
</head>
<body>

<div class="orb orb1"></div>
<div class="orb orb2"></div>

<div class="wrapper">

  <div class="logo-section">
    <div class="logo-icon">
      <img src="assets/images/logo.png" alt="Logo">
    </div>
    <div class="logo-title">SCHOOL CLINIC</div>
    <div class="logo-sub">Student Registration</div>
  </div>

  <div class="card">
    <div class="card-title">Create Account</div>
    <div class="card-sub">Register to request medicines from the clinic</div>

    <?php if ($success): ?>
      <div class="alert-success">
        <i class="fas fa-circle-check"></i>
        Registration successful!
        <a href="login.php" style="color:#3fb950;font-weight:700;margin-left:4px;">Login here →</a>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert-error">
        <i class="fas fa-circle-exclamation"></i>
        <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
      </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" action="register.php">
      <div class="grid2" style="gap:14px;">

        <div class="field span2">
          <label>Full Name *</label>
          <input type="text" name="full_name"
                 value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                 placeholder="e.g. Juan Dela Cruz" required>
        </div>

        <div class="field span2">
          <label>Student ID *</label>
          <input type="text" name="student_id"
                 value="<?= htmlspecialchars($old['student_id'] ?? '') ?>"
                 placeholder="e.g. 2024-00123" required>
        </div>

        <div class="field">
          <label>Grade / Year</label>
          <select name="grade">
            <option value="">— Select —</option>
            <?php
            $grades = ['Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12','1st Year','2nd Year','3rd Year','4th Year','Faculty'];
            foreach ($grades as $g):
              $sel = ($old['grade'] ?? '') === $g ? 'selected' : '';
            ?>
              <option <?= $sel ?>><?= $g ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label>Section</label>
          <input type="text" name="section"
                 value="<?= htmlspecialchars($old['section'] ?? '') ?>"
                 placeholder="e.g. Rizal">
        </div>

        <div class="field span2">
          <label>Email <span style="color:rgba(255,255,255,0.2);font-weight:400;">(optional)</span></label>
          <input type="email" name="email"
                 value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                 placeholder="your@email.com">
        </div>

        <hr class="divider" style="grid-column:1/-1;">

        <div class="field">
          <label>Password *</label>
          <input type="password" name="password"
                 placeholder="Min. 6 characters" required
                 id="pwInput" oninput="checkPw(this.value)">
          <div style="margin-top:6px;height:3px;background:rgba(0,245,255,0.1);border-radius:2px;overflow:hidden;">
            <div id="pwBar" style="height:100%;width:0;transition:width .3s,background .3s;border-radius:2px;"></div>
          </div>
          <div id="pwLabel" style="font-size:10px;color:rgba(0,245,255,0.4);margin-top:4px;"></div>
        </div>

        <div class="field">
          <label>Confirm Password *</label>
          <input type="password" name="confirm_password"
                 placeholder="Repeat password" required>
        </div>

      </div>

      <button type="submit" class="btn-register">
        <i class="fas fa-user-plus"></i> &nbsp;Create Account
      </button>
    </form>
    <?php endif; ?>

    <div class="login-link">
      Already have an account?
      <a href="login.php">Sign in →</a>
    </div>
  </div>

  <div class="footer-note">School Clinic · Authorized Personnel Only</div>
</div>

<script>
function checkPw(pw) {
  let score = 0;
  if (pw.length >= 6)  score++;
  if (pw.length >= 10) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;
  const pct    = (score / 5) * 100;
  const colors = ['#f85149','#f85149','#e3b341','#e3b341','#00f5ff'];
  const labels = ['','Very Weak','Weak','Fair','Strong','Very Strong'];
  document.getElementById('pwBar').style.width      = pct + '%';
  document.getElementById('pwBar').style.background = colors[score-1] || 'rgba(0,245,255,0.1)';
  document.getElementById('pwLabel').textContent    = pw.length ? labels[score] : '';
  document.getElementById('pwLabel').style.color    = colors[score-1] || 'rgba(0,245,255,0.4)';
}
</script>

</body>
</html>