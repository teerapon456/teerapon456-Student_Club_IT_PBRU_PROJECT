  <?php
  // ตั้งค่าการแสดง error
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  // ตั้งค่า session
  session_start();

  // ตั้งค่า timezone
  date_default_timezone_set('Asia/Bangkok');

  // ตัวอย่าง flash message (ถ้ามี)
  $flashMessage = $_SESSION['flash_message'] ?? null;
  if ($flashMessage) {
    unset($_SESSION['flash_message']);
  }

  $pageTitle = "หน้าหลัก - สโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ";
  ?>
  <!DOCTYPE html>
  <html lang="th">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/it_smo/assets/img/itnobg.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Css -->
    <link rel="stylesheet" href="assets/css/components.css">
    <style>
    .overlay {
      pointer-events: none !important;
    }
    </style>
  </head>

  <body>
    <!-- ======================== Navbar Section ======================== -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
      <div class="container">
        <a class="navbar-brand" href="/it_smo/">
          <i class="fas fa-laptop-code me-2"></i> IT SMO
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link active" href="/it_smo/">หน้าหลัก</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/public/about.php">เกี่ยวกับเรา</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/public/news.php">ข่าวสาร</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/public/activities.php">กิจกรรม</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/public/gallery.php">แกลเลอรี่</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/public/documents.php">รายการเอกสาร</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/public/contact.php">ติดต่อเรา</a></li>
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link btn btn-outline-light" href="pages/public/login.php" id="loginBtn"><i
                  class="fas fa-sign-in-alt me-1"></i> เข้าสู่ระบบ</a></li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- ======================== End Navbar Section ======================== -->

    <?php if ($flashMessage): ?>
    <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
      <?= $flashMessage['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Preloader (เพิ่มเฉพาะถ้ายังไม่มี) -->
    <?php if (!isset($preloaderAdded)): ?>
    <div id="preloader">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">กำลังโหลด...</span>
      </div>
    </div>
    <?php $preloaderAdded = true; endif; ?>

    <!-- SweetAlert แจ้งล็อกอินสำเร็จ (โหลด script เฉพาะกรณี) -->
    <?php if (isset($_GET['login_success'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'เข้าสู่ระบบสำเร็จ!',
        text: 'ยินดีต้อนรับเข้าสู่ระบบ IT SMO',
        confirmButtonText: 'ตกลง'
      });
    });
    </script>
    <?php endif; ?>

    <!-- JS ปิด preloader (เพิ่มเฉพาะถ้ายังไม่มี) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      var preloader = document.getElementById('preloader');
      if (preloader) preloader.style.display = 'none';
    });
    </script>

<?php
$userName = '';
if (isset($_SESSION['user_data']['first_name']) || isset($_SESSION['user_data']['last_name'])) {
  $userName = trim(($_SESSION['user_data']['first_name'] ?? '') . ' ' . ($_SESSION['user_data']['last_name'] ?? ''));
}
?>

    <!-- ======================== Main Content ======================== -->
    <main>
      <!-- ======================== Hero Section ======================== -->
      <section class="hero-section" data-aos="fade-up">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
              <h1 class="display-4 fw-bold mb-4">ยินดีต้อนรับสู่<br>สโมสรนักศึกษา IT</h1>
              <p class="lead mb-4">ร่วมเป็นส่วนหนึ่งของชุมชนนักศึกษาเทคโนโลยีสารสนเทศ
                ที่เต็มไปด้วยกิจกรรมและโอกาสในการพัฒนาตนเอง</p>
              <div class="d-flex gap-3">
                <a href="pages/public/about.php" class="btn btn-light" id="aboutBtn"><i
                    class="fas fa-info-circle me-2"></i>เกี่ยวกับเรา</a>
                <a href="pages/public/activities.php" class="btn btn-outline-light" id="activitiesBtn"><i
                    class="fas fa-calendar-alt me-2"></i>กิจกรรม</a>
              </div>
            </div>
            <div class="col-lg-6">
                <img
                  src="/it_smo/assets/img/photo2566/enprener2566.jpg"
                alt="IT Student Club" class="img-fluid rounded-4 shadow-lg">
            </div>
          </div>
        </div>
      </section>
      <!-- ======================== End Hero Section ======================== -->

      <!-- ======================== News Section ======================== -->
      <section class="py-5" data-aos="fade-up">
        <div class="container">
          <h2 class="section-title">ข่าวสารล่าสุด</h2>
          <div class="row g-4">
            <!-- News Card 1 -->
            <div class="col-md-4">
              <div class="card h-100">
                <img src="/it_smo/assets/img/photo2567/act1-2567.jpg"
                  class="card-img-top" alt="News 1">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-primary">กิจกรรม</span>
                    <small class="text-muted">1 วัน ที่แล้ว</small>
                  </div>
                  <h5 class="card-title">กิจกรรมรับน้องปี 2567</h5>
                  <p class="card-text">ปฐมนิเทศนักศึกษาปีใหม่ประจำปีการศึกษา 2567 
                  </p>
                  <a href="pages/public/news.php" class="btn btn-primary" id="newsBtn"><i
                      class="fas fa-arrow-right me-2"></i>อ่านเพิ่มเติม</a>
                </div>
              </div>
            </div>
            <!-- News Card 2 -->
            <div class="col-md-4">
              <div class="card h-100">
                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1170&q=80"
                  class="card-img-top" alt="News 2">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-success">ประกาศ</span>
                    <small class="text-muted">3 วัน ที่แล้ว</small>
                  </div>
                  <h5 class="card-title">การแข่งขัน IT Contest</h5>
                  <p class="card-text">ร่วมประกวดผลงานนักศึกษาในงาน IT Contest 2024 ชิงรางวัลและโอกาสในการพัฒนาตนเอง</p>
                  <a href="pages/public/news.php" class="btn btn-primary" id="newsBtn2"><i
                      class="fas fa-arrow-right me-2"></i>อ่านเพิ่มเติม</a>
                </div>
              </div>
            </div>
            <!-- News Card 3 -->
            <div class="col-md-4">
              <div class="card h-100">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1171&q=80"
                  class="card-img-top" alt="News 3">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-info">ค่าย</span>
                    <small class="text-muted">5 วัน ที่แล้ว</small>
                  </div>
                  <h5 class="card-title">กิจกรรม IT Camp</h5>
                  <p class="card-text">ค่ายพัฒนาทักษะด้านไอทีสำหรับนักศึกษาปี 1 พบกับกิจกรรมสร้างสรรค์และเพื่อนใหม่</p>
                  <a href="pages/public/news.php" class="btn btn-primary" id="newsBtn3"><i
                      class="fas fa-arrow-right me-2"></i>อ่านเพิ่มเติม</a>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center mt-5">
            <a href="pages/public/news.php" class="btn btn-outline-primary btn-lg" id="allNewsBtn"><i
                class="fas fa-newspaper me-2"></i>ดูข่าวสารทั้งหมด</a>
          </div>
        </div>
      </section>
      <!-- ======================== End News Section ======================== -->

      <!-- ======================== Activities Section ======================== -->
      <section class="py-5 bg-light" data-aos="fade-up">
        <div class="container">
          <h2 class="section-title">กิจกรรมที่กำลังจะมาถึง</h2>
          <div class="row g-4">
            <!-- Activity 1 -->
            <div class="col-md-6">
              <div class="card h-100">
                <div class="row g-0">
                  <div class="col-md-4">
                    <img
                      src="https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1112&q=80"
                      class="img-fluid rounded-start h-100" alt="Activity 1">
                  </div>
                  <div class="col-md-8">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-warning text-dark">เร็วๆ นี้</span>
                        <small class="text-muted">15 มี.ค. 2567</small>
                      </div>
                      <h5 class="card-title">IT Open House 2024</h5>
                      <p class="card-text">งานเปิดบ้านคณะ IT พบกับนิทรรศการและผลงานของนักศึกษา</p>
                      <a href="pages/public/activities.php" class="btn btn-sm btn-primary" id="activityDetailBtn1"><i
                          class="fas fa-calendar-check me-2"></i>รายละเอียด</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Activity 2 -->
            <div class="col-md-6">
              <div class="card h-100">
                <div class="row g-0">
                  <div class="col-md-4">
                    <img
                      src="https://images.unsplash.com/photo-1517649763962-0c623861013b?auto=format&fit=crop&w=1170&q=80"
                      class="img-fluid rounded-start h-100" alt="Activity 2">
                  </div>
                  <div class="col-md-8">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-warning text-dark">เร็วๆ นี้</span>
                        <small class="text-muted">20 มี.ค. 2567</small>
                      </div>
                      <h5 class="card-title">IT Sport Day</h5>
                      <p class="card-text">การแข่งขันกีฬาสีภายในคณะเทคโนโลยีสารสนเทศ</p>
                      <a href="pages/public/activities.php" class="btn btn-sm btn-primary" id="activityDetailBtn2"><i
                          class="fas fa-calendar-check me-2"></i>รายละเอียด</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center mt-5">
            <a href="pages/public/activities.php" class="btn btn-outline-primary btn-lg" id="allActivitiesBtn"><i
                class="fas fa-calendar-alt me-2"></i>ดูกิจกรรมทั้งหมด</a>
          </div>
        </div>
      </section>
      <!-- ======================== End Activities Section ======================== -->

      <!-- ======================== Gallery Preview Section ======================== -->
      <section class="py-5" data-aos="fade-up">
        <div class="container">
          <h2 class="section-title">ภาพกิจกรรม</h2>
          <div class="row g-4">
            <?php
            $galleryImages = [
              'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1170&q=80',
              'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1170&q=80',
              'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1171&q=80',
              'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1112&q=80',
              'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1112&q=80',
              'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1170&q=80'
            ];
            foreach ($galleryImages as $index => $image):
            ?>
            <div class="col-md-4 col-6">
              <a href="pages/public/gallery.php" class="gallery-item" id="galleryItemBtn<?= $index + 1 ?>">
                <img src="<?= $image ?>" alt="Gallery Image <?= $index + 1 ?>" class="img-fluid rounded-4 shadow-sm">
              </a>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="text-center mt-5">
            <a href="pages/public/gallery.php" class="btn btn-outline-primary btn-lg" id="allGalleryBtn"><i
                class="fas fa-images me-2"></i>ดูภาพทั้งหมด</a>
          </div>
        </div>
      </section>
      <!-- ======================== End Gallery Preview Section ======================== -->
    </main>
    <!-- ======================== End Main Content ======================== -->

  <!-- Footer -->
  <footer class="footer">
      <div class="footer-content">
          <div class="container">
              <div class="footer-grid">
                  <!-- About Section -->
                  <div class="footer-section">
                      <h3 class="footer-title">เกี่ยวกับเรา</h3>
                      <p class="footer-description">
                          สโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ <br>มหาวิทยาลัยราชภัฏเพชรบุรี
                      </p>
                      <div class="social-links">
                          <a href="#" class="social-link" target="_blank">
                              <i class="fab fa-facebook"></i>
                          </a>
                          <a href="#" class="social-link" target="_blank">
                              <i class="fab fa-instagram"></i>
                          </a>
                          <a href="#" class="social-link" target="_blank">
                              <i class="fab fa-line"></i>
                          </a>
                          <a href="#" class="social-link" target="_blank">
                              <i class="fab fa-youtube"></i>
                          </a>
                      </div>
                  </div>

                  <!-- Quick Links -->
                  <div class="footer-section">
                      <h3 class="footer-title">เมนูด่วน</h3>
                      <ul class="footer-links">
                          <li><a href="/it_smo/pages/public/home.php">หน้าแรก</a></li>
                          <li><a href="/it_smo/pages/public/about.php">เกี่ยวกับเรา</a></li>
                          <li><a href="/it_smo/pages/public/activities.php">กิจกรรม</a></li>
                          <li><a href="/it_smo/pages/public/news.php">ข่าวสาร</a></li>
                          <li><a href="/it_smo/pages/public/gallery.php">แกลเลอรี่</a></li>
                          <li><a href="/it_smo/pages/public/documents.php">เอกสาร</a></li>
                      </ul>
                  </div>

                  <!-- Contact Info -->
                  <div class="footer-section">
                      <h3 class="footer-title">ติดต่อเรา</h3>
                      <ul class="contact-info">
                          <li>
                              <i class="fas fa-map-marker-alt"></i>
                              <span>38 หมู่ 8 ถนนหาดเจ้าสำราญ ตำบลนาวุ้ง <br>อำเภอเมืองเพชรบุรี จังหวัดเพชรบุรี 76000</span>
                          </li>
                          <li>
                              <i class="fas fa-phone"></i>
                              <span>032-708617</span>
                          </li>
                          <li>
                              <i class="fas fa-envelope"></i>
                              <span>it.smo@mail.pbru.ac.th</span>
                          </li>
                      </ul>
                  </div>
              </div>
          </div>
      </div>

      <!-- Copyright -->
      <div class="footer-bottom">
          <div class="container">
              <div class="footer-bottom-content">
                  <p class="copyright">
                      &copy; <?php echo date('Y'); ?> สโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ. สงวนลิขสิทธิ์.
                  </p>
                  <div class="footer-bottom-links">
                      <a href="/it_smo/pages/public/privacy-policy.php">นโยบายความเป็นส่วนตัว</a>
                      <a href="/it_smo/pages/public/terms-of-service.php">เงื่อนไขการใช้งาน</a>
                  </div>
              </div>
          </div>
      </div>
  </footer>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- ======================== Scripts Section ======================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    AOS.init({
      duration: 1000,
      once: true
    });
    </script>
    <!-- ======================== End Scripts Section ======================== -->
  </body>

  </html>