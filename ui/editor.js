/*!
 * AI Article Generator — UI / editor.js (Pro Edition)
 * Tanrısal Sürüm: Diagnostik + Grid + Log Renkleri
 */
(function (win, doc, $) {
  'use strict';
  if (win.__AIG_PRO__) return;
  win.__AIG_PRO__ = true;

  function getAjaxUrl() { return (win.AI_ARTICLE_EDITOR && win.AI_ARTICLE_EDITOR.ajax) || ''; }
  function getNonces() {
    var g = win.AI_ARTICLE_EDITOR || {};
    var $root = $('[data-ai-root]').first();
    return {
      media: (g.nonces && g.nonces.media) || $root.attr('data-nonce-media') || '',
      log: (g.nonces && g.nonces.log) || $root.attr('data-nonce-log') || ''
    };
  }

  function parseJson(txt) {
    try { return JSON.parse(txt); } catch (_) {
      var i = txt.indexOf('{'); if (i > 0) txt = txt.slice(i);
      try { return JSON.parse(txt); } catch (e) { return null; }
    }
  }

  function safePost(action, payload) {
    return new Promise(resolve => {
      $.ajax({
        url: getAjaxUrl(), method: 'POST', dataType: 'text',
        data: Object.assign({ action }, payload || {}), timeout: 20000
      }).done(resTxt => {
        var r = parseJson(resTxt);
        if (r && r.success) resolve({ ok: true, data: r.data });
        else resolve({ ok: false, data: (r && r.data) || { msg: 'Başarısız' } });
      }).fail(xhr => resolve({ ok: false, data: { status: xhr.status, msg: 'network_error' } }));
    });
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
    var $r = $('#ai-log-refresh'), $c = $('#ai-log-clear'),
        $a = $('#ai-log-auto'), $st = $('#ai-log-status');
    var timer = null;

    function status(t) { $st.text(t); }

    function refresh() {
      var n = getNonces();
      safePost('ai_article_log_tail', { nonce: n.log, max: 80 }).then(r => {
        if (!r.ok) { status('⛔'); return; }
        var lines = Array.isArray(r.data?.data) ? r.data.data : (r.data || []);
        if (!lines.length) { $box.html('<em style="color:#777;">Henüz log yok.</em>'); return; }
        $box.html(lines.reverse().map(colorize).join('<br>'));
        $box.scrollTop($box.prop('scrollHeight')); status('✅');
      });
    }

    function clear() {
      var n = getNonces();
      safePost('ai_article_log_clear', { nonce: n.log }).then(() => { $box.text(''); });
    }

    $r.on('click', refresh); $c.on('click', clear);
    $a.on('change', function () {
      if (this.checked) { refresh(); timer = setInterval(refresh, 5000); }
      else { clearInterval(timer); }
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
    var src = item.file_url || item.src?.medium || item.src?.original;
    var media = isImage
      ? `<img src="${src}" alt="" style="width:100%;border-radius:10px;">`
      : `<video src="${src}" controls style="width:100%;border-radius:10px;"></video>`;
    return `<div class="aig-card" style="border:1px solid #ddd;border-radius:12px;padding:8px;background:#fff;">
      ${media}
      <div style="font-size:12px;color:#333;margin-top:4px;">${title}</div>
      <div style="font-size:11px;color:#666;">${meta.join(' • ')}</div>
    </div>`;
  }

  function initMediaSearch() {
    var $btn = $('#ai-media-fetch'),
        $probe = $('#ai-media-probe'),
        $res = $('#ai-media-results'),
        $q = $('#ai-media-q'),
        $type = $('#ai-media-type'),
        $count = $('#ai-media-count'),
        $size = $('#ai-media-size');

    $probe.on('click', function () {
      var n = getNonces();
      $(this).prop('disabled', true).text('⏳ Test...');
      safePost('ai_media_probe', { nonce: n.media }).then(r => {
        $(this).prop('disabled', false).text('🔍 Anahtarı Doğrula');
        alert(r.ok ? '✅ API anahtarı geçerli.' : '⛔ ' + (r.data.msg || 'Hata'));
      });
    });

    $btn.on('click', function () {
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
      safePost('ai_media_fetch', payload).then(r => {
        $btn.prop('disabled', false).text('Ara');
        if (!r.ok) { $res.html('<div style="color:red;">⛔ ' + (r.data.msg || 'Hata') + '</div>'); return; }
        var items = Array.isArray(r.data?.items) ? r.data.items : (r.data || []);
        if (!items.length) { $res.html('<div>⚠️ Sonuç yok.</div>'); return; }
        var html = items.map(cardHTML).join('');
        $res.html('<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">' + html + '</div>');
      });
    });
  }

  /* ---------------------------------------------------------
   * DIAGNOSTIC PANEL
   * --------------------------------------------------------- */
  function initDiagnostics() {
    // ✅ Diagnostik panel sadece AI Article Generator kutusuna eklensin
const $panel = $('[data-ai-root]').first();
    if (!$panel.length || $('#aig-diagnostics').length) return;

    const html = `
      <div id="aig-diagnostics" class="postbox" style="margin:10px 0;">
        <h2 class="hndle"><span>⚙️ Diagnostik</span></h2>
        <div class="inside">
          <p>
            <button id="aig-ping" class="button">Ping</button>
            <button id="aig-nonce" class="button">Nonce Test</button>
            <button id="aig-probe" class="button">Probe Test</button>
          </p>
          <pre id="aig-diag-log" style="background:#f6f8fa;padding:6px;border:1px solid #ddd;border-radius:6px;height:120px;overflow:auto;font-size:12px;"></pre>
        </div>
      </div>`;
    
    // 🔥 Sadece "Makale Üretici" kutusunun hemen ÜSTÜNE ekle
    $panel.before(html);

    // Event bağlama
    const $out = $('#aig-diag-log');
    function log(m, c) {
      $out.append(`<div style="color:${c || '#000'}">${m}</div>`)
          .scrollTop($out.prop('scrollHeight'));
    }

    $('#aig-ping').off('click').on('click', () => {
      safePost('ai_diag_ping', {}).then(r => log(JSON.stringify(r), r.ok ? 'green' : 'red'));
    });
    $('#aig-nonce').off('click').on('click', () => {
      log(JSON.stringify(getNonces()), '#555');
    });
    $('#aig-probe').off('click').on('click', () => {
      const n = getNonces();
      safePost('ai_media_probe', { nonce: n.media })
        .then(r => log(JSON.stringify(r), r.ok ? 'green' : 'red'));
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
      if (!key) { alert('⚠️ Anahtar boş olamaz.'); return; }

      $btn.prop('disabled', true).text('Kaydediliyor…');
      safePost('ai_save_pexels_key', { key: key }).then(function (r) {
        $btn.prop('disabled', false).text('Anahtarı Kaydet');
        if (r.ok) {
          alert('✅ Anahtar kaydedildi.');
          // log paneli açıksa yenile
          try { $('#ai-log-refresh').trigger('click'); } catch(e) {}
        } else {
          alert('⛔ Kaydedilemedi: ' + ((r.data && (r.data.msg || r.data.message)) || 'Hata'));
        }
      });
    });
  }


  /* ---------------------------------------------------------
   * V4: Article Generator (Pipeline)
   * --------------------------------------------------------- */
  function htmlEscape(s){ return String(s||'').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

  function initGenerator(){
    var $out = $('#aig-gen-output');
    var $st  = $('#aig-gen-status');

    function payload(save){
      return {
        _ajax_nonce: (win.AI_ARTICLE_EDITOR && win.AI_ARTICLE_EDITOR.nonce) || '',
        topic: $('#aig-gen-topic').val() || '',
        keyword: $('#aig-gen-keyword').val() || '',
        lang: $('#aig-gen-lang').val() || 'tr',
        tone: $('#aig-gen-tone').val() || 'neutral',
        template: $('#aig-gen-template').val() || 'news_basic',
        min_quality: $('#aig-gen-minq').val() || 60,
        auto_improve: $('#aig-gen-auto').is(':checked') ? 1 : 0,
        max_attempts: $('#aig-gen-attempts').val() || 3,
        similarity_guard: $('#aig-gen-simguard').is(':checked') ? 1 : 0,
        similarity_threshold: $('#aig-gen-simthr').val() || 0.80,
        save: save ? 1 : 0,
        post_status: 'draft'
      };
    }

    function render(res){
      if (!res || !res.article) { $out.html('<em>Çıktı yok</em>'); return; }
      var a = res.article;
      var html = '';
      html += '<h2 style="margin-top:0;">' + htmlEscape(a.title || '') + '</h2>';
      if (a.quality) {
        html += '<p><b>Quality:</b> ' + htmlEscape(a.quality.score) + ' / 100'
          + ' • <b>Attempt:</b> ' + htmlEscape(a.attempt) + '</p>';
      }
      if (a.similarity) {
        html += '<p><b>Similarity best:</b> ' + htmlEscape(a.similarity.best) + ' • <b>Index:</b> ' + htmlEscape(a.similarity.index_size) + '</p>';
      }
      (a.sections||[]).forEach(function(s){
        html += '<h3>' + htmlEscape(s.h2||'') + '</h3>';
        html += '<div>' + (s.content||'') + '</div>';
      });
      if (a.wp && a.wp.ok) {
        html += '<p><b>WP:</b> post_id=' + htmlEscape(a.wp.post_id) + '</p>';
      }
      $out.html(html);
    }

    $('#aig-gen-run').on('click', function(){
      $st.text('çalışıyor…');
      safePost('ai_article_pipeline_generate', payload(false)).then(function(r){
        if (r.ok) { render(r.data); $st.text('ok'); }
        else { $st.text('hata'); $out.html('<pre>'+htmlEscape(r.error||'error')+'</pre>'); }
      });
    });

    $('#aig-gen-save').on('click', function(){
      $st.text('kaydediliyor…');
      safePost('ai_article_pipeline_generate', payload(true)).then(function(r){
        if (r.ok) { render(r.data); $st.text('ok'); }
        else { $st.text('hata'); $out.html('<pre>'+htmlEscape(r.error||'error')+'</pre>'); }
      });
    });
  }

  /* ---------------------------------------------------------
   * V4: Rewrite Studio
   * --------------------------------------------------------- */
  function initRewrite(){
    var $out = $('#aig-rewrite-output');
    var $st  = $('#aig-rewrite-status');

    $('#aig-rewrite-run').on('click', function(){
      $st.text('çalışıyor…');
      safePost('ai_article_rewrite', {
        _ajax_nonce: (win.AI_ARTICLE_EDITOR && win.AI_ARTICLE_EDITOR.nonce) || '',
        text: $('#aig-rewrite-text').val() || '',
        instruction: $('#aig-rewrite-instruction').val() || 'rewrite',
        tone: $('#aig-gen-tone').val() || 'neutral',
        lang: $('#aig-gen-lang').val() || 'tr',
        model: 'auto'
      }).then(function(r){
        if (r.ok && r.data && r.data.ok) {
          $out.html(r.data.content || '');
          $st.text('ok');
        } else {
          $st.text('hata');
          $out.html('<pre>'+htmlEscape((r.data && r.data.error) || r.error || 'error')+'</pre>');
        }
      });
    });
  }

  /* ---------------------------------------------------------
   * V4: Templates
   * --------------------------------------------------------- */
  function initTemplates(){
    var $st = $('#aig-tpl-status');
    var $list = $('#aig-tpl-list');
    var $json = $('#aig-tpl-json');

    function noncePayload(extra){
      return Object.assign({_ajax_nonce: (win.AI_ARTICLE_EDITOR && win.AI_ARTICLE_EDITOR.nonce) || ''}, extra||{});
    }

    $('#aig-tpl-refresh').on('click', function(){
      $st.text('yükleniyor…');
      safePost('ai_article_templates_list', noncePayload()).then(function(r){
        if (r.ok && r.data && r.data.templates) {
          var keys = Object.keys(r.data.templates);
          $list.text('Toplam: '+keys.length+'\n' + keys.sort().join('\n'));
          $st.text('ok');
        } else { $st.text('hata'); }
      });
    });

    $('#aig-tpl-export').on('click', function(){
      $st.text('export…');
      safePost('ai_article_templates_export', noncePayload()).then(function(r){
        if (r.ok && r.data && r.data.templates) {
          $json.val(JSON.stringify(r.data.templates, null, 2));
          $st.text('ok');
        } else { $st.text('hata'); }
      });
    });

    $('#aig-tpl-import').on('click', function(){
      $st.text('import…');
      safePost('ai_article_templates_import', noncePayload({json: $json.val() || ''})).then(function(r){
        if (r.ok && r.data && r.data.ok) {
          $st.text('ok ('+(r.data.count||0)+')');
        } else { $st.text('hata'); }
      });
    });
  }

  /* ---------------------------------------------------------
   * V4: Usage + Selftest
   * --------------------------------------------------------- */
  function initUsage(){
    var $st = $('#aig-usage-status');
    var $out = $('#aig-usage-out');

    $('#aig-usage-refresh').on('click', function(){
      $st.text('yükleniyor…');
      safePost('ai_article_usage_totals', {
        _ajax_nonce: (win.AI_ARTICLE_EDITOR && win.AI_ARTICLE_EDITOR.nonce) || ''
      }).then(function(r){
        if (r.ok) {
          $out.text(JSON.stringify(r.data, null, 2));
          $st.text('ok');
        } else { $st.text('hata'); }
      });
    });
  }

  function initSelftest(){
    var $st = $('#aig-selftest-status');
    var $out = $('#aig-selftest-out');

    $('#aig-selftest-run').on('click', function(){
      $st.text('çalışıyor…');
      safePost('ai_article_selftest', {
        _ajax_nonce: (win.AI_ARTICLE_EDITOR && win.AI_ARTICLE_EDITOR.nonce) || ''
      }).then(function(r){
        if (r.ok) {
          $out.text(JSON.stringify(r.data, null, 2));
          $st.text('ok');
        } else { $st.text('hata'); }
      });
    });
  }

/* ---------------------------------------------------------
   * STARTUP
   * --------------------------------------------------------- */
  $(function(){
    console.log('✅ AI Article Generator — Pro UI Loaded');
    initDiagnostics();
    initMediaSearch();
    initLogBox();
    initApiKeys();
    initGenerator();
    initRewrite();
    initTemplates();
    initUsage();
    initSelftest();
  });

})(window, document, jQuery);
