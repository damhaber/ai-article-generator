<?php
/**
 * AI Article Generator — Pipeline Engine (V2)
 *
 * Amaç:
 * - Üretimi tek merkezden yönetmek (Outline → Sections → SEO → Schema → Quality → Save)
 * - JSON-first çıktı üretmek (article object)
 * - Fatal üretmemek: her zaman array döndürmek
 *
 * Not:
 * - Bu dosya, mevcut modül fonksiyonlarını kullanır:
 *   - ai_article_build_outline()
 *   - ai_article_generate()
 *   - ai_article_save_post()
 *
 * @since 1.3.0
 */

if (!defined('ABSPATH')) { exit; }

if (!function_exists('ai_article_pipeline_generate')) {

    /**
     * Pipeline ile makale üret.
     *
     * @param array $args
     *  - topic (string)  : ana konu
     *  - keyword (string): SEO anahtar kelime (opsiyonel)
     *  - lang (string)   : tr/en/...
     *  - tone (string)   : neutral / news / expert / friendly / ...
     *  - template (string): blog/news/guide/... (ai_article_templates ile uyumlu olmalı)
     *  - save (bool)     : WP'ye taslak kaydet
     *  - post_status (string): draft/publish (publish için yetki gerekir)
     *  - min_quality (int): kalite barajı (0-100)
     * @return array {ok:bool, article?:array, error?:string}
     */
    function ai_article_pipeline_generate(array $args): array {

        $topic       = trim((string)($args['topic'] ?? ''));
        $keyword     = trim((string)($args['keyword'] ?? ''));
        $lang        = (string)($args['lang'] ?? 'tr');
        $tone        = (string)($args['tone'] ?? 'neutral');
        $template_id = (string)($args['template'] ?? 'news_basic');
        $save        = !empty($args['save']);
        $post_status = (string)($args['post_status'] ?? 'draft');
        $min_quality = (int)($args['min_quality'] ?? 60);

        $auto_improve      = !empty($args['auto_improve']);
        $max_attempts      = (int)($args['max_attempts'] ?? ($auto_improve ? 3 : 1));
        $max_attempts      = max(1, min(5, $max_attempts));

        $similarity_guard  = !empty($args['similarity_guard']);
        $sim_threshold     = (float)($args['similarity_threshold'] ?? 0.80);

        if ($topic === '') {
            return ['ok' => false, 'error' => 'topic_required'];
        }

        $templates = function_exists('ai_article_templates_all') ? ai_article_templates_all() : ai_article_templates();
        $tpl = $templates[$template_id] ?? null;
        if (!$tpl || !is_array($tpl)) {
            return ['ok' => false, 'error' => 'template_not_found', 'template' => $template_id];
        }

        $attempt = 0;
        $last_article = null;
        $last_quality = null;
        $last_sim     = null;

        while ($attempt < $max_attempts) {
            $attempt++;

            // Outline
            $outline = function_exists('ai_article_build_outline')
                ? ai_article_build_outline(['topic' => $topic, 'lang' => $lang, 'keyword' => $keyword])
                : ['ok'=>false,'outline'=>[],'error'=>'outline_missing'];

            if (empty($outline['ok'])) {
                return ['ok' => false, 'error' => $outline['error'] ?? 'outline_failed'];
            }

            $sections = (array)($outline['outline']['sections'] ?? $outline['sections'] ?? []);
            if (!$sections) {
                return ['ok' => false, 'error' => 'empty_outline'];
            }

            // Generate sections
            $out = [];
            foreach ($sections as $sec) {
                $h2 = (string)($sec['h2'] ?? $sec['title'] ?? '');
                $sys = (string)($tpl['system'] ?? '');
                $usr = (string)($tpl['user'] ?? '');

                // Simple template vars
                $vars = [
                    '{{topic}}'   => $topic,
                    '{{keyword}}' => $keyword,
                    '{{date}}'    => date('Y-m-d'),
                    '{{h2}}'      => $h2,
                    '{{attempt}}' => (string)$attempt,
                ];
                $user_prompt = strtr($usr, $vars);

                // Improvement hint for retries
                if ($attempt > 1) {
                    $user_prompt .= "

EK KURAL: Önceki deneme yeterince özgün/kaliteli değildi. Daha farklı cümle yapıları, yeni örnekler ve özgün anlatım kullan. Telifsiz ve sıfırdan üret.";
                }

                $prompt = trim($sys . "

" . $user_prompt . "

BÖLÜM BAŞLIĞI: " . $h2);

                $gen = function_exists('ai_article_generate')
                    ? ai_article_generate(['prompt'=>$prompt,'tone'=>$tone,'lang'=>$lang,'model'=>'auto'])
                    : ['ok'=>false,'content'=>'','meta'=>[],'error'=>'generate_missing'];

                if (empty($gen['ok'])) {
                    return ['ok' => false, 'error' => $gen['error'] ?? 'generate_failed', 'attempt' => $attempt];
                }

                $out[] = [
                    'h2'      => $h2,
                    'content' => (string)($gen['content'] ?? ''),
                    'meta'    => (array)($gen['meta'] ?? []),
                ];
            }

            $article = [
                'title'    => $outline['outline']['title'] ?? $outline['title'] ?? $topic,
                'topic'    => $topic,
                'keyword'  => $keyword,
                'lang'     => $lang,
                'tone'     => $tone,
                'template' => $template_id,
                'sections' => $out,
                'attempt'  => $attempt,
            ];

            // Quality
            $q = function_exists('ai_article_quality_score') ? ai_article_quality_score($article) : ['score'=>0,'signals'=>[]];
            $last_quality = $q;
            $article['quality'] = $q;

            // Similarity
            $plain = '';
            foreach ($out as $s) {
                $plain .= "
" . wp_strip_all_tags((string)($s['content'] ?? ''));
            }
            $plain = trim($plain);
            if ($similarity_guard && function_exists('aig_similarity_check_and_store')) {
                $sim = aig_similarity_check_and_store($plain, $sim_threshold);
                $last_sim = $sim;
                $article['similarity'] = $sim;
            }

            $last_article = $article;

            $ok_quality = (int)($q['score'] ?? 0) >= $min_quality;
            $ok_sim     = !$similarity_guard || (is_array($last_sim) && !empty($last_sim['ok']));

            if ($ok_quality && $ok_sim) {
                break;
            }

            if (!$auto_improve) {
                break;
            }
        }

        if (!$last_article) {
            return ['ok' => false, 'error' => 'pipeline_failed'];
        }

        // Save
        if ($save) {
            if (!function_exists('ai_article_save_post')) {
                return ['ok' => true, 'article' => $last_article, 'warning' => 'save_post_missing'];
            }

            $html = '';
            foreach ((array)$last_article['sections'] as $s) {
                $h2 = esc_html((string)($s['h2'] ?? ''));
                $html .= "<h2>{$h2}</h2>
";
                $html .= wpautop((string)($s['content'] ?? '')) . "
";
            }

            $save_res = ai_article_save_post([
                'title'   => (string)($last_article['title'] ?? $topic),
                'content' => $html,
                'status'  => $post_status,
                'meta'    => [
                    '_aig_keyword'  => $keyword,
                    '_aig_quality'  => (int)($last_quality['score'] ?? 0),
                    '_aig_template' => $template_id,
                    '_aig_attempts' => (int)($last_article['attempt'] ?? 1),
                ],
            ]);

            $last_article['wp'] = $save_res;
        }

        return ['ok' => true, 'article' => $last_article];
    }
}
