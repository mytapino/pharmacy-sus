
<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบขายยา | Order Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }
        .card { border-radius: 15px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">💊 PHARMACY SYSTEM</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">หน้าหลัก</a>
                <a class="nav-link active" href="order_form.php">ระบบขายยา</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="card shadow-sm border-0 p-4">
            <h4 class="mb-4 text-success fw-bold">🧾 สร้างรายการขายใหม่</h4>
            
            <form action="save_order.php" method="POST">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">ข้อมูลลูกค้า</label>
                        <div class="mb-3 p-3 bg-light" style="border-radius: 10px;">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" id="old_cust" value="old" checked onclick="toggleCustomer('old')">
                                <label class="form-check-label" for="old_cust">ลูกค้าสมาชิกเดิม</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="customer_type" id="new_cust" value="new" onclick="toggleCustomer('new')">
                                <label class="form-check-label" for="new_cust">ลูกค้าใหม่/ทั่วไป</label>
                            </div>
                        </div>

                        <div id="section_old_cust">
                            <select name="customer_id" class="form-select border-primary">
                                <option value="" selected disabled>-- ค้นหา/เลือกชื่อลูกค้าสมาชิก --</option>
                                <?php 
                                $res = $conn->query("SELECT * FROM customers ORDER BY name ASC"); 
                                while($r = $res->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$r['customer_id']}'>👤 {$r['name']} (โทร: {$r['phone']})</option>"; 
                                }
                                ?>
                            </select>
                        </div>

                        <div id="section_new_cust" style="display: none;">
                            <div class="row g-2">
                                <div class="col-md-7">
                                    <input type="text" name="new_customer_name" class="form-control border-success" placeholder="ระบุชื่อ-นามสกุล ลูกค้าใหม่">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="new_customer_phone" class="form-control border-success" placeholder="เบอร์โทรศัพท์">
                                </div>
                            </div>
                            <div class="form-text text-success">* ระบบจะบันทึกรายชื่อนี้เข้าฐานข้อมูลสมาชิกให้อัตโนมัติ</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">พนักงานผู้ขาย</label>
                        <select name="employee_id" class="form-select" required>
                            <?php 
                            $res = $conn->query("SELECT * FROM employees"); 
                            while($r = $res->fetch(PDO::FETCH_ASSOC)) echo "<option value='{$r['employee_id']}'>👩‍⚕️ {$r['name']}</option>"; 
                            ?>
                        </select>
                    </div>
                </div>

                <hr>

                <h5 class="fw-bold mb-3 text-secondary">📦 รายการยาที่ขาย</h5>
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>รายการยา</th>
                            <th style="width: 150px;" class="text-center">จำนวน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="medicine_id[]" class="form-select" required>
                                    <option value="">เลือกยา...</option>
                                    <?php 
                                    $res = $conn->query("SELECT * FROM medicines WHERE quantity_in_stock > 0"); 
                                    while($r = $res->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$r['medicine_id']}'>{$r['name']} (คงเหลือ: {$r['quantity_in_stock']} {$r['unit_type']}) - ฿" . number_format($r['unit_price'], 2) . "</option>"; 
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="number" name="qty[]" class="form-control text-center" value="1" min="1" required>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="text-end mt-4">
                    <a href="index.php" class="btn btn-light px-4 me-2">ยกเลิก</a>
                    <button type="submit" class="btn btn-success btn-lg px-5 shadow">ยืนยันการขายและบันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleCustomer(type) {
        const oldSection = document.getElementById('section_old_cust');
        const newSection = document.getElementById('section_new_cust');
        
        if (type === 'new') {
            oldSection.style.display = 'none';
            newSection.style.display = 'block';
            // ล้างค่าและตั้งค่า required
            document.getElementsByName('customer_id')[0].required = false;
            document.getElementsByName('new_customer_name')[0].required = true;
        } else {
            oldSection.style.display = 'block';
            newSection.style.display = 'none';
            document.getElementsByName('customer_id')[0].required = true;
            document.getElementsByName('new_customer_name')[0].required = false;
        }
    }
    </script>
</body>
</html>