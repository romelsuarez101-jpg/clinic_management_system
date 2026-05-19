<?php
$pageTitle  = 'Health Records';
$activePage = 'health';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

header('Cache-Control: no store, no cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Filters
$filterRisk   = $_GET['risk']   ?? 'all';
$filterSource = $_GET['source'] ?? 'all';
$search       = trim($_GET['search'] ?? '');

$conditions = [];
if ($filterRisk === 'low')      $conditions[] = "hr.cvd_category = 'Low Risk'";
if ($filterRisk === 'moderate') $conditions[] = "hr.cvd_category = 'Moderate Risk'";
if ($filterRisk === 'high')     $conditions[] = "hr.cvd_category = 'High Risk'";
if ($filterSource === 'self')   $conditions[] = "hr.source = 'self'";
if ($filterSource === 'clinic') $conditions[] = "hr.source = 'clinic'";
if ($search)                    $conditions[] = "(u.full_name LIKE '%{$search}%' OR u.student_id LIKE '%{$search}%')";

$where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

$records = $conn->query("
    SELECT hr.*, u.full_name, u.student_id, u.grade, u.section
    FROM health_records hr
    JOIN users u ON hr.user_id = u.user_id
    {$where}
    ORDER BY hr.checked_at DESC
");

// Stats
$totalRecords = $conn->query("SELECT COUNT(*) AS c FROM health_records")->fetch_assoc()['c'];
$lowRisk      = $conn->query("SELECT COUNT(*) AS c FROM health_records WHERE cvd_category='Low Risk'")->fetch_assoc()['c'];
$modRisk      = $conn->query("SELECT COUNT(*) AS c FROM health_records WHERE cvd_category='Moderate Risk'")->fetch_assoc()['c'];
$highRisk     = $conn->query("SELECT COUNT(*) AS c FROM health_records WHERE cvd_category='High Risk'")->fetch_assoc()['c'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>❤️ Health Records</h1>
    <p>View and monitor student health assessment results</p>
  </div>
  <a href="admin_health_check.php" class="btn btn-primary">🩺 New Clinic Assessment</a>
</div>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:24px;">
  <div class="stat-card blue">
    <div class="stat-label">Total Records</div>
    <div class="stat-value"><?= $totalRecords ?></div>
    <div class="stat-sub">all assessments</div>
    <div class="stat-icon">📋</div>
  </div>
  <div class="stat-card green">
    <div class="stat-label">Low Risk</div>
    <div class="stat-value"><?= $lowRisk ?></div>
    <div class="stat-sub">students</div>
    <div class="stat-icon">✅</div>
  </div>
  <div class="stat-card yellow">
    <div class="stat-label">Moderate Risk</div>
    <div class="stat-value"><?= $modRisk ?></div>
    <div class="stat-sub">students</div>
    <div class="stat-icon">⚠️</div>
  </div>
  <div class="stat-card red">
    <div class="stat-label">High Risk</div>
    <div class="stat-value"><?= $highRisk ?></div>
    <div class="stat-sub">needs attention</div>
    <div class="stat-icon">🚨</div>
  </div>
</div>

<!-- Filters -->
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
  <form method="GET" style="display:flex;gap:8px;align-items:center;flex:1;">
    <div class="search-box" style="max-width:280px;">
      <span class="search-icon">🔍</span>
      <input type="text" name="search"
             value="<?= htmlspecialchars($search) ?>"
             placeholder="Search student…"
             onchange="this.form.submit()">
    </div>
    <input type="hidden" name="risk"   value="<?= htmlspecialchars($filterRisk) ?>">
    <input type="hidden" name="source" value="<?= htmlspecialchars($filterSource) ?>">
  </form>

  <!-- Risk Filter -->
  <div style="display:flex;gap:6px;">
    <a href="?risk=all&source=<?= $filterSource ?>"
       class="btn <?= $filterRisk==='all'?'btn-primary':'btn-secondary' ?> btn-sm">All</a>
    <a href="?risk=low&source=<?= $filterSource ?>"
       class="btn btn-secondary btn-sm"
       style="color:var(--green);border-color:rgba(63,185,80,.3)">✅ Low</a>
    <a href="?risk=moderate&source=<?= $filterSource ?>"
       class="btn btn-secondary btn-sm"
       style="color:var(--yellow);border-color:rgba(227,179,65,.3)">⚠️ Moderate</a>
    <a href="?risk=high&source=<?= $filterSource ?>"
       class="btn btn-secondary btn-sm"
       style="color:var(--red);border-color:rgba(248,81,73,.3)">🚨 High</a>
  </div>

  <!-- Source Filter -->
  <div style="display:flex;gap:6px;">
    <a href="?source=all&risk=<?= $filterRisk ?>"
       class="btn <?= $filterSource==='all'?'btn-primary':'btn-secondary' ?> btn-sm">All Sources</a>
    <a href="?source=self&risk=<?= $filterRisk ?>"
       class="btn <?= $filterSource==='self'?'btn-primary':'btn-secondary' ?> btn-sm">👤 Self</a>
    <a href="?source=clinic&risk=<?= $filterRisk ?>"
       class="btn <?= $filterSource==='clinic'?'btn-primary':'btn-secondary' ?> btn-sm">🏥 Clinic</a>
  </div>
</div>

<!-- Table -->
<div class="table-card">
  <table>
    <thead><tr>
      <th>Student</th>
      <th>Age / Sex</th>
      <th>BMI</th>
      <th>Blood Pressure</th>
      <th>Heart Risk</th>
      <th>Health Score</th>
      <th>Source</th>
      <th>Date</th>
      <th>Action</th>
    </tr></thead>
    <tbody>
    <?php if ($records->num_rows > 0): while ($r = $records->fetch_assoc()):

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
    ?>
      <tr>
        <td>
          <div class="med-name"><?= htmlspecialchars($r['full_name']) ?></div>
          <div style="font-size:11px;color:var(--accent);font-family:'DM Mono',monospace">
            <?= htmlspecialchars($r['student_id']) ?>
          </div>
          <div style="font-size:11px;color:var(--text3)">
            <?= htmlspecialchars($r['grade'] ?: '') ?>
            <?= $r['section'] ? '— '.htmlspecialchars($r['section']) : '' ?>
          </div>
        </td>
        <td>
          <span style="font-weight:600"><?= $r['age'] ?></span>
          <span style="color:var(--text2);font-size:12px"> / <?= ucfirst($r['sex']) ?></span>
        </td>
        <td>
          <div style="font-weight:700;color:<?= $bmiColor ?>"><?= $r['bmi'] ?></div>
          <div style="font-size:11px;color:<?= $bmiColor ?>"><?= htmlspecialchars($r['bmi_category']) ?></div>
        </td>
        <td>
          <div style="font-family:'DM Mono',monospace;font-weight:600;color:<?= $bpColor ?>">
            <?= $r['systolic_bp'] ?>/<?= $r['diastolic_bp'] ?>
          </div>
          <div style="font-size:11px;color:<?= $bpColor ?>"><?= htmlspecialchars($r['bp_category']) ?></div>
        </td>
        <td>
          <div style="font-weight:700;color:<?= $cvdColor ?>"><?= $r['cvd_risk_percent'] ?>%</div>
          <div style="font-size:11px;color:<?= $cvdColor ?>"><?= htmlspecialchars($r['cvd_category']) ?></div>
        </td>
        <td>
          <div style="font-weight:700;font-size:18px;color:<?= $hsColor ?>"><?= $r['health_score'] ?></div>
          <div style="font-size:11px;color:<?= $hsColor ?>"><?= htmlspecialchars($r['health_category']) ?></div>
        </td>
        <td>
          <?php if ($r['source'] === 'clinic'): ?>
            <span class="badge in-stock">🏥 Clinic</span>
          <?php else: ?>
            <span class="badge cat">👤 Self</span>
          <?php endif; ?>
        </td>
        <td class="mono" style="font-size:11px;">
          <?= date('m/d/Y', strtotime($r['checked_at'])) ?>
          <div style="color:var(--text3)"><?= date('h:i A', strtotime($r['checked_at'])) ?></div>
        </td>
        <td>
          <a href="admin_health_view.php?id=<?= $r['record_id'] ?>"
             class="btn btn-view btn-sm">👁 View</a>
        </td>
      </tr>
    <?php endwhile; else: ?>
      <tr><td colspan="9">
        <div class="empty-state">
          <span class="empty-icon">❤️</span>
          <p>No health records found</p>
        </div>
      </td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>