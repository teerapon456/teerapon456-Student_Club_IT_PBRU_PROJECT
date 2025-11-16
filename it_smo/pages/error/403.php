<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 Forbidden | IT SMO</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .error-container {
      background: #fff;
      border-radius: 1.5rem;
      box-shadow: 0 0.5rem 2rem rgba(0,0,0,0.08);
      padding: 3rem 2.5rem;
      text-align: center;
      max-width: 420px;
      width: 100%;
    }
    .error-icon {
      font-size: 4rem;
      color: #dc3545;
      margin-bottom: 1.2rem;
    }
    .error-title {
      font-size: 2.2rem;
      font-weight: 700;
      color: #dc3545;
      margin-bottom: 0.5rem;
    }
    .error-desc {
      font-size: 1.1rem;
      color: #555;
      margin-bottom: 2rem;
    }
    .btn-home {
      font-size: 1.1rem;
      padding: 0.7rem 2.2rem;
      border-radius: 0.7rem;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <div class="error-icon">
      <i class="fas fa-ban"></i>
    </div>
    <div class="error-title">403 Forbidden</div>
    <div class="error-desc">คุณไม่มีสิทธิ์เข้าถึงหน้านี้<br>กรุณาตรวจสอบสิทธิ์การใช้งานหรือกลับไปยังหน้าหลัก</div>
    <a href="/it_smo/index.php" class="btn btn-danger btn-home"><i class="fas fa-home me-2"></i>กลับหน้าหลัก</a>
  </div>
</body>
</html>