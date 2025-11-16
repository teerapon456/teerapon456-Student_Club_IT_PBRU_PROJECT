<?php
require_once __DIR__ . '/../models/DocumentModal.php';
require_once __DIR__ . '/../config/Database.php';

class DocumentService
{
  protected $model;

  public function __construct()
  {
    $database = new Database();
    $db = $database->getConnection();
    $this->model = new DocumentModal($db);
  }

  public function getAll($params = [])
  {
    $where = [];
    $binds = [];
    // กรอง params ที่ว่างออก
    $params = array_filter($params, function($v) {
        return $v !== '' && $v !== null;
    });
    try {
      return $this->model->getAll($params);
    } catch (Exception $e) {
      throw new Exception("ไม่สามารถดึงข้อมูลเอกสารทั้งหมดได้: " . $e->getMessage());
    }
  }

  public function getById($id)
  {
    try {
      $document = $this->model->getById($id);
      if (!$document) {
        throw new Exception("ไม่พบเอกสารที่ต้องการ");
      }
      return $document;
    } catch (Exception $e) {
      throw new Exception("ไม่สามารถดึงข้อมูลเอกสารได้: " . $e->getMessage());
    }
  }

  public function createDocument($data)
  {
    try {
      // ตรวจสอบช่องที่ต้องกรอก
      $required_fields = ['document_number', 'title', 'file_path', 'category_id', 'uploaded_by'];
      foreach ($required_fields as $field) {
        if (empty($data[$field])) {
          throw new Exception("กรุณากรอกข้อมูล {$field}");
        }
      }
      // ตั้งค่าเริ่มต้น
      $data['status'] = $data['status'] ?? 'ร่าง';
      $data['access_level'] = $data['access_level'] ?? 'ภายใน';
      $data['created_at'] = date('Y-m-d H:i:s');
      $data['updated_at'] = date('Y-m-d H:i:s');
      $data['publish_date'] = $data['publish_date'] ?? null;
      $data['expiry_date'] = $data['expiry_date'] ?? null;
      $data['document_year'] = $data['document_year'] ?? null;
      $data['keywords'] = $data['keywords'] ?? null;
      // สร้างเอกสาร
      $result = $this->model->create($data);
      if (!$result) {
        throw new Exception("ไม่สามารถสร้างเอกสารได้");
      }
      return ['success' => true, 'message' => 'สร้างเอกสารสำเร็จ'];
    } catch (Exception $e) {
      throw new Exception("ไม่สามารถสร้างเอกสารได้: " . $e->getMessage());
    }
  }

  public function update($id, $data)
  {
    try {
      $document = $this->model->getById($id);
      if (!$document) {
        throw new Exception("ไม่พบเอกสารที่ต้องการแก้ไข");
      }
      $data['updated_at'] = date('Y-m-d H:i:s');
      $data['publish_date'] = $data['publish_date'] ?? $document['publish_date'];
      $data['expiry_date'] = $data['expiry_date'] ?? $document['expiry_date'];
      $data['document_year'] = $data['document_year'] ?? $document['document_year'];
      $data['keywords'] = $data['keywords'] ?? $document['keywords'];
      $result = $this->model->update($id, $data);
      if (!$result) {
        throw new Exception("ไม่สามารถแก้ไขเอกสารได้");
      }
      return ['success' => true, 'message' => 'แก้ไขเอกสารสำเร็จ'];
    } catch (Exception $e) {
      throw new Exception("ไม่สามารถแก้ไขเอกสารได้: " . $e->getMessage());
    }
  }

  public function delete($id)
  {
    try {
      // ตรวจสอบว่ามีเอกสารอยู่หรือไม่
      $document = $this->model->getById($id);
      if (!$document) {
        throw new Exception("ไม่พบเอกสารที่ต้องการลบ");
      }

      // ลบเอกสาร
      $result = $this->model->delete($id);
      if (!$result) {
        throw new Exception("ไม่สามารถลบเอกสารได้");
      }

      return ['success' => true, 'message' => 'ลบเอกสารสำเร็จ'];
    } catch (Exception $e) {
      throw new Exception("ไม่สามารถลบเอกสารได้: " . $e->getMessage());
    }
  }

  public function searchDocuments($keyword)
  {
    try {
      return $this->model->search($keyword);
    } catch (Exception $e) {
      throw new Exception("ไม่สามารถค้นหาเอกสารได้: " . $e->getMessage());
    }
  }

  public function getDocumentsByCategory($category_id)
  {
    try {
      return $this->model->getByCategory($category_id);
    } catch (Exception $e) {
      throw new Exception("ไม่สามารถดึงข้อมูลเอกสารตามหมวดหมู่ได้: " . $e->getMessage());
    }
  }

  public function getDocumentsByAccessLevel($access_level)
  {
    try {
      return $this->model->getByAccessLevel($access_level);
    } catch (Exception $e) {
      throw new Exception("ไม่สามารถดึงข้อมูลเอกสารตามระดับการเข้าถึงได้: " . $e->getMessage());
    }
  }

  public function getDocumentsByUploader($user_id)
  {
    try {
      return $this->model->getByUploader($user_id);
    } catch (Exception $e) {
      throw new Exception("ไม่สามารถดึงข้อมูลเอกสารตามผู้อัปโหลดได้: " . $e->getMessage());
    }
  }

  public function filterDocuments($params = [])
  {
    try {
      return $this->model->filter($params);
    } catch (Exception $e) {
      throw new Exception("ไม่สามารถค้นหาเอกสารได้: " . $e->getMessage());
    }
  }
}