<?php
$pageTitle = 'ข่าวสาร | IT SMO';
include_once '../../includes/header.php';

// ------------------ ตัวอย่างข้อมูลข่าวสาร (array จำลอง) ------------------
$all_news = [
  [
    'img' => 'https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=600&q=80',
    'badge' => 'กิจกรรม',
    'badge_class' => 'bg-primary',
    'date' => '12 มีนาคม 2566',
    'title' => 'เปิดรับสมัคร IT Camp 2023',
    'desc' => 'สโมสรนักศึกษาคณะเทคโนโลยีสารสนเทศ เปิดรับสมัครนักศึกษาใหม่เข้าร่วมกิจกรรม IT Camp 2023',
  ],
  [
    'img' => 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?auto=format&fit=crop&w=600&q=80',
    'badge' => 'ประกาศ',
    'badge_class' => 'bg-success',
    'date' => '10 มีนาคม 2566',
    'title' => 'ประกาศผลการเลือกตั้งสโมสรนักศึกษา',
    'desc' => 'ประกาศผลการเลือกตั้งคณะกรรมการสโมสรนักศึกษาประจำปีการศึกษา 2566',
  ],
  [
    'img' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=600&q=80',
    'badge' => 'ประชาสัมพันธ์',
    'badge_class' => 'bg-info',
    'date' => '8 มีนาคม 2566',
    'title' => 'กิจกรรมจิตอาสาพัฒนาชุมชน',
    'desc' => 'สโมสรนักศึกษาจัดกิจกรรมจิตอาสาพัฒนาชุมชน ณ โรงเรียนบ้านหนองบัว',
  ],
  [
    'img' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=600&q=80',
    'badge' => 'กิจกรรม',
    'badge_class' => 'bg-warning',
    'date' => '5 มีนาคม 2566',
    'title' => 'อบรมการเขียนโปรแกรม Python',
    'desc' => 'เปิดอบรมการเขียนโปรแกรม Python สำหรับนักศึกษาชั้นปีที่ 1',
  ],
  [
    'img' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80',
    'badge' => 'ประกาศ',
    'badge_class' => 'bg-danger',
    'date' => '3 มีนาคม 2566',
    'title' => 'ประกาศปิดรับสมัครกิจกรรม',
    'desc' => 'ประกาศปิดรับสมัครกิจกรรม IT Camp 2023 เนื่องจากมีผู้สมัครครบตามจำนวนแล้ว',
  ],
  [
    'img' => 'https://images.unsplash.com/photo-1520201163981-8cc95007dd2a?auto=format&fit=crop&w=600&q=80',
    'badge' => 'ประชาสัมพันธ์',
    'badge_class' => 'bg-secondary',
    'date' => '1 มีนาคม 2566',
    'title' => 'กิจกรรมกีฬาสีประจำปี',
    'desc' => 'เตรียมพบกับกิจกรรมกีฬาสีประจำปีของคณะเทคโนโลยีสารสนเทศ',
  ],
];
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 6;
$total_news = count($all_news);
$total_pages = ceil($total_news / $per_page);
$offset = ($page - 1) * $per_page;
$news = array_slice($all_news, $offset, $per_page);
?>

<div class="container py-5">
  <!-- Hero Section -->
  <div class="hero-section text-center mb-5" data-aos="fade-up">
    <h1 class="display-4 mb-3">ข่าวสารและประกาศ</h1>
    <p class="lead">ติดตามข่าวสารและกิจกรรมล่าสุดของสโมสรนักศึกษา</p>
  </div>

  <!-- News Grid -->
  <div class="row g-4">
    <?php foreach ($news as $item): ?>
    <div class="col-md-6 col-lg-4" data-aos="fade-up">
      <div class="card h-100 shadow-sm" style="border-radius: 15px; overflow: hidden;">
        <div class="position-relative">
          <img src="<?= htmlspecialchars($item['img']) ?>"
            class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>" style="height: 200px; object-fit: cover;">
          <div class="position-absolute top-0 end-0 m-3">
            <span class="badge <?= $item['badge_class'] ?> rounded-pill px-3 py-2"><?= htmlspecialchars($item['badge']) ?></span>
          </div>
        </div>
        <div class="card-body">
          <small class="text-muted d-block mb-2"><?= htmlspecialchars($item['date']) ?></small>
          <h5 class="card-title mb-3"><?= htmlspecialchars($item['title']) ?></h5>
          <p class="card-text mb-4"><?= htmlspecialchars($item['desc']) ?></p>
          <a href="#" class="btn btn-primary rounded-pill px-4">อ่านเพิ่มเติม</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
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
</div>

<?php include_once '../../includes/footer.php'; ?>