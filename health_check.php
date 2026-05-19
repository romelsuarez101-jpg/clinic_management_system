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

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── INPUT ──
    $age        = (int)($_POST['age']        ?? 0);
    $sex        = $_POST['sex']              ?? 'male';
    $height     = (float)($_POST['height']   ?? 0);
    $weight     = (float)($_POST['weight']   ?? 0);
    $systolic   = (int)($_POST['systolic']   ?? 0);
    $diastolic  = (int)($_POST['diastolic']  ?? 0);
    $total_chol = (float)($_POST['total_chol']?? 0);
    $hdl_chol   = (float)($_POST['hdl_chol'] ?? 0);
    $smoking    = isset($_POST['smoking'])   ? 1 : 0;
    $diabetes   = isset($_POST['diabetes'])  ? 1 : 0;
    $bp_treated = isset($_POST['bp_treated'])? 1 : 0;

    // ══ BMI CALCULATION (WHO) ══
    $height_m = $height / 100;
    $bmi      = $height_m > 0 ? round($weight / ($height_m * $height_m), 1) : 0;

    if      ($bmi < 18.5) { $bmi_cat = 'Underweight'; $bmi_color = '#58a6ff'; $bmi_risk = 1; }
    elseif  ($bmi < 25.0) { $bmi_cat = 'Normal';      $bmi_color = '#3fb950'; $bmi_risk = 0; }
    elseif  ($bmi < 30.0) { $bmi_cat = 'Overweight';  $bmi_color = '#e3b341'; $bmi_risk = 2; }
    else                  { $bmi_cat = 'Obese';        $bmi_color = '#f85149'; $bmi_risk = 3; }

    // ══ BLOOD PRESSURE (WHO/AHA) ══
    if      ($systolic < 120 && $diastolic < 80)  { $bp_cat = 'Normal';           $bp_color = '#3fb950'; $bp_risk = 0; }
    elseif  ($systolic < 130 && $diastolic < 80)  { $bp_cat = 'Elevated';         $bp_color = '#e3b341'; $bp_risk = 1; }
    elseif  ($systolic < 140 || $diastolic < 90)  { $bp_cat = 'High Stage 1';     $bp_color = '#f78166'; $bp_risk = 2; }
    elseif  ($systolic < 180 || $diastolic < 120) { $bp_cat = 'High Stage 2';     $bp_color = '#f85149'; $bp_risk = 3; }
    else                                           { $bp_cat = 'Hypertensive Crisis'; $bp_color = '#f85149'; $bp_risk = 4; }

    // ══ FRAMINGHAM HEART SCORE ══
    // Based on: Wilson PWF et al. Circulation. 1998;97:1837-1847
    // Coefficients for 10-year CVD risk
    $score = 0;

    if ($sex === 'male') {
        // Age
        if      ($age >= 70) $score += 13;
        elseif  ($age >= 65) $score += 12;
        elseif  ($age >= 60) $score += 10;
        elseif  ($age >= 55) $score += 9;
        elseif  ($age >= 50) $score += 8;
        elseif  ($age >= 45) $score += 6;
        elseif  ($age >= 40) $score += 5;
        elseif  ($age >= 35) $score += 3;
        else                 $score += 0;

        // Total Cholesterol (mg/dL)
        if      ($total_chol >= 280) $score += 3;
        elseif  ($total_chol >= 240) $score += 2;
        elseif  ($total_chol >= 200) $score += 1;

        // HDL Cholesterol
        if      ($hdl_chol < 35)  $score += 2;
        elseif  ($hdl_chol < 45)  $score += 1;
        elseif  ($hdl_chol >= 60) $score -= 1;

        // Systolic BP
        if ($bp_treated) {
            if      ($systolic >= 160) $score += 6;
            elseif  ($systolic >= 140) $score += 4;
            elseif  ($systolic >= 130) $score += 3;
            elseif  ($systolic >= 120) $score += 2;
        } else {
            if      ($systolic >= 160) $score += 4;
            elseif  ($systolic >= 140) $score += 2;
            elseif  ($systolic >= 130) $score += 1;
        }

        // Smoking
        if ($smoking)  $score += 4;
        // Diabetes
        if ($diabetes) $score += 3;

        // Convert score to risk %
        $risk_map = [
            -3=>1, -2=>2, -1=>2, 0=>3, 1=>4, 2=>4, 3=>6,
            4=>7, 5=>9, 6=>11, 7=>14, 8=>18, 9=>22,
            10=>27, 11=>33, 12=>40, 13=>47
        ];

    } else {
        // Female coefficients
        if      ($age >= 70) $score += 14;
        elseif  ($age >= 65) $score += 13;
        elseif  ($age >= 60) $score += 12;
        elseif  ($age >= 55) $score += 10;
        elseif  ($age >= 50) $score += 8;
        elseif  ($age >= 45) $score += 7;
        elseif  ($age >= 40) $score += 5;
        elseif  ($age >= 35) $score += 3;
        else                 $score += 0;

        // Total Cholesterol
        if      ($total_chol >= 280) $score += 4;
        elseif  ($total_chol >= 240) $score += 3;
        elseif  ($total_chol >= 200) $score += 2;
        elseif  ($total_chol >= 160) $score += 1;

        // HDL Cholesterol
        if      ($hdl_chol < 35)  $score += 5;
        elseif  ($hdl_chol < 45)  $score += 2;
        elseif  ($hdl_chol < 50)  $score += 1;
        elseif  ($hdl_chol >= 60) $score -= 2;

        // Systolic BP
        if ($bp_treated) {
            if      ($systolic >= 160) $score += 8;
            elseif  ($systolic >= 140) $score += 6;
            elseif  ($systolic >= 130) $score += 5;
            elseif  ($systolic >= 120) $score += 4;
        } else {
            if      ($systolic >= 160) $score += 5;
            elseif  ($systolic >= 140) $score += 3;
            elseif  ($systolic >= 130) $score += 2;
            elseif  ($systolic >= 120) $score += 1;
        }

        // Smoking
        if ($smoking)  $score += 3;
        // Diabetes
        if ($diabetes) $score += 4;

        // Female risk map
        $risk_map = [
            -2=>1, -1=>2, 0=>2, 1=>2, 2=>3, 3=>3,
            4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9,
            10=>11, 11=>13, 12=>15, 13=>17, 14=>20,
            15=>24, 16=>27, 17=>30
        ];
    }

    // Get CVD risk %
    $score = max(min($score, 13), -3);
    $cvd_risk = $risk_map[$score] ?? ($score >= 13 ? 30 : 1);

    // CVD Risk Category
    if      ($cvd_risk < 10) { $cvd_cat = 'Low Risk';      $cvd_color = '#3fb950'; }
    elseif  ($cvd_risk < 20) { $cvd_cat = 'Moderate Risk'; $cvd_color = '#e3b341'; }
    else                     { $cvd_cat = 'High Risk';      $cvd_color = '#f85149'; }

    // ══ OVERALL HEALTH SCORE (0-100) ══
    $health_score = 100;
    $health_score -= ($bmi_risk  * 8);
    $health_score -= ($bp_risk   * 7);
    $health_score -= ($cvd_risk  * 1.5);
    $health_score -= ($smoking   * 10);
    $health_score -= ($diabetes  * 8);
    $health_score  = max(0, min(100, round($health_score)));

    if      ($health_score >= 80) { $health_cat = 'Excellent'; $health_color = '#3fb950'; }
    elseif  ($health_score >= 60) { $health_cat = 'Good';      $health_color = '#58a6ff'; }
    elseif  ($health_score >= 40) { $health_cat = 'Fair';      $health_color = '#e3b341'; }
    else                          { $health_cat = 'Poor';      $health_color = '#f85149'; }

    // ══ RECOMMENDATIONS ══
    $recommendations = [];
    if ($bmi >= 25)        $recommendations[] = ['⚖️', 'Maintain a balanced diet and regular exercise to achieve a healthy weight.'];
    if ($bmi < 18.5)       $recommendations[] = ['🥗', 'Increase caloric intake with nutritious foods to reach a healthy weight.'];
    if ($bp_risk >= 2)     $recommendations[] = ['🩺', 'Consult a doctor about your blood pressure. Reduce salt intake and exercise regularly.'];
    if ($cvd_risk >= 10)   $recommendations[] = ['💓', 'Your 10-year heart disease risk is elevated. Consult a cardiologist.'];
    if ($smoking)          $recommendations[] = ['🚭', 'Quitting smoking significantly reduces heart disease and cancer risk.'];
    if ($diabetes)         $recommendations[] = ['💉', 'Manage blood sugar levels through diet, exercise, and medication if prescribed.'];
    if ($total_chol >= 200)$recommendations[] = ['🥩', 'Reduce intake of saturated fats and cholesterol-rich foods.'];
    if ($hdl_chol < 40)    $recommendations[] = ['🏃', 'Increase physical activity to raise HDL (good) cholesterol levels.'];
    if (empty($recommendations)) $recommendations[] = ['🌟', 'Great job! Keep maintaining your healthy lifestyle habits.'];

   // Save to database
$source = 'self';
$save = $conn->prepare("
    INSERT INTO health_records
      (user_id, age, sex, height, weight, bmi, bmi_category,
       systolic_bp, diastolic_bp, bp_category,
       total_cholesterol, hdl_cholesterol,
       smoking, diabetes, bp_treatment,
       cvd_risk_percent, cvd_category,
       health_score, health_category, source)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$uid = (int)$user_id;
$a   = (int)$age;
$h   = (float)$height;
$w   = (float)$weight;
$b   = (float)$bmi;
$sys = (int)$systolic;
$dia = (int)$diastolic;
$tc  = (float)$total_chol;
$hdl = (float)$hdl_chol;
$sm  = (int)$smoking;
$db  = (int)$diabetes;
$bpt = (int)$bp_treated;
$cvd = (float)$cvd_risk;
$hs  = (int)$health_score;

$save->bind_param("iisdddsiiisddiiidsis",
    $uid, $a, $sex,
    $h, $w, $b, $bmi_cat,
    $sys, $dia, $bp_cat,
    $tc, $hdl,
    $sm, $db, $bpt,
    $cvd, $cvd_cat,
    $hs, $health_cat,
    $source
);
$save->execute();
$save->close();

// Count: 19 values
// i=user_id, i=age, s=sex, d=height, d=weight,
// d=bmi, s=bmi_cat, i=systolic, i=diastolic, s=bp_cat,
// d=total_chol, d=hdl_chol, i=smoking, i=diabetes, i=bp_treated,
// d=cvd_risk, s=cvd_cat, i=health_score, s=health_cat
$types = "iisdddsiiisddiiiids";

// Convert to correct types
$user_id_int    = (int)$user_id;
$age_int        = (int)$age;
$height_d       = (float)$height;
$weight_d       = (float)$weight;
$bmi_d          = (float)$bmi;
$systolic_int   = (int)$systolic;
$diastolic_int  = (int)$diastolic;
$total_chol_d   = (float)$total_chol;
$hdl_chol_d     = (float)$hdl_chol;
$smoking_int    = (int)$smoking;
$diabetes_int   = (int)$diabetes;
$bp_treated_int = (int)$bp_treated;
$cvd_risk_d     = (float)$cvd_risk;
$health_int     = (int)$health_score;

$save->bind_param($types,
    $user_id_int,
    $age_int,
    $sex,
    $height_d,
    $weight_d,
    $bmi_d,
    $bmi_cat,
    $systolic_int,
    $diastolic_int,
    $bp_cat,
    $total_chol_d,
    $hdl_chol_d,
    $smoking_int,
    $diabetes_int,
    $bp_treated_int,
    $cvd_risk_d,
    $cvd_cat,
    $health_int,
    $health_cat
);
$save->execute();
$save->close();

    $result = compact(
        'bmi','bmi_cat','bmi_color',
        'bp_cat','bp_color','systolic','diastolic',
        'cvd_risk','cvd_cat','cvd_color',
        'health_score','health_cat','health_color',
        'recommendations','score','age','sex'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Health Risk Check — Clinic</title>
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
      <a href="user_dashboard.php" class="nav-item"><span class="nav-icon">🏠</span> Dashboard</a>
      <a href="user_medicines.php" class="nav-item"><span class="nav-icon">💊</span> View Medicines</a>
      <a href="user_requests.php"  class="nav-item"><span class="nav-icon">📋</span> My Requests</a>
      <a href="health_check.php"   class="nav-item active"><span class="nav-icon">❤️</span> Health Check</a>
    </div>
    <div class="sidebar-footer">
      <div class="sidebar-build">Student Portal v1.0</div>
      <div class="sidebar-build">© <?= date('Y') ?> ICAS School Clinic</div>
    </div>
  </aside>

  <main class="main-content">

    <div class="page-header">
      <div>
        <h1>❤️ Health Risk Assessment</h1>
        <p>Based on WHO standards & Framingham Heart Score</p>
      </div>
    </div>

    <!-- Disclaimer -->
    <div class="alert alert-warning" style="margin-bottom:24px;">
      ⚠ <strong>Medical Disclaimer:</strong> This tool is for educational purposes only and does not constitute medical advice. Results are based on validated scoring systems but should not replace professional medical consultation. Please consult a licensed physician for proper diagnosis and treatment.
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">

      <!-- INPUT FORM -->
      <div class="form-card">
        <h3 style="font-family:'Playfair Display',serif;font-size:18px;margin-bottom:20px;">📋 Input Parameters</h3>

        <form method="POST" action="health_check.php" id="healthForm">
          <div class="form-grid">

            <div class="field">
              <label>Age *</label>
              <input type="number" name="age" min="18" max="79"
                     value="<?= htmlspecialchars($_POST['age'] ?? '') ?>"
                     placeholder="e.g. 20" required>
              <div class="hint">18–79 years</div>
            </div>

            <div class="field">
              <label>Sex *</label>
              <select name="sex" style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--text);outline:none;">
                <option value="male"   <?= ($_POST['sex']??'male')==='male'?'selected':'' ?>>Male</option>
                <option value="female" <?= ($_POST['sex']??'')==='female'?'selected':'' ?>>Female</option>
              </select>
            </div>

            <div class="field">
              <label>Height (cm) *</label>
              <input type="number" name="height" min="100" max="250" step="0.1"
                     value="<?= htmlspecialchars($_POST['height'] ?? '') ?>"
                     placeholder="e.g. 165" required>
            </div>

            <div class="field">
              <label>Weight (kg) *</label>
              <input type="number" name="weight" min="30" max="300" step="0.1"
                     value="<?= htmlspecialchars($_POST['weight'] ?? '') ?>"
                     placeholder="e.g. 60" required>
            </div>

            <div class="field">
              <label>Systolic BP (mmHg) *</label>
              <input type="number" name="systolic" min="80" max="250"
                     value="<?= htmlspecialchars($_POST['systolic'] ?? '') ?>"
                     placeholder="e.g. 120" required>
              <div class="hint">Upper number in BP reading</div>
            </div>

            <div class="field">
              <label>Diastolic BP (mmHg) *</label>
              <input type="number" name="diastolic" min="40" max="150"
                     value="<?= htmlspecialchars($_POST['diastolic'] ?? '') ?>"
                     placeholder="e.g. 80" required>
              <div class="hint">Lower number in BP reading</div>
            </div>

            <div class="field">
              <label>Total Cholesterol (mg/dL)</label>
              <input type="number" name="total_chol" min="100" max="400" step="0.1"
                     value="<?= htmlspecialchars($_POST['total_chol'] ?? '') ?>"
                     placeholder="e.g. 180">
              <div class="hint">From blood test results</div>
            </div>

            <div class="field">
              <label>HDL Cholesterol (mg/dL)</label>
              <input type="number" name="hdl_chol" min="20" max="100" step="0.1"
                     value="<?= htmlspecialchars($_POST['hdl_chol'] ?? '') ?>"
                     placeholder="e.g. 50">
              <div class="hint">Good cholesterol</div>
            </div>

            <!-- Checkboxes -->
            <div class="field span2">
              <label>Risk Factors</label>
              <div style="display:flex;flex-direction:column;gap:12px;margin-top:8px;">

                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;color:var(--text);text-transform:none;letter-spacing:0;">
                  <input type="checkbox" name="smoking" value="1"
                         <?= isset($_POST['smoking'])?'checked':'' ?>
                         style="width:16px;height:16px;accent-color:var(--accent);">
                  🚬 Current Smoker
                </label>

                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;color:var(--text);text-transform:none;letter-spacing:0;">
                  <input type="checkbox" name="diabetes" value="1"
                         <?= isset($_POST['diabetes'])?'checked':'' ?>
                         style="width:16px;height:16px;accent-color:var(--accent);">
                  💉 Diagnosed with Diabetes
                </label>

                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;color:var(--text);text-transform:none;letter-spacing:0;">
                  <input type="checkbox" name="bp_treated" value="1"
                         <?= isset($_POST['bp_treated'])?'checked':'' ?>
                         style="width:16px;height:16px;accent-color:var(--accent);">
                  💊 Currently on Blood Pressure Medication
                </label>

              </div>
            </div>

          </div>

          <div style="margin-top:8px;">
            <button type="submit" class="btn btn-primary btn-block btn-lg" id="analyzeBtn">
              ⚡ Analyze Health Risk
            </button>
          </div>
        </form>
      </div>

      <!-- RESULTS -->
      <div id="resultsPanel">
      <?php if ($result): ?>

        <!-- Overall Health Score -->
        <div class="form-card" style="margin-bottom:20px;text-align:center;">
          <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text2);margin-bottom:16px;">
            Overall Health Score
          </div>

          <!-- Animated Circle -->
          <div style="position:relative;width:160px;height:160px;margin:0 auto 16px;">
            <svg width="160" height="160" style="transform:rotate(-90deg)">
              <circle cx="80" cy="80" r="65" fill="none" stroke="var(--border)" stroke-width="12"/>
              <circle cx="80" cy="80" r="65" fill="none"
                      stroke="<?= $result['health_color'] ?>"
                      stroke-width="12"
                      stroke-dasharray="<?= round(2 * M_PI * 65) ?>"
                      stroke-dashoffset="<?= round(2 * M_PI * 65 * (1 - $result['health_score']/100)) ?>"
                      stroke-linecap="round"
                      id="scoreCircle"
                      style="transition:stroke-dashoffset 1.5s ease"/>
            </svg>
            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
              <div style="font-family:'Playfair Display',serif;font-size:36px;font-weight:700;color:<?= $result['health_color'] ?>">
                <?= $result['health_score'] ?>
              </div>
              <div style="font-size:11px;color:var(--text3)">out of 100</div>
            </div>
          </div>

          <div style="font-size:22px;font-weight:700;color:<?= $result['health_color'] ?>">
            <?= htmlspecialchars($result['health_cat']) ?>
          </div>
          <div style="font-size:12px;color:var(--text3);margin-top:4px;">
            Based on your health inputs
          </div>
        </div>

        <!-- 3 Metric Cards -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:20px;">

          <!-- BMI -->
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text2);margin-bottom:8px;">BMI</div>
            <div style="font-size:28px;font-weight:700;color:<?= $result['bmi_color'] ?>;font-family:'Playfair Display',serif;">
              <?= $result['bmi'] ?>
            </div>
            <div style="font-size:11px;color:<?= $result['bmi_color'] ?>;font-weight:600;margin-top:4px;">
              <?= htmlspecialchars($result['bmi_cat']) ?>
            </div>
            <div style="font-size:10px;color:var(--text3);margin-top:4px;">WHO Standard</div>
          </div>

          <!-- Blood Pressure -->
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text2);margin-bottom:8px;">Blood Pressure</div>
            <div style="font-size:20px;font-weight:700;color:<?= $result['bp_color'] ?>;font-family:'DM Mono',monospace;">
              <?= $result['systolic'] ?>/<?= $result['diastolic'] ?>
            </div>
            <div style="font-size:11px;color:<?= $result['bp_color'] ?>;font-weight:600;margin-top:4px;">
              <?= htmlspecialchars($result['bp_cat']) ?>
            </div>
            <div style="font-size:10px;color:var(--text3);margin-top:4px;">AHA Standard</div>
          </div>

          <!-- CVD Risk -->
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text2);margin-bottom:8px;">Heart Risk</div>
            <div style="font-size:28px;font-weight:700;color:<?= $result['cvd_color'] ?>;font-family:'Playfair Display',serif;">
              <?= $result['cvd_risk'] ?>%
            </div>
            <div style="font-size:11px;color:<?= $result['cvd_color'] ?>;font-weight:600;margin-top:4px;">
              <?= htmlspecialchars($result['cvd_cat']) ?>
            </div>
            <div style="font-size:10px;color:var(--text3);margin-top:4px;">10-yr CVD Risk</div>
          </div>

        </div>

        <!-- Recommendations -->
        <div class="form-card">
          <h3 style="font-family:'Playfair Display',serif;font-size:16px;margin-bottom:16px;">
            💡 Recommendations
          </h3>
          <?php foreach ($result['recommendations'] as $rec): ?>
            <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;margin-bottom:8px;">
              <span style="font-size:20px;"><?= $rec[0] ?></span>
              <span style="font-size:13px;color:var(--text2);line-height:1.5;"><?= htmlspecialchars($rec[1]) ?></span>
            </div>
          <?php endforeach; ?>
          <div style="margin-top:16px;padding:12px;background:rgba(88,166,255,.08);border:1px solid rgba(88,166,255,.2);border-radius:8px;">
            <div style="font-size:12px;color:var(--blue);">
              ℹ Based on: <strong>Framingham Heart Study</strong> (Wilson et al., Circulation 1998) & <strong>WHO BMI Classification</strong> & <strong>AHA Blood Pressure Guidelines 2017</strong>
            </div>
          </div>
        </div>

      <?php else: ?>
        <!-- Empty State -->
        <div class="form-card" style="text-align:center;padding:48px 32px;">
          <div style="font-size:64px;margin-bottom:16px;">❤️</div>
          <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px;">
            Ready to Check Your Health?
          </h3>
          <p style="color:var(--text2);font-size:14px;line-height:1.6;">
            Fill in the form on the left and click<br>
            <strong style="color:var(--accent)">Analyze Health Risk</strong> to see your results.
          </p>
          <div style="margin-top:24px;display:flex;flex-direction:column;gap:8px;text-align:left;">
            <div style="font-size:12px;color:var(--text3);display:flex;align-items:center;gap:8px;">
              ✅ BMI Assessment (WHO)
            </div>
            <div style="font-size:12px;color:var(--text3);display:flex;align-items:center;gap:8px;">
              ✅ Blood Pressure (AHA 2017)
            </div>
            <div style="font-size:12px;color:var(--text3);display:flex;align-items:center;gap:8px;">
              ✅ 10-Year Heart Risk (Framingham)
            </div>
            <div style="font-size:12px;color:var(--text3);display:flex;align-items:center;gap:8px;">
              ✅ Personalized Recommendations
            </div>
          </div>
        </div>
      <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<div id="toast" class="toast"></div>
<script src="assets/js/main.js"></script>
<script>
// Animate the score circle on load
window.addEventListener('load', () => {
  const circle = document.getElementById('scoreCircle');
  if (circle) {
    const final = circle.getAttribute('stroke-dashoffset');
    circle.style.strokeDashoffset = circle.getAttribute('stroke-dasharray');
    setTimeout(() => { circle.style.strokeDashoffset = final; }, 100);
  }
});
</script>
</body>
</html>