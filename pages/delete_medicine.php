<?php
require_once 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $sql = "DELETE FROM medicines WHERE medicine_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        
        // ลบเสร็จแล้วเด้งกลับหน้าจัดการยา
        header("Location: medicines.php");
    } catch (PDOException $e) {
        echo "ไม่สามารถลบได้ เนื่องจากยานี้ถูกใช้ในประวัติการขายแล้ว: " . $e->getMessage();
    }
}
?>