<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['customer_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    try {
        $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, email = ?, address = ? WHERE customer_id = ?");
        $stmt->execute([$name, $phone, $email, $address, $id]);
        
        header("Location: customers.php?status=success");
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>