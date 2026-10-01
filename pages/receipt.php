<?php
require_once 'db.php';

if (!isset($_GET['id'])) {
    die('ไม่พบรหัสใบเสร็จ');
}

$id = intval($_GET['id']);

try {
    // ดึงข้อมูลการขาย พร้อมชื่อลูกค้าและพนักงาน
    $stmt = $conn->prepare("SELECT o.*, c.name as cname, e.name as ename 
                            FROM orders o 
                            LEFT JOIN customers c ON o.customer_id = c.customer_id 
                            LEFT JOIN employees e ON o.employee_id = e.employee_id 
                            WHERE o.order_id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if (!$order) {
        die('ไม่พบข้อมูลในระบบ');
    }

    // ดึงรายการยาทั้งหมดในบิล
    $stmt_items = $conn->prepare("SELECT oi.*, m.name as med_name 
                                  FROM order_items oi 
                                  JOIN medicines m ON oi.medicine_id = m.medicine_id 
                                  WHERE oi.order_id = ?");
    $stmt_items->execute([$id]);
    $items = $stmt_items->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสร็จรับเงิน #<?= $id ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #068b82; --secondary: #f0fdf4; --dark: #333; }
        body { font-family: 'Sarabun', sans-serif; background: #f5f5f5; display: flex; justify-content: center; padding: 40px 20px; color: var(--dark); }
        
        .receipt-card { 
            width: 400px; 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            position: relative;
        }

        .receipt-header { text-align: center; margin-bottom: 20px; }
        .receipt-header h2 { color: var(--primary); margin: 0; font-size: 26px; font-weight: 700; }
        .receipt-header p { font-size: 13px; color: #777; margin: 5px 0 0; }

        .dashed-line { border-top: 1px dashed #ddd; margin: 15px 0; }

        .info-grid { font-size: 13px; margin-bottom: 15px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .info-label { color: #888; }
        .info-value { font-weight: 400; }

        .item-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 14px; }
        .item-table th { text-align: left; color: #888; font-weight: 400; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .item-table td { padding: 10px 0; vertical-align: top; }
        .text-right { text-align: right; }

        .total-box { background: var(--secondary); padding: 15px; border-radius: 8px; margin-top: 10px; }
        .total-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .grand-total-label { font-weight: 700; font-size: 16px; }
        .grand-total-price { font-weight: 700; font-size: 22px; color: var(--primary); }

        .payment-status { 
            margin-top: 20px; 
            text-align: center; 
            border: 2px solid var(--primary); 
            color: var(--primary); 
            padding: 5px; 
            border-radius: 5px; 
            font-weight: 700;
            text-transform: uppercase;
            transform: rotate(-3deg);
            opacity: 0.8;
        }

        .footer-msg { text-align: center; font-size: 12px; color: #aaa; margin-top: 25px; }

        .btn-group { margin-top: 30px; display: flex; gap: 10px; }
        .btn { 
            flex: 1; 
            padding: 12px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-family: 'Sarabun'; 
            font-weight: 700; 
            text-decoration: none; 
            text-align: center;
            transition: 0.2s;
        }
        .btn-print { background: var(--primary); color: white; }
        .btn-print:hover { background: #056d66; }
        .btn-back { background: #eee; color: #555; }
        .btn-back:hover { background: #ddd; }

        @media print {
            body { background: white; padding: 0; }
            .receipt-card { box-shadow: none; width: 100%; border: none; }
            .btn-group { display: none; }
        }
    </style>
</head>
<body>

<div class="receipt-card">
    <div class="receipt-header">
        <h2>PHARMACY SYSTEM</h2>
        <p>ใบเสร็จรับเงิน/ใบกำกับภาษีอย่างย่อ</p>
    </div>

    <div class="dashed-line"></div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">เลขที่ใบเสร็จ:</span>
            <span class="info-value">#<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">วันที่เวลา:</span>
            <span class="info-value"><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?> น.</span>
        </div>
        <div class="info-row">
            <span class="info-label">พนักงาน:</span>
            <span class="info-value"><?= htmlspecialchars($order['ename'] ?? 'Admin') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">ลูกค้า:</span>
            <span class="info-value"><?= htmlspecialchars($order['cname'] ?? 'ลูกค้าทั่วไป') ?></span>
        </div>
    </div>

    <div class="dashed-line"></div>

    <table class="item-table">
        <thead>
            <tr>
                <th>รายการยา</th>
                <th class="text-right">จำนวน</th>
                <th class="text-right">รวม</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['med_name']) ?></td>
                <td class="text-right">x<?= $item['quantity'] ?></td>
                <td class="text-right"><?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-row">
            <span class="grand-total-label">ยอดสุทธิ</span>
            <span class="grand-total-price">฿ <?= number_format($order['total_amount'], 2) ?></span>
        </div>
        <div class="dashed-line" style="margin: 8px 0; opacity: 0.3;"></div>
        <div class="info-row" style="font-size: 14px;">
            <span>ชำระโดย: <?= $order['payment_method'] == 'cash' ? 'เงินสด' : 'เงินโอน' ?></span>
            <span>รับมา: <?= number_format($order['amount_received'], 2) ?></span>
        </div>
        <div class="info-row" style="font-size: 14px; color: #d32f2f; font-weight: bold;">
            <span>เงินทอน:</span>
            <span>฿ <?= number_format($order['amount_change'], 2) ?></span>
        </div>
    </div>

    <div class="payment-status">ชำระเงินเรียบร้อยแล้ว</div>

    <div class="footer-msg">
        ขอบคุณที่วางใจใช้บริการ<br>
        กรุณาเก็บใบเสร็จไว้เป็นหลักฐานเพื่อความปลอดภัย
    </div>

    <div class="btn-group">
        <a href="javascript:window.print()" class="btn btn-print">พิมพ์ใบเสร็จ</a>
        <a href="sell_medicine.php" class="btn btn-back">กลับหน้าขาย</a>
    </div>
</div>

</body>
</html>