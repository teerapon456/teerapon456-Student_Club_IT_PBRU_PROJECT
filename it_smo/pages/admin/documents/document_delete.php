<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../includes/auth.php';
require_once '../../../api/config/Database.php';

// ตรวจสอบสิทธิ์ด้วย Auth::getInstance()
$auth = Auth::getInstance();
$user = $auth->getCurrentUser();
// ตรวจสอบสิทธิ์
session_start();
$allowedRoles = ['ผู้ดูแลระบบ', 'ประธานสโมสร', 'รองประธานสโมสร', 'เลขานุการ', 'เจ้าหน้าที่'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
  header('Location: /it_smo/pages/error/403.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['document_id'])) {
  $docId = intval($_POST['document_id']);
  $database = new Database();
  $db = $database->getConnection();
  // ดึงชื่อไฟล์
  $stmt = $db->prepare("SELECT file_path FROM documents WHERE document_id = ?");
  $stmt->execute([$docId]);
  $doc = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($doc) {
    $uploadDir = '../../../uploads/documents/';
    $file = $doc['file_path'];
    if ($file && file_exists($uploadDir . $file)) {
      @unlink($uploadDir . $file);
    }
    // ลบข้อมูลในฐานข้อมูล
    $delStmt = $db->prepare("DELETE FROM documents WHERE document_id = ?");
    $delStmt->execute([$docId]);
    header('Location: ./document_index.php?delete_success=1');
    exit;
  } else {
    header('Location: ./document_index.php?delete_error=ไม่พบข้อมูลเอกสาร');
    exit;
  }
} else {
  header('Location: document_index.php?delete_error=ข้อมูลไม่ถูกต้อง');
  exit;
}
