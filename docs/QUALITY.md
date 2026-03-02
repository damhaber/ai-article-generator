# QUALITY — Kalite Kapısı

Kalite skoru deterministik sinyallerle hesaplanır ve pipeline çıktısına eklenir.

## Sinyaller
- word_count
- section_count
- keyword_density
- repetition_ratio
- meta_description_len

## Politika
- `quality >= 75`: yayınlanabilir (varsayılan)
- `quality < 75`: Auto-Improve (AŞAMA 3)

## Not
Bu skor “tek başına” mükemmellik değildir; amaç otomatik kontrol kapısı oluşturmaktır.
