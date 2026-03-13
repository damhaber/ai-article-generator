
---

# `docs/SECURITY.md`

```markdown
# AI Article Generator – Security
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/SECURITY.md

---

## 1. Amaç

Bu belge modülün güvenlik ilkelerini tanımlar.

AI Article Generator; panel, ajax, provider config, storage, logs ve dış API çağrılarını bir araya getirdiği için çok katmanlı güvenliğe ihtiyaç duyar.

Amaç:
- yetkisiz erişimi önlemek
- kötü girdileri temizlemek
- API anahtarlarını korumak
- abuse ve aşırı kullanımı sınırlamak
- log ve storage tarafında hassas veri sızıntısını önlemek

---

## 2. Güvenlik Yüzeyleri

Bu modülde başlıca risk alanları:
- ajax endpoints
- panel formları
- provider config kaydı
- dış API çağrıları
- log üretimi
- storage JSON yazımı
- rewrite/generate input’ları
- rest-api entegrasyonu

---

## 3. Yetki Kontrolü

### Zorunlu kurallar
- panel işlemleri yetki kontrolü olmadan çalışmamalı
- ajax işlemleri nonce ve permission kontrolünden geçmeli
- provider config değişiklikleri yalnız yetkili kullanıcılarca yapılmalı
- selftest ve log görüntüleme de yetki gerektirmeli

### Kural
"Panel içinden geldi" varsayımı güvenlik yerine geçmez.

---

## 4. Nonce ve Request Doğrulama

Özellikle şu alanlarda zorunlu:
- makale generate
- rewrite
- save settings
- provider save
- selftest run
- manual log clear
- prompt preset save

Nonce kontrolü başarısızsa:
- işlem durmalı
- anlaşılır hata dönmeli
- sessiz fallback yapılmamalı

---

## 5. Input Sanitization

Generate/rewrite/SEO girdileri mutlaka normalize edilmelidir.

Örnek alanlar:
- topic
- category
- tone
- language
- instruction
- template
- model/provider override
- max length

### Kural
Input yalnızca trim etmekle yetinilmemelidir.

Yapılması gerekenler:
- string sanitize
- enum doğrulama
- max length sınırları
- beklenmeyen alanların atılması
- JSON input ise schema doğrulama

---

## 6. Provider Config Güvenliği

Provider config dosyası:
- storage/providers.json

Bu dosyada hassas bilgiler bulunabilir:
- API key
- base url
- provider secret
- timeout/retry ayarları

### Kurallar
- ham anahtarlar loglanmamalı
- panelde gösterilirken maskelenmeli
- export/debug sırasında redacted görünmeli
- yetkisiz kullanıcıya açılmamalı

Mümkünse:
- environment variable desteği
- şifrelenmiş saklama
- masked save/update davranışı

tercih edilmelidir.

---

## 7. Log Güvenliği

Loglar yararlıdır ama tehlikeli de olabilir.

### Loglanmaması gerekenler
- düz API anahtarları
- auth header içerikleri
- tam gizli secret değerleri
- kontrolsüz tam prompt dump
- gereksiz ham provider response

### Kontrollü loglanabilecekler
- provider adı
- model adı
- task tipi
- duration
- token usage
- hata kodu
- redacted request özeti

### Kural
Debug için bile tam secret dump yasaktır.

---

## 8. Storage Güvenliği

Storage tarafındaki JSON dosyaları korunmalıdır.

Özellikle:
- settings.json
- providers.json
- router.json
- health.json
- prompt-presets.json
- usage/

### Kurallar
- yalnız beklenen süreçler yazabilmeli
- bozuk JSON yazımı önlenmeli
- kısmi yazımda dosya bozulmamalı
- mümkünse atomic write uygulanmalı
- kullanıcı girdisi doğrudan dosyaya dökülmemeli

---

## 9. Rate Limit ve Abuse Koruması

Bu modül dış AI servisleri kullandığı için abuse riski yüksektir.

Korunması gereken alanlar:
- generate
- rewrite
- selftest runtime checks
- REST API expose edilen işlemler

### Rate limit hedefleri
- kullanıcı bazlı
- görev bazlı
- provider bazlı
- zaman penceresi bazlı

### Kural
Rate limit yalnız maliyet için değil, güvenlik için de zorunludur.

---

## 10. REST / Integration Güvenliği

Dosyalar:
- integrations/rest-api.php
- diğer hook dosyaları

### Kurallar
- açık endpoint’ler minimum tutulmalı
- authentication net olmalı
- write operasyonları yetkisiz kullanıma kapalı olmalı
- iç sistem helper’ları doğrudan dışarı açılmamalı

### Kural
İç helper fonksiyonu = public API değildir.

---

## 11. Rewrite ve Prompt Injection Riski

Rewrite ve generate input’ları kullanıcıdan gelebilir.  
Bu yüzden prompt injection, format kaçırma ve kontrol dışı davranış riski vardır.

### Önlemler
- sistem prompt’u ile kullanıcı talebi ayrılmalı
- instruction alanı normalize edilmeli
- kritik task’lerde izinli alanlar kullanılmalı
- html koruma modunda ek doğrulama yapılmalı

### Kural
Kullanıcı metni LLM’e kontrolsüz “üst talimat” gibi taşınmamalıdır.

---

## 12. HTML ve Output Güvenliği

Rewrite ve article output’unda HTML bulunabilir.

Önlemler:
- izinli tag listesi
- tehlikeli script/event attribute temizliği
- panel preview’da güvenli render
- save öncesi sanitize

### Kural
LLM çıktısı güvenilir HTML kabul edilmemelidir.

---

## 13. Provider Network Güvenliği

Dış çağrılarda:
- timeout zorunlu olmalı
- SSL doğrulaması kapatılmamalı
- key/header dikkatli yönetilmeli
- redirect/unsafe endpoint politikası net olmalı

### Kural
“Çalışsın yeter” diye güvenlik gevşetilmemelidir.

---

## 14. Selftest Güvenliği

Selftest bazen gerçek provider testleri çalıştırabilir.  
Bu da güvenlik ve maliyet riski taşır.

### Kurallar
- selftest yalnız yetkili kullanıcıya açık olmalı
- deep runtime testler kontrolsüz tekrar edilmemeli
- loglarda gizli config açığa çıkmamalı

---

## 15. Panel Güvenliği

Panel ekranlarında:
- CSRF koruması
- capability kontrolü
- masked secret gösterimi
- güvenli form işleme
- güvenli log render

zorunludur.

### Kural
Admin paneli olması, otomatik güvenli olduğu anlamına gelmez.

---

## 16. Hata Mesajı Politikası

Kullanıcıya dönen hata mesajları:
- anlaşılır
- ama aşırı iç detay vermeyen
- secret/stack trace açmayan

yapıda olmalıdır.

### Yanlış
- tam exception trace gösterimi
- auth header dump
- raw provider body

### Doğru
- `provider_unavailable`
- `timeout`
- `missing_provider_config`

gibi kontrollü hata kodları

---

## 17. Dosya İzinleri ve Dağıtım İlkeleri

Üretimde:
- logs ve storage yazılabilir
- source code mümkün olduğunca korunmuş
- doğrudan web erişimi istemeyen alanlar kontrol altında
- hassas JSON dosyaları gerekiyorsa erişim politikası ile korunmuş

olmalıdır.

---

## 18. Bu Belgenin Rolü

Bu belge modülün güvenlik prensiplerinin resmi kaydıdır.

Şu soruların cevabını burada bulmak gerekir:
- hangi alanlar korunmalı?
- nonce/capability nerede zorunlu?
- API key nasıl saklanmalı?
- loglarda ne yasak?
- rate limit neden güvenlik parçası?

---

## 19. Son Hüküm

AI Article Generator modülü yalnız üretim kalitesiyle değil, güvenlik disipliniyle de güçlü olmalıdır.

Doğru güvenlik:
- gizli veriyi korur
- abuse’u azaltır
- paneli ve ajax’ı güvenli kılar
- dış AI çağrılarını kontrollü hale getirir

Gevşek güvenlik:
- maliyet kaçağı
- veri sızıntısı
- config ifşası
- kontrolsüz AI kullanımı

gibi ciddi riskler doğurur

