/**
 * UX Enhancements for IT SMO System
 * Enhanced user experience with loading states, animations, and feedback
 */

class UXEnhancements {
  constructor() {
    this.init();
  }

  init() {
    this.setupLoadingStates();
    this.setupFormValidation();
    this.setupAnimations();
    this.setupNotifications();
    this.setupProgressBars();
    this.setupSkeletonLoaders();
  }

  // Loading States
  setupLoadingStates() {
    // Button loading states
    document.addEventListener('click', (e) => {
      if (e.target.matches('.btn[data-loading]')) {
        this.showButtonLoading(e.target);
      }
    });

    // Form submission loading
    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (form.classList.contains('needs-validation')) {
        this.showFormLoading(form);
      }
    });

    // AJAX loading states
    this.setupAjaxLoading();
  }

  showButtonLoading(button) {
    const originalText = button.innerHTML;
    const loadingText = button.dataset.loadingText || 'กำลังประมวลผล...';

    button.disabled = true;
    button.classList.add('loading');
    button.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            ${loadingText}
        `;

    // Auto-disable loading after 10 seconds
    setTimeout(() => {
      this.hideButtonLoading(button, originalText);
    }, 10000);
  }

  hideButtonLoading(button, originalText) {
    button.disabled = false;
    button.classList.remove('loading');
    button.innerHTML = originalText;
  }

  showFormLoading(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      this.showButtonLoading(submitBtn);
    }

    // Disable all form inputs
    const inputs = form.querySelectorAll('input, select, textarea, button');
    inputs.forEach(input => {
      input.disabled = true;
    });

    // Add loading overlay
    const overlay = document.createElement('div');
    overlay.className = 'form-loading-overlay';
    overlay.innerHTML = `
            <div class="loading-content">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">กำลังโหลด...</span>
                </div>
                <p class="text-muted">กำลังประมวลผลข้อมูล...</p>
            </div>
        `;
    form.style.position = 'relative';
    form.appendChild(overlay);
  }

  hideFormLoading(form) {
    const overlay = form.querySelector('.form-loading-overlay');
    if (overlay) {
      overlay.remove();
    }

    // Re-enable all form inputs
    const inputs = form.querySelectorAll('input, select, textarea, button');
    inputs.forEach(input => {
      input.disabled = false;
    });
  }

  setupAjaxLoading() {
    // Intercept fetch requests
    const originalFetch = window.fetch;
    window.fetch = async (...args) => {
      this.showGlobalLoading();
      try {
        const response = await originalFetch(...args);
        this.hideGlobalLoading();
        return response;
      } catch (error) {
        this.hideGlobalLoading();
        throw error;
      }
    };
  }

  showGlobalLoading() {
    if (document.querySelector('.global-loading')) return;

    const loading = document.createElement('div');
    loading.className = 'global-loading';
    loading.innerHTML = `
            <div class="loading-backdrop"></div>
            <div class="loading-content">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">กำลังโหลด...</span>
                </div>
                <p class="text-white">กำลังโหลดข้อมูล...</p>
            </div>
        `;
    document.body.appendChild(loading);
  }

  hideGlobalLoading() {
    const loading = document.querySelector('.global-loading');
    if (loading) {
      loading.remove();
    }
  }

  // Form Validation
  setupFormValidation() {
    // Real-time validation
    document.addEventListener('input', (e) => {
      if (e.target.matches('.form-control[data-validate]')) {
        this.validateField(e.target);
      }
    });

    // Form submission validation
    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (form.classList.contains('needs-validation')) {
        if (!this.validateForm(form)) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      }
    });
  }

  validateField(field) {
    const value = field.value.trim();
    const type = field.dataset.validate;
    let isValid = true;
    let message = '';

    // Remove previous validation
    field.classList.remove('is-valid', 'is-invalid');
    this.removeFieldFeedback(field);

    // Required validation
    if (field.hasAttribute('required') && !value) {
      isValid = false;
      message = 'กรุณากรอกข้อมูลนี้';
    }

    // Type-specific validation
    if (value && isValid) {
      switch (type) {
        case 'email':
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(value)) {
            isValid = false;
            message = 'กรุณากรอกอีเมลที่ถูกต้อง';
          }
          break;
        case 'phone':
          const phoneRegex = /^[0-9]{10}$/;
          if (!phoneRegex.test(value)) {
            isValid = false;
            message = 'กรุณากรอกเบอร์โทรศัพท์ 10 หลัก';
          }
          break;
        case 'student-id':
          const studentIdRegex = /^[0-9]{9}$/;
          if (!studentIdRegex.test(value)) {
            isValid = false;
            message = 'กรุณากรอกรหัสนักศึกษา 9 หลัก';
          }
          break;
        case 'password':
          if (value.length < 8) {
            isValid = false;
            message = 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร';
          }
          break;
      }
    }

    // Show validation result
    if (isValid) {
      field.classList.add('is-valid');
      this.showFieldFeedback(field, message, 'valid');
    } else {
      field.classList.add('is-invalid');
      this.showFieldFeedback(field, message, 'invalid');
    }

    return isValid;
  }

  validateForm(form) {
    const fields = form.querySelectorAll('.form-control[data-validate]');
    let isFormValid = true;

    fields.forEach(field => {
      if (!this.validateField(field)) {
        isFormValid = false;
      }
    });

    return isFormValid;
  }

  showFieldFeedback(field, message, type) {
    this.removeFieldFeedback(field);

    const feedback = document.createElement('div');
    feedback.className = `${type}-feedback`;
    feedback.textContent = message;
    field.parentNode.appendChild(feedback);
  }

  removeFieldFeedback(field) {
    const existingFeedback = field.parentNode.querySelector('.valid-feedback, .invalid-feedback');
    if (existingFeedback) {
      existingFeedback.remove();
    }
  }

  // Animations
  setupAnimations() {
    // Scroll animations
    this.setupScrollAnimations();

    // Hover animations
    this.setupHoverAnimations();

    // Page transitions
    this.setupPageTransitions();
  }

  setupScrollAnimations() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in');
        }
      });
    }, observerOptions);

    // Observe elements with animation classes
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
      observer.observe(el);
    });
  }

  setupHoverAnimations() {
    // Card hover effects
    document.querySelectorAll('.card').forEach(card => {
      card.addEventListener('mouseenter', () => {
        card.style.transform = 'translateY(-5px)';
        card.style.transition = 'transform 0.3s ease';
      });

      card.addEventListener('mouseleave', () => {
        card.style.transform = 'translateY(0)';
      });
    });

    // Button hover effects
    document.querySelectorAll('.btn').forEach(btn => {
      btn.addEventListener('mouseenter', () => {
        if (!btn.disabled) {
          btn.style.transform = 'translateY(-2px)';
          btn.style.transition = 'transform 0.3s ease';
        }
      });

      btn.addEventListener('mouseleave', () => {
        btn.style.transform = 'translateY(0)';
      });
    });
  }

  setupPageTransitions() {
    // Add transition class to body
    document.body.classList.add('page-transition');

    // Handle page navigation
    document.querySelectorAll('a[href]').forEach(link => {
      link.addEventListener('click', (e) => {
        // Only for internal links
        if (link.hostname === window.location.hostname) {
          e.preventDefault();
          this.navigateToPage(link.href);
        }
      });
    });
  }

  navigateToPage(url) {
    // Show loading
    this.showGlobalLoading();

    // Navigate after short delay
    setTimeout(() => {
      window.location.href = url;
    }, 300);
  }

  // Notifications
  setupNotifications() {
    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(alert => {
      if (alert.dataset.autoDismiss !== 'false') {
        setTimeout(() => {
          this.dismissAlert(alert);
        }, 5000);
      }
    });
  }

  dismissAlert(alert) {
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-20px)';
    alert.style.transition = 'all 0.3s ease';

    setTimeout(() => {
      alert.remove();
    }, 300);
  }

  showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show notification`;
    notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    // Add to page
    const container = document.querySelector('.notifications-container') || this.createNotificationContainer();
    container.appendChild(notification);

    // Auto-dismiss
    if (duration > 0) {
      setTimeout(() => {
        this.dismissAlert(notification);
      }, duration);
    }
  }

  createNotificationContainer() {
    const container = document.createElement('div');
    container.className = 'notifications-container';
    container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
    document.body.appendChild(container);
    return container;
  }

  // Progress Bars
  setupProgressBars() {
    // Animate progress bars on scroll
    const progressBars = document.querySelectorAll('.progress-bar');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          this.animateProgressBar(entry.target);
        }
      });
    });

    progressBars.forEach(bar => {
      observer.observe(bar);
    });
  }

  animateProgressBar(bar) {
    const targetWidth = bar.dataset.width || bar.style.width;
    bar.style.width = '0%';
    bar.style.transition = 'width 1s ease-in-out';

    setTimeout(() => {
      bar.style.width = targetWidth;
    }, 100);
  }

  // Skeleton Loaders
  setupSkeletonLoaders() {
    // Show skeleton while loading content
    document.querySelectorAll('[data-skeleton]').forEach(element => {
      this.showSkeleton(element);
    });
  }

  showSkeleton(element) {
    const skeleton = document.createElement('div');
    skeleton.className = 'skeleton-loader';
    skeleton.innerHTML = `
            <div class="skeleton-line"></div>
            <div class="skeleton-line"></div>
            <div class="skeleton-line short"></div>
        `;

    element.style.position = 'relative';
    element.appendChild(skeleton);
  }

  hideSkeleton(element) {
    const skeleton = element.querySelector('.skeleton-loader');
    if (skeleton) {
      skeleton.remove();
    }
  }
}

// CSS for loading states and animations
const loadingStyles = `
<style>
/* Loading States */
.btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.form-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: 12px;
}

.global-loading {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loading-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

.loading-content {
    position: relative;
    z-index: 1;
    text-align: center;
    color: white;
}

/* Skeleton Loader */
.skeleton-loader {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 8px;
    padding: 1rem;
}

.skeleton-line {
    height: 12px;
    background: #e0e0e0;
    border-radius: 6px;
    margin-bottom: 8px;
}

.skeleton-line.short {
    width: 60%;
}

@keyframes skeleton-loading {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

/* Animations */
.animate-on-scroll {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
}

.animate-on-scroll.animate-in {
    opacity: 1;
    transform: translateY(0);
}

/* Page Transitions */
.page-transition {
    transition: opacity 0.3s ease;
}

/* Notification Styles */
.notification {
    margin-bottom: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: none;
    border-radius: 8px;
}

/* Progress Bar Animation */
.progress-bar {
    transition: width 1s ease-in-out;
}

/* Responsive Loading */
@media (max-width: 768px) {
    .loading-content {
        padding: 1rem;
    }
    
    .notification {
        margin: 0.5rem;
        max-width: calc(100vw - 2rem);
    }
}
</style>
`;

// Add styles to head
document.head.insertAdjacentHTML('beforeend', loadingStyles);

// Initialize UX enhancements when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new UXEnhancements();
});

// Export for use in other scripts
window.UXEnhancements = UXEnhancements;
