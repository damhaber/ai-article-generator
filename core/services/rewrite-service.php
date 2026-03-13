<?php
/**
 * AI Article Generator V6
 * Rewrite Service
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_rewrite_service_defaults')) {
    function aig_rewrite_service_defaults(): array
    {
        return [
            'mode'          => 'rewrite',
            'tone'          => 'professional',
            'lang'          => 'tr',
            'instruction'   => '',
            'preserve_html' => true,
            'target_length' => 'long',
            'provider'      => null,
            'model'         => null,
        ];
    }
}

if (!function_exists('aig_rewrite_service_compile_sections')) {
    function aig_rewrite_service_compile_sections(array $sections): string
    {
        $parts = [];

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $h2      = trim((string) ($section['h2'] ?? $section['heading'] ?? $section['title'] ?? ''));
            $content = trim((string) ($section['content'] ?? ''));

            if ($h2 !== '') {
                $parts[] = '<h2>' . esc_html($h2) . '</h2>';
            }

            if ($content !== '') {
                $parts[] = wp_kses_post($content);
            }
        }

        return implode("\n\n", $parts);
    }
}

if (!function_exists('aig_rewrite_service_build_sections_from_content')) {
    function aig_rewrite_service_build_sections_from_content(string $content, string $title = 'Metin'): array
    {
        $content = trim($content);

        if ($content === '') {
            return [];
        }

        if (preg_match_all('/<h2[^>]*>(.*?)<\/h2>(.*?)(?=<h2[^>]*>|$)/isu', $content, $matches, PREG_SET_ORDER)) {
            $sections = [];

            foreach ($matches as $match) {
                $heading = trim(wp_strip_all_tags((string) ($match[1] ?? '')));
                $body    = trim((string) ($match[2] ?? ''));

                $sections[] = [
                    'heading' => $heading,
                    'content' => $body,
                ];
            }

            if (!empty($sections)) {
                return $sections;
            }
        }

        return [
            [
                'heading' => $title,
                'content' => $content,
            ],
        ];
    }
}

if (!function_exists('aig_rewrite_service_make_html')) {
    function aig_rewrite_service_make_html(string $content): string
    {
        $content = trim($content);

        if ($content === '') {
            return '';
        }

        if (strpos($content, '<p') !== false || strpos($content, '<h2') !== false) {
            return wp_kses_post($content);
        }

        return function_exists('wpautop')
            ? wpautop(esc_html($content))
            : nl2br(esc_html($content));
    }
}

if (!function_exists('aig_rewrite_service_ensure_article_shape')) {
    function aig_rewrite_service_ensure_article_shape(array $article): array
    {
        if (!isset($article['title']) || trim((string) $article['title']) === '') {
            $article['title'] = 'Rewrite';
        }

        if (!isset($article['html']) || !is_string($article['html'])) {
            $article['html'] = '';
        }

        if (!isset($article['content']) || !is_string($article['content'])) {
            $article['content'] = '';
        }

        if ($article['html'] === '' && $article['content'] !== '') {
            $article['html'] = aig_rewrite_service_make_html($article['content']);
        }

        if ($article['content'] === '' && $article['html'] !== '') {
            $article['content'] = trim(wp_strip_all_tags($article['html']));
        }

        if (empty($article['sections']) || !is_array($article['sections'])) {
            $seed = $article['html'] !== '' ? $article['html'] : $article['content'];
            $article['sections'] = aig_rewrite_service_build_sections_from_content($seed, 'Metin');
        }

        if (empty($article['summary']) && !empty($article['content'])) {
            $plain = trim(wp_strip_all_tags((string) $article['content']));
            $article['summary'] = mb_substr($plain, 0, 280);
        }

        return $article;
    }
}

if (!function_exists('aig_rewrite_service_finalize')) {
    function aig_rewrite_service_finalize(array $article): array
    {
        $article = aig_rewrite_service_ensure_article_shape($article);

        if (function_exists('aig_rewrite_pipeline_finalize')) {
            $article = aig_rewrite_pipeline_finalize($article);
            $article = aig_rewrite_service_ensure_article_shape($article);
        } elseif (!empty($article['sections']) && is_array($article['sections'])) {
            $article['html'] = aig_rewrite_service_compile_sections($article['sections']);
            $article['content'] = trim(wp_strip_all_tags($article['html']));
        }

        return $article;
    }
}

if (!function_exists('aig_rewrite_service_apply_basic_cleanup')) {
    function aig_rewrite_service_apply_basic_cleanup(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $html = preg_replace('/\n{3,}/', "\n\n", $html);
        $html = preg_replace('/[ \t]{2,}/u', ' ', $html);

        return trim((string) $html);
    }
}

if (!function_exists('aig_rewrite_service_change_tone')) {
    function aig_rewrite_service_change_tone(array $article, string $tone): array
    {
        $article['tone'] = sanitize_text_field($tone);
        return $article;
    }
}

if (!function_exists('aig_rewrite_service_expand')) {
    function aig_rewrite_service_expand(array $article, array $options = []): array
    {
        $article = aig_rewrite_service_ensure_article_shape($article);
        $sections = (array) ($article['sections'] ?? []);

        foreach ($sections as &$section) {
            $content = trim((string) ($section['content'] ?? ''));

            if ($content !== '') {
                $content .= "\n<p>Bu gelişmenin kısa vadeli etkileri, sektör dinamikleri ve kullanıcı davranışları açısından dikkatle izlenmeye devam ediyor.</p>";
                $content .= "\n<p>Önümüzdeki dönemde bu başlığın teknik, ekonomik ve stratejik sonuçlarının daha görünür hale gelmesi beklenebilir.</p>";
                $section['content'] = $content;
            }
        }
        unset($section);

        $article['sections'] = $sections;
        $article['html'] = aig_rewrite_service_compile_sections($sections);
        $article['content'] = trim(wp_strip_all_tags($article['html']));

        return aig_rewrite_service_finalize($article);
    }
}

if (!function_exists('aig_rewrite_service_shorten')) {
    function aig_rewrite_service_shorten(array $article, array $options = []): array
    {
        $article = aig_rewrite_service_ensure_article_shape($article);
        $sections = (array) ($article['sections'] ?? []);

        foreach ($sections as &$section) {
            $content = trim((string) ($section['content'] ?? ''));
            $plain   = trim(wp_strip_all_tags($content));

            if ($plain === '') {
                continue;
            }

            if (mb_strlen($plain) > 320) {
                $plain = mb_substr($plain, 0, 317) . '...';
            }

            $section['content'] = '<p>' . esc_html($plain) . '</p>';
        }
        unset($section);

        $article['sections'] = $sections;
        $article['html'] = aig_rewrite_service_compile_sections($sections);
        $article['content'] = trim(wp_strip_all_tags($article['html']));

        return aig_rewrite_service_finalize($article);
    }
}

if (!function_exists('aig_rewrite_service_try_llm')) {
    function aig_rewrite_service_try_llm(array $article, array $options = []): array
    {
        if (!function_exists('aig_prompt_engine_build')) {
            return ['ok' => false, 'error' => 'missing_prompt_engine'];
        }

        $payload = [
            'task'          => 'rewrite',
            'content'       => (string) ($article['html'] ?? ($article['content'] ?? '')),
            'instruction'   => (string) ($options['instruction'] ?? ''),
            'lang'          => (string) ($options['lang'] ?? 'tr'),
            'tone'          => (string) ($options['tone'] ?? 'professional'),
            'mode'          => (string) ($options['mode'] ?? 'rewrite'),
            'target_length' => (string) ($options['target_length'] ?? 'long'),
        ];

        $messagesResult = aig_prompt_engine_build($payload);
        if (empty($messagesResult['ok'])) {
            return ['ok' => false, 'error' => 'prompt_build_failed'];
        }

        $messages = is_array($messagesResult['messages'] ?? null) ? $messagesResult['messages'] : [];
        if (empty($messages)) {
            return ['ok' => false, 'error' => 'empty_messages'];
        }

        $provider = !empty($options['provider']) ? (string) $options['provider'] : null;
        $model    = !empty($options['model']) ? (string) $options['model'] : null;
        $route    = null;

        if (function_exists('aig_router_select')) {
            $route = aig_router_select([
                'task'               => 'rewrite',
                'preferred_provider' => $provider,
                'preferred_model'    => $model,
                'lang'               => (string) ($options['lang'] ?? 'tr'),
                'length'             => (string) ($options['target_length'] ?? 'long'),
                'request_meta'       => [
                    'message_count' => count($messages),
                ],
            ]);
        }

        if (is_array($route) && !empty($route['ok'])) {
            $provider = (string) ($route['provider'] ?? $provider);
            $model    = (string) ($route['model'] ?? $model);
        }

        $result = null;

        if (function_exists('aig_gateway_generate')) {
            $result = aig_gateway_generate([
                'provider' => (string) $provider,
                'model'    => (string) $model,
                'messages' => $messages,
                'task'     => 'rewrite',
                'options'  => [
                    'temperature' => 0.55,
                    'max_tokens'  => 2200,
                    'timeout'     => 45,
                    'retry'       => 1,
                ],
            ]);
        } elseif (function_exists('aig_llm_generate_messages')) {
            $result = aig_llm_generate_messages([
                'provider' => (string) $provider,
                'model'    => (string) $model,
                'messages' => $messages,
                'task'     => 'rewrite',
                'options'  => [
                    'temperature' => 0.55,
                    'max_tokens'  => 2200,
                ],
            ]);
        }

        if (!is_array($result) || empty($result['ok'])) {
            return [
                'ok'    => false,
                'error' => is_array($result) ? ($result['error']['code'] ?? ($result['error'] ?? 'rewrite_llm_failed')) : 'rewrite_llm_failed',
            ];
        }

        $raw = trim((string) ($result['content'] ?? ''));
        if ($raw === '') {
            return ['ok' => false, 'error' => 'empty_llm_rewrite_output'];
        }

        $article['html'] = aig_rewrite_service_make_html($raw);
        $article['content'] = trim(wp_strip_all_tags($article['html']));
        $article['sections'] = aig_rewrite_service_build_sections_from_content($article['html'], 'Metin');
        $article['summary'] = mb_substr($article['content'], 0, 280);

        return [
            'ok'      => true,
            'article' => aig_rewrite_service_finalize($article),
            'meta'    => [
                'provider' => (string) ($result['provider'] ?? $provider),
                'model'    => (string) ($result['model'] ?? $model),
                'usage'    => is_array($result['usage'] ?? null) ? $result['usage'] : [],
            ],
        ];
    }
}

if (!function_exists('aig_rewrite_service_rewrite')) {
    function aig_rewrite_service_rewrite(array $article, array $options = []): array
    {
        $options = array_merge(aig_rewrite_service_defaults(), $options);
        $article = aig_rewrite_service_ensure_article_shape($article);

        $mode = sanitize_key((string) ($options['mode'] ?? 'rewrite'));
        $tone = sanitize_text_field((string) ($options['tone'] ?? 'professional'));

        switch ($mode) {
            case 'expand':
                $article = aig_rewrite_service_expand($article, $options);
                break;

            case 'shorten':
                $article = aig_rewrite_service_shorten($article, $options);
                break;

            case 'fix_grammar':
            case 'formalize':
            case 'rewrite':
            case 'polish':
            case 'standard':
            default:
                $llmResult = aig_rewrite_service_try_llm($article, $options);

                if (!empty($llmResult['ok']) && is_array($llmResult['article'] ?? null)) {
                    $article = $llmResult['article'];
                } else {
                    if (function_exists('aig_rewrite_pipeline_run')) {
                        $article = aig_rewrite_pipeline_run($article, $options);
                    } else {
                        $article['html'] = aig_rewrite_service_apply_basic_cleanup((string) ($article['html'] ?: $article['content']));
                        $article['content'] = trim(wp_strip_all_tags($article['html']));
                        $article['sections'] = aig_rewrite_service_build_sections_from_content($article['html'], 'Metin');
                    }

                    $article = aig_rewrite_service_finalize($article);
                }
                break;
        }

        if ($tone !== '') {
            $article = aig_rewrite_service_change_tone($article, $tone);
        }

        return aig_rewrite_service_finalize($article);
    }
}

if (!function_exists('aig_rewrite_service_run')) {
    function aig_rewrite_service_run(array $payload): array
    {
        $article = is_array($payload['article'] ?? null) ? $payload['article'] : [
            'title'   => (string) ($payload['title'] ?? 'Rewrite'),
            'content' => (string) ($payload['content'] ?? ''),
            'html'    => !empty($payload['preserve_html'])
                ? (string) ($payload['content'] ?? '')
                : '',
        ];

        $options = [
            'mode'          => (string) ($payload['mode'] ?? 'rewrite'),
            'tone'          => (string) ($payload['tone'] ?? 'professional'),
            'lang'          => (string) ($payload['lang'] ?? 'tr'),
            'instruction'   => (string) ($payload['instruction'] ?? ''),
            'preserve_html' => !empty($payload['preserve_html']),
            'target_length' => (string) ($payload['target_length'] ?? 'long'),
            'provider'      => $payload['provider'] ?? null,
            'model'         => $payload['model'] ?? null,
        ];

        $rewritten = aig_rewrite_service_rewrite($article, $options);

        return [
            'ok' => true,
            'rewrite' => [
                'title'    => (string) ($rewritten['title'] ?? ''),
                'content'  => (string) ($rewritten['content'] ?? ''),
                'html'     => (string) ($rewritten['html'] ?? ''),
                'summary'  => (string) ($rewritten['summary'] ?? ''),
                'sections' => is_array($rewritten['sections'] ?? null) ? $rewritten['sections'] : [],
            ],
            'error' => null,
        ];
    }
}