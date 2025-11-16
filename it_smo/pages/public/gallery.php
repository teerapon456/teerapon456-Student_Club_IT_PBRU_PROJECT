<?php
$pageTitle = 'แกลเลอรี่ | IT SMO';
include_once '../../includes/header.php';

// ------------------ ตัวอย่างข้อมูลแกลเลอรี่ (array จำลอง) ------------------
$all_gallery = [
  [
    'img' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=600&q=80',
    'badge' => 'IT Camp',
    'badge_class' => 'bg-primary',
    'title' => 'IT Camp 2023',
    'desc' => 'ค่ายพัฒนาทักษะด้านไอที',
  ],
  [
    'img' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80',
    'badge' => 'กีฬาสี',
    'badge_class' => 'bg-success',
    'title' => 'กีฬาสี IT 2023',
    'desc' => 'การแข่งขันกีฬาสีภายในคณะ',
  ],
  [
    'img' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=600&q=80',
    'badge' => 'กิจกรรมวิชาการ',
    'badge_class' => 'bg-info',
    'title' => 'อบรม Python',
    'desc' => 'กิจกรรมอบรมการเขียนโปรแกรม',
  ],
  [
    'img' => 'https://images.unsplash.com/photo-1520201163981-8cc95007dd2a?auto=format&fit=crop&w=600&q=80',
    'badge' => 'กิจกรรมเพื่อสังคม',
    'badge_class' => 'bg-warning',
    'title' => 'จิตอาสาพัฒนาชุมชน',
    'desc' => 'กิจกรรมเพื่อสังคม',
  ],
  [
    'img' => 'https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=600&q=80',
    'badge' => 'IT Camp',
    'badge_class' => 'bg-primary',
    'title' => 'IT Camp 2022',
    'desc' => 'ค่ายพัฒนาทักษะด้านไอที',
  ],
  [
    'img' => 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?auto=format&fit=crop&w=600&q=80',
    'badge' => 'กีฬาสี',
    'badge_class' => 'bg-success',
    'title' => 'กีฬาสี IT 2022',
    'desc' => 'การแข่งขันกีฬาสีภายในคณะ',
  ],
];
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 6;
$total_gallery = count($all_gallery);
$total_pages = ceil($total_gallery / $per_page);
$offset = ($page - 1) * $per_page;
$gallery = array_slice($all_gallery, $offset, $per_page);
?>

<div class="gallery-page">
  <!-- Hero Section -->
  <div class="hero-section text-center py-5 mb-5">
    <div class="container">
      <div class="hero-content" data-aos="fade-up">
        <h1 class="display-4 fw-bold mb-4">แกลเลอรี่</h1>
        <p class="lead text-white" id="gallery-description">ภาพความทรงจำจากกิจกรรมต่างๆ ของสโมสรนักศึกษา</p>
      </div>
    </div>
  </div>

  <!-- Gallery Categories -->
  <div class="gallery-categories py-4">
    <div class="container">
      <div class="d-flex justify-content-center flex-wrap gap-3">
        <button class="btn btn-primary rounded-pill px-4 py-2 active">
          <i class="fas fa-images me-2"></i>ทั้งหมด
        </button>
        <button class="btn btn-outline-primary rounded-pill px-4 py-2">
          <i class="fas fa-campground me-2"></i>IT Camp
        </button>
        <button class="btn btn-outline-primary rounded-pill px-4 py-2">
          <i class="fas fa-running me-2"></i>กีฬาสี
        </button>
        <button class="btn btn-outline-primary rounded-pill px-4 py-2">
          <i class="fas fa-graduation-cap me-2"></i>กิจกรรมวิชาการ
        </button>
        <button class="btn btn-outline-primary rounded-pill px-4 py-2">
          <i class="fas fa-hands-helping me-2"></i>กิจกรรมเพื่อสังคม
        </button>
      </div>
    </div>
  </div>

  <!-- Gallery Grid -->
  <div class="gallery-grid py-5">
    <div class="container">
      <div class="row g-4">
        <?php foreach ($gallery as $item): ?>
        <div class="col-12 col-md-6 col-xl-3 mb-4">
          <div class="card h-100">
            <img src="<?= htmlspecialchars($item['img']) ?>" class="card-img-top img-fluid" alt="<?= htmlspecialchars($item['title']) ?>">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
              <p class="card-text"><?= htmlspecialchars($item['desc']) ?></p>
              <button class="btn btn-light">
                <i class="fas fa-search-plus me-2"></i>ดูภาพ
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
  <div class="gallery-pagination py-4">
    <div class="container">
      <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
          <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page-1 ?>" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item<?= $i == $page ? ' active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item<?= $page >= $total_pages ? ' disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page+1 ?>" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
  <?php endif; ?>
</div>

<style>
/* Page Layout */
.gallery-page {
  background: var(--light-bg);
  min-height: 100vh;
}

/* Hero Section */
.hero-section {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
  color: white;
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
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="none"/><path d="M0,0 L100,100 M100,0 L0,100" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></svg>');
  opacity: 0.1;
}

.hero-content {
  position: relative;
  z-index: 1;
}

.hero-content h1 {
  font-size: 3.5rem;
  margin-bottom: 1rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  color: #ffffff;
}

.hero-content p {
  color: rgba(255, 255, 255, 0.9);
}

/* Gallery Categories */
.gallery-categories {
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.btn-primary {
  background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
  border: none;
}

.btn-outline-primary {
  color: var(--primary-color);
  border-color: var(--primary-color);
}

.btn-outline-primary:hover {
  background: var(--primary-color);
  color: white;
}

/* Gallery Grid */
.gallery-grid {
  background: var(--light-bg);
}

.gallery-card {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.gallery-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
  border-color: var(--primary-color);
}

.gallery-image {
  position: relative;
  padding-top: 75%;
  overflow: hidden;
}

.gallery-image img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.gallery-card:hover .gallery-image img {
  transform: scale(1.1);
}

.gallery-info {
  padding: 1.5rem;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.gallery-info .badge {
  align-self: flex-start;
  margin-bottom: 1rem;
  font-size: 0.8rem;
  padding: 0.5rem 1rem;
}

.badge.bg-primary {
  background: var(--primary-color) !important;
}

.badge.bg-success {
  background: var(--secondary-color) !important;
}

.badge.bg-info {
  background: var(--accent-color) !important;
}

.badge.bg-warning {
  background: #ffc107 !important;
}

.gallery-info h3 {
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
  color: var(--primary-color);
}

.gallery-info p {
  color: var(--text-color);
  margin-bottom: 1rem;
  flex-grow: 1;
}

.gallery-info .btn {
  align-self: flex-start;
  padding: 0.5rem 1.5rem;
  border-radius: 2rem;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
  color: white;
  border: none;
}

.gallery-info .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
}

/* Pagination */
.gallery-pagination {
  background: white;
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.05);
}

.pagination {
  margin: 0;
}

.page-link {
  border: none;
  padding: 0.5rem 1rem;
  margin: 0 0.25rem;
  border-radius: 0.5rem;
  color: var(--primary-color);
  transition: all 0.3s ease;
}

.page-link:hover {
  background-color: var(--light-bg);
  color: var(--primary-color);
}

.page-item.active .page-link {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
  color: white;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .hero-content h1 {
    font-size: 2.5rem;
  }

  .gallery-categories {
    padding: 1rem 0;
  }

  .gallery-info {
    padding: 1rem;
  }

  .gallery-description {
    font-size: 1.2rem;
    color: white;
  }
}
</style>

<?php include_once '../../includes/footer.php'; ?>