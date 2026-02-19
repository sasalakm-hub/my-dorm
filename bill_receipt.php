<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: payment_history.php"); exit(); }

$bill_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// ดึงข้อมูลบิล
$sql = "SELECT b.*, r.room_number FROM bills b JOIN rooms r ON b.room_id = r.id WHERE b.id = '$bill_id' AND b.user_id = '$user_id'";
$result = $conn->query($sql);
$bill = $result->fetch_assoc();

if (!$bill) { echo "ไม่พบข้อมูล"; exit(); }
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จรับเงิน - <?php echo date('M Y', strtotime($bill['month'])); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                
                <div class="card border-0 shadow rounded-4 position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 m-3">
                        <?php if($bill['status']=='paid'): ?>
                            <div class="border border-success text-success px-3 py-1 rounded fw-bold text-uppercase" style="transform: rotate(0deg);">PAID</div>
                        <?php else: ?>
                            <div class="border border-warning text-warning px-3 py-1 rounded fw-bold text-uppercase">PENDING</div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-primary"><i class="bi bi-building-fill me-2"></i>หอพักแสนสุข</h4>
                            <p class="text-muted mb-0">ใบเสร็จรับเงิน / Receipt</p>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">ผู้เช่า / ห้อง</small>
                                <strong>ห้อง <?php echo $bill['room_number']; ?></strong>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block">ประจำเดือน</small>
                                <strong><?php echo date('F Y', strtotime($bill['month'])); ?></strong>
                            </div>
                        </div>

                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>รายการ</th>
                                    <th class="text-end">จำนวนเงิน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>ค่าเช่าห้องพัก</td>
                                    <td class="text-end"><?php echo number_format($bill['price_room'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>
                                        ค่าไฟ (<?php echo $bill['elec_unit_new'] - $bill['elec_unit_old']; ?> หน่วย)
                                        <div class="small text-muted" style="font-size: 0.75rem;">มิเตอร์: <?php echo $bill['elec_unit_old']; ?> - <?php echo $bill['elec_unit_new']; ?></div>
                                    </td>
                                    <td class="text-end"><?php echo number_format($bill['elec_price'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>
                                        ค่าน้ำ (<?php echo $bill['water_unit_new'] - $bill['water_unit_old']; ?> หน่วย)
                                        <div class="small text-muted" style="font-size: 0.75rem;">มิเตอร์: <?php echo $bill['water_unit_old']; ?> - <?php echo $bill['water_unit_new']; ?></div>
                                    </td>
                                    <td class="text-end"><?php echo number_format($bill['water_price'], 2); ?></td>
                                </tr>
                                <tr class="table-active fw-bold">
                                    <td>รวมทั้งสิ้น</td>
                                    <td class="text-end"><?php echo number_format($bill['total_price'], 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <?php if($bill['slip_image']): ?>
                        <div class="text-center mt-4">
                            <p class="small text-muted mb-1">หลักฐานการโอน</p>
                            <img src="uploads/bill_slips/<?php echo $bill['slip_image']; ?>" class="rounded border" style="max-height: 150px;">
                        </div>
                        <?php endif; ?>

                        <div class="text-center mt-5 d-print-none">
                            <button onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-pill px-3 me-2"><i class="bi bi-printer"></i> พิมพ์</button>
                            <a href="payment_history.php" class="btn btn-primary btn-sm rounded-pill px-3">กลับ</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>