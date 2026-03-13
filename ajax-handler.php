<?php
/**
 * AI Article Generator — AJAX Gateway
 * Stabilized Hybrid Runtime
 */

if (!defined('ABSPATH')) {
    exit;
}

@ini_set('display_errors', '0');
@error_reporting(0);

if (!ob_get_level()) {
    @ob_start();
}

/* --------------------------------------------------------------------------
 * Fatal-safe AJAX shutdown
 * -------------------------------------------------------------------------- */
if (!function_exists('aig_ajax_shutdown_handler')) {
    function aig_ajax_shutdown_handler()
    {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            return;
        }

        $e = error_get_last();
        if (!$e) {
            return;
        }

        $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!in_array($e['type'], $fatal, true)) {
            return;
        }

        if (function_exists('aig_log_write')) {
            aig_log_write('error', 'ajax_fatal', [
                'message' => $e['message'] ?? '',
                'file'    => $e['file'] ?? '',
                'line'    => $e['line'] ?? '',
            ]);
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        if (!headers_sent()) {
            nocache_headers();
            header('Content-Type: application/json; charset=' . get_option('blog_charset'));
        }

        echo wp_json_encode([
            'success' => false,
            'data'    => [
                'msg'   => 'server_error',
                'fatal' => true,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }
}
register_shutdown_function('aig_ajax_shutdown_handler');

/* --------------------------------------------------------------------------
 * JSON helpers
 * -------------------------------------------------------------------------- */
if (!function_exists('_aig_json_success')) {
    function _aig_json_success($data = [])
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        nocache_headers();
        header('Content-Type: application/json; charset=' . get_option('blog_charset'));
        wp_send_json_success($data);
    }
}

if (!function_exists('_aig_json_error')) {
    function _aig_json_error($data = [], int $status_code = 200)
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        nocache_headers();
        header('Content-Type: application/json; charset=' . get_option('blog_charset'));
        wp_send_json_error($data, $status_code);
    }
}

/* --------------------------------------------------------------------------
 * Generic helpers
 * -------------------------------------------------------------------------- */
if (!function_exists('aig_ajax_verify_nonce_any')) {
    function aig_ajax_verify_nonce_any(array $pairs): bool
    {
        foreach ($pairs as $field => $actions) {
            if (!isset($_POST[$field])) {
                continue;
            }

            $value = sanitize_text_field(wp_unslash($_POST[$field]));
            foreach ((array) $actions as $action) {
                if (wp_verify_nonce($value, $action)) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('aig_ajax_require_editor_caps')) {
    function aig_ajax_require_editor_caps()
    {
        if (!current_user_can('edit_posts')) {
            _aig_json_error(['ok' => false, 'error' => 'permission_denied'], 403);
        }
    }
}

if (!function_exists('aig_ajax_require_admin_caps')) {
    function aig_ajax_require_admin_caps()
    {
        if (!current_user_can('manage_options')) {
            _aig_json_error(['msg' => 'no_permission'], 403);
        }
    }
}

if (!function_exists('aig_ajax_collect_v6_request')) {
    function aig_ajax_collect_v6_request(): array
    {
        $tags = $_POST['tags'] ?? [];
        if (!is_array($tags)) {
            $tags = [];
        }

        $save_mode = sanitize_text_field(wp_unslash($_POST['save_mode'] ?? 'preview'));
        if (!in_array($save_mode, ['preview', 'draft', 'publish'], true)) {
            $save_mode = 'preview';
        }

        $provider = sanitize_text_field(wp_unslash($_POST['provider'] ?? ''));
        $model    = sanitize_text_field(wp_unslash($_POST['model'] ?? ''));

        /**
         * AUTO değerlerini gerçek boş değere çevir.
         * Router/settings kendi default'unu çözsün.
         */
        if ($provider === 'auto') {
            $provider = '';
        }

        if ($model === 'auto') {
            $model = '';
        }

        return [
            'topic'                => sanitize_text_field(wp_unslash($_POST['topic'] ?? '')),
            'keyword'              => sanitize_text_field(wp_unslash($_POST['keyword'] ?? '')),
            'category'             => sanitize_text_field(wp_unslash($_POST['category'] ?? 'tech')),
            'news_range'           => sanitize_text_field(wp_unslash($_POST['news_range'] ?? '24h')),
            'source_limit'         => (int) ($_POST['source_limit'] ?? 10),
            'language'             => sanitize_text_field(wp_unslash($_POST['language'] ?? ($_POST['lang'] ?? 'tr'))),
            'lang'                 => sanitize_text_field(wp_unslash($_POST['lang'] ?? ($_POST['language'] ?? 'tr'))),
            'tone'                 => sanitize_text_field(wp_unslash($_POST['tone'] ?? 'professional')),
            'length'               => sanitize_text_field(wp_unslash($_POST['length'] ?? 'long')),
            'template'             => sanitize_text_field(wp_unslash($_POST['template'] ?? 'news_basic')),
            'provider'             => ($provider !== '' ? $provider : null),
            'model'                => ($model !== '' ? $model : null),
            'save_mode'            => $save_mode,
            'post_status'          => sanitize_text_field(wp_unslash($_POST['post_status'] ?? ($save_mode === 'publish' ? 'publish' : 'draft'))),
            'category_id'          => (int) ($_POST['category_id'] ?? 0),
            'post_id'              => (int) ($_POST['post_id'] ?? 0),
            'brief'                => trim((string) wp_unslash($_POST['brief'] ?? '')),
            'save'                 => !empty($_POST['save']) || $save_mode !== 'preview',
            'min_quality'          => (int) ($_POST['min_quality'] ?? 65),
            'auto_improve'         => !empty($_POST['auto_improve']),
            'max_attempts'         => (int) ($_POST['max_attempts'] ?? 3),
            'similarity_guard'     => !empty($_POST['similarity_guard']),
            'similarity_threshold' => (float) ($_POST['similarity_threshold'] ?? 0.80),
            'tags'                 => array_values(array_filter(array_map('sanitize_text_field', $tags))),
            'rewrite'              => true,
            'seo'                  => true,
            'include_sources'      => true,
            'include_summary'      => true,
            'request_meta'         => [
                'origin' => 'ajax',
                'ip'     => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
            ],
        ];
    }
}

if (!function_exists('aig_ajax_format_editor_payload')) {
    function aig_ajax_format_editor_payload(array $service_result, array $request = []): array
    {
        $article    = is_array($service_result['article'] ?? null) ? $service_result['article'] : [];
        $seo        = is_array($service_result['seo'] ?? null) ? $service_result['seo'] : [];
        $meta       = is_array($service_result['meta'] ?? null) ? $service_result['meta'] : [];
        $fact_pack  = is_array($service_result['fact_pack'] ?? null) ? $service_result['fact_pack'] : [];
        $save_result = is_array($service_result['save_result'] ?? null) ? $service_result['save_result'] : [];
        $quality    = is_array($meta['quality'] ?? null) ? $meta['quality'] : [];

        return [
            'title'         => (string) ($article['title'] ?? ''),
            'content'       => (string) ($article['html'] ?? ($article['content'] ?? '')),
            'plain_content' => (string) ($article['plain_content'] ?? ($article['content'] ?? '')),
            'summary'       => (string) ($article['summary'] ?? ''),
            'category'      => (string) ($article['category'] ?? ($request['category'] ?? '')),
            'topic'         => (string) ($article['topic'] ?? ($request['topic'] ?? '')),
            'lang'          => (string) ($article['lang'] ?? ($request['lang'] ?? 'tr')),
            'sections'      => is_array($article['sections'] ?? null) ? $article['sections'] : [],
            'sources'       => is_array($article['sources'] ?? null) ? $article['sources'] : [],
            'fact_pack'     => $fact_pack,
            'seo'           => $seo,
            'save_result'   => $save_result,
            'quality_score' => (int) ($quality['score'] ?? ($article['quality_score'] ?? 0)),
            'quality'       => $quality,
            'provider'      => (string) ($meta['provider'] ?? ''),
            'model'         => (string) ($meta['model'] ?? ''),
            'usage'         => is_array($meta['usage'] ?? null) ? $meta['usage'] : [],
            'timing'        => is_array($meta['timing'] ?? null) ? $meta['timing'] : [],
            'meta'          => $meta,
        ];
    }
}

if (!function_exists('aig_ajax_templates_file')) {
    function aig_ajax_templates_file(): string
    {
        if (defined('AIG_STORAGE_DIR')) {
            return rtrim(AIG_STORAGE_DIR, '/\\') . '/templates-marketplace.json';
        }

        return __DIR__ . '/storage/templates-marketplace.json';
    }
}

if (!function_exists('aig_ajax_load_templates_marketplace')) {
    function aig_ajax_load_templates_marketplace(): array
    {
        $file = aig_ajax_templates_file();
        if (!file_exists($file)) {
            return [];
        }

        $json = file_get_contents($file);
        $data = json_decode((string) $json, true);

        return is_array($data) ? $data : [];
    }
}

if (!function_exists('aig_ajax_save_templates_marketplace')) {
    function aig_ajax_save_templates_marketplace(array $data): bool
    {
        $file = aig_ajax_templates_file();
        $dir  = dirname($file);

        if (!is_dir($dir)) {
            @wp_mkdir_p($dir);
        }

        return false !== file_put_contents($file, wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}

/* --------------------------------------------------------------------------
 * LOG
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_ai_article_log_tail', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        'nonce'       => ['ai_article_log', 'ai_article_nonce', 'aig_admin_nonce'],
        '_ajax_nonce' => ['ai_article_log', 'ai_article_nonce', 'aig_admin_nonce'],
    ]);

    if (!$nonce_ok) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    $max = max(10, min(300, (int) ($_POST['max'] ?? 80)));
    $file = defined('AIG_LOG_DIR')
        ? rtrim(AIG_LOG_DIR, '/\\') . '/ai-article-generator.log'
        : __DIR__ . '/logs/ai-article-generator.log';

    if (!file_exists($file)) {
        return _aig_json_success(['data' => []]);
    }

    $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
        $lines = [];
    }

    $lines = array_slice($lines, -1 * $max);
    _aig_json_success(['data' => $lines]);
});

add_action('wp_ajax_ai_article_log_clear', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        'nonce'       => ['ai_article_log', 'ai_article_nonce', 'aig_admin_nonce'],
        '_ajax_nonce' => ['ai_article_log', 'ai_article_nonce', 'aig_admin_nonce'],
    ]);

    if (!$nonce_ok) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    $file = defined('AIG_LOG_DIR')
        ? rtrim(AIG_LOG_DIR, '/\\') . '/ai-article-generator.log'
        : __DIR__ . '/logs/ai-article-generator.log';

    if (!is_dir(dirname($file))) {
        @wp_mkdir_p(dirname($file));
    }

    file_put_contents($file, '');
    _aig_json_success(['ok' => true]);
});

/* --------------------------------------------------------------------------
 * PEXELS
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_ai_media_probe', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        'nonce'       => ['ai_article_media', 'ai_article_nonce', 'aig_admin_nonce'],
        '_ajax_nonce' => ['ai_article_media', 'ai_article_nonce', 'aig_admin_nonce'],
    ]);

    if (!$nonce_ok) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    if (!function_exists('aig_media_probe')) {
        return _aig_json_error(['msg' => 'media_probe_missing']);
    }

    $result = aig_media_probe();
    if (!empty($result['ok'])) {
        return _aig_json_success($result);
    }

    _aig_json_error($result);
});

add_action('wp_ajax_ai_media_fetch', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        'nonce'       => ['ai_article_media', 'ai_article_nonce', 'aig_admin_nonce'],
        '_ajax_nonce' => ['ai_article_media', 'ai_article_nonce', 'aig_admin_nonce'],
    ]);

    if (!$nonce_ok) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    if (!function_exists('aig_media_fetch')) {
        return _aig_json_error(['msg' => 'media_fetch_missing']);
    }

    $q     = sanitize_text_field(wp_unslash($_POST['q'] ?? 'news'));
    $type  = sanitize_text_field(wp_unslash($_POST['type'] ?? 'image'));
    $count = (int) ($_POST['count'] ?? 6);
    $size  = sanitize_text_field(wp_unslash($_POST['size'] ?? 'medium'));

    $result = aig_media_fetch($type, $q, $count, $size);

    if (!empty($result['ok'])) {
        return _aig_json_success(($result['data'] ?? $result));
    }

    _aig_json_error($result);
});

/* --------------------------------------------------------------------------
 * Diagnostic / misc
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_ai_diag_ping', function () {
    _aig_json_success([
        'ok'   => true,
        'msg'  => 'AI Article AJAX ping ok',
        'time' => current_time('mysql'),
    ]);
});

add_action('wp_ajax_ai_save_pexels_key', function () {
    aig_ajax_require_admin_caps();

    $key = trim((string) ($_POST['key'] ?? ''));
    if (!defined('AIG_OPT_API_KEYS')) {
        define('AIG_OPT_API_KEYS', 'ai_article_generator_api_keys');
    }

    $opt = get_option(AIG_OPT_API_KEYS, []);
    if (!is_array($opt)) {
        $opt = [];
    }

    $opt['pexels'] = $key;
    update_option(AIG_OPT_API_KEYS, $opt, false);

    _aig_json_success(['ok' => true]);
});

/* --------------------------------------------------------------------------
 * Article generation: legacy endpoint
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_ai_article_pipeline_generate', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        '_ajax_nonce' => ['ai_article_pipeline', 'ai_article_nonce', 'aig_admin_nonce'],
        'nonce'       => ['ai_article_pipeline', 'ai_article_nonce', 'aig_admin_nonce'],
    ]);

    if (!$nonce_ok) {
        if (function_exists('aig_log_write')) {
            aig_log_write('warn', 'pipeline_nonce_fail', [
                'got' => $_POST['_ajax_nonce'] ?? ($_POST['nonce'] ?? null),
            ]);
        }

        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    $request = aig_ajax_collect_v6_request();

    if (function_exists('aig_article_service_generate')) {
        $result = aig_article_service_generate($request);
        if (!empty($result['ok'])) {
            return _aig_json_success($result);
        }
        return _aig_json_error($result);
    }

    if (function_exists('ai_article_pipeline_generate')) {
        $result = ai_article_pipeline_generate($request);
        if (!empty($result['ok'])) {
            return _aig_json_success($result);
        }
        return _aig_json_error($result);
    }

    _aig_json_error(['msg' => 'pipeline_missing']);
});

/* --------------------------------------------------------------------------
 * Article generation: V6 endpoint
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_aig_generate_article', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        'nonce'       => ['aig_admin_nonce', 'ai_article_nonce', 'ai_article_pipeline'],
        '_ajax_nonce' => ['aig_admin_nonce', 'ai_article_nonce', 'ai_article_pipeline'],
    ]);

    if (!$nonce_ok) {
        if (function_exists('aig_log_write')) {
            aig_log_write('warn', 'v6_generate_nonce_fail', [
                'got' => $_POST['nonce'] ?? ($_POST['_ajax_nonce'] ?? null),
            ]);
        }

        return _aig_json_error(['ok' => false, 'error' => 'nonce_failed'], 403);
    }

    if (!function_exists('aig_article_service_generate')) {
        return _aig_json_error(['ok' => false, 'error' => 'article_service_missing']);
    }

    $request = aig_ajax_collect_v6_request();

    if (function_exists('aig_log_write')) {
        aig_log_write('info', 'v6_generate_request', [
            'topic'        => $request['topic'] ?? '',
            'category'     => $request['category'] ?? '',
            'news_range'   => $request['news_range'] ?? '',
            'source_limit' => $request['source_limit'] ?? 0,
            'save_mode'    => $request['save_mode'] ?? 'preview',
            'provider'     => $request['provider'] ?? 'auto',
            'model'        => $request['model'] ?? 'auto',
        ]);
    }

    $result = aig_article_service_generate($request);

    if (empty($result['ok'])) {
        if (function_exists('aig_log_write')) {
            aig_log_write('warn', 'v6_generate_failed', [
                'error' => $result['error'] ?? 'generate_failed',
            ]);
        }

        return _aig_json_error($result);
    }

    $data = aig_ajax_format_editor_payload($result, $request);

    _aig_json_success([
        'ok'   => true,
        'data' => $data,
    ]);
});

/* --------------------------------------------------------------------------
 * Selftest
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_ai_article_selftest', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        'nonce'       => ['aig_admin_nonce', 'ai_article_nonce'],
        '_ajax_nonce' => ['aig_admin_nonce', 'ai_article_nonce'],
    ]);

    if (!$nonce_ok) {
        return _aig_json_error(['ok' => false, 'error' => 'nonce_failed'], 403);
    }

    if (!function_exists('aig_selftest_service_run')) {
        return _aig_json_error(['ok' => false, 'error' => 'selftest_service_missing']);
    }

    $result = aig_selftest_service_run();
    _aig_json_success(['ok' => !empty($result['ok']), 'data' => $result]);
});

add_action('wp_ajax_aig_selftest', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        'nonce'       => ['aig_admin_nonce', 'ai_article_nonce'],
        '_ajax_nonce' => ['aig_admin_nonce', 'ai_article_nonce'],
    ]);

    if (!$nonce_ok) {
        return _aig_json_error(['ok' => false, 'error' => 'nonce_failed'], 403);
    }

    if (!function_exists('aig_selftest_service_run')) {
        return _aig_json_error(['ok' => false, 'error' => 'selftest_service_missing']);
    }

    $result = aig_selftest_service_run();
    _aig_json_success(['ok' => !empty($result['ok']), 'data' => $result]);
});

/* --------------------------------------------------------------------------
 * LLM control center
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_ai_llm_get', function () {
    aig_ajax_require_admin_caps();

    $state = function_exists('aig_llm_panel_state') ? aig_llm_panel_state() : ['provider' => []];

    if (!empty($state['provider']['api_key'])) {
        $state['provider']['api_key'] = str_repeat('•', 8);
    }

    _aig_json_success($state);
});

add_action('wp_ajax_ai_llm_save', function () {
    aig_ajax_require_admin_caps();

    $nonce = $_POST['_ajax_nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'ai_article_nonce')) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    $data = [
        'enabled'     => !empty($_POST['enabled']),
        'provider'    => sanitize_key((string) ($_POST['provider'] ?? '')),
        'endpoint'    => esc_url_raw((string) ($_POST['endpoint'] ?? '')),
        'model'       => sanitize_text_field((string) ($_POST['model'] ?? '')),
        'model_id'    => sanitize_key((string) ($_POST['model_id'] ?? '')),
        'preset'      => sanitize_key((string) ($_POST['preset'] ?? 'free_first')),
        'priority'    => (int) ($_POST['priority'] ?? 100),
        'timeout'     => (int) ($_POST['timeout'] ?? 60),
        'temperature' => (float) ($_POST['temperature'] ?? 0.7),
        'max_tokens'  => (int) ($_POST['max_tokens'] ?? 1200),
    ];

    $api_key = isset($_POST['api_key']) ? (string) $_POST['api_key'] : '';
    if ($api_key !== '') {
        $data['api_key'] = sanitize_text_field($api_key);
    }

    $provider = function_exists('aig_llm_save_provider')
        ? aig_llm_save_provider($data)
        : $data;

    if (!empty($provider['api_key'])) {
        $provider['api_key'] = str_repeat('•', 8);
    }

    _aig_json_success([
        'provider' => $provider,
        'state'    => function_exists('aig_llm_panel_state') ? aig_llm_panel_state() : [],
    ]);
});

add_action('wp_ajax_ai_llm_test', function () {
    aig_ajax_require_admin_caps();

    $nonce = $_POST['_ajax_nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'ai_article_nonce')) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    if (!function_exists('aig_llm_get_provider')) {
        return _aig_json_error(['msg' => 'llm_missing']);
    }

    $provider = aig_llm_get_provider();
    $provider_id = sanitize_key((string) ($_POST['provider'] ?? ($provider['provider'] ?? '')));
    $model_id    = sanitize_key((string) ($_POST['model_id'] ?? ($provider['model_id'] ?? '')));

    if ($provider_id !== '') {
        $provider['provider'] = $provider_id;
    }
    if ($model_id !== '') {
        $provider['model_id'] = $model_id;
    }
    if (!empty($_POST['model'])) {
        $provider['model'] = sanitize_text_field((string) $_POST['model']);
    }
    if (!empty($_POST['endpoint'])) {
        $provider['endpoint'] = esc_url_raw((string) $_POST['endpoint']);
    }
    if (isset($_POST['temperature'])) {
        $provider['temperature'] = (float) $_POST['temperature'];
    }
    if (isset($_POST['max_tokens'])) {
        $provider['max_tokens'] = (int) $_POST['max_tokens'];
    }
    if (isset($_POST['timeout'])) {
        $provider['timeout'] = max(5, (int) $_POST['timeout']);
    }

    $provider['enabled'] = true;

    $prompt = 'Kısa bir test üret: <b>Merhaba</b> de ve 1 cümle ekle.';
    $result = function_exists('aig_llm_generate_openai_compat')
        ? aig_llm_generate_openai_compat($prompt, $provider)
        : ['ok' => false, 'error' => 'llm_missing'];

    if (!empty($result['ok'])) {
        return _aig_json_success([
            'ok'    => true,
            'model' => $result['model'] ?? null,
            'usage' => $result['usage'] ?? [],
            'html'  => wp_trim_words(strip_tags((string) ($result['html'] ?? '')), 30, '…'),
        ]);
    }

    _aig_json_error([
        'msg'    => 'llm_test_failed',
        'detail' => $result,
    ]);
});

/* --------------------------------------------------------------------------
 * Rewrite
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_ai_article_rewrite', function () {
    aig_ajax_require_admin_caps();

    $nonce = $_POST['_ajax_nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'ai_article_rewrite') && !wp_verify_nonce($nonce, 'ai_article_nonce')) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    $text        = trim((string) ($_POST['text'] ?? ''));
    $instruction = sanitize_text_field((string) ($_POST['instruction'] ?? 'rewrite'));
    $lang        = sanitize_text_field((string) ($_POST['lang'] ?? 'tr'));
    $tone        = sanitize_text_field((string) ($_POST['tone'] ?? 'neutral'));
    $model       = sanitize_text_field((string) ($_POST['model'] ?? 'auto'));

    if ($text === '') {
        return _aig_json_error(['msg' => 'invalid_input']);
    }

    if (function_exists('aig_rewrite_service_run')) {
        $result = aig_rewrite_service_run([
            'title'         => 'Rewrite',
            'content'       => $text,
            'instruction'   => $instruction,
            'lang'          => $lang,
            'tone'          => $tone,
            'mode'          => $instruction,
            'target_length' => 'long',
            'preserve_html' => true,
            'model'         => $model === 'auto' ? null : $model,
        ]);

        if (!empty($result['ok'])) {
            $rewrite = is_array($result['rewrite'] ?? null) ? $result['rewrite'] : [];
            return _aig_json_success([
                'ok'      => true,
                'html'    => (string) ($rewrite['html'] ?? ($rewrite['content'] ?? '')),
                'content' => (string) ($rewrite['content'] ?? ''),
                'summary' => (string) ($rewrite['summary'] ?? ''),
                'sections'=> is_array($rewrite['sections'] ?? null) ? $rewrite['sections'] : [],
            ]);
        }
    }

    if (function_exists('ai_article_generate')) {
        $prompt = "DİL: {$lang}\nGÖREV: Aşağıdaki metni talimata göre yeniden yaz.\nTALİMAT: {$instruction}\nKURALLAR: Sadece düzeltilmiş metni döndür.\n\nMETİN:\n{$text}";
        $fallback = ai_article_generate([
            'prompt' => $prompt,
            'lang'   => $lang,
            'tone'   => $tone,
            'format' => 'html',
        ]);

        if (!empty($fallback['ok']) && !empty($fallback['html'])) {
            return _aig_json_success([
                'ok'    => true,
                'html'  => $fallback['html'],
                'model' => $fallback['model'] ?? null,
                'usage' => $fallback['usage'] ?? [],
            ]);
        }
    }

    _aig_json_error(['msg' => 'rewrite_failed']);
});

/* --------------------------------------------------------------------------
 * Template marketplace
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_ai_article_templates_list', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        '_ajax_nonce' => ['ai_article_nonce', 'ai_article_pipeline', 'aig_admin_nonce'],
        'nonce'       => ['ai_article_nonce', 'ai_article_pipeline', 'aig_admin_nonce'],
    ]);

    if (!$nonce_ok) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    _aig_json_success([
        'ok'   => true,
        'list' => aig_ajax_load_templates_marketplace(),
    ]);
});

add_action('wp_ajax_ai_article_templates_export', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        '_ajax_nonce' => ['ai_article_nonce', 'ai_article_pipeline', 'aig_admin_nonce'],
        'nonce'       => ['ai_article_nonce', 'ai_article_pipeline', 'aig_admin_nonce'],
    ]);

    if (!$nonce_ok) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    _aig_json_success([
        'ok'   => true,
        'json' => wp_json_encode(aig_ajax_load_templates_marketplace(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
});

add_action('wp_ajax_ai_article_templates_import', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        '_ajax_nonce' => ['ai_article_nonce', 'ai_article_pipeline', 'aig_admin_nonce'],
        'nonce'       => ['ai_article_nonce', 'ai_article_pipeline', 'aig_admin_nonce'],
    ]);

    if (!$nonce_ok) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    $raw = trim((string) wp_unslash($_POST['json'] ?? ''));
    if ($raw === '') {
        return _aig_json_error(['msg' => 'empty_json']);
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return _aig_json_error(['msg' => 'invalid_json']);
    }

    $saved = aig_ajax_save_templates_marketplace($data);

    if (!$saved) {
        return _aig_json_error(['msg' => 'template_save_failed']);
    }

    _aig_json_success([
        'ok'   => true,
        'list' => $data,
    ]);
});

/* --------------------------------------------------------------------------
 * Usage totals
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_ai_article_usage_totals', function () {
    aig_ajax_require_editor_caps();

    $nonce_ok = aig_ajax_verify_nonce_any([
        '_ajax_nonce' => ['ai_article_nonce', 'ai_article_pipeline', 'aig_admin_nonce'],
        'nonce'       => ['ai_article_nonce', 'ai_article_pipeline', 'aig_admin_nonce'],
    ]);

    if (!$nonce_ok) {
        return _aig_json_error(['msg' => 'nonce_failed'], 403);
    }

    $data = [];

    if (function_exists('aig_usage_get_totals')) {
        $data = aig_usage_get_totals();
    } elseif (defined('AIG_STORAGE_DIR') && file_exists(AIG_STORAGE_DIR . '/health.json')) {
        $health = json_decode((string) file_get_contents(AIG_STORAGE_DIR . '/health.json'), true);
        $data = is_array($health) ? $health : [];
    }

    _aig_json_success([
        'ok'   => true,
        'data' => $data,
    ]);
});