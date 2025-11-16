<?php
require_once __DIR__ . '/../models/UserModal.php';
require_once __DIR__ . '/../services/UserService.php';

class UserController
{
  private $service;

  public function __construct($db)
  {
    $model = new UserModal($db);
    $this->service = new UserService($model);
  }

  public function handleRequest($method, $data = null)
  {
    switch ($method) {
      case 'GET':
        if (isset($data['type']) && $data['type'] === 'roles') {
          return $this->service->getRoles();
        }
        if (isset($data['student_id'])) {
          return $this->service->getById($data['student_id']);
        }
        return $this->service->getAll($data);
      case 'POST':
        return $this->service->create($data);
      case 'PUT':
        // รองรับทั้ง user_id และ student_id
        $id = $data['user_id'] ?? $data['student_id'] ?? null;
        return $this->service->update($id, $data);
      case 'DELETE':
        $id = $data['user_id'] ?? $data['student_id'] ?? null;
        return $this->service->delete($id);
      case 'SOFT_DELETE':
        return $this->service->softDelete($data['student_id']);
      default:
        return ['success' => false, 'message' => 'Invalid method'];
    }
  }

  public function getUserById($id)
  {
    return $this->service->getById($id);
  }
}
