<?php
/**
 * AI Article Generator V6
 * SEO Pipeline
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_seo_pipeline_run')) {
    function aig_seo_pipeline_run(array $article, array $options = []): array
    {
        $seo = [
            'meta_title'       => '',
            'meta_description' => '',
            'faq'              => [],
            'schema'           => [],
        ];

        $seo['meta_title']       = aig_seo_pipeline_build_meta($article)['meta_title'] ?? '';
        $seo['meta_description'] = aig_seo_pipeline_build_meta($article)['meta_description'] ?? '';
        $seo['faq']              = aig_seo_pipeline_build_faq($article);
        $seo['schema']           = aig_seo_pipeline_build_schema($article, $seo['faq']);

        $article['seo'] = $seo;

        if (!isset($article['meta']) || !is_array($article['meta'])) {
            $article['meta'] = [];
        }

        $article['meta']['meta_title']       = $seo['meta_title'];
        $article['meta']['meta_description'] = $seo['meta_description'];

        return $article;
    }
}

if (!function_exists('aig_seo_pipeline_build_meta')) {
    function aig_seo_pipeline_build_meta(array $article): array
    {
        return function_exists('aig_meta_build')
            ? aig_meta_build($article)
            : [
                'meta_title'       => (string) ($article['title'] ?? ''),
                'meta_description' => '',
            ];
    }
}

if (!function_exists('aig_seo_pipeline_build_faq')) {
    function aig_seo_pipeline_build_faq(array $article): array
    {
        return function_exists('aig_faq_build')
            ? aig_faq_build($article)
            : [];
    }
}

if (!function_exists('aig_seo_pipeline_build_schema')) {
    function aig_seo_pipeline_build_schema(array $article, array $faq = []): array
    {
        if (function_exists('aig_schema_build_bundle')) {
            return aig_schema_build_bundle($article, $faq);
        }

        return [];
    }
}