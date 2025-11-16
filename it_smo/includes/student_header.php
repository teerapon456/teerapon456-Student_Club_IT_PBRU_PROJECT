<?php
$pageTitle = $pageTitle ?? 'Student Dashboard | IT SMO';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'นักศึกษา') {
  header("Location: /it_smo/pages/public/login.php");
  exit();
}

require_once __DIR__ . '/../api/config/Database.php';
require_once __DIR__ . '/../api/controllers/UserController.php';
require_once __DIR__ . '/../api/models/MajorModal.php';

$database = new Database();
$db = $database->getConnection();
$userController = new UserController($db);
$majorModel = new MajorModal($db);

$userData = $userController->getUserById($_SESSION['user_id']);
if (is_object($userData)) $userData = (array)$userData;
if (!is_array($userData)) $userData = [];

$studentId = isset($userData['student_id']) ? $userData['student_id'] : '-';
$majorName = '-';
if (!empty($userData['major_id'])) {
  $majors = $majorModel->getAllMajors();
  foreach ($majors as $m) {
    if ($m['major_id'] == $userData['major_id']) {
      $majorName = $m['major_name'];
      break;
    }
  }
}
$profileImage = $userData['profile_image'] ?? '/it_smo/assets/img/default_profile.png';

$user = [
  'user_id' => $_SESSION['user_id'],
  'first_name' => $userData['first_name'] ?? '',
  'last_name' => $userData['last_name'] ?? '',
  'status' => $userData['status'] ?? ''
];
$fullName = trim($user['first_name'] . ' ' . $user['last_name']);
if ($fullName === '') $fullName = 'นักศึกษา';

?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" type="image/png" href="/it_smo/assets/img/itnobg.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/it_smo/assets/css/admincss.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
</head>
<body style="margin:0;padding:0;">
  <div id="preloader">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">กำลังโหลด...</span>
    </div>
  </div>
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo-container">
        <img src="/it_smo/assets/img/it_logo.png" alt="IT Logo" class="logo-img">
        <h5 class="text-white mb-1">สโมสรนักศึกษา IT</h5>
        <small class="text-white-50">มหาวิทยาลัยราชภัฏเพชรบุรี</small>
      </div>
    </div>
    <nav class="sidebar-menu">
      <a href="/it_smo/pages/student/dashboard.php" class="sidebar-link <?php echo (isset($pageGroup) && $pageGroup === 'dashboard') ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>หน้าหลัก</span>
      </a>
      <a href="/it_smo/pages/student/profile.php" class="sidebar-link <?php echo (isset($pageGroup) && $pageGroup === 'profile') ? 'active' : ''; ?>">
        <i class="fas fa-user-edit"></i>
        <span>แก้ไขโปรไฟล์</span>
      </a>
      <a href="/it_smo/pages/student/documents.php" class="sidebar-link <?php echo (isset($pageGroup) && $pageGroup === 'documents') ? 'active' : ''; ?>">
        <i class="fas fa-file-alt"></i>
        <span>ดาวน์โหลดเอกสาร</span>
      </a>
      <a href="/it_smo/pages/public/logout.php" class="sidebar-link logout-link" id="logout-link">
        <i class="fas fa-sign-out-alt"></i>
        <span>ออกจากระบบ</span>
      </a>
    </nav>
  </aside>
  <main class="main-content">
    <nav class="custom-navbar">
      <div class="mr-auto"></div>
      <div class="d-flex align-items-center">
        <img src="https://flagcdn.com/th.svg" alt="TH" style="height:18px; width:auto; margin-right:6px;">
        <img src="https://flagcdn.com/gb.svg" alt="EN" style="height:18px; width:auto; margin-right:18px;">
        <a href="#" class="text-secondary mx-2" title="แจ้งเตือน"><i class="fas fa-bell"></i></a>
        <a href="#" class="text-secondary mx-2" title="คู่มือ"><i class="fas fa-question-circle"></i></a>
        <div class="profile-box ml-3">
          <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile" style="width:48px;height:48px;object-fit:cover;" class="rounded-circle">
          <div class="profile-info">
            <div class="name"><?= htmlspecialchars($fullName) ?></div>
            <div class="id">บทบาท: นักศึกษา</div>
            <div class="status">สถานะ: <?= htmlspecialchars($user['status']) ?></div>
          </div>
        </div>
      </div>
    </nav>
    <div class="content-container"> 