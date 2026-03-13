
---

# `docs/PANEL_ARCHITECTURE.md`

```markdown
# AI Article Generator – Panel Architecture
Version: 6.0 Draft Stabilization
Status: Source of Truth
Location: masal-panel/modules/ai-article-generator/docs/PANEL_ARCHITECTURE.md

---

## 1. Amaç

Bu belge panel ve UI mimarisini tanımlar.

Amaç:
- panelin neyi yapacağını
- neyi asla yapmaması gerektiğini
- core/service/pipeline katmanlarıyla nasıl ilişki kuracağını

netleştirmektir.

---

## 2. Ana Dosyalar

- panel.php
- ui/settings.php
- ui/editor.js
- ui/style.css
- ui/components/*

İlgili action katmanı:
- ajax-handler.php

---

## 3. Panelin Rolü

Panel şu işleri yapmalıdır:
- ayar ekranları sunmak
- provider yapılandırmasını yönetmek
- prompt preset düzenlemek
- manual article generate ekranı sağlamak
- rewrite işlemini tetiklemek
- selftest sonuçlarını göstermek
- logları okunabilir biçimde göstermek
- kalite raporunu göstermek

Panel şunları yapmamalıdır:
- gerçek article orchestration
- provider seçme algoritması
- prompt engine’in yerini almak
- response contract yamamak
- business logic taşımak

---

## 4. editor.js Rolü

editor.js şu görevleri üstlenir:
- buton aksiyonları
- AJAX çağrıları
- loading state
- response render
- preview güncelleme
- kullanıcı etkileşimi

Ama şunları yapmamalıdır:
- article contract tahmin etmek
- eksik backend response’u JS ile icat etmek
- rewrite prompt’unu core’dan bağımsız kurmak
- model routing kararı vermek

### Kural
editor.js akıllı olabilir; ama çekirdeğin yerine geçemez.

---

## 5. Panel Bölümleri

Önerilen ana panel alanları:
- Dashboard
- Generate
- Rewrite
- SEO
- Providers
- Models/Router
- Prompt Presets
- News Sources
- Selftest
- Logs
- Usage / Health

Bu yapı accordion veya sekmeli olabilir; ama mantık ayrımı net olmalıdır.

---

## 6. Panel ve AJAX İlişkisi

Panel doğrudan core dosyalarına bağlanmamalıdır.  
Her aksiyon mümkün olduğunca AJAX/service üzerinden gitmelidir.

Akış:
Panel UI
→ editor.js
→ ajax-handler.php
→ service
→ pipeline/core
→ response
→ editor.js render

### Kural
panel.php içinde iş mantığı birikmemelidir.

---

## 7. Panelde Görünen Veri Tipleri

Panel şu veri türlerini göstermelidir:
- settings values
- provider status
- selftest summary
- article preview
- rewrite preview
- seo preview
- usage numbers
- logs
- health summary

Ama gizli verileri maskeli göstermelidir.

---

## 8. Hata Gösterim İlkeleri

Panel hata gösterirken:
- kullanıcı dostu açıklama sunmalı
- ama gizli teknik detay sızdırmamalı

Örnek:
- “Provider yapılandırması eksik”
- “Rewrite route bulunamadı”
- “News source dosyası okunamadı”

### Kural
Ham stack trace panel ana görünümünde açılmamalıdır.

---

## 9. Kalite ve Health Gösterimi

Panel, yalnız çıktı göstermemeli; kalite ve health bilgisini de göstermelidir.

Örnek:
- article quality score
- language warning
- fallback used
- provider/model used
- selftest overall status

Bu alanlar özellikle debug ve üretim hazırlığında çok değerlidir.

---

## 10. Panelin Çekirdekten Ayrılığı

Panel değişebilir.  
Theme/UI değişebilir.  
Ama core/service/pipeline sözleşmeleri mümkün olduğunca sabit kalmalıdır.

### Kural
UI değişikliği backend contract’ı kırmamalıdır.

---

## 11. Bu Belgenin Rolü

Bu belge panel mimarisinin sınırlarını korumak içindir.

Şu soruların cevabı burada bulunmalıdır:
- panel ne yapar?
- editor.js ne yapar?
- business logic nerede başlar?
- hangi veriler panelde görünür?
- error/health/quality nasıl gösterilir?

---

## 12. Son Hüküm

Panel, AI Article Generator modülünün kontrol yüzüdür; beyni değildir.

Beyin:
- services
- pipeline
- context
- router/gateway/provider

katmanlarında olmalıdır.

