/*!
 * AI Article Generator — UI / editor.js
 * Hybrid V4/V5/V6 Edition
 */
(function (win, doc, $) {
  'use strict';
  if (win.__AIG_PRO__) return;
  win.__AIG_PRO__ = true;

  function getAjaxUrl() {
    return (win.AI_ARTICLE_EDITOR && win.AI_ARTICLE_EDITOR.ajax) || '';
  }

  function getNonces() {
    var g = win.AI_ARTICLE_EDITOR || {};
    var $root = $('#aig-root');
    if (!$root.length) {
      $root = $('[data-ai-root]').first();
    }

    return {
      media: (g.nonces && g.nonces.media) || $root.attr('data-nonce-media') || '',
      log: (g.nonces && g.nonces.log) || $root.attr('data-nonce-log') || '',
      main: (g.nonce) || $root.attr('data-nonce-main') || '',
      pipeline: $root.attr('data-nonce-pipeline') || '',
      rewrite: $root.attr('data-nonce-rewrite') || '',
      admin: (g.admin_nonce) || (g.nonces && g.nonces.admin) || $root.attr('data-nonce-admin') || ''
    };
  }

  function parseJson(txt) {
    try {
      return JSON.parse(txt);
    } catch (_) {
      var i = txt.indexOf('{');
      if (i > 0) txt = txt.slice(i);
      try {
        return JSON.parse(txt);
      } catch (e) {
        return null;
      }
    }
  }

  function safePost(action, payload, cb) {
    return new Promise(function (resolve) {
      $.ajax({
        url: getAjaxUrl(),
        method: 'POST',
        dataType: 'text',
        data: Object.assign({ action: action }, payload || {}),
        timeout: 180000
      }).done(function (resTxt) {
        var r = parseJson(resTxt);
        var out;

        if (r && r.success) {
          out = { ok: true, data: r.data };
        } else {
          out = {
            ok: false,
            data: (r && r.data) || { msg: 'Başarısız' },
            error: (r && r.data && (r.data.error || r.data.msg)) || 'Başarısız'
          };
        }

        try {
          if (typeof cb === 'function') cb(out);
        } catch (e) {}

        resolve(out);
      }).fail(function (xhr) {
        var out = {
          ok: false,
          data: {
            status: xhr.status,
            msg: 'network_error'
          }
        };

        try {
          if (typeof cb === 'function') cb(out);
        } catch (e) {}

        resolve(out);
      });
    });
  }

  function htmlEscape(s) {
    return String(s || '').replace(/[&<>"]/g, function (c) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c];
    });
  }

  function pretty(obj) {
    try {
      return JSON.stringify(obj, null, 2);
    } catch (e) {
      return String(obj);
    }
  }

  /* ---------------------------------------------------------
   * LOG PANEL
   * --------------------------------------------------------- */
  function colorize(line) {
    if (/ERROR|⛔/i.test(line)) return '<span style="color:#f66;">' + line + '</span>';
    if (/WARN|⚠️/i.test(line)) return '<span style="color:#e8b34b;">' + line + '</span>';
    if (/SUCCESS|✅/i.test(line)) return '<span style="color:#6f6;">' + line + '</span>';
    return '<span style="color:#9cf;">' + line + '</span>';
  }

  function initLogBox() {
    var $box = $('#ai-log-box');
    if (!$box.length) return;

    var $r = $('#ai-log-refresh'),
      $c = $('#ai-log-clear'),
      $a = $('#ai-log-auto'),
      $st = $('#ai-log-status');

    var timer = null;

    function status(t) { $st.text(t); }

    function refresh() {
      var n = getNonces();
      safePost('ai_article_log_tail', { nonce: n.log, max: 80 }).then(function (r) {
        if (!r.ok) {
          status('⛔');
          return;
        }

        var lines = Array.isArray(r.data && r.data.data) ? r.data.data : (r.data || []);
        if (!lines.length) {
          $box.html('<em style="color:#777;">Henüz log yok.</em>');
          return;
        }

        $box.html(lines.reverse().map(colorize).join('<br>'));
        $box.scrollTop($box.prop('scrollHeight'));
        status('✅');
      });
    }

    function clear() {
      var n = getNonces();
      safePost('ai_article_log_clear', { nonce: n.log }).then(function () {
        $box.text('');
      });
    }

    $r.off('click').on('click', refresh);
    $c.off('click').on('click', clear);
    $a.off('change').on('change', function () {
      if (this.checked) {
        refresh();
        timer = setInterval(refresh, 5000);
      } else {
        clearInterval(timer);
      }
    });

    refresh();
  }

  /* ---------------------------------------------------------
   * PEXELS MEDIA SEARCH
   * --------------------------------------------------------- */
  function cardHTML(item) {
    var isImage = item.type === 'image';
    var title = item.photographer || item.user || '';
    var meta = [];

    if (item.width) meta.push(item.width + 'x' + (item.height || '?'));
    if (item.duration) meta.push(item.duration + 's');

    var src = item.file_url || (item.src && (item.src.medium || item.src.original)) || '';
    var media = isImage
      ? '<img src="' + src + '" alt="" style="width:100%;border-radius:10px;">'
      : '<video src="' + src + '" controls style="width:100%;border-radius:10px;"></video>';

    return '<div class="aig-card" style="border:1px solid #ddd;border-radius:12px;padding:8px;background:#fff;">'
      + media
      + '<div style="font-size:12px;color:#333;margin-top:4px;">' + htmlEscape(title) + '</div>'
      + '<div style="font-size:11px;color:#666;">' + htmlEscape(meta.join(' • ')) + '</div>'
      + '</div>';
  }

  function initMediaSearch() {
    var $btn = $('#ai-media-fetch'),
      $probe = $('#ai-media-probe'),
      $res = $('#ai-media-results'),
      $q = $('#ai-media-q'),
      $type = $('#ai-media-type'),
      $count = $('#ai-media-count'),
      $size = $('#ai-media-size');

    $probe.off('click').on('click', function () {
      var n = getNonces();
      var $self = $(this);

      $self.prop('disabled', true).text('⏳ Test...');

      safePost('ai_media_probe', { nonce: n.media }).then(function (r) {
        $self.prop('disabled', false).text('🔍 Anahtarı Doğrula');
        alert(r.ok ? '✅ API anahtarı geçerli.' : '⛔ ' + ((r.data && r.data.msg) || 'Hata'));
      });
    });

    $btn.off('click').on('click', function () {
      var n = getNonces();

      var payload = {
        q: $q.val() || 'news',
        type: $type.val() || 'image',
        count: parseInt($count.val() || 6, 10),
        size: $size.val() || 'medium',
        nonce: n.media
      };

      $btn.prop('disabled', true).text('⏳ Aranıyor...');
      $res.html('<div>⏳ Pexels aranıyor...</div>');

      safePost('ai_media_fetch', payload).then(function (r) {
        $btn.prop('disabled', false).text('Ara');

        if (!r.ok) {
          $res.html('<div style="color:red;">⛔ ' + (((r.data && r.data.msg) || 'Hata')) + '</div>');
          return;
        }

        var items = Array.isArray(r.data && r.data.items) ? r.data.items : (r.data || []);
        if (!items.length) {
          $res.html('<div>⚠️ Sonuç yok.</div>');
          return;
        }

        var html = items.map(cardHTML).join('');
        $res.html('<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">' + html + '</div>');
      });
    });
  }

  /* ---------------------------------------------------------
   * DIAGNOSTIC PANEL
   * --------------------------------------------------------- */
  function initDiagnostics() {
    var $panel = $('[data-ai-root]').first();
    if (!$panel.length || $('#aig-diagnostics').length) return;

    var html = ''
      + '<div id="aig-diagnostics" class="postbox" style="margin:10px 0;">'
      + '  <h2 class="hndle"><span>⚙️ Diagnostik</span></h2>'
      + '  <div class="inside">'
      + '    <p>'
      + '      <button id="aig-ping" class="button">Ping</button>'
      + '      <button id="aig-nonce" class="button">Nonce Test</button>'
      + '      <button id="aig-probe" class="button">Probe Test</button>'
      + '    </p>'
      + '    <pre id="aig-diag-log" style="background:#f6f8fa;padding:6px;border:1px solid #ddd;border-radius:6px;height:120px;overflow:auto;font-size:12px;"></pre>'
      + '  </div>'
      + '</div>';

    $panel.before(html);

    var $out = $('#aig-diag-log');

    function log(m, c) {
      $out.append('<div style="color:' + (c || '#000') + '">' + htmlEscape(m) + '</div>')
        .scrollTop($out.prop('scrollHeight'));
    }

    $('#aig-ping').off('click').on('click', function () {
      safePost('ai_diag_ping', {}).then(function (r) {
        log(pretty(r), r.ok ? 'green' : 'red');
      });
    });

    $('#aig-nonce').off('click').on('click', function () {
      log(pretty(getNonces()), '#555');
    });

    $('#aig-probe').off('click').on('click', function () {
      var n = getNonces();
      safePost('ai_media_probe', { nonce: n.media }).then(function (r) {
        log(pretty(r), r.ok ? 'green' : 'red');
      });
    });
  }

  /* ---------------------------------------------------------
   * API KEYS (PEXELS)
   * --------------------------------------------------------- */
  function initApiKeys() {
    var $btn = $('#aig-key-save');
    if (!$btn.length) return;

    $btn.off('click').on('click', function () {
      var key = ($('#aig_pexels').val() || '').trim();
      if (!key) {
        alert('⚠️ Anahtar boş olamaz.');
        return;
      }

      $btn.prop('disabled', true).text('Kaydediliyor…');

      safePost('ai_save_pexels_key', { key: key }).then(function (r) {
        $btn.prop('disabled', false).text('Anahtarı Kaydet');

        if (r.ok) {
          alert('✅ Anahtar kaydedildi.');
          try { $('#ai-log-refresh').trigger('click'); } catch (e) {}
        } else {
          alert('⛔ Kaydedilemedi: ' + (((r.data && (r.data.msg || r.data.message)) || 'Hata')));
        }
      });
    });
  }

  /* ---------------------------------------------------------
   * LLM Provider
   * --------------------------------------------------------- */
  function initLLMProvider() {
    var $st = $('#aig-llm-status');
    var $out = $('#aig-llm-out');
    if (!$('#aig-llm-load').length) return;

    function show(obj) {
      try { $out.text(JSON.stringify(obj, null, 2)); } catch (e) { $out.text(String(obj)); }
    }

    function optionHtml(value, label) {
      return '<option value="' + String(value || '').replace(/"/g, '&quot;') + '">' + String(label || value || '') + '</option>';
    }

    function fillProviders(providers, selected) {
      var html = '';
      Object.keys(providers || {}).forEach(function (id) {
        var p = providers[id] || {};
        var label = (p.name || id) + (p.enabled ? '' : ' (pasif)');
        html += optionHtml(id, label);
      });
      $('#aig-llm-provider').html(html).val(selected || $('#aig-llm-provider option:first').val() || '');
    }

    function fillPresets(presets, selected) {
      var html = '';
      (presets || []).forEach(function (p) {
        html += optionHtml(p.id, p.label || p.id);
      });
      $('#aig-llm-preset').html(html).val(selected || $('#aig-llm-preset option:first').val() || '');
    }

    function fillModels(modelsByProvider, providerId, selectedModelId) {
      var items = (modelsByProvider && modelsByProvider[providerId]) ? modelsByProvider[providerId] : [];
      var html = '';

      items.forEach(function (m) {
        var suffix = [];
        if (m.tier) suffix.push(m.tier);
        if (m.quality) suffix.push('q:' + m.quality);
        if (m.speed) suffix.push('s:' + m.speed);

        html += optionHtml(
          m.id,
          (m.name || m.model || m.id) + (suffix.length ? ' — ' + suffix.join(', ') : '')
        );
      });

      if (!html) html = optionHtml('', 'Model bulunamadı');

      $('#aig-llm-model-id').html(html).val(selectedModelId || $('#aig-llm-model-id option:first').val() || '');
    }

    function syncEndpointFromProvider(state) {
      var providerId = $('#aig-llm-provider').val() || '';
      var providers = (state && state.providers) || {};
      var p = providers[providerId] || {};

      if (!$('#aig-llm-endpoint').val()) $('#aig-llm-endpoint').val(p.endpoint || '');
      if (!$('#aig-llm-timeout').val()) $('#aig-llm-timeout').val(p.timeout || 60);
      $('#aig-llm-priority').val(typeof p.priority !== 'undefined' ? p.priority : ($('#aig-llm-priority').val() || 100));
    }

    function applyState(state) {
      state = state || {};
      var current = state.provider || {};

      fillProviders(state.providers || {}, current.provider || '');
      fillPresets(state.presets || [], current.preset || 'free_first');
      fillModels(state.models || {}, current.provider || ($('#aig-llm-provider').val() || ''), current.model_id || '');

      $('#aig-llm-enabled').prop('checked', !!current.enabled);
      $('#aig-llm-endpoint').val(current.endpoint || '');
      $('#aig-llm-timeout').val(current.timeout || 60);
      $('#aig-llm-temperature').val(typeof current.temperature !== 'undefined' ? current.temperature : 0.7);
      $('#aig-llm-max-tokens').val(current.max_tokens || 1200);
      $('#aig-llm-priority').val(typeof current.priority !== 'undefined' ? current.priority : 100);

      syncEndpointFromProvider(state);
      show(state);
    }

    $('#aig-llm-provider').off('change').on('change', function () {
      var state = $out.data('panelState') || {};
      fillModels(state.models || {}, $(this).val() || '', '');
      $('#aig-llm-endpoint').val((((state.providers || {})[$(this).val()] || {}).endpoint) || '');
      $('#aig-llm-timeout').val((((state.providers || {})[$(this).val()] || {}).timeout) || 60);
      $('#aig-llm-priority').val((((state.providers || {})[$(this).val()] || {}).priority) || 100);
    });

    $('#aig-llm-load').off('click').on('click', function () {
      $st.text('yükleniyor…');
      safePost('ai_llm_get', {
        _ajax_nonce: getNonces().main || ''
      }, function (r) {
        if (r && r.ok && r.data) {
          $out.data('panelState', r.data);
          applyState(r.data);
          $st.text('ok');
        } else {
          show(r);
          $st.text('error');
        }
      });
    });

    $('#aig-llm-save').off('click').on('click', function () {
      $st.text('kaydediliyor…');

      var state = $out.data('panelState') || {};
      var providerId = $('#aig-llm-provider').val() || '';
      var modelId = $('#aig-llm-model-id').val() || '';
      var modelMap = ((state.models || {})[providerId] || []).find(function (m) { return m.id === modelId; }) || {};

      safePost('ai_llm_save', {
        _ajax_nonce: getNonces().main || '',
        enabled: $('#aig-llm-enabled').is(':checked') ? 1 : 0,
        provider: providerId,
        endpoint: $('#aig-llm-endpoint').val() || '',
        model_id: modelId,
        model: modelMap.model || '',
        preset: $('#aig-llm-preset').val() || 'free_first',
        api_key: $('#aig-llm-key').val() || '',
        priority: $('#aig-llm-priority').val() || 100,
        timeout: $('#aig-llm-timeout').val() || 60,
        temperature: $('#aig-llm-temperature').val() || 0.7,
        max_tokens: $('#aig-llm-max-tokens').val() || 1200
      }, function (r) {
        if (r && r.ok && r.data && r.data.state) {
          $out.data('panelState', r.data.state);
          applyState(r.data.state);
        } else {
          show(r);
        }

        $st.text((r && r.ok) ? 'ok' : 'error');
        $('#aig-llm-key').val('');
      });
    });

    $('#aig-llm-test').off('click').on('click', function () {
      $st.text('test…');

      var state = $out.data('panelState') || {};
      var providerId = $('#aig-llm-provider').val() || '';
      var modelId = $('#aig-llm-model-id').val() || '';
      var modelMap = ((state.models || {})[providerId] || []).find(function (m) { return m.id === modelId; }) || {};

      safePost('ai_llm_test', {
        _ajax_nonce: getNonces().main || '',
        provider: providerId,
        model_id: modelId,
        model: modelMap.model || '',
        endpoint: $('#aig-llm-endpoint').val() || '',
        timeout: $('#aig-llm-timeout').val() || 60,
        temperature: $('#aig-llm-temperature').val() || 0.7,
        max_tokens: $('#aig-llm-max-tokens').val() || 1600
      }, function (r) {
        show(r);
        $st.text((r && r.ok) ? 'ok' : 'error');
      });
    });

    $('#aig-llm-load').trigger('click');
  }

  /* ---------------------------------------------------------
   * ARTICLE GENERATOR — V6 Hybrid
   * --------------------------------------------------------- */
  function initGenerator() {
    var $out = $('#aig-gen-output');
    var $sum = $('#aig-gen-summary');
    var $fact = $('#aig-fact-pack-output');
    var $sources = $('#aig-sources-output');
    var $seo = $('#aig-seo-output');
    var $st = $('#aig-gen-status');

    function setStatus(txt) {
      $st.text(txt || '');
    }

    function buildPayload(mode) {
      mode = mode || 'preview';

      return {
        nonce: getNonces().admin || getNonces().main || getNonces().pipeline || '',
        _ajax_nonce: getNonces().pipeline || getNonces().main || getNonces().admin || '',
        topic: $('#aig-gen-topic').val() || '',
        keyword: $('#aig-gen-keyword').val() || '',
        category: $('#aig-gen-category').val() || 'tech',
        news_range: $('#aig-gen-range').val() || '24h',
        source_limit: $('#aig-gen-source-limit').val() || 10,
        language: $('#aig-gen-lang').val() || 'tr',
        lang: $('#aig-gen-lang').val() || 'tr',
        tone: $('#aig-gen-tone').val() || 'professional',
        length: $('#aig-gen-length').val() || 'long',
        template: $('#aig-gen-template').val() || 'news_basic',
        provider: $('#aig-gen-provider').val() || 'auto',
        model: $('#aig-gen-model').val() || 'auto',
        brief: $('#aig-gen-brief').val() || '',
        min_quality: $('#aig-gen-minq').val() || 60,
        auto_improve: $('#aig-gen-auto').is(':checked') ? 1 : 0,
        max_attempts: $('#aig-gen-attempts').val() || 3,
        similarity_guard: $('#aig-gen-simguard').is(':checked') ? 1 : 0,
        similarity_threshold: $('#aig-gen-simthr').val() || 0.80,
        save_mode: mode,
        save: mode === 'preview' ? 0 : 1,
        post_status: mode === 'publish' ? 'publish' : 'draft'
      };
    }

    function renderSummary(data) {
      if (!$sum.length) return;

      var html = '';
      html += '<div><strong>Başlık:</strong> ' + htmlEscape(data.title || '') + '</div>';
      html += '<div><strong>Kategori:</strong> ' + htmlEscape(data.category || '') + '</div>';
      html += '<div><strong>Kalite:</strong> ' + htmlEscape(data.quality_score || 0) + '</div>';
      html += '<div><strong>Özet:</strong> ' + htmlEscape(data.summary || '') + '</div>';

      if (data.save_result && data.save_result.ok) {
        html += '<div><strong>Kayıt:</strong> başarılı</div>';
      }

      $sum.html(html);
    }

    function renderMainContent(data) {
      if (!$out.length) return;

      var html = '';
      html += '<h2 style="margin-top:0;">' + htmlEscape(data.title || '') + '</h2>';
      html += '<div>' + (data.content || '<em>İçerik boş</em>') + '</div>';

      $out.html(html);
    }

    function renderFactPack(factPack) {
      if (!$fact.length) return;

      if (!factPack || !factPack.headlines || !factPack.headlines.length) {
        $fact.html('<em>Fact pack verisi yok.</em>');
        return;
      }

      var html = '';

      if (factPack.key_points && factPack.key_points.length) {
        html += '<h3 style="margin-top:0;">Ana Noktalar</h3><ul>';
        factPack.key_points.forEach(function (item) {
          html += '<li>' + htmlEscape(item) + '</li>';
        });
        html += '</ul>';
      }

      html += '<h3>Başlıklar</h3><ul>';
      factPack.headlines.forEach(function (item) {
        html += '<li style="margin-bottom:10px;">';
        html += '<strong>' + htmlEscape(item.title || '') + '</strong><br>';
        html += '<span style="color:#666;">' + htmlEscape(item.source || '') + '</span><br>';
        html += '<span>' + htmlEscape(item.summary || '') + '</span>';
        html += '</li>';
      });
      html += '</ul>';

      $fact.html(html);
    }

    function renderSources(sources) {
      if (!$sources.length) return;

      if (!sources || !sources.length) {
        $sources.html('<em>Kaynak verisi yok.</em>');
        return;
      }

      var html = '<ul>';
      sources.forEach(function (item) {
        var name = item.name || item.source || 'Kaynak';
        var url = item.url || '';

        if (url) {
          html += '<li><a href="' + htmlEscape(url) + '" target="_blank" rel="noopener noreferrer">' + htmlEscape(name) + '</a></li>';
        } else {
          html += '<li>' + htmlEscape(name) + '</li>';
        }
      });
      html += '</ul>';

      $sources.html(html);
    }

    function renderSeo(seo) {
      if (!$seo.length) return;

      if (!seo || typeof seo !== 'object') {
        $seo.html('<em>SEO verisi yok.</em>');
        return;
      }

      var html = '';
      html += '<div><strong>Meta Title:</strong> ' + htmlEscape(seo.meta_title || '') + '</div>';
      html += '<div><strong>Meta Description:</strong> ' + htmlEscape(seo.meta_description || '') + '</div>';

      if (seo.faq && seo.faq.length) {
        html += '<h3>FAQ</h3><ul>';
        seo.faq.forEach(function (row) {
          html += '<li><strong>' + htmlEscape(row.question || '') + '</strong><br>' + htmlEscape(row.answer || '') + '</li>';
        });
        html += '</ul>';
      }

      $seo.html(html);
    }

    function renderLegacy(res) {
      var article = res && res.article ? res.article : null;
      if (!article) {
        $out.html('<em>Çıktı yok</em>');
        return;
      }

      var html = '';
      html += '<h2 style="margin-top:0;">' + htmlEscape(article.title || '') + '</h2>';

      if (article.quality) {
        html += '<p><b>Quality:</b> ' + htmlEscape(article.quality.score) + ' / 100'
          + ' • <b>Attempt:</b> ' + htmlEscape(article.attempt) + '</p>';
      }

      if (article.similarity) {
        html += '<p><b>Similarity best:</b> ' + htmlEscape(article.similarity.best) + ' • <b>Index:</b> ' + htmlEscape(article.similarity.index_size) + '</p>';
      }

      (article.sections || []).forEach(function (s) {
        html += '<h3>' + htmlEscape(s.h2 || '') + '</h3>';
        html += '<div>' + (s.content || '') + '</div>';
      });

      if (article.wp && article.wp.ok) {
        html += '<p><b>WP:</b> post_id=' + htmlEscape(article.wp.post_id) + '</p>';
      }

      $out.html(html);
      $sum.html('<div><strong>Başlık:</strong> ' + htmlEscape(article.title || '') + '</div>');
      $fact.html('<em>Legacy response: fact pack yok.</em>');
      $sources.html('<em>Legacy response: sources yok.</em>');
      $seo.html('<em>Legacy response: seo yok.</em>');
    }

    function handleResult(r) {
      if (!r.ok) {
        setStatus('hata');
        $out.html('<pre>' + htmlEscape(pretty(r.data || r)) + '</pre>');
        return;
      }

      var d = r.data || {};

      // Yeni V6 endpoint formatı:
      // { ok:true, data:{ title, content, summary, fact_pack, sources, seo... } }
      if (d && d.ok && d.data) {
        renderSummary(d.data);
        renderMainContent(d.data);
        renderFactPack(d.data.fact_pack || {});
        renderSources(d.data.sources || []);
        renderSeo(d.data.seo || {});
        setStatus('ok');
        return;
      }

      // Bazı durumlarda service iç data direkt gelebilir
      if (d && d.title && (d.content || d.summary)) {
        renderSummary(d);
        renderMainContent(d);
        renderFactPack(d.fact_pack || {});
        renderSources(d.sources || []);
        renderSeo(d.seo || {});
        setStatus('ok');
        return;
      }

      // Eski pipeline response fallback
      if (d && d.article) {
        renderLegacy(d);
        setStatus('ok');
        return;
      }

      $out.html('<pre>' + htmlEscape(pretty(d)) + '</pre>');
      setStatus('ok');
    }

    function requestGenerate(mode) {
      mode = mode || 'preview';
      setStatus(mode === 'publish' ? 'yayınlanıyor…' : (mode === 'draft' ? 'kaydediliyor…' : 'çalışıyor…'));

      var payload = buildPayload(mode);

      // Önce yeni endpoint
      safePost('aig_generate_article', payload).then(function (r) {
        if (r.ok) {
          handleResult(r);
          return;
        }

        // fallback eski endpoint
        safePost('ai_article_pipeline_generate', payload).then(function (legacy) {
          handleResult(legacy);
        });
      });
    }

    $('#aig-gen-run').off('click').on('click', function () {
      requestGenerate('preview');
    });

    $('#aig-gen-save').off('click').on('click', function () {
      requestGenerate('draft');
    });

    $('#aig-gen-publish').off('click').on('click', function () {
      requestGenerate('publish');
    });
  }

  /* ---------------------------------------------------------
   * REWRITE STUDIO
   * --------------------------------------------------------- */
  function initRewrite() {
    var $out = $('#aig-rewrite-output');
    var $st = $('#aig-rewrite-status');

    $('#aig-rewrite-run').off('click').on('click', function () {
      $st.text('çalışıyor…');

      safePost('ai_article_rewrite', {
        _ajax_nonce: getNonces().rewrite || getNonces().main || getNonces().admin || '',
        text: $('#aig-rewrite-text').val() || '',
        instruction: $('#aig-rewrite-instruction').val() || 'rewrite',
        tone: $('#aig-gen-tone').val() || 'neutral',
        lang: $('#aig-gen-lang').val() || 'tr',
        model: 'auto'
      }).then(function (r) {
        if (r.ok && r.data) {
          var html = r.data.html || r.data.content || '';
          $out.html(html || '<em>Boş çıktı</em>');
          $st.text('ok');
        } else {
          $st.text('hata');
          $out.html('<pre>' + htmlEscape((r.data && (r.data.error || r.data.msg)) || r.error || 'error') + '</pre>');
        }
      });
    });
  }

  /* ---------------------------------------------------------
   * TEMPLATES
   * --------------------------------------------------------- */
  function initTemplates() {
    var $st = $('#aig-tpl-status');
    var $list = $('#aig-tpl-list');
    var $json = $('#aig-tpl-json');

    function noncePayload(extra) {
      return Object.assign({
        _ajax_nonce: getNonces().main || getNonces().pipeline || ''
      }, extra || {});
    }

    $('#aig-tpl-refresh').off('click').on('click', function () {
      $st.text('yükleniyor…');

      safePost('ai_article_templates_list', noncePayload()).then(function (r) {
        if (r.ok && r.data && r.data.templates) {
          var keys = Object.keys(r.data.templates);
          $list.text('Toplam: ' + keys.length + '\n' + keys.sort().join('\n'));
          $st.text('ok');
        } else {
          $st.text('hata');
        }
      });
    });

    $('#aig-tpl-export').off('click').on('click', function () {
      $st.text('export…');

      safePost('ai_article_templates_export', noncePayload()).then(function (r) {
        if (r.ok && r.data && r.data.templates) {
          $json.val(JSON.stringify(r.data.templates, null, 2));
          $st.text('ok');
        } else {
          $st.text('hata');
        }
      });
    });

    $('#aig-tpl-import').off('click').on('click', function () {
      $st.text('import…');

      safePost('ai_article_templates_import', noncePayload({
        json: $json.val() || ''
      })).then(function (r) {
        if (r.ok && r.data && r.data.ok) {
          $st.text('ok (' + (r.data.count || 0) + ')');
        } else {
          $st.text('hata');
        }
      });
    });
  }

  /* ---------------------------------------------------------
   * USAGE
   * --------------------------------------------------------- */
  function initUsage() {
    var $st = $('#aig-usage-status');
    var $out = $('#aig-usage-out');

    $('#aig-usage-refresh').off('click').on('click', function () {
      $st.text('yükleniyor…');

      safePost('ai_article_usage_totals', {
        _ajax_nonce: getNonces().main || getNonces().pipeline || ''
      }).then(function (r) {
        if (r.ok) {
          $out.text(pretty(r.data));
          $st.text('ok');
        } else {
          $st.text('hata');
        }
      });
    });
  }

  /* ---------------------------------------------------------
   * SELFTEST
   * --------------------------------------------------------- */
  function initSelftest() {
    var $st = $('#aig-selftest-status');
    var $out = $('#aig-selftest-out');

    $('#aig-selftest-run').off('click').on('click', function () {
      $st.text('çalışıyor…');

      var payload = {
        nonce: getNonces().admin || getNonces().main || '',
        _ajax_nonce: getNonces().admin || getNonces().main || ''
      };

      // Önce yeni endpoint
      safePost('aig_selftest', payload).then(function (r) {
        if (r.ok) {
          $out.text(pretty(r.data));
          $st.text('ok');
          return;
        }

        // fallback eski endpoint
        safePost('ai_article_selftest', {
          _ajax_nonce: getNonces().main || getNonces().pipeline || ''
        }).then(function (legacy) {
          if (legacy.ok) {
            $out.text(pretty(legacy.data));
            $st.text('ok');
          } else {
            $out.text(pretty(legacy.data || legacy));
            $st.text('hata');
          }
        });
      });
    });
  }

  /* ---------------------------------------------------------
   * STARTUP
   * --------------------------------------------------------- */
  $(function () {
    console.log('✅ AI Article Generator — Hybrid UI Loaded');
    initDiagnostics();
    initMediaSearch();
    initLogBox();
    initApiKeys();
    initLLMProvider();
    initGenerator();
    initRewrite();
    initTemplates();
    initUsage();
    initSelftest();
  });

})(window, document, jQuery);