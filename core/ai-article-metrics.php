<?php
if (!defined('ABSPATH')) { exit; }

if (!function_exists('ai_article_metrics_inc')) {
    function ai_article_metrics_inc(string $key): void {
        $opt = 'ai_article_metrics';
        $arr = get_option($opt, []);
        if (!is_array($arr)) $arr = [];
        $arr[$key] = isset($arr[$key]) ? ((int)$arr[$key] + 1) : 1;
        update_option($opt, $arr, false);
    }
}
