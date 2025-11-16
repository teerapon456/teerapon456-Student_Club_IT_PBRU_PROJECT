<?php
class Utils
{
  public static function sendResponse($data, $status = 200)
  {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
  }

  public static function validateInput($data, $requiredFields)
  {
    $errors = [];
    foreach ($requiredFields as $field) {
      if (!isset($data[$field]) || empty($data[$field])) {
        $errors[] = "Field '{$field}' is required";
      }
    }
    return $errors;
  }

  public static function sanitizeInput($data)
  {
    if (is_array($data)) {
      return array_map([self::class, 'sanitizeInput'], $data);
    }
    return htmlspecialchars(strip_tags($data));
  }

  public static function generateToken($length = 32)
  {
    return bin2hex(random_bytes($length));
  }

  public static function hashPassword($password)
  {
    return password_hash($password, PASSWORD_DEFAULT);
  }

  public static function verifyPassword($password, $hash)
  {
    return password_verify($password, $hash);
  }

  public static function logError($message, $data = [])
  {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if (!empty($data)) {
      $logMessage .= " - " . json_encode($data);
    }
    error_log($logMessage . "\n", 3, __DIR__ . '/../logs/error.log');
  }
}