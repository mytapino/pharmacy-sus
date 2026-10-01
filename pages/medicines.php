<?php
require_once 'db.php';

// รับค่าสำหรับ Search & Filter
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

// สร้าง Dynamic Query
$sql = "SELECT * FROM medicines WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (name ILIKE ? OR generic_name ILIKE ? OR category ILIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($status_filter !== '') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

// เรียงลำดับตามสถานะ active ขึ้นก่อน และเรียงตามชื่อยาเหมือนเดิม
$sql .= " ORDER BY status ASC, name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

function safe($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHARMACY SYSTEM — จัดการผลิตภัณฑ์</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">

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

        body {
            margin: 0;
            background: var(--background-page);
            color: var(--text-primary);
            font-family: "Noto Sans Thai", sans-serif;
            font-size: 14px;
            line-height: 22px;
        }

        a { color: inherit; text-decoration: none; }

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

        .brand { font-size: 16px; line-height: 24px; font-weight: 700; }

        .nav {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
        }

        .nav a {
            color: var(--text-secondary);
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
        }

        .nav a:hover { background: var(--background-page); color: var(--text-primary); }
        .nav a.active { color: var(--primary-700); background: var(--primary-50); }

        .page {
            width: min(1280px, calc(100% - 64px));
            margin: 0 auto;
            padding: 32px 0 48px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 24px;
        }

        .page-title { margin: 0; font-size: 24px; line-height: 32px; font-weight: 700; }
        .page-subtitle { margin: 4px 0 0; color: var(--text-secondary); }

        .btn-primary, .btn-secondary {
            height: 40px;
            border-radius: 8px;
            padding: 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary-600);
            color: #FFFFFF;
        }
        .btn-primary:hover { background: var(--primary-700); }

        .btn-secondary {
            border: 1px solid var(--border-default);
            background: var(--surface);
            color: var(--text-primary);
        }
        .btn-secondary:hover { border-color: var(--primary-600); color: var(--primary-700); }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border-default);
            border-radius: 12px;
            padding: 24px;
        }

        .filter-bar {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-input, .select-input {
            height: 40px;
            border: 1px solid var(--border-default);
            border-radius: 8px;
            padding: 0 16px;
            font-family: inherit;
            font-size: 14px;
            background: var(--surface);
            color: var(--text-primary);
        }

        .search-input { flex: 1; min-width: 240px; }
        .search-input:focus, .select-input:focus {
            outline: none;
            border-color: var(--primary-600);
        }

        .table-wrapper { overflow-x: auto; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th {
            background: var(--background-page);
            color: var(--text-primary);
            font-weight: 600;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-default);
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-default);
            vertical-align: middle;
        }

        .data-table tr:hover { background: var(--primary-50); }
        .data-table tr.status-inactive { background-color: #FDF2F2; }

        .product-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .product-thumb {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid var(--border-default);
            object-fit: cover;
            background: var(--background-page);
        }

        .product-name-text { font-weight: 600; color: var(--text-primary); }
        .product-generic-text { font-size: 12px; color: var(--text-secondary); }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            line-height: 16px;
        }

        .badge-success { background: var(--primary-50); color: var(--success-600); }
        .badge-warning { background: #FFF8E1; color: var(--warning-600); }
        .badge-danger { background: #FDF2F2; color: var(--danger-600); }
        .badge-muted { background: #E5E7EB; color: #4B5563; }

        .action-cell {
            text-align: right;
            white-space: nowrap;
        }

        .action-link {
            color: var(--primary-600);
            font-weight: 500;
            margin-left: 12px;
        }
        .action-link:hover { color: var(--primary-700); text-decoration: underline; }

        .action-link-danger { color: var(--danger-600); }
        .action-link-danger:hover { color: #8C3A3A; }

        .empty-state {
            padding: 48px 24px;
            text-align: center;
            color: var(--text-secondary);
        }

        @media (max-width: 900px) {
            .site-header__inner, .page { width: calc(100% - 48px); }
            .page-header { flex-direction: column; }
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="site-header__inner">
        <div class="brand">PHARMACY SYSTEM</div>
        <nav class="nav" aria-label="เมนูหลัก">
            <a href="index.php">หน้าหลัก</a>
            <a class="active" href="medicines.php">จัดการผลิตภัณฑ์</a>
            <a href="supplier.php">จัดการซัพพลายเออร์</a>
            <a href="employees.php">จัดการพนักงาน</a>
            <a href="sales_history.php">ประวัติการขาย</a>
            <a href="sell_medicine.php">ขายยา</a>
        </nav>
    </div>
</header>

<main class="page">
    <div class="page-header">
        <div>
            <h1 class="page-title">รายการยาในคลัง</h1>
            <p class="page-subtitle">จัดการ ค้นหา และติดตามสถานะสต็อกสินค้าในระบบ</p>
        </div>
        <a class="btn-primary" href="add_medicine_form.php">+ เพิ่มยาใหม่</a>
    </div>

    <section class="panel">
        <!-- Filter & Search Bar -->
        <form method="GET" action="medicines.php" class="filter-bar">
            <input type="text" name="search" class="search-input" placeholder="ค้นหาชื่อยา, ชื่อสามัญ หรือหมวดหมู่..." value="<?php echo safe($search); ?>">
            
            <select name="status" class="select-input" onchange="this.form.submit()">
                <option value="">ทุกสถานะ</option>
                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>กำลังขาย</option>
                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>เลิกขาย</option>
            </select>

            <button type="submit" class="btn-secondary">ค้นหา</button>
            <?php if ($search !== '' || $status_filter !== ''): ?>
                <a href="medicines.php" class="btn-secondary">ล้างการกรอง</a>
            <?php endif; ?>
        </form>

        <!-- Product Table -->
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ชื่อยา / รายละเอียด</th>
                        <th>หมวดหมู่</th>
                        <th>ราคาต่อหน่วย</th>
                        <th>สต็อก</th>
                        <th>วันหมดอายุ</th>
                        <th>สถานะสินค้า</th>
                        <th style="text-align: right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($medicines)): ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                ไม่พบรายการยาที่ค้นหา
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($medicines as $med): ?>
                            <tr class="<?php echo $med['status'] == 'inactive' ? 'status-inactive' : ''; ?>">
                                <td>
                                    <div class="product-info">
                                        <?php if (!empty($med['image_url'])): ?>
                                            <img src="<?php echo safe($med['image_url']); ?>" class="product-thumb" alt="">
                                        <?php else: ?>
                                            <div class="product-thumb" style="display:grid;place-items:center;color:var(--text-secondary);font-size:10px;">ไม่มีรูป</div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="product-name-text"><?php echo safe($med['name']); ?></div>
                                            <div class="product-generic-text"><?php echo safe($med['generic_name'] ?: '-'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo safe($med['category'] ?: 'ทั่วไป'); ?></td>
                                <td>฿<?php echo number_format((float)$med['unit_price'], 2); ?></td>
                                <td>
                                    <?php 
                                        $stock = (int)$med['quantity_in_stock'];
                                        $unit = safe($med['unit_type'] ?: 'ชิ้น');
                                        echo "{$stock} {$unit}";
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        if (!empty($med['expiry_date'])) {
                                            echo date('d/m/Y', strtotime($med['expiry_date']));
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($med['status'] == 'active'): ?>
                                        <span class="badge badge-success">กำลังขาย</span>
                                    <?php else: ?>
                                        <span class="badge badge-muted">เลิกขาย</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-cell">
                                    <a href="supplier.php?medicine_id=<?php echo $med['medicine_id']; ?>" class="action-link">ซัพพลายเออร์</a>
                                    <a href="edit_medicine.php?id=<?php echo $med['medicine_id']; ?>" class="action-link">แก้ไข</a>
                                    
                                    <?php if ($med['status'] == 'active'): ?>
                                        <a href="update_status.php?id=<?php echo $med['medicine_id']; ?>&set=inactive" class="action-link action-link-danger" onclick="return confirm('ระงับการขาย?')">ระงับ</a>
                                    <?php else: ?>
                                        <a href="update_status.php?id=<?php echo $med['medicine_id']; ?>&set=active" class="action-link">เปิดขาย</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

</body>
</html>