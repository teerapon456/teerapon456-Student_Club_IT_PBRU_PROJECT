<?php
require_once __DIR__ . '/../../api/config/Database.php';
require_once __DIR__ . '/../../api/helpers/PasswordHelper.php';

$db = (new Database())->getConnection();
$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$validToken = false;
$userId = null;

// Validate token (token must exist, not expired, and not used)
if ($token) {
  try {
    $stmt = $db->prepare("
            SELECT user_id 
            FROM users 
            WHERE reset_token = ? 
            AND reset_token_expires > NOW() 
            AND status = 'เปิดใช้งาน'
        ");
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
      $validToken = true;
      $userId = $result['user_id'];
    }
  } catch (PDOException $e) {
    error_log("Token validation error: " . $e->getMessage());
  }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';

  // Validate password
  $passwordValidation = PasswordHelper::validatePassword($password);
  if (!$passwordValidation['valid']) {
    $error = implode('<br>', $passwordValidation['errors']);
  } elseif ($password !== $confirmPassword) {
    $error = 'รหัสผ่านไม่ตรงกัน';
  } else {
    try {
      // Update password and invalidate token immediately
      $hashedPassword = PasswordHelper::hashPassword($password);
      $stmt = $db->prepare("
                UPDATE users 
                SET password = ?, 
                    reset_token = NULL, 
                    reset_token_expires = NULL 
                WHERE user_id = ? AND reset_token = ?
            ");

      if ($stmt->execute([$hashedPassword, $userId, $token]) && $stmt->rowCount() > 0) {
        $success = 'รีเซ็ตรหัสผ่านสำเร็จ คุณสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้แล้ว';
        $validToken = false; // Prevent reuse
      } else {
        $error = 'ไม่สามารถรีเซ็ตรหัสผ่านได้ กรุณาลองใหม่อีกครั้ง';
      }
    } catch (PDOException $e) {
      $error = 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
      error_log("Password reset error: " . $e->getMessage());
    }
  }
}

$pageTitle = 'ตั้งรหัสผ่านใหม่ | IT SMO';
include_once '../../includes/header.php';
?>

<div class="auth-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="auth-card" data-aos="fade-up">
          <div class="auth-header text-center mb-4">
            <h2 class="auth-title">ตั้งรหัสผ่านใหม่</h2>
            <p class="auth-subtitle">กรุณาตั้งรหัสผ่านใหม่ของคุณ</p>
          </div>

          <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>

          <?php if ($success): ?>
          <div class="alert alert-success"><?php echo $success; ?></div>
          <div class="text-center mt-4">
            <a href="login.php" class="btn btn-primary">เข้าสู่ระบบ</a>
          </div>
          <?php elseif ($validToken): ?>
          <form method="POST" action="" class="auth-form">
            <div class="form-group mb-4">
              <label for="password" class="form-label">รหัสผ่านใหม่</label>
              <div class="input-group">
                <span class="input-group-text">
                  <i class="fas fa-lock"></i>
                </span>
                <input type="password" class="form-control" id="password" name="password" required
                  pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                  title="รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวอักษรตัวพิมพ์ใหญ่ ตัวพิมพ์เล็ก ตัวเลข และอักขระพิเศษ">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <small class="form-text text-muted">
                รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร ประกอบด้วย:<br>
                - ตัวอักษรตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว<br>
                - ตัวอักษรตัวพิมพ์เล็กอย่างน้อย 1 ตัว<br>
                - ตัวเลขอย่างน้อย 1 ตัว<br>
                - อักขระพิเศษอย่างน้อย 1 ตัว
              </small>
            </div>

            <div class="form-group mb-4">
              <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
              <div class="input-group">
                <span class="input-group-text">
                  <i class="fas fa-lock"></i>
                </span>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary btn-lg">บันทึกรหัสผ่านใหม่</button>
            </div>
          </form>
          <?php else: ?>
          <div class="alert alert-danger">
            ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้องหรือหมดอายุแล้ว กรุณาขอรีเซ็ตรหัสผ่านใหม่
          </div>
          <div class="text-center mt-4">
            <a href="reset-password.php" class="btn btn-primary">ขอรีเซ็ตรหัสผ่านใหม่</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

<script>
// Toggle Password Visibility
document.getElementById('togglePassword').addEventListener('click', function() {
  const password = document.getElementById('password');
  const icon = this.querySelector('i');

  if (password.type === 'password') {
    password.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    password.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
});

document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
  const password = document.getElementById('confirm_password');
  const icon = this.querySelector('i');

  if (password.type === 'password') {
    password.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    password.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
});
</script>