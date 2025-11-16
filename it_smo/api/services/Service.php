<?php
abstract class Service
{
  protected $model;

  public function __construct($model)
  {
    $this->model = $model;
  }

  // รับบันทึกทั้งหมด
  public function getAll()
  {
    $stmt = $this->model->getAll();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // รับบันทึกเดียว
  public function getById($id)
  {
    $stmt = $this->model->getById($id);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // สร้างบันทึก
  public function create($data)
  {
    return $this->model->create($data);
  }

  // อัปเดตบันทึก
  public function update($id, $data)
  {
    return $this->model->update($id, $data);
  }

  // ลบบันทึก
  public function delete($id)
  {
    return $this->model->delete($id);
  }

  // ตรวจสอบข้อมูล
  protected function validate($data, $rules)
  {
    $errors = [];

    foreach ($rules as $field => $rule) {
      if (isset($data[$field])) {
        if ($rule['required'] && empty($data[$field])) {
          $errors[$field] = "This field is required";
        }

        if (isset($rule['min_length']) && strlen($data[$field]) < $rule['min_length']) {
          $errors[$field] = "Minimum length is " . $rule['min_length'];
        }

        if (isset($rule['max_length']) && strlen($data[$field]) > $rule['max_length']) {
          $errors[$field] = "Maximum length is " . $rule['max_length'];
        }

        if (isset($rule['email']) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
          $errors[$field] = "Invalid email format";
        }
      }
    }

    return $errors;
  }

  protected function validateRequiredFields($data, $requiredFields)
  {
    $errors = Utils::validateInput($data, $requiredFields);
    if (!empty($errors)) {
      throw new Exception(implode(', ', $errors));
    }
    return true;
  }

  protected function sanitizeData($data)
  {
    return Utils::sanitizeInput($data);
  }

  protected function successResponse($data, $message = 'Success')
  {
    return [
      'success' => true,
      'message' => $message,
      'data' => $data
    ];
  }

  protected function errorResponse($message, $code = 400)
  {
    return [
      'success' => false,
      'message' => $message,
      'code' => $code
    ];
  }
}