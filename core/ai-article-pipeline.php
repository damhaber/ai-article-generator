<?php
/**
 * AI Article Generator
 * Article Pipeline
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_article_pipeline_run')) {
    function aig_article_pipeline_run(array $input): array
    {
        $startedAt = microtime(true);

        $contextResult = aig_article_pipeline_build_context($input);
        if (empty($contextResult['ok'])) {
            return aig_article_pipeline_build_stage_error(
                'context',
                (string) ($contextResult['error']['code'] ?? 'context_failed'),
                (string) ($contextResult['error']['message'] ?? 'Context build failed.'),
                [
                    'context_meta' => is_array($contextResult['meta'] ?? null) ? $contextResult['meta'] : [],
                ]
            );
        }

        $context = is_array($contextResult['context'] ?? null) ? $contextResult['context'] : [];

      if (!function_exists('aig_article_pipeline_build_outline')) {
    function aig_article_pipeline_build_outline(array $context, array $input): array
    {
        if (function_exists('aig_article_outline_build')) {
            return aig_article_outline_build($context, $input);
        }

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
            'error' => [
                'code'    => 'missing_outline_builder',
                'message' => 'Outline builder function is missing.',
            ],
            'meta' => [],
        ];
    }
}

        $outline = is_array($outlineResult['outline'] ?? null) ? $outlineResult['outline'] : [];

        $messagesResult = aig_article_pipeline_build_messages($context, $outline, $input);
        if (empty($messagesResult['ok'])) {
            return aig_article_pipeline_build_stage_error(
                'prompt',
                (string) ($messagesResult['error']['code'] ?? 'prompt_build_failed'),
                (string) ($messagesResult['error']['message'] ?? 'Prompt/messages build failed.'),
                [
                    'prompt_meta' => is_array($messagesResult['meta'] ?? null) ? $messagesResult['meta'] : [],
                ]
            );
        }

        $messages = is_array($messagesResult['messages'] ?? null) ? $messagesResult['messages'] : [];

        if (!function_exists('aig_article_pipeline_route')) {
    function aig_article_pipeline_route(array $input, array $messages): array
    {
        if (function_exists('aig_router_select')) {
            $result = aig_router_select([
                'task'               => 'article_generate',
                'preferred_provider' => $input['provider'] ?? null,
                'preferred_model'    => $input['model'] ?? null,
                'lang'               => $input['lang'] ?? 'tr',
                'length'             => $input['length'] ?? 'long',
                'quality_profile'    => $input['settings']['routing']['default_quality_profile'] ?? 'quality',
                'request_meta'       => [
                    'message_count' => count($messages),
                ],
            ]);

            if (empty($result['ok'])) {
                return $result;
            }

            return [
                'ok'    => true,
                'route' => $result,
                'error' => null,
            ];
        }

        /**
         * ZIP gerçekliği:
         * router select yok ama router generate var.
         * Bu durumda generate aşamasında direct router mode kullanacağız.
         */
        if (function_exists('aig_router_generate') || function_exists('aig_ai_router_generate')) {
            return [
                'ok'    => true,
                'route' => [
                    '_mode' => 'router_generate_direct',
                ],
                'meta'  => [
                    'route_mode' => 'direct_generate',
                ],
                'error' => null,
            ];
        }

        return [
            'ok' => false,
            'error' => [
                'code'    => 'missing_router',
                'message' => 'Router selection function is missing.',
            ],
            'meta' => [],
        ];
    }
}

        $route = is_array($routeResult['route'] ?? null) ? $routeResult['route'] : [];
        if (empty($route) && !empty($routeResult['provider'])) {
            $route = $routeResult;
        }

if (!function_exists('aig_article_pipeline_generate')) {
    function aig_article_pipeline_generate(array $route, array $messages, array $input): array
    {
        /**
         * ZIP gerçekliği:
         * aig_router_select yoksa, aig_router_generate / aig_ai_router_generate
         * ile doğrudan üretim yapılır.
         */
        if (($route['_mode'] ?? '') === 'router_generate_direct') {
            $payload = [
                'task_type'        => 'article_generation',
                'preset'           => $input['preset'] ?? 'free_first',
                'required_quality' => 'medium',
                'max_latency'      => 45,
                'temperature'      => 0.7,
                'max_tokens'       => 2600,
                'messages'         => $messages,
                'context'          => [
                    'topic'    => (string) ($input['topic'] ?? ''),
                    'category' => (string) ($input['category'] ?? 'general'),
                    'lang'     => (string) ($input['lang'] ?? 'tr'),
                    'length'   => (string) ($input['length'] ?? 'long'),
                    'template' => (string) ($input['template'] ?? 'news_analysis'),
                ],
            ];

            if (function_exists('aig_ai_router_generate')) {
                $result = aig_ai_router_generate($payload);
                return is_array($result) ? $result : [
                    'ok' => false,
                    'error' => [
                        'code' => 'router_generate_failed',
                        'message' => 'Router generate returned invalid result.',
                    ],
                ];
            }

            if (function_exists('aig_router_generate')) {
                $result = aig_router_generate($payload);
                return is_array($result) ? $result : [
                    'ok' => false,
                    'error' => [
                        'code' => 'router_generate_failed',
                        'message' => 'Router generate returned invalid result.',
                    ],
                ];
            }
        }

        if (function_exists('aig_gateway_generate')) {
            return aig_gateway_generate([
                'provider' => (string) ($route['provider'] ?? ''),
                'model'    => (string) ($route['model'] ?? ''),
                'messages' => $messages,
                'task'     => 'article_generate',
                'options'  => [
                    'temperature' => $route['options']['temperature'] ?? 0.7,
                    'max_tokens'  => $route['options']['max_tokens'] ?? 2600,
                    'timeout'     => $route['options']['timeout'] ?? 45,
                    'retry'       => $route['options']['retry'] ?? 1,
                ],
            ]);
        }

        if (function_exists('aig_llm_generate_messages')) {
            return aig_llm_generate_messages([
                'provider' => (string) ($route['provider'] ?? ''),
                'model'    => (string) ($route['model'] ?? ''),
                'messages' => $messages,
                'task'     => 'article_generate',
                'options'  => $route['options'] ?? [],
            ]);
        }

        return [
            'ok' => false,
            'error' => [
                'code'    => 'missing_gateway_and_llm',
                'message' => 'Neither gateway nor llm helper is available.',
            ],
        ];
    }
}
        $articleResult = aig_article_pipeline_parse_output($generateResult, $context, $outline, $input);
        if (empty($articleResult['ok'])) {
            return aig_article_pipeline_build_stage_error(
                'parse',
                (string) ($articleResult['error']['code'] ?? 'article_parse_failed'),
                (string) ($articleResult['error']['message'] ?? 'Generated article parse failed.'),
                [
                    'parse_meta' => is_array($articleResult['meta'] ?? null) ? $articleResult['meta'] : [],
                ]
            );
        }

        $article = is_array($articleResult['article'] ?? null) ? $articleResult['article'] : [];

        if (!empty($input['rewrite'])) {
            $rewriteResult = aig_article_pipeline_apply_rewrite($article, $input);

            if (!empty($rewriteResult['ok']) && is_array($rewriteResult['article'] ?? null)) {
                $article = $rewriteResult['article'];
            }
        }

        $seo = [
            'meta_title'       => '',
            'meta_description' => '',
            'faq'              => [],
            'schema'           => [],
            'keywords'         => [],
        ];

        if (!empty($input['seo'])) {
            $seoResult = aig_article_pipeline_apply_seo($article, $input);
            if (!empty($seoResult['ok']) && is_array($seoResult['seo'] ?? null)) {
                $seo = $seoResult['seo'];
            }
        }

        $quality = aig_article_pipeline_apply_quality($article, $input);

        $meta = [
            'provider'      => (string) ($generateResult['provider'] ?? ($route['provider'] ?? '')),
            'model'         => (string) ($generateResult['model'] ?? ($route['model'] ?? '')),
            'usage'         => is_array($generateResult['usage'] ?? null) ? $generateResult['usage'] : [],
            'quality'       => $quality,
            'timing'        => [
                'pipeline_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ],
            'build'         => (string) ($input['build'] ?? (defined('AIG_MODULE_BUILD') ? AIG_MODULE_BUILD : '')),
            'version'       => (string) ($input['version'] ?? (defined('AIG_MODULE_VERSION') ? AIG_MODULE_VERSION : '')),
            'fallback_used' => !empty($generateResult['meta']['fallback_used']),
        ];

        if (function_exists('aig_log_write')) {
            aig_log_write('info', 'article_pipeline_run_ok', [
                'topic'       => $input['topic'] ?? '',
                'category'    => $input['category'] ?? '',
                'provider'    => $meta['provider'],
                'model'       => $meta['model'],
                'pipeline_ms' => $meta['timing']['pipeline_ms'],
            ]);
        }

        return aig_article_pipeline_finalize($article, $seo, $meta);
    }
}

if (!function_exists('aig_article_pipeline_target_words')) {
    function aig_article_pipeline_target_words(array $input): int
    {
        $length = sanitize_key((string) ($input['length'] ?? 'long'));

        $map = [
            'short'  => 700,
            'medium' => 1100,
            'long'   => 1600,
        ];

        return $map[$length] ?? 1600;
    }
}

if (!function_exists('aig_article_pipeline_normalize_heading')) {
    function aig_article_pipeline_normalize_heading(string $heading): string
    {
        $heading = trim(wp_strip_all_tags($heading));
        $heading = preg_replace('/^[\-\*\d\.\)\s]+/u', '', $heading);
        return trim((string) $heading);
    }
}

if (!function_exists('aig_article_pipeline_build_html')) {
    function aig_article_pipeline_build_html(string $body): string
    {
        $body = trim($body);

        if ($body === '') {
            return '';
        }

        if (strpos($body, '<h2') !== false || strpos($body, '<p') !== false || strpos($body, '<ul') !== false) {
            return wp_kses_post($body);
        }

        return function_exists('wpautop')
            ? wpautop(esc_html($body))
            : nl2br(esc_html($body));
    }
}

if (!function_exists('aig_article_pipeline_extract_title_body')) {
    function aig_article_pipeline_extract_title_body(string $raw, array $outline, array $input): array
    {
        $normalizedRaw = str_replace(["\r\n", "\r"], "\n", trim($raw));
        $title = '';
        $body  = $normalizedRaw;

        if (preg_match('/^([^\n]{5,180})\n+/u', $normalizedRaw, $m)) {
            $firstLine = trim((string) $m[1]);

            if (
                $firstLine !== ''
                && mb_strlen($firstLine) <= 180
                && stripos($firstLine, '<p') === false
                && stripos($firstLine, '<h2') === false
            ) {
                $title = $firstLine;
                $body  = trim(substr($normalizedRaw, strlen($m[0])));
            }
        }

        if ($title === '') {
            if (!empty($outline['title_hint'])) {
                $title = (string) $outline['title_hint'];
            } else {
                $title = (string) ($input['topic'] ?? 'Generated Article');
            }
        }

        if ($body === '') {
            $body = $normalizedRaw;
        }

        return [
            'title' => trim($title),
            'body'  => trim($body),
        ];
    }
}

if (!function_exists('aig_article_pipeline_split_into_blocks')) {
    function aig_article_pipeline_split_into_blocks(string $body): array
    {
        $body = trim($body);

        if ($body === '') {
            return [];
        }

        if (preg_match_all('/<h2[^>]*>(.*?)<\/h2>(.*?)(?=<h2[^>]*>|$)/isu', $body, $matches, PREG_SET_ORDER)) {
            $sections = [];

            foreach ($matches as $match) {
                $heading = aig_article_pipeline_normalize_heading((string) ($match[1] ?? ''));
                $content = trim((string) ($match[2] ?? ''));

                if ($heading === '' && $content === '') {
                    continue;
                }

                $sections[] = [
                    'key'     => sanitize_title($heading ?: 'section'),
                    'heading' => $heading,
                    'content' => $content !== '' ? wp_kses_post($content) : '',
                ];
            }

            if (!empty($sections)) {
                return $sections;
            }
        }

        $parts = preg_split('/\n\s*\n/u', wp_strip_all_tags($body, '<p><ul><li><strong><em><a><blockquote>'));
        $parts = array_values(array_filter(array_map('trim', (array) $parts)));

        if (empty($parts)) {
            return [];
        }

        $sections = [];
        $chunks   = array_chunk($parts, max(1, (int) ceil(count($parts) / 3)));

        foreach ($chunks as $index => $chunk) {
            $label = ['Giriş', 'Gelişmeler', 'Değerlendirme'][$index] ?? ('Bölüm ' . ($index + 1));
            $html  = [];

            foreach ($chunk as $paragraph) {
                if (preg_match('/^\s*<p[\s>]/i', $paragraph)) {
                    $html[] = $paragraph;
                } else {
                    $html[] = '<p>' . esc_html(wp_strip_all_tags($paragraph)) . '</p>';
                }
            }

            $sections[] = [
                'key'     => sanitize_title($label),
                'heading' => $label,
                'content' => implode("\n", $html),
            ];
        }

        return $sections;
    }
}

if (!function_exists('aig_article_pipeline_compile_sections_html')) {
    function aig_article_pipeline_compile_sections_html(array $sections): string
    {
        $parts = [];

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $heading = trim((string) ($section['heading'] ?? $section['h2'] ?? ''));
            $content = trim((string) ($section['content'] ?? ''));

            if ($heading !== '') {
                $parts[] = '<h2>' . esc_html($heading) . '</h2>';
            }

            if ($content !== '') {
                $parts[] = wp_kses_post($content);
            }
        }

        return trim(implode("\n\n", $parts));
    }
}

if (!function_exists('aig_article_context_build')) {
    function aig_article_context_build(array $input): array
    {
        $topic        = trim((string) ($input['topic'] ?? ''));
        $category     = trim((string) ($input['category'] ?? 'tech'));
        $lang         = trim((string) ($input['lang'] ?? ($input['language'] ?? 'tr')));
        $tone         = trim((string) ($input['tone'] ?? 'professional'));
        $length       = trim((string) ($input['length'] ?? 'long'));
        $brief        = trim((string) ($input['brief'] ?? ''));
        $target_words = (int) ($input['target_words'] ?? aig_article_pipeline_target_words($input));

        if ($topic === '') {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'missing_topic',
                    'message' => 'Topic is required to build context.',
                ],
            ];
        }

        if (!function_exists('ai_article_collect_news_facts')) {
            $fallback_context = [
                'topic'        => $topic,
                'category'     => $category,
                'lang'         => $lang,
                'tone'         => $tone,
                'length'       => $length,
                'target_words' => $target_words,
                'brief'        => $brief,
                'headlines'    => [],
                'key_points'   => [],
                'entities'     => [],
                'sources'      => [],
                'source_count' => 0,
                'item_count'   => 0,
            ];

            return [
                'ok'      => true,
                'context' => $fallback_context,
                'meta'    => [
                    'context_mode'   => 'fallback_minimal',
                    'fact_pack_used' => false,
                ],
                'error'   => null,
            ];
        }

        $fact_pack = ai_article_collect_news_facts([
            'topic'        => $topic,
            'keyword'      => (string) ($input['keyword'] ?? ''),
            'category'     => $category,
            'news_range'   => (string) ($input['news_range'] ?? '24h'),
            'source_limit' => (int) ($input['source_limit'] ?? 10),
            'language'     => $lang,
            'lang'         => $lang,
            'brief'        => $brief,
        ]);

        if (!is_array($fact_pack) || empty($fact_pack['ok'])) {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'fact_pack_failed',
                    'message' => (string) ($fact_pack['error'] ?? 'Fact pack build failed.'),
                ],
                'meta' => [
                    'fact_pack' => is_array($fact_pack) ? $fact_pack : [],
                ],
            ];
        }

        if (!function_exists('aig_article_context_build_from_fact_pack')) {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'missing_context_builder_from_fact_pack',
                    'message' => 'Context builder from fact pack is missing.',
                ],
            ];
        }

        $context = aig_article_context_build_from_fact_pack($fact_pack, [
            'topic'        => $topic,
            'category'     => $category,
            'lang'         => $lang,
            'tone'         => $tone,
            'length'       => $length,
            'target_words' => $target_words,
            'brief'        => $brief,
        ]);

        return [
            'ok'      => true,
            'context' => is_array($context) ? $context : [],
            'meta'    => [
                'context_mode'   => 'news_fact_pack',
                'fact_pack_used' => true,
                'item_count'     => (int) ($context['item_count'] ?? 0),
                'source_count'   => (int) ($context['source_count'] ?? 0),
            ],
            'error'   => null,
        ];
    }
}

if (!function_exists('aig_article_pipeline_build_context')) {
    function aig_article_pipeline_build_context(array $input): array
    {
        if (!function_exists('aig_article_context_build')) {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'missing_context_builder',
                    'message' => 'Context builder function is missing.',
                ],
            ];
        }

        return aig_article_context_build($input);
    }
}

if (!function_exists('aig_article_pipeline_build_outline')) {
    function aig_article_pipeline_build_outline(array $context, array $input): array
    {
        if (!function_exists('aig_article_outline_build')) {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'missing_outline_builder',
                    'message' => 'Outline builder function is missing.',
                ],
            ];
        }

        return aig_article_outline_build($context, $input);
    }
}

if (!function_exists('aig_article_pipeline_build_messages')) {
    function aig_article_pipeline_build_messages(array $context, array $outline, array $input): array
    {
        if (!function_exists('aig_prompt_engine_build')) {
            return [
                'ok' => false,
                'messages' => [],
                'error' => [
                    'code'    => 'missing_prompt_engine',
                    'message' => 'Prompt engine is missing.',
                ],
            ];
        }

        return aig_prompt_engine_build([
            'task'     => 'article_generate',
            'topic'    => (string) ($input['topic'] ?? ''),
            'category' => (string) ($input['category'] ?? 'general'),
            'lang'     => (string) ($input['lang'] ?? 'tr'),
            'tone'     => (string) ($input['tone'] ?? 'analytical'),
            'length'   => (string) ($input['length'] ?? 'long'),
            'template' => (string) ($input['template'] ?? 'news_analysis'),
            'context'  => $context,
            'outline'  => $outline,
        ]);
    }
}

if (!function_exists('aig_article_pipeline_route')) {
    function aig_article_pipeline_route(array $input, array $messages): array
    {
        if (!function_exists('aig_router_select')) {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'missing_router',
                    'message' => 'Router selection function is missing.',
                ],
            ];
        }

        $result = aig_router_select([
            'task'               => 'article_generate',
            'preferred_provider' => $input['provider'] ?? null,
            'preferred_model'    => $input['model'] ?? null,
            'lang'               => $input['lang'] ?? 'tr',
            'length'             => $input['length'] ?? 'long',
            'quality_profile'    => $input['settings']['routing']['default_quality_profile'] ?? 'quality',
            'request_meta'       => [
                'message_count' => count($messages),
            ],
        ]);

        if (empty($result['ok'])) {
            return $result;
        }

        return [
            'ok'    => true,
            'route' => $result,
            'error' => null,
        ];
    }
}

if (!function_exists('aig_article_pipeline_generate')) {
    function aig_article_pipeline_generate(array $route, array $messages, array $input): array
    {
        if (function_exists('aig_gateway_generate')) {
            return aig_gateway_generate([
                'provider' => (string) ($route['provider'] ?? ''),
                'model'    => (string) ($route['model'] ?? ''),
                'messages' => $messages,
                'task'     => 'article_generate',
                'options'  => [
                    'temperature' => $route['options']['temperature'] ?? 0.7,
                    'max_tokens'  => $route['options']['max_tokens'] ?? 2600,
                    'timeout'     => $route['options']['timeout'] ?? 45,
                    'retry'       => $route['options']['retry'] ?? 1,
                ],
            ]);
        }

        if (function_exists('aig_llm_generate_messages')) {
            return aig_llm_generate_messages([
                'provider' => (string) ($route['provider'] ?? ''),
                'model'    => (string) ($route['model'] ?? ''),
                'messages' => $messages,
                'task'     => 'article_generate',
                'options'  => $route['options'] ?? [],
            ]);
        }

        return [
            'ok' => false,
            'error' => [
                'code'    => 'missing_gateway_and_llm',
                'message' => 'Neither gateway nor llm helper is available.',
            ],
        ];
    }
}

if (!function_exists('aig_article_pipeline_parse_output')) {
    function aig_article_pipeline_parse_output(array $generateResult, array $context, array $outline, array $input): array
    {
        $raw = trim((string) ($generateResult['content'] ?? ''));
        if ($raw === '') {
            return [
                'ok' => false,
                'error' => [
                    'code'    => 'empty_generation',
                    'message' => 'Generated content was empty.',
                ],
            ];
        }

        $parsed  = aig_article_pipeline_extract_title_body($raw, $outline, $input);
        $title   = (string) ($parsed['title'] ?? '');
        $body    = (string) ($parsed['body'] ?? '');
        $html    = aig_article_pipeline_build_html($body);
        $sections = aig_article_pipeline_extract_sections($html, $outline);
        $summary = aig_article_pipeline_extract_summary($body, $input);
        $sources = is_array($context['sources'] ?? null) ? $context['sources'] : [];

        if (empty($sections) && $html !== '') {
            $sections = aig_article_pipeline_split_into_blocks($html);
        }

        if ($body === '' && $html !== '') {
            $body = trim(wp_strip_all_tags($html));
        }

        return [
            'ok' => true,
            'article' => [
                'title'    => $title,
                'content'  => $body,
                'html'     => $html,
                'summary'  => $summary,
                'sections' => $sections,
                'sources'  => $sources,
                'lang'     => (string) ($input['lang'] ?? 'tr'),
                'category' => (string) ($input['category'] ?? 'general'),
                'topic'    => (string) ($input['topic'] ?? ''),
            ],
            'meta' => [
                'raw_length'    => mb_strlen($raw),
                'parsed_length' => mb_strlen($body),
                'section_count' => count($sections),
            ],
            'error' => null,
        ];
    }
}

if (!function_exists('aig_article_pipeline_apply_rewrite')) {
    function aig_article_pipeline_apply_rewrite(array $article, array $input): array
    {
        if (!function_exists('aig_rewrite_service_run')) {
            return ['ok' => false];
        }

        $result = aig_rewrite_service_run([
            'article'       => $article,
            'content'       => (string) ($article['html'] ?? ($article['content'] ?? '')),
            'instruction'   => 'Akışı güçlendir, tekrarları azalt, editoryal kaliteyi yükselt, dili ve anlamı koru.',
            'lang'          => (string) ($input['lang'] ?? 'tr'),
            'tone'          => (string) ($input['tone'] ?? 'analytical'),
            'mode'          => 'rewrite',
            'preserve_html' => true,
            'target_length' => (string) ($input['length'] ?? 'long'),
            'provider'      => $input['provider'] ?? null,
            'model'         => $input['model'] ?? null,
        ]);

        if (empty($result['ok'])) {
            return ['ok' => false];
        }

        $rewritten = is_array($result['rewrite'] ?? null) ? $result['rewrite'] : [];

        $article['content']  = (string) ($rewritten['content'] ?? ($article['content'] ?? ''));
        $article['html']     = (string) ($rewritten['html'] ?? ($article['html'] ?? ''));
        $article['summary']  = (string) ($rewritten['summary'] ?? ($article['summary'] ?? ''));
        $article['sections'] = is_array($rewritten['sections'] ?? null) ? $rewritten['sections'] : ($article['sections'] ?? []);

        return [
            'ok'      => true,
            'article' => $article,
        ];
    }
}

if (!function_exists('aig_article_pipeline_apply_seo')) {
    function aig_article_pipeline_apply_seo(array $article, array $input): array
    {
        if (!function_exists('aig_seo_service_generate')) {
            return ['ok' => false];
        }

        return aig_seo_service_generate([
            'title'    => (string) ($article['title'] ?? ''),
            'content'  => (string) ($article['content'] ?? ''),
            'category' => (string) ($article['category'] ?? ($input['category'] ?? 'general')),
            'lang'     => (string) ($article['lang'] ?? ($input['lang'] ?? 'tr')),
            'topic'    => (string) ($article['topic'] ?? ($input['topic'] ?? '')),
            'provider' => $input['provider'] ?? null,
            'model'    => $input['model'] ?? null,
        ]);
    }
}

if (!function_exists('aig_article_pipeline_apply_quality')) {
    function aig_article_pipeline_apply_quality(array $article, array $input): array
    {
        if (!function_exists('aig_quality_evaluate_article')) {
            return [];
        }

        $quality = aig_quality_evaluate_article($article, $input);
        return is_array($quality) ? $quality : [];
    }
}

if (!function_exists('aig_article_pipeline_finalize')) {
    function aig_article_pipeline_finalize(array $article, array $seo, array $meta): array
    {
        return [
            'ok' => true,
            'article' => [
                'title'    => (string) ($article['title'] ?? ''),
                'content'  => (string) ($article['content'] ?? ''),
                'html'     => (string) ($article['html'] ?? ''),
                'summary'  => (string) ($article['summary'] ?? ''),
                'sections' => is_array($article['sections'] ?? null) ? $article['sections'] : [],
                'sources'  => is_array($article['sources'] ?? null) ? $article['sources'] : [],
                'lang'     => (string) ($article['lang'] ?? 'tr'),
                'category' => (string) ($article['category'] ?? 'general'),
                'topic'    => (string) ($article['topic'] ?? ''),
            ],
            'seo' => [
                'meta_title'       => (string) ($seo['meta_title'] ?? ''),
                'meta_description' => (string) ($seo['meta_description'] ?? ''),
                'faq'              => is_array($seo['faq'] ?? null) ? $seo['faq'] : [],
                'schema'           => is_array($seo['schema'] ?? null) ? $seo['schema'] : [],
                'keywords'         => is_array($seo['keywords'] ?? null) ? $seo['keywords'] : [],
            ],
            'meta'  => is_array($meta) ? $meta : [],
            'error' => null,
        ];
    }
}

if (!function_exists('aig_article_pipeline_build_stage_error')) {
    function aig_article_pipeline_build_stage_error(string $stage, string $code, string $message, array $meta = []): array
    {
        $meta['stage'] = $stage;

        if (function_exists('aig_log_write')) {
            aig_log_write('error', 'article_pipeline_stage_error', [
                'stage'   => $stage,
                'code'    => $code,
                'message' => $message,
                'meta'    => $meta,
            ]);
        }

        return [
            'ok' => false,
            'meta' => $meta,
            'error' => [
                'code'    => $code,
                'message' => $message,
            ],
        ];
    }
}

if (!function_exists('aig_article_pipeline_extract_summary')) {
    function aig_article_pipeline_extract_summary(string $body, array $input = []): string
    {
        $body = trim(strip_tags($body));
        if ($body === '') {
            return '';
        }

        $sentences = preg_split('/(?<=[.!?])\s+/u', $body);
        $sentences = array_values(array_filter(array_map('trim', (array) $sentences)));

        if (empty($sentences)) {
            return mb_substr($body, 0, 240);
        }

        $summary = implode(' ', array_slice($sentences, 0, 2));
        return mb_substr(trim($summary), 0, 320);
    }
}

if (!function_exists('aig_article_pipeline_extract_sections')) {
    function aig_article_pipeline_extract_sections(string $bodyHtml, array $outline = []): array
    {
        $sections = aig_article_pipeline_split_into_blocks($bodyHtml);
        if (!empty($sections)) {
            return $sections;
        }

        $fallback = [];
        $outlineSections = is_array($outline['sections'] ?? null) ? $outline['sections'] : [];

        foreach ($outlineSections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $heading = trim((string) ($section['heading'] ?? ''));
            $goal    = trim((string) ($section['goal'] ?? ''));

            if ($heading === '' && $goal === '') {
                continue;
            }

            $fallback[] = [
                'key'     => (string) ($section['key'] ?? sanitize_title($heading ?: 'section')),
                'heading' => $heading,
                'goal'    => $goal,
                'content' => '',
            ];
        }

        if (empty($fallback) && trim($bodyHtml) !== '') {
            $fallback[] = [
                'key'     => 'article',
                'heading' => '',
                'goal'    => '',
                'content' => $bodyHtml,
            ];
        }

        return $fallback;
    }
}