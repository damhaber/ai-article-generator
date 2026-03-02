<?php
/**
 * AI Article Generator — Loader (V4 STABLE)
 * Tek kaynak: bu modül (JSON-first, log + self-test + admin panel)
 */
if (!defined('ABSPATH')) exit;

define('AI_ARTICLE_MODULE_PATH', __DIR__);
define('AI_ARTICLE_GENERATOR_VERSION', '1.4.0');
define('AI_ARTICLE_GENERATOR_BUILD', '20260302-024708');

// Masal Panel altında sabit URL (yerel kurulumlar için güvenli)
$base_url = home_url('/masal-panel/modules/ai-article-generator');
define('AI_ARTICLE_MODULE_URL', rtrim($base_url, '/'));

// ------------------------------------------------------------
// Core
// ------------------------------------------------------------
require_once AI_ARTICLE_MODULE_PATH . '/core/ai-log.php';
require_once AI_ARTICLE_MODULE_PATH . '/core/ai-article-media.php';
require_once AI_ARTICLE_MODULE_PATH . '/core/ai-article-core.php';
require_once AI_ARTICLE_MODULE_PATH . '/core/ai-article-templates.php';
require_once AI_ARTICLE_MODULE_PATH . '/core/ai-article-outline.php';
require_once AI_ARTICLE_MODULE_PATH . '/core/ai-article-quality.php';
require_once AI_ARTICLE_MODULE_PATH . '/core/ai-article-metrics.php';
require_once AI_ARTICLE_MODULE_PATH . '/core/ai-article-pipeline.php';
require_once AI_ARTICLE_MODULE_PATH . '/core/ai-article-post.php';
require_once AI_ARTICLE_MODULE_PATH . '/core/ai-article-bridge.php';

// AJAX gate (Pexels + log + pipeline + templates + self-test)
require_once AI_ARTICLE_MODULE_PATH . '/ajax-handler.php';

// ------------------------------------------------------------
// Admin enqueue (panel.php için)
// ------------------------------------------------------------
add_action('admin_enqueue_scripts', function() {
    if (
        empty($_GET['page']) || $_GET['page'] !== 'masal-panel' ||
        empty($_GET['mod'])  || $_GET['mod']  !== 'ai-article-generator'
    ) return;

    $js  = AI_ARTICLE_MODULE_PATH . '/ui/editor.js';
    $css = AI_ARTICLE_MODULE_PATH . '/ui/style.css';

    if (file_exists($css)) {
        wp_enqueue_style('ai-article-style', AI_ARTICLE_MODULE_URL . '/ui/style.css', [], filemtime($css));
    }

    $handle = 'ai-article-editor';
    $src = AI_ARTICLE_MODULE_URL . '/ui/editor.js';
    $ver = file_exists($js) ? filemtime($js) : time();
    wp_register_script($handle, $src, ['jquery'], $ver, true);

    wp_add_inline_script($handle, 'window.AI_ARTICLE_EDITOR=' . json_encode([
        'ajax' => admin_url('admin-ajax.php'),
        'nonces' => [
            'media' => wp_create_nonce('ai_article_media'),
            'log'   => wp_create_nonce('ai_article_log'),
        ],
        'nonce' => wp_create_nonce('ai_article_nonce'),
        'build' => AI_ARTICLE_GENERATOR_BUILD,
        'ver'   => AI_ARTICLE_GENERATOR_VERSION,
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . ';', 'before');

    wp_enqueue_script($handle);
});
