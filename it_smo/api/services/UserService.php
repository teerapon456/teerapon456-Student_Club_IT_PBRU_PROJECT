<?php
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/../models/UserModal.php';
require_once __DIR__ . '/../helpers/PasswordHelper.php';
class UserService extends Service
{
  public function __construct($model)
  {
    parent::__construct($model);
  }

  public function login($student_id, $password)
  {
    $user = $this->model->getByStudentId($student_id);
    if ($user) {
      // ตรวจสอบว่ารหัสผ่านตรงกับรหัสผ่านที่แฮชหรือไม่
      if (PasswordHelper::verifyPassword($password, $user['password'])) {
        // ตรวจสอบว่ารหัสผ่านจำเป็นต้องมีการแฮชใหม่หรือไม่
        if (PasswordHelper::needsRehash($user['password'])) {
          $this->updatePassword($user['user_id'], $password);
        }
        $this->model->updateLastLogin($user['user_id']);
        unset($user['password']);
        return $user;
      }
      // ตรวจสอบว่ารหัสผ่านตรงกับข้อความธรรมดาหรือไม่ (เพื่อความเข้ากันได้แบบย้อนหลัง)
      if (trim($password) === trim($user['password'])) {
        // แฮชรหัสผ่านข้อความธรรมดาและอัปเดต
        $this->updatePassword($user['user_id'], $password);
        $this->model->updateLastLogin($user['user_id']);
        unset($user['password']);
        return $user;
      }
    }
    return false;
  }

  public function register($data)
  {
    $rules = [
      'student_id' => ['required' => true, 'min_length' => 9, 'max_length' => 9],
      'email' => ['required' => true, 'email' => true],
      'password' => ['required' => true],
      'first_name' => ['required' => true],
      'last_name' => ['required' => true]
    ];

    $errors = $this->validate($data, $rules);
    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    // ตรวจสอบความแข็งแกร่งของรหัสผ่าน
    $passwordValidation = PasswordHelper::validatePassword($data['password']);
    if (!$passwordValidation['valid']) {
      return ['success' => false, 'errors' => ['password' => $passwordValidation['errors']]];
    }

    // ตรวจสอบว่ามีอีเมลหรือ student_id อยู่แล้วหรือไม่
    $stmt = $this->model->getByEmail($data['email']);
    if ($stmt) {
      return ['success' => false, 'errors' => ['email' => 'Email already exists']];
    }

    $stmt = $this->model->getByStudentId($data['student_id']);
    if ($stmt) {
      return ['success' => false, 'errors' => ['student_id' => 'Student ID already exists']];
    }

    // รหัสผ่านแฮช
    $data['password'] = PasswordHelper::hashPassword($data['password']);

    // สร้างบัญชีผู้ใช้
    $result = $this->model->create($data);
    return ['success' => $result];
  }

  /**
   * รับค่าผู้ใช้ด้วยชื่อผู้ใช้
   * @param string $username
   * @return array|bool
   */
  public function getByUsername($username)
  {
    try {
      $stmt = $this->model->getConnection()->prepare(
        "SELECT * FROM users WHERE username = ?"
      );
      $stmt->execute([$username]);
      return $stmt->fetch();
    } catch (PDOException $e) {
      return false;
    }
  }

  /**
   * บันทึกโทเค็นการรีเซ็ตสำหรับผู้ใช้
   * @param int $userId
   * @param string $token
   * @return bool
   */
  public function saveResetToken($userId, $token)
  {
    try {
      $stmt = $this->model->getConnection()->prepare(
        "UPDATE users SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?"
      );
      return $stmt->execute([$token, $userId]);
    } catch (PDOException $e) {
      return false;
    }
  }

  /**
   * รับผู้ใช้ด้วยการรีเซ็ตโทเค็น
   * @param string $token
   * @return array|bool
   */
  public function getByResetToken($token)
  {
    try {
      $stmt = $this->model->getConnection()->prepare(
        "SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()"
      );
      $stmt->execute([$token]);
      return $stmt->fetch();
    } catch (PDOException $e) {
      return false;
    }
  }

  /**
   * อัปเดตรหัสผ่านของผู้ใช้
   * @param int $userId User ID
   * @param string $password New password
   * @return bool Success status
   */
  private function updatePassword($userId, $password)
  {
    try {
      $hashedPassword = PasswordHelper::hashPassword($password);
      $this->model->updatePassword($userId, $hashedPassword);
      return true;
    } catch (PDOException $e) {
      error_log("Update password error: " . $e->getMessage());
      return false;
    }
  }

  /**
   * รับระดับการเข้าถึงเอกสาร
   * @param int $documentId
   * @return array|bool
   */
  public function getDocumentAccessLevel($documentId)
  {
    try {
      $stmt = $this->model->getConnection()->prepare(
        "SELECT access_level FROM documents WHERE id = ?"
      );
      $stmt->execute([$documentId]);
      return $stmt->fetch();
    } catch (PDOException $e) {
      return false;
    }
  }

  public function getAll($params = [])
  {
    return $this->model->getAll($params);
  }

  public function getRoles()
  {
    return $this->model->getRoles();
  }

  public function getById($id)
  {
    return $this->model->getById($id);
  }

  public function create($data)
  {
    $result = $this->model->create($data);
    if ($result === true) {
      return ['success' => true];
    } else {
      return ['success' => false, 'message' => $result];
    }
  }

  public function update($id, $data)
  {
    // ตรวจสอบว่ามี id จริง
    if (!$id) {
        return ['success' => false, 'message' => 'ไม่พบรหัสผู้ใช้สำหรับอัปเดต'];
    }

    // ถ้ามี password ใหม่ ให้ hash ก่อน
    if (isset($data['password']) && !empty($data['password'])) {
        $data['password'] = PasswordHelper::hashPassword($data['password']);
    } else {
        unset($data['password']); // ไม่อัปเดต password ถ้าไม่ได้กรอก
    }

    $result = $this->model->update($id, $data);
    if ($result) {
        return ['success' => true, 'message' => 'อัปเดตข้อมูลผู้ใช้เรียบร้อยแล้ว'];
    } else {
        return ['success' => false, 'message' => 'ไม่สามารถอัปเดตข้อมูลผู้ใช้ได้'];
    }
  }

  public function delete($id)
  {
    $result = $this->model->delete($id);
    if ($result) {
      // 1. ลบไฟล์โปรไฟล์
      $userToDeleteData = $this->getById($id);
      if (!empty($userToDeleteData['profile_image'])) {
        $profilePath = $_SERVER['DOCUMENT_ROOT'] . $userToDeleteData['profile_image'];
        if (file_exists($profilePath)) {
          unlink($profilePath);
        }
      }

      // 2. ลบประวัติการใช้งาน (ตัวอย่าง: document_access_logs)
      $stmt = $this->model->getConnection()->prepare("DELETE FROM document_access_logs WHERE user_id = ?");
      $stmt->execute([$id]);

      // 3. ลบข้อมูลอื่น ๆ ที่เกี่ยวข้อง (เพิ่มตามต้องการ)
      return ['success' => true, 'message' => 'ลบผู้ใช้สำเร็จ'];
    } else {
      return ['success' => false, 'message' => 'ไม่สามารถลบผู้ใช้ได้'];
    }
  }

  public function softDelete($id)
  {
    $data = ['status' => 'ระงับการใช้งาน'];
    $result = $this->model->update($id, $data);
    if ($result) {
      return ['success' => true, 'message' => 'ระงับการใช้งานผู้ใช้สำเร็จ'];
    } else {
      return ['success' => false, 'message' => 'ไม่สามารถระงับการใช้งานผู้ใช้ได้'];
    }
  }
}