<?php
/*
 * เพิ่มยาใหม่ — Redesign HTS Final (R-04)
 * Finding F-04: ผู้ทดสอบติดขัดที่ช่องเลือกวันหมดอายุ และไม่แน่ใจว่ากดบันทึกแล้วระบบไปต่อหรือไม่ (A, B, E)
 * สิ่งที่แก้:
 *   1. ปุ่มบันทึกมี Loading State "กำลังบันทึก…" และกันกดซ้ำ (design.md 4.1 Button States)
 *   2. บันทึกสำเร็จ → กลับหน้ารายการยาพร้อมแถบยืนยัน และยาที่เพิ่งเพิ่มอยู่บนสุดของตาราง (R-04 / H1)
 *   3. บันทึกไม่สำเร็จ → แสดงข้อความใต้ฟอร์มตาม Voice & Tone และเก็บค่าที่กรอกไว้ (design.md 4.2, 5.2)
 *   4. ช่องวันหมดอายุมีคำอธิบายรูปแบบ (วัน/เดือน/ปี ค.ศ.) และห้ามเลือกวันที่ผ่านมาแล้ว
 *   5. เพิ่มช่อง "ชื่อสามัญ" (มีใน DB อยู่แล้ว) + รายการหมวดหมู่/หน่วยที่มีในระบบให้เลือก
 *   6. ทำเครื่องหมาย * ช่องที่จำเป็น และย้ายหน้าเข้า token / Topbar เดียวกับ Dashboard
 */
require_once 'db.php';

function safe($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$old = [
    'name' => '', 'generic_name' => '', 'category' => '', 'unit_price' => '',
    'quantity_in_stock' => '', 'unit_type' => '', 'expiry_date' => '', 'image_url' => '',
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) { $old[$k] = trim($_POST[$k] ?? ''); }

    if ($old['name'] === '' || $old['unit_price'] === '' || $old['quantity_in_stock'] === ''
        || $old['unit_type'] === '' || $old['expiry_date'] === '') {
        $error = 'กรอกช่องที่มีเครื่องหมาย * ให้ครบก่อนบันทึก';
    } else {
        try {
            // ดึง supplier_id ตัวแรกมาใช้ (โครงสร้าง DB บังคับต้องมีผู้ขาย — เหมือนเวอร์ชันเดิม ไม่แก้ schema)
            $sup_id = $conn->query("SELECT supplier_id FROM suppliers ORDER BY supplier_id LIMIT 1")->fetchColumn() ?: 1;

            $stmt = $conn->prepare("INSERT INTO medicines
                (name, generic_name, category, unit_price, quantity_in_stock, unit_type, expiry_date, image_url, supplier_id)
                VALUES (:name, :generic, :cat, :price, :stock, :unit, :exp, :img, :sup_id)
                RETURNING medicine_id");
            $stmt->execute([
                'name' => $old['name'], 'generic' => $old['generic_name'] ?: null, 'cat' => $old['category'] ?: null,
                'price' => $old['unit_price'], 'stock' => $old['quantity_in_stock'], 'unit' => $old['unit_type'],
                'exp' => $old['expiry_date'], 'img' => $old['image_url'] ?: null, 'sup_id' => $sup_id,
            ]);
            $new_id = (int)$stmt->fetchColumn();

            header('Location: medicines.php?added=' . $new_id);
            exit();
        } catch (PDOException $e) {
            $error = 'บันทึกข้อมูลไม่สำเร็จ ลองอีกครั้ง';
        }
    }
}

$categories = $conn->query("SELECT DISTINCT category FROM medicines WHERE category IS NOT NULL AND category <> '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$units = $conn->query("SELECT DISTINCT unit_type FROM medicines WHERE unit_type IS NOT NULL AND unit_type <> '' ORDER BY unit_type")->fetchAll(PDO::FETCH_COLUMN);
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHARMACY SYSTEM — เพิ่มยาใหม่</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-600: #2F6F68; --primary-700: #245A55; --primary-50: #EAF4F2;
            --background-page: #F6F8F7; --surface: #FFFFFF;
            --text-primary: #25312F; --text-secondary: #66726F; --border-default: #DCE4E1;
            --danger-600: #B55252;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--background-page); color: var(--text-primary);
               font-family: "Noto Sans Thai", sans-serif; font-size: 14px; line-height: 22px; }
        a { color: inherit; text-decoration: none; }

        .site-header { min-height: 64px; background: var(--surface); border-bottom: 1px solid var(--border-default); }
        .site-header__inner { width: min(1280px, calc(100% - 64px)); min-height: 64px; margin: 0 auto;
                              display: flex; align-items: center; justify-content: space-between; gap: 32px; }
        .brand { font-size: 16px; line-height: 24px; font-weight: 700; }
        .nav { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
        .nav a { color: var(--text-secondary); padding: 8px 16px; border-radius: 8px; font-weight: 500; }
        .nav a:hover { background: var(--background-page); color: var(--text-primary); }
        .nav a.active { color: var(--primary-700); background: var(--primary-50); }

        .page { width: min(720px, calc(100% - 64px)); margin: 0 auto; padding: 32px 0 48px; }
        .breadcrumb { color: var(--text-secondary); margin-bottom: 8px; }
        .breadcrumb a { color: var(--primary-700); font-weight: 500; }
        .page-title { margin: 0; font-size: 24px; line-height: 32px; font-weight: 700; }
        .page-subtitle { margin: 4px 0 24px; color: var(--text-secondary); }

        .panel { background: var(--surface); border: 1px solid var(--border-default); border-radius: 12px; padding: 24px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 24px; }
        .full { grid-column: 1 / -1; }
        .label { display: block; font-weight: 500; margin-bottom: 8px; }
        .req { color: var(--danger-600); }
        .input { width: 100%; height: 40px; border: 1px solid var(--border-default); border-radius: 8px; padding: 0 16px;
                 font-family: inherit; font-size: 14px; background: var(--surface); color: var(--text-primary); }
        .input:hover { border-color: var(--primary-600); }
        .input:focus { outline: none; border-color: var(--primary-600); box-shadow: 0 0 0 3px var(--primary-50); }
        .input.is-error { border-color: var(--danger-600); }
        .helper { margin: 8px 0 0; font-size: 12px; line-height: 18px; color: var(--text-secondary); }
        .field-error { margin: 8px 0 0; font-size: 12px; line-height: 18px; color: var(--danger-600); display: none; }
        .is-error + .helper + .field-error, .is-error ~ .field-error { display: block; }

        .alert-error { border: 1px solid var(--danger-600); color: var(--danger-600); background: var(--surface);
                       border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; }
        .note { color: var(--text-secondary); font-size: 12px; margin: 0 0 16px; }

        .actions { display: flex; gap: 16px; margin-top: 24px; }
        .btn { height: 40px; border-radius: 8px; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center;
               font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer; border: none; }
        .btn-primary { flex: 1; background: var(--primary-600); color: var(--surface); }
        .btn-primary:hover { background: var(--primary-700); }
        .btn-primary:disabled { background: var(--background-page); color: var(--text-secondary); box-shadow: inset 0 0 0 1px var(--border-default); cursor: progress; }
        .btn-secondary { border: 1px solid var(--border-default); background: var(--surface); color: var(--text-primary); }
        .btn-secondary:hover { border-color: var(--primary-600); color: var(--primary-700); }
        .spinner { width: 16px; height: 16px; border: 2px solid currentColor; border-right-color: transparent;
                   border-radius: 50%; margin-right: 8px; display: none; animation: spin .8s linear infinite; }
        .btn-primary.is-loading .spinner { display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 720px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php $topbar_active = 'products'; include __DIR__ . '/_topbar.php'; ?>

<main class="page">
    <div class="breadcrumb"><a href="medicines.php">จัดการผลิตภัณฑ์</a> › เพิ่มยาใหม่</div>
    <h1 class="page-title">เพิ่มยาใหม่เข้าคลัง</h1>
    <p class="page-subtitle">บันทึกยาที่รับเข้าร้าน เมื่อบันทึกแล้วยาจะแสดงอยู่บนสุดของรายการยา</p>

    <?php if ($error): ?>
        <div class="alert-error" role="alert"><?= safe($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="panel" id="med_form" novalidate>
        <p class="note">ช่องที่มี <span class="req">*</span> จำเป็นต้องกรอก</p>
        <div class="grid">
            <div>
                <label class="label" for="name">ชื่อยา <span class="req">*</span></label>
                <input id="name" type="text" name="name" class="input" required value="<?= safe($old['name']) ?>" placeholder="เช่น Paracetamol 500mg">
                <p class="helper">ชื่อทางการค้าพร้อมความแรง</p>
                <p class="field-error">กรอกชื่อยา</p>
            </div>
            <div>
                <label class="label" for="generic_name">ชื่อสามัญ</label>
                <input id="generic_name" type="text" name="generic_name" class="input" value="<?= safe($old['generic_name']) ?>" placeholder="เช่น Paracetamol">
                <p class="helper">ใช้ค้นหายาได้ทั้งหน้าขายยาและหน้ารายการยา</p>
            </div>
            <div>
                <label class="label" for="category">หมวดหมู่</label>
                <input id="category" type="text" name="category" class="input" list="category_list" value="<?= safe($old['category']) ?>" placeholder="เลือกหรือพิมพ์ใหม่">
                <datalist id="category_list">
                    <?php foreach ($categories as $c): ?><option value="<?= safe($c) ?>"></option><?php endforeach; ?>
                </datalist>
                <p class="helper">กดที่ช่องเพื่อเลือกหมวดหมู่ที่มีอยู่ หรือพิมพ์หมวดใหม่</p>
            </div>
            <div>
                <label class="label" for="unit_price">ราคาต่อหน่วย (บาท) <span class="req">*</span></label>
                <input id="unit_price" type="number" step="0.01" min="0" name="unit_price" class="input" required value="<?= safe($old['unit_price']) ?>" placeholder="เช่น 10">
                <p class="helper">ราคาขายต่อ 1 หน่วยตามช่องหน่วยนับ</p>
                <p class="field-error">กรอกราคาเป็นตัวเลข</p>
            </div>
            <div>
                <label class="label" for="quantity_in_stock">จำนวนที่รับเข้า <span class="req">*</span></label>
                <input id="quantity_in_stock" type="number" min="0" step="1" name="quantity_in_stock" class="input" required value="<?= safe($old['quantity_in_stock']) ?>" placeholder="เช่น 50">
                <p class="helper">จะเป็นจำนวนสต็อกเริ่มต้นของยานี้</p>
                <p class="field-error">กรอกจำนวนเป็นตัวเลข</p>
            </div>
            <div>
                <label class="label" for="unit_type">หน่วยนับ <span class="req">*</span></label>
                <input id="unit_type" type="text" name="unit_type" class="input" list="unit_list" required value="<?= safe($old['unit_type']) ?>" placeholder="เช่น เม็ด">
                <datalist id="unit_list">
                    <?php foreach ($units as $u): ?><option value="<?= safe($u) ?>"></option><?php endforeach; ?>
                </datalist>
                <p class="helper">เม็ด / แคปซูล / ขวด / หลอด / ซอง / ชิ้น</p>
                <p class="field-error">กรอกหน่วยนับ</p>
            </div>
            <div class="full">
                <label class="label" for="expiry_date">วันหมดอายุ <span class="req">*</span></label>
                <input id="expiry_date" type="date" name="expiry_date" class="input" required min="<?= $today ?>" value="<?= safe($old['expiry_date']) ?>">
                <p class="helper" id="exp_helper">กดไอคอนปฏิทินทางขวาของช่องเพื่อเลือกวัน — รูปแบบในช่องขึ้นกับภาษาของเบราว์เซอร์ ให้ดูวันที่ที่เลือกจากข้อความนี้</p>
                <p class="field-error">เลือกวันหมดอายุที่ยังไม่ผ่านมา</p>
            </div>
            <div class="full">
                <label class="label" for="image_url">ลิงก์รูปภาพ (URL)</label>
                <input id="image_url" type="url" name="image_url" class="input" value="<?= safe($old['image_url']) ?>" placeholder="https://...">
                <p class="helper">ไม่บังคับ — ถ้าไม่ใส่ ระบบจะแสดงว่า "ไม่มีรูป"</p>
            </div>
        </div>

        <div class="actions">
            <a href="medicines.php" class="btn btn-secondary">ยกเลิก</a>
            <button type="submit" class="btn btn-primary" id="btn_save"><span class="spinner" aria-hidden="true"></span><span id="btn_label">บันทึกยาใหม่</span></button>
        </div>
    </form>
</main>

<script>
// ตรวจก่อนส่ง: บอกช่องที่ยังไม่ครบใต้ช่องนั้น (design.md 4.2 Error State) แล้วแสดง Loading State (4.1)
document.getElementById('med_form').addEventListener('submit', function (e) {
    let firstInvalid = null;
    this.querySelectorAll('.input').forEach(el => {
        const bad = !el.checkValidity();
        el.classList.toggle('is-error', bad && el.required);
        if (bad && el.required && !firstInvalid) firstInvalid = el;
    });
    if (firstInvalid) { e.preventDefault(); firstInvalid.focus(); return; }
    const btn = document.getElementById('btn_save');
    btn.disabled = true; btn.classList.add('is-loading');
    document.getElementById('btn_label').textContent = 'กำลังบันทึก…';
});
// แสดงวันที่ที่เลือกเป็นภาษาไทยทันที เพื่อให้ผู้ใช้มั่นใจว่าเลือกถูกวัน (ไม่ต้องเดาว่าช่องเป็น วว/ดด หรือ ดด/วว)
const exp = document.getElementById('expiry_date');
function showExpiry() {
    const h = document.getElementById('exp_helper');
    if (!exp.value) return;
    const d = new Date(exp.value + 'T00:00:00');
    const ce = d.toLocaleDateString('th-TH-u-ca-gregory', { day: 'numeric', month: 'long', year: 'numeric' });
    const be = d.getFullYear() + 543;
    h.innerHTML = `วันที่เลือก: <strong>${ce}</strong> (พ.ศ. ${be})`;
}
exp.addEventListener('change', showExpiry); exp.addEventListener('input', showExpiry); showExpiry();

document.querySelectorAll('.input').forEach(el => el.addEventListener('input', () => {
    if (el.checkValidity()) el.classList.remove('is-error');
}));
</script>
</body>
</html>
