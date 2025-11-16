<?php
$pageTitle = 'นโยบายความเป็นส่วนตัว | IT SMO';
include_once '../../includes/header.php';
?>

<div class="privacy-policy-page py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow-sm">
          <div class="card-body p-4 p-md-5">
            <h1 class="text-center mb-4">นโยบายความเป็นส่วนตัว</h1>
            <p class="text-muted text-center mb-5">อัปเดตล่าสุด: <?= date('d/m/Y') ?></p>

            <div class="content">
              <section class="mb-5">
                <h2 class="h4 mb-3">1. ข้อมูลที่เรารวบรวม</h2>
                <p>เว็บไซต์ IT SMO อาจรวบรวมข้อมูลส่วนบุคคลของคุณ ดังนี้:</p>
                <ul>
                  <li>ข้อมูลการลงทะเบียน (ชื่อ, นามสกุล, รหัสนักศึกษา, อีเมล)</li>
                  <li>ข้อมูลการใช้งานเว็บไซต์ (IP Address, Cookies, ประวัติการเข้าชม)</li>
                  <li>ข้อมูลการติดต่อ (เบอร์โทรศัพท์, ที่อยู่)</li>
                </ul>
              </section>

              <section class="mb-5">
                <h2 class="h4 mb-3">2. การใช้ข้อมูล</h2>
                <p>เราใช้ข้อมูลที่รวบรวมเพื่อ:</p>
                <ul>
                  <li>ให้บริการและปรับปรุงเว็บไซต์</li>
                  <li>จัดการกิจกรรมและโครงการของสโมสร</li>
                  <li>ส่งข้อมูลข่าวสารที่เกี่ยวข้อง</li>
                  <li>วิเคราะห์และพัฒนาประสิทธิภาพของเว็บไซต์</li>
                </ul>
              </section>

              <section class="mb-5">
                <h2 class="h4 mb-3">3. การเปิดเผยข้อมูล</h2>
                <p>เราจะไม่เปิดเผยข้อมูลส่วนบุคคลของคุณให้กับบุคคลที่สาม ยกเว้นในกรณีที่:</p>
                <ul>
                  <li>ได้รับความยินยอมจากคุณ</li>
                  <li>เป็นไปตามกฎหมายหรือคำสั่งศาล</li>
                  <li>เพื่อปกป้องสิทธิ์และความปลอดภัยของเว็บไซต์</li>
                </ul>
              </section>

              <section class="mb-5">
                <h2 class="h4 mb-3">4. ความปลอดภัยของข้อมูล</h2>
                <p>เราใช้มาตรการความปลอดภัยที่เหมาะสมเพื่อปกป้องข้อมูลส่วนบุคคลของคุณจากการเข้าถึงโดยไม่ได้รับอนุญาต การเปิดเผย การเปลี่ยนแปลง หรือการทำลาย</p>
              </section>

              <section class="mb-5">
                <h2 class="h4 mb-3">5. สิทธิ์ของคุณ</h2>
                <p>คุณมีสิทธิ์:</p>
                <ul>
                  <li>เข้าถึงและแก้ไขข้อมูลส่วนบุคคลของคุณ</li>
                  <li>ขอให้ลบข้อมูลส่วนบุคคลของคุณ</li>
                  <li>ยกเลิกการรับข้อมูลข่าวสาร</li>
                  <li>ร้องเรียนเกี่ยวกับการจัดการข้อมูลส่วนบุคคล</li>
                </ul>
              </section>

              <section class="mb-5">
                <h2 class="h4 mb-3">6. การเปลี่ยนแปลงนโยบาย</h2>
                <p>เราอาจปรับปรุงนโยบายความเป็นส่วนตัวนี้เป็นครั้งคราว โดยจะประกาศการเปลี่ยนแปลงบนเว็บไซต์นี้</p>
              </section>

              <section class="mb-5">
                <h2 class="h4 mb-3">7. การติดต่อ</h2>
                <p>หากคุณมีคำถามหรือข้อกังวลเกี่ยวกับนโยบายความเป็นส่วนตัวนี้ กรุณาติดต่อเราได้ที่:</p>
                <ul class="list-unstyled">
                  <li><i class="fas fa-envelope me-2"></i> info@it-smo.com</li>
                  <li><i class="fas fa-phone me-2"></i> 02-123-4567</li>
                </ul>
              </section>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .privacy-policy-page {
    background: var(--light-bg);
    min-height: 100vh;
  }

  .privacy-policy-page .card {
    border: none;
    border-radius: 1rem;
  }

  .privacy-policy-page h1 {
    color: var(--primary-color);
    font-weight: 600;
  }

  .privacy-policy-page h2 {
    color: var(--secondary-color);
    font-weight: 600;
  }

  .privacy-policy-page ul {
    padding-left: 1.5rem;
  }

  .privacy-policy-page li {
    margin-bottom: 0.5rem;
    color: var(--text-color);
  }

  .privacy-policy-page .list-unstyled li {
    margin-bottom: 1rem;
  }

  .privacy-policy-page .text-muted {
    color: var(--text-muted) !important;
  }

  @media (max-width: 768px) {
    .privacy-policy-page .card-body {
      padding: 2rem;
    }
  }
</style>

<?php include_once '../../includes/footer.php'; ?>