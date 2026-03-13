# AI Article Generator – Providers
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/PROVIDERS.md

---

## 1. Amaç

Bu belge, AI Article Generator modülündeki provider mimarisini tanımlar.

Bu modül tek bir LLM sağlayıcısına bağlı kalmamalıdır.  
Amaç:

- çoklu sağlayıcı desteği
- ortak bir provider sözleşmesi
- router/gateway ile uyumlu çalışma
- failover yapılabilir yapı
- model çeşitliliği
- maliyet / kalite / hız dengesi

Provider katmanı, dış AI servisleriyle konuşan ama üst katmanları bu detaylardan koruyan soyutlama katmanıdır.

---

## 2. Desteklenen Provider Dosyaları

Provider dosyaları:

- core/providers/provider-interface.php
- core/providers/provider-base-openai-compat.php
- core/providers/provider-openai.php
- core/providers/provider-groq.php
- core/providers/provider-gemini.php
- core/providers/provider-deepseek.php
- core/providers/provider-mistral.php
- core/providers/provider-ollama.php
- core/providers/provider-openrouter.php

Registry dosyası:

- core/ai-article-provider-registry.php

Yapılandırma dosyaları:

- storage/providers.json
- storage/models.json

---

## 3. Provider Katmanının Rolü

Provider katmanı şunları yapar:

- dış API ile konuşur
- request body kurar
- auth header ekler
- sağlayıcıya özel endpoint kullanır
- response içinden içerik çıkarır
- usage alanlarını normalize eder
- hata bilgisini ortak yapıya dönüştürür

Provider katmanı şunları yapmaz:

- haber toplamaz
- topic belirlemez
- makale outline kurmaz
- SEO üretmez
- panel davranışı belirlemez
- route kararı vermez

Bu sınır çok önemlidir.

---

## 4. Provider Interface

Tüm providerlar ortak bir sözleşmeyi uygulamalıdır.

Bu sözleşme en azından şu mantıksal yetenekleri içermelidir:

- generate
- chat
- embeddings (opsiyonel ama tanımlı)
- ping / health (önerilir)
- normalize error
- normalize usage

### Örnek mantıksal interface

```php
interface AIG_Provider_Interface
{
    public function get_key(): string;
    public function get_label(): string;
    public function is_available(): bool;
    public function generate(array $payload): array;
    public function chat(array $messages, array $options = []): array;
    public function embeddings(array $input, array $options = []): array;
}

Gerçek imza projeye göre değişebilir; ama mantık değişmemelidir.

Kural

Interface olmadan provider çoğaldıkça sistem dağılır.
Her provider aynı temel contract’ı uygulamalıdır.

5. Base OpenAI Compatible Katmanı

Dosya:

core/providers/provider-base-openai-compat.php

Bu dosyanın amacı, OpenAI benzeri API tasarımını kullanan sağlayıcılar için ortak tekrarları azaltmaktır.

Örnek sağlayıcılar:

OpenAI

Groq

DeepSeek

OpenRouter

bazı Mistral/OpenAI-compatible uçları

Bu temel sınıfın işi

ortak HTTP request yapısı

authorization header

messages tabanlı body

temperature/max_tokens map etme

standard response extraction

usage çıkarımı

ortak hata handling

Yapmaması gereken

route seçimi

task yorumlama

article/rewrite business logic

6. Provider Registry

Ana dosya:

core/ai-article-provider-registry.php

Bu dosya provider sisteminin kayıt merkezi olmalıdır.

Registry’nin görevleri

mevcut provider sınıflarını kaydetmek

aktif provider listesini döndürmek

belirli bir provider örneği oluşturmak

provider anahtarları ile dosya/sınıf ilişkisini yönetmek

availability kontrolünde merkezi referans olmak

Registry’nin yapmaması gereken

maliyet puanı hesaplamak

kalite puanı belirlemek

route kararı vermek

UI panel mantığını taşımak

Kritik not

Registry ana loader tarafından garantili biçimde yüklenmelidir.
Yarım bağlı registry, sistemde “provider var ama görünmüyor” gibi sorunlar üretir.

7. Provider Kimlikleri

Provider key’leri sistem genelinde sabit ve tutarlı olmalıdır.

Örnek key listesi:

openai

groq

gemini

deepseek

mistral

ollama

openrouter

Kural

Bir provider’ın panelde, router’da, storage dosyasında ve runtime içinde farklı isimlerle anılması yasaktır.

Yanlış örnek:

panelde deep_seek

storage’da deepseek

registry’de provider-deepseek

runtime’da ds

Bu tür ayrışmalar debug kabusuna dönüşür.

8. Provider Availability

Bir provider’ın “mevcut” olması, yalnızca dosyasının var olması demek değildir.

Bir provider şu koşullarda available sayılmalıdır:

sınıf yüklenmiş olmalı

gerekli API anahtarı veya bağlantı ayarı mevcut olmalı

provider disable edilmemiş olmalı

health/ping başarısız durumda olmamalı

kritik endpoint bilgisi hazır olmalı

Availability check alanları

dosya yüklü mü

sınıf var mı

config var mı

provider enabled mi

API anahtarı boş mu

test isteği başarısız mı

9. Provider Yapılandırması

Ana dosya:

storage/providers.json

Bu dosyada ideal olarak şunlar tutulmalıdır:

provider enabled/disabled

default model

api key reference

base url

timeout

retry limit

health status

capabilities

notes

Örnek mantıksal yapı
{
  "openai": {
    "enabled": true,
    "api_key": "env_or_saved_key",
    "default_model": "gpt-4.1-mini",
    "timeout": 45,
    "retry": 1
  },
  "groq": {
    "enabled": true,
    "api_key": "env_or_saved_key",
    "default_model": "llama-3.3-70b",
    "timeout": 30,
    "retry": 1
  }
}

Gerçek anahtar isimleri proje standardına göre kesinleştirilmelidir.

10. Model Yapılandırması

Ana dosya:

storage/models.json

Bu dosyada model bazlı özellikler saklanmalıdır.

Örnek alanlar:

provider

model key

display label

max token

supports json output

supports tool use

supports streaming

cost class

latency class

quality class

preferred tasks

Amaç

Router’ın ve panelin sabitlere gömülmeden model seçebilmesi.

11. Provider Response Standardı

Provider katmanından dönen veri tek biçime indirilmelidir.

Örnek normalize response:

[
  'ok' => true,
  'content' => 'generated text',
  'provider' => 'openai',
  'model' => 'gpt-4.1-mini',
  'finish_reason' => 'stop',
  'usage' => [
    'prompt_tokens' => 1200,
    'completion_tokens' => 950,
    'total_tokens' => 2150,
  ],
  'raw' => [],
  'error' => null,
]
Başarısız durumda
[
  'ok' => false,
  'content' => '',
  'provider' => 'groq',
  'model' => 'llama-3.3-70b',
  'usage' => [],
  'raw' => [],
  'error' => [
    'code' => 'http_error',
    'message' => 'Timeout while calling provider',
    'retryable' => true,
  ],
]
Kural

Üst katman provider-specific response okumamalıdır.
Normalize edilmiş ortak response zorunludur.

12. Provider Capability Profili

Her provider aynı şeyi aynı kalitede yapmaz.

Bu yüzden provider veya model bazında capability profili tutulmalıdır.

Örnek yetenek alanları:

article_generate

rewrite

seo_generate

title_generate

summary_generate

faq_generate

schema_generate

embeddings

long_context

multilingual

Amaç

Router’ın kör seçim yapmaması.

13. Task Bazlı Provider Kullanımı

Tüm task’ler için aynı provider en iyi çözüm olmayabilir.

Örnek:

article_generate → kalite yüksek model

rewrite → dil akıcılığı güçlü model

seo_generate → hızlı ve ekonomik model

summary_generate → düşük maliyetli hızlı model

embeddings → özel embedding modeli

Kural

Provider seçimi task duyarlı olmalıdır.

14. Provider Hata Türleri

Provider katmanında görülebilecek temel hata türleri:

auth_error

http_error

timeout

rate_limited

invalid_response

unavailable

missing_config

disabled_provider

parse_error

Bu hata tipleri ortak kodlara indirgenmelidir.

Neden?

Failover sistemi ve gateway bu ortak kodları kullanarak karar verir.

15. Loglama İlkesi

Provider çağrılarında loglanması yararlı alanlar:

provider

model

task

start time

end time

duration

success/failure

retry count

error code

usage summary

Loglanmaması gerekenler

düz API anahtarı

kullanıcıya ait hassas içeriklerin kontrolsüz dump’ı

tam prompt’ların güvenliksiz log’u

Prompt loglanacaksa redacted veya debug moduna bağlı olmalıdır.

16. Selftest ile İlişki

Provider sistemi selftest tarafından doğrulanmalıdır.

Kontrol örnekleri:

provider dosyası var mı

provider sınıfı yüklenmiş mi

registry provider’ı görüyor mu

providers.json okunuyor mu

aktif provider’da gerekli config var mı

basit ping/generate testi yapılabiliyor mu

Kural

Selftest yanlış fonksiyon/sabit kontrol ederek false negative üretmemelidir.

17. Legacy ve Yeni Yapı İlişkisi

Eski kodlarda tek provider veya sabit model mantığı kalmış olabilir.
Yeni resmi yapı şu olmalıdır:

provider registry merkezi

router karar verici

gateway normalize edici

provider sınıfı dış API uzmanı

Legacy davranışlar yalnızca uyumluluk amaçlı adapter olarak kalabilir.

18. Gelecek Genişleme Kuralı

Yeni provider ekleneceğinde şu sıra izlenmelidir:

provider dosyası eklenir

interface uyumu sağlanır

registry’ye kaydedilir

providers.json / models.json güncellenir

selftest’e eklenir

docs güncellenir

router capability bilgisi tamamlanır

Kural

Yeni provider eklemek “yalnızca bir PHP dosyası eklemek” değildir.

19. Bu Belgenin Rolü

Bu belge provider sisteminin kalıcı referansıdır.

Yeni geliştirmelerde şu soruların cevabı burada bulunmalıdır:

provider nasıl eklenir?

provider katmanı ne yapar?

registry ne işe yarar?

ortak response shape nedir?

availability nasıl ölçülür?

router/gateway ile ilişkisi nedir?

20. Son Hüküm

Provider katmanı, AI Article Generator modülünün dış dünya ile konuşan yüzüdür.

Ama bu yüz:

business logic sahibi değildir

editoryal karar verici değildir

panel katmanı değildir

Provider sistemi doğru çalıştığında modül:

çoklu sağlayıcıyla güvenli çalışır

failover yapabilir

task bazlı model seçebilir

daha sürdürülebilir ve daha esnek hale gelir