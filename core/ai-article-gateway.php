<?php
/**
 * AI Article Generator — Gateway
 *
 * Purpose:
 * - single transport layer for all providers
 * - OpenAI-compatible request formatting
 * - response normalization
 * - latency measurement
 * - error classification
 * - usage tracking hook
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_gateway_now_mysql')) {
    function aig_gateway_now_mysql(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}

if (!function_exists('aig_gateway_get_llm_settings')) {
    function aig_gateway_get_llm_settings(): array
    {
        if (function_exists('aig_settings_get_all')) {
            $all = aig_settings_get_all();
            if (is_array($all) && isset($all['llm']) && is_array($all['llm'])) {
                return $all['llm'];
            }
        }

        return [];
    }
}

if (!function_exists('aig_gateway_find_model_from_settings')) {
    function aig_gateway_find_model_from_settings(string $providerId, string $modelIdOrSlug): array
    {
        $llm = aig_gateway_get_llm_settings();
        $modelsByProvider = isset($llm['models']) && is_array($llm['models']) ? $llm['models'] : [];

        $providerModels = isset($modelsByProvider[$providerId]) && is_array($modelsByProvider[$providerId])
            ? $modelsByProvider[$providerId]
            : [];

        foreach ($providerModels as $row) {
            if (!is_array($row)) {
                continue;
            }

            $id    = (string) ($row['id'] ?? '');
            $model = (string) ($row['model'] ?? '');

            if ($modelIdOrSlug !== '' && ($id === $modelIdOrSlug || $model === $modelIdOrSlug)) {
                return $row;
            }
        }

        return [];
    }
}

if (!function_exists('aig_gateway_find_provider_from_settings')) {
    function aig_gateway_find_provider_from_settings(string $providerId): array
    {
        $llm = aig_gateway_get_llm_settings();
        $providers = isset($llm['providers']) && is_array($llm['providers']) ? $llm['providers'] : [];

        $row = isset($providers[$providerId]) && is_array($providers[$providerId]) ? $providers[$providerId] : [];
        if (empty($row)) {
            return [];
        }

        if (empty($row['id'])) {
            $row['id'] = $providerId;
        }

        if (empty($row['base_url']) && !empty($row['endpoint'])) {
            $row['base_url'] = (string) $row['endpoint'];
        }

        return $row;
    }
}

if (!function_exists('aig_gateway_normalize_messages')) {
    function aig_gateway_normalize_messages($messages): array
    {
        if (!is_array($messages)) {
            return [];
        }

        $out = [];

        foreach ($messages as $msg) {
            if (!is_array($msg)) {
                continue;
            }

            $role = sanitize_key((string) ($msg['role'] ?? 'user'));
            $content = isset($msg['content']) ? (string) $msg['content'] : '';

            if ($content === '') {
                continue;
            }

            if (!in_array($role, ['system', 'user', 'assistant', 'tool'], true)) {
                $role = 'user';
            }

            $out[] = [
                'role'    => $role,
                'content' => $content,
            ];
        }

        return $out;
    }
}

if (!function_exists('aig_gateway_prepare_url')) {
    function aig_gateway_prepare_url(string $baseUrl): string
    {
        $baseUrl = trim($baseUrl);

        if ($baseUrl === '') {
            return '';
        }

        $baseUrl = rtrim($baseUrl, '/');

        if (substr($baseUrl, -3) === '/v1') {
            return $baseUrl . '/chat/completions';
        }

        if (substr($baseUrl, -17) === '/chat/completions') {
            return $baseUrl;
        }

        return $baseUrl . '/chat/completions';
    }
}

if (!function_exists('aig_gateway_build_headers')) {
    function aig_gateway_build_headers(array $payload): array
    {
        $provider   = isset($payload['provider']) && is_array($payload['provider']) ? $payload['provider'] : [];
        $headers    = isset($payload['headers']) && is_array($payload['headers']) ? $payload['headers'] : [];
        $apiKey     = isset($payload['api_key']) ? trim((string) $payload['api_key']) : '';
        $providerId = sanitize_key((string) ($payload['provider_id'] ?? ($provider['id'] ?? 'custom')));

        $final = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        if ($apiKey !== '') {
            $final['Authorization'] = 'Bearer ' . $apiKey;
        }

        foreach ($headers as $k => $v) {
            $key = trim((string) $k);
            if ($key === '') {
                continue;
            }
            $final[$key] = (string) $v;
        }

        if ($providerId === 'openrouter') {
            if (empty($final['HTTP-Referer'])) {
                $final['HTTP-Referer'] = home_url('/');
            }
            if (empty($final['X-Title'])) {
                $final['X-Title'] = 'AI Article Generator';
            }
        }

        return $final;
    }
}

if (!function_exists('aig_gateway_build_body')) {
    function aig_gateway_build_body(array $payload): array
    {
        $model    = isset($payload['model']) && is_array($payload['model']) ? $payload['model'] : [];
        $messages = aig_gateway_normalize_messages($payload['messages'] ?? []);

        $modelSlug = '';
        if (!empty($model['model'])) {
            $modelSlug = (string) $model['model'];
        } elseif (!empty($payload['model'])) {
            $modelSlug = is_array($payload['model']) ? (string) ($payload['model']['model'] ?? '') : (string) $payload['model'];
        } elseif (!empty($payload['model_id'])) {
            $modelSlug = (string) $payload['model_id'];
        }

        $body = [
            'model'       => $modelSlug,
            'messages'    => $messages,
            'temperature' => isset($payload['temperature']) ? (float) $payload['temperature'] : 0.7,
        ];

        $maxTokens = (int) ($payload['max_tokens'] ?? 0);
        if ($maxTokens > 0) {
            $body['max_tokens'] = $maxTokens;
        }

        $wantJson    = !empty($payload['json_mode']);
        $jsonSupport = !empty($model['json_support']);

        if ($wantJson && $jsonSupport) {
            $body['response_format'] = ['type' => 'json_object'];
        }

        $passthrough = [
            'top_p',
            'frequency_penalty',
            'presence_penalty',
            'stop',
            'seed',
        ];

        foreach ($passthrough as $field) {
            if (array_key_exists($field, $payload)) {
                $body[$field] = $payload[$field];
            }
        }

        return $body;
    }
}

if (!function_exists('aig_gateway_http_status_to_error')) {
    function aig_gateway_http_status_to_error(int $status, array $json = []): string
    {
        $message = '';
        if (isset($json['error']['message']) && is_string($json['error']['message'])) {
            $message = strtolower(trim($json['error']['message']));
        }

        if ($status === 401 || $status === 403) {
            return 'auth_error';
        }

        if ($status === 404) {
            return 'model_not_found';
        }

        if ($status === 408) {
            return 'timeout';
        }

        if ($status === 409) {
            return 'provider_down';
        }

        if ($status === 422) {
            return 'bad_request';
        }

        if ($status === 429) {
            if (strpos($message, 'quota') !== false || strpos($message, 'insufficient') !== false) {
                return 'quota_exceeded';
            }
            return 'rate_limited';
        }

        if ($status >= 500) {
            return 'provider_down';
        }

        if ($status >= 400) {
            if (strpos($message, 'policy') !== false || strpos($message, 'safety') !== false) {
                return 'policy_block';
            }
            return 'bad_request';
        }

        return 'http_error';
    }
}

if (!function_exists('aig_gateway_extract_content')) {
    function aig_gateway_extract_content(array $json): string
    {
        $content = $json['choices'][0]['message']['content'] ?? '';

        if (is_string($content)) {
            return $content;
        }

        if (is_array($content)) {
            $parts = [];

            foreach ($content as $item) {
                if (is_array($item) && isset($item['text']) && is_string($item['text'])) {
                    $parts[] = $item['text'];
                } elseif (is_string($item)) {
                    $parts[] = $item;
                }
            }

            return trim(implode("\n", $parts));
        }

        return '';
    }
}

if (!function_exists('aig_gateway_extract_usage')) {
    function aig_gateway_extract_usage(array $json): array
    {
        $usage = isset($json['usage']) && is_array($json['usage']) ? $json['usage'] : [];

        return [
            'tokens_input'  => (int) ($usage['prompt_tokens'] ?? $usage['input_tokens'] ?? 0),
            'tokens_output' => (int) ($usage['completion_tokens'] ?? $usage['output_tokens'] ?? 0),
            'tokens_total'  => (int) ($usage['total_tokens'] ?? 0),
        ];
    }
}

if (!function_exists('aig_gateway_normalize_success')) {
    function aig_gateway_normalize_success(array $payload, array $json, int $latencyMs, int $status): array
    {
        $provider = isset($payload['provider']) && is_array($payload['provider']) ? $payload['provider'] : [];
        $model    = isset($payload['model']) && is_array($payload['model']) ? $payload['model'] : [];

        $content = aig_gateway_extract_content($json);
        $usage   = aig_gateway_extract_usage($json);

        $result = [
            'ok'            => ($content !== ''),
            'error'         => ($content !== '') ? '' : 'empty_output',
            'detail'        => ($content !== '') ? '' : 'Provider returned no text content',
            'content'       => $content,
            'text'          => $content,
            'provider'      => (string) ($payload['provider_id'] ?? ($provider['id'] ?? '')),
            'provider_name' => (string) ($provider['name'] ?? ''),
            'model'         => (string) ($model['model'] ?? $payload['model_id'] ?? ''),
            'model_id'      => (string) ($payload['model_id'] ?? ''),
            'http_status'   => $status,
            'latency_ms'    => $latencyMs,
            'tokens_input'  => $usage['tokens_input'],
            'tokens_output' => $usage['tokens_output'],
            'tokens_total'  => $usage['tokens_total'],
            'raw'           => $json,
            'request_id'    => (string) ($payload['request_id'] ?? ''),
        ];

        return $result;
    }
}

if (!function_exists('aig_gateway_normalize_error')) {
    function aig_gateway_normalize_error(
        array $payload,
        string $errorClass,
        $detail,
        int $latencyMs = 0,
        int $status = 0,
        array $raw = []
    ): array {
        $provider = isset($payload['provider']) && is_array($payload['provider']) ? $payload['provider'] : [];
        $model    = isset($payload['model']) && is_array($payload['model']) ? $payload['model'] : [];

        return [
            'ok'            => false,
            'error'         => sanitize_key($errorClass),
            'detail'        => $detail,
            'content'       => '',
            'text'          => '',
            'provider'      => (string) ($payload['provider_id'] ?? ($provider['id'] ?? '')),
            'provider_name' => (string) ($provider['name'] ?? ''),
            'model'         => (string) ($model['model'] ?? $payload['model_id'] ?? ''),
            'model_id'      => (string) ($payload['model_id'] ?? ''),
            'http_status'   => $status,
            'latency_ms'    => $latencyMs,
            'tokens_input'  => 0,
            'tokens_output' => 0,
            'tokens_total'  => 0,
            'raw'           => $raw,
            'request_id'    => (string) ($payload['request_id'] ?? ''),
        ];
    }
}

if (!function_exists('aig_gateway_log_usage')) {
    function aig_gateway_log_usage(array $payload, array $result): void
    {
        if (!empty($result['ok'])) {
            if (function_exists('ai_article_log')) {
                ai_article_log('gateway_success', [
                    'request_id'    => $result['request_id'] ?? '',
                    'provider'      => $result['provider'] ?? '',
                    'model'         => $result['model'] ?? '',
                    'latency_ms'    => $result['latency_ms'] ?? 0,
                    'tokens_input'  => $result['tokens_input'] ?? 0,
                    'tokens_output' => $result['tokens_output'] ?? 0,
                    'tokens_total'  => $result['tokens_total'] ?? 0,
                ], 'info');
            }
        } else {
            if (function_exists('ai_article_log')) {
                ai_article_log('gateway_error', [
                    'request_id' => $result['request_id'] ?? '',
                    'provider'   => $result['provider'] ?? '',
                    'model'      => $result['model'] ?? '',
                    'error'      => $result['error'] ?? '',
                    'status'     => $result['http_status'] ?? 0,
                    'latency_ms' => $result['latency_ms'] ?? 0,
                    'detail'     => $result['detail'] ?? '',
                ], 'warn');
            }
        }

        if (function_exists('aig_usage_record')) {
            aig_usage_record([
                'ts'            => aig_gateway_now_mysql(),
                'request_id'    => (string) ($result['request_id'] ?? ''),
                'task_type'     => (string) ($payload['task_type'] ?? ''),
                'preset'        => (string) ($payload['preset'] ?? ''),
                'provider_id'   => (string) ($result['provider'] ?? ''),
                'model_id'      => (string) ($result['model_id'] ?? ''),
                'model'         => (string) ($result['model'] ?? ''),
                'ok'            => !empty($result['ok']),
                'error'         => (string) ($result['error'] ?? ''),
                'http_status'   => (int) ($result['http_status'] ?? 0),
                'latency_ms'    => (int) ($result['latency_ms'] ?? 0),
                'tokens_input'  => (int) ($result['tokens_input'] ?? 0),
                'tokens_output' => (int) ($result['tokens_output'] ?? 0),
                'tokens_total'  => (int) ($result['tokens_total'] ?? 0),
            ]);
        }
    }
}

if (!function_exists('aig_gateway_validate_payload')) {
    function aig_gateway_validate_payload(array $payload): array
    {
        $llm = aig_gateway_get_llm_settings();

        $providerId = sanitize_key((string) (
            $payload['provider_id']
            ?? (is_array($payload['provider'] ?? null) ? ($payload['provider']['id'] ?? '') : ($payload['provider'] ?? ''))
            ?? ''
        ));

        $modelId = sanitize_key((string) (
            $payload['model_id']
            ?? (is_array($payload['model'] ?? null) ? ($payload['model']['id'] ?? '') : '')
            ?? ''
        ));

        $provider = isset($payload['provider']) && is_array($payload['provider']) ? $payload['provider'] : [];
        $model    = isset($payload['model']) && is_array($payload['model']) ? $payload['model'] : [];

        /**
         * Preferred/fallback provider resolution
         */
        if ($providerId === '') {
            $providerId = sanitize_key((string) (
                $payload['preferred_provider']
                ?? ($llm['defaults']['provider'] ?? '')
                ?? ($llm['routing']['primary_provider'] ?? '')
                ?? 'openai'
            ));
        }

        if (empty($provider)) {
            if (function_exists('aig_settings_get_provider')) {
                $resolved = aig_settings_get_provider($providerId);
                if (is_array($resolved) && !empty($resolved)) {
                    $provider = $resolved;
                }
            }

            if (empty($provider)) {
                $provider = aig_gateway_find_provider_from_settings($providerId);
            }
        }

        if (empty($provider['id'])) {
            $provider['id'] = $providerId;
        }

        if (empty($provider['base_url']) && !empty($provider['endpoint'])) {
            $provider['base_url'] = (string) $provider['endpoint'];
        }

        if (empty($provider['name'])) {
            $provider['name'] = ucfirst($providerId ?: 'OpenAI');
        }

        /**
         * API key fallback
         */
        if (empty($payload['api_key']) && !empty($provider['api_key'])) {
            $payload['api_key'] = (string) $provider['api_key'];
        }

        /**
         * Model resolution
         */
        $payloadModelScalar = '';
        if (!is_array($payload['model'] ?? null) && !empty($payload['model'])) {
            $payloadModelScalar = (string) $payload['model'];
        }

        if ($modelId === '') {
            $modelId = sanitize_key((string) (
                $payload['preferred_model']
                ?? ($payloadModelScalar !== '' ? $payloadModelScalar : '')
                ?? ($llm['defaults']['model'] ?? '')
                ?? ($llm['routing']['primary_model'] ?? '')
                ?? 'gpt-4o-mini'
            ));
        }

        if (empty($model)) {
            if (function_exists('aig_settings_get_model')) {
                $resolvedModel = aig_settings_get_model($modelId);
                if (is_array($resolvedModel) && !empty($resolvedModel)) {
                    $model = $resolvedModel;
                }
            }

            if (empty($model)) {
                $model = aig_gateway_find_model_from_settings($providerId, $modelId);
            }
        }

        if (empty($model) && $payloadModelScalar !== '') {
            $model = [
                'id'    => $modelId !== '' ? $modelId : sanitize_key($payloadModelScalar),
                'model' => $payloadModelScalar,
            ];
        }

        if (empty($model['id'])) {
            $model['id'] = $modelId !== '' ? $modelId : 'gpt-4o-mini';
        }

        if (empty($model['model'])) {
            $model['model'] = !empty($payloadModelScalar)
                ? $payloadModelScalar
                : (!empty($modelId) ? $modelId : 'gpt-4o-mini');
        }

        /**
         * Base URL resolution
         */
        $baseUrl = trim((string) (
            $payload['base_url']
            ?? $payload['endpoint']
            ?? ($provider['base_url'] ?? '')
            ?? ($provider['endpoint'] ?? '')
            ?? ''
        ));

        if ($baseUrl === '' && $providerId === 'openai') {
            $baseUrl = 'https://api.openai.com/v1';
        }

        /**
         * Messages
         */
        $messages = aig_gateway_normalize_messages($payload['messages'] ?? []);

        $payload['provider_id'] = $providerId;
        $payload['provider']    = $provider;
        $payload['model_id']    = (string) ($model['id'] ?? $modelId);
        $payload['model']       = $model;
        $payload['base_url']    = $baseUrl;
        $payload['messages']    = $messages;
        $payload['timeout']     = max(5, (int) ($payload['timeout'] ?? ($provider['timeout'] ?? 60)));

        if (empty($payload['api_key']) && !empty($llm['providers'][$providerId]['api_key'])) {
            $payload['api_key'] = (string) $llm['providers'][$providerId]['api_key'];
        }

        if (empty($model['model'])) {
            return [
                'ok'      => false,
                'payload' => $payload,
                'error'   => 'missing_model',
                'detail'  => 'Model slug is empty',
            ];
        }

        if ($baseUrl === '') {
            return [
                'ok'      => false,
                'payload' => $payload,
                'error'   => 'missing_configuration',
                'detail'  => 'Base URL is empty',
            ];
        }

        if (empty($messages)) {
            return [
                'ok'      => false,
                'payload' => $payload,
                'error'   => 'bad_request',
                'detail'  => 'Messages array is empty',
            ];
        }

        return [
            'ok'      => true,
            'payload' => $payload,
        ];
    }
}

if (!function_exists('aig_gateway_generate')) {
    function aig_gateway_generate(array $payload): array
    {
        $validated = aig_gateway_validate_payload($payload);

        if (empty($validated['ok'])) {
            return aig_gateway_normalize_error(
                $validated['payload'] ?? $payload,
                (string) ($validated['error'] ?? 'bad_request'),
                (string) ($validated['detail'] ?? 'Invalid payload')
            );
        }

        $payload = $validated['payload'];
        $url     = aig_gateway_prepare_url((string) ($payload['base_url'] ?? ''));
        $headers = aig_gateway_build_headers($payload);
        $body    = aig_gateway_build_body($payload);

        if (function_exists('ai_article_log')) {
            ai_article_log('gateway_request_start', [
                'provider_id' => $payload['provider_id'] ?? '',
                'model_id'    => $payload['model_id'] ?? '',
                'model'       => $body['model'] ?? '',
                'base_url'    => $payload['base_url'] ?? '',
                'url'         => $url,
                'timeout'     => $payload['timeout'] ?? 60,
                'has_api_key' => !empty($payload['api_key']),
            ], 'info');
        }

        if ($url === '') {
            return aig_gateway_normalize_error(
                $payload,
                'missing_configuration',
                'Prepared gateway URL is empty'
            );
        }

        $start = microtime(true);

        $response = wp_remote_post($url, [
            'timeout' => (int) ($payload['timeout'] ?? 60),
            'headers' => $headers,
            'body'    => wp_json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $latencyMs = (int) round((microtime(true) - $start) * 1000);

        if (is_wp_error($response)) {
            $message = $response->get_error_message();
            $codes   = $response->get_error_codes();
            $data    = $response->get_error_data();
            $lower   = strtolower($message);

            $errorClass = 'network_error';

            if (strpos($lower, 'timed out') !== false || strpos($lower, 'timeout') !== false) {
                $errorClass = 'timeout';
            }

            if (function_exists('ai_article_log')) {
                ai_article_log('gateway_wp_error', [
                    'error_class' => $errorClass,
                    'message'     => $message,
                    'codes'       => $codes,
                    'data'        => $data,
                    'base_url'    => $payload['base_url'] ?? '',
                    'provider_id' => $payload['provider_id'] ?? '',
                    'model_id'    => $payload['model_id'] ?? '',
                    'timeout'     => $payload['timeout'] ?? '',
                ], 'error');
            }

            $result = aig_gateway_normalize_error(
                $payload,
                $errorClass,
                [
                    'message' => $message,
                    'codes'   => $codes,
                    'data'    => $data,
                ],
                $latencyMs
            );

            aig_gateway_log_usage($payload, $result);
            return $result;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $raw    = (string) wp_remote_retrieve_body($response);
        $json   = json_decode($raw, true);

        if ($status < 200 || $status >= 300) {
            $result = aig_gateway_normalize_error(
                $payload,
                aig_gateway_http_status_to_error($status, is_array($json) ? $json : []),
                [
                    'status' => $status,
                    'body'   => is_array($json) ? $json : $raw,
                ],
                $latencyMs,
                $status,
                is_array($json) ? $json : []
            );

            aig_gateway_log_usage($payload, $result);
            return $result;
        }

        if (!is_array($json)) {
            $result = aig_gateway_normalize_error(
                $payload,
                'invalid_json',
                'Provider response is not valid JSON',
                $latencyMs,
                $status
            );

            aig_gateway_log_usage($payload, $result);
            return $result;
        }

        $result = aig_gateway_normalize_success($payload, $json, $latencyMs, $status);

        aig_gateway_log_usage($payload, $result);
        return $result;
    }
}

if (!function_exists('aig_ai_gateway_generate')) {
    function aig_ai_gateway_generate(array $payload): array
    {
        return aig_gateway_generate($payload);
    }
}

if (!class_exists('AI_Article_Gateway')) {
    class AI_Article_Gateway
    {
        public static function generate(array $payload): array
        {
            return aig_gateway_generate($payload);
        }

        public static function instance(): self
        {
            static $instance = null;

            if (!$instance instanceof self) {
                $instance = new self();
            }

            return $instance;
        }
    }
}