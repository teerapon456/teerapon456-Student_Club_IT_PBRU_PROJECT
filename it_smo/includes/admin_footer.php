            </div> <!-- End content-container -->
            </main> <!-- End main-content -->

            <!-- Footer -->
            <footer class="footer">
              <div class="container">
                <div class="footer-content">
                  <p class="footer-text">ระบบจัดการสโมสรนักศึกษา คณะเทคโนโลยีสารสนเทศ มหาวิทยาลัยราชภัฏเพชรบุรี</p>
                  <p class="footer-copyright">&copy; <?php echo date('Y'); ?> IT Student Club Management System. All
                    rights
                    reserved.</p>
                </div>
              </div>
            </footer>
            </div> <!-- End wrapper -->

            <!-- Core Scripts -->
            <script>
// Preloader
window.addEventListener('load', function() {
  const preloader = document.getElementById('preloader');
  if (preloader) {
    setTimeout(() => {
      preloader.style.opacity = '0';
      setTimeout(() => {
        preloader.style.display = 'none';
      }, 300);
    }, 500);
  }
});

// สลับแถบด้านข้าง
document.querySelector('.sidebar-toggle')?.addEventListener('click', function() {
  document.querySelector('.sidebar').classList.toggle('show');
});

// ลิงค์ที่ใช้งานอยู่
document.addEventListener('DOMContentLoaded', function() {
  const currentPath = window.location.pathname;
  const sidebarLinks = document.querySelectorAll('.sidebar-link');

  sidebarLinks.forEach(link => {
    if (currentPath === new URL(link.href).pathname) {
      link.classList.add('active');
    }
  });
});

// เคล็ดลับเครื่องมือ
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function(tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl, {
    trigger: 'hover'
  });
});

// แถบด้านข้างที่ตอบสนอง
function handleSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const mainContent = document.querySelector('.main-content');

  if (window.innerWidth < 992) {
    sidebar.classList.remove('show');
    mainContent.style.marginLeft = '0';
  } else {
    sidebar.classList.add('show');
    mainContent.style.marginLeft = '280px';
  }
}

window.addEventListener('resize', handleSidebar);
window.addEventListener('load', handleSidebar);
            </script>

            <?php if (isset($pageScripts)): ?>
            <?php foreach ($pageScripts as $script): ?>
            <script src="<?php echo $script; ?>" defer></script>
            <?php endforeach; ?>
            <?php endif; ?>
            </body>

            </html>