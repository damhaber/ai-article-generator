<?php
/**
 * AI Article Generator
 * Article Service
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_article_service_generate')) {
    function aig_article_service_generate(array $input): array
    {
        $started_at = microtime(true);

        $normalized = aig_article_service_normalize_input($input);

        $validation = aig_article_service_validate_input($normalized);
        if (empty($validation['ok'])) {
            return aig_article_service_build_error(
                (string) ($validation['error']['code'] ?? 'invalid_input'),
                (string) ($validation['error']['message'] ?? 'Invalid article input.'),
                ['stage' => 'validate']
            );
        }

        $resolved = aig_article_service_resolve_options($normalized);

        $feature_gate = aig_article_service_apply_feature_gates($resolved);
        if (empty($feature_gate['ok'])) {
            return aig_article_service_build_error(
                (string) ($feature_gate['error']['code'] ?? 'feature_blocked'),
                (string) ($feature_gate['error']['message'] ?? 'Feature gate blocked article generation.'),
                ['stage' => 'feature_gate']
            );
        }

        if (!function_exists('aig_article_pipeline_run')) {
            return aig_article_service_build_error(
                'missing_article_pipeline',
                'Article pipeline is not available.',
                ['stage' => 'pipeline_boot']
            );
        }

        $pipeline_result = aig_article_pipeline_run($resolved);

        if (empty($pipeline_result['ok'])) {
            return aig_article_service_build_error(
                (string) ($pipeline_result['error']['code'] ?? 'article_pipeline_failed'),
                (string) ($pipeline_result['error']['message'] ?? 'Article pipeline failed.'),
                [
                    'stage'         => 'pipeline',
                    'pipeline_meta' => is_array($pipeline_result['meta'] ?? null) ? $pipeline_result['meta'] : [],
                ]
            );
        }

        $response = aig_article_service_finalize_response($pipeline_result, $resolved);

        /**
         * Fact pack / source snapshot:
         * Panelin soldaki kutularında gerçek veri gözüksün diye burada ekleniyor.
         */
        $fact_pack = aig_article_service_build_fact_pack_snapshot($resolved);
        if (!empty($fact_pack['ok'])) {
            $response['fact_pack'] = is_array($fact_pack['fact_pack'] ?? null) ? $fact_pack['fact_pack'] : [];
            if (empty($response['article']['sources']) && !empty($response['fact_pack']['sources'])) {
                $response['article']['sources'] = (array) $response['fact_pack']['sources'];
            }
        } else {
            $response['fact_pack'] = [];
        }

        /**
         * Save flow
         */
        if (($resolved['save_mode'] ?? 'preview') !== 'preview' || !empty($resolved['save'])) {
            $save_result = aig_article_service_maybe_save_post($response, $resolved);
            $response['save_result'] = $save_result;
        } else {
            $response['save_result'] = [
                'ok'      => false,
                'skipped' => true,
                'mode'    => 'preview',
            ];
        }

        $service_ms = (int) round((microtime(true) - $started_at) * 1000);

        if (!isset($response['meta']) || !is_array($response['meta'])) {
            $response['meta'] = [];
        }
        if (!isset($response['meta']['timing']) || !is_array($response['meta']['timing'])) {
            $response['meta']['timing'] = [];
        }

        $response['meta']['timing']['article_service_ms'] = $service_ms;
        $response['meta']['build'] = defined('AIG_MODULE_BUILD') ? AIG_MODULE_BUILD : '';
        $response['meta']['version'] = defined('AIG_MODULE_VERSION') ? AIG_MODULE_VERSION : '';
        $response['meta']['request'] = [
            'topic'             => (string) ($resolved['topic'] ?? ''),
            'keyword'           => (string) ($resolved['keyword'] ?? ''),
            'category'          => (string) ($resolved['category'] ?? ''),
            'news_range'        => (string) ($resolved['news_range'] ?? ''),
            'source_limit'      => (int) ($resolved['source_limit'] ?? 0),
            'lang'              => (string) ($resolved['lang'] ?? 'tr'),
            'tone'              => (string) ($resolved['tone'] ?? ''),
            'length'            => (string) ($resolved['length'] ?? 'long'),
            'template'          => (string) ($resolved['template'] ?? ''),
            'provider'          => (string) ($resolved['provider'] ?? ''),
            'model'             => (string) ($resolved['model'] ?? ''),
            'brief'             => (string) ($resolved['brief'] ?? ''),
            'min_quality'       => (int) ($resolved['min_quality'] ?? 0),
            'auto_improve'      => !empty($resolved['auto_improve']),
            'max_attempts'      => (int) ($resolved['max_attempts'] ?? 0),
            'similarity_guard'  => !empty($resolved['similarity_guard']),
            'similarity_threshold' => (float) ($resolved['similarity_threshold'] ?? 0),
            'save_mode'         => (string) ($resolved['save_mode'] ?? 'preview'),
        ];

        if (function_exists('aig_log_write')) {
            aig_log_write('info', 'article_service_generate_ok', [
                'topic'      => $resolved['topic'] ?? '',
                'category'   => $resolved['category'] ?? '',
                'lang'       => $resolved['lang'] ?? '',
                'provider'   => $response['meta']['provider'] ?? '',
                'model'      => $response['meta']['model'] ?? '',
                'save_mode'  => $resolved['save_mode'] ?? 'preview',
                'service_ms' => $service_ms,
            ]);
        }

        return $response;
    }
}

if (!function_exists('aig_article_service_normalize_input')) {
    function aig_article_service_normalize_input(array $input): array
    {
        $default_lang = function_exists('aig_settings_get')
            ? (string) aig_settings_get('article.default_lang', 'tr')
            : 'tr';

        $default_tone = function_exists('aig_settings_get')
            ? (string) aig_settings_get('article.default_tone', 'analytical')
            : 'analytical';

        $default_length = function_exists('aig_settings_get')
            ? (string) aig_settings_get('article.default_length', 'long')
            : 'long';

        $default_template = function_exists('aig_settings_get')
            ? (string) aig_settings_get('article.default_template', 'news_basic')
            : 'news_basic';

        $auto_rewrite = function_exists('aig_settings_get')
            ? (bool) aig_settings_get('article.auto_rewrite', true)
            : true;

        $auto_seo = function_exists('aig_settings_get')
            ? (bool) aig_settings_get('article.auto_seo', true)
            : true;

        $save_mode = trim((string) ($input['save_mode'] ?? 'preview'));
        if (!in_array($save_mode, ['preview', 'draft', 'publish'], true)) {
            $save_mode = 'preview';
        }

        $provider = isset($input['provider']) ? trim((string) $input['provider']) : '';
        if ($provider === 'auto') {
            $provider = '';
        }

        $model = isset($input['model']) ? trim((string) $input['model']) : '';
        if ($model === 'auto') {
            $model = '';
        }

        $tags = $input['tags'] ?? [];
        if (!is_array($tags)) {
            $tags = [];
        }

        return [
            'task'                 => 'article_generate',
            'topic'                => trim((string) ($input['topic'] ?? '')),
            'keyword'              => trim((string) ($input['keyword'] ?? '')),
            'category'             => trim((string) ($input['category'] ?? 'tech')),
            'news_range'           => trim((string) ($input['news_range'] ?? '24h')),
            'source_limit'         => max(3, min(20, (int) ($input['source_limit'] ?? 10))),
            'language'             => trim((string) ($input['language'] ?? ($input['lang'] ?? $default_lang))),
            'lang'                 => trim((string) ($input['lang'] ?? ($input['language'] ?? $default_lang))),
            'tone'                 => trim((string) ($input['tone'] ?? $default_tone)),
            'length'               => trim((string) ($input['length'] ?? $default_length)),
            'template'             => trim((string) ($input['template'] ?? $default_template)),
            'provider'             => $provider !== '' ? $provider : null,
            'model'                => $model !== '' ? $model : null,
            'brief'                => trim((string) ($input['brief'] ?? '')),
            'rewrite'              => array_key_exists('rewrite', $input) ? !empty($input['rewrite']) : $auto_rewrite,
            'seo'                  => array_key_exists('seo', $input) ? !empty($input['seo']) : $auto_seo,
            'include_sources'      => array_key_exists('include_sources', $input) ? !empty($input['include_sources']) : true,
            'include_summary'      => array_key_exists('include_summary', $input) ? !empty($input['include_summary']) : true,
            'context_mode'         => trim((string) ($input['context_mode'] ?? 'news')),
            'user_id'              => (int) ($input['user_id'] ?? (function_exists('get_current_user_id') ? get_current_user_id() : 0)),
            'save_mode'            => $save_mode,
            'save'                 => !empty($input['save']) || $save_mode !== 'preview',
            'post_status'          => trim((string) ($input['post_status'] ?? ($save_mode === 'publish' ? 'publish' : 'draft'))),
            'category_id'          => (int) ($input['category_id'] ?? 0),
            'post_id'              => (int) ($input['post_id'] ?? 0),
            'tags'                 => array_values(array_filter(array_map('sanitize_text_field', $tags))),
            'min_quality'          => max(0, min(100, (int) ($input['min_quality'] ?? 65))),
            'auto_improve'         => !empty($input['auto_improve']),
            'max_attempts'         => max(1, min(5, (int) ($input['max_attempts'] ?? 3))),
            'similarity_guard'     => !empty($input['similarity_guard']),
            'similarity_threshold' => max(0, min(1, (float) ($input['similarity_threshold'] ?? 0.80))),
            'request_meta'         => is_array($input['request_meta'] ?? null) ? $input['request_meta'] : [],
        ];
    }
}

if (!function_exists('aig_article_service_validate_input')) {
    function aig_article_service_validate_input(array $input): array
    {
        if (trim((string) ($input['topic'] ?? '')) === '') {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'missing_topic',
                    'message' => 'Topic is required for article generation.',
                ],
            ];
        }

        $length = (string) ($input['length'] ?? 'long');
        if (!in_array($length, ['short', 'medium', 'long'], true)) {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'invalid_length',
                    'message' => 'Length must be short, medium, or long.',
                ],
            ];
        }

        $lang = trim((string) ($input['lang'] ?? ''));
        if ($lang === '') {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'missing_lang',
                    'message' => 'Language is required.',
                ],
            ];
        }

        return ['ok' => true];
    }
}

if (!function_exists('aig_article_service_resolve_options')) {
    function aig_article_service_resolve_options(array $input): array
    {
        $settings    = function_exists('aig_settings_get_all') ? aig_settings_get_all() : [];
        $feature_map = function_exists('aig_feature_map_get_all') ? aig_feature_map_get_all() : [];

        $resolved = $input;
        $resolved['settings']    = is_array($settings) ? $settings : [];
        $resolved['feature_map'] = is_array($feature_map) ? $feature_map : [];
        $resolved['build']       = defined('AIG_MODULE_BUILD') ? AIG_MODULE_BUILD : '';
        $resolved['version']     = defined('AIG_MODULE_VERSION') ? AIG_MODULE_VERSION : '';

        return $resolved;
    }
}

if (!function_exists('aig_article_service_apply_feature_gates')) {
    function aig_article_service_apply_feature_gates(array $resolved): array
    {
        $feature_map = is_array($resolved['feature_map'] ?? null) ? $resolved['feature_map'] : [];

        if (!empty($feature_map['article_generation_disabled'])) {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'article_generation_disabled',
                    'message' => 'Article generation is disabled by feature map.',
                ],
            ];
        }

        return ['ok' => true];
    }
}

if (!function_exists('aig_article_service_build_fact_pack_snapshot')) {
    function aig_article_service_build_fact_pack_snapshot(array $resolved): array
    {
        if (!function_exists('aig_news_collect') || !function_exists('aig_news_build_fact_pack')) {
            return [
                'ok'      => false,
                'fact_pack' => [],
                'error'   => 'news_fact_pack_stack_missing',
            ];
        }

        $collected = aig_news_collect([
            'category'  => (string) ($resolved['category'] ?? 'tech'),
            'range'     => (string) ($resolved['news_range'] ?? '24h'),
            'limit'     => (int) ($resolved['source_limit'] ?? 10),
            'use_cache' => true,
        ]);

        if (empty($collected['ok'])) {
            return [
                'ok'      => false,
                'fact_pack' => [],
                'error'   => $collected['errors'] ?? 'news_collect_failed',
            ];
        }

        $fact_pack = aig_news_build_fact_pack([
            'items'          => (array) ($collected['items'] ?? []),
            'category'       => (string) ($resolved['category'] ?? 'tech'),
            'range'          => (string) ($resolved['news_range'] ?? '24h'),
            'topic'          => (string) ($resolved['topic'] ?? ''),
            'max_highlights' => min(10, max(4, (int) ($resolved['source_limit'] ?? 10))),
        ]);

        return [
            'ok'        => true,
            'fact_pack' => is_array($fact_pack) ? $fact_pack : [],
            'error'     => null,
        ];
    }
}

if (!function_exists('aig_article_service_maybe_save_post')) {
    function aig_article_service_maybe_save_post(array $response, array $resolved): array
    {
        if (!function_exists('ai_article_save_post')) {
            return [
                'ok'      => false,
                'skipped' => true,
                'error'   => 'post_saver_missing',
            ];
        }

        $article = is_array($response['article'] ?? null) ? $response['article'] : [];
        $seo     = is_array($response['seo'] ?? null) ? $response['seo'] : [];
        $meta    = is_array($response['meta'] ?? null) ? $response['meta'] : [];

        $html = (string) ($article['html'] ?? ($article['content'] ?? ''));
        if (trim($html) === '') {
            return [
                'ok'    => false,
                'error' => 'empty_article_for_save',
            ];
        }

        $save_payload = [
            'title'       => (string) ($article['title'] ?? ($resolved['topic'] ?? 'AI Article')),
            'content'     => $html,
            'status'      => (string) ($resolved['post_status'] ?? 'draft'),
            'category_id' => (int) ($resolved['category_id'] ?? 0),
            'post_id'     => (int) ($resolved['post_id'] ?? 0),
            'tags'        => (array) ($resolved['tags'] ?? []),
            'meta'        => [
                'ai_topic'           => (string) ($resolved['topic'] ?? ''),
                'ai_keyword'         => (string) ($resolved['keyword'] ?? ''),
                'ai_category'        => (string) ($resolved['category'] ?? ''),
                'ai_lang'            => (string) ($resolved['lang'] ?? 'tr'),
                'ai_tone'            => (string) ($resolved['tone'] ?? ''),
                'ai_template'        => (string) ($resolved['template'] ?? ''),
                'ai_provider'        => (string) ($meta['provider'] ?? ''),
                'ai_model'           => (string) ($meta['model'] ?? ''),
                'ai_summary'         => (string) ($article['summary'] ?? ''),
                'seo_title'          => (string) ($seo['meta_title'] ?? ''),
                'seo_desc'           => (string) ($seo['meta_description'] ?? ''),
                'focus_key'          => (string) ($resolved['keyword'] ?? ''),
                'quality_score'      => (string) (($meta['quality']['score'] ?? '') ?: ''),
                'quality_label'      => (string) (($meta['quality']['label'] ?? '') ?: ''),
                'save_mode'          => (string) ($resolved['save_mode'] ?? 'preview'),
                'generated_build'    => (string) ($meta['build'] ?? ''),
                'generated_version'  => (string) ($meta['version'] ?? ''),
            ],
        ];

        return ai_article_save_post($save_payload);
    }
}

if (!function_exists('aig_article_service_finalize_response')) {
    function aig_article_service_finalize_response(array $pipeline_result, array $resolved_options): array
    {
        $article = is_array($pipeline_result['article'] ?? null) ? $pipeline_result['article'] : [];
        $seo     = is_array($pipeline_result['seo'] ?? null) ? $pipeline_result['seo'] : [];
        $meta    = is_array($pipeline_result['meta'] ?? null) ? $pipeline_result['meta'] : [];

        $quality = is_array($meta['quality'] ?? null) ? $meta['quality'] : [];

        return [
            'ok' => true,
            'article' => [
                'title'         => (string) ($article['title'] ?? ''),
                'content'       => (string) ($article['html'] ?? ($article['content'] ?? '')),
                'plain_content' => (string) ($article['content'] ?? ''),
                'html'          => (string) ($article['html'] ?? ($article['content'] ?? '')),
                'summary'       => (string) ($article['summary'] ?? ''),
                'sections'      => is_array($article['sections'] ?? null) ? $article['sections'] : [],
                'sources'       => is_array($article['sources'] ?? null) ? $article['sources'] : [],
                'lang'          => (string) ($article['lang'] ?? ($resolved_options['lang'] ?? 'tr')),
                'category'      => (string) ($article['category'] ?? ($resolved_options['category'] ?? 'general')),
                'topic'         => (string) ($article['topic'] ?? ($resolved_options['topic'] ?? '')),
                'quality_score' => (int) ($quality['score'] ?? 0),
            ],
            'seo' => [
                'meta_title'       => (string) ($seo['meta_title'] ?? ''),
                'meta_description' => (string) ($seo['meta_description'] ?? ''),
                'faq'              => is_array($seo['faq'] ?? null) ? $seo['faq'] : [],
                'schema'           => is_array($seo['schema'] ?? null) ? $seo['schema'] : [],
                'keywords'         => is_array($seo['keywords'] ?? null) ? $seo['keywords'] : [],
            ],
            'meta' => [
                'provider'      => (string) ($meta['provider'] ?? ''),
                'model'         => (string) ($meta['model'] ?? ''),
                'usage'         => is_array($meta['usage'] ?? null) ? $meta['usage'] : [],
                'quality'       => $quality,
                'timing'        => is_array($meta['timing'] ?? null) ? $meta['timing'] : [],
                'build'         => (string) ($meta['build'] ?? ($resolved_options['build'] ?? '')),
                'version'       => (string) ($meta['version'] ?? ($resolved_options['version'] ?? '')),
                'fallback_used' => !empty($meta['fallback_used']),
            ],
            'fact_pack'   => [],
            'save_result' => [
                'ok'      => false,
                'skipped' => true,
            ],
            'error' => null,
        ];
    }
}

if (!function_exists('aig_article_service_build_error')) {
    function aig_article_service_build_error(string $code, string $message, array $meta = []): array
    {
        if (function_exists('aig_log_write')) {
            aig_log_write('error', 'article_service_generate_error', [
                'code'    => $code,
                'message' => $message,
                'meta'    => $meta,
            ]);
        }

        return [
            'ok' => false,
            'article' => [
                'title'         => '',
                'content'       => '',
                'plain_content' => '',
                'html'          => '',
                'summary'       => '',
                'sections'      => [],
                'sources'       => [],
                'lang'          => 'tr',
                'category'      => '',
                'topic'         => '',
                'quality_score' => 0,
            ],
            'seo' => [
                'meta_title'       => '',
                'meta_description' => '',
                'faq'              => [],
                'schema'           => [],
                'keywords'         => [],
            ],
            'fact_pack' => [],
            'save_result' => [
                'ok'      => false,
                'skipped' => true,
            ],
            'meta' => $meta,
            'error' => [
                'code'    => $code,
                'message' => $message,
            ],
        ];
    }
}