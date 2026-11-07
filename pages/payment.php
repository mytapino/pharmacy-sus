<?php
/*
 * รับชำระเงิน — Redesign HTS Final (R-06 ขั้นที่ 3)
 * ใช้แถบขั้นตอนเดียวกับหน้าขายยา + token จาก design.md
 * ปุ่มบอกผลลัพธ์ชัดเจน "ยืนยันรับเงินและจบการขาย" และบอกเหตุผลเมื่อยังกดไม่ได้
 */
require_once 'db.php';
$order_id = (int)($_GET['order_id'] ?? 0);
$stmt = $conn->prepare("SELECT total_amount FROM orders WHERE order_id = ?");
$stmt->execute([$order_id]);
$bill_total = (float)$stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHARMACY SYSTEM — รับชำระเงิน บิล #<?= $order_id ?></title>
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
        .page { width: min(560px, calc(100% - 48px)); margin: 0 auto; padding: 32px 0 48px; }
        .page-title { margin: 0; font-size: 24px; line-height: 32px; font-weight: 700; }
        .page-subtitle { margin: 4px 0 0; color: var(--text-secondary); }

        .steps { display: flex; gap: 8px; margin: 24px 0; padding: 0; list-style: none; flex-wrap: wrap; }
        .step { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px;
                border: 1px solid var(--border-default); background: var(--surface); color: var(--success-600); font-weight: 500; }
        .step__num { width: 24px; height: 24px; border-radius: 6px; display: grid; place-items: center;
                     background: var(--background-page); font-size: 12px; font-weight: 600; }
        .step.is-current { border-color: var(--primary-600); color: var(--primary-700); background: var(--primary-50); }
        .step.is-current .step__num { background: var(--primary-600); color: var(--surface); }
        .step__arrow { align-self: center; color: var(--text-secondary); }

        .panel { background: var(--surface); border: 1px solid var(--border-default); border-radius: 12px; padding: 24px; }
        .bill { display: flex; justify-content: space-between; align-items: baseline; padding-bottom: 16px;
                margin-bottom: 24px; border-bottom: 1px solid var(--border-default); }
        .bill__value { font-size: 28px; line-height: 36px; font-weight: 700; }
        .field { margin-bottom: 16px; }
        .label { display: block; font-weight: 500; margin-bottom: 8px; }
        .input, .select { width: 100%; height: 40px; border: 1px solid var(--border-default); border-radius: 8px;
                          padding: 0 16px; font-family: inherit; font-size: 14px; background: var(--surface); color: var(--text-primary); }
        .input:focus, .select:focus { outline: none; border-color: var(--primary-600); box-shadow: 0 0 0 3px var(--primary-50); }
        .input[readonly] { background: var(--background-page); }
        .change { display: flex; justify-content: space-between; align-items: baseline; padding: 16px;
                  background: var(--background-page); border-radius: 8px; margin-bottom: 8px; }
        .change__value { font-size: 20px; line-height: 28px; font-weight: 600; }
        .helper { margin: 8px 0 0; font-size: 12px; line-height: 18px; color: var(--text-secondary); }
        .helper.is-error { color: var(--danger-600); }
        .btn-primary { width: 100%; height: 48px; margin-top: 16px; border: none; border-radius: 8px;
                       background: var(--primary-600); color: var(--surface); font-family: inherit; font-size: 16px; font-weight: 600; cursor: pointer; }
        .btn-primary:hover { background: var(--primary-700); }
        .btn-primary:disabled { background: var(--background-page); color: var(--text-secondary); box-shadow: inset 0 0 0 1px var(--border-default); cursor: not-allowed; }
        .back { display: inline-block; margin-top: 16px; color: var(--primary-700); font-weight: 500; text-decoration: none; }
    </style>
</head>
<body>
<main class="page">
    <h1 class="page-title">รับชำระเงิน</h1>
    <p class="page-subtitle">บิล #<?= $order_id ?></p>

    <ol class="steps" aria-label="ขั้นตอนการขาย">
        <li class="step"><span class="step__num">✓</span>เลือกยาเข้าบิล</li>
        <li class="step__arrow" aria-hidden="true">›</li>
        <li class="step"><span class="step__num">✓</span>ตรวจบิล</li>
        <li class="step__arrow" aria-hidden="true">›</li>
        <li class="step is-current"><span class="step__num">3</span>รับชำระเงิน</li>
    </ol>

    <section class="panel">
        <div class="bill">
            <span>ยอดที่ต้องชำระ</span>
            <span class="bill__value">฿<?= number_format($bill_total, 2) ?></span>
        </div>
        <input type="hidden" id="total_val" value="<?= $bill_total ?>">

        <form action="confirm_payment.php" method="POST" id="pay_form">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <div class="field">
                <label class="label" for="method">วิธีชำระเงิน</label>
                <select name="method" id="method" class="select" onchange="checkMethod()">
                    <option value="cash">เงินสด</option>
                    <option value="transfer">เงินโอน</option>
                </select>
            </div>

            <div class="field">
                <label class="label" for="received">รับเงินมา (บาท)</label>
                <input type="number" name="received" id="received" class="input" step="0.01" min="0" inputmode="decimal" oninput="cal()" placeholder="เช่น 100">
            </div>

            <div class="change">
                <span>เงินทอน</span>
                <span class="change__value">฿<span id="change_text">0.00</span></span>
            </div>
            <p class="helper" id="pay_hint">กรอกจำนวนเงินที่รับจากลูกค้า ระบบจะคำนวณเงินทอนให้</p>

            <button type="submit" id="btn_submit" class="btn-primary" disabled>ยืนยันรับเงินและจบการขาย</button>
        </form>
    </section>
</main>

<script>
function checkMethod() {
    const method = document.getElementById('method').value;
    const total = document.getElementById('total_val').value;
    const received = document.getElementById('received');
    if (method === 'transfer') { received.value = total; received.readOnly = true; }
    else { received.value = ''; received.readOnly = false; }
    cal();
}

function cal() {
    const total = parseFloat(document.getElementById('total_val').value) || 0;
    const received = parseFloat(document.getElementById('received').value) || 0;
    const change = received - total;
    const btn = document.getElementById('btn_submit');
    const hint = document.getElementById('pay_hint');
    const fmt = n => n.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (received > 0 && change >= 0) {
        document.getElementById('change_text').innerText = fmt(change);
        btn.disabled = false;
        hint.className = 'helper';
        hint.textContent = 'ตรวจเงินทอนแล้วกดยืนยันเพื่อจบการขายและออกใบเสร็จ';
    } else {
        document.getElementById('change_text').innerText = '0.00';
        btn.disabled = true;
        if (received > 0) {
            hint.className = 'helper is-error';
            hint.textContent = `เงินที่รับยังไม่พอ ขาดอีก ฿${fmt(-change)}`;
        } else {
            hint.className = 'helper';
            hint.textContent = 'กรอกจำนวนเงินที่รับจากลูกค้า ระบบจะคำนวณเงินทอนให้';
        }
    }
}
document.getElementById('pay_form').addEventListener('submit', () => {
    const b = document.getElementById('btn_submit'); b.disabled = true; b.textContent = 'กำลังบันทึกการขาย…';
});
window.onload = checkMethod;
</script>
</body>
</html>
