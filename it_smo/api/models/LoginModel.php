<?php
require_once __DIR__ . '/../config/Database.php';

class LoginModel
{
  private $conn;
  private $table = 'users';

  public function __construct()
  {
    $database = new Database();
    $this->conn = $database->getConnection();
  }

  /**
   * รับผู้ใช้โดย student_id (สำหรับการตรวจสอบสิทธิ์)
   * @param string $student_id
   * @return array|false ข้อมูลผู้ใช้หากพบว่าเป็นความลับหากไม่พบ
   */
  public function getUserByStudentId($student_id)
  {
    try {
      $query = "
                SELECT u.*, r.role_name 
                FROM " . $this->table . " u
                LEFT JOIN roles r ON u.role_id = r.role_id
                WHERE u.student_id = :student_id 
                AND u.status = 'เปิดใช้งาน'
            ";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':student_id', $student_id);
      $stmt->execute();
      if ($stmt->rowCount() > 0) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
      }
      return false;
    } catch (PDOException $e) {
      error_log("Login error: " . $e->getMessage());
      return false;
    }
  }

  /**
   * อัปเดตรหัสผ่านของผู้ใช้
   * @param int $user_id User ID
   * @param string $password New password
   * @return bool Success status
   */
  private function updatePassword($user_id, $password)
  {
    try {
      $hashedPassword = PasswordHelper::hashPassword($password);
      $stmt = $this->conn->prepare("
                UPDATE {$this->table} 
                SET password = ? 
                WHERE user_id = ?
            ");
      return $stmt->execute([$hashedPassword, $user_id]);
    } catch (PDOException $e) {
      error_log("Update password error: " . $e->getMessage());
      throw new Exception('เกิดข้อผิดพลาดในการอัปเดตรหัสผ่าน', 500);
    }
  }

  /**
   * อัปเดตเวลาเข้าสู่ระบบครั้งล่าสุดของผู้ใช้
   * @param int $user_id User ID
   * @return bool Success status
   */
  public function updateLastLogin($user_id)
  {
    try {
      $query = "UPDATE " . $this->table . " 
               SET last_login = CURRENT_TIMESTAMP 
               WHERE user_id = :user_id";

      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':user_id', $user_id);
      return $stmt->execute();
    } catch (PDOException $e) {
      error_log("Update last login error: " . $e->getMessage());
      return false;
    }
  }

  /**
   * ตรวจสอบว่ามีผู้ใช้อยู่หรือไม่
   * @param string $student_id Student ID
   * @return bool
   */
  public function userExists($student_id)
  {
    try {
      $stmt = $this->conn->prepare("
                SELECT COUNT(*) 
                FROM {$this->table} 
                WHERE student_id = ?
            ");
      $stmt->execute([$student_id]);
      return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
      error_log("Check user exists error: " . $e->getMessage());
      throw new Exception('เกิดข้อผิดพลาดในการตรวจสอบผู้ใช้', 500);
    }
  }
}