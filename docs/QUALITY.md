# AI Article Generator – Quality
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/QUALITY.md

---

## 1. Amaç

Bu belge modülün kalite değerlendirme ilkelerini tanımlar.

Amaç:
- üretilen içeriğin yalnızca “dönmüş” olmasını değil, gerçekten yeterli kalitede olmasını sağlamak
- fallback veya zayıf model çıktılarının görünmeden sisteme sızmasını önlemek
- article, rewrite ve SEO kalitesini ölçmek
- minimum kabul eşiği tanımlamak

AI sistemlerinde “cevap geldi” kalite anlamına gelmez.  
Kalite ayrıca ölçülmelidir.

---

## 2. Kalite Katmanı Nerede?

İlgili dosyalar:
- core/ai-article-quality.php
- core/ai-article-metrics.php
- core/services/article-service.php
- core/services/rewrite-service.php
- core/services/seo-service.php

Kalite değerlendirmesi:
- pipeline sonrası
- rewrite sonrası
- gerekiyorsa save öncesi

uygulanmalıdır.

---

## 3. Kalite Hedefi

İyi bir article output şunları sağlamalıdır:
- yeterli uzunluk
- açık yapı
- konuya uygun bağlam
- dil bütünlüğü
- kaynak uyumu
- tekrar oranının düşük olması
- SEO destek alanlarının uyumu

Kalite hedefi task bazlı değişebilir.

---

## 4. Kalite Ölçüm Grupları

### 4.1 Length Score
Makalenin beklenen uzunluğa yakın olup olmadığını ölçer.

Örnek:
- short
- medium
- long

### 4.2 Structure Score
Başlıklar, section akışı, giriş-gelişme-sonuç dengesi var mı?

### 4.3 Relevance Score
Metin gerçekten topic/category ile uyumlu mu?

### 4.4 Language Consistency Score
Hedef dil korunmuş mu?  
Türkçe beklenirken İngilizce kalıntılar var mı?

### 4.5 Fact Density
Makale yeterince veri/fact barındırıyor mu, yoksa boş genellemeler mi dolu?

### 4.6 Redundancy Score
Aynı fikir cümleleri gereksiz tekrar ediyor mu?

### 4.7 Readability Score
Metin okunabilir mi, akış dengeli mi?

### 4.8 SEO Support Score
Meta/FAQ/schema üretimiyle içerik arasında uyum var mı?

---

## 5. Article Kalite Kriterleri

Bir article için minimum bakılması gereken alanlar:
- title mevcut mu
- content boş mu
- content uzunluğu yeterli mi
- section mantığı var mı
- topic kelimeleri içerikte anlamlı biçimde geçiyor mu
- kaynaklar bağlanmış mı
- dil hedefi korunmuş mu

### Kural
Sadece `content != ""` olması başarı değildir.

---

## 6. Rewrite Kalite Kriterleri

Rewrite için ayrı kalite kriterleri gerekir.

Ölçülmesi gerekenler:
- anlam korunmuş mu
- ton hedefe yaklaştı mı
- gereksiz kısalma oldu mu
- html yapısı bozuldu mu
- dil akıcılığı arttı mı

### Kural
Rewrite kalitesi article kalitesinden farklı metriklerle değerlendirilmelidir.

---

## 7. SEO Kalite Kriterleri

SEO alanında ölçülebilecekler:
- meta_title boş mu
- meta_description yeterli mi
- FAQ maddeleri anlamlı mı
- schema alanları doldurulmuş mu
- keyword set topic ile uyumlu mu

SEO çıktısı sırf üretildi diye kaliteli sayılmamalıdır.

---

## 8. Kalite Sonucu Formatı

Kalite motoru normalize bir sonuç döndürmelidir.

Örnek:

```php
[
  'ok' => true,
  'score' => 84,
  'threshold' => 70,
  'passed' => true,
  'metrics' => [
    'length' => 82,
    'structure' => 90,
    'relevance' => 88,
    'language_consistency' => 76,
    'fact_density' => 79,
    'redundancy' => 85,
    'readability' => 83,
  ],
  'flags' => [
    'minor_english_residue',
  ],
]

9. Minimum Eşik Politikası

Örnek kalite eşikleri:

85+ = güçlü

70-84 = kabul edilebilir

55-69 = zayıf / warning

0-54 = başarısız

Bu eşikler task bazlı ayarlanabilir.

Örnek

article_generate → daha yüksek threshold

summary_generate → daha düşük threshold

seo_generate → ayrı threshold

10. Fallback ve Kalite İlişkisi

Failover veya emergency fallback ile gelen içerik kalite motorundan geçmelidir.

Neden?

Çünkü fallback sonucu:

daha kısa olabilir

daha kuru olabilir

daha zayıf dil kalitesi gösterebilir

Kural

Fallback output primary quality ile aynı kabul edilmemelidir.

11. Bugünkü Kritik Kalite Sorunları

Mevcut mimaride özellikle şu kalite sorunları görülür:

kısa article

İngilizce kalıntı

rewrite etkisizliği

fallback section üretiminin gerçek article gibi görünmesi

Kalite motoru bu tür sorunları işaretleyebilmelidir.

Örnek flag’ler:

too_short

language_mismatch

low_fact_density

weak_structure

rewrite_no_effect

fallback_content_detected

12. Panelde Kalite Gösterimi

Kalite sonuçları panelde gösterilebilir:

toplam score

metrik kırılımı

warning flag’ler

başarısız alanlar

Bu, kullanıcıya “neden zayıf çıktı”yı anlamada yardımcı olur.

13. Save Öncesi Kalite Politikası

İsteğe bağlı olarak kalite eşiği altındaki içerikler:

yayınlanmayabilir

yalnız taslak kaydedilebilir

warning ile kullanıcıya gösterilebilir

Kural

Kalite katmanı yalnız raporlama değil, kontrol kapısı da olabilir.

14. Bu Belgenin Rolü

Bu belge kalite sisteminin resmi referansıdır.

Şu soruların cevabını burada bulmak gerekir:

kalite neye göre ölçülür?

hangi metrikler zorunlu?

minimum eşik nedir?

fallback kalitesi nasıl ele alınır?

panelde ne gösterilir?

15. Son Hüküm

Kalite motoru olmadan modül yalnızca içerik üreten bir makine olur.

Kalite motoru ile modül:

zayıf çıktıyı fark eder

dil ve yapı sorunlarını görünür kılar

fallback kaynaklı kalite düşüşünü yakalar

daha güvenilir editoryal üretime yaklaşır