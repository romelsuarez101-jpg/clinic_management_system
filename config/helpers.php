<?php
// ═══════════════════════════════════════
//  helpers.php — Utility functions
// ═══════════════════════════════════════

/**
 * Sanitize a string input
 */
function clean(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

/**
 * Return the badge CSS class for a given status
 */
function statusClass(string $s): string {
    return match($s) {
        'In Stock'     => 'in-stock',
        'Low Stock'    => 'low-stock',
        'Out of Stock' => 'out-stock',
        'Expired'      => 'expired',
        default        => ''
    };
}

/**
 * Format a Y-m-d date to m/d/Y, or return '—'
 */
function fmtDate(?string $d): string {
    if (!$d) return '—';
    return date('m/d/Y', strtotime($d));
}

/**
 * Format a Y-m-d date to a long readable format
 */
function fmtDateLong(?string $d): string {
    if (!$d) return '—';
    return date('F d, Y', strtotime($d));
}

/**
 * Calculate days left until a date (negative = already passed)
 */
function daysLeft(?string $dateStr): ?int {
    if (!$dateStr) return null;
    return (int) ceil((strtotime($dateStr) - time()) / 86400);
}

/**
 * Return a color variable string based on days left
 */
function daysColor(?int $days): string {
    if ($days === null) return 'var(--text3)';
    if ($days < 0)  return 'var(--red)';
    if ($days < 7)  return 'var(--red)';
    if ($days < 30) return 'var(--yellow)';
    return 'var(--text2)';
}

/**
 * Return a color for quantity display
 */
function qtyColor(int $qty): string {
    if ($qty === 0)  return 'var(--red)';
    if ($qty <= 10) return 'var(--yellow)';
    return 'var(--text)';
}

/**
 * Zero-pad the medicine ID for display
 */
function medId(int $id): string {
    return '#' . str_pad($id, 3, '0', STR_PAD_LEFT);
}

/**
 * Set a flash message
 */
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/**
 * Redirect helper
 */
function redirect(string $url): void {
    header("Location: $url");
    exit();
}

/**
 * Build a category <select> options string
 */
function categoryOptions(string $selected = ''): string {
    $cats = [
        'Analgesic', 'Antibiotic', 'Antihistamine', 'Antacid',
        'Antiseptic', 'Vitamin/Supplement', 'First Aid', 'Decongestant', 'Other'
    ];
    $html = '<option value="">— Select Category —</option>';
    foreach ($cats as $c) {
        $sel   = $selected === $c ? ' selected' : '';
        $html .= "<option{$sel}>" . htmlspecialchars($c) . "</option>";
    }
    return $html;
}

/**
 * Build a unit <select> options string
 */
function unitOptions(string $selected = ''): string {
    $units = ['tablets','capsules','bottles','sachets','ampules','pieces','boxes','packs'];
    $html  = '<option value="">— Select Unit —</option>';
    foreach ($units as $u) {
        $sel   = $selected === $u ? ' selected' : '';
        $html .= "<option{$sel}>" . htmlspecialchars($u) . "</option>";
    }
    return $html;
}

/**
 * Build a status <select> options string
 */
function statusOptions(string $selected = 'In Stock'): string {
    $statuses = ['In Stock','Low Stock','Out of Stock','Expired'];
    $html     = '';
    foreach ($statuses as $s) {
        $sel   = $selected === $s ? ' selected' : '';
        $html .= "<option{$sel}>" . htmlspecialchars($s) . "</option>";
    }
    return $html;
}
?>
