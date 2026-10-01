<?php
require_once 'db.php';

$ym = $_GET['ym'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
    $ym = date('Y-m');
}

try {
    $start = new DateTime($ym . '-01 00:00:00');
    $end = (clone $start)->modify('+1 month');
} catch (Exception $e) {
    $start = new DateTime(date('Y-m') . '-01 00:00:00');
    $end = (clone $start)->modify('+1 month');
    $ym = $start->format('Y-m');
}

$sql = "
    SELECT DATE(order_date) AS d,
           SUM(total_amount) AS total,
           COUNT(*) AS bills
    FROM orders
    WHERE order_date >= :start AND order_date < :end
    GROUP BY d
    ORDER BY d ASC
";
$stmt = $conn->prepare($sql);
$stmt->execute([
    'start' => $start->format('Y-m-d H:i:s'),
    'end' => $end->format('Y-m-d H:i:s'),
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];
$grand = 0.0;
$bills = 0;
foreach ($rows as $r) {
    $labels[] = date('d/m', strtotime($r['d']));
    $v = (float)($r['total'] ?? 0);
    $data[] = $v;
    $grand += $v;
    $bills += (int)($r['bills'] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายได้รายวัน (<?php echo htmlspecialchars($ym); ?>) | PHARMACY SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7f6; }
        .card { border-radius: 15px; border: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">💊 PHARMACY SYSTEM</a>
        <div class="navbar-nav flex-row align-items-center">
            <a class="nav-link me-3 text-secondary" href="index.php">หน้าหลัก</a>
            <a class="nav-link active me-3 text-white" href="#">รายได้รายวัน</a>
            <a class="nav-link me-3 text-secondary" href="sales_history.php">ประวัติการขาย</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold m-0">📅 รายได้รายวัน (<?php echo htmlspecialchars($start->format('m/Y')); ?>)</h3>
            <div class="text-muted small">รวม ฿<?php echo number_format($grand, 2); ?> • <?php echo number_format($bills); ?> บิล</div>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">กลับแดชบอร์ด</a>
    </div>

    <div class="card shadow-sm p-4 mb-4">
        <div style="height: 320px;">
            <canvas id="dailyRevenueChart"></canvas>
        </div>
    </div>

    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0">รายละเอียดรายวัน</h5>
            <form class="d-flex gap-2" method="GET">
                <input type="month" name="ym" class="form-control form-control-sm" value="<?php echo htmlspecialchars($ym); ?>">
                <button class="btn btn-sm btn-primary">ดู</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>วันที่</th>
                        <th class="text-end">รายได้</th>
                        <th class="text-end">จำนวนบิล</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($rows) === 0): ?>
                    <tr><td colspan="3" class="text-center text-muted p-4">ไม่มีข้อมูลในเดือนนี้</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($r['d'])); ?></td>
                            <td class="text-end fw-bold text-success">฿<?php echo number_format((float)$r['total'], 2); ?></td>
                            <td class="text-end"><?php echo number_format((int)$r['bills']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('dailyRevenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'รายได้ (บาท)',
            data: <?php echo json_encode($data); ?>,
            backgroundColor: 'rgba(13, 110, 253, 0.75)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 2,
            borderRadius: 10,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const v = Number(ctx.raw || 0);
                        return ' ฿' + v.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { display: false },
                ticks: {
                    callback: function(value) {
                        const v = Number(value || 0);
                        return v.toLocaleString('th-TH');
                    }
                }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>

</body>
</html>

