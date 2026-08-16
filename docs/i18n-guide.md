# Harmoniva i18n & çok dilli SEO kılavuzu

Bu dosya, Almanca tamamlama turunda (2026-08-09) öğrenilenleri kaydeder. Yeni bir dil
eklerken veya mevcut bir dili tamamlarken **önce burayı oku** — buradaki her madde
gerçekten yaşanmış bir hatanın veya doğrulanmış bir kuralın karşılığı.

Diller: `en` (kaynak), `de`, `es`, `fr`, `it`, `pt`, `tr` tam çeviri.
`config/locales.php` → `prefixed` bunların hangilerinin `/{locale}` önekiyle
yayınlandığını, `public_pages` ve `page_sections` hangi sayfanın hangi `pages.*`
grubuna karşılık geldiğini tutar.

---

## 1. Çevirinin gerçekten bittiğini nasıl anlarsın

Anahtar saymak yeterli değil. Beş ayrı boyut var ve hepsi ayrı ayrı taranmalı:

| # | Boyut | Nasıl ölçülür |
|---|---|---|
| 1 | Eksik anahtar | `en/*.php` düzleştir, hedef dille `array_diff_key` |
| 2 | Kodda çağrılıp dosyada olmayan | `__('...')` + `crm_trans('...')` çağrılarını grep'le, dile karşı doğrula |
| 3 | Blade'de sabit kodlanmış metin | `>metin<` text-node taraması (aşağıdaki tuzağa dikkat) |
| 4 | Inline JS'te sabit metin | `<script>` bloklarında `textContent`/`innerHTML`/`alert` ataması |
| 5 | PHP'de sabit kullanıcı mesajı | `->with('error'\|'success'\|'warning', '...')` ve `abort(4xx, '...')` |

Almanca turunda 1. boyut %94,7'de görünüyordu ama asıl hasar 3–5. boyuttaydı:
kontrolörlerde **Türkçe** sabit metinler her dildeki kullanıcıya Türkçe gösteriliyordu.

**Dinamik anahtarları unutma.** Kod `__('app.billing.status_'.$x)` gibi birleştirme
yapıyorsa grep bunu `app.billing.status_` olarak görür. Bu önekle başlayan anahtarların
hedef dilde var olduğunu ayrıca doğrula.

### Tarama tuzakları

- **Alt küme tuzağı:** taramayı her zaman `resources/views` tamamında yap, seçtiğin
  bir alt klasörde değil.
- **Satır numarası kayması:** tarayıcı `<script>`/`<style>` bloklarını siliyorsa
  bildirdiği satır numarası yanlıştır. Her bulguyu `grep -n` ile teyit et.
- **Yanlış pozitifler gerçek metinden çoktur.** Marka adları (Harmoniva, Google Chrome,
  YouTube), şirket adresi, referans kişi adları, URL örnekleri, font adları çevrilmez.

---

## 2. Hitap biçimi — dil başına tek karar

| dil | biçim | not |
|---|---|---|
| de | **du** | 2026-08 turunda birleştirildi (128 dize Sie→du) |
| fr | **vous** | |
| it | **tu** | |
| pt | **você** | |
| es | **tú** | |
| tr | **siz** (resmî) | |

Yeni metin yazarken komşu gruba bak, sözlüğe değil.

### Almanca'da `Sie` taraması yaparken

`\b(Sie|Ihre|Ihnen|Ihr)\b` eşleşmelerinin bir kısmı **3. şahıs zamiridir**, hitap değil:

> „Diese Cookies sind erforderlich… **Sie** erheben keine personenbezogenen Daten."
> („sie" = Cookies)

Almanca turunda 142 eşleşmenin **14'ü** böyleydi (Cookies, Gehörbildung, KI, Übungen,
Einladung, Plattformen). **Asla toplu replace yapma** — her eşleşmeyi tek tek oku.

---

## 3. Müzik terimleri: değer sabit, etiket çevrilir

Akor ve gam tipleri motorun kanonik anahtarlarıdır
(`ChordPractice::chordIntervals()` / `ScalePractice::scaleIntervals()`), çevrilemezler.
Ama öğrencinin gördüğü etiket çevrilmelidir.

Kural: **`data-answer`, Alpine değeri ve `config_json` kanonik İngilizce kalır;
yalnızca görünen etiket `music_label()`'dan geçer.**

```blade
{{-- data-answer stays canonical; only the label is localised. --}}
<button data-answer="{{ strtolower($option) }}">{{ music_label($option, 'chord') }}</button>
```

- `app.music.chord` / `app.music.scale` — 18+18 kanonik ad → yerel etiket, 7 dilde
- `music_label($canonical, 'chord'|'scale')` — bilinmeyen tip kanonik ada düşer
- Inline JS için `livewire/partials/music-labels.blade.php` → `window.musicLabel()`
  (`practice-i18n.blade.php` deseninin kardeşi; küçük harfli anahtarla arar)

Yeni bir akor/gam tipi eklersen `app.music.*`'a 7 dilde girmesi gerekir; girmezse
sessizce İngilizce görünür (kırılmaz, ama gözden kaçar).

Almanca karşılıklar örnek: Dur, Moll, Vermindert, Übermäßig, Dominantseptakkord,
Halbverminderter Septakkord, Natürliches Moll, Äolisch, Ganztonleiter.

---

## 4. Meta metin uzunlukları — dil başına yeniden ölçülmeli

Google `<title>`'ı ~60, `<meta description>`'ı ~160 karakterde keser.

**Roman dilleri İngilizce'den %20–25 uzundur.** İngilizce'de sınırın altındaki bir
metin çevrildiğinde taşar. 2026-08 turunda 7 dilde **85 açıklama** sınırın üzerindeydi
(fr 20, it 19, pt 19, es 18, en 8, tr 1) ve hepsi yeniden yazıldı.

Çeviri yaparken 160'ı baştan hedefle; sonradan kısaltmak yeniden yazmak demektir.

### Title tuzağı

Layout başlığa son ek ekler — `standalone` → ` — Harmoniva`, `/learn` → ` | Harmoniva`
(12 karakter). Yani **`meta_title` anahtarı 48 karakteri geçmemeli.**
Ölçümü ham anahtarda değil, canlı `<title>`'da yap:

```bash
curl -s https://harmoniva.app/fr/learn | grep -o '<title>[^<]*</title>'
```

`jsonld_headline` ayrı bir anahtardır ve son ek almaz — onu kısaltma.

### Doğrulama

```bash
# 7 dil × 27 sayfa, render edilmiş title/description uzunlukları
# (scratchpad'deki denetim scripti; HTML entity'leri çözmeyi unutma —
#  &#039; tek karakterdir, aksi halde uzunluk yanlış çıkar)
```

---

## 5. SEO: canonical / hreflang / yapısal veri

**Tek kaynak:** `App\Services\Seo\PublicPageSeo::forRequest()` → `ShareSeoContext`
middleware ile `View::share`. İki tüketici:

- `layouts/standalone.blade.php` — public sayfaların çoğu
- `partials/public-seo-alt.blade.php` — kendi `<head>`'i olan 3 sayfa
  (piano-studio, learn, pricing-teachers); `seoPageTitle`/`seoPageDescription` parametresi alır
- `welcome.blade.php` (ana sayfa) kendi `@graph`'ını kurar — PublicPageSeo kullanmaz

Bu blade'lerden **birine** dokunduğunda diğerini de kontrol et; yoksa dillere göre
canonical ayrışır.

### Çeviri kapısı

`locale_page_translated($path, $locale)` — bir `/{locale}` URL'i, ilgili `pages.*`
grubu o dilde gerçekten varsa (kapsam ≥ `locales.page_translation_threshold`)
**gerçek çeviri** olarak ilan edilir. Aksi halde İngilizce'ye canonical verir ve
hreflang setinden çıkarılır.

Bu kasıtlıdır: çevrilmemiş bir sayfa İngilizce metinle ikinci bir URL'de yayınlanırsa
Google "Duplicate, Google chose a different canonical" der. **Yeni dil eklerken bu kapı
kendiliğinden devreye girer** — çeviri tamamlanınca sayfa otomatik olarak hreflang
setine katılır.

### Dil nasıl seçilir — sıra sabittir

`SetLocale::resolveLocale()` sırası, her adımın neden orada olduğuyla birlikte:

| # | Kaynak | `locale_explicit` |
|---|---|---|
| 1 | Giriş yapmış kullanıcının `users.locale` değeri | `true` |
| 2 | `session('locale')` | yalnızca `locale_selected` varsa `true` |
| 3 | `harmoniva_locale` çerezi (1 yıl, **şifresiz**) | `true` |
| 4 | `Accept-Language` başlığı | `true` |
| 5 | **İngilizce** — sinyal yoksa | `false` |

**3. adım neden var:** misafirin kota sayaçları `harmoniva_guest_id` çerezinde 1 yıl
yaşıyor (`UsageQuotaService`), dil tercihi ise oturumda yalnızca 120 dakika yaşıyordu.
Oturum önce düşünce kota kalıyor ama dil kayboluyordu: İngilizce okuyan bir ziyaretçi
günlük oyun limitine takılıyor ve **oyun alanının tamamını kaplayan** "limit doldu"
ekranını tarayıcısının dilinde görüyordu. Çerez, dili kotayla aynı ömre kavuşturur.

Çerez `bootstrap/app.php` içinde `encryptCookies(except:)` listesinde — şifreli olsaydı
her isteğe (asset'ler dahil) ~300 bayt binerdi, şifresiz ~20 bayt. Değer her okumada
`$supported` listesine karşı doğrulandığı için kurcalanan bir çerez ancak sitenin zaten
yayınladığı bir dili seçebilir.

**5. adımda IP coğrafi konumu yok, geri de eklenmemeli.** Ülke bir dil değildir: gurbetçiyi,
yolcuyu, VPN kullanıcısını ve yabancı SIM'i yanlış etiketliyordu, üstelik sinyalsiz her
isteğe bir `ip-api.com` çağrısı (2 sn timeout) bindiriyordu. **Saptanamayan dil = İngilizce.**

Regresyon testleri: `tests/Feature/GuestLocalePersistenceTest.php`.
Testte `Accept-Language`'ı **atlamak yetmez** — Symfony test istemcisi varsayılan olarak
`en-us,en;q=0.5` gönderir, gerçekten sinyalsiz durum için başlığı boş string olarak geç.

### Bot davranışı — bozma

Doğrulanmış ve olması gereken davranış:

| istek | sonuç |
|---|---|
| `Accept-Language` yok (GPTBot, Googlebot) → `/` | **200, yönlendirme yok** |
| `Accept-Language: de` → `/` | 302 → `/de` |
| `/de` doğrudan | 200 |

`SetLocale::resolveLocale()` içindeki `locale_explicit` bayrağı bunu sağlar. Bot'a
yönlendirme yapılırsa ana sayfa "yönlendirmeli sayfa" olarak indeks dışı kalır.

### Yapısal veri

- **`Organization`'a `inLanguage` ekleme** — kurumsal kimlik dil-nötr bir varlıktır.
- Dil sinyali **URL başına benzersiz** `WebPage` düğümünde durur (`WebSite/@id` tüm
  dillerde ortaktır, oraya da uygun değil).
- `Product`, `FAQPage`, `Article` gibi içerik varlıklarına `inLanguage` **eklenir**.
- `@context` anahtarını mutlaka `@php` bloğu içinde kur — Blade onu `@context`
  direktifi sanıp JSON'u bozar.

### `llms.txt` (AI arama motorları)

`public/llms.txt` içinde **"## Languages"** bölümü var: 7 dil, prefix kuralı ve müzik
terimlerinin yerelleştiği notu. Yeni dil eklerken bu listeye de ekle — yoksa ChatGPT /
Perplexity / Claude o dilde içerik olduğunu bilmez ve İngilizce URL'i önerir.

`robots.txt` GPTBot, ClaudeBot, PerplexityBot, Google-Extended, OAI-SearchBot için
özel kural içermez → genel `*` geçerli, hepsi izinli. Bu kasıtlı.

---

## 6. Depoya özgü tuzaklar

### `git stash` kullanma

Bu depoda çok sayıda dosya **root sahipli**, ayrıca kritik dosyaların bir kısmı git'e
hiç girmemiş (untracked). `git stash -u` root sahipli dosyaları geri alamaz ama
untracked olanları **siler** — 2026-08-09'da `app/Http/Middleware/ShareSeoContext.php`
ve `resources/lang/{de,fr,it,pt}/pages.php` dahil 22 dosya kayboldu, canlı site 500 verdi.

Baseline karşılaştırması gerekiyorsa `git show HEAD:<dosya>` kullan.

Kurtarma (drop edilmiş stash bir süre dangling kalır):

```bash
git fsck --unreachable --no-reflog | grep commit | awk '{print $3}'
# "untracked files on main:" başlıklı commit'i bul
git ls-tree -r --name-only <commit> | while read f; do [ -e "$f" ] || git checkout <commit> -- "$f"; done
```

### Root sahipli dosyaya yazma

Dizin bize ait olduğu için dosyayı dizin üzerinden değiştir:

```python
tmp = path + '.new'
open(tmp,'w').write(s); os.unlink(path); os.rename(tmp, path); os.chmod(path, 0o644)
```

### Lang dosyalarında tırnak stili

Apostrof içeren metinler **çift tırnakla** yazılmıştır
(`"Harmoniva's mission…"`), diğerleri tek tırnakla. Tam-dize eşleştirmeli bir script
yazarken ikisini de dene, yoksa sessizce "not found" alırsın.

### Değişiklik yaparken

- Tam dize eşleştir ve **dosyada tam olarak bir kez geçiyorsa** değiştir; tekrar eden
  ifadeler için ayrı bir liste tut.
- `php -l` ile her lang dosyasını doğrula.
- Livewire blade'leri `view:cache`'e **girmez** — onları Blade derleyicisiyle ayrıca
  derleyip syntax'ı doğrula.
- Blade yorumu içine literal `@php` yazma; sayfanın yarısını sessizce yutar.

---

## 7. Testler

- `users.locale` sütununun **DB varsayılanı `'tr'`**. `UserFactory` bunu `'en'` ile
  ezer; ezmeseydi her test kullanıcısı Türkçe render eder ve İngilizce metne bakan
  her assertion kırılırdı. Locale'e duyarlı testler kendi locale'ini açıkça verir.
- Kullanıcı oluşturan **yeni bir kayıt akışı yazarsan `locale` set et.**
  `SocialAuthController` bunu yapmıyordu: Google ile kaydolan Alman kullanıcının
  hesabı Türkçe oluyordu.
- Blade `{{ }}` HTML-escape eder. `assertSee("You're Premium", false)` çalışmaz —
  apostrof `&#039;` olur. Escape'i açık bırak (varsayılan).

---

## 8. Çalışan denetim komutları

```bash
# Anahtar kapsamı (dil başına eksik/İngilizce-kalmış)
php8.2 artisan test                    # composer test değil — config cache tuzağı

# Canlı SEO doğrulaması
curl -s https://harmoniva.app/de/pricing | grep -oE '<link rel="canonical"[^>]*>|hreflang="[a-z-]*"'

# Bot davranışı
curl -s -o /dev/null -A "GPTBot/1.1" -w "%{http_code} %{redirect_url}\n" https://harmoniva.app/

# Değişiklik sonrası
php8.2 artisan view:clear && php8.2 artisan view:cache
```

Canlı doğrulamayı **mutlaka** yap: derlenmiş view cache'i ve tarayıcı cache'i,
"değişiklik görünmüyor" şikayetlerinin en sık nedenidir.
