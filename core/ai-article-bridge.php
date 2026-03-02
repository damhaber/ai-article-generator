<?php
/**
 * AI Article Generator — Bridge (V4 Stable)
 * - Admin AJAX: ai_article_generate (single prompt)
 * - Admin AJAX: ai_article_rewrite  (rewrite studio)
 * - Universal hooks (optional): ai_article/llm_generate filter is the LLM gateway.
 */
if (!defined('ABSPATH')) { exit; }

/**
 * AJAX: Single prompt generation (HTML)
 * Expects: prompt, tone, lang, model
 */
if (!has_action('wp_ajax_ai_article_generate')) {
    add_action('wp_ajax_ai_article_generate', function () {
        if (!current_user_can('edit_posts')) wp_die('⛔');
        check_ajax_referer('ai_article_nonce','_ajax_nonce');

        $args = [
            'prompt' => wp_unslash($_POST['prompt'] ?? ''),
            'tone'   => sanitize_text_field($_POST['tone'] ?? 'neutral'),
            'lang'   => sanitize_text_field($_POST['lang'] ?? 'tr'),
            'model'  => sanitize_text_field($_POST['model'] ?? 'auto'),
        ];

        if (!function_exists('ai_article_generate')) {
            wp_send_json_success(['ok'=>false,'content'=>'','meta'=>[],'error'=>'core_missing']);
        }

        $res = ai_article_generate($args);
        wp_send_json_success($res);
    });
}

/**
 * AJAX: Rewrite (input text -> output html)
 * Expects: text, instruction, tone, lang, model
 */
if (!has_action('wp_ajax_ai_article_rewrite')) {
    add_action('wp_ajax_ai_article_rewrite', function () {
        if (!current_user_can('edit_posts')) wp_die('⛔');
        check_ajax_referer('ai_article_nonce','_ajax_nonce');

        $text = (string)wp_unslash($_POST['text'] ?? '');
        $instruction = sanitize_text_field($_POST['instruction'] ?? 'rewrite');

        $tone   = sanitize_text_field($_POST['tone'] ?? 'neutral');
        $lang   = sanitize_text_field($_POST['lang'] ?? 'tr');
        $model  = sanitize_text_field($_POST['model'] ?? 'auto');

        if ($text === '') {
            wp_send_json_success(['ok'=>false,'content'=>'','meta'=>[],'error'=>'empty_text']);
        }

        $prompt =
            "GÖREV: Aşağıdaki metni talimata göre yeniden yaz.\n"
          . "TALİMAT: {$instruction}\n"
          . "KURALLAR: Telifsiz / sıfırdan yeniden üret. Kopyalama yapma. Dili koru (lang={$lang}). Çıktı HTML olsun (p, h2, ul vb).\n\n"
          . "METİN:\n" . $text;

        $res = function_exists('ai_article_generate')
            ? ai_article_generate(['prompt'=>$prompt,'tone'=>$tone,'lang'=>$lang,'model'=>$model])
            : ['ok'=>false,'content'=>'','meta'=>[],'error'=>'core_missing'];

        wp_send_json_success($res);
    });
}
