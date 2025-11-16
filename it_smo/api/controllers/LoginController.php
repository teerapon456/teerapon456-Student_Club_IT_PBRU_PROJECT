<?php
require_once __DIR__ . '/../models/LoginModel.php';
require_once __DIR__ . '/../services/UserService.php';

class LoginController
{
  private $userService;

  public function __construct()
  {
    $db = (new \Database())->getConnection();
    $model = new UserModal($db);
    $this->userService = new UserService($model);
  }
  public function login($student_id, $password)
  {
    try {
      // ตรวจสอบการรับค่าข้อมูล
      if (empty($student_id) || empty($password)) {
        return [
          'success' => false,
          'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'
        ];
      }

      // ตรวจสอบผู้ใช้โดยใช้ ในส่วนของ UserService
      $user = $this->userService->login($student_id, $password);

      if ($user) {
        // เริ่มเซสชันถ้ายังไม่เริ่ม
        if (session_status() === PHP_SESSION_NONE) {
          session_start();
        }

        // ตั้งค่าข้อมูลเซสชัน
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_role'] = $user['role_name'];
        $_SESSION['user_data'] = $user;

        return [
          'success' => true,
          'message' => 'เข้าสู่ระบบสำเร็จ',
          'user' => $user
        ];
      }

      return [
        'success' => false,
        'message' => 'รหัสนักศึกษาหรือรหัสผ่านไม่ถูกต้อง'
      ];
    } catch (Exception $e) {
      error_log("Login Controller Error: " . $e->getMessage());
      return [
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ'
      ];
    }
  }

  /**
   * จัดการคำขอออกจากระบบ
   * @return array ข้อมูลที่มีการตอบสนอง
   */
  public function logout()
  {
    // ตรวจสอบผู้ใช้โดยใช้ ในส่วนของ UserService
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    // ล้างข้อมูลเซสชันทั้งหมด
    $_SESSION = array();

    // ทำลายข้อมูลเซสชันทั้งหมด
    session_destroy();

    return [
      'success' => true,
      'message' => 'ออกจากระบบสำเร็จ'
    ];
  }

  /**
   * ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
   * @return bool
   */
  public function isLoggedIn()
  {
    return isset($_SESSION['user_id']);
  }

  /**
   * รับข้อมูลผู้ใช้ปัจจุบัน
   * @return array|null ข้อมูลผู้ใช้หรือเป็นค่าว่างไหม ถ้ายังไม่ได้เข้าสู่ระบบ
   */
  public function getCurrentUser()
  {
    return $_SESSION['user_data'] ?? null;
  }
}