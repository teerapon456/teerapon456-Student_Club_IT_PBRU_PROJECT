<?php
class PasswordHelper
{
  /**
   * ตรวจสอบความแข็งแกร่งของรหัสผ่าน
   * @param string $password
   * @return array ข้อมูลที่มีผลการตรวจสอบและข้อความแสดงข้อผิดพลาด
   */
  public static function validatePassword($password)
  {
    $errors = [];

    // ตรวจสอบความยาวขั้นต่ำของรหัสผ่าน
    if (strlen($password) < 8) {
      $errors[] = 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร';
    }

    // ตรวจสอบอักษรตัวพิมพ์ใหญ่อย่างน้อยหนึ่งตัว
    if (!preg_match('/[A-Z]/', $password)) {
      $errors[] = 'รหัสผ่านต้องมีตัวอักษรตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว';
    }

    // ตรวจสอบอักษรตัวพิมพ์เล็กอย่างน้อยหนึ่งตัว
    if (!preg_match('/[a-z]/', $password)) {
      $errors[] = 'รหัสผ่านต้องมีตัวอักษรตัวพิมพ์เล็กอย่างน้อย 1 ตัว';
    }

    // ตรวจสอบอย่างน้อยหนึ่งหมายเลข
    if (!preg_match('/[0-9]/', $password)) {
      $errors[] = 'รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว';
    }

    //  ตรวจสอบอักขระพิเศษอย่างน้อยหนึ่งตัว
    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
      $errors[] = 'รหัสผ่านต้องมีอักขระพิเศษอย่างน้อย 1 ตัว';
    }

    return [
      'valid' => empty($errors),
      'errors' => $errors
    ];
  }

  /**
   * รหัสผ่านแฮชโดยใช้password_hashของ PHP
   * @param string $password
   * @return string รหัสผ่านที่แฮช
   */
  public static function hashPassword($password)
  {
    return password_hash($password, PASSWORD_DEFAULT);
  }

  /**
   * ตรวจสอบรหัสผ่านกับแฮช
   * @param string $password
   * @param string $hash
   * @return bool
   */
  public static function verifyPassword($password, $hash)
  {
    return password_verify($password, $hash);
  }

  /**
   * ตรวจสอบว่ารหัสผ่านจำเป็นต้องมีการแฮชใหม่หรือไม่
   * @param string $hash
   * @return bool
   */
  public static function needsRehash($hash)
  {
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
  }
}