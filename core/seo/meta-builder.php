<?php
/**
 * AI Article Generator V6
 * Meta Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_meta_build_title')) {
    function aig_meta_build_title(array $article): string
    {
        $title    = trim((string) ($article['title'] ?? ''));
        $keyword  = trim((string) ($article['keyword'] ?? ''));
        $category = trim((string) ($article['category'] ?? ''));

        $final = $title;

        if ($keyword !== '' && stripos($title, $keyword) === false) {
            $final = $title . ' | ' . $keyword;
        }

        if ($category !== '' && stripos($final, $category) === false) {
            $final .= ' | ' . strtoupper($category);
        }

        $final = trim(preg_replace('/\s+/u', ' ', $final));

        if (mb_strlen($final) > 60) {
            $final = mb_substr($final, 0, 57) . '...';
        }

        return $final;
    }
}

if (!function_exists('aig_meta_build_description')) {
    function aig_meta_build_description(array $article): string
    {
        $summary = trim((string) ($article['summary'] ?? ''));
        $content = trim((string) ($article['content'] ?? ''));

        $plain = $summary !== '' ? $summary : wp_strip_all_tags($content);
        $plain = trim(preg_replace('/\s+/u', ' ', $plain));

        if ($plain === '') {
            $plain = 'Güncel gelişmeleri özetleyen kapsamlı analiz.';
        }

        if (mb_strlen($plain) > 160) {
            $plain = mb_substr($plain, 0, 157) . '...';
        }

        return $plain;
    }
}

if (!function_exists('aig_meta_build')) {
    function aig_meta_build(array $article, array $options = []): array
    {
        return [
            'meta_title'       => aig_meta_build_title($article),
            'meta_description' => aig_meta_build_description($article),
        ];
    }
}