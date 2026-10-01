<?php
// 1. รับค่าและปรับ Normalize คำค้นหาประเภทสถานะให้ตรงกันทั้งหมด
$search = trim($_GET['search'] ?? '');
$raw_status = trim($_GET['status'] ?? '');

$status = '';
if (in_array($raw_status, ['กำลังขาย', 'ขายอยู่', 'normal'])) {
    $status = 'กำลังขาย';
} elseif (in_array($raw_status, ['สินค้าหมด', 'หมด', 'out'])) {
    $status = 'สินค้าหมด';
} elseif (in_array($raw_status, ['ระงับ', 'ถูกระงับ', 'ระงับการขาย', 'suspended'])) {
    $status = 'ถูกระงับ';
}


// 3. กรองข้อมูล
$filtered_medicines = array_filter($all_medicines, function($item) use ($search, $status) {
    $match_search = true;
    if ($search !== '') {
        $s = mb_strtolower($search);
        $match_search = (mb_strpos(mb_strtolower($item['name']), $s) !== false) ||
                         (mb_strpos(mb_strtolower($item['generic_name']), $s) !== false) ||
                         (mb_strpos(mb_strtolower($item['category']), $s) !== false);
    }

    $match_status = true;
    if ($status !== '') {
        $match_status = ($item['status'] === $status);
    }

    return $match_search && $match_status;
});
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">รายการยาในคลัง</h2>
            <p class="text-muted small mb-0">จัดการ ค้นหา และติดตามสถานะสต็อกสินค้าในระบบ</p>
        </div>
        <a class="btn btn-teal text-white px-3 py-2" href="../add_medicine_form.php" style="background-color: #2F6F68;">+ เพิ่มยาใหม่</a>
    </div>
    
    <div class="card shadow-sm border-0 p-3 mb-4 rounded-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อยา, ชื่อสามัญ หรือหมวดหมู่..." value="<?= htmlspecialchars($search) ?>">
            
            <select name="status" class="form-select w-auto">
                <option value="">ทุกสถานะ</option>
                <option value="กำลังขาย" <?= $status === 'กำลังขาย' ? 'selected' : '' ?>>กำลังขาย</option>
                <option value="สินค้าหมด" <?= $status === 'สินค้าหมด' ? 'selected' : '' ?>>สินค้าหมด</option>
                <option value="ระงับ" <?= $status === 'ถูกระงับ' ? 'selected' : '' ?>>ระงับการขาย</option>
            </select>

            <button type="submit" class="btn btn-outline-secondary text-nowrap">ค้นหา</button>
            <a href="?" class="btn btn-light border text-nowrap">ล้างการกรอง</a>
        </form>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">ชื่อยา / รายละเอียด</th>
                    <th>หมวดหมู่</th>
                    <th>ราคาต่อหน่วย</th>
                    <th>สต็อก</th>
                    <th>วันหมดอายุ</th>
                    <th>สถานะสินค้า</th>
                    <th class="text-end pe-3">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filtered_medicines)): ?>
                    <?php foreach ($filtered_medicines as $row): ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold text-dark"><?= htmlspecialchars($row['name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($row['generic_name'] ?: '-') ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td>฿<?= number_format($row['price'], 2) ?></td>
                        <td>
                            <span class="<?= $row['stock'] < 10 && $row['status'] !== 'ถูกระงับ' ? 'text-danger fw-bold' : '' ?>">
                                <?= $row['stock'] ?> <?= htmlspecialchars($row['unit']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['expiry_date']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'กำลังขาย'): ?>
                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">กำลังขาย</span>
                            <?php elseif ($row['status'] === 'สินค้าหมด'): ?>
                                <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">สินค้าหมด</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">ถูกระงับ</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-3">
                            <button class="btn btn-sm btn-link text-decoration-none text-secondary">ซัพพลายเออร์</button>
                            <button class="btn btn-sm btn-link text-decoration-none text-primary">แก้ไข</button>
                            
                            <?php if ($row['status'] === 'ถูกระงับ'): ?>
                                <button class="btn btn-sm btn-link text-decoration-none text-success">ยกเลิกระงับ</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-link text-decoration-none text-danger">ระงับการขาย</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">ไม่พบรายการยาที่ค้นหา</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>