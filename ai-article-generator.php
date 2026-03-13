<?php
/**
 * Modul Name: AI Article Generator
 * Description: Multi-provider AI article generation module for Masal Panel / Yokno ecosystem.
 * Version: 1.5.1
 * Author: Yokno / Masal Panel
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * --------------------------------------------------------------------------
 * Duplicate Load Guard
 * --------------------------------------------------------------------------
 */
if (defined('AIG_MODULE_BOOTSTRAPPED')) {
    return;
}
define('AIG_MODULE_BOOTSTRAPPED', true);

/**
 * --------------------------------------------------------------------------
 * Legacy Core Constants
 * --------------------------------------------------------------------------
 */
if (!defined('AI_ARTICLE_MODULE_PATH')) {
    define('AI_ARTICLE_MODULE_PATH', __DIR__);
}

if (!defined('AI_ARTICLE_GENERATOR_VERSION')) {
    define('AI_ARTICLE_GENERATOR_VERSION', '1.5.1');
}

if (!defined('AI_ARTICLE_GENERATOR_BUILD')) {
    define('AI_ARTICLE_GENERATOR_BUILD', '20260313-V6-STABILIZATION-PACK1');
}

/**
 * --------------------------------------------------------------------------
 * New Compatibility Constants (V6 Bridge)
 * --------------------------------------------------------------------------
 */
if (!defined('AIG_MODULE_VERSION')) {
    define('AIG_MODULE_VERSION', AI_ARTICLE_GENERATOR_VERSION);
}

if (!defined('AIG_MODULE_BUILD')) {
    define('AIG_MODULE_BUILD', AI_ARTICLE_GENERATOR_BUILD);
}

if (!defined('AIG_MODULE_SLUG')) {
    define('AIG_MODULE_SLUG', 'ai-article-generator');
}

if (!defined('AIG_MODULE_FILE')) {
    define('AIG_MODULE_FILE', __FILE__);
}

if (!defined('AIG_MODULE_DIR')) {
    define('AIG_MODULE_DIR', AI_ARTICLE_MODULE_PATH);
}

if (!defined('AIG_CORE_DIR')) {
    define('AIG_CORE_DIR', AIG_MODULE_DIR . '/core');
}

if (!defined('AIG_DATA_DIR')) {
    define('AIG_DATA_DIR', AIG_MODULE_DIR . '/data');
}

if (!defined('AIG_DOCS_DIR')) {
    define('AIG_DOCS_DIR', AIG_MODULE_DIR . '/docs');
}

if (!defined('AIG_LOG_DIR')) {
    define('AIG_LOG_DIR', AIG_MODULE_DIR . '/logs');
}

if (!defined('AIG_STORAGE_DIR')) {
    define('AIG_STORAGE_DIR', AIG_MODULE_DIR . '/storage');
}

if (!defined('AIG_UI_DIR')) {
    define('AIG_UI_DIR', AIG_MODULE_DIR . '/ui');
}

if (!defined('AIG_INTEGRATIONS_DIR')) {
    define('AIG_INTEGRATIONS_DIR', AIG_MODULE_DIR . '/integrations');
}

if (!defined('AIG_CACHE_DIR')) {
    define('AIG_CACHE_DIR', AIG_MODULE_DIR . '/cache');
}

if (!defined('AIG_CORE_NEWS_DIR')) {
    define('AIG_CORE_NEWS_DIR', AIG_CORE_DIR . '/news');
}

if (!defined('AIG_CORE_PIPELINES_DIR')) {
    define('AIG_CORE_PIPELINES_DIR', AIG_CORE_DIR . '/pipelines');
}

if (!defined('AIG_CORE_PROVIDERS_DIR')) {
    define('AIG_CORE_PROVIDERS_DIR', AIG_CORE_DIR . '/providers');
}

if (!defined('AIG_CORE_SEO_DIR')) {
    define('AIG_CORE_SEO_DIR', AIG_CORE_DIR . '/seo');
}

if (!defined('AIG_CORE_SERVICES_DIR')) {
    define('AIG_CORE_SERVICES_DIR', AIG_CORE_DIR . '/services');
}

/**
 * --------------------------------------------------------------------------
 * Module URL Resolver
 * --------------------------------------------------------------------------
 */
if (!defined('AI_ARTICLE_MODULE_URL')) {
    $base_url = home_url('/masal-panel/modules/ai-article-generator');

    if (
        function_exists('wp_normalize_path')
        && defined('ABSPATH')
        && strpos(wp_normalize_path(__DIR__), wp_normalize_path(ABSPATH)) === 0
    ) {
        $relative = ltrim(
            str_replace(wp_normalize_path(ABSPATH), '', wp_normalize_path(__DIR__)),
            '/'
        );

        if (!empty($relative)) {
            $base_url = home_url('/' . trim($relative, '/'));
        }
    }

    define('AI_ARTICLE_MODULE_URL', rtrim($base_url, '/'));
}

if (!defined('AIG_MODULE_URL')) {
    define('AIG_MODULE_URL', AI_ARTICLE_MODULE_URL);
}

/**
 * --------------------------------------------------------------------------
 * Safe Loader Helper
 * --------------------------------------------------------------------------
 */
if (!function_exists('aig_require_once_safe')) {
    function aig_require_once_safe(string $relative_file, bool $required = true): bool
    {
        $full = AI_ARTICLE_MODULE_PATH . '/' . ltrim($relative_file, '/');

        if (file_exists($full)) {
            require_once $full;
            return true;
        }

        if (function_exists('error_log')) {
            error_log('[AI Article Generator] Missing file: ' . $full);
        }

        if (function_exists('ai_article_log')) {
            ai_article_log('loader_missing_file', [
                'file'     => $relative_file,
                'required' => $required,
            ], $required ? 'error' : 'warn');
        }

        if ($required) {
            add_action('admin_notices', function () use ($relative_file) {
                if (!current_user_can('manage_options')) {
                    return;
                }

                echo '<div class="notice notice-error"><p>';
                echo '<strong>AI Article Generator:</strong> Missing required file: <code>' . esc_html($relative_file) . '</code>';
                echo '</p></div>';
            });
        }

        return false;
    }
}

if (!function_exists('aig_boot_contract_snapshot')) {
    function aig_boot_contract_snapshot(): array
    {
        $required_functions = [
            'aig_article_service_generate',
            'aig_article_pipeline_run',
            'aig_article_context_build',
            'aig_prompt_engine_build',
            'aig_rewrite_service_run',
            'aig_router_select',
        ];

        $map = [];

        foreach ($required_functions as $fn) {
            $map[$fn] = function_exists($fn);
        }

        return $map;
    }
}

if (!function_exists('aig_boot_log_contract_snapshot')) {
    function aig_boot_log_contract_snapshot(): void
    {
        $snapshot = aig_boot_contract_snapshot();

        if (function_exists('ai_article_log')) {
            ai_article_log('boot_contract_snapshot', $snapshot, 'info');
            return;
        }

        if (function_exists('error_log')) {
            error_log('[AIG] boot_contract_snapshot ' . wp_json_encode($snapshot));
        }
    }
}

/**
 * --------------------------------------------------------------------------
 * Ensure Base Directories
 * --------------------------------------------------------------------------
 */
foreach (
    [
        AIG_STORAGE_DIR,
        AIG_LOG_DIR,
        AIG_CACHE_DIR,
        AIG_DATA_DIR,
        AIG_DATA_DIR . '/news-cache',
        AIG_STORAGE_DIR . '/usage',
    ] as $aig_dir
) {
    if (!is_dir($aig_dir)) {
        @wp_mkdir_p($aig_dir);
    }
}

/**
 * --------------------------------------------------------------------------
 * Boot Order
 * --------------------------------------------------------------------------
 */

/**
 * 1) Logging first
 */
aig_require_once_safe('core/ai-log.php', true);

/**
 * 2) New low-level storage/config compatibility first
 */
aig_require_once_safe('core/ai-article-storage.php', false);
aig_require_once_safe('core/ai-article-feature-map.php', false);

/**
 * 3) Settings / storage / router / gateway
 */
aig_require_once_safe('core/ai-article-settings.php', true);
aig_require_once_safe('core/ai-article-usage.php', true);
aig_require_once_safe('core/ai-article-engines.php', true);
aig_require_once_safe('core/ai-article-router.php', true);
aig_require_once_safe('core/ai-article-gateway.php', true);
aig_require_once_safe('core/ai-article-selftest.php', false);

/**
 * 4) Provider layer
 */
aig_require_once_safe('core/providers/provider-interface.php', false);
aig_require_once_safe('core/providers/provider-base-openai-compat.php', false);
aig_require_once_safe('core/providers/provider-openai.php', false);
aig_require_once_safe('core/providers/provider-groq.php', false);
aig_require_once_safe('core/providers/provider-gemini.php', false);
aig_require_once_safe('core/providers/provider-deepseek.php', false);
aig_require_once_safe('core/providers/provider-mistral.php', false);
aig_require_once_safe('core/providers/provider-ollama.php', false);
aig_require_once_safe('core/providers/provider-openrouter.php', false);
aig_require_once_safe('core/ai-article-provider-registry.php', false);

/**
 * 5) Support modules
 */
aig_require_once_safe('core/ai-article-media.php', true);
aig_require_once_safe('core/ai-article-templates.php', true);
aig_require_once_safe('core/ai-article-metrics.php', false);
aig_require_once_safe('core/ai-article-context.php', true);
aig_require_once_safe('core/ai-article-devnotes.php', false);
aig_require_once_safe('core/ai-article-internal-links.php', false);
aig_require_once_safe('core/ai-article-queue.php', false);

/**
 * 6) Prompt engine
 * Artık runtime contract için gerçek dosya bekleniyor.
 */
aig_require_once_safe('core/ai-article-prompt-engine.php', true);

/**
 * 7) SEO layer
 */
aig_require_once_safe('core/seo/meta-builder.php', false);
aig_require_once_safe('core/seo/faq-builder.php', false);
aig_require_once_safe('core/seo/schema-builder.php', false);
aig_require_once_safe('core/seo/seo-engine.php', false);

/**
 * 8) News Intelligence Layer
 */
aig_require_once_safe('core/news/news-helpers.php', true);
aig_require_once_safe('core/news/news-cache.php', false);
aig_require_once_safe('core/news/news-sources.php', true);
aig_require_once_safe('core/news/news-normalizer.php', false);
aig_require_once_safe('core/news/news-collector.php', true);
aig_require_once_safe('core/news/news-fact-pack.php', true);

/**
 * 9) Main article generation runtime
 */
aig_require_once_safe('core/ai-article-core.php', true);
aig_require_once_safe('core/ai-article-outline.php', true);
aig_require_once_safe('core/ai-article-quality.php', true);
aig_require_once_safe('core/ai-article-pipeline.php', true);
aig_require_once_safe('core/ai-article-post.php', true);

/**
 * 10) Pipelines
 */
aig_require_once_safe('core/pipelines/rewrite-pipeline.php', false);
aig_require_once_safe('core/pipelines/seo-pipeline.php', false);

/**
 * 11) Service layer
 */
aig_require_once_safe('core/services/article-service.php', true);
aig_require_once_safe('core/services/media-service.php', false);
aig_require_once_safe('core/services/rewrite-service.php', true);
aig_require_once_safe('core/services/selftest-service.php', false);
aig_require_once_safe('core/services/seo-service.php', false);

/**
 * 12) LLM bridge / compatibility
 */
aig_require_once_safe('core/ai-article-llm.php', true);
aig_require_once_safe('core/ai-article-bridge.php', false);

/**
 * 13) AJAX gate
 */
aig_require_once_safe('ajax-handler.php', true);

/**
 * --------------------------------------------------------------------------
 * Create Minimal Default JSON Files
 * --------------------------------------------------------------------------
 */
if (function_exists('aig_storage_exists') && function_exists('aig_storage_write_json')) {
    if (!aig_storage_exists('settings.json') && function_exists('aig_settings_defaults')) {
        aig_storage_write_json('settings.json', aig_settings_defaults());
    }

    if (!aig_storage_exists('feature-map.json') && function_exists('aig_feature_map_defaults')) {
        aig_storage_write_json('feature-map.json', aig_feature_map_defaults());
    }

    if (!aig_storage_exists('providers.json')) {
        aig_storage_write_json('providers.json', [
            'schema_version' => '1.0.0',
            'providers' => [],
        ]);
    }

    if (!aig_storage_exists('models.json')) {
        aig_storage_write_json('models.json', [
            'schema_version' => '1.0.0',
            'models' => [],
        ]);
    }

    if (!aig_storage_exists('router.json')) {
        aig_storage_write_json('router.json', [
            'schema_version' => '1.0.0',
            'tasks' => [],
        ]);
    }

    if (!aig_storage_exists('prompt-presets.json')) {
        aig_storage_write_json('prompt-presets.json', [
            'schema_version' => '1.0.0',
            'presets' => [],
        ]);
    }

    if (!aig_storage_exists('health.json')) {
        aig_storage_write_json('health.json', [
            'last_run_at' => null,
            'overall_status' => 'unknown',
            'summary' => [],
            'build' => AIG_MODULE_BUILD,
        ]);
    }
}

/**
 * --------------------------------------------------------------------------
 * Admin Assets
 * --------------------------------------------------------------------------
 */
add_action('admin_enqueue_scripts', function () {
    $page   = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    $mod    = isset($_GET['mod']) ? sanitize_text_field(wp_unslash($_GET['mod'])) : '';
    $module = isset($_GET['module']) ? sanitize_text_field(wp_unslash($_GET['module'])) : '';
    $uri    = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    $is_masal_panel = ($page === 'masal-panel');
    $is_this_module = (
        $mod === 'ai-article-generator'
        || $module === 'ai-article-generator'
        || strpos($uri, 'ai-article-generator') !== false
    );

    if (!$is_masal_panel || !$is_this_module) {
        return;
    }

    $js_file  = AI_ARTICLE_MODULE_PATH . '/ui/editor.js';
    $css_file = AI_ARTICLE_MODULE_PATH . '/ui/style.css';

    if (file_exists($css_file)) {
        wp_enqueue_style(
            'ai-article-style',
            AI_ARTICLE_MODULE_URL . '/ui/style.css',
            [],
            (string) filemtime($css_file)
        );
    }

    $handle = 'ai-article-editor';
    $src    = AI_ARTICLE_MODULE_URL . '/ui/editor.js';
    $ver    = file_exists($js_file) ? (string) filemtime($js_file) : AI_ARTICLE_GENERATOR_VERSION;

    wp_register_script($handle, $src, ['jquery'], $ver, true);

    $payload = [
        'ajax'   => admin_url('admin-ajax.php'),
        'nonces' => [
            'media'    => wp_create_nonce('ai_article_media'),
            'log'      => wp_create_nonce('ai_article_log'),
            'pipeline' => wp_create_nonce('ai_article_pipeline'),
            'main'     => wp_create_nonce('ai_article_nonce'),
            'admin'    => wp_create_nonce('aig_admin_nonce'),
        ],
        'nonce'  => wp_create_nonce('ai_article_nonce'),
        'build'  => AI_ARTICLE_GENERATOR_BUILD,
        'ver'    => AI_ARTICLE_GENERATOR_VERSION,
    ];

    wp_add_inline_script(
        $handle,
        'window.AI_ARTICLE_EDITOR=' . wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';',
        'before'
    );

    wp_enqueue_script($handle);
});

/**
 * --------------------------------------------------------------------------
 * Legacy Filter Bridge
 * --------------------------------------------------------------------------
 */
if (function_exists('aig_llm_filter_handler')) {
    add_filter('ai_article/llm_generate', 'aig_llm_filter_handler', 10, 2);
}

/**
 * --------------------------------------------------------------------------
 * Activation Bootstrap
 * --------------------------------------------------------------------------
 */
if (function_exists('register_activation_hook')) {
    @register_activation_hook(__FILE__, function () {
        $storage_dir = AI_ARTICLE_MODULE_PATH . '/storage';
        $logs_dir    = AI_ARTICLE_MODULE_PATH . '/logs';
        $cache_dir   = AI_ARTICLE_MODULE_PATH . '/cache';

        foreach ([$storage_dir, $logs_dir, $cache_dir] as $dir) {
            if (!is_dir($dir)) {
                @wp_mkdir_p($dir);
            }
        }

        if (function_exists('aig_settings_get_all') && function_exists('aig_settings_set_all')) {
            $settings = aig_settings_get_all();
            if (is_array($settings)) {
                aig_settings_set_all($settings);
            }
        }

        if (function_exists('ai_article_log')) {
            ai_article_log('plugin_activated', [
                'version' => AI_ARTICLE_GENERATOR_VERSION,
                'build'   => AI_ARTICLE_GENERATOR_BUILD,
            ], 'info');
        }
    });
}

/**
 * --------------------------------------------------------------------------
 * Health Marker
 * --------------------------------------------------------------------------
 */
add_action('init', function () {
    static $boot_logged = false;

    if ($boot_logged) {
        return;
    }

    $boot_logged = true;

    aig_boot_log_contract_snapshot();

    if (function_exists('ai_article_log')) {
        ai_article_log('plugin_booted', [
            'version' => AI_ARTICLE_GENERATOR_VERSION,
            'build'   => AI_ARTICLE_GENERATOR_BUILD,
            'path'    => AI_ARTICLE_MODULE_PATH,
            'url'     => AI_ARTICLE_MODULE_URL,
        ], 'info');
        return;
    }

    if (function_exists('error_log')) {
        error_log('[AIG] module_booted ' . wp_json_encode([
            'version' => AI_ARTICLE_GENERATOR_VERSION,
            'build'   => AI_ARTICLE_GENERATOR_BUILD,
            'path'    => AI_ARTICLE_MODULE_PATH,
            'url'     => AI_ARTICLE_MODULE_URL,
        ]));
    }
}, 1);