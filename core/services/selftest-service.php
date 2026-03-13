<?php
/**
 * AI Article Generator V6
 * Selftest Service
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_selftest_service_provider_status')) {
    function aig_selftest_service_provider_status(): array
    {
        $checks = [
            'registry_loaded' => function_exists('aig_provider_registry_get_all') || function_exists('ai_article_provider_registry'),
            'gateway_loaded'  => function_exists('aig_gateway_generate') || function_exists('ai_article_gateway_generate'),
        ];

        $providers = [];
        if (function_exists('aig_provider_registry_get_all')) {
            $providers = aig_provider_registry_get_all();
        } elseif (function_exists('ai_article_provider_registry')) {
            $providers = ai_article_provider_registry();
        }

        return [
            'ok'        => !empty($checks['registry_loaded']),
            'checks'    => $checks,
            'providers' => is_array($providers) ? array_keys($providers) : [],
        ];
    }
}

if (!function_exists('aig_selftest_service_news_status')) {
    function aig_selftest_service_news_status(): array
    {
        $checks = [
            'sources_loader' => function_exists('aig_news_get_all_sources') || function_exists('ai_news_get_all_sources'),
            'collector'      => function_exists('aig_news_collect') || function_exists('ai_news_collect'),
            'fact_pack'      => function_exists('aig_news_build_fact_pack') || function_exists('ai_news_build_fact_pack'),
        ];

        $categories = [];
        if (function_exists('aig_news_get_categories')) {
            $categories = aig_news_get_categories();
        }

        return [
            'ok'         => !empty($checks['sources_loader']) && !empty($checks['collector']),
            'checks'     => $checks,
            'categories' => is_array($categories) ? $categories : [],
        ];
    }
}

if (!function_exists('aig_selftest_service_storage_status')) {
    function aig_selftest_service_storage_status(): array
    {
        if (!defined('AIG_MODULE_DIR')) {
            return [
                'ok'     => false,
                'checks' => ['module_dir_defined' => false],
            ];
        }

        $files = [
            'storage/settings.json',
            'storage/providers.json',
            'storage/models.json',
            'storage/router.json',
        ];

        $checks = [];
        foreach ($files as $file) {
            $path = trailingslashit(AIG_MODULE_DIR) . ltrim($file, '/');
            $checks[$file] = file_exists($path);
        }

        return [
            'ok'     => !in_array(false, $checks, true),
            'checks' => $checks,
        ];
    }
}

if (!function_exists('aig_selftest_service_cache_status')) {
    function aig_selftest_service_cache_status(): array
    {
        if (!defined('AIG_MODULE_DIR')) {
            return [
                'ok'     => false,
                'checks' => ['module_dir_defined' => false],
            ];
        }

        $cache_dir = trailingslashit(AIG_MODULE_DIR) . 'cache';
        $checks = [
            'cache_dir_exists'    => is_dir($cache_dir),
            'cache_dir_writable'  => is_dir($cache_dir) ? wp_is_writable($cache_dir) : false,
        ];

        return [
            'ok'     => !empty($checks['cache_dir_exists']) && !empty($checks['cache_dir_writable']),
            'checks' => $checks,
        ];
    }
}

if (!function_exists('aig_selftest_service_panel_status')) {
    function aig_selftest_service_panel_status(): array
    {
        $checks = [
            'article_service'   => function_exists('aig_article_service_generate'),
            'pipeline'          => function_exists('aig_article_pipeline_run'),
            'rewrite_pipeline'  => function_exists('aig_rewrite_pipeline_run'),
            'seo_pipeline'      => function_exists('aig_seo_pipeline_run'),
        ];

        return [
            'ok'     => !in_array(false, $checks, true),
            'checks' => $checks,
        ];
    }
}

if (!function_exists('aig_selftest_service_run')) {
    function aig_selftest_service_run(array $args = []): array
    {
        $result = [
            'ok' => true,
            'checks' => [
                'providers' => aig_selftest_service_provider_status(),
                'news'      => aig_selftest_service_news_status(),
                'storage'   => aig_selftest_service_storage_status(),
                'cache'     => aig_selftest_service_cache_status(),
                'panel'     => aig_selftest_service_panel_status(),
            ],
        ];

        foreach ($result['checks'] as $section) {
            if (empty($section['ok'])) {
                $result['ok'] = false;
                break;
            }
        }

        aig_log_write('INFO', 'aig_selftest_service_run', [
            'ok' => $result['ok'],
        ]);

        return $result;
    }
}