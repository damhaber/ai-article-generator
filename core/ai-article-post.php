<?php
/**
 * AI Article Generator — Post Writer
 * - WP yazı kaydı (taslak)
 * - Meta alanları ekleme / güncelleme
 * - AJAX uç: ai_article_save_post
 * - Fatal üretmez; her zaman JSON döner.
 */

if (!defined('ABSPATH')) { exit; }

if (!function_exists('ai_article_log')) {
    // Group 1’de tanımlı; yoksa küçük fallback:
    function ai_article_log(string $op, $data = null, string $level = 'info'): void {
        $row  = ['ts'=>gmdate('c'),'level'=>$level,'op'=>$op];
        if ($data !== null) $row['data'] = $data;
        $line = json_encode($row, JSON_UNESCAPED_UNICODE);
        $file = wp_normalize_path(dirname(__DIR__, 1).'/logs/ai-article-generator.log');
        if (!is_dir(dirname($file))) @wp_mkdir_p(dirname($file));
        @file_put_contents($file, $line.PHP_EOL, FILE_APPEND|LOCK_EX);
        if (defined('AISEO_LOG_FILE')) @file_put_contents(AISEO_LOG_FILE, $line.PHP_EOL, FILE_APPEND|LOCK_EX);
    }
}

/**
 * AI Meta Hub için basit okuma süresi tahmini
 */
if (!function_exists('ai_article_estimate_reading_time')) {
    function ai_article_estimate_reading_time(string $html): int {
        $text  = wp_strip_all_tags($html);
        $words = str_word_count($text);
        if ($words <= 0) {
            return 1;
        }
        return max(1, (int) ceil($words / 200)); // 200 kelime ≈ 1 dk
    }
}

/**
 * Güvenli başlık çıkarımı: <h1>/<h2> → yoksa ilk 8 kelime
 */
if (!function_exists('ai_article_guess_title')) {
    function ai_article_guess_title(string $html, string $fallback = ''): string {
        if (preg_match('#<h1[^>]*>(.*?)</h1>#is', $html, $m) && trim($m[1])) {
            return wp_strip_all_tags($m[1]);
        }
        if (preg_match('#<h2[^>]*>(.*?)</h2>#is', $html, $m) && trim($m[1])) {
            return wp_strip_all_tags($m[1]);
        }
        $txt = wp_strip_all_tags($html);
        $words = preg_split('/\s+/', trim($txt));
        $title = implode(' ', array_slice($words, 0, 8));
        if (!$title && $fallback) $title = wp_strip_all_tags($fallback);
        return $title ?: __('AI Generated Article', 'masal-panel');
    }
}

/**
 * İçeriği WP için güvenle süz: wp_kses_post
 */
if (!function_exists('ai_article_sanitize_html')) {
    function ai_article_sanitize_html(string $html): string {
        return wp_kses_post($html);
    }
}

/**
 * Ana kayıtçı: taslak oluşturur/günceller
 * @param array $payload {content,title,category_id,tags[],status,meta[]}
 * @return array {ok:bool, post_id:int, meta:array, error?:string}
 */
if (!function_exists('ai_article_save_post')) {
    function ai_article_save_post(array $payload): array {
        try {
            if (!current_user_can('edit_posts')) {
                return ['ok'=>false,'post_id'=>0,'meta'=>[],'error'=>'İzin yok'];
            }

            $content   = (string)($payload['content'] ?? '');
            $titleRaw  = (string)($payload['title'] ?? '');
            $status    = (string)($payload['status'] ?? 'draft');
            $category  = (int)($payload['category_id'] ?? 0);
            $tags      = is_array($payload['tags'] ?? null) ? array_map('sanitize_text_field', $payload['tags']) : [];
            $meta      = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
            $post_id   = (int)($payload['post_id'] ?? 0);

            if ($content === '') {
                return ['ok'=>false,'post_id'=>0,'meta'=>[],'error'=>'Boş içerik'];
            }

            $clean   = ai_article_sanitize_html($content);
            $title   = $titleRaw ?: ai_article_guess_title($clean, (string)($meta['ai_prompt'] ?? ''));

            $postarr = [
                'post_title'   => $title,
                'post_content' => $clean,
                'post_status'  => in_array($status, ['draft','pending','publish'], true) ? $status : 'draft',
                'post_type'    => 'post',
                'post_author'  => get_current_user_id() ?: 1,
            ];

            if ($post_id > 0) {
                $postarr['ID'] = $post_id;
                $post_id = wp_update_post($postarr, true);
            } else {
                $post_id = wp_insert_post($postarr, true);
            }

            if (is_wp_error($post_id)) {
                $msg = $post_id->get_error_message();
                ai_article_log('post_save_error', $msg, 'error');
                return ['ok'=>false,'post_id'=>0,'meta'=>[],'error'=>$msg];
            }

            // Kategori / etiketler
            if ($category > 0) {
                wp_set_post_categories($post_id, [$category], false);
            }
            if ($tags) {
                wp_set_post_tags($post_id, $tags, false);
            }

            // Meta yaz
            foreach ($meta as $k=>$v) {
                $key = sanitize_key($k);
                if ($key === '') continue;
                if (is_array($v) || is_object($v)) {
                    update_post_meta($post_id, $key, wp_json_encode($v, JSON_UNESCAPED_UNICODE));
                } else {
                    update_post_meta($post_id, $key, sanitize_text_field((string)$v));
                }
            }

            // 🔱 AI META HUB — Otomatik Senkron
            if (function_exists('tce_ai_meta_hub_save')) {
                $hub_meta = [
                    'headline'     => $title,
                    'seo_title'    => $meta['seo_title']    ?? $title,
                    'seo_desc'     => $meta['seo_desc']     ?? '',
                    'focus_key'    => $meta['focus_key']    ?? '',
                    'tone'         => $meta['tone']         ?? ($meta['ai_tone'] ?? ''),
                    'reading_time' => isset($meta['reading_time'])
                        ? (int) $meta['reading_time']
                        : ai_article_estimate_reading_time($clean),
                ];

                // Boş değerleri at
                $hub_meta_filtered = [];
                foreach ($hub_meta as $k_hub => $v_hub) {
                    if ($v_hub !== null && $v_hub !== '') {
                        $hub_meta_filtered[$k_hub] = $v_hub;
                    }
                }

                if (!empty($hub_meta_filtered)) {
                    tce_ai_meta_hub_save((int) $post_id, $hub_meta_filtered);
                }
            }

            ai_article_log('post_saved', ['post_id'=>$post_id,'status'=>get_post_status($post_id),'title'=>$title]);

            /**
             * SEO analiz tetikle (integrations/ai-seo-hook.php dinler)
             */
            do_action('ai_article/post_saved', $post_id, $meta);

            // Görsel ekleme (opsiyonel) — post_id burada kesin hazır.
            // ai-article-media.php içinde tanımlı olmalı. Dosya yoksa/fonksiyon yoksa fatal olmaz.
            $keywords = array_filter(array_unique(array_merge([$title], (array) $tags)));

            $media_file = __DIR__ . '/ai-article-media.php';
            if (!function_exists('ai_article_attach_images') && file_exists($media_file)) {
                require_once $media_file;
            }

            if (function_exists('ai_article_attach_images') && !empty($keywords)) {
                try {
                    ai_article_attach_images((int) $post_id, $keywords);
                } catch (Throwable $e_media) {
                    ai_article_log('attach_images_error', $e_media->getMessage(), 'warn');
                }
            }

            return ['ok'=>true,'post_id'=>$post_id,'meta'=>$meta];
        } catch (Throwable $e) {
            ai_article_log('post_save_exception', $e->getMessage(), 'error');
            return ['ok'=>false,'post_id'=>0,'meta'=>[],'error'=>$e->getMessage()];
        }
    }
}

/* ---------------------- AJAX: ai_article_save_post ---------------------- */
if (!has_action('wp_ajax_ai_article_save_post')) {
    add_action('wp_ajax_ai_article_save_post', function () {
        if (!current_user_can('edit_posts')) wp_die('⛔');
        check_ajax_referer('ai_article_nonce', '_ajax_nonce');

        $payload = [
            'post_id'     => (int)($_POST['post_id'] ?? 0),
            'title'       => wp_unslash($_POST['title'] ?? ''),
            'content'     => wp_unslash($_POST['content'] ?? ''),
            'status'      => sanitize_text_field($_POST['status'] ?? 'draft'),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'tags'        => is_array($_POST['tags'] ?? null) ? array_map('sanitize_text_field', $_POST['tags']) : [],
            'meta'        => isset($_POST['meta']) ? (is_array($_POST['meta']) ? $_POST['meta'] : []) : [],
        ];

        $res = ai_article_save_post($payload);
        wp_send_json_success($res);
    });
}
