<?php
/**
 * AI Article Generator
 * Storage Helpers
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_storage_base_dir')) {
    /**
     * Return base storage directory.
     *
     * @return string
     */
    function aig_storage_base_dir(): string
    {
        if (defined('AIG_STORAGE_DIR')) {
            return AIG_STORAGE_DIR;
        }

        if (defined('AIG_MODULE_DIR')) {
            return AIG_MODULE_DIR . '/storage';
        }

        return __DIR__ . '/../storage';
    }
}

if (!function_exists('aig_storage_path')) {
    /**
     * Build full path for a storage file.
     *
     * @param string $filename
     * @return string
     */
    function aig_storage_path(string $filename): string
    {
        return rtrim(aig_storage_base_dir(), '/\\') . '/' . ltrim($filename, '/\\');
    }
}

if (!function_exists('aig_storage_exists')) {
    /**
     * Check whether a storage file exists.
     *
     * @param string $filename
     * @return bool
     */
    function aig_storage_exists(string $filename): bool
    {
        return file_exists(aig_storage_path($filename));
    }
}

if (!function_exists('aig_storage_read_json')) {
    /**
     * Read JSON file safely.
     *
     * @param string $filename
     * @return array|null
     */
    function aig_storage_read_json(string $filename): ?array
    {
        $path = aig_storage_path($filename);

        if (!file_exists($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            return null;
        }

        return $json;
    }
}

if (!function_exists('aig_storage_read_or_default')) {
    /**
     * Read JSON or return default array.
     *
     * @param string $filename
     * @param array $default
     * @return array
     */
    function aig_storage_read_or_default(string $filename, array $default): array
    {
        $data = aig_storage_read_json($filename);
        return is_array($data) ? $data : $default;
    }
}

if (!function_exists('aig_storage_write_json')) {
    /**
     * Write JSON file safely.
     *
     * @param string $filename
     * @param array  $data
     * @return bool
     */
    function aig_storage_write_json(string $filename, array $data): bool
    {
        $path = aig_storage_path($filename);
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            if (function_exists('wp_mkdir_p')) {
                if (!wp_mkdir_p($dir)) {
                    return false;
                }
            } else {
                if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
                    return false;
                }
            }
        }

        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        if ($json === false) {
            return false;
        }

        $tmp = $path . '.tmp';
        $written = @file_put_contents($tmp, $json);

        if ($written === false) {
            return false;
        }

        return @rename($tmp, $path);
    }
}