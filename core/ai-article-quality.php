<?php
/**
 * AI Article Generator — Quality Engine (Deterministic)
 *
 * Amaç:
 * - Üretim sonrası kalite skorunu hesaplamak (0-100)
 * - Skoru açıklayan sinyalleri döndürmek
 *
 * Not: Bu dosya LLM'e bağlı değildir; deterministiktir.
 *
 * @since 1.3.0
 */
if (!defined('ABSPATH')) { exit; }

if (!function_exists('ai_article_quality_score')) {

    /**
     * @param array $article pipeline output
     * @return array {score:int, signals:array}
     */
    function ai_article_quality_score(array $article): array {

        $title   = (string)($article['title'] ?? '');
        $keyword = (string)($article['keyword'] ?? '');
        $meta_desc = (string)($article['meta']['meta_description'] ?? '');
        $sections = (array)($article['sections'] ?? []);

        $all_text = [];
        foreach ($sections as $s) {
            $all_text[] = wp_strip_all_tags((string)($s['content'] ?? ''));
        }
        $plain = trim(implode("\n", $all_text));
        $words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY);
        $word_count = is_array($words) ? count($words) : 0;

        $signals = [
            'word_count' => $word_count,
            'sections'   => count($sections),
            'keyword_density' => null,
            'meta_description_len' => mb_strlen($meta_desc),
            'repetition_ratio' => null,
        ];

        // Base score by length
        $score = 40;
        if ($word_count >= 500)  $score = 60;
        if ($word_count >= 800)  $score = 70;
        if ($word_count >= 1100) $score = 78;
        if ($word_count >= 1500) $score = 84;
        if ($word_count >= 2000) $score = 90;

        // Title sanity
        $tlen = mb_strlen(trim($title));
        if ($tlen < 20) $score -= 5;
        if ($tlen > 80) $score -= 3;

        // Meta description length (ideal: 120-160)
        $md = $signals['meta_description_len'];
        if ($md >= 120 && $md <= 170) $score += 4;
        else if ($md < 80) $score -= 4;

        // Keyword density
        if ($keyword !== '' && $plain !== '') {
            $kw = mb_strtolower($keyword);
            $pl = mb_strtolower($plain);
            $occ = substr_count($pl, $kw);
            $density = $word_count > 0 ? ($occ / $word_count) * 100 : 0;
            $signals['keyword_density'] = round($density, 2);

            // Sweet spot: ~0.3% - 1.8% (dile göre değişir ama güvenli aralık)
            if ($density >= 0.3 && $density <= 1.8) $score += 6;
            else if ($density < 0.15) $score -= 4;
            else if ($density > 2.5) $score -= 8;
        }

        // Repetition heuristic: 4-gram frequency ratio
        $rep_ratio = 0.0;
        if ($plain !== '') {
            $tokens = preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', mb_strtolower($plain)), -1, PREG_SPLIT_NO_EMPTY);
            $grams = [];
            if (is_array($tokens) && count($tokens) >= 12) {
                for ($i=0; $i < count($tokens)-3; $i++) {
                    $g = $tokens[$i].' '.$tokens[$i+1].' '.$tokens[$i+2].' '.$tokens[$i+3];
                    $grams[$g] = ($grams[$g] ?? 0) + 1;
                }
                arsort($grams);
                $top = reset($grams);
                $total = array_sum($grams);
                $rep_ratio = $total > 0 ? ($top / $total) : 0.0;
            }
        }
        $signals['repetition_ratio'] = round($rep_ratio, 4);

        // Penalize heavy repetition
        if ($rep_ratio > 0.03) $score -= 6;
        if ($rep_ratio > 0.05) $score -= 10;

        // Section coverage
        $sec_count = count($sections);
        if ($sec_count >= 5) $score += 4;
        if ($sec_count <= 2) $score -= 8;

        // Clamp
        if ($score < 0) $score = 0;
        if ($score > 100) $score = 100;

        return [
            'score' => (int)round($score),
            'signals' => $signals,
        ];
    }
}
