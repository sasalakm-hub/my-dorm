<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// ดึงบิลที่จ่ายแล้ว (paid) หรือ รอตรวจสอบ (pending)
$sql = "SELECT b.*, r.room_number 
        FROM bills b 
        JOIN rooms r ON b.room_id = r.id 
        WHERE b.user_id = '$user_id' AND (b.status = 'paid' OR b.status = 'pending')
        ORDER BY b.month DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการชำระเงิน - หอพักแสนสุข</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <h3 class="fw-bold mb-4"><i class="bi bi-clock-history me-2"></i>ประวัติการชำระค่าเช่า</h3>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>รอบเดือน</th>
                                <th>ยอดชำระ</th>
                                <th>วันที่จ่าย</th>
                                <th>สถานะ</th>
                                <th>รายละเอียด</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary"><?php echo date('F Y', strtotime($row['month'])); ?></span>
                                            <div class="small text-muted">ห้อง <?php echo $row['room_number']; ?></div>
                                        </td>
                                        <td class="fw-bold">฿<?php echo number_format($row['total_price'], 2); ?></td>
                                        <td>
                                            <?php echo ($row['pay_date']) ? date('d/m/Y H:i', strtotime($row['pay_date'])) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if($row['status']=='paid') echo '<span class="badge bg-success"><i class="bi bi-check-circle"></i> ชำระแล้ว</span>';
                                                elseif($row['status']=='pending') echo '<span class="badge bg-warning text-dark">รอตรวจสอบ</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <a href="bill_receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                <i class="bi bi-file-earmark-text"></i> ดูใบเสร็จ
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">ยังไม่มีประวัติการชำระเงิน</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="user_dashboard.php" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> กลับหน้าหลัก</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>