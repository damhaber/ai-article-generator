<?php
/**
 * AI Article Generator — JSONL Logger
 */
if (!defined('ABSPATH')) { exit; }

if (!defined('AIG_LOG_FILE')) {
  $log_dir = wp_normalize_path(dirname(__FILE__) . '/../logs');
  if (!file_exists($log_dir)) { wp_mkdir_p($log_dir); }
  define('AIG_LOG_FILE', wp_normalize_path($log_dir . '/ai-article-generator.log'));
}

function aig_log_write($level, $message, $context = []){
  $row = [
    'ts'   => current_time('mysql'),
    'lvl'  => strtoupper($level),
    'msg'  => is_string($message) ? $message : wp_json_encode($message),
    'ctx'  => $context,
  ];
  $line = wp_json_encode($row, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . "\n";
  $fh = @fopen(AIG_LOG_FILE, 'ab');
  if ($fh) { fwrite($fh, $line); fclose($fh); return true; }
  return false;
}

function aig_log_tail($max = 120){
  $max = max(1, intval($max));
  if (!file_exists(AIG_LOG_FILE)) return [];
  $fp = fopen(AIG_LOG_FILE, 'rb');
  if (!$fp) return [];
  $pos = -1; $lines = [];
  $eof = ''; fseek($fp, $pos, SEEK_END);
  while ($max > 0) {
    $char = fgetc($fp);
    if ($char === "\n") { $line = strrev($eof); $lines[] = rtrim($line, "\r\n"); $eof = ''; $max--; }
    $pos--;
    if (fseek($fp, $pos, SEEK_END) === -1) { $line = strrev($eof); if ($line!=='') $lines[] = rtrim($line, "\r\n"); break; }
    $eof .= $char;
  }
  fclose($fp);
  return array_reverse($lines);
}

function aig_log_clear(){
  if (file_exists(AIG_LOG_FILE)) {
    $ok = @file_put_contents(AIG_LOG_FILE, '');
    return ($ok !== false);
  }
  return true;
}


