<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../../../api/config/Database.php';
$database = new Database();
$pdo = $database->getConnection();

if (!isset($_SESSION['user_id'])) {
  header('Location: /it_smo/pages/public/login.php');
  exit();
}

$allowedRoles = ['ผู้ดูแลระบบ','อาจารย์ที่ปรึกษา'];
if (!in_array($_SESSION['user_role'], $allowedRoles)) {
  header('Location: /it_smo/pages/public/login.php');
  exit();
}

$pageTitle = 'แผงควบคุมผู้ดูแลระบบ | IT SMO';
$pageName = 'dashboard';
$pageGroup = 'dashboard';

function thai_date($datetime) {
  $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
  $ts = strtotime($datetime);
  $day = date('j', $ts);
  $month = $months[(int)date('n', $ts)];
  $year = date('Y', $ts) + 543;
  return "$day $month $year";
}

$totalDocuments = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pendingDocuments = $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'ร่าง'")->fetchColumn();
$todayDownloads = $pdo->query("SELECT COUNT(*) FROM document_access_logs WHERE action = 'ดาวน์โหลด' AND DATE(created_at) = CURDATE()") ? $pdo->query("SELECT COUNT(*) FROM document_access_logs WHERE action = 'ดาวน์โหลด' AND DATE(created_at) = CURDATE()")->fetchColumn() : 0;

$recentDocs = $pdo->query("
  SELECT d.document_id, d.title, d.file_path, c.category_name, u.first_name, u.last_name, d.created_at, d.status
  FROM documents d
  LEFT JOIN document_categories c ON d.category_id = c.category_id
  LEFT JOIN users u ON d.uploaded_by = u.user_id
  ORDER BY d.created_at DESC
  LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

include_once '../../../includes/admin_header.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="/it_smo/assets/css/forms.css">
<link rel="stylesheet" href="/it_smo/assets/css/responsive.css">
<script src="/it_smo/assets/js/ux-enhancements.js"></script>
<style>
.dashboard-card {
  border: none;
  border-radius: 1rem;
  box-shadow: 0 2px 16px rgba(0,0,0,0.07);
  transition: all 0.3s ease;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  position: relative;
  overflow: hidden;
}
.dashboard-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}
.dashboard-card:hover {
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
  transform: translateY(-5px);
}
.dashboard-card:hover::before {
  transform: scaleX(1);
}
.stat-icon {
  width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;
  border-radius: 50%; font-size: 2rem; margin-bottom: 1rem;
}
.stat-icon.bg1 { background: linear-gradient(135deg,#4e73df,#224abe); color: #fff; }
.stat-icon.bg2 { background: linear-gradient(135deg,#1cc88a,#13855c); color: #fff; }
.stat-icon.bg3 { background: linear-gradient(135deg,#f6c23e,#dda20a); color: #fff; }
.stat-icon.bg4 { background: linear-gradient(135deg,#36b9cc,#258391); color: #fff; }
.table thead th { background: #f8f9fc; }
.table tbody tr:hover {
  background-color: rgba(78, 115, 223, 0.05);
  transform: translateY(-1px);
  transition: all 0.2s ease;
}
.btn-group .btn {
  transition: all 0.2s ease;
}
.btn-group .btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.document-title {
  cursor: pointer;
  transition: color 0.2s ease;
}
.document-title:hover {
  color: #4e73df !important;
}
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div class="mb-2 mb-md-0">
    <h2 class="fw-bold mb-0">
      <i class="bi bi-speedometer2 me-2 text-primary"></i>
      แดชบอร์ด
    </h2>
    <div class="text-muted small ms-1">สรุปภาพรวมระบบจัดการเอกสารและสโมสรนักศึกษา</div>
  </div>
  <div>
    <a href="../documents/document_upload.php" class="btn btn-primary btn-lg shadow-sm d-flex align-items-center gap-2">
      <i class="bi bi-cloud-arrow-up fs-5"></i>
      <span class="fw-semibold">อัปโหลดเอกสาร</span>
    </a>
  </div>
</div>
<div class="container-fluid py-4">
  <!-- Stat Cards -->
  <div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
      <div class="card dashboard-card text-center">
        <div class="card-body">
          <div class="stat-icon bg1 mb-2"><i class="bi bi-file-earmark-text"></i></div>
          <h2 class="fw-bold mb-0" id="totalDocuments"><?= $totalDocuments ?></h2>
          <div class="text-muted">เอกสารทั้งหมด</div>
          <div class="small text-success mt-1" id="docGrowth">+0% จากเดือนที่แล้ว</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card dashboard-card text-center">
        <div class="card-body">
          <div class="stat-icon bg2 mb-2"><i class="bi bi-people"></i></div>
          <h2 class="fw-bold mb-0" id="totalUsers"><?= $totalUsers ?></h2>
          <div class="text-muted">ผู้ใช้งานทั้งหมด</div>
          <div class="small text-success mt-1" id="userGrowthText">+0 คนใหม่เดือนนี้</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card dashboard-card text-center">
        <div class="card-body">
          <div class="stat-icon bg3 mb-2"><i class="bi bi-clock-history"></i></div>
          <h2 class="fw-bold mb-0" id="pendingDocuments"><?= $pendingDocuments ?></h2>
          <div class="text-muted">เอกสารรออนุมัติ</div>
          <div class="small text-warning mt-1" id="pendingStatus">รอการตรวจสอบ</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card dashboard-card text-center">
        <div class="card-body">
          <div class="stat-icon bg4 mb-2"><i class="bi bi-cloud-arrow-down"></i></div>
          <h2 class="fw-bold mb-0" id="todayDownloads"><?= $todayDownloads ?></h2>
          <div class="text-muted">ดาวน์โหลดวันนี้</div>
          <div class="small text-info mt-1" id="downloadTrend">+0% จากเมื่อวาน</div>
        </div>
    </div>
  </div>
</div>

  <!-- Charts -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
      <div class="card dashboard-card h-100">
        <div class="card-header bg-white border-0">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <h5 class="mb-2 mb-md-0">สถิติการใช้งานระบบ (6 เดือนล่าสุด)</h5>
            <div class="d-flex gap-2">
              <button class="btn btn-sm btn-outline-primary" onclick="updateChart('monthly')">
                <i class="bi bi-calendar-month me-1"></i>รายเดือน
              </button>
              <button class="btn btn-sm btn-outline-secondary" onclick="updateChart('weekly')">
                <i class="bi bi-calendar-week me-1"></i>รายสัปดาห์
              </button>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="position-relative" style="height: 300px;">
            <canvas id="systemStats"></canvas>
          </div>
          <div class="mt-3 text-center">
            <div class="row text-muted small">
              <div class="col-6">
                <i class="bi bi-circle-fill text-primary me-1"></i>
                <span id="uploadTotal">0</span> การอัปโหลด
              </div>
              <div class="col-6">
                <i class="bi bi-circle-fill text-success me-1"></i>
                <span id="downloadTotal">0</span> การดาวน์โหลด
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card dashboard-card h-100">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0">ประเภทเอกสาร</h5>
  </div>
      <div class="card-body">
          <div class="position-relative" style="height: 300px;">
            <canvas id="documentTypes"></canvas>
          </div>
          <div class="mt-3">
            <div id="docTypeLegend" class="text-center small"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Additional Stats Cards -->
  <div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
      <div class="card dashboard-card">
        <div class="card-body text-center">
          <div class="d-flex align-items-center justify-content-center mb-2">
            <i class="bi bi-calendar-check text-primary fs-4 me-2"></i>
            <h6 class="mb-0">เดือนนี้</h6>
          </div>
          <div class="row text-center">
            <div class="col-6">
              <h4 class="fw-bold text-primary mb-0" id="monthUploads">0</h4>
              <small class="text-muted">อัปโหลด</small>
            </div>
            <div class="col-6">
              <h4 class="fw-bold text-success mb-0" id="monthDownloads">0</h4>
              <small class="text-muted">ดาวน์โหลด</small>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-lg-3">
      <div class="card dashboard-card">
        <div class="card-body text-center">
          <div class="d-flex align-items-center justify-content-center mb-2">
            <i class="bi bi-graph-up text-warning fs-4 me-2"></i>
            <h6 class="mb-0">อัตราการเติบโต</h6>
  </div>
          <div class="row text-center">
            <div class="col-6">
              <h4 class="fw-bold text-warning mb-0" id="growthRate">0%</h4>
              <small class="text-muted">เอกสาร</small>
            </div>
            <div class="col-6">
              <h4 class="fw-bold text-info mb-0" id="userGrowth">0%</h4>
              <small class="text-muted">ผู้ใช้</small>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-lg-3">
      <div class="card dashboard-card">
        <div class="card-body text-center">
          <div class="d-flex align-items-center justify-content-center mb-2">
            <i class="bi bi-clock-history text-info fs-4 me-2"></i>
          <h6 class="mb-0">สถานะเอกสาร</h6>
          </div>
          <div class="row text-center">
            <div class="col-6">
              <h4 class="fw-bold text-success mb-0" id="publishedDocs">0</h4>
              <small class="text-muted">เผยแพร่</small>
            </div>
            <div class="col-6">
              <h4 class="fw-bold text-warning mb-0" id="draftDocs">0</h4>
              <small class="text-muted">ร่าง</small>
  </div>
</div>
      </div>
      </div>
    </div>
    <div class="col-md-6 col-lg-3">
      <div class="card dashboard-card">
        <div class="card-body text-center">
          <div class="d-flex align-items-center justify-content-center mb-2">
            <i class="bi bi-people text-secondary fs-4 me-2"></i>
            <h6 class="mb-0">ผู้ใช้งาน</h6>
          </div>
          <div class="row text-center">
            <div class="col-6">
              <h4 class="fw-bold text-primary mb-0" id="activeUsers">0</h4>
              <small class="text-muted">ใช้งาน</small>
            </div>
            <div class="col-6">
              <h4 class="fw-bold text-secondary mb-0" id="newUsers">0</h4>
              <small class="text-muted">ใหม่</small>
    </div>
  </div>
      </div>
      </div>
    </div>
  </div>

  <!-- Recent Table & Activity -->
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card dashboard-card h-100">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
          <h5 class="mb-0">เอกสารล่าสุด</h5>
          <a href="../documents/document_index.php" class="btn btn-sm btn-outline-primary">ดูทั้งหมด</a>
</div>
        <div class="px-3 pt-3">
          <input type="text" id="searchDocInput" class="form-control form-control-sm" placeholder="ค้นหาเอกสารหรือผู้อัปโหลด...">
      </div>
      <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="recentDocsTable">
            <thead>
              <tr>
                <th>ชื่อเอกสาร</th>
                <th>หมวดหมู่</th>
                <th>ผู้อัปโหลด</th>
                <th>วันที่</th>
                <th>สถานะ</th>
                <th>การดูแล</th>
            </tr>
          </thead>
          <tbody>
              <?php foreach ($recentDocs as $doc): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center">
                    <i class="bi bi-file-earmark-text text-primary me-2"></i>
                    <div class="fw-semibold document-title"><?= htmlspecialchars($doc['title']) ?></div>
                </div>
              </td>
              <td><?= htmlspecialchars($doc['category_name'] ?: 'ไม่มีหมวดหมู่') ?></td>
              <td><?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></td>
              <td><?= thai_date($doc['created_at']) ?></td>
              <td>
                <?php
                  $status = $doc['status'];
                  $badgeClass = 'bg-secondary';
                  if ($status === 'เผยแพร่') $badgeClass = 'bg-success';
                  else if ($status === 'ร่าง') $badgeClass = 'bg-warning text-dark';
                ?>
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
              </td>
              <td>
                  <?php
    $filePath = isset($doc['file_path']) ? $doc['file_path'] : '';
    $fileExists = $filePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $filePath);
  ?>
<div class="btn-group btn-group-sm" role="group">
  <?php if ($fileExists): ?>
    <a href="<?= htmlspecialchars($filePath) ?>"
       target="_blank"
       class="btn btn-outline-primary btn-sm"
       title="ดูเอกสาร"
       data-bs-toggle="tooltip"
       data-bs-placement="top">
      <i class="bi bi-eye"></i>
      <span class="d-none d-md-inline ms-1">ดู</span>
    </a>
    <a href="<?= htmlspecialchars($filePath) ?>"
       download
       class="btn btn-outline-success btn-sm"
       title="ดาวน์โหลด"
       data-bs-toggle="tooltip"
       data-bs-placement="top">
      <i class="bi bi-download"></i>
      <span class="d-none d-md-inline ms-1">ดาวน์โหลด</span>
    </a>
  <?php else: ?>
    <a href="#"
       class="btn btn-outline-secondary btn-sm disabled"
       tabindex="-1"
       aria-disabled="true"
       title="ไม่มีไฟล์ให้ดาวน์โหลด"
       data-bs-toggle="tooltip"
       data-bs-placement="top">
      <i class="bi bi-download"></i>
      <span class="d-none d-md-inline ms-1">ดาวน์โหลด</span>
    </a>
  <?php endif; ?>
</div>
              </td>
            </tr>
              <?php endforeach; ?>
              <?php if (empty($recentDocs)): ?>
            <tr>
                <td colspan="6" class="text-center text-muted">ไม่มีข้อมูล</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
    <div class="col-lg-4">
      <div class="card dashboard-card h-100">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0">กิจกรรมล่าสุด</h5>
      </div>
        <div class="card-body">
          <div id="recentActivities">
            <div class="text-center text-muted py-4">
              <i class="bi bi-hourglass-split fs-2"></i>
              <p class="mt-2">กำลังโหลดข้อมูล...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let systemStatsChart, documentTypesChart;
let currentChartType = 'monthly';

// ฟังก์ชันสำหรับแสดงเวลาที่ผ่านมา
function timeAgo(dateString) {
  const now = new Date();
  const past = new Date(dateString);
  const diffInSeconds = Math.floor((now - past) / 1000);
  
  if (diffInSeconds < 60) return 'ไม่กี่วินาทีที่แล้ว';
  if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' นาทีที่แล้ว';
  if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' ชั่วโมงที่แล้ว';
  if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' วันที่แล้ว';
  return Math.floor(diffInSeconds / 2592000) + ' เดือนที่แล้ว';
}

// ฟังก์ชันอัปเดต charts
function updateChart(type) {
  currentChartType = type;
  loadDashboardData();
}

// ฟังก์ชันโหลดข้อมูล dashboard
function loadDashboardData() {
  // แสดง loading state
  const loadingElements = document.querySelectorAll('.card-body canvas');
  loadingElements.forEach(canvas => {
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    
    // ล้าง canvas
    ctx.clearRect(0, 0, width, height);
    
    // วาดพื้นหลัง
    ctx.fillStyle = '#f8f9fa';
    ctx.fillRect(0, 0, width, height);
    
    // วาด loading spinner
    const centerX = width / 2;
    const centerY = height / 2;
    const radius = 20;
    
    ctx.strokeStyle = '#dee2e6';
    ctx.lineWidth = 3;
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    ctx.stroke();
    
    // วาด loading arc
    const time = Date.now() * 0.005;
    ctx.strokeStyle = '#4e73df';
    ctx.lineWidth = 3;
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, time, time + Math.PI);
    ctx.stroke();
    
    // วาดข้อความ
    ctx.fillStyle = '#6c757d';
    ctx.font = '14px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('กำลังโหลดข้อมูล...', centerX, centerY + radius + 30);
  });
  
  fetch('dashboard_data.php?type=' + currentChartType)
    .then(res => {
      if (!res.ok) {
        throw new Error('Network response was not ok');
      }
      return res.json();
    })
  .then(data => {
      if (data.success) {
        updateCharts(data);
        updateStats(data.stats);
        updateRecentActivities(data.recentActivities);
      } else {
        console.error('Error loading dashboard data:', data.error);
        showError('ไม่สามารถโหลดข้อมูลได้: ' + data.error);
      }
    })
    .catch(error => {
      console.error('Error fetching dashboard data:', error);
      showError('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error.message);
    });
}
// ฟังก์ชันแสดงข้อผิดพลาด
function showError(message) {
  const errorHtml = `
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  
  // แสดงข้อผิดพลาดที่ด้านบนของหน้า
  const container = document.querySelector('.container-fluid');
  const firstRow = container.querySelector('.row');
  container.insertBefore(document.createRange().createContextualFragment(errorHtml), firstRow);
}

// ฟังก์ชันอัปเดต charts
function updateCharts(data) {
  // System Stats Chart
  const systemStatsCtx = document.getElementById('systemStats').getContext('2d');
  
  if (systemStatsChart) {
    systemStatsChart.destroy();
  }
  
  systemStatsChart = new Chart(systemStatsCtx, {
        type: 'line',
        data: {
          labels: data.months,
      datasets: [
        {
          label: 'การอัปโหลด',
              data: data.uploads,
          borderColor: '#4e73df',
          backgroundColor: 'rgba(78,115,223,0.1)',
              tension: 0.4,
          fill: true,
          pointBackgroundColor: '#4e73df',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointHoverBorderWidth: 3
            },
            {
              label: 'การดาวน์โหลด',
              data: data.downloads,
          borderColor: '#1cc88a',
          backgroundColor: 'rgba(28,200,138,0.1)',
              tension: 0.4,
          fill: true,
          pointBackgroundColor: '#1cc88a',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointHoverBorderWidth: 3
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
        animation: {
          duration: 1000,
          easing: 'easeInOutQuart'
        },
          plugins: {
            legend: {
            position: 'top',
            labels: {
              usePointStyle: true,
              padding: 20,
              font: {
                size: 12
              }
            }
          },
          tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: 'rgba(0,0,0,0.9)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: '#4e73df',
            borderWidth: 1,
            cornerRadius: 8,
            callbacks: {
              title: function(context) {
                return 'ช่วงเวลา: ' + context[0].label;
              },
              label: function(context) {
                return context.dataset.label + ': ' + context.parsed.y + ' รายการ';
              }
            }
          }
        },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: '#858796'
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0,0,0,0.05)'
          },
          ticks: {
            color: '#858796',
            stepSize: 1
          }
        }
      },
      interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
      }
    }
  });

  // Document Types Chart
  const documentTypesCtx = document.getElementById('documentTypes').getContext('2d');
  
  if (documentTypesChart) {
    documentTypesChart.destroy();
  }
  
  const colors = ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc', '#858796', '#fd7e14', '#20c997'];
  
  documentTypesChart = new Chart(documentTypesCtx, {
        type: 'doughnut',
        data: {
          labels: data.docTypeLabels,
          datasets: [{
            data: data.docTypes,
        backgroundColor: colors.slice(0, data.docTypes.length),
        borderWidth: 2,
        borderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
      animation: {
        duration: 1000,
        easing: 'easeInOutQuart'
      },
          plugins: {
            legend: {
          position: 'bottom',
          labels: {
            usePointStyle: true,
            padding: 15,
            font: {
              size: 11
            }
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0,0,0,0.9)',
          titleColor: '#fff',
          bodyColor: '#fff',
          cornerRadius: 8,
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((context.parsed / total) * 100).toFixed(1);
              return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
            }
          }
        }
      },
      cutout: '65%'
    }
  });

  // อัปเดตสรุปข้อมูล
  document.getElementById('uploadTotal').textContent = data.uploads.reduce((a, b) => a + b, 0);
  document.getElementById('downloadTotal').textContent = data.downloads.reduce((a, b) => a + b, 0);
  
  // อัปเดต legend ของ document types
  const docTypeLegend = document.getElementById('docTypeLegend');
  if (data.docTypeLabels.length > 0) {
    const legendHtml = data.docTypeLabels.map((label, index) => {
      const color = colors[index % colors.length];
      const count = data.docTypes[index];
      const total = data.docTypes.reduce((a, b) => a + b, 0);
      const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
      
      return `
        <div class="d-inline-block me-2 mb-2">
          <i class="bi bi-circle-fill me-1" style="color: ${color}"></i>
          <span class="small">${label}: ${count} (${percentage}%)</span>
        </div>
      `;
    }).join('');
    docTypeLegend.innerHTML = legendHtml;
  } else {
    docTypeLegend.innerHTML = `
      <div class="text-center text-muted py-3">
        <i class="bi bi-pie-chart fs-4 text-light"></i>
        <p class="mt-2 mb-0 small">ไม่มีข้อมูลประเภทเอกสาร</p>
      </div>
    `;
  }
}

// ฟังก์ชันอัปเดตสถิติ
function updateStats(stats) {
  // อัปเดต stat cards หลัก
  document.getElementById('totalDocuments').textContent = stats.total_documents || 0;
  document.getElementById('totalUsers').textContent = stats.total_users || 0;
  document.getElementById('pendingDocuments').textContent = stats.pending_documents || 0;
  document.getElementById('todayDownloads').textContent = stats.today_downloads || 0;
  
  // อัปเดต additional stats cards
  document.getElementById('monthUploads').textContent = stats.this_month_uploads || 0;
  document.getElementById('monthDownloads').textContent = stats.this_month_downloads || 0;
  document.getElementById('publishedDocs').textContent = stats.published_documents || 0;
  document.getElementById('draftDocs').textContent = stats.pending_documents || 0;
  document.getElementById('activeUsers').textContent = stats.active_users_this_month || 0;
  document.getElementById('newUsers').textContent = stats.new_users_this_month || 0;
  
  // คำนวณอัตราการเติบโต
  const totalDocs = stats.total_documents || 1;
  const growthRate = stats.this_month_uploads > 0 ? Math.round((stats.this_month_uploads / totalDocs) * 100) : 0;
  const userGrowth = stats.total_users > 0 ? Math.round((stats.new_users_this_month / stats.total_users) * 100) : 0;
  
  document.getElementById('growthRate').textContent = growthRate + '%';
  document.getElementById('userGrowth').textContent = userGrowth + '%';
  
  // อัปเดตข้อความเพิ่มเติม
  document.getElementById('docGrowth').textContent = `+${stats.this_month_uploads || 0} รายการเดือนนี้`;
  document.getElementById('userGrowthText').textContent = `+${stats.new_users_this_month || 0} คนใหม่เดือนนี้`;
  document.getElementById('pendingStatus').textContent = stats.pending_documents > 0 ? 'รอการตรวจสอบ' : 'ไม่มีเอกสารรอ';
  document.getElementById('downloadTrend').textContent = `+${stats.this_month_downloads || 0} รายการเดือนนี้`;
}

// ฟังก์ชันอัปเดตกิจกรรมล่าสุด
function updateRecentActivities(activities) {
  const container = document.getElementById('recentActivities');
  
  if (!activities || activities.length === 0) {
    container.innerHTML = `
      <div class="text-center text-muted py-4">
        <i class="bi bi-inbox fs-1 text-light"></i>
        <p class="mt-3 mb-0">ไม่มีกิจกรรมล่าสุด</p>
        <small class="text-muted">กิจกรรมจะแสดงที่นี่เมื่อมีการใช้งานระบบ</small>
      </div>
    `;
    return;
  }
  
  const activitiesHtml = activities.map(activity => {
    let icon, statusBadge = '';
    
    if (activity.type === 'upload') {
      icon = 'bi-cloud-arrow-up text-primary';
      if (activity.status) {
        statusBadge = `<span class="badge ${activity.status === 'เผยแพร่' ? 'bg-success' : 'bg-warning'} ms-2">${activity.status}</span>`;
      }
    } else if (activity.type === 'download') {
      icon = 'bi-cloud-arrow-down text-success';
      if (activity.status) {
        statusBadge = `<span class="badge ${activity.status === 'เผยแพร่' ? 'bg-success' : 'bg-warning'} ms-2">${activity.status}</span>`;
      }
    } else if (activity.type === 'user') {
      icon = 'bi-person-plus text-info';
    }
    
    const timeAgoText = timeAgo(activity.created_at);
    
    return `
      <div class="d-flex align-items-start mb-3">
        <div class="flex-shrink-0">
          <i class="bi ${icon} fs-5"></i>
        </div>
        <div class="flex-grow-1 ms-3">
          <div class="fw-semibold small">
            ${activity.user_name || 'ไม่ระบุ'}
            ${statusBadge}
          </div>
          <div class="text-muted small">${activity.action_text}</div>
          ${activity.title ? `<div class="text-muted small">${activity.title}</div>` : ''}
          <div class="text-muted small">${timeAgoText}</div>
        </div>
      </div>
    `;
  }).join('');
  
  container.innerHTML = activitiesHtml;
}

// ฟังก์ชันค้นหาเอกสารในตาราง
const searchInput = document.getElementById('searchDocInput');
const table = document.getElementById('recentDocsTable');
searchInput.addEventListener('input', function() {
  const filter = this.value.toLowerCase();
  const rows = table.querySelectorAll('tbody tr');
  rows.forEach(row => {
    const docName = row.cells[0].innerText.toLowerCase();
    const uploader = row.cells[2].innerText.toLowerCase(); // Changed from 1 to 2 for uploader
    const category = row.cells[1].innerText.toLowerCase(); // Added category
    if (docName.includes(filter) || uploader.includes(filter) || category.includes(filter)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});

// โหลดข้อมูลเมื่อหน้าเว็บโหลดเสร็จ
document.addEventListener('DOMContentLoaded', function() {
  loadDashboardData();
  
  // อัปเดตข้อมูลทุก 5 นาที
  setInterval(loadDashboardData, 300000);
  
  // ปรับขนาด charts เมื่อหน้าจอเปลี่ยนขนาด
  window.addEventListener('resize', function() {
    if (systemStatsChart) {
      systemStatsChart.resize();
    }
    if (documentTypesChart) {
      documentTypesChart.resize();
    }
  });
  
  // เปิดใช้งาน tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
</script>
<?php include_once '../../../includes/admin_footer.php'; ?>