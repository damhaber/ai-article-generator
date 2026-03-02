<?php
if (!defined('ABSPATH')) { exit; }
/**
 * AI Article Generator — Citation/Sources
 * AI SEO Engine’in SERP sürücüsü varsa konuya göre kaynak listesi ekler.
 */
add_action('ai_article/post_saved', function($post_id, $meta){
    if (!current_user_can('edit_posts')) return;
    $topic = get_post_field('post_title', $post_id);
    if (!$topic) { $topic = wp_strip_all_tags(get_post_field('post_content', $post_id)); }
    $sources = [];

    // AI SEO Engine varsa
    if (function_exists('ai_serp_sources')) {
        $sources = (array) ai_serp_sources($topic);
    }

    if ($sources) {
        $list = array_map('sanitize_text_field', $sources);
        $html = '<p><strong>Kaynaklar:</strong> ' . esc_html(implode(', ', $list)) . '</p>';
        $content = get_post_field('post_content', $post_id) . "\n\n" . $html;
        wp_update_post(['ID' => $post_id, 'post_content' => $content]);
        update_post_meta($post_id, 'ai_article_sources', $list);
    }
}, 20, 2);
