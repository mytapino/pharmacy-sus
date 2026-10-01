<?php
require_once 'db.php';

$medicines = $conn->query("SELECT * FROM medicines WHERE status = 'active' AND quantity_in_stock > 0 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$customers = $conn->query("SELECT * FROM customers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบขายยา | PHARMACY SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root { --main-color: #068b82; --sub-color: #f0fdf4; --dark-blue: #2c3e50; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--sub-color); color: var(--dark-blue); }
        .navbar { background-color: var(--main-color) !important; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .section-title { color: var(--main-color); font-weight: 700; border-left: 5px solid var(--main-color); padding-left: 10px; margin-bottom: 20px; }
        /* ปรับสีปุ่มให้เป็นธีมหลัก */
        .btn-theme { background-color: var(--main-color); border: none; color: white; }
        .btn-theme:hover { background-color: #056d66; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">💊 PHARMACY SYSTEM</a>
        <div class="text-white small">เจ้าหน้าที่: <?= htmlspecialchars(DEFAULT_EMPLOYEE_NAME) ?></div>
    </div>
</nav>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card p-4 mb-4">
                <h5 class="section-title">1. ข้อมูลลูกค้า</h5>
                <div class="d-flex gap-3 mb-3 p-2 bg-light rounded-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="cust_type" id="t_guest" value="guest" checked onclick="toggleCust(false)">
                        <label class="form-check-label" for="t_guest">ลูกค้าทั่วไป</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="cust_type" id="t_member" value="member" onclick="toggleCust(true)">
                        <label class="form-check-label" for="t_member">สมาชิก</label>
                    </div>
                </div>
                <div id="mem_wrap" style="display: none;">
                    <select id="cust_select" class="form-select select2-search" onchange="updateCust()">
                        <option value="9999">-- ค้นหาชื่อ/เบอร์โทร --</option>
                        <?php foreach($customers as $c): ?>
                            <option value="<?= $c['customer_id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= $c['phone'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="card p-4 mb-4">
                <h5 class="section-title">2. เลือกรายการยา</h5>
                <select id="med_select" class="form-select mb-3 select2-search">
                    <option value="">-- ค้นหาชื่อยา --</option>
                    <?php foreach($medicines as $m): ?>
                        <option value="<?= $m['medicine_id'] ?>" data-name="<?= $m['name'] ?>" data-price="<?= $m['unit_price'] ?>" data-stock="<?= $m['quantity_in_stock'] ?>">
                            <?= $m['name'] ?> (คงเหลือ: <?= $m['quantity_in_stock'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-theme w-100 py-2 fw-bold" onclick="add()">+ เพิ่มเข้าบิล</button>
            </div>
        </div>

        <div class="col-lg-8">
            <form action="process_sale.php" method="POST" class="card p-4 h-100">
                <h4 class="fw-bold mb-4" style="color: var(--main-color);">🧾 รายการสั่งซื้อ</h4>
                <input type="hidden" name="customer_id" id="h_cust_id" value="9999">
                <div class="table-responsive">
                    <table class="table align-middle" id="cart">
                        <thead>
                            <tr>
                                <th>รายการ</th>
                                <th width="120">จำนวน</th>
                                <th class="text-end">ราคา</th>
                                <th class="text-end">รวม</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="mt-auto pt-4 border-top">
                    <div class="d-flex justify-content-between">
                        <h5 class="fw-bold">ยอดเงินรวม:</h5>
                        <h2 class="fw-bold text-success">฿ <span id="total_price">0.00</span></h2>
                    </div>
                    <input type="hidden" name="grand_total" id="h_total" value="0">
                    <button type="submit" class="btn btn-theme btn-lg w-100 mt-3 py-3 fw-bold shadow">ไปหน้าการชำระเงิน</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() { $('.select2-search').select2({ theme: 'bootstrap-5', width: '100%' }); });
let cart = [];
function toggleCust(isMem) { $('#mem_wrap').toggle(isMem); if(!isMem) $('#h_cust_id').val('9999'); }
function updateCust() { $('#h_cust_id').val($('#cust_select').val()); }
function add() {
    const s = document.getElementById('med_select');
    const o = s.options[s.selectedIndex];
    if(!o.value) return;
    const item = { id: o.value, name: o.dataset.name, price: parseFloat(o.dataset.price), qty: 1, stock: parseInt(o.dataset.stock) };
    const found = cart.find(i => i.id === item.id);
    if(found) { if(found.qty < item.stock) found.qty++; else alert('สต็อกไม่พอ'); } 
    else { cart.push(item); }
    render();
}
function render() {
    const b = $('#cart tbody');
    let t = 0; b.empty();
    cart.forEach((i, idx) => {
        let sub = i.price * i.qty; t += sub;
        b.append(`<tr><td>${i.name}</td><td><input type="number" name="items[${idx}][qty]" value="${i.qty}" class="form-control form-control-sm" onchange="cart[${idx}].qty=this.value;render()"> <input type="hidden" name="items[${idx}][id]" value="${i.id}"></td><td class="text-end">${i.price.toLocaleString()}</td><td class="text-end fw-bold">${sub.toLocaleString(undefined,{minimumFractionDigits:2})}</td><td><button type="button" class="btn btn-sm text-danger" onclick="cart.splice(${idx},1);render()">✕</button></td></tr>`);
    });
    $('#total_price').text(t.toLocaleString(undefined,{minimumFractionDigits:2}));
    $('#h_total').val(t); 
}
</script>
</body>
</html>