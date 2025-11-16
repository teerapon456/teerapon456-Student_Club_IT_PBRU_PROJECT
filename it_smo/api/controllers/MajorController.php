<?php
// api/controllers/MajorController.php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/MajorModal.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $majorModel = new MajorModal($db);

    if (isset($_GET['major_id'])) {
        $majorId = (int)$_GET['major_id'];
        if ($majorId > 0) {
            $subMajors = $majorModel->getSubMajorsByMajorId($majorId);
            echo json_encode(['success' => true, 'data' => $subMajors]);
        } else {
            throw new Exception("Major ID ไม่ถูกต้อง");
        }
    } else {
        throw new Exception("ไม่ได้ระบุ Major ID");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 