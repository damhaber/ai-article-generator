<?php
/**
 * AI Article Generator — Admin Panel (V4 STABLE)
 * Tek dosya: panel.php (admin her şeyi görür/yönetir)
 */
if (!defined('ABSPATH')) { exit; }

$module_title = 'Makale Üretim + Düzenleme (V4)';
$nonce_media = wp_create_nonce('ai_article_media');
$nonce_log   = wp_create_nonce('ai_article_log');
$nonce_main  = wp_create_nonce('ai_article_nonce');

$opt = get_option(AIG_OPT_API_KEYS, []);
$pex = is_array($opt) && !empty($opt['pexels']) ? $opt['pexels'] : '';
?>
<div class="wrap" data-ai-root="1"
     data-nonce-media="<?php echo esc_attr($nonce_media); ?>"
     data-nonce-log="<?php echo esc_attr($nonce_log); ?>"
     data-nonce-main="<?php echo esc_attr($nonce_main); ?>">
  <h1>🧠 <?php echo esc_html($module_title); ?></h1>
  <p style="color:#666;margin-top:4px;">
    Build: <code><?php echo esc_html(defined('AI_ARTICLE_GENERATOR_BUILD')?AI_ARTICLE_GENERATOR_BUILD:'-'); ?></code>
    • Version: <code><?php echo esc_html(defined('AI_ARTICLE_GENERATOR_VERSION')?AI_ARTICLE_GENERATOR_VERSION:'-'); ?></code>
  </p>

  <div class="aig-grid">

    <!-- LEFT -->
    <div class="aig-col">

      <div class="postbox">
        <h2 class="hndle"><span>📝 Makale Üret</span></h2>
        <div class="inside">
          <p><label>Konu<br>
            <input type="text" id="aig-gen-topic" class="regular-text" value="Bugünün teknoloji gündemi" />
          </label></p>

          <p style="display:flex;gap:10px;flex-wrap:wrap;">
            <label>Anahtar Kelime<br>
              <input type="text" id="aig-gen-keyword" class="regular-text" value="yapay zeka" />
            </label>
            <label>Dil<br>
              <select id="aig-gen-lang">
                <option value="tr" selected>tr</option>
                <option value="en">en</option>
              </select>
            </label>
            <label>Tone<br>
              <select id="aig-gen-tone">
                <option value="neutral" selected>neutral</option>
                <option value="news">news</option>
                <option value="expert">expert</option>
                <option value="friendly">friendly</option>
              </select>
            </label>
            <label>Şablon<br>
              <select id="aig-gen-template">
                <option value="news_basic" selected>news_basic</option>
                <option value="blog_opinion">blog_opinion</option>
                <option value="howto_steps">howto_steps</option>
              </select>
            </label>
          </p>

          <p style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <label>Min Quality<br>
              <input type="number" id="aig-gen-minq" value="65" min="0" max="100" style="width:90px;" />
            </label>
            <label><input type="checkbox" id="aig-gen-auto" checked /> Auto-Improve</label>
            <label>Max Attempts
              <input type="number" id="aig-gen-attempts" value="3" min="1" max="5" style="width:70px;" />
            </label>
            <label><input type="checkbox" id="aig-gen-simguard" checked /> Similarity Guard</label>
            <label>Threshold
              <input type="number" step="0.01" id="aig-gen-simthr" value="0.80" min="0" max="1" style="width:80px;" />
            </label>
          </p>

          <p style="display:flex;gap:10px;flex-wrap:wrap;">
            <button id="aig-gen-run" type="button" class="button button-primary">Makale Üret</button>
            <button id="aig-gen-save" type="button" class="button">Taslak Kaydet</button>
            <span id="aig-gen-status" style="color:#666;align-self:center;">{ready}</span>
          </p>

          <div id="aig-gen-output" class="aig-output">(çıktı)</div>
        </div>
      </div>

      <div class="postbox">
        <h2 class="hndle"><span>🛠️ Edit / Rewrite Studio</span></h2>
        <div class="inside">
          <p><label>Metin<br>
            <textarea id="aig-rewrite-text" rows="8" style="width:100%;" placeholder="Düzenlemek istediğin metni buraya yapıştır…"></textarea>
          </label></p>
          <p style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <label>Talimat<br>
              <select id="aig-rewrite-instruction">
                <option value="rewrite" selected>rewrite (sıfırdan yeniden yaz)</option>
                <option value="shorten">shorten (kısalt)</option>
                <option value="expand">expand (genişlet)</option>
                <option value="fix_grammar">fix_grammar (imla/akıcılık)</option>
                <option value="formalize">formalize (daha resmi)</option>
              </select>
            </label>
            <button id="aig-rewrite-run" type="button" class="button button-primary">Düzenle</button>
            <span id="aig-rewrite-status" style="color:#666;">{ready}</span>
          </p>
          <div id="aig-rewrite-output" class="aig-output">(çıktı)</div>
        </div>
      </div>

      <div class="postbox">
        <h2 class="hndle"><span>📦 Template Marketplace (JSON)</span></h2>
        <div class="inside">
          <p style="margin-top:0;color:#666;">Marketplace dosyası: <code>storage/templates-marketplace.json</code></p>
          <p style="display:flex;gap:10px;flex-wrap:wrap;">
            <button id="aig-tpl-refresh" class="button">Listele</button>
            <button id="aig-tpl-export" class="button">Export (Marketplace)</button>
            <button id="aig-tpl-import" class="button button-primary">Import (JSON)</button>
            <span id="aig-tpl-status" style="color:#666;align-self:center;">{ready}</span>
          </p>
          <textarea id="aig-tpl-json" rows="10" style="width:100%;" placeholder='{"template_id":{"title":"...","system":"...","user":"..."}}'></textarea>
          <pre id="aig-tpl-list" class="aig-pre">(liste)</pre>
        </div>
      </div>

    </div>

    <!-- RIGHT -->
    <div class="aig-col">

      <div class="postbox">
        <h2 class="hndle"><span>🔍 Pexels Medya Arama</span></h2>
        <div class="inside">
          <p>Önce anahtar kaydedin, sonra <b>Anahtarı Doğrula</b> ve <b>Ara</b> sırasını izleyin.</p>
          <p><button id="ai-media-probe" type="button" class="button">🔍 Anahtarı Doğrula</button></p>

          <p>
            <label>Sorgu<br>
              <input type="text" id="ai-media-q" value="news" class="regular-text" />
            </label>
          </p>
          <p style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <label>Tür
              <select id="ai-media-type">
                <option value="image" selected>image</option>
                <option value="video">video</option>
              </select>
            </label>
            <label>Adet
              <input type="number" id="ai-media-count" value="6" min="1" max="20" style="width:70px;" />
            </label>
            <label>Boyut
              <select id="ai-media-size">
                <option value="medium" selected>medium</option>
                <option value="large">large</option>
              </select>
            </label>
            <button id="ai-media-fetch" type="button" class="button button-primary">Ara</button>
          </p>

          <div id="ai-media-results" class="aig-output">Sonuçlar burada görünecek…</div>
        </div>
      </div>

      <div class="postbox">
        <h2 class="hndle"><span>🔑 API Anahtarları</span></h2>
        <div class="inside">
          <p><label for="aig_pexels">Pexels API Key</label><br>
          <input id="aig_pexels" type="text" class="regular-text" value="<?php echo esc_attr($pex); ?>" autocomplete="off" /></p>
          <p><button id="aig-key-save" type="button" class="button button-primary">Anahtarı Kaydet</button></p>
        </div>
      </div>

      <div class="postbox">
        <h2 class="hndle"><span>📊 Token/Cost + Similarity Monitor</span></h2>
        <div class="inside">
          <p>
            <button id="aig-usage-refresh" class="button">Yenile</button>
            <span id="aig-usage-status" style="color:#666;margin-left:10px;">{ready}</span>
          </p>
          <pre id="aig-usage-out" class="aig-pre">(usage)</pre>
        </div>
      </div>

      <div class="postbox">
        <h2 class="hndle"><span>✅ Self-Test</span></h2>
        <div class="inside">
          <p>
            <button id="aig-selftest-run" class="button button-primary">Self-Test Çalıştır</button>
            <span id="aig-selftest-status" style="color:#666;margin-left:10px;">{ready}</span>
          </p>
          <pre id="aig-selftest-out" class="aig-pre">(selftest)</pre>
        </div>
      </div>

      <div class="postbox">
        <h2 class="hndle"><span>🪵 Log</span></h2>
        <div class="inside">
          <div style="margin-bottom:8px;">
            <button id="ai-log-refresh" class="button">Yenile</button>
            <button id="ai-log-clear" class="button">Temizle</button>
            <label style="margin-left:10px;">
              <input type="checkbox" id="ai-log-auto" /> Otomatik
            </label>
            <span id="ai-log-status" style="margin-left:10px;color:#666;">{log}</span>
          </div>
          <pre id="ai-log-box" style="background:#0d1117;color:#c9d1d9;padding:8px;border-radius:12px;height:240px;overflow:auto;font-size:12px;">(log)</pre>
        </div>
      </div>

    </div>

  </div>
</div>
