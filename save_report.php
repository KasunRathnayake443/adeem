<?php
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_report.php');
    exit;
}

$pdo = get_db();
$type = $_POST['report_type'] ?? '';

function num($v) { return is_numeric($v) ? $v + 0 : 0; }
function redirect_error(string $msg) {
    header('Location: add_report.php?error=' . urlencode($msg));
    exit;
}

try {
    switch ($type) {

        case 'daily_kpi': {
            $date = $_POST['kpi_date'] ?? '';
            $line = trim($_POST['kpi_line'] ?? '');
            $stage = $_POST['kpi_stage'] ?? '';
            $check = num($_POST['kpi_check_qty'] ?? 0);
            $fail  = num($_POST['kpi_fail_qty'] ?? 0);

            if (!$date || !$line || !$stage) redirect_error('Please fill in date, line, and stage.');
            if ($fail > $check) redirect_error('Failed qty cannot be greater than checked qty.');

            $pass = $check - $fail;
            $passPct = $check > 0 ? $pass / $check : 0;
            $failPct = $check > 0 ? $fail / $check : 0;

            $stmt = $pdo->prepare("INSERT INTO fact_daily_line_kpi (report_date, line_name, stage, check_qty, fail_qty, pass_qty, pass_pct, fail_pct) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$date, $line, $stage, $check, $fail, $pass, $passPct, $failPct]);
            break;
        }

        case 'team_quality': {
            $date = $_POST['team_date'] ?? '';
            $line = trim($_POST['team_line'] ?? '');
            $style = trim($_POST['team_style'] ?? '');
            if (!$date || !$line || !$style) redirect_error('Please fill in date, line, and style number.');

            $inC = num($_POST['team_inline_check'] ?? 0);  $inD = num($_POST['team_inline_defect'] ?? 0);
            $enC = num($_POST['team_endline_check'] ?? 0); $enD = num($_POST['team_endline_defect'] ?? 0);
            $apC = num($_POST['team_app_check'] ?? 0);     $apD = num($_POST['team_app_defect'] ?? 0);
            $audT = num($_POST['team_audits_total'] ?? 0); $audP = num($_POST['team_audits_pass'] ?? 0);
            $audF = max(0, $audT - $audP);

            $inPct = $inC > 0 ? $inD / $inC : 0;
            $enPct = $enC > 0 ? $enD / $enC : 0;
            $apPct = $apC > 0 ? $apD / $apC : 0;
            $aqlPct = $audT > 0 ? $audP / $audT : 0;

            $stmt = $pdo->prepare("INSERT INTO fact_daily_team_quality
                (report_date, line_name, style_no, inline_check_qty, inline_defect_qty, inline_defect_pct,
                 endline_check_qty, endline_defect_qty, endline_defect_pct, appearance_check_qty, appearance_defect_qty,
                 appearance_defect_pct, audits_total, audits_pass, audits_fail, aql_pass_pct)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$date, $line, $style, $inC, $inD, $inPct, $enC, $enD, $enPct, $apC, $apD, $apPct, $audT, $audP, $audF, $aqlPct]);
            break;
        }

        case 'defect_category': {
            $monthNum = (int)($_POST['defect_month'] ?? 0);
            $category = trim($_POST['defect_category'] ?? '');
            $count = num($_POST['defect_count'] ?? 0);
            if (!$monthNum || !$category) redirect_error('Please choose a month and category.');

            $months = [1=>'January','February','March','April','May','June','July','August','September','October','November','December'];
            $monthName = $months[$monthNum] ?? 'January';

            $stmt = $pdo->prepare("INSERT INTO fact_monthly_defect_category (month_name, month_num, category, defect_count) VALUES (?,?,?,?)");
            $stmt->execute([$monthName, $monthNum, $category, $count]);
            break;
        }

        case 'monthly_summary': {
            $monthNum = (int)($_POST['summary_month'] ?? 0);
            if (!$monthNum) redirect_error('Please choose a month.');
            $months = [1=>'January','February','March','April','May','June','July','August','September','October','November','December'];
            $monthName = $months[$monthNum] ?? 'January';

            $inspected = num($_POST['summary_inspected'] ?? 0);
            $passStyles = num($_POST['summary_pass_styles'] ?? 0);
            $shipped = num($_POST['summary_qty_shipped'] ?? 0);
            $sample = num($_POST['summary_sample_inspected'] ?? 0);
            $defects = num($_POST['summary_defects'] ?? 0);

            $oql = $sample > 0 ? $defects / $sample : 0;
            $ftpr = $inspected > 0 ? $passStyles / $inspected : 0;

            $fabric = num($_POST['summary_fabric_pct'] ?? 0) / 100;
            $sewing = num($_POST['summary_sewing_pct'] ?? 0) / 100;
            $pressing = num($_POST['summary_pressing_pct'] ?? 0) / 100;
            $packing = num($_POST['summary_packing_pct'] ?? 0) / 100;
            $measure = num($_POST['summary_measurements_pct'] ?? 0) / 100;

            // Upsert on month_num so re-submitting a month updates it rather than duplicating.
            $stmt = $pdo->prepare("INSERT INTO fact_monthly_summary
                (month_name, month_num, inspected_styles, pass_styles, qty_shipped, sample_inspected, defects, oql,
                 first_time_pass_rate, fabric_pct, sewing_pct, pressing_finishing_pct, packing_trims_pct, measurements_pct)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE
                 inspected_styles=VALUES(inspected_styles), pass_styles=VALUES(pass_styles), qty_shipped=VALUES(qty_shipped),
                 sample_inspected=VALUES(sample_inspected), defects=VALUES(defects), oql=VALUES(oql),
                 first_time_pass_rate=VALUES(first_time_pass_rate), fabric_pct=VALUES(fabric_pct), sewing_pct=VALUES(sewing_pct),
                 pressing_finishing_pct=VALUES(pressing_finishing_pct), packing_trims_pct=VALUES(packing_trims_pct),
                 measurements_pct=VALUES(measurements_pct)");
            $stmt->execute([$monthName, $monthNum, $inspected, $passStyles, $shipped, $sample, $defects, $oql, $ftpr, $fabric, $sewing, $pressing, $packing, $measure]);
            break;
        }

        default:
            redirect_error('Unknown report type.');
    }

    header('Location: add_report.php?saved=1');
    exit;

} catch (Throwable $e) {
    redirect_error('Could not save report: ' . $e->getMessage());
}
