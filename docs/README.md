# AI Article Generator – Docs

Bu klasör modülün **kalıcı mimari rehberidir**.

Amaç:
- sistemin nasıl çalıştığını açıklamak
- dosya sorumluluklarını netleştirmek
- yeni geliştirmelerde sıfırdan analiz yapılmasını önlemek

Bu docs seti **koddan daha önemlidir** çünkü mimari kararları saklar.

---

# Ana Belgeler

ARCHITECTURE.md  
→ sistemin genel mimarisi

PIPELINE.md  
→ makale üretim pipeline'ı

CODEMAP.md  
→ dosya haritası

WHICH_FILE_WHAT.md  
→ her dosyanın görevi

IMPLEMENTATION_PLAN.md  
→ geliştirme planı

SELFTEST.md  
→ sistem sağlık kontrolü

CURRENT_RUNTIME_TRUTH.md  
→ bugünkü gerçek durum

V6_STABILIZATION_PLAN.md  
→ V6 mimari stabilizasyon planı

DOCS_TRUTH.md  
→ docs kullanım prensipleri

---

# Ana Amaç

AI Article Generator:

news → context → outline → prompt → LLM → rewrite → seo → publish

akışıyla çalışan bir **AI gazetecilik motorudur**.