# ADEEM UNIFORM (PVT) LTD — QC Dashboard (Demo)

A small plain-PHP + MySQL app: employees log daily/monthly quality reports
through a form, managers/CEOs see them on a live dashboard with charts.
No login system — built for a quick internal demo.


## What's in here

```
index.php            Manager/CEO dashboard (KPI cards + 4 charts + recent activity)
add_report.php        Employee form — pick a report type, fill in the numbers
save_report.php        Handles the form POST, does the math, inserts a row
schema.sql             Creates the 5 tables
seed_data.sql           Sample data (pulled from your Master_QC_Dashboard_Data.xlsx —
                        520 daily KPI rows, 104 team quality rows, 88 defect-category
                        rows, 8 monthly summaries) so the dashboard isn't empty on day one
includes/config.php     Database credentials — edit this
includes/db.php         PDO connection helper
api/chart_data.php      JSON endpoints the dashboard's charts call
assets/                 CSS + JS (Chart.js is loaded from a CDN, no build step)
```

## The 4 report types 

1. **Daily Line KPI** — checked/failed/passed qty by line & inspection stage (In Line, End Line, Appearance, Pre Final, Final)
2. **Daily Team Quality** — by line & style number: in-line/end-line/appearance defects, plus AQL audit pass/fail
3. **Monthly Defect Category** — defect counts by category (Fabric, Sewing, Stain, etc.) for a given month
4. **Monthly Summary** — company-wide: inspected/passed styles, qty shipped, OQL, first-time pass rate, and the 5-way defect-source breakdown

Employees only enter raw counts (checked/failed, inspected/passed, etc.) —
percentages like Pass %, OQL, and AQL Pass % are calculated automatically
in `save_report.php` so nobody has to do the maths by hand or can enter an
inconsistent number.


## Testing locally with Laragon

1. Drop this folder into `laragon/www/adeem-qc-dashboard`.
2. Open HeidiSQL (bundled with Laragon) or phpMyAdmin, create a database
   called `adeem_qc`, and import `schema.sql` then `seed_data.sql`.
3. `includes/config.php` already has Laragon-friendly defaults
   (`localhost`, user `root`, empty password) — no edits needed locally.
4. Visit `http://adeem-qc-dashboard.test` (or `localhost/adeem-qc-dashboard`).

## Extending past the demo

This is deliberately a "no login" demo per your spec. Before using it for
anything beyond a demo, you'd want at minimum: a login system (even a
simple one) so submissions are attributed to an employee, input validation
hardening, and HTTPS enforced (InfinityFree gives you free HTTPS on their
subdomains). Happy to help with any of that when you're ready.
