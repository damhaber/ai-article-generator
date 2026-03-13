<?php
/**
 * AI Article Generator V6
 * SEO Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_seo_engine_defaults')) {
    function aig_seo_engine_defaults(): array
    {
        return [
            'with_faq'    => true,
            'with_schema' => true,
        ];
    }
}

if (!function_exists('aig_seo_engine_generate')) {
    function aig_seo_engine_generate(array $article, array $options = []): array
    {
        $options = array_merge(aig_seo_engine_defaults(), $options);

        $meta = [];
        if (function_exists('aig_meta_build')) {
            $built_meta = aig_meta_build($article, $options);
            if (is_array($built_meta)) {
                $meta = $built_meta;
            }
        }

        $faq = [];
        if (!empty($options['with_faq']) && function_exists('aig_faq_build')) {
            $built_faq = aig_faq_build($article, $options);
            if (is_array($built_faq)) {
                $faq = $built_faq;
            }
        }

        $schema = [];
        if (!empty($options['with_schema']) && function_exists('aig_schema_build_bundle')) {
            $built_schema = aig_schema_build_bundle($article, $faq);
            if (is_array($built_schema)) {
                $schema = $built_schema;
            }
        }

        return [
            'meta_title'       => trim((string) ($meta['meta_title'] ?? '')),
            'meta_description' => trim((string) ($meta['meta_description'] ?? '')),
            'faq'              => $faq,
            'schema'           => $schema,
        ];
    }
}