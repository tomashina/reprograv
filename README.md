# Reprograv

OpenCart 3 projekt za lokalni razvoj u Laravel Herdu.

## Lokalno pokretanje

1. Kopiraj `.env.example` u `.env`.
2. U `.env` upiši pristup lokalnoj MySQL bazi `reprograv`.
3. U Herdu poveži direktorij `upload` s nazivom `reprograv` i koristi PHP 7.4.
4. Otvori `https://reprograv.test`.

Datoteka `upload/LocalValetDriver.php` omogućuje OpenCart SEO URL-ove u Herdu,
koji inače ne obrađuje Apacheova pravila iz `.htaccess` datoteke.

Početni SEO sadržaj kategorija i veze FAQ pitanja s kategorijama mogu se bez
Terminala uvesti kroz phpMyAdmin datotekom:

```text
sql/2026-07-30_category_seo.sql
```

Uvoz dodaje sadržaj samo potpuno praznim kategorijama. Postojeći i djelomično
ispunjeni ručni opisi ostaju nepromijenjeni.

Prazni SEO podaci aktivnih proizvoda dopunjuju se zasebnom idempotentnom
phpMyAdmin migracijom:

```text
sql/2026-07-30_product_seo.sql
```

Migracija ne mijenja postojeće opise, meta podatke ni SEO URL-ove.

Za lokalni razvoj ista se idempotentna instalacija može pokrenuti naredbom:

```bash
php tools/install_category_seo.php
```

Primjer Herd naredbe iz korijena projekta:

```bash
cd upload
herd link reprograv --secure --isolate=7.4
```

## Git i slike

Slike, videozapisi, generirani cache, logovi, sesije, download datoteke i
lokalni `.env` namjerno se ne spremaju u Git. Lokalni direktoriji i datoteke
ostaju netaknuti, pa lokalna stranica i dalje koristi sve postojeće slike.

Na drugom računalu slike i ostale medijske datoteke treba prenijeti zasebno u:

- `upload/image/`
- `upload/wp-content/uploads/`

Nakon prijenosa slike može biti potrebno očistiti sadržaj `upload/image/cache/`
kako bi ih OpenCart ponovno generirao.
