<?php
require_once 'db.php';

// ดึงข้อมูลพนักงาน
$employees = $conn->query("SELECT * FROM employees ORDER BY employee_id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการพนักงาน | PHARMACY SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root { --main-color: #068b82; --sub-color: #f0fdf4; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--sub-color); }
        .navbar { background-color: var(--main-color) !important; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .table thead { background-color: #f8f9fa; color: var(--main-color); }
        .status-active { color: #27ae60; background: #e8f8f0; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status-resigned { color: #e74c3c; background: #fdedec; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .btn-edit { color: var(--main-color); border: 1px solid var(--main-color); border-radius: 20px; transition: 0.3s; }
        .btn-edit:hover { background: var(--main-color); color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">💊 PHARMACY SYSTEM</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link text-white" href="index.php">หน้าหลัก</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-bold active" href="employees.php">จัดการพนักงาน</a></li>
                <li class="nav-item ms-3"><a href="sell_medicine.php" class="btn btn-warning btn-sm fw-bold px-4 rounded-pill">🛒 ขายยา</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0" style="color: var(--main-color);">👨‍⚕️ รายชื่อพนักงาน</h4>
            <input type="text" id="searchEmp" class="form-control w-25 rounded-pill" placeholder="🔍 ค้นหาพนักงาน...">
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="empTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($employees as $e): ?>
                    <tr>
                        <td class="fw-bold">#<?= $e['employee_id'] ?></td>
                        <td><?= htmlspecialchars($e['name']) ?></td>
                        <td><?= htmlspecialchars($e['phone'] ?? '-') ?></td>
                        <td class="text-center">
                            <?php if($e['status'] == 'active'): ?>
                                <span class="status-active">● ทำงานอยู่</span>
                            <?php else: ?>
                                <span class="status-resigned">● ลาออก</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-edit px-3" onclick="openEditModal(<?= htmlspecialchars(json_encode($e)) ?>)">
                                📝 แก้ไขข้อมูล
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="update_employee.php" method="POST" class="modal-content" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold">แก้ไขข้อมูลพนักงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="employee_id" id="edit_id">
                
                <div class="mb-3">
                    <label class="form-label fw-bold small">ชื่อ-นามสกุล</label>
                    <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" id="edit_phone" class="form-control rounded-3">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">สถานะการทำงาน</label>
                    <select name="status" id="edit_status" class="form-select rounded-3">
                        <option value="active">ทำงานอยู่ (Active)</option>
                        <option value="resigned">ลาออก (Resigned)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4" style="background: var(--main-color); border:none;">บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ระบบค้นหา
$("#searchEmp").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#empTable tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
});

// ฟังก์ชันเปิด Modal พร้อมดึงข้อมูลเดิมมาโชว์
function openEditModal(emp) {
    document.getElementById('edit_id').value = emp.employee_id;
    document.getElementById('edit_name').value = emp.name;
    document.getElementById('edit_phone').value = emp.phone || '';
    document.getElementById('edit_status').value = emp.status;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
</body>
</html>