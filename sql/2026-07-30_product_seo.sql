-- Repro-Grav: dopuna praznih SEO podataka aktivnih proizvoda
-- Datum: 2026-07-30
--
-- Uvoz kroz cPanel:
--   phpMyAdmin > odaberi OpenCart bazu > Import > odaberi ovu datoteku > Import
--
-- Pretpostavke:
--   prefiks tablica: oc_
--   hrvatski language_id: 3
--   zadani store_id: 0
--
-- Skripta je idempotentna i smije se ponovno uvesti. Postojeći opisi, meta
-- naslovi, meta opisi i SEO URL-ovi ostaju nepromijenjeni. Dopunjavaju se samo
-- potpuno prazna polja aktivnih proizvoda.

SET NAMES utf8mb4;

-- Označi datum izmjene samo proizvodima kojima će ova migracija dopuniti
-- opis, meta naslov ili meta opis.
UPDATE `oc_product` p
INNER JOIN `oc_product_description` pd
    ON pd.product_id = p.product_id AND pd.language_id = 3
SET p.date_modified = NOW()
WHERE p.status = 1
  AND (
      TRIM(COALESCE(pd.description, '')) = ''
      OR TRIM(COALESCE(pd.meta_title, '')) = ''
      OR TRIM(COALESCE(pd.meta_description, '')) = ''
  );

-- Ručno napisani opisi za šest aktivnih proizvoda koji nisu imali nikakav
-- sadržaj. Upis se izvodi samo ako je opis i dalje prazan.
UPDATE `oc_product_description`
SET `description` = CASE `product_id`
    WHEN 27403 THEN '<p>Magnetna kopča STD namijenjena je pričvršćivanju identifikacijskih bedževa i pločica bez klasične igle. Sastoji se od magnetskog prihvata i dijela koji se lijepi na poleđinu oznake.</p><h2>Odabir i primjena</h2><ul><li>model i šifra: 19362;</li><li>prikladna za bedževe i lakše identifikacijske pločice;</li><li>prije izrade provjerite dimenzije oznake i položaj kopče;</li><li>prikladnost magneta provjerite prema odjeći, uvjetima rada i osjetljivosti korisnika na magnetska polja.</li></ul>'
    WHEN 27404 THEN '<p>Magnetna kopča B5 koristi se za pričvršćivanje bedževa i identifikacijskih oznaka bez probadanja odjeće. Kopča se postavlja na poleđinu pripremljene oznake, a magnetski dio drži je s unutarnje strane odjeće.</p><h2>Prije odabira</h2><ul><li>model i šifra: P715ME;</li><li>provjerite veličinu i masu bedža;</li><li>uskladite položaj kopče s raspoloživom površinom na poleđini;</li><li>uzmite u obzir uvjete korištenja i sigurnosna ograničenja magneta.</li></ul>'
    WHEN 27405 THEN '<p>Magnetna kopča M praktično je rješenje za pričvršćivanje personaliziranih bedževa i oznaka bez igle. Magnetski sustav olakšava postavljanje i skidanje oznake kada materijal, težina bedža i uvjeti uporabe to dopuštaju.</p><h2>Podaci za provjeru</h2><ul><li>model i šifra: 750ME;</li><li>dimenzije i masa gotove oznake;</li><li>položaj kopče na poleđini bedža;</li><li>prikladnost za korisnika i planirano radno okruženje.</li></ul>'
    WHEN 27406 THEN '<p>Samoljepljiva kopča s iglom služi pričvršćivanju bedževa, pločica i drugih lakših identifikacijskih oznaka. Samoljepljiva poleđina omogućuje montažu kopče na ravnu stražnju površinu pripremljenog proizvoda.</p><h2>Prije uporabe</h2><ul><li>model i šifra: 30364;</li><li>površina mora biti čista, suha i prikladna za lijepljenje;</li><li>provjerite dimenzije i masu oznake;</li><li>iglu koristite pažljivo i primjereno vrsti odjeće.</li></ul>'
    WHEN 27407 THEN '<p>Kopča s iglom i krokodil prihvatom omogućuje dva praktična načina pričvršćivanja identifikacijskih bedževa. Ovisno o odjeći i načinu uporabe, oznaka se može pričvrstiti iglom ili prihvatom bez dodatne obrade prednje strane.</p><h2>Odabir kopče</h2><ul><li>model i šifra: 19358;</li><li>provjerite raspoloživi prostor na poleđini bedža;</li><li>uskladite kopču s dimenzijama i masom oznake;</li><li>odaberite način pričvršćivanja prema vrsti odjeće i učestalosti uporabe.</li></ul>'
    WHEN 27440 THEN '<p>Brusilica za frezere namijenjena je preciznom održavanju i oštrenju odgovarajućih graverskih alata. Stabilno vođenje alata i pravilno podešavanje pomažu ujednačenom rezultatu prije ponovne uporabe frezera.</p><h2>Prije odabira i rada</h2><ul><li>provjerite kompatibilne vrste i promjere frezera;</li><li>usporedite prihvat alata i mogućnosti podešavanja;</li><li>slijedite upute proizvođača za oštrenje i održavanje;</li><li>koristite odgovarajuću zaštitnu opremu i siguran radni postupak.</li></ul>'
    ELSE `description`
END
WHERE `language_id` = 3
  AND `product_id` IN (27403, 27404, 27405, 27406, 27407, 27440)
  AND TRIM(COALESCE(`description`, '')) = '';

-- Jedinstveni i čitljivi meta naslovi. Model se dodaje kada je stvarno zadan.
UPDATE `oc_product_description` pd
INNER JOIN `oc_product` p ON p.product_id = pd.product_id
SET pd.meta_title = CASE
    WHEN CHAR_LENGTH(
        CONCAT(
            TRIM(pd.name),
            CASE
                WHEN TRIM(COALESCE(p.model, '')) NOT IN ('', '0')
                    AND LOCATE(TRIM(p.model), pd.name) = 0
                THEN CONCAT(' – ', TRIM(p.model))
                ELSE ''
            END,
            ' | Repro-Grav'
        )
    ) <= 65
    THEN CONCAT(
        TRIM(pd.name),
        CASE
            WHEN TRIM(COALESCE(p.model, '')) NOT IN ('', '0')
                AND LOCATE(TRIM(p.model), pd.name) = 0
            THEN CONCAT(' – ', TRIM(p.model))
            ELSE ''
        END,
        ' | Repro-Grav'
    )
    WHEN CHAR_LENGTH(CONCAT(TRIM(pd.name), ' | Repro-Grav')) <= 65
    THEN CONCAT(TRIM(pd.name), ' | Repro-Grav')
    ELSE TRIM(pd.name)
END
WHERE p.status = 1
  AND pd.language_id = 3
  AND TRIM(COALESCE(pd.meta_title, '')) = '';

-- Meta opisi koriste podatke konkretnog proizvoda i ne objavljuju cijenu ni
-- dostupnost koju gost na stranici ne može vidjeti.
UPDATE `oc_product_description` pd
INNER JOIN `oc_product` p ON p.product_id = pd.product_id
LEFT JOIN `oc_manufacturer` m ON m.manufacturer_id = p.manufacturer_id
SET pd.meta_description = CASE
    WHEN CHAR_LENGTH(
        CONCAT(
            TRIM(pd.name),
            CASE
                WHEN TRIM(COALESCE(p.model, '')) NOT IN ('', '0')
                    AND LOCATE(TRIM(p.model), pd.name) = 0
                THEN CONCAT(' (', TRIM(p.model), ')')
                ELSE ''
            END,
            CASE
                WHEN TRIM(COALESCE(m.name, '')) <> ''
                    AND LOCATE(TRIM(m.name), pd.name) = 0
                THEN CONCAT(' proizvođača ', TRIM(m.name))
                ELSE ''
            END,
            '. Tehnički podaci, opis i namjena proizvoda iz ponude Repro-Grava.'
        )
    ) <= 160
    THEN CONCAT(
        TRIM(pd.name),
        CASE
            WHEN TRIM(COALESCE(p.model, '')) NOT IN ('', '0')
                AND LOCATE(TRIM(p.model), pd.name) = 0
            THEN CONCAT(' (', TRIM(p.model), ')')
            ELSE ''
        END,
        CASE
            WHEN TRIM(COALESCE(m.name, '')) <> ''
                AND LOCATE(TRIM(m.name), pd.name) = 0
            THEN CONCAT(' proizvođača ', TRIM(m.name))
            ELSE ''
        END,
        '. Tehnički podaci, opis i namjena proizvoda iz ponude Repro-Grava.'
    )
    WHEN CHAR_LENGTH(
        CONCAT(
            TRIM(pd.name),
            CASE
                WHEN TRIM(COALESCE(p.model, '')) NOT IN ('', '0')
                    AND LOCATE(TRIM(p.model), pd.name) = 0
                THEN CONCAT(' (', TRIM(p.model), ')')
                ELSE ''
            END,
            '. Tehnički podaci, opis i namjena proizvoda iz ponude Repro-Grava.'
        )
    ) <= 160
    THEN CONCAT(
        TRIM(pd.name),
        CASE
            WHEN TRIM(COALESCE(p.model, '')) NOT IN ('', '0')
                AND LOCATE(TRIM(p.model), pd.name) = 0
            THEN CONCAT(' (', TRIM(p.model), ')')
            ELSE ''
        END,
        '. Tehnički podaci, opis i namjena proizvoda iz ponude Repro-Grava.'
    )
    ELSE CONCAT(
        TRIM(pd.name),
        '. Tehnički podaci, opis i namjena proizvoda iz ponude Repro-Grava.'
    )
END
WHERE p.status = 1
  AND pd.language_id = 3
  AND TRIM(COALESCE(pd.meta_description, '')) = '';

-- Nedostajući SEO URL-ovi aktivnih proizvoda. ON DUPLICATE KEY čini uvoz
-- sigurnim za ponavljanje, a odabrane ključne riječi nemaju kolizije u bazi.
INSERT INTO `oc_seo_url`
    (`store_id`, `language_id`, `query`, `keyword`)
VALUES
    (0, 3, 'product_id=27413', 'b6-6-automatski-numerator'),
    (0, 3, 'product_id=27449', 'plocica-za-vrata-215'),
    (0, 3, 'product_id=27451', 'plocica-za-vrata-510'),
    (0, 3, 'product_id=27452', 'plocica-za-vrata-910'),
    (0, 3, 'product_id=27454', 'plocica-za-vrata-920'),
    (0, 3, 'product_id=27453', 'plocica-za-vrata-940'),
    (0, 3, 'product_id=27455', 'plocica-za-vrata-950')
ON DUPLICATE KEY UPDATE
    `keyword` = IF(TRIM(`keyword`) = '', VALUES(`keyword`), `keyword`);

-- Kratki rezultat za phpMyAdmin. Za trenutačni katalog sve četiri vrijednosti
-- trebaju biti 363.
SELECT
    (
        SELECT COUNT(*)
        FROM `oc_product` p
        INNER JOIN `oc_product_description` pd
            ON pd.product_id = p.product_id AND pd.language_id = 3
        WHERE p.status = 1
          AND TRIM(COALESCE(pd.description, '')) <> ''
    ) AS `proizvodi_s_opisom`,
    (
        SELECT COUNT(*)
        FROM `oc_product` p
        INNER JOIN `oc_product_description` pd
            ON pd.product_id = p.product_id AND pd.language_id = 3
        WHERE p.status = 1
          AND TRIM(COALESCE(pd.meta_title, '')) <> ''
    ) AS `proizvodi_s_meta_naslovom`,
    (
        SELECT COUNT(*)
        FROM `oc_product` p
        INNER JOIN `oc_product_description` pd
            ON pd.product_id = p.product_id AND pd.language_id = 3
        WHERE p.status = 1
          AND TRIM(COALESCE(pd.meta_description, '')) <> ''
    ) AS `proizvodi_s_meta_opisom`,
    (
        SELECT COUNT(*)
        FROM `oc_product` p
        WHERE p.status = 1
          AND EXISTS (
              SELECT 1
              FROM `oc_seo_url` su
              WHERE su.store_id = 0
                AND su.language_id = 3
                AND su.query = CONCAT('product_id=', p.product_id)
                AND TRIM(COALESCE(su.keyword, '')) <> ''
          )
    ) AS `proizvodi_sa_seo_urlom`;
