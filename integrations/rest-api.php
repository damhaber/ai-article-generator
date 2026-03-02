<?php
if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function(){
    register_rest_route('ai-article/v1', '/generate', [
        'methods'  => 'POST',
        'permission_callback' => function(){ return current_user_can('edit_posts'); },
        'callback' => function(WP_REST_Request $req){
            $args = [
                'prompt' => (string)$req->get_param('prompt'),
                'tone'   => (string)$req->get_param('tone') ?: 'neutral',
                'lang'   => (string)$req->get_param('lang') ?: 'tr',
                'model'  => (string)$req->get_param('model') ?: 'auto',
            ];
            return rest_ensure_response( function_exists('ai_article_generate') ? ai_article_generate($args) : ['ok'=>false,'error'=>'core yok'] );
        }
    ]);
});

// Panelde mini SEO refresh için bir admin-ajax uç
if (!has_action('wp_ajax_ai_article_last_post_seo')) {
    add_action('wp_ajax_ai_article_last_post_seo', function(){
        if (!current_user_can('edit_posts')) wp_die('⛔');
        // son kaydedilen postun skorunu okumaya çalış
        $q = new WP_Query([
            'post_type'=>'post','post_status'=>'any',
            'posts_per_page'=>1,'orderby'=>'ID','order'=>'DESC','no_found_rows'=>true
        ]);
        $out = ['score'=>0,'schema'=>''];
        if ($q->have_posts()) {
            $pid = (int)$q->posts[0]->ID;
            $out['score']  = (int)get_post_meta($pid, 'ai_seo_score', true);
            $out['schema'] = (string)get_post_meta($pid, 'ai_seo_schema', true);
        }
        wp_send_json_success($out);
    });
}

// Export JSON (panel altındaki form)
if (!has_action('admin_post_ai_article_export_json')) {
    add_action('admin_post_ai_article_export_json', function(){
        if (!current_user_can('edit_posts')) wp_die('⛔');
        check_admin_referer('ai_article_export');

        $file = AI_ARTICLE_LOG;
        $name = 'ai-article-generator-'.date('Ymd-His').'.jsonl';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'. $name .'"');
        if (file_exists($file)) readfile($file); else echo '';
        exit;
    });
}
