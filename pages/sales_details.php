<?php 
require_once 'db.php'; 

// 1. รับค่า ID บิลที่ส่งมาจากปุ่ม
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$order_id = $_GET['id'];

// 2. ดึงข้อมูลหัวบิล (ชื่อลูกค้า, วันที่)
$sql_order = "SELECT o.*, c.name AS cust_name 
              FROM orders o 
              LEFT JOIN customers c ON o.customer_id = c.customer_id 
              WHERE o.order_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->execute([$order_id]);
$order = $stmt_order->fetch(PDO::FETCH_ASSOC);

// 3. ดึงรายการยาที่อยู่ในบิลนี้
$sql_items = "SELECT oi.*, m.name AS med_name 
              FROM order_items oi 
              JOIN medicines m ON oi.medicine_id = m.medicine_id 
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดการขาย #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }</style>
</head>
<body>

<div class="container mt-5">
    <div class="card shadow border-0 p-4" style="border-radius: 15px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary">📑 รายละเอียดการขาย #<?php echo $order_id; ?></h3>
            <a href="index.php" class="btn btn-secondary btn-sm">กลับหน้าหลัก</a>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>ชื่อลูกค้า:</strong> <?php echo htmlspecialchars($order['cust_name'] ?? 'ทั่วไป'); ?></p>
                <p><strong>วันที่/เวลา:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?> น.</p>
            </div>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>รายการยา</th>
                    <th class="text-center">ราคา/หน่วย</th>
                    <th class="text-center">จำนวน</th>
                    <th class="text-end">รวม</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['med_name']); ?></td>
                    <td class="text-center">฿<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                    <td class="text-end">฿<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-primary">
                    <td colspan="3" class="text-end fw-bold">ยอดเงินรวมทั้งสิ้น:</td>
                    <td class="text-end fw-bold">฿<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="text-center mt-3">
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm">🖨️ พิมพ์ใบเสร็จ</button>
        </div>
    </div>
</div>

</body>
</html>