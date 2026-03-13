<?php
/**
 * AI Article Generator
 * Outline Builder + Compatibility Wrappers
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_article_outline_title_hint')) {
    function aig_article_outline_title_hint(array $context, array $input): string
    {
        $topic    = trim((string) ($input['topic'] ?? ($context['topic'] ?? '')));
        $category = trim((string) ($input['category'] ?? ($context['category'] ?? 'general')));

        if ($topic !== '') {
            return $topic . ' — Kapsamlı Analiz';
        }

        return strtoupper($category ?: 'GENEL') . ' — Güncel Analiz';
    }
}

if (!function_exists('aig_article_outline_section_templates')) {
    function aig_article_outline_section_templates(string $category = 'general'): array
    {
        $common = [
            [
                'key'     => 'giris',
                'heading' => 'Genel Çerçeve',
                'goal'    => 'Konuyu güçlü bir giriş ile aç, kısa bağlam ver.',
            ],
            [
                'key'     => 'gelismeler',
                'heading' => 'Öne Çıkan Gelişmeler',
                'goal'    => 'Ana gelişmeleri açıkla, aralarındaki ilişkiyi kur.',
            ],
            [
                'key'     => 'etkiler',
                'heading' => 'Olası Etkiler',
                'goal'    => 'Kısa ve orta vadeli etkileri değerlendir.',
            ],
            [
                'key'     => 'sonuc',
                'heading' => 'Değerlendirme',
                'goal'    => 'Toparlayıcı ve editoryal kapanış yap.',
            ],
        ];

        $category = sanitize_key($category);

        $map = [
            'tech' => [
                [
                    'key'     => 'giris',
                    'heading' => 'Teknoloji Gündeminin Çerçevesi',
                    'goal'    => 'Teknoloji tarafındaki güncel tabloyu çerçevele.',
                ],
                [
                    'key'     => 'urun_ve_platformlar',
                    'heading' => 'Ürünler, Platformlar ve Yeni Hamleler',
                    'goal'    => 'Şirket, ürün ve platform bazlı gelişmeleri açıkla.',
                ],
                [
                    'key'     => 'pazar_etkisi',
                    'heading' => 'Pazar ve Kullanıcı Etkisi',
                    'goal'    => 'Bu gelişmelerin sektör ve kullanıcı tarafındaki etkisini yaz.',
                ],
                [
                    'key'     => 'sonuc',
                    'heading' => 'Genel Değerlendirme',
                    'goal'    => 'İleriye dönük kısa değerlendirme yap.',
                ],
            ],
            'ai' => [
                [
                    'key'     => 'giris',
                    'heading' => 'Yapay Zekâ Gündeminin Özeti',
                    'goal'    => 'AI tarafındaki güncel görünümü özetle.',
                ],
                [
                    'key'     => 'modeller_ve_sirketler',
                    'heading' => 'Modeller, Şirketler ve Rekabet',
                    'goal'    => 'Model, sağlayıcı ve şirket hamlelerini açıkla.',
                ],
                [
                    'key'     => 'uygulama_ve_etki',
                    'heading' => 'Uygulama Alanları ve Etki',
                    'goal'    => 'Gerçek kullanım alanları ve etkileri değerlendir.',
                ],
                [
                    'key'     => 'sonuc',
                    'heading' => 'Kısa Sonuç',
                    'goal'    => 'Editoryal bir sonuç paragrafı üret.',
                ],
            ],
            'finance' => [
                [
                    'key'     => 'giris',
                    'heading' => 'Piyasa Çerçevesi',
                    'goal'    => 'Genel piyasa görünümünü girişte açıkla.',
                ],
                [
                    'key'     => 'veriler',
                    'heading' => 'Öne Çıkan Finansal Veriler',
                    'goal'    => 'Şirket, endeks, yatırım ve ekonomi başlıklarını işle.',
                ],
                [
                    'key'     => 'risk_ve_firsat',
                    'heading' => 'Riskler ve Fırsatlar',
                    'goal'    => 'Kısa/orta vadeli risk ve fırsatları değerlendir.',
                ],
                [
                    'key'     => 'sonuc',
                    'heading' => 'Genel Değerlendirme',
                    'goal'    => 'Toparlayıcı sonuç ver.',
                ],
            ],
        ];

        return $map[$category] ?? $common;
    }
}

if (!function_exists('aig_article_outline_build')) {
    function aig_article_outline_build(array $context, array $input = []): array
    {
        $topic      = trim((string) ($input['topic'] ?? ($context['topic'] ?? '')));
        $category   = trim((string) ($input['category'] ?? ($context['category'] ?? 'general')));
        $length     = trim((string) ($input['length'] ?? ($context['length'] ?? 'long')));
        $headlines  = is_array($context['headlines'] ?? null) ? $context['headlines'] : [];
        $key_points = is_array($context['key_points'] ?? null) ? $context['key_points'] : [];
        $entities   = is_array($context['entities'] ?? null) ? $context['entities'] : [];

        if ($topic === '') {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'missing_topic_for_outline',
                    'message' => 'Outline build requires topic.',
                ],
                'meta' => [],
            ];
        }

        $sections = aig_article_outline_section_templates($category);

        /**
         * Uzunluk uzunsa biraz daha dolu outline ver.
         */
        if ($length === 'long') {
            array_splice($sections, 2, 0, [[
                'key'     => 'arka_plan',
                'heading' => 'Arka Plan ve Bağlam',
                'goal'    => 'Konunun geçmişi ve bağlamını açıkla.',
            ]]);
        }

        $outline = [
            'title_hint' => aig_article_outline_title_hint($context, $input),
            'sections'   => $sections,
            'anchors'    => [
                'headlines'  => array_slice($headlines, 0, 6),
                'key_points' => array_slice($key_points, 0, 8),
                'entities'   => array_slice($entities, 0, 10),
            ],
        ];

        return [
            'ok'      => true,
            'outline' => $outline,
            'meta'    => [
                'section_count' => count($sections),
                'category'      => $category,
                'length'        => $length,
            ],
            'error'   => null,
        ];
    }
}

/**
 * Compatibility aliases
 * ZIP içindeki eski çağrılar için güvenli köprüler.
 */
if (!function_exists('ai_article_outline_build')) {
    function ai_article_outline_build(array $context, array $input = []): array
    {
        return aig_article_outline_build($context, $input);
    }
}

if (!function_exists('aig_outline_build')) {
    function aig_outline_build(array $context, array $input = []): array
    {
        return aig_article_outline_build($context, $input);
    }
}

/**
 * Compatibility wrapper for pipeline
 */
if (!function_exists('aig_article_outline_build')) {
    function aig_article_outline_build(array $context, array $input = [])
    {
        if (function_exists('ai_article_build_outline')) {
            return ai_article_build_outline($context, $input);
        }

        // fallback minimal outline
        $title = aig_article_outline_title_hint($context, $input);

        $sections = aig_article_outline_section_templates(
            $input['category'] ?? ($context['category'] ?? 'general')
        );

        return [
            'title' => $title,
            'sections' => $sections,
            'meta' => [
                'fallback' => true
            ]
        ];
    }
}

if (!function_exists('aig_article_outline_build')) {
    function aig_article_outline_build(array $context, array $input = []): array
    {
        if (function_exists('ai_article_build_outline')) {
            $legacy = ai_article_build_outline([
                'topic'    => (string) ($input['topic'] ?? ($context['topic'] ?? 'Gündem')),
                'category' => (string) ($input['category'] ?? ($context['category'] ?? 'general')),
                'lang'     => (string) ($input['lang'] ?? ($context['lang'] ?? 'tr')),
                'length'   => (string) ($input['length'] ?? ($context['length'] ?? 'long')),
                'context'  => $context,
                'input'    => $input,
            ]);

            if (is_array($legacy)) {
                return [
                    'ok'      => true,
                    'outline' => $legacy,
                    'meta'    => [
                        'builder' => 'legacy_ai_article_build_outline',
                    ],
                    'error'   => null,
                ];
            }
        }

        return [
            'ok' => false,
            'outline' => [],
            'meta' => [],
            'error' => [
                'code'    => 'missing_outline_builder',
                'message' => 'Outline builder function is missing.',
            ],
        ];
    }
}