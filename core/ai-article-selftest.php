<?php
/**
 * AI Article Generator — Self Test / Diagnostics
 *
 * Purpose:
 * - test providers individually
 * - test router/default route
 * - expose diagnostics snapshot for admin panel
 * - centralize health/latency visibility
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_selftest_now_mysql')) {
    function aig_selftest_now_mysql(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}

if (!function_exists('aig_selftest_default_prompt')) {
    function aig_selftest_default_prompt(): string
    {
        return 'Return only the word OK.';
    }
}

if (!function_exists('aig_selftest_normalize_result')) {
    function aig_selftest_normalize_result(array $result, array $extra = []): array
    {
        $normalized = [
            'ok'            => !empty($result['ok']),
            'error'         => sanitize_key((string) ($result['error'] ?? '')),
            'detail'        => $result['detail'] ?? '',
            'provider'      => sanitize_key((string) ($result['provider'] ?? '')),
            'provider_name' => sanitize_text_field((string) ($result['provider_name'] ?? '')),
            'model'         => sanitize_text_field((string) ($result['model'] ?? '')),
            'model_id'      => sanitize_key((string) ($result['model_id'] ?? '')),
            'http_status'   => max(0, (int) ($result['http_status'] ?? 0)),
            'latency_ms'    => max(0, (int) ($result['latency_ms'] ?? 0)),
            'tokens_input'  => max(0, (int) ($result['tokens_input'] ?? 0)),
            'tokens_output' => max(0, (int) ($result['tokens_output'] ?? 0)),
            'tokens_total'  => max(0, (int) ($result['tokens_total'] ?? 0)),
            'request_id'    => sanitize_text_field((string) ($result['request_id'] ?? '')),
            'content'       => isset($result['content']) && is_string($result['content']) ? $result['content'] : '',
            'meta'          => isset($extra['meta']) && is_array($extra['meta']) ? $extra['meta'] : [],
            'ts'            => aig_selftest_now_mysql(),
        ];

        if (!empty($extra)) {
            $normalized = aig_settings_deep_merge($normalized, $extra);
        }

        return $normalized;
    }
}

if (!function_exists('aig_selftest_build_messages')) {
    function aig_selftest_build_messages(string $prompt = ''): array
    {
        $prompt = trim($prompt);
        if ($prompt === '') {
            $prompt = aig_selftest_default_prompt();
        }

        return [
            [
                'role'    => 'system',
                'content' => 'You are a health-check assistant. Return a minimal valid answer.',
            ],
            [
                'role'    => 'user',
                'content' => $prompt,
            ],
        ];
    }
}

if (!function_exists('aig_selftest_provider_payload')) {
    function aig_selftest_provider_payload(string $providerId, array $provider, array $model, array $args = []): array
    {
        $prompt = !empty($args['prompt']) && is_string($args['prompt'])
            ? $args['prompt']
            : aig_selftest_default_prompt();

        return [
            'provider_id'  => $providerId,
            'model_id'     => sanitize_key((string) ($model['id'] ?? '')),
            'provider'     => $provider,
            'model'        => $model,
            'base_url'     => (string) ($provider['base_url'] ?? ''),
            'api_key'      => (string) ($provider['api_key'] ?? ''),
            'headers'      => isset($provider['headers']) && is_array($provider['headers']) ? $provider['headers'] : [],
            'timeout'      => max(5, (int) ($provider['timeout'] ?? 30)),
            'messages'     => aig_selftest_build_messages($prompt),
            'temperature'  => isset($args['temperature']) ? (float) $args['temperature'] : 0.1,
            'max_tokens'   => isset($args['max_tokens']) ? max(1, (int) $args['max_tokens']) : 12,
            'task_type'    => 'selftest',
            'preset'       => sanitize_key((string) ($args['preset'] ?? 'free_first')),
            'request_id'   => function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('aig_selftest_', true),
            'debug'        => !empty($args['debug']),
        ];
    }
}

if (!function_exists('aig_selftest_pick_provider_model')) {
    function aig_selftest_pick_provider_model(string $providerId): array
    {
        $providerId = sanitize_key($providerId);
        $models = aig_engines_get_provider_models($providerId, [
            'enabled' => true,
        ]);

        foreach ($models as $modelId => $model) {
            if (!empty($model['enabled']) && !empty($model['model'])) {
                return $model;
            }
        }

        foreach ($models as $model) {
            if (!empty($model['model'])) {
                return $model;
            }
        }

        return [];
    }
}

if (!function_exists('aig_selftest_test_provider')) {
    /**
     * Tests one provider directly through gateway.
     */
    function aig_selftest_test_provider(string $providerId, array $args = []): array
    {
        $providerId = sanitize_key($providerId);
        $provider   = aig_engines_get_provider($providerId);

        if (empty($provider)) {
            return aig_selftest_normalize_result([
                'ok'    => false,
                'error' => 'provider_not_found',
                'detail'=> 'Provider is not registered',
            ], [
                'provider' => $providerId,
            ]);
        }

        if (empty($provider['enabled'])) {
            return aig_selftest_normalize_result([
                'ok'    => false,
                'error' => 'provider_disabled',
                'detail'=> 'Provider is disabled',
            ], [
                'provider'      => $providerId,
                'provider_name' => (string) ($provider['name'] ?? ''),
            ]);
        }

        $model = aig_selftest_pick_provider_model($providerId);

        if (empty($model)) {
            return aig_selftest_normalize_result([
                'ok'    => false,
                'error' => 'model_not_found',
                'detail'=> 'No enabled model found for provider',
            ], [
                'provider'      => $providerId,
                'provider_name' => (string) ($provider['name'] ?? ''),
            ]);
        }

        $payload = aig_selftest_provider_payload($providerId, $provider, $model, $args);

        if (!function_exists('aig_gateway_generate')) {
            return aig_selftest_normalize_result([
                'ok'    => false,
                'error' => 'gateway_unavailable',
                'detail'=> 'Gateway function is missing',
            ], [
                'provider'      => $providerId,
                'provider_name' => (string) ($provider['name'] ?? ''),
                'model'         => (string) ($model['model'] ?? ''),
                'model_id'      => (string) ($model['id'] ?? ''),
            ]);
        }

        $result = aig_gateway_generate($payload);

        if (!empty($result['ok'])) {
            if (function_exists('aig_engines_mark_provider_success')) {
                aig_engines_mark_provider_success($providerId, (int) ($result['latency_ms'] ?? 0));
            }
        } else {
            if (function_exists('aig_engines_mark_provider_failure')) {
                aig_engines_mark_provider_failure(
                    $providerId,
                    (string) ($result['error'] ?? 'unknown_error'),
                    is_scalar($result['detail'] ?? null) ? (string) $result['detail'] : '',
                    (int) ($result['latency_ms'] ?? 0)
                );
            }
        }

        return aig_selftest_normalize_result($result, [
            'provider'      => $providerId,
            'provider_name' => (string) ($provider['name'] ?? ''),
            'model'         => (string) ($result['model'] ?? ($model['model'] ?? '')),
            'model_id'      => (string) ($result['model_id'] ?? ($model['id'] ?? '')),
            'meta'          => [
                'test_type' => 'provider_direct',
            ],
        ]);
    }
}

if (!function_exists('aig_selftest_test_router')) {
    /**
     * Tests the default router flow with preset selection.
     */
    function aig_selftest_test_router(array $args = []): array
    {
        if (!function_exists('aig_router_generate')) {
            return aig_selftest_normalize_result([
                'ok'    => false,
                'error' => 'router_unavailable',
                'detail'=> 'Router function is missing',
            ], [
                'meta' => [
                    'test_type' => 'router',
                ],
            ]);
        }

        $prompt = !empty($args['prompt']) && is_string($args['prompt'])
            ? $args['prompt']
            : aig_selftest_default_prompt();

        $request = [
            'task_type'        => 'selftest',
            'preset'           => sanitize_key((string) ($args['preset'] ?? aig_settings_get('llm.defaults.preset', 'free_first'))),
            'required_quality' => sanitize_key((string) ($args['required_quality'] ?? 'medium')),
            'max_budget'       => $args['max_budget'] ?? null,
            'max_latency'      => isset($args['max_latency']) ? (int) $args['max_latency'] : null,
            'messages'         => aig_selftest_build_messages($prompt),
            'temperature'      => isset($args['temperature']) ? (float) $args['temperature'] : 0.1,
            'max_tokens'       => isset($args['max_tokens']) ? max(1, (int) $args['max_tokens']) : 12,
            'min_context'      => 0,
            'debug'            => !empty($args['debug']),
        ];

        $result = aig_router_generate($request);

        return aig_selftest_normalize_result($result, [
            'meta' => [
                'test_type' => 'router',
                'attempts'  => isset($result['attempts']) && is_array($result['attempts']) ? $result['attempts'] : [],
                'route'     => isset($result['route']) && is_array($result['route']) ? $result['route'] : [],
            ],
        ]);
    }
}

if (!function_exists('aig_selftest_test_default_route')) {
    function aig_selftest_test_default_route(): array
    {
        return aig_selftest_test_router([
            'preset'      => aig_settings_get('llm.defaults.preset', 'free_first'),
            'temperature' => 0.1,
            'max_tokens'  => 12,
        ]);
    }
}

if (!function_exists('aig_selftest_test_all_providers')) {
    function aig_selftest_test_all_providers(array $args = []): array
    {
        $providers = aig_engines_get_active_providers(false);
        $results   = [];

        foreach ($providers as $providerId => $provider) {
            $results[$providerId] = aig_selftest_test_provider($providerId, $args);
        }

        return $results;
    }
}

if (!function_exists('aig_selftest_provider_card')) {
    function aig_selftest_provider_card(string $providerId): array
    {
        $provider = aig_engines_get_provider($providerId);

        if (empty($provider)) {
            return [
                'provider_id' => sanitize_key($providerId),
                'exists'      => false,
            ];
        }

        $health       = isset($provider['health']) && is_array($provider['health']) ? $provider['health'] : [];
        $lastSuccess  = function_exists('aig_usage_last_success_for_provider')
            ? aig_usage_last_success_for_provider($providerId)
            : [];
        $lastError    = function_exists('aig_usage_last_error_for_provider')
            ? aig_usage_last_error_for_provider($providerId)
            : [];
        $usageSnap    = function_exists('aig_usage_provider_snapshot')
            ? aig_usage_provider_snapshot($providerId, max(1, (int) aig_settings_get('llm.billing.dashboard_days', 14)))
            : ['totals' => []];

        return [
            'provider_id'           => $providerId,
            'exists'                => true,
            'name'                  => (string) ($provider['name'] ?? ucfirst($providerId)),
            'enabled'               => !empty($provider['enabled']),
            'healthy'               => !empty($provider['healthy']),
            'transport'             => (string) ($provider['transport'] ?? ''),
            'base_url'              => (string) ($provider['base_url'] ?? ''),
            'priority'              => (int) ($provider['priority'] ?? 999),
            'tags'                  => isset($provider['tags']) && is_array($provider['tags']) ? $provider['tags'] : [],
            'health_status'         => sanitize_key((string) ($health['status'] ?? 'unknown')),
            'last_test_at'          => (string) ($health['last_test_at'] ?? ''),
            'last_success_at'       => (string) ($health['last_success_at'] ?? ''),
            'last_error_class'      => sanitize_key((string) ($health['last_error_class'] ?? '')),
            'last_error_message'    => sanitize_text_field((string) ($health['last_error_message'] ?? '')),
            'last_latency_ms'       => max(0, (int) ($health['last_latency_ms'] ?? 0)),
            'consecutive_failures'  => max(0, (int) ($health['consecutive_failures'] ?? 0)),
            'disabled_until'        => sanitize_text_field((string) ($health['disabled_until'] ?? '')),
            'usage_totals'          => $usageSnap['totals'] ?? [],
            'last_success_event'    => $lastSuccess,
            'last_error_event'      => $lastError,
            'models'                => array_keys(aig_engines_get_provider_models($providerId)),
        ];
    }
}

if (!function_exists('aig_selftest_diagnostics_snapshot')) {
    function aig_selftest_diagnostics_snapshot(): array
    {
        $providerIds = aig_engines_get_enabled_provider_ids(false);
        $providerCards = [];

        foreach ($providerIds as $providerId) {
            $providerCards[$providerId] = aig_selftest_provider_card($providerId);
        }

        $defaultProviderId = aig_engines_get_default_provider_id();
        $defaultModelId    = aig_engines_get_default_model_id();

        $usageSummary = function_exists('aig_usage_panel_summary')
            ? aig_usage_panel_summary()
            : [];

        return [
            'ts'                => aig_selftest_now_mysql(),
            'defaults'          => [
                'provider_id' => $defaultProviderId,
                'model_id'    => $defaultModelId,
                'preset'      => aig_settings_get('llm.defaults.preset', 'free_first'),
            ],
            'routing'           => aig_settings_get('llm.routing', []),
            'providers'         => $providerCards,
            'usage_summary'     => $usageSummary,
            'recent_failures'   => function_exists('aig_usage_recent_failures') ? aig_usage_recent_failures(20) : [],
            'engine_snapshot'   => function_exists('aig_engines_debug_snapshot') ? aig_engines_debug_snapshot() : [],
        ];
    }
}

if (!function_exists('aig_selftest_run_full')) {
    /**
     * Full diagnostics run:
     * - test each enabled provider
     * - test router default flow
     */
    function aig_selftest_run_full(array $args = []): array
    {
        $providerResults = aig_selftest_test_all_providers($args);
        $routerResult    = aig_selftest_test_router($args);

        $successCount = 0;
        $errorCount   = 0;

        foreach ($providerResults as $result) {
            if (!empty($result['ok'])) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        $summary = [
            'ts'                => aig_selftest_now_mysql(),
            'provider_results'  => $providerResults,
            'router_result'     => $routerResult,
            'providers_ok'      => $successCount,
            'providers_failed'  => $errorCount,
            'diagnostics'       => aig_selftest_diagnostics_snapshot(),
        ];

        if (function_exists('ai_article_log')) {
            ai_article_log('selftest_run_full', [
                'providers_ok'     => $successCount,
                'providers_failed' => $errorCount,
                'router_ok'        => !empty($routerResult['ok']),
            ], !empty($routerResult['ok']) ? 'info' : 'warn');
        }

        return $summary;
    }
}

if (!class_exists('AI_Article_SelfTest')) {
    class AI_Article_SelfTest
    {
        public static function test_provider(string $providerId, array $args = []): array
        {
            return aig_selftest_test_provider($providerId, $args);
        }

        public static function test_router(array $args = []): array
        {
            return aig_selftest_test_router($args);
        }

        public static function run_full(array $args = []): array
        {
            return aig_selftest_run_full($args);
        }

        public static function diagnostics(): array
        {
            return aig_selftest_diagnostics_snapshot();
        }
    }
}