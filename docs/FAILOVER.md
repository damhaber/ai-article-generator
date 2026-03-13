
---

# `docs/FAILOVER.md`

```markdown
# AI Article Generator – Failover
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/FAILOVER.md

---

## 1. Amaç

Failover sistemi, bir provider veya model başarısız olduğunda modülün tamamen durmasını önlemek için vardır.

Amaç:
- tek sağlayıcı çökünce üretimin bitmemesi
- geçici ağ sorunlarında sistemin toparlanabilmesi
- task bazlı alternatif sağlayıcıya geçebilmek
- kullanıcıya mümkün olduğunca kontrollü sonuç döndürmek

Failover, rastgele fallback demek değildir.  
Kurallı, gözlemlenebilir ve sınırlı olmalıdır.

---

## 2. Failover Nerede Devreye Girer?

Temel akış:
- router primary provider/model seçer
- gateway çağrı yapar
- hata oluşursa retry politikası uygulanır
- retry sonrası hâlâ başarısızsa failover değerlendirilir
- uygun ise secondary provider/model denenir

Failover ile retry aynı şey değildir.

### Retry
Aynı provider/model üzerinde tekrar deneme

### Failover
Başka provider/model zincirine geçme

---

## 3. Failover Amaçları

Failover şunları sağlamalıdır:
- süreklilik
- kontrollü alternatif kullanım
- görünür hata yönetimi
- gereksiz çifte maliyetin önlenmesi
- kalite kaybını bilinçli yönetme

### Failover’ın yapmaması gereken
- gizli sonsuz döngü
- log’suz model değiştirme
- auth/config hatalarında anlamsız provider gezme
- her küçük sorunda hemen provider değiştirme

---

## 4. Failover Zinciri

Task bazlı ideal zincir router tarafından belirlenebilir.

Örnek article_generate için:

1. primary high-quality model
2. secondary high-quality compatible model
3. cost-balanced backup
4. local/offline emergency fallback (opsiyonel)

Örnek summary_generate için:
1. hızlı model
2. ekonomik model
3. local lightweight model

### Kural
Her task için aynı failover zinciri zorunlu değildir.

---

## 5. Hangi Hatalarda Retry, Hangi Hatalarda Failover?

### Önce retry düşünülebilecek durumlar
- timeout
- geçici 5xx
- kısa süreli network reset
- retryable rate limit
- provider temporary unavailable

### Doğrudan failover düşünülebilecek durumlar
- provider service outage
- provider capability mismatch
- model unavailable
- provider-specific internal failure repeated

### Failover yapılmaması gereken durumlar
- auth_error
- missing_config
- disabled_provider
- malformed request
- local validation failure

Bu son grupta asıl sorun düzeltilmeden provider değiştirmek anlamsızdır.

---

## 6. Failover Karar Kuralları

Failover kararı verirken şu alanlar değerlendirilmelidir:
- task type
- hata kodu
- retry sonucu
- secondary provider availability
- secondary provider task capability
- maliyet politikası
- kalite minimum eşiği
- kullanıcı/panel override ayarları

### Örnek
`article_generate` gibi kritik bir görevde:
- timeout sonrası 1 retry
- tekrar başarısızsa secondary provider
- secondary başarısızsa kontrollü user-facing error

---

## 7. Failover Metadata

Failover kullanıldıysa bu durum gizlenmemelidir.

Meta alanlarında ideal olarak şunlar bulunmalıdır:
- fallback_used
- fallback_from_provider
- fallback_from_model
- fallback_to_provider
- fallback_to_model
- failover_reason
- attempts

### Neden?
- log okunabilirliği
- kalite analizi
- maliyet takibi
- debugging

---

## 8. Local / Emergency Fallback

Bazı görevlerde son çare local veya deterministic fallback olabilir.

Örnekler:
- basit summary üretimi
- minimum structure ile article skeleton oluşturma
- system unavailable notu ile geçici içerik hazırlama

### Kural
Bu tür fallback’ler gerçek LLM üretimi gibi sunulmamalıdır.

Yanlış:
- zayıf template output’u “başarılı makale” gibi göstermek

Doğru:
- fallback kullanıldığı meta içinde görünmeli
- kalite düşükse kullanıcıya açıkça belirtilmeli

---

## 9. Failover ve Kalite İlişkisi

Tüm failover sonuçları eşdeğer kalite üretmez.

Örneğin:
- primary model = uzun, dengeli, çok iyi dil kalitesi
- secondary = daha hızlı ama daha kuru
- emergency = yalnızca temel yapı

Bu yüzden failover sonrası kalite motoru yeniden değerlendirme yapmalıdır.

### Kural
Failover sonucu gelen içerik, primary ile aynı kabul edilmemelidir.

---

## 10. Failover ve Router İlişkisi

Failover zinciri tamamen sabit kod içinde gömülmemelidir.  
Router bu konuda politika kaynağı olmalıdır.

Kaynak dosya:
- storage/router.json

Router burada şunları tanımlayabilir:
- task bazlı primary
- task bazlı secondary
- failover disabled/enabled
- maliyet önceliği
- kalite önceliği
- local fallback izni

---

## 11. Failover ve Gateway İlişkisi

Gateway şu bilgileri sağlamalıdır:
- hata tipi
- retry durumu
- provider availability sonucu
- normalize error code

Pipeline veya service ise daha üst karar verebilir.

### İdeal ayrım
- Gateway = uygulama seviyesi çağrı sonucu ve retry
- Router = alternatif seçimi
- Service/Pipeline = son kullanıcıya nasıl raporlanacağı

---

## 12. Failover ve Rewrite Görevleri

Rewrite gibi görevlerde failover daha hassas yönetilmelidir.

Neden?
Çünkü rewrite’da:
- ton
- dil
- biçim koruma
- html koruma

çok önemlidir.

Bazı secondary modeller rewrite için uygun olmayabilir.

### Kural
Rewrite failover zinciri, article_generate zincirinden farklı olabilir.

---

## 13. Failover ve SEO Görevleri

SEO görevleri genelde daha düşük risklidir.  
Bu nedenle burada daha ekonomik ve daha agresif failover politikası uygulanabilir.

Örnek:
- primary hızlı model
- secondary daha ekonomik model
- failure durumunda kısmi seo çıktısı üret

Ama yine de response contract korunmalıdır.

---

## 14. Loglama İlkeleri

Failover olayları mutlaka loglanmalıdır.

Önerilen log alanları:
- task
- original provider/model
- error code
- retry count
- fallback provider/model
- final success/failure
- duration

### Kural
Failover sessiz olmamalıdır.

---

## 15. Selftest ile İlişki

Selftest yalnızca provider var mı diye bakmamalı; failover hazır mı sorusunu da kısmen cevaplamalıdır.

Örnek kontroller:
- secondary provider tanımlı mı
- secondary provider available mı
- task capability uyumu var mı
- router.json failover zinciri okunabiliyor mu

---

## 16. Yanlış Failover Desenleri

Aşağıdaki desenler sakıncalıdır:

### Yanlış 1
Her hata tipinde anında provider değiştirmek

### Yanlış 2
Auth error alıp başka provider’a geçmek

### Yanlış 3
Sonsuz fallback döngüsü

### Yanlış 4
Fallback sonucu kalite düşmesine rağmen “başarılı final” gibi raporlamak

### Yanlış 5
Failover kullanıldığı halde meta/log’a yazmamak

---

## 17. Üretim Seviyesi Politika Önerisi

### article_generate
- 1 retry
- sonra secondary
- sonra controlled hard fail
- emergency fallback yalnız açıkça işaretlenmişse

### article_rewrite
- 0 veya 1 retry
- secondary yalnız rewrite capability güçlü ise
- html koruma desteklemeyen modele geçme

### seo_generate
- 1 retry
- sonra secondary low-cost model
- gerekirse partial output kabul edilebilir

### summary/title
- daha agresif failover kabul edilebilir

---

## 18. Kullanıcıya Görünen Davranış

Failover iç detayları her zaman kullanıcıya aynen gösterilmek zorunda değildir.  
Ama sistem:
- sessizce bozulmuş içerik vermemeli
- eksik kaliteyi başarı gibi göstermemeli
- meta/log katmanında gerçeği saklamamalıdır

---

## 19. Bu Belgenin Rolü

Bu belge failover mantığının resmi referansıdır.

Şu soruların cevabını burada bulmak gerekir:
- retry ile failover farkı nedir?
- hangi hatada provider değişir?
- hangi hatada değişmez?
- zincir nasıl tanımlanır?
- metadata ve logging nasıl yapılır?

---

## 20. Son Hüküm

Failover sistemi, AI Article Generator modülünün dayanıklılık mekanizmasıdır.

Doğru failover:
- sistemi ayakta tutar
- kaliteyi kontrollü yönetir
- maliyeti izlenebilir kılar
- debugging’i kolaylaştırır

Yanlış failover:
- karmaşa üretir
- maliyeti artırır
- kaliteyi gizlice düşürür
- hataları görünmez yapar