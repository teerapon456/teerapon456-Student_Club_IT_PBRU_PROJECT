<?php
// Manual require (เหมือนใน import.php)
$psBase = __DIR__ . '/../../../vendor/PhpSpreadsheet-4.3.1/src/PhpSpreadsheet/';
require_once $psBase . 'Exception.php';
require_once $psBase . 'IComparable.php';
require_once $psBase . 'Settings.php';
require_once $psBase . 'Spreadsheet.php';
require_once $psBase . 'Document/Properties.php';
require_once $psBase . 'Document/Security.php';
require_once $psBase . 'Worksheet/Worksheet.php';
require_once $psBase . 'Worksheet/Row.php';
require_once $psBase . 'Worksheet/Column.php';
require_once $psBase . 'Cell/Cell.php';
require_once $psBase . 'Cell/DataType.php';
require_once $psBase . 'Cell/IValueBinder.php';
require_once $psBase . 'Cell/DefaultValueBinder.php';
require_once $psBase . 'Cell/Coordinate.php';
require_once $psBase . 'Reader/IReader.php';
require_once $psBase . 'Reader/BaseReader.php';
require_once $psBase . 'Reader/XlsBase.php';
require_once $psBase . 'Reader/Xlsx.php';
require_once $psBase . 'Reader/Xls.php';
require_once $psBase . 'IOFactory.php';
require_once $psBase . 'Writer/Xlsx.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray([
  ['รหัสนักศึกษา', 'อีเมล', 'ชื่อ', 'นามสกุล', 'เบอร์โทรศัพท์', 'รหัสบทบาท', 'รหัสสาขา', 'รหัสสาขาย่อย'],
  ['650000001', 'user1@example.com', 'สมชาย', 'ใจดี', '0812345678', '1', '1', '1'],
  ['650000002', 'user2@example.com', 'สมหญิง', 'รักเรียน', '0898765432', '2', '1', '2'],
], NULL, 'A1');

$writer = new Xlsx($spreadsheet);
$filename = __DIR__ . '/import_users_example.xlsx';
$writer->save($filename);
echo 'สร้างไฟล์ตัวอย่างสำเร็จ: ' . $filename;
