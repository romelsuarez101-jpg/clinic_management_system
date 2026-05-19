/* ═══════════════════════════════════
   MedVault — main.js
   ═══════════════════════════════════ */

/* ── TOAST NOTIFICATIONS ── */
let toastTimer;
function showToast(msg, type = 'info') {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className = `toast show toast-${type}`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.className = 'toast'; }, 3500);
}

document.addEventListener('DOMContentLoaded', () => {

  /* ── AUTO-DISMISS ALERTS ── */
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(a => {
    setTimeout(() => {
      a.style.transition = 'opacity .5s, max-height .5s';
      a.style.opacity = '0';
      a.style.maxHeight = '0';
      a.style.padding = '0';
      a.style.margin = '0';
      setTimeout(() => a.remove(), 500);
    }, 4500);
  });

  /* ── CONFIRM DELETE ── */
  document.querySelectorAll('.delete-confirm').forEach(btn => {
    btn.addEventListener('click', e => {
      const name = btn.dataset.name || 'this item';
      if (!confirm(`⚠ Delete "${name}"?\n\nThis action cannot be undone.`)) {
        e.preventDefault();
      }
    });
  });

  /* ── LIVE SEARCH + FILTER ── */
  const searchInput  = document.getElementById('search-input');
  const statusFilter = document.getElementById('filter-status');
  const catFilter    = document.getElementById('filter-category');

  if (searchInput)  searchInput.addEventListener('input', filterTable);
  if (statusFilter) statusFilter.addEventListener('change', filterTable);
  if (catFilter)    catFilter.addEventListener('change', filterTable);

  /* ── HIGHLIGHT SEARCH MATCH ── */
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.toLowerCase();
      document.querySelectorAll('#medicine-table tbody tr[data-name] .med-name').forEach(cell => {
        const text = cell.textContent;
        if (!q) { cell.innerHTML = text; return; }
        const regex = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        cell.innerHTML = text.replace(regex, '<mark style="background:rgba(63,185,80,.3);color:var(--text);border-radius:3px;padding:0 2px">$1</mark>');
      });
    });
  }

  /* ── FORM VALIDATION FEEDBACK ── */
  document.querySelectorAll('form input[required], form select[required]').forEach(input => {
    input.addEventListener('blur', () => {
      if (input.value.trim() === '') {
        input.style.borderColor = 'var(--red)';
      } else {
        input.style.borderColor = 'var(--accent)';
        setTimeout(() => { input.style.borderColor = ''; }, 1500);
      }
    });
    input.addEventListener('input', () => {
      if (input.value.trim() !== '') input.style.borderColor = '';
    });
  });

  /* ── AUTO STATUS SUGGESTION based on qty input ── */
  const qtyInput    = document.getElementById('qty-input') || document.querySelector('input[name="quantity"]');
  const statusSelect = document.querySelector('select[name="status"]');
  if (qtyInput && statusSelect) {
    qtyInput.addEventListener('input', () => {
      const val = parseInt(qtyInput.value);
      if (isNaN(val)) return;
      if (val === 0) {
        statusSelect.value = 'Out of Stock';
        showStatusHint('Auto-set to Out of Stock');
      } else if (val <= 10) {
        statusSelect.value = 'Low Stock';
        showStatusHint('Auto-set to Low Stock');
      } else {
        if (statusSelect.value === 'Out of Stock' || statusSelect.value === 'Low Stock') {
          statusSelect.value = 'In Stock';
          showStatusHint('Auto-set to In Stock');
        }
      }
    });
  }

  function showStatusHint(msg) {
    let hint = document.getElementById('status-hint');
    if (!hint) {
      hint = document.createElement('div');
      hint.id = 'status-hint';
      hint.style.cssText = 'font-size:11px;color:var(--accent);margin-top:5px;transition:opacity .3s';
      statusSelect?.parentElement?.appendChild(hint);
    }
    hint.textContent = '✓ ' + msg;
    hint.style.opacity = '1';
    clearTimeout(hint._timer);
    hint._timer = setTimeout(() => { hint.style.opacity = '0'; }, 2500);
  }

});

/* ── TABLE FILTER FUNCTION ── */
function filterTable() {
  const q   = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
  const fs  = document.getElementById('filter-status')?.value   || '';
  const fc  = document.getElementById('filter-category')?.value || '';
  const rows = document.querySelectorAll('#medicine-table tbody tr[data-name]');
  let visible = 0;

  rows.forEach(row => {
    const name   = (row.dataset.name     || '').toLowerCase();
    const status = (row.dataset.status   || '');
    const cat    = (row.dataset.category || '');
    const show   = (!q  || name.includes(q))
                && (!fs || status === fs)
                && (!fc || cat === fc);
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  const emptyRow = document.getElementById('empty-row');
  if (emptyRow) emptyRow.style.display = visible === 0 ? '' : 'none';

  // Update result count
  const counter = document.getElementById('result-count');
  if (counter) counter.textContent = `${visible} result${visible !== 1 ? 's' : ''}`;
}
