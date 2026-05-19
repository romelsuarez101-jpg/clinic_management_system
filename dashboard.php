<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/config/session.php';
requireLogin();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
// Chart data — stock by category
$chartQuery = $conn->query("SELECT category, COUNT(*) as total, SUM(quantity) as qty FROM medicines WHERE category IS NOT NULL AND category != '' GROUP BY category ORDER BY total DESC");
$chartLabels = [];
$chartCounts = [];
$chartQtys   = [];
while ($row = $chartQuery->fetch_assoc()) {
    $chartLabels[] = $row['category'];
    $chartCounts[] = $row['total'];
    $chartQtys[]   = $row['qty'];
}
$chartLabelsJson = json_encode($chartLabels);
$chartCountsJson = json_encode($chartCounts);
$chartQtysJson   = json_encode($chartQtys);

// Status distribution for pie chart
$statusQuery = $conn->query("SELECT status, COUNT(*) as total FROM medicines GROUP BY status");
$statusLabels = [];
$statusCounts = [];
while ($row = $statusQuery->fetch_assoc()) {
    $statusLabels[] = $row['status'];
    $statusCounts[] = $row['total'];
}
$statusLabelsJson = json_encode($statusLabels);
$statusCountsJson = json_encode($statusCounts);
requireLogin();
$total   = $conn->query("SELECT COUNT(*) AS c FROM medicines")->fetch_assoc()['c'];
$inStock = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE status='In Stock'")->fetch_assoc()['c'];
$lowOut  = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE status IN ('Low Stock','Out of Stock') OR quantity <= 10")->fetch_assoc()['c'];
$expired = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE status='Expired' OR expiration_date < CURDATE()")->fetch_assoc()['c'];
$recent  = $conn->query("SELECT * FROM medicines ORDER BY medicine_id DESC LIMIT 5");
$expiring = $conn->query("SELECT * FROM medicines WHERE expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY expiration_date ASC LIMIT 5");

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div><h1>Dashboard</h1><p>Overview of the clinic inventory — <?= date('F d, Y') ?></p></div>
</div>


<div class="stats-grid">
  <div class="stat-card green"><div class="stat-label">Total Medicines</div><div class="stat-value"><?= $total ?></div><div class="stat-sub">items in inventory</div><div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24">
	<path fill="#d22614" d="m16.366 16.179l.512-.512c.916-.916 2.432-.885 3.386.07c.954.953.985 2.469.069 3.385l-.512.512a5 5 0 0 0-.168-.377a6.5 6.5 0 0 0-1.232-1.678a6.5 6.5 0 0 0-1.678-1.232a5 5 0 0 0-.377-.168m-1.226 1.226l-.473.473c-.916.916-.885 2.432.07 3.386c.953.954 2.469.985 3.385.069l.473-.473l-.1-.47l-.003-.011l-.026-.084a3 3 0 0 0-.156-.37a5 5 0 0 0-.95-1.285c-.5-.501-.962-.79-1.285-.95a3 3 0 0 0-.454-.182l-.011-.003zM6.076 2.617C6 2.801 6 3.034 6 3.5s0 .699.076.883a1 1 0 0 0 .541.54C6.801 5 7.034 5 7.5 5h9c.466 0 .699 0 .883-.076a1 1 0 0 0 .54-.541C18 4.199 18 3.966 18 3.5s0-.699-.076-.883a1 1 0 0 0-.541-.54C17.199 2 16.966 2 16.5 2h-9c-.466 0-.699 0-.883.076a1 1 0 0 0-.54.541M7.75 6L5.501 7.799a4 4 0 0 0-1.135 1.45H19.64a4 4 0 0 0-1.123-1.45L16.294 6z" />
	<path fill="#d22614" fill-rule="evenodd" d="M20 10.908q0-.079-.003-.158H4.004L4 10.922v6.328h9.234q.166-.226.373-.433l2.21-2.21c1.144-1.144 2.795-1.377 4.183-.812zm-7.25 2.342V12a.75.75 0 0 0-1.5 0v1.25H10a.75.75 0 0 0 0 1.5h1.25V16a.75.75 0 0 0 1.5 0v-1.25H14a.75.75 0 0 0 0-1.5z" clip-rule="evenodd" />
	<path fill="#d22614" d="M12.573 18.75a3.98 3.98 0 0 0 .812 3.25H8.957c-.571 0-.857 0-1.127-.037a4 4 0 0 1-2.153-1.008c-.201-.184-.384-.404-.75-.842a4 4 0 0 1-.743-1.363z" />
</svg></div></div>
  <div class="stat-card blue"><div class="stat-label">In Stock</div><div class="stat-value"><?= $inStock ?></div><div class="stat-sub">available items</div><div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24">
	<g fill="none">
		<path fill="#78eb7b" d="M1.987 13.704a1.084 1.084 0 0 0 0 1.534l5.203 5.204c.424.423 1.11.423 1.534 0l13.289-13.29a1.084 1.084 0 0 0 0-1.533l-2.06-2.06a1.084 1.084 0 0 0-1.533 0L7.957 14.022L5.58 11.644a1.085 1.085 0 0 0-1.534 0z" />
		<path fill="#c9f7ca" d="M7.957 17.167L20.76 4.365l-.809-.809a1.085 1.085 0 0 0-1.534 0L7.957 14.022L5.58 11.644a1.084 1.084 0 0 0-1.534 0l-.809.809z" />
		<path stroke="#191919" stroke-linecap="round" stroke-linejoin="round" d="M1.987 13.704a1.084 1.084 0 0 0 0 1.534l5.203 5.204c.424.423 1.11.423 1.534 0l13.289-13.29a1.084 1.084 0 0 0 0-1.533l-2.06-2.06a1.084 1.084 0 0 0-1.533 0L7.957 14.022L5.58 11.644a1.085 1.085 0 0 0-1.534 0z" stroke-width="1" />
	</g>
</svg></div></div>
  <div class="stat-card yellow"><div class="stat-label">Low / Out of Stock</div><div class="stat-value"><?= $lowOut ?></div><div class="stat-sub">need restocking</div><div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 32 32">
	<g fill="none">
		<path fill="url(#SVG7ERGldNd)" d="M12.937 3.809c1.33-2.41 4.796-2.41 6.127 0l10.494 18.999c1.288 2.333-.4 5.192-3.064 5.192H5.507c-2.665 0-4.352-2.86-3.064-5.192z" />
		<path fill="url(#SVGUJmwPdpi)" d="M17.25 22a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0M16 9a1 1 0 0 0-1 1v8a1 1 0 1 0 2 0v-8a1 1 0 0 0-1-1" />
		<defs>
			<linearGradient id="SVG7ERGldNd" x1="6.377" x2="22.707" y1="-2.061" y2="31.433" gradientUnits="userSpaceOnUse">
				<stop stop-color="#ffcd0f" />
				<stop offset="1" stop-color="#fe8401" />
			</linearGradient>
			<linearGradient id="SVGUJmwPdpi" x1="12.666" x2="20.071" y1="9" y2="22.856" gradientUnits="userSpaceOnUse">
				<stop stop-color="#4a4a4a" />
				<stop offset="1" stop-color="#212121" />
			</linearGradient>
		</defs>
	</g>
</svg></div></div>
  <div class="stat-card red"><div class="stat-label">Expired</div><div class="stat-value"><?= $expired ?></div><div class="stat-sub">to be disposed</div><div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48">
	<circle cx="17" cy="17" r="14" fill="#00acc1" />
	<circle cx="17" cy="17" r="11" fill="#eee" />
	<path d="M16 8h2v9h-2z" />
	<path d="m22.655 20.954l-1.697 1.697l-4.808-4.807l1.697-1.697z" />
	<circle cx="17" cy="17" r="2" />
	<circle cx="17" cy="17" r="1" fill="#00acc1" />
	<path fill="#db1d1d" d="m11.9 42l14.4-24.1c.8-1.3 2.7-1.3 3.4 0L44.1 42c.8 1.3-.2 3-1.7 3H13.6c-1.5 0-2.5-1.7-1.7-3" />
	<path fill="#000" d="M26.4 39.9c0-.2 0-.4.1-.6s.2-.3.3-.5s.3-.2.5-.3s.4-.1.6-.1s.5 0 .7.1s.4.2.5.3s.2.3.3.5s.1.4.1.6s0 .4-.1.6s-.2.3-.3.5s-.3.2-.5.3s-.4.1-.7.1s-.5 0-.6-.1s-.4-.2-.5-.3s-.2-.3-.3-.5s-.1-.4-.1-.6m2.8-3.1h-2.3l-.4-9.8h3z" />
</svg></div></div>
</div>

<div class="table-card" style="margin-bottom:24px">
  <div class="table-card-header">
    <div><h3>Recent Entries</h3><p>Last 5 medicines added</p></div>
    <a href="medicines.php" class="btn btn-secondary btn-sm">View All →</a>
  </div>
  <table><thead><tr><th>ID</th><th>Medicine Name</th><th>Category</th><th>Qty</th><th>Expiration</th><th>Status</th></tr></thead>
  <tbody>
  <?php if ($recent->num_rows > 0): while ($m = $recent->fetch_assoc()): ?>
  <tr>
    <td><span class="med-id"><?= medId($m['medicine_id']) ?></span></td>
    <td><div class="med-name"><?= htmlspecialchars($m['medicine_name']) ?></div></td>
    <td><span class="badge cat"><?= htmlspecialchars($m['category'] ?: '—') ?></span></td>
    <td><span class="qty-num"><?= $m['quantity'] ?></span> <?= htmlspecialchars($m['unit'] ?: '') ?></td>
    <td class="mono"><?= fmtDate($m['expiration_date']) ?></td>
    <td><span class="badge <?= statusClass($m['status']) ?>"><?= htmlspecialchars($m['status']) ?></span></td>
  </tr>
  <?php endwhile; else: ?>
  <tr><td colspan="6"><div class="empty-state"><span class="empty-icon">📭</span><p>No medicines yet</p></div></td></tr>
  <?php endif; ?>
  </tbody></table>
</div>

<!-- CHARTS ROW -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;">

  <!-- Bar Chart -->
  <div class="table-card" style="padding:20px;">
    <div style="margin-bottom:14px;">
      <div style="font-weight:600;font-size:14px;"><svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 48 48">
	<g fill="#1922e1">
		<path d="M27.707 6.293a1 1 0 0 1 0 1.414L25.414 10l2.293 2.293a1 1 0 0 1-1.414 1.414L24 11.414l-2.293 2.293a1 1 0 1 1-1.414-1.415L22.586 10l-2.293-2.293a1 1 0 1 1 1.415-1.414L24 8.586l2.293-2.293a1 1 0 0 1 1.414 0m-8.509 26.435A3.5 3.5 0 0 0 23 31.64v10.383L11 37.5v-7.504z" />
		<path fill-rule="evenodd" d="m37 37.5l-12 4.523V31.64a3.5 3.5 0 0 0 3.802 1.088L37 29.996zm-3.684-2.051a1 1 0 0 0-.632-1.898l-4.5 1.5a1 1 0 0 0 .632 1.898zm-8.989-20.394a1 1 0 0 0-.654 0l-12.998 4.5l-.023.007a1 1 0 0 0-.442.325l-3.99 4.988a1 1 0 0 0 .464 1.574l13.5 4.5a1 1 0 0 0 1.135-.376L24 26.743l2.68 3.83a1 1 0 0 0 1.136.376l13.5-4.5a1 1 0 0 0 .465-1.574l-3.99-4.988a1 1 0 0 0-.466-.333zM24 23.942l9.943-3.442L24 17.058L14.057 20.5z" clip-rule="evenodd" />
	</g>
</svg> Stock Quantity by Category</div>
      <div style="font-size:12px;color:var(--text3);margin-top:3px;">Total units per category</div>
    </div>
    <div style="position:relative;height:200px;">
      <canvas id="barChart"></canvas>
    </div>
  </div>

  <!-- Pie Chart -->
  <div class="table-card" style="padding:20px;">
    <div style="margin-bottom:14px;">
      <div style="font-weight:600;font-size:14px;"><svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 48 48">
	<g fill="none">
		<path fill="#deeeff" d="M24 47.997c13.255 0 24-10.745 24-24c0-13.254-10.745-24-24-24s-24 10.746-24 24c0 13.255 10.745 24 24 24" />
		<path fill="#2e3ecd" d="M32.727 18.76a3.49 3.49 0 0 0-3.491-3.49H18.763a3.49 3.49 0 0 0-3.49 3.49v15.71a3.49 3.49 0 0 0 3.49 3.49h10.473a3.49 3.49 0 0 0 3.49-3.49z" />
		<path fill="#6bafff" fill-rule="evenodd" d="M32.727 20.506H15.273v-1.309h17.454zM15.273 32.724h17.454v1.31H15.273z" clip-rule="evenodd" />
		<path fill="#6bafff" d="M32.727 14.397c0 .482-.39.873-.873.873H16.145a.87.87 0 0 1-.873-.873v-3.49c0-.483.391-.873.873-.873h15.71c.481 0 .872.39.872.872zM28.8 24.616H26v-2.8h-4v2.8h-2.8v4H22v2.8h4v-2.8h2.8z" />
	</g>
</svg> Medicine Status Distribution</div>
      <div style="font-size:12px;color:var(--text3);margin-top:3px;">Breakdown by current status</div>
    </div>
    <div style="position:relative;height:200px;">
      <canvas id="pieChart"></canvas>
    </div>
  </div>
</div>

<div class="table-card">
  <div class="table-card-header">
    <div><h3><svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 16 16">
	<path fill="#d5161d" fill-rule="evenodd" d="M8.175.002a8 8 0 1 0 2.309 15.603a.75.75 0 0 0-.466-1.426a6.5 6.5 0 1 1 3.996-8.646a.75.75 0 0 0 1.388-.569A8 8 0 0 0 8.175.002M8.75 3.75a.75.75 0 0 0-1.5 0v3.94L5.216 9.723a.75.75 0 1 0 1.06 1.06L8.53 8.53l.22-.22zM15 15a1 1 0 1 1-2 0a1 1 0 0 1 2 0m-.25-6.25a.75.75 0 0 0-1.5 0v3.5a.75.75 0 0 0 1.5 0z" clip-rule="evenodd" />
</svg> Expiring Within 30 Days</h3><p>Medicines that need attention soon</p></div>
    <a href="expiring.php" class="btn btn-secondary btn-sm">View All →</a>
  </div>
  <table><thead><tr><th>Medicine</th><th>Category</th><th>Qty</th><th>Expiration</th><th>Days Left</th></tr></thead>
  <tbody>
  <?php if ($expiring->num_rows > 0): while ($m = $expiring->fetch_assoc()):
    $days = daysLeft($m['expiration_date']);
    $cls  = $days < 7 ? 'days-critical' : ($days < 15 ? 'days-warning' : 'days-ok');
  ?>
  <tr>
    <td><div class="med-name"><?= htmlspecialchars($m['medicine_name']) ?></div></td>
    <td><span class="badge cat"><?= htmlspecialchars($m['category'] ?: '—') ?></span></td>
    <td><?= $m['quantity'] ?> <?= htmlspecialchars($m['unit'] ?: '') ?></td>
    <td class="mono"><?= fmtDate($m['expiration_date']) ?></td>
    <td><span class="<?= $cls ?>"><?= $days <= 0 ? 'Expired' : "In {$days} days" ?></span></td>
  </tr>
  <?php endwhile; else: ?>
  <tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 24 24">
	<g fill="none">
		<path fill="#78eb7b" d="M1.987 13.704a1.084 1.084 0 0 0 0 1.534l5.203 5.204c.424.423 1.11.423 1.534 0l13.289-13.29a1.084 1.084 0 0 0 0-1.533l-2.06-2.06a1.084 1.084 0 0 0-1.533 0L7.957 14.022L5.58 11.644a1.085 1.085 0 0 0-1.534 0z" />
		<path fill="#c9f7ca" d="M7.957 17.167L20.76 4.365l-.809-.809a1.085 1.085 0 0 0-1.534 0L7.957 14.022L5.58 11.644a1.084 1.084 0 0 0-1.534 0l-.809.809z" />
		<path stroke="#191919" stroke-linecap="round" stroke-linejoin="round" d="M1.987 13.704a1.084 1.084 0 0 0 0 1.534l5.203 5.204c.424.423 1.11.423 1.534 0l13.289-13.29a1.084 1.084 0 0 0 0-1.533l-2.06-2.06a1.084 1.084 0 0 0-1.533 0L7.957 14.022L5.58 11.644a1.085 1.085 0 0 0-1.534 0z" stroke-width="1" />
	</g>
</svg></span><p>No medicines expiring soon</p></div></td></tr>
  <?php endif; ?>
  </tbody></table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── BAR CHART ──
const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
  type: 'bar',
  data: {
    labels: <?= $chartLabelsJson ?>,
    datasets: [{
      label: 'Total Units',
      data: <?= $chartQtysJson ?>,
      backgroundColor: [
        'rgba(63,185,80,0.75)',
        'rgba(88,166,255,0.75)',
        'rgba(227,179,65,0.75)',
        'rgba(248,81,73,0.75)',
        'rgba(188,140,255,0.75)',
        'rgba(247,129,102,0.75)',
        'rgba(63,185,80,0.5)',
        'rgba(88,166,255,0.5)',
        'rgba(227,179,65,0.5)',
      ],
      borderColor: [
        '#3fb950','#58a6ff','#e3b341','#f85149',
        '#bc8cff','#f78166','#3fb950','#58a6ff','#e3b341',
      ],
      borderWidth: 1,
      borderRadius: 8,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
      x: { duration: 0 },
      y: {
        duration: 1000,
        from: 500,
        easing: 'easeOutElastic',
      },
    },
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#1c2333',
        borderColor: '#30363d',
        borderWidth: 1,
        titleColor: '#e6edf3',
        bodyColor: '#8b949e',
        padding: 10,
      }
    },
    scales: {
      x: {
        ticks: { color: '#8b949e', font: { size: 10 } },
        grid:  { color: 'rgba(48,54,61,0.4)' }
      },
      y: {
        ticks: { color: '#8b949e', font: { size: 10 } },
        grid:  { color: 'rgba(48,54,61,0.4)' },
        beginAtZero: true
      }
    }
  }
});

// ── DOUGHNUT CHART ──
const pieCtx = document.getElementById('pieChart').getContext('2d');
const statusColors = {
  'In Stock':     '#3fb950',
  'Low Stock':    '#e3b341',
  'Out of Stock': '#f85149',
  'Expired':      '#bc8cff',
};
const statusLabels = <?= $statusLabelsJson ?>;
const pieColors    = statusLabels.map(l => statusColors[l] || '#58a6ff');

new Chart(pieCtx, {
  type: 'doughnut',
  data: {
    labels: statusLabels,
    datasets: [{
      data: <?= $statusCountsJson ?>,
      backgroundColor: pieColors.map(c => c + 'cc'),
      borderColor: pieColors,
      borderWidth: 2,
      hoverOffset: 10,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
      animateRotate: true,
      animateScale: true,
      duration: 1500,
      easing: 'easeInOutCirc',
    },
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          color: '#8b949e',
          font: { size: 11 },
          padding: 14,
          usePointStyle: true,
          pointStyleWidth: 10,
        }
      },
      tooltip: {
        backgroundColor: '#1c2333',
        borderColor: '#30363d',
        borderWidth: 1,
        titleColor: '#e6edf3',
        bodyColor: '#8b949e',
        padding: 10,
        callbacks: {
          label: function(context) {
            const total = context.dataset.data.reduce((a, b) => a + b, 0);
            const pct   = Math.round((context.parsed / total) * 100);
            return ` ${context.parsed} medicines (${pct}%)`;
          }
        }
      }
    },
    cutout: '62%',
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
