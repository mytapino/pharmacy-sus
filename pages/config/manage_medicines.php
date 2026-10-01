<?php require_once 'config/db.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>จัดการข้อมูลยา</h3>
        <a class="btn btn-primary" href="../add_medicine_form.php">+ เพิ่มยาใหม่</a>
    </div>
    
    <div class="card shadow-sm border-0 p-3 mb-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อยาหรือหมวดหมู่..." value="<?= $_GET['search'] ?? '' ?>">
            <button class="btn btn-dark text-nowrap">ค้นหา</button>
        </form>
    </div>

    <table class="table table-hover bg-white shadow-sm rounded">
        <thead class="table-dark">
            <tr><th>ชื่อยา</th><th>หมวดหมู่</th><th>ราคา</th><th>สต็อก</th><th>จัดการ</th></tr>
        </thead>
        <tbody>
            <?php
            $search = $_GET['search'] ?? '';
            $stmt = $conn->prepare("SELECT * FROM medicines WHERE name ILIKE ? OR category ILIKE ?");
            $stmt->execute(["%$search%", "%$search%"]);
            while($row = $stmt->fetch()):
            ?>
            <tr>
                <td><?= $row['name'] ?></td>
                <td><?= $row['category'] ?></td>
                <td><?= number_format($row['unit_price'], 2) ?></td>
                <td class="<?= $row['quantity_in_stock'] < 10 ? 'text-danger fw-bold' : '' ?>"><?= $row['quantity_in_stock'] ?></td>
                <td>
                    <button class="btn btn-sm btn-warning">แก้ไข</button>
                    <button class="btn btn-sm btn-danger">ลบ</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>