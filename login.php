<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - หอพักแสนสุข</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="body-login">

    <div class="login-card">
        <div class="text-center">
            <div class="brand-logo shadow-sm">
                <i class="bi bi-buildings"></i>
            </div>
            <h3 class="fw-bold mb-1">ยินดีต้อนรับ</h3>
            <p class="text-muted mb-4">เข้าสู่ระบบจัดการหอพัก</p>
        </div>

        <form action="login_check.php" method="POST">
            
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="ชื่อผู้ใช้" required>
                <label for="username"><i class="bi bi-person me-2"></i>ชื่อผู้ใช้</label>
            </div>

            <div class="form-floating mb-3 password-wrapper">
                <input type="password" class="form-control" id="password" name="password" placeholder="รหัสผ่าน" required>
                <label for="password"><i class="bi bi-lock me-2"></i>รหัสผ่าน</label>
                <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label small text-muted" for="rememberMe">
                        จดจำฉันไว้
                    </label>
                </div>
                
                <a href="#" class="text-decoration-none small text-muted" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                    ลืมรหัสผ่าน?
                </a>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-login mb-3">เข้าสู่ระบบ</button>
            
            <div class="text-center text-muted">
                ยังไม่มีบัญชีผู้ใช้? 
                <a href="register.php" class="text-decoration-none fw-bold">สมัครสมาชิก</a>
            </div>

            <div class="text-center mt-3">
                <a href="index.php" class="back-home"><i class="bi bi-arrow-left"></i> กลับหน้าหลัก</a>
            </div>
        </form>
    </div>

    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header bg-warning bg-opacity-10 border-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-key-fill me-2"></i>ตั้งค่ารหัสผ่านใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="reset_password_save.php" method="POST">
                        <p class="text-muted small mb-3">กรุณากรอกข้อมูลให้ตรงกับที่ลงทะเบียนไว้ เพื่อยืนยันตัวตน</p>
                        
                        <div class="form-floating mb-2">
                            <input type="text" class="form-control" name="check_username" placeholder="Username" required>
                            <label>ชื่อผู้ใช้ (Username)</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input type="email" class="form-control" name="check_email" placeholder="Email" required>
                            <label>อีเมล (Email)</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" name="check_phone" placeholder="Phone" required>
                            <label>เบอร์โทรศัพท์</label>
                        </div>

                        <hr>

                        <div class="form-floating mb-2">
                            <input type="password" class="form-control" name="new_password" placeholder="New Password" required>
                            <label>รหัสผ่านใหม่</label>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 rounded-pill text-dark fw-bold">ยืนยันการเปลี่ยนรหัส</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // เช็คว่าตอนนี้เป็น type อะไรอยู่
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            
            // เปลี่ยน type
            password.setAttribute('type', type);
            
            // เปลี่ยนรูปไอคอน
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
            
            // เพิ่ม class เพื่อปรับ styling
            password.classList.toggle('password-reveal');
        });
    </script>
</body>
</html>