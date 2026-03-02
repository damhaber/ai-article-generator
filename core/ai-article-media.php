<?php
/**
 * AI Article Media — Pexels probe & search
 */
if (!defined('ABSPATH')) { exit; }

// Ayar anahtarı
if (!defined('AIG_OPT_API_KEYS')) define('AIG_OPT_API_KEYS', 'ai_article_generator_api_keys');

function aig_get_pexels_key(){
  $opt = get_option(AIG_OPT_API_KEYS, []);
  $key = is_array($opt) && !empty($opt['pexels']) ? trim($opt['pexels']) : '';
  return $key;
}

function aig_media_probe(){
  $key = aig_get_pexels_key();
  if (!$key) {
    aig_log_write('WARN', 'Pexels anahtarı yok');
    return ['ok'=>false, 'msg'=>'pexels_key_missing'];
  }
  // Plan gereği: ilk adım yalnız DB okuma ve OK döndürme
  return ['ok'=>true, 'provider'=>'pexels'];
}

function aig_media_fetch($type, $q, $count = 6, $size = 'medium'){
  $type  = strtolower($type);
  $q     = $q ?: 'news';
  $count = max(1, min(20, intval($count)));

  $key = aig_get_pexels_key();
  if (!$key) { return ['ok'=>false,'msg'=>'pexels_key_missing']; }

  $endpoint = ($type === 'video') ? 'https://api.pexels.com/videos/search' : 'https://api.pexels.com/v1/search';
  $args = [ 'headers' => [ 'Authorization' => $key ], 'timeout' => 15 ];
  $url  = add_query_arg([
    'query' => rawurlencode($q),
    'per_page' => $count,
    'orientation' => 'landscape'
  ], $endpoint);

  $resp = wp_remote_get($url, $args);
  if (is_wp_error($resp)){
    aig_log_write('ERROR','pexels_request_error',[ 'error' => $resp->get_error_message() ]);
    return ['ok'=>false,'msg'=>'request_failed','detail'=>$resp->get_error_message()];
  }
  $code = wp_remote_retrieve_response_code($resp);
  $body = wp_remote_retrieve_body($resp);
  if ($code < 200 || $code >= 300){
    aig_log_write('ERROR','pexels_http_error',[ 'code'=>$code, 'body'=>$body ]);
    return ['ok'=>false,'msg'=>'http_error','detail'=>$code];
  }

  $json = json_decode($body, true);
  if (!is_array($json)){
    aig_log_write('ERROR','pexels_json_error',[ 'body'=>$body ]);
    return ['ok'=>false,'msg'=>'json_error'];
  }

  $items = [];
  if ($type === 'video'){
    $videos = isset($json['videos']) && is_array($json['videos']) ? $json['videos'] : [];
    foreach ($videos as $v){
      $file = null; $quality = null; $mime = null; $width = null; $height = null; $duration = isset($v['duration']) ? intval($v['duration']) : null;
      if (!empty($v['video_files'])){
        // Kalite önceliği: hd > sd > tiny
        usort($v['video_files'], function($a,$b){return strcmp($a['quality']??'', $b['quality']??'');});
        $vf = $v['video_files'][0];
        $file = $vf['link'] ?? '';
        $quality = $vf['quality'] ?? '';
        $mime = $vf['file_type'] ?? 'video/mp4';
        $width = $vf['width'] ?? null; $height = $vf['height'] ?? null;
      }
      if ($file){
        $items[] = [
          'type' => 'video',
          'mime' => $mime,
          'quality' => $quality,
          'width' => $width,
          'height'=> $height,
          'duration' => $duration,
          'file_url' => $file,
        ];
      }
    }
  } else {
    $photos = isset($json['photos']) && is_array($json['photos']) ? $json['photos'] : [];
    foreach ($photos as $p){
      $src = isset($p['src']) ? (array)$p['src'] : [];
      $file = $src['medium'] ?? ($src['large'] ?? ($src['original'] ?? ''));
      if ($size==='original' && !empty($src['original'])) $file = $src['original'];
      elseif ($size==='large' && !empty($src['large']))     $file = $src['large'];
      $items[] = [
        'type' => 'image',
        'mime' => 'image/jpeg',
        'width' => $p['width'] ?? null,
        'height'=> $p['height'] ?? null,
        'file_url' => $file,
      ];
    }
  }

  aig_log_write('INFO','pexels_fetch_ok',[ 'q'=>$q, 'type'=>$type, 'count'=>count($items) ]);
  return ['ok'=>true, 'data'=>['items'=>$items]];
}


