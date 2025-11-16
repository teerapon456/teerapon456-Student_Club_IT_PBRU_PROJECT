<?php
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

          <form action="process_reset_password.php" method="POST" class="auth-form">
            <div class="form-group mb-4">
              <label for="email" class="form-label">อีเมล</label>
              <div class="input-group">
                <span class="input-group-text">
                  <i class="fas fa-envelope"></i>
                </span>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
              </div>
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary btn-lg">ส่งลิงก์รีเซ็ตรหัสผ่าน</button>
            </div>

            <div class="text-center mt-4">
              <a href="login.php" class="text-decoration-none">กลับไปหน้าเข้าสู่ระบบ</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.auth-page {
  min-height: 100vh;
  background: var(--light-bg);
  padding: 4rem 0;
}

.auth-card {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.auth-header {
  margin-bottom: 2rem;
}

.auth-title {
  color: var(--primary-color);
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.auth-subtitle {
  color: var(--text-color);
  opacity: 0.8;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  color: var(--text-color);
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.input-group-text {
  background: var(--light-bg);
  border-color: #dee2e6;
  color: var(--primary-color);
}

.form-control {
  border-color: #dee2e6;
  padding: 0.75rem;
}

.form-control:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 0.25rem rgba(44, 62, 80, 0.25);
}

.btn-primary {
  background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
  border: none;
  padding: 0.75rem 1.5rem;
  font-weight: 500;
}

.btn-primary:hover {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.text-decoration-none {
  color: var(--primary-color);
  transition: all 0.3s ease;
}

.text-decoration-none:hover {
  color: var(--secondary-color);
  text-decoration: underline !important;
}

@media (max-width: 768px) {
  .auth-page {
    padding: 2rem 0;
  }

  .auth-card {
    padding: 1.5rem;
  }
}
</style>

<?php include_once '../../includes/footer.php'; ?>