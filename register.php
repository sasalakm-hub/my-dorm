<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - หอพักแสนสุข</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="body-login">

    <div class="login-card">
        <div class="text-center">
            <div class="brand-logo shadow-sm" style="background: #b36c32aa;"> <i class="bi bi-person-plus-fill"></i>
            </div>
            <h3 class="fw-bold mb-1">สมัครสมาชิกใหม่</h3>
            <p class="text-muted mb-4">กรอกข้อมูลเพื่อลงทะเบียนผู้เช่า</p>
        </div>

        <form action="register_save.php" method="POST">
            
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="ชื่อจริง" required>
                        <label for="firstname">ชื่อจริง</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="นามสกุล" required>
                        <label for="lastname">นามสกุล</label>
                    </div>
                </div>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="email" name="email" placeholder="กรอกอีเมลของคุณ" required>
                <label for="email"><i class="bi bi-person me-2"></i>email</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="ชื่อผู้ใช้ (ภาษาอังกฤษ)" required>
                <label for="username"><i class="bi bi-person me-2"></i>ชื่อผู้ใช้ (Username)</label>
            </div>

            <div class="form-floating mb-3">
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="เบอร์โทรศัพท์" required>
                <label for="phone"><i class="bi bi-telephone me-2"></i>เบอร์โทรศัพท์</label>
            </div>

            <div class="form-floating mb-3 password-wrapper">
                <input type="password" class="form-control" id="password" name="password" placeholder="รหัสผ่าน" required>
                <label for="password"><i class="bi bi-lock me-2"></i>รหัสผ่าน</label>
                <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
            </div>

            <div class="form-floating mb-4 password-wrapper">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
                <label for="confirm_password"><i class="bi bi-lock-fill me-2"></i>ยืนยันรหัสผ่าน</label>
                <i class="bi bi-eye-slash password-toggle" id="toggleConfirmPassword"></i>
                
                <div id="password_match_msg" class="small mt-1 text-start ps-1"></div>
            </div>

            <button type="submit" id="submitBtn" class="btn btn-success w-100 btn-login mb-3">ลงทะเบียน</button>
            
            <div class="text-center text-muted">
                มีบัญชีอยู่แล้ว? 
                <a href="login.php" class="text-decoration-none fw-bold text-primary">เข้าสู่ระบบ</a>
            </div>

            <div class="text-center mt-3">
                <a href="index.php" class="back-home"><i class="bi bi-arrow-left"></i> กลับหน้าหลัก</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function setupPasswordToggle(inputId, toggleId) {
            const toggleBtn = document.querySelector('#' + toggleId);
            const passwordInput = document.querySelector('#' + inputId);

            toggleBtn.addEventListener('click', function (e) {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
                passwordInput.classList.toggle('password-reveal');
            });
        }

        setupPasswordToggle('password', 'togglePassword');
        setupPasswordToggle('confirm_password', 'toggleConfirmPassword');
    </script>

    <script>
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const messageBox = document.getElementById('password_match_msg');
        const submitBtn = document.getElementById('submitBtn');

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;

            // ถ้าช่องยืนยันยังว่างอยู่ ให้เคลียร์สถานะ
            if (confirm === '') {
                confirmInput.classList.remove('is-valid', 'is-invalid');
                messageBox.innerHTML = '';
                submitBtn.disabled = false; // ปล่อยให้กดได้ (หรือจะ true ก็ได้แล้วแต่ design)
                return;
            }

            // ถ้าตรงกัน
            if (password === confirm) {
                confirmInput.classList.remove('is-invalid');
                confirmInput.classList.add('is-valid'); // กรอบเขียว
                messageBox.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> รหัสผ่านตรงกัน</span>';
                submitBtn.disabled = false; // ให้กดปุ่มได้
            } 
            // ถ้าไม่ตรงกัน
            else {
                confirmInput.classList.remove('is-valid');
                confirmInput.classList.add('is-invalid'); // กรอบแดง
                messageBox.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> รหัสผ่านไม่ตรงกัน</span>';
                submitBtn.disabled = true; // ล็อกปุ่ม ห้ามกดส่ง
            }
        }

        // สั่งให้ทำงานเมื่อมีการพิมพ์ในช่องรหัสผ่านทั้ง 2 ช่อง
        passwordInput.addEventListener('keyup', checkPasswordMatch);
        confirmInput.addEventListener('keyup', checkPasswordMatch);
    </script>
    
</body>
</html>