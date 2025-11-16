<?php
// ตั้งค่าการแสดง error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ตั้งค่า timezone
date_default_timezone_set('Asia/Bangkok');

// ตัวอย่าง flash message (ถ้ามี)
$flashMessage = $_SESSION['flash_message'] ?? null;
if ($flashMessage) {
  unset($_SESSION['flash_message']);
}

// ตรวจสอบหน้าที่กำลังแสดง
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <!-- Favicon -->
  <link rel="apple-touch-icon" sizes="180x180" href="/it_smo/assets/img/itnobg.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/it_smo/assets/img/itnobg.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/it_smo/assets/img/itnobg.png">
  <link rel="manifest" href="/it_smo/assets/img/itnobg.png">
  <link rel="mask-icon" href="/it_smo/assets/img/itnobg.png" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- AOS Animation -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- UX Enhancements -->
  <script src="/it_smo/assets/js/ux-enhancements.js" defer></script>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="/it_smo/assets/css/components.css">
  <link rel="stylesheet" href="/it_smo/assets/css/forms.css">
  <link rel="stylesheet" href="/it_smo/assets/css/responsive.css">
  <style>
  /* Favicon Circle Style */
  .navbar-brand img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
  }
  </style>
</head>

<body>
  <!-- Enhanced Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="/it_smo/">
        <img src="/it_smo/assets/img/itnobg.png" alt="IT SMO Logo" class="me-2" style="width: 32px; height: 32px;">
        <span class="brand-text">IT SMO</span>
      </a>
      
      <!-- Mobile Menu Button -->
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <!-- Main Navigation -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="/it_smo/">
              <i class="fas fa-home me-1"></i>หน้าหลัก
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>" href="/it_smo/pages/public/about.php">
              <i class="fas fa-info-circle me-1"></i>เกี่ยวกับเรา
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'news.php' ? 'active' : '' ?>" href="/it_smo/pages/public/news.php">
              <i class="fas fa-newspaper me-1"></i>ข่าวสาร
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'activities.php' ? 'active' : '' ?>" href="/it_smo/pages/public/activities.php">
              <i class="fas fa-calendar-alt me-1"></i>กิจกรรม
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'gallery.php' ? 'active' : '' ?>" href="/it_smo/pages/public/gallery.php">
              <i class="fas fa-images me-1"></i>แกลเลอรี่
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'documents.php' ? 'active' : '' ?>" href="/it_smo/pages/public/documents.php">
              <i class="fas fa-file-alt me-1"></i>เอกสาร
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'contact.php' ? 'active' : '' ?>" href="/it_smo/pages/public/contact.php">
              <i class="fas fa-envelope me-1"></i>ติดต่อเรา
            </a>
          </li>
        </ul>
        
        <!-- User Actions -->
        <ul class="navbar-nav">
          <?php if (isset($_SESSION['user_id'])): ?>
            <!-- User is logged in -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="/it_smo/uploads/profile_images/user_<?= $_SESSION['user_id'] ?>.jpg" 
                     alt="Profile" class="rounded-circle me-2" 
                     style="width: 32px; height: 32px; object-fit: cover;"
                     onerror="this.src='/it_smo/assets/img/default-avatar.png'">
                <span class="d-none d-md-inline"><?= $_SESSION['user_data']['first_name'] ?? 'ผู้ใช้' ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="/it_smo/pages/student/profile.php">
                  <i class="fas fa-user me-2"></i>โปรไฟล์
                </a></li>
                <li><a class="dropdown-item" href="/it_smo/pages/student/dashboard.php">
                  <i class="fas fa-tachometer-alt me-2"></i>แดชบอร์ด
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/it_smo/pages/public/logout.php">
                  <i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ
                </a></li>
              </ul>
            </li>
          <?php else: ?>
            <!-- User is not logged in -->
            <li class="nav-item">
              <a class="nav-link btn btn-outline-light px-3" href="/it_smo/pages/public/login.php" id="loginBtn">
                <i class="fas fa-sign-in-alt me-1"></i>เข้าสู่ระบบ
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
  <?php if ($flashMessage): ?>
  <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
    <?= $flashMessage['message'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>