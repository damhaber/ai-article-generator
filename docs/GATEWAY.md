# `docs/GATEWAY.md`

```markdown
# AI Article Generator – Gateway
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/GATEWAY.md

---

## 1. Amaç

Gateway katmanı, router tarafından seçilen provider/model kararını gerçek AI çağrısına dönüştüren katmandır.

Gateway’in temel amacı:
- provider farklarını üst katmanlardan gizlemek
- retry ve timeout yönetmek
- response standardı üretmek
- usage alanlarını normalize etmek
- hata tiplerini ortak biçime çevirmek

Gateway, service ve pipeline katmanını dış AI sistemlerinin düzensizliklerinden korur.

---

## 2. Mimari Konum

Genel akış:

Service
↓
Pipeline
↓
Router
↓
Gateway
↓
Provider
↓
LLM Model

Gateway, router ile provider arasında duran “uygulama güvenlik ve uyumluluk katmanı”dır.

---

## 3. Gateway’in Temel Görevleri

Gateway şu işleri yapar:
- router kararını alır
- provider örneğini registry üzerinden bulur
- request options oluşturur
- timeout uygular
- retry yapar
- provider cevabını normalize eder
- usage çıkarır
- hata sınıflandırması yapar
- failover için okunabilir meta döner

Gateway şunları yapmaz:
- haber toplamaz
- topic belirlemez
- outline üretmez
- prompt yazarlığı yapmaz
- panel UI yönetmez

---

## 4. Gateway Dosyaları ile İlişki

Ana dosyalar:
- core/ai-article-gateway.php
- core/ai-article-router.php
- core/ai-article-provider-registry.php
- core/providers/*
- core/ai-article-llm.php

### İlişki
- Router karar verir
- Gateway uygular
- Provider çağırır
- LLM utility katmanı gerektiğinde gateway’i daha yüksek seviyeli yardımcıya dönüştürür

---

## 5. Gateway Girdileri

Gateway tipik olarak şu bilgileri alır:
- task type
- provider
- model
- prompt veya messages
- options
- timeout
- retry policy
- max tokens
- temperature
- context meta

Örnek mantıksal input:

```php
[
  'task' => 'article_generate',
  'provider' => 'openrouter',
  'model' => 'anthropic/claude-x',
  'messages' => [...],
  'options' => [
    'temperature' => 0.7,
    'max_tokens' => 2200,
    'timeout' => 45,
  ],
]
Kural

Gateway’e gelen veri yarım veya belirsiz olmamalıdır.
Özellikle prompt/messages alanı net olmalıdır.

6. Gateway Çıktısı

Gateway tek bir normalize response üretmelidir.

Örnek başarı response’u
[
  'ok' => true,
  'content' => 'generated text',
  'provider' => 'openrouter',
  'model' => 'anthropic/claude-x',
  'finish_reason' => 'stop',
  'usage' => [
    'prompt_tokens' => 1024,
    'completion_tokens' => 890,
    'total_tokens' => 1914,
  ],
  'meta' => [
    'duration_ms' => 4120,
    'retries' => 0,
    'fallback_used' => false,
  ],
  'raw' => [],
  'error' => null,
]
Örnek hata response’u
[
  'ok' => false,
  'content' => '',
  'provider' => 'groq',
  'model' => 'llama-3.3-70b',
  'usage' => [],
  'meta' => [
    'duration_ms' => 30010,
    'retries' => 1,
    'fallback_used' => false,
  ],
  'raw' => [],
  'error' => [
    'code' => 'timeout',
    'message' => 'Provider request timed out',
    'retryable' => true,
  ],
]
Kural

Üst katman raw içeriğe muhtaç olmadan karar verebilmelidir.

7. Retry Politikası

Gateway retry yönetmelidir; provider sınıfları içinde dağınık retry mantığı olmamalıdır.

Retry yapılabilecek durumlar

geçici ağ hatası

timeout

5xx sunucu hataları

retryable rate limit senaryoları

geçici provider unavailable hataları

Retry yapılmaması gereken durumlar

auth error

invalid config

disabled provider

malformed request

kalıcı parse hataları

Kural

Retry sayısı sınırlı ve açık olmalıdır.
Sonsuz veya gizli retry yasaktır.

8. Timeout Politikası

Gateway timeout’u merkezi uygular.

Timeout şu faktörlere göre değişebilir:

task type

provider

model

content length

streaming/non-streaming

Örnek

title_generate → düşük timeout

article_generate → daha yüksek timeout

rewrite long-form → orta/yüksek timeout

Kural

Timeout sabiti tüm task’lere kör uygulanmamalıdır.

9. Hata Standardizasyonu

Provider’lar farklı hata biçimleri döndürebilir.
Gateway bunları ortak hata kodlarına çevirmelidir.

Örnek ortak hata kodları:

auth_error

timeout

http_error

rate_limited

invalid_response

provider_unavailable

missing_provider

disabled_provider

bad_request

parse_error

Amaç

Failover ve selftest mantığının tek dil konuşması.

10. Usage Standardizasyonu

Farklı provider’lar usage alanını farklı isimlerle döndürür.
Gateway bunu ortak şemaya çevirir.

Ortak usage yapısı:

[
  'prompt_tokens' => 0,
  'completion_tokens' => 0,
  'total_tokens' => 0,
]

Opsiyonel alanlar:

cached_tokens

reasoning_tokens

billable_units

Kural

Usage bilgisi yoksa boş dizi yerine kontrollü varsayılan yapı tercih edilmelidir.

11. Fallback ile İlişki

Gateway, failover mantığının bütününe sahip olmak zorunda değildir; ama failover için gerekli işaretleri üretmelidir.

Örnek meta alanları:

retries

last_error_code

provider_attempts

fallback_used

fallback_from

fallback_to

Bu bilgiler pipeline veya service tarafından daha üst raporlamada kullanılabilir.

12. Güvenlik İlkeleri

Gateway katmanı güvenlik açısından kritik bir noktadır.

Uygulanması gerekenler

API anahtarını açık loglamama

request body içindeki hassas alanları redaction ile loglama

timeout / retry abuse’ını önleme

disabled provider bypass’ına izin vermeme

Yapılmaması gerekenler

raw API key dump

tam header dump

kullanıcı içeriklerini kontrolsüz loglama

13. Gözlemlenebilirlik

Gateway iyi log üretmelidir.

Önerilen log alanları:

task

provider

model

start

duration

success

retries

error_code

token usage

Bu sayede:

yavaş model tespiti

bozuk provider tespiti

maliyet takibi

failover yoğunluğu

izlenebilir hale gelir.

14. Gateway ve Prompt Contract

Gateway prompt tasarlamaz.
Ama gateway’e gelen içerik açık olmalıdır.

Desteklenebilecek iki ana giriş tipi:

prompt

messages

Kural

Pipeline hangi formu kullanıyorsa gateway onu açık şekilde almalı ve provider’a doğru map etmelidir.

Yarım payload kabul edilmemelidir.

Yanlış örnek:

context pack var

outline var

ama provider’a gönderilecek gerçek prompt yok

Bu durumda gateway’in yapacağı şey fallback büyütmek değil; hatayı görünür kılmaktır.

15. Gateway ve Streaming

Streaming destekleniyorsa bunun ayrı contract’ı olmalıdır.

İki mod

blocking generate

streaming generate

Bunların response ve timeout mantıkları ayrılmalıdır.

Bu modülde ilk hedef blocking akışın stabil olmasıdır.
Streaming daha sonra kontrollü eklenmelidir.

16. Selftest ile İlişki

Selftest-service gateway için şu kontrolleri yapabilmelidir:

gateway fonksiyonları mevcut mu

registry üzerinden provider bulunabiliyor mu

test prompt ile temel generate mümkün mü

normalize response doğru shape’te mi

timeout/retry alanları okunabiliyor mu

Kural

Gateway testi yalnızca “fonksiyon var mı” değil, “gerçek contract sağlıklı mı” sorusunu cevaplamalıdır.

17. Legacy Sorunlar

Geçmiş sürümlerde şu sorunlar görülebilir:

provider sınıfı doğrudan service içinde çağrılmış olabilir

gateway atlanmış olabilir

usage normalize edilmemiş olabilir

hata tipleri tekilleştirilmemiş olabilir

Yeni resmi yapı:

service → pipeline → router → gateway → provider

olmalıdır.

Gateway atlanmamalıdır.

18. Gelecek Genişleme Kuralları

Yeni provider/model desteği eklenirken gateway şu sorulara cevap verebilmelidir:

bu provider blocking generate destekliyor mu?

usage döndürüyor mu?

retry güvenli mi?

timeout profili ne olmalı?

messages mi prompt mu kullanıyor?

hata map’i hazır mı?

Hazır değilse yeni provider “eklenmiş” sayılmaz.

19. Bu Belgenin Rolü

Bu belge, gateway katmanının resmi çalışma çerçevesidir.

Yeni geliştirmelerde şu sorular burada cevap bulmalıdır:

gateway neden var?

provider’dan farkı ne?

retry/timeout nerede yönetilir?

normalize response nasıl görünür?

failover için hangi alanlar gerekir?

20. Son Hüküm

Gateway katmanı, AI Article Generator modülünün güvenli ve tutarlı AI çağrı kapısıdır.

Doğru gateway tasarımı olmadan:

provider çeşitliliği kaosa dönüşür

hata yönetimi dağılır

usage takibi bozulur

failover kör çalışır

Doğru gateway tasarımı ile:

sistem daha kararlı olur

çoklu sağlayıcı sürdürülebilir hale gelir

pipeline ve service katmanları sadeleşir

modül üretim seviyesine yaklaşır