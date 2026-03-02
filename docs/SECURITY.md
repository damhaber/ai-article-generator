# SECURITY — AI Article Generator

## Temel Kurallar
1) Admin işlemleri **capability** kontrolü ister: `manage_options`
2) Her AJAX/REST çağrısı **nonce** ile doğrulanır
3) Her input sanitize edilir; her output escape edilir
4) admin-ajax yanıtına HTML/notice karışması engellenir

## Nonce Standardı
UI -> AJAX:
- `security: AIG.nonce`

PHP tarafı:
- `check_ajax_referer('aig_admin', 'security')`
- uyumluluk için gerekirse fallback: `'nonce'`

## Veri Hijyeni
- API anahtarları WP options veya modül storage’da saklanır.
- Log’lara **anahtarın kendisi** yazılmaz (sadece uzunluk gibi masum metrikler).

## Rate Limit / Abuse
AŞAMA 3’te eklenecek:
- günlük üretim limiti
- ip/user bazlı rate limit
