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

  const heroScrollHint = document.querySelector('.hero-scroll-hint');
  if (heroScrollHint) {
    heroScrollHint.addEventListener('click', () => {
      const nextSection = document.querySelector('main > section:nth-of-type(2)') || document.querySelector('.info-banner');
      if (nextSection) nextSection.scrollIntoView({ behavior: 'smooth' });
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

  // Anneau de satisfaction (page À propos) : animation déclenchée à l'apparition
  const aboutRing = document.querySelector('.about-ring');
  if (aboutRing && 'IntersectionObserver' in window) {
    const ringObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          aboutRing.classList.add('in-view');
          ringObserver.unobserve(aboutRing);
        }
      });
    }, { threshold: 0.5 });
    ringObserver.observe(aboutRing);
  } else if (aboutRing) {
    aboutRing.classList.add('in-view');
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

  /* ===== LIGHTBOX : galerie photos de la fiche produit ===== */
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = lightbox ? lightbox.querySelector('img') : null;
  const lightboxCaption = lightbox ? lightbox.querySelector('.lightbox-caption') : null;

  if (lightbox && lightboxImg) {
    // Toutes les photos de la galerie (grande image + vignettes)
    const gallerySources = [];
    if (produitMain) gallerySources.push({ src: produitMain.src, alt: produitMain.alt });
    produitThumbs.forEach(thumb => {
      gallerySources.push({ src: thumb.src, alt: thumb.alt });
    });

    const hasMultiple = gallerySources.length > 1;
    let currentIndex = 0;
    const prevBtn = lightbox.querySelector('.lightbox-prev');
    const nextBtn = lightbox.querySelector('.lightbox-next');
    const closeBtn = lightbox.querySelector('.lightbox-close');
    let lastFocused = null;

    function showLightbox(i) {
      currentIndex = (i + gallerySources.length) % gallerySources.length;
      lightboxImg.src = gallerySources[currentIndex].src;
      lightboxImg.alt = gallerySources[currentIndex].alt;
      if (lightboxCaption) {
        lightboxCaption.textContent = gallerySources[currentIndex].alt || '';
      }
      if (prevBtn) prevBtn.classList.toggle('is-hidden', !hasMultiple);
      if (nextBtn) nextBtn.classList.toggle('is-hidden', !hasMultiple);
    }

    function openLightbox(i) {
      lastFocused = document.activeElement;
      showLightbox(i);
      lightbox.classList.add('open');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      if (closeBtn) closeBtn.focus();
    }

    function closeLightbox() {
      lightbox.classList.remove('open');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      if (lastFocused) lastFocused.focus();
    }

    const zoomBtn = document.querySelector('.produit-zoom');
    if (zoomBtn) {
      zoomBtn.addEventListener('click', (e) => { e.stopPropagation(); openLightbox(0); });
    }
    if (produitMain) {
      produitMain.addEventListener('click', (e) => { e.stopPropagation(); openLightbox(0); });
    }
    produitThumbs.forEach((thumb, idx) => {
      thumb.addEventListener('click', (e) => {
        e.stopPropagation();
        openLightbox(idx + 1);
      });
    });

    if (closeBtn) closeBtn.addEventListener('click', (e) => { e.stopPropagation(); closeLightbox(); });
    if (prevBtn) prevBtn.addEventListener('click', (e) => { e.stopPropagation(); showLightbox(currentIndex - 1); });
    if (nextBtn) nextBtn.addEventListener('click', (e) => { e.stopPropagation(); showLightbox(currentIndex + 1); });

    // Fermer la lightbox au clic sur le fond (zone sombre)
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) closeLightbox();
    });
    // Empêcher les clics sur le contenu de la lightbox de la fermer par accident
    const lbFigure = lightbox.querySelector('.lightbox-figure');
    if (lbFigure) lbFigure.addEventListener('click', (e) => e.stopPropagation());
    if (lightboxImg) lightboxImg.addEventListener('click', (e) => e.stopPropagation());

    document.addEventListener('keydown', (e) => {
      if (!lightbox.classList.contains('open')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') { e.preventDefault(); showLightbox(currentIndex - 1); }
      if (e.key === 'ArrowRight') { e.preventDefault(); showLightbox(currentIndex + 1); }
    });
  }

  /* ============================================================
   * Carrousel vidéo (fiche produit)
   * ============================================================ */
  const videoCarousel = document.getElementById('videoCarousel');
  if (videoCarousel) {
    const videoSlides = videoCarousel.querySelectorAll('.produit-video-slide');
    const videoDots = videoCarousel.querySelectorAll('.video-dot');
    const videoCounter = document.getElementById('videoCounter');
    let currentVideo = 0;

    window.videoCarouselGo = function(idx) {
      // Pause la vidéo en cours
      const currentSlide = videoSlides[currentVideo];
      if (currentSlide) {
        const vid = currentSlide.querySelector('video');
        if (vid) vid.pause();
      }
      currentVideo = (idx + videoSlides.length) % videoSlides.length;
      videoSlides.forEach((s, i) => s.classList.toggle('active', i === currentVideo));
      videoDots.forEach((d, i) => d.classList.toggle('active', i === currentVideo));
      if (videoCounter) videoCounter.textContent = (currentVideo + 1) + ' / ' + videoSlides.length;
    };

    window.videoCarouselNav = function(dir) {
      videoCarouselGo(currentVideo + dir);
    };
  }

  /* ============================================================
   * Calcul du prix en temps réel (fiche produit)
   * ============================================================ */
  const bookingForm = document.querySelector('.produit-booking[data-price]');

  function fmtF(n) {
    return Number(n).toLocaleString('fr-FR').replace(/[\u202f\u00a0]/g, ' ');
  }

  if (bookingForm) {
    const calc = document.getElementById('produitPriceCalc');
    const detailEl = document.getElementById('priceCalcDetail');
    const eurEl = document.getElementById('priceCalcEur');
    const totalEl = document.getElementById('priceCalcTotal');
    const dateSelects = bookingForm.querySelectorAll('select[name^="debut_"], select[name^="fin_"]');

    function updatePrice() {
      const v = {};
      let complete = true;
      dateSelects.forEach(s => {
        if (!s.value) complete = false;
        v[s.name] = parseInt(s.value, 10) || 0;
      });
      if (!complete) {
        if (calc) calc.classList.remove('is-visible');
        return;
      }
      const checkIn = new Date(v.debut_annee, v.debut_mois - 1, v.debut_jour);
      const checkOut = new Date(v.fin_annee, v.fin_mois - 1, v.fin_jour);
      const valid = checkIn.getDate() === v.debut_jour && checkIn.getMonth() === v.debut_mois - 1
        && checkOut.getDate() === v.fin_jour && checkOut.getMonth() === v.fin_mois - 1;
      if (!valid || checkOut <= checkIn) {
        if (calc) calc.classList.remove('is-visible');
        return;
      }
      const nights = Math.round((checkOut - checkIn) / 86400000);
      const price = parseInt(bookingForm.dataset.price, 10) || 0;
      const eur = parseInt(bookingForm.dataset.priceEur, 10) || 0;
      detailEl.textContent = nights + ' nuit' + (nights > 1 ? 's' : '') + ' × ' + bookingForm.dataset.priceLabel;
      eurEl.textContent = '≈ ' + fmtF(nights * eur) + ' €';
      totalEl.textContent = fmtF(nights * price) + ' F';
      calc.classList.add('is-visible');
    }

    dateSelects.forEach(s => s.addEventListener('change', updatePrice));
  }

  /* ============================================================
   * Tiroir « Réservation(s) » — panier de réservation
   * ============================================================ */
  const CART_KEY = 'rubis_reservations_v1';
  const resFloat = document.getElementById('resFloat');
  const resDrawer = document.getElementById('resDrawer');
  const resOverlay = document.getElementById('resOverlay');
  const resClose = document.getElementById('resClose');
  const resContinue = document.getElementById('resContinue');
  const resBadge = document.getElementById('resBadge');
  const resEmpty = document.getElementById('resEmpty');
  const resList = document.getElementById('resList');
  const resFoot = document.getElementById('resFoot');
  const resTotal = document.getElementById('resTotal');
  const resEmail = document.getElementById('resEmail');
  const resAlert = document.getElementById('resAlert');
  const resSubmit = document.getElementById('resSubmit');

  function esc(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function readCart() {
    try {
      const data = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
      return Array.isArray(data) ? data : [];
    } catch (e) {
      return [];
    }
  }

  function saveCart(items) {
    try { localStorage.setItem(CART_KEY, JSON.stringify(items)); } catch (e) { /* stockage indisponible */ }
  }

  function cartTotal(items) {
    return items.reduce((sum, it) => sum + ((it.nights || 0) * (it.price || 0)), 0);
  }

  function fmtDate(iso) {
    const p = String(iso).split('-');
    return (p[2] || '') + '/' + (p[1] || '') + '/' + (p[0] || '');
  }

  function flashBadge() {
    if (!resBadge) return;
    resBadge.classList.remove('pop');
    void resBadge.offsetWidth;
    resBadge.classList.add('pop');
  }

  function renderCart() {
    if (!resDrawer) return;
    const items = readCart();
    const count = items.length;
    if (resBadge) {
      resBadge.textContent = count;
      resBadge.hidden = count === 0;
    }
    if (count === 0) {
      resEmpty.hidden = false;
      resList.innerHTML = '';
      resFoot.hidden = true;
      return;
    }
    resEmpty.hidden = true;
    resFoot.hidden = false;
    resTotal.textContent = fmtF(cartTotal(items)) + ' F';
    resList.innerHTML = items.map((it, idx) =>
      '<li class="res-drawer-item">' +
        (it.image ? '<img src="' + esc(it.image) + '" alt="" loading="lazy">' : '<span class="res-drawer-item-ph"></span>') +
        '<div class="res-drawer-item-info">' +
          '<strong>' + esc(it.name) + '</strong>' +
          '<span>' + esc(fmtDate(it.checkIn)) + ' → ' + esc(fmtDate(it.checkOut)) + ' · ' + Number(it.nights || 0) + ' nuit' + (Number(it.nights || 0) > 1 ? 's' : '') + '</span>' +
          '<em>' + fmtF((it.nights || 0) * (it.price || 0)) + ' F</em>' +
        '</div>' +
        '<button type="button" class="res-drawer-remove" data-idx="' + idx + '" aria-label="Retirer ' + esc(it.name) + '"><i class="ph ph-fill ph-trash" aria-hidden="true"></i></button>' +
      '</li>'
    ).join('');
    resList.querySelectorAll('.res-drawer-remove').forEach(btn => {
      btn.addEventListener('click', () => {
        const items = readCart();
        items.splice(parseInt(btn.dataset.idx, 10), 1);
        saveCart(items);
        renderCart();
        flashBadge();
      });
    });
  }

  function openDrawer() {
    if (!resDrawer) return;
    resDrawer.classList.add('open');
    resOverlay.hidden = false;
    requestAnimationFrame(() => resOverlay.classList.add('show'));
    resDrawer.setAttribute('aria-hidden', 'false');
    resFloat.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
    const firstFocus = resDrawer.querySelector('.res-drawer-close');
    if (firstFocus) firstFocus.focus();
  }

  function closeDrawer() {
    if (!resDrawer) return;
    resDrawer.classList.remove('open');
    resOverlay.classList.remove('show');
    window.setTimeout(() => { resOverlay.hidden = true; }, 300);
    resDrawer.setAttribute('aria-hidden', 'true');
    resFloat.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    resFloat.focus();
  }

  if (resFloat) {
    resFloat.addEventListener('click', () => {
      if (resDrawer.classList.contains('open')) closeDrawer();
      else openDrawer();
    });
    if (resClose) resClose.addEventListener('click', closeDrawer);
    if (resOverlay) resOverlay.addEventListener('click', closeDrawer);
    if (resContinue) resContinue.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && resDrawer.classList.contains('open')) closeDrawer();
    });

    // À la soumission du formulaire de réservation : on enregistre la réservation
    // dans le panier (localStorage) avant l'envoi serveur classique.
    if (bookingForm) {
      bookingForm.addEventListener('submit', () => {
        const g = (n) => parseInt((bookingForm.querySelector('select[name="' + n + '"]') || {}).value, 10) || 0;
        const checkIn = new Date(g('debut_annee'), g('debut_mois') - 1, g('debut_jour'));
        const checkOut = new Date(g('fin_annee'), g('fin_mois') - 1, g('fin_jour'));
        const emailInput = bookingForm.querySelector('input[name="email"]');
        const email = emailInput ? emailInput.value.trim() : '';
        const datesOk = checkIn.getDate() === g('debut_jour') && checkIn.getMonth() === g('debut_mois') - 1
          && checkOut.getDate() === g('fin_jour') && checkOut.getMonth() === g('fin_mois') - 1;
        // On n'enregistre dans le panier que si la demande sera réellement acceptée
        // par le serveur (mêmes validations qu'en PHP : dates réelles + email).
        if (!datesOk || checkOut <= checkIn || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return;
        const nights = Math.round((checkOut - checkIn) / 86400000);
        const iso = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        const items = readCart();
        const entry = {
          name: bookingForm.dataset.apartment || '',
          image: bookingForm.dataset.image || '',
          checkIn: iso(checkIn),
          checkOut: iso(checkOut),
          nights: nights,
          price: parseInt(bookingForm.dataset.price, 10) || 0
        };
        const dup = items.findIndex(it => it.name === entry.name && it.checkIn === entry.checkIn && it.checkOut === entry.checkOut);
        if (dup >= 0) items.splice(dup, 1);
        items.push(entry);
        saveCart(items);
        flashBadge();
      });
    }

    // Envoi de la demande groupée (AJAX vers contact.php)
    if (resSubmit) {
      resSubmit.addEventListener('click', async () => {
        const email = resEmail.value.trim();
        const items = readCart();
        const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        if (!emailOk) {
          resAlert.textContent = 'Veuillez saisir une adresse email valide.';
          resAlert.className = 'res-drawer-alert err';
          resAlert.hidden = false;
          resEmail.focus();
          return;
        }
        if (items.length === 0) {
          resAlert.textContent = 'Votre sélection est vide.';
          resAlert.className = 'res-drawer-alert err';
          resAlert.hidden = false;
          return;
        }
        resSubmit.disabled = true;
        const csrfInput = resDrawer.querySelector('input[name="csrf_token"]');
        const fd = new FormData();
        fd.append('panier_submit', '1');
        fd.append('csrf_token', csrfInput ? csrfInput.value : '');
        fd.append('website', '');
        fd.append('email', email);
        fd.append('items', JSON.stringify(items));
        try {
          const resp = await fetch('contact.php', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: fd
          });
          const data = await resp.json();
          resAlert.textContent = data.message || 'Une erreur est survenue.';
          resAlert.className = 'res-drawer-alert ' + (data.ok ? 'ok' : 'err');
          resAlert.hidden = false;
          if (data.ok) {
            saveCart([]);
            renderCart();
            resEmail.value = '';
            const p = resEmpty.querySelector('p');
            const defaultText = p.textContent;
            p.textContent = data.message;
            window.setTimeout(() => { p.textContent = defaultText; }, 9000);
          }
        } catch (err) {
          resAlert.textContent = 'Une erreur est survenue. Veuillez réessayer.';
          resAlert.className = 'res-drawer-alert err';
          resAlert.hidden = false;
        }
        resSubmit.disabled = false;
      });
    }

    renderCart();
  }

});
