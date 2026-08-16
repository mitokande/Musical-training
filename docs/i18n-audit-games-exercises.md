# Music Games & Egzersiz — Dil Denetimi Raporu

Tarih: 2026-08-10 · Kapsam: `/games`, `/games/{slug}`, `/practice/*`, `/exercise-setup`,
`/learn-exercise/*`, `/ai-exercises`, `/practice-ai/*` · Diller: **EN, TR, ES, DE** (fr/it/pt de tarandı)

---

## 0. Bildirilen hata — kök neden bulundu ve yeniden üretildi

**Belirti:** İngilizce arayüzde misafir olarak Note Fall oynandı; kredi bitince uyarı kutusu Türkçe çıktı.

**Kök neden:** Misafirin dil tercihi ile misafirin oyun kotası **iki farklı ömre sahip depolarda** tutuluyor.

| Veri | Nerede | Ömür |
|---|---|---|
| Misafir dil tercihi | `session('locale')` — `SetLocale::handle()` ([app/Http/Middleware/SetLocale.php:50](app/Http/Middleware/SetLocale.php:50)) | **120 dakika** (`SESSION_LIFETIME`) |
| Misafir oyun kotası | `harmoniva_guest_id` imzalı çerezi ([app/Services/UsageQuotaService.php:48](app/Services/UsageQuotaService.php:48)) | **1 yıl** |

Oturum düştüğünde (2 saat hareketsizlik, oturum tablosu temizliği, tarayıcı oturum çerezini
atması) dil tercihi kayboluyor, kota kaybolmuyor. `SetLocale::resolveLocale()` bir sonraki
istekte 3. adıma — `Accept-Language` başlığına — düşüyor. Türk tarayıcılı bir kullanıcı için
bu **tr** demek.

`!$canPlay` durumunda oyun alanının **tamamı** limit kutusuyla değiştiği için
([resources/views/partials/games/note-fall.blade.php:3](resources/views/partials/games/note-fall.blade.php:3)),
sayfanın tamamı Türkçe dönmüş olsa bile kullanıcı "sadece uyarı kutusu Türkçe çıktı" olarak görüyor.

**Canlı sitede yeniden üretim** (aynı misafir kimliği, oturum çerezi olmadan):

```
Accept-Language: en  -> "Daily limit reached"
Accept-Language: de  -> "Tageslimit erreicht"
Accept-Language: es  -> "Límite diario alcanzado"
Accept-Language: tr  -> "Günlük limit doldu"     <-- kullanıcının gördüğü
```

**Etki alanı yalnızca oyunlar değil.** Aynı asimetri şu ekranlarda da var, çünkü hepsi
`UsageQuotaService`'in 1 yıllık çerezini kullanıyor:
- Öğrenme Yolu misafir limiti (`app.limits.lp_guest_reached_*`)
- Exercise Setup Studio misafir limiti (`app.limits.studio_guest_reached_*`)
- Misafir seviye kilidi modalı (`app.limits.game_level_locked_*`)
- 180 saniyelik misafir kayıt açılır kutusu (`partials/guest-timer-popup`)

**İkincil bulgu:** `/games/{slug}` sayfasında footer yok
([resources/views/games/show.blade.php](resources/views/games/show.blade.php)) — sitedeki tek dil
değiştirici footer'da olduğu için, oyun sayfasında yanlış dile düşen bir kullanıcının sayfayı
terk etmeden dili düzeltme imkânı yok. `partials/language-switcher.blade.php` dosyası hiçbir
yerde include edilmiyor (ölü dosya).

### ✅ ÇÖZÜLDÜ — 2026-08-10 (Faz 1)

- Misafir dili artık `harmoniva_locale` çerezinde (1 yıl, şifresiz ~20 bayt) tutuluyor;
  `SetLocale::resolveLocale()` oturumdan sonra, `Accept-Language`'tan önce okuyor.
  `LanguageController::switch()` de yazıyor.
- **IP coğrafi konum adımı kaldırıldı.** Dil sinyali yoksa artık İngilizce servis ediliyor;
  `ip-api.com` çağrısı da tamamen ortadan kalktı.
- `/games/{slug}` başlığına koyu temalı dil değiştirici eklendi.
- Regresyon testleri: `tests/Feature/GuestLocalePersistenceTest.php` (7 test).
  Kural `docs/i18n-guide.md` § "Dil nasıl seçilir" bölümüne yazıldı.

Canlı doğrulama — oturum çerezi silinmiş, `harmoniva_locale=en` + `harmoniva_guest_id` duruyor,
tarayıcı `Accept-Language: tr`:

```
öncesi : <html lang="tr">  "Günlük limit doldu"
sonrası: <html lang="en">  "Daily limit reached"
```

---

## 1. Sözlük durumu — temiz

| Ölçüm | Sonuç |
|---|---|
| `app/email/notifications/pages/school/teacher` anahtar pariteleri (7 dil) | **1869 + … anahtar, 0 eksik, 0 fazla** |
| Diğer dil dosyalarına sızmış Türkçe metin | **0** |
| Kodda çağrılıp dosyada olmayan anahtar | **0** |
| `games.*` / `practice*.*` / `setup_ui.*` gruplarında çevrilmemiş değer | tr 14, es 15, de 19 — **hepsi meşru** (marka adı, "Tempo", "Arcade", "Normal" gibi ödünç sözcükler) |

Yani sorun sözlükte değil; **sözlüğe hiç girmemiş sabit kodlanmış metinlerde**.

---

## 2. Oyunlar — sabit kodlanmış metinler

| # | Yer | Metin | Etki |
|---|---|---|---|
| G1 | [interval-blitz.blade.php:270-284](resources/views/partials/games/interval-blitz.blade.php:270) | 13 aralık adı (`Perfect Unison`, `Minor 2nd`, … `Tritone`, `Perfect Octave`) | **Yüksek** — cevap şıkları her dilde İngilizce |
| G2 | [games/index.blade.php:429,435,441](resources/views/games/index.blade.php:429) | `games`, `audio`, `mobile` | Orta — oyun hub'ının istatistik şeridi |
| G3 | [games/test-c.blade.php:436,442,448,527](resources/views/games/test-c.blade.php:436) | aynı 3 metin + `Original` | Düşük — `/games/c` A/B test sayfası |
| G4 | `resources/views/livewire/games/*.blade.php` (4 dosya) + `app/Livewire/Games/*.php` (4 dosya) | Tamamen İngilizce, ~60 dize | **Yok — ölü kod.** Hiçbir route/blade bunlara referans vermiyor. Silinmeli. |
| G5 | [GameController.php:13-72](app/Http/Controllers/GameController.php:13) `GAMES` sabiti | 6 oyunun `name`, `description`, `difficulty`, `duration`, `tags` alanları | **Yüksek** — aşağıya bak |

**G5 detayı (Faz 1 doğrulaması sırasında bulundu, ilk taramada gözden kaçmıştı).**
`/games` hub'ı `$meta` dizisiyle `__()` üzerinden çeviriliyor, ama `/games/{slug}` sayfası
`GAMES` sabitini ham kullanıyor. Almanca sayfada gözlenen çıktı:

```html
<html lang="de">
<h1>Note Fall</h1>
<p>Notes drop from above — press the matching piano key before they hit the bottom!</p>
```

Etkilenen alanlar: `<h1>`, sayfa açıklaması, `<title>`, `<meta name="description">`,
`og:title`/`og:description`, `twitter:*` ve `VideoGame` JSON-LD şeması — yani hem kullanıcı
hem de arama motoru her dilde İngilizce metin görüyor. Çeviriler **zaten mevcut**
(`app.games.note_fall.title`, `app.games.note_fall_desc`, …); sadece sabit yerine bunların
okunması gerekiyor. `difficulty` alanı `games/index.blade.php`'de stil anahtarı olarak da
kullanıldığı için kanonik İngilizce kalmalı, yalnızca etiketi çevrilmeli.

Chord Clash, Note Fall, Note Catcher, Note Rush, Melody Memory ve `games/show.blade.php`
**tam çevrili** — akor adları dahil (`app.games.chord_clash.quality_*`).

---

## 3. Egzersizler — sabit kodlanmış metinler

### 3.1 Aralık adları — sistemik boşluk (en yüksek öncelik)

`app.music.chord` ve `app.music.scale` haritaları var, ama **`app.music.interval` yok**.
Aralık adları beş egzersizin cevap butonlarında ham İngilizce basılıyor:

- [practice-melodic-interval.blade.php:90,104](resources/views/livewire/practice-melodic-interval.blade.php:90) — `{{ $option }}`
- `practice-harmonic-interval`, `practice-interval-comparison`,
  `practice-interval-construction`, `practice-interval-direction` — aynı desen
- Ayrıca Interval Blitz oyunu (G1)

DE/ES/TR kullanıcısı "Minor 3rd", "Perfect 5th" görüyor. `music_label()` deseninin
üçüncü türü (`interval`) açılıp 7 dile 13 ad girilmeli; `data-answer` kanonik İngilizce kalır.

### 3.2 Geri bildirim mesajları — kullanıcıya doğrudan görünür

| Yer | Metin |
|---|---|
| [partials/melodic-dictation.blade.php:938-939](resources/views/livewire/partials/melodic-dictation.blade.php:938) | `✓ Correct!`, `Segment N done.`, `Perfect melody!`, `✗ Incorrect. The correct melody is shown below your answer.` |
| [practice-rhythm.blade.php:910-911](resources/views/livewire/practice-rhythm.blade.php:910) | `✓ Perfect timing! All beats correct.` / `✗ Not quite — green notes were tapped correctly, red ones were missed or mistimed.` |
| [practice-mixed.blade.php:1633-1634](resources/views/livewire/practice-mixed.blade.php:1633) | aynı iki cümle |

Dikte partial'ı hem Öğrenme Yolu hem Studio hem AI akışında kullanılıyor — görünürlüğü en yüksek olan bu.

### 3.3 Ritim nota-değeri paleti — 14 etiket × 2 dosya

[practice-rhythm.blade.php:942-955](resources/views/livewire/practice-rhythm.blade.php:942) ve
[practice-mixed.blade.php:960-973](resources/views/livewire/practice-mixed.blade.php:960):
`Whole`, `Half`, `Quarter`, `8th`, `16th`, `Half.`, `Qtr.`, `8th.`, `W. rest`, `½ rest`,
`Qtr rest`, `8th rest`, `Triplet` — hepsi `label:` alanında sabit İngilizce.

### 3.4 Buton ve durum etiketleri

| Yer | Metin |
|---|---|
| [practice-mixed.blade.php:1695-1702](resources/views/livewire/practice-mixed.blade.php:1695) | `Play`, `Play Both Intervals`, `Play Interval`, `Play Starting Note`, `Play Chord`, `Play Scale`, `Play Rhythm`, `Play Melody` |
| [practice-mixed.blade.php:1985,1996,2003,2006,2012](resources/views/livewire/practice-mixed.blade.php:1985) | `Play Again` × 5 — **oysa `app.practice_js.play_again` anahtarı zaten var** |
| [practice-rhythm.blade.php:1233](resources/views/livewire/practice-rhythm.blade.php:1233) · [practice-mixed.blade.php:1267](resources/views/livewire/practice-mixed.blade.php:1267) | `' (too long)'` |
| [practice-rhythm.blade.php:199](resources/views/livewire/practice-rhythm.blade.php:199) · [practice-mixed.blade.php:817](resources/views/livewire/practice-mixed.blade.php:817) | statik `0 / 0 beats`, `Bar` (JS devralmadan önceki ilk render) |
| [practice-single-note.blade.php:87](resources/views/livewire/practice-single-note.blade.php:87) | statik `Oct 4` |
| [practice-chord.blade.php:215-225](resources/views/livewire/practice-chord.blade.php:215) | 16 akor kısaltması (`Maj.`, `min.`, `dim.`, `aug.`, `Maj7`, `Dom7`…) — düşük öncelik, notasyon kısaltması sayılabilir |

### 3.5 Exercise Setup Studio (`/exercise-setup`)

| Yer | Metin |
|---|---|
| [exercise-setup.blade.php:431](resources/views/exercise-setup.blade.php:431) | 3 ritim modu açıklaması (`Listen with the metronome, then rebuild…` vb.) |
| [exercise-setup.blade.php:1057](resources/views/exercise-setup.blade.php:1057) | `Beginner` / `Intermediate` / `Advanced` |
| [exercise-setup.blade.php:1092](resources/views/exercise-setup.blade.php:1092) | Özet rozeti: `selectedCategory.replace(/-/g,' ')` → "melodic dictation" her dilde İngilizce |
| [exercise-setup.blade.php:1193-1197](resources/views/exercise-setup.blade.php:1193) | 15 donanım adı `C Major / A minor` biçiminde; `Major`/`minor` sözcükleri çevrilmiyor |
| [exercise-setup.blade.php:1167,1401](resources/views/exercise-setup.blade.php:1167) | `Natural Minor` — kanonik değer, doğru; ama etiket `music_label()`'dan geçmiyor |

---

## 4. Kapsam dışı ama yakalandı

- [profile/edit.blade.php:162](resources/views/profile/edit.blade.php:162) —
  `app()->getLocale() === 'tr' ? 'Aboneliği yönet' : 'Manage subscription'`:
  ES/DE/FR/IT/PT kullanıcısı İngilizce görüyor.
- `public/build/assets/zoom-room-*.js` — derlenmiş varlıkta Türkçe dizeler var (Zoom modülü).
- `/games` ve `/practice/*` için `/{locale}` önekli URL yok → bu sayfaların çok dilli
  varyantları indekslenmiyor (SEO; ayrı bir iş kalemi).
- **Nota adı yerelleştirmesi:** ES/FR/IT/PT'de notalar Do-Re-Mi, DE'de `B` → `H`.
  Şu an her dilde İngilizce/Alman harf sistemi (`C D E F G A B`) kullanılıyor. Ürün kararı —
  bu raporda düzeltme önerilmiyor, bilgi olarak not düşüldü.

---

## 5. Önerilen iş sırası

| Faz | İş | Tahmini dosya |
|---|---|---|
| ~~**1 — kritik**~~ ✅ | ~~Misafir dil tercihini 1 yıllık çereze taşı~~ | `SetLocale.php`, `LanguageController.php`, `bootstrap/app.php` |
| ~~**1 — kritik**~~ ✅ | ~~`/games/{slug}` sayfasına dil değiştirici ekle~~ | `games/show.blade.php`, `partials/language-switcher.blade.php` |
| **2 — yüksek** | G5: `GameController::GAMES` metinlerini `app.games.*`'a bağla (oyun sayfası H1 + tüm meta + JSON-LD) | `GameController.php`, `games/show.blade.php` |
| **2 — yüksek** | `app.music.interval` haritası + `music_label($x,'interval')` — 7 dil × 13 ad; 5 egzersiz + Interval Blitz | 7 lang + 6 blade |
| **2 — yüksek** | Dikte ve ritim geri bildirim cümleleri → `practice_js` | 3 blade + 7 lang |
| **3 — orta** | Ritim paleti 14 etiket, Play/Play Again butonları, `(too long)`, statik `Bar`/`beats`/`Oct 4` | 3 blade + 7 lang |
| **3 — orta** | Exercise Setup: mod açıklamaları, zorluk, özet rozeti, donanım etiketleri | 1 blade + 7 lang |
| **4 — düşük** | `games/index` 3 etiket, `profile/edit` ternary, ölü `livewire/games/*` silinmesi | 3 blade + 8 dosya silme |
