<?php
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['order_id'];
    $method = $_POST['method'];
    $received = (float)$_POST['received'];

    // คำนวณเงินทอนอีกครั้งก่อนบันทึก
    $stmt_order = $conn->prepare("SELECT total_amount FROM orders WHERE order_id = ?");
    $stmt_order->execute([$id]);
    $total = (float)$stmt_order->fetchColumn();
    $change = $received - $total;

    $stmt = $conn->prepare("UPDATE orders SET payment_method = ?, amount_received = ?, amount_change = ?, payment_status = 'paid' WHERE order_id = ?");
    $stmt->execute([$method, $received, $change, $id]);

    header("Location: receipt.php?id=" . $id);
}