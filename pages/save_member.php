<?php
require_once 'db.php';

// รับค่าจาก Form หรือ AJAX
$customer_id = $_POST['customer_id'] ?? null; // ถ้ามีค่าแสดงว่าเป็นการ "แก้ไข"
$name        = $_POST['name'] ?? '';
$phone       = $_POST['phone'] ?? '';
$email       = $_POST['email'] ?? '';
$address     = $_POST['address'] ?? '';

if (!empty($name)) {
    try {
        if ($customer_id) {
            // --- [กรณีแก้ไขข้อมูล] ---
            $sql  = "UPDATE customers SET name = ?, phone = ?, email = ?, address = ? WHERE customer_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $phone, $email, $address, $customer_id]);
            
            $res_id = $customer_id;
            $mode   = 'edit';
        } else {

            $sql  = "INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $phone, $email, $address]);
            
            $res_id = $conn->lastInsertId();
            $mode   = 'add';
        }

        echo json_encode([
            'success' => true,
            'mode'    => $mode,
            'id'      => $res_id,
            'name'    => $name,
            'phone'   => $phone
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อ']);
}