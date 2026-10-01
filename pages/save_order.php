<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction(); // เริ่ม Transaction เพื่อความปลอดภัยของข้อมูล

        // 1. จัดการเรื่องข้อมูลลูกค้า
        $customer_id = null;
        if ($_POST['customer_type'] == 'new' && !empty($_POST['new_customer_name'])) {
            // บันทึกลูกค้าใหม่
            $stmt_cust = $conn->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
            $stmt_cust->execute([$_POST['new_customer_name'], $_POST['new_customer_phone']]);
            $customer_id = $conn->lastInsertId(); // ดึง ID ลูกค้าที่เพิ่งเพิ่ม
        } else {
            // ใช้ ID ลูกค้าเดิม
            $customer_id = $_POST['customer_id'];
        }

        $employee_id = $_POST['employee_id'];
        $medicine_ids = $_POST['medicine_id']; // มาเป็น Array
        $qtys = $_POST['qty']; // มาเป็น Array
        $total_amount = 0;

        // 2. สร้างเลขที่บิล (Order) ไว้ก่อน เพื่อเอา ID ไปใช้ในรายการย่อย
        $stmt_order = $conn->prepare("INSERT INTO orders (customer_id, employee_id, total_amount, order_date) VALUES (?, ?, 0, NOW())");
        $stmt_order->execute([$customer_id, $employee_id]);
        $order_id = $conn->lastInsertId();

        // ... (โค้ดส่วนบนคงเดิม) ...

        // 3. วนลูปบันทึกรายการยาที่ขาย
        foreach ($medicine_ids as $index => $m_id) {
            $qty = $qtys[$index];

            // ดึงราคาปัจจุบันและเช็คสต็อก (ใช้ unit_price และ quantity_in_stock ตามรูป)
            $stmt_med = $conn->prepare("SELECT unit_price, quantity_in_stock FROM medicines WHERE medicine_id = ?");
            $stmt_med->execute([$m_id]);
            $med = $stmt_med->fetch();

            if ($med['quantity_in_stock'] < $qty) {
                throw new Exception("สินค้าในสต็อกไม่พอสำหรับบางรายการ");
            }

            $price_at_sale = $med['unit_price'];
            $subtotal = $price_at_sale * $qty;
            $total_amount += $subtotal;

            // บันทึกลงตาราง order_items (เปลี่ยนชื่อคอลัมน์จาก price_at_sale เป็น unit_price ตามรูป)
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, medicine_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stmt_item->execute([$order_id, $m_id, $qty, $price_at_sale]);

            // ตัดสต็อกยา (ใช้ quantity_in_stock ตามรูป)
            $stmt_update_stock = $conn->prepare("UPDATE medicines SET quantity_in_stock = quantity_in_stock - ? WHERE medicine_id = ?");
            $stmt_update_stock->execute([$qty, $m_id]);
        }

// ... (โค้ดส่วนล่างคงเดิม) ...

        // 4. อัปเดตราคาสรุปยอดรวมกลับเข้าไปในบิลหลัก
        $stmt_update_order = $conn->prepare("UPDATE orders SET total_amount = ? WHERE order_id = ?");
        $stmt_update_order->execute([$total_amount, $order_id]);

        $conn->commit(); // บันทึกทุกอย่างลง DB พร้อมกัน

        // ขายสำเร็จ! ส่งกลับหน้าหลัก หรือไปหน้าดูรายละเอียดบิล
        header("Location: sales_details.php?id=" . $order_id);
        exit;

    } catch (Exception $e) {
        $conn->rollBack(); // ถ้ามีอะไรพลาด ให้ยกเลิกทั้งหมดที่ทำมาข้างบน
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>