<?php
require_once 'db.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['unit_price'];
    $stock = $_POST['quantity_in_stock'];
    $unit = $_POST['unit_type'];
    $exp = $_POST['expiry_date'];
    $img = $_POST['image_url'];

    try {
        // ดึง supplier_id ตัวแรกมาใช้ (ป้องกัน Error เพราะโครงสร้าง DB บังคับต้องมีผู้ขาย)
        $res = $conn->query("SELECT supplier_id FROM suppliers LIMIT 1");
        $sup = $res->fetch();
        $sup_id = ($sup) ? $sup['supplier_id'] : 1; 

        $sql = "INSERT INTO medicines (name, category, unit_price, quantity_in_stock, unit_type, expiry_date, image_url, supplier_id) 
                VALUES (:name, :cat, :price, :stock, :unit, :exp, :img, :sup_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'name' => $name, 'cat' => $category, 'price' => $price, 
            'stock' => $stock, 'unit' => $unit, 'exp' => $exp, 'img' => $img, 'sup_id' => $sup_id
        ]);
        
        echo "<script>alert('เพิ่มข้อมูลยาสำเร็จ!'); window.location='medicines.php';</script>";
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มยาใหม่ | PHARMACY SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f0f8ff; padding: 40px; }
        .form-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        .btn-save { background-color: #198754; color: white; font-weight: bold; }
    </style>
</head>
<body>
    <div class="form-card">
        <h3 class="text-success fw-bold mb-4">➕ เพิ่มยาใหม่เข้าคลัง</h3>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">ชื่อยา</label>
                <input type="text" name="name" class="form-control" required placeholder="กรอกชื่อยา">
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">หมวดหมู่</label>
                    <input type="text" name="category" class="form-control" placeholder="เช่น ยาแก้แพ้">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ราคา/หน่วย (บาท)</label>
                    <input type="number" step="0.01" name="unit_price" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">จำนวนสต็อก</label>
                    <input type="number" name="quantity_in_stock" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">หน่วยเรียก</label>
                    <input type="text" name="unit_type" class="form-control" placeholder="เม็ด / ขวด" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">วันหมดอายุ</label>
                <input type="date" name="expiry_date" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label">ลิงก์รูปภาพ (URL)</label>
                <input type="text" name="image_url" class="form-control" placeholder="คัดลอกลิงก์รูปภาพจากเน็ตมาวางที่นี่">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-save w-100 py-2">บันทึกข้อมูล</button>
                <a href="medicines.php" class="btn btn-light w-100 py-2 border">ยกเลิก</a>
            </div>
        </form>
    </div>
</body>
</html>