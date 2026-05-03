/**
 * RELATIONAL LENS — MASTER JAVASCRIPT
 */

document.addEventListener('DOMContentLoaded', function() {
  
  // 1. Scroll Reveal Animation
  const revealElements = document.querySelectorAll('.reveal');
  
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  });

  revealElements.forEach(el => {
    revealObserver.observe(el);
  });

  // 2. Navbar Scroll Effect
  const navbar = document.querySelector('.navbar');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      navbar.classList.add('shadow-sm');
      navbar.style.padding = '0.5rem 0';
    } else {
      navbar.classList.remove('shadow-sm');
      navbar.style.padding = '1rem 0';
    }
  });

});
