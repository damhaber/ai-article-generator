<?php
if (!defined('ABSPATH')) { exit; }
add_action('admin_init', function(){
    register_setting('ai_article_settings','ai_article_provider');
    register_setting('ai_article_settings','ai_article_queue_enabled');
    register_setting('ai_article_settings','ai_article_queue_count');
    register_setting('ai_article_settings','ai_article_queue_template');
    register_setting('ai_article_settings','ai_article_queue_category');
    register_setting('ai_article_settings','ai_article_queue_tags');
        // (Mevcut kayıtların yanına ekle)
    register_setting('ai_article_settings','ai_media_provider');      // pexels|off
    register_setting('ai_article_settings','ai_pexels_api_key');      // text
    register_setting('ai_article_settings','ai_media_count');         // 1..3
    register_setting('ai_article_settings','ai_media_safe');          // 0/1
});

function ai_article_settings_render() {
    $prov  = get_option('ai_article_provider','auto');
    $q_en  = (bool)get_option('ai_article_queue_enabled', false);
    $q_cnt = (int)get_option('ai_article_queue_count', 1);
    $q_tpl = get_option('ai_article_queue_template','news_basic');
    $q_cat = (int)get_option('ai_article_queue_category', 0);
    $q_tags= (string)get_option('ai_article_queue_tags','');
    if (!function_exists('ai_article_templates')) { @include dirname(__DIR__).'/core/ai-article-templates.php'; }
    $templates = function_exists('ai_article_templates') ? ai_article_templates() : [];
    ?>
    <div class="card">
      <h3>⚙️ AI Article Ayarları</h3>
      <table class="form-table">
        <tr><th>LLM Sağlayıcı</th><td>
            <select name="ai_article_provider">
              <?php foreach (['auto'=>'Otomatik','gpt'=>'GPT','gemini'=>'Gemini','claude'=>'Claude'] as $k=>$v): ?>
                <option value="<?php echo esc_attr($k); ?>" <?php selected($prov,$k); ?>><?php echo esc_html($v); ?></option>
              <?php endforeach; ?>
            </select>
            <p class="description">Sağlayıcıda 'auto' önerilir.</p>
        </td></tr>
      </table>
    </div>
    <div class="card">
      <h3>⏱️ Otomatik Üretim (Cron)</h3>
      <table class="form-table">
        <tr><th>Etkin</th><td><label><input type="checkbox" name="ai_article_queue_enabled" value="1" <?php checked($q_en, true); ?>> Saatlik otomatik üretim</label></td></tr>
        <tr><th>Adet / Saat</th><td><input type="number" min="1" max="5" name="ai_article_queue_count" value="<?php echo esc_attr($q_cnt); ?>"></td></tr>
        <tr><th>Şablon</th><td>
            <select name="ai_article_queue_template">
              <?php foreach ($templates as $id=>$row): ?>
                <option value="<?php echo esc_attr($id); ?>" <?php selected($q_tpl,$id); ?>><?php echo esc_html(($row['title']??$id) . ' ('.$id.')'); ?></option>
              <?php endforeach; ?>
            </select>
        </td></tr>
        <tr><th>Kategori ID</th><td><input type="number" min="0" name="ai_article_queue_category" value="<?php echo esc_attr($q_cat); ?>"></td></tr>
        <tr><th>Etiketler</th><td><input type="text" name="ai_article_queue_tags" value="<?php echo esc_attr($q_tags); ?>" placeholder="haber, gündem, ekonomi"></td></tr>
      </table>
    </div>
    <?php
}


/* — Form — */
function ai_article_settings_render_media() {
    $prov  = get_option('ai_media_provider','off');
    $key   = get_option('ai_pexels_api_key','');
    $count = max(1, min(3, (int)get_option('ai_media_count', 1)));
    $safe  = (bool)get_option('ai_media_safe', true);
    ?>
    <div class="card" style="margin-top:12px;">
      <h3>📸 Görsel Sağlayıcı</h3>
      <table class="form-table">
        <tr>
          <th scope="row">Sağlayıcı</th>
          <td>
            <select name="ai_media_provider">
              <option value="off"   <?php selected($prov,'off'); ?>>Kapalı</option>
              <option value="pexels"<?php selected($prov,'pexels'); ?>>Pexels</option>
            </select>
            <p class="description">Şimdilik Pexels destekli. Gerekirse Gemini Images ekleriz.</p>
          </td>
        </tr>
        <tr>
          <th scope="row">Pexels API Anahtarı</th>
          <td>
            <input type="text" name="ai_pexels_api_key" value="<?php echo esc_attr($key); ?>" class="regular-text" autocomplete="off" />
            <p class="description">Anahtar hesap bazlıdır; domain değişse de çalışır.</p>
          </td>
        </tr>
        <tr>
          <th scope="row">Görsel Adedi</th>
          <td>
            <input type="number" min="1" max="3" name="ai_media_count" value="<?php echo esc_attr($count); ?>" />
            <label><input type="checkbox" name="ai_media_safe" value="1" <?php checked($safe, true); ?> /> Güvenli arama</label>
          </td>
        </tr>
      </table>
    </div>
    <?php
}