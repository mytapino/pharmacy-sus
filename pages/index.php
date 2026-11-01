<?php
require_once 'db.php';

/* =========================================================
   Dashboard — ตาม design.md
   Level 1: Business Overview (4 KPI)
   Level 2: Sales Trend
   Level 3: Operational Alerts
   + Recent Sales
   ========================================================= */

// KPI: ยอดขาย + จำนวนบิล
$stmt_summary = $conn->query("
    SELECT
        COALESCE(SUM(total_amount), 0) AS grand_total,
        COUNT(order_id) AS total_bills
    FROM orders
");
$summary = $stmt_summary->fetch(PDO::FETCH_ASSOC);

$grand_total = (float)($summary['grand_total'] ?? 0);
$total_bills = (int)($summary['total_bills'] ?? 0);
$avg_per_bill = $total_bills > 0 ? $grand_total / $total_bills : 0;

// KPI: จำนวนลูกค้า
$stmt_customers = $conn->query("SELECT COUNT(customer_id) AS total_customers FROM customers");
$total_customers = (int)(($stmt_customers->fetch(PDO::FETCH_ASSOC)['total_customers'] ?? 0));

// Sales Trend + range filter (สูงสุด 30 วัน)
$period = $_GET['period'] ?? '7d';

$today = date('Y-m-d');
$default_from = date('Y-m-d', strtotime('-6 days'));
$default_to = $today;

$range_from = $_GET['from'] ?? $default_from;
$range_to = $_GET['to'] ?? $default_to;
$range_error = null;

// Quick presets
if ($period === '30d') {
    $range_from = date('Y-m-d', strtotime('-29 days'));
    $range_to = $today;
} elseif ($period === '7d') {
    $range_from = $default_from;
    $range_to = $default_to;
} elseif ($period === 'range') {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $range_from) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $range_to)) {
        $range_from = $default_from;
        $range_to = $default_to;
        $range_error = 'รูปแบบวันที่ไม่ถูกต้อง จึงกลับมาแสดง 7 วันล่าสุด';
    }

    $from_ts = strtotime($range_from);
    $to_ts = strtotime($range_to);

    if ($from_ts > $to_ts) {
        [$range_from, $range_to] = [$range_to, $range_from];
        $from_ts = strtotime($range_from);
        $to_ts = strtotime($range_to);
    }

    $days_diff = (int)floor(($to_ts - $from_ts) / 86400) + 1;

    if ($days_diff > 30) {
        $range_to = date('Y-m-d', strtotime($range_from . ' +29 days'));
        $range_error = 'เลือกช่วงเวลาได้สูงสุด 30 วัน ระบบจึงปรับวันสิ้นสุดให้อัตโนมัติ';
    }
} else {
    $period = '7d';
    $range_from = $default_from;
    $range_to = $default_to;
}

$stmt_trend = $conn->prepare("
    SELECT
        DATE(order_date) AS bucket,
        COALESCE(SUM(total_amount), 0) AS total
    FROM orders
    WHERE DATE(order_date) BETWEEN ? AND ?
    GROUP BY DATE(order_date)
    ORDER BY DATE(order_date) ASC
");
$stmt_trend->execute([$range_from, $range_to]);
$trend_rows = $stmt_trend->fetchAll(PDO::FETCH_ASSOC);

$trend_map = [];
foreach ($trend_rows as $row) {
    $trend_map[$row['bucket']] = (float)$row['total'];
}

$trend_labels = [];
$trend_values = [];
$trend_dates = [];

$cursor = strtotime($range_from);
$end_ts = strtotime($range_to);

while ($cursor <= $end_ts) {
    $date = date('Y-m-d', $cursor);
    $trend_dates[] = $date;
    $trend_labels[] = date('d/m', $cursor);
    $trend_values[] = $trend_map[$date] ?? 0;
    $cursor = strtotime('+1 day', $cursor);
}

$max_sales = max($trend_values ?: [0]);
$max_idx = array_search($max_sales, $trend_values, true);
$max_sales_label = isset($trend_dates[$max_idx])
    ? date('d/m/Y', strtotime($trend_dates[$max_idx]))
    : 'ยังไม่มีข้อมูล';

$period_total = array_sum($trend_values);
$period_avg = count($trend_values) ? $period_total / count($trend_values) : 0;

$trend_label = 'ยอดขายรายวัน • ' .
    date('d/m/Y', strtotime($range_from)) .
    ' – ' .
    date('d/m/Y', strtotime($range_to));

// Low Stock: 10 รายการคงเหลือน้อยสุด
$stmt_low = $conn->prepare("
    SELECT
        m.medicine_id,
        m.name,
        m.generic_name,
        m.category,
        m.quantity_in_stock,
        m.unit_type,
        m.image_url,
        m.supplier_id,
        s.name AS supplier_name
    FROM medicines m
    LEFT JOIN suppliers s ON m.supplier_id = s.supplier_id
    WHERE m.status = 'active'
    ORDER BY m.quantity_in_stock ASC, m.name ASC
    LIMIT 10
");
$stmt_low->execute();
$low_stock = $stmt_low->fetchAll(PDO::FETCH_ASSOC);

// Near Expiry: 10 รายการวันหมดอายุใกล้ที่สุด
$stmt_expiry = $conn->prepare("
    SELECT
        medicine_id,
        name,
        generic_name,
        category,
        unit_type,
        image_url,
        expiry_date
    FROM medicines
    WHERE status = 'active'
      AND expiry_date IS NOT NULL
      AND expiry_date >= CURRENT_DATE
    ORDER BY expiry_date ASC, name ASC
    LIMIT 10
");
$stmt_expiry->execute();
$near_expiry = $stmt_expiry->fetchAll(PDO::FETCH_ASSOC);

// Recent Sales
$stmt_recent = $conn->query("
    SELECT
        o.order_id,
        o.order_date,
        c.name AS cust_name,
        o.total_amount
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY o.order_id DESC
    LIMIT 5
");

function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function productImage(?string $url): string {
    return trim((string)$url);
}

function displayDate(?string $date): string {
    if (!$date) return 'ยังไม่มีข้อมูล';
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : 'ยังไม่มีข้อมูล';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PHARMACY SYSTEM — ภาพรวมร้านค้า</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
    --primary-600: #2F6F68;
    --primary-700: #245A55;
    --primary-50: #EAF4F2;
    --background-page: #F6F8F7;
    --surface: #FFFFFF;
    --text-primary: #25312F;
    --text-secondary: #66726F;
    --border-default: #DCE4E1;
    --info-600: #4F7390;
    --warning-600: #A06A2B;
    --danger-600: #B55252;
    --success-600: #4F806B;
}

* { box-sizing: border-box; }

html { background: var(--background-page); }

body {
    margin: 0;
    background: var(--background-page);
    color: var(--text-primary);
    font-family: "Noto Sans Thai", sans-serif;
    font-size: 14px;
    line-height: 22px;
}

a { color: inherit; }
button, select { font: inherit; }

.site-header {
    min-height: 64px;
    background: var(--surface);
    border-bottom: 1px solid var(--border-default);
}

.site-header__inner {
    width: min(1280px, calc(100% - 64px));
    min-height: 64px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 32px;
}

.brand {
    font-size: 16px;
    line-height: 24px;
    font-weight: 700;
    white-space: nowrap;
}

.nav {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.nav a {
    color: var(--text-secondary);
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 500;
}

.nav a:hover {
    color: var(--text-primary);
    background: var(--background-page);
}

.nav a.active {
    color: var(--primary-700);
    background: var(--primary-50);
}

.page {
    width: min(1280px, calc(100% - 64px));
    margin: 0 auto;
    padding: 32px 0 48px;
}

.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 32px;
}

.page-title {
    margin: 0;
    font-size: 24px;
    line-height: 32px;
    font-weight: 700;
}

.page-subtitle {
    margin: 8px 0 0;
    color: var(--text-secondary);
}

.system-time {
    display: flex;
    align-items: center;
    gap: 16px;
    color: var(--text-secondary);
    white-space: nowrap;
}

.status-inline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 6px;
    background: var(--success-600);
}

.panel {
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: 12px;
    padding: 24px;
}

.section { margin-top: 32px; }

.section-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
}

.section-title-row,
.kpi-label-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title {
    margin: 0;
    font-size: 20px;
    line-height: 28px;
    font-weight: 600;
}

.section-meta {
    margin-top: 4px;
    color: var(--text-secondary);
    font-size: 12px;
    line-height: 18px;
}

.ui-icon {
    width: 20px;
    height: 20px;
    flex: 0 0 20px;
    color: var(--primary-600);
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
    fill: none;
}

/* KPI — 4 Columns ตาม design.md */
.kpi-panel { padding: 24px 0; }

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.kpi {
    padding: 0 24px;
    border-right: 1px solid var(--border-default);
    min-width: 0;
}

.kpi:last-child { border-right: 0; }

.kpi-label-row { margin-bottom: 8px; }
.kpi-label-row .ui-icon { color: var(--text-secondary); }

.kpi-label {
    color: var(--text-secondary);
    font-weight: 500;
}

.kpi-value {
    font-size: 28px;
    line-height: 36px;
    font-weight: 700;
}

.kpi-note {
    margin-top: 8px;
    color: var(--text-secondary);
    font-size: 12px;
    line-height: 18px;
}


.trend-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.filter-link,
.filter-input,
.filter-submit {
    min-height: 40px;
    border: 1px solid var(--border-default);
    border-radius: 8px;
    background: var(--surface);
    color: var(--text-primary);
}

.filter-link,
.filter-submit {
    padding: 0 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
}

.filter-input {
    padding: 0 10px;
    min-width: 142px;
}

.filter-link:hover,
.filter-submit:hover {
    border-color: var(--primary-600);
    color: var(--primary-700);
}

.filter-link.active {
    background: var(--primary-50);
    border-color: var(--primary-600);
    color: var(--primary-700);
}

.range-form {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    padding-left: 8px;
    border-left: 1px solid var(--border-default);
}

.range-field {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text-secondary);
    font-size: 12px;
    line-height: 18px;
}

.range-separator {
    color: var(--text-secondary);
}

.filter-submit {
    cursor: pointer;
    background: var(--primary-600);
    border-color: var(--primary-600);
    color: var(--surface);
}

.filter-submit:hover {
    background: var(--primary-700);
    border-color: var(--primary-700);
    color: var(--surface);
}

.range-message {
    margin: -8px 0 16px;
    color: var(--warning-600);
    font-size: 12px;
    line-height: 18px;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Sales Trend */
.trend-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 240px;
    gap: 24px;
    align-items: stretch;
}

.chart-wrap {
    height: 280px;
    position: relative;
}

.trend-summary {
    display: grid;
    grid-template-columns: 1fr;
    border-left: 1px solid var(--border-default);
}

.summary-item {
    padding: 16px 24px;
    border-bottom: 1px solid var(--border-default);
}

.summary-item:last-child { border-bottom: 0; }

.summary-label {
    color: var(--text-secondary);
    font-size: 12px;
    line-height: 18px;
}

.summary-value {
    margin-top: 4px;
    font-size: 16px;
    line-height: 24px;
    font-weight: 600;
}

.summary-note {
    margin-top: 2px;
    color: var(--text-secondary);
    font-size: 12px;
    line-height: 18px;
}

/* Operational Alerts */
.alerts-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
    align-items: start;
}

.list-panel {
    padding-bottom: 0;
    overflow: hidden;
}

.compact-list {
    margin: 0 -24px;
    border-top: 1px solid var(--border-default);
}

.product-row,
.expiry-row {
    min-height: 72px;
    display: grid;
    grid-template-columns: 48px minmax(0, 1fr) auto;
    align-items: center;
    gap: 16px;
    padding: 8px 24px;
    border-bottom: 1px solid var(--border-default);
}

.product-row:hover,
.expiry-row:hover {
    background: var(--background-page);
}

.thumb {
    width: 48px;
    height: 48px;
    border: 1px solid var(--border-default);
    border-radius: 8px;
    object-fit: cover;
    background: var(--background-page);
}

.thumb-placeholder {
    width: 48px;
    height: 48px;
    border: 1px solid var(--border-default);
    border-radius: 8px;
    display: grid;
    place-items: center;
    background: var(--background-page);
    color: var(--text-secondary);
    font-size: 12px;
}

.product-name {
    font-weight: 600;
    color: var(--text-primary);
}

.product-meta {
    margin-top: 2px;
    color: var(--text-secondary);
    font-size: 12px;
    line-height: 18px;
}

.stock-block,
.expiry-block {
    text-align: right;
    white-space: nowrap;
}

.stock-value,
.expiry-value {
    color: var(--warning-600);
    font-weight: 600;
}

.row-action {
    margin-top: 4px;
    min-height: 32px;
    border-radius: 8px;
    padding: 0 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-700);
    text-decoration: none;
    font-weight: 500;
}

.row-action:hover { background: var(--primary-50); }

.row-action.is-disabled {
    color: var(--text-secondary);
    pointer-events: none;
}

.list-footer {
    margin: 0 -24px;
    border-top: 1px solid var(--border-default);
    padding: 8px 24px;
}

.list-toggle {
    min-height: 40px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: var(--primary-700);
    padding: 0 8px;
    cursor: pointer;
    font-weight: 500;
}

.list-toggle:hover { background: var(--primary-50); }

.is-hidden { display: none !important; }

/* Recent Sales */
.table-wrap {
    overflow-x: auto;
    margin: 0 -24px -24px;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 780px;
}

thead { background: var(--background-page); }

th, td {
    height: 56px;
    padding: 0 24px;
    border-bottom: 1px solid var(--border-default);
    text-align: left;
    vertical-align: middle;
}

th { font-weight: 600; }
tbody tr:hover { background: var(--primary-50); }
tbody tr:last-child td { border-bottom: 0; }
.num { text-align: right; white-space: nowrap; }

.text-action {
    min-height: 40px;
    border-radius: 8px;
    padding: 0 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-700);
    text-decoration: none;
    font-weight: 500;
}

.text-action:hover { background: var(--primary-50); }

.empty-state {
    padding: 40px 24px;
    text-align: center;
    color: var(--text-secondary);
}

@media (max-width: 1040px) {
    .site-header__inner, .page { width: calc(100% - 48px); }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .kpi {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border-default);
    }

    .kpi:nth-child(2) { border-right: 0; }
    .kpi:nth-child(3),
    .kpi:nth-child(4) { border-bottom: 0; }

    .trend-layout {
        grid-template-columns: 1fr;
    }

    .section-header {
        flex-direction: column;
    }

    .trend-toolbar {
        justify-content: flex-start;
        width: 100%;
    }

    .range-form {
        border-left: 0;
        padding-left: 0;
    }

    .trend-summary {
        grid-template-columns: repeat(3, 1fr);
        border-left: 0;
        border-top: 1px solid var(--border-default);
    }

    .summary-item {
        border-bottom: 0;
        border-right: 1px solid var(--border-default);
    }

    .summary-item:last-child { border-right: 0; }

    .alerts-grid { grid-template-columns: 1fr; }
}

@media (max-width: 760px) {
    .site-header__inner, .page { width: calc(100% - 32px); }

    .site-header__inner {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px 0;
    }

    .nav { justify-content: flex-start; }

    .kpi-grid { grid-template-columns: 1fr; }

    .kpi {
        border-right: 0;
        border-bottom: 1px solid var(--border-default);
    }

    .kpi:last-child { border-bottom: 0; }

    .trend-summary { grid-template-columns: 1fr; }

    .summary-item {
        border-right: 0;
        border-bottom: 1px solid var(--border-default);
    }

    .product-row,
    .expiry-row {
        grid-template-columns: 48px minmax(0, 1fr);
    }

    .stock-block,
    .expiry-block {
        grid-column: 2;
        text-align: left;
    }

    .range-form {
        width: 100%;
        flex-wrap: wrap;
    }

    .range-field {
        flex: 1 1 160px;
    }

    .filter-input {
        width: 100%;
        min-width: 0;
    }

    .filter-submit {
        flex: 1 1 100%;
    }

    .system-time { flex-wrap: wrap; }
}
</style>
</head>
<body>

<?php $topbar_active = 'home'; include __DIR__ . '/_topbar.php'; ?>

<main class="page">
    <div class="page-header">
        <div>
            <h1 class="page-title">ภาพรวมร้านค้า</h1>
            <p class="page-subtitle">ติดตามยอดขาย แนวโน้ม และรายการที่ต้องจัดการจากหน้าจอเดียว</p>
        </div>

        <div class="system-time">
            <span id="live-date">--/--/----</span>
            <span id="live-time">--:--:--</span>
            <span class="status-inline"><span class="status-dot"></span>ระบบออนไลน์</span>
        </div>
    </div>

    <!-- Level 1 — Business Overview -->
    <section class="panel kpi-panel" aria-label="ภาพรวมธุรกิจ">
        <div class="kpi-grid">

            <div class="kpi">
                <div class="kpi-label-row">
                    <svg class="ui-icon" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M8 12h8M12 8v8"></path>
                    </svg>
                    <span class="kpi-label">ยอดขาย</span>
                </div>
                <div class="kpi-value">฿<?php echo number_format($grand_total, 2); ?></div>
                <div class="kpi-note">ยอดขายรวมทั้งหมด</div>
            </div>

            <div class="kpi">
                <div class="kpi-label-row">
                    <svg class="ui-icon" viewBox="0 0 24 24">
                        <path d="M6 3h12v18H6z"></path>
                        <path d="M9 7h6M9 11h6M9 15h4"></path>
                    </svg>
                    <span class="kpi-label">จำนวนบิล</span>
                </div>
                <div class="kpi-value"><?php echo number_format($total_bills); ?></div>
                <div class="kpi-note">รายการขายทั้งหมด</div>
            </div>

            <div class="kpi">
                <div class="kpi-label-row">
                    <svg class="ui-icon" viewBox="0 0 24 24">
                        <path d="M4 19h16M6 16l4-4 3 2 5-7"></path>
                    </svg>
                    <span class="kpi-label">ยอดเฉลี่ยต่อบิล</span>
                </div>
                <div class="kpi-value">฿<?php echo number_format($avg_per_bill, 2); ?></div>
                <div class="kpi-note">ยอดขายรวม ÷ จำนวนบิล</div>
            </div>

            <div class="kpi">
                <div class="kpi-label-row">
                    <svg class="ui-icon" viewBox="0 0 24 24">
                        <circle cx="9" cy="8" r="3"></circle>
                        <circle cx="17" cy="9" r="2"></circle>
                        <path d="M4 20v-2a5 5 0 0 1 10 0v2"></path>
                        <path d="M15 20v-1a4 4 0 0 1 5-4"></path>
                    </svg>
                    <span class="kpi-label">จำนวนลูกค้า</span>
                </div>
                <div class="kpi-value"><?php echo number_format($total_customers); ?></div>
                <div class="kpi-note">ลูกค้าในระบบ</div>
            </div>

        </div>
    </section>

    <!-- Level 2 — Sales Trend -->
    <section class="section panel" aria-labelledby="trendTitle">
        <div class="section-header">
            <div>
                <div class="section-title-row">
                    <svg class="ui-icon" viewBox="0 0 24 24">
                        <path d="M4 19V5"></path>
                        <path d="M4 19h16"></path>
                        <path d="M7 15l4-4 3 2 5-6"></path>
                    </svg>
                    <h2 class="section-title" id="trendTitle">แนวโน้มยอดขาย</h2>
                </div>
                <div class="section-meta"><?php echo h($trend_label); ?></div>
            </div>

            <div class="trend-toolbar" aria-label="เลือกช่วงเวลา">
                <a class="filter-link <?php echo $period === '7d' ? 'active' : ''; ?>" href="?period=7d">7 วัน</a>
                <a class="filter-link <?php echo $period === '30d' ? 'active' : ''; ?>" href="?period=30d">30 วัน</a>

                <form class="range-form" method="get">
                    <input type="hidden" name="period" value="range">

                    <label class="range-field">
                        <span>จาก</span>
                        <input
                            class="filter-input"
                            type="date"
                            name="from"
                            value="<?php echo h($range_from); ?>"
                            max="<?php echo h($today); ?>"
                            aria-label="วันที่เริ่มต้น"
                        >
                    </label>

                    <span class="range-separator">ถึง</span>

                    <label class="range-field">
                        <span class="sr-only">ถึง</span>
                        <input
                            class="filter-input"
                            type="date"
                            name="to"
                            value="<?php echo h($range_to); ?>"
                            max="<?php echo h($today); ?>"
                            aria-label="วันที่สิ้นสุด"
                        >
                    </label>

                    <button class="filter-submit" type="submit">ดูช่วงนี้</button>
                </form>
            </div>
        </div>

        <?php if ($range_error): ?>
            <div class="range-message"><?php echo h($range_error); ?></div>
        <?php endif; ?>

        <div class="trend-layout">
            <div class="chart-wrap">
                <canvas id="salesTrendChart"></canvas>
            </div>

            <aside class="trend-summary" aria-label="สรุปแนวโน้มยอดขาย">
                <div class="summary-item">
                    <div class="summary-label">วันขายสูงสุด</div>
                    <div class="summary-value">฿<?php echo number_format($max_sales, 2); ?></div>
                    <div class="summary-note"><?php echo h($max_sales_label); ?></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">ยอดขายรวมช่วงที่เลือก</div>
                    <div class="summary-value">฿<?php echo number_format($period_total, 2); ?></div>
                    <div class="summary-note">รวมช่วงเวลาที่กำลังแสดง</div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">ค่าเฉลี่ยต่อช่วง</div>
                    <div class="summary-value">฿<?php echo number_format($period_avg, 2); ?></div>
                    <div class="summary-note">เฉลี่ยจากจุดข้อมูลในช่วงที่เลือก</div>
                </div>
            </aside>
        </div>
    </section>

    <!-- Level 3 — Operational Alerts -->
    <section class="section alerts-grid" aria-label="รายการที่ต้องติดตาม">

        <article class="panel list-panel" aria-labelledby="lowStockTitle">
            <div class="section-header">
                <div>
                    <div class="section-title-row">
                        <svg class="ui-icon" viewBox="0 0 24 24">
                            <path d="M4 7l8-4 8 4-8 4-8-4z"></path>
                            <path d="M4 7v10l8 4 8-4V7"></path>
                            <path d="M12 11v10"></path>
                        </svg>
                        <h2 class="section-title" id="lowStockTitle">สินค้าใกล้หมดสต็อก</h2>
                    </div>
                    <div class="section-meta"><?php echo count($low_stock); ?> รายการ • ควรตรวจสอบซัพพลายเออร์</div>
                </div>
            </div>

            <div class="compact-list">
                <?php if (!$low_stock): ?>
                    <div class="empty-state">ยังไม่มีสินค้าในรายการที่ต้องติดตาม</div>
                <?php else: ?>
                    <?php foreach ($low_stock as $index => $item): ?>
                        <div class="product-row <?php echo $index >= 5 ? 'is-hidden extra-low-stock' : ''; ?>">
                            <?php if (productImage($item['image_url'])): ?>
                                <img
                                    class="thumb"
                                    src="<?php echo h(productImage($item['image_url'])); ?>"
                                    alt="ภาพบรรจุภัณฑ์ <?php echo h($item['name']); ?>"
                                >
                            <?php else: ?>
                                <div class="thumb-placeholder">ไม่มีรูป</div>
                            <?php endif; ?>

                            <div>
                                <div class="product-name"><?php echo h($item['name']); ?></div>
                                <div class="product-meta">
                                    <?php
                                    $meta = array_filter([
                                        $item['generic_name'] ?? null,
                                        $item['category'] ?? null
                                    ]);
                                    echo h(implode(' • ', $meta) ?: 'ยังไม่มีข้อมูลรายละเอียดสินค้า');
                                    ?>
                                </div>
                            </div>

                            <div class="stock-block">
                                <div class="stock-value">
                                    เหลือ <?php echo number_format((int)$item['quantity_in_stock']); ?>
                                    <?php echo h($item['unit_type'] ?: 'หน่วย'); ?>
                                </div>

                                <?php if (!empty($item['supplier_id'])): ?>
                                    <a
                                        class="row-action"
                                        href="supplier.php?medicine_id=<?php echo urlencode($item['medicine_id']); ?>"
                                    >
                                        ดูซัพพลายเออร์
                                    </a>
                                <?php else: ?>
                                    <span class="row-action is-disabled">ยังไม่มีซัพพลายเออร์</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (count($low_stock) > 5): ?>
                <div class="list-footer">
                    <button
                        class="list-toggle"
                        id="lowStockToggle"
                        type="button"
                        aria-expanded="false"
                    >
                        ดูทั้งหมด <?php echo count($low_stock); ?> รายการ →
                    </button>
                </div>
            <?php endif; ?>
        </article>

        <article class="panel list-panel" aria-labelledby="expiryTitle">
            <div class="section-header">
                <div>
                    <div class="section-title-row">
                        <svg class="ui-icon" viewBox="0 0 24 24">
                            <rect x="4" y="5" width="16" height="15" rx="2"></rect>
                            <path d="M8 3v4M16 3v4M4 9h16"></path>
                            <circle cx="15.5" cy="15.5" r="2.5"></circle>
                            <path d="M15.5 14v1.7l1.1.8"></path>
                        </svg>
                        <h2 class="section-title" id="expiryTitle">ยาใกล้หมดอายุ</h2>
                    </div>
                    <div class="section-meta"><?php echo count($near_expiry); ?> รายการ • เรียงตามวันหมดอายุใกล้ที่สุด</div>
                </div>
            </div>

            <div class="compact-list">
                <?php if (!$near_expiry): ?>
                    <div class="empty-state">ยังไม่พบยาที่มีวันหมดอายุสำหรับติดตาม</div>
                <?php else: ?>
                    <?php foreach ($near_expiry as $index => $item): ?>
                        <div class="expiry-row <?php echo $index >= 5 ? 'is-hidden extra-expiry' : ''; ?>">
                            <?php if (productImage($item['image_url'])): ?>
                                <img
                                    class="thumb"
                                    src="<?php echo h(productImage($item['image_url'])); ?>"
                                    alt="ภาพบรรจุภัณฑ์ <?php echo h($item['name']); ?>"
                                >
                            <?php else: ?>
                                <div class="thumb-placeholder">ไม่มีรูป</div>
                            <?php endif; ?>

                            <div>
                                <div class="product-name"><?php echo h($item['name']); ?></div>
                                <div class="product-meta">
                                    <?php
                                    $meta = array_filter([
                                        $item['generic_name'] ?? null,
                                        $item['category'] ?? null,
                                        $item['unit_type'] ?? null
                                    ]);
                                    echo h(implode(' • ', $meta) ?: 'ยังไม่มีข้อมูลรายละเอียดสินค้า');
                                    ?>
                                </div>
                            </div>

                            <div class="expiry-block">
                                <div class="product-meta">วันหมดอายุ</div>
                                <div class="expiry-value"><?php echo h(displayDate($item['expiry_date'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (count($near_expiry) > 5): ?>
                <div class="list-footer">
                    <button
                        class="list-toggle"
                        id="expiryToggle"
                        type="button"
                        aria-expanded="false"
                    >
                        ดูทั้งหมด <?php echo count($near_expiry); ?> รายการ →
                    </button>
                </div>
            <?php endif; ?>
        </article>

    </section>

    <!-- Recent Sales -->
    <section class="section panel" aria-labelledby="recentTitle">
        <div class="section-header">
            <div>
                <div class="section-title-row">
                    <svg class="ui-icon" viewBox="0 0 24 24">
                        <path d="M8 6h12M8 12h12M8 18h12"></path>
                        <path d="M4 6h.01M4 12h.01M4 18h.01"></path>
                    </svg>
                    <h2 class="section-title" id="recentTitle">รายการขายล่าสุด</h2>
                </div>
                <div class="section-meta">5 รายการล่าสุดจากฐานข้อมูล</div>
            </div>

            <a href="sales_history.php" class="text-action">ดูประวัติทั้งหมด</a>
        </div>

        <div class="table-wrap">
            <table aria-label="รายการขายล่าสุด">
                <thead>
                    <tr>
                        <th>เลขที่</th>
                        <th>ลูกค้า</th>
                        <th>วันที่/เวลา</th>
                        <th class="num">ยอดเงิน</th>
                        <th class="num">รายการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $has_recent = false; ?>
                    <?php while ($row = $stmt_recent->fetch(PDO::FETCH_ASSOC)): $has_recent = true; ?>
                        <tr>
                            <td>#<?php echo h($row['order_id']); ?></td>
                            <td><?php echo h($row['cust_name'] ?? 'ลูกค้าทั่วไป'); ?></td>
                            <td><?php echo h(date('d/m/Y H:i', strtotime($row['order_date']))); ?> น.</td>
                            <td class="num">฿<?php echo number_format((float)$row['total_amount'], 2); ?></td>
                            <td class="num">
                                <a
                                    class="text-action"
                                    href="sales_details.php?id=<?php echo urlencode($row['order_id']); ?>"
                                >
                                    ดูรายละเอียด
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    <?php if (!$has_recent): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">ยังไม่มีรายการขายในช่วงเวลานี้</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
function updateClock() {
    const now = new Date();
    const d = now.getDate().toString().padStart(2, '0');
    const m = (now.getMonth() + 1).toString().padStart(2, '0');
    const y = now.getFullYear();

    document.getElementById('live-date').innerText = `${d}/${m}/${y}`;
    document.getElementById('live-time').innerText =
        now.toLocaleTimeString('th-TH', { hour12: false });
}
setInterval(updateClock, 1000);
updateClock();

function bindExpandable(toggleId, itemSelector, total) {
    const toggle = document.getElementById(toggleId);
    if (!toggle) return;

    const items = document.querySelectorAll(itemSelector);
    let expanded = false;

    toggle.addEventListener('click', () => {
        expanded = !expanded;

        items.forEach(item => {
            item.classList.toggle('is-hidden', !expanded);
        });

        toggle.textContent = expanded
            ? 'ย่อเหลือ 5 รายการ ↑'
            : `ดูทั้งหมด ${total} รายการ →`;

        toggle.setAttribute('aria-expanded', String(expanded));
    });
}

bindExpandable('lowStockToggle', '.extra-low-stock', <?php echo count($low_stock); ?>);
bindExpandable('expiryToggle', '.extra-expiry', <?php echo count($near_expiry); ?>);

Chart.defaults.font.family = '"Noto Sans Thai", sans-serif';
Chart.defaults.color = '#66726F';

const trendCtx = document.getElementById('salesTrendChart').getContext('2d');

new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($trend_labels, JSON_UNESCAPED_UNICODE); ?>,
        datasets: [{
            label: 'ยอดขาย',
            data: <?php echo json_encode($trend_values); ?>,
            borderColor: '#2F6F68',
            backgroundColor: '#EAF4F2',
            borderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 5,
            pointBackgroundColor: '#2F6F68',
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        return ' ฿' + Number(ctx.raw || 0).toLocaleString('th-TH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#DCE4E1' },
                ticks: {
                    callback: value => '฿' + Number(value || 0).toLocaleString('th-TH')
                }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>

</body>
</html>
