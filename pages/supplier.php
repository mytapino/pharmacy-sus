<?php
require_once 'db.php';

$medicine_id = filter_input(INPUT_GET, 'medicine_id', FILTER_VALIDATE_INT);
$product = null;
$supplier = null;

if ($medicine_id) {
    $stmt = $conn->prepare("
        SELECT
            m.medicine_id,
            m.name,
            m.generic_name,
            m.category,
            m.unit_price,
            m.quantity_in_stock,
            m.expiry_date,
            m.unit_type,
            m.image_url,
            m.supplier_id,
            s.name AS supplier_name,
            s.contact_person,
            s.phone,
            s.email,
            s.address
        FROM medicines m
        LEFT JOIN suppliers s ON m.supplier_id = s.supplier_id
        WHERE m.medicine_id = ?
        LIMIT 1
    ");
    $stmt->execute([$medicine_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && !empty($product['supplier_id'])) {
        $supplier = [
            'supplier_id' => $product['supplier_id'],
            'name' => $product['supplier_name'],
            'contact_person' => $product['contact_person'],
            'phone' => $product['phone'],
            'email' => $product['email'],
            'address' => $product['address'],
        ];
    }
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
    <title>PHARMACY SYSTEM — ซัพพลายเออร์</title>

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
            --info-600: #4F7390;
            --warning-600: #A06A2B;
            --danger-600: #B55252;
            --success-600: #4F806B;
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

        a { color: inherit; }

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
            gap: 32px;
        }

        .brand { font-size: 16px; line-height: 24px; font-weight: 700; }

        .nav {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
        }

        .nav a {
            color: var(--text-secondary);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
        }

        .nav a:hover { background: var(--background-page); color: var(--text-primary); }
        .nav a.active { color: var(--primary-700); background: var(--primary-50); }

        .page {
            width: min(1280px, calc(100% - 64px));
            margin: 0 auto;
            padding: 32px 0 48px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 32px;
        }

        .page-title { margin: 0; font-size: 24px; line-height: 32px; font-weight: 700; }
        .page-subtitle { margin: 8px 0 0; color: var(--text-secondary); }

        .btn-secondary,
        .contact-link {
            min-height: 40px;
            border-radius: 8px;
            padding: 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-weight: 500;
        }

        .btn-secondary {
            border: 1px solid var(--border-default);
            background: var(--surface);
            color: var(--text-primary);
        }

        .btn-secondary:hover { border-color: var(--primary-600); color: var(--primary-700); }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border-default);
            border-radius: 12px;
            padding: 24px;
        }

        .product-context {
            display: grid;
            grid-template-columns: 72px minmax(0, 1fr) auto;
            gap: 24px;
            align-items: center;
        }

        .product-thumb {
            width: 72px;
            height: 72px;
            border: 1px solid var(--border-default);
            border-radius: 8px;
            object-fit: cover;
            background: var(--background-page);
        }

        .product-placeholder {
            width: 72px;
            height: 72px;
            border: 1px solid var(--border-default);
            border-radius: 8px;
            background: var(--background-page);
            display: grid;
            place-items: center;
            color: var(--text-secondary);
        }

        .product-kicker { color: var(--text-secondary); font-size: 12px; line-height: 18px; margin-bottom: 8px; }
        .product-name { font-size: 20px; line-height: 28px; font-weight: 600; }
        .product-meta { margin-top: 8px; color: var(--text-secondary); display: flex; gap: 8px; flex-wrap: wrap; }

        .stock-block { text-align: right; white-space: nowrap; }
        .stock-label { color: var(--text-secondary); font-size: 12px; line-height: 18px; }
        .stock-value { margin-top: 8px; color: var(--warning-600); font-size: 20px; line-height: 28px; font-weight: 600; }

        .section { margin-top: 32px; }
        .section-title { margin: 0 0 16px; font-size: 20px; line-height: 28px; font-weight: 600; }

        .info-note {
            margin-top: 16px;
            border: 1px solid var(--border-default);
            border-left: 4px solid var(--info-600);
            border-radius: 8px;
            padding: 16px;
            background: var(--surface);
        }

        .warning-note {
            margin-top: 16px;
            border: 1px solid var(--border-default);
            border-left: 4px solid var(--warning-600);
            border-radius: 8px;
            padding: 16px;
            background: var(--surface);
        }

        .supplier-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0;
            border-top: 1px solid var(--border-default);
            border-bottom: 1px solid var(--border-default);
        }

        .summary-item {
            padding: 16px 24px;
        }

        .summary-item + .summary-item {
            border-left: 1px solid var(--border-default);
        }

        .summary-label { color: var(--text-secondary); font-size: 12px; line-height: 18px; }
        .summary-value { margin-top: 8px; font-size: 16px; line-height: 24px; font-weight: 600; }

        .supplier-card {
            display: grid;
            grid-template-columns: minmax(0,1fr) minmax(0,1fr);
            gap: 24px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 140px minmax(0, 1fr);
            gap: 8px 16px;
        }

        .detail-label { color: var(--text-secondary); }
        .detail-value { color: var(--text-primary); }

        .contact-actions {
            margin-top: 24px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .contact-link {
            border: 1px solid var(--border-default);
            background: var(--surface);
            color: var(--primary-700);
        }

        .contact-link:hover { background: var(--primary-50); }
        .contact-link.is-disabled { color: var(--text-secondary); pointer-events: none; }

        .empty-state {
            padding: 48px 24px;
            text-align: center;
        }

        .empty-title { font-size: 16px; line-height: 24px; font-weight: 600; }
        .empty-text { margin-top: 8px; color: var(--text-secondary); }

        @media (max-width: 900px) {
            .site-header__inner, .page { width: calc(100% - 48px); }
            .page-header { flex-direction: column; }
            .supplier-card { grid-template-columns: 1fr; }
            .supplier-grid { grid-template-columns: 1fr; }
            .summary-item + .summary-item { border-left: 0; border-top: 1px solid var(--border-default); }
        }

        @media (max-width: 640px) {
            .site-header__inner, .page { width: calc(100% - 32px); }
            .site-header__inner { flex-direction: column; align-items: flex-start; padding: 16px 0; }
            .nav { justify-content: flex-start; }
            .product-context { grid-template-columns: 72px minmax(0,1fr); }
            .stock-block { grid-column: 2; text-align: left; }
            .detail-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php $topbar_active = 'supplier'; include __DIR__ . '/_topbar.php'; ?>

<main class="page">
    <div class="page-header">
        <div>
            <h1 class="page-title">ซัพพลายเออร์ที่เคยซื้อ</h1>
            <p class="page-subtitle">ดูข้อมูลซัพพลายเออร์ของสินค้าที่เลือกจาก Dashboard</p>
        </div>
        <a class="btn-secondary" href="index.php">← กลับไปภาพรวม</a>
    </div>

    <?php if (!$medicine_id || !$product): ?>
        <section class="panel empty-state">
            <div class="empty-title">ยังไม่พบสินค้าที่ต้องการดูซัพพลายเออร์</div>
            <div class="empty-text">กลับไปที่ Dashboard แล้วเลือก “ดูซัพพลายเออร์” จากสินค้าใกล้หมดสต็อก</div>
        </section>
    <?php else: ?>

        <!-- Product Context -->
        <section class="panel">
            <div class="product-context">
                <?php if (!empty($product['image_url'])): ?>
                    <img class="product-thumb" src="<?php echo safe($product['image_url']); ?>" alt="ภาพบรรจุภัณฑ์ <?php echo safe($product['name']); ?>">
                <?php else: ?>
                    <div class="product-placeholder">ไม่มีรูป</div>
                <?php endif; ?>

                <div>
                    <div class="product-kicker">สินค้าที่กำลังตรวจสอบซัพพลายเออร์</div>
                    <div class="product-name"><?php echo safe($product['name']); ?></div>
                    <div class="product-meta">
                        <?php if ($product['generic_name']): ?><span><?php echo safe($product['generic_name']); ?></span><?php endif; ?>
                        <?php if ($product['category']): ?><span>• <?php echo safe($product['category']); ?></span><?php endif; ?>
                        <?php if ($product['unit_type']): ?><span>• หน่วย: <?php echo safe($product['unit_type']); ?></span><?php endif; ?>
                    </div>
                </div>

                <div class="stock-block">
                    <div class="stock-label">จำนวนคงเหลือ</div>
                    <div class="stock-value">
                        <?php echo number_format((int)$product['quantity_in_stock']); ?>
                        <?php echo safe($product['unit_type'] ?: 'หน่วย'); ?>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!$supplier): ?>
            <section class="section panel empty-state">
                <div class="empty-title">ยังไม่มีข้อมูลซัพพลายเออร์สำหรับสินค้านี้</div>
                <div class="empty-text">ตรวจสอบข้อมูลสินค้าในเมนูจัดการสต็อกยา แล้วกำหนดซัพพลายเออร์ให้สินค้านี้ก่อน</div>
            </section>
        <?php else: ?>

            <!-- Compact Summary -->
            <section class="section">
                <div class="supplier-grid">
                    <div class="summary-item">
                        <div class="summary-label">ซัพพลายเออร์ที่ผูกกับสินค้านี้</div>
                        <div class="summary-value">1 ราย</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">ราคาสินค้าในฐานข้อมูล</div>
                        <div class="summary-value">฿<?php echo number_format((float)$product['unit_price'], 2); ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">ข้อมูลประวัติการซื้อ</div>
                        <div class="summary-value">ยังไม่มีใน schema ปัจจุบัน</div>
                    </div>
                </div>

                <div class="warning-note">
                    ระบบปัจจุบันเก็บซัพพลายเออร์ไว้เพียง 1 รายต่อสินค้า และยังไม่มีตารางประวัติการซื้อจากซัพพลายเออร์
                    จึงยังไม่สามารถเปรียบเทียบราคาซื้อล่าสุด เงื่อนไขการซื้อ และประวัติหลายซัพพลายเออร์ได้โดยไม่เพิ่มฐานข้อมูล
                </div>
            </section>

            <!-- Supplier Details -->
            <section class="section panel">
                <h2 class="section-title">ข้อมูลซัพพลายเออร์</h2>

                <div class="supplier-card">
                    <div>
                        <div class="detail-grid">
                            <div class="detail-label">ชื่อบริษัท</div>
                            <div class="detail-value"><?php echo safe($supplier['name'] ?: 'ยังไม่มีข้อมูล'); ?></div>

                            <div class="detail-label">ผู้ติดต่อ</div>
                            <div class="detail-value"><?php echo safe($supplier['contact_person'] ?: 'ยังไม่มีข้อมูลผู้ติดต่อ'); ?></div>

                            <div class="detail-label">โทรศัพท์</div>
                            <div class="detail-value"><?php echo safe($supplier['phone'] ?: 'ยังไม่มีข้อมูลโทรศัพท์'); ?></div>

                            <div class="detail-label">อีเมล</div>
                            <div class="detail-value"><?php echo safe($supplier['email'] ?: 'ยังไม่มีข้อมูลอีเมล'); ?></div>

                            <div class="detail-label">ที่อยู่</div>
                            <div class="detail-value"><?php echo safe($supplier['address'] ?: 'ยังไม่มีข้อมูลที่อยู่'); ?></div>
                        </div>

                        <div class="contact-actions">
                            <?php if (!empty($supplier['phone'])): ?>
                                <a class="contact-link" href="tel:<?php echo safe($supplier['phone']); ?>">โทรหาซัพพลายเออร์</a>
                            <?php else: ?>
                                <span class="contact-link is-disabled">ยังไม่มีเบอร์โทรศัพท์</span>
                            <?php endif; ?>

                            <?php if (!empty($supplier['email'])): ?>
                                <a class="contact-link" href="mailto:<?php echo safe($supplier['email']); ?>">ส่งอีเมล</a>
                            <?php else: ?>
                                <span class="contact-link is-disabled">ยังไม่มีอีเมล</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="detail-grid">
                            <div class="detail-label">สินค้าที่เลือก</div>
                            <div class="detail-value"><?php echo safe($product['name']); ?></div>

                            <div class="detail-label">ราคาที่เก็บใน medicines</div>
                            <div class="detail-value">฿<?php echo number_format((float)$product['unit_price'], 2); ?></div>

                            <div class="detail-label">ราคาซื้อล่าสุด</div>
                            <div class="detail-value">ยังไม่มีข้อมูลในฐานข้อมูล</div>

                            <div class="detail-label">วันที่ซื้อครั้งล่าสุด</div>
                            <div class="detail-value">ยังไม่มีข้อมูลในฐานข้อมูล</div>

                            <div class="detail-label">เงื่อนไขการซื้อ</div>
                            <div class="detail-value">ยังไม่มีข้อมูลในฐานข้อมูล</div>
                        </div>

                        <div class="info-note">
                            ราคาใน medicines เป็นค่าที่ระบบมีอยู่ในปัจจุบัน แต่ไม่ได้ยืนยันว่าเป็น “ราคาซื้อครั้งล่าสุด”
                            จึงไม่ใช้แทนข้อมูล Purchase History โดยอัตโนมัติ
                        </div>
                    </div>
                </div>
            </section>

        <?php endif; ?>
    <?php endif; ?>
</main>

</body>
</html>
