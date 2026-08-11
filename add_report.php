<?php
require_once __DIR__ . '/includes/db.php';
$page_title = 'Add Report';

$pdo = get_db();
$lines = $pdo->query("SELECT line_name FROM dim_line ORDER BY line_name")->fetchAll(PDO::FETCH_COLUMN);
$categories = ['Fabric Defects','Fabric Holes','Sewing Defects','Cutting Damage','Color Shading','Stain','Iron','Heat Seal','Pad Print','Print Damages','Embellishment Defects'];
$months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <span class="eyebrow">Employee input</span>
  <h1>Add a Report</h1>
  <p>Pick the report type, fill in the figures for today's (or this month's) inspection, and submit. It shows up on the dashboard immediately — no login needed for this demo.</p>
</div>

<?php if (isset($_GET['saved'])): ?>
  <div class="flash success">Report saved. <a href="index.php">View it on the dashboard →</a></div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="flash error"><?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

<div class="form-panel">
  <form action="save_report.php" method="post" id="reportForm">

    <div class="type-select">
      <input type="radio" name="report_type" value="daily_kpi" id="t1" checked>
      <label for="t1">Daily Line KPI</label>

      <input type="radio" name="report_type" value="team_quality" id="t2">
      <label for="t2">Daily Team Quality</label>

      <input type="radio" name="report_type" value="defect_category" id="t3">
      <label for="t3">Monthly Defect Category</label>

      <input type="radio" name="report_type" value="monthly_summary" id="t4">
      <label for="t4">Monthly Summary</label>
    </div>

    <!-- 1. Daily Line KPI -->
    <section class="report-section is-active" data-section="daily_kpi">
      <div class="field-grid">
        <div class="field"><label>Date</label><input type="date" name="kpi_date" value="<?php echo date('Y-m-d'); ?>"></div>
        <div class="field"><label>Line</label>
          <select name="kpi_line">
            <?php foreach ($lines as $l): ?><option value="<?php echo htmlspecialchars($l); ?>"><?php echo htmlspecialchars($l); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Stage</label>
          <select name="kpi_stage">
            <?php foreach (['In Line','End Line','Appearance','Pre Final','Final'] as $s): ?><option value="<?php echo $s; ?>"><?php echo $s; ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Checked Qty</label><input type="number" min="0" name="kpi_check_qty" placeholder="e.g. 540"></div>
        <div class="field"><label>Failed Qty</label><input type="number" min="0" name="kpi_fail_qty" placeholder="e.g. 18"></div>
      </div>
      <p style="font-size:12px;color:var(--ink-faint);margin-top:-8px;">Pass qty and pass/fail % are calculated automatically from checked and failed quantities.</p>
    </section>

    <!-- 2. Daily Team Quality -->
    <section class="report-section" data-section="team_quality">
      <div class="field-grid">
        <div class="field"><label>Date</label><input type="date" name="team_date" value="<?php echo date('Y-m-d'); ?>"></div>
        <div class="field"><label>Line</label>
          <select name="team_line">
            <?php foreach ($lines as $l): ?><option value="<?php echo htmlspecialchars($l); ?>"><?php echo htmlspecialchars($l); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Style No.</label><input type="text" name="team_style" placeholder="e.g. A330115"></div>
        <div class="field"></div>
        <div class="field"><label>In-Line Checked</label><input type="number" min="0" name="team_inline_check"></div>
        <div class="field"><label>In-Line Defects</label><input type="number" min="0" name="team_inline_defect"></div>
        <div class="field"><label>End-Line Checked</label><input type="number" min="0" name="team_endline_check"></div>
        <div class="field"><label>End-Line Defects</label><input type="number" min="0" name="team_endline_defect"></div>
        <div class="field"><label>Appearance Checked</label><input type="number" min="0" name="team_app_check"></div>
        <div class="field"><label>Appearance Defects</label><input type="number" min="0" name="team_app_defect"></div>
        <div class="field"><label>Audits — Total</label><input type="number" min="0" name="team_audits_total"></div>
        <div class="field"><label>Audits — Passed</label><input type="number" min="0" name="team_audits_pass"></div>
      </div>
      <p style="font-size:12px;color:var(--ink-faint);margin-top:-8px;">Defect %, audit fails, and AQL pass % are calculated automatically.</p>
    </section>

    <!-- 3. Monthly Defect Category -->
    <section class="report-section" data-section="defect_category">
      <div class="field-grid">
        <div class="field"><label>Month</label>
          <select name="defect_month">
            <?php foreach ($months as $i => $m): ?><option value="<?php echo $i+1; ?>"><?php echo $m; ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Category</label>
          <select name="defect_category">
            <?php foreach ($categories as $c): ?><option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field full"><label>Defect Count</label><input type="number" min="0" name="defect_count" placeholder="e.g. 12"></div>
      </div>
    </section>

    <!-- 4. Monthly Summary -->
    <section class="report-section" data-section="monthly_summary">
      <div class="field-grid">
        <div class="field"><label>Month</label>
          <select name="summary_month">
            <?php foreach ($months as $i => $m): ?><option value="<?php echo $i+1; ?>"><?php echo $m; ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"></div>
        <div class="field"><label>Inspected Styles</label><input type="number" min="0" name="summary_inspected"></div>
        <div class="field"><label>Pass Styles</label><input type="number" min="0" name="summary_pass_styles"></div>
        <div class="field"><label>Qty Shipped</label><input type="number" min="0" name="summary_qty_shipped"></div>
        <div class="field"><label>Sample Inspected</label><input type="number" min="0" name="summary_sample_inspected"></div>
        <div class="field"><label>Defects</label><input type="number" min="0" name="summary_defects"></div>
        <div class="field"></div>
        <div class="field"><label>Fabric %</label><input type="number" step="0.01" min="0" max="100" name="summary_fabric_pct" placeholder="e.g. 15.4"></div>
        <div class="field"><label>Sewing %</label><input type="number" step="0.01" min="0" max="100" name="summary_sewing_pct"></div>
        <div class="field"><label>Pressing & Finishing %</label><input type="number" step="0.01" min="0" max="100" name="summary_pressing_pct"></div>
        <div class="field"><label>Packing & Trims %</label><input type="number" step="0.01" min="0" max="100" name="summary_packing_pct"></div>
        <div class="field"><label>Measurements %</label><input type="number" step="0.01" min="0" max="100" name="summary_measurements_pct"></div>
      </div>
      <p style="font-size:12px;color:var(--ink-faint);margin-top:-8px;">OQL and First-Time Pass Rate are calculated automatically from the figures above (Defects ÷ Sample Inspected, Pass Styles ÷ Inspected Styles). The five defect-source percentages should add up to roughly 100%.</p>
    </section>

    <button type="submit" class="submit-btn">Save Report</button>
  </form>
</div>

<script src="assets/js/add_report.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
