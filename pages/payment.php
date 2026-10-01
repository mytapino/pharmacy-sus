<?php
require_once 'db.php';
$order_id = $_GET['order_id'];
$stmt = $conn->prepare("SELECT total_amount FROM orders WHERE order_id = ?");
$stmt->execute([$order_id]);
$bill_total = (float)$stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ชำระเงิน บิล #<?= $order_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f0fdf4; font-family: 'Sarabun', sans-serif; }
        .payment-card { max-width: 450px; margin: 50px auto; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border:none; }
        .bill-display { background: #068b82; color: white; padding: 20px; border-radius: 15px; }
        .btn-theme { background-color: #068b82; border: none; color: white; }
        .btn-theme:disabled { background-color: #ccc; }
    </style>
</head>
<body>
<div class="card payment-card p-4 text-center">
    <h3 class="fw-bold mb-4 text-success">💰 ชำระเงิน</h3>
    <div class="bill-display mb-4">
        <small>ยอดที่ต้องชำระตามบิล</small>
        <h1 class="fw-bold m-0">฿ <?= number_format($bill_total, 2) ?></h1>
        <input type="hidden" id="total_val" value="<?= $bill_total ?>">
    </div>

    <form action="confirm_payment.php" method="POST">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <div class="mb-3 text-start">
            <label class="fw-bold">วิธีชำระเงิน</label>
            <select name="method" id="method" class="form-select form-select-lg" onchange="checkMethod()">
                <option value="cash">💵 เงินสด</option>
                <option value="transfer">📱 เงินโอน</option>
            </select>
        </div>

        <div id="cash_area">
            <div class="mb-3 text-start">
                <label class="fw-bold text-primary">รับเงินมา</label>
                <input type="number" name="received" id="received" class="form-control form-control-lg text-center" step="0.01" onkeyup="cal()">
            </div>
            <div class="d-flex justify-content-between p-3 bg-light rounded mb-4">
                <span>เงินทอน:</span>
                <h3 class="m-0 text-danger">฿ <span id="change_text">0.00</span></h3>
            </div>
        </div>

        <button type="submit" id="btn_submit" class="btn btn-theme btn-lg w-100 py-3 fw-bold rounded-pill" disabled>ยืนยันชำระเงิน</button>
    </form>
</div>

<script>
function checkMethod() {
    const method = document.getElementById('method').value;
    const total = document.getElementById('total_val').value;
    const received = document.getElementById('received');
    if(method === 'transfer') {
        received.value = total;
        document.getElementById('cash_area').style.opacity = '0.5';
        received.readOnly = true;
    } else {
        received.value = '';
        document.getElementById('cash_area').style.opacity = '1';
        received.readOnly = false;
    }
    cal();
}

function cal() {
    const total = parseFloat(document.getElementById('total_val').value) || 0;
    const received = parseFloat(document.getElementById('received').value) || 0;
    const change = received - total;
    const btn = document.getElementById('btn_submit');
    if (change >= 0 && received > 0) {
        document.getElementById('change_text').innerText = change.toLocaleString(undefined, {minimumFractionDigits:2});
        btn.disabled = false;
    } else {
        document.getElementById('change_text').innerText = "0.00";
        btn.disabled = true;
    }
}
window.onload = checkMethod;
</script>
</body>
</html>