<?php
require_once __DIR__ . '/../api/config/Database.php';

class Auth extends Database
{
  private static $instance = null;
  private $db;
  private $user;
  private $password;

  public function __construct()
  {
    parent::__construct();
    $this->db = $this->getConnection();
  }

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  // ป้องกันการ clone object
  public function __clone() {}

  // ป้องกันการ unserialize
  public function __wakeup() {}

  /**
   * ตรวจสอบการล็อกอิน
   * @param string $student_id รหัสนักศึกษา
   * @param string $password รหัสผ่าน
   * @return array|false ข้อมูลผู้ใช้ถ้าสำเร็จ, false ถ้าไม่สำเร็จ
   */
  public function login($student_id, $password)
  {
    try {
      // Debug: แสดงค่าที่รับเข้ามา
      error_log("Login attempt - Student ID: " . $student_id);

      // ตรวจสอบข้อมูลผู้ใช้
      $stmt = $this->db->prepare("
        SELECT u.*, r.role_name 
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.role_id
        WHERE u.student_id = ? AND u.password = ?
      ");

      $stmt->execute([$student_id, $password]);

      // Debug: ดูจำนวนแถวที่พบ
      $rowCount = $stmt->rowCount();
      error_log("Found rows: " . $rowCount);

      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      // Debug: ดูข้อมูลที่ได้
      error_log("User data: " . print_r($user, true));

      if ($user) {
        // เริ่ม session ถ้ายังไม่มี
        if (session_status() === PHP_SESSION_NONE) {
          session_start();
        }

        // เก็บข้อมูลใน session
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
    } catch (PDOException $e) {
      // Debug: ดู error ที่เกิดขึ้น
      error_log("Login error: " . $e->getMessage());
      return [
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ: ' . $e->getMessage()
      ];
    }
  }

  /**
   * ตรวจสอบว่ามีการล็อกอินหรือไม่
   * @return bool
   */
  public function isLoggedIn()
  {
    return isset($_SESSION['user_id']);
  }

  /**
   * ออกจากระบบ
   * @return array
   */
  public function logout()
  {
    // ล้างข้อมูล session
    $_SESSION = array();

    // ลบ session cookie
    if (isset($_COOKIE[session_name()])) {
      setcookie(session_name(), '', time() - 3600, '/');
    }

    // ทำลาย session
    session_destroy();

    return [
      'success' => true,
      'message' => 'ออกจากระบบสำเร็จ'
    ];
  }

  /**
   * รับข้อมูลผู้ใช้ปัจจุบัน
   * @return array|null ข้อมูลผู้ใช้หรือ null ถ้าไม่ได้ล็อกอิน
   */
  public function getCurrentUser()
  {
    return $_SESSION['user_data'] ?? null;
  }

  /**
   * ตรวจสอบว่ามีผู้ใช้หรือไม่
   * @param string $student_id รหัสนักศึกษา
   * @return bool
   */
  public function userExists($student_id)
  {
    try {
      $stmt = $this->db->prepare("
        SELECT COUNT(*) 
        FROM users 
        WHERE student_id = ?
      ");
      $stmt->execute([$student_id]);
      return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
      error_log("Check user exists error: " . $e->getMessage());
      return false;
    }
  }

  /**
   * เปลี่ยนรหัสผ่าน
   * @param int $user_id รหัสผู้ใช้
   * @param string $new_password รหัสผ่านใหม่
   * @return bool
   */
  public function changePassword($user_id, $new_password)
  {
    try {
      $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
      $stmt = $this->db->prepare("
        UPDATE users 
        SET password = ? 
        WHERE user_id = ?
      ");
      return $stmt->execute([$hashedPassword, $user_id]);
    } catch (PDOException $e) {
      error_log("Change password error: " . $e->getMessage());
      return false;
    }
  }

  /**
   * ตรวจสอบสิทธิ์การเข้าถึง
   * @param string $role บทบาทที่ต้องการตรวจสอบ
   * @param string $permission สิทธิ์ที่ต้องการตรวจสอบ
   * @return bool
   */
  public function hasPermission($role, $permission)
  {
    try {
      // ดึงข้อมูลผู้ใช้ปัจจุบัน
      $user = $this->getCurrentUser();
      if (!$user) {
        return false;
      }

      // ถ้าเป็น admin มีสิทธิ์ทุกอย่าง
      if ($user['role_name'] === 'admin') {
        return true;
      }

      // ตรวจสอบบทบาทและสิทธิ์
      $stmt = $this->db->prepare("
        SELECT r.permissions 
        FROM roles r
        WHERE r.role_name = ?
        LIMIT 1
      ");
      $stmt->execute([$role]);
      $roleData = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$roleData || !$roleData['permissions']) {
        return false;
      }

      // แปลง JSON permissions เป็น array
      $permissions = json_decode($roleData['permissions'], true);
      if (!$permissions) {
        return false;
      }

      // ตรวจสอบว่ามีสิทธิ์ที่ต้องการหรือไม่
      return in_array($permission, $permissions) || in_array('all', $permissions);
    } catch (PDOException $e) {
      error_log("Permission check error: " . $e->getMessage());
      return false;
    }
  }
}
