<?php
/**
 * AI Article Generator V6
 * Prompt Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_prompt_engine_length_words')) {
    function aig_prompt_engine_length_words(string $length): int
    {
        $length = sanitize_key($length);

        $map = [
            'short'  => 700,
            'medium' => 1100,
            'long'   => 1600,
        ];

        return $map[$length] ?? 1600;
    }
}

if (!function_exists('aig_prompt_engine_safe_json')) {
    function aig_prompt_engine_safe_json($value): string
    {
        $json = wp_json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (!is_string($json) || $json === '') {
            return '[]';
        }

        return $json;
    }
}

if (!function_exists('aig_prompt_engine_normalize_lang_label')) {
    function aig_prompt_engine_normalize_lang_label(string $lang): string
    {
        $lang = trim(mb_strtolower($lang));

        if ($lang === 'tr') {
            return 'Türkçe';
        }

        if ($lang === 'en') {
            return 'English';
        }

        return $lang !== '' ? $lang : 'Türkçe';
    }
}

if (!function_exists('aig_prompt_engine_build_article_generate')) {
    function aig_prompt_engine_build_article_generate(array $payload): array
    {
        $topic    = trim((string) ($payload['topic'] ?? ''));
        $category = trim((string) ($payload['category'] ?? 'general'));
        $lang     = trim((string) ($payload['lang'] ?? 'tr'));
        $tone     = trim((string) ($payload['tone'] ?? 'professional'));
        $length   = trim((string) ($payload['length'] ?? 'long'));
        $template = trim((string) ($payload['template'] ?? 'news_analysis'));
        $context  = is_array($payload['context'] ?? null) ? $payload['context'] : [];
        $outline  = is_array($payload['outline'] ?? null) ? $payload['outline'] : [];

        $target_words = (int) ($context['target_words'] ?? aig_prompt_engine_length_words($length));
        if ($target_words <= 0) {
            $target_words = aig_prompt_engine_length_words($length);
        }

        $lang_label = aig_prompt_engine_normalize_lang_label($lang);

        $system = implode("\n", [
            'Sen deneyimli bir dijital yayın editörüsün.',
            'Görevin, verilen fact pack ve outline temelinde güçlü, dolu, editoryal bir haber-analiz makalesi yazmaktır.',
            'Çıktı dili kesinlikle ' . $lang_label . ' olmalıdır.',
            'Metin, doğal insan yazımı gibi akmalı; kısa ve çıplak maddeler halinde kalmamalıdır.',
            'Her bölüm gerçekten paragraf içeriği taşımalı; sadece başlık listesi üretme.',
            'Doğrulanmamış bilgi uydurma, kaynakta olmayan iddia ekleme.',
            'Aşırı tekrar, boş genel geçer cümle ve anlamsız uzatma yapma.',
            'Makale HTML uyumlu yazılmalı: <h2>, <p>, gerekirse <ul><li> kullanılabilir.',
            'İlk satıra yalnızca başlık yaz; devamında makale gövdesi gelsin.',
            'Makalenin sonunda kısa bir değerlendirme/sonuç bölümü oluştur.',
        ]);

        $outline_sections = is_array($outline['sections'] ?? null) ? $outline['sections'] : [];
        $headlines        = is_array($context['headlines'] ?? null) ? $context['headlines'] : [];
        $key_points       = is_array($context['key_points'] ?? null) ? $context['key_points'] : [];
        $entities         = is_array($context['entities'] ?? null) ? $context['entities'] : [];

        $user = implode("\n", [
            'KONU: ' . $topic,
            'KATEGORİ: ' . $category,
            'ŞABLON: ' . $template,
            'DİL: ' . $lang_label,
            'TON: ' . $tone,
            'HEDEF KELİME: yaklaşık ' . $target_words,
            'KISA EDİTÖR NOTU: ' . (string) ($context['brief'] ?? ''),
            'ANA NOKTALAR: ' . implode(' | ', $key_points),
            'ÖNE ÇIKAN VARLIKLAR: ' . implode(', ', $entities),
            'OUTLINE JSON: ' . aig_prompt_engine_safe_json($outline_sections),
            'FACT PACK JSON: ' . aig_prompt_engine_safe_json($headlines),
            '',
            'YAZIM TALİMATI:',
            '1) Güçlü bir giriş ile başla.',
            '2) Her alt başlık altında gerçek analiz ve açıklama yap.',
            '3) Gelişmeleri sadece sıralama; bağlantı kur, etkiyi açıkla.',
            '4) İçerik çıplak kalmasın; bölüm gövdeleri en az birkaç paragraf içersin.',
            '5) Son bölümde ileriye dönük kısa değerlendirme ver.',
            '6) Kaynak başlıklarıyla tutarlı kal, varsayım üretme.',
        ]);

        return [
            'ok' => true,
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => $system,
                ],
                [
                    'role'    => 'user',
                    'content' => $user,
                ],
            ],
            'meta' => [
                'task' => 'article_generate',
                'target_words' => $target_words,
            ],
            'error' => null,
        ];
    }
}

if (!function_exists('aig_prompt_engine_build_rewrite')) {
    function aig_prompt_engine_build_rewrite(array $payload): array
    {
        $content       = trim((string) ($payload['content'] ?? ''));
        $instruction   = trim((string) ($payload['instruction'] ?? ''));
        $lang          = trim((string) ($payload['lang'] ?? 'tr'));
        $tone          = trim((string) ($payload['tone'] ?? 'professional'));
        $mode          = trim((string) ($payload['mode'] ?? 'rewrite'));
        $target_length = trim((string) ($payload['target_length'] ?? 'long'));

        if ($content === '') {
            return [
                'ok' => false,
                'messages' => [],
                'error' => [
                    'code' => 'empty_rewrite_content',
                    'message' => 'Rewrite content is empty.',
                ],
            ];
        }

        $lang_label = aig_prompt_engine_normalize_lang_label($lang);

        $system = implode("\n", [
            'Sen deneyimli bir editörsün.',
            'Verilen metni aynı dilde, daha akıcı, daha güçlü ve daha yayınlanabilir hale getir.',
            'Anlamı bozma, uydurma bilgi ekleme.',
            'Gerekiyorsa tekrarları temizle, akışı güçlendir, paragraf yapısını düzelt.',
            'Çıktı dili kesinlikle ' . $lang_label . ' olmalıdır.',
            'HTML yapısı korunabilir; temiz ve okunaklı içerik üret.',
        ]);

        $user = implode("\n", [
            'MOD: ' . $mode,
            'TON: ' . $tone,
            'HEDEF UZUNLUK: ' . $target_length,
            'EK TALİMAT: ' . $instruction,
            '',
            'METİN:',
            $content,
        ]);

        return [
            'ok' => true,
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => $system,
                ],
                [
                    'role'    => 'user',
                    'content' => $user,
                ],
            ],
            'meta' => [
                'task' => 'rewrite',
            ],
            'error' => null,
        ];
    }
}

if (!function_exists('aig_prompt_engine_build')) {
    function aig_prompt_engine_build(array $payload): array
    {
        $task = sanitize_key((string) ($payload['task'] ?? 'article_generate'));

        switch ($task) {
            case 'rewrite':
                return aig_prompt_engine_build_rewrite($payload);

            case 'article_generate':
            default:
                return aig_prompt_engine_build_article_generate($payload);
        }
    }
}