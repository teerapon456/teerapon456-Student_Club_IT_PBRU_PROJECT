<?php
$pageTitle = 'ติดต่อเรา | IT SMO';
include_once '../../includes/header.php';

// ตัวแปรสำหรับเก็บค่าฟอร์มและข้อความแจ้งเตือน
$name = $email = $subject = $message = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  // Validate
  if ($name === '') {
    $errors['name'] = 'กรุณากรอกชื่อ-นามสกุล';
  }
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'กรุณากรอกอีเมลที่ถูกต้อง';
  }
  if ($subject === '') {
    $errors['subject'] = 'กรุณากรอกหัวข้อ';
  }
  if ($message === '') {
    $errors['message'] = 'กรุณากรอกข้อความ';
  }

  if (!$errors) {
    // ตัวอย่าง: สามารถส่งอีเมลหรือบันทึกลงฐานข้อมูลที่นี่
    $success = 'ส่งข้อความเรียบร้อยแล้ว ขอบคุณที่ติดต่อเรา!';
    $name = $email = $subject = $message = '';
  }
}
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8">
      <!-- เนื้อหา contact us -->
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">ติดต่อเรา</h5>
          <p class="card-text">รายละเอียดการติดต่อ...</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Hero Section -->
  <div class="hero-section text-center mb-5" data-aos="fade-up">
    <h1 class="display-4 mb-3">ติดต่อเรา</h1>
    <p class="lead">มีคำถามหรือข้อเสนอแนะ ติดต่อเราได้ที่นี่</p>
  </div>

  <div class="row">
    <!-- Contact Form -->
    <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
      <div class="card h-100">
        <div class="card-body p-4">
          <h3 class="card-title mb-4">ส่งข้อความถึงเรา</h3>

          <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
              <?= htmlspecialchars($success) ?>
            </div>
          <?php endif; ?>
          <?php if ($errors): ?>
            <div class="alert alert-danger" role="alert">
              <ul class="mb-0" style="padding-left: 1.2em;">
                <?php foreach ($errors as $err): ?>
                  <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" action="">
            <div class="mb-3">
              <label for="name" class="form-label">ชื่อ-นามสกุล</label>
              <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" id="name" name="name" placeholder="กรอกชื่อ-นามสกุลของคุณ" value="<?= htmlspecialchars($name) ?>">
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">อีเมล</label>
              <input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="email" name="email" placeholder="กรอกอีเมลของคุณ" value="<?= htmlspecialchars($email) ?>">
            </div>
            <div class="mb-3">
              <label for="subject" class="form-label">หัวข้อ</label>
              <input type="text" class="form-control<?= isset($errors['subject']) ? ' is-invalid' : '' ?>" id="subject" name="subject" placeholder="กรอกหัวข้อข้อความ" value="<?= htmlspecialchars($subject) ?>">
            </div>
            <div class="mb-3">
              <label for="message" class="form-label">ข้อความ</label>
              <textarea class="form-control<?= isset($errors['message']) ? ' is-invalid' : '' ?>" id="message" name="message" rows="5" placeholder="กรอกข้อความของคุณ"><?= htmlspecialchars($message) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane me-2"></i>ส่งข้อความ
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Contact Information -->
    <div class="col-lg-6" data-aos="fade-left">
      <div class="card h-100">
        <div class="card-body p-4">
          <h3 class="card-title mb-4">ข้อมูลติดต่อ</h3>
          <div class="mb-4">
            <h5 class="mb-3">
              <i class="fas fa-map-marker-alt text-primary me-2"></i>
              ที่อยู่
            </h5>
            <p class="mb-0">
              สโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ<br>
              มหาวิทยาลัยราชภัฏเพชรบุรี<br>
              38 หมู่ 8 ถนนหาดเจ้าสำราญ ตำบลนาวุ้ง<br>
              อำเภอเมืองเพชรบุรี จังหวัดเพชรบุรี 76000
            </p>
          </div>
          <div class="mb-4">
            <h5 class="mb-3">
              <i class="fas fa-phone text-primary me-2"></i>
              โทรศัพท์
            </h5>
            <p class="mb-0">
              <a href="tel:+66032708617" class="text-decoration-none">032-708617</a>
            </p>
          </div>
          <div class="mb-4">
            <h5 class="mb-3">
              <i class="fas fa-envelope text-primary me-2"></i>
              อีเมล
            </h5>
            <p class="mb-0">
              <a href="mailto:it.smo@mail.pbru.ac.th" class="text-decoration-none">it.smo@mail.pbru.ac.th</a>
            </p>
          </div>
          <div>
            <h5 class="mb-3">
              <i class="fas fa-clock text-primary me-2"></i>
              เวลาทำการ
            </h5>
            <p class="mb-0">
              จันทร์ - ศุกร์: 08:30 - 16:30 น.<br>
              เสาร์ - อาทิตย์: ปิดทำการ
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Map Section -->
  <div class="row mt-5">
    <div class="col-12" data-aos="fade-up">
      <div class="card">
        <div class="card-body p-0">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3874.6500000000005!2d100.6167!3d13.9872!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTPCsDU5JzEzLjkiTiAxMDDCsDM3JzAwLjEiRQ!5e0!3m2!1sth!2sth!4v1620000000000!5m2!1sth!2sth"
            width="100%"
            height="450"
            style="border:0;"
            allowfullscreen=""
            loading="lazy">
          </iframe>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .form-control {
    border-radius: 8px;
    padding: 0.75rem 1rem;
    border: 1px solid #dee2e6;
  }

  .form-control:focus {
    border-color: #4fc3f7;
    box-shadow: 0 0 0 0.25rem rgba(79, 195, 247, 0.25);
  }

  .btn-primary {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
  }

  .card {
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  }

  .card-title {
    color: #1a237e;
    font-weight: 600;
  }

  a {
    color: #1a237e;
    transition: all 0.3s ease;
  }

  a:hover {
    color: #4fc3f7;
  }
</style>

<?php include_once '../../includes/footer.php'; ?>