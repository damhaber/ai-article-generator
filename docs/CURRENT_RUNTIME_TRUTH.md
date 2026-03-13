# AI Article Generator – Current Runtime Truth
Version: 6.0 Draft Stabilization
Status: Runtime Diagnosis
Location: masal-panel/modules/ai-article-generator/docs/CURRENT_RUNTIME_TRUTH.md

---

## 1. Amaç

Bu belge, modülün bugünkü gerçek runtime durumunu açık ve dürüst biçimde kaydetmek için vardır.

Amaç:
- semptomları değil, kök mimari sorunları tanımlamak
- sonraki geliştirmelerde geçmişteki aynı hatalara dönülmesini önlemek
- “neden kısa içerik çıkıyor?”, “neden rewrite zayıf?”, “neden bazı katmanlar çalışıyor gibi ama değil?” sorularına tek yerde net cevap vermek

Bu belge pazarlama metni değildir.  
Bu belge, sistemin bugünkü teknik gerçeğinin kayıt defteridir.

---

## 2. Genel Hüküm

Modülün bugünkü ana problemi tekil bug değildir.

Ana problem:

**mimari sürüklenme + contract uyumsuzluğu**

Özellikle şu üç yapı aynı anda yaşamaya çalışmaktadır:
- legacy V4/V5 üretim mantığı
- V6 service/pipeline/context mantığı
- yarım entegre olmuş provider/router/gateway mantığı

Bu üç yapı tek omurgada birleşmediği için modül:
- bazı yerlerde çalışıyor gibi görünür
- ama uçtan uca tutarlı üretim vermez

---

## 3. Dışarıdan Görülen Ana Semptomlar

Bugünkü semptomlar:
- makale kısa kalıyor
- içerik “çıplak” görünüyor
- İngilizce kalıntılar sızıyor
- rewrite beklenen etkiyi yapmıyor
- bazı durumlarda fallback üstüne fallback çalışıyor
- response alanları her endpoint’te aynı değil
- bazı selftest sonuçları güven vermiyor
- bazen katman mevcut olduğu halde missing gibi davranışlar oluşuyor

Bu semptomlar ayrı ayrı bug gibi görünse de, ana kaynak çoğunlukla ortak mimari kopukluklardır.

---

## 4. En Kritik Kök Sorun: Pipeline Contract Kopukluğu

Bugünkü en büyük teknik sorun şudur:

### Gözlenen durum
V6 article pipeline:
- context oluşturuyor
- outline oluşturuyor
- prompt payload benzeri veri oluşturuyor

Ama legacy generate/gateway zincirinin beklediği gerçek:
- `prompt`
veya
- `messages`

sözleşmesine her zaman eksiksiz ve deterministik biçimde bağlanmıyor.

### Sonuç
LLM çağrısı ya:
- yetersiz veriyle çalışıyor
- ya fallback’e gereğinden erken kayıyor
- ya da beklenen editoryal derinliği üretemiyor

### Dış semptom olarak
- kısa içerik
- zayıf yapı
- RSS summary hissi
- İngilizce özet sızıntıları

ortaya çıkıyor.

Bu nedenle bugünkü asıl onarım alanı:
**prompt/messages contract**

---

## 5. Rewrite Gerçeği

Rewrite tarafında bugünkü durum tam anlamıyla güçlü bir rewrite motoru değildir.

### Gözlenen durum
Rewrite hattında:
- gerçek LLM tabanlı yeniden yazım ile
- cleanup/postprocess

rolleri birbirine karışmış görünmektedir.

Bazı akışlarda “rewrite” denilen işlem aslında daha çok:
- temizlik
- bölüm toparlama
- heading düzeltme
- hafif parlatma

seviyesinde kalmaktadır.

### Sonuç
Kullanıcı “rewrite” beklediğinde:
- ton ciddi biçimde değişmeyebilir
- metin derinleşmeyebilir
- dil kalitesi yeterince yükselmeyebilir
- bazen neredeyse no-op hissi oluşabilir

### Net hüküm
Bugünkü sistemde rewrite hattı,
**gerçek rewrite service + cleanup pipeline**
ayrımına tam oturmamıştır.

---

## 6. News Katmanı Hakkındaki Gerçek

Bugünkü tabloda news katmanı, ilk bakışta göründüğü kadar ana suçlu değildir.

### Gözlenen durum
Mevcut dosya setinde:
- `news-collector.php` mevcut olabilir
- kaynaklar okunuyor olabilir
- loglarda collect başarılı görünüyor olabilir

Bu, news katmanının mükemmel olduğu anlamına gelmez.  
Ama ana kök sorun çoğu zaman “collector dosyası yok” değildir.

### Daha doğru teşhis
Sorun çoğunlukla şu seviyededir:
- news verisi geliyor
- ama article pipeline bunu güçlü editoryal çıktıya çeviremiyor

### Sonuç
Haber var ama makale gücü düşük kalıyor.

Yani:
- news katmanı = veri tabanı
- asıl kırık = üretim omurgası

---

## 7. Provider / Registry / Router / Gateway Gerçeği

Bu bölgede ana durum:
- yapı mevcut
- ama tam resmileşmiş ve tamamen stabil değil

### Gözlenen problemler
- registry ana bootstrap içinde her zaman merkezi konumda olmayabilir
- router karar modeli net task-based standarda tam oturmamış olabilir
- gateway normalize contract’ı her çağrıda aynı görünmeyebilir
- provider availability / config / active state zinciri her yerde aynı dille okunmayabilir

### Sonuç
Çoklu provider mimarisi kağıt üzerinde güçlü görünse de, pratikte bazı çağrılarda:
- yarım bağlı
- heterojen
- debug’u zor

bir davranış gösterebilir.

### Net hüküm
Provider sistemi vardır; ama **omurga seviyesinde tam resmileştirilmesi gerekir.**

---

## 8. Selftest Gerçeği

Selftest bugünkü durumda yararlı ama tam güvenilir referans değildir.

### Gözlenen riskler
- yanlış fonksiyon adı beklentisi
- yanlış constant beklentisi
- yalnız statik kontrol yapıp runtime contract’ı kaçırma
- sistem sağlıklı olsa bile false negative üretme ihtimali

### Sonuç
Selftest sonuçları tamamen yok sayılmamalı; ama sorgusuz “source of truth” da kabul edilmemelidir.

### Net hüküm
Selftest yeniden düzenlenmeden:
- üretim güveni
- otomatik health takibi
- panel güvenilirliği

tam sağlanmaz.

---

## 9. Response Shape Gerçeği

Bugünkü sistemde tek bir response contract tam yerleşmiş değildir.

### Gözlenen durum
Farklı alanlar dönüyor olabilir:
- `content`
- `html`
- `article`
- `rewrite`
- `data`
- `meta`
- bazen nested, bazen flat

### Sonuç
UI/JS tarafı savunmacı davranmak zorunda kalıyor.  
Bu da:
- backend’in net olmayan davranışını frontend’in yamamaya çalışması
anlamına geliyor.

### Net hüküm
Bugünkü sistemde response birliği eksikliği,
semptomları büyüten çarpanlardan biridir.

---

## 10. UI Gerçeği

Panel ve editor.js tarafı şu an kısmen “hayatta tutucu” rol üstleniyor olabilir.

### Gözlenen durum
Backend response her zaman aynı netlikte gelmediği için UI:
- farklı shape’leri handle etmeye
- eksik alanları tahmin etmeye
- preview’ı kurtarmaya

çalışıyor olabilir.

### Sonuç
UI sade bir görüntü katmanı olmaktan çıkıp,
backend zayıflığını telafi eden savunmacı katmana dönüşür.

### Net hüküm
UI suçlu değildir; ama şu an normalden fazla yük taşıyor olabilir.

---

## 11. Legacy vs V6 Gerçeği

Bugünkü ana mimari gerilim burada yatıyor.

### Legacy mantık
- düşük seviye generate
- daha basit prompt odaklı akış
- tek parça içerik üretimi

### V6 mantık
- context
- news
- fact pack
- outline
- multi-provider routing
- rewrite/postprocess
- seo enrichment

### Sorun
Bu iki dünya net ayrılmadan birlikte çalıştırılmaya çalışılıyor.

### Sonuç
Bazı yerlerde:
- legacy ana merkez gibi davranıyor
bazı yerlerde:
- V6 ana merkez gibi davranıyor

Bu da sürdürülebilir değil.

### Net hüküm
V6 resmi omurga olmalı; legacy adaptere indirilmeli.

---

## 12. Dil Kalitesi Sorunu Gerçeği

Türkçe article içinde İngilizce kalıntılar görünmesi rastgele bir olay değildir.

### Muhtemel nedenler
- kaynakların önemli kısmı İngilizce
- fact pack/summary İngilizce kökenli
- gerçek rewrite/polish yeterince güçlü devreye girmiyor
- prompt contract hedef dili yeterince zorlamıyor
- fallback içerikler kaynak dilini daha çok taşıyor

### Net hüküm
Dil sorunu yalnız “çeviri eksikliği” değil, üretim zincirinin genel kalitesine bağlıdır.

---

## 13. Fallback Gerçeği

Fallback mekanizması vardır; ama bugünkü durumda bazı yerlerde fazla görünür hale gelmiş olabilir.

### İdeal fallback
- kontrollü
- sınırlı
- meta içinde görünür
- kalite motoru tarafından işaretlenmiş

### Bugünkü risk
Fallback bazen:
- gizli kalite düşüşü yaratabilir
- gerçek article gibi görünebilir
- kullanıcıyı yanıltabilir

### Net hüküm
Fallback gereklidir; ama şu anda fazla taşıyıcı rol üstleniyor olabilir.

---

## 14. En Kritik Bugünlük Sonuçlar

Bugünkü sistem için en önemli teknik hükümler:

### Hüküm 1
News katmanı tek başına ana sorun değildir.

### Hüküm 2
Asıl kırık, article pipeline ile generate/gateway contract zincirindedir.

### Hüküm 3
Rewrite gerçek rewrite service seviyesine tam ulaşmamıştır.

### Hüküm 4
Provider/router/gateway mimarisi vardır ama tam merkezileşmemiştir.

### Hüküm 5
Response contract birliği eksiktir.

### Hüküm 6
UI, backend eksiklerini telafi etmeye çalışıyor olabilir.

### Hüküm 7
V6 merkez, legacy adapter olacak şekilde net ayrım yapılmamıştır.

---

## 15. Bu Belgenin Rolü

Bu belge şunlar için referanstır:
- bugünkü teknik gerçek
- neden patch’lerle tam çözüm alınamadığı
- neden bütüncül stabilizasyon gerektiği
- hangi sorunların semptom değil kök sorun olduğu

Bu belge olmadan gelecekte:
- aynı teşhis tekrar tekrar yapılır
- aynı yanlış dosyalara odaklanılır
- sorun yine “tek bug” gibi ele alınır

---

## 16. Son Hüküm

AI Article Generator modülü bugünkü durumda:
- potansiyeli yüksek
- parçaları değerli
- ama omurgası tam birleşmemiş

bir yapıdadır.

Sorun “modül çalışmıyor” değildir.  
Sorun “modül tek resmi mimariye oturmadan birden fazla dönemi aynı anda taşımaya çalışıyor” olmasıdır.

Bu nedenle doğru çözüm:
- tek bug avı değil
- V6 omurga resmileştirmesi
- loader + contract + service/pipeline stabilizasyonu
- legacy’yi adaptere indirgeme

olmalıdır.

