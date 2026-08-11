<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$pdo = get_db();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        // ---- KPI summary strip -------------------------------------------------
        case 'kpi_summary': {
            $latestDate = $pdo->query("SELECT MAX(report_date) FROM fact_daily_line_kpi")->fetchColumn();

            $overall = $pdo->prepare("SELECT SUM(check_qty) AS c, SUM(pass_qty) AS p FROM fact_daily_line_kpi WHERE report_date = ?");
            $overall->execute([$latestDate]);
            $o = $overall->fetch();
            $latestPassPct = ($o && $o['c'] > 0) ? round(($o['p'] / $o['c']) * 100, 1) : null;

            $summary = $pdo->query("SELECT * FROM fact_monthly_summary ORDER BY month_num DESC LIMIT 1")->fetch();

            echo json_encode([
                'latest_date'      => $latestDate,
                'latest_pass_pct'  => $latestPassPct,
                'latest_check_qty' => $o['c'] ?? 0,
                'oql'              => $summary ? round($summary['oql'] * 100, 2) : null,
                'first_time_pass'  => $summary ? round($summary['first_time_pass_rate'] * 100, 1) : null,
                'defects'          => $summary ? (int)$summary['defects'] : null,
                'month_name'       => $summary ? $summary['month_name'] : null,
            ]);
            break;
        }

        // ---- Chart 1: daily Pass % trend per line (last N days) ---------------
        case 'daily_trend': {
            $days = max(5, min(60, (int)($_GET['days'] ?? 20)));
            $stmt = $pdo->prepare("
                SELECT report_date, line_name,
                       ROUND(SUM(pass_qty) / NULLIF(SUM(check_qty),0) * 100, 2) AS pass_pct
                FROM fact_daily_line_kpi
                WHERE report_date >= (SELECT DATE_SUB(MAX(report_date), INTERVAL ? DAY) FROM fact_daily_line_kpi)
                GROUP BY report_date, line_name
                ORDER BY report_date ASC
            ");
            $stmt->execute([$days]);
            $rows = $stmt->fetchAll();

            $dates = [];
            $byLine = [];
            foreach ($rows as $r) {
                $dates[$r['report_date']] = true;
                $byLine[$r['line_name']][$r['report_date']] = (float)$r['pass_pct'];
            }
            $dates = array_keys($dates);
            sort($dates);

            $datasets = [];
            foreach ($byLine as $line => $vals) {
                $series = [];
                foreach ($dates as $d) { $series[] = $vals[$d] ?? null; }
                $datasets[] = ['line' => $line, 'data' => $series];
            }

            echo json_encode(['labels' => $dates, 'datasets' => $datasets]);
            break;
        }

        // ---- Chart 2: defects by category, for a given month -------------------
        case 'defects_by_category': {
            $month = (int)($_GET['month'] ?? 0);
            if ($month) {
                $stmt = $pdo->prepare("SELECT category, defect_count FROM fact_monthly_defect_category WHERE month_num = ? ORDER BY defect_count DESC");
                $stmt->execute([$month]);
            } else {
                $stmt = $pdo->query("SELECT category, SUM(defect_count) AS defect_count FROM fact_monthly_defect_category GROUP BY category ORDER BY defect_count DESC");
            }
            $rows = $stmt->fetchAll();
            echo json_encode([
                'labels' => array_column($rows, 'category'),
                'data'   => array_map('intval', array_column($rows, 'defect_count')),
            ]);
            break;
        }

        // ---- Chart 3: Pass % by stage, latest date, grouped by line -----------
        case 'stage_comparison': {
            $latestDate = $pdo->query("SELECT MAX(report_date) FROM fact_daily_line_kpi")->fetchColumn();
            $stmt = $pdo->prepare("SELECT line_name, stage, pass_pct FROM fact_daily_line_kpi WHERE report_date = ? ORDER BY FIELD(stage,'In Line','End Line','Appearance','Pre Final','Final')");
            $stmt->execute([$latestDate]);
            $rows = $stmt->fetchAll();

            $stages = [];
            $byLine = [];
            foreach ($rows as $r) {
                $stages[$r['stage']] = true;
                $byLine[$r['line_name']][$r['stage']] = round($r['pass_pct'] * 100, 1);
            }
            $stages = array_keys($stages);

            $datasets = [];
            foreach ($byLine as $line => $vals) {
                $series = [];
                foreach ($stages as $s) { $series[] = $vals[$s] ?? null; }
                $datasets[] = ['line' => $line, 'data' => $series];
            }

            echo json_encode(['date' => $latestDate, 'labels' => $stages, 'datasets' => $datasets]);
            break;
        }

        // ---- Chart 4: monthly First-Time Pass Rate & OQL trend ----------------
        case 'monthly_trend': {
            $rows = $pdo->query("SELECT month_name, month_num, first_time_pass_rate, oql FROM fact_monthly_summary ORDER BY month_num ASC")->fetchAll();
            echo json_encode([
                'labels'  => array_column($rows, 'month_name'),
                'ftpr'    => array_map(fn($v) => round($v * 100, 1), array_column($rows, 'first_time_pass_rate')),
                'oql'     => array_map(fn($v) => round($v * 100, 2), array_column($rows, 'oql')),
            ]);
            break;
        }

        // ---- Recent submissions across all report types -----------------------
        case 'recent': {
            $limit = 15;
            $stmt = $pdo->query("
                (SELECT 'Daily Line KPI' AS type, report_date AS date_val, line_name AS line,
                        CONCAT(stage, ' — ', ROUND(pass_pct*100,1), '% pass') AS detail, created_at
                 FROM fact_daily_line_kpi ORDER BY created_at DESC LIMIT $limit)
                UNION ALL
                (SELECT 'Daily Team Quality', report_date, line_name,
                        CONCAT('Style ', style_no, ' — AQL ', ROUND(aql_pass_pct*100,1), '%'), created_at
                 FROM fact_daily_team_quality ORDER BY created_at DESC LIMIT $limit)
                UNION ALL
                (SELECT 'Monthly Defect Category', NULL, month_name,
                        CONCAT(category, ' — ', defect_count, ' defects'), created_at
                 FROM fact_monthly_defect_category ORDER BY created_at DESC LIMIT $limit)
                UNION ALL
                (SELECT 'Monthly Summary', NULL, month_name,
                        CONCAT('FTPR ', ROUND(first_time_pass_rate*100,1), '% · OQL ', ROUND(oql*100,2), '%'), created_at
                 FROM fact_monthly_summary ORDER BY created_at DESC LIMIT $limit)
                ORDER BY created_at DESC
                LIMIT $limit
            ");
            echo json_encode($stmt->fetchAll());
            break;
        }

        // ---- Raw table data for the "view data" modal (whitelisted) -----------
        case 'raw': {
            $table = $_GET['table'] ?? '';
            $map = [
                'daily_trend'          => "SELECT report_date AS `Date`, line_name AS `Line`, stage AS `Stage`, check_qty AS `Checked`, fail_qty AS `Failed`, pass_qty AS `Passed`, ROUND(pass_pct*100,2) AS `Pass %` FROM fact_daily_line_kpi ORDER BY report_date DESC, line_name LIMIT 200",
                'defects_by_category'  => "SELECT month_name AS `Month`, category AS `Category`, defect_count AS `Defect Count` FROM fact_monthly_defect_category ORDER BY month_num DESC, defect_count DESC LIMIT 200",
                'stage_comparison'     => "SELECT report_date AS `Date`, line_name AS `Line`, stage AS `Stage`, ROUND(pass_pct*100,2) AS `Pass %` FROM fact_daily_line_kpi WHERE report_date = (SELECT MAX(report_date) FROM fact_daily_line_kpi) ORDER BY line_name, FIELD(stage,'In Line','End Line','Appearance','Pre Final','Final')",
                'monthly_trend'        => "SELECT month_name AS `Month`, inspected_styles AS `Inspected Styles`, qty_shipped AS `Qty Shipped`, defects AS `Defects`, ROUND(oql*100,2) AS `OQL %`, ROUND(first_time_pass_rate*100,1) AS `First-Time Pass %` FROM fact_monthly_summary ORDER BY month_num ASC",
            ];
            if (!isset($map[$table])) { http_response_code(400); echo json_encode(['error' => 'unknown table']); break; }
            $rows = $pdo->query($map[$table])->fetchAll();
            echo json_encode($rows);
            break;
        }

        default:
            http_response_code(400);
            echo json_encode(['error' => 'unknown action']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
