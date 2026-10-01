<?php
require_once 'db.php';

$id = $_GET['id'] ?? null;
$status = $_GET['set'] ?? $_GET['status'] ?? null;

if ($id && $status) {
    $sql = "UPDATE medicines SET status = ? WHERE medicine_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$status, $id]);
}

header("Location: medicines.php");
exit();
?>