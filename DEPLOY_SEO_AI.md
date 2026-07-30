# Repro-Grav SEO/AI – puštanje na produkciju

Ove izmjene zadržavaju postojeće poslovno pravilo: gost ne vidi cijenu ni
dostupnost, a prijavljeni korisnik ih i dalje vidi. Javni strukturirani podaci,
analytics, pretraga, filtri i feedovi također ne smiju otkriti te podatke.

## 1. Sigurnosna kopija

Prije puštanja napraviti potpunu kopiju baze i ovih direktorija:

- `upload/catalog`
- `upload/admin`
- `upload/system`
- `upload/image`
- `upload/sitemaps`

## 2. Prijenos datoteka

Na produkciju prenijeti sve promijenjene datoteke iz repozitorija. Ne prenositi
lokalne konfiguracije `upload/config.php`, `upload/admin/config.php` ni
`upload/env.php`.

Datoteka `upload/LocalValetDriver.php` služi samo lokalnom Laravel Herd
okruženju i nije potrebna na produkcijskom Apache/nginx serveru.

## 3. SQL migracija

Pokrenuti:

```bash
mysql -u KORISNIK -p NAZIV_BAZE < sql/2026-07-29_seo_ai.sql
```

Migracija je idempotentna i smije se ponoviti. Pretpostavlja:

- prefiks tablica `oc_`
- zadani `store_id = 0`
- hrvatski `language_id = 3`

Ako produkcija koristi druge vrijednosti, prilagoditi ih u SQL datoteci prije
pokretanja. Sve izmjene baze za ovaj paket nalaze se u toj jednoj SQL datoteci.

## 4. Osvježavanje OCMOD-a

Iz korijena projekta pokrenuti:

```bash
php tools/refresh_ocmod.php
```

Isto se može napraviti kroz administraciju:
`Extensions > Modifications > Refresh`.

Nakon toga očistiti OpenCart i Basel/theme cache.

## 5. Generiranje sitemapova

Iz korijena projekta pokrenuti:

```bash
php tools/generate_sitemaps.php
```

Ako hosting nema Terminal/SSH, u OpenCart administraciji otvoriti
`Extensions > Extensions > Feeds > Boost Sitemap`, uključiti feed, označiti
Product, Category, Information i Blog te kliknuti `Generate XML Files`.
Generator će sam kreirati zapisivu mapu `upload/sitemaps` ako ona ne postoji.

Sitemap indeks mora biti dostupan na:

```text
https://www.repro-grav.com/sitemap-index.xml
```

Treba sadržavati sitemapove za proizvode, kategorije, informacije i blog, bez
starog `category_product` sitemap duplikata.

## 6. WebP generator

U administraciji otvoriti:
`Extensions > Extensions > Modules > WebP generator kataloga`.

Kliknuti `Generiraj sve WebP slike` i pričekati 100 %. Generator:

- čuva sve originalne JPG, PNG i postojeće WebP datoteke
- za svaku izvornu sliku stvara WebP kopiju u `upload/image/cache`
- za slike aktivnih proizvoda unaprijed stvara sve standardne dimenzije teme
- postojeće i ažurne datoteke preskače, pa se smije ponovno pokrenuti

Na produkciji PHP mora imati GD WebP podršku, a `upload/image/cache` mora biti
zapisiv PHP procesu. Generirani cache se ne sprema u Git.

## 7. Automatska provjera

Nakon produkcijskog puštanja pokrenuti:

```bash
php tests/seo_smoke.php https://www.repro-grav.com
```

Test mora završiti bez grešaka.

## 8. Cache, WebP i PageSpeed

Prije PageSpeed mjerenja:

- potvrditi da produkcija podržava `.htaccess` pravila za kompresiju i cache
  statičkih datoteka; na nginx hostingu prenijeti ista pravila u nginx
  konfiguraciju
- potvrditi da je `upload/image/cache` zapisiv PHP procesu
- završiti WebP generator do 100 % i zatim otvoriti naslovnicu jednom u
  aktualnom Chromeu kako bi se generirale eventualne dodatne responsivne
  dimenzije bannera
- očistiti OpenCart/Basel cache, a zatim CDN/proxy cache ako postoji

Nakon toga pokrenuti [PageSpeed Insights](https://pagespeed.web.dev/) za
`https://www.repro-grav.com/`, posebno za mobitel i desktop. Završni kriterij
prihvata je zelena ocjena za sve četiri kategorije:

- Performance
- Accessibility
- Best Practices
- SEO

Mjerenje ponoviti najmanje dva puta. Produkcijski PageSpeed rezultat ne može se
zaključno potvrditi prije prijenosa datoteka i SQL migracije jer ovisi o
produkcijskom serveru, cacheu i vanjskim skriptama.

Lokalna završna Lighthouse provjera 29. 7. 2026.:

- desktop, standardni desktop profil: **99 / 100 / 100 / 100**
- mobitel, bez lokalnog Chrome Paint Holding laboratorijskog kašnjenja:
  **98 / 100 / 100 / 100**
- mobilni TBT: **20 ms**, CLS: **0**, prijenos: približno **810 KiB**

Standardni headless Chrome na lokalnoj `.test` domeni povremeno dodaje oko dvije
sekunde `elementRenderDelay` kroz Paint Holding i tada umjetno spušta samo
mobilni Performance rezultat. To nije kriterij prihvata produkcije. Završni
kriterij ostaje službeni PageSpeed Insights na javnom produkcijskom URL-u nakon
primjene cache pravila; sve četiri ocjene moraju biti zelene.

## 9. Ručna provjera

U anonimnom prozoru provjeriti:

- naslovnicu, kategoriju, pretragu, proizvod, blog i blog članak
- da nema cijene, akcijske cijene, stanja zalihe ni dostupnosti
- da opcije proizvoda ne otkrivaju stanje ili dodatne cijene
- da dodavanje u košaricu vodi na prijavu
- da `/robots.txt`, `/llms.txt` i `/sitemap-index.xml` vraćaju HTTP 200

Zatim se prijaviti testnim korisnikom i potvrditi:

- prikaz cijena, akcijskih cijena i dostupnosti
- promjenu cijene kroz opcije
- dodavanje proizvoda u košaricu
- checkout i izračun ukupnog iznosa

## 10. Google alati

Nakon provjere poslati `sitemap-index.xml` u Google Search Console i pokrenuti
provjeru nekoliko reprezentativnih URL-ova. Product schema bez `offers` je
namjerna: stranica ostaje razumljiva tražilicama i AI sustavima, ali javno ne
objavljuje komercijalne podatke koje gost ne vidi.

## Povrat na staro

Ako se pojavi problem, vratiti sigurnosnu kopiju datoteka i baze, zatim ponovno
osvježiti OCMOD i cache. Ne vraćati samo generirani modification cache bez
odgovarajućih izvornih datoteka i zapisa u bazi.
