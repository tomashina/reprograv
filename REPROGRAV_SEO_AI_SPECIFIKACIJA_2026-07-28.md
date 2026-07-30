# Repro-Grav — prijedlog SEO i AI-friendly poboljšanja kataloga

**Datum:** 2026-07-28  
**Analizirano:** lokalna razvojna verzija i produkcija `www.repro-grav.com`

## Predloženi predmet maila

Repro-Grav — SEO i AI-friendly tehnička specifikacija

## Tekst za uvod maila

Pozdrav,

u nastavku šaljem prijedlog SEO i AI-friendly poboljšanja za Repro-Grav katalog, izrađen nakon pregleda projekta, lokalne stranice i produkcije.

Glavni prioriteti su:

1. zaštititi poslovno pravilo da cijenu i dostupnost vide samo prijavljeni korisnici;
2. ukloniti duplikate i zastarjele URL-ove iz sitemapova;
3. popraviti canonical, structured data, blog i meta podatke;
4. olakšati Googleu, Bingu i AI tražilicama razumijevanje proizvoda, kategorija, namjene i tehničkih karakteristika — bez javnog otkrivanja cijene ili zalihe;
5. postaviti automatske provjere kako se isti problemi ne bi vraćali.

Detalji, nalazi i kriteriji prihvata nalaze se u nastavku.

Lijep pozdrav

---

## 1. Kontekst i cilj

Repro-Grav je B2B katalog za pečatare i gravere. Korisnici sve češće koriste Google, Bing, ChatGPT, Claude, Perplexity i druge AI sustave za pronalazak dobavljača, usporedbu strojeva i materijala te odgovore na konkretna pitanja, primjerice:

- koji je materijal prikladan za lasersko graviranje;
- koji proizvod odgovara određenom stroju ili načinu obrade;
- koje su dimenzije, boje, debljine i ostale tehničke karakteristike;
- nudi li Repro-Grav određeni stroj, materijal, alat, servis ili podršku;
- kako kontaktirati prodaju i zatražiti ponudu.

Cilj nije samo klasični SEO, nego tehnički urediti katalog tako da bude razumljiv:

- tražilicama;
- AI search/discovery crawlerima;
- korisnicima koji dolaze iz generativnih odgovora;
- budućim integracijama koje smiju pristupiti katalogu.

AI-friendly katalog u ovom slučaju ima četiri sloja:

1. crawler mora moći pristupiti javnim stranicama bez blokade;
2. proizvodi, kategorije, tvrtka, blog i navigacija moraju imati validne i međusobno usklađene podatke;
3. sitemapovi, canonical URL-ovi, redirecti i robots pravila moraju slati jednoznačne i svježe signale;
4. javni sadržaj mora jasno opisivati proizvode i primjenu, ali ne smije otkriti podatke rezervirane za prijavljene korisnike.

## 2. Obavezno poslovno pravilo: cijena i dostupnost nisu javni

Na Repro-Grav stranici **cijenu i dostupnost smiju vidjeti samo prijavljeni korisnici**. To je namjerno ponašanje i nije SEO greška.

Ovo pravilo mora vrijediti na svim javno dostupnim slojevima, ne samo u vizualnom HTML prikazu:

- gost ne vidi cijenu, akcijsku cijenu ni dostupnost;
- javni `Product` JSON-LD ne sadrži `Offer`, `price`, `salePrice`, `lowPrice`, `highPrice`, `priceCurrency`, `availability` ni količinu;
- javni Open Graph i Twitter meta podaci ne sadrže cijenu ili stanje zalihe;
- javni XML, JSON, NDJSON, Google Base, Merchant ili AI feed ne smije otkriti cijenu i dostupnost;
- endpoint koji sadrži cijenu ili dostupnost mora biti privatan i zaštićen provjerom ovlasti, a ne samo skriven od navigacije ili blokiran u `robots.txt`;
- cache ne smije pomiješati odgovor za prijavljenog korisnika s odgovorom za gosta.

### Važan SEO kompromis

Google za puni product/merchant prikaz očekuje javno dostupne i provjerljive komercijalne podatke. Ako javna stranica namjerno nema ponudu, cijenu i dostupnost, Repro-Grav svjesno ograničava mogućnost dobivanja pojedinih product/merchant rich rezultata.

To ne sprječava indeksiranje proizvoda ni organsko rangiranje za naziv, kategoriju, namjenu, brand i tehničke karakteristike. Fokus treba prebaciti na:

- kvalitetan naslov i opis;
- ispravne slike i identifikatore;
- brand, SKU/MPN i kategoriju;
- tehničke atribute i primjene;
- kvalitetno interno povezivanje;
- stručni sadržaj i blog;
- jasan poziv na prijavu ili upit za ponudu.

Ne preporučuje se dodavanje lažnih recenzija ili umjetno generiranih ocjena radi ispunjavanja Google uvjeta.

## 3. Opseg provedene provjere

Provjera je napravljena 2026-07-28 i uključivala je:

- pregled OpenCart/PHP projekta;
- lokalno otvaranje naslovnice, proizvoda, kategorija, paginacije, bloga i članaka;
- pregled server-renderiranog HTML-a, canonical oznaka, meta podataka, heading strukture i JSON-LD-a;
- provjeru lokalnih i produkcijskih HTTP statusa;
- test više crawler user-agentova;
- pregled `robots.txt`, sitemap indeksa i sitemap datoteka;
- uzorkovanje product, category i information URL-ova iz sitemapova;
- pregled redirecta za HTTP/HTTPS i `www`/non-`www` varijante;
- osnovni pregled slika, skripti i stilova u HTML-u;
- provjeru Google Base rute;
- pregled relevantnih controller, model, Twig, `.htaccess` i sitemap generator datoteka.

Ovo je tehnički audit i specifikacija implementacije. Ne uključuje podatke iz Google Search Consolea, Google Analyticsa, server logova ni pravi terenski Core Web Vitals izvještaj, jer ti pristupi nisu bili dio provjere.

## 4. Pozitivno trenutno stanje

- Stranice su server-renderirane i glavni sadržaj je dostupan bez JavaScripta.
- Produkcija vraća HTTP 200 za Googlebot, Bingbot i testirane AI crawlere.
- Proizvod ima vidljiv H1, canonical URL, opis, specifikacije, katalog broj i povezane proizvode.
- Category stranice imaju H1, canonical, crawlable proizvode, breadcrumb i `ItemList` JSON-LD.
- Paginirane category stranice imaju zaseban canonical te `prev`/`next` linkove.
- Naslovnica ima smislen title i meta description.
- Postoji sitemap infrastruktura podijeljena po tipovima sadržaja.
- Statički CSS i slike na produkciji imaju cache headere i gzip je aktivan.
- Vidljivi dio product templatea ispravno prikazuje cijenu samo prijavljenim korisnicima.

To znači da se većina posla može napraviti nadogradnjom postojeće infrastrukture, bez promjene osnovnog poslovnog modela kataloga.

## 5. Sažetak najvažnijih nalaza

### P0 — javni structured data otkriva skrivenu cijenu i dostupnost

Na testiranom proizvodu `laser-paper-wood` gost u vidljivom sadržaju ispravno ne vidi cijenu ni dostupnost. Međutim, javni JSON-LD trenutno sadrži:

- trenutnu cijenu;
- akcijsku cijenu;
- `availability: InStock`;
- zastarjeli `priceValidUntil: 2022-12-31`.

Javni Open Graph sloj također može izložiti komercijalne podatke. To je u suprotnosti s pravilom stranice i treba biti prvi popravak.

### P0 — sitemapovi su zastarjeli i sadrže duplikate

Produkcijski sitemap indeks sadrži šest sitemap datoteka i ukupno približno 1.615 zapisa:

| Sitemap | Broj zapisa |
|---|---:|
| category | 70 |
| category_product | 598 |
| category_product_1 | 500 |
| category_product_2 | 98 |
| information | 26 |
| product | 323 |

Svi zapisi u sitemap indeksu imaju `lastmod` od 2025-11-13, iako je provjera napravljena 2026-07-28.

Canonical product sitemap ima približno 315 jedinstvenih slugova, a category-product sitemapovi približno 318. Preklapa se oko 312 proizvoda. To znači da sitemapovi za isti proizvod prijavljuju i kratki canonical URL i dodatne category-path URL-ove.

U uzorku prvih 50 product URL-ova pronađen je i HTTP 404:

`https://www.repro-grav.com/r26-postanski-datumar`

Sitemap treba sadržavati samo indeksabilne canonical URL-ove koji vraćaju HTTP 200.

### P1 — canonical i redirect signali nisu potpuno usklađeni

- Naslovnica nema canonical kad se otvara na root URL-u.
- Blog index nema canonical.
- `http://www.repro-grav.com/` vraća HTTP 200 umjesto redirekta na HTTPS.
- Non-`www` varijante se ispravno preusmjeravaju, ali treba osigurati jedan dosljedan redirect korak na `https://www.repro-grav.com/`.
- Sirova ruta `index.php?route=common/home` vraća HTTP 200, a naslovnica bez canonicala povećava mogućnost duplikata.

### P1 — većina product i category stranica nema meta description

U lokalnom uzorku:

- 29 od 29 ispravnih testiranih product stranica nema meta description;
- 29 od prvih 30 testiranih category stranica nema meta description;
- jedan product URL iz uzorka vraća 404;
- većina titleova je samo kratki naziv proizvoda ili kategorije, bez dodatnog konteksta;
- jedna category meta description vrijednost ima približno 261 znak i preduga je.

Naslovnica je znatno bolje uređena od unutarnjih stranica.

### P1 — blog ima tehničke SEO probleme

- Blog članci koriste query URL-ove poput `index.php?route=extension/blog/blog&blog_id=41`, bez čitljivog SEO sluga.
- Blog index nema canonical ni blog structured data.
- Testirani članak nema meta description, Open Graph ni Twitter opis.
- `NewsArticle` JSON-LD na testiranom članku nije validan JSON.
- `mainEntityOfPage` je hardkodiran na `https://google.com/article`.
- Glavna slika je relativan URL.
- Datum objave i izmjene nisu popunjeni.
- Blog članci nisu uključeni u sitemap.

### P1 — Organization/Store schema sadrži konfliktne ili pogrešne podatke

Na naslovnici se generira više djelomično preklopljenih entiteta:

- `OnlineStore`;
- `WebSite`;
- dodatni `Store`.

Pronađeni problemi:

- logo pokazuje na banner sliku umjesto stvarnog logotipa;
- jedan `@id` ima tipfeler `https://rwww.repro-grav.com/`;
- dio polja je prazan;
- `sameAs` vodi samo na `troteclaser.hr` Instagram, dok drugi dio stranice koristi `reprograv.hr`;
- footer također vodi na pogrešan Instagram profil;
- `SearchAction` URL sadrži literalni `&amp;`;
- NAP i društveni profili nisu predstavljeni kao jedan konzistentan entitet.

### P1 — Product JSON-LD ima dodatne tehničke greške

Osim curenja cijene i dostupnosti:

- slika može biti relativan URL;
- brand se generira i kad je naziv prazan;
- brand URL može sadržavati `&amp;`;
- opis zadržava HTML entitete;
- prazni `review` i `aggregateRating` nizovi se nepotrebno ispisuju;
- `salePrice` nije standardni Google način označavanja aktivne prodajne cijene;
- seller se zbog tipfelera ne dodaje u Offer;
- breadcrumb na testiranom proizvodu sadrži samo trenutni proizvod umjesto punog puta.

Za Repro-Grav javnu verziju se Offer uopće ne treba ispisivati gostu. Ostala Product polja ipak treba popraviti.

### P1 — robots i lokalna razvojna verzija nisu potpuno usklađeni

- Produkcijski `robots.txt` vraća HTTP 200 i pokazuje na `sitemap-index.xml`.
- Lokalni `robots.txt` ima sadržaj, ali zbog lokalnog drivera vraća HTTP 404.
- `/sitemap.xml` vraća 404, a koristi se `/sitemap-index.xml`.
- Robots datoteka sadrži duplicirana i teško održiva pravila za `sort`, `limit` i `page`.

### P2 — indexation pravila za paginaciju i filtere se ne ispisuju

Category controller pokušava postaviti `noindex,follow` za stranicu 2 i dalje, ali theme header ne ispisuje vrijednost `robots`. Zbog toga željeno pravilo nije prisutno u HTML-u.

Potrebno je definirati jednu jasnu politiku za:

- category paginaciju;
- sort i limit parametre;
- filter URL-ove;
- interne rezultate pretrage;
- canonical ponašanje na varijantama URL-a.

Robots blokada nije zamjena za canonical i kvalitetnu indexation politiku.

### P2 — naslovnica nema H1 i ima prostor za bolju semantiku

Naslovnica počinje nižim heading razinama i nema glavni H1. Preporučen je jedan smislen H1, primjerice:

`Sve za pečatare i gravere`

H1 se može vizualno uklopiti u postojeći dizajn bez velikog bannera.

### P2 — slike i skripte nude Core Web Vitals quick win

Na lokalnoj naslovnici pronađeno je približno:

- 67 slika;
- 14 slika bez korisnog `alt` opisa;
- samo 11 lazy-loaded slika;
- nijedna analizirana slika nema eksplicitni `width` i `height`;
- 28 vanjskih skripti;
- 25 skripti u `<head>` dijelu;
- gotovo nijedna skripta nije označena s `defer`;
- 12 stylesheetova.

Dio slidera učitava istu sliku više puta. Ovo nisu izmjereni produkcijski Core Web Vitals rezultati, nego konkretne tehničke prilike koje treba potvrditi Lighthouse/PageSpeed mjerenjem prije i poslije zahvata.

### P2 — Google Base feed trenutno nije upotrebljiv

`/googlebase.xml` i izravna Google Base ruta vraćaju HTTP 200, ali `text/html` i prazan body. Ruta također postavlja session/language/currency cookieje.

Zbog pravila skrivene cijene nije preporučeno samo “popraviti i javno objaviti” feed. Ako je feed potreban partneru ili internom sustavu, treba ga napraviti kao autentificirani B2B endpoint.

### P3 — `/llms.txt` ne postoji

`/llms.txt` trenutno vraća 404. Datoteka je opcionalan dodatni signal i još nije zamjena za sitemap, dobar HTML, schema.org ili interne linkove. Može se napraviti tek nakon važnijih tehničkih popravaka.

## 6. Live audit produkcije

Provjera je napravljena 2026-07-28.

### HTTP i crawler pristup

| Resurs / user-agent | Rezultat |
|---|---|
| Naslovnica | HTTP 200 |
| `robots.txt` | HTTP 200, `text/plain` |
| `sitemap-index.xml` | HTTP 200, XML |
| `sitemap.xml` | HTTP 404 |
| `llms.txt` | HTTP 404 |
| Googlebot | HTTP 200 |
| Bingbot | HTTP 200 |
| OAI-SearchBot | HTTP 200 |
| ChatGPT-User | HTTP 200 |
| GPTBot | HTTP 200 |
| Claude-SearchBot | HTTP 200 |
| Claude-User | HTTP 200 |
| ClaudeBot | HTTP 200 |
| PerplexityBot | HTTP 200 |

Pozitivno je da na testu nije pronađena WAF/CDN blokada za navedene crawlere.

### Redirecti

| Ulazni URL | Rezultat |
|---|---|
| `http://repro-grav.com/` | 301 na HTTPS `www` |
| `https://repro-grav.com/` | 301 na HTTPS `www` |
| `http://www.repro-grav.com/` | HTTP 200 — treba 301 na HTTPS |
| `https://www.repro-grav.com/` | HTTP 200 |
| `https://www.repro-grav.com/index.php?route=common/home` | HTTP 200 |

### Sitemap kvaliteta

- šest sitemap datoteka validno je kao XML;
- sitemap indeks i generirane datoteke imaju zastarjele datume;
- isti proizvodi se pojavljuju kroz canonical i category-path URL-ove;
- pronađen je najmanje jedan 404 product URL;
- blog nije uključen;
- category-product i information sitemapovi nemaju koristan URL-level `lastmod`;
- product generator uključuje samo proizvode sa slikom, pa treba provjeriti izostavlja li aktivne proizvode bez slike;
- sitemap indeks se generira dinamički i postavlja session/language/currency cookieje.

## 7. Konkretne tehničke lokacije u projektu

Sljedeće lokacije predstavljaju glavna mjesta implementacije:

- `upload/catalog/model/extension/module/hb_seo_snippets.php`  
  Generiranje Product, Offer, Organization/Store, SearchAction, ItemList i breadcrumb podataka.

- `upload/catalog/controller/product/product.php`  
  Product canonical, slike, opis, podaci o brandu i postojeće pravilo prikaza cijene samo prijavljenima.

- `upload/catalog/controller/product/category.php`  
  Category meta podaci, canonical, paginacija, opis i product URL-ovi.

- `upload/catalog/view/theme/basel/template/common/header.twig`  
  Meta description, canonical linkovi, robots meta i veliki dio CSS/JS učitavanja.

- `upload/catalog/view/theme/basel/template/common/home.twig`  
  H1 i semantika naslovnice.

- `upload/catalog/view/theme/basel/template/product/product.twig`  
  Vidljivi product sadržaj i conditional prikaz cijene.

- `upload/catalog/view/theme/basel/template/product/category.twig`  
  Category H1, opis i product listing.

- `upload/catalog/controller/common/home.php`  
  Canonical naslovnice.

- `upload/catalog/controller/common/header.php` i `upload/system/library/document.php`  
  Robots vrijednost postoji u controller/document sloju, ali se trenutačno ne ispisuje u themeu.

- `upload/catalog/controller/extension/blog/blog.php`  
  Podaci članka, canonical, meta podaci, slike, datum i URL.

- `upload/catalog/controller/extension/blog/home.php`  
  Blog index meta podaci i canonical.

- `upload/catalog/view/theme/basel/template/blog/blog.twig`  
  Ručno sastavljen i trenutno nevalidan Article JSON-LD.

- `upload/admin/controller/extension/feed/boost_sitemap.php`  
  Generiranje canonical product i dupliciranih category-product sitemap URL-ova.

- `upload/catalog/controller/extension/feed/boost_sitemap.php`  
  Dinamički sitemap indeks i `lastmod` prema vremenu datoteke.

- `upload/LocalValetDriver.php`  
  Lokalno posluživanje `robots.txt`, sitemapova i posebnih ruta.

- `upload/robots.txt`  
  Crawler pravila i sitemap lokacija.

- `upload/.htaccess`  
  HTTP/HTTPS, `www` redirecti i sitemap/feed rute.

- `upload/catalog/view/theme/basel/template/common/footer.twig`  
  Pogrešan društveni link.

## 8. Preporučene stavke

| # | Stavka | Opis |
|---|---|---|
| 1 | HTTPS, `www` i canonical normalizacija | Jedan 301 korak na `https://www.repro-grav.com`, canonical na naslovnici i blog indexu, normalizacija `common/home` i provjera svih glavnih tipova stranice. |
| 2 | Sitemap, robots i lokalna parity provjera | Ukloniti category-product duplikate i 404 URL-ove, dodati blog, generirati stvarni `lastmod`, uključiti sve aktivne canonical proizvode, automatski osvježavati sitemap, očistiti robots pravila i osigurati lokalni HTTP 200. |
| 3 | Product JSON-LD privatnost i ispravnost | U javnoj verziji potpuno ukloniti Offer/cijenu/dostupnost; koristiti apsolutne slike, čist opis, valjan brand/SKU/MPN, izostaviti prazna polja, popraviti breadcrumb i ukloniti zastarjele vrijednosti. Isto pravilo primijeniti na OG/Twitter podatke i cache. |
| 4 | Organization, WebSite, category i breadcrumb schema | Konsolidirati duple Store entitete, ispraviti logo, URL, profile i NAP, validirati SearchAction, zadržati kvalitetan ItemList i uskladiti pune breadcrumbe. |
| 5 | Blog tehnički SEO | SEO slugovi i 301 sa starih query URL-ova, canonical, meta/OG/Twitter, validan `BlogPosting`/`Article` JSON-LD kroz `json_encode`, pravi datumi, apsolutne slike, blog sitemap i interno povezivanje. |
| 6 | Title/meta predlošci i naslovnica | Skalabilni title i meta-description fallbacki, zaštita duljine i kvalitete, H1 na naslovnici te prioritetni ručni tekstovi za najvažnije stranice. |
| 7 | Indexation, filteri i paginacija | Početi ispisivati robots meta, definirati pravilo za paginaciju/filter/sort/search URL-ove, provjeriti canonical i crawlable linkove. |
| 8 | Performance i slike — quick wins | Odgoditi nekritične skripte, smanjiti blokiranje u headu, ukloniti sigurne duplikate, dodati dimenzije slika, alt, lazy loading ispod pregiba, prioritizirati LCP sliku i provjeriti WebP/AVIF/srcset. |
| 9 | AI crawler politika i opcionalni `llms.txt` | Dokumentirati search/discovery naspram training botova, uskladiti robots/WAF politiku, testirati statuse i po želji dodati kratak `llms.txt` bez poslovno osjetljivih podataka. |
| 10 | Monitoring i regresijski testovi | Automatski testovi za crawler HTTP status, redirect/canonical, sitemap svježinu i 404, validan JSON-LD, metadata coverage te obaveznu potvrdu da anonimni odgovor ne sadrži cijenu/dostupnost. |
| 11 | Deploy, Search Console i završni QA | Staging/produkcijska provjera, Rich Results/Schema validacija, predaja novih sitemapova Search Consoleu, crawl inspection uzorka i zapis rezultata prije/poslije. |

### Osnovni paket

Osnovni paket uključuje svih 11 stavki iz tablice i predstavlja preporučeni opseg.

## 9. Opcionalne stavke

| # | Stavka | Opis |
|---|---|---|
| 12 | Prioritetni SEO/content paket | Ručna dorada naslovnice, do 10 najvažnijih kategorija i predložaka/top proizvoda: primjene, kompatibilnost, tehnički atributi, FAQ i poveznice prema relevantnim proizvodima. |
| 13 | Privatni B2B feed ili API | Autentificirani read-only feed/API za odobrene partnere ili interne AI alate. Cijena i dostupnost smiju se vratiti samo nakon provjere ovlasti; endpoint nije javno indeksabilan. |

### Prošireni paket

Osnovni paket + obje opcionalne stavke:

Javni OpenAI/merchant product feed nije uključen u osnovni paket. Trenutna OpenAI product feed specifikacija traži cijenu i dostupnost, a Repro-Grav ih namjerno ne prikazuje gostima. Dodatno, aktualna specifikacija ima ograničenja vezana uz podržano tržište SAD-a. Zato je za Repro-Grav trenutno prikladniji privatni B2B feed, ako za njega postoji konkretan partner ili interna potreba.

## 10. Predloženi redoslijed izvedbe

### Faza 1 — zaštita podataka i tehnički signali

1. uklanjanje cijene/dostupnosti iz anonimnog JSON-LD-a, meta podataka i javnih endpointa;
2. HTTPS/`www` redirecti i canonical;
3. sitemap/robots čišćenje;
4. testovi koji potvrđuju da gost nigdje ne dobiva cijenu ili dostupnost.

### Faza 2 — strukturirani podaci i metadata

1. Product i breadcrumb refaktor;
2. Organization/WebSite konsolidacija;
3. title/meta fallbacki;
4. category, paginacija i indexation pravila;
5. H1 i semantika naslovnice.

### Faza 3 — blog, sadržaj i brzina

1. blog URL-ovi, schema, sitemap i meta podaci;
2. performance/image quick wins;
3. prioritetni category i product content, ako se ugovori opcionalni paket.

### Faza 4 — monitoring i produkcijski QA

1. automatizirani regresijski testovi;
2. staging i produkcijski crawl;
3. Search Console predaja;
4. dokumentirani rezultati i lista eventualnih content zadataka.

## 11. Predloženi metadata obrasci

Primjeri trebaju biti fallback, uz mogućnost ručne dorade važnih stranica.

### Product title

`{Naziv proizvoda} | {Brand ili kategorija} | Repro-Grav`

Primjer:

`Laser Paper Wood | Materijali za graviranje | Repro-Grav`

### Product meta description

`{Naziv proizvoda} za {glavna primjena}. Pogledajte tehničke karakteristike i prijavite se za cijenu i dostupnost kod Repro-Grava.`

### Category title

`{Naziv kategorije} – oprema i materijali | Repro-Grav`

### Category meta description

`Pregledajte {naziv kategorije} za profesionalne pečatare i gravere. Tehnički podaci, primjene i podrška Repro-Grava.`

Fallback mora:

- ukloniti HTML i entitete;
- spriječiti duple i generičke rečenice;
- imati kontroliranu duljinu;
- ne ubacivati cijenu ili dostupnost;
- koristiti samo podatke koji stvarno postoje za proizvod.

## 12. Predloženi javni Product JSON-LD

Javni anonimni markup treba opisivati proizvod bez ponude:

```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "@id": "https://www.repro-grav.com/primjer-proizvoda#product",
  "url": "https://www.repro-grav.com/primjer-proizvoda",
  "name": "Naziv proizvoda",
  "description": "Čist tekstualni opis bez HTML-a.",
  "image": [
    "https://www.repro-grav.com/image/catalog/primjer.jpg"
  ],
  "sku": "KATALOG-BROJ",
  "mpn": "MODEL-AKO-POSTOJI",
  "brand": {
    "@type": "Brand",
    "name": "Brand ako postoji"
  },
  "category": "Materijali za graviranje",
  "additionalProperty": [
    {
      "@type": "PropertyValue",
      "name": "Debljina",
      "value": "Primjer vrijednosti"
    }
  ]
}
```

U anonimnoj verziji namjerno nema:

- `offers`;
- `price`;
- `salePrice`;
- `priceCurrency`;
- `availability`;
- `inventoryLevel`;
- `priceValidUntil`.

Brand, MPN i dodatna svojstva ispisuju se samo ako stvarna vrijednost postoji. JSON-LD treba graditi kroz PHP polje i `json_encode`, a ne ručnim spajanjem JSON stringova u Twig templateu.

## 13. AI crawler politika

Preporučena početna politika:

- dopustiti `Googlebot` i `Bingbot`;
- dopustiti discovery/search crawlere kao što su `OAI-SearchBot`, `Claude-SearchBot` i `PerplexityBot`;
- dopustiti user-triggered agente kao što su `ChatGPT-User` i `Claude-User`, ako nema drugog sigurnosnog razloga za blokadu;
- zasebno donijeti poslovnu odluku za training crawlere kao što su `GPTBot` i `ClaudeBot`;
- WAF pravila, ako se uvedu, temeljiti na službenim IP rasponima i reverse-DNS/provjeri gdje je primjenjivo, a ne samo na lako lažiranom user-agent stringu.

Bez obzira na crawler politiku, javni crawler dobiva samo podatke dostupne gostu. Dopušten pristup crawleru nije dopuštenje za otkrivanje cijene ili zalihe.

## 14. Kriteriji prihvata

Rad se smatra tehnički prihvaćenim kad vrijedi sljedeće:

### Privatnost cijene i dostupnosti

- anonimni HTML ne prikazuje cijenu ni dostupnost;
- anonimni source, JSON-LD, OG/Twitter, inline JavaScript, API/XHR i javni feedovi ne sadrže cijenu ni dostupnost;
- prijavljeni korisnik i dalje vidi podatke prema postojećim pravilima;
- cache test potvrđuje da anonimni korisnik ne može dobiti prijavljenu verziju;
- automatizirani test pada ako se `price`, `salePrice`, `availability` ili stvarne vrijednosti slučajno pojave u anonimnom odgovoru.

### Canonical i redirecti

- sve HTTP i non-`www` varijante idu jednim 301 korakom na HTTPS `www`;
- svaka indexable stranica ima odgovarajući self-canonical;
- naslovnica i blog index imaju canonical;
- duplikati glavnih ruta imaju 301 ili ispravan canonical prema dogovorenoj politici.

### Sitemap i robots

- sitemap sadrži samo canonical HTTP 200 URL-ove;
- nema category-path duplikata proizvoda;
- nema 3xx, 4xx ili 5xx URL-ova;
- `lastmod` odgovara stvarnoj promjeni sadržaja;
- blog članci su uključeni;
- sitemap se automatski osvježava;
- `robots.txt` vraća HTTP 200 lokalno i na produkciji;
- sitemap odgovor je stabilan XML bez nepotrebnog session cookieja gdje je izvedivo.

### Structured data

- svi JSON-LD blokovi prolaze JSON i schema validaciju;
- slike i URL-ovi su apsolutni;
- nema praznog branda, praznih rating/review blokova ni zastarjelih cijena;
- puni breadcrumb odgovara vidljivoj navigaciji;
- Organization/WebSite podaci koriste konzistentan `@id`, logo, NAP i društvene profile;
- blog članci imaju stvarni canonical, datum, autora, izdavača i apsolutnu sliku.

### Metadata i sadržaj

- sve indexable product i category stranice imaju smislen title;
- sve prioritetne stranice imaju kvalitetan meta description;
- fallback ne generira prazne, duple, preduge ili cijenom obogaćene opise;
- naslovnica ima jedan smislen H1;
- visokovrijedne kategorije imaju kratak opis namjene i ključne interne linkove.

### Performance

- slike ispod pregiba koriste lazy loading;
- LCP slika nije pogrešno lazy-loadana;
- ključne slike imaju dimenzije;
- nekritične skripte ne blokiraju prvi prikaz gdje ih je sigurno odgoditi;
- Lighthouse/PageSpeed usporedba prije i poslije je spremljena; završni kriterij prihvata je zelena ocjena za Performance, Accessibility, Best Practices i SEO, zasebno na mobitelu i desktopu, potvrđena na produkciji nakon puštanja.

## 15. Napomene i ograničenja

- SEO i AI discovery radovi nisu garancija pozicije, citiranja ili prikaza u pojedinom AI sustavu.
- Svaki sustav ima vlastiti crawl, indeks, ranking i politiku prikaza.
- Zadržavanje cijene i dostupnosti iza prijave namjerno smanjuje dio merchant/product rich-result mogućnosti.
- Search Console, Analytics i server-log podaci mogu promijeniti prioritete nakon početka rada.
- Specifikacija pretpostavlja da postojeći OpenCart projekt ostaje tehnička osnova i da nema potpune promjene teme ili platforme.
- Opsežno ručno pisanje pojedinačnih product opisa nije uključeno u osnovni paket.
- Troškovi vanjskih modula, servisa, hostinga ili licenci nisu uključeni.
- Privatni feed/API treba raditi samo ako postoji jasan primatelj, način autentikacije i poslovno pravilo ovlasti.

## 16. Zaključak

Repro-Grav već ima dobru osnovu: server-renderiran katalog, stvarne opise i specifikacije, canonical na product/category stranicama, ItemList za kategorije i otvoren pristup crawlerima.

Najveći trenutni rizici nisu nedostatak novog AI sadržaja, nego:

1. skrivena cijena i dostupnost ipak cure kroz javni structured-data sloj;
2. sitemapovi su zastarjeli, duplicirani i sadrže najmanje jedan 404;
3. canonical/redirect, blog, Organization schema i metadata nisu ujednačeni;
4. ne postoje regresijski testovi koji bi čuvali privatnost i kvalitetu SEO signala.

Preporuka je prvo provesti osnovni paket. Time se katalog tehnički uređuje za Google, Bing i AI discovery, uz potpuno očuvanje pravila da cijenu i dostupnost vide samo prijavljeni korisnici.

## 17. Izvori i relevantne smjernice

- [Google Search Central — Canonical i duplicate URL-ovi](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls)
- [Google Search Central — Izrada i slanje sitemapa](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap)
- [Google Search Central — Product structured data](https://developers.google.com/search/docs/appearance/structured-data/product)
- [Google Search Central — Product snippet structured data](https://developers.google.com/search/docs/appearance/structured-data/product-snippet)
- [Google Search Central — Article structured data](https://developers.google.com/search/docs/appearance/structured-data/article)
- [Google Search Central — Ecommerce SEO](https://developers.google.com/search/docs/specialty/ecommerce)
- [Google Search Central — Struktura ecommerce weba](https://developers.google.com/search/docs/specialty/ecommerce/help-google-understand-your-ecommerce-site-structure)
- [Google Search Central — Titleovi i meta opisi za developere](https://developers.google.com/search/docs/fundamentals/get-started-developers)
- [Google Search Central — Paginacija i incremental loading](https://developers.google.com/search/docs/specialty/ecommerce/pagination-and-incremental-page-loading)
- [web.dev — Lazy loading slika i dimenzije](https://web.dev/articles/browser-level-image-lazy-loading)
- [OpenAI — Crawleri i user-agentovi](https://developers.openai.com/api/docs/bots)
- [OpenAI — Product feed specifikacija](https://developers.openai.com/commerce/specs/file-upload/products)
- [Perplexity — Crawleri i WAF smjernice](https://docs.perplexity.ai/docs/resources/perplexity-crawlers)
- [Anthropic — Claude crawleri](https://support.claude.com/en/articles/8896518-does-anthropic-crawl-data-from-the-web-and-how-can-site-owners-block-the-crawler)
- [llms.txt prijedlog](https://llmstxt.org/)
