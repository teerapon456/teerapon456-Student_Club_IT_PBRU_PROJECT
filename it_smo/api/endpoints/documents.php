<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/DocumentController.php';
require_once __DIR__ . '/../middleware/auth.php';

// ตั้งค่าหัวเรื่องการเข้าถึง
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// วัตถุจากคลาส DocumentController
$documentController = new DocumentController();

// รับค่าจากฟังก์ชันตอบสนอง
$method = $_SERVER['REQUEST_METHOD'];

try {
  // ประกาศการตรวจสอบสิทธิ์เข้าใช้งานระบบ
  if (!verifyAuth()) {
    throw new Exception('Unauthorized access', 401);
  }

  switch ($method) {
    case 'GET':
      // GET /documents.php, /documents.php?id=xxx, /documents.php?search=xxx
      if (isset($_GET['search'])) {
        $response = $documentController->search($_GET['search']);
      } elseif (isset($_GET['id'])) {
        $response = $documentController->getDocument($_GET['id']);
      } else {
        $response = $documentController->getAllDocuments();
      }
      break;

    case 'POST':
      // รองรับทั้ง JSON และ multipart/form-data (ดู logic ที่เพิ่มไว้ก่อนหน้า)
      if (isset($_FILES['document'])) {
        // --- อัปโหลดไฟล์ ---
        $uploadDir = __DIR__ . '/../../uploads/documents/';
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }
        $file = $_FILES['document'];
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['error'] !== UPLOAD_ERR_OK) {
          throw new Exception('เกิดข้อผิดพลาดในการอัปโหลดไฟล์', 400);
        }
        if (!in_array($file['type'], $allowedTypes)) {
          throw new Exception('ประเภทไฟล์ไม่ถูกต้อง', 400);
        }
        if ($file['size'] > $maxSize) {
          throw new Exception('ไฟล์มีขนาดใหญ่เกิน 10MB', 400);
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('doc_', true) . '.' . $ext;
        $filePath = $uploadDir . $newFileName;
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
          throw new Exception('ไม่สามารถบันทึกไฟล์ได้', 500);
        }
        // เตรียมข้อมูลสำหรับบันทึกฐานข้อมูล
        $data = [
          'title' => $_POST['title'] ?? '',
          'category_id' => $_POST['category_id'] ?? '',
          'document_year' => $_POST['document_year'] ?? '',
          'description' => $_POST['description'] ?? '',
          'status' => $_POST['status'] ?? 'ร่าง',
          'access_level' => $_POST['access_level'] ?? 'ภายใน',
          'file_path' => 'uploads/documents/' . $newFileName,
          'file_type' => $file['type'],
          'file_size' => $file['size'],
          'uploaded_by' => $_SESSION['user_id'] ?? null,
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s'),
          // เพิ่มฟิลด์อื่น ๆ ตาม schema ถ้ามี เช่น document_number, publish_date, expiry_date, keywords
        ];
        $response = $documentController->createDocument($data);
      } else {
        // รับ JSON ตามเดิม (เช่น API call จากระบบอื่น)
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $documentController->createDocument($data);
      }
      break;

    case 'PUT':
      if (!isset($_GET['id'])) {
        throw new Exception('Document ID is required', 400);
      }
      // รองรับทั้ง JSON และ multipart/form-data สำหรับแก้ไขไฟล์
      if (strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false && isset($_FILES['document'])) {
        // --- อัปโหลดไฟล์ใหม่ ---
        $uploadDir = __DIR__ . '/../../uploads/documents/';
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }
        $file = $_FILES['document'];
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['error'] !== UPLOAD_ERR_OK) {
          throw new Exception('เกิดข้อผิดพลาดในการอัปโหลดไฟล์', 400);
        }
        if (!in_array($file['type'], $allowedTypes)) {
          throw new Exception('ประเภทไฟล์ไม่ถูกต้อง', 400);
        }
        if ($file['size'] > $maxSize) {
          throw new Exception('ไฟล์มีขนาดใหญ่เกิน 10MB', 400);
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('doc_', true) . '.' . $ext;
        $filePath = $uploadDir . $newFileName;
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
          throw new Exception('ไม่สามารถบันทึกไฟล์ได้', 500);
        }
        // เตรียมข้อมูลสำหรับอัปเดตฐานข้อมูล
        $data = [
          'title' => $_POST['title'] ?? '',
          'category_id' => $_POST['category_id'] ?? '',
          'document_year' => $_POST['document_year'] ?? '',
          'description' => $_POST['description'] ?? '',
          'status' => $_POST['status'] ?? 'ร่าง',
          'access_level' => $_POST['access_level'] ?? 'ภายใน',
          'file_path' => 'uploads/documents/' . $newFileName,
          'file_type' => $file['type'],
          'file_size' => $file['size'],
          'updated_at' => date('Y-m-d H:i:s'),
        ];
        $response = $documentController->updateDocument($_GET['id'], $data);
      } else {
        // รับ JSON ตามเดิม (เช่น API call จากระบบอื่น)
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $documentController->updateDocument($_GET['id'], $data);
      }
      break;

    case 'DELETE':
      if (!isset($_GET['id'])) {
        throw new Exception('Document ID is required', 400);
      }
      $response = $documentController->deleteDocument($_GET['id']);
      break;

    default:
      throw new Exception('Method not allowed', 405);
  }

  echo json_encode([
    'status' => 'success',
    'data' => $response
  ]);
} catch (Exception $e) {
  http_response_code($e->getCode() ?: 500);
  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
  ]);
}