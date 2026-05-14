/**
 * RELATIONAL LENS — MASTER JAVASCRIPT
 */

document.addEventListener('DOMContentLoaded', function() {
  
  // 0. Theme Toggle Logic
  const themeToggle = document.querySelector('#theme-toggle');
  
  // Initialize theme
  const savedTheme = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-theme', savedTheme);

  if (themeToggle) {
    const toggleTheme = function() {
      const currentTheme = document.documentElement.getAttribute('data-theme');
      const newTheme = currentTheme === 'light' ? 'dark' : 'light';
      
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      console.log('Theme switched to:', newTheme);
    };

    themeToggle.addEventListener('click', toggleTheme);
    
    // Accessibility: Keyboard support for theme toggle
    themeToggle.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleTheme();
      }
    });
  }

  // 1. Scroll Reveal Animation (with Staggering)
  const revealElements = document.querySelectorAll('.reveal');
  
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const target = entry.target;
        
        // Check for siblings to stagger
        const parent = target.parentElement;
        const index = Array.from(parent.children).indexOf(target);
        const staggerDelay = (index % 3) * 150; // Stagger in groups of 3
        
        const customDelay = parseInt(target.getAttribute('data-delay') || 0);
        
        setTimeout(() => {
          target.classList.add('visible');
        }, customDelay + staggerDelay);
        
        revealObserver.unobserve(target);
      }
    });
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  });

  revealElements.forEach(el => {
    revealObserver.observe(el);
  });

  // 2. Custom Cinematic Cursor
  if (window.matchMedia("(pointer: fine)").matches) {
    const cursor = document.createElement('div');
    cursor.className = 'cursor-follower';
    document.body.appendChild(cursor);

    document.addEventListener('mousemove', (e) => {
      cursor.style.left = e.clientX + 'px';
      cursor.style.top = e.clientY + 'px';
    });

    const activeElements = 'a, button, .story-card, .article-card, .theme-toggle';
    document.querySelectorAll(activeElements).forEach(el => {
      el.addEventListener('mouseenter', () => cursor.classList.add('active'));
      el.addEventListener('mouseleave', () => cursor.classList.remove('active'));
    });
  }

  // 3. Skeleton Loader Cleanup
  const skeletonImages = document.querySelectorAll('.card-img-wrapper img');
  skeletonImages.forEach(img => {
    const wrapper = img.parentElement;
    wrapper.classList.add('skeleton-loader');
    
    if (img.complete) {
      wrapper.classList.remove('skeleton-loader');
    } else {
      img.addEventListener('load', () => {
        wrapper.classList.remove('skeleton-loader');
      });
    }
  });

  // 4. Navbar Scroll Effect (Pill Morph)
  const navbar = document.querySelector('.navbar');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 100) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });

  // 3. Smooth Anchor Scrolling
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        window.scrollTo({
          top: target.offsetTop - 100,
          behavior: 'smooth'
        });
      }
    });
  });

});
