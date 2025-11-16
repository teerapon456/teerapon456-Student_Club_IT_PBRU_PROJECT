<?php
require_once __DIR__ . '/../../api/config/Database.php';
require_once __DIR__ . '/../../api/helpers/PasswordHelper.php';

$db = (new Database())->getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

  // Always show the same message for security
  $success = 'หากอีเมลนี้มีอยู่ในระบบ ระบบจะส่งลิงก์รีเซ็ตรหัสผ่านไปให้';

  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    try {
      // Check if email exists
      $stmt = $db->prepare("SELECT user_id, first_name, last_name FROM users WHERE email = ? AND status = 'เปิดใช้งาน'");
      $stmt->execute([$email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user) {
        // Generate secure reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token to database (invalidate previous tokens)
        $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?");
        $stmt->execute([$token, $expires, $user['user_id']]);

        // Send reset email (prevent header injection)
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/it_smo/pages/public/reset_password_confirm.php?token=" . urlencode($token);
        $to = $email;
        $subject = "=?UTF-8?B?" . base64_encode("รีเซ็ตรหัสผ่าน - IT SMO") . "?=";
        $message = "เรียน {$user['first_name']} {$user['last_name']},\n\n";
        $message .= "คุณได้ขอรีเซ็ตรหัสผ่าน กรุณาคลิกที่ลิงก์ด้านล่างเพื่อตั้งรหัสผ่านใหม่:\n\n";
        $message .= $resetLink . "\n\n";
        $message .= "ลิงก์นี้จะหมดอายุใน 1 ชั่วโมง\n\n";
        $message .= "หากคุณไม่ได้ขอรีเซ็ตรหัสผ่าน กรุณาละเลยอีเมลนี้\n\n";
        $message .= "ขอแสดงความนับถือ,\nทีมงาน IT SMO";

        $headers = "From: noreply@it_smo.com\r\n";
        $headers .= "Reply-To: noreply@it_smo.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // ป้องกัน header injection
        if (preg_match('/[\r\n]/', $to) || preg_match('/[\r\n]/', $subject)) {
          // Do not send if header injection detected
          error_log('Header injection detected in password reset');
        } else {
          @mail($to, $subject, $message, $headers);
        }
      }
      // Always show the same message regardless of user existence
    } catch (PDOException $e) {
      // Log error but do not show to user
      error_log("Password reset error: " . $e->getMessage());
    }
  }
}

$pageTitle = 'รีเซ็ตรหัสผ่าน | IT SMO';
include_once '../../includes/header.php';
?>

<div class="auth-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="auth-card" data-aos="fade-up">
          <div class="auth-header text-center mb-4">
            <h2 class="auth-title">รีเซ็ตรหัสผ่าน</h2>
            <p class="auth-subtitle">กรุณากรอกอีเมลที่ใช้สมัครสมาชิก</p>
          </div>

          <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <div class="text-center mt-4">
              <a href="login.php" class="btn btn-primary">กลับไปหน้าเข้าสู่ระบบ</a>
            </div>
          <?php else: ?>
            <form method="POST" action="" class="auth-form">
              <div class="form-group mb-4">
                <label for="email" class="form-label">อีเมล</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="fas fa-envelope"></i>
                  </span>
                  <input type="email" class="form-control" id="email" name="email" required>
                </div>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">ส่งลิงก์รีเซ็ตรหัสผ่าน</button>
              </div>

              <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none">กลับไปหน้าเข้าสู่ระบบ</a>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once '../../includes/footer.php'; ?>