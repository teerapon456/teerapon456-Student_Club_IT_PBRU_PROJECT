<?php
class SessionHandler
{
  private static $instance = null;
  private $sessionTimeout = 1800; // 30 minutes

  private function __construct()
  {
    // ตั้งค่าพารามิเตอร์เซสชันที่ปลอดภัย
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.cookie_samesite', 'Strict');

    // ตรวจเซสชันว่าเริ่มแล้วหรือยัง ถ้ายังให้เริ่มเซสซัน
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    // ตรวจสอบการหมดเวลาเซสชัน
    $this->checkSessionTimeout();
  }

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function checkSessionTimeout()
  {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $this->sessionTimeout)) {
      $this->destroy();
      throw new Exception('Session expired', 401);
    }
    $_SESSION['last_activity'] = time();
  }

  public function set($key, $value)
  {
    $_SESSION[$key] = $value;
  }

  public function get($key, $default = null)
  {
    return $_SESSION[$key] ?? $default;
  }

  public function destroy()
  {
    session_unset();
    session_destroy();
    if (isset($_COOKIE[session_name()])) {
      setcookie(session_name(), '', time() - 3600, '/');
    }
  }

  public function regenerate()
  {
    session_regenerate_id(true);
  }

  public function isLoggedIn()
  {
    return isset($_SESSION['user_id']);
  }

  public function getUserId()
  {
    return $_SESSION['user_id'] ?? null;
  }

  public function getUserRole()
  {
    return $_SESSION['user_role'] ?? null;
  }
}