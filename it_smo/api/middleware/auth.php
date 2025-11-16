<?php
session_start();

function verifyAuth()
{
  // ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
  if (!isset($_SESSION['user_id'])) {
    return false;
  }

  // ตรวจสอบโทเค็น API ในส่วนหัว
  $headers = getallheaders();
  $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

  if (!$token) {
    return false;
  }

  // ตรวจสอบโทเค็น (ใช้ตรรกะการตรวจสอบโทเค็นของคุณที่นี่)
  // สำหรับตอนนี้ เราจะตรวจสอบว่าเซสชันนั้นถูกต้องหรือไม่
  return true;
}

function getCurrentUserId()
{
  return $_SESSION['user_id'] ?? null;
}

function requireAuth()
{
  if (!verifyAuth()) {
    http_response_code(401);
    echo json_encode([
      'status' => 'error',
      'message' => 'Unauthorized access'
    ]);
    exit;
  }
}