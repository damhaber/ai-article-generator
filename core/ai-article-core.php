<?php
/**
 * AI Article Generator — Core Engine
 *
 * - Rate limit + cache + log entegrasyonu
 * - Fatal üretmez, her zaman array döndürür
 */

if (!defined('ABSPATH')) { exit; }

if (!defined('AI_ARTICLE_PATH')) {
    define('AI_ARTICLE_PATH', wp_normalize_path(dirname(__DIR__, 1)));
}
if (!defined('AI_ARTICLE_LOG')) {
    // Article generator için ayrı log; yoksa SEO Log’a düşer.
    define('AI_ARTICLE_LOG', wp_normalize_path(AI_ARTICLE_PATH . '/logs/ai-article-generator.log'));
}

/* ------------------------------------------------------------
 * Yardımcı: güvenli log
 * ------------------------------------------------------------ */
if (!function_exists('ai_article_log')) {
    function ai_article_log(string $op, $data = null, string $level = 'info'): void {
        $row = [
            'ts'    => gmdate('c'),
            'level' => $level,
            'op'    => $op,
        ];
        if ($data !== null) $row['data'] = $data;

        $line = json_encode($row, JSON_UNESCAPED_UNICODE);
        $file = AI_ARTICLE_LOG;

        // Fallback: AISEO_LOG_FILE varsa oraya da yaz
        $targets = [$file];
        if (defined('AISEO_LOG_FILE')) $targets[] = AISEO_LOG_FILE;

        foreach (array_unique(array_filter($targets)) as $t) {
            if (!$t) continue;
            $dir = dirname($t);
            if (!is_dir($dir)) @wp_mkdir_p($dir);
            if (@is_writable($dir)) {
                @file_put_contents($t, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
        }
    }
}

/* ------------------------------------------------------------
 * Rate limit yardımcıları
 * - Bu modül artık gizli ortak limiter’a bağımlı değildir.
 * - Admin/manage_options kullanıcıları varsayılan olarak bypass edilir.
 * - Public çağrılar için kullanıcı/IP bazlı sayaç tutulur.
 * ------------------------------------------------------------ */
if (!function_exists('ai_article_safe_retry')) {
    function ai_article_safe_retry(callable $fn, int $tries = 2, int $waitMs = 400) {
        $last = null;
        for ($i = 0; $i < max(1, $tries); $i++) {
            try {
                return $fn();
            } catch (Throwable $e) {
                $last = $e;
                usleep($waitMs * 1000);
            }
        }
        if ($last) throw $last;
        return null;
    }
}

if (!function_exists('ai_article_rate_limit_defaults')) {
    function ai_article_rate_limit_defaults(): array {
        return [
            'enabled' => true,
            'bypass_manage_options' => true,
            'identity_mode' => 'user_or_ip', // user_or_ip|shared|ip
            'buckets' => [
                'generate' => [
                    'limit' => 10,
                    'window' => 60,
                ],
            ],
        ];
    }
}

if (!function_exists('ai_article_rate_limit_settings')) {
    function ai_article_rate_limit_settings(): array {
        $defaults = ai_article_rate_limit_defaults();
        $settings = function_exists('aig_settings_read') ? aig_settings_read() : [];
        $rate = isset($settings['rate_limit']) && is_array($settings['rate_limit']) ? $settings['rate_limit'] : [];
        $rate = array_replace_recursive($defaults, $rate);
        if (empty($rate['buckets']) || !is_array($rate['buckets'])) {
            $rate['buckets'] = $defaults['buckets'];
        }
        return $rate;
    }
}

if (!function_exists('ai_article_rate_limit_identity')) {
    function ai_article_rate_limit_identity(string $bucket = 'generate'): string {
        $rate = ai_article_rate_limit_settings();
        $mode = (string)($rate['identity_mode'] ?? 'user_or_ip');

        if ($mode === 'shared') {
            return 'shared';
        }

        $uid = get_current_user_id();
        if ($uid > 0 && $mode === 'user_or_ip') {
            return 'user:' . $uid;
        }

        $candidates = [
            $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
            $_SERVER['HTTP_X_REAL_IP'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];
        $ip = '';
        foreach ($candidates as $candidate) {
            $candidate = is_string($candidate) ? trim($candidate) : '';
            if ($candidate !== '') {
                $ip = $candidate;
                break;
            }
        }
        if ($ip === '') {
            $ip = 'unknown';
        }

        return 'ip:' . md5($ip . '|' . $bucket);
    }
}

if (!function_exists('ai_article_rate_limit_check')) {
    function ai_article_rate_limit_check(string $bucket = 'generate', ?int $limit = null, ?int $window = null): array {
        $rate = ai_article_rate_limit_settings();
        $bucketCfg = isset($rate['buckets'][$bucket]) && is_array($rate['buckets'][$bucket]) ? $rate['buckets'][$bucket] : [];
        $limit = max(0, (int)($limit ?? $bucketCfg['limit'] ?? 10));
        $window = max(1, (int)($window ?? $bucketCfg['window'] ?? 60));

        if (empty($rate['enabled'])) {
            return [
                'ok' => true,
                'allowed' => true,
                'bucket' => $bucket,
                'limit' => $limit,
                'window' => $window,
                'remaining' => $limit,
                'retry_after' => 0,
                'reset_after' => 0,
                'bypassed' => true,
                'reason' => 'disabled',
            ];
        }

        if (!empty($rate['bypass_manage_options']) && current_user_can('manage_options')) {
            return [
                'ok' => true,
                'allowed' => true,
                'bucket' => $bucket,
                'limit' => $limit,
                'window' => $window,
                'remaining' => $limit,
                'retry_after' => 0,
                'reset_after' => 0,
                'bypassed' => true,
                'reason' => 'manage_options',
            ];
        }

        if ($limit === 0) {
            return [
                'ok' => true,
                'allowed' => true,
                'bucket' => $bucket,
                'limit' => 0,
                'window' => $window,
                'remaining' => 0,
                'retry_after' => 0,
                'reset_after' => 0,
                'bypassed' => true,
                'reason' => 'unlimited',
            ];
        }

        $identity = ai_article_rate_limit_identity($bucket);
        $key = 'ai_article_rl_' . md5($bucket . '|' . $identity . '|' . $window . '|' . $limit);
        $now = time();
        $arr = get_transient($key);
        if (!is_array($arr)) {
            $arr = [];
        }

        $arr = array_values(array_filter($arr, static function ($ts) use ($now, $window) {
            return ($now - (int)$ts) < $window;
        }));

        $count = count($arr);
        if ($count >= $limit) {
            $oldest = (int)min($arr);
            $retryAfter = max(1, $window - max(0, $now - $oldest));
            set_transient($key, $arr, $window);

            return [
                'ok' => false,
                'allowed' => false,
                'bucket' => $bucket,
                'identity' => $identity,
                'limit' => $limit,
                'window' => $window,
                'used' => $count,
                'remaining' => 0,
                'retry_after' => $retryAfter,
                'reset_after' => $retryAfter,
                'bypassed' => false,
                'reason' => 'rate_limited',
                'key' => $key,
            ];
        }

        $arr[] = $now;
        set_transient($key, $arr, $window);
        $used = count($arr);

        return [
            'ok' => true,
            'allowed' => true,
            'bucket' => $bucket,
            'identity' => $identity,
            'limit' => $limit,
            'window' => $window,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
            'retry_after' => 0,
            'reset_after' => $window,
            'bypassed' => false,
            'reason' => 'allowed',
            'key' => $key,
        ];
    }
}

if (!function_exists('ai_article_rate_allow')) {
    function ai_article_rate_allow(string $bucket = 'generate', int $limit = 10, int $window = 60): bool {
        $check = ai_article_rate_limit_check($bucket, $limit, $window);
        return !empty($check['allowed']);
    }
}

if (!function_exists('ai_article_format_rate_limit_error')) {
    function ai_article_format_rate_limit_error(array $check): string {
        $limit = (int)($check['limit'] ?? 0);
        $window = (int)($check['window'] ?? 0);
        $remaining = (int)($check['remaining'] ?? 0);
        $retryAfter = (int)($check['retry_after'] ?? 0);
        return sprintf(
            'Rate limit: %d request / %ds • Remaining: %d • Retry after: %ds',
            $limit,
            $window,
            $remaining,
            $retryAfter
        );
    }
}

if (!function_exists('ai_article_cache_remember')) {
    /**
     * Cache helper: mevcutsa döner, yoksa closure çalıştırır ve yazar.
     * Hatalı LLM sonuçlarını cache'e yazmaz.
     */
    function ai_article_cache_remember(array $key, int $ttl, callable $producer) {
        // Ortak helper varsa ve başarısız sonuçları cache'e basma kontrolü yapılamıyorsa,
        // bu modül için local transient yolunu kullan.
        $k = 'ai_article_c_' . md5(wp_json_encode($key, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $v = get_transient($k);
        if ($v !== false) {
            return $v;
        }
        $v = $producer();
        if (is_array($v) && !empty($v['ok'])) {
            set_transient($k, $v, $ttl);
        }
        return $v;
    }
}

/* ------------------------------------------------------------
 * LLM Çağrısı
 * ------------------------------------------------------------ */
if (!function_exists('ai_article_call_llm')) {
    /**
     * LLM üretimi — mevcut LLM köprünüze delegasyon
     * @return array{ok:bool, html:string, model?:string, usage?:array, error?:string}
     */
    function ai_article_provider(): string {
        $opt = get_option('ai_article_provider', 'auto');
        $v = is_string($opt) ? strtolower($opt) : 'auto';
        return in_array($v, ['auto','gpt','gemini','claude'], true) ? $v : 'auto';
    }

    function ai_article_call_llm(array $args): array {
        $prompt = (string)($args['prompt'] ?? '');
        $tone   = (string)($args['tone'] ?? 'neutral');
        $lang   = (string)($args['lang'] ?? 'tr');
        $model  = (string)($args['model'] ?? 'auto');

        if ($prompt === '') {
            return ['ok' => false, 'html' => '', 'error' => 'Boş prompt'];
        }

        try {
            /**
             * LLM Köprüsü (Provider-agnostic)
             *
             * 1) 'ai_article/llm_generate' filtresi döndürür:
             *    ['html'=>string, 'model'=>string, 'usage'=>['prompt_tokens'=>..,'completion_tokens'=>..,'total_tokens'=>..,'cost_usd'=>..]]
             *
             * 2) Hiç provider yoksa DEMO fallback döner (stabilite için).
             */
            $llm_args = [
                'prompt' => $prompt,
                'tone'   => $tone,
                'lang'   => $lang,
                'model'  => $model,
                'format' => 'html',
                'task_type' => 'article_generation',
            ];

            if (function_exists('aig_llm_generate')) {
                $result = aig_llm_generate($prompt, $llm_args);
            } else {
                $result = apply_filters('ai_article/llm_generate', $prompt, $llm_args);
            }

            // Provider hata döndürdüyse demo fallback yerine gerçek hata üret (pipeline/parsing bozulmasın)
            if (is_array($result) && !empty($result['error'])) {
                return [
                    'ok'    => false,
                    'html'  => '',
                    'error' => (string)$result['error'],
                    'detail'=> $result['detail'] ?? null,
                ];
            }

            if (is_array($result)) {
                $html = '';
                if (!empty($result['html']) && is_string($result['html'])) {
                    $html = (string)$result['html'];
                } elseif (!empty($result['content']) && is_string($result['content'])) {
                    $html = (string)$result['content'];
                } elseif (!empty($result['text']) && is_string($result['text'])) {
                    $html = (string)$result['text'];
                }

                if ($html !== '') {
                    return [
                        'ok'    => true,
                        'html'  => $html,
                        'model' => (string)($result['model'] ?? $result['provider'] ?? $model),
                        'usage' => (array)($result['usage'] ?? []),
                    ];
                }
            }

            // Demo fallback: minimal HTML (provider yoksa bile sistem kırılmaz)
            $demo = '<p><em>Demo üretim:</em> LLM sağlayıcısı tanımlı değil. '
                  . 'Panelden sağlayıcı entegrasyonu ekleyin veya ai_article/llm_generate filtresini kullanın.</p>';

            return ['ok' => true, 'html' => $demo, 'model' => 'demo-fallback', 'usage' => []];

        } catch (Throwable $e) {
            return ['ok' => false, 'html' => '', 'error' => $e->getMessage()];
        }
    }
}

/* ------------------------------------------------------------
 * Ana: Makale üret (rate limit + cache + log)
 * ------------------------------------------------------------ */
if (!function_exists('ai_article_generate')) {
    /**
     * @param array $args {prompt, tone, lang, category_id, tags[], model}
     * @return array{ok:bool, content:string, meta:array, error?:string}
     */
    function ai_article_generate(array $args): array {
        $prompt = (string)($args['prompt'] ?? '');
        $tone   = (string)($args['tone'] ?? 'neutral');
        $lang   = (string)($args['lang'] ?? 'tr');
        $model  = (string)($args['model'] ?? 'auto');

        if ($prompt === '') {
            return ['ok' => false, 'content' => '', 'meta' => [], 'error' => 'Boş prompt'];
        }

        $rateCheck = ai_article_rate_limit_check('generate');
        if (empty($rateCheck['allowed'])) {
            $message = ai_article_format_rate_limit_error($rateCheck);
            ai_article_log('rate_limit_block', [
                'bucket' => $rateCheck['bucket'] ?? 'generate',
                'identity' => $rateCheck['identity'] ?? null,
                'limit' => $rateCheck['limit'] ?? null,
                'window' => $rateCheck['window'] ?? null,
                'retry_after' => $rateCheck['retry_after'] ?? null,
            ], 'warn');
            return [
                'ok' => false,
                'content' => '',
                'meta' => ['rate_limit' => $rateCheck],
                'error' => $message,
                'error_code' => 'rate_limited',
            ];
        }

        $cacheKey = [
            'v'      => 5,
            'prompt' => substr(md5($prompt), 0, 16),
            'tone'   => $tone,
            'lang'   => $lang,
            'model'  => $model,
        ];

        $resp = ai_article_cache_remember($cacheKey, 120, function () use ($args) {
            return ai_article_call_llm($args);
        });

        if (!is_array($resp) || empty($resp['ok'])) {
            $err = is_array($resp) ? ($resp['error'] ?? 'LLM hata') : 'LLM bilinmeyen hata';
            ai_article_log('generate_fail', ['error' => $err], 'error');
            return ['ok' => false, 'content' => '', 'meta' => [], 'error' => $err];
        }

        $html  = (string)$resp['html'];
        $meta  = [
            'ai_source'      => (string)($resp['model'] ?? $model),
            'ai_prompt'      => $prompt,
            'ai_output_hash' => md5($html),
            'ai_lang'        => $lang,
            'usage'          => (array)($resp['usage'] ?? []),
            'rate_limit'     => $rateCheck,
        ];

        // Token/Cost Monitor (V4)
        if (!defined('AIG_OPT_USAGE')) define('AIG_OPT_USAGE', 'ai_article_generator_usage_totals');
        $u = (array)($meta['usage'] ?? []);
        $tot = get_option(AIG_OPT_USAGE, []);
        if (!is_array($tot)) $tot = [];
        $tot['calls'] = isset($tot['calls']) ? ((int)$tot['calls'] + 1) : 1;
        $tot['prompt_tokens'] = (int)($tot['prompt_tokens'] ?? 0) + (int)($u['prompt_tokens'] ?? 0);
        $tot['completion_tokens'] = (int)($tot['completion_tokens'] ?? 0) + (int)($u['completion_tokens'] ?? 0);
        $tot['total_tokens'] = (int)($tot['total_tokens'] ?? 0) + (int)($u['total_tokens'] ?? 0);
        $tot['cost_usd'] = (float)($tot['cost_usd'] ?? 0) + (float)($u['cost_usd'] ?? 0);
        $tot['last_at'] = current_time('mysql');
        update_option(AIG_OPT_USAGE, $tot, false);

        ai_article_log('generate_ok', ['model' => $meta['ai_source'], 'hash' => $meta['ai_output_hash']]);

        return ['ok' => true, 'content' => $html, 'meta' => $meta];
    }
}


function ai_article_sanitize_html_safeplus(string $html): string {
    // Minimal XSS hardening for admin preview / draft save.
    $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
    $html = preg_replace('#<iframe\b[^>]*>.*?</iframe>#is', '', $html);
    $html = preg_replace('#on\w+\s*=\s*([\'\"]).*?\1#is', '', $html);
    return (string)$html;
}

/* ------------------------------------------------------------
 * Similarity Guard (V4)
 * - hızlı shingle hash + Jaccard
 * - son N çıktıyı option içinde tutar (db)
 * ------------------------------------------------------------ */
if (!defined('AIG_OPT_SIM_INDEX')) define('AIG_OPT_SIM_INDEX', 'ai_article_generator_similarity_index');

if (!function_exists('aig_similarity_shingles')) {
    function aig_similarity_shingles(string $text, int $k = 7, int $max = 800): array {
        $t = mb_strtolower(wp_strip_all_tags($text));
        $t = preg_replace('/\s+/u', ' ', trim($t));
        if ($t === '') return [];
        // karakter tabanlı shingles (dil bağımsız)
        $len = mb_strlen($t);
        $set = [];
        for ($i=0; $i<=($len-$k); $i++) {
            $sh = mb_substr($t, $i, $k);
            $h  = substr(sha1($sh), 0, 12);
            $set[$h] = 1;
            if (count($set) >= $max) break;
        }
        return array_keys($set);
    }
}

if (!function_exists('aig_similarity_jaccard')) {
    function aig_similarity_jaccard(array $a, array $b): float {
        if (!$a || !$b) return 0.0;
        $setA = array_fill_keys($a, 1);
        $inter = 0;
        foreach ($b as $h) {
            if (isset($setA[$h])) $inter++;
        }
        $union = count($a) + count($b) - $inter;
        return $union > 0 ? ($inter / $union) : 0.0;
    }
}

if (!function_exists('aig_similarity_check_and_store')) {
    /**
     * @return array{ok:bool, best:float, hit?:array, index_size:int}
     */
    function aig_similarity_check_and_store(string $text, float $threshold = 0.80, int $keep = 120): array {
        $sh = aig_similarity_shingles($text);
        $index = get_option(AIG_OPT_SIM_INDEX, []);
        if (!is_array($index)) $index = [];

        $best = 0.0;
        $best_hit = null;

        foreach ($index as $row) {
            if (!is_array($row) || empty($row['sh'])) continue;
            $score = aig_similarity_jaccard($sh, (array)$row['sh']);
            if ($score > $best) { $best = $score; $best_hit = $row; }
        }

        // store current
        $index[] = [
            'ts' => time(),
            'sh' => $sh,
            'meta' => [
                'hash' => substr(sha1($text), 0, 16),
            ],
        ];
        // trim
        if (count($index) > $keep) $index = array_slice($index, -$keep);
        update_option(AIG_OPT_SIM_INDEX, $index, false);

        return [
            'ok' => ($best < $threshold),
            'best' => $best,
            'hit' => $best_hit,
            'index_size' => count($index),
        ];
    }
}

