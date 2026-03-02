<?php
if (!defined('ABSPATH')) { exit; }
add_action('init', function(){
    if (!wp_next_scheduled('ai_article_queue_hourly')) {
        wp_schedule_event(time()+60, 'hourly', 'ai_article_queue_hourly');
    }
});
add_action('ai_article_queue_hourly', 'ai_article_queue_run');
function ai_article_queue_run() {
    $enabled  = (bool)get_option('ai_article_queue_enabled', false);
    if (!$enabled) return;
    $count    = max(1, (int)get_option('ai_article_queue_count', 1));
    $tpl      = (string)get_option('ai_article_queue_template', 'news_basic');
    $category = (int)get_option('ai_article_queue_category', 0);
    $tags_raw = (string)get_option('ai_article_queue_tags', '');
    $tags     = array_filter(array_map('trim', explode(',', $tags_raw)));
    if (!function_exists('ai_article_templates')) { @include __DIR__.'/ai-article-templates.php'; }
    $templates = function_exists('ai_article_templates') ? ai_article_templates() : [];
    $tpl_def   = $templates[$tpl] ?? null;
    if (!$tpl_def) return;
    for ($i=0; $i<$count; $i++) {
        $topic = apply_filters('ai_article/queue_topic', 'Günün gündemi', $i);
        $prompt = strtr($tpl_def['user'], [
            '{{topic}}'=>'Gündem: ' . $topic,
            '{{sources}}'=>site_url(),
            '{{date}}'=>date('Y-m-d'),
            '{{counter}}'=>'—','{{reqs}}'=>'','{{audience}}'=>'genel','{{product}}'=>'','{{rivals}}'=>'','{{criteria}}'=>'trend',
        ]);
        $args = ['prompt'=>$prompt,'tone'=>'neutral','lang'=>get_option('WPLANG')?:'tr','model'=>get_option('ai_article_provider','auto'),
                 'category_id'=>$category,'tags'=>$tags];
        if (function_exists('ai_article_generate') && function_exists('ai_article_save_post')) {
            $gen = ai_article_generate($args);
            if (is_array($gen) && !empty($gen['ok']) && !empty($gen['content'])) {
                ai_article_save_post(['title'=>'','content'=>$gen['content'],'status'=>'draft','category_id'=>$category,'tags'=>$tags,'meta'=>(array)($gen['meta']??[])]);
            }
        }
    }
}



// Saatlik cron
add_action('init', function(){
    if (!wp_next_scheduled('ai_article_queue_hourly')) {
        wp_schedule_event(time()+60, 'hourly', 'ai_article_queue_hourly');
    }
});
add_action('ai_article_queue_hourly', 'ai_article_queue_run');

if (!function_exists('ai_article_queue_log')) {
    function ai_article_queue_log(string $event, array $data = []) {
        $upload = wp_upload_dir();
        $dir = trailingslashit($upload['basedir']) . 'ai-article-logs';
        if (!file_exists($dir)) { wp_mkdir_p($dir); }
        $file = trailingslashit($dir) . 'ai-article-queue.log';
        $row = wp_json_encode(['t'=>gmdate('c'),'event'=>$event,'data'=>$data], JSON_UNESCAPED_UNICODE);
        file_put_contents($file, $row . PHP_EOL, FILE_APPEND);
    }
}
// Tick log (istatistik için)
add_action('ai_article_queue_hourly', function(){
    ai_article_queue_log('hourly_tick', ['count'=>(int)get_option('ai_article_queue_count',1)]);
}, 99);
