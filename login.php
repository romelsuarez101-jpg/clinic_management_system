<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (isset($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit(); }
if (isset($_SESSION['user_id']))  { header('Location: user_dashboard.php'); exit(); }

$error = '';
$role  = $_POST['role'] ?? $_GET['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']     ?? '';
    $role     = $_POST['role']         ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both ID and password.';
    } elseif ($role === 'admin') {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($admin && ($admin['password'] === $password || password_verify($password, $admin['password']))) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role']     = 'admin';
            header('Location: dashboard.php'); exit();
        } else {
            $error = 'Invalid admin credentials.';
        }
    } elseif ($role === 'student') {
        $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
            if ($user['status'] !== 'Active') {
                $error = 'Your account is inactive. Please contact the clinic.';
            } else {
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['user_name']  = $user['full_name'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['role']       = 'user';
                header('Location: user_dashboard.php'); exit();
            }
        } else {
            $error = 'Invalid Student ID or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Clinic Inventory System</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

    body {
      font-family: 'Exo 2', sans-serif;
      background: #020e1e;
      background-image:
        radial-gradient(ellipse at 20% 20%, rgba(0,245,255,0.08) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 80%, rgba(0,150,255,0.06) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 50%, rgba(0,80,150,0.04) 0%, transparent 70%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #e6edf3;
    }

    /* Floating orbs */
    .orb {
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      pointer-events: none;
      animation: floatOrb 8s ease-in-out infinite;
    }
    .orb1 { width:300px; height:300px; background:rgba(0,245,255,0.06); top:-50px; left:-50px; animation-delay:0s; }
    .orb2 { width:200px; height:200px; background:rgba(0,100,255,0.08); bottom:-30px; right:-30px; animation-delay:3s; }
    .orb3 { width:150px; height:150px; background:rgba(0,245,255,0.04); top:50%; right:10%; animation-delay:6s; }

    @keyframes floatOrb {
      0%,100% { transform: translateY(0px); }
      50%      { transform: translateY(-20px); }
    }

    .login-wrapper {
      width: 100%;
      max-width: 460px;
      padding: 20px;
      position: relative;
      z-index: 1;
    }

    /* Logo */
    .logo-section {
      text-align: center;
      margin-bottom: 32px;
    }
    .logo-icon {
      width: 80px; height: 80px;
      background: linear-gradient(135deg, #00b4d8, #00f5ff);
      border-radius: 22px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 16px;
      box-shadow: 0 0 40px rgba(0,245,255,0.3), 0 0 80px rgba(0,245,255,0.1);
      animation: logoPulse 3s ease-in-out infinite;
    }
    .logo-icon img {
      width: 52px; height: 52px;
      object-fit: contain; border-radius: 10px;
    }
    @keyframes logoPulse {
      0%,100% { box-shadow: 0 0 40px rgba(0,245,255,0.3), 0 0 80px rgba(0,245,255,0.1); }
      50%      { box-shadow: 0 0 60px rgba(0,245,255,0.5), 0 0 120px rgba(0,245,255,0.2); }
    }
    .logo-title {
      font-family: 'Syne', sans-serif;
      font-size: 22px; font-weight: 800;
      letter-spacing: 3px;
      color: #00f5ff;
      text-shadow: 0 0 20px rgba(0,245,255,0.5);
    }
    .logo-sub {
      font-size: 11px; letter-spacing: 4px;
      color: rgba(0,245,255,0.4);
      text-transform: uppercase;
      margin-top: 4px;
    }

    /* Card */
    .card {
      background: rgba(255,255,255,0.03);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(0,245,255,0.12);
      border-radius: 24px;
      padding: 32px 28px;
      box-shadow: 0 24px 80px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.05);
    }

    /* Step 1: Role Selection */
    .step-title {
      text-align: center;
      font-size: 11px;
      letter-spacing: 3px;
      color: rgba(0,245,255,0.5);
      text-transform: uppercase;
      margin-bottom: 20px;
    }
    .role-cards {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .role-card {
      background: rgba(0,245,255,0.03);
      border: 1px solid rgba(0,245,255,0.1);
      border-radius: 16px;
      padding: 20px;
      cursor: pointer;
      transition: all 0.25s ease;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .role-card:hover {
      background: rgba(0,245,255,0.07);
      border-color: rgba(0,245,255,0.3);
      transform: translateY(-2px);
      box-shadow: 0 8px 32px rgba(0,245,255,0.1);
    }
    .role-card.selected {
      background: rgba(0,245,255,0.1);
      border-color: rgba(0,245,255,0.4);
      box-shadow: 0 0 24px rgba(0,245,255,0.15);
    }
    .role-icon {
      width: 52px; height: 52px;
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px;
      flex-shrink: 0;
    }
    .role-icon.admin  { background: rgba(0,245,255,0.1); color: #00f5ff; }
    .role-icon.student{ background: rgba(100,200,255,0.1); color: #64c8ff; }
    .role-info { flex: 1; }
    .role-name {
      font-family: 'Syne', sans-serif;
      font-weight: 700; font-size: 15px;
      color: #fff; margin-bottom: 3px;
    }
    .role-desc { font-size: 12px; color: rgba(255,255,255,0.35); }
    .role-arrow { color: rgba(0,245,255,0.4); font-size: 14px; }

    /* Step 2: Login Form */
    .back-btn {
      display: flex; align-items: center; gap: 8px;
      color: rgba(0,245,255,0.6);
      font-size: 12px; cursor: pointer;
      margin-bottom: 20px;
      transition: color 0.2s;
      background: none; border: none;
      font-family: 'Exo 2', sans-serif;
    }
    .back-btn:hover { color: #00f5ff; }

    .role-badge {
      display: flex; align-items: center; gap: 10px;
      background: rgba(0,245,255,0.06);
      border: 1px solid rgba(0,245,255,0.15);
      border-radius: 12px;
      padding: 12px 16px;
      margin-bottom: 24px;
    }
    .role-badge-icon {
      width: 36px; height: 36px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px;
    }
    .role-badge-icon.admin  { background: rgba(0,245,255,0.12); color: #00f5ff; }
    .role-badge-icon.student{ background: rgba(100,200,255,0.12); color: #64c8ff; }
    .role-badge-text { font-size: 13px; color: #fff; font-weight: 600; }
    .role-badge-sub  { font-size: 11px; color: rgba(255,255,255,0.35); }

    .alert-error {
      background: rgba(248,81,73,0.1);
      border: 1px solid rgba(248,81,73,0.3);
      border-radius: 10px;
      padding: 11px 14px;
      font-size: 13px;
      color: #f85149;
      margin-bottom: 16px;
      display: flex; align-items: center; gap: 8px;
    }

    .field { margin-bottom: 16px; }
    .field label {
      display: block;
      font-size: 10px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .1em;
      color: rgba(0,245,255,0.5);
      margin-bottom: 8px;
    }
    .field input {
      width: 100%;
      background: rgba(0,245,255,0.04);
      border: 1px solid rgba(0,245,255,0.12);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 14px;
      color: #e6edf3;
      transition: all 0.2s;
      font-family: 'Exo 2', sans-serif;
    }
    .field input:focus {
      outline: none;
      border-color: rgba(0,245,255,0.4);
      background: rgba(0,245,255,0.06);
      box-shadow: 0 0 0 3px rgba(0,245,255,0.07);
    }
    .field input::placeholder { color: rgba(255,255,255,0.2); }

    .btn-login {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #00b4d8, #00f5ff);
      border: none;
      border-radius: 12px;
      color: #020e1e;
      font-family: 'Syne', sans-serif;
      font-size: 14px; font-weight: 700;
      letter-spacing: 1px;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow: 0 4px 24px rgba(0,245,255,0.25);
      margin-top: 8px;
    }
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 32px rgba(0,245,255,0.4);
    }

    .register-link {
      text-align: center;
      margin-top: 20px;
      font-size: 12px;
      color: rgba(255,255,255,0.3);
    }
    .register-link a {
      color: #00f5ff;
      text-decoration: none;
      font-weight: 600;
      transition: opacity 0.2s;
    }
    .register-link a:hover { opacity: 0.7; }

    .footer-note {
      text-align: center;
      margin-top: 24px;
      font-size: 11px;
      color: rgba(255,255,255,0.15);
      letter-spacing: 1px;
    }

    /* Animated dots */
    .dots { display: flex; justify-content: center; gap: 6px; margin-top: 16px; }
    .dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: rgba(0,245,255,0.2);
      transition: background 0.3s;
    }
    .dot.active { background: #00f5ff; }
  </style>
</head>
<body>

<!-- Floating orbs -->
<div class="orb orb1"></div>
<div class="orb orb2"></div>
<div class="orb orb3"></div>

<div class="login-wrapper">

  <!-- Logo -->
  <div class="logo-section">
    <div class="logo-icon">
      <img src="assets/images/logo.png" alt="Logo">
    </div>
    <div class="logo-title">SCHOOL CLINIC</div>
    <div class="logo-sub">Inventory Management System</div>
  </div>

  <!-- Step 1: Role Selection -->
  <div class="card" id="step1" style="display:block;">
    <div class="step-title">Select your role to continue</div>
    <div class="role-cards">

      <div class="role-card <?= $role==='admin'?'selected':'' ?>"
           onclick="selectRole('admin')">
        <div class="role-icon admin">
          <i class="fas fa-user-nurse"></i>
        </div>
        <div class="role-info">
          <div class="role-name">Admin / Nurse</div>
          <div class="role-desc">Manage inventory &amp; requests</div>
        </div>
        <div class="role-arrow"><i class="fas fa-chevron-right"></i></div>
      </div>

      <div class="role-card <?= $role==='student'?'selected':'' ?>"
           onclick="selectRole('student')">
        <div class="role-icon student">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="role-info">
          <div class="role-name">Student / Faculty</div>
          <div class="role-desc">Browse &amp; request medicines</div>
        </div>
        <div class="role-arrow"><i class="fas fa-chevron-right"></i></div>
      </div>

    </div>
    <div class="dots">
      <div class="dot active" id="d1"></div>
      <div class="dot" id="d2"></div>
    </div>
  </div>

  <!-- Step 2: Login Form -->
  <div class="card" id="step2" style="display:none;">

    <button class="back-btn" onclick="goBack()">
      <i class="fas fa-arrow-left"></i> Back
    </button>

    <!-- Role Badge -->
    <div class="role-badge" id="roleBadge">
      <div class="role-badge-icon admin" id="badgeIcon">
        <i class="fas fa-user-nurse" id="badgeIc"></i>
      </div>
      <div>
        <div class="role-badge-text" id="badgeText">Admin / Nurse</div>
        <div class="role-badge-sub" id="badgeSub">Enter your credentials</div>
      </div>
    </div>

    <?php if ($error && $role): ?>
      <div class="alert-error">
        <i class="fas fa-circle-exclamation"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <input type="hidden" name="login" value="1">
      <input type="hidden" name="role"  value="" id="roleInput">

      <div class="field">
        <label id="idLabel">Username</label>
        <input type="text" name="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               placeholder="Enter your username"
               id="usernameInput"
               required autofocus>
      </div>

      <div class="field">
        <label>Password</label>
        <input type="password" name="password"
               placeholder="Enter your password" required>
      </div>

      <button type="submit" class="btn-login">
        <i class="fas fa-right-to-bracket"></i> &nbsp;Sign In
      </button>
    </form>

    <div class="register-link" id="registerLink" style="display:none;">
      Don't have an account?
      <a href="register.php">Register here →</a>
    </div>

    <div class="dots">
      <div class="dot" id="d3"></div>
      <div class="dot active" id="d4"></div>
    </div>

  </div>

  <div class="footer-note">School Clinic · Authorized Personnel Only</div>

</div>

<script>
const roleData = {
  admin: {
    text: 'Admin / Nurse',
    sub:  'Enter your admin credentials',
    icon: 'fa-user-nurse',
    cls:  'admin',
    label:'Username',
    placeholder: 'Enter admin username',
    showRegister: false
  },
  student: {
    text: 'Student / Faculty',
    sub:  'Enter your Student ID',
    icon: 'fa-graduation-cap',
    cls:  'student',
    label:'Student ID',
    placeholder: 'e.g. 2024-00123',
    showRegister: true
  }
};

let currentRole = '<?= $role ?>';

function selectRole(role) {
  currentRole = role;
  // Animate out step1
  const s1 = document.getElementById('step1');
  const s2 = document.getElementById('step2');
  s1.style.opacity = '0';
  s1.style.transform = 'translateY(-10px)';
  s1.style.transition = 'all 0.25s ease';
  setTimeout(() => {
    s1.style.display = 'none';
    setupStep2(role);
    s2.style.display = 'block';
    s2.style.opacity = '0';
    s2.style.transform = 'translateY(10px)';
    s2.style.transition = 'all 0.25s ease';
    setTimeout(() => {
      s2.style.opacity = '1';
      s2.style.transform = 'translateY(0)';
    }, 10);
  }, 250);
}

function setupStep2(role) {
  const d = roleData[role];
  document.getElementById('roleInput').value       = role;
  document.getElementById('badgeText').textContent = d.text;
  document.getElementById('badgeSub').textContent  = d.sub;
  document.getElementById('badgeIc').className     = 'fas ' + d.icon;
  document.getElementById('badgeIcon').className   = 'role-badge-icon ' + d.cls;
  document.getElementById('idLabel').textContent   = d.label;
  document.getElementById('usernameInput').placeholder = d.placeholder;
  document.getElementById('registerLink').style.display = d.showRegister ? 'block' : 'none';
  document.getElementById('usernameInput').focus();
}

function goBack() {
  const s1 = document.getElementById('step1');
  const s2 = document.getElementById('step2');
  s2.style.opacity = '0';
  s2.style.transform = 'translateY(10px)';
  s2.style.transition = 'all 0.25s ease';
  setTimeout(() => {
    s2.style.display = 'none';
    s1.style.display = 'block';
    s1.style.opacity = '0';
    s1.style.transform = 'translateY(-10px)';
    s1.style.transition = 'all 0.25s ease';
    setTimeout(() => {
      s1.style.opacity = '1';
      s1.style.transform = 'translateY(0)';
    }, 10);
  }, 250);
}

// Auto show step 2 if there was an error on POST
<?php if ($error && $role): ?>
window.addEventListener('DOMContentLoaded', () => {
  document.getElementById('step1').style.display = 'none';
  document.getElementById('step2').style.display = 'block';
  document.getElementById('roleInput').value = '<?= $role ?>';
  setupStep2('<?= $role ?>');
});
<?php endif; ?>
</script>

</body>
</html>