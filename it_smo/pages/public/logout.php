<?php
require_once __DIR__ . '/../../includes/auth.php';

// เริ่ม session ถ้ายังไม่ได้เริ่ม
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

try {
  // สร้าง instance ของ Auth class โดยใช้ Singleton pattern
  $auth = Auth::getInstance();

  // ออกจากระบบ
  $result = $auth->logout();

  if ($result['success']) {
    $role = $result['user']['role_name'] ?? '';
    if (!$role) {
      $error = 'บัญชีนี้ไม่มีสิทธิ์เข้าใช้งาน (ไม่มี role)';
      // ไม่ต้อง set session หรือ redirect
    } else {
      // ... redirect ตาม role ...
    }
  }

  // redirect ไปหน้า login
  header('Location: login.php');
  exit();
} catch (Exception $e) {
  // บันทึก error log
  error_log("Logout error: " . $e->getMessage());

  // แสดงข้อความ error
  $_SESSION['error'] = 'เกิดข้อผิดพลาดในการออกจากระบบ';

  // redirect ไปหน้า login
  header('Location: login.php');
  exit();
}
