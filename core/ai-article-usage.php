<?php
/**
 * AI Article Generator
 * Usage Tracking Helpers
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('aig_usage_base_dir')) {
    /**
     * Return usage storage directory.
     *
     * @return string
     */
    function aig_usage_base_dir(): string
    {
        if (defined('AIG_STORAGE_DIR')) {
            return rtrim(AIG_STORAGE_DIR, '/\\') . '/usage';
        }

        if (defined('AIG_MODULE_DIR')) {
            return rtrim(AIG_MODULE_DIR, '/\\') . '/storage/usage';
        }

        return __DIR__ . '/../storage/usage';
    }
}

if (!function_exists('aig_usage_ensure_dir')) {
    /**
     * Ensure usage directory exists.
     *
     * @return bool
     */
    function aig_usage_ensure_dir(): bool
    {
        $dir = aig_usage_base_dir();

        if (is_dir($dir)) {
            return true;
        }

        if (function_exists('wp_mkdir_p')) {
            return (bool) wp_mkdir_p($dir);
        }

        return @mkdir($dir, 0775, true);
    }
}

if (!function_exists('aig_usage_daily_file')) {
    /**
     * Daily usage JSONL file path.
     *
     * @param string|null $date
     * @return string
     */
    function aig_usage_daily_file(?string $date = null): string
    {
        $date = $date ?: gmdate('Y-m-d');
        return rtrim(aig_usage_base_dir(), '/\\') . '/' . $date . '.jsonl';
    }
}

if (!function_exists('aig_usage_append')) {
    /**
     * Append one usage event as JSONL.
     *
     * @param array $event
     * @return bool
     */
    function aig_usage_append(array $event): bool
    {
        if (!aig_usage_ensure_dir()) {
            return false;
        }

        $payload = [
            'ts' => gmdate('c'),
            'provider' => (string) ($event['provider'] ?? ''),
            'model' => (string) ($event['model'] ?? ''),
            'task' => (string) ($event['task'] ?? ''),
            'prompt_tokens' => (int) ($event['prompt_tokens'] ?? 0),
            'completion_tokens' => (int) ($event['completion_tokens'] ?? 0),
            'total_tokens' => (int) ($event['total_tokens'] ?? 0),
            'estimated_cost' => isset($event['estimated_cost']) ? (float) $event['estimated_cost'] : 0.0,
            'meta' => is_array($event['meta'] ?? null) ? $event['meta'] : [],
        ];

        $line = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($line === false) {
            return false;
        }

        $line .= PHP_EOL;

        return @file_put_contents(aig_usage_daily_file(), $line, FILE_APPEND | LOCK_EX) !== false;
    }
}

if (!function_exists('aig_usage_record_from_usage_block')) {
    /**
     * Record usage from normalized usage block.
     *
     * @param string $task
     * @param string $provider
     * @param string $model
     * @param array  $usage
     * @param array  $meta
     * @return bool
     */
    function aig_usage_record_from_usage_block(
        string $task,
        string $provider,
        string $model,
        array $usage,
        array $meta = []
    ): bool {
        return aig_usage_append([
            'task' => $task,
            'provider' => $provider,
            'model' => $model,
            'prompt_tokens' => (int) ($usage['prompt_tokens'] ?? 0),
            'completion_tokens' => (int) ($usage['completion_tokens'] ?? 0),
            'total_tokens' => (int) ($usage['total_tokens'] ?? 0),
            'estimated_cost' => isset($usage['estimated_cost']) ? (float) $usage['estimated_cost'] : 0.0,
            'meta' => $meta,
        ]);
    }
}

if (!function_exists('aig_usage_read_day')) {
    /**
     * Read one day usage JSONL file.
     *
     * @param string|null $date
     * @return array
     */
    function aig_usage_read_day(?string $date = null): array
    {
        $file = aig_usage_daily_file($date);

        if (!file_exists($file)) {
            return [];
        }

        $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return [];
        }

        $rows = [];

        foreach ($lines as $line) {
            $row = json_decode((string) $line, true);
            if (is_array($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

if (!function_exists('aig_usage_summarize_day')) {
    /**
     * Summarize one day usage.
     *
     * @param string|null $date
     * @return array
     */
    function aig_usage_summarize_day(?string $date = null): array
    {
        $rows = aig_usage_read_day($date);

        $summary = [
            'count' => 0,
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
            'estimated_cost' => 0.0,
            'by_provider' => [],
            'by_task' => [],
        ];

        foreach ($rows as $row) {
            $summary['count']++;
            $summary['prompt_tokens'] += (int) ($row['prompt_tokens'] ?? 0);
            $summary['completion_tokens'] += (int) ($row['completion_tokens'] ?? 0);
            $summary['total_tokens'] += (int) ($row['total_tokens'] ?? 0);
            $summary['estimated_cost'] += (float) ($row['estimated_cost'] ?? 0);

            $provider = (string) ($row['provider'] ?? '');
            $task = (string) ($row['task'] ?? '');

            if ($provider !== '') {
                if (!isset($summary['by_provider'][$provider])) {
                    $summary['by_provider'][$provider] = [
                        'count' => 0,
                        'total_tokens' => 0,
                    ];
                }

                $summary['by_provider'][$provider]['count']++;
                $summary['by_provider'][$provider]['total_tokens'] += (int) ($row['total_tokens'] ?? 0);
            }

            if ($task !== '') {
                if (!isset($summary['by_task'][$task])) {
                    $summary['by_task'][$task] = [
                        'count' => 0,
                        'total_tokens' => 0,
                    ];
                }

                $summary['by_task'][$task]['count']++;
                $summary['by_task'][$task]['total_tokens'] += (int) ($row['total_tokens'] ?? 0);
            }
        }

        return $summary;
    }
}