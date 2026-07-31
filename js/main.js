document.addEventListener('DOMContentLoaded', () => {

  /* SCROLL PROGRESS */
  const progress = document.createElement('div');
  progress.className = 'scroll-progress';
  document.body.prepend(progress);

  /* BACK TO TOP */
  const backToTop = document.createElement('button');
  backToTop.className = 'back-to-top';
  backToTop.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 15l-6-6-6 6"/></svg>';
  backToTop.setAttribute('aria-label', 'Retour en haut');
  document.body.appendChild(backToTop);

  let ticking = false;

  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(() => {
        const scrollY = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;

        if (docHeight > 0) {
          progress.style.width = (scrollY / docHeight * 100) + '%';
        }

        nav.classList.toggle('scrolled', scrollY > 50);
        backToTop.classList.toggle('visible', scrollY > 400);

        ticking = false;
      });
      ticking = true;
    }
  });

  backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  /* MOBILE NAV TOGGLE */
  const nav = document.querySelector('.nav');
  const toggle = document.querySelector('.nav-toggle');
  const links = document.querySelector('.nav-links');

  if (toggle) {
    toggle.addEventListener('click', () => {
      links.classList.toggle('open');
      toggle.classList.toggle('open');
    });
  }

  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
      links.classList.remove('open');
      toggle.classList.remove('open');
    });
  });

  /* SCROLL ANIMATIONS WITH STAGGER */
  const animateElements = document.querySelectorAll('.animate-on-scroll');

  if (animateElements.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
          const parent = entry.target.closest('.apartments-grid, .features-grid, .testimonials-grid, .team-grid, .benin-grid, .contact-cards');
          let delay = 0;

          if (parent) {
            const children = Array.from(parent.children);
            const i = children.indexOf(entry.target);
            delay = i * 0.08;
          }

          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          entry.target.style.transitionDelay = delay + 's';
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.08,
      rootMargin: '0px 0px -40px 0px'
    });

    animateElements.forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(28px)';
      el.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
      observer.observe(el);
    });
  }

  /* FORM HANDLING */
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const btn = form.querySelector('.btn');
      if (!btn) return;
      const orig = btn.textContent;
      btn.textContent = '✓ Envoyé !';
      btn.style.background = '#10B981';
      setTimeout(() => {
        btn.textContent = orig;
        btn.style.background = '';
      }, 3000);
    });
  });

  /* HERO PARALLAX */
  const hero = document.querySelector('.hero');
  if (hero) {
    window.addEventListener('mousemove', (e) => {
      const x = (e.clientX / window.innerWidth - 0.5) * 12;
      const y = (e.clientY / window.innerHeight - 0.5) * 12;
      hero.style.setProperty('--x', x + 'px');
      hero.style.setProperty('--y', y + 'px');
    });
  }

  /* COUNTER ANIMATION FOR STATS */
  const stats = document.querySelectorAll('.stat-number');
  if (stats.length) {
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const target = el.textContent.trim();
          const num = parseInt(target);
          if (!isNaN(num) && num > 0) {
            el.textContent = '0';
            const duration = 1200;
            const start = performance.now();

            function update(currentTime) {
              const elapsed = currentTime - start;
              const progress = Math.min(elapsed / duration, 1);
              const eased = 1 - Math.pow(1 - progress, 3);
              el.textContent = Math.floor(eased * num);
              if (progress < 1) {
                requestAnimationFrame(update);
              } else {
                el.textContent = target;
              }
            }

            requestAnimationFrame(update);
          }
          counterObserver.unobserve(el);
        }
      });
    }, { threshold: 0.5 });

    stats.forEach(el => counterObserver.observe(el));
  }

});
