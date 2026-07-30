-- Repro-Grav: SEO sadržaj kategorija i FAQ
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
-- Skripta je idempotentna i smije se ponovno uvesti.
-- Postojeći i djelomično ispunjeni ručni opisi ostaju sačuvani. Sadržaj se
-- dodaje samo kategorijama kojima su kratki opis, dugi opis i meta opis svi
-- potpuno prazni.

SET NAMES utf8mb4;

-- Starije instalacije možda nemaju polje za kratki opis kategorije.
SET @has_short_description := (
    SELECT COUNT(*)
    FROM `information_schema`.`COLUMNS`
    WHERE `TABLE_SCHEMA` = DATABASE()
      AND `TABLE_NAME` = 'oc_category_description'
      AND `COLUMN_NAME` = 'short_description'
);
SET @add_short_description_sql := IF(
    @has_short_description = 0,
    'ALTER TABLE `oc_category_description` ADD `short_description` TEXT NULL AFTER `description`',
    'SELECT 1'
);
PREPARE add_short_description_statement FROM @add_short_description_sql;
EXECUTE add_short_description_statement;
DEALLOCATE PREPARE add_short_description_statement;

CREATE TABLE IF NOT EXISTS `oc_faq_to_catalog_category` (
    `faq_id` int(11) NOT NULL,
    `category_id` int(11) NOT NULL,
    PRIMARY KEY (`faq_id`, `category_id`),
    KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TEMPORARY TABLE IF EXISTS `tmp_reprograv_category_seo`;
CREATE TEMPORARY TABLE `tmp_reprograv_category_seo` (
    `category_id` int(11) NOT NULL,
    `short_description` text NOT NULL,
    `description` text NOT NULL,
    `meta_description` varchar(255) NOT NULL,
    PRIMARY KEY (`category_id`)
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `tmp_reprograv_category_seo`
    (`category_id`, `short_description`, `description`, `meta_description`)
VALUES
(
    274,
    '<p>Pregledajte opremu i potrošni materijal za pečatarstvo: pečate, rezervne jastučiće, tinte, materijale za izradu pečata, suhe žigove te industrijske ručne pisače. Za brži odabir krenite od namjene, željene veličine otiska i podloge na kojoj će se oznaka koristiti.</p>',
    '<h2>Oprema i materijal za profesionalno pečatarstvo</h2><p>Kategorija Pečatarstvo povezuje <a href="/pecatarstvo/pecati">pečate</a>, <a href="/pecatarstvo/rezervni-jastucici">rezervne jastučiće</a>, <a href="/pecatarstvo/tinte">tinte</a> i ostali pribor potreban za izradu, korištenje i održavanje otiska. Dostupne su i zasebne kategorije za materijale za izradu pečata, suhe žigove, kliješta za plombiranje te jetStamp® industrijske ručne pisače.</p><h3>Kako suziti izbor</h3><ul><li>Odredite treba li vam tekstualni, datumski, brojčani, prijenosni ili industrijski uređaj.</li><li>Provjerite najveću potrebnu veličinu otiska i broj redaka teksta.</li><li>Za posebne podloge odaberite tintu prema materijalu i tehničkoj dokumentaciji.</li><li>Kod zamjene jastučića koristite točnu oznaku modela pečata.</li></ul>',
    'Pečati, rezervni jastučići, tinte, materijali i pribor za profesionalno pečatarstvo. Pregledajte rješenja i tehničke podatke Repro-Grava.'
),
(
    275,
    '<p>Odaberite pečat prema vrsti otiska, učestalosti korištenja i potrebnoj veličini teksta. U ponudi su Trodat i Reiner modeli za tekstualne, datumske i brojčane otiske, prijenosnu upotrebu i specifične profesionalne zadatke.</p>',
    '<h2>Kako odabrati odgovarajući pečat</h2><p>Najprije odredite sadržaj i najveće dimenzije otiska. Za uredsku i svakodnevnu upotrebu praktični su samobojajući modeli, dok su robusnije i specijalizirane izvedbe namijenjene češćem radu, datumima, numeriranju ili posebnim formatima. Pregledajte <a href="/pecatarstvo/pecati/trodat-pecati">Trodat pečate</a> i <a href="/pecatarstvo/pecati/reiner-pecati">Reiner pečate</a>.</p><h3>Podaci koje vrijedi provjeriti</h3><ul><li>najveća veličina otiska i preporučeni broj redaka;</li><li>tekstualni, datumski ili brojčani mehanizam;</li><li>stolna, prijenosna ili industrijska primjena;</li><li>oznaka kompatibilnog rezervnog jastučića.</li></ul>',
    'Trodat i Reiner pečati za tekstualne, datumske i brojčane otiske. Usporedite modele, veličine otiska i namjenu uz podršku Repro-Grava.'
),
(
    276,
    '<p>U kategoriji Graverstvo pronaći ćete materijale za graviranje, gotove proizvode za personalizaciju te pomoćni pribor za obradu i završnu doradu. Materijal birajte prema tehnologiji stroja, željenom izgledu, načinu obrade i uvjetima u kojima će se gotov proizvod koristiti.</p>',
    '<h2>Materijali, gotovi proizvodi i pribor za graviranje</h2><p>Ponuda je podijeljena na <a href="/graverstvo/materijali-za-graviranje">materijale za graviranje</a>, <a href="/graverstvo/gotovi-proizvodi">gotove proizvode</a> i <a href="/graverstvo/pomocni-pribor">pomoćni pribor</a>. Tako možete odvojeno birati osnovni materijal, predmet za personalizaciju te sredstva i pribor za pričvršćivanje, označavanje, čišćenje ili završnu obradu.</p><h3>Prije odabira provjerite</h3><ul><li>je li materijal namijenjen laserskoj ili mehaničkoj obradi;</li><li>odgovaraju li format i debljina radnom području stroja;</li><li>traži li primjena unutarnju ili vanjsku otpornost;</li><li>koji su pomoćni materijali potrebni za pripremu i završnu obradu.</li></ul>',
    'Materijali za graviranje, gotovi proizvodi i pomoćni pribor za lasersku i mehaničku obradu. Usporedite primjene i tehničke podatke.'
),
(
    297,
    '<p>Trodat pečati obuhvaćaju Printy, Professional, prijenosne, Typomatic i Classic linije, pečat olovke te specijalne modele i numeratore. Odaberite liniju prema učestalosti rada, vrsti otiska, potrebnoj prenosivosti i dimenziji otisne pločice.</p>',
    '<h2>Trodat pečati prema načinu korištenja</h2><p>Za svakodnevne tekstualne i datumske otiske pregledajte <a href="/pecatarstvo/pecati/trodat-pecati/printy-linija">Printy liniju</a>. Za intenzivniji rad dostupna je <a href="/pecatarstvo/pecati/trodat-pecati/professional-linija">Professional linija</a>, dok prijenosni modeli olakšavaju rad izvan ureda. Classic, Typomatic, specijalni modeli, pečat olovke i automatski numeratori pokrivaju dodatne načine primjene.</p><h3>Odabir modela</h3><p>Usporedite najveću veličinu otiska, broj redaka, oblik otiska i vrstu mehanizma. Oznaku modela sačuvajte jer se prema njoj najlakše pronalazi odgovarajući rezervni jastučić.</p>',
    'Trodat Printy, Professional, Classic, prijenosni i specijalni pečati. Usporedite linije, veličine otiska i kompatibilne jastučiće.'
),
(
    283,
    '<p>Rezervni jastučić mora odgovarati točnoj oznaci modela pečata. Prije odabira provjerite broj modela na kućištu, proizvođača i željenu boju otiska. Pregledajte Trodat i Reiner jastučiće prema liniji i modelu uređaja.</p>',
    '<h2>Kako pronaći odgovarajući rezervni jastučić</h2><p>Najsigurniji način odabira je usporedba pune oznake pečata s oznakom jastučića. Pregledajte <a href="/pecatarstvo/rezervni-jastucici/trodat-rezervni-jastucici">Trodat rezervne jastučiće</a> ili <a href="/pecatarstvo/rezervni-jastucici/reiner-rezervni-jastucici">Reiner rezervne jastučiće</a>, zatim odaberite pripadajuću liniju.</p><h3>Prije naručivanja</h3><ul><li>pročitajte oznaku modela s kućišta pečata;</li><li>provjerite je li riječ o tekstualnom, datumskom ili brojčanom modelu;</li><li>odaberite odgovarajuću boju otiska;</li><li>ako oznaka nije čitljiva, usporedite oblik i dimenzije uz stručnu podršku.</li></ul>',
    'Trodat i Reiner rezervni jastučići za pečate. Pronađite kompatibilan jastučić prema točnoj oznaci modela, liniji i boji otiska.'
),
(
    277,
    '<p>Birajte materijal za graviranje prema stroju i završnoj primjeni. Dostupne su plastične, metalne i akrilne ploče, drvo, papir, EVA pjena i koža. Prije obrade provjerite preporučenu tehnologiju, debljinu, format i tehničke upute za odabrani materijal.</p>',
    '<h2>Odabir materijala za graviranje</h2><p>Za pločice, oznake, natpise, modele i personalizirane predmete možete birati <a href="/graverstvo/materijali-za-graviranje/plasticne-ploce">plastične ploče</a>, <a href="/graverstvo/materijali-za-graviranje/metalne-ploce">metalne ploče</a>, <a href="/graverstvo/materijali-za-graviranje/akrilne-ploce">akrilne ploče</a>, drvo, papir, EVA pjenu i kožu.</p><h3>Kriteriji za usporedbu</h3><ul><li>laserska ili mehanička tehnologija obrade;</li><li>format i debljina materijala;</li><li>boja površine i izgled nakon graviranja;</li><li>unutarnja ili vanjska primjena;</li><li>potreba za rezanjem, lijepljenjem ili dodatnom završnom obradom.</li></ul><p>Za konačne postavke uvijek slijedite tehničke podatke materijala i preporuke proizvođača stroja.</p>',
    'Plastične, metalne i akrilne ploče, drvo, papir, EVA pjena i koža za graviranje. Odaberite materijal prema stroju i primjeni.'
),
(
    304,
    '<p>Trodat Printy linija nudi samobojajuće tekstualne, datumske i brojčane pečate za svakodnevnu upotrebu. Model odaberite prema obliku i najvećoj dimenziji otiska, broju redaka te vrsti sadržaja koji želite otisnuti.</p>',
    '<h2>Trodat Printy tekstualni i datumski modeli</h2><p>Printy modeli grupirani su na <a href="/pecatarstvo/pecati/trodat-pecati/printy-linija/tekst-stambilji">tekstualne štambilje</a> te <a href="/pecatarstvo/pecati/trodat-pecati/printy-linija/datumski-brojcani-stambilji">datumske i brojčane štambilje</a>. Naziv modela upućuje na konkretno kućište i pripadajući format otiska, pa ga treba provjeriti prije izrade teksta ili odabira jastučića.</p><h3>Što usporediti</h3><ul><li>pravokutni, kvadratni ili okrugli oblik otiska;</li><li>najveće dimenzije otisne pločice;</li><li>tekstualni, datumski ili kombinirani sadržaj;</li><li>oznaku pripadajućeg rezervnog jastučića.</li></ul>',
    'Trodat Printy tekstualni, datumski i brojčani pečati. Usporedite oblik, veličinu otiska, broj redaka i kompatibilne jastučiće.'
),
(
    290,
    '<p>Plastične ploče za graviranje dostupne su u različitim konstrukcijama, bojama i završnim obradama. Pri odabiru provjerite je li ploča namijenjena laserskom ili mehaničkom graviranju, kakav kontrast daje nakon obrade te odgovaraju li format i debljina projektu.</p>',
    '<h2>Plastične ploče za oznake, natpise i personalizaciju</h2><p>U ponudi su materijali kao što su Gravoply, Gravoglas, TroLase, Metallex, Flexilase i druge ploče i folije za različite načine obrade. Pojedini materijali razlikuju se prema konstrukciji slojeva, izgledu površine, fleksibilnosti i preporučenoj tehnologiji graviranja.</p><h3>Kako usporediti ploče</h3><ul><li>provjerite lasersku ili mehaničku kompatibilnost;</li><li>odaberite boju površine i željeni kontrast gravure;</li><li>uskladite debljinu i format s projektom i strojem;</li><li>za zahtjevne uvjete provjerite tehničku dokumentaciju konkretne ploče;</li><li>uzmite u obzir treba li materijal rezati, savijati ili lijepiti.</li></ul><p>Ako niste sigurni, pošaljite podatke o stroju i namjeni kako bi se izbor suzio na odgovarajuće materijale.</p>',
    'Plastične ploče za lasersko i mehaničko graviranje: Gravoply, Gravoglas, TroLase i drugi materijali za oznake, natpise i personalizaciju.'
),
(
    285,
    '<p>Tintu odaberite prema podlozi, načinu nanošenja i zahtjevima otiska. U ponudi su Noris, Reiner i Trodat tinte za papir te specijalizirane tinte za metal, plastiku, drvo, tekstil, staklo i druge materijale. Prije uporabe provjerite tehničke upute konkretnog proizvoda.</p>',
    '<h2>Tinte prema materijalu i načinu primjene</h2><p>Pregledajte <a href="/pecatarstvo/tinte/noris-tinte">Noris tinte</a>, <a href="/pecatarstvo/tinte/reiner-tinte">Reiner tinte</a> i <a href="/pecatarstvo/tinte/trodat-tinte">Trodat tinte</a>. Standardne i specijalizirane formulacije nisu međusobno zamjenjive za svaku podlogu, pa izbor treba temeljiti na materijalu, vrsti uređaja i uputama proizvoda.</p><h3>Prije odabira provjerite</h3><ul><li>otiskujete li na papir, metal, plastiku, drvo, tekstil, staklo ili drugu podlogu;</li><li>koji su način nanošenja i odgovarajući jastučić;</li><li>traži li primjena brzo sušenje ili posebnu postojanost;</li><li>postoje li posebni sigurnosni i procesni zahtjevi za namjenu.</li></ul>',
    'Noris, Reiner i Trodat tinte za papir, metal, plastiku, drvo, tekstil, staklo i druge podloge. Odaberite tintu prema primjeni.'
),
(
    289,
    '<p>Pomoćni pribor za graviranje obuhvaća duploljepljive trake, sredstva za lasersko označavanje, lubrikante, boje, ispune, materijale za maskiranje i pribor za čišćenje. Odaberite ga prema osnovnom materijalu, postupku obrade i željenoj završnoj obradi.</p>',
    '<h2>Priprema, obrada i završna dorada</h2><p>Pomoćni pribor može olakšati pričvršćivanje materijala, pripremu površine, označavanje, bojanje gravure, čišćenje i završnu obradu. Ponuda je podijeljena na <a href="/graverstvo/pomocni-pribor/duploljepljive-trake">duploljepljive trake</a>, <a href="/graverstvo/pomocni-pribor/emulzije-za-laser">emulzije za laser</a>, <a href="/graverstvo/pomocni-pribor/lubrifikanti">lubrikante</a>, <a href="/graverstvo/pomocni-pribor/boje-i-ispune">boje i ispune</a> te ostali pribor.</p><h3>Kako odabrati pribor</h3><ul><li>uskladite proizvod s materijalom koji obrađujete;</li><li>provjerite je li namijenjen pripremi, samoj obradi ili završnoj doradi;</li><li>slijedite tehničke i sigurnosne upute proizvoda;</li><li>prije serijske obrade napravite probu na manjem uzorku kada je to primjenjivo.</li></ul>',
    'Duploljepljive trake, emulzije za laser, lubrikanti, boje, ispune i pribor za pripremu, graviranje, čišćenje i završnu obradu.'
);

-- Posebno napisani uvodi za ostale trenutno prazne kategorije. Dugi opis i
-- meta opis zatim se prilagođavaju vrsti kategorije.
INSERT IGNORE INTO `tmp_reprograv_category_seo`
    (`category_id`, `short_description`, `description`, `meta_description`)
SELECT
    cd.category_id,
    CASE cd.category_id
        WHEN 369 THEN '<p>Akrilne ploče koriste se za natpise, oznake, dekoracije, modele i druge gravirane ili rezane elemente. Pri odabiru usporedite vrstu akrila, boju, prozirnost, debljinu i preporučeni postupak obrade kako bi završni rubovi i gravura odgovarali projektu.</p>'
        WHEN 301 THEN '<p>Drvo daje prirodan izgled graviranim oznakama, dekoracijama, poklonima i personaliziranim predmetima. Rezultat ovisi o vrsti i strukturi materijala, pa prije obrade provjerite debljinu, ravnost, sastav te preporučene postavke rezanja ili graviranja.</p>'
        WHEN 371 THEN '<p>Koža je prikladna za personalizaciju dodataka, oznaka i ukrasnih predmeta kada je odabrana vrsta materijala kompatibilna s postupkom obrade. Provjerite radi li se o prirodnoj ili umjetnoj koži, njezinu debljinu, završnu površinu i tehničke preporuke.</p>'
        WHEN 300 THEN '<p>Metalne ploče namijenjene su izradi trajnih oznaka, natpisnih pločica, identifikacijskih elemenata i dekorativnih aplikacija. Usporedite vrstu metala i površinske obrade, debljinu, format te podržava li proizvod lasersko označavanje ili mehaničko graviranje.</p>'
        WHEN 302 THEN '<p>Papir za lasersku obradu omogućuje precizno rezanje, označavanje i izradu dekorativnih ili prezentacijskih elemenata. Odaberite gramaturu, boju, strukturu i format prema projektu te prije serijske izrade provjerite preporuke za siguran rad i napravite probni uzorak.</p>'
        WHEN 303 THEN '<p>Pjenasta spužva EVA koristi se za lagane umetke, modele, dekoracije i druge rezane ili gravirane elemente. Pri odabiru uskladite debljinu, gustoću, boju i format s namjenom te provjerite preporučeni način obrade za konkretan materijal.</p>'
        WHEN 288 THEN '<p>Gotovi proizvodi olakšavaju izradu personaliziranih bedževa, privjesaka, pločica, kopči i nosača natpisa. Proizvod odaberite prema dimenziji, materijalu, načinu označavanja i montaže te prostoru dostupnom za tekst, logotip ili drugi motiv.</p>'
        WHEN 320 THEN '<p>Bedževi su praktična osnova za personalizirane oznake, identifikaciju, događanja i promotivne namjene. Pri izboru provjerite oblik, dimenziju, način pričvršćivanja, materijal i raspoloživu površinu za graviranje, tisak ili umetanje pripremljenog motiva.</p>'
        WHEN 321 THEN '<p>Držači natpisa služe urednom postavljanju pločica i informacija na stolove, vrata, zidove ili druge površine. Uskladite duljinu, profil, način montaže i debljinu umetka s mjestom postavljanja i dimenzijama pripremljenog natpisa.</p>'
        WHEN 322 THEN '<p>Kopče omogućuju pričvršćivanje bedževa, identifikacijskih oznaka i drugih personaliziranih elemenata. Prije odabira provjerite tip kopče, način montaže, kompatibilnost s podlogom te zahtjeve svakodnevnog korištenja.</p>'
        WHEN 323 THEN '<p>Pločice za vrata mogu se prilagoditi tekstom, simbolima i oznakama za poslovne, javne ili privatne prostore. Odaberite format, boju, materijal i način pričvršćivanja prema željenoj čitljivosti, izgledu prostora i uvjetima uporabe.</p>'
        WHEN 324 THEN '<p>Privjesci su prikladni za personalizaciju imenom, brojem, logotipom ili kratkom porukom. Usporedite oblik, dimenziju, materijal, otvor ili spojnicu te veličinu površine dostupne za graviranje ili drugu vrstu označavanja.</p>'
        WHEN 316 THEN '<p>Boje i ispune koriste se za naglašavanje gravure, popunjavanje udubljenja i završno oblikovanje oznaka. Proizvod uskladite s osnovnim materijalom, postupkom nanošenja, željenom bojom i uvjetima uporabe te slijedite tehničke i sigurnosne upute.</p>'
        WHEN 318 THEN '<p>Duploljepljive trake omogućuju pričvršćivanje pločica, oznaka i drugih obrađenih elemenata bez vidljivih vijaka. Odaberite širinu, debljinu i vrstu ljepila prema podlozi, težini predmeta, uvjetima uporabe i potrebnoj trajnosti spoja.</p>'
        WHEN 315 THEN '<p>Emulzije za laser koriste se pri laserskom označavanju odabranih površina kada to dopuštaju proizvod i oprema. Prije uporabe provjerite kompatibilnost s materijalom i strojem, način nanošenja, potrebne postavke te sve tehničke i sigurnosne upute.</p>'
        WHEN 314 THEN '<p>Lubrikanti mogu olakšati mehaničku obradu i pomoći kvaliteti završnog rezultata na kompatibilnim materijalima. Izbor prilagodite materijalu, alatu i postupku graviranja te koristite količinu i način nanošenja navedene u tehničkim uputama proizvoda.</p>'
        WHEN 319 THEN '<p>Ostali pomoćni pribor obuhvaća proizvode za pripremu, označavanje, obradu, čišćenje i završnu doradu. Prije odabira provjerite namjenu, kompatibilnost s materijalom i strojem te preporučeni način primjene.</p>'
        WHEN 330 THEN '<p>Reiner rezervni jastučići biraju se prema točnoj oznaci kompatibilnog modela uređaja i potrebnoj boji otiska. Prije naručivanja usporedite puni broj modela s kućišta s podacima jastučića jer slični uređaji ne moraju koristiti isti potrošni dio.</p>'
        WHEN 325 THEN '<p>Trodat rezervni jastučići raspoređeni su prema liniji i modelu pečata. Najsigurniji odabir temelji se na punoj oznaci kućišta, vrsti pečata i željenoj boji otiska, a ne samo na vanjskom obliku jastučića.</p>'
        WHEN 327 THEN '<p>Rezervni jastučići za Trodat Professional liniju namijenjeni su određenim modelima robusnih uredskih pečata. Provjerite puni broj modela na kućištu, vrstu datumskog ili tekstualnog mehanizma i oznaku kompatibilnog jastučića.</p>'
        WHEN 329 THEN '<p>Jastučići za automatske numeratore moraju odgovarati konkretnom mehanizmu i oznaci uređaja. Prije odabira provjerite proizvođača, puni model, način numeriranja i traženu boju kako bi otisak ostao ravnomjeran i čitljiv.</p>'
        WHEN 328 THEN '<p>Rezervni jastučići za prijenosne Trodat pečate prilagođeni su kompaktnim kućištima pojedinih linija. Usporedite oznaku Pocket Printy, Mobile Printy ili Micro Printy modela s popisom kompatibilnosti i zatim odaberite boju otiska.</p>'
        WHEN 326 THEN '<p>Jastučići za Trodat Printy liniju dostupni su za različite tekstualne, datumske i brojčane modele. Točnu zamjenu pronađite prema oznaci kućišta i modela jer dimenzije i položaj jastučića ovise o izvedbi pečata.</p>'
        WHEN 352 THEN '<p>Noris tinte obuhvaćaju formulacije za papir i različite neupojne ili posebne podloge. Tintu odaberite prema materijalu na koji otiskujete, načinu nanošenja, potrebnom vremenu sušenja i tehničkim uputama konkretnog proizvoda.</p>'
        WHEN 366 THEN '<p>Brzosušeće tinte namijenjene su primjenama u kojima otisak mora brzo postati otporan na razmazivanje. Kompatibilnost obavezno provjerite prema podlozi, vrsti jastučića i načinu nanošenja te slijedite tehničke i sigurnosne upute.</p>'
        WHEN 363 THEN '<p>Tinte za beton biraju se prema poroznosti i stanju površine, traženoj postojanosti te načinu nanošenja. Prije uporabe provjerite tehničke podatke proizvoda i napravite probni otisak na usporedivom uzorku materijala.</p>'
        WHEN 362 THEN '<p>Tinte za drvo trebaju odgovarati vrsti, obradi i upojnosti drvene površine. Za čitljiv otisak provjerite preporučeni jastučić, vrijeme sušenja, postojanost i pripremu podloge navedenu u tehničkim uputama.</p>'
        WHEN 364 THEN '<p>Tinte za metal formulirane su za odabrane metalne i druge slabo upojne površine. Odabir uskladite s vrstom metala, pripremom podloge, načinom nanošenja, vremenom sušenja i traženom otpornošću otiska.</p>'
        WHEN 353 THEN '<p>Tinte za papir namijenjene su jasnim i ujednačenim otiscima na uobičajenim upojnim podlogama. Pri odabiru provjerite boju, kompatibilnost s pečatom ili jastučićem te zahtjeve dokumenta i učestalost korištenja.</p>'
        WHEN 354 THEN '<p>Tinte za plastiku trebaju biti usklađene s konkretnom vrstom plastične podloge i načinom nanošenja. Budući da se plastike razlikuju po površinskim svojstvima, prije serijskog rada provjerite dokumentaciju i napravite probni otisak.</p>'
        WHEN 367 THEN '<p>Tinte za prehrambene proizvode koriste se samo za namjene i podloge izričito navedene u dokumentaciji konkretnog proizvoda. Prije odabira provjerite sve deklaracije, način primjene, ograničenja i važeće higijenske zahtjeve.</p>'
        WHEN 365 THEN '<p>Tinte za staklo namijenjene su označavanju odabranih glatkih i neupojnih površina. Provjerite pripremu stakla, kompatibilnost tinte i jastučića, vrijeme sušenja te uvjete kojima će otisak biti izložen.</p>'
        WHEN 361 THEN '<p>Tinte za tekstil odabiru se prema vrsti vlakna, načinu nanošenja i željenoj postojanosti oznake. Prije uporabe provjerite tehničke upute, zahtjeve sušenja ili fiksiranja i napravite probu na usporedivom komadu tkanine.</p>'
        WHEN 368 THEN '<p>UV tinte koriste se u specifičnim postupcima označavanja i moraju biti kompatibilne s opremom, podlogom i predviđenim izvorom UV zračenja. Odabir i uporabu uvijek temeljite na tehničkoj i sigurnosnoj dokumentaciji proizvoda.</p>'
        WHEN 360 THEN '<p>Reiner tinte namijenjene su kompatibilnim Reiner uređajima i određenim podlogama. Pri izboru provjerite točnu oznaku pisača ili sustava, boju, vrstu spremnika i preporučenu primjenu iz tehničke dokumentacije.</p>'
        WHEN 359 THEN '<p>Trodat tinte koriste se za ponovno natapanje odgovarajućih jastučića i za druge predviđene primjene. Provjerite boju, formulaciju, vrstu podloge i kompatibilnost s pečatom ili jastučićem prije nadopunjavanja.</p>'
        WHEN 284 THEN '<p>Jastuk u kutiji koristi se za ručno nanošenje tinte na klasične, veće ili posebne pečate. Dimenziju jastuka uskladite s veličinom otisne pločice, a vrstu tinte s podlogom i materijalom jastuka.</p>'
        WHEN 286 THEN '<p>Materijali za izradu pečata biraju se prema tehnologiji izrade, dostupnoj opremi i traženoj kvaliteti otiska. Prije narudžbe provjerite format, debljinu, način obrade, kompatibilnost s tintom i tehničke preporuke proizvođača.</p>'
        WHEN 298 THEN '<p>Reiner pečati i uređaji za označavanje obuhvaćaju datumare i numeratore za različite poslovne zadatke. Model odaberite prema sadržaju otiska, broju znamenki, potrebnoj veličini, učestalosti rada i dostupnom potrošnom materijalu.</p>'
        WHEN 305 THEN '<p>Reiner datumari omogućuju ponavljajuće označavanje datuma u uredskim, skladišnim i drugim procesima. Usporedite format datuma, veličinu otiska, dodatni tekst, način podešavanja i kompatibilan jastučić ili tintu.</p>'
        WHEN 306 THEN '<p>Reiner numeratori služe brzom i dosljednom označavanju brojevima. Pri odabiru provjerite broj znamenki, veličinu znakova, način pomicanja broja, konstrukciju uređaja i odgovarajući potrošni materijal.</p>'
        WHEN 313 THEN '<p>Trodat automatski numeratori namijenjeni su ponavljajućem numeriranju dokumenata i drugih prikladnih podloga. Model birajte prema broju znamenki, visini znakova, načinu ponavljanja i automatskog pomicanja te kompatibilnom jastučiću.</p>'
        WHEN 310 THEN '<p>Trodat Classic linija obuhvaća tradicionalne ručne pečate, datumare i numeratore za rad s odvojenim jastukom. Usporedite vrstu mehanizma, veličinu otiska, broj znamenki ili format datuma i učestalost korištenja.</p>'
        WHEN 349 THEN '<p>Ručni datumari Trodat Classic koriste se s odvojenim jastukom i omogućuju brzo mijenjanje datuma. Pri izboru provjerite format i visinu datuma, širinu traka, veličinu ukupnog otiska te odgovarajući jastuk i tintu.</p>'
        WHEN 350 THEN '<p>Ručni numeratori Trodat Classic namijenjeni su označavanju brojevima uz odvojeni jastuk za tintu. Odaberite broj traka i znamenki, visinu znakova, konstrukciju držača te veličinu jastuka prema planiranom otisku.</p>'
        WHEN 308 THEN '<p>Trodat prijenosna linija pruža kompaktne pečate za rad izvan ureda i jednostavno nošenje. Pocket Printy, Mobile Printy i Micro Printy modele usporedite prema načinu otvaranja, dimenziji otiska, broju redaka i kompatibilnom jastučiću.</p>'
        WHEN 356 THEN '<p>Trodat Micro Printy kompaktni su samobojajući pečati za kraći tekst i praktično nošenje. Model odaberite prema maksimalnoj veličini otiska, broju redaka, obliku kućišta i oznaci pripadajućeg rezervnog jastučića.</p>'
        WHEN 357 THEN '<p>Trodat Mobile Printy dizajniran je za prijenosnu upotrebu uz zaštitu otisne pločice i jednostavno rukovanje. Usporedite raspoložive formate, veličinu sadržaja, broj redaka i kompatibilan zamjenski jastučić.</p>'
        WHEN 358 THEN '<p>Trodat Pocket Printy nudi sklopivo prijenosno kućište za tekstualne otiske na terenu. Pri izboru provjerite najveću dimenziju otiska, preporučeni broj redaka, način otvaranja i oznaku rezervnog jastučića.</p>'
        WHEN 332 THEN '<p>Trodat Printy datumski i brojčani štambilji povezuju samobojajuće kućište s podesivim trakama. Usporedite format datuma ili broj znamenki, visinu znakova, prostor za dodatni tekst i dimenziju ukupnog otiska.</p>'
        WHEN 331 THEN '<p>Trodat Printy tekstualni štambilji praktični su za svakodnevne uredske i poslovne otiske. Model odaberite prema količini teksta, najvećoj dimenziji otiska, broju redaka, obliku otisne pločice i oznaci rezervnog jastučića.</p>'
        WHEN 307 THEN '<p>Trodat Professional linija namijenjena je čestom korištenju i stabilnom radu tekstualnih, datumskih i brojčanih pečata. Model birajte prema vrsti sadržaja, veličini otiska, učestalosti rada i kompatibilnom rezervnom jastučiću.</p>'
        WHEN 355 THEN '<p>Trodat Professional datumski i brojčani štambilji namijenjeni su zahtjevnijem ponavljajućem označavanju. Provjerite format datuma, broj znamenki, veličinu znakova, prostor za dodatni tekst i oznaku odgovarajućeg jastučića.</p>'
        WHEN 351 THEN '<p>Trodat Professional tekstualni štambilji nude robusnu konstrukciju za učestale poslovne otiske. Pri odabiru usporedite najveću veličinu otisne pločice, preporučeni broj redaka, oblik otiska i kompatibilan rezervni jastučić.</p>'
        WHEN 311 THEN '<p>Trodat specijalna linija obuhvaća pečate za posebne formate i načine označavanja koji nisu pokriveni standardnim modelima. Odabir temeljite na konkretnoj namjeni, sadržaju, dimenziji otiska i potrebnom potrošnom materijalu.</p>'
        WHEN 309 THEN '<p>Trodat Typomatic pečati omogućuju samostalno slaganje i izmjenu teksta pomoću pripadajućih znakova. Model odaberite prema veličini otiska, broju redaka, veličini slovnog seta i učestalosti promjene sadržaja.</p>'
        WHEN 287 THEN '<p>Suhi žigovi stvaraju reljefni otisak bez tinte, dok kliješta za plombiranje služe kontrolnom zatvaranju odgovarajućih plombi. Izbor prilagodite namjeni, veličini i položaju oznake, materijalu podloge te potrebnoj dubini ili čitljivosti otiska.</p>'
    END,
    CONCAT(
        '<h2>', TRIM(cd.name), ' – informacije za lakši odabir</h2>',
        CASE
            WHEN cd.category_id IN (369, 301, 371, 300, 302, 303) THEN
                '<p>Materijal uskladite s tehnologijom stroja i završnom namjenom proizvoda. Isti naziv materijala ne znači nužno jednako ponašanje pri graviranju ili rezanju, zato provjerite podatke za konkretnu izvedbu.</p><h3>Prije naručivanja provjerite</h3><ul><li>lasersku ili mehaničku kompatibilnost;</li><li>format, debljinu i izgled površine;</li><li>unutarnju ili vanjsku primjenu;</li><li>preporučene postavke i sigurnosne upute.</li></ul>'
            WHEN cd.category_id IN (288, 320, 321, 322, 323, 324) THEN
                '<p>Gotov proizvod treba odgovarati sadržaju koji želite personalizirati i načinu na koji će se koristiti. Uzmite u obzir čitljivost teksta, raspoloživu površinu za motiv i završni izgled nakon označavanja.</p><h3>Važni podaci</h3><ul><li>dimenzija, oblik i materijal proizvoda;</li><li>tehnologija personalizacije;</li><li>način pričvršćivanja ili montaže;</li><li>količina i uvjeti svakodnevne uporabe.</li></ul>'
            WHEN cd.category_id IN (316, 318, 315, 314, 319) THEN
                '<p>Pomoćni materijal odaberite prema osnovnoj podlozi, postupku obrade i željenom završnom rezultatu. Prije rada provjerite kompatibilnost i napravite probu na manjem uzorku kada je to primjenjivo.</p><h3>Sigurna i pravilna primjena</h3><ul><li>slijedite tehnički list proizvoda;</li><li>provjerite način nanošenja i uklanjanja;</li><li>poštujte sigurnosne i ventilacijske zahtjeve;</li><li>zabilježite uspješne postavke prije serijske obrade.</li></ul>'
            WHEN cd.category_id IN (330, 325, 327, 329, 328, 326) THEN
                '<p>Rezervni jastučić nije dovoljno birati samo prema približnoj veličini. Puna oznaka pečata i popis kompatibilnih modela najvažniji su podaci za sigurnu zamjenu.</p><h3>Provjera kompatibilnosti</h3><ul><li>proizvođač, linija i broj modela;</li><li>tekstualni, datumski ili brojčani mehanizam;</li><li>oznaka samog jastučića;</li><li>željena boja i prikladna vrsta tinte.</li></ul>'
            WHEN cd.category_id IN (352, 366, 363, 362, 364, 353, 354, 367, 365, 361, 368, 360, 359) THEN
                '<p>Formulacije tinte razlikuju se prema podlozi i načinu primjene. Neodgovarajuća tinta može se sporo sušiti, razmazivati ili oštetiti jastučić, pa izbor treba potvrditi tehničkim podacima.</p><h3>Prije uporabe provjerite</h3><ul><li>točnu vrstu i pripremu podloge;</li><li>kompatibilan uređaj i jastučić;</li><li>vrijeme sušenja i potrebnu postojanost;</li><li>sigurnosne, procesne i regulatorne zahtjeve.</li></ul>'
            WHEN cd.category_id IN (298, 305, 306, 313, 310, 349, 350, 308, 356, 357, 358, 332, 331, 307, 355, 351, 311, 309) THEN
                '<p>Pečat ili uređaj odaberite prema stvarnom sadržaju i najvećoj potrebnoj dimenziji otiska. Naziv linije opisuje konstrukciju i način rada, dok konkretan broj modela određuje format i kompatibilan potrošni materijal.</p><h3>Što usporediti</h3><ul><li>tekstualni, datumski ili brojčani sadržaj;</li><li>veličinu, oblik i broj redaka otiska;</li><li>učestalost i mjesto korištenja;</li><li>oznaku rezervnog jastučića ili tinte.</li></ul>'
            WHEN cd.category_id = 284 THEN
                '<p>Za puni i ravnomjeran otisak cijela otisna pločica treba stati na tintnu površinu jastuka. Tintu uvijek odaberite prema podlozi na kojoj se radi, a jastuk održavajte i nadopunjavajte prema uputama proizvođača.</p><h3>Prije odabira provjerite</h3><ul><li>dimenzije otisne pločice;</li><li>unutarnje dimenzije jastuka;</li><li>vrstu podloge i tinte;</li><li>očekivanu učestalost korištenja.</li></ul>'
            WHEN cd.category_id = 286 THEN
                '<p>Kvaliteta gotove otisne pločice ovisi o pravilnom spoju materijala, postupka izrade i opreme. Za stabilan rezultat usporedite tehničke podatke materijala prije izlaganja, obrade i montaže.</p><h3>Kriteriji odabira</h3><ul><li>tehnologija i model opreme;</li><li>format i debljina materijala;</li><li>željena razlučivost i dubina reljefa;</li><li>kompatibilnost s ljepilom, tintom i podlogom.</li></ul>'
            WHEN cd.category_id = 287 THEN
                '<p>Reljefni i plombirni otisci imaju različite mehanizme i namjene. Prije izrade potrebno je definirati sadržaj oznake, dostupni prostor, materijal na kojem se radi i način svakodnevnog rukovanja.</p><h3>Podaci za odabir</h3><ul><li>suhi žig ili kliješta za plombiranje;</li><li>promjer i položaj otiska;</li><li>stolna ili ručna izvedba;</li><li>materijal papira, naljepnice ili plombe.</li></ul>'
        END
    ),
    CASE
        WHEN cd.category_id IN (369, 301, 371, 300, 302, 303) THEN CONCAT(TRIM(cd.name), ' za graviranje i personalizaciju. Usporedite tehnologiju obrade, format, debljinu, izgled površine i namjenu materijala.')
        WHEN cd.category_id IN (288, 320, 321, 322, 323, 324) THEN CONCAT(TRIM(cd.name), ' za graviranje i personalizaciju. Pregledajte dimenzije, materijale, način označavanja i mogućnosti pričvršćivanja.')
        WHEN cd.category_id IN (316, 318, 315, 314, 319) THEN CONCAT(TRIM(cd.name), ' za pripremu, obradu i završnu doradu. Provjerite kompatibilnost s materijalom, način primjene i tehničke upute.')
        WHEN cd.category_id IN (330, 325, 327, 329, 328, 326) THEN CONCAT(TRIM(cd.name), ' za kompatibilne modele pečata. Odaberite jastučić prema proizvođaču, punoj oznaci modela i boji otiska.')
        WHEN cd.category_id IN (352, 366, 363, 362, 364, 353, 354, 367, 365, 361, 368, 360, 359) THEN CONCAT(TRIM(cd.name), ' za profesionalno označavanje. Odaberite formulaciju prema podlozi, uređaju, načinu nanošenja i vremenu sušenja.')
        WHEN cd.category_id IN (298, 305, 306, 313, 310, 349, 350, 308, 356, 357, 358, 332, 331, 307, 355, 351, 311, 309) THEN CONCAT(TRIM(cd.name), ' za tekstualne, datumske ili brojčane otiske. Usporedite modele, veličine otiska, način rada i kompatibilne jastučiće.')
        WHEN cd.category_id = 284 THEN 'Jastuci u kutiji za ručne i posebne pečate. Odaberite dimenziju prema otisnoj pločici, a tintu prema materijalu podloge i namjeni.'
        WHEN cd.category_id = 286 THEN 'Materijali za izradu pečata za različite tehnologije i formate. Usporedite način obrade, debljinu, kvalitetu otiska i kompatibilnost.'
        WHEN cd.category_id = 287 THEN 'Suhi žigovi i kliješta za plombiranje za reljefne i kontrolne oznake. Usporedite mehanizme, veličinu otiska, materijal i namjenu.'
    END
FROM `oc_category_description` cd
WHERE cd.language_id = 3
  AND cd.category_id IN (
      284, 286, 287, 288, 298, 300, 301, 302, 303, 305, 306, 307, 308,
      309, 310, 311, 313, 314, 315, 316, 318, 319, 320, 321, 322, 323,
      324, 325, 326, 327, 328, 329, 330, 331, 332, 349, 350, 351, 352,
      353, 354, 355, 356, 357, 358, 359, 360, 361, 362, 363, 364, 365,
      366, 367, 368, 369, 371
  );

-- Datum izmjene mijenja se samo potpuno praznim kategorijama koje će dobiti
-- sva tri SEO polja.
UPDATE `oc_category` c
INNER JOIN `oc_category_description` cd
    ON cd.category_id = c.category_id AND cd.language_id = 3
INNER JOIN `tmp_reprograv_category_seo` seed
    ON seed.category_id = cd.category_id
SET c.date_modified = NOW()
WHERE TRIM(COALESCE(cd.short_description, '')) = ''
  AND TRIM(COALESCE(cd.description, '')) = ''
  AND TRIM(COALESCE(cd.meta_description, '')) = '';

UPDATE `oc_category_description` cd
INNER JOIN `tmp_reprograv_category_seo` seed
    ON seed.category_id = cd.category_id
SET
    cd.short_description = seed.short_description,
    cd.description = seed.description,
    cd.meta_description = seed.meta_description
WHERE cd.language_id = 3
  AND TRIM(COALESCE(cd.short_description, '')) = ''
  AND TRIM(COALESCE(cd.description, '')) = ''
  AND TRIM(COALESCE(cd.meta_description, '')) = '';

DROP TEMPORARY TABLE `tmp_reprograv_category_seo`;

-- Ispravak nevidljivog znaka iz jednog starijeg opisa tinte.
UPDATE `oc_category_description`
SET `description` = REPLACE(`description`, 'otis­kujete', 'otiskujete')
WHERE `language_id` = 3
  AND `category_id` = 285;

-- Glavna FAQ grupa.
SET @faq_group_id := (
    SELECT f.fcategory_id
    FROM `oc_fcategory` f
    INNER JOIN `oc_fcategory_description` fd
        ON fd.fcategory_id = f.fcategory_id
    WHERE fd.language_id = 3
      AND fd.name = 'Česta pitanja'
    ORDER BY f.fcategory_id
    LIMIT 1
);

INSERT INTO `oc_fcategory`
    (`sort_order`, `status`, `image`, `date_added`, `date_modified`)
SELECT 0, 1, '', NOW(), NOW()
WHERE @faq_group_id IS NULL;

SET @faq_group_id := COALESCE(@faq_group_id, LAST_INSERT_ID());

INSERT IGNORE INTO `oc_fcategory_description`
    (`fcategory_id`, `language_id`, `name`, `meta_title`, `meta_description`, `meta_keyword`)
VALUES
    (@faq_group_id, 3, 'Česta pitanja', 'Česta pitanja', '', '');

INSERT IGNORE INTO `oc_fcategory_to_store`
    (`fcategory_id`, `store_id`)
VALUES
    (@faq_group_id, 0);

-- 1. Kako odabrati odgovarajući pečat?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Kako odabrati odgovarajući pečat?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 10, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Kako odabrati odgovarajući pečat?', '<p>Odabir započnite vrstom sadržaja, najvećom potrebnom veličinom otiska i učestalošću korištenja. Zatim usporedite tekstualne, datumske, brojčane, prijenosne i profesionalne modele. Ako već imate pripremljen tekst ili grafiku, njihove dimenzije mogu dodatno suziti izbor.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 274), (@faq_id, 275), (@faq_id, 297), (@faq_id, 298),
    (@faq_id, 304), (@faq_id, 305), (@faq_id, 306), (@faq_id, 307),
    (@faq_id, 308), (@faq_id, 309), (@faq_id, 310), (@faq_id, 311),
    (@faq_id, 312), (@faq_id, 313), (@faq_id, 331), (@faq_id, 332),
    (@faq_id, 349), (@faq_id, 350), (@faq_id, 351), (@faq_id, 355),
    (@faq_id, 356), (@faq_id, 357), (@faq_id, 358);

-- 2. Koja je razlika između Trodat Printy i Professional linije?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Koja je razlika između Trodat Printy i Professional linije?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 20, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Koja je razlika između Trodat Printy i Professional linije?', '<p>Printy linija namijenjena je praktičnoj svakodnevnoj upotrebi i obuhvaća velik broj formata. Professional linija koristi drukčiju konstrukciju za zahtjevniji i učestaliji rad. Konačan model birajte prema vrsti, dimenziji i učestalosti otiska, a ne samo prema nazivu linije.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 275), (@faq_id, 297), (@faq_id, 304), (@faq_id, 307),
    (@faq_id, 331), (@faq_id, 332), (@faq_id, 351), (@faq_id, 355);

-- 3. Kako pronaći kompatibilan rezervni jastučić?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Kako pronaći kompatibilan rezervni jastučić?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 30, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Kako pronaći kompatibilan rezervni jastučić?', '<p>Pronađite punu oznaku modela na kućištu pečata i usporedite je s popisom kompatibilnih modela jastučića. Proizvođač, linija i broj modela moraju odgovarati. Ako oznaka nije čitljiva, pošaljite fotografiju i približne dimenzije kućišta.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 274), (@faq_id, 283), (@faq_id, 297), (@faq_id, 298),
    (@faq_id, 304), (@faq_id, 325), (@faq_id, 326), (@faq_id, 327),
    (@faq_id, 328), (@faq_id, 329), (@faq_id, 330);

-- 4. Mijenja li se cijeli pečat kada se jastučić istroši?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Mijenja li se cijeli pečat kada se jastučić istroši?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 40, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Mijenja li se cijeli pečat kada se jastučić istroši?', '<p>U pravilu se kod modela s izmjenjivim jastučićem mijenja samo odgovarajući rezervni jastučić. Potrebno je provjeriti točnu oznaku pečata jer vizualno slična kućišta mogu koristiti različite jastučiće.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 283), (@faq_id, 325), (@faq_id, 326), (@faq_id, 327),
    (@faq_id, 328), (@faq_id, 329), (@faq_id, 330);

-- 5. Kako odabrati tintu prema podlozi?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Kako odabrati tintu prema podlozi?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 50, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Kako odabrati tintu prema podlozi?', '<p>Najprije odredite materijal na koji otiskujete, zatim provjerite preporučeni način nanošenja, vrijeme sušenja i tehničke upute proizvoda. Tinta za papir nije automatski prikladna za metal, plastiku, staklo, drvo ili tekstil.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 274), (@faq_id, 285), (@faq_id, 352), (@faq_id, 353),
    (@faq_id, 354), (@faq_id, 359), (@faq_id, 360), (@faq_id, 361),
    (@faq_id, 362), (@faq_id, 363), (@faq_id, 364), (@faq_id, 365),
    (@faq_id, 366), (@faq_id, 367), (@faq_id, 368), (@faq_id, 372);

-- 6. Jesu li sve tinte međusobno zamjenjive?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Jesu li sve tinte međusobno zamjenjive?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 60, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Jesu li sve tinte međusobno zamjenjive?', '<p>Nisu. Formulacije se razlikuju prema podlozi, uređaju i načinu primjene. Neodgovarajuća tinta može dati loš otisak ili biti neprikladna za jastučić i postupak, zato uvijek treba provjeriti tehničku dokumentaciju konkretnog proizvoda.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 285), (@faq_id, 352), (@faq_id, 353), (@faq_id, 354),
    (@faq_id, 359), (@faq_id, 360), (@faq_id, 361), (@faq_id, 362),
    (@faq_id, 363), (@faq_id, 364), (@faq_id, 365), (@faq_id, 366),
    (@faq_id, 367), (@faq_id, 368), (@faq_id, 372);

-- 7. Koji je materijal prikladan za moj stroj za graviranje?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Koji je materijal prikladan za moj stroj za graviranje?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 70, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Koji je materijal prikladan za moj stroj za graviranje?', '<p>Potrebno je znati tehnologiju i model stroja, podržane materijale, radno područje te najveću dopuštenu debljinu. Nakon toga se uspoređuju tehnički podaci ploče ili drugog materijala s preporukama proizvođača stroja.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 276), (@faq_id, 277), (@faq_id, 300), (@faq_id, 301),
    (@faq_id, 302), (@faq_id, 303), (@faq_id, 369), (@faq_id, 371);

-- 8. Što provjeriti prije odabira materijala za vanjsku primjenu?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Što provjeriti prije odabira materijala za vanjsku primjenu?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 80, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Što provjeriti prije odabira materijala za vanjsku primjenu?', '<p>Provjerite tehničke podatke konkretnog materijala, posebno preporučeno područje primjene i otpornost potrebnu za očekivane uvjete. Boja i izgled nisu dovoljni kriteriji; važni su i način obrade, montaže i održavanja.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 277), (@faq_id, 290), (@faq_id, 300), (@faq_id, 301),
    (@faq_id, 302), (@faq_id, 303), (@faq_id, 369), (@faq_id, 371);

-- 9. Koja je razlika između plastičnih, akrilnih i metalnih ploča?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Koja je razlika između plastičnih, akrilnih i metalnih ploča?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 90, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Koja je razlika između plastičnih, akrilnih i metalnih ploča?', '<p>Razlikuju se po konstrukciji, izgledu, krutosti, načinu obrade i završnom rezultatu. Nisu svi materijali namijenjeni istoj tehnologiji graviranja, pa izbor treba temeljiti na stroju, primjeni i tehničkoj dokumentaciji proizvoda.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 276), (@faq_id, 277), (@faq_id, 290), (@faq_id, 300),
    (@faq_id, 369);

-- 10. Kako odabrati plastičnu ploču za lasersko ili mehaničko graviranje?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Kako odabrati plastičnu ploču za lasersko ili mehaničko graviranje?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 100, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Kako odabrati plastičnu ploču za lasersko ili mehaničko graviranje?', '<p>U tehničkim podacima ploče provjerite preporučenu tehnologiju obrade. Zatim usporedite debljinu, format, konstrukciju slojeva, boju površine i kontrast koji nastaje graviranjem. Ako materijal planirate i rezati, provjerite i preporuke za rezanje.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES (@faq_id, 290);

-- 11. Kako odabrati veličinu Trodat Printy pečata?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Kako odabrati veličinu Trodat Printy pečata?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 110, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Kako odabrati veličinu Trodat Printy pečata?', '<p>Izmjerite potreban sadržaj i usporedite ga s najvećom veličinom otiska pojedinog modela. Uzmite u obzir broj redaka, logotip, oblik otiska i čitljivost. Veće kućište nije uvijek potrebno ako sadržaj uredno stane u manji format.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 297), (@faq_id, 304), (@faq_id, 326), (@faq_id, 331),
    (@faq_id, 332);

-- 12. Koja je razlika između tekstualnog i datumskog pečata?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Koja je razlika između tekstualnog i datumskog pečata?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 120, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Koja je razlika između tekstualnog i datumskog pečata?', '<p>Tekstualni pečat koristi pripremljenu otisnu pločicu s fiksnim sadržajem. Datumski model ima podesive trake ili mehanizam za datum, a može sadržavati i dodatni tekst. Kombinirani modeli zato zahtijevaju provjeru ukupne veličine otiska.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 275), (@faq_id, 298), (@faq_id, 304), (@faq_id, 305),
    (@faq_id, 306), (@faq_id, 307), (@faq_id, 310), (@faq_id, 331),
    (@faq_id, 332), (@faq_id, 349), (@faq_id, 350), (@faq_id, 351),
    (@faq_id, 355);

-- 13. Čemu služi pomoćni pribor za graviranje?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Čemu služi pomoćni pribor za graviranje?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 130, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Čemu služi pomoćni pribor za graviranje?', '<p>Ovisno o proizvodu, pomoćni pribor koristi se za pričvršćivanje materijala, pripremu i maskiranje površine, lasersko označavanje, podmazivanje, bojanje gravure, čišćenje ili završnu obradu.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 276), (@faq_id, 289), (@faq_id, 314), (@faq_id, 315),
    (@faq_id, 316), (@faq_id, 318), (@faq_id, 319);

-- 14. Kako sigurno isprobati novo pomoćno sredstvo ili materijal?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Kako sigurno isprobati novo pomoćno sredstvo ili materijal?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 140, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Kako sigurno isprobati novo pomoćno sredstvo ili materijal?', '<p>Pročitajte tehničke i sigurnosne upute proizvoda te preporuke proizvođača stroja. Kada je primjenjivo, napravite probu na manjem uzorku prije serijske obrade i zabilježite korištene postavke.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 277), (@faq_id, 289), (@faq_id, 300), (@faq_id, 301),
    (@faq_id, 302), (@faq_id, 303), (@faq_id, 314), (@faq_id, 315),
    (@faq_id, 316), (@faq_id, 318), (@faq_id, 319), (@faq_id, 369),
    (@faq_id, 371);

-- 15. Kako odabrati gotov proizvod za personalizaciju?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Kako odabrati gotov proizvod za personalizaciju?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 150, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Kako odabrati gotov proizvod za personalizaciju?', '<p>Usporedite dimenziju i materijal proizvoda s tehnologijom označavanja te provjerite kolika je stvarno dostupna površina za tekst, broj ili logotip. Uzmite u obzir i način pričvršćivanja, mjesto uporabe i potrebnu količinu.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES
    (@faq_id, 288), (@faq_id, 320), (@faq_id, 321), (@faq_id, 322),
    (@faq_id, 323), (@faq_id, 324);

-- 16. Što provjeriti pri odabiru materijala za izradu pečata?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Što provjeriti pri odabiru materijala za izradu pečata?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 160, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Što provjeriti pri odabiru materijala za izradu pečata?', '<p>Materijal uskladite s tehnologijom i modelom opreme, željenom razlučivošću i dubinom reljefa te formatom otisne pločice. Provjerite i kompatibilnost s ljepilom, tintom i podlogama na kojima će se gotov pečat koristiti.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES (@faq_id, 274), (@faq_id, 286);

-- 17. Kako odabrati suhi žig ili kliješta za plombiranje?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Kako odabrati suhi žig ili kliješta za plombiranje?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 170, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Kako odabrati suhi žig ili kliješta za plombiranje?', '<p>Najprije odredite treba li vam reljefni otisak na papiru ili kontrolna oznaka na plombi. Zatim provjerite promjer i položaj otiska, materijal podloge, ručnu ili stolnu izvedbu i koliko često će se alat koristiti.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES (@faq_id, 274), (@faq_id, 287);

-- 18. Kako odabrati jastuk u kutiji?
SET @faq_id := (
    SELECT f.faq_id
    FROM `oc_faq` f
    INNER JOIN `oc_faq_description` fd ON fd.faq_id = f.faq_id
    WHERE fd.language_id = 3
      AND fd.name = 'Kako odabrati jastuk u kutiji?'
    ORDER BY f.faq_id
    LIMIT 1
);
INSERT INTO `oc_faq` (`sort_order`, `status`, `date_added`, `date_modified`)
SELECT 180, 1, NOW(), NOW() WHERE @faq_id IS NULL;
SET @faq_id := COALESCE(@faq_id, LAST_INSERT_ID());
INSERT IGNORE INTO `oc_faq_description`
    (`faq_id`, `language_id`, `name`, `description`)
VALUES
    (@faq_id, 3, 'Kako odabrati jastuk u kutiji?', '<p>Unutarnja tintna površina jastuka treba biti veća od cijele otisne pločice pečata. Nakon odabira dimenzije provjerite je li materijal jastuka prikladan za potrebnu tintu, a tintu uvijek uskladite s podlogom na kojoj otiskujete.</p>');
INSERT INTO `oc_faq_2_category` (`faq_id`, `fcategory_id`)
SELECT @faq_id, @faq_group_id
WHERE NOT EXISTS (
    SELECT 1 FROM `oc_faq_2_category`
    WHERE `faq_id` = @faq_id AND `fcategory_id` = @faq_group_id
);
INSERT IGNORE INTO `oc_faq_to_catalog_category` (`faq_id`, `category_id`)
VALUES (@faq_id, 274), (@faq_id, 284);

-- Kratki rezultat za phpMyAdmin.
SELECT
    (
        SELECT COUNT(*)
        FROM `oc_category_description`
        WHERE `language_id` = 3
          AND TRIM(COALESCE(`short_description`, '')) <> ''
          AND TRIM(COALESCE(`description`, '')) <> ''
          AND TRIM(COALESCE(`meta_description`, '')) <> ''
    ) AS `uredjene_kategorije`,
    (
        SELECT COUNT(DISTINCT fd.faq_id)
        FROM `oc_faq_description` fd
        WHERE fd.language_id = 3
          AND fd.name IN (
              'Kako odabrati odgovarajući pečat?',
              'Koja je razlika između Trodat Printy i Professional linije?',
              'Kako pronaći kompatibilan rezervni jastučić?',
              'Mijenja li se cijeli pečat kada se jastučić istroši?',
              'Kako odabrati tintu prema podlozi?',
              'Jesu li sve tinte međusobno zamjenjive?',
              'Koji je materijal prikladan za moj stroj za graviranje?',
              'Što provjeriti prije odabira materijala za vanjsku primjenu?',
              'Koja je razlika između plastičnih, akrilnih i metalnih ploča?',
              'Kako odabrati plastičnu ploču za lasersko ili mehaničko graviranje?',
              'Kako odabrati veličinu Trodat Printy pečata?',
              'Koja je razlika između tekstualnog i datumskog pečata?',
              'Čemu služi pomoćni pribor za graviranje?',
              'Kako sigurno isprobati novo pomoćno sredstvo ili materijal?',
              'Kako odabrati gotov proizvod za personalizaciju?',
              'Što provjeriti pri odabiru materijala za izradu pečata?',
              'Kako odabrati suhi žig ili kliješta za plombiranje?',
              'Kako odabrati jastuk u kutiji?'
          )
    ) AS `faq_pitanja`,
    (
        SELECT COUNT(*)
        FROM `oc_faq_to_catalog_category`
    ) AS `faq_veze_s_kategorijama`;
