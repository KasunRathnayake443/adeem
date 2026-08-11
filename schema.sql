-- ============================================================================
-- ADEEM UNIFORM (PVT) LTD — QC Dashboard
-- Database schema
-- Import this FIRST (creates tables), then import seed_data.sql (sample rows)
-- Works on MySQL 5.7+ / MariaDB (InfinityFree uses MariaDB)
-- ============================================================================

-- --------------------------------------------------------------------------
-- Dim_Line: lookup table of production lines. Employees pick from this list
-- on the report form; add a new line here any time a line is added.
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS dim_line (
    line_id   INT PRIMARY KEY AUTO_INCREMENT,
    line_name VARCHAR(50) NOT NULL UNIQUE,
    section   VARCHAR(50) NOT NULL DEFAULT 'Sewing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------------
-- Report Type 1: Daily Line KPI
-- One row per line, per inspection stage, per day.
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fact_daily_line_kpi (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    report_date DATE NOT NULL,
    line_name   VARCHAR(50) NOT NULL,
    stage       ENUM('In Line','End Line','Appearance','Pre Final','Final') NOT NULL,
    check_qty   INT NOT NULL DEFAULT 0,
    fail_qty    INT NOT NULL DEFAULT 0,
    pass_qty    INT NOT NULL DEFAULT 0,
    pass_pct    DECIMAL(6,4) NOT NULL DEFAULT 0,
    fail_pct    DECIMAL(6,4) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (report_date),
    INDEX idx_line (line_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------------
-- Report Type 2: Daily Team Quality (by line + style)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fact_daily_team_quality (
    id                     INT PRIMARY KEY AUTO_INCREMENT,
    report_date            DATE NOT NULL,
    line_name              VARCHAR(50) NOT NULL,
    style_no               VARCHAR(50) NOT NULL,
    inline_check_qty       INT NOT NULL DEFAULT 0,
    inline_defect_qty      INT NOT NULL DEFAULT 0,
    inline_defect_pct      DECIMAL(6,4) NOT NULL DEFAULT 0,
    endline_check_qty      INT NOT NULL DEFAULT 0,
    endline_defect_qty     INT NOT NULL DEFAULT 0,
    endline_defect_pct     DECIMAL(6,4) NOT NULL DEFAULT 0,
    appearance_check_qty   INT NOT NULL DEFAULT 0,
    appearance_defect_qty  INT NOT NULL DEFAULT 0,
    appearance_defect_pct  DECIMAL(6,4) NOT NULL DEFAULT 0,
    audits_total           INT NOT NULL DEFAULT 0,
    audits_pass            INT NOT NULL DEFAULT 0,
    audits_fail            INT NOT NULL DEFAULT 0,
    aql_pass_pct           DECIMAL(6,4) NOT NULL DEFAULT 0,
    created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (report_date),
    INDEX idx_line (line_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------------
-- Report Type 3: Monthly Defect Category (long format — one row per category)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fact_monthly_defect_category (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    month_name    VARCHAR(20) NOT NULL,
    month_num     TINYINT NOT NULL,
    category      VARCHAR(50) NOT NULL,
    defect_count  INT NOT NULL DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_month (month_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------------
-- Report Type 4: Monthly Summary (one row per month — company-wide KPIs)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fact_monthly_summary (
    id                        INT PRIMARY KEY AUTO_INCREMENT,
    month_name                VARCHAR(20) NOT NULL,
    month_num                 TINYINT NOT NULL UNIQUE,
    inspected_styles          INT NOT NULL DEFAULT 0,
    pass_styles               INT NOT NULL DEFAULT 0,
    qty_shipped                INT NOT NULL DEFAULT 0,
    sample_inspected           INT NOT NULL DEFAULT 0,
    defects                   INT NOT NULL DEFAULT 0,
    oql                       DECIMAL(6,4) NOT NULL DEFAULT 0,
    first_time_pass_rate      DECIMAL(6,4) NOT NULL DEFAULT 0,
    fabric_pct                DECIMAL(6,4) NOT NULL DEFAULT 0,
    sewing_pct                DECIMAL(6,4) NOT NULL DEFAULT 0,
    pressing_finishing_pct    DECIMAL(6,4) NOT NULL DEFAULT 0,
    packing_trims_pct         DECIMAL(6,4) NOT NULL DEFAULT 0,
    measurements_pct          DECIMAL(6,4) NOT NULL DEFAULT 0,
    created_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
