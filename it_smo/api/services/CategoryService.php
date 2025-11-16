<?php
require_once 'Service.php';

class CategoryService extends Service
{
  public function __construct($model)
  {
    parent::__construct($model);
  }

  public function createCategory($data)
  {
    $rules = [
      'category_name' => ['required' => true]
    ];

    $errors = $this->validate($data, $rules);
    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    $result = $this->model->create($data);
    return ['success' => $result];
  }

  public function getCategoriesByParent($parent_id)
  {
    $stmt = $this->model->getByParent($parent_id);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getCategoryHierarchy()
  {
    $stmt = $this->model->getHierarchy();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
