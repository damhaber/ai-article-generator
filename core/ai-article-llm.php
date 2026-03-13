<?php
/**
 * AI Article Generator
 * Legacy LLM Compatibility Bridge
 *
 * Purpose:
 * - keep old integrations working
 * - normalize legacy settings
 * - forward generation to new router/gateway architecture
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_llm_bridge_default_settings')) {
    function aig_llm_bridge_default_settings(): array
    {
        return [
            'enabled'  => true,
            'provider' => 'custom',
            'endpoint' => '',
            'model'    => '',
            'api_key'  => '',
            'timeout'  => 60,
        ];
    }
}

if (!function_exists('aig_llm_bridge_read_legacy_options')) {
    /**
     * Reads old WP options so older panel data is not lost during migration.
     */
    function aig_llm_bridge_read_legacy_options(): array
    {
        $defaults = aig_llm_bridge_default_settings();

        $legacy = [
            'enabled'  => get_option('ai_article_llm_enabled', $defaults['enabled']),
            'provider' => get_option('ai_article_llm_provider', $defaults['provider']),
            'endpoint' => get_option('ai_article_llm_endpoint', $defaults['endpoint']),
            'model'    => get_option('ai_article_llm_model', $defaults['model']),
            'api_key'  => get_option('ai_article_llm_api_key', $defaults['api_key']),
            'timeout'  => get_option('ai_article_llm_timeout', $defaults['timeout']),
        ];

        $legacy['enabled']  = (bool) $legacy['enabled'];
        $legacy['provider'] = sanitize_key((string) $legacy['provider']);
        $legacy['endpoint'] = esc_url_raw((string) $legacy['endpoint']);
        $legacy['model']    = sanitize_text_field((string) $legacy['model']);
        $legacy['api_key']  = is_string($legacy['api_key']) ? trim($legacy['api_key']) : '';
        $legacy['timeout']  = max(5, (int) $legacy['timeout']);

        return $legacy;
    }
}

if (!function_exists('aig_llm_bridge_get_settings')) {
    /**
     * Returns normalized LLM settings from new JSON-first config if available,
     * otherwise falls back to legacy WP options.
     */
    function aig_llm_bridge_get_settings(): array
    {
        $defaults = aig_llm_bridge_default_settings();
        $legacy   = aig_llm_bridge_read_legacy_options();

        $resolved = $legacy;

        if (function_exists('aig_settings_read')) {
            $settings = aig_settings_read();

            if (is_array($settings)) {
                $llm = $settings['llm'] ?? [];

                $providers = isset($llm['providers']) && is_array($llm['providers']) ? $llm['providers'] : [];
                $defaults2 = isset($llm['defaults']) && is_array($llm['defaults']) ? $llm['defaults'] : [];
                $routing   = isset($llm['routing']) && is_array($llm['routing']) ? $llm['routing'] : [];

                $active_provider = '';
                if (!empty($defaults2['provider'])) {
                    $active_provider = sanitize_key((string) $defaults2['provider']);
                } elseif (!empty($routing['primary_provider'])) {
                    $active_provider = sanitize_key((string) $routing['primary_provider']);
                }

                if ($active_provider && !empty($providers[$active_provider]) && is_array($providers[$active_provider])) {
                    $p = $providers[$active_provider];

                    $resolved['enabled']  = isset($p['enabled']) ? (bool) $p['enabled'] : $defaults['enabled'];
                    $resolved['provider'] = $active_provider;
                    $resolved['endpoint'] = !empty($p['base_url']) ? esc_url_raw((string) $p['base_url']) : $legacy['endpoint'];
                    $resolved['api_key']  = !empty($p['api_key']) ? (string) $p['api_key'] : $legacy['api_key'];
                    $resolved['timeout']  = !empty($p['timeout']) ? max(5, (int) $p['timeout']) : $legacy['timeout'];

                    if (!empty($defaults2['model'])) {
                        $resolved['model'] = sanitize_text_field((string) $defaults2['model']);
                    } elseif (!empty($routing['primary_model'])) {
                        $resolved['model'] = sanitize_text_field((string) $routing['primary_model']);
                    } else {
                        $resolved['model'] = $legacy['model'];
                    }
                } else {
                    if (!empty($defaults2['provider'])) {
                        $resolved['provider'] = sanitize_key((string) $defaults2['provider']);
                    }
                    if (!empty($defaults2['model'])) {
                        $resolved['model'] = sanitize_text_field((string) $defaults2['model']);
                    }
                }

                if (array_key_exists('enabled', $llm)) {
                    $resolved['enabled'] = (bool) $llm['enabled'];
                }
            }
        }

        return array_merge($defaults, $resolved);
    }
}

if (!function_exists('aig_llm_bridge_make_messages')) {
    /**
     * Normalizes incoming prompt/context into Chat Completions style messages.
     */
    function aig_llm_bridge_make_messages($prompt, array $args = []): array
    {
        $messages = [];

        $system = '';
        if (!empty($args['system_prompt']) && is_string($args['system_prompt'])) {
            $system = trim($args['system_prompt']);
        } elseif (!empty($args['system']) && is_string($args['system'])) {
            $system = trim($args['system']);
        }

        if ($system !== '') {
            $messages[] = [
                'role'    => 'system',
                'content' => $system,
            ];
        }

        if (!empty($args['messages']) && is_array($args['messages'])) {
            foreach ($args['messages'] as $msg) {
                if (!is_array($msg)) {
                    continue;
                }

                $role    = isset($msg['role']) ? sanitize_key((string) $msg['role']) : 'user';
                $content = isset($msg['content']) ? (string) $msg['content'] : '';

                if ($content === '') {
                    continue;
                }

                $messages[] = [
                    'role'    => in_array($role, ['system', 'user', 'assistant', 'tool'], true) ? $role : 'user',
                    'content' => $content,
                ];
            }
        } else {
            $content = is_string($prompt) ? trim($prompt) : '';
            if ($content !== '') {
                $messages[] = [
                    'role'    => 'user',
                    'content' => $content,
                ];
            }
        }

        return $messages;
    }
}

if (!function_exists('aig_llm_bridge_normalize_result')) {
    /**
     * Forces result into stable response shape.
     */
    function aig_llm_bridge_normalize_result($result, array $meta = []): array
    {
        if (!is_array($result)) {
            return [
                'ok'      => false,
                'error'   => 'invalid_result',
                'detail'  => 'LLM result is not an array',
                'content' => '',
                'text'    => '',
                'html'    => '',
                'meta'    => $meta,
            ];
        }

        $ok = !empty($result['ok']);

        $content = '';
        if (isset($result['content']) && is_string($result['content'])) {
            $content = trim($result['content']);
        } elseif (isset($result['text']) && is_string($result['text'])) {
            $content = trim($result['text']);
        } elseif (isset($result['html']) && is_string($result['html'])) {
            $content = trim(wp_strip_all_tags($result['html']));
        }

        $html = '';
        if (isset($result['html']) && is_string($result['html']) && trim($result['html']) !== '') {
            $html = trim($result['html']);
        } elseif ($content !== '') {
            $html = wpautop(esc_html($content));
        }

        $tokens_input  = isset($result['tokens_input']) ? (int) $result['tokens_input'] : 0;
        $tokens_output = isset($result['tokens_output']) ? (int) $result['tokens_output'] : 0;

        $normalized = [
            'ok'            => (bool) $ok,
            'error'         => isset($result['error']) ? (string) $result['error'] : '',
            'detail'        => $result['detail'] ?? '',
            'content'       => $content,
            'text'          => $content,
            'html'          => $html,
            'provider'      => isset($result['provider']) ? (string) $result['provider'] : ($meta['provider'] ?? ''),
            'model'         => isset($result['model']) ? (string) $result['model'] : ($meta['model'] ?? ''),
            'tokens_input'  => $tokens_input,
            'tokens_output' => $tokens_output,
            'latency_ms'    => isset($result['latency_ms']) ? (int) $result['latency_ms'] : 0,
            'usage'         => [
                'input_tokens'  => $tokens_input,
                'output_tokens' => $tokens_output,
                'total_tokens'  => $tokens_input + $tokens_output,
            ],
            'raw'           => $result,
            'meta'          => $meta,
        ];

        if ($normalized['ok'] && $normalized['content'] === '' && $normalized['html'] === '') {
            $normalized['ok']    = false;
            $normalized['error'] = 'empty_output';
        }

        return $normalized;
    }
}

if (!function_exists('aig_llm_bridge_call_router')) {
    /**
     * Preferred execution path: new router/gateway stack.
     */
    function aig_llm_bridge_call_router($prompt, array $args = []): array
    {
        $messages = aig_llm_bridge_make_messages($prompt, $args);

        $forcedProvider = isset($args['provider']) ? sanitize_key((string) $args['provider']) : '';
        $forcedModelId  = isset($args['model_id']) ? sanitize_key((string) $args['model_id']) : '';
        $forcedModel    = isset($args['model']) ? sanitize_text_field((string) $args['model']) : '';

        $payload = [
            'task_type'          => !empty($args['task_type']) ? sanitize_key((string) $args['task_type']) : 'article_generation',
            'preset'             => !empty($args['preset']) ? sanitize_key((string) $args['preset']) : 'free_first',
            'required_quality'   => !empty($args['required_quality']) ? sanitize_key((string) $args['required_quality']) : 'medium',
            'max_budget'         => isset($args['max_budget']) ? (float) $args['max_budget'] : null,
            'max_latency'        => isset($args['max_latency']) ? (int) $args['max_latency'] : null,
            'messages'           => $messages,
            'temperature'        => isset($args['temperature']) ? (float) $args['temperature'] : 0.7,
            'max_tokens'         => isset($args['max_tokens']) ? (int) $args['max_tokens'] : 0,
            'context'            => !empty($args['context']) && is_array($args['context']) ? $args['context'] : [],
            'debug'              => !empty($args['debug']),
            'preferred_provider' => $forcedProvider,
            'preferred_model'    => $forcedModelId,
            'preferred_model_name' => $forcedModel,
            'force_provider'     => $forcedProvider !== '',
        ];

        if (function_exists('aig_ai_router_generate')) {
            return aig_llm_bridge_normalize_result(
                aig_ai_router_generate($payload),
                [
                    'entry' => 'function:aig_ai_router_generate',
                ]
            );
        }

        if (function_exists('aig_router_generate')) {
            return aig_llm_bridge_normalize_result(
                aig_router_generate($payload),
                [
                    'entry' => 'function:aig_router_generate',
                ]
            );
        }

        if (class_exists('AI_Article_Router') && method_exists('AI_Article_Router', 'generate')) {
            return aig_llm_bridge_normalize_result(
                AI_Article_Router::generate($payload),
                [
                    'entry' => 'class:AI_Article_Router::generate',
                ]
            );
        }

        if (class_exists('AI_Article_Router') && method_exists('AI_Article_Router', 'instance')) {
            $instance = AI_Article_Router::instance();
            if (is_object($instance) && method_exists($instance, 'generate')) {
                return aig_llm_bridge_normalize_result(
                    $instance->generate($payload),
                    [
                        'entry' => 'instance:AI_Article_Router->generate',
                    ]
                );
            }
        }

        return [
            'ok'      => false,
            'error'   => 'router_unavailable',
            'detail'  => 'No router entry point found',
            'content' => '',
            'text'    => '',
            'html'    => '',
            'meta'    => [
                'payload' => $payload,
            ],
        ];
    }
}

if (!function_exists('aig_llm_bridge_fallback_direct')) {
    /**
     * Emergency fallback path if router is not yet wired.
     * Uses gateway if possible, otherwise legacy HTTP logic.
     */
    function aig_llm_bridge_fallback_direct($prompt, array $args = []): array
    {
        $settings = aig_llm_bridge_get_settings();

        if (empty($settings['enabled'])) {
            return [
                'ok'      => false,
                'error'   => 'llm_disabled',
                'detail'  => 'LLM is disabled',
                'content' => '',
                'text'    => '',
                'html'    => '',
            ];
        }

        $messages = aig_llm_bridge_make_messages($prompt, $args);

        $modelId = isset($args['model_id']) ? sanitize_key((string) $args['model_id']) : '';
        if ($modelId === '' && function_exists('aig_settings_get')) {
            $modelId = sanitize_key((string) aig_settings_get('llm.defaults.model', ''));
        }

        $providerId = isset($args['provider']) ? sanitize_key((string) $args['provider']) : sanitize_key((string) ($settings['provider'] ?? ''));
        $providerPayload = function_exists('aig_settings_get_provider') ? aig_settings_get_provider($providerId) : [];

        if (empty($providerPayload) && $providerId !== '') {
            $providerPayload = [
                'id'       => $providerId,
                'base_url' => $settings['endpoint'],
                'api_key'  => $settings['api_key'],
                'timeout'  => $settings['timeout'],
            ];
        }

        $modelPayload = function_exists('aig_settings_get_model') ? aig_settings_get_model($modelId) : [];
        if (empty($modelPayload) && !empty($settings['model'])) {
            $modelPayload = [
                'id'    => $modelId !== '' ? $modelId : sanitize_key((string) $settings['model']),
                'model' => (string) $settings['model'],
            ];
        }

        $payload = [
            'provider_id' => $providerId,
            'provider'    => $providerPayload,
            'base_url'    => $settings['endpoint'],
            'api_key'     => $settings['api_key'],
            'model_id'    => $modelId,
            'model'       => $modelPayload,
            'timeout'     => $settings['timeout'],
            'messages'    => $messages,
            'temperature' => isset($args['temperature']) ? (float) $args['temperature'] : 0.7,
            'max_tokens'  => isset($args['max_tokens']) ? (int) $args['max_tokens'] : 0,
        ];

        if (function_exists('aig_ai_gateway_generate')) {
            return aig_llm_bridge_normalize_result(
                aig_ai_gateway_generate($payload),
                [
                    'entry'    => 'function:aig_ai_gateway_generate',
                    'provider' => $settings['provider'],
                    'model'    => $settings['model'],
                ]
            );
        }

        if (function_exists('aig_gateway_generate')) {
            return aig_llm_bridge_normalize_result(
                aig_gateway_generate($payload),
                [
                    'entry'    => 'function:aig_gateway_generate',
                    'provider' => $settings['provider'],
                    'model'    => $settings['model'],
                ]
            );
        }

        if (class_exists('AI_Article_Gateway') && method_exists('AI_Article_Gateway', 'generate')) {
            return aig_llm_bridge_normalize_result(
                AI_Article_Gateway::generate($payload),
                [
                    'entry'    => 'class:AI_Article_Gateway::generate',
                    'provider' => $settings['provider'],
                    'model'    => $settings['model'],
                ]
            );
        }

        if (class_exists('AI_Article_Gateway') && method_exists('AI_Article_Gateway', 'instance')) {
            $instance = AI_Article_Gateway::instance();
            if (is_object($instance) && method_exists($instance, 'generate')) {
                return aig_llm_bridge_normalize_result(
                    $instance->generate($payload),
                    [
                        'entry'    => 'instance:AI_Article_Gateway->generate',
                        'provider' => $settings['provider'],
                        'model'    => $settings['model'],
                    ]
                );
            }
        }

        if (empty($settings['endpoint']) || empty($settings['model'])) {
            return [
                'ok'      => false,
                'error'   => 'missing_configuration',
                'detail'  => 'Missing endpoint or model in legacy fallback mode',
                'content' => '',
                'text'    => '',
                'html'    => '',
            ];
        }

        $url = rtrim($settings['endpoint'], '/') . '/chat/completions';

        $body = [
            'model'       => $settings['model'],
            'messages'    => $messages,
            'temperature' => $payload['temperature'],
        ];

        if (!empty($payload['max_tokens'])) {
            $body['max_tokens'] = $payload['max_tokens'];
        }

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (!empty($settings['api_key'])) {
            $headers['Authorization'] = 'Bearer ' . $settings['api_key'];
        }

        $started = microtime(true);

        $response = wp_remote_post($url, [
            'timeout' => (int) $settings['timeout'],
            'headers' => $headers,
            'body'    => wp_json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $latency_ms = (int) round((microtime(true) - $started) * 1000);

        if (is_wp_error($response)) {
            return [
                'ok'         => false,
                'error'      => 'network_error',
                'detail'     => $response->get_error_message(),
                'content'    => '',
                'text'       => '',
                'html'       => '',
                'provider'   => $settings['provider'],
                'model'      => $settings['model'],
                'latency_ms' => $latency_ms,
            ];
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $raw    = (string) wp_remote_retrieve_body($response);
        $json   = json_decode($raw, true);

        if ($status < 200 || $status >= 300) {
            $error = 'http_error';
            if ($status === 401 || $status === 403) {
                $error = 'auth_error';
            } elseif ($status === 404) {
                $error = 'model_not_found';
            } elseif ($status === 408) {
                $error = 'timeout';
            } elseif ($status === 429) {
                $error = 'rate_limited';
            }

            return [
                'ok'         => false,
                'error'      => $error,
                'detail'     => [
                    'status' => $status,
                    'body'   => $raw,
                ],
                'content'    => '',
                'text'       => '',
                'html'       => '',
                'provider'   => $settings['provider'],
                'model'      => $settings['model'],
                'latency_ms' => $latency_ms,
            ];
        }

        $content = '';
        if (is_array($json)) {
            $content = (string) ($json['choices'][0]['message']['content'] ?? '');
        }

        $html = $content !== '' ? wpautop(esc_html($content)) : '';

        return [
            'ok'            => ($content !== ''),
            'error'         => ($content !== '') ? '' : 'empty_output',
            'detail'        => ($content !== '') ? '' : ['body' => $json],
            'content'       => $content,
            'text'          => $content,
            'html'          => $html,
            'provider'      => $settings['provider'],
            'model'         => $settings['model'],
            'latency_ms'    => $latency_ms,
            'tokens_input'  => (int) ($json['usage']['prompt_tokens'] ?? 0),
            'tokens_output' => (int) ($json['usage']['completion_tokens'] ?? 0),
            'usage'         => [
                'input_tokens'  => (int) ($json['usage']['prompt_tokens'] ?? 0),
                'output_tokens' => (int) ($json['usage']['completion_tokens'] ?? 0),
                'total_tokens'  => (int) (($json['usage']['prompt_tokens'] ?? 0) + ($json['usage']['completion_tokens'] ?? 0)),
            ],
            'raw'           => $json,
        ];
    }
}

if (!function_exists('aig_llm_generate')) {
    /**
     * Main public entry point for code-level generation calls.
     */
    function aig_llm_generate($prompt, array $args = []): array
    {
        $request_id = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('aig_', true);

        $args['request_id'] = $request_id;

        if (empty($args['task_type'])) {
            $args['task_type'] = 'article_generation';
        }

        if (empty($args['preset'])) {
            $args['preset'] = 'free_first';
        }

        if (!isset($args['temperature'])) {
            $args['temperature'] = 0.4;
        }

        if (!isset($args['max_tokens']) || (int) $args['max_tokens'] <= 0) {
            $args['max_tokens'] = 2200;
        }

        if (function_exists('ai_article_log')) {
            ai_article_log('llm_generate_start', [
                'request_id' => $request_id,
                'task_type'  => $args['task_type'] ?? 'article_generation',
                'preset'     => $args['preset'] ?? 'free_first',
            ], 'info');
        }

        $result = aig_llm_bridge_call_router($prompt, $args);

        if (empty($result['ok'])) {
            $result = aig_llm_bridge_fallback_direct($prompt, $args);
        }

        if (function_exists('ai_article_log')) {
            ai_article_log('llm_generate_finish', [
                'request_id'    => $request_id,
                'ok'            => !empty($result['ok']),
                'error'         => $result['error'] ?? '',
                'provider'      => $result['provider'] ?? '',
                'model'         => $result['model'] ?? '',
                'tokens_input'  => $result['tokens_input'] ?? 0,
                'tokens_output' => $result['tokens_output'] ?? 0,
                'latency_ms'    => $result['latency_ms'] ?? 0,
            ], !empty($result['ok']) ? 'info' : 'warn');
        }

        return $result;
    }
}

if (!function_exists('aig_llm_test_connection')) {
    /**
     * Simple test call for panel or self-test use.
     */
    function aig_llm_test_connection(array $args = []): array
    {
        $prompt = !empty($args['prompt']) && is_string($args['prompt'])
            ? $args['prompt']
            : 'Return only the word OK.';

        $args['task_type']   = $args['task_type'] ?? 'selftest';
        $args['preset']      = $args['preset'] ?? 'free_first';
        $args['max_tokens']  = $args['max_tokens'] ?? 12;
        $args['temperature'] = $args['temperature'] ?? 0.1;

        return aig_llm_generate($prompt, $args);
    }
}

if (!function_exists('aig_llm_filter_handler')) {
    /**
     * Legacy WordPress filter adapter.
     * Keeps old filter consumers alive.
     */
    function aig_llm_filter_handler($prompt, $args = []): array
    {
        if (!is_array($args)) {
            $args = [];
        }

        if ((!is_string($prompt) || trim($prompt) === '') && !empty($args['prompt']) && is_string($args['prompt'])) {
            $prompt = $args['prompt'];
        }

        $result = aig_llm_generate((string) $prompt, $args);

        $html = '';
        if (!empty($result['html']) && is_string($result['html'])) {
            $html = (string) $result['html'];
        } elseif (!empty($result['content']) && is_string($result['content'])) {
            $html = wpautop(esc_html((string) $result['content']));
        } elseif (!empty($result['text']) && is_string($result['text'])) {
            $html = wpautop(esc_html((string) $result['text']));
        }

        return [
            'ok'    => !empty($result['ok']) && $html !== '',
            'html'  => $html,
            'text'  => (string) ($result['text'] ?? $result['content'] ?? ''),
            'model' => (string) ($result['model'] ?? $result['provider'] ?? ''),
            'usage' => (array) ($result['usage'] ?? []),
            'error' => (string) ($result['error'] ?? ''),
            'detail'=> $result['detail'] ?? null,
            'raw'   => $result['raw'] ?? [],
        ];
    }
}

if (!function_exists('aig_llm_list_providers_for_panel')) {
    function aig_llm_list_providers_for_panel(): array
    {
        $providers = function_exists('aig_settings_get') ? aig_settings_get('llm.providers', []) : [];
        $out = [];

        if (is_array($providers)) {
            foreach ($providers as $id => $provider) {
                if (!is_array($provider)) {
                    continue;
                }

                $out[$id] = [
                    'id'        => sanitize_key((string) $id),
                    'name'      => (string) ($provider['name'] ?? ucfirst((string) $id)),
                    'enabled'   => !empty($provider['enabled']),
                    'endpoint'  => (string) ($provider['base_url'] ?? ''),
                    'timeout'   => (int) ($provider['timeout'] ?? 60),
                    'priority'  => (int) ($provider['priority'] ?? 100),
                    'transport' => (string) ($provider['transport'] ?? 'openai_compat'),
                    'tags'      => isset($provider['tags']) && is_array($provider['tags']) ? array_values($provider['tags']) : [],
                ];
            }
        }

        return $out;
    }
}

if (!function_exists('aig_llm_list_models_for_panel')) {
    function aig_llm_list_models_for_panel(): array
    {
        $models = function_exists('aig_settings_get') ? aig_settings_get('llm.models', []) : [];
        $out = [];

        if (is_array($models)) {
            foreach ($models as $id => $model) {
                if (!is_array($model)) {
                    continue;
                }

                $provider = sanitize_key((string) ($model['provider'] ?? 'custom'));
                if (!isset($out[$provider])) {
                    $out[$provider] = [];
                }

                $out[$provider][] = [
                    'id'      => sanitize_key((string) ($model['id'] ?? $id)),
                    'name'    => (string) ($model['name'] ?? $id),
                    'model'   => (string) ($model['model'] ?? ''),
                    'enabled' => !empty($model['enabled']),
                    'tier'    => (string) ($model['tier'] ?? ''),
                    'quality' => (string) ($model['quality'] ?? ''),
                    'speed'   => (string) ($model['speed'] ?? ''),
                ];
            }

            foreach ($out as $provider => $items) {
                usort($out[$provider], function ($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
            }
        }

        return $out;
    }
}


if (!function_exists('aig_llm_state_read')) {
    function aig_llm_state_read(): array
    {
        if (function_exists('aig_settings_read')) {
            $settings = aig_settings_read();
            if (is_array($settings)) {
                return $settings;
            }
        }

        if (function_exists('aig_settings_get_all')) {
            $settings = aig_settings_get_all();
            if (is_array($settings)) {
                return $settings;
            }
        }

        return [];
    }
}

if (!function_exists('aig_llm_state_write')) {
    function aig_llm_state_write(array $settings): bool
    {
        if (function_exists('aig_settings_write')) {
            return (bool) aig_settings_write($settings);
        }

        if (function_exists('aig_settings_set_all')) {
            aig_settings_set_all($settings);
            return true;
        }

        return false;
    }
}

if (!function_exists('aig_llm_state_models_flatten')) {
    function aig_llm_state_models_flatten($models): array
    {
        $flat = [];

        if (!is_array($models)) {
            return $flat;
        }

        foreach ($models as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $isList = array_keys($value) === range(0, count($value) - 1);

            if ($isList) {
                foreach ($value as $row) {
                    if (!is_array($row)) {
                        continue;
                    }

                    $id = sanitize_key((string) ($row['id'] ?? ''));
                    if ($id === '') {
                        $id = sanitize_key((string) ($row['model'] ?? $key));
                    }

                    if ($id === '') {
                        continue;
                    }

                    if (empty($row['provider'])) {
                        $row['provider'] = sanitize_key((string) $key);
                    }

                    $flat[$id] = $row;
                }
            } else {
                $row = $value;
                $id = sanitize_key((string) ($row['id'] ?? $key));
                if ($id === '') {
                    continue;
                }
                $flat[$id] = $row;
            }
        }

        return $flat;
    }
}

if (!function_exists('aig_llm_state_current_defaults')) {
    function aig_llm_state_current_defaults(): array
    {
        $settings = aig_llm_state_read();
        $llm      = is_array($settings['llm'] ?? null) ? $settings['llm'] : [];

        $providers = is_array($llm['providers'] ?? null) ? $llm['providers'] : [];
        $models    = aig_llm_state_models_flatten($llm['models'] ?? []);
        $defaults  = is_array($llm['defaults'] ?? null) ? $llm['defaults'] : [];
        $routing   = is_array($llm['routing'] ?? null) ? $llm['routing'] : [];

        $providerId = sanitize_key((string) ($defaults['provider'] ?? ''));
        if ($providerId === '') {
            $providerId = sanitize_key((string) ($routing['primary_provider'] ?? ''));
        }
        if ($providerId === '' && !empty($providers)) {
            foreach ($providers as $pid => $prow) {
                if (is_array($prow) && !empty($prow['enabled'])) {
                    $providerId = sanitize_key((string) $pid);
                    break;
                }
            }
        }
        if ($providerId === '' && !empty($providers)) {
            $keys = array_keys($providers);
            $providerId = sanitize_key((string) reset($keys));
        }
        if ($providerId === '') {
            $providerId = 'local';
        }

        $modelId = sanitize_key((string) ($defaults['model'] ?? ''));
        if ($modelId === '') {
            $modelId = sanitize_key((string) ($routing['primary_model'] ?? ''));
        }
        if ($modelId === '' && !empty($models)) {
            foreach ($models as $mid => $mrow) {
                if (!is_array($mrow)) {
                    continue;
                }
                if (sanitize_key((string) ($mrow['provider'] ?? '')) === $providerId && !empty($mrow['enabled'])) {
                    $modelId = sanitize_key((string) $mid);
                    break;
                }
            }
        }
        if ($modelId === '' && !empty($models)) {
            foreach ($models as $mid => $mrow) {
                if (!is_array($mrow)) {
                    continue;
                }
                if (sanitize_key((string) ($mrow['provider'] ?? '')) === $providerId) {
                    $modelId = sanitize_key((string) $mid);
                    break;
                }
            }
        }
        if ($modelId === '' && !empty($models)) {
            $keys = array_keys($models);
            $modelId = sanitize_key((string) reset($keys));
        }

        return [
            'settings'   => $settings,
            'llm'        => $llm,
            'providers'  => $providers,
            'models'     => $models,
            'defaults'   => $defaults,
            'routing'    => $routing,
            'providerId' => $providerId,
            'modelId'    => $modelId,
        ];
    }
}

if (!function_exists('aig_llm_current_provider_id')) {
    function aig_llm_current_provider_id(): string
    {
        $state = aig_llm_state_current_defaults();
        return sanitize_key((string) ($state['providerId'] ?? 'local'));
    }
}

if (!function_exists('aig_llm_get_provider')) {
    function aig_llm_get_provider(): array
    {
        $state      = aig_llm_state_current_defaults();
        $providerId = sanitize_key((string) ($state['providerId'] ?? 'local'));
        $modelId    = sanitize_key((string) ($state['modelId'] ?? ''));
        $providers  = is_array($state['providers'] ?? null) ? $state['providers'] : [];
        $models     = is_array($state['models'] ?? null) ? $state['models'] : [];
        $defaults   = is_array($state['defaults'] ?? null) ? $state['defaults'] : [];

        $provider = isset($providers[$providerId]) && is_array($providers[$providerId]) ? $providers[$providerId] : [];
        $model    = isset($models[$modelId]) && is_array($models[$modelId]) ? $models[$modelId] : [];

        if (empty($model) && !empty($models)) {
            foreach ($models as $mid => $mrow) {
                if (!is_array($mrow)) {
                    continue;
                }
                if (sanitize_key((string) ($mrow['provider'] ?? '')) === $providerId) {
                    $modelId = sanitize_key((string) $mid);
                    $model   = $mrow;
                    break;
                }
            }
        }

        $endpoint = (string) ($provider['base_url'] ?? ($provider['endpoint'] ?? ''));
        $apiKey   = (string) ($provider['api_key'] ?? '');

        return [
            'enabled'        => !empty($provider['enabled']),
            'provider'       => $providerId,
            'provider_label' => (string) ($provider['name'] ?? ucfirst($providerId ?: 'provider')),
            'endpoint'       => $endpoint,
            'model'          => (string) ($model['model'] ?? ''),
            'model_id'       => $modelId,
            'api_key'        => $apiKey,
            'timeout'        => (int) ($defaults['timeout'] ?? ($provider['timeout'] ?? 60)),
            'temperature'    => (float) ($defaults['temperature'] ?? 0.7),
            'max_tokens'     => (int) ($defaults['max_tokens'] ?? 1600),
            'preset'         => (string) ($defaults['preset'] ?? 'free_first'),
            'priority'       => (int) ($provider['priority'] ?? 100),
        ];
    }
}

if (!function_exists('aig_llm_panel_state')) {
    function aig_llm_panel_state(): array
    {
        $presets = function_exists('aig_settings_get') ? aig_settings_get('llm.presets', []) : [];
        $presetOut = [];

        if (is_array($presets)) {
            foreach ($presets as $id => $preset) {
                $presetOut[] = [
                    'id'      => sanitize_key((string) $id),
                    'label'   => (string) (($preset['label'] ?? '') ?: $id),
                    'enabled' => !isset($preset['enabled']) || !empty($preset['enabled']),
                ];
            }
        }

        return [
            'provider'  => aig_llm_get_provider(),
            'providers' => aig_llm_list_providers_for_panel(),
            'models'    => aig_llm_list_models_for_panel(),
            'presets'   => $presetOut,
            'routing'   => function_exists('aig_settings_get') ? aig_settings_get('llm.routing', []) : [],
        ];
    }
}

if (!function_exists('aig_llm_save_provider')) {
    function aig_llm_save_provider(array $data): array
    {
        $settings = aig_llm_state_read();
        if (!isset($settings['llm']) || !is_array($settings['llm'])) {
            $settings['llm'] = [];
        }
        if (!isset($settings['llm']['providers']) || !is_array($settings['llm']['providers'])) {
            $settings['llm']['providers'] = [];
        }
        if (!isset($settings['llm']['models']) || !is_array($settings['llm']['models'])) {
            $settings['llm']['models'] = [];
        }
        if (!isset($settings['llm']['defaults']) || !is_array($settings['llm']['defaults'])) {
            $settings['llm']['defaults'] = [];
        }
        if (!isset($settings['llm']['routing']) || !is_array($settings['llm']['routing'])) {
            $settings['llm']['routing'] = [];
        }

        $providerId = sanitize_key((string) ($data['provider'] ?? aig_llm_current_provider_id() ?: 'local'));
        if ($providerId === '') {
            $providerId = 'local';
        }

        $provider = isset($settings['llm']['providers'][$providerId]) && is_array($settings['llm']['providers'][$providerId])
            ? $settings['llm']['providers'][$providerId]
            : [
                'id'        => $providerId,
                'name'      => ucfirst($providerId),
                'enabled'   => false,
                'transport' => 'openai_compat',
                'base_url'  => '',
                'api_key'   => '',
                'priority'  => 100,
                'timeout'   => 60,
            ];

        $provider['id'] = $providerId;
        $provider['enabled'] = !empty($data['enabled']);
        if (array_key_exists('endpoint', $data)) {
            $provider['base_url'] = esc_url_raw((string) $data['endpoint']);
        }
        if (array_key_exists('api_key', $data) && trim((string) $data['api_key']) !== '') {
            $provider['api_key'] = trim((string) $data['api_key']);
        }
        if (array_key_exists('priority', $data)) {
            $provider['priority'] = (int) $data['priority'];
        }
        if (array_key_exists('timeout', $data)) {
            $provider['timeout'] = max(5, (int) $data['timeout']);
        }

        $settings['llm']['providers'][$providerId] = $provider;

        $selectedModelId = sanitize_key((string) ($data['model_id'] ?? ''));
        $rawModelName    = sanitize_text_field((string) ($data['model'] ?? ''));
        $flatModels      = aig_llm_state_models_flatten($settings['llm']['models']);

        if ($selectedModelId === '' && $rawModelName !== '') {
            foreach ($flatModels as $mid => $mrow) {
                if (!is_array($mrow)) {
                    continue;
                }
                if (sanitize_key((string) ($mrow['provider'] ?? '')) === $providerId && (string) ($mrow['model'] ?? '') === $rawModelName) {
                    $selectedModelId = sanitize_key((string) $mid);
                    break;
                }
            }
        }

        $settings['llm']['defaults']['provider'] = $providerId;
        if ($selectedModelId !== '') {
            $settings['llm']['defaults']['model'] = $selectedModelId;
            $settings['llm']['routing']['primary_model'] = $selectedModelId;
        }
        $settings['llm']['routing']['primary_provider'] = $providerId;

        if (array_key_exists('preset', $data)) {
            $settings['llm']['defaults']['preset'] = sanitize_key((string) $data['preset']);
        }
        if (array_key_exists('temperature', $data)) {
            $settings['llm']['defaults']['temperature'] = (float) $data['temperature'];
        }
        if (array_key_exists('max_tokens', $data)) {
            $settings['llm']['defaults']['max_tokens'] = (int) $data['max_tokens'];
        }
        if (array_key_exists('timeout', $data)) {
            $settings['llm']['defaults']['timeout'] = max(5, (int) $data['timeout']);
        }

        aig_llm_state_write($settings);
        return aig_llm_get_provider();
    }
}

if (!function_exists('aig_llm_generate_openai_compat')) {
    function aig_llm_generate_openai_compat($prompt, array $provider = []): array
    {
        $args = [
            'provider'    => (string) ($provider['provider'] ?? aig_llm_current_provider_id()),
            'model_id'    => (string) ($provider['model_id'] ?? ''),
            'model'       => (string) ($provider['model'] ?? ''),
            'temperature' => isset($provider['temperature']) ? (float) $provider['temperature'] : 0.7,
            'max_tokens'  => isset($provider['max_tokens']) ? (int) $provider['max_tokens'] : 1200,
            'max_latency' => isset($provider['timeout']) ? (int) $provider['timeout'] : 60,
            'preset'      => (string) ($provider['preset'] ?? 'free_first'),
            'task_type'   => 'selftest',
        ];

        $res = aig_llm_generate($prompt, $args);
        $content = (string) ($res['content'] ?? $res['text'] ?? '');

        if (empty($res['html'])) {
            $res['html'] = $content !== '' ? wpautop(esc_html($content)) : '';
        }

        if (empty($res['usage']) || !is_array($res['usage'])) {
            $res['usage'] = [
                'input_tokens'  => (int) ($res['tokens_input'] ?? 0),
                'output_tokens' => (int) ($res['tokens_output'] ?? 0),
                'total_tokens'  => (int) (($res['tokens_input'] ?? 0) + ($res['tokens_output'] ?? 0)),
            ];
        }

        return $res;
    }
}