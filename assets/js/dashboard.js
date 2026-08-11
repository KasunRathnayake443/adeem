// ADEEM UNIFORM QC Dashboard — chart rendering + click-to-expand data views

const LINE_COLORS = {
  'Line 01': '#23415C',
  'Line 02': '#3F7D5C',
  'Line 03': '#E2A63B',
  'Line 04': '#C1443B',
};
const FALLBACK_COLORS = ['#23415C', '#3F7D5C', '#E2A63B', '#C1443B', '#7A5CB0'];
function colorFor(name, i) { return LINE_COLORS[name] || FALLBACK_COLORS[i % FALLBACK_COLORS.length]; }

const MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];

async function getJSON(url) {
  const res = await fetch(url);
  const text = await res.text();
  let data;
  try { data = JSON.parse(text); }
  catch (e) { throw new Error(`${url} did not return valid JSON (got: ${text.slice(0, 200)})`); }
  if (!res.ok) throw new Error(data.error || `${url} failed with ${res.status}`);
  return data;
}

function showPanelError(canvasId, message) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const wrap = canvas.closest('.panel-canvas-wrap') || canvas.parentElement;
  wrap.innerHTML = `<p class="empty-state">${message}</p>`;
}

// Chart.js may fail to load (blocked CDN, offline, etc.) — don't let that
// take down the KPI cards and table, which don't depend on it.
const chartJsReady = typeof Chart !== 'undefined';
if (chartJsReady) {
  Chart.defaults.font.family = "'Inter', sans-serif";
  Chart.defaults.font.size = 12;
  Chart.defaults.color = '#5B6A72';
} else {
  console.error('Chart.js did not load — charts will be skipped. Check your internet connection / CDN access to cdnjs.cloudflare.com, or view browser console for the network error.');
}

// ---------------------------------------------------------------- KPI strip
async function loadKpis() {
  try {
    const d = await getJSON('api/chart_data.php?action=kpi_summary');
    document.getElementById('kpiPassPct').textContent = d.latest_pass_pct !== null ? d.latest_pass_pct + '%' : '—';
    document.getElementById('kpiPassPct').className = 'qc-tag__value ' + (d.latest_pass_pct >= 95 ? 'is-good' : d.latest_pass_pct < 90 ? 'is-bad' : '');
    document.getElementById('kpiPassDate').textContent = d.latest_date || 'No data yet';
    document.getElementById('kpiChecked').textContent = d.latest_check_qty ? Number(d.latest_check_qty).toLocaleString() : '—';
    document.getElementById('kpiOql').textContent = d.oql !== null ? d.oql + '%' : '—';
    document.getElementById('kpiOqlMonth').textContent = d.month_name || 'No data yet';
    document.getElementById('kpiFtpr').textContent = d.first_time_pass !== null ? d.first_time_pass + '%' : '—';
    document.getElementById('kpiDefects').textContent = d.defects !== null ? d.defects : '—';
  } catch (e) {
    console.error('loadKpis failed:', e);
    document.querySelectorAll('#kpiRow .qc-tag__value').forEach(el => el.textContent = 'Error');
  }
}

// ---------------------------------------------------------------- Chart 1: daily trend
let trendChart;
async function loadTrend() {
  if (!chartJsReady) return showPanelError('chartTrend', 'Chart library failed to load.');
  try {
    const d = await getJSON('api/chart_data.php?action=daily_trend&days=20');
    if (!d.labels.length) return showPanelError('chartTrend', 'No daily KPI reports yet — add one to see this chart.');
    const datasets = d.datasets.map((s, i) => ({
      label: s.line,
      data: s.data,
      borderColor: colorFor(s.line, i),
      backgroundColor: colorFor(s.line, i),
      tension: 0.3,
      spanGaps: true,
      pointRadius: 2,
    }));
    if (trendChart) trendChart.destroy();
    trendChart = new Chart(document.getElementById('chartTrend'), {
      type: 'line',
      data: { labels: d.labels, datasets },
      options: {
        maintainAspectRatio: false,
        scales: { y: { min: 80, max: 100, ticks: { callback: v => v + '%' } } },
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } } },
      }
    });
  } catch (e) {
    console.error('loadTrend failed:', e);
    showPanelError('chartTrend', 'Could not load this chart — see browser console for details.');
  }
}

// ---------------------------------------------------------------- Chart 2: defects by category
let defectsChart;
async function loadDefects(month) {
  if (!chartJsReady) return showPanelError('chartDefects', 'Chart library failed to load.');
  try {
    const url = month ? `api/chart_data.php?action=defects_by_category&month=${month}` : 'api/chart_data.php?action=defects_by_category';
    const d = await getJSON(url);
    if (!d.labels.length) return showPanelError('chartDefects', 'No defect-category reports yet — add one to see this chart.');
    if (defectsChart) defectsChart.destroy();
    defectsChart = new Chart(document.getElementById('chartDefects'), {
      type: 'bar',
      data: { labels: d.labels, datasets: [{ data: d.data, backgroundColor: '#E2A63B', borderRadius: 4 }] },
      options: {
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } },
      }
    });
  } catch (e) {
    console.error('loadDefects failed:', e);
    showPanelError('chartDefects', 'Could not load this chart — see browser console for details.');
  }
}

async function setupMonthSelect() {
  const sel = document.getElementById('monthSelect');
  sel.innerHTML = '<option value="">All months</option>' + MONTH_NAMES.map((m, i) => `<option value="${i+1}">${m}</option>`).join('');
  sel.addEventListener('change', () => loadDefects(sel.value || null));
}

// ---------------------------------------------------------------- Chart 3: stage comparison
let stageChart;
async function loadStage() {
  if (!chartJsReady) return showPanelError('chartStage', 'Chart library failed to load.');
  try {
    const d = await getJSON('api/chart_data.php?action=stage_comparison');
    document.getElementById('stageDate').textContent = d.date ? `Reporting day: ${d.date}, by line` : 'No data yet';
    if (!d.labels.length) return showPanelError('chartStage', 'No daily KPI reports yet — add one to see this chart.');
    const datasets = d.datasets.map((s, i) => ({
      label: s.line,
      data: s.data,
      backgroundColor: colorFor(s.line, i),
      borderRadius: 3,
    }));
    if (stageChart) stageChart.destroy();
    stageChart = new Chart(document.getElementById('chartStage'), {
      type: 'bar',
      data: { labels: d.labels, datasets },
      options: {
        maintainAspectRatio: false,
        scales: { y: { min: 70, max: 100, ticks: { callback: v => v + '%' } } },
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } } },
      }
    });
  } catch (e) {
    console.error('loadStage failed:', e);
    showPanelError('chartStage', 'Could not load this chart — see browser console for details.');
  }
}

// ---------------------------------------------------------------- Chart 4: monthly trend
let monthlyChart;
async function loadMonthly() {
  if (!chartJsReady) return showPanelError('chartMonthly', 'Chart library failed to load.');
  try {
    const d = await getJSON('api/chart_data.php?action=monthly_trend');
    if (!d.labels.length) return showPanelError('chartMonthly', 'No monthly summary reports yet — add one to see this chart.');
    if (monthlyChart) monthlyChart.destroy();
    monthlyChart = new Chart(document.getElementById('chartMonthly'), {
      type: 'line',
      data: {
        labels: d.labels,
        datasets: [
          { label: 'First-Time Pass Rate', data: d.ftpr, borderColor: '#3F7D5C', backgroundColor: '#3F7D5C', tension: 0.3 },
          { label: 'OQL', data: d.oql, borderColor: '#C1443B', backgroundColor: '#C1443B', tension: 0.3 },
        ]
      },
      options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } } },
        scales: { y: { ticks: { callback: v => v + '%' } } },
      }
    });
  } catch (e) {
    console.error('loadMonthly failed:', e);
    showPanelError('chartMonthly', 'Could not load this chart — see browser console for details.');
  }
}

// ---------------------------------------------------------------- Recent submissions table
async function loadRecent() {
  const wrap = document.getElementById('recentTableWrap');
  try {
    const rows = await getJSON('api/chart_data.php?action=recent');
    if (!rows.length) { wrap.innerHTML = '<p class="empty-state">No reports submitted yet.</p>'; return; }
    const typeClass = t => ({
      'Daily Line KPI': 'type-kpi', 'Daily Team Quality': 'type-team',
      'Monthly Defect Category': 'type-defect', 'Monthly Summary': 'type-summary',
    }[t] || 'type-kpi');
    wrap.innerHTML = `
      <table>
        <thead><tr><th>Type</th><th>Date / Month</th><th>Line</th><th>Detail</th><th>Submitted</th></tr></thead>
        <tbody>
          ${rows.map(r => `
            <tr>
              <td><span class="pill ${typeClass(r.type)}">${r.type}</span></td>
              <td>${r.date_val || '—'}</td>
              <td>${r.line || '—'}</td>
              <td>${r.detail}</td>
              <td>${r.created_at}</td>
            </tr>`).join('')}
        </tbody>
      </table>`;
  } catch (e) {
    console.error('loadRecent failed:', e);
    wrap.innerHTML = '<p class="empty-state">Could not load recent submissions — see browser console for details.</p>';
  }
}

// ---------------------------------------------------------------- Modal / expand-on-click
const backdrop = document.getElementById('modalBackdrop');
const modalTitle = document.getElementById('modalTitle');
const modalBody = document.getElementById('modalBody');

function renderTable(rows) {
  if (!rows.length) return '<p class="empty-state">No rows found.</p>';
  const cols = Object.keys(rows[0]);
  return `<table>
    <thead><tr>${cols.map(c => `<th>${c}</th>`).join('')}</tr></thead>
    <tbody>${rows.map(r => `<tr>${cols.map(c => `<td>${r[c]}</td>`).join('')}</tr>`).join('')}</tbody>
  </table>`;
}

document.querySelectorAll('.expand-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    modalTitle.textContent = btn.dataset.title;
    modalBody.innerHTML = '<p class="empty-state">Loading…</p>';
    backdrop.classList.add('is-open');
    try {
      const rows = await getJSON('api/chart_data.php?action=raw&table=' + encodeURIComponent(btn.dataset.table));
      modalBody.innerHTML = renderTable(rows);
    } catch (e) {
      console.error('modal data load failed:', e);
      modalBody.innerHTML = '<p class="empty-state">Could not load data — see browser console for details.</p>';
    }
  });
});
document.getElementById('modalClose').addEventListener('click', () => backdrop.classList.remove('is-open'));
backdrop.addEventListener('click', e => { if (e.target === backdrop) backdrop.classList.remove('is-open'); });

// Chart.js elements are also click-expandable — clicking a chart opens the same modal as its "View data" button
['chartTrend', 'chartDefects', 'chartStage', 'chartMonthly'].forEach(id => {
  const el = document.getElementById(id);
  if (el) el.addEventListener('click', () => {
    const panel = el.closest('.panel');
    const expandBtn = panel && panel.querySelector('.expand-btn');
    if (expandBtn) expandBtn.click();
  });
});

// ---------------------------------------------------------------- init
// Each loader runs and fails independently — one bad request no longer
// blanks the rest of the dashboard.
(async function init() {
  await setupMonthSelect();
  loadKpis();
  loadTrend();
  loadDefects();
  loadStage();
  loadMonthly();
  loadRecent();
})();