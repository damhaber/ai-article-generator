<?php
/**
 * AI Article Generator — Engines Registry
 *
 * Purpose:
 * - expose provider registry
 * - expose model catalog
 * - provide active / filtered engine lookups
 * - keep compatibility with legacy "engines.items"
 * - support router scoring inputs
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_engines_allowed_tiers')) {
    function aig_engines_allowed_tiers(): array
    {
        return ['free', 'cheap', 'balanced', 'premium', 'custom'];
    }
}

if (!function_exists('aig_engines_allowed_quality')) {
    function aig_engines_allowed_quality(): array
    {
        return ['low', 'medium', 'high', 'ultra'];
    }
}

if (!function_exists('aig_engines_allowed_speed')) {
    function aig_engines_allowed_speed(): array
    {
        return ['slow', 'medium', 'fast'];
    }
}

if (!function_exists('aig_engine_now_mysql')) {
    function aig_engine_now_mysql(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}

if (!function_exists('aig_engine_is_provider_healthy')) {
    function aig_engine_is_provider_healthy(array $provider): bool
    {
        $health = isset($provider['health']) && is_array($provider['health']) ? $provider['health'] : [];
        $status = sanitize_key((string) ($health['status'] ?? 'unknown'));

        if ($status === 'down' || $status === 'disabled') {
            return false;
        }

        $disabledUntil = (string) ($health['disabled_until'] ?? '');
        if ($disabledUntil !== '') {
            $disabledTs = strtotime($disabledUntil . ' UTC');
            if ($disabledTs !== false && time() < $disabledTs) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('aig_engine_provider_matches_tier')) {
    function aig_engine_provider_matches_tier(array $provider, array $tiers): bool
    {
        if (empty($tiers)) {
            return true;
        }

        $providerTags = isset($provider['tags']) && is_array($provider['tags']) ? $provider['tags'] : [];
        $providerTags = array_map('sanitize_key', $providerTags);

        foreach ($tiers as $tier) {
            $tier = sanitize_key((string) $tier);
            if (in_array($tier, $providerTags, true)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('aig_engine_model_matches_filters')) {
    function aig_engine_model_matches_filters(array $model, array $filters = []): bool
    {
        if (isset($filters['enabled']) && (bool) $filters['enabled'] !== !empty($model['enabled'])) {
            return false;
        }

        if (!empty($filters['provider'])) {
            $provider = sanitize_key((string) $filters['provider']);
            if (($model['provider'] ?? '') !== $provider) {
                return false;
            }
        }

        if (!empty($filters['providers']) && is_array($filters['providers'])) {
            $providers = array_values(array_filter(array_map('sanitize_key', $filters['providers'])));
            if (!in_array((string) ($model['provider'] ?? ''), $providers, true)) {
                return false;
            }
        }

        if (!empty($filters['tiers']) && is_array($filters['tiers'])) {
            $tiers = array_values(array_filter(array_map('sanitize_key', $filters['tiers'])));
            if (!in_array((string) ($model['tier'] ?? ''), $tiers, true)) {
                return false;
            }
        }

        if (!empty($filters['quality'])) {
            $quality = sanitize_key((string) $filters['quality']);
            $current = sanitize_key((string) ($model['quality'] ?? 'medium'));

            $rank = [
                'low'    => 1,
                'medium' => 2,
                'high'   => 3,
                'ultra'  => 4,
            ];

            if (($rank[$current] ?? 0) < ($rank[$quality] ?? 0)) {
                return false;
            }
        }

        if (!empty($filters['min_context'])) {
            $minContext = (int) $filters['min_context'];
            if ((int) ($model['context_window'] ?? 0) < $minContext) {
                return false;
            }
        }

        if (array_key_exists('json_support', $filters)) {
            if ((bool) $filters['json_support'] !== !empty($model['json_support'])) {
                return false;
            }
        }

        if (array_key_exists('vision_support', $filters)) {
            if ((bool) $filters['vision_support'] !== !empty($model['vision_support'])) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('aig_engines_registry')) {
    /**
     * Full normalized runtime registry.
     */
    function aig_engines_registry(): array
    {
        $settings = function_exists('aig_settings_read') ? aig_settings_read() : [];
        $providers = isset($settings['llm']['providers']) && is_array($settings['llm']['providers'])
            ? $settings['llm']['providers']
            : [];
        $models = isset($settings['llm']['models']) && is_array($settings['llm']['models'])
            ? $settings['llm']['models']
            : [];

        $normalizedProviders = [];
        foreach ($providers as $providerId => $provider) {
            if (!is_array($provider)) {
                continue;
            }

            $providerId = sanitize_key((string) $providerId);

            if (function_exists('aig_settings_sanitize_provider')) {
                $provider = aig_settings_sanitize_provider($providerId, $provider);
            }

            $provider['id'] = $providerId;
            $provider['healthy'] = aig_engine_is_provider_healthy($provider);

            $normalizedProviders[$providerId] = $provider;
        }

        $normalizedModels = [];
        foreach ($models as $modelId => $model) {
            if (!is_array($model)) {
                continue;
            }

            $modelId = sanitize_key((string) $modelId);

            if (function_exists('aig_settings_sanitize_model')) {
                $model = aig_settings_sanitize_model($modelId, $model);
            }

            $providerId = sanitize_key((string) ($model['provider'] ?? ''));
            $provider   = $normalizedProviders[$providerId] ?? null;

            $model['id'] = $modelId;
            $model['provider_enabled'] = $provider ? !empty($provider['enabled']) : false;
            $model['provider_healthy'] = $provider ? !empty($provider['healthy']) : false;
            $model['runtime_enabled']  = !empty($model['enabled']) && !empty($model['provider_enabled']);

            $normalizedModels[$modelId] = $model;
        }

        $defaults = isset($settings['llm']['defaults']) && is_array($settings['llm']['defaults'])
            ? $settings['llm']['defaults']
            : [];

        $routing = isset($settings['llm']['routing']) && is_array($settings['llm']['routing'])
            ? $settings['llm']['routing']
            : [];

        $presets = isset($settings['llm']['presets']) && is_array($settings['llm']['presets'])
            ? $settings['llm']['presets']
            : [];

        return [
            'providers' => $normalizedProviders,
            'models'    => $normalizedModels,
            'defaults'  => $defaults,
            'routing'   => $routing,
            'presets'   => $presets,
            'meta'      => [
                'provider_count' => count($normalizedProviders),
                'model_count'    => count($normalizedModels),
                'generated_at'   => aig_engine_now_mysql(),
            ],
        ];
    }
}

if (!function_exists('aig_engines_get_providers')) {
    function aig_engines_get_providers(array $filters = []): array
    {
        $registry  = aig_engines_registry();
        $providers = $registry['providers'] ?? [];

        $out = [];

        foreach ($providers as $providerId => $provider) {
            if (!is_array($provider)) {
                continue;
            }

            if (isset($filters['enabled']) && (bool) $filters['enabled'] !== !empty($provider['enabled'])) {
                continue;
            }

            if (isset($filters['healthy']) && (bool) $filters['healthy'] !== !empty($provider['healthy'])) {
                continue;
            }

            if (!empty($filters['transport'])) {
                $transport = sanitize_key((string) $filters['transport']);
                if (($provider['transport'] ?? '') !== $transport) {
                    continue;
                }
            }

            if (!empty($filters['tags']) && is_array($filters['tags'])) {
                $needTags = array_values(array_filter(array_map('sanitize_key', $filters['tags'])));
                $haveTags = isset($provider['tags']) && is_array($provider['tags'])
                    ? array_values(array_filter(array_map('sanitize_key', $provider['tags'])))
                    : [];

                $tagMatch = false;
                foreach ($needTags as $tag) {
                    if (in_array($tag, $haveTags, true)) {
                        $tagMatch = true;
                        break;
                    }
                }

                if (!$tagMatch) {
                    continue;
                }
            }

            if (!empty($filters['tiers']) && is_array($filters['tiers'])) {
                if (!aig_engine_provider_matches_tier($provider, $filters['tiers'])) {
                    continue;
                }
            }

            $out[$providerId] = $provider;
        }

        uasort($out, function ($a, $b) {
            $pa = (int) ($a['priority'] ?? 999);
            $pb = (int) ($b['priority'] ?? 999);

            if ($pa === $pb) {
                return strcmp((string) ($a['id'] ?? ''), (string) ($b['id'] ?? ''));
            }

            return $pa <=> $pb;
        });

        return $out;
    }
}

if (!function_exists('aig_engines_get_provider')) {
    function aig_engines_get_provider(string $providerId): array
    {
        $providerId = sanitize_key($providerId);
        $providers  = aig_engines_get_providers();

        return isset($providers[$providerId]) && is_array($providers[$providerId])
            ? $providers[$providerId]
            : [];
    }
}

if (!function_exists('aig_engines_get_models')) {
    function aig_engines_get_models(array $filters = []): array
    {
        $registry = aig_engines_registry();
        $models   = $registry['models'] ?? [];

        $out = [];

        foreach ($models as $modelId => $model) {
            if (!is_array($model)) {
                continue;
            }

            if (!aig_engine_model_matches_filters($model, $filters)) {
                continue;
            }

            if (isset($filters['runtime_enabled']) && (bool) $filters['runtime_enabled'] !== !empty($model['runtime_enabled'])) {
                continue;
            }

            if (isset($filters['provider_enabled']) && (bool) $filters['provider_enabled'] !== !empty($model['provider_enabled'])) {
                continue;
            }

            if (isset($filters['provider_healthy']) && (bool) $filters['provider_healthy'] !== !empty($model['provider_healthy'])) {
                continue;
            }

            $out[$modelId] = $model;
        }

        uasort($out, function ($a, $b) {
            $providerA = aig_engines_get_provider((string) ($a['provider'] ?? ''));
            $providerB = aig_engines_get_provider((string) ($b['provider'] ?? ''));

            $priorityA = (int) ($providerA['priority'] ?? 999);
            $priorityB = (int) ($providerB['priority'] ?? 999);

            if ($priorityA !== $priorityB) {
                return $priorityA <=> $priorityB;
            }

            $tierRank = [
                'free'     => 1,
                'cheap'    => 2,
                'balanced' => 3,
                'premium'  => 4,
                'custom'   => 5,
            ];

            $rankA = $tierRank[(string) ($a['tier'] ?? 'custom')] ?? 999;
            $rankB = $tierRank[(string) ($b['tier'] ?? 'custom')] ?? 999;

            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            return strcmp((string) ($a['id'] ?? ''), (string) ($b['id'] ?? ''));
        });

        return $out;
    }
}

if (!function_exists('aig_engines_get_model')) {
    function aig_engines_get_model(string $modelId): array
    {
        $modelId = sanitize_key($modelId);
        $models  = aig_engines_get_models();

        return isset($models[$modelId]) && is_array($models[$modelId])
            ? $models[$modelId]
            : [];
    }
}

if (!function_exists('aig_engines_get_active_providers')) {
    function aig_engines_get_active_providers(bool $healthyOnly = false): array
    {
        return aig_engines_get_providers([
            'enabled' => true,
            'healthy' => $healthyOnly ? true : null,
        ]);
    }
}

if (!function_exists('aig_engines_get_active_models')) {
    function aig_engines_get_active_models(array $filters = []): array
    {
        $filters['runtime_enabled'] = true;
        return aig_engines_get_models($filters);
    }
}

if (!function_exists('aig_engines_get_preset')) {
    function aig_engines_get_preset(?string $preset = null): array
    {
        $registry = aig_engines_registry();
        $presets  = isset($registry['presets']) && is_array($registry['presets']) ? $registry['presets'] : [];
        $defaults = isset($registry['defaults']) && is_array($registry['defaults']) ? $registry['defaults'] : [];

        $presetId = $preset ? sanitize_key($preset) : sanitize_key((string) ($defaults['preset'] ?? 'free_first'));

        if (!empty($presets[$presetId]) && is_array($presets[$presetId])) {
            $item = $presets[$presetId];
            $item['id'] = sanitize_key((string) ($item['id'] ?? $presetId));
            return $item;
        }

        return $presets['free_first'] ?? [];
    }
}

if (!function_exists('aig_engines_get_default_provider_id')) {
    function aig_engines_get_default_provider_id(): string
    {
        $registry = aig_engines_registry();
        $defaults = isset($registry['defaults']) && is_array($registry['defaults']) ? $registry['defaults'] : [];

        return sanitize_key((string) ($defaults['provider'] ?? 'local'));
    }
}

if (!function_exists('aig_engines_get_default_model_id')) {
    function aig_engines_get_default_model_id(): string
    {
        $registry = aig_engines_registry();
        $defaults = isset($registry['defaults']) && is_array($registry['defaults']) ? $registry['defaults'] : [];

        return sanitize_key((string) ($defaults['model'] ?? 'local-default'));
    }
}

if (!function_exists('aig_engines_get_default_model')) {
    function aig_engines_get_default_model(): array
    {
        return aig_engines_get_model(aig_engines_get_default_model_id());
    }
}

if (!function_exists('aig_engines_get_default_provider')) {
    function aig_engines_get_default_provider(): array
    {
        return aig_engines_get_provider(aig_engines_get_default_provider_id());
    }
}

if (!function_exists('aig_engines_get_provider_models')) {
    function aig_engines_get_provider_models(string $providerId, array $filters = []): array
    {
        $filters['provider'] = sanitize_key($providerId);
        return aig_engines_get_models($filters);
    }
}

if (!function_exists('aig_engines_get_enabled_provider_ids')) {
    function aig_engines_get_enabled_provider_ids(bool $healthyOnly = false): array
    {
        return array_keys(aig_engines_get_active_providers($healthyOnly));
    }
}

if (!function_exists('aig_engines_get_enabled_model_ids')) {
    function aig_engines_get_enabled_model_ids(array $filters = []): array
    {
        return array_keys(aig_engines_get_active_models($filters));
    }
}

if (!function_exists('aig_engines_resolve_provider_for_model')) {
    function aig_engines_resolve_provider_for_model(string $modelId): array
    {
        $model = aig_engines_get_model($modelId);

        if (empty($model)) {
            return [];
        }

        return aig_engines_get_provider((string) ($model['provider'] ?? ''));
    }
}

if (!function_exists('aig_engines_build_candidate_routes')) {
    /**
     * Produces a simple ordered candidate list for router usage.
     *
     * Each route item:
     * - provider_id
     * - model_id
     * - provider
     * - model
     */
    function aig_engines_build_candidate_routes(array $request = []): array
    {
        $presetId        = !empty($request['preset']) ? sanitize_key((string) $request['preset']) : null;
        $requiredQuality = !empty($request['required_quality']) ? sanitize_key((string) $request['required_quality']) : '';
        $minContext      = !empty($request['min_context']) ? (int) $request['min_context'] : 0;
        $needJson        = array_key_exists('json_support', $request) ? (bool) $request['json_support'] : null;
        $needVision      = array_key_exists('vision_support', $request) ? (bool) $request['vision_support'] : null;

        $preset = aig_engines_get_preset($presetId);

        $providerOrder = !empty($preset['provider_order']) && is_array($preset['provider_order'])
            ? array_values(array_filter(array_map('sanitize_key', $preset['provider_order'])))
            : array_values(aig_engines_get_enabled_provider_ids(true));

        $tiers = !empty($preset['tiers']) && is_array($preset['tiers'])
            ? array_values(array_filter(array_map('sanitize_key', $preset['tiers'])))
            : ['free', 'cheap', 'balanced', 'premium', 'custom'];

        $routes = [];

        foreach ($providerOrder as $providerId) {
            $provider = aig_engines_get_provider($providerId);

            if (empty($provider) || empty($provider['enabled']) || empty($provider['healthy'])) {
                continue;
            }

            $models = aig_engines_get_provider_models($providerId, [
                'runtime_enabled'  => true,
                'provider_healthy' => true,
                'tiers'            => $tiers,
                'quality'          => $requiredQuality ?: null,
                'min_context'      => $minContext ?: null,
                'json_support'     => $needJson,
                'vision_support'   => $needVision,
            ]);

            foreach ($models as $modelId => $model) {
                $routes[] = [
                    'provider_id' => $providerId,
                    'model_id'    => $modelId,
                    'provider'    => $provider,
                    'model'       => $model,
                ];
            }
        }

        return $routes;
    }
}

if (!function_exists('aig_engines_legacy_view')) {
    /**
     * Returns old "engines" style data for legacy panel/ajax consumers.
     */
    function aig_engines_legacy_view(): array
    {
        $providers = aig_engines_get_providers();
        $defaults  = [
            'default' => aig_engines_get_default_provider_id(),
            'items'   => [],
        ];

        foreach ($providers as $providerId => $provider) {
            $models = aig_engines_get_provider_models($providerId, [
                'runtime_enabled' => false, // include any known mapping
            ]);

            $modelSlug = '';
            foreach ($models as $model) {
                if (!empty($model['enabled']) && !empty($model['model'])) {
                    $modelSlug = (string) $model['model'];
                    break;
                }
            }

            $defaults['items'][$providerId] = [
                'enabled'     => !empty($provider['enabled']),
                'type'        => (string) ($provider['transport'] ?? 'openai_compat'),
                'label'       => (string) ($provider['name'] ?? ucfirst($providerId)),
                'endpoint'    => (string) ($provider['base_url'] ?? ''),
                'api_key'     => (string) ($provider['api_key'] ?? ''),
                'model'       => $modelSlug,
                'temperature' => (float) aig_settings_get('llm.defaults.temperature', 0.7),
                'max_tokens'  => (int) aig_settings_get('llm.defaults.max_tokens', 1600),
                'timeout'     => (int) ($provider['timeout'] ?? 60),
            ];
        }

        return $defaults;
    }
}

if (!function_exists('aig_engines_update_provider_health')) {
    function aig_engines_update_provider_health(string $providerId, array $healthPatch): bool
    {
        $providerId = sanitize_key($providerId);
        $provider   = aig_settings_get_provider($providerId);

        if (empty($provider)) {
            return false;
        }

        $health = isset($provider['health']) && is_array($provider['health']) ? $provider['health'] : [];

        $provider['health'] = aig_settings_deep_merge($health, [
            'status'               => sanitize_key((string) ($healthPatch['status'] ?? ($health['status'] ?? 'unknown'))),
            'last_test_at'         => sanitize_text_field((string) ($healthPatch['last_test_at'] ?? ($health['last_test_at'] ?? ''))),
            'last_success_at'      => sanitize_text_field((string) ($healthPatch['last_success_at'] ?? ($health['last_success_at'] ?? ''))),
            'last_error_class'     => sanitize_key((string) ($healthPatch['last_error_class'] ?? ($health['last_error_class'] ?? ''))),
            'last_error_message'   => sanitize_text_field((string) ($healthPatch['last_error_message'] ?? ($health['last_error_message'] ?? ''))),
            'last_latency_ms'      => max(0, (int) ($healthPatch['last_latency_ms'] ?? ($health['last_latency_ms'] ?? 0))),
            'consecutive_failures' => max(0, (int) ($healthPatch['consecutive_failures'] ?? ($health['consecutive_failures'] ?? 0))),
            'disabled_until'       => sanitize_text_field((string) ($healthPatch['disabled_until'] ?? ($health['disabled_until'] ?? ''))),
        ]);

        return aig_settings_upsert_provider($providerId, $provider);
    }
}

if (!function_exists('aig_engines_mark_provider_success')) {
    function aig_engines_mark_provider_success(string $providerId, int $latencyMs = 0): bool
    {
        return aig_engines_update_provider_health($providerId, [
            'status'               => 'healthy',
            'last_test_at'         => aig_engine_now_mysql(),
            'last_success_at'      => aig_engine_now_mysql(),
            'last_error_class'     => '',
            'last_error_message'   => '',
            'last_latency_ms'      => max(0, $latencyMs),
            'consecutive_failures' => 0,
            'disabled_until'       => '',
        ]);
    }
}

if (!function_exists('aig_engines_mark_provider_failure')) {
    function aig_engines_mark_provider_failure(string $providerId, string $errorClass = '', string $errorMessage = '', int $latencyMs = 0): bool
    {
        $provider = aig_settings_get_provider($providerId);
        if (empty($provider)) {
            return false;
        }

        $health = isset($provider['health']) && is_array($provider['health']) ? $provider['health'] : [];
        $currentFailures = max(0, (int) ($health['consecutive_failures'] ?? 0));
        $currentFailures++;

        $threshold = (int) aig_settings_get('llm.health.consecutive_failures_threshold', 3);
        $cooldown  = (int) aig_settings_get('llm.health.cooldown_minutes', 10);

        $status = 'degraded';
        $disabledUntil = '';

        if ($currentFailures >= max(1, $threshold)) {
            $status = 'unhealthy';
            $disabledUntil = gmdate('Y-m-d H:i:s', time() + max(1, $cooldown) * 60);
        }

        return aig_engines_update_provider_health($providerId, [
            'status'               => $status,
            'last_test_at'         => aig_engine_now_mysql(),
            'last_error_class'     => sanitize_key($errorClass),
            'last_error_message'   => sanitize_text_field($errorMessage),
            'last_latency_ms'      => max(0, $latencyMs),
            'consecutive_failures' => $currentFailures,
            'disabled_until'       => $disabledUntil,
        ]);
    }
}

if (!function_exists('aig_engines_pick_primary_route')) {
    /**
     * Lightweight route picker.
     * Router may later apply scoring on top of this.
     */
    function aig_engines_pick_primary_route(array $request = []): array
    {
        $routes = aig_engines_build_candidate_routes($request);

        return !empty($routes[0]) && is_array($routes[0]) ? $routes[0] : [];
    }
}

if (!function_exists('aig_engines_debug_snapshot')) {
    function aig_engines_debug_snapshot(): array
    {
        $registry = aig_engines_registry();

        return [
            'providers' => array_map(function ($provider) {
                return [
                    'id'         => $provider['id'] ?? '',
                    'enabled'    => !empty($provider['enabled']),
                    'healthy'    => !empty($provider['healthy']),
                    'priority'   => (int) ($provider['priority'] ?? 999),
                    'transport'  => $provider['transport'] ?? '',
                    'base_url'   => $provider['base_url'] ?? '',
                    'tags'       => $provider['tags'] ?? [],
                    'health'     => $provider['health'] ?? [],
                ];
            }, $registry['providers'] ?? []),

            'models' => array_map(function ($model) {
                return [
                    'id'               => $model['id'] ?? '',
                    'provider'         => $model['provider'] ?? '',
                    'model'            => $model['model'] ?? '',
                    'enabled'          => !empty($model['enabled']),
                    'runtime_enabled'  => !empty($model['runtime_enabled']),
                    'tier'             => $model['tier'] ?? '',
                    'quality'          => $model['quality'] ?? '',
                    'speed'            => $model['speed'] ?? '',
                    'context_window'   => (int) ($model['context_window'] ?? 0),
                    'json_support'     => !empty($model['json_support']),
                    'vision_support'   => !empty($model['vision_support']),
                ];
            }, $registry['models'] ?? []),

            'defaults' => $registry['defaults'] ?? [],
            'routing'  => $registry['routing'] ?? [],
            'presets'  => array_keys($registry['presets'] ?? []),
            'meta'     => $registry['meta'] ?? [],
        ];
    }
}