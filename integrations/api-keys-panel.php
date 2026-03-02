<?php
/**
 * API Keys Panel — AJAX tabanlı kayıt ve doğrulama (2025-11-03)
 * Konum: /masal-panel/modules/ai-article-generator/integrations/api-keys-panel.php
 */
if (!defined('ABSPATH')) { exit; }

$opt = get_option(AIG_OPT_API_KEYS, []);
$pex = is_array($opt) && !empty($opt['pexels']) ? $opt['pexels'] : '';
?>
<form id="aig-key-form" method="post" action="">
  <table class="form-table" role="presentation">
    <tbody>
      <tr>
        <th scope="row"><label for="aig_pexels">Pexels API Key</label></th>
        <td>
          <input id="aig_pexels" type="text"
                 value="<?php echo esc_attr($pex); ?>"
                 class="regular-text" autocomplete="off" />
          <p class="description">
            Anahtarınızı buraya girin ve <b>Anahtarı Kaydet</b> tuşuna basın.
          </p>
        </td>
      </tr>
    </tbody>
  </table>
  <p><button type="submit" class="button button-primary">Anahtarı Kaydet</button></p>
</form>

<script>
jQuery(function ($) {
  $('#aig-key-form').on('submit', function (e) {
    e.preventDefault();
    const key = $('#aig_pexels').val().trim();
    if (!key) { alert('⚠️ Anahtar boş olamaz.'); return; }

    const $btn = $(this).find('button[type=submit]');
    $btn.prop('disabled', true).text('Kaydediliyor…');

    $.post(ajaxurl, { action: 'ai_save_pexels_key', key: key }, function (r) {
      $btn.prop('disabled', false).text('Anahtarı Kaydet');
      if (r && r.success) {
        alert('✅ Anahtar başarıyla kaydedildi.');
      } else {
        alert('⛔ Kaydedilemedi: ' + (r.data?.msg || 'Hata'));
      }
    }).fail(function (xhr) {
      $btn.prop('disabled', false).text('Anahtarı Kaydet');
      alert('⛔ Ağ hatası (' + xhr.status + ')');
    });
  });
});
</script>
