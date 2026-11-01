<?php
/*
 * ขายยา — Redesign HTS Final (R-06)
 * Finding F-01: ผู้ทดสอบหาจุดเพิ่มยาเข้าบิลและปุ่มดำเนินการขายไม่เจอในครั้งแรก (A, B, E, F)
 * สิ่งที่แก้:
 *   1. เลือกยาแล้วเพิ่มเข้าบิลทันที (ตัดขั้นตอนกดปุ่ม "+ เพิ่มเข้าบิล" ที่ผู้ใช้มองข้าม)
 *   2. แถบขั้นตอน 1 เลือกยา → 2 ตรวจบิล → 3 รับเงิน บอกว่าอยู่ขั้นไหน (H1)
 *   3. บิลว่างมี Empty State บอกสิ่งที่ต้องทำต่อ / ปุ่มไปขั้นรับเงินบอกจำนวนรายการและปิดไว้เมื่อบิลว่าง
 *   4. ตัวเลือกยาแสดงชื่อสามัญ ราคา และคงเหลือ + ปุ่ม − / + ปรับจำนวน
 *   5. ย้ายหน้าเข้าระบบ token ของ design.md (Noto Sans Thai, primary-600, radius 8/12, Topbar เดียวกับ Dashboard)
 */
require_once 'db.php';

$medicines = $conn->query("SELECT medicine_id, name, generic_name, unit_price, quantity_in_stock, unit_type
                           FROM medicines WHERE status = 'active' AND quantity_in_stock > 0 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$customers = $conn->query("SELECT * FROM customers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

function safe($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
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
            --danger-600: #B55252; --success-600: #4F806B;
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

        .page { width: min(1280px, calc(100% - 64px)); margin: 0 auto; padding: 32px 0 48px; }
        .page-title { margin: 0; font-size: 24px; line-height: 32px; font-weight: 700; }
        .page-subtitle { margin: 4px 0 0; color: var(--text-secondary); }

        /* Step indicator (H1 Visibility of system status) */
        .steps { display: flex; gap: 8px; margin: 24px 0; padding: 0; list-style: none; flex-wrap: wrap; }
        .step { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px;
                border: 1px solid var(--border-default); background: var(--surface); color: var(--text-secondary); font-weight: 500; }
        .step__num { width: 24px; height: 24px; border-radius: 6px; display: grid; place-items: center;
                     background: var(--background-page); font-size: 12px; font-weight: 600; }
        .step.is-current { border-color: var(--primary-600); color: var(--primary-700); background: var(--primary-50); }
        .step.is-current .step__num { background: var(--primary-600); color: #FFFFFF; }
        .step.is-done { color: var(--success-600); }
        .step__arrow { align-self: center; color: var(--text-secondary); }

        .layout { display: grid; grid-template-columns: 4fr 8fr; gap: 24px; align-items: start; }
        .panel { background: var(--surface); border: 1px solid var(--border-default); border-radius: 12px; padding: 24px; }
        .panel + .panel { margin-top: 24px; }
        .panel-title { margin: 0 0 16px; font-size: 16px; line-height: 24px; font-weight: 600; }
        .helper { margin: 8px 0 0; font-size: 12px; line-height: 18px; color: var(--text-secondary); }
        .label { display: block; font-weight: 500; margin-bottom: 8px; }

        .radio-row { display: flex; gap: 16px; margin-bottom: 16px; }
        .radio-row label { display: flex; gap: 8px; align-items: center; cursor: pointer; }

        .table { width: 100%; border-collapse: collapse; }
        .table th { background: var(--background-page); font-weight: 600; text-align: left; padding: 12px 16px;
                    border-bottom: 1px solid var(--border-default); }
        .table td { padding: 12px 16px; border-bottom: 1px solid var(--border-default); vertical-align: middle; }
        .num { text-align: right; white-space: nowrap; }
        .item-name { font-weight: 600; }
        .item-sub { font-size: 12px; line-height: 18px; color: var(--text-secondary); }

        .qty { display: inline-flex; align-items: center; border: 1px solid var(--border-default); border-radius: 8px; overflow: hidden; }
        .qty button { width: 32px; height: 32px; border: none; background: var(--surface); color: var(--primary-700);
                      font-size: 16px; cursor: pointer; font-family: inherit; }
        .qty button:hover { background: var(--primary-50); }
        .qty input { width: 48px; height: 32px; border: none; border-left: 1px solid var(--border-default);
                     border-right: 1px solid var(--border-default); text-align: center; font-family: inherit; font-size: 14px; }
        .link-danger { color: var(--danger-600); background: none; border: none; cursor: pointer; font-family: inherit; font-size: 14px; }

        .empty-state { padding: 40px 16px; text-align: center; color: var(--text-secondary); }
        .empty-state strong { display: block; color: var(--text-primary); font-size: 16px; margin-bottom: 4px; }

        .summary { display: flex; justify-content: space-between; align-items: baseline; padding-top: 24px; margin-top: 8px; }
        .summary__label { font-size: 16px; font-weight: 600; }
        .summary__value { font-size: 28px; line-height: 36px; font-weight: 700; }

        .btn-primary { width: 100%; height: 48px; margin-top: 16px; border: none; border-radius: 8px;
                       background: var(--primary-600); color: #FFFFFF; font-family: inherit; font-size: 16px; font-weight: 600; cursor: pointer; }
        .btn-primary:hover { background: var(--primary-700); }
        .btn-primary:disabled { background: #E3E8E6; color: var(--text-secondary); cursor: not-allowed; }

        .toast { position: fixed; right: 32px; bottom: 32px; background: var(--text-primary); color: #FFFFFF;
                 padding: 12px 16px; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.16); opacity: 0;
                 transform: translateY(8px); transition: all .2s; pointer-events: none; }
        .toast.show { opacity: 1; transform: none; }

        /* Select2 ให้ตรง token Input ของ design.md */
        .select2-container .select2-selection--single { height: 40px; border: 1px solid var(--border-default); border-radius: 8px; }
        .select2-container .select2-selection--single .select2-selection__rendered { line-height: 38px; padding-left: 16px; color: var(--text-secondary); }
        .select2-container .select2-selection--single .select2-selection__arrow { height: 38px; }
        .select2-container--open .select2-selection--single { border-color: var(--primary-600); }
        .select2-results__option--highlighted { background: var(--primary-50) !important; color: var(--text-primary) !important; }
        .select2-dropdown { border-color: var(--border-default); border-radius: 8px; font-family: "Noto Sans Thai", sans-serif; }
        .opt-name { font-weight: 600; }
        .opt-meta { font-size: 12px; color: var(--text-secondary); }

        @media (max-width: 960px) { .layout { grid-template-columns: 1fr; } }
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
    <h1 class="page-title">ขายยา</h1>
    <p class="page-subtitle">เลือกยาเข้าบิล ตรวจจำนวน แล้วไปขั้นตอนรับชำระเงิน · เจ้าหน้าที่: <?= safe(DEFAULT_EMPLOYEE_NAME) ?></p>

    <ol class="steps" aria-label="ขั้นตอนการขาย">
        <li class="step is-current" id="step1"><span class="step__num">1</span>เลือกยาเข้าบิล</li>
        <li class="step__arrow" aria-hidden="true">›</li>
        <li class="step" id="step2"><span class="step__num">2</span>ตรวจบิล</li>
        <li class="step__arrow" aria-hidden="true">›</li>
        <li class="step"><span class="step__num">3</span>รับชำระเงิน</li>
    </ol>

    <div class="layout">
        <div>
            <section class="panel">
                <h2 class="panel-title">เลือกยาเข้าบิล</h2>
                <label class="label" for="med_select">ค้นหาชื่อยาหรือชื่อสามัญ</label>
                <select id="med_select" class="select2-med">
                    <option value=""></option>
                    <?php foreach ($medicines as $m): ?>
                        <option value="<?= (int)$m['medicine_id'] ?>"
                                data-name="<?= safe($m['name']) ?>"
                                data-generic="<?= safe($m['generic_name']) ?>"
                                data-unit="<?= safe($m['unit_type'] ?: 'ชิ้น') ?>"
                                data-price="<?= safe($m['unit_price']) ?>"
                                data-stock="<?= (int)$m['quantity_in_stock'] ?>">
                            <?= safe($m['name']) ?> <?= safe($m['generic_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="helper">เลือกยาแล้วระบบจะเพิ่มเข้าบิลทางขวาทันที · เลือกยาเดิมซ้ำเพื่อเพิ่มจำนวน</p>
            </section>

            <section class="panel">
                <h2 class="panel-title">ลูกค้า</h2>
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
                <p class="helper">ไม่ต้องเลือกถ้าเป็นลูกค้าทั่วไป</p>
            </section>
        </div>

        <form action="process_sale.php" method="POST" class="panel" id="bill_form">
            <h2 class="panel-title">บิลขาย <span class="item-sub" id="bill_count">(0 รายการ)</span></h2>
            <input type="hidden" name="customer_id" id="h_cust_id" value="9999">
            <table class="table">
                <thead>
                    <tr>
                        <th>รายการ</th>
                        <th style="width:140px;">จำนวน</th>
                        <th class="num">ราคา/หน่วย</th>
                        <th class="num">รวม</th>
                        <th style="width:56px;"></th>
                    </tr>
                </thead>
                <tbody id="cart_body"></tbody>
            </table>

            <div class="summary">
                <span class="summary__label">ยอดรวมที่ต้องชำระ</span>
                <span class="summary__value">฿<span id="total_price">0.00</span></span>
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
let cart = [];
const fmt = n => n.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

function optionTemplate(o) {
    if (!o.id) return o.text;
    const d = o.element.dataset;
    return $(`<div><div class="opt-name">${esc(d.name)}</div>
              <div class="opt-meta">${esc(d.generic || '-')} · ฿${fmt(parseFloat(d.price))}/${esc(d.unit)} · คงเหลือ ${esc(d.stock)} ${esc(d.unit)}</div></div>`);
}

$(function () {
    $('.select2-med').select2({ width: '100%', placeholder: 'พิมพ์ เช่น Para หรือ Cetirizine', templateResult: optionTemplate });
    $('.select2-cust').select2({ width: '100%' });
    // jQuery 3.6 + Select2 ไม่โฟกัสช่องค้นหาให้อัตโนมัติ — เปิดแล้วพิมพ์ชื่อยาได้ทันที
    $(document).on('select2:open', () => {
        const f = document.querySelector('.select2-container--open .select2-search__field');
        if (f) f.focus();
    });
    // เลือกยาแล้วเพิ่มเข้าบิลทันที — ไม่ต้องหาปุ่มเพิ่มแยก (F-01 / R-06)
    $('#med_select').on('select2:select', function (e) {
        addItem(e.params.data.element);
        $(this).val(null).trigger('change');
    });
    $('#bill_form').on('submit', function () {
        const b = document.getElementById('btn_checkout');
        b.disabled = true; b.textContent = 'กำลังบันทึกบิล…';
    });
    render();
});

function toggleCust(isMem) { $('#mem_wrap').toggle(isMem); if (!isMem) $('#h_cust_id').val('9999'); }
function updateCust() { $('#h_cust_id').val($('#cust_select').val()); }

function toast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(t._h); t._h = setTimeout(() => t.classList.remove('show'), 2200);
}

function addItem(o) {
    const d = o.dataset;
    const found = cart.find(i => i.id === o.value);
    if (found) {
        if (found.qty >= found.stock) { toast(`${d.name} คงเหลือ ${found.stock} ${d.unit} เพิ่มไม่ได้อีก`); return; }
        found.qty++;
    } else {
        cart.push({ id: o.value, name: d.name, generic: d.generic, unit: d.unit,
                    price: parseFloat(d.price), qty: 1, stock: parseInt(d.stock, 10) });
    }
    render();
    toast(`เพิ่ม ${d.name} เข้าบิลแล้ว`);
}

function setQty(idx, value) {
    const item = cart[idx];
    let q = parseInt(value, 10);
    if (isNaN(q) || q < 1) q = 1;
    if (q > item.stock) { q = item.stock; toast(`${item.name} คงเหลือ ${item.stock} ${item.unit}`); }
    item.qty = q;
    render();
}

function removeItem(idx) { cart.splice(idx, 1); render(); }

function render() {
    const body = document.getElementById('cart_body');
    let total = 0;
    if (cart.length === 0) {
        body.innerHTML = `<tr><td colspan="5" class="empty-state">
            <strong>ยังไม่มียาในบิล</strong>ค้นหาชื่อยาในช่องทางซ้าย แล้วเลือกเพื่อเพิ่มเข้าบิล</td></tr>`;
    } else {
        body.innerHTML = cart.map((i, idx) => {
            const sub = i.price * i.qty; total += sub;
            return `<tr>
                <td><div class="item-name">${esc(i.name)}</div><div class="item-sub">${esc(i.generic || '-')} · คงเหลือ ${i.stock} ${esc(i.unit)}</div>
                    <input type="hidden" name="items[${idx}][id]" value="${esc(i.id)}"></td>
                <td><div class="qty">
                    <button type="button" aria-label="ลดจำนวน" onclick="setQty(${idx}, ${i.qty - 1})">−</button>
                    <input type="number" min="1" max="${i.stock}" name="items[${idx}][qty]" value="${i.qty}" aria-label="จำนวน" onchange="setQty(${idx}, this.value)">
                    <button type="button" aria-label="เพิ่มจำนวน" onclick="setQty(${idx}, ${i.qty + 1})">+</button>
                </div></td>
                <td class="num">฿${fmt(i.price)}</td>
                <td class="num"><strong>฿${fmt(sub)}</strong></td>
                <td class="num"><button type="button" class="link-danger" onclick="removeItem(${idx})">ลบ</button></td>
            </tr>`;
        }).join('');
    }
    document.getElementById('total_price').textContent = fmt(total);
    document.getElementById('h_total').value = total.toFixed(2);
    document.getElementById('bill_count').textContent = `(${cart.length} รายการ)`;

    const btn = document.getElementById('btn_checkout');
    const hint = document.getElementById('checkout_hint');
    btn.disabled = cart.length === 0;
    btn.textContent = cart.length === 0 ? 'ไปขั้นตอนรับชำระเงิน' : `ไปขั้นตอนรับชำระเงิน (${cart.length} รายการ · ฿${fmt(total)})`;
    hint.textContent = cart.length === 0
        ? 'เพิ่มยาเข้าบิลอย่างน้อย 1 รายการก่อนไปขั้นตอนรับชำระเงิน'
        : 'ตรวจรายการและจำนวนให้ถูกต้อง แล้วกดปุ่มด้านบนเพื่อรับเงิน';

    document.getElementById('step1').className = 'step ' + (cart.length ? 'is-done' : 'is-current');
    document.getElementById('step2').className = 'step ' + (cart.length ? 'is-current' : '');
}
</script>
</body>
</html>
