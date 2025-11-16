<?php
require_once 'Modal.php';

class CategoryModal extends Modal
{
  public function __construct($db)
  {
    parent::__construct($db);
    $this->table_name = "document_categories";
    $this->primary_key = "category_id";
  }

  public function getByParent($parent_id)
  {
    $query = "SELECT * FROM " . $this->table_name . " WHERE parent_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $parent_id);
    $stmt->execute();
    return $stmt;
  }

  public function getHierarchy()
  {
    $query = "WITH RECURSIVE category_hierarchy AS (
                    SELECT category_id, category_name, parent_id, 1 as level
                    FROM " . $this->table_name . "
                    WHERE parent_id IS NULL
                    UNION ALL
                    SELECT c.category_id, c.category_name, c.parent_id, ch.level + 1
                    FROM " . $this->table_name . " c
                    JOIN category_hierarchy ch ON c.parent_id = ch.category_id
                )
                SELECT * FROM category_hierarchy
                ORDER BY level, category_name";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt;
  }
}
