<?php
session_start();
$pageTitle = "เข้าสู่ระบบ | IT SMO";
require_once __DIR__ . '/../../api/controllers/LoginController.php';
include_once '../../includes/header.php';

// ไม่ต้องใช้ $error/$success แล้ว เพราะใช้ SweetAlert AJAX

if (isset($_SESSION['user_id'])) {
  $role = $_SESSION['user_role'] ?? '';
  switch ($role) {
    case 'ผู้ดูแลระบบ':
    case 'อาจารย์ที่ปรึกษา':
      header('Location: /it_smo/pages/admin/dashboard/admin.php'); break;
    case 'นายกสโมสรนักศึกษา':
      header('Location: /it_smo/pages/admin/dashboard/president.php'); break;
    case 'รองนายกสโมสรนักศึกษา':
      header('Location: /it_smo/pages/admin/dashboard/vice_president.php'); break;
    case 'เลขานุการสโมสรนักศึกษา':
      header('Location: /it_smo/pages/admin/dashboard/secretary.php'); break;
    case 'กรรมการสโมสรนักศึกษา':
    case 'อนุกรรมการสโมสรนักศึกษา':
      header('Location: /it_smo/pages/admin/dashboard/committee.php'); break;
    case 'นักศึกษา':
      header('Location: /it_smo/pages/student/dashboard.php'); break;
    default:
      header('Location: /it_smo/pages/public/login.php?error=role_undefined'); exit();
  }
  exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ | IT SMO</title>
  <link rel="icon" type="image/png" href="/it_smo/assets/img/itnobg.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background: linear-gradient(135deg, #e3f0ff 0%, #f8fbff 100%); font-family: 'Prompt', sans-serif; }
    .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .login-card {
      background: #fff; border-radius: 1.5rem; box-shadow: 0 8px 32px rgba(44,62,80,0.10);
      padding: 2.5rem 2rem; max-width: 400px; width: 100%;
    }
    .login-logo {
      width: 80px; height: 80px; object-fit: contain; margin-bottom: 1rem;
      display: block; margin-left: auto; margin-right: auto;
    }
    .login-title { font-weight: 700; color: #1565c0; text-align: center; margin-bottom: 0.5rem; }
    .login-desc { color: #607d8b; text-align: center; margin-bottom: 2rem; }
    .form-label { font-weight: 500; color: #263238; }
    .input-group-text { background: #e3f0ff; color: #1565c0; border: none; }
    .form-control { border-radius: 0.75rem; }
    .btn-primary {
      background: linear-gradient(90deg, #1976d2 0%, #42a5f5 100%);
      border: none; font-weight: 600; border-radius: 0.75rem;
      box-shadow: 0 2px 8px rgba(33,150,243,0.10);
    }
    .btn-primary:hover { background: linear-gradient(90deg, #42a5f5 0%, #1976d2 100%); }
    .form-check-label { color: #607d8b; }
    .forgot-link { color: #1976d2; text-decoration: none; font-size: 0.95rem; }
    .forgot-link:hover { text-decoration: underline; color: #0d47a1; }
    .alert { border-radius: 0.75rem; }
    @media (max-width: 576px) {
      .login-card { padding: 1.5rem 0.5rem; }
    }
  </style>
</head>
<body>
<div class="login-container">
  <div class="login-card">
    <img src="/it_smo/assets/img/itnobg.png" alt="IT SMO Logo" class="login-logo">
    <div class="login-title">เข้าสู่ระบบ IT SMO</div>
    <div class="login-desc" style="font-size:1.15rem;font-weight:600;">ระบบจัดการเอกสารสโมสรนักศึกษา<br>คณะเทคโนโลยีสารสนเทศ</div>
    <form id="loginForm" action="/it_smo/api/endpoints/login.php" method="POST" autocomplete="off">
      <div class="mb-3">
        <label for="student_id" class="form-label">รหัสนักศึกษา</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-user"></i></span>
          <input type="text" class="form-control" id="student_id" name="student_id" required autofocus>
        </div>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">รหัสผ่าน</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password" required>
          <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fas fa-eye"></i></button>
        </div>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">จดจำการเข้าสู่ระบบ</label>
      </div>
      <div class="d-grid mb-2">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ</button>
      </div>
      <div class="text-end">
        <a href="reset-password.php" class="forgot-link">ลืมรหัสผ่าน?</a>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle Password Visibility
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
togglePassword.addEventListener('click', function() {
  const icon = this.querySelector('i');
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    passwordInput.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
});

// AJAX Login + SweetAlert
const loginForm = document.getElementById('loginForm');
loginForm.addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(loginForm);
  fetch(loginForm.action, {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(result => {
    if (result.success) {
      Swal.fire({
        icon: 'success',
        title: 'เข้าสู่ระบบสำเร็จ!',
        text: 'กำลังนำคุณไปยังหน้าหลัก...',
        timer: 1500,
        showConfirmButton: false
      }).then(() => {
        window.location.href = result.redirect || '/it_smo/pages/student/dashboard.php';
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'เข้าสู่ระบบล้มเหลว',
        text: result.message || 'รหัสผ่านหรือชื่อบัญชีผิด'
      });
    }
  })
  .catch(() => {
    Swal.fire({
      icon: 'error',
      title: 'เกิดข้อผิดพลาด',
      text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
    });
  });
});
</script>
</body>
</html>
<?php include_once '../../includes/footer.php'; ?>