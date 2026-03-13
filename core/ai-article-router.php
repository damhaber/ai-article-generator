<?php
/**
 * AI Article Generator — Router
 *
 * Purpose:
 * - select provider/model candidates
 * - score candidates by preset
 * - execute primary + fallback chain
 * - classify retry/fallback behavior
 * - call gateway as single transport entry
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_settings_deep_merge')) {
    function aig_settings_deep_merge(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = aig_settings_deep_merge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }
}

if (!function_exists('aig_router_quality_rank')) {
    function aig_router_quality_rank(string $quality): int
    {
        $map = [
            'low'    => 1,
            'medium' => 2,
            'high'   => 3,
            'ultra'  => 4,
        ];

        return $map[sanitize_key($quality)] ?? 2;
    }
}

if (!function_exists('aig_router_speed_rank')) {
    function aig_router_speed_rank(string $speed): int
    {
        $map = [
            'slow'   => 1,
            'medium' => 2,
            'fast'   => 3,
        ];

        return $map[sanitize_key($speed)] ?? 2;
    }
}

if (!function_exists('aig_router_tier_rank')) {
    function aig_router_tier_rank(string $tier): int
    {
        $map = [
            'free'     => 1,
            'cheap'    => 2,
            'balanced' => 3,
            'premium'  => 4,
            'custom'   => 5,
        ];

        return $map[sanitize_key($tier)] ?? 5;
    }
}

if (!function_exists('aig_router_error_classify')) {
    /**
     * Converts any result into a stable error class.
     */
    function aig_router_error_classify(array $result): string
    {
        $error = sanitize_key((string) ($result['error'] ?? ''));

        if ($error !== '') {
            return $error;
        }

        $detail = $result['detail'] ?? null;

        if (is_array($detail) && isset($detail['status'])) {
            $status = (int) $detail['status'];

            if ($status === 401 || $status === 403) {
                return 'auth_error';
            }
            if ($status === 404) {
                return 'model_not_found';
            }
            if ($status === 408) {
                return 'timeout';
            }
            if ($status === 429) {
                return 'rate_limited';
            }
            if ($status >= 500) {
                return 'provider_down';
            }
            if ($status >= 400) {
                return 'bad_request';
            }
        }

        if (!empty($result['ok'])) {
            return '';
        }

        return 'unknown_error';
    }
}

if (!function_exists('aig_router_is_retryable_error')) {
    function aig_router_is_retryable_error(string $errorClass): bool
    {
        $retryable = [
            'timeout',
            'network_error',
            'provider_down',
            'empty_output',
            'invalid_json',
            'rate_limited',
        ];

        return in_array(sanitize_key($errorClass), $retryable, true);
    }
}

if (!function_exists('aig_router_should_failover')) {
    function aig_router_should_failover(string $errorClass): bool
    {
        $map = aig_settings_get('llm.routing.failover_on', []);

        if (!is_array($map)) {
            $map = [];
        }

        return !empty($map[sanitize_key($errorClass)]);
    }
}

if (!function_exists('aig_router_get_retry_config')) {
    function aig_router_get_retry_config(): array
    {
        return [
            'max_retries_per_route' => max(0, (int) aig_settings_get('llm.routing.max_retries_per_route', 2)),
            'retry_delay_ms'        => max(0, (int) aig_settings_get('llm.routing.retry_delay_ms', 1200)),
        ];
    }
}

if (!function_exists('aig_router_get_request_defaults')) {
    function aig_router_get_request_defaults(): array
    {
        return [
            'task_type'        => (string) aig_settings_get('llm.defaults.task_type', 'article_generation'),
            'preset'           => (string) aig_settings_get('llm.defaults.preset', 'free_first'),
            'required_quality' => 'medium',
            'max_budget'       => null,
            'max_latency'      => null,
            'messages'         => [],
            'temperature'      => (float) aig_settings_get('llm.defaults.temperature', 0.7),
            'max_tokens'       => (int) aig_settings_get('llm.defaults.max_tokens', 1600),
            'min_context'      => 0,
            'json_support'     => null,
            'vision_support'   => null,
            'debug'            => false,
            'preferred_provider' => '',
            'preferred_model'    => '',
            'preferred_model_name' => '',
            'force_provider'      => false,
            'request_id'       => function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('aig_route_', true),
        ];
    }
}

if (!function_exists('aig_router_normalize_request')) {
    function aig_router_normalize_request(array $request): array
    {
        $defaults = aig_router_get_request_defaults();
        $request  = aig_settings_deep_merge($defaults, $request);

        $request['task_type']        = sanitize_key((string) $request['task_type']);
        $request['preset']           = sanitize_key((string) $request['preset']);
        $request['required_quality'] = sanitize_key((string) $request['required_quality']);
        $request['temperature']      = (float) $request['temperature'];
        $request['max_tokens']       = max(0, (int) $request['max_tokens']);
        $request['min_context']      = max(0, (int) $request['min_context']);
        $request['request_id']       = sanitize_text_field((string) $request['request_id']);
        $request['debug']            = !empty($request['debug']);
        $request['preferred_provider'] = sanitize_key((string) ($request['preferred_provider'] ?? ''));
        $request['preferred_model'] = sanitize_key((string) ($request['preferred_model'] ?? ''));
        $request['preferred_model_name'] = sanitize_text_field((string) ($request['preferred_model_name'] ?? ''));
        $request['force_provider'] = !empty($request['force_provider']);

        if (!is_array($request['messages'])) {
            $request['messages'] = [];
        }

        if ($request['max_budget'] !== null) {
            $request['max_budget'] = (float) $request['max_budget'];
        }

        if ($request['max_latency'] !== null) {
            $request['max_latency'] = (int) $request['max_latency'];
        }

        if ($request['json_support'] !== null) {
            $request['json_support'] = (bool) $request['json_support'];
        }

        if ($request['vision_support'] !== null) {
            $request['vision_support'] = (bool) $request['vision_support'];
        }

        return $request;
    }
}

if (!function_exists('aig_router_estimate_cost')) {
    /**
     * Lightweight cost estimate.
     * Later this can be improved with token estimator logic.
     */
    function aig_router_estimate_cost(array $route, array $request): float
    {
        $model = $route['model'] ?? [];
        $maxTokens = max(1, (int) ($request['max_tokens'] ?? 1600));

        $inputPrice  = (float) ($model['price_input'] ?? 0.0);
        $outputPrice = (float) ($model['price_output'] ?? 0.0);

        if ($inputPrice <= 0 && $outputPrice <= 0) {
            return 0.0;
        }

        /**
         * Cheap approximation:
         * - assume ~30% input, ~70% output token share from max_tokens budget
         */
        $inputTokens  = (int) floor($maxTokens * 0.3);
        $outputTokens = (int) floor($maxTokens * 0.7);

        return (($inputTokens / 1000) * $inputPrice) + (($outputTokens / 1000) * $outputPrice);
    }
}

if (!function_exists('aig_router_cost_score')) {
    function aig_router_cost_score(array $route, array $request): float
    {
        $cost = aig_router_estimate_cost($route, $request);

        if ($cost <= 0) {
            return 100.0;
        }

        $maxBudget = $request['max_budget'];
        if ($maxBudget !== null && $maxBudget > 0 && $cost > $maxBudget) {
            return -9999.0;
        }

        return max(1.0, 100.0 - ($cost * 100.0));
    }
}

if (!function_exists('aig_router_quality_score')) {
    function aig_router_quality_score(array $route, array $request): float
    {
        $model = $route['model'] ?? [];

        $actual   = aig_router_quality_rank((string) ($model['quality'] ?? 'medium'));
        $required = aig_router_quality_rank((string) ($request['required_quality'] ?? 'medium'));

        if ($actual < $required) {
            return 5.0 * $actual;
        }

        return 40.0 + ($actual * 15.0);
    }
}

if (!function_exists('aig_router_speed_score')) {
    function aig_router_speed_score(array $route, array $request): float
    {
        $provider = $route['provider'] ?? [];
        $model    = $route['model'] ?? [];

        $base = aig_router_speed_rank((string) ($model['speed'] ?? 'medium')) * 20.0;

        $latency = (int) (($provider['health']['last_latency_ms'] ?? 0));
        if ($latency > 0) {
            if ($request['max_latency'] !== null && $request['max_latency'] > 0 && $latency > $request['max_latency']) {
                return 1.0;
            }

            if ($latency <= 1000) {
                $base += 20.0;
            } elseif ($latency <= 3000) {
                $base += 10.0;
            } elseif ($latency > 8000) {
                $base -= 15.0;
            }
        }

        return max(1.0, $base);
    }
}

if (!function_exists('aig_router_health_score')) {
    function aig_router_health_score(array $route): float
    {
        $provider = $route['provider'] ?? [];
        $healthy  = !empty($provider['healthy']);

        if (!$healthy) {
            return 1.0;
        }

        $health = $provider['health'] ?? [];
        $failures = max(0, (int) ($health['consecutive_failures'] ?? 0));

        $score = 100.0 - ($failures * 20.0);

        $status = sanitize_key((string) ($health['status'] ?? 'unknown'));
        if ($status === 'healthy') {
            $score += 10.0;
        } elseif ($status === 'degraded') {
            $score -= 10.0;
        }

        return max(1.0, $score);
    }
}

if (!function_exists('aig_router_preset_priority_score')) {
    function aig_router_preset_priority_score(array $route, array $preset): float
    {
        $providerOrder = isset($preset['provider_order']) && is_array($preset['provider_order'])
            ? array_values(array_filter(array_map('sanitize_key', $preset['provider_order'])))
            : [];

        $providerId = sanitize_key((string) ($route['provider_id'] ?? ''));

        $index = array_search($providerId, $providerOrder, true);

        if ($index === false) {
            return 10.0;
        }

        return max(10.0, 100.0 - ($index * 15.0));
    }
}

if (!function_exists('aig_router_score_route')) {
    function aig_router_score_route(array $route, array $request, array $preset): array
    {
        $qualityWeight = (float) ($preset['quality_weight'] ?? 4);
        $costWeight    = (float) ($preset['cost_weight'] ?? 3);
        $speedWeight   = (float) ($preset['speed_weight'] ?? 3);
        $healthWeight  = (float) ($preset['health_weight'] ?? 4);

        $qualityScore  = aig_router_quality_score($route, $request);
        $costScore     = aig_router_cost_score($route, $request);
        $speedScore    = aig_router_speed_score($route, $request);
        $healthScore   = aig_router_health_score($route);
        $priorityScore = aig_router_preset_priority_score($route, $preset);

        $tierPenalty = 0.0;
        $modelTier   = sanitize_key((string) (($route['model']['tier'] ?? 'custom')));
        $allowedTiers = isset($preset['tiers']) && is_array($preset['tiers'])
            ? array_values(array_filter(array_map('sanitize_key', $preset['tiers'])))
            : [];

        if (!empty($allowedTiers) && !in_array($modelTier, $allowedTiers, true)) {
            $tierPenalty = 40.0;
        }

        $total =
            ($qualityWeight * $qualityScore) +
            ($costWeight    * $costScore) +
            ($speedWeight   * $speedScore) +
            ($healthWeight  * $healthScore) +
            $priorityScore -
            $tierPenalty;

        $route['score'] = [
            'quality'  => $qualityScore,
            'cost'     => $costScore,
            'speed'    => $speedScore,
            'health'   => $healthScore,
            'priority' => $priorityScore,
            'penalty'  => $tierPenalty,
            'total'    => $total,
        ];

        return $route;
    }
}

if (!function_exists('aig_router_build_routes')) {
    /**
     * Builds and scores all candidate routes.
     */
    function aig_router_build_routes(array $request): array
    {
        $request = aig_router_normalize_request($request);
        $preset  = aig_engines_get_preset($request['preset']);

        $candidateRoutes = aig_engines_build_candidate_routes([
            'preset'         => $request['preset'],
            'required_quality' => $request['required_quality'],
            'min_context'    => $request['min_context'],
            'json_support'   => $request['json_support'],
            'vision_support' => $request['vision_support'],
        ]);

        $preferredProvider = sanitize_key((string) ($request['preferred_provider'] ?? ''));
        $preferredModel    = sanitize_key((string) ($request['preferred_model'] ?? ''));
        $preferredModelName = sanitize_text_field((string) ($request['preferred_model_name'] ?? ''));
        $forceProvider     = !empty($request['force_provider']);

        if ($preferredProvider !== '') {
            $filtered = [];
            foreach ($candidateRoutes as $route) {
                $routeProviderId = sanitize_key((string) ($route['provider_id'] ?? ($route['provider']['id'] ?? '')));
                $routeModelId    = sanitize_key((string) ($route['model_id'] ?? ($route['model']['id'] ?? '')));
                $routeModelName  = sanitize_text_field((string) ($route['model']['model'] ?? ''));

                if ($routeProviderId !== $preferredProvider) {
                    continue;
                }
                if ($preferredModel !== '' && $routeModelId !== $preferredModel) {
                    continue;
                }
                if ($preferredModel === '' && $preferredModelName !== '' && $routeModelName !== '' && $routeModelName !== $preferredModelName) {
                    continue;
                }
                $filtered[] = $route;
            }
            if (!empty($filtered)) {
                $candidateRoutes = $filtered;
            } elseif ($forceProvider) {
                $candidateRoutes = [];
            }
        }

        $scored = [];

        foreach ($candidateRoutes as $route) {
            if (empty($route['provider']) || empty($route['model'])) {
                continue;
            }

            $scored[] = aig_router_score_route($route, $request, $preset);
        }

        usort($scored, function ($a, $b) {
            $ta = (float) (($a['score']['total'] ?? 0));
            $tb = (float) (($b['score']['total'] ?? 0));

            if ($ta === $tb) {
                return strcmp(
                    (string) ($a['model_id'] ?? ''),
                    (string) ($b['model_id'] ?? '')
                );
            }

            return ($ta > $tb) ? -1 : 1;
        });

        return $scored;
    }
}

if (!function_exists('aig_router_sleep_retry')) {
    function aig_router_sleep_retry(int $attemptIndex, int $baseDelayMs): void
    {
        $attemptIndex = max(1, $attemptIndex);
        $delayMs = $baseDelayMs * $attemptIndex;

        if ($delayMs <= 0) {
            return;
        }

        usleep($delayMs * 1000);
    }
}

if (!function_exists('aig_router_execute_route')) {
    /**
     * Sends request through gateway for a single route.
     */
    function aig_router_execute_route(array $route, array $request): array
    {
        $provider = $route['provider'] ?? [];
        $model    = $route['model'] ?? [];

        if (empty($provider) || empty($model)) {
            return [
                'ok'    => false,
                'error' => 'invalid_route',
                'detail'=> 'Missing provider or model',
            ];
        }

        $payload = [
            'provider_id'  => (string) ($route['provider_id'] ?? ''),
            'model_id'     => (string) ($route['model_id'] ?? ''),
            'provider'     => $provider,
            'model'        => $model,
            'base_url'     => (string) ($provider['base_url'] ?? ''),
            'api_key'      => (string) ($provider['api_key'] ?? ''),
            'headers'      => is_array($provider['headers'] ?? null) ? $provider['headers'] : [],
            'timeout'      => (int) ($provider['timeout'] ?? aig_settings_get('llm.defaults.timeout', 60)),
            'messages'     => is_array($request['messages'] ?? null) ? $request['messages'] : [],
            'temperature'  => (float) ($request['temperature'] ?? 0.7),
            'max_tokens'   => (int) ($request['max_tokens'] ?? 0),
            'task_type'    => (string) ($request['task_type'] ?? 'article_generation'),
            'request_id'   => (string) ($request['request_id'] ?? ''),
            'preset'       => (string) ($request['preset'] ?? 'free_first'),
            'debug'        => !empty($request['debug']),
        ];

        if (function_exists('aig_ai_gateway_generate')) {
            return aig_ai_gateway_generate($payload);
        }

        if (function_exists('aig_gateway_generate')) {
            return aig_gateway_generate($payload);
        }

        if (class_exists('AI_Article_Gateway') && method_exists('AI_Article_Gateway', 'generate')) {
            return AI_Article_Gateway::generate($payload);
        }

        if (class_exists('AI_Article_Gateway') && method_exists('AI_Article_Gateway', 'instance')) {
            $instance = AI_Article_Gateway::instance();
            if (is_object($instance) && method_exists($instance, 'generate')) {
                return $instance->generate($payload);
            }
        }

        return [
            'ok'    => false,
            'error' => 'gateway_unavailable',
            'detail'=> 'No gateway entry point found',
        ];
    }
}

if (!function_exists('aig_router_generate')) {
    /**
     * Main router entry point.
     */
    function aig_router_generate(array $request): array
    {
        $request = aig_router_normalize_request($request);
        $routes  = aig_router_build_routes($request);

        if (empty($routes)) {
            return [
                'ok'      => false,
                'error'   => 'no_candidate_routes',
                'detail'  => 'No provider/model candidate available',
                'content' => '',
                'text'    => '',
                'request_id' => $request['request_id'],
            ];
        }

        $retryCfg   = aig_router_get_retry_config();
        $maxRetries = (int) ($retryCfg['max_retries_per_route'] ?? 2);
        $delayMs    = (int) ($retryCfg['retry_delay_ms'] ?? 1200);

        $attemptLog = [];
        $routeIndex = 0;

        foreach ($routes as $route) {
            $routeIndex++;
            $providerId = (string) ($route['provider_id'] ?? '');
            $modelId    = (string) ($route['model_id'] ?? '');

            $attempt = 0;
            $finalResultForRoute = null;

            do {
                $attempt++;

                if (function_exists('ai_article_log')) {
                    ai_article_log('router_attempt_start', [
                        'request_id'  => $request['request_id'],
                        'route_index' => $routeIndex,
                        'attempt'     => $attempt,
                        'provider_id' => $providerId,
                        'model_id'    => $modelId,
                        'score'       => $route['score'] ?? [],
                    ], 'info');
                }

                $result = aig_router_execute_route($route, $request);
                $errorClass = aig_router_error_classify(is_array($result) ? $result : []);

                $attemptLog[] = [
                    'route_index' => $routeIndex,
                    'attempt'     => $attempt,
                    'provider_id' => $providerId,
                    'model_id'    => $modelId,
                    'ok'          => !empty($result['ok']),
                    'error_class' => $errorClass,
                    'score'       => $route['score'] ?? [],
                ];

                if (!empty($result['ok'])) {
                    if (function_exists('aig_engines_mark_provider_success')) {
                        aig_engines_mark_provider_success($providerId, (int) ($result['latency_ms'] ?? 0));
                    }

                    $result['provider']   = $result['provider'] ?? $providerId;
                    $result['model']      = $result['model'] ?? (($route['model']['model'] ?? '') ?: $modelId);
                    $result['request_id'] = $request['request_id'];
                    $result['route']      = $route;
                    $result['attempts']   = $attemptLog;

                    if (function_exists('ai_article_log')) {
                        ai_article_log('router_success', [
                            'request_id'  => $request['request_id'],
                            'provider_id' => $providerId,
                            'model_id'    => $modelId,
                            'route_index' => $routeIndex,
                            'attempt'     => $attempt,
                        ], 'info');
                    }

                    return $result;
                }

                if (function_exists('aig_engines_mark_provider_failure')) {
                    aig_engines_mark_provider_failure(
                        $providerId,
                        $errorClass,
                        is_scalar($result['detail'] ?? null) ? (string) $result['detail'] : '',
                        (int) ($result['latency_ms'] ?? 0)
                    );
                }

                $finalResultForRoute = $result;

                $retryable = aig_router_is_retryable_error($errorClass);
                $canRetry  = $retryable && ($attempt <= $maxRetries);

                if ($canRetry) {
                    aig_router_sleep_retry($attempt, $delayMs);
                    continue;
                }

                $shouldFailover = aig_router_should_failover($errorClass);

                if (!$shouldFailover) {
                    $result['request_id'] = $request['request_id'];
                    $result['route']      = $route;
                    $result['attempts']   = $attemptLog;

                    if (function_exists('ai_article_log')) {
                        ai_article_log('router_hard_stop', [
                            'request_id'  => $request['request_id'],
                            'provider_id' => $providerId,
                            'model_id'    => $modelId,
                            'error_class' => $errorClass,
                        ], 'warn');
                    }

                    return $result;
                }

                break;

            } while ($attempt <= $maxRetries);

            if (function_exists('ai_article_log')) {
                ai_article_log('router_failover_next', [
                    'request_id'  => $request['request_id'],
                    'provider_id' => $providerId,
                    'model_id'    => $modelId,
                    'route_index' => $routeIndex,
                    'last_error'  => aig_router_error_classify(is_array($finalResultForRoute) ? $finalResultForRoute : []),
                ], 'warn');
            }
        }

        $last = end($attemptLog);
        $lastError = is_array($last) ? (string) ($last['error_class'] ?? 'all_routes_failed') : 'all_routes_failed';

        return [
            'ok'         => false,
            'error'      => $lastError ?: 'all_routes_failed',
            'detail'     => 'All candidate routes failed',
            'content'    => '',
            'text'       => '',
            'request_id' => $request['request_id'],
            'attempts'   => $attemptLog,
        ];
    }
}

if (!function_exists('aig_ai_router_generate')) {
    /**
     * Preferred alias used by bridge layer.
     */
    function aig_ai_router_generate(array $request): array
    {
        return aig_router_generate($request);
    }
}

if (!function_exists('aig_router_debug_routes')) {
    function aig_router_debug_routes(array $request = []): array
    {
        $request = aig_router_normalize_request($request);
        return [
            'request' => $request,
            'routes'  => aig_router_build_routes($request),
        ];
    }
}


/**
 * Compatibility selector for V6 pipeline.
 * Yeni pipeline route select ister; eski ZIP generate/router stack kullanır.
 */
if (!function_exists('aig_router_select')) {
    function aig_router_select(array $request = []): array
    {
        $providerState = function_exists('aig_llm_get_provider') ? aig_llm_get_provider() : [];

        $providerId = sanitize_key((string) ($request['preferred_provider'] ?? ($providerState['provider'] ?? '')));
        $modelId    = sanitize_key((string) ($request['preferred_model'] ?? ($providerState['model_id'] ?? '')));
        $modelName  = (string) ($providerState['model'] ?? '');

        if ($providerId === '' && function_exists('aig_settings_get')) {
            $providerId = sanitize_key((string) aig_settings_get('llm.routing.primary_provider', ''));
        }
        if ($modelId === '' && function_exists('aig_settings_get')) {
            $modelId = sanitize_key((string) aig_settings_get('llm.routing.primary_model', ''));
        }

        $provider = [];
        if ($providerId !== '' && function_exists('aig_settings_get')) {
            $providers = aig_settings_get('llm.providers', []);
            if (is_array($providers) && !empty($providers[$providerId]) && is_array($providers[$providerId])) {
                $provider = $providers[$providerId];
            }
        }

        $model = [];
        if (function_exists('aig_settings_get')) {
            $modelsRaw  = aig_settings_get('llm.models', []);
            $flatModels = function_exists('aig_llm_state_models_flatten') ? aig_llm_state_models_flatten($modelsRaw) : (is_array($modelsRaw) ? $modelsRaw : []);

            if ($modelId !== '' && !empty($flatModels[$modelId]) && is_array($flatModels[$modelId])) {
                $model = $flatModels[$modelId];
            } elseif ($providerId !== '') {
                foreach ($flatModels as $mid => $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    if (sanitize_key((string) ($row['provider'] ?? '')) === $providerId && !empty($row['enabled'])) {
                        $modelId = sanitize_key((string) $mid);
                        $model   = $row;
                        break;
                    }
                }
                if (empty($model)) {
                    foreach ($flatModels as $mid => $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        if (sanitize_key((string) ($row['provider'] ?? '')) === $providerId) {
                            $modelId = sanitize_key((string) $mid);
                            $model   = $row;
                            break;
                        }
                    }
                }
            }
        }

        if ($providerId === '') {
            $providerId = 'openai';
        }
        if ($modelId === '') {
            $modelId = 'gpt-4o-mini';
        }

        $resolvedModelName = (string) ($model['model'] ?? ($modelName !== '' ? $modelName : $modelId));
        $resolvedTimeout   = (int) ($providerState['timeout'] ?? ($provider['timeout'] ?? 60));
        $resolvedTemp      = (float) ($providerState['temperature'] ?? 0.7);
        $resolvedTokens    = (int) ($providerState['max_tokens'] ?? 1600);

        return [
            'ok'       => true,
            'provider' => $providerId,
            'model'    => $resolvedModelName,
            'model_id' => $modelId,
            'options'  => [
                'timeout'     => max(5, $resolvedTimeout),
                'temperature' => $resolvedTemp,
                'max_tokens'  => max(1, $resolvedTokens),
                'retry'       => 1,
            ],
            'meta' => [
                'selector' => 'compat_aig_router_select',
            ],
            'error' => null,
        ];
    }
}
