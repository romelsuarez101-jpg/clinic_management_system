<?php
$pageTitle  = 'Health Record Detail';
$activePage = 'health';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

header('Cache-Control: no store, no cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: admin_health.php'); exit(); }

// Fetch record with student info
$stmt = $conn->prepare("
    SELECT hr.*, u.full_name, u.student_id, u.grade, u.section, u.email
    FROM health_records hr
    JOIN users u ON hr.user_id = u.user_id
    WHERE hr.record_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$r) { header('Location: admin_health.php'); exit(); }

// Colors
$bmiColor = match($r['bmi_category']) {
    'Normal'      => 'var(--green)',
    'Underweight' => 'var(--blue)',
    'Overweight'  => 'var(--yellow)',
    'Obese'       => 'var(--red)',
    default       => 'var(--text2)'
};
$cvdColor = match(true) {
    str_contains($r['cvd_category'], 'Low')      => 'var(--green)',
    str_contains($r['cvd_category'], 'Moderate') => 'var(--yellow)',
    str_contains($r['cvd_category'], 'High')     => 'var(--red)',
    default                                       => 'var(--text2)'
};
$bpColor = match(true) {
    str_contains($r['bp_category'], 'Normal')   => 'var(--green)',
    str_contains($r['bp_category'], 'Elevated') => 'var(--yellow)',
    str_contains($r['bp_category'], 'Stage 1')  => 'var(--yellow)',
    str_contains($r['bp_category'], 'Stage 2')  => 'var(--red)',
    str_contains($r['bp_category'], 'Crisis')   => 'var(--red)',
    default => 'var(--text2)'
};
$hsColor = $r['health_score'] >= 80 ? 'var(--green)' :
          ($r['health_score'] >= 60 ? 'var(--blue)'  :
          ($r['health_score'] >= 40 ? 'var(--yellow)': 'var(--red)'));

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>❤️ Health Record Detail</h1>
    <p>
      Record #<?= str_pad($r['record_id'],3,'0',STR_PAD_LEFT) ?> —
      <?= htmlspecialchars($r['full_name']) ?>
    </p>
  </div>
  <div style="display:flex;gap:8px;">
    <a href="admin_health_check.php?student_id=<?= htmlspecialchars($r['student_id']) ?>"
       class="btn btn-edit btn-sm">🩺 New Assessment</a>
    <a href="admin_health.php" class="btn btn-secondary">← Back</a>
  </div>
</div>

<!-- Source Badge -->
<div style="margin-bottom:24px;">
  <?php if ($r['source'] === 'clinic'): ?>
    <div style="display:inline-flex;align-items:center;gap:10px;background:rgba(63,185,80,.08);border:1px solid rgba(63,185,80,.3);border-radius:8px;padding:10px 16px;">
      <span style="font-size:20px;">🏥</span>
      <div>
        <div style="font-weight:600;color:var(--green);font-size:14px;">Clinic-Verified Assessment</div>
        <div style="font-size:12px;color:var(--text2);">Measured and recorded by clinic nurse</div>
      </div>
    </div>
  <?php else: ?>
    <div style="display:inline-flex;align-items:center;gap:10px;background:rgba(88,166,255,.08);border:1px solid rgba(88,166,255,.3);border-radius:8px;padding:10px 16px;">
      <span style="font-size:20px;">👤</span>
      <div>
        <div style="font-weight:600;color:var(--blue);font-size:14px;">Self-Reported Assessment</div>
        <div style="font-size:12px;color:var(--text2);">Data entered by student — may not be fully accurate</div>
      </div>
    </div>
  <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

  <!-- LEFT COLUMN -->
  <div>

    <!-- Student Info -->
    <div class="form-card" style="margin-bottom:20px;">
      <h3 style="font-family:'Playfair Display',serif;font-size:16px;margin-bottom:16px;">
        👤 Student Information
      </h3>
      <div style="display:flex;align-items:center;gap:14px;padding:14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;margin-bottom:16px;">
        <div style="width:48px;height:48px;background:linear-gradient(135deg,#3fb950,#58a6ff);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">👤</div>
        <div>
          <div style="font-weight:600;font-size:16px;"><?= htmlspecialchars($r['full_name']) ?></div>
          <div style="font-size:12px;color:var(--accent);font-family:'DM Mono',monospace;"><?= htmlspecialchars($r['student_id']) ?></div>
          <div style="font-size:12px;color:var(--text3);">
            <?= htmlspecialchars($r['grade'] ?: '') ?>
            <?= $r['section'] ? '— '.htmlspecialchars($r['section']) : '' ?>
          </div>
        </div>
      </div>
      <div class="detail-grid">
        <div class="detail-item">
          <div class="detail-label">Age</div>
          <div class="detail-value"><?= $r['age'] ?> years old</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Sex</div>
          <div class="detail-value"><?= ucfirst($r['sex']) ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Height</div>
          <div class="detail-value"><?= $r['height'] ?> cm</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Weight</div>
          <div class="detail-value"><?= $r['weight'] ?> kg</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Smoker</div>
          <div class="detail-value"><?= $r['smoking'] ? '🚬 Yes' : '✅ No' ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Diabetes</div>
          <div class="detail-value"><?= $r['diabetes'] ? '💉 Yes' : '✅ No' ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">BP Medication</div>
          <div class="detail-value"><?= $r['bp_treatment'] ? '💊 Yes' : '✅ No' ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Date Assessed</div>
          <div class="detail-value"><?= date('M d, Y h:i A', strtotime($r['checked_at'])) ?></div>
        </div>
      </div>
    </div>

    <!-- Nurse Notes -->
    <?php if (!empty($r['notes'])): ?>
    <div class="form-card">
      <h3 style="font-family:'Playfair Display',serif;font-size:16px;margin-bottom:12px;">
        📝 Nurse Notes
      </h3>
      <div style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:14px;font-size:14px;color:var(--text2);line-height:1.6;">
        <?= htmlspecialchars($r['notes']) ?>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <!-- RIGHT COLUMN -->
  <div>

    <!-- Overall Health Score -->
    <div class="form-card" style="margin-bottom:20px;text-align:center;">
      <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text2);margin-bottom:16px;">
        Overall Health Score
      </div>
      <div style="position:relative;width:150px;height:150px;margin:0 auto 14px;">
        <svg width="150" height="150" style="transform:rotate(-90deg)">
          <circle cx="75" cy="75" r="60" fill="none" stroke="var(--border)" stroke-width="10"/>
          <circle cx="75" cy="75" r="60" fill="none"
                  stroke="<?= $hsColor ?>"
                  stroke-width="10"
                  stroke-dasharray="<?= round(2 * M_PI * 60) ?>"
                  stroke-dashoffset="<?= round(2 * M_PI * 60 * (1 - $r['health_score']/100)) ?>"
                  stroke-linecap="round"
                  id="scoreCircle"
                  style="transition:stroke-dashoffset 1.5s ease"/>
        </svg>
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
          <div style="font-family:'Playfair Display',serif;font-size:34px;font-weight:700;color:<?= $hsColor ?>">
            <?= $r['health_score'] ?>
          </div>
          <div style="font-size:11px;color:var(--text3)">/ 100</div>
        </div>
      </div>
      <div style="font-size:22px;font-weight:700;color:<?= $hsColor ?>">
        <?= htmlspecialchars($r['health_category']) ?>
      </div>
    </div>

    <!-- 3 Metric Cards -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:20px;">
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text2);margin-bottom:6px;">BMI</div>
        <div style="font-size:26px;font-weight:700;color:<?= $bmiColor ?>;font-family:'Playfair Display',serif;"><?= $r['bmi'] ?></div>
        <div style="font-size:11px;color:<?= $bmiColor ?>;font-weight:600;"><?= htmlspecialchars($r['bmi_category']) ?></div>
        <div style="font-size:10px;color:var(--text3);margin-top:2px;">WHO</div>
      </div>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text2);margin-bottom:6px;">Blood Pressure</div>
        <div style="font-size:18px;font-weight:700;color:<?= $bpColor ?>;font-family:'DM Mono',monospace;"><?= $r['systolic_bp'] ?>/<?= $r['diastolic_bp'] ?></div>
        <div style="font-size:11px;color:<?= $bpColor ?>;font-weight:600;"><?= htmlspecialchars($r['bp_category']) ?></div>
        <div style="font-size:10px;color:var(--text3);margin-top:2px;">AHA</div>
      </div>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text2);margin-bottom:6px;">Heart Risk</div>
        <div style="font-size:26px;font-weight:700;color:<?= $cvdColor ?>;font-family:'Playfair Display',serif;"><?= $r['cvd_risk_percent'] ?>%</div>
        <div style="font-size:11px;color:<?= $cvdColor ?>;font-weight:600;"><?= htmlspecialchars($r['cvd_category']) ?></div>
        <div style="font-size:10px;color:var(--text3);margin-top:2px;">Framingham</div>
      </div>
    </div>

    <!-- Cholesterol -->
    <div class="form-card" style="margin-bottom:20px;">
      <h3 style="font-family:'Playfair Display',serif;font-size:15px;margin-bottom:14px;">
        🩸 Cholesterol Values
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:14px;text-align:center;">
          <div style="font-size:11px;color:var(--text2);margin-bottom:6px;">Total Cholesterol</div>
          <div style="font-size:22px;font-weight:700;color:<?= $r['total_cholesterol'] >= 200 ? 'var(--yellow)' : 'var(--green)' ?>">
            <?= $r['total_cholesterol'] ?>
          </div>
          <div style="font-size:10px;color:var(--text3)">mg/dL</div>
        </div>
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:14px;text-align:center;">
          <div style="font-size:11px;color:var(--text2);margin-bottom:6px;">HDL Cholesterol</div>
          <div style="font-size:22px;font-weight:700;color:<?= $r['hdl_cholesterol'] >= 60 ? 'var(--green)' : ($r['hdl_cholesterol'] >= 40 ? 'var(--yellow)' : 'var(--red)') ?>">
            <?= $r['hdl_cholesterol'] ?>
          </div>
          <div style="font-size:10px;color:var(--text3)">mg/dL</div>
        </div>
      </div>
    </div>

    <!-- Sources -->
    <div style="padding:12px;background:rgba(88,166,255,.06);border:1px solid rgba(88,166,255,.2);border-radius:8px;">
      <div style="font-size:11px;color:var(--blue);">
        ℹ Based on: <strong>Framingham Heart Study</strong> (Wilson et al., Circulation 1998) &
        <strong>WHO BMI Classification</strong> & <strong>AHA Blood Pressure Guidelines 2017</strong>
      </div>
    </div>

  </div>
</div>

<script>
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