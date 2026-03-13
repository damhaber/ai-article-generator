<?php
/**
 * AI Article Generator
 * Feature Map Access Layer
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_feature_map_defaults')) {
    /**
     * Default feature flags.
     *
     * @return array
     */
    function aig_feature_map_defaults(): array
    {
        return [
            'article_generation_disabled' => false,
            'rewrite_disabled' => false,
            'seo_disabled' => false,
            'selftest_runtime_checks_enabled' => true,
            'quality_gate_enabled' => true,
            'news_cache_enabled' => true,
        ];
    }
}

if (!function_exists('aig_feature_map_get_all')) {
    /**
     * Return merged feature map.
     *
     * @return array
     */
    function aig_feature_map_get_all(): array
    {
        $defaults = aig_feature_map_defaults();

        if (!function_exists('aig_storage_read_json')) {
            return $defaults;
        }

        $stored = aig_storage_read_json('feature-map.json');
        if (!is_array($stored)) {
            return $defaults;
        }

        return array_replace($defaults, $stored);
    }
}

if (!function_exists('aig_feature_is_enabled')) {
    /**
     * Check single feature flag.
     *
     * @param string $key
     * @param bool   $default
     * @return bool
     */
    function aig_feature_is_enabled(string $key, bool $default = true): bool
    {
        $map = aig_feature_map_get_all();

        if (!array_key_exists($key, $map)) {
            return $default;
        }

        return (bool) $map[$key];
    }
}

if (!function_exists('aig_feature_map_set_all')) {
    /**
     * Save full feature map.
     *
     * @param array $data
     * @return bool
     */
    function aig_feature_map_set_all(array $data): bool
    {
        if (!function_exists('aig_storage_write_json')) {
            return false;
        }

        $merged = array_replace(aig_feature_map_defaults(), $data);

        return aig_storage_write_json('feature-map.json', $merged);
    }
}