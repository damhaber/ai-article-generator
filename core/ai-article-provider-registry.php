<?php
/**
 * AI Article Generator
 * Provider Registry
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_provider_registry_all')) {
    /**
     * Return full static provider registry map.
     *
     * @return array
     */
    function aig_provider_registry_all(): array
    {
        return [
            'openai' => [
                'class' => 'AIG_Provider_OpenAI',
                'label' => 'OpenAI',
            ],
            'groq' => [
                'class' => 'AIG_Provider_Groq',
                'label' => 'Groq',
            ],
            'gemini' => [
                'class' => 'AIG_Provider_Gemini',
                'label' => 'Gemini',
            ],
            'deepseek' => [
                'class' => 'AIG_Provider_DeepSeek',
                'label' => 'DeepSeek',
            ],
            'mistral' => [
                'class' => 'AIG_Provider_Mistral',
                'label' => 'Mistral',
            ],
            'ollama' => [
                'class' => 'AIG_Provider_Ollama',
                'label' => 'Ollama',
            ],
            'openrouter' => [
                'class' => 'AIG_Provider_OpenRouter',
                'label' => 'OpenRouter',
            ],
        ];
    }
}

if (!function_exists('aig_provider_registry_keys')) {
    /**
     * Return all registered provider keys.
     *
     * @return array
     */
    function aig_provider_registry_keys(): array
    {
        return array_keys(aig_provider_registry_all());
    }
}

if (!function_exists('aig_provider_registry_has')) {
    /**
     * Check whether a provider key is registered.
     *
     * @param string $key
     * @return bool
     */
    function aig_provider_registry_has(string $key): bool
    {
        $all = aig_provider_registry_all();
        return isset($all[$key]);
    }
}

if (!function_exists('aig_provider_registry_make')) {
    /**
     * Create provider instance by key.
     *
     * @param string $key
     * @return object|null
     */
    function aig_provider_registry_make(string $key)
    {
        $all = aig_provider_registry_all();

        if (!isset($all[$key])) {
            return null;
        }

        $class = (string) ($all[$key]['class'] ?? '');
        if ($class === '' || !class_exists($class)) {
            return null;
        }

        return new $class();
    }
}

if (!function_exists('aig_provider_registry_config')) {
    /**
     * Return provider config block from storage/providers.json
     *
     * Supports both:
     * - { "providers": { ... } }
     * - legacy flat map { "openai": { ... } }
     *
     * @param string $key
     * @return array
     */
    function aig_provider_registry_config(string $key): array
    {
        if (!function_exists('aig_storage_read_json')) {
            return [];
        }

        $json = aig_storage_read_json('providers.json');
        if (!is_array($json)) {
            return [];
        }

        if (isset($json['providers']) && is_array($json['providers'])) {
            $block = $json['providers'][$key] ?? [];
            return is_array($block) ? $block : [];
        }

        $legacy = $json[$key] ?? [];
        return is_array($legacy) ? $legacy : [];
    }
}

if (!function_exists('aig_provider_registry_is_enabled')) {
    /**
     * Check whether provider is enabled by config.
     *
     * Missing config defaults to true for registered providers.
     *
     * @param string $key
     * @return bool
     */
    function aig_provider_registry_is_enabled(string $key): bool
    {
        if (!aig_provider_registry_has($key)) {
            return false;
        }

        $config = aig_provider_registry_config($key);

        if (array_key_exists('enabled', $config)) {
            return (bool) $config['enabled'];
        }

        return true;
    }
}

if (!function_exists('aig_provider_registry_is_available')) {
    /**
     * Check runtime availability of provider.
     *
     * Rules:
     * - must be registered
     * - must be enabled
     * - class must exist
     * - if provider has is_available(), trust it
     *
     * @param string $key
     * @return bool
     */
    function aig_provider_registry_is_available(string $key): bool
    {
        if (!aig_provider_registry_has($key)) {
            return false;
        }

        if (!aig_provider_registry_is_enabled($key)) {
            return false;
        }

        $provider = aig_provider_registry_make($key);
        if (!$provider) {
            return false;
        }

        if (method_exists($provider, 'is_available')) {
            return (bool) $provider->is_available();
        }

        return true;
    }
}

if (!function_exists('aig_provider_registry_available_keys')) {
    /**
     * Return registered and currently available provider keys.
     *
     * @return array
     */
    function aig_provider_registry_available_keys(): array
    {
        $keys = aig_provider_registry_keys();
        $available = [];

        foreach ($keys as $key) {
            if (aig_provider_registry_is_available($key)) {
                $available[] = $key;
            }
        }

        return $available;
    }
}

if (!function_exists('aig_provider_registry_describe')) {
    /**
     * Return human-readable registry state for debug/selftest.
     *
     * @return array
     */
    function aig_provider_registry_describe(): array
    {
        $all = aig_provider_registry_all();
        $rows = [];

        foreach ($all as $key => $item) {
            $class = (string) ($item['class'] ?? '');
            $rows[$key] = [
                'label' => (string) ($item['label'] ?? $key),
                'class' => $class,
                'class_exists' => ($class !== '' && class_exists($class)),
                'enabled' => aig_provider_registry_is_enabled($key),
                'available' => aig_provider_registry_is_available($key),
            ];
        }

        return $rows;
    }
}