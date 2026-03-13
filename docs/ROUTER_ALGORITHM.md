# AI Article Generator – Router Algorithm
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/ROUTER_ALGORITHM.md

---

## 1. Amaç

Router, her AI görevi için en uygun provider/model çiftini seçen karar katmanıdır.

Bu modülde router’ın amacı:
- tek modele bağımlılığı önlemek
- task bazlı doğru modeli seçmek
- kalite / hız / maliyet dengesini kurmak
- availability ve failover politikasını dikkate almak
- settings ve router.json üzerinden kontrol edilebilir bir sistem sunmak

Router “hangi model daha iyi olabilir?” sorusunu sistematik hale getirir.

---

## 2. Router’ın Mimari Konumu

Akış:

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
Model

Router içerik üretmez.  
Router dış API çağrısı yapmaz.  
Router karar verir.

---

## 3. Router Girdileri

Router karar verirken şu girdileri kullanabilir:
- task type
- preferred provider
- preferred model
- user/panel override
- router.json politikaları
- providers.json enabled durumu
- models.json capability bilgileri
- health/availability bilgileri
- maliyet önceliği
- kalite önceliği
- hız önceliği

Örnek task tipleri:
- article_generate
- article_rewrite
- seo_generate
- title_generate
- summary_generate
- faq_generate
- schema_generate
- news_analyze
- fact_expand

---

## 4. Router Çıktıları

Router ideal olarak şu alanları döndürmelidir:

```php
[
  'ok' => true,
  'task' => 'article_generate',
  'provider' => 'openrouter',
  'model' => 'anthropic/claude-x',
  'fallback_chain' => [
    ['provider' => 'openai', 'model' => 'gpt-4.1-mini'],
    ['provider' => 'groq', 'model' => 'llama-3.3-70b']
  ],
  'options' => [
    'temperature' => 0.7,
    'max_tokens' => 2400,
    'timeout' => 45,
  ],
  'meta' => [
    'selection_reason' => 'quality_priority',
  ],
  'error' => null,
]

Kural

Gateway, router’dan kararlı ve açık bir karar objesi almalıdır.

5. Task Bazlı Yönlendirme

Her görev için aynı model seçilmez.

article_generate

Öncelik:

kalite

uzun bağlam

akıcı dil

editoryal yetenek

article_rewrite

Öncelik:

ton koruma

dil akıcılığı

yapısal sadakat

html koruma

seo_generate

Öncelik:

hız

yeterli kalite

düşük maliyet

title_generate / summary_generate

Öncelik:

hız

maliyet

yeterli doğruluk

news_analyze / fact_expand

Öncelik:

bilgi yoğunluğu

kavramsal çıkarım

makul maliyet

6. Router Politika Kaynakları

Router için ana politika kaynağı:

storage/router.json

Destekleyici kaynaklar:

storage/providers.json

storage/models.json

storage/settings.json

storage/health.json

router.json rolü

task bazlı primary modeller

task bazlı fallback zinciri

quality/speed/cost profilleri

override kuralları

disabled route kuralları

Router kod içine gömülü sabitlerle değil, bu yapılandırmalarla yönlendirilmelidir.

7. Seçim Kriterleri

Router karar verirken şu kriterleri puanlayabilir:

provider enabled mi

provider available mı

model task’i destekliyor mu

kalite sınıfı

hız sınıfı

maliyet sınıfı

timeout profili

dil uyumu

uzun context desteği

rewrite uygunluğu

JSON/structured output kabiliyeti

Amaç

Kararın tek sebebi “ilk bulduğunu kullanmak” olmamalıdır.

8. Availability Kontrolü

Router hiçbir zaman disabled veya unavailable provider’ı primary seçmemelidir.

Kontrol kaynakları:

registry

providers.json

health.json

opsiyonel ping sonuçları

Kural

Availability kontrolü yalnız dosya/sınıf varlığı değildir.

9. Override Sistemi

Router kararları bazı durumlarda override edilebilir.

Override kaynakları:

panel seçimi

request içindeki explicit provider/model tercihi

admin debug modu

özel task kuralı

Kural

Override sistemi güvenli ve kontrollü olmalıdır.

Örnek:

disabled provider override edilemez

missing config olan provider zorla seçilemez

rewrite görevi rewrite capability olmayan modele zorlanamaz

10. Quality / Cost / Speed Profilleri

Router’da üç ana öncelik ekseni vardır:

Quality Priority

Daha pahalı ama daha iyi çıktı veren model tercih edilir.

Cost Priority

Daha ekonomik model tercih edilir.

Speed Priority

Daha hızlı model tercih edilir.

Bu profiller task bazında değişebilir.

Örnek:

article_generate → quality_priority

seo_generate → cost_priority

title_generate → speed_priority

11. Fallback Chain

Router primary kararın yanında fallback zinciri de üretmelidir.

Fallback zinciri seçerken dikkat edilecekler

secondary model aynı task’i destekliyor mu

aynı kalite sınıfında mı

farklı provider mı

availability durumu uygun mu

maliyet sınırını aşıyor mu

Kural

Fallback zinciri gelişi güzel liste olmamalıdır.

12. Router ve Prompt İlişkisi

Router prompt üretmez.
Ama karar objesi prompt’in niteliğini etkileyebilir.

Örnek:

bazı modeller daha kısa max_tokens ile kullanılmalı

bazı modeller için temperature daha düşük olmalı

bazı modeller structured output için daha uygun olabilir

Bu nedenle router options alanı da döndürmelidir.

13. Router ve Rewrite Görevleri

Rewrite task’i için router ayrı politika kullanmalıdır.

Rewrite özel gereksinimleri:

dil sadakati

ton sadakati

html koruma desteği

kısa/uzun yeniden yazım kalitesi

Kural

Article_generate için iyi olan model, rewrite için en iyi model olmayabilir.

14. Router ve SEO Görevleri

SEO görevleri genelde daha kısa ve yapılandırılmış çıktılar ister.

Bu nedenle:

daha hızlı

daha ekonomik

daha öngörülebilir

modeller tercih edilebilir.

Ama schema/FAQ gibi yapılandırılmış içeriklerde modelin düzenli format üretme becerisi önemlidir.

15. Router Hata Yönetimi

Router şu tip durumlarda açık hata döndürmelidir:

uygun provider bulunamadı

task destekleyen model yok

bütün adaylar disabled

override geçersiz

yapılandırma eksik

Örnek response:

[
  'ok' => false,
  'error' => [
    'code' => 'no_route_found',
    'message' => 'No available provider/model could satisfy task article_generate',
  ],
]
Kural

Router belirsiz veya boş karar döndürmemelidir.

16. Router ve Selftest

Selftest router için şunları sınamalıdır:

router.json okunuyor mu

en az bir article_generate rotası var mı

en az bir rewrite rotası var mı

selected provider available mı

fallback chain geçerli mi

Bu sayede router sorunları runtime’da değil erken görülür.

17. Yanlış Router Desenleri
Yanlış 1

Tüm task’lerde tek modele zorlamak

Yanlış 2

Disabled provider’ı availability kontrol etmeden seçmek

Yanlış 3

Fallback zincirini hiç üretmemek

Yanlış 4

Task capability dikkate almamak

Yanlış 5

Cost/quality/speed profilini dokümansız ve rastgele kullanmak

18. Üretim Seviyesi Tavsiye
article_generate

primary: güçlü dil ve uzun form modeli

secondary: güçlü ama alternatif provider

tertiary: maliyet dengeli model

article_rewrite

primary: ton/dil iyi model

secondary: rewrite uyumlu alternatif

tertiary: yalnız destekliyorsa

seo_generate

primary: hızlı/ekonomik model

secondary: yapılandırılmış çıktı yetenekli model

title/summary

hızlı ve ucuz modeller öne çıkabilir

19. Bu Belgenin Rolü

Bu belge router davranışının resmi referansıdır.

Aşağıdaki soruların cevabını burada bulmak gerekir:

task bazlı model seçimi nasıl yapılır?

fallback zinciri nasıl kurulur?

override nasıl çalışır?

quality/cost/speed dengesi nasıl ele alınır?

availability nasıl değerlendirilir?

20. Son Hüküm

Router, AI Article Generator modülünün karar beynidir.

Doğru router:

doğru göreve doğru modeli seçer

maliyeti kontrol eder

kaliteyi yükseltir

failover’ı anlamlı hale getirir

Yanlış router:

çoklu provider mimarisini anlamsızlaştırır

kaotik sonuç üretir

maliyeti yükseltir

kaliteyi dengesizleştirir