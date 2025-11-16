<?php
session_set_cookie_params(['path' => '/']);
session_start();
require_once __DIR__ . '/../controllers/LoginController.php';

$loginController = new LoginController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $student_id = $_POST['student_id'] ?? '';
  $password = $_POST['password'] ?? '';

  $response = $loginController->login($student_id, $password);

  header('Content-Type: application/json');
  if ($response['success']) {
    // กำหนด redirect ตามบทบาท
    $role = $response['user']['role_name'] ?? '';
    $redirect = '/it_smo/pages/student/dashboard.php';
    switch ($role) {
      case 'ผู้ดูแลระบบ':
      case 'อาจารย์ที่ปรึกษา':
      case 'นายกสโมสรนักศึกษา':
        $redirect = '/it_smo/pages/admin/dashboard/admin.php'; break;
      case 'รองนายกสโมสรนักศึกษา':
        $redirect = '/it_smo/pages/admin/dashboard/vice_president.php'; break;
      case 'เลขานุการสโมสรนักศึกษา':
        $redirect = '/it_smo/pages/admin/dashboard/secretary.php'; break;
      case 'กรรมการสโมสรนักศึกษา':
      case 'อนุกรรมการสโมสรนักศึกษา':
        $redirect = '/it_smo/pages/admin/dashboard/committee.php'; break;
      case 'นักศึกษา':
        $redirect = '/it_smo/pages/student/dashboard.php'; break;
    }
    echo json_encode([
      'success' => true,
      'message' => 'เข้าสู่ระบบสำเร็จ',
      'redirect' => $redirect
    ]);
    exit();
  } else {
    // ความปลอดภัย: clear session user ทุกครั้งที่ fail
    unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_data']);
    session_regenerate_id(true);
    echo json_encode([
      'success' => false,
      'message' => $response['message'] ?? 'รหัสผ่านหรือชื่อบัญชีผิด'
    ]);
    exit();
  }
}