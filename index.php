<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <span class="eyebrow">Live · pulls from the reports table</span>
  <h1>Quality Control Dashboard</h1>
  <p>A running view of inspection results across all sewing lines. Every report an employee submits on the <a href="add_report.php"><strong>Add Report</strong></a> page appears here automatically — click any chart to see the rows behind it.</p>
</div>

<div class="kpi-row" id="kpiRow">
  <div class="qc-tag"><span class="qc-tag__label">Latest Day Pass %</span><span class="qc-tag__value" id="kpiPassPct">—</span><span class="qc-tag__sub" id="kpiPassDate">&nbsp;</span></div>
  <div class="qc-tag"><span class="qc-tag__label">Units Checked (Latest Day)</span><span class="qc-tag__value" id="kpiChecked">—</span><span class="qc-tag__sub">across 4 lines</span></div>
  <div class="qc-tag"><span class="qc-tag__label">OQL — Latest Month</span><span class="qc-tag__value" id="kpiOql">—</span><span class="qc-tag__sub" id="kpiOqlMonth">&nbsp;</span></div>
  <div class="qc-tag"><span class="qc-tag__label">First-Time Pass Rate</span><span class="qc-tag__value" id="kpiFtpr">—</span><span class="qc-tag__sub">latest month</span></div>
  <div class="qc-tag"><span class="qc-tag__label">Defects Logged</span><span class="qc-tag__value" id="kpiDefects">—</span><span class="qc-tag__sub">latest month</span></div>
</div>

<div class="panel-grid">

  <div class="panel">
    <div class="panel__head">
      <div>
        <h2>Daily Pass % by Line</h2>
        <p>Trend across all inspection stages, most recent days</p>
      </div>
    </div>
    <div class="panel-canvas-wrap"><canvas id="chartTrend"></canvas></div>
    <button class="expand-btn" data-table="daily_trend" data-title="Daily Pass % — underlying rows">View data ⤢</button>
  </div>

  <div class="panel">
    <div class="panel__head">
      <div>
        <h2>Defects by Category</h2>
        <p>Fabric, sewing, pressing, packing, measurement</p>
      </div>
      <select class="panel__select" id="monthSelect"></select>
    </div>
    <div class="panel-canvas-wrap"><canvas id="chartDefects"></canvas></div>
    <button class="expand-btn" data-table="defects_by_category" data-title="Monthly defects — underlying rows">View data ⤢</button>
  </div>

  <div class="panel">
    <div class="panel__head">
      <div>
        <h2>Pass % by Inspection Stage</h2>
        <p id="stageDate">Most recent reporting day, by line</p>
      </div>
    </div>
    <div class="panel-canvas-wrap"><canvas id="chartStage"></canvas></div>
    <button class="expand-btn" data-table="stage_comparison" data-title="Stage comparison — underlying rows">View data ⤢</button>
  </div>

  <div class="panel">
    <div class="panel__head">
      <div>
        <h2>Monthly First-Time Pass Rate vs OQL</h2>
        <p>Company-wide, from Monthly Summary reports</p>
      </div>
    </div>
    <div class="panel-canvas-wrap"><canvas id="chartMonthly"></canvas></div>
    <button class="expand-btn" data-table="monthly_trend" data-title="Monthly summary — underlying rows">View data ⤢</button>
  </div>

</div>

<div class="table-panel">
  <div class="panel__head">
    <div>
      <h2 style="font-family:var(--font-display);text-transform:uppercase;font-size:15.5px;margin:0 0 2px;">Recent Submissions</h2>
      <p style="margin:0;font-size:12.5px;color:var(--ink-faint);">Last 15 reports added, any type</p>
    </div>
  </div>
  <div id="recentTableWrap"><p class="empty-state">Loading…</p></div>
</div>

<div class="modal-backdrop" id="modalBackdrop">
  <div class="modal">
    <div class="modal__head">
      <h3 id="modalTitle">Data</h3>
      <button class="modal__close" id="modalClose">&times;</button>
    </div>
    <div class="modal__body" id="modalBody"></div>
  </div>
</div>

<script src="assets/js/chart.umd.js"></script>
<script src="assets/js/dashboard.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>