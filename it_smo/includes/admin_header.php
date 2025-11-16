<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params(['path' => '/']);
  session_start();
}

// ตั้งค่า timezone
date_default_timezone_set('Asia/Bangkok');
// ตรวจสอบสิทธิ์การเข้าถึงหน้าต่างแอดมิน
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
  header("Location: /it_smo/pages/public/login.php");
  exit();
}

// ดึงข้อมูลมาเก็บไว้ที่ session เพื่อเอามาแสดงในหน้าต่างแอดมิน
$user = [
  'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '',
  'role' => isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '',
  'first_name' => isset($_SESSION['user_data']['first_name']) ? $_SESSION['user_data']['first_name'] : '',
  'last_name' => isset($_SESSION['user_data']['last_name']) ? $_SESSION['user_data']['last_name'] : '',
  'status' => isset($_SESSION['user_data']['status']) ? $_SESSION['user_data']['status'] : '',
  'profile_image' => isset($_SESSION['user_data']['profile_image']) ? $_SESSION['user_data']['profile_image'] : ''
];
$fullName = trim($user['first_name'] . ' ' . $user['last_name']);
if ($fullName === '') $fullName = 'ผู้ใช้งาน';

//ประกาศเมนูเพื่อเก็บสิทธิ์การเข้าถึงหน้าต่างตามบทบาท
$menuItems = [];

// Define all possible menu items ประกาศฟังก์ชันส่วนการทำงานทั้งหมด
$allMenuItems = [
  // จัดการระบบสมาชิก
  'system_management' => [
    'group' => 'users',
    'items' => [
      ['icon' => 'fas fa-users', 'text' => 'จัดการข้อมูลสมาชิก', 'link' => '/it_smo/pages/admin/users/user_index.php', 'group' => 'users', 'permissions' => ['ผู้ดูแลระบบ','อาจารย์ที่ปรึกษา']],
      ['icon' => 'fas fa-user-shield', 'text' => 'จัดการข้อมูลสิทธิ์การใช้งาน', 'link' => '/it_smo/pages/admin/roles/role_index.php', 'group' => 'roles', 'permissions' => ['ผู้ดูแลระบบ','อาจารย์ที่ปรึกษา']]
    ]
  ],
    // จัดการข้อมูลเอกสาร
    'document_management' => [
      'group' => 'documents',
      'items' => [
        ['icon' => 'fas fa-file-alt', 'text' => 'จัดการข้อมูลเอกสาร', 'link' => '/it_smo/pages/admin/documents/document_index.php', 'group' => 'documents', 'permissions' => ['ผู้ดูแลระบบ','อาจารย์ที่ปรึกษา', 'นายกสโมสรนักศึกษา', 'รองนายกสโมสรนักศึกษา', 'เลขานุการสโมสรนักศึกษา']],
        ['icon' => 'fas fa-folder', 'text' => 'จัดการข้อมูลหมวดหมู่เอกสาร', 'link' => '/it_smo/pages/admin/documents/document_categories.php', 'group' => 'categories', 'permissions' => ['ผู้ดูแลระบบ','อาจารย์ที่ปรึกษา']],
        ['icon' => 'fas fa-upload', 'text' => 'อัปโหลดเอกสาร', 'link' => '/it_smo/pages/admin/documents/document_upload.php', 'group' => 'upload', 'permissions' => ['ผู้ดูแลระบบ','อาจารย์ที่ปรึกษา', 'นายกสโมสรนักศึกษา', 'รองนายกสโมสรนักศึกษา', 'เลขานุการสโมสรนักศึกษา']],
      ]
    ],
  // รายงาน
  'reports' => [
    'group' => 'reports',
    'items' => [
      ['icon' => 'fas fa-chart-bar', 'text' => 'รายงานสถิติภาพรวม', 'link' => '/it_smo/pages/admin/reports/report_index.php', 'group' => 'reports', 'permissions' => ['ผู้ดูแลระบบ','อาจารย์ที่ปรึกษา', 'นายกสโมสรนักศึกษา', 'อาจารย์ที่ปรึกษา']],
    ]
  ],
    // ตั้งค่าระบบ
    'settings' => [
      'group' => 'settings',
      'items' => [
        ['icon' => 'fas fa-cogs', 'text' => 'ตั้งค่าการทำงานระบบ', 'link' => '/it_smo/pages/admin/settings/setting_index.php', 'group' => 'settings', 'permissions' => ['ผู้ดูแลระบบ','อาจารย์ที่ปรึกษา']]
      ]
    ]
];

// Add dashboard as first menu item for all roles เส้นทางไปยังหน้าแดชบอร์ด
$roleToDashboardPath = [
  'ผู้ดูแลระบบ' => '/it_smo/pages/admin/dashboard/admin.php',
  'นายกสโมสรนักศึกษา' => '/it_smo/pages/admin/dashboard/president.php',
  'รองนายกสโมสรนักศึกษา' => '/it_smo/pages/admin/dashboard/vice_president.php',
  'เลขานุการสโมสรนักศึกษา' => '/it_smo/pages/admin/dashboard/secretary.php',
  'กรรมการสโมสรนักศึกษา' => '/it_smo/pages/admin/dashboard/committee.php',
  'อาจารย์ที่ปรึกษา' => '/it_smo/pages/admin/dashboard/admin.php',
];
$dashboardLink = isset($roleToDashboardPath[$user['role']]) ? $roleToDashboardPath[$user['role']] : '/it_smo/pages/admin/dashboard/dashboard_admin.php';
$menuItems[] = [
  'icon' => 'fas fa-home',
  'text' => 'หน้าหลัก',
  'link' => $dashboardLink,
  'group' => 'dashboard'
];

// กรองรายการเมนูตามบทบาทของผู้ใช้สิทธิ์ในการอนุญาต
foreach ($allMenuItems as $section) {
  foreach ($section['items'] as $item) {
    if (in_array($user['role'], $item['permissions'])) {
      // เพิ่มข้อมูลกลุ่มลงในรายการเมนู
      $item['parent_group'] = $section['group'];
      $menuItems[] = $item;
    }
  }
}

// Remove 'อัปโหลดเอกสาร' from menuItems
$menuItems = array_values(array_filter($menuItems, function($item) {
  return !(isset($item['text']) && $item['text'] === 'อัปโหลดเอกสาร');
}));
// Optionally, reorder menuItems here if needed (e.g., by group or custom order)
// Example: Move 'จัดการข้อมูลสมาชิก' and 'จัดการข้อมูลสิทธิ์การใช้งาน' to top after dashboard/profile
// You can customize the order as needed

?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/it_smo/assets/img/itnobg.png">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/it_smo/assets/css/admincss.css">


  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
</head>

<body style="margin:0;padding:0;">
  <!-- ตัวโหลดล่วงหน้า -->
  <div id="preloader">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">กำลังโหลด...</span>
    </div>
  </div>

  <!-- แถบด้านข้าง -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo-container">
        <img src="/it_smo/assets/img/it_logo.png" alt="IT Logo" class="logo-img">
        <h5 class="text-white mb-1">สโมสรนักศึกษา IT</h5>
        <small class="text-white-50">มหาวิทยาลัยราชภัฏเพชรบุรี</small>
      </div>
    </div>

    <nav class="sidebar-menu">
      <!-- แดชบอร์ด -->
      <a href="<?php echo $menuItems[0]['link']; ?>"
        class="sidebar-link <?php echo (isset($pageGroup) && $pageGroup === 'dashboard') ? 'active' : ''; ?>">
        <i class="<?php echo $menuItems[0]['icon']; ?>"></i>
        <span><?php echo $menuItems[0]['text']; ?></span>
      </a>
      <!-- แก้ไขข้อมูลส่วนตัว -->
      <a href="/it_smo/pages/admin/user_profile.php" class="sidebar-link">
        <i class="fas fa-user-edit"></i>
        <span>แก้ไขข้อมูลส่วนตัว</span>
      </a>
      <?php
      // Render all permitted menu items as flat links (no dropdowns)
      for ($i = 1; $i < count($menuItems); $i++) {
        $item = $menuItems[$i];
        $isActive = isset($pageGroup) && $pageGroup === $item['group'];
      ?>
        <a href="<?php echo $item['link']; ?>" class="sidebar-link <?php echo $isActive ? 'active' : ''; ?>">
          <i class="<?php echo $item['icon']; ?>"></i>
          <span><?php echo $item['text']; ?></span>
        </a>
      <?php
      }
      ?>
      <!-- ออกจากระบบ -->
      <a href="/it_smo/pages/public/logout.php" class="sidebar-link logout-link" id="logout-link">
        <i class="fas fa-sign-out-alt"></i>
        <span>ออกจากระบบ</span>
      </a>
    </nav>
  </aside>

  <!-- เนื้อหาหลัก -->
  <main class="main-content">
    <!-- แถบนำทาง -->
     <!-- Navbar -->
    <nav class="custom-navbar">
      <div class="mr-auto"></div>
      <div class="d-flex align-items-center">
        <img src="https://flagcdn.com/th.svg" alt="TH" style="height:18px; width:auto; margin-right:6px;">
        <img src="https://flagcdn.com/gb.svg" alt="EN" style="height:18px; width:auto; margin-right:18px;">
        <a href="#" class="text-secondary mx-2" title="แจ้งเตือน"><i class="fas fa-bell"></i></a>
        <a href="#" class="text-secondary mx-2" title="คู่มือ"><i class="fas fa-question-circle"></i></a>
        <div class="profile-box ml-3">
          <?php
            $profileDir = $_SERVER['DOCUMENT_ROOT'] . '/it_smo/uploads/profile/' . $user['user_id'] . '/';
            $profileWebPath = '/it_smo/uploads/profile/' . $user['user_id'] . '/';
            $profileImg = '';
            if (is_dir($profileDir)) {
              $files = glob($profileDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
              if ($files && count($files) > 0) {
                $basename = basename($files[0]);
                $profileImg = $profileWebPath . $basename;
              }
            }
            if ($profileImg && file_exists($_SERVER['DOCUMENT_ROOT'] . $profileImg)) {
              echo '<img src="' . htmlspecialchars($profileImg) . '" alt="Profile" style="width:48px;height:48px;object-fit:cover;" class="rounded-circle">';
            } else {
              // Generate initials avatar if no profile image
              $initials = '';
              if (!empty($user['first_name'])) {
                $initials .= mb_substr($user['first_name'], 0, 1, 'UTF-8');
              }
              if (!empty($user['last_name'])) {
                $initials .= mb_substr($user['last_name'], 0, 1, 'UTF-8');
              }
              echo '<img src="https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=random&color=fff&size=48" alt="Profile" style="width:48px;height:48px;object-fit:cover;" class="rounded-circle">';
            }
          ?>
          <div class="profile-info">
            <div class="name"><?php echo htmlspecialchars($fullName); ?></div>
            <div class="id">ตำแหน่ง: <?php echo htmlspecialchars($user['role']); ?></div>
            <div class="status">สถานะ: <?php echo htmlspecialchars($user['status']); ?></div>
          </div>
        </div>
      </div>
    </nav>
    <!-- คอนเทนเนอร์เนื้อหาของหน้า -->
    <div class="content-container"></body>

</html>
