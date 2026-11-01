<?php
require_once 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$message = '';
$error = '';

if (!$id) {
    header("Location: medicines.php");
    exit();
}

// เมื่อมีการกดบันทึกการแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $generic_name = trim($_POST['generic_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $unit_price = filter_input(INPUT_POST, 'unit_price', FILTER_VALIDATE_FLOAT);
    $quantity_in_stock = filter_input(INPUT_POST, 'quantity_in_stock', FILTER_VALIDATE_INT);
    $unit_type = trim($_POST['unit_type'] ?? '');
    $expiry_date = $_POST['expiry_date'] ?? null;
    $status = (($_POST['status'] ?? 'active') === 'inactive') ? 'inactive' : 'active';
    if ($expiry_date === '') { $expiry_date = null; }

    if ($name && $unit_price !== false && $quantity_in_stock !== false) {
        $stmt = $conn->prepare("
            UPDATE medicines 
            SET name = ?, generic_name = ?, category = ?, unit_price = ?, quantity_in_stock = ?, unit_type = ?, expiry_date = ?, status = ?
            WHERE medicine_id = ?
        ");
        $success = $stmt->execute([$name, $generic_name, $category, $unit_price, $quantity_in_stock, $unit_type, $expiry_date, $status, $id]);

        if ($success) {
            header("Location: medicines.php");
            exit();
        } else {
            $error = 'บันทึกข้อมูลไม่สำเร็จ ลองอีกครั้ง';
        }
    } else {
        $error = 'กรุณากรอกข้อมูลสำคัญให้ถูกต้องและครบถ้วน';
    }
}

// ดึงข้อมูลยาตัวเดิมมาแสดงในฟอร์ม
$stmt = $conn->prepare("SELECT * FROM medicines WHERE medicine_id = ?");
$stmt->execute([$id]);
$med = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$med) {
    header("Location: medicines.php");
    exit();
}

function safe($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHARMACY SYSTEM — แก้ไขข้อมูลยา</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-600: #2F6F68;
            --primary-700: #245A55;
            --primary-50: #EAF4F2;
            --background-page: #F6F8F7;
            --surface: #FFFFFF;
            --text-primary: #25312F;
            --text-secondary: #66726F;
            --border-default: #DCE4E1;
            --danger-600: #B55252;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--background-page);
            color: var(--text-primary);
            font-family: "Noto Sans Thai", sans-serif;
            font-size: 14px;
            line-height: 22px;
        }

        a { color: inherit; text-decoration: none; }

        .site-header {
            min-height: 64px;
            background: var(--surface);
            border-bottom: 1px solid var(--border-default);
        }

        .site-header__inner {
            width: min(1280px, calc(100% - 64px));
            min-height: 64px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand { font-size: 16px; font-weight: 700; }

        .nav { display: flex; gap: 8px; }
        .nav a { color: var(--text-secondary); padding: 8px 16px; border-radius: 8px; font-weight: 500; }
        .nav a.active { color: var(--primary-700); background: var(--primary-50); }

        .page {
            width: min(800px, calc(100% - 64px));
            margin: 0 auto;
            padding: 32px 0 48px;
        }

        .page-header { margin-bottom: 24px; }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border-default);
            border-radius: 12px;
            padding: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width { grid-column: span 2; }

        label { font-weight: 500; color: var(--text-primary); }

        input, select {
            height: 40px;
            border: 1px solid var(--border-default);
            border-radius: 8px;
            padding: 0 16px;
            font-family: inherit;
            font-size: 14px;
            background: var(--surface);
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-600);
        }

        .alert-error {
            padding: 12px 16px;
            background: #FDF2F2;
            color: var(--danger-600);
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid var(--danger-600);
        }

        .form-actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-primary, .btn-secondary {
            height: 40px;
            border-radius: 8px;
            padding: 0 20px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-primary { background: var(--primary-600); color: #FFFFFF; }
        .btn-primary:hover { background: var(--primary-700); }

        .btn-secondary { border: 1px solid var(--border-default); background: var(--surface); color: var(--text-primary); }
        .btn-secondary:hover { border-color: var(--primary-600); }
    </style>
</head>
<body>

<?php $topbar_active = 'products'; include __DIR__ . '/_topbar.php'; ?>

<main class="page">
    <div class="page-header">
        <h1 class="page-title">แก้ไขข้อมูลยา</h1>
    </div>

    <section class="panel">
        <?php if ($error): ?>
            <div class="alert-error"><?php echo safe($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_medicine.php?id=<?php echo $id; ?>">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>ชื่อยา / สินค้า *</label>
                    <input type="text" name="name" value="<?php echo safe($med['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>ชื่อสามัญทางยา (Generic Name)</label>
                    <input type="text" name="generic_name" value="<?php echo safe($med['generic_name']); ?>">
                </div>

                <div class="form-group">
                    <label>หมวดหมู่</label>
                    <input type="text" name="category" value="<?php echo safe($med['category']); ?>">
                </div>

                <div class="form-group">
                    <label>ราคาต่อหน่วย (บาท) *</label>
                    <input type="number" step="0.01" name="unit_price" value="<?php echo safe($med['unit_price']); ?>" required>
                </div>

                <div class="form-group">
                    <label>จำนวนคงเหลือในคลัง *</label>
                    <input type="number" name="quantity_in_stock" value="<?php echo safe($med['quantity_in_stock']); ?>" required>
                </div>

                <div class="form-group">
                    <label>หน่วยนับ (เช่น เม็ด, ขวด, กล่อง)</label>
                    <input type="text" name="unit_type" value="<?php echo safe($med['unit_type']); ?>">
                </div>

                <div class="form-group">
                    <label>วันหมดอายุ</label>
                    <input type="date" name="expiry_date" value="<?php echo safe($med['expiry_date']); ?>">
                </div>

                <div class="form-group full-width">
                    <label>สถานะสินค้า</label>
                    <select name="status">
                        <option value="active" <?php echo ($med['status'] ?? '') === 'active' ? 'selected' : ''; ?>>กำลังขาย</option>
                        <option value="inactive" <?php echo ($med['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>ระงับการขาย</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a href="medicines.php" class="btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn-primary">บันทึกการแก้ไข</button>
            </div>
        </form>
    </section>
</main>

</body>
</html>