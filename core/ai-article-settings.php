<?php
/**
 * AI Article Generator
 * Settings Access Layer
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_settings_defaults')) {
    /**
     * Default module settings.
     *
     * @return array
     */
    function aig_settings_defaults(): array
    {
        return [
            'version' => '1.0.0',
            'article' => [
                'default_lang' => 'tr',
                'default_tone' => 'analytical',
                'default_length' => 'long',
                'default_template' => 'news_analysis',
                'auto_rewrite' => true,
                'auto_seo' => true,
            ],
            'news' => [
                'cache_ttl_seconds' => 1800,
                'max_items_per_source' => 10,
            ],
            'routing' => [
                'default_quality_profile' => 'balanced',
            ],
            'quality' => [
                'threshold' => 70,
            ],
        ];
    }
}

if (!function_exists('aig_settings_merge_with_defaults')) {
    /**
     * Merge stored settings with defaults.
     *
     * @param array $data
     * @return array
     */
    function aig_settings_merge_with_defaults(array $data): array
    {
        $defaults = aig_settings_defaults();
        return array_replace_recursive($defaults, $data);
    }
}

if (!function_exists('aig_settings_get_all')) {
    /**
     * Return merged settings.
     *
     * @return array
     */
    function aig_settings_get_all(): array
    {
        $defaults = aig_settings_defaults();

        if (!function_exists('aig_storage_read_json')) {
            return $defaults;
        }

        $stored = aig_storage_read_json('settings.json');
        if (!is_array($stored)) {
            return $defaults;
        }

        return aig_settings_merge_with_defaults($stored);
    }
}

if (!function_exists('aig_settings_get')) {
    /**
     * Dot-path settings getter.
     *
     * Example:
     * aig_settings_get('article.default_lang', 'tr')
     *
     * @param string $path
     * @param mixed  $default
     * @return mixed
     */
    function aig_settings_get(string $path, $default = null)
    {
        $settings = aig_settings_get_all();

        if ($path === '') {
            return $settings;
        }

        $segments = explode('.', $path);
        $value = $settings;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('aig_settings_set_all')) {
    /**
     * Save full settings payload.
     *
     * @param array $data
     * @return bool
     */
    function aig_settings_set_all(array $data): bool
    {
        if (!function_exists('aig_storage_write_json')) {
            return false;
        }

        $merged = aig_settings_merge_with_defaults($data);

        return aig_storage_write_json('settings.json', $merged);
    }
}