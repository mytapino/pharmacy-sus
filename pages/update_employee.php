<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['employee_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];

    try {
        $stmt = $conn->prepare("UPDATE employees SET name = ?, phone = ?, status = ? WHERE employee_id = ?");
        $stmt->execute([$name, $phone, $status, $id]);

        header("Location: employees.php?success=1");
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}