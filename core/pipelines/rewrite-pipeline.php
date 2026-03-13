<?php
/**
 * AI Article Generator V6
 * Rewrite Pipeline
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_rewrite_pipeline_run')) {
    function aig_rewrite_pipeline_run(array $article, array $options = []): array
    {
        $article = aig_rewrite_pipeline_cleanup($article);
        $article = aig_rewrite_pipeline_fix_headings($article);
        $article = aig_rewrite_pipeline_improve_flow($article);
        $article = aig_rewrite_pipeline_finalize($article);

        return $article;
    }
}

if (!function_exists('aig_rewrite_pipeline_cleanup')) {
    function aig_rewrite_pipeline_cleanup(array $article): array
    {
        $sections = isset($article['sections']) && is_array($article['sections']) ? $article['sections'] : [];
        $seen = [];
        $out  = [];

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $h2      = trim((string) ($section['h2'] ?? $section['title'] ?? ''));
            $content = trim((string) ($section['content'] ?? ''));

            if ($content === '') {
                continue;
            }

            $plain = trim(preg_replace('/\s+/u', ' ', mb_strtolower(wp_strip_all_tags($content))));
            if ($plain === '') {
                continue;
            }

            $hash = md5($plain);
            if (isset($seen[$hash])) {
                continue;
            }

            $seen[$hash] = true;

            $content = preg_replace('/\{\{\s*sources\s*\}\}/iu', '', $content);
            $content = preg_replace('/<p>\s*<\/p>/iu', '', $content);
            $content = preg_replace('/\n{3,}/', "\n\n", $content);

            $out[] = [
                'h2'      => $h2,
                'content' => trim((string) $content),
            ];
        }

        $article['sections'] = $out;
        return $article;
    }
}

if (!function_exists('aig_rewrite_pipeline_fix_headings')) {
    function aig_rewrite_pipeline_fix_headings(array $article): array
    {
        $sections = isset($article['sections']) && is_array($article['sections']) ? $article['sections'] : [];

        foreach ($sections as &$section) {
            $h2 = trim((string) ($section['h2'] ?? ''));

            if ($h2 === '') {
                $section['h2'] = 'Bölüm';
                continue;
            }

            $section['h2'] = sanitize_text_field($h2);

            $content = trim((string) ($section['content'] ?? ''));
            if ($content !== '') {
                $quoted = preg_quote($section['h2'], '/');
                $content = preg_replace('/^(?:<p>\s*)?(?:<strong>\s*)?' . $quoted . '(?:\s*<\/strong>)?(?:\s*<\/p>)?\s*/iu', '', $content, 1);
                $content = preg_replace('/^<h[1-6][^>]*>\s*' . $quoted . '\s*<\/h[1-6]>\s*/iu', '', $content, 1);
                $section['content'] = trim((string) $content);
            }
        }
        unset($section);

        $article['sections'] = $sections;
        return $article;
    }
}

if (!function_exists('aig_rewrite_pipeline_improve_flow')) {
    function aig_rewrite_pipeline_improve_flow(array $article): array
    {
        $sections = isset($article['sections']) && is_array($article['sections']) ? $article['sections'] : [];

        foreach ($sections as $index => &$section) {
            $content = trim((string) ($section['content'] ?? ''));
            if ($content === '') {
                continue;
            }

            if ($index > 0 && stripos($content, '<p>') === 0) {
                $content = preg_replace('/^<p>/i', '<p>Bu çerçevede, ', $content, 1);
            }

            $section['content'] = $content;
        }
        unset($section);

        $article['sections'] = $sections;
        return $article;
    }
}

if (!function_exists('aig_rewrite_pipeline_finalize')) {
    function aig_rewrite_pipeline_finalize(array $article): array
    {
        $parts = [];
        $sections = isset($article['sections']) && is_array($article['sections']) ? $article['sections'] : [];

        foreach ($sections as $section) {
            $h2 = trim((string) ($section['h2'] ?? ''));
            $content = trim((string) ($section['content'] ?? ''));

            if ($h2 !== '') {
                $parts[] = '<h2>' . esc_html($h2) . '</h2>';
            }

            if ($content !== '') {
                $parts[] = $content;
            }
        }

        $article['content'] = implode("\n\n", $parts);
        return $article;
    }
}