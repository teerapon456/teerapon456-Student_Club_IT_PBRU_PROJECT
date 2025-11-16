<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../../includes/auth.php';
require_once '../../../api/config/Database.php';
require_once '../../../api/controllers/UserController.php';

session_start();

// Permission check for page access
$allowedRoles = ['ผู้ดูแลระบบ', 'อาจารย์ที่ปรึกษา'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
  header('Location: /it_smo/pages/error/403.php');
  exit();
}

$database = new Database();
$db = $database->getConnection();
$userController = new UserController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userIdToDelete = $_POST['user_id'] ?? null;

  if ($userIdToDelete) {
    // Fetch user data for permission check
    $userToDeleteData = $userController->getUserById($userIdToDelete);

    if ($userToDeleteData) {
      $isTargetAdmin = $userToDeleteData['role_name'] === 'ผู้ดูแลระบบ';
      $isCurrentUserAdmin = $_SESSION['user_role'] === 'ผู้ดูแลระบบ';

      // Prevent non-admins from deleting admins and prevent self-deletion
      if (($isTargetAdmin && !$isCurrentUserAdmin) || ($userIdToDelete == $_SESSION['user_id'])) {
        $errorMsg = ($userIdToDelete == $_SESSION['user_id'])
          ? 'คุณไม่สามารถระงับการใช้งานบัญชีของตัวเองได้'
          : 'คุณไม่มีสิทธิ์ระงับการใช้งานบัญชีผู้ดูแลระบบ';
        header('Location: ./user_index.php?delete_error=' . urlencode($errorMsg));
        exit();
      }
    }

    // Proceed with hard deletion
    $result = $userController->handleRequest('DELETE', ['user_id' => $userIdToDelete]);

    if (isset($result['success']) && $result['success']) {
      header('Location: ./user_index.php?delete_success=1');
    } else {
      $errorMsg = $result['message'] ?? 'เกิดข้อผิดพลาดในการระงับการใช้งานผู้ใช้';
      header('Location: ./user_index.php?delete_error=' . urlencode($errorMsg));
    }
  } else {
    header('Location: ./user_index.php?delete_error=' . urlencode('ไม่พบ ID ผู้ใช้'));
  }
  exit();
} else {
  // Redirect if not a POST request
  header('Location: ./user_index.php');
  exit();
}