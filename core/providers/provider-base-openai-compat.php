<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('AIG_Provider_OpenAI_Compat_Base')) {
    abstract class AIG_Provider_OpenAI_Compat_Base implements AIG_Provider_Interface {
        protected string $id = '';
        protected string $label = '';
        protected string $defaultBaseUrl = '';

        public function get_id(): string { return $this->id; }
        public function get_label(): string { return $this->label; }

        public function is_available(array $config = []): bool {
            if (!empty($config['enabled']) && !empty($config['base_url'])) {
                return true;
            }
            return !empty($this->defaultBaseUrl);
        }

        public function list_models(array $config = []): array {
            if (function_exists('aig_models_catalog_for_provider')) {
                return aig_models_catalog_for_provider($this->id);
            }
            return [];
        }

        protected function merge_payload(array $payload, array $config = []): array {
            if (empty($payload['provider_id'])) {
                $payload['provider_id'] = $this->id;
            }
            if (empty($payload['provider_name'])) {
                $payload['provider_name'] = $this->label;
            }
            if (empty($payload['base_url'])) {
                $payload['base_url'] = (string) ($config['base_url'] ?? $this->defaultBaseUrl);
            }
            if (empty($payload['api_key']) && !empty($config['api_key'])) {
                $payload['api_key'] = (string) $config['api_key'];
            }
            if (empty($payload['provider']) || !is_array($payload['provider'])) {
                $payload['provider'] = $config;
                $payload['provider']['id'] = $this->id;
                $payload['provider']['name'] = $config['name'] ?? $this->label;
            }
            return $payload;
        }

        protected function generate_via_http(array $payload): array {
            if (!function_exists('aig_gateway_generate_http')) {
                return [
                    'ok' => false,
                    'error' => 'gateway_http_unavailable',
                    'detail' => 'aig_gateway_generate_http() missing',
                    'provider' => $this->id,
                    'model' => (string) (($payload['model']['model'] ?? $payload['model_id'] ?? '')),
                ];
            }
            return aig_gateway_generate_http($payload);
        }

        public function generate(array $payload, array $config = []): array {
            return $this->generate_via_http($this->merge_payload($payload, $config));
        }

        public function test(array $config = []): array {
            $model = '';
            $list = $this->list_models($config);
            if (!empty($list[0]['model'])) {
                $model = (string) $list[0]['model'];
            }
            if ($model === '' && !empty($config['default_model'])) {
                $model = (string) $config['default_model'];
            }
            if ($model === '') {
                $model = 'gpt-4o-mini';
            }
            return $this->generate([
                'model_id' => sanitize_key($model),
                'model' => ['id' => sanitize_key($model), 'model' => $model],
                'messages' => [
                    ['role' => 'system', 'content' => 'Return only the word OK.'],
                    ['role' => 'user', 'content' => 'Health check'],
                ],
                'temperature' => 0.0,
                'max_tokens' => 10,
                'request_id' => function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('aig_test_', true),
                'task_type' => 'provider_test',
                'preset' => 'balanced',
            ], $config);
        }
    }
}
