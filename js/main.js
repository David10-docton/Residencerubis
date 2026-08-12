document.addEventListener('DOMContentLoaded', () => {
  document.documentElement.classList.add('js-anim');

  const progress = document.createElement('div');
  progress.className = 'scroll-progress';
  document.body.prepend(progress);

  const backToTop = document.createElement('button');
  backToTop.className = 'back-to-top';
  backToTop.innerHTML = '<i class="ph ph-fill ph-arrow-up" aria-hidden="true"></i>';
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

  const settings = document.getElementById('navSettings');
  const settingsBtn = document.getElementById('navSettingsBtn');

  if (settings && settingsBtn) {
    settingsBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const open = settings.classList.toggle('open');
      settingsBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', (e) => {
      if (!settings.contains(e.target)) {
        settings.classList.remove('open');
        settingsBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  const animateElements = document.querySelectorAll('.animate-on-scroll');

  if (animateElements.length && 'IntersectionObserver' in window) {
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

    animateElements.forEach(el => observer.observe(el));
  } else {
    animateElements.forEach(el => {
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    });
  }

  const hero = document.querySelector('.hero');
  if (hero) {
    window.addEventListener('mousemove', (e) => {
      const x = (e.clientX / window.innerWidth - 0.5) * 12;
      const y = (e.clientY / window.innerHeight - 0.5) * 12;
      hero.style.setProperty('--x', x + 'px');
      hero.style.setProperty('--y', y + 'px');
    });
  }

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

  document.querySelectorAll('[data-bg]').forEach(el => {
    const lazyBgObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const gradient = el.dataset.gradient ? el.dataset.gradient + ',' : '';
          el.style.backgroundImage = gradient + "url('" + el.dataset.bg + "')";
          lazyBgObserver.unobserve(el);
        }
      });
    }, { rootMargin: '200px 0px' });
    lazyBgObserver.observe(el);
  });

  document.querySelectorAll('.slider').forEach(slider => {
    const imgs = slider.querySelectorAll('.slider-img');
    if (imgs.length < 2) return;

    const dotsWrap = slider.querySelector('.slider-dots');
    const dots = [];
    let current = 0;

    imgs.forEach((_, i) => {
      const d = document.createElement('button');
      d.type = 'button';
      d.className = 'slider-dot' + (i === 0 ? ' active' : '');
      d.setAttribute('aria-label', 'Image ' + (i + 1));
      d.addEventListener('click', () => { show(i); restart(); });
      dotsWrap.appendChild(d);
      dots.push(d);
    });

    let timer = null;

    function ensureLoaded(i) {
      const img = imgs[i];
      if (img.dataset.src && !img.src) {
        img.src = img.dataset.src;
      }
    }

    function show(i) {
      ensureLoaded(i);
      imgs[current].classList.remove('active');
      dots[current].classList.remove('active');
      current = i;
      imgs[current].classList.add('active');
      dots[current].classList.add('active');
      ensureLoaded((current + 1) % imgs.length);
    }

    function next() { show((current + 1) % imgs.length); }

    function restart() {
      if (timer) clearInterval(timer);
      timer = setInterval(next, 4500);
    }

    slider.querySelector('.slider-prev').addEventListener('click', (e) => {
      e.preventDefault();
      show((current - 1 + imgs.length) % imgs.length);
      restart();
    });

    slider.querySelector('.slider-next').addEventListener('click', (e) => {
      e.preventDefault();
      next();
      restart();
    });

    restart();
  });

  const heroSlides = document.querySelectorAll('.hero-slide');
  if (heroSlides.length > 1) {
    let currentSlide = 0;
    setInterval(() => {
      heroSlides[currentSlide].classList.remove('active');
      currentSlide = (currentSlide + 1) % heroSlides.length;
      heroSlides[currentSlide].classList.add('active');
    }, 3000);
  }

  const buildingSlides = document.querySelectorAll('.building-slide');
  if (buildingSlides.length > 1) {
    const dotsWrap = document.querySelector('.building-dots');
    const dots = [];
    let currentBuilding = 0;
    let buildingTimer = null;

    buildingSlides.forEach((_, i) => {
      const d = document.createElement('button');
      d.type = 'button';
      d.className = 'building-dot' + (i === 0 ? ' active' : '');
      d.setAttribute('aria-label', 'Bâtiment ' + (i + 1));
      d.addEventListener('click', () => { showBuilding(i); restartBuilding(); });
      if (dotsWrap) dotsWrap.appendChild(d);
      dots.push(d);
    });

    function showBuilding(i) {
      buildingSlides[currentBuilding].classList.remove('active');
      if (dots[currentBuilding]) dots[currentBuilding].classList.remove('active');
      currentBuilding = i;
      buildingSlides[currentBuilding].classList.add('active');
      if (dots[currentBuilding]) dots[currentBuilding].classList.add('active');
    }

    function restartBuilding() {
      if (buildingTimer) clearInterval(buildingTimer);
      buildingTimer = setInterval(() => showBuilding((currentBuilding + 1) % buildingSlides.length), 5000);
    }

    const buildingPrev = document.querySelector('.building-prev');
    const buildingNext = document.querySelector('.building-next');

    if (buildingPrev) {
      buildingPrev.addEventListener('click', () => {
        showBuilding((currentBuilding - 1 + buildingSlides.length) % buildingSlides.length);
        restartBuilding();
      });
    }

    if (buildingNext) {
      buildingNext.addEventListener('click', () => {
        showBuilding((currentBuilding + 1) % buildingSlides.length);
        restartBuilding();
      });
    }

    restartBuilding();
  }

  const navSearch = document.getElementById('navSearch');
  const navSearchBtn = document.getElementById('navSearchBtn');
  if (navSearch && navSearchBtn) {
    navSearchBtn.addEventListener('click', () => {
      const isOpen = navSearch.classList.toggle('open');
      if (isOpen) {
        const input = navSearch.querySelector('input');
        if (input) input.focus();
      }
    });
    document.addEventListener('click', (e) => {
      if (!navSearch.contains(e.target) && navSearch.classList.contains('open')) {
        navSearch.classList.remove('open');
      }
    });
  }

  const produitMain = document.querySelector('.produit-main img');
  const produitThumbs = document.querySelectorAll('.produit-thumb');
  if (produitMain && produitThumbs.length) {
    produitThumbs.forEach(thumb => {
      thumb.addEventListener('click', () => {
        produitMain.src = thumb.src;
        produitMain.alt = thumb.alt;
        produitThumbs.forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
      });
    });
  }

  const iconFallbackMap = [
    [/wifi/i, 'ph-wifi-high'],
    [/veilleur/i, 'ph-shield-check'],
    [/parking/i, 'ph-car-simple'],
    [/s[èe]che/i, 'ph-wind'],
    [/table/i, 'ph-table'],
    [/fer/i, 'ph-iron'],
    [/m[ée]nage/i, 'ph-spray-bottle'],
    [/dressage/i, 'ph-bed'],
    [/nettoyage/i, 'ph-washing-machine'],
    [/repassage/i, 'ph-arrows-vertical'],
    [/jeu de lit/i, 'ph-bed'],
    [/poussette/i, 'ph-baby'],
    [/voiture/i, 'ph-car-simple']
  ];
  document.querySelectorAll('.service-item-icon img').forEach((img) => {
    img.addEventListener('error', () => {
      const alt = img.getAttribute('alt') || '';
      let cls = 'ph-question';
      for (const [re, icon] of iconFallbackMap) {
        if (re.test(alt)) { cls = icon; break; }
      }
      const icon = document.createElement('i');
      icon.className = 'ph ph-fill ' + cls;
      icon.setAttribute('aria-hidden', 'true');
      img.replaceWith(icon);
    });
  });

});
