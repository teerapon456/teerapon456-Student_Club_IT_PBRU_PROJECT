<?php $pageTitle = "เกี่ยวกับเรา | IT SMO";
include_once __DIR__ . '/../../includes/header.php';

// --- ส่วนข้อมูลคงที่ชั่วคราว (Hardcode) พร้อมลิงก์รูปภาพจริง ---
$club_members = [
    [
        'first_name' => 'สมชาย',
        'last_name' => 'ใจดี',
        'profile_image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80',
        'role_name' => 'ประธานสโมสรนักศึกษา'
    ],
    [
        'first_name' => 'สมหญิง',
        'last_name' => 'ใจเย็น',
        'profile_image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=80',
        'role_name' => 'รองประธานสโมสรนักศึกษา'
    ],
    [
        'first_name' => 'กิจดี',
        'last_name' => 'ทำจริง',
        'profile_image' => 'https://images.unsplash.com/photo-1511367461989-f85a21fda167?auto=format&fit=crop&w=200&q=80',
        'role_name' => 'เลขานุการ'
    ],
    [
        'first_name' => 'สุชาติ',
        'last_name' => 'ร่วมคิด',
        'profile_image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80',
        'role_name' => 'กรรมการ'
    ],
    [
        'first_name' => 'สมปอง',
        'last_name' => 'ร่วมแรง',
        'profile_image' => 'https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=200&q=80',
        'role_name' => 'กรรมการ'
    ],
    [
        'first_name' => 'สมศรี',
        'last_name' => 'ร่วมใจ',
        'profile_image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=200&q=80',
        'role_name' => 'กรรมการ'
    ]
];

// แยกกลุ่มตามตำแหน่ง
$president = [];
$vice_president = [];
$secretary = [];
$committees = [];
foreach ($club_members as $member) {
    switch ($member['role_name']) {
        case 'ประธานสโมสรนักศึกษา':
            $president[] = $member;
            break;
        case 'รองประธานสโมสรนักศึกษา':
            $vice_president[] = $member;
            break;
        case 'เลขานุการ':
            $secretary[] = $member;
            break;
        default:
            $committees[] = $member;
    }
}
?>
<main>
  <!-- Hero Section -->
  <section class="hero-section text-center mb-5" data-aos="fade-up">
    <div class="container">
      <div class="hero-content">
        <h1 class="display-4 mb-3 fw-bold">เกี่ยวกับเรา</h1>
        <p class="lead">สโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ มหาวิทยาลัยราชภัฏเพชรบุรี</p>
        <div class="hero-buttons mt-4">
          <a href="#vision" class="btn btn-light btn-lg me-3">
            <i class="fas fa-eye me-2"></i>วิสัยทัศน์
          </a>
          <a href="#structure" class="btn btn-outline-light btn-lg">
            <i class="fas fa-sitemap me-2"></i>โครงสร้างองค์กร
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Vision, Mission, Policy Section -->
  <section id="vision" class="py-5" data-aos="fade-up">
    <div class="container">
      <div class="row g-4 align-items-stretch">
        <div class="col-md-4" data-aos="fade-right" data-aos-delay="100">
          <div class="card h-100 shadow-sm hover-card">
            <div class="card-body">
              <div class="icon-wrapper mb-4">
                <i class="fas fa-eye fa-3x text-primary"></i>
              </div>
              <h3 class="card-title text-primary mb-4">วิสัยทัศน์</h3>
              <p class="card-text">เป็นสโมสรนักศึกษาที่มุ่งพัฒนาศักยภาพนักศึกษาให้เป็นบัณฑิตที่มีคุณภาพ มีความเป็นผู้นำ
                และมีจิตสาธารณะ พร้อมก้าวสู่การเป็นองค์กรนักศึกษาระดับแนวหน้าของประเทศ</p>
            </div>
          </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
          <div class="card h-100 shadow-sm hover-card">
            <div class="card-body">
              <div class="icon-wrapper mb-4">
                <i class="fas fa-bullseye fa-3x text-primary"></i>
              </div>
              <h3 class="card-title text-primary mb-4">พันธกิจ</h3>
              <p class="card-text">จัดกิจกรรมที่ส่งเสริมการเรียนรู้ พัฒนาทักษะ และสร้างประสบการณ์ให้นักศึกษา
                พร้อมทั้งส่งเสริมการมีส่วนร่วมในกิจกรรมของคณะและมหาวิทยาลัย เพื่อสร้างบัณฑิตที่มีคุณภาพและความพร้อมในการทำงาน</p>
            </div>
          </div>
        </div>
        <div class="col-md-4" data-aos="fade-left" data-aos-delay="300">
          <div class="card h-100 shadow-sm hover-card">
            <div class="card-body">
              <div class="icon-wrapper mb-4">
                <i class="fas fa-balance-scale fa-3x text-primary"></i>
              </div>
              <h3 class="card-title text-primary mb-4">นโยบาย</h3>
              <ul class="list-unstyled mb-0">
                <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>ส่งเสริมความโปร่งใสและธรรมาภิบาลในการบริหารงาน</li>
                <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>เน้นการมีส่วนร่วมของนักศึกษาทุกคน</li>
                <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>สนับสนุนกิจกรรมสร้างสรรค์และจิตอาสา</li>
                <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>พัฒนาทักษะ Soft Skills & Hard Skills</li>
                <li><i class="fas fa-check-circle text-success me-2"></i>สร้างเครือข่ายความร่วมมือกับองค์กรภายนอก</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- History Section -->
  <section class="py-5 bg-light" data-aos="fade-up">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6 mb-4 mb-md-0" data-aos="fade-right">
          <div class="history-image-wrapper">
            <img 
              src="../../assets/img/photo2566/meeting2566-2.jpg"
              class="img-fluid rounded shadow"
              alt="ภาพกิจกรรมสโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ"
              style="width:100%; height:320px; object-fit:cover; object-position:center;"
            >
            <div class="history-overlay">
              <span class="year-badge">ประชุมคณะกรรมการปี 2566</span>
            </div>
          </div>
        </div>
        <div class="col-md-6" data-aos="fade-left">
          <h2 class="mb-4 section-title">ประวัติสโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ</h2>
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <h4>2545</h4>
                <p>ก่อตั้งสโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ</p>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <h4>2550</h4>
                <p>เริ่มจัดกิจกรรมวิชาการและกีฬาระหว่างคณะ</p>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <h4>2555</h4>
                <p>ขยายกิจกรรมสู่ชุมชนและสังคม</p>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <h4>2567</h4>
                <p>พัฒนาสู่การเป็นองค์กรนักศึกษาระดับแนวหน้า</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Committee Structure Section -->
  <section id="structure" class="py-5" data-aos="fade-up">
    <div class="container">
      <div class="card shadow-lg border-0 mb-5 structure-card">
        <div class="card-body p-5">
          <div class="text-center mb-5">
            <h2 class="section-title display-4 fw-bold text-primary mb-3">
              <span class="position-relative">
                ผังโครงสร้างสโมสรนักศึกษาปี 2567
              </span>
            </h2>
            <p class="lead text-muted">แสดงโครงสร้างและรายชื่อคณะกรรมการสโมสรนักศึกษาปีปัจจุบัน</p>
          </div>
          <div class="container py-3">
            <h3 class="mb-4 text-center">รายชื่อคณะกรรมการสโมสรนักศึกษา</h3>
            <div class="row g-4 justify-content-center">
              <?php foreach ($president as $member): ?>
                <div class="col-md-4 text-center">
                  <img src="<?= htmlspecialchars($member['profile_image']) ?>"
                       alt="<?= htmlspecialchars($member['first_name'].' '.$member['last_name']) ?>"
                       class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                  <h5><?= htmlspecialchars($member['first_name'].' '.$member['last_name']) ?></h5>
                  <p class="text-primary fw-bold mb-0"><?= htmlspecialchars($member['role_name']) ?></p>
                </div>
              <?php endforeach; ?>
              <?php foreach ($vice_president as $member): ?>
                <div class="col-md-4 text-center">
                  <img src="<?= htmlspecialchars($member['profile_image']) ?>"
                       alt="<?= htmlspecialchars($member['first_name'].' '.$member['last_name']) ?>"
                       class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                  <h5><?= htmlspecialchars($member['first_name'].' '.$member['last_name']) ?></h5>
                  <p class="text-info fw-bold mb-0"><?= htmlspecialchars($member['role_name']) ?></p>
                </div>
              <?php endforeach; ?>
              <?php foreach ($secretary as $member): ?>
                <div class="col-md-4 text-center">
                  <img src="<?= htmlspecialchars($member['profile_image']) ?>"
                       alt="<?= htmlspecialchars($member['first_name'].' '.$member['last_name']) ?>"
                       class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                  <h5><?= htmlspecialchars($member['first_name'].' '.$member['last_name']) ?></h5>
                  <p class="text-success fw-bold mb-0"><?= htmlspecialchars($member['role_name']) ?></p>
                </div>
              <?php endforeach; ?>
              <?php foreach ($committees as $member): ?>
                <div class="col-md-4 text-center">
                  <img src="<?= htmlspecialchars($member['profile_image']) ?>"
                       alt="<?= htmlspecialchars($member['first_name'].' '.$member['last_name']) ?>"
                       class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                  <h5><?= htmlspecialchars($member['first_name'].' '.$member['last_name']) ?></h5>
                  <p class="text-muted mb-0"><?= htmlspecialchars($member['role_name']) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <style>
        .org-structure .org-card {
          transition: all 0.3s ease;
          min-height: 280px;
          display: flex;
          align-items: center;
          position: relative;
          overflow: hidden;
        }

        .org-structure .org-card::before {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
          opacity: 0;
          transition: opacity 0.3s ease;
        }

        .org-structure .org-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .org-structure .org-card:hover::before {
          opacity: 1;
        }

        .org-structure .org-card:hover img {
          transform: scale(1.1);
        }

        .org-structure .org-card:hover .badge {
          transform: scale(1.1);
        }

        .section-title {
          position: relative;
          display: inline-block;
        }

        .section-title:after {
          content: '';
          position: absolute;
          width: 100%;
          height: 4px;
          background: linear-gradient(90deg, #3498db, #2ecc71);
          bottom: -10px;
          left: 50%;
          border-radius: 2px;
        }

        .badge {
          transition: all 0.3s ease;
        }

        .rounded-circle {
          transition: all 0.3s ease;
        }

        .org-card:hover .rounded-circle {
          transform: scale(1.05);
        }

        .card-body {
          position: relative;
          z-index: 1;
        }
      </style>
    </div>
  </section>

  <!-- Achievement Section -->
  <section class="py-5 bg-light" data-aos="fade-up">
    <div class="container">
      <h2 class="text-center mb-5 section-title">ผลงานและรางวัล</h2>
      <div class="row g-4">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
          <div class="achievement-card">
            <div class="achievement-icon">
              <i class="fas fa-trophy"></i>
            </div>
            <h4>รางวัลองค์กรนักศึกษาดีเด่น</h4>
            <p>ปีการศึกษา 2566</p>
          </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
          <div class="achievement-card">
            <div class="achievement-icon">
              <i class="fas fa-medal"></i>
            </div>
            <h4>รางวัลกิจกรรมสร้างสรรค์</h4>
            <p>ปีการศึกษา 2565</p>
          </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
          <div class="achievement-card">
            <div class="achievement-icon">
              <i class="fas fa-award"></i>
            </div>
            <h4>รางวัลจิตอาสาดีเด่น</h4>
            <p>ปีการศึกษา 2564</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<style>
/* Hero Section */
.hero-section {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
  padding: 6rem 0;
  position: relative;
  overflow: hidden;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="rgba(255,255,255,0.1)"/></svg>');
  opacity: 0.1;
}

.hero-content {
  position: relative;
  z-index: 1;
}

.hero-buttons .btn {
  padding: 0.8rem 2rem;
  border-radius: 50px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.hero-buttons .btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Cards */
.hover-card {
  transition: all 0.3s ease;
  border: none;
  border-radius: 15px;
}

.hover-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
}

.icon-wrapper {
  width: 80px;
  height: 80px;
  background: rgba(52, 152, 219, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  transition: all 0.3s ease;
}

.hover-card:hover .icon-wrapper {
  transform: scale(1.1);
  background: rgba(52, 152, 219, 0.2);
}

/* History Section */
.history-image-wrapper {
  position: relative;
  overflow: hidden;
  border-radius: 15px;
}

.history-image-wrapper img {
  transition: transform 0.5s ease;
}

.history-image-wrapper:hover img {
  transform: scale(1.05);
}

.history-overlay {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(52, 152, 219, 0.9);
  padding: 10px 20px;
  border-radius: 50px;
  color: white;
}

/* Timeline */
.timeline {
  position: relative;
  padding-left: 30px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--primary-color);
}

.timeline-item {
  position: relative;
  margin-bottom: 30px;
}

.timeline-dot {
  position: absolute;
  left: -39px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--primary-color);
  border: 4px solid white;
}

.timeline-content {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.timeline-content h4 {
  color: var(--primary-color);
  margin-bottom: 10px;
}

/* Achievement Section */
.achievement-card {
  background: white;
  padding: 30px;
  border-radius: 15px;
  text-align: center;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.achievement-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.achievement-icon {
  width: 80px;
  height: 80px;
  background: rgba(52, 152, 219, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
}

.achievement-icon i {
  font-size: 2rem;
  color: var(--primary-color);
}

/* Responsive */
@media (max-width: 768px) {
  .hero-section {
    padding: 4rem 0;
  }

  .hero-buttons .btn {
    display: block;
    width: 100%;
    margin: 10px 0;
  }

  .timeline {
    padding-left: 20px;
  }

  .timeline-dot {
    left: -29px;
  }
}
</style>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>