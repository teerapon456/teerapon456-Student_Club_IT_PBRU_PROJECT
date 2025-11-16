<?php
require_once __DIR__ . '/../models/DocumentModal.php';
require_once __DIR__ . '/../services/DocumentService.php';

class DocumentController
{
  private $service;

  public function __construct()
  {
    $this->service = new DocumentService();
  }

  public function getDocument($id)
  {
    return $this->service->getById($id);
  }

  public function getAllDocuments()
  {
    return $this->service->getAll();
  }

  public function createDocument($data)
  {
    return $this->service->createDocument($data);
  }

  public function updateDocument($id, $data)
  {
    return $this->service->update($id, $data);
  }

  public function deleteDocument($id)
  {
    return $this->service->delete($id);
  }

  public function search($keyword)
  {
    return $this->service->searchDocuments($keyword);
  }
}