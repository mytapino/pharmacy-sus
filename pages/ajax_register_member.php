<?php
require_once 'db.php';

$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';

if (!empty($name)) {
    try {

        // 2. บันทึกลูกค้าใหม่
        $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $address]);

        $new_id = $conn->lastInsertId();

        echo json_encode([
            'success' => true,
            'id' => $new_id,
            'name' => $name,
            'phone' => $phone
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อ']);
}