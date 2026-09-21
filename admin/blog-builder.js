/* ============================================================
 * Blog Builder — éditeur visuel d'articles (Blog Résidence Rubis)
 *
 * L'administrateur compose l'article avec des blocs (chaque bloc
 * a une partie « Titre », une partie « Contenu » et des options
 * de style). Le HTML + le CSS sont générés automatiquement et
 * l'aperçu est mis à jour en direct, sans écrire de code.
 * ============================================================ */
(function () {
  'use strict';

  function el(id) { return document.getElementById(id); }
  function escAttr(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }
  function escHtml(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  /* --- Définition des blocs -------------------------------- */
  var BLOCK_TYPES = {
    section: {
      label: 'Section',
      icon: 'ph ph-fill ph-text-aa',
      fields: [
        { name: 'title',      label: 'Titre de la section',              type: 'text' },
        { name: 'content',    label: 'Texte de la section (une phrase par ligne)', type: 'textarea' },
        { name: 'titleLevel', label: 'Niveau du titre',                  type: 'select', options: [['2', 'Titre principal'], ['3', 'Sous-titre']] },
        { name: 'align',      label: 'Alignement',                       type: 'align' },
        { name: 'titleColor', label: 'Couleur du titre',                 type: 'color' }
      ]
    },
    list: {
      label: 'Liste',
      icon: 'ph ph-fill ph-list-bullets',
      fields: [
        { name: 'style', label: 'Style de la liste', type: 'select', options: [['bullets', 'À puces'], ['numbers', 'Numérotée'], ['checks', 'Avec coches']] },
        { name: 'items', label: 'Éléments (un par ligne)', type: 'textarea' },
        { name: 'align', label: 'Alignement',          type: 'align' }
      ]
    },
    quote: {
      label: 'Citation',
      icon: 'ph ph-fill ph-quotes',
      fields: [
        { name: 'text',   label: 'Texte de la citation', type: 'textarea' },
        { name: 'author', label: 'Auteur (optionnel)',   type: 'text' },
        { name: 'align',  label: 'Alignement',           type: 'align' }
      ]
    },
    image: {
      label: 'Image',
      icon: 'ph ph-fill ph-image',
      fields: [
        { name: 'url',     label: 'URL de l\'image',      type: 'text', mediaType: 'image' },
        { name: 'caption', label: 'Légende (optionnelle)', type: 'text' },
        { name: 'align',   label: 'Alignement',           type: 'align' },
        { name: 'width',   label: 'Largeur (%)',          type: 'number' }
      ]
    },
    video: {
      label: 'Vidéo',
      icon: 'ph ph-fill ph-video',
      fields: [
        { name: 'url',     label: 'Lien de la vidéo (YouTube, Facebook, TikTok)', type: 'text' },
        { name: 'caption', label: 'Légende (optionnelle)', type: 'text' }
      ]
    },
    divider: {
      label: 'Séparateur',
      icon: 'ph ph-fill ph-minus',
      fields: []
    }
  };

  var FIELD_DEFAULTS = {
    section: { title: '', content: '', titleLevel: '2', align: 'left', titleColor: '#1a1a1a' },
    list:    { style: 'bullets', items: '', align: 'left' },
    quote:   { text: '', author: '', align: 'left' },
    image:   { url: '', caption: '', align: 'center', width: '100' },
    video:   { url: '', caption: '' },
    divider: {}
  };

  /* --- État ------------------------------------------------ */
  var uid = 1;
  var blocks = [];
  var mode = 'visual';
  var previewOpen = false;
  var previewTimer = null;

  /* --- Éléments DOM ---------------------------------------- */
  var codeEl         = el('blog-edit-content');
  var blocksHidden   = el('blog-content-blocks');
  var blocksList     = el('ab-blocks');
  var emptyEl        = el('ab-empty');
  var globalCssEl    = el('ab-global-css');
  var titleEl        = el('blog-edit-title');
  var subtitleEl     = el('blog-edit-subtitle');
  var formWrap       = el('blog-form-wrap');
  var editorForm     = formWrap ? formWrap.querySelector('form') : null;
  var previewModal   = el('blog-preview-modal');
  var previewFrame   = el('ab-preview-frame');

  function makeBlock(type) {
    var f = {};
    var defs = FIELD_DEFAULTS[type] || {};
    Object.keys(defs).forEach(function (k) { f[k] = defs[k]; });
    f.css = '';
    return { id: 'b' + (uid++), type: type, fields: f };
  }

  /* --- Génération du champ d'un bloc ------------------------ */
  function fieldHtml(fd) {
    var html = '<div class="ab-field ab-field--' + fd.type + '">'
      + '<label>' + fd.label + '</label>';
    if (fd.type === 'text') {
      var uploadHint = '';
      if (fd.name === 'url' && fd.mediaType) {
        var uidUpload = 'ab-file-' + (uid++);
        uploadHint = '<div class="ab-upload-row">'
          + '<input type="text" data-f="' + fd.name + '" value="" placeholder="Collez une URL ou choisissez un fichier image">'
          + '<label class="ab-upload-inline-btn" title="Choisir une image"><i class="ph ph-fill ph-upload" aria-hidden="true"></i>'
          + '<input type="file" id="' + uidUpload + '" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden-input" data-ab-file-upload>'
          + '</label>'
          + '</div>';
        html += uploadHint;
      } else {
        html += '<input type="text" data-f="' + fd.name + '" value="">';
      }
    } else if (fd.type === 'textarea') {
      html += '<textarea data-f="' + fd.name + '" rows="3"></textarea>';
    } else if (fd.type === 'select') {
      html += '<select data-f="' + fd.name + '">';
      (fd.options || []).forEach(function (op) {
        html += '<option value="' + op[0] + '">' + op[1] + '</option>';
      });
      html += '</select>';
    } else if (fd.type === 'color') {
      html += '<div class="ab-color">'
        + '<input type="color" data-f="' + fd.name + '" class="ab-color-input" value="">'
        + '<input type="text" data-f2="' + fd.name + '" class="ab-color-text" maxlength="9" placeholder="#1a1a1a" value="">'
        + '</div>';
    } else if (fd.type === 'number') {
      html += '<input type="number" data-f="' + fd.name + '" min="10" max="100" step="5" value="">';
    } else if (fd.type === 'align') {
      html += '<div class="ab-align" data-align="' + fd.name + '">'
        + '<button type="button" data-v="left" title="Gauche"><i class="ph ph-fill ph-text-align-left" aria-hidden="true"></i></button>'
        + '<button type="button" data-v="center" title="Centré"><i class="ph ph-fill ph-text-align-center" aria-hidden="true"></i></button>'
        + '<button type="button" data-v="right" title="Droite"><i class="ph ph-fill ph-text-align-right" aria-hidden="true"></i></button>'
        + '</div>';
    } else if (fd.type === 'css') {
      html += '<textarea data-f="' + fd.name + '" rows="2" class="ab-css-input" placeholder="Ex : .article-body h2 { letter-spacing: 1px; }"></textarea>';
    }
    html += '</div>';
    return html;
  }

  function renderBlock(block) {
    var def = BLOCK_TYPES[block.type];
    var elBlock = document.createElement('div');
    elBlock.className = 'ab-block';
    elBlock.dataset.id = block.id;

    elBlock.innerHTML =
      '<div class="ab-block-head">'
      + '<button type="button" class="ab-block-collapse" tabindex="-1" aria-label="Replier / dérouler"><i class="ph ph-fill ph-caret-down" aria-hidden="true"></i></button>'
      + '<span class="ab-block-icon ' + def.icon + '" aria-hidden="true"></span>'
      + '<span class="ab-block-label">' + def.label + '</span>'
      + '<span class="ab-block-title"></span>'
      + '<span class="ab-block-actions">'
      + '<button type="button" class="ab-block-move-up" title="Monter" tabindex="-1"><i class="ph ph-fill ph-arrow-up" aria-hidden="true"></i></button>'
      + '<button type="button" class="ab-block-move-down" title="Descendre" tabindex="-1"><i class="ph ph-fill ph-arrow-down" aria-hidden="true"></i></button>'
      + '<button type="button" class="ab-block-del" title="Supprimer ce bloc" tabindex="-1"><i class="ph ph-fill ph-trash" aria-hidden="true"></i></button>'
      + '</span>'
      + '</div>';

    var body = document.createElement('div');
    body.className = 'ab-block-body';
    var htmlFields = '';
    def.fields.forEach(function (fd) { htmlFields += fieldHtml(fd); });
    body.innerHTML = htmlFields;
    elBlock.appendChild(body);
    return elBlock;
  }

  function blockSummary(block) {
    var f = block.fields;
    switch (block.type) {
      case 'section': return String(f.title || '');
      case 'list': {
        var items = String(f.items || '').split(/\n+/).filter(Boolean);
        if (!items.length) return '';
        return items[0] + (items.length > 1 ? ' (+' + (items.length - 1) + ' élément' + (items.length > 2 ? 's' : '') + ')' : '');
      }
      case 'quote': return String(f.text || '');
      case 'image': return String(f.url || '');
      case 'video': return String(f.url || '');
      default: return '';
    }
  }

  function refreshBadge(block, blockEl) {
    var t = blockEl.querySelector('.ab-block-title');
    if (t) t.textContent = blockSummary(block);
  }

  function bindBlock(block, blockEl) {
    blockEl.querySelectorAll('[data-f]').forEach(function (input) {
      var name = input.getAttribute('data-f');
      if (name in block.fields) input.value = block.fields[name];
      var upd = function () {
        block.fields[name] = input.value;
        refreshBadge(block, blockEl);
        schedulePreview();
      };
      input.addEventListener('input', upd);
      input.addEventListener('change', upd);
    });

    blockEl.querySelectorAll('[data-f2]').forEach(function (input) {
      var name = input.getAttribute('data-f2');
      var swatch = blockEl.querySelector('[data-f="' + name + '"]');
      if (name in block.fields) input.value = block.fields[name];
      var upd = function () {
        var v = String(input.value || '').trim();
        if (v && v.charAt(0) !== '#') v = '#' + v;
        input.value = v;
        block.fields[name] = v;
        if (swatch) swatch.value = v || swatch.value;
        schedulePreview();
      };
      input.addEventListener('input', upd);
      input.addEventListener('change', upd);
    });

    blockEl.querySelectorAll('.ab-align').forEach(function (group) {
      var name = group.getAttribute('data-align');
      var buttons = group.querySelectorAll('button');
      var setActive = function (v) {
        buttons.forEach(function (b) {
          b.classList.toggle('is-active', b.getAttribute('data-v') === v);
        });
      };
      buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          block.fields[name] = btn.getAttribute('data-v');
          setActive(block.fields[name]);
          schedulePreview();
        });
      });
      setActive(block.fields[name]);
    });
  }

  function renderAll() {
    blocksList.innerHTML = '';
    blocks.forEach(function (b) {
      var node = renderBlock(b);
      bindBlock(b, node);
      refreshBadge(b, node);
      blocksList.appendChild(node);
    });
    emptyEl.style.display = blocks.length ? 'none' : 'flex';
  }

  function moveBlock(idx, dir) {
    var j = idx + dir;
    if (j < 0 || j >= blocks.length) return;
    var tmp = blocks[idx];
    blocks[idx] = blocks[j];
    blocks[j] = tmp;
    renderAll();
    schedulePreview();
  }

  function addBlock(type) {
    blocks.push(makeBlock(type));
    renderAll();
    var nodes = blocksList.querySelectorAll('.ab-block');
    if (nodes.length) nodes[nodes.length - 1].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    schedulePreview();
  }

  /* --- Composition HTML de l'article ------------------------ */
  function alignStyle(a) {
    if (a === 'center') return 'text-align:center;';
    if (a === 'right')  return 'text-align:right;';
    return 'text-align:left;';
  }

  function buildBlock(block) {
    var f = block.fields || {};
    var rules = [];
    var html = '';

    if (block.type === 'section') {
      var level = f.titleLevel === '3' ? 'h3' : 'h2';
      var title = String(f.title || '').trim();
      var pars = String(f.content || '').split(/\n+/).map(function (s) { return s.trim(); }).filter(Boolean);
      var a = f.align || 'left';
      if (title) {
        var color = f.titleColor || '#1a1a1a';
        var st = alignStyle(a) + 'color:' + color + ';';
        if (level === 'h2' && a !== 'left') st += 'padding-left:0;border-left:none;';
        html += '<' + level + ' style="' + st + '">' + escHtml(title) + '</' + level + '>';
      }
      pars.forEach(function (p) {
        html += '<p style="' + alignStyle(a) + '">' + escHtml(p) + '</p>';
      });
    } else if (block.type === 'list') {
      var items = String(f.items || '').split(/\n+/).map(function (s) { return s.trim(); }).filter(Boolean);
      if (items.length) {
        var tag = f.style === 'numbers' ? 'ol' : 'ul';
        var cls = f.style === 'checks' ? ' class="article-body__check"' : '';
        var liStyle = alignStyle(f.align);
        html += '<' + tag + cls + '>';
        items.forEach(function (it) {
          html += '<li style="' + liStyle + '">' + escHtml(it) + '</li>';
        });
        html += '</' + tag + '>';
      }
      if (f.style === 'checks') {
        rules.push(
          '.article-body__check{list-style:none;padding-left:0;margin:18px 0;}'
          + '.article-body__check li{position:relative;padding-left:30px;margin-bottom:12px;}'
          + '.article-body__check li::before{content:"\\2713";position:absolute;left:0;top:0;color:#B85D3F;font-weight:700;}'
        );
      }
    } else if (block.type === 'quote') {
      if (String(f.text || '').trim()) {
        html += '<blockquote style="' + alignStyle(f.align) + '">' + escHtml(f.text) + '</blockquote>';
        if (String(f.author || '').trim()) {
          html += '<p style="' + alignStyle(f.align) + ';color:#9A8E85;font-size:.95rem;margin-top:-12px;">— ' + escHtml(f.author) + '</p>';
        }
      }
    } else if (block.type === 'image') {
      var url = String(f.url || '').trim();
      if (url) {
        var w = parseInt(f.width || '100', 10);
        if (!w || w < 10) w = 10;
        if (w > 100) w = 100;
        var al = f.align || 'center';
        var imgStyle = 'max-width:100%;width:' + w + '%;height:auto;border-radius:14px;box-shadow:0 4px 16px rgba(0,0,0,.08);display:block;';
        if (al === 'center') imgStyle += 'margin:0 auto;';
        else if (al === 'right') imgStyle += 'margin-left:auto;margin-right:0;';
        else imgStyle += 'margin-left:0;margin-right:auto;';
        html += '<figure style="margin:24px 0;text-align:' + al + ';">';
        html += '<img src="' + escAttr(url) + '" alt="' + escAttr(f.caption || '') + '" style="' + imgStyle + '">';
        if (String(f.caption || '').trim()) {
          html += '<figcaption style="color:#9A8E85;font-size:.9rem;margin-top:8px;font-style:italic;">' + escHtml(f.caption) + '</figcaption>';
        }
        html += '</figure>';
      }
    } else if (block.type === 'video') {
      var vurl = String(f.url || '').trim();
      if (vurl) {
        var isYT = /youtube\.com/i.test(vurl) || /youtu\.be/i.test(vurl);
        if (isYT) {
          html += '<div class="article-body__video">'
            + '<iframe src="' + escAttr(vurl) + '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen title="Vidéo"></iframe>'
            + '</div>';
          rules.push(
            '.article-body__video{position:relative;margin:24px 0;}'
            + '.article-body__video iframe{width:100%;aspect-ratio:16/9;border:0;border-radius:14px;display:block;}'
          );
        } else {
          html += '<div class="article-body__video">'
            + '<video controls playsinline preload="metadata" style="width:100%;border-radius:14px;display:block;margin:24px 0;">'
            + '<source src="' + escAttr(vurl) + '" type="video/mp4">'
            + '</video></div>';
        }
        if (String(f.caption || '').trim()) {
          html += '<p style="text-align:center;color:#9A8E85;font-size:.9rem;font-style:italic;margin-top:6px;">' + escHtml(f.caption) + '</p>';
        }
      }
    } else if (block.type === 'divider') {
      html += '<div class="article-body__divider"><span></span><i>&#9670;</i><span></span></div>';
      rules.push(
        '.article-body__divider{display:flex;align-items:center;gap:16px;margin:36px 0;}'
        + '.article-body__divider span{flex:1;height:1px;background:linear-gradient(90deg,transparent,#EBE3DA,transparent);}'
        + '.article-body__divider i{color:#DCB159;font-size:.9rem;font-style:normal;}'
      );
    }

    if (String(f.css || '').trim()) rules.push(f.css.trim());
    return { html: html, css: rules };
  }

  function composeHTML() {
    var parts = blocks.map(function (b) { return buildBlock(b); });
    var rules = [];
    var globalCss = String(globalCssEl ? globalCssEl.value : '').trim();
    if (globalCss) rules.push(globalCss);
    parts.forEach(function (p) { rules = rules.concat(p.css); });

    var out = '';
    if (rules.length) out += '<style>\n' + rules.join('\n') + '\n</style>\n';
    out += parts.map(function (p) { return p.html; }).join('\n');
    return out;
  }

  function currentContent() {
    if (mode === 'visual') return composeHTML();
    return String(codeEl.value || '');
  }

  function syncToForm() {
    codeEl.value = currentContent();
    blocksHidden.value = mode === 'visual' ? JSON.stringify(blocks) : '';
  }

  /* --- Onglets ---------------------------------------------- */
  function setTab(tab) {
    if (tab === 'code' && mode === 'visual') syncToForm();
    mode = tab;
    document.querySelectorAll('.ab-tab').forEach(function (t) {
      t.classList.toggle('is-active', t.getAttribute('data-ab-tab') === tab);
    });
    document.querySelectorAll('.ab-pane').forEach(function (p) {
      p.classList.toggle('is-active', p.getAttribute('data-ab-pane') === tab);
    });
  }

  /* --- Aperçu ----------------------------------------------- */
  function previewDoc() {
    var title = String(titleEl.value || '').trim();
    var subtitle = String(subtitleEl.value || '').trim();
    var content = currentContent();
    if (mode === 'visual' && !blocks.length) {
      content = '<p style="color:#9A8E85;text-align:center;">(Aucun bloc — le contenu est vide. Ajoutez des blocs pour composer l\'article.)</p>';
    }
    return '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">'
      + '<link rel="preconnect" href="https://fonts.googleapis.com">'
      + '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
      + '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">'
      + '<link rel="stylesheet" href="../css/style.css">'
      + '<style>'
      + 'html{-webkit-text-size-adjust:100%;}'
      + 'body{margin:0;background:#EEEBE6;padding:26px 16px;font-family:Inter,system-ui,sans-serif;}'
      + '.preview-card{max-width:760px;margin:0 auto;background:#fff;border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,.12);padding:48px 40px;}'
      + '.preview-head{text-align:center;margin-bottom:12px;}'
      + '</style></head><body><div class="preview-card">'
      + '<div class="preview-head"><h1 class="article-title">' + escHtml(title || 'Titre de l\'article') + '</h1>'
      + (subtitle ? '<p class="article-subtitle">' + escHtml(subtitle) + '</p>' : '')
      + '</div><div class="article-body">' + content + '</div></div></body></html>';
  }

  function refreshPreview() {
    if (!previewFrame) return;
    previewFrame.srcdoc = previewDoc();
  }

  function schedulePreview() {
    if (!previewOpen) return;
    if (previewTimer) clearTimeout(previewTimer);
    previewTimer = setTimeout(refreshPreview, 350);
  }

  function openPreview() {
    previewOpen = true;
    previewModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    refreshPreview();
  }

  function closePreview() {
    previewOpen = false;
    previewModal.style.display = 'none';
    document.body.style.overflow = '';
  }

  /* --- API publique (utilisée par index.php) ---------------- */
  function openNew() {
    ['blog-edit-id', 'blog-edit-title', 'blog-edit-subtitle', 'blog-edit-slug', 'blog-edit-image', 'blog-edit-video', 'blog-edit-excerpt']
      .forEach(function (id) { el(id).value = ''; });
    el('blog-edit-published').value = '1';
    if (globalCssEl) globalCssEl.value = '';
    codeEl.value = '';
    blocksHidden.value = '';
    /* Réinitialiser l'aperçu hero */
    if (typeof window.clearHeroMedia === 'function') window.clearHeroMedia();
    blocks = [makeBlock('section')];
    mode = 'visual';
    setTab('visual');
    renderAll();
    formWrap.style.display = 'block';
    formWrap.scrollIntoView({ behavior: 'smooth' });
  }

  function load(id, content, blocksJson) {
    var parsed = null;
    var s = String(blocksJson || '').trim();
    if (s && s !== 'null') {
      try { parsed = JSON.parse(s); } catch (e) { parsed = null; }
    }
    if (Array.isArray(parsed) && parsed.length) {
      blocks = parsed;
    } else {
      blocks = [];
    }
    mode = 'visual';
    setTab('visual');
    codeEl.value = content || '';
    blocksHidden.value = blocks.length ? JSON.stringify(blocks) : '';
    renderAll();
  }

  window.BlogBuilder = {
    openNew: openNew,
    load: load
  };

  /* --- Initialisation et évènements ------------------------- */
  function init() {
    document.querySelectorAll('.ab-add-btn').forEach(function (btn) {
      btn.addEventListener('click', function () { addBlock(btn.getAttribute('data-type')); });
    });

    document.querySelectorAll('.ab-tab').forEach(function (tab) {
      tab.addEventListener('click', function () { setTab(tab.getAttribute('data-ab-tab')); });
    });

    /* Toujours forcer le mode visuel — l'onglet code est masqué */
    mode = 'visual';
    setTab('visual');

    var previewOpenBtn = el('ab-preview-open');
    if (previewOpenBtn) previewOpenBtn.addEventListener('click', openPreview);
    var previewCloseBtn = el('ab-preview-close');
    if (previewCloseBtn) previewCloseBtn.addEventListener('click', closePreview);
    var previewRefreshBtn = el('ab-preview-refresh');
    if (previewRefreshBtn) previewRefreshBtn.addEventListener('click', refreshPreview);
    if (previewModal) previewModal.addEventListener('click', function (e) { if (e.target === previewModal) closePreview(); });

    if (globalCssEl) globalCssEl.addEventListener('input', schedulePreview);

    /* ── Upload AJAX pour les fichiers dans les blocs ── */
    blocksList.addEventListener('change', function (e) {
      var fileInput = e.target.closest('[data-ab-file-upload]');
      if (!fileInput) return;
      var file = fileInput.files[0];
      if (!file) return;
      var row = fileInput.closest('.ab-upload-row');
      var textInput = row ? row.querySelector('input[data-f]') : null;
      if (!textInput) return;
      /* Indiquer le chargement */
      var origPh = textInput.placeholder;
      textInput.placeholder = 'Upload en cours...';
      var fd = new FormData();
      fd.append('action', 'blog_media_upload');
      fd.append('blog_media_file', file);
      fd.append('blog_media_type', 'auto');
      fd.append('blog_media_target', 'block');
      fd.append('tab', 'blog');
      var csrfEl = document.querySelector('input[name="csrf_token"]');
      if (csrfEl) fd.append('csrf_token', csrfEl.value);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'actions.php', true);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.onload = function () {
        textInput.placeholder = origPh;
        try {
          var res = JSON.parse(xhr.responseText);
          if (res.ok && res.url) {
            textInput.value = res.url;
            textInput.dispatchEvent(new Event('input', { bubbles: true }));
          } else {
            alert(res.error || 'Erreur lors de l\'upload.');
          }
        } catch (ex) {
          alert('Erreur serveur.');
        }
      };
      xhr.onerror = function () {
        textInput.placeholder = origPh;
        alert('Erreur réseau.');
      };
      xhr.send(fd);
      fileInput.value = '';
    });

    if (editorForm) {
      editorForm.addEventListener('submit', function (e) {
        if (mode === 'visual' && !blocks.length) {
          e.preventDefault();
          alert('Ajoutez au moins un bloc (une section) avant d\'enregistrer.');
          return;
        }
        syncToForm();
        if (!String(codeEl.value || '').trim()) {
          e.preventDefault();
          alert('Le contenu de l\'article est vide. Ajoutez une section avant d\'enregistrer.');
        }
      });
    }

    blocksList.addEventListener('click', function (e) {
      var node = e.target.closest('.ab-block');
      if (!node) return;
      var idx = Array.prototype.indexOf.call(blocksList.children, node);
      if (idx < 0) return;

      if (e.target.closest('.ab-block-del')) {
        blocks.splice(idx, 1);
        renderAll();
        schedulePreview();
      } else if (e.target.closest('.ab-block-move-up')) {
        moveBlock(idx, -1);
      } else if (e.target.closest('.ab-block-move-down')) {
        moveBlock(idx, 1);
      } else if (e.target.closest('.ab-block-collapse')) {
        var body = node.querySelector('.ab-block-body');
        var icon = node.querySelector('.ab-block-collapse i');
        var collapsed = node.classList.toggle('is-collapsed');
        body.style.display = collapsed ? 'none' : '';
        if (icon) icon.className = collapsed ? 'ph ph-fill ph-caret-right' : 'ph ph-fill ph-caret-down';
      }
    });

    renderAll();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();