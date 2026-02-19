<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// รับ id บิลที่จะจ่าย
if (!isset($_GET['id'])) { header("Location: user_dashboard.php"); exit(); }
$bill_id = $_GET['id'];

// ดึงข้อมูลบิล (ต้องเช็คด้วยว่าเป็นบิลของ user คนนี้จริงๆ ห้ามจ่ายให้คนอื่น)
$sql = "SELECT * FROM bills WHERE id = '$bill_id' AND user_id = '$user_id'";
$result = $conn->query($sql);
$bill = $result->fetch_assoc();

if (!$bill) { echo "ไม่พบข้อมูลบิล"; exit(); }
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ชำระค่าเช่า - หอพักแสนสุข</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-primary text-white p-4 rounded-top-4 text-center">
                        <h4 class="mb-0 fw-bold">📄 ใบแจ้งหนี้ (Invoice)</h4>
                        <p class="mb-0 opacity-75">ประจำเดือน <?php echo date('F Y', strtotime($bill['month'])); ?></p>
                    </div>
                    <div class="card-body p-4">
                        
                        <table class="table table-bordered mb-4">
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
                                        ค่าไฟฟ้า (7 บาท/หน่วย) <br>
                                        <small class="text-muted">มิเตอร์: <?php echo $bill['elec_unit_old']; ?> ➔ <?php echo $bill['elec_unit_new']; ?> (<?php echo $bill['elec_unit_new'] - $bill['elec_unit_old']; ?> หน่วย)</small>
                                    </td>
                                    <td class="text-end"><?php echo number_format($bill['elec_price'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>
                                        ค่าน้ำประปา (17 บาท/หน่วย) <br>
                                        <small class="text-muted">มิเตอร์: <?php echo $bill['water_unit_old']; ?> ➔ <?php echo $bill['water_unit_new']; ?> (<?php echo $bill['water_unit_new'] - $bill['water_unit_old']; ?> หน่วย)</small>
                                    </td>
                                    <td class="text-end"><?php echo number_format($bill['water_price'], 2); ?></td>
                                </tr>
                                <tr class="table-active fw-bold fs-5">
                                    <td>รวมสุทธิ</td>
                                    <td class="text-end text-primary"><?php echo number_format($bill['total_price'], 2); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <hr class="my-4">

                        <div class="text-center mb-4">
                            <h5 class="fw-bold mb-3">ช่องทางการชำระเงิน</h5>
                            <div class="p-3 border rounded-3 bg-white d-inline-block shadow-sm">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/59/KASIKORNBANK_LOGO.png" width="50" class="mb-2">
                                <h5 class="fw-bold text-success mb-1">123-4-56789-0</h5>
                                <small>ธ.กสิกรไทย | หอพักแสนสุข จำกัด</small>
                            </div>
                        </div>

                        <form action="bill_payment_save.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">แนบหลักฐานการโอนเงิน (สลิป)</label>
                                <input type="file" name="bill_slip" class="form-control" accept="image/*" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow">
                                ยืนยันการชำระเงิน
                            </button>
                            <a href="user_dashboard.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted">ยกเลิก / กลับหน้าหลัก</a>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>