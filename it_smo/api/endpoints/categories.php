<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/CategoryModal.php';
require_once __DIR__ . '/../services/CategoryService.php';

// สร้างวัตถุเชื่อมต่อฐานข้อมูล
$db = new Database();
$model = new CategoryModal($db->getConnection());
$service = new CategoryService($model);

// ตัวแปรรับฟังก์ชันและข้อมูล
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// ตรวจสอบการรับค่าจากฟังก์ชัน GET POST PUT
switch ($method) {
  case 'GET':
    if (isset($_GET['id'])) {
      $response = $service->getById($_GET['id']);
    } elseif (isset($_GET['parent_id'])) {
      $response = $service->getCategoriesByParent($_GET['parent_id']);
    } elseif (isset($_GET['hierarchy'])) {
      $response = $service->getCategoryHierarchy();
    } else {
      $response = $service->getAll();
    }
    break;

  case 'POST':
    $response = $service->createCategory($data);
    break;

  case 'PUT':
    if (isset($data['id'])) {
      $response = $service->update($data['id'], $data);
    } else {
      $response = ['success' => false, 'message' => 'ID is required'];
    }
    break;

  case 'DELETE':
    if (isset($data['id'])) {
      $response = $service->delete($data['id']);
    } else {
      $response = ['success' => false, 'message' => 'ID is required'];
    }
    break;

  default:
    $response = ['success' => false, 'message' => 'Invalid method'];
}

// ส่งค่าตอบสนอง
header('Content-Type: application/json');
echo json_encode($response);