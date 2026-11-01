<?php
/*
 * ขายยา — Redesign HTS Final (R-06) · เลย์เอาต์ POS
 * Finding F-01: ผู้ทดสอบหาจุดเพิ่มยาเข้าบิลและปุ่มดำเนินการขายไม่เจอในครั้งแรก (A, B, E, F)
 * สิ่งที่แก้:
 *   1. ยาแสดงเป็นการ์ดมีรูป (R-02 Recognition) กดการ์ดเพื่อเพิ่มเข้าบิลได้ทันที ไม่มีขั้นตอน "เลือกแล้วกดเพิ่ม" แยก
 *   2. ค้นหาจากชื่อยา/ชื่อสามัญ + กรองหมวดหมู่ · กด Enter เพื่อเพิ่มรายการแรก
 *   3. แถบขั้นตอน 1 เลือกยา → 2 ตรวจบิล → 3 รับเงิน (H1)
 *   4. บิลติดขอบขวา (sticky) มี Empty State และปุ่มไปขั้นรับเงินบอกจำนวนรายการ + ยอดรวม
 *   5. ใช้ token / Topbar เดียวกับ Dashboard ตาม design.md
 */
require_once 'db.php';

$medicines = $conn->query("SELECT medicine_id, name, generic_name, category, unit_price, quantity_in_stock, unit_type, image_url
                           FROM medicines WHERE status = 'active' AND quantity_in_stock > 0 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$customers = $conn->query("SELECT * FROM customers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$categories = array_values(array_unique(array_filter(array_map(fn($m) => $m['category'], $medicines))));
sort($categories);

function safe($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$med_json = array_map(fn($m) => [
    'id' => (string)$m['medicine_id'],
    'name' => $m['name'],
    'generic' => $m['generic_name'] ?? '',
    'category' => $m['category'] ?? '',
    'price' => (float)$m['unit_price'],
    'stock' => (int)$m['quantity_in_stock'],
    'unit' => $m['unit_type'] ?: 'ชิ้น',
    'img' => $m['image_url'] ?? '',
], $medicines);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHARMACY SYSTEM — ขายยา</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-600: #2F6F68; --primary-700: #245A55; --primary-50: #EAF4F2;
            --background-page: #F6F8F7; --surface: #FFFFFF;
            --text-primary: #25312F; --text-secondary: #66726F; --border-default: #DCE4E1;
            --warning-600: #A06A2B; --danger-600: #B55252; --success-600: #4F806B;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--background-page); color: var(--text-primary);
               font-family: "Noto Sans Thai", sans-serif; font-size: 14px; line-height: 22px; }
        a { color: inherit; text-decoration: none; }
        button { font-family: inherit; }

        .site-header { min-height: 64px; background: var(--surface); border-bottom: 1px solid var(--border-default); }
        .site-header__inner { width: min(1280px, calc(100% - 64px)); min-height: 64px; margin: 0 auto;
                              display: flex; align-items: center; justify-content: space-between; gap: 32px; }
        .brand { font-size: 16px; line-height: 24px; font-weight: 700; }
        .nav { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
        .nav a { color: var(--text-secondary); padding: 8px 16px; border-radius: 8px; font-weight: 500; }
        .nav a:hover { background: var(--background-page); color: var(--text-primary); }
        .nav a.active { color: var(--primary-700); background: var(--primary-50); }

        .page { width: min(1280px, calc(100% - 64px)); margin: 0 auto; padding: 32px 0 48px; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; flex-wrap: wrap; margin-bottom: 24px; }
        .page-title { margin: 0; font-size: 24px; line-height: 32px; font-weight: 700; }
        .page-subtitle { margin: 4px 0 0; color: var(--text-secondary); }

        /* Step indicator (H1) */
        .steps { display: flex; gap: 8px; margin: 0; padding: 0; list-style: none; flex-wrap: wrap; }
        .step { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px;
                border: 1px solid var(--border-default); background: var(--surface); color: var(--text-secondary); font-weight: 500; }
        .step__num { width: 24px; height: 24px; border-radius: 6px; display: grid; place-items: center;
                     background: var(--background-page); font-size: 12px; font-weight: 600; }
        .step.is-current { border-color: var(--primary-600); color: var(--primary-700); background: var(--primary-50); }
        .step.is-current .step__num { background: var(--primary-600); color: #FFFFFF; }
        .step.is-done { color: var(--success-600); }
        .step__arrow { align-self: center; color: var(--text-secondary); }

        .layout { display: grid; grid-template-columns: minmax(0, 1fr) 400px; gap: 24px; align-items: start; }
        .panel { background: var(--surface); border: 1px solid var(--border-default); border-radius: 12px; padding: 24px; }
        .panel-title { margin: 0; font-size: 16px; line-height: 24px; font-weight: 600; }
        .helper { margin: 8px 0 0; font-size: 12px; line-height: 18px; color: var(--text-secondary); }

        /* Catalog */
        .catalog-tools { display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px; }
        .search { position: relative; }
        .search svg { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); }
        .search input { width: 100%; height: 40px; border: 1px solid var(--border-default); border-radius: 8px;
                        padding: 0 16px 0 44px; font-family: inherit; font-size: 14px; color: var(--text-primary); background: var(--surface); }
        .search input:focus { outline: none; border-color: var(--primary-600); box-shadow: 0 0 0 3px var(--primary-50); }
        .chips { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: thin; }
        .chip { flex: 0 0 auto; white-space: nowrap; }
        .chip { height: 32px; padding: 0 16px; border-radius: 8px; border: 1px solid var(--border-default);
                background: var(--surface); color: var(--text-secondary); font-size: 14px; font-weight: 500; cursor: pointer; }
        .chip:hover { border-color: var(--primary-600); color: var(--primary-700); }
        .chip.is-active { background: var(--primary-50); border-color: var(--primary-600); color: var(--primary-700); }
        .result-count { color: var(--text-secondary); font-size: 12px; }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(176px, 1fr)); gap: 16px; }
        .card { position: relative; display: flex; flex-direction: column; text-align: left; padding: 0; overflow: hidden;
                background: var(--surface); border: 1px solid var(--border-default); border-radius: 12px; cursor: pointer;
                transition: border-color .15s, box-shadow .15s; color: var(--text-primary); font-size: 14px; }
        .card:hover { border-color: var(--primary-600); }
        .card:focus-visible { outline: none; border-color: var(--primary-600); box-shadow: 0 0 0 3px var(--primary-50); }
        .card.in-cart { border-color: var(--primary-600); box-shadow: inset 0 0 0 1px var(--primary-600); }
        .card__img { height: 112px; background: #FFFFFF; display: grid; place-items: center;
                     border-bottom: 1px solid var(--border-default); color: var(--text-secondary); font-size: 12px; }
        .card__imgel { width: 100%; height: 112px; object-fit: contain; padding: 8px; display: grid; place-items: center; background: var(--background-page); }
        img.card__imgel { background: #FFFFFF; }
        .card__body { padding: 12px 16px 16px; display: flex; flex-direction: column; gap: 2px; flex: 1; width: 100%; }
        .card__name { font-weight: 600; line-height: 20px; }
        .card__generic { font-size: 12px; line-height: 18px; color: var(--text-secondary); }
        .card__foot { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 8px; }
        .card__price { font-weight: 600; }
        .card__stock { font-size: 12px; line-height: 18px; color: var(--text-secondary); }
        .card__stock.is-low { color: var(--warning-600); }
        .card__add { width: 32px; height: 32px; border-radius: 8px; background: var(--primary-600); color: #FFFFFF;
                     display: grid; place-items: center; font-size: 18px; line-height: 1; }
        .card:hover .card__add { background: var(--primary-700); }
        .card__badge { position: absolute; top: 8px; right: 8px; background: var(--primary-600); color: #FFFFFF;
                       font-size: 12px; font-weight: 600; line-height: 16px; padding: 4px 8px; border-radius: 6px; }
        .catalog-empty { grid-column: 1 / -1; padding: 48px 16px; text-align: center; color: var(--text-secondary); }

        /* Bill */
        .bill { position: sticky; top: 24px; display: flex; flex-direction: column; max-height: calc(100vh - 48px); }
        .bill__head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 16px; }
        .bill__count { font-size: 12px; color: var(--text-secondary); }
        .bill__list { list-style: none; margin: 0; padding: 0; overflow-y: auto; min-height: 120px; }
        .line { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; gap: 12px; padding: 12px 0;
                border-bottom: 1px solid var(--border-default); align-items: center; }
        .line:first-child { padding-top: 0; }
        .line__thumb { width: 40px; height: 40px; border-radius: 8px; border: 1px solid var(--border-default);
                       background: #FFFFFF; object-fit: contain; display: grid; place-items: center;
                       font-size: 10px; line-height: 12px; text-align: center; color: var(--text-secondary); }
        .line__name { font-weight: 600; line-height: 20px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .line__meta { font-size: 12px; line-height: 18px; color: var(--text-secondary); }
        .line__right { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
        .line__sub { font-weight: 600; }
        .qty { display: inline-flex; align-items: center; border: 1px solid var(--border-default); border-radius: 8px; overflow: hidden; }
        .qty button { width: 28px; height: 28px; border: none; background: var(--surface); color: var(--primary-700); font-size: 16px; cursor: pointer; }
        .qty button:hover { background: var(--primary-50); }
        .qty input { width: 40px; height: 28px; border: none; border-left: 1px solid var(--border-default);
                     border-right: 1px solid var(--border-default); text-align: center; font-family: inherit; font-size: 14px; }
        .link-danger { color: var(--danger-600); background: none; border: none; cursor: pointer; font-size: 12px; padding: 0; }
        .bill-empty { padding: 32px 8px; text-align: center; color: var(--text-secondary); }
        .bill-empty strong { display: block; color: var(--text-primary); font-size: 16px; margin-bottom: 4px; }

        .customer { padding-top: 16px; margin-top: 16px; border-top: 1px solid var(--border-default); }
        .customer__label { font-weight: 500; margin-bottom: 8px; }
        .radio-row { display: flex; gap: 16px; }
        .radio-row label { display: flex; gap: 8px; align-items: center; cursor: pointer; }
        #mem_wrap { margin-top: 8px; }

        .total { display: flex; justify-content: space-between; align-items: baseline; padding-top: 16px; margin-top: 16px; border-top: 1px solid var(--border-default); }
        .total__label { font-size: 16px; font-weight: 600; }
        .total__value { font-size: 28px; line-height: 36px; font-weight: 700; }
        .btn-primary { width: 100%; min-height: 48px; margin-top: 16px; border: none; border-radius: 8px; padding: 8px 16px;
                       background: var(--primary-600); color: #FFFFFF; font-size: 16px; font-weight: 600; cursor: pointer; }
        .btn-primary:hover { background: var(--primary-700); }
        .btn-primary:disabled { background: #E3E8E6; color: var(--text-secondary); cursor: not-allowed; }

        .toast { position: fixed; left: 50%; bottom: 32px; transform: translate(-50%, 8px); background: var(--text-primary); color: #FFFFFF;
                 padding: 12px 16px; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.16); opacity: 0;
                 transition: all .2s; pointer-events: none; z-index: 10; }
        .toast.show { opacity: 1; transform: translate(-50%, 0); }

        .select2-container .select2-selection--single { height: 40px; border: 1px solid var(--border-default); border-radius: 8px; }
        .select2-container .select2-selection--single .select2-selection__rendered { line-height: 38px; padding-left: 16px; }
        .select2-container .select2-selection--single .select2-selection__arrow { height: 38px; }
        .select2-dropdown { border-color: var(--border-default); font-family: "Noto Sans Thai", sans-serif; }
        .select2-results__option--highlighted { background: var(--primary-50) !important; color: var(--text-primary) !important; }

        @media (max-width: 1080px) { .layout { grid-template-columns: 1fr; } .bill { position: static; max-height: none; } }
    </style>
</head>
<body>

<header class="site-header">
    <div class="site-header__inner">
        <div class="brand">PHARMACY SYSTEM</div>
        <nav class="nav" aria-label="เมนูหลัก">
            <a href="index.php">หน้าหลัก</a>
            <a href="medicines.php">จัดการผลิตภัณฑ์</a>
            <a href="supplier.php">จัดการซัพพลายเออร์</a>
            <a href="employees.php">จัดการพนักงาน</a>
            <a href="sales_history.php">ประวัติการขาย</a>
            <a class="active" href="sell_medicine.php">ขายยา</a>
        </nav>
    </div>
</header>

<main class="page">
    <div class="page-header">
        <div>
            <h1 class="page-title">ขายยา</h1>
            <p class="page-subtitle">กดที่การ์ดยาเพื่อเพิ่มเข้าบิล แล้วไปขั้นตอนรับชำระเงิน · เจ้าหน้าที่: <?= safe(DEFAULT_EMPLOYEE_NAME) ?></p>
        </div>
        <ol class="steps" aria-label="ขั้นตอนการขาย">
            <li class="step is-current" id="step1"><span class="step__num">1</span>เลือกยาเข้าบิล</li>
            <li class="step__arrow" aria-hidden="true">›</li>
            <li class="step" id="step2"><span class="step__num">2</span>ตรวจบิล</li>
            <li class="step__arrow" aria-hidden="true">›</li>
            <li class="step"><span class="step__num">3</span>รับชำระเงิน</li>
        </ol>
    </div>

    <div class="layout">
        <section class="panel" aria-label="รายการยา">
            <div class="catalog-tools">
                <label class="search">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input type="search" id="q" placeholder="ค้นหาชื่อยาหรือชื่อสามัญ เช่น Para, Cetirizine" autocomplete="off" aria-label="ค้นหายา" autofocus>
                </label>
                <div class="chips" role="group" aria-label="หมวดหมู่">
                    <button type="button" class="chip is-active" data-cat="">ทั้งหมด</button>
                    <?php foreach ($categories as $c): ?>
                        <button type="button" class="chip" data-cat="<?= safe($c) ?>"><?= safe($c) ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="result-count" id="result_count"></div>
            </div>
            <div class="grid" id="grid"></div>
        </section>

        <form action="process_sale.php" method="POST" class="panel bill" id="bill_form">
            <div class="bill__head">
                <h2 class="panel-title">บิลขาย</h2>
                <span class="bill__count" id="bill_count">0 รายการ</span>
            </div>
            <input type="hidden" name="customer_id" id="h_cust_id" value="9999">
            <ul class="bill__list" id="bill_list"></ul>
            <div id="bill_inputs"></div>

            <div class="customer">
                <div class="customer__label">ลูกค้า</div>
                <div class="radio-row">
                    <label><input type="radio" name="cust_type" value="guest" checked onclick="toggleCust(false)"> ลูกค้าทั่วไป</label>
                    <label><input type="radio" name="cust_type" value="member" onclick="toggleCust(true)"> สมาชิก</label>
                </div>
                <div id="mem_wrap" style="display:none;">
                    <select id="cust_select" class="select2-cust" onchange="updateCust()">
                        <option value="9999">ค้นหาชื่อหรือเบอร์โทร</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= (int)$c['customer_id'] ?>"><?= safe($c['name']) ?> (<?= safe($c['phone']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="total">
                <span class="total__label">ยอดรวมที่ต้องชำระ</span>
                <span class="total__value">฿<span id="total_price">0.00</span></span>
            </div>
            <input type="hidden" name="grand_total" id="h_total" value="0">
            <button type="submit" class="btn-primary" id="btn_checkout" disabled>ไปขั้นตอนรับชำระเงิน</button>
            <p class="helper" id="checkout_hint">เพิ่มยาเข้าบิลอย่างน้อย 1 รายการก่อนไปขั้นตอนรับชำระเงิน</p>
        </form>
    </div>
</main>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const MEDS = <?= json_encode($med_json, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let cart = [];
let filterCat = '';
let visible = [];
const fmt = n => n.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
function imgFail(el) { const s = document.createElement('span'); s.className = el.className; s.textContent = 'ไม่มีรูป'; el.replaceWith(s); }
const thumb = (m, cls) => m.img
    ? `<img class="${cls}" src="${esc(m.img)}" alt="" loading="lazy" referrerpolicy="no-referrer" onerror="imgFail(this)">`
    : `<span class="${cls}">ไม่มีรูป</span>`;

function renderGrid() {
    const q = document.getElementById('q').value.trim().toLowerCase();
    visible = MEDS.filter(m => (!filterCat || m.category === filterCat) &&
        (!q || m.name.toLowerCase().includes(q) || m.generic.toLowerCase().includes(q)));
    const grid = document.getElementById('grid');
    if (!visible.length) {
        grid.innerHTML = `<div class="catalog-empty"><strong>ไม่พบยาที่ค้นหา</strong><br>ลองพิมพ์ชื่อสามัญ หรือเลือกหมวด "ทั้งหมด"</div>`;
    } else {
        grid.innerHTML = visible.map(m => {
            const inCart = cart.find(i => i.id === m.id);
            return `<button type="button" class="card${inCart ? ' in-cart' : ''}" onclick="addItem('${m.id}')" aria-label="เพิ่ม ${esc(m.name)} เข้าบิล">
                ${inCart ? `<span class="card__badge">ในบิล ${inCart.qty}</span>` : ''}
                <div class="card__img">${thumb(m, 'card__imgel')}</div>
                <div class="card__body">
                    <div class="card__name">${esc(m.name)}</div>
                    <div class="card__generic">${esc(m.generic || '-')}</div>
                    <div class="card__stock${m.stock <= 20 ? ' is-low' : ''}">คงเหลือ ${m.stock} ${esc(m.unit)}</div>
                    <div class="card__foot"><span class="card__price">฿${fmt(m.price)}<span class="card__generic">/${esc(m.unit)}</span></span><span class="card__add" aria-hidden="true">+</span></div>
                </div>
            </button>`;
        }).join('');
    }
    document.getElementById('result_count').textContent =
        `แสดง ${visible.length} จาก ${MEDS.length} รายการ` + (q && visible.length ? ' · กด Enter เพื่อเพิ่มรายการแรก' : '');
}

function toast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(t._h); t._h = setTimeout(() => t.classList.remove('show'), 2000);
}

function addItem(id) {
    const m = MEDS.find(x => x.id === id);
    const found = cart.find(i => i.id === id);
    if (found) {
        if (found.qty >= m.stock) { toast(`${m.name} คงเหลือ ${m.stock} ${m.unit} เพิ่มไม่ได้อีก`); return; }
        found.qty++;
    } else {
        cart.push({ id, qty: 1 });
    }
    render();
    toast(`เพิ่ม ${m.name} เข้าบิลแล้ว`);
}

function setQty(id, value) {
    const m = MEDS.find(x => x.id === id);
    const item = cart.find(i => i.id === id);
    let q = parseInt(value, 10);
    if (isNaN(q) || q < 1) q = 1;
    if (q > m.stock) { q = m.stock; toast(`${m.name} คงเหลือ ${m.stock} ${m.unit}`); }
    item.qty = q;
    render();
}

function removeItem(id) { cart = cart.filter(i => i.id !== id); render(); }

function render() {
    const list = document.getElementById('bill_list');
    const inputs = document.getElementById('bill_inputs');
    let total = 0;
    if (!cart.length) {
        list.innerHTML = `<li class="bill-empty"><strong>ยังไม่มียาในบิล</strong>กดที่การ์ดยาทางซ้ายเพื่อเพิ่มเข้าบิล</li>`;
        inputs.innerHTML = '';
    } else {
        list.innerHTML = cart.map(i => {
            const m = MEDS.find(x => x.id === i.id);
            const sub = m.price * i.qty; total += sub;
            return `<li class="line">
                ${thumb(m, 'line__thumb')}
                <div style="min-width:0">
                    <div class="line__name" title="${esc(m.name)}">${esc(m.name)}</div>
                    <div class="line__meta">฿${fmt(m.price)}/${esc(m.unit)}</div>
                    <button type="button" class="link-danger" onclick="removeItem('${i.id}')">ลบออกจากบิล</button>
                </div>
                <div class="line__right">
                    <div class="qty">
                        <button type="button" aria-label="ลดจำนวน" onclick="setQty('${i.id}', ${i.qty - 1})">−</button>
                        <input type="number" min="1" max="${m.stock}" value="${i.qty}" aria-label="จำนวน ${esc(m.name)}" onchange="setQty('${i.id}', this.value)">
                        <button type="button" aria-label="เพิ่มจำนวน" onclick="setQty('${i.id}', ${i.qty + 1})">+</button>
                    </div>
                    <span class="line__sub">฿${fmt(sub)}</span>
                </div>
            </li>`;
        }).join('');
        inputs.innerHTML = cart.map((i, idx) =>
            `<input type="hidden" name="items[${idx}][id]" value="${esc(i.id)}"><input type="hidden" name="items[${idx}][qty]" value="${i.qty}">`).join('');
    }
    document.getElementById('total_price').textContent = fmt(total);
    document.getElementById('h_total').value = total.toFixed(2);
    document.getElementById('bill_count').textContent = `${cart.length} รายการ`;

    const btn = document.getElementById('btn_checkout');
    btn.disabled = !cart.length;
    btn.textContent = cart.length ? `ไปขั้นตอนรับชำระเงิน (${cart.length} รายการ · ฿${fmt(total)})` : 'ไปขั้นตอนรับชำระเงิน';
    document.getElementById('checkout_hint').textContent = cart.length
        ? 'ตรวจรายการและจำนวนให้ถูกต้อง แล้วกดปุ่มด้านบนเพื่อรับเงิน'
        : 'เพิ่มยาเข้าบิลอย่างน้อย 1 รายการก่อนไปขั้นตอนรับชำระเงิน';
    document.getElementById('step1').className = 'step ' + (cart.length ? 'is-done' : 'is-current');
    document.getElementById('step2').className = 'step ' + (cart.length ? 'is-current' : '');
    renderGrid();
}

function toggleCust(isMem) { $('#mem_wrap').toggle(isMem); if (!isMem) $('#h_cust_id').val('9999'); }
function updateCust() { $('#h_cust_id').val($('#cust_select').val()); }

$(function () {
    $('.select2-cust').select2({ width: '100%' });
    $(document).on('select2:open', () => {
        const f = document.querySelector('.select2-container--open .select2-search__field');
        if (f) f.focus();
    });
    document.getElementById('q').addEventListener('input', renderGrid);
    document.getElementById('q').addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); if (visible.length) { addItem(visible[0].id); e.target.select(); } }
    });
    document.querySelectorAll('.chip').forEach(ch => ch.addEventListener('click', () => {
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('is-active'));
        ch.classList.add('is-active'); filterCat = ch.dataset.cat; renderGrid();
    }));
    document.getElementById('bill_form').addEventListener('submit', () => {
        const b = document.getElementById('btn_checkout'); b.disabled = true; b.textContent = 'กำลังบันทึกบิล…';
    });
    render();
});
</script>
</body>
</html>
