<?php
/**
 * AI Article Generator V6
 * Schema Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_schema_clean_text')) {
    function aig_schema_clean_text(string $text): string
    {
        $text = wp_strip_all_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim((string) $text);
    }
}

if (!function_exists('aig_schema_pick_author_name')) {
    function aig_schema_pick_author_name(array $article = []): string
    {
        $author = trim((string) ($article['author_name'] ?? ''));

        if ($author !== '') {
            return $author;
        }

        $blog_name = trim((string) get_bloginfo('name'));
        return $blog_name !== '' ? $blog_name : 'Publisher';
    }
}

if (!function_exists('aig_schema_pick_publisher_name')) {
    function aig_schema_pick_publisher_name(array $article = []): string
    {
        $publisher = trim((string) ($article['publisher_name'] ?? ''));

        if ($publisher !== '') {
            return $publisher;
        }

        $blog_name = trim((string) get_bloginfo('name'));
        return $blog_name !== '' ? $blog_name : 'Publisher';
    }
}

if (!function_exists('aig_schema_build_article')) {
    function aig_schema_build_article(array $article, array $options = []): array
    {
        $title   = trim((string) ($article['title'] ?? 'Güncel Analiz'));
        $summary = trim((string) ($article['summary'] ?? ''));
        $content = trim((string) ($article['content'] ?? ''));

        $description = $summary !== ''
            ? aig_schema_clean_text($summary)
            : aig_schema_clean_text($content);

        if ($description === '') {
            $description = 'Güncel gelişmeleri özetleyen analiz içeriği.';
        }

        $article_body = aig_schema_clean_text($content);

        $published_at = trim((string) ($article['date_published'] ?? ''));
        $modified_at  = trim((string) ($article['date_modified'] ?? ''));

        if ($published_at === '') {
            $published_at = current_time('c');
        }

        if ($modified_at === '') {
            $modified_at = current_time('c');
        }

        $schema = [
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => $title,
            'description'   => $description,
            'datePublished' => $published_at,
            'dateModified'  => $modified_at,
            'articleBody'   => $article_body,
            'author'        => [
                '@type' => 'Organization',
                'name'  => aig_schema_pick_author_name($article),
            ],
            'publisher'     => [
                '@type' => 'Organization',
                'name'  => aig_schema_pick_publisher_name($article),
            ],
        ];

        $url = trim((string) ($article['url'] ?? ''));
        if ($url !== '') {
            $schema['mainEntityOfPage'] = [
                '@type' => 'WebPage',
                '@id'   => esc_url_raw($url),
            ];
        }

        $image = trim((string) ($article['image'] ?? ''));
        if ($image !== '') {
            $schema['image'] = [esc_url_raw($image)];
        }

        return $schema;
    }
}

if (!function_exists('aig_schema_build_faq')) {
    function aig_schema_build_faq(array $faq): array
    {
        $mainEntity = [];

        foreach ($faq as $row) {
            if (!is_array($row)) {
                continue;
            }

            $q = aig_schema_clean_text((string) ($row['question'] ?? ''));
            $a = aig_schema_clean_text((string) ($row['answer'] ?? ''));

            if ($q === '' || $a === '') {
                continue;
            }

            $mainEntity[] = [
                '@type'          => 'Question',
                'name'           => $q,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $a,
                ],
            ];
        }

        return [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
    }
}

if (!function_exists('aig_schema_build_bundle')) {
    function aig_schema_build_bundle(array $article, array $faq = []): array
    {
        $bundle = [
            'article' => aig_schema_build_article($article),
        ];

        $faq_schema = [];
        if (!empty($faq)) {
            $faq_schema = aig_schema_build_faq($faq);
        }

        if (!empty($faq_schema['mainEntity'])) {
            $bundle['faq'] = $faq_schema;
        }

        return $bundle;
    }
}