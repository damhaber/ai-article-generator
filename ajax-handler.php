<?php
/**
 * AI Article Generator — AJAX Tek Kapı (Final, 403-Bypass & Log Enabled)
 */
if (!defined('ABSPATH')) { exit; }

@ini_set('display_errors', 0);
@error_reporting(0);
@ob_start();

/* ------------------------------------------------------------
 * JSON Yardımcıları
 * ------------------------------------------------------------ */
function _aig_json_success($data = []) {
  @ob_clean();
  nocache_headers();
  header('Content-Type: application/json; charset=' . get_option('blog_charset'));
  wp_send_json_success($data);
}
function _aig_json_error($data = []) {
  @ob_clean();
  nocache_headers();
  header('Content-Type: application/json; charset=' . get_option('blog_charset'));
  wp_send_json_error($data);
}

/* ------------------------------------------------------------
 * 1) LOG Uçları
 * ------------------------------------------------------------ */
add_action('wp_ajax_ai_article_log_tail', function () {
  $nonce_ok = isset($_POST['nonce']) && check_ajax_referer('ai_article_log','nonce',false);
  if (!$nonce_ok) aig_log_write('WARN','log_nonce_fail',['got'=>$_POST['nonce']??null]);

  $max = isset($_POST['max']) ? max(10, min(1000, intval($_POST['max']))) : 120;
  $lines = aig_log_tail($max);
  _aig_json_success(['ok'=>true, 'data'=>$lines]);
});

add_action('wp_ajax_ai_article_log_clear', function () {
  $nonce_ok = isset($_POST['nonce']) && check_ajax_referer('ai_article_log','nonce',false);
  if (!$nonce_ok) aig_log_write('WARN','log_clear_nonce_fail',['got'=>$_POST['nonce']??null]);

  $ok = aig_log_clear();
  _aig_json_success(['ok'=>$ok]);
});

/* ------------------------------------------------------------
 * 2) PEXELS PROBE & FETCH
 * ------------------------------------------------------------ */
add_action('wp_ajax_ai_media_probe', function () {
  $nonce_ok = isset($_POST['nonce']) && check_ajax_referer('ai_article_media','nonce',false);
  if (!$nonce_ok) aig_log_write('WARN','media_probe_nonce_fail',['got'=>$_POST['nonce']??null]);

  $probe = aig_media_probe();
  if ($probe['ok']) _aig_json_success($probe);
  else _aig_json_error($probe);
});

add_action('wp_ajax_ai_media_fetch', function () {
  $nonce_ok = isset($_POST['nonce']) && check_ajax_referer('ai_article_media','nonce',false);
  if (!$nonce_ok) aig_log_write('WARN','media_fetch_nonce_fail',['got'=>$_POST['nonce']??null]);

  $q     = isset($_POST['q']) ? sanitize_text_field(wp_unslash($_POST['q'])) : 'news';
  $type  = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'image';
  $count = isset($_POST['count']) ? max(1, min(20, intval($_POST['count']))) : 6;
  $size  = isset($_POST['size']) ? sanitize_text_field(wp_unslash($_POST['size'])) : 'medium';

  $res = aig_media_fetch($type, $q, $count, $size);
  if (!empty($res['ok'])) _aig_json_success($res['data']);
  else _aig_json_error(['msg'=>$res['msg'] ?? 'fetch_failed', 'detail'=>$res['detail'] ?? null]);
});

/* ------------------------------------------------------------
 * 3) PEXELS ANAHTARI KAYDETME
 * ------------------------------------------------------------ */
add_action('wp_ajax_ai_save_pexels_key', function () {
  if (!current_user_can('manage_options')) return _aig_json_error(['msg'=>'no_permission']);

  $key = sanitize_text_field($_POST['key'] ?? '');
  $opt = get_option(AIG_OPT_API_KEYS, []);
  if (!is_array($opt)) $opt = [];
  $opt['pexels'] = $key;
  update_option(AIG_OPT_API_KEYS, $opt);

  aig_log_write('INFO','pexels_key_saved',['len'=>strlen($key)]);
  _aig_json_success(['ok'=>true]);
});

/* ------------------------------------------------------------
 * 4) Genel Tanılama (isteğe bağlı)
 * ------------------------------------------------------------ */
add_action('wp_ajax_ai_diag_ping', function(){
  _aig_json_success(['ok'=>true,'msg'=>'AI Article AJAX ping ok','time'=>current_time('mysql')]);
});



/* ------------------------------------------------------------
 * 5) PIPELINE: Makale Üret (Outline → Sections → Quality → Similarity → Save)
 * ------------------------------------------------------------ */
add_action('wp_ajax_ai_article_pipeline_generate', function () {
  if (!current_user_can('edit_posts')) return _aig_json_error(['msg'=>'no_permission']);
  // ai_article_nonce
  $nonce_ok = isset($_POST['_ajax_nonce']) && check_ajax_referer('ai_article_nonce','_ajax_nonce',false);
  if (!$nonce_ok) aig_log_write('WARN','pipeline_nonce_fail',['got'=>$_POST['_ajax_nonce']??null]);

  $args = [
    'topic'      => sanitize_text_field(wp_unslash($_POST['topic'] ?? '')),
    'keyword'    => sanitize_text_field(wp_unslash($_POST['keyword'] ?? '')),
    'lang'       => sanitize_text_field($_POST['lang'] ?? 'tr'),
    'tone'       => sanitize_text_field($_POST['tone'] ?? 'neutral'),
    'template'   => sanitize_text_field($_POST['template'] ?? 'news_basic'),
    'min_quality'=> (int)($_POST['min_quality'] ?? 60),
    'auto_improve' => !empty($_POST['auto_improve']),
    'max_attempts' => (int)($_POST['max_attempts'] ?? 3),
    'similarity_guard' => !empty($_POST['similarity_guard']),
    'similarity_threshold' => (float)($_POST['similarity_threshold'] ?? 0.80),
    'save'       => !empty($_POST['save']),
    'post_status'=> sanitize_text_field($_POST['post_status'] ?? 'draft'),
  ];

  if (!function_exists('ai_article_pipeline_generate')) return _aig_json_error(['msg'=>'pipeline_missing']);
  $res = ai_article_pipeline_generate($args);
  if (!empty($res['ok'])) _aig_json_success($res);
  _aig_json_error($res);
});

/* ------------------------------------------------------------
 * 6) TEMPLATE MARKETPLACE (JSON)
 * ------------------------------------------------------------ */
add_action('wp_ajax_ai_article_templates_list', function(){
  if (!current_user_can('manage_options')) return _aig_json_error(['msg'=>'no_permission']);
  $nonce_ok = isset($_POST['_ajax_nonce']) && check_ajax_referer('ai_article_nonce','_ajax_nonce',false);
  if (!$nonce_ok) aig_log_write('WARN','tpl_list_nonce_fail',[]);
  $all = function_exists('ai_article_templates_all') ? ai_article_templates_all() : [];
  _aig_json_success(['ok'=>true,'templates'=>$all]);
});

add_action('wp_ajax_ai_article_templates_import', function(){
  if (!current_user_can('manage_options')) return _aig_json_error(['msg'=>'no_permission']);
  $nonce_ok = isset($_POST['_ajax_nonce']) && check_ajax_referer('ai_article_nonce','_ajax_nonce',false);
  if (!$nonce_ok) aig_log_write('WARN','tpl_import_nonce_fail',[]);

  $raw = wp_unslash($_POST['json'] ?? '');
  $arr = json_decode((string)$raw, true);
  if (!is_array($arr)) return _aig_json_error(['msg'=>'invalid_json']);

  if (!function_exists('ai_article_templates_marketplace_save')) return _aig_json_error(['msg'=>'tpl_save_missing']);
  $ok = ai_article_templates_marketplace_save($arr);
  _aig_json_success(['ok'=>$ok,'count'=>count($arr)]);
});

add_action('wp_ajax_ai_article_templates_export', function(){
  if (!current_user_can('manage_options')) return _aig_json_error(['msg'=>'no_permission']);
  $nonce_ok = isset($_POST['_ajax_nonce']) && check_ajax_referer('ai_article_nonce','_ajax_nonce',false);
  if (!$nonce_ok) aig_log_write('WARN','tpl_export_nonce_fail',[]);

  $mkt = function_exists('ai_article_templates_marketplace_load') ? ai_article_templates_marketplace_load() : [];
  _aig_json_success(['ok'=>true,'templates'=>$mkt]);
});

/* ------------------------------------------------------------
 * 7) USAGE TOTALS (Token/Cost Monitor)
 * ------------------------------------------------------------ */
add_action('wp_ajax_ai_article_usage_totals', function(){
  if (!current_user_can('manage_options')) return _aig_json_error(['msg'=>'no_permission']);
  $nonce_ok = isset($_POST['_ajax_nonce']) && check_ajax_referer('ai_article_nonce','_ajax_nonce',false);
  if (!$nonce_ok) aig_log_write('WARN','usage_nonce_fail',[]);

  $tot = defined('AIG_OPT_USAGE') ? get_option(AIG_OPT_USAGE, []) : [];
  if (!is_array($tot)) $tot = [];
  $sim = defined('AIG_OPT_SIM_INDEX') ? get_option(AIG_OPT_SIM_INDEX, []) : [];
  $sim_size = is_array($sim) ? count($sim) : 0;

  _aig_json_success(['ok'=>true,'totals'=>$tot,'similarity_index_size'=>$sim_size]);
});

/* ------------------------------------------------------------
 * 8) SELF-TEST (Permissions + Core Health)
 * ------------------------------------------------------------ */
add_action('wp_ajax_ai_article_selftest', function(){
  if (!current_user_can('manage_options')) return _aig_json_error(['msg'=>'no_permission']);
  $nonce_ok = isset($_POST['_ajax_nonce']) && check_ajax_referer('ai_article_nonce','_ajax_nonce',false);
  if (!$nonce_ok) aig_log_write('WARN','selftest_nonce_fail',[]);

  $checks = [];
  $checks['php'] = PHP_VERSION;
  $checks['wp']  = get_bloginfo('version');
  $checks['build'] = defined('AI_ARTICLE_GENERATOR_BUILD') ? AI_ARTICLE_GENERATOR_BUILD : null;

  $logfile = wp_normalize_path(dirname(__FILE__) . '/logs/ai-article-generator.log');
  $checks['log_writable'] = is_writable(dirname($logfile));
  $checks['storage_writable'] = is_writable(wp_normalize_path(dirname(__FILE__) . '/storage'));

  $checks['functions'] = [
    'generate'  => function_exists('ai_article_generate'),
    'pipeline'  => function_exists('ai_article_pipeline_generate'),
    'save_post' => function_exists('ai_article_save_post'),
    'pexels'    => function_exists('aig_media_fetch'),
  ];

  aig_log_write('INFO','selftest_run',$checks);
  _aig_json_success(['ok'=>true,'checks'=>$checks]);
});
