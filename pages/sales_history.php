<?php 
// 1. ตรวจสอบไฟล์เชื่อมต่อฐานข้อมูล
if (!file_exists('db.php')) {
    die("<div style='color:red; padding:20px;'>ไม่พบไฟล์ db.php กรุณาเช็คชื่อไฟล์ครับ</div>");
}
require_once 'db.php'; 

// 2. ทดสอบว่าตัวแปร $conn ใช้งานได้ไหม
if (!isset($conn)) {
    die("<div style='color:red; padding:20px;'>ตัวแปร \$conn ไม่ทำงาน กรุณาเช็คในไฟล์ db.php ว่าตั้งชื่อตัวแปรนี้ไว้หรือไม่</div>");
}

// รับค่าการค้นหาและเรียงลำดับ
$search_customer = trim($_GET['customer'] ?? '');
$search_employee = trim($_GET['employee'] ?? '');
$search_medicine = trim($_GET['medicine'] ?? '');
$start_date      = trim($_GET['start_date'] ?? '');
$end_date        = trim($_GET['end_date'] ?? '');
$sort_by         = trim($_GET['sort_by'] ?? 'date_desc');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการขาย | PHARMACY SYSTEM</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Prompt Font (Gen-Z & Professional Focus) -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-teal: #2F6F68;
            --primary-hover: #245852;
            --bg-page: #F6F8F7;
            --text-main: #2D3748;
            --text-muted: #718096;
            --border-color: #E2E8F0;
        }

        body { 
            font-family: 'Prompt', sans-serif; 
            background-color: var(--bg-page); 
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* Topbar Design Standard */
        .navbar-custom { 
            background-color: #FFFFFF; 
            border-bottom: 1px solid var(--border-color);
            padding: 12px 0;
        }

        .navbar-brand {
            color: var(--primary-teal) !important;
            font-weight: 600;
            font-size: 1.15rem;
            letter-spacing: -0.2px;
        }

        .nav-link-custom {
            color: var(--text-muted);
            font-weight: 400;
            font-size: 0.95rem;
            padding: 6px 16px !important;
            border-radius: 8px;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
        }

        .nav-link-custom:hover {
            color: var(--primary-teal);
            background-color: #F0F4F3;
        }

        .nav-link-custom.active {
            color: var(--primary-teal);
            font-weight: 500;
            background-color: #E6EFEE;
        }

        /* Main Container & Card */
        .content-card { 
            background: #FFFFFF; 
            border-radius: 12px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02); 
            padding: 28px; 
            margin-top: 24px;
            margin-bottom: 48px;
        }

        /* Section Title */
        .page-header {
            margin-bottom: 24px;
        }

        .page-title {
            font-weight: 600;
            font-size: 1.35rem;
            color: var(--text-main);
            margin: 0;
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Search Filter Form */
        .filter-card {
            background-color: #FAFAFA;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .form-label-custom {
            font-size: 0.825rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .form-control-custom, .form-select-custom {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 8px 14px;
            font-size: 0.9rem;
            color: var(--text-main);
            transition: all 0.15s ease-in-out;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            border-color: var(--primary-teal);
            box-shadow: 0 0 0 3px rgba(47, 111, 104, 0.12);
            outline: none;
        }

        /* Button Standard */
        .btn-teal {
            background-color: var(--primary-teal);
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.15s ease-in-out;
        }

        .btn-teal:hover {
            background-color: var(--primary-hover);
            color: #FFFFFF;
        }

        .btn-secondary-custom {
            background-color: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 0.9rem;
            font-weight: 400;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
        }

        .btn-secondary-custom:hover {
            background-color: #F1F5F9;
            color: var(--text-main);
        }

        /* Table Design Clean & Crisp */
        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .custom-table thead th {
            background-color: #F8FAFC;
            color: var(--text-muted);
            font-size: 0.825rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            border-top: 1px solid var(--border-color);
            white-space: nowrap;
        }

        .custom-table tbody tr {
            transition: background-color 0.12s ease-in-out;
        }

        .custom-table tbody tr:hover {
            background-color: #F8FAFC;
        }

        .custom-table tbody td {
            padding: 14px 16px;
            font-size: 0.9rem;
            border-bottom: 1px solid #EDF2F7;
            vertical-align: middle;
            white-space: nowrap;
        }

        .order-id-tag {
            font-weight: 600;
            color: var(--text-main);
            font-family: monospace;
            font-size: 0.95rem;
        }

        .amount-text {
            font-weight: 600;
            color: var(--primary-teal);
            font-family: 'Prompt', monospace;
        }

        .btn-action-text {
            color: var(--primary-teal);
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            transition: background-color 0.12s ease-in-out;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .btn-action-text:hover {
            background-color: #E6EFEE;
            color: var(--primary-hover);
        }

        /* Empty State */
        .empty-state {
            padding: 48px 16px;
            text-align: center;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

    <!-- Navigation Topbar -->
    <?php $topbar_active = 'history'; include __DIR__ . '/_topbar.php'; ?>
<div style="height:24px" aria-hidden="true"></div>

    <!-- Main Container -->
    <div class="container">
        <div class="content-card">
            
            <!-- Header Section -->
            <div class="page-header">
                <h1 class="page-title">ประวัติการขายทั้งหมด</h1>
                <p class="page-subtitle">ค้นหา และเรียงลำดับรายการขายยาทั้งหมดในระบบ</p>
            </div>

            <!-- Filter Section -->
            <form method="GET" class="filter-card">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">ชื่อยา</label>
                        <input type="text" name="medicine" class="form-control form-control-custom" placeholder="ระบุชื่อยาที่ค้นหา..." value="<?= htmlspecialchars($search_medicine); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">ชื่อลูกค้า</label>
                        <input type="text" name="customer" class="form-control form-control-custom" placeholder="ระบุชื่อลูกค้า..." value="<?= htmlspecialchars($search_customer); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">ชื่อพนักงาน</label>
                        <input type="text" name="employee" class="form-control form-control-custom" placeholder="ระบุชื่อพนักงาน..." value="<?= htmlspecialchars($search_employee); ?>">
                    </div>
                    
                    <!-- ฟีเจอร์เพิ่มเติม: เลือกช่วงวันที่ -->
                    <div class="col-md-3">
                        <label class="form-label-custom">ตั้งแต่วันที่</label>
                        <input type="date" name="start_date" class="form-control form-control-custom" value="<?= htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">ถึงวันที่</label>
                        <input type="date" name="end_date" class="form-control form-control-custom" value="<?= htmlspecialchars($end_date); ?>">
                    </div>

                    <!-- ฟีเจอร์เพิ่มเติม: การเรียงลำดับความสำคัญ -->
                    <div class="col-md-6">
                        <label class="form-label-custom">เรียงลำดับตาม</label>
                        <select name="sort_by" class="form-select form-select-custom">
                            <option value="date_desc" <?= $sort_by === 'date_desc' ? 'selected' : ''; ?>>วันที่ล่าสุด → เก่าสุด (ค่าเริ่มต้น)</option>
                            <option value="date_asc" <?= $sort_by === 'date_asc' ? 'selected' : ''; ?>>วันที่เก่าสุด → ล่าสุด</option>
                            <option value="amount_desc" <?= $sort_by === 'amount_desc' ? 'selected' : ''; ?>>ยอดเงิน สูงสุด → ต่ำสุด</option>
                            <option value="amount_asc" <?= $sort_by === 'amount_asc' ? 'selected' : ''; ?>>ยอดเงิน ต่ำสุด → สูงสุด</option>
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                        <a href="sales_history.php" class="btn btn-secondary-custom">ล้างการกรอง</a>
                        <button type="submit" class="btn btn-teal">
                            <i class="bi bi-search me-1"></i> ค้นหา
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Table Section -->
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">เลขที่บิล</th>
                            <th style="width: 20%;">วันที่ / เวลา</th>
                            <th style="width: 23%;">ลูกค้า</th>
                            <th style="width: 20%;">พนักงานผู้ขาย</th>
                            <th style="width: 15%; text-align: right;">ยอดเงินรวม</th>
                            <th style="width: 10%; text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "SELECT o.order_id, o.order_date, c.name AS cust_name, e.name AS emp_name, o.total_amount
                            FROM orders o
                            LEFT JOIN customers c ON o.customer_id = c.customer_id
                            LEFT JOIN employees e ON o.employee_id = e.employee_id
                            WHERE 1=1";
                    $params = [];

                    if ($search_customer !== '') {
                        $sql .= " AND c.name ILIKE ?";
                        $params[] = '%' . $search_customer . '%';
                    }

                    if ($search_employee !== '') {
                        $sql .= " AND e.name ILIKE ?";
                        $params[] = '%' . $search_employee . '%';
                    }

                    if ($search_medicine !== '') {
                        $sql .= " AND EXISTS (
                                    SELECT 1 FROM order_items oi
                                    JOIN medicines m ON oi.medicine_id = m.medicine_id
                                    WHERE oi.order_id = o.order_id AND m.name ILIKE ?
                                )";
                        $params[] = '%' . $search_medicine . '%';
                    }

                    if ($start_date !== '') {
                        $sql .= " AND DATE(o.order_date) >= ?";
                        $params[] = $start_date;
                    }

                    if ($end_date !== '') {
                        $sql .= " AND DATE(o.order_date) <= ?";
                        $params[] = $end_date;
                    }

                    // กำหนดการเรียงลำดับข้อมูล
                    switch ($sort_by) {
                        case 'date_asc':
                            $sql .= " ORDER BY o.order_date ASC, o.order_id ASC";
                            break;
                        case 'amount_desc':
                            $sql .= " ORDER BY o.total_amount DESC, o.order_id DESC";
                            break;
                        case 'amount_asc':
                            $sql .= " ORDER BY o.total_amount ASC, o.order_id ASC";
                            break;
                        case 'date_desc':
                        default:
                            $sql .= " ORDER BY o.order_date DESC, o.order_id DESC";
                            break;
                    }

                    try {
                        $stmt = $conn->prepare($sql);
                        $stmt->execute($params);
                        $count = 0;
                        
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $count++;
                            echo "<tr>";
                            echo "<td><span class='order-id-tag'>#" . htmlspecialchars($row['order_id']) . "</span></td>";
                            echo "<td class='text-secondary'>" . date('d/m/Y H:i', strtotime($row['order_date'])) . "</td>";
                            echo "<td>" . ($row['cust_name'] ? htmlspecialchars($row['cust_name']) : '<span class="text-muted small">ลูกค้าทั่วไป (Walk-in)</span>') . "</td>";
                            echo "<td>" . ($row['emp_name'] ? htmlspecialchars($row['emp_name']) : '<span class="text-muted">-</span>') . "</td>";
                            echo "<td style='text-align: right;'><span class='amount-text'>฿" . number_format($row['total_amount'], 2) . "</span></td>";
                            echo "<td style='text-align: center;'>
                                    <a href='order_details.php?id={$row['order_id']}' class='btn-action-text'>
                                        <i class='bi bi-eye'></i> รายละเอียด
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                        
                        if ($count == 0) {
                            echo "<tr><td colspan='6'><div class='empty-state'><i class='bi bi-inbox fs-2 d-block mb-2 text-muted'></i>ไม่พบรายการขายที่ตรงกับเงื่อนไขการค้นหา</div></td></tr>";
                        }

                    } catch (PDOException $e) {
                        echo "<tr><td colspan='6' class='text-danger text-center p-4'><b>Error:</b> " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>