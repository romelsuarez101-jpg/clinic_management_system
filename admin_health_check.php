<?php
$pageTitle  = 'Clinic Assessment';
$activePage = 'health';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

header('Cache-Control: no store, no cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$result  = null;
$patient = null;
$errors  = [];

// Search student
if (isset($_GET['student_id']) && !empty($_GET['student_id'])) {
    $sid  = trim($_GET['student_id']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ? OR full_name LIKE ?");
    $like = "%{$sid}%";
    $stmt->bind_param("ss", $sid, $like);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$patient) $errors[] = 'Student not found. Please check the ID or name.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {

    $user_id    = (int)$_POST['user_id'];
    $age        = (int)($_POST['age']          ?? 0);
    $sex        = $_POST['sex']                ?? 'male';
    $height     = (float)($_POST['height']     ?? 0);
    $weight     = (float)($_POST['weight']     ?? 0);
    $systolic   = (int)($_POST['systolic']     ?? 0);
    $diastolic  = (int)($_POST['diastolic']    ?? 0);
    $total_chol = (float)($_POST['total_chol'] ?? 0);
    $hdl_chol   = (float)($_POST['hdl_chol']   ?? 0);
    $smoking    = isset($_POST['smoking'])     ? 1 : 0;
    $diabetes   = isset($_POST['diabetes'])    ? 1 : 0;
    $bp_treated = isset($_POST['bp_treated'])  ? 1 : 0;
    $notes      = trim($_POST['notes']         ?? '');

    // ══ BMI (WHO) ══
    $height_m = $height / 100;
    $bmi      = $height_m > 0 ? round($weight / ($height_m * $height_m), 1) : 0;

    if      ($bmi < 18.5) { $bmi_cat = 'Underweight'; $bmi_color = '#58a6ff'; $bmi_risk = 1; }
    elseif  ($bmi < 25.0) { $bmi_cat = 'Normal';      $bmi_color = '#3fb950'; $bmi_risk = 0; }
    elseif  ($bmi < 30.0) { $bmi_cat = 'Overweight';  $bmi_color = '#e3b341'; $bmi_risk = 2; }
    else                  { $bmi_cat = 'Obese';        $bmi_color = '#f85149'; $bmi_risk = 3; }

    // ══ BLOOD PRESSURE (AHA) ══
    if      ($systolic < 120 && $diastolic < 80)  { $bp_cat = 'Normal';             $bp_color = '#3fb950'; $bp_risk = 0; }
    elseif  ($systolic < 130 && $diastolic < 80)  { $bp_cat = 'Elevated';           $bp_color = '#e3b341'; $bp_risk = 1; }
    elseif  ($systolic < 140 || $diastolic < 90)  { $bp_cat = 'High Stage 1';       $bp_color = '#f78166'; $bp_risk = 2; }
    elseif  ($systolic < 180 || $diastolic < 120) { $bp_cat = 'High Stage 2';       $bp_color = '#f85149'; $bp_risk = 3; }
    else                                           { $bp_cat = 'Hypertensive Crisis';$bp_color = '#f85149'; $bp_risk = 4; }

    // ══ FRAMINGHAM HEART SCORE ══
    $score = 0;
    if ($sex === 'male') {
        if      ($age >= 70) $score += 13;
        elseif  ($age >= 65) $score += 12;
        elseif  ($age >= 60) $score += 10;
        elseif  ($age >= 55) $score += 9;
        elseif  ($age >= 50) $score += 8;
        elseif  ($age >= 45) $score += 6;
        elseif  ($age >= 40) $score += 5;
        elseif  ($age >= 35) $score += 3;

        if      ($total_chol >= 280) $score += 3;
        elseif  ($total_chol >= 240) $score += 2;
        elseif  ($total_chol >= 200) $score += 1;

        if      ($hdl_chol < 35)  $score += 2;
        elseif  ($hdl_chol < 45)  $score += 1;
        elseif  ($hdl_chol >= 60) $score -= 1;

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
        if ($smoking)  $score += 4;
        if ($diabetes) $score += 3;

        $risk_map = [
            -3=>1,-2=>2,-1=>2,0=>3,1=>4,2=>4,3=>6,
            4=>7,5=>9,6=>11,7=>14,8=>18,9=>22,
            10=>27,11=>33,12=>40,13=>47
        ];
    } else {
        if      ($age >= 70) $score += 14;
        elseif  ($age >= 65) $score += 13;
        elseif  ($age >= 60) $score += 12;
        elseif  ($age >= 55) $score += 10;
        elseif  ($age >= 50) $score += 8;
        elseif  ($age >= 45) $score += 7;
        elseif  ($age >= 40) $score += 5;
        elseif  ($age >= 35) $score += 3;

        if      ($total_chol >= 280) $score += 4;
        elseif  ($total_chol >= 240) $score += 3;
        elseif  ($total_chol >= 200) $score += 2;
        elseif  ($total_chol >= 160) $score += 1;

        if      ($hdl_chol < 35)  $score += 5;
        elseif  ($hdl_chol < 45)  $score += 2;
        elseif  ($hdl_chol < 50)  $score += 1;
        elseif  ($hdl_chol >= 60) $score -= 2;

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
        if ($smoking)  $score += 3;
        if ($diabetes) $score += 4;

        $risk_map = [
            -2=>1,-1=>2,0=>2,1=>2,2=>3,3=>3,
            4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,
            10=>11,11=>13,12=>15,13=>17,14=>20,
            15=>24,16=>27,17=>30
        ];
    }

    $score    = max(min($score, 17), -3);
    $cvd_risk = $risk_map[$score] ?? ($score >= 17 ? 30 : 1);

    if      ($cvd_risk < 10) { $cvd_cat = 'Low Risk';      $cvd_color = '#3fb950'; }
    elseif  ($cvd_risk < 20) { $cvd_cat = 'Moderate Risk'; $cvd_color = '#e3b341'; }
    else                     { $cvd_cat = 'High Risk';      $cvd_color = '#f85149'; }

    // ══ OVERALL HEALTH SCORE ══
    $health_score  = 100;
    $health_score -= ($bmi_risk * 8);
    $health_score -= ($bp_risk  * 7);
    $health_score -= ($cvd_risk * 1.5);
    $health_score -= ($smoking  * 10);
    $health_score -= ($diabetes * 8);
    $health_score  = max(0, min(100, round($health_score)));

    if      ($health_score >= 80) { $health_cat = 'Excellent'; $health_color = '#3fb950'; }
    elseif  ($health_score >= 60) { $health_cat = 'Good';      $health_color = '#58a6ff'; }
    elseif  ($health_score >= 40) { $health_cat = 'Fair';      $health_color = '#e3b341'; }
    else                          { $health_cat = 'Poor';      $health_color = '#f85149'; }

    // ══ RECOMMENDATIONS ══
    $recommendations = [];
    if ($bmi >= 25)         $recommendations[] = ['⚖️', 'Maintain a balanced diet and regular exercise to achieve a healthy weight.'];
    if ($bmi < 18.5)        $recommendations[] = ['🥗', 'Increase caloric intake with nutritious foods to reach a healthy weight.'];
    if ($bp_risk >= 2)      $recommendations[] = ['🩺', 'Monitor blood pressure regularly. Reduce salt intake and exercise regularly.'];
    if ($cvd_risk >= 10)    $recommendations[] = ['💓', '10-year heart disease risk is elevated. Recommend cardiology referral.'];
    if ($smoking)           $recommendations[] = ['🚭', 'Advise patient to stop smoking to significantly reduce cardiovascular risk.'];
    if ($diabetes)          $recommendations[] = ['💉', 'Monitor blood sugar levels. Ensure proper medication compliance.'];
    if ($total_chol >= 200) $recommendations[] = ['🥩', 'Advise reduction of saturated fats. Consider lipid-lowering therapy if persistent.'];
    if ($hdl_chol < 40)     $recommendations[] = ['🏃', 'Encourage regular physical activity to raise HDL cholesterol levels.'];
    if (empty($recommendations)) $recommendations[] = ['🌟', 'Patient shows good health indicators. Encourage continued healthy lifestyle.'];

    // ══ SAVE TO DB ══
    $source = 'clinic';
    $save   = $conn->prepare("
        INSERT INTO health_records
          (user_id, age, sex, height, weight, bmi, bmi_category,
           systolic_bp, diastolic_bp, bp_category,
           total_cholesterol, hdl_cholesterol,
           smoking, diabetes, bp_treatment,
           cvd_risk_percent, cvd_category,
           health_score, health_category, source, notes)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
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

    // 21 values — type string must have exactly 21 chars
    $save->bind_param("iisdddsiiisddiiidsiis",
        $uid, $a, $sex,
        $h, $w, $b, $bmi_cat,
        $sys, $dia, $bp_cat,
        $tc, $hdl,
        $sm, $db, $bpt,
        $cvd, $cvd_cat,
        $hs, $health_cat,
        $source, $notes
    );
    $save->execute();
    $save->close();

    // Fetch patient info
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $result = compact(
        'bmi','bmi_cat','bmi_color',
        'bp_cat','bp_color','systolic','diastolic',
        'cvd_risk','cvd_cat','cvd_color',
        'health_score','health_cat','health_color',
        'recommendations'
    );
}

// Get all active students for dropdown
$allStudents = $conn->query("
    SELECT user_id, full_name, student_id, grade
    FROM users
    WHERE status = 'Active'
    ORDER BY full_name ASC
");

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>🩺 Clinic Health Assessment</h1>
    <p>Nurse-verified health check for students visiting the clinic</p>
  </div>
  <a href="admin_health.php" class="btn btn-secondary">← Health Records</a>
</div>

<!-- Notice -->
<div class="alert alert-success" style="margin-bottom:24px;">
  🏥 <strong>Clinic Assessment:</strong> Results entered by the nurse are marked as
  <strong>Clinic-Verified</strong> and are considered more accurate than self-reported data.
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">

  <!-- LEFT: INPUT FORM -->
  <div class="form-card">

    <h3 style="font-family:'Playfair Display',serif;font-size:17px;margin-bottom:16px;">
      👤 Step 1 — Find Student
    </h3>

    <!-- Search by ID or Name -->
    <form method="GET" action="admin_health_check.php" style="margin-bottom:16px;">
      <div style="display:flex;gap:8px;">
        <input type="text" name="student_id"
               value="<?= htmlspecialchars($_GET['student_id'] ?? '') ?>"
               placeholder="Enter Student ID or Name..."
               style="flex:1;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--text);outline:none;">
        <button type="submit" class="btn btn-primary">🔍 Search</button>
      </div>
    </form>

    <!-- Or select from dropdown -->
    <div style="margin-bottom:20px;">
      <div style="font-size:12px;color:var(--text3);margin-bottom:8px;text-align:center;">
        — or select from list —
      </div>
      <select id="studentDropdown" onchange="selectStudent(this.value)"
              style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:13px;color:var(--text);outline:none;">
        <option value="">— Select Student —</option>
        <?php
        $allStudents->data_seek(0);
        while ($s = $allStudents->fetch_assoc()):
        ?>
          <option value="<?= htmlspecialchars($s['student_id']) ?>">
            <?= htmlspecialchars($s['full_name']) ?>
            — <?= htmlspecialchars($s['student_id']) ?>
            <?= $s['grade'] ? '('.$s['grade'].')' : '' ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error" data-auto-dismiss>
        ⚠ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
      </div>
    <?php endif; ?>

    <?php if ($patient): ?>

      <!-- Patient Found -->
      <div style="background:rgba(63,185,80,.08);border:1px solid rgba(63,185,80,.3);border-radius:8px;padding:14px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
          <div style="width:44px;height:44px;background:linear-gradient(135deg,#3fb950,#58a6ff);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">👤</div>
          <div>
            <div style="font-weight:600;font-size:15px;"><?= htmlspecialchars($patient['full_name']) ?></div>
            <div style="font-size:12px;color:var(--accent);font-family:'DM Mono',monospace;"><?= htmlspecialchars($patient['student_id']) ?></div>
            <div style="font-size:12px;color:var(--text3);">
              <?= htmlspecialchars($patient['grade'] ?: '') ?>
              <?= $patient['section'] ? '— '.htmlspecialchars($patient['section']) : '' ?>
            </div>
          </div>
          <span class="badge in-stock" style="margin-left:auto;">✅ Found</span>
        </div>
      </div>

      <!-- Step 2: Input Values -->
      <h3 style="font-family:'Playfair Display',serif;font-size:17px;margin-bottom:16px;">
        📋 Step 2 — Enter Measured Values
      </h3>

      <form method="POST" action="admin_health_check.php">
        <input type="hidden" name="user_id" value="<?= $patient['user_id'] ?>">
        <div class="form-grid">

          <div class="field">
            <label>Age *</label>
            <input type="number" name="age" min="1" max="100"
                   value="<?= htmlspecialchars($_POST['age'] ?? '') ?>"
                   placeholder="e.g. 20" required>
          </div>

          <div class="field">
            <label>Sex *</label>
            <select name="sex" style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--text);outline:none;">
              <option value="male"   <?= ($_POST['sex']??'male')==='male'  ?'selected':'' ?>>Male</option>
              <option value="female" <?= ($_POST['sex']??'')==='female'    ?'selected':'' ?>>Female</option>
            </select>
          </div>

          <div class="field">
            <label>Height (cm) *</label>
            <input type="number" name="height" min="50" max="250" step="0.1"
                   value="<?= htmlspecialchars($_POST['height'] ?? '') ?>"
                   placeholder="e.g. 165" required>
          </div>

          <div class="field">
            <label>Weight (kg) *</label>
            <input type="number" name="weight" min="10" max="300" step="0.1"
                   value="<?= htmlspecialchars($_POST['weight'] ?? '') ?>"
                   placeholder="e.g. 60" required>
          </div>

          <div class="field">
            <label>Systolic BP (mmHg) *</label>
            <input type="number" name="systolic" min="60" max="300"
                   value="<?= htmlspecialchars($_POST['systolic'] ?? '') ?>"
                   placeholder="e.g. 120" required>
          </div>

          <div class="field">
            <label>Diastolic BP (mmHg) *</label>
            <input type="number" name="diastolic" min="40" max="200"
                   value="<?= htmlspecialchars($_POST['diastolic'] ?? '') ?>"
                   placeholder="e.g. 80" required>
          </div>

          <div class="field">
            <label>Total Cholesterol (mg/dL)</label>
            <input type="number" name="total_chol" min="50" max="500" step="0.1"
                   value="<?= htmlspecialchars($_POST['total_chol'] ?? '0') ?>"
                   placeholder="e.g. 180">
          </div>

          <div class="field">
            <label>HDL Cholesterol (mg/dL)</label>
            <input type="number" name="hdl_chol" min="10" max="150" step="0.1"
                   value="<?= htmlspecialchars($_POST['hdl_chol'] ?? '0') ?>"
                   placeholder="e.g. 50">
          </div>

          <div class="field span2">
            <label>Risk Factors</label>
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px;">
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
                💊 On Blood Pressure Medication
              </label>
            </div>
          </div>

          <div class="field span2">
            <label>Nurse Notes</label>
            <textarea name="notes"
                      placeholder="Additional observations, symptoms, or recommendations..."
                      style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--text);outline:none;min-height:80px;resize:vertical;"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
          </div>

        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
          ⚡ Generate Health Assessment
        </button>
      </form>

    <?php else: ?>
      <div style="text-align:center;padding:32px;color:var(--text3);">
        <div style="font-size:40px;margin-bottom:12px;">🔍</div>
        <p>Search for a student above to begin the assessment</p>
      </div>
    <?php endif; ?>

  </div>

  <!-- RIGHT: RESULTS -->
  <div>
  <?php if ($result && $patient): ?>

    <!-- Verified Badge -->
    <div style="background:rgba(63,185,80,.08);border:1px solid rgba(63,185,80,.3);border-radius:10px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:12px;">
      <span style="font-size:24px;">🏥</span>
      <div>
        <div style="font-weight:600;color:var(--green);">Clinic-Verified Assessment</div>
        <div style="font-size:12px;color:var(--text2);">
          Patient: <strong><?= htmlspecialchars($patient['full_name']) ?></strong> —
          <?= htmlspecialchars($patient['student_id']) ?>
        </div>
      </div>
    </div>

    <!-- Overall Health Score -->
    <div class="form-card" style="margin-bottom:16px;text-align:center;">
      <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text2);margin-bottom:14px;">
        Overall Health Score
      </div>
      <div style="position:relative;width:140px;height:140px;margin:0 auto 12px;">
        <svg width="140" height="140" style="transform:rotate(-90deg)">
          <circle cx="70" cy="70" r="56" fill="none" stroke="var(--border)" stroke-width="10"/>
          <circle cx="70" cy="70" r="56" fill="none"
                  stroke="<?= $result['health_color'] ?>"
                  stroke-width="10"
                  stroke-dasharray="<?= round(2 * M_PI * 56) ?>"
                  stroke-dashoffset="<?= round(2 * M_PI * 56 * (1 - $result['health_score']/100)) ?>"
                  stroke-linecap="round"
                  id="scoreCircle"
                  style="transition:stroke-dashoffset 1.5s ease"/>
        </svg>
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
          <div style="font-family:'Playfair Display',serif;font-size:30px;font-weight:700;color:<?= $result['health_color'] ?>">
            <?= $result['health_score'] ?>
          </div>
          <div style="font-size:10px;color:var(--text3)">/ 100</div>
        </div>
      </div>
      <div style="font-size:20px;font-weight:700;color:<?= $result['health_color'] ?>">
        <?= htmlspecialchars($result['health_cat']) ?>
      </div>
    </div>

    <!-- 3 Metric Cards -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px;">
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text2);margin-bottom:6px;">BMI</div>
        <div style="font-size:24px;font-weight:700;color:<?= $result['bmi_color'] ?>;font-family:'Playfair Display',serif;"><?= $result['bmi'] ?></div>
        <div style="font-size:11px;color:<?= $result['bmi_color'] ?>;font-weight:600;"><?= htmlspecialchars($result['bmi_cat']) ?></div>
        <div style="font-size:10px;color:var(--text3);margin-top:2px;">WHO Standard</div>
      </div>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text2);margin-bottom:6px;">Blood Pressure</div>
        <div style="font-size:18px;font-weight:700;color:<?= $result['bp_color'] ?>;font-family:'DM Mono',monospace;"><?= $result['systolic'] ?>/<?= $result['diastolic'] ?></div>
        <div style="font-size:11px;color:<?= $result['bp_color'] ?>;font-weight:600;"><?= htmlspecialchars($result['bp_cat']) ?></div>
        <div style="font-size:10px;color:var(--text3);margin-top:2px;">AHA Standard</div>
      </div>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text2);margin-bottom:6px;">Heart Risk</div>
        <div style="font-size:24px;font-weight:700;color:<?= $result['cvd_color'] ?>;font-family:'Playfair Display',serif;"><?= $result['cvd_risk'] ?>%</div>
        <div style="font-size:11px;color:<?= $result['cvd_color'] ?>;font-weight:600;"><?= htmlspecialchars($result['cvd_cat']) ?></div>
        <div style="font-size:10px;color:var(--text3);margin-top:2px;">10-yr CVD Risk</div>
      </div>
    </div>

    <!-- Recommendations -->
    <div class="form-card">
      <h3 style="font-family:'Playfair Display',serif;font-size:15px;margin-bottom:14px;">
        💡 Clinical Recommendations
      </h3>
      <?php foreach ($result['recommendations'] as $rec): ?>
        <div style="display:flex;align-items:flex-start;gap:10px;padding:10px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;margin-bottom:8px;">
          <span style="font-size:18px;"><?= $rec[0] ?></span>
          <span style="font-size:12px;color:var(--text2);line-height:1.5;"><?= htmlspecialchars($rec[1]) ?></span>
        </div>
      <?php endforeach; ?>
      <div style="margin-top:12px;padding:10px;background:rgba(63,185,80,.06);border:1px solid rgba(63,185,80,.2);border-radius:8px;">
        <div style="font-size:11px;color:var(--green);">
          ✅ <strong>Clinic-verified</strong> — Recorded by
          <?= htmlspecialchars(getCurrentUser()) ?> on
          <?= date('F d, Y h:i A') ?>
        </div>
      </div>
      <div style="margin-top:12px;display:flex;gap:8px;">
        <a href="admin_health.php" class="btn btn-secondary btn-sm">← View All Records</a>
        <a href="admin_health_check.php" class="btn btn-primary btn-sm">🩺 New Assessment</a>
      </div>
    </div>

  <?php else: ?>
    <div class="form-card" style="text-align:center;padding:48px 32px;">
      <div style="font-size:64px;margin-bottom:16px;">🩺</div>
      <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px;">
        Clinic Assessment Tool
      </h3>
      <p style="color:var(--text2);font-size:14px;line-height:1.6;">
        Search for a student on the left,<br>
        enter their measured values,<br>
        and generate a <strong style="color:var(--accent)">clinic-verified</strong> health report.
      </p>
      <div style="margin-top:24px;padding:14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;text-align:left;">
        <div style="font-size:12px;color:var(--text3);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;">Why Clinic Assessment?</div>
        <div style="font-size:12px;color:var(--text2);line-height:1.8;">
          ✅ Nurse-measured values are accurate<br>
          ✅ Eliminates inaccurate self-reported data<br>
          ✅ Marked as verified in health records<br>
          ✅ Better basis for medical referrals
        </div>
      </div>
    </div>
  <?php endif; ?>
  </div>

</div>

<script>
function selectStudent(studentId) {
  if (studentId) {
    window.location.href = 'admin_health_check.php?student_id=' + encodeURIComponent(studentId);
  }
}
window.addEventListener('load', () => {
  const circle = document.getElementById('scoreCircle');
  if (circle) {
    const final = circle.getAttribute('stroke-dashoffset');
    circle.style.strokeDashoffset = circle.getAttribute('stroke-dasharray');
    setTimeout(() => { circle.style.strokeDashoffset = final; }, 100);
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>