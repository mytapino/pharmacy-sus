<?php
require_once 'db.php';

// ประมวลผลเมื่อมีการเพิ่มสมาชิก
if (isset($_POST['add_customer'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    try {
        $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $address]);
        $success = "เพิ่มสมาชิกใหม่เรียบร้อยแล้ว!";
    } catch (PDOException $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

$customers = $conn->query("SELECT * FROM customers WHERE customer_id != 9999 ORDER BY customer_id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสมาชิก | PHARMACY SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root { --main-color: #068b82; --sub-color: #f0fdf4; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--sub-color); }
        .nav-tabs .nav-link.active { background-color: var(--main-color); color: white; border: none; }
        .nav-tabs .nav-link { color: var(--main-color); }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .btn-theme { background-color: var(--main-color); color: white; border: none; }
        .btn-theme:hover { background-color: #056d66; color: white; }
    </style>
</head>
<body>

<?php $topbar_active = ''; include __DIR__ . '/_topbar.php'; ?>
<div style="height:24px" aria-hidden="true"></div>

<div class="container">
    <h3 class="fw-bold mb-4" style="color: var(--main-color);">👥 ระบบจัดการสมาชิก</h3>

    <?php if(isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card p-4">
        <ul class="nav nav-tabs mb-4" id="customerTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-content" type="button">รายชื่อสมาชิก</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold" id="add-tab" data-bs-toggle="tab" data-bs-target="#add-content" type="button">+ เพิ่มสมาชิกใหม่</button>
            </li>
        </ul>

        <div class="tab-content" id="customerTabContent">
            
            <div class="tab-pane fade show active" id="list-content">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>เบอร์โทรศัพท์</th>
                                <th>อีเมล</th>
                                <th>ที่อยู่</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($customers as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['customer_id']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($c['name']) ?></td>
                                <td><?= htmlspecialchars($c['phone']) ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= htmlspecialchars($c['address']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="add-content">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form action="" method="POST" class="bg-light p-4 rounded-4 shadow-sm">
                            <h5 class="mb-4 fw-bold">กรอกข้อมูลสมาชิกใหม่</h5>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="เช่น นายสมชาย ใจดี">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control" required placeholder="08x-xxxxxxx">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">อีเมล</label>
                                    <input type="email" name="email" class="form-control" placeholder="example@mail.com">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">ที่อยู่</label>
                                    <textarea name="address" class="form-control" rows="3" placeholder="บ้านเลขที่, ถนน, ตำบล..."></textarea>
                                </div>
                                <div class="col-12 mt-4 text-end">
                                    <button type="reset" class="btn btn-light px-4">ล้างข้อมูล</button>
                                    <button type="submit" name="add_customer" class="btn btn-theme px-5 fw-bold shadow-sm">บันทึกข้อมูลสมาชิก</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>