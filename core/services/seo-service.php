<?php
/**
 * AI Article Generator V6
 * SEO Service
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_seo_service_defaults')) {
    function aig_seo_service_defaults(): array
    {
        return [
            'with_faq'    => true,
            'with_schema' => true,
        ];
    }
}

if (!function_exists('aig_seo_service_generate')) {
    function aig_seo_service_generate(array $article, array $options = []): array
    {
        $options = array_merge(aig_seo_service_defaults(), $options);

        if (function_exists('aig_seo_engine_generate')) {
            return aig_seo_engine_generate($article, $options);
        }

        return [
            'meta_title'       => (string) ($article['title'] ?? ''),
            'meta_description' => '',
            'faq'              => [],
            'schema'           => [],
        ];
    }
}

if (!function_exists('aig_seo_service_attach_to_article')) {
    function aig_seo_service_attach_to_article(array $article, array $seo): array
    {
        $article['seo'] = $seo;

        if (!isset($article['meta']) || !is_array($article['meta'])) {
            $article['meta'] = [];
        }

        $article['meta']['meta_title']       = (string) ($seo['meta_title'] ?? '');
        $article['meta']['meta_description'] = (string) ($seo['meta_description'] ?? '');

        return $article;
    }
}