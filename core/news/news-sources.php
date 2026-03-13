<?php
// core/news/news-sources.php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * News Sources Loader / Resolver
 *
 * Amaç:
 * - data/news-sources.json dosyasını güvenli şekilde okumak
 * - kategori bazlı aktif kaynakları döndürmek
 * - WP kategori slug -> engine category eşlemesi yapmak
 *
 * Not:
 * - Bu dosya HTTP isteği atmaz
 * - Sadece kaynak veri çözümlemesi yapar
 */

if (!function_exists('aig_news_sources_file')) {
    /**
     * news-sources.json tam dosya yolu
     */
    function aig_news_sources_file(): string
    {
        if (defined('AI_ARTICLE_MODULE_PATH')) {
            return trailingslashit(AI_ARTICLE_MODULE_PATH) . 'data/news-sources.json';
        }

        if (defined('AIG_MODULE_DIR')) {
            return trailingslashit(AIG_MODULE_DIR) . 'data/news-sources.json';
        }

        // Güvenli fallback
        return dirname(__DIR__, 2) . '/data/news-sources.json';
    }
}

if (!function_exists('aig_news_sources_default_data')) {
    /**
     * news-sources.json varsayılan fallback verisi
     */
    function aig_news_sources_default_data(): array
    {
        return [
            'version'          => '0.0.0',
            'updated_at'       => '',
            'default_category' => 'tech',
            'categories'       => [
                'tech'    => [],
                'finance' => [],
                'science' => [],
                'ai'      => [],
                'gaming'  => [],
                'defense' => [],
                'space'   => [],
            ],
            'wp_category_map'  => [],
        ];
    }
}

if (!function_exists('aig_news_get_all_sources')) {
    /**
     * Ham JSON verisini oku ve normalize et
     */
    function aig_news_get_all_sources(): array
    {
        $file = aig_news_sources_file();

        if (!file_exists($file) || !is_readable($file)) {
            return aig_news_sources_default_data();
        }

        $raw = file_get_contents($file);
        if ($raw === false || trim($raw) === '') {
            return aig_news_sources_default_data();
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return aig_news_sources_default_data();
        }

        $defaults = aig_news_sources_default_data();

        $version_key = isset($data['version']) ? 'version' : (isset($data['schema_version']) ? 'schema_version' : null);

        $data['version'] = $version_key ? (string) ($data[$version_key] ?? $defaults['version']) : $defaults['version'];
        $data['updated_at'] = isset($data['updated_at']) ? (string) $data['updated_at'] : $defaults['updated_at'];
        $data['default_category'] = isset($data['default_category'])
            ? sanitize_key((string) $data['default_category'])
            : $defaults['default_category'];

        $data['categories'] = isset($data['categories']) && is_array($data['categories'])
            ? $data['categories']
            : $defaults['categories'];

        $data['wp_category_map'] = isset($data['wp_category_map']) && is_array($data['wp_category_map'])
            ? $data['wp_category_map']
            : $defaults['wp_category_map'];

        $normalized_categories = [];
        foreach ($data['categories'] as $cat => $sources) {
            $cat_key = sanitize_key((string) $cat);
            if ($cat_key === '') {
                continue;
            }
            $normalized_categories[$cat_key] = is_array($sources) ? array_values($sources) : [];
        }

        if (empty($normalized_categories)) {
            $normalized_categories = $defaults['categories'];
        }

        $data['categories'] = $normalized_categories;

        if (!isset($data['categories'][$data['default_category']])) {
            $data['default_category'] = 'tech';
        }

        $normalized_map = [];
        foreach ($data['wp_category_map'] as $slug => $engine_category) {
            $normalized_slug = sanitize_title((string) $slug);
            $normalized_engine = sanitize_key((string) $engine_category);

            if ($normalized_slug === '' || $normalized_engine === '') {
                continue;
            }

            $normalized_map[$normalized_slug] = $normalized_engine;
        }

        $data['wp_category_map'] = $normalized_map;

        return $data;
    }
}

if (!function_exists('aig_news_get_categories')) {
    /**
     * Geçerli engine kategorilerini döndür
     */
    function aig_news_get_categories(): array
    {
        $all = aig_news_get_all_sources();
        $categories = array_keys(isset($all['categories']) && is_array($all['categories']) ? $all['categories'] : []);

        $categories = array_values(array_filter(array_map('sanitize_key', $categories)));

        if (empty($categories)) {
            return ['tech', 'finance', 'science', 'ai', 'gaming', 'defense', 'space'];
        }

        return $categories;
    }
}

if (!function_exists('aig_news_get_default_category')) {
    /**
     * Varsayılan kategori
     */
    function aig_news_get_default_category(): string
    {
        $all = aig_news_get_all_sources();
        $default = isset($all['default_category']) ? sanitize_key((string) $all['default_category']) : 'tech';

        return aig_news_is_valid_category($default) ? $default : 'tech';
    }
}

if (!function_exists('aig_news_is_valid_category')) {
    /**
     * Kategori doğrulama
     */
    function aig_news_is_valid_category(string $category): bool
    {
        $category = sanitize_key($category);
        return in_array($category, aig_news_get_categories(), true);
    }
}

if (!function_exists('aig_news_normalize_source_row')) {
    /**
     * Tek bir source satırını normalize et
     */
    function aig_news_normalize_source_row(array $row, string $category = ''): array
    {
        $normalized = [
            'id'       => isset($row['id']) ? sanitize_key((string) $row['id']) : '',
            'name'     => isset($row['name']) ? sanitize_text_field((string) $row['name']) : '',
            'type'     => isset($row['type']) ? sanitize_key((string) $row['type']) : 'rss',
            'url'      => isset($row['url']) ? esc_url_raw((string) $row['url']) : '',
            'lang'     => isset($row['lang']) ? sanitize_key((string) $row['lang']) : 'en',
            'priority' => isset($row['priority']) ? (int) $row['priority'] : 0,
            'active'   => !empty($row['active']),
            'category' => sanitize_key($category),
        ];

        if ($normalized['id'] === '' && $normalized['name'] !== '') {
            $normalized['id'] = sanitize_key($normalized['name']);
        }

        if ($normalized['type'] === '') {
            $normalized['type'] = 'rss';
        }

        if ($normalized['priority'] < 0) {
            $normalized['priority'] = 0;
        }
        if ($normalized['priority'] > 100) {
            $normalized['priority'] = 100;
        }

        return $normalized;
    }
}

if (!function_exists('aig_news_get_sources_by_category')) {
    /**
     * Kategoriye göre aktif kaynakları döndür
     */
    function aig_news_get_sources_by_category(string $category): array
    {
        $category = sanitize_key($category);
        if (!aig_news_is_valid_category($category)) {
            return [];
        }

        $all = aig_news_get_all_sources();
        $rows = $all['categories'][$category] ?? [];

        if (!is_array($rows) || empty($rows)) {
            return [];
        }

        $out = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $source = aig_news_normalize_source_row($row, $category);

            if ($source['active'] !== true) {
                continue;
            }
            if ($source['type'] !== 'rss') {
                continue;
            }
            if ($source['url'] === '') {
                continue;
            }

            $out[] = $source;
        }

        usort($out, static function (array $a, array $b): int {
            $prioCompare = ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0);
            if ($prioCompare !== 0) {
                return $prioCompare;
            }

            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return $out;
    }
}

if (!function_exists('aig_news_get_wp_category_map')) {
    /**
     * WP slug -> engine category map döndür
     */
    function aig_news_get_wp_category_map(): array
    {
        $all = aig_news_get_all_sources();
        return isset($all['wp_category_map']) && is_array($all['wp_category_map'])
            ? $all['wp_category_map']
            : [];
    }
}

if (!function_exists('aig_news_map_wp_slug_to_engine_category')) {
    /**
     * WP kategori slug'ını engine kategoriye çevir
     */
    function aig_news_map_wp_slug_to_engine_category(string $slug): string
    {
        $slug = sanitize_title($slug);
        $map  = aig_news_get_wp_category_map();

        if (isset($map[$slug]) && aig_news_is_valid_category($map[$slug])) {
            return $map[$slug];
        }

        return aig_news_get_default_category();
    }
}

if (!function_exists('aig_news_map_wp_term_to_engine_category')) {
    /**
     * WP term nesnesinden engine kategori bul
     */
    function aig_news_map_wp_term_to_engine_category($term): string
    {
        if (!is_object($term) || empty($term->slug)) {
            return aig_news_get_default_category();
        }

        return aig_news_map_wp_slug_to_engine_category((string) $term->slug);
    }
}

if (!function_exists('aig_news_get_wp_categories_for_ui')) {
    /**
     * WP category listesini UI için oku
     *
     * Dönüş:
     * [
     *   [
     *     'term_id' => 1,
     *     'name' => 'Teknoloji',
     *     'slug' => 'teknoloji',
     *     'engine_category' => 'tech',
     *   ]
     * ]
     */
    function aig_news_get_wp_categories_for_ui(): array
    {
        if (!function_exists('get_terms')) {
            return [];
        }

        $terms = get_terms([
            'taxonomy'   => 'category',
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }

        $out = [];

        foreach ($terms as $term) {
            if (!is_object($term)) {
                continue;
            }

            $slug = isset($term->slug) ? sanitize_title((string) $term->slug) : '';
            $name = isset($term->name) ? sanitize_text_field((string) $term->name) : '';
            $id   = isset($term->term_id) ? (int) $term->term_id : 0;

            if ($slug === '' || $name === '') {
                continue;
            }

            $out[] = [
                'term_id'         => $id,
                'name'            => $name,
                'slug'            => $slug,
                'engine_category' => aig_news_map_wp_slug_to_engine_category($slug),
            ];
        }

        return $out;
    }
}

if (!function_exists('aig_news_get_engine_categories_for_ui')) {
    /**
     * UI dropdown için engine categories döndür
     *
     * Dönüş:
     * [
     *   ['value' => 'tech', 'label' => 'Tech'],
     *   ...
     * ]
     */
    function aig_news_get_engine_categories_for_ui(): array
    {
        $labels = [
            'tech'    => 'Tech',
            'finance' => 'Finance',
            'science' => 'Science',
            'ai'      => 'AI',
            'gaming'  => 'Gaming',
            'defense' => 'Defense',
            'space'   => 'Space',
        ];

        $out = [];
        foreach (aig_news_get_categories() as $category) {
            $out[] = [
                'value' => $category,
                'label' => $labels[$category] ?? ucfirst($category),
            ];
        }

        return $out;
    }
}

if (!function_exists('aig_news_sources_debug_summary')) {
    /**
     * Debug / admin ekranı için tam özet
     */
    function aig_news_sources_debug_summary(): array
    {
        $all = aig_news_get_all_sources();

        $summary = [
            'file'             => aig_news_sources_file(),
            'exists'           => file_exists(aig_news_sources_file()),
            'version'          => $all['version'] ?? '',
            'updated_at'       => $all['updated_at'] ?? '',
            'default_category' => $all['default_category'] ?? 'tech',
            'categories'       => [],
        ];

        foreach (aig_news_get_categories() as $category) {
            $summary['categories'][$category] = [
                'total_rows'      => isset($all['categories'][$category]) && is_array($all['categories'][$category])
                    ? count($all['categories'][$category])
                    : 0,
                'active_sources'  => count(aig_news_get_sources_by_category($category)),
            ];
        }

        return $summary;
    }
}