# AI Article Generator – V6 Stabilization Plan
Version: 6.0 Draft Stabilization
Status: Active Stabilization Roadmap
Location: masal-panel/modules/ai-article-generator/docs/V6_STABILIZATION_PLAN.md

---

## 1. Amaç

Bu belge, AI Article Generator modülünü dağınık/yarım bağlı durumdan çıkarıp resmi V6 omurgasına oturtmak için uygulanacak stabilizasyon planını tanımlar.

Amaç:
- tek tek semptom düzeltmek değil
- modülü resmi, tutarlı, sürdürülebilir bir mimariye geçirmek
- docs, loader, services, pipeline, routing, rewrite ve quality sistemini birbiriyle uyumlu hale getirmek

Bu plan patch listesi değildir.  
Bu plan, omurga inşa sırasıdır.

---

## 2. Nihai Hedef

Resmi V6 omurga şu olacaktır:

UI / Panel
↓
AJAX
↓
Service Layer
↓
Pipeline Layer
↓
Context / News Layer
↓
Router
↓
Gateway
↓
Provider
↓
LLM
↓
Rewrite / SEO / Quality
↓
Final Response / Save / Publish

### Bu hedefin anlamı
- tek giriş kapıları
- tek response contract
- gerçek task-aware routing
- gerçek rewrite service
- context-first article generation
- legacy’nin merkezden çıkarılması

---

## 3. Ana Strateji

Stabilizasyonun temel stratejisi:

### Strateji 1
Önce bootstrap ve contract stabilizasyonu

### Strateji 2
Sonra article/rewrite omurgasını düzeltme

### Strateji 3
Sonra router/gateway/provider zincirini resmileştirme

### Strateji 4
Sonra quality/selftest/panel sadeleştirme

Bu sıra bozulursa sistem yine yamalı kalır.

---

## 4. Resmi Mimari Kararları

### Karar 1
Bu yapı plugin değildir; modüldür.

### Karar 2
Ana merkez `service + pipeline` olacaktır.

### Karar 3
`core/ai-article-core.php` düşük seviye helper/adaptör rolüne indirgenecektir.

### Karar 4
Gerçek article production girişi `core/services/article-service.php` olacaktır.

### Karar 5
Gerçek rewrite girişi `core/services/rewrite-service.php` olacaktır.

### Karar 6
`core/ai-article-pipeline.php` resmi orchestration merkezi olacaktır.

### Karar 7
Provider sistemi `registry + router + gateway` üçlüsüyle resmileştirilecektir.

### Karar 8
UI ve AJAX business logic taşımaz hale getirilecektir.

---

## 5. Faz 1 — Bootstrap Stabilizasyonu

### Hedef
`ai-article-generator.php` modül loader’ını resmi ve deterministik hale getirmek.

### Yapılacaklar
- foundation dosyalarını başa almak
- provider sınıflarını ve registry’yi garantili yüklemek
- router/gateway/llm zincirini providerlardan sonra yüklemek
- news/context/outline katmanını pipeline öncesine yerleştirmek
- services’i pipelines ve SEO’dan sonra yüklemek
- integrations/UI/AJAX’i en sona almak

### Başarı kriteri
Aynı runtime’da:
- provider missing
- collector missing
- function/class not found
- yanlış include sırası kaynaklı kopukluklar
azalmalı veya bitmelidir.

---

## 6. Faz 2 — Response Contract Stabilizasyonu

### Hedef
Modülün tüm ana use-case’leri için tek response standardı oluşturmak.

### Zorunlu contract’lar
- article generate response
- rewrite response
- seo response
- selftest response

### Yapılacaklar
- `article`, `rewrite`, `seo`, `meta`, `error` alanlarını standardize etmek
- flat ve nested response karışıklığını azaltmak
- AJAX ve UI’nin aynı shape’e güvenebilmesini sağlamak

### Başarı kriteri
`ui/editor.js` backend eksiklerini tahmin etmeye daha az ihtiyaç duyar.

---

## 7. Faz 3 — Article Service Resmileştirmesi

### Hedef
Makale üretim isteğinin tek resmi giriş kapısı oluşturulacak.

### Dosya
- `core/services/article-service.php`

### Yapılacaklar
- input normalize
- settings/feature-map kontrolü
- pipeline çağrısı
- final response standardı
- quality/meta bağlama

### Başarı kriteri
AJAX doğrudan low-level generate helper’ları çağırmaz; yalnız service kullanır.

---

## 8. Faz 4 — Pipeline Onarımı

### Hedef
`core/ai-article-pipeline.php` dosyasını gerçek V6 orchestration merkezi haline getirmek.

### Yapılacaklar
1. normalize input almak
2. context build
3. outline build
4. gerçek prompt/messages üretmek
5. router kararı almak
6. gateway call yapmak
7. raw output parse etmek
8. rewrite/polish entegre etmek
9. SEO enrich etmek
10. final article object döndürmek

### En kritik onarım
Prompt payload ile gerçek LLM input contract’ı arasındaki kopukluğu kapatmak.

### Başarı kriteri
Kısa/çıplak/fallback ağırlıklı article davranışı ciddi biçimde azalır.

---

## 9. Faz 5 — Rewrite Motorunun Resmileştirilmesi

### Hedef
Rewrite’ı gerçek bir use-case service haline getirmek.

### Dosyalar
- `core/services/rewrite-service.php`
- `core/pipelines/rewrite-pipeline.php`

### Yapılacaklar
- rewrite-service gerçek LLM rewrite görevini yönetecek
- rewrite-pipeline yalnız cleanup/postprocess yapacak
- instruction, tone, lang, preserve_html, target_length gibi alanlar standartlaşacak

### Başarı kriteri
Rewrite çıktısı no-op hissi vermez; gerçekten yeniden yazılmış görünür.

---

## 10. Faz 6 — Router / Gateway / Registry Stabilizasyonu

### Hedef
Çoklu provider mimarisini kağıt üzerinde değil, resmi runtime omurgasında çalışır hale getirmek.

### Dosyalar
- `core/ai-article-provider-registry.php`
- `core/ai-article-router.php`
- `core/ai-article-gateway.php`
- `core/ai-article-llm.php`

### Yapılacaklar
- task bazlı route seçimi
- availability kontrollü karar
- normalize error/usage
- retry + failover meta
- provider response standardı

### Başarı kriteri
Provider sistemi debug edilebilir, predictable ve sürdürülebilir hale gelir.

---

## 11. Faz 7 — Context / News Güçlendirmesi

### Hedef
Makale kalitesini kaynaktan itibaren yükseltmek.

### Dosyalar
- `core/ai-article-context.php`
- `core/ai-article-outline.php`
- `core/news/*`

### Yapılacaklar
- context pack standardı oluşturmak
- fact density artırmak
- topic/category/news hizasını güçlendirmek
- outline’ı template-aware hale getirmek
- dil hedefini context ve prompt içinde daha güçlü sabitlemek

### Başarı kriteri
Article’lar daha uzun, daha bağlamlı ve daha az İngilizce kalıntılı hale gelir.

---

## 12. Faz 8 — Quality Sistemi

### Hedef
“cevap geldi = başarı” anlayışını bırakmak.

### Dosyalar
- `core/ai-article-quality.php`
- ilgili metrics/helper yapıları

### Yapılacaklar
- article quality score
- rewrite quality score
- language consistency flag
- fallback content flag
- too_short / weak_structure / low_fact_density işaretleri

### Başarı kriteri
Sistem zayıf article’ı görünür hale getirir.

---

## 13. Faz 9 — Selftest ve Health

### Hedef
Gerçek güvenilir sağlık raporu.

### Dosyalar
- `core/services/selftest-service.php`
- `storage/health.json`

### Yapılacaklar
- static checks
- contract checks
- runtime checks
- provider/router/gateway/news/pipeline doğrulaması

### Başarı kriteri
Selftest yanlış negatif yerine gerçek runtime durumunu yansıtır.

---

## 14. Faz 10 — UI ve Panel Sadeleştirmesi

### Hedef
UI’yi çekirdeğin kurtarıcısı olmaktan çıkarmak.

### Dosyalar
- `panel.php`
- `ui/editor.js`
- `ui/settings.php`

### Yapılacaklar
- response shape yamalarını azaltmak
- paneli yalnız kontrol yüzeyi haline getirmek
- quality/health/fallback bilgilerini görünür kılmak

### Başarı kriteri
editor.js sadeleşir; çekirdeğin net contract’ına güvenebilir.

---

## 15. Faz 11 — Legacy’yi Adaptere İndirme

### Hedef
Eski davranışları tamamen silmeden merkezden çıkarmak.

### Dosyalar
- `core/ai-article-core.php`
- `core/ai-article-bridge.php`
- diğer legacy destek yapıları

### Yapılacaklar
- merkezden kenara çekme
- yalnız uyumluluk/helper rolünde bırakma
- ana üretim akışından çıkarmak

### Başarı kriteri
V6 resmi merkez olur.

---

## 16. İlk Uygulama Paketi

Birlikte ele alınması gereken ilk paket:

- `ai-article-generator.php`
- `ajax-handler.php`
- `core/services/article-service.php`
- `core/ai-article-pipeline.php`
- `core/services/rewrite-service.php`
- `core/services/selftest-service.php`

### Neden birlikte?
Çünkü:
- bootstrap
- giriş kapıları
- ana article contract
- rewrite contract
- health contract

tek seferde netleşmeden stabil sistem oluşmaz.

---

## 17. İkinci Paket

- `core/ai-article-provider-registry.php`
- `core/ai-article-router.php`
- `core/ai-article-gateway.php`
- `core/ai-article-llm.php`

### Amaç
AI çağrı omurgasını resmileştirmek.

---

## 18. Üçüncü Paket

- `core/ai-article-context.php`
- `core/ai-article-outline.php`
- `core/news/news-collector.php`
- `core/news/news-normalizer.php`
- `core/news/news-fact-pack.php`

### Amaç
Kalite ve bağlam derinliğini yükseltmek.

---

## 19. Dördüncü Paket

- `core/services/seo-service.php`
- `core/pipelines/seo-pipeline.php`
- `core/ai-article-quality.php`
- `panel.php`
- `ui/editor.js`

### Amaç
SEO, kalite ve panel görünürlüğünü tamamlamak.

---

## 20. Başarı Ölçütleri

Stabilizasyon başarılı sayılmak için şu sonuçlar gözlenmelidir:

### Ölçüt 1
Article generate tek contract ile döner.

### Ölçüt 2
Rewrite gerçek ve görünür etki üretir.

### Ölçüt 3
Türkçe article’larda İngilizce kalıntı belirgin biçimde azalır.

### Ölçüt 4
Fallback kullanımı meta/log içinde görünür olur.

### Ölçüt 5
Selftest false negative üretmez.

### Ölçüt 6
UI backend shape tahmin ederek sistemi ayakta tutmak zorunda kalmaz.

### Ölçüt 7
Provider/router/gateway zinciri debug edilebilir hale gelir.

---

## 21. Bu Belgenin Rolü

Bu belge, V6 stabilizasyonunun resmi yol haritasıdır.

Şu soruların cevabını burada bulmak gerekir:
- önce ne düzelecek?
- neden o dosyalar birlikte ele alınacak?
- ana hedef ne?
- başarı neye göre ölçülecek?
- legacy nasıl ele alınacak?

---

## 22. Son Hüküm

AI Article Generator modülünü kurtaracak şey:
- tek tek semptom patch’leri değil
- resmi V6 omurga stabilizasyonudur

Bu plan uygulandığında modül:
- dağınık çok katmanlı yapıdan
- sürdürülebilir, debug edilebilir, kaliteli bir AI editorial module yapısına

geçebilir.