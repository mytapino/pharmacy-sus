<?php
require_once 'db.php';

// รับชื่อยาที่ส่งมาจากหน้า Dashboard (ถ้ามี)
$search_med = $_GET['search_med'] ?? '';

// ดึงข้อมูล Supplier ทั้งหมด
$stmt = $conn->query("SELECT * FROM suppliers ORDER BY name ASC");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายชื่อ Supplier | PHARMACY SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0fdf4; font-family: 'Sarabun', sans-serif; }
        .supplier-card { border: none; border-radius: 15px; transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .supplier-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .badge-med { background-color: #e9ecef; color: #495057; font-weight: normal; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="bi bi-truck me-2 text-primary"></i>รายชื่อ Supplier</h2>
            <p class="text-muted">เลือกติดต่อผู้ผลิตเพื่อสั่งซื้อยาเติมเข้าสต็อก</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> กลับหน้าหลัก</a>
    </div>

    <?php if ($search_med): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <i class="bi bi-info-circle-fill me-2"></i> 
        กำลังหา Supplier สำหรับสั่งซื้อ: <strong><?= htmlspecialchars($search_med) ?></strong>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($suppliers as $s): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card supplier-card h-100 p-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary mb-3">
                        <?= htmlspecialchars($s['name']) ?>
                    </h5>
                    
                    <p class="mb-2 small">
                        <i class="bi bi-person text-muted me-2"></i> 
                        <strong>ผู้ติดต่อ:</strong> <?= htmlspecialchars($s['contact_person']) ?>
                    </p>
                    
                    <p class="mb-2 small">
                        <i class="bi bi-geo-alt text-muted me-2"></i> 
                        <strong>ที่อยู่:</strong> <?= htmlspecialchars($s['address']) ?>
                    </p>

                    <div class="mt-4 pt-3 border-top">
                        <div class="d-grid gap-2">
                            <!-- ปุ่มโทรออก -->
                            <a href="tel:<?= $s['phone'] ?>" class="btn btn-success">
                                <i class="bi bi-telephone-fill me-2"></i> โทร: <?= htmlspecialchars($s['phone']) ?>
                            </a>
                            <!-- ปุ่มส่งอีเมล -->
                            <a href="mailto:<?= $s['email'] ?>" class="btn btn-light btn-sm text-muted">
                                <i class="bi bi-envelope-fill me-2"></i> <?= htmlspecialchars($s['email']) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>