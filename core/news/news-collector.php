<?php
/**
 * AI Article Generator
 * News Collector
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_news_collector_default_user_agent')) {
    function aig_news_collector_default_user_agent(): string
    {
        return 'Mozilla/5.0 (compatible; YoknoNewsBot/1.0; +https://yokno.com)';
    }
}

if (!function_exists('aig_news_collector_log')) {
    function aig_news_collector_log(string $op, array $ctx = [], string $level = 'info'): void
    {
        if (function_exists('ai_article_log')) {
            ai_article_log($op, $ctx, $level);
            return;
        }

        if (function_exists('error_log')) {
            error_log('[AIG NEWS] ' . $op . ' ' . wp_json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }
}

if (!function_exists('aig_news_collector_builtin_sources')) {
    function aig_news_collector_builtin_sources(): array
    {
        return [
            [
                'name'       => 'Reuters World',
                'url'        => 'https://feeds.reuters.com/reuters/worldNews',
                'language'   => 'en',
                'categories' => ['general', 'all'],
                'enabled'    => true,
            ],
            [
                'name'       => 'Associated Press',
                'url'        => 'https://feeds.apnews.com/apf-topnews',
                'language'   => 'en',
                'categories' => ['general', 'all'],
                'enabled'    => true,
            ],
            [
                'name'       => 'TechCrunch',
                'url'        => 'https://techcrunch.com/feed/',
                'language'   => 'en',
                'categories' => ['tech', 'ai', 'general'],
                'enabled'    => true,
            ],
            [
                'name'       => 'The Verge',
                'url'        => 'https://www.theverge.com/rss/index.xml',
                'language'   => 'en',
                'categories' => ['tech', 'ai', 'gaming', 'general'],
                'enabled'    => true,
            ],
            [
                'name'       => 'Engadget',
                'url'        => 'https://www.engadget.com/rss.xml',
                'language'   => 'en',
                'categories' => ['tech', 'ai', 'gaming', 'general'],
                'enabled'    => true,
            ],
            [
                'name'       => 'VentureBeat',
                'url'        => 'https://venturebeat.com/feed/',
                'language'   => 'en',
                'categories' => ['tech', 'ai', 'general'],
                'enabled'    => true,
            ],
            [
                'name'       => 'ScienceDaily',
                'url'        => 'https://www.sciencedaily.com/rss/all.xml',
                'language'   => 'en',
                'categories' => ['science', 'general'],
                'enabled'    => true,
            ],
            [
                'name'       => 'Space.com',
                'url'        => 'https://www.space.com/feeds/all',
                'language'   => 'en',
                'categories' => ['space', 'science', 'general'],
                'enabled'    => true,
            ],
            [
                'name'       => 'Polygon',
                'url'        => 'https://www.polygon.com/rss/index.xml',
                'language'   => 'en',
                'categories' => ['gaming', 'general'],
                'enabled'    => true,
            ],
        ];
    }
}

if (!function_exists('aig_news_collector_prepare_sources')) {
    function aig_news_collector_prepare_sources(array $args = []): array
    {
        $bucket = [];

        if (function_exists('aig_news_get_sources_for_category')) {
            $sources = aig_news_get_sources_for_category((string) ($args['category'] ?? 'tech'));
            if (is_array($sources) && !empty($sources)) {
                return array_values($sources);
            }
        }

        if (function_exists('aig_news_get_all_sources')) {
            $all = aig_news_get_all_sources();
            if (is_array($all) && !empty($all)) {
                return array_values($all);
            }
        }

        if (function_exists('ai_article_news_sources_get_all')) {
            $all = ai_article_news_sources_get_all();
            if (is_array($all) && !empty($all)) {
                return array_values($all);
            }
        }

        if (defined('AIG_DATA_DIR')) {
            $jsonFile = rtrim(AIG_DATA_DIR, '/\\') . '/news-sources.json';

            if (file_exists($jsonFile)) {
                $json = json_decode((string) file_get_contents($jsonFile), true);

                if (is_array($json)) {
                    if (!empty($json['sources']) && is_array($json['sources'])) {
                        return array_values($json['sources']);
                    }

                    foreach ($json as $value) {
                        if (!is_array($value)) {
                            continue;
                        }

                        $isAssoc = array_keys($value) !== range(0, count($value) - 1);

                        if (!$isAssoc) {
                            foreach ($value as $row) {
                                if (is_array($row) && (!empty($row['url']) || !empty($row['feed_url']))) {
                                    $bucket[] = $row;
                                }
                            }
                        } elseif (!empty($value['sources']) && is_array($value['sources'])) {
                            foreach ($value['sources'] as $row) {
                                if (is_array($row) && (!empty($row['url']) || !empty($row['feed_url']))) {
                                    $bucket[] = $row;
                                }
                            }
                        }
                    }

                    if (!empty($bucket)) {
                        return array_values($bucket);
                    }

                    $isAssocTop = array_keys($json) !== range(0, count($json) - 1);
                    if (!$isAssocTop) {
                        foreach ($json as $row) {
                            if (is_array($row) && (!empty($row['url']) || !empty($row['feed_url']))) {
                                $bucket[] = $row;
                            }
                        }
                    }

                    if (!empty($bucket)) {
                        return array_values($bucket);
                    }
                }
            }
        }

        /**
         * SON FALLBACK:
         * JSON / helper gelmezse gömülü kaynaklarla devam et.
         */
        return aig_news_collector_builtin_sources();
    }
}

if (!function_exists('aig_news_collector_filter_sources')) {
    function aig_news_collector_filter_sources(array $sources, array $args = []): array
    {
        $category = sanitize_key((string) ($args['category'] ?? ''));
        $limit    = max(1, min(50, (int) ($args['limit'] ?? 10)));

        $normalized = [];
        $matched    = [];

        foreach ($sources as $source) {
            if (!is_array($source)) {
                continue;
            }

            $enabled = !array_key_exists('enabled', $source) || !empty($source['enabled']);
            if (!$enabled) {
                continue;
            }

            $url = trim((string) ($source['url'] ?? $source['feed_url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $row = [
                'name'       => (string) ($source['name'] ?? $source['title'] ?? parse_url($url, PHP_URL_HOST) ?? 'Source'),
                'url'        => $url,
                'site'       => (string) ($source['site'] ?? ''),
                'language'   => (string) ($source['language'] ?? 'en'),
                'categories' => $source['categories'] ?? ($source['category'] ?? []),
            ];

            $normalized[] = $row;

            if ($category === '') {
                $matched[] = $row;
                continue;
            }

            $sourceCats = $row['categories'];

            if (is_string($sourceCats)) {
                $sourceCats = [$sourceCats];
            }

            if (!is_array($sourceCats) || empty($sourceCats)) {
                $matched[] = $row;
                continue;
            }

            $sourceCats = array_map(static function ($v) {
                $v = sanitize_key((string) $v);

                $map = [
                    'technology'              => 'tech',
                    'tech'                    => 'tech',
                    'artificial_intelligence' => 'ai',
                    'ai'                      => 'ai',
                    'finance'                 => 'finance',
                    'business'                => 'finance',
                    'science'                 => 'science',
                    'gaming'                  => 'gaming',
                    'games'                   => 'gaming',
                    'defense'                 => 'defense',
                    'security'                => 'defense',
                    'space'                   => 'space',
                    'general'                 => 'general',
                    'all'                     => 'all',
                ];

                return $map[$v] ?? $v;
            }, $sourceCats);

            if (
                in_array($category, $sourceCats, true) ||
                in_array('all', $sourceCats, true) ||
                in_array('general', $sourceCats, true)
            ) {
                $matched[] = $row;
            }
        }

        $final = !empty($matched) ? $matched : $normalized;

        return array_slice(array_values($final), 0, $limit);
    }
}

if (!function_exists('aig_news_collector_build_http_args')) {
    function aig_news_collector_build_http_args(array $args = []): array
    {
        return [
            'timeout'     => max(8, min(30, (int) ($args['timeout'] ?? 15))),
            'redirection' => 5,
            'user-agent'  => aig_news_collector_default_user_agent(),
            'headers'     => [
                'Accept'     => 'application/rss+xml, application/xml, text/xml, application/atom+xml, text/plain;q=0.8, */*;q=0.5',
                'User-Agent' => aig_news_collector_default_user_agent(),
            ],
        ];
    }
}

if (!function_exists('aig_news_collector_fetch_url')) {
    function aig_news_collector_fetch_url(string $url, array $args = []): array
    {
        $httpArgs = aig_news_collector_build_http_args($args);
        $start    = microtime(true);
        $resp     = wp_remote_get($url, $httpArgs);
        $ms       = (int) round((microtime(true) - $start) * 1000);

        if (is_wp_error($resp)) {
            return [
                'ok'      => false,
                'error'   => 'http_error',
                'status'  => 0,
                'detail'  => $resp->get_error_message(),
                'body'    => '',
                'headers' => [],
                'ms'      => $ms,
            ];
        }

        $status  = (int) wp_remote_retrieve_response_code($resp);
        $body    = (string) wp_remote_retrieve_body($resp);
        $headers = wp_remote_retrieve_headers($resp);

        if ($status < 200 || $status >= 300) {
            return [
                'ok'      => false,
                'error'   => 'http_error',
                'status'  => $status,
                'detail'  => 'Non-2xx response',
                'body'    => $body,
                'headers' => $headers,
                'ms'      => $ms,
            ];
        }

        if (trim($body) === '') {
            return [
                'ok'      => false,
                'error'   => 'empty_body',
                'status'  => $status,
                'detail'  => 'Remote body is empty',
                'body'    => '',
                'headers' => $headers,
                'ms'      => $ms,
            ];
        }

        return [
            'ok'      => true,
            'error'   => '',
            'status'  => $status,
            'detail'  => '',
            'body'    => $body,
            'headers' => $headers,
            'ms'      => $ms,
        ];
    }
}

if (!function_exists('aig_news_collector_parse_date')) {
    function aig_news_collector_parse_date($value): string
    {
        if (!is_string($value) || trim($value) === '') {
            return '';
        }

        $ts = strtotime($value);
        if (!$ts) {
            return '';
        }

        return gmdate('c', $ts);
    }
}

if (!function_exists('aig_news_collector_xml_load')) {
    function aig_news_collector_xml_load(string $body)
    {
        if (!class_exists('DOMDocument') || !function_exists('simplexml_load_string')) {
            return false;
        }

        $prev = libxml_use_internal_errors(true);
        $xml  = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return $xml ?: false;
    }
}

if (!function_exists('aig_news_collector_parse_rss')) {
    function aig_news_collector_parse_rss(string $body, array $source = [], array $args = []): array
    {
        $xml = aig_news_collector_xml_load($body);
        if ($xml === false) {
            return [];
        }

        $items      = [];
        $sourceName = (string) ($source['name'] ?? 'Source');
        $sourceUrl  = (string) ($source['url'] ?? '');

        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $title = trim((string) ($item->title ?? ''));
                $link  = trim((string) ($item->link ?? ''));
                $desc  = trim((string) ($item->description ?? ''));
                $date  = aig_news_collector_parse_date((string) ($item->pubDate ?? ''));

                if ($title === '' && $link === '') {
                    continue;
                }

                $items[] = [
                    'title'        => $title,
                    'url'          => $link,
                    'summary'      => wp_strip_all_tags($desc),
                    'published_at' => $date,
                    'source'       => $sourceName,
                    'source_url'   => $sourceUrl,
                ];
            }
        }

        if (empty($items) && isset($xml->entry)) {
            foreach ($xml->entry as $entry) {
                $title = trim((string) ($entry->title ?? ''));
                $link  = '';

                if (isset($entry->link)) {
                    foreach ($entry->link as $lnk) {
                        $attrs = $lnk->attributes();
                        $href  = trim((string) ($attrs['href'] ?? ''));
                        if ($href !== '') {
                            $link = $href;
                            break;
                        }
                    }
                }

                $summary = trim((string) ($entry->summary ?? $entry->content ?? ''));
                $date    = aig_news_collector_parse_date((string) ($entry->updated ?? $entry->published ?? ''));

                if ($title === '' && $link === '') {
                    continue;
                }

                $items[] = [
                    'title'        => $title,
                    'url'          => $link,
                    'summary'      => wp_strip_all_tags($summary),
                    'published_at' => $date,
                    'source'       => $sourceName,
                    'source_url'   => $sourceUrl,
                ];
            }
        }

        return $items;
    }
}

if (!function_exists('aig_news_collector_match_keyword')) {
    function aig_news_collector_match_keyword(array $item, string $keyword): bool
    {
        $keyword = trim(mb_strtolower($keyword));
        if ($keyword === '') {
            return true;
        }

        $haystack = mb_strtolower(
            trim(
                (string) ($item['title'] ?? '') . ' ' .
                (string) ($item['summary'] ?? '')
            )
        );

        return strpos($haystack, $keyword) !== false;
    }
}

if (!function_exists('aig_news_collector_dedupe_items')) {
    function aig_news_collector_dedupe_items(array $items): array
    {
        $seen = [];
        $out  = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $key = md5(
                trim((string) ($item['title'] ?? '')) . '|' .
                trim((string) ($item['url'] ?? ''))
            );

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $out[] = $item;
        }

        return $out;
    }
}

if (!function_exists('aig_news_collector_sort_items')) {
    function aig_news_collector_sort_items(array $items): array
    {
        usort($items, static function ($a, $b) {
            $ta = !empty($a['published_at']) ? strtotime((string) $a['published_at']) : 0;
            $tb = !empty($b['published_at']) ? strtotime((string) $b['published_at']) : 0;
            return $tb <=> $ta;
        });

        return $items;
    }
}

if (!function_exists('aig_news_collector_apply_range_filter')) {
    function aig_news_collector_apply_range_filter(array $items, string $range = '24h'): array
    {
        $range = trim($range);
        if ($range === '') {
            return $items;
        }

        $secondsMap = [
            '6h'  => 6 * HOUR_IN_SECONDS,
            '12h' => 12 * HOUR_IN_SECONDS,
            '24h' => 24 * HOUR_IN_SECONDS,
            '3d'  => 3 * DAY_IN_SECONDS,
            '7d'  => 7 * DAY_IN_SECONDS,
        ];

        if (!isset($secondsMap[$range])) {
            return $items;
        }

        $cutoff = time() - $secondsMap[$range];
        $out    = [];

        foreach ($items as $item) {
            $ts = !empty($item['published_at']) ? strtotime((string) $item['published_at']) : 0;

            if ($ts <= 0 || $ts >= $cutoff) {
                $out[] = $item;
            }
        }

        return $out;
    }
}

if (!function_exists('aig_news_collect')) {
    function aig_news_collect(array $args = []): array
    {
        $args = wp_parse_args($args, [
            'category'  => 'tech',
            'keyword'   => '',
            'range'     => '24h',
            'limit'     => 10,
            'timeout'   => 15,
            'use_cache' => false,
        ]);

        aig_news_collector_log('news_collect_start', [
            'category' => $args['category'],
            'keyword'  => $args['keyword'],
            'range'    => $args['range'],
            'limit'    => $args['limit'],
        ], 'info');

        $sources = aig_news_collector_prepare_sources($args);
        $sources = aig_news_collector_filter_sources($sources, [
            'category' => $args['category'],
            'limit'    => max(5, (int) $args['limit']),
        ]);

        if (empty($sources)) {
            aig_news_collector_log('news_collect_done', [
                'ok'          => false,
                'raw_count'   => 0,
                'final_count' => 0,
                'reason'      => 'no_sources',
            ], 'warn');

            return [
                'ok'     => false,
                'items'  => [],
                'errors' => ['no_sources'],
                'meta'   => [
                    'raw_count'   => 0,
                    'final_count' => 0,
                ],
                'error'  => 'no_sources',
            ];
        }

        $allItems = [];
        $errors   = [];

        foreach ($sources as $source) {
            $url  = (string) ($source['url'] ?? '');
            $name = (string) ($source['name'] ?? 'Source');

            $fetched = aig_news_collector_fetch_url($url, [
                'timeout' => (int) $args['timeout'],
            ]);

            if (empty($fetched['ok'])) {
                $errors[] = [
                    'source' => $name,
                    'url'    => $url,
                    'error'  => (string) ($fetched['error'] ?? 'http_error'),
                    'status' => (int) ($fetched['status'] ?? 0),
                    'detail' => $fetched['detail'] ?? '',
                ];

                aig_news_collector_log('news_collect_source_error', [
                    'source' => $name,
                    'url'    => $url,
                    'error'  => (string) ($fetched['error'] ?? 'http_error'),
                    'status' => (int) ($fetched['status'] ?? 0),
                    'detail' => $fetched['detail'] ?? '',
                ], 'warn');

                continue;
            }

            $parsed = aig_news_collector_parse_rss((string) $fetched['body'], $source, $args);

            if (empty($parsed)) {
                aig_news_collector_log('news_collect_source_empty', [
                    'source' => $name,
                    'url'    => $url,
                    'status' => (int) ($fetched['status'] ?? 200),
                ], 'warn');
                continue;
            }

            foreach ($parsed as $item) {
                if (!aig_news_collector_match_keyword($item, (string) $args['keyword'])) {
                    continue;
                }
                $allItems[] = $item;
            }
        }

        $rawCount = count($allItems);

        $allItems   = aig_news_collector_dedupe_items($allItems);
        $allItems   = aig_news_collector_apply_range_filter($allItems, (string) $args['range']);
        $allItems   = aig_news_collector_sort_items($allItems);
        $finalLimit = max(1, min(50, (int) $args['limit']));
        $allItems   = array_slice($allItems, 0, $finalLimit);

        $finalCount = count($allItems);

        aig_news_collector_log('news_collect_done', [
            'ok'          => ($finalCount > 0),
            'raw_count'   => $rawCount,
            'final_count' => $finalCount,
            'error_count' => count($errors),
            'source_count'=> count($sources),
        ], $finalCount > 0 ? 'info' : 'warn');

        if ($finalCount === 0) {
            return [
                'ok'     => false,
                'items'  => [],
                'errors' => $errors,
                'meta'   => [
                    'raw_count'   => $rawCount,
                    'final_count' => 0,
                    'source_count'=> count($sources),
                ],
                'error'  => 'no_news_found',
            ];
        }

        return [
            'ok'     => true,
            'items'  => $allItems,
            'errors' => $errors,
            'meta'   => [
                'raw_count'   => $rawCount,
                'final_count' => $finalCount,
                'source_count'=> count($sources),
            ],
            'error'  => null,
        ];
    }
}

if (!function_exists('ai_article_collect_news_facts')) {
    function ai_article_collect_news_facts(array $args = []): array
    {
        $category = (string) ($args['category'] ?? 'tech');
        $keyword  = (string) ($args['keyword'] ?? '');
        $topic    = (string) ($args['topic'] ?? '');
        $range    = (string) ($args['news_range'] ?? $args['range'] ?? '24h');
        $limit    = (int) ($args['source_limit'] ?? $args['limit'] ?? 10);

        $collected = aig_news_collect([
            'category' => $category,
            'keyword'  => $keyword,
            'range'    => $range,
            'limit'    => $limit,
            'timeout'  => 15,
        ]);

        /**
         * FAIL-SOFT:
         * Haber bulunamazsa pipeline düşmesin.
         * Topic / keyword / category bazlı fallback fact-pack üret.
         */
        if (empty($collected['ok'])) {
            $topicText = trim($topic);

            if ($topicText === '') {
                $topicText = trim($keyword);
            }

            if ($topicText === '') {
                $topicText = trim($category);
            }

            if ($topicText === '') {
                $topicText = 'güncel gelişmeler';
            }

            return [
                'ok'         => true,
                'error'      => null,
                'items'      => [],
                'headlines'  => [
                    [
                        'title'        => $topicText . ' alanındaki son gelişmeler',
                        'url'          => '',
                        'source'       => 'analysis',
                        'published_at' => gmdate('c'),
                    ],
                ],
                'key_points' => [
                    $topicText . ' alanında son dönemde dikkat çeken gelişmeler yaşanıyor.',
                    'Şirketler, girişimler ve uzmanlar bu başlıkta yeni adımlar atıyor.',
                    'Pazar dinamikleri ve kullanıcı ilgisi bu konunun önemini artırıyor.',
                    'Önümüzdeki dönemde bu alandaki rekabetin daha görünür hale gelmesi bekleniyor.',
                    'Teknolojik, ekonomik ve stratejik etkiler birlikte değerlendirilmelidir.',
                ],
                'entities'   => [$topicText],
                'sources'    => [],
                'meta'       => [
                    'fallback'     => true,
                    'reason'       => (string) ($collected['error'] ?? 'news_collect_failed'),
                    'raw_count'    => (int) ($collected['meta']['raw_count'] ?? 0),
                    'final_count'  => (int) ($collected['meta']['final_count'] ?? 0),
                    'source_count' => (int) ($collected['meta']['source_count'] ?? 0),
                ],
            ];
        }

        $items = is_array($collected['items'] ?? null) ? $collected['items'] : [];

        $headlines = [];
        $keyPoints = [];
        $entities  = [];
        $sources   = [];

        foreach ($items as $item) {
            $title   = trim((string) ($item['title'] ?? ''));
            $summary = trim((string) ($item['summary'] ?? ''));
            $source  = trim((string) ($item['source'] ?? ''));
            $url     = trim((string) ($item['url'] ?? ''));

            if ($title !== '') {
                $headlines[] = [
                    'title'        => $title,
                    'url'          => $url,
                    'source'       => $source,
                    'published_at' => (string) ($item['published_at'] ?? ''),
                ];
            }

            if ($summary !== '') {
                $keyPoints[] = $summary;
            }

            if ($source !== '') {
                $sources[] = [
                    'title' => $source,
                    'url'   => $url,
                ];
            }

            if ($title !== '') {
                preg_match_all('/\b[A-ZÇĞİÖŞÜ][A-Za-zÇĞİÖŞÜçğıöşü0-9\-\+\.]{2,}\b/u', $title, $m);
                if (!empty($m[0]) && is_array($m[0])) {
                    foreach ($m[0] as $ent) {
                        $entities[] = $ent;
                    }
                }
            }
        }

        $entities = array_values(array_unique(array_filter(array_map('trim', $entities))));
        $sources  = array_values(array_filter($sources));

        return [
            'ok'         => true,
            'items'      => $items,
            'headlines'  => $headlines,
            'key_points' => array_slice(array_values(array_unique(array_filter($keyPoints))), 0, 12),
            'entities'   => array_slice($entities, 0, 20),
            'sources'    => array_slice($sources, 0, 12),
            'meta'       => is_array($collected['meta'] ?? null) ? $collected['meta'] : [],
            'error'      => null,
        ];
    }
}