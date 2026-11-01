<?php
require_once 'db.php';

// ดึงข้อมูลสมาชิกทั้งหมด
$customers = $conn->query("SELECT * FROM customers WHERE customer_id != 9999 ORDER BY customer_id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสมาชิก | PHARMACY SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --main-color: #068b82; --sub-color: #f0fdf4; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--sub-color); }
        .navbar { background-color: #212529 !important; border-bottom: 3px solid var(--main-color); }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .table thead { background-color: var(--main-color); color: white; }
        .btn-primary { background-color: var(--main-color); border: none; }
        .section-title { color: var(--main-color); font-weight: 700; border-left: 5px solid var(--main-color); padding-left: 10px; }
    </style>
</head>
<body>

<?php $topbar_active = ''; include __DIR__ . '/_topbar.php'; ?>
<div style="height:24px" aria-hidden="true"></div>

<div class="container pb-5">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="section-title m-0">👥 รายชื่อสมาชิกและประวัติ</h4>
            <div class="d-flex gap-2">
                <input type="text" id="searchCust" class="form-control" placeholder="ค้นหาชื่อหรือเบอร์โทร...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="custTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>อีเมล</th>
                        <th>ที่อยู่</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($customers as $c): ?>
                    <tr>
                        <td class="fw-bold">#<?= $c['customer_id'] ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= htmlspecialchars($c['phone'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($c['email'] ?: '-') ?></td>
                        <td class="small text-muted"><?= mb_strimwidth(htmlspecialchars($c['address'] ?: '-'), 0, 50, "...") ?></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                    onclick="editCust(<?= htmlspecialchars(json_encode($c)) ?>)">
                                แก้ไขข้อมูล
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
    <div class="modal-dialog">
        <form action="update_customer.php" method="POST" class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-primary">📝 อัปเดตข้อมูลสมาชิก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="customer_id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">ชื่อ-นามสกุล</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" id="edit_phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">อีเมล</label>
                    <input type="email" name="email" id="edit_email" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">ที่อยู่</label>
                    <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary px-4">บันทึกการเปลี่ยนแปลง</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ระบบค้นหา Real-time
$("#searchCust").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#custTable tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
});

// ฟังก์ชันเปิด Modal พร้อมใส่ข้อมูลเดิม
function editCust(data) {
    $('#edit_id').val(data.customer_id);
    $('#edit_name').val(data.name);
    $('#edit_phone').val(data.phone);
    $('#edit_email').val(data.email);
    $('#edit_address').val(data.address);
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
</body>
</html>