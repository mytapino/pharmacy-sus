<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'];
    $employee_id = DEFAULT_EMPLOYEE_ID; // ไม่มีระบบ login: บันทึกการขายเป็นพนักงาน Admin
    $grand_total = $_POST['grand_total']; // รับค่าผลรวมสุทธิ
    $items = $_POST['items'];

    try {
        $conn->beginTransaction();

        // บันทึก Order พร้อมยอดเงินรวมเพื่อให้ประวัติการขายถูกต้อง
        $sql = "INSERT INTO orders (order_date, customer_id, employee_id, total_amount, payment_status) 
                VALUES (NOW(), ?, ?, ?, 'unpaid')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$customer_id, $employee_id, $grand_total]);
        $order_id = $conn->lastInsertId();

        foreach ($items as $item) {
            $stmt_price = $conn->prepare("SELECT unit_price FROM medicines WHERE medicine_id = ?");
            $stmt_price->execute([$item['id']]);
            $price = $stmt_price->fetchColumn();

            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, medicine_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stmt_item->execute([$order_id, $item['id'], $item['qty'], $price]);

            $conn->prepare("UPDATE medicines SET quantity_in_stock = quantity_in_stock - ? WHERE medicine_id = ?")
                 ->execute([$item['qty'], $item['id']]);
        }

        $conn->commit();
        header("Location: payment.php?order_id=" . $order_id);
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("Error: " . $e->getMessage());
    }
}