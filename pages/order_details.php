<?php

require_once 'db.php';

// 1. รับรหัสออเดอร์จาก URL และใช้ intval เพื่อป้องกัน Error และเพิ่มความปลอดภัย
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // 2. ดึงข้อมูลภาพรวมของ Order (วันที่, ชื่อลูกค้า, ชื่อพนักงาน)
    $stmt = $conn->prepare("
        SELECT o.*, c.name AS cust_name, e.name AS emp_name 
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.customer_id
        LEFT JOIN employees e ON o.employee_id = e.employee_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // ถ้าไม่พบข้อมูลให้แจ้งเตือน
    if (!$order) {
        die("<div class='container mt-5 alert alert-danger'>ไม่พบข้อมูลรายการขายรหัส #$order_id</div>");
    }

    // 3. ดึงรายการยาจากตาราง order_items (ตามโครงสร้างที่ Cream แจ้งมา)
    $itemStmt = $conn->prepare("
        SELECT oi.*, m.name AS med_name 
        FROM order_items oi
        JOIN medicines m ON oi.medicine_id = m.medicine_id
        WHERE oi.order_id = ?
    ");
    $itemStmt->execute([$order_id]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดการขาย #<?= $order_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #0bb58d; }
        .detail-card { border-radius: 15px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .table thead { background-color: #80ffc6; color: white; }
        /* ซ่อนปุ่มเวลาพิมพ์ใบเสร็จ */
        @media print {
            .btn, .navbar { display: none !important; }
            body { background-color: blue; }
            .detail-card { box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card detail-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <h3 class="fw-bold text-primary mb-0">📄 รายละเอียดการขาย #<?= $order_id ?></h3>
                    <a href="sales_history.php" class="btn btn-outline-secondary">กลับไปหน้าประวัติ</a>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">ชื่อลูกค้า</p>
                        <h6 class="fw-bold"><?= htmlspecialchars($order['cust_name'] ?: 'ลูกค้าทั่วไป (Guest)') ?></h6>
                        <p class="mb-1 text-muted small mt-3">วันที่ทำรายการ</p>
                        <h6><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?> น.</h6>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-1 text-muted small">พนักงานผู้ดูแล</p>
                        <h6><?= htmlspecialchars($order['emp_name'] ?: 'ไม่ระบุ') ?></h6>
                        <p class="mb-1 text-muted small mt-3">สถานะ</p>
                        <span class="badge bg-success">ชำระเงินเรียบร้อย</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="10%">#</th>
                                <th>รายการยา</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-end">ราคา/หน่วย</th>
                                <th class="text-end">รวม (บาท)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grand_total = 0;
                            foreach ($items as $index => $item): 
                                $subtotal = $item['quantity'] * $item['unit_price'];
                                $grand_total += $subtotal;
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($item['med_name']) ?></td>
                                <td class="text-center"><?= number_format($item['quantity']) ?></td>
                                <td class="text-end"><?= number_format($item['unit_price'], 2) ?></td>
                                <td class="text-end fw-bold"><?= number_format($subtotal, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">ยอดเงินรวมทั้งสิ้น:</td>
                                <td class="text-end text-primary fw-bold h5">฿<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4 text-center">
                    <button onclick="window.print()" class="btn btn-light btn-sm text-muted">🖨 พิมพ์ใบเสร็จ</button>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>