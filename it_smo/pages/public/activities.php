<?php $pageTitle = "เกี่ยวกับเรา | IT SMO";
include_once __DIR__ . '/../../includes/header.php';

// ------------------ ตัวอย่างข้อมูลกิจกรรม (array จำลอง) ------------------
$all_activities = [
  [
    'title' => 'IT Open House 2024',
    'desc' => 'งานเปิดบ้านคณะ IT พบกับนิทรรศการและผลงานของนักศึกษา พร้อมกิจกรรมเวิร์กช็อปสุดพิเศษ',
    'date' => '15 มี.ค. 2567',
    'img' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80',
    'badge' => 'เร็วๆ นี้',
    'badge_class' => 'bg-warning text-dark',
  ],
  [
    'title' => 'IT Sport Day',
    'desc' => 'การแข่งขันกีฬาสีภายในคณะเทคโนโลยีสารสนเทศ สร้างความสามัคคีและสุขภาพดี',
    'date' => '20 มี.ค. 2567',
    'img' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80',
    'badge' => 'เร็วๆ นี้',
    'badge_class' => 'bg-warning text-dark',
  ],
  [
    'title' => 'IT Camp 2023',
    'desc' => 'ค่ายพัฒนาทักษะด้านไอทีสำหรับนักศึกษาปี 1 พบกับกิจกรรมสร้างสรรค์และเพื่อนใหม่',
    'date' => '10 ธ.ค. 2566',
    'img' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
    'badge' => 'ค่าย',
    'badge_class' => 'bg-info',
  ],
  [
    'title' => 'IT Contest 2023',
    'desc' => 'เวทีแสดงผลงานและความสามารถของนักศึกษา IT ชิงรางวัลและโอกาสในการพัฒนาตนเอง',
    'date' => '5 พ.ย. 2566',
    'img' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80',
    'badge' => 'ประกวด',
    'badge_class' => 'bg-success',
  ],
  [
    'title' => 'กิจกรรมรับน้องใหม่ 2566',
    'desc' => 'สร้างความประทับใจและมิตรภาพใหม่ ๆ ให้กับนักศึกษาใหม่',
    'date' => '1 ก.ค. 2566',
    'img' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80',
    'badge' => 'กิจกรรมรับน้อง',
    'badge_class' => 'bg-primary',
  ],
  [
    'title' => 'อบรม Python สำหรับมือใหม่',
    'desc' => 'เรียนรู้พื้นฐานการเขียนโปรแกรม Python ตั้งแต่เริ่มต้นจนถึงการสร้างโปรเจคจริง',
    'date' => '15 ส.ค. 2566',
    'img' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80',
    'badge' => 'อบรม',
    'badge_class' => 'bg-warning',
  ],
  [
    'title' => 'IT Hackathon 2023',
    'desc' => 'การแข่งขันเขียนโปรแกรม 24 ชั่วโมง เพื่อสร้างนวัตกรรมใหม่ๆ ด้านเทคโนโลยี',
    'date' => '20 ก.ย. 2566',
    'img' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
    'badge' => 'Hackathon',
    'badge_class' => 'bg-danger',
  ],
  [
    'title' => 'IT จิตอาสาพัฒนาชุมชน',
    'desc' => 'กิจกรรมจิตอาสาพัฒนาชุมชนและถ่ายทอดความรู้ด้านเทคโนโลยีให้กับชุมชน',
    'date' => '5 ต.ค. 2566',
    'img' => 'https://images.unsplash.com/photo-1520201163981-8cc95007dd2a?auto=format&fit=crop&w=800&q=80',
    'badge' => 'จิตอาสา',
    'badge_class' => 'bg-secondary',
  ],
];
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 6;
$total_activities = count($all_activities);
$total_pages = ceil($total_activities / $per_page);
$offset = ($page - 1) * $per_page;
$activities = array_slice($all_activities, $offset, $per_page);
?>
<main>
  <!-- Hero Section -->
  <section class="hero-section text-center mb-5" data-aos="fade-up">
    <div class="container">
      <h1 class="display-4 mb-3">กิจกรรมสโมสรนักศึกษา IT</h1>
      <p class="lead">รวมกิจกรรมสร้างสรรค์ พัฒนาทักษะ และประสบการณ์ดี ๆ สำหรับนักศึกษา IT</p>
    </div>
  </section>

  <!-- Upcoming Activities -->
  <section class="py-5" data-aos="fade-up">
    <div class="container">
      <h2 class="section-title mb-4"><i class="fas fa-calendar-alt me-2"></i>กิจกรรมที่กำลังจะมาถึง</h2>
      <div class="row g-4">
        <?php foreach ($activities as $activity): ?>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
          <div class="card h-100 shadow-sm"
            style="border-radius: 20px; overflow: hidden; transition: transform 0.3s ease;">
            <div class="position-relative">
              <img src="<?= htmlspecialchars($activity['img']) ?>"
                class="card-img-top img-fluid" alt="<?= htmlspecialchars($activity['title']) ?>" style="height: 240px; object-fit: cover;">
              <div class="position-absolute top-0 end-0 m-3">
                <span class="badge <?= $activity['badge_class'] ?> rounded-pill px-4 py-2" style="font-size: 0.9rem;">
                  <?= htmlspecialchars($activity['badge']) ?>
                </span>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="card-title mb-3" style="font-size: 1.25rem;"><?= htmlspecialchars($activity['title']) ?></h5>
              <p class="card-text mb-4" style="font-size: 1rem; line-height: 1.6;"><?= htmlspecialchars($activity['desc']) ?></p>
              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" style="font-size: 0.9rem;"><i class="fas fa-clock me-1"></i><?= htmlspecialchars($activity['date']) ?></small>
                <a href="#" class="btn btn-primary rounded-pill px-4 py-2" style="font-size: 0.95rem;"><i
                    class="fas fa-info-circle me-1"></i>รายละเอียด</a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Highlight Activities -->
  <section class="py-5 bg-light" data-aos="fade-up">
    <div class="container">
      <h2 class="section-title mb-4"><i class="fas fa-star me-2"></i>กิจกรรมเด่นที่ผ่านมา</h2>
      <div class="row g-4">
        <div class="col-12 col-md-6 col-xl-4 mb-4">
          <div class="card h-100 shadow-sm"
            style="border-radius: 20px; overflow: hidden; transition: transform 0.3s ease;">
            <div class="position-relative">
              <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80"
                class="card-img-top img-fluid" alt="IT Camp" style="height: 240px; object-fit: cover;">
              <div class="position-absolute top-0 end-0 m-3">
                <span class="badge bg-info rounded-pill px-4 py-2" style="font-size: 0.9rem;">ค่าย</span>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="card-title mb-3" style="font-size: 1.25rem;">IT Camp 2023</h5>
              <p class="card-text mb-4" style="font-size: 1rem; line-height: 1.6;">
                ค่ายพัฒนาทักษะด้านไอทีสำหรับนักศึกษาปี 1 พบกับกิจกรรมสร้างสรรค์และเพื่อนใหม่</p>
              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" style="font-size: 0.9rem;"><i class="fas fa-calendar me-1"></i>10 ธ.ค.
                  2566</small>
                <a href="#" class="btn btn-outline-primary rounded-pill px-4 py-2"
                  style="font-size: 0.95rem;">ดูภาพกิจกรรม</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
          <div class="card h-100 shadow-sm"
            style="border-radius: 20px; overflow: hidden; transition: transform 0.3s ease;">
            <div class="position-relative">
              <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80"
                class="card-img-top img-fluid" alt="IT Contest" style="height: 240px; object-fit: cover;">
              <div class="position-absolute top-0 end-0 m-3">
                <span class="badge bg-success rounded-pill px-4 py-2" style="font-size: 0.9rem;">ประกวด</span>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="card-title mb-3" style="font-size: 1.25rem;">IT Contest 2023</h5>
              <p class="card-text mb-4" style="font-size: 1rem; line-height: 1.6;">เวทีแสดงผลงานและความสามารถของนักศึกษา
                IT ชิงรางวัลและโอกาสในการพัฒนาตนเอง</p>
              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" style="font-size: 0.9rem;"><i class="fas fa-calendar me-1"></i>5 พ.ย.
                  2566</small>
                <a href="#" class="btn btn-outline-primary rounded-pill px-4 py-2"
                  style="font-size: 0.95rem;">ดูรายละเอียด</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
          <div class="card h-100 shadow-sm"
            style="border-radius: 20px; overflow: hidden; transition: transform 0.3s ease;">
            <div class="position-relative">
              <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80"
                class="card-img-top img-fluid" alt="รับน้องใหม่" style="height: 240px; object-fit: cover;">
              <div class="position-absolute top-0 end-0 m-3">
                <span class="badge bg-primary rounded-pill px-4 py-2" style="font-size: 0.9rem;">กิจกรรมรับน้อง</span>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="card-title mb-3" style="font-size: 1.25rem;">กิจกรรมรับน้องใหม่ 2566</h5>
              <p class="card-text mb-4" style="font-size: 1rem; line-height: 1.6;">สร้างความประทับใจและมิตรภาพใหม่ ๆ
                ให้กับนักศึกษาใหม่</p>
              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" style="font-size: 0.9rem;"><i class="fas fa-calendar me-1"></i>1 ก.ค.
                  2566</small>
                <a href="#" class="btn btn-outline-primary rounded-pill px-4 py-2"
                  style="font-size: 0.95rem;">ดูภาพกิจกรรม</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
          <div class="card h-100 shadow-sm"
            style="border-radius: 20px; overflow: hidden; transition: transform 0.3s ease;">
            <div class="position-relative">
              <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80"
                class="card-img-top img-fluid" alt="อบรม Python" style="height: 240px; object-fit: cover;">
              <div class="position-absolute top-0 end-0 m-3">
                <span class="badge bg-warning rounded-pill px-4 py-2" style="font-size: 0.9rem;">อบรม</span>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="card-title mb-3" style="font-size: 1.25rem;">อบรม Python สำหรับมือใหม่</h5>
              <p class="card-text mb-4" style="font-size: 1rem; line-height: 1.6;">เรียนรู้พื้นฐานการเขียนโปรแกรม Python
                ตั้งแต่เริ่มต้นจนถึงการสร้างโปรเจคจริง</p>
              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" style="font-size: 0.9rem;"><i class="fas fa-calendar me-1"></i>15 ส.ค.
                  2566</small>
                <a href="#" class="btn btn-outline-primary rounded-pill px-4 py-2"
                  style="font-size: 0.95rem;">ดูภาพกิจกรรม</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
          <div class="card h-100 shadow-sm"
            style="border-radius: 20px; overflow: hidden; transition: transform 0.3s ease;">
            <div class="position-relative">
              <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80"
                class="card-img-top img-fluid" alt="Hackathon" style="height: 240px; object-fit: cover;">
              <div class="position-absolute top-0 end-0 m-3">
                <span class="badge bg-danger rounded-pill px-4 py-2" style="font-size: 0.9rem;">Hackathon</span>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="card-title mb-3" style="font-size: 1.25rem;">IT Hackathon 2023</h5>
              <p class="card-text mb-4" style="font-size: 1rem; line-height: 1.6;">การแข่งขันเขียนโปรแกรม 24 ชั่วโมง
                เพื่อสร้างนวัตกรรมใหม่ๆ ด้านเทคโนโลยี</p>
              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" style="font-size: 0.9rem;"><i class="fas fa-calendar me-1"></i>20 ก.ย.
                  2566</small>
                <a href="#" class="btn btn-outline-primary rounded-pill px-4 py-2"
                  style="font-size: 0.95rem;">ดูภาพกิจกรรม</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
          <div class="card h-100 shadow-sm"
            style="border-radius: 20px; overflow: hidden; transition: transform 0.3s ease;">
            <div class="position-relative">
              <img src="https://images.unsplash.com/photo-1520201163981-8cc95007dd2a?auto=format&fit=crop&w=800&q=80"
                class="card-img-top img-fluid" alt="จิตอาสา" style="height: 240px; object-fit: cover;">
              <div class="position-absolute top-0 end-0 m-3">
                <span class="badge bg-secondary rounded-pill px-4 py-2" style="font-size: 0.9rem;">จิตอาสา</span>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="card-title mb-3" style="font-size: 1.25rem;">IT จิตอาสาพัฒนาชุมชน</h5>
              <p class="card-text mb-4" style="font-size: 1rem; line-height: 1.6;">
                กิจกรรมจิตอาสาพัฒนาชุมชนและถ่ายทอดความรู้ด้านเทคโนโลยีให้กับชุมชน</p>
              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" style="font-size: 0.9rem;"><i class="fas fa-calendar me-1"></i>5 ต.ค.
                  2566</small>
                <a href="#" class="btn btn-outline-primary rounded-pill px-4 py-2"
                  style="font-size: 0.95rem;">ดูภาพกิจกรรม</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section class="py-5 text-center" data-aos="fade-up">
    <div class="container">
      <h2 class="mb-4">อยากร่วมกิจกรรมกับเรา?</h2>
      <p class="lead mb-4">ติดตามข่าวสารและกิจกรรมใหม่ ๆ ได้ที่หน้าเว็บไซต์ หรือสอบถามข้อมูลเพิ่มเติมได้ที่สโมสรนักศึกษา
        IT</p>
      <a href="contact.php" class="btn btn-lg btn-primary"><i class="fas fa-envelope me-2"></i>ติดต่อสโมสร</a>
    </div>
  </section>
</main>

<?php if ($total_pages > 1): ?>
<nav aria-label="Page navigation" class="mt-5">
  <ul class="pagination justify-content-center">
    <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
      <a class="page-link" href="?page=<?= $page-1 ?>">Previous</a>
    </li>
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <li class="page-item<?= $i == $page ? ' active' : '' ?>">
        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item<?= $page >= $total_pages ? ' disabled' : '' ?>">
      <a class="page-link" href="?page=<?= $page+1 ?>">Next</a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>