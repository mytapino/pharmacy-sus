<?php
require_once 'db.php';
$id = $_POST['medicine_id'];
$amt = intval($_POST['amount']);
$conn->prepare("UPDATE medicines SET quantity_in_stock = quantity_in_stock - ? WHERE medicine_id = ? AND quantity_in_stock >= ?")->execute([$amt, $id, $amt]);
header("Location: medicines.php");