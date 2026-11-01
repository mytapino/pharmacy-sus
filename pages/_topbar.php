<?php
/*
 * Topbar กลางของทุกหน้า (R-06 + design.md Shared Shell)
 * ใช้: $topbar_active = 'home' | 'products' | 'supplier' | 'employees' | 'history' | 'sell'; include '_topbar.php';
 * ปุ่ม "ขายยา" เป็น Primary Action เพียงปุ่มเดียวของ Topbar (design.md 4.1: Primary 1 ปุ่มต่อ Section)
 * CSS ใช้ prefix .pt- เพื่อไม่ชนกับ Bootstrap ในหน้าที่ยังใช้ธีมเก่า
 */
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__ || basename($_SERVER['SCRIPT_NAME'] ?? '') === '_topbar.php') {
    http_response_code(404);
    exit;
}
$topbar_active = $topbar_active ?? '';
$pt_links = [
    'home' => ['index.php', 'หน้าหลัก'],
    'products' => ['medicines.php', 'จัดการผลิตภัณฑ์'],
    'supplier' => ['supplier.php', 'จัดการซัพพลายเออร์'],
    'employees' => ['employees.php', 'จัดการพนักงาน'],
    'history' => ['sales_history.php', 'ประวัติการขาย'],
];
?>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .pt-header { min-height: 64px; background: #FFFFFF; border-bottom: 1px solid #DCE4E1; font-family: "Noto Sans Thai", sans-serif;
                 font-size: 14px; line-height: 22px; position: relative; z-index: 20; }
    .pt-header__inner { width: min(1280px, calc(100% - 64px)); min-height: 64px; margin: 0 auto;
                        display: flex; align-items: center; justify-content: space-between; gap: 32px; }
    .pt-brand { font-size: 16px; line-height: 24px; font-weight: 700; color: #25312F !important; text-decoration: none !important; white-space: nowrap; }
    .pt-nav { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-end; margin: 0; }
    .pt-nav a { color: #66726F; padding: 8px 16px; border-radius: 8px; font-weight: 500; text-decoration: none !important; white-space: nowrap; }
    .pt-nav a:hover { background: #F6F8F7; color: #25312F; }
    .pt-nav a.is-active { color: #245A55; background: #EAF4F2; }
    .pt-divider { width: 1px; height: 24px; background: #DCE4E1; margin: 0 8px; }
    .pt-nav a.pt-cta { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px;
                       background: #2F6F68; color: #FFFFFF; font-weight: 600; }
    .pt-nav a.pt-cta:hover { background: #245A55; color: #FFFFFF; }
    .pt-nav a.pt-cta:focus-visible { outline: none; box-shadow: 0 0 0 3px #EAF4F2, 0 0 0 5px #2F6F68; }
    .pt-nav a.pt-cta.is-active { background: #245A55; color: #FFFFFF; box-shadow: inset 0 -3px 0 #EAF4F2; }
    .pt-nav a.pt-cta svg { flex: 0 0 auto; }
    @media (max-width: 900px) { .pt-header__inner { width: calc(100% - 48px); flex-wrap: wrap; padding: 8px 0; } .pt-divider { display: none; } }
</style>
<header class="pt-header">
    <div class="pt-header__inner">
        <a class="pt-brand" href="index.php">PHARMACY SYSTEM</a>
        <nav class="pt-nav" aria-label="เมนูหลัก">
            <?php foreach ($pt_links as $key => [$href, $label]): ?>
                <a href="<?= $href ?>"<?= $topbar_active === $key ? ' class="is-active" aria-current="page"' : '' ?>><?= $label ?></a>
            <?php endforeach; ?>
            <span class="pt-divider" aria-hidden="true"></span>
            <a href="sell_medicine.php" class="pt-cta<?= $topbar_active === 'sell' ? ' is-active' : '' ?>"<?= $topbar_active === 'sell' ? ' aria-current="page"' : '' ?>>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.6 12.2a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 2-1.5L22 8H6.2"/></svg>
                ขายยา
            </a>
        </nav>
    </div>
</header>
