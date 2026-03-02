<?php
/**
 * AI Article Generator — Internal Link Engine (lightweight)
 *
 * Amaç:
 * - Site içinden ilgili içerikleri bulup öneri listesi üretmek
 * - Pipeline çıktısına internal_links eklemek
 *
 * @since 1.3.0
 */
if (!defined('ABSPATH')) { exit; }

if (!function_exists('ai_article_internal_links')) {

    /**
     * @param string $query topic/keyword
     * @param int $limit
     * @return array list of {title, url, post_id}
     */
    function ai_article_internal_links(string $query, int $limit = 5): array {

        $query = trim($query);
        if ($query === '') return [];

        $q = new WP_Query([
            's' => $query,
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => max(1, min(10, $limit)),
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
        ]);

        $out = [];
        if ($q->have_posts()) {
            foreach ($q->posts as $p) {
                $out[] = [
                    'post_id' => (int)$p->ID,
                    'title'   => get_the_title($p),
                    'url'     => get_permalink($p),
                ];
            }
        }
        wp_reset_postdata();

        return $out;
    }
}
