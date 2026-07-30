-- Strukturirani sadržajni blokovi za OpenCart Information stranice.
-- Predviđeni prefix baze: oc_
-- Import je idempotentan za information_id = 34: postojeći blokovi te stranice
-- brišu se i ponovno popunjavaju, dok originalni information_description ostaje
-- netaknut kao fallback.

CREATE TABLE IF NOT EXISTS `oc_information_block` (
  `information_block_id` int(11) NOT NULL AUTO_INCREMENT,
  `information_id` int(11) NOT NULL,
  `image` varchar(1024) NOT NULL DEFAULT '',
  `layout` varchar(32) NOT NULL DEFAULT 'image_right',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`information_block_id`),
  KEY `information_id_sort_order` (`information_id`,`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oc_information_block_description` (
  `information_block_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `image_alt` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`information_block_id`,`language_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oc_information_block_action` (
  `information_block_action_id` int(11) NOT NULL AUTO_INCREMENT,
  `information_block_id` int(11) NOT NULL,
  `type` varchar(16) NOT NULL DEFAULT 'link',
  `url` varchar(2048) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `mask` varchar(255) NOT NULL DEFAULT '',
  `new_window` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`information_block_action_id`),
  KEY `block_sort_order` (`information_block_id`,`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oc_information_block_action_description` (
  `information_block_action_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`information_block_action_id`,`language_id`),
  KEY `language_id` (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @information_id := 34;
SET @language_id := COALESCE(
  (SELECT `language_id` FROM `oc_language` WHERE `code` = 'hr-hr' LIMIT 1),
  3
);

DELETE ibad
FROM `oc_information_block_action_description` ibad
INNER JOIN `oc_information_block_action` iba
  ON iba.`information_block_action_id` = ibad.`information_block_action_id`
INNER JOIN `oc_information_block` ib
  ON ib.`information_block_id` = iba.`information_block_id`
WHERE ib.`information_id` = @information_id;

DELETE iba
FROM `oc_information_block_action` iba
INNER JOIN `oc_information_block` ib
  ON ib.`information_block_id` = iba.`information_block_id`
WHERE ib.`information_id` = @information_id;

DELETE ibd
FROM `oc_information_block_description` ibd
INNER JOIN `oc_information_block` ib
  ON ib.`information_block_id` = ibd.`information_block_id`
WHERE ib.`information_id` = @information_id;

DELETE FROM `oc_information_block`
WHERE `information_id` = @information_id;

-- 1. Uvodni Trotec blok
INSERT INTO `oc_information_block`
  (`information_id`, `image`, `layout`, `status`, `sort_order`, `date_added`, `date_modified`)
VALUES
  (@information_id, 'https://www.repro-grav.com/image/catalog/kategorije/TRO_Speedy400_2021_Mila_rechts_Laserkopf_rechts_ret.jpg', 'image_right', 1, 0, NOW(), NOW());
SET @block_id := LAST_INSERT_ID();

INSERT INTO `oc_information_block_description`
  (`information_block_id`, `language_id`, `title`, `description`, `image_alt`)
VALUES
  (
    @block_id,
    @language_id,
    '',
    '<p><img alt="Laserski strojevi – Trotec - Logo" src="https://www.repro-grav.com/image/catalog/brand-images/trotec.png" /></p>

<p><strong>Tvrtka Trotec</strong> globalno je priznati proizvođač laserskih sustava koji nudi širok asortiman uređaja za <strong>lasersko graviranje, rezanje i označavanje</strong>. Njihovi proizvodi nalaze primjenu u mnogobrojnim područjima kao što su automobilska industrija, medicinska tehnologija, izrada satova, natpisa, pečata, arhitektonskih modela, označavanje elektroničkih komponenti, personaliziranog nakita pa čak i u obrazovnim sustavima.&nbsp;</p>

<p>Trotec razvija i <a href="https://www.troteclaser.com/hr/laserski-strojevi/laserski-softver"><strong>vlastiti softver, Ruby&reg;</strong></a>, koji je dizajniran za unapređenje korisničkog iskustva. Ruby omogućava lakše upravljanje laserom, optimizaciju procesa i bolje praćenje rada strojeva, sve to s jednostavnim sučeljem koje je prilagodljivo potrebama korisnika.</p>

<p>Laseri su prepoznatljivi po svojoj <strong>visokoj brzini i preciznosti</strong> te obrađuju širok spektar materijala, uključujući <strong>drvo, plastiku, kožu, staklo, gumu, metal, tekstile, pa čak i papir.</strong></p>',
    'Laserski strojevi – Trotec'
  );

INSERT INTO `oc_information_block_action`
  (`information_block_id`, `type`, `url`, `filename`, `mask`, `new_window`, `sort_order`)
VALUES
  (@block_id, 'link', 'https://www.troteclaser.com/hr/', '', '', 1, 0);
SET @action_id := LAST_INSERT_ID();

INSERT INTO `oc_information_block_action_description`
  (`information_block_action_id`, `language_id`, `label`)
VALUES
  (@action_id, @language_id, 'Saznajte više');

-- 2. Serija Speedy
INSERT INTO `oc_information_block`
  (`information_id`, `image`, `layout`, `status`, `sort_order`, `date_added`, `date_modified`)
VALUES
  (@information_id, 'https://www.repro-grav.com/image/catalog/banneri/speedy.jpg', 'image_right', 1, 10, NOW(), NOW());
SET @block_id := LAST_INSERT_ID();

INSERT INTO `oc_information_block_description`
  (`information_block_id`, `language_id`, `title`, `description`, `image_alt`)
VALUES
  (
    @block_id,
    @language_id,
    'Serija Speedy',
    '<p>Svi strojevi iz serije Speedy opremljeni su InPack-Technology&trade; i našim naprednim laserskim softverom, <a href="https://www.troteclaser.com/hr/laserski-strojevi/laserski-softver">Ruby&reg;</a>.</p>

<p>Speedy sustavi dostupni su s CO2 RF laserom (valna duljina 10600 nm) ili kao Speedy flexx, koji uključuje dodatni vlaknasti laser s valnom duljinom od 1060 nm.</p>

<p>Jedine iznimke su Speedy 50, koji je dostupan isključivo s CO2 laserskom opcijom, i <a href="https://www.troteclaser.com/hr/laserski-strojevi/diodni-laser-speedy-100-cross">Speedy 100 cross</a>, koji je opremljen diodnim laserom.</p>',
    'Laserski strojevi serije Speedy'
  );

INSERT INTO `oc_information_block_action`
  (`information_block_id`, `type`, `url`, `filename`, `mask`, `new_window`, `sort_order`)
VALUES
  (@block_id, 'file', 'https://www.troteclaser.com/static/pdf/speedy-series/2025-04-Brochure-Speedy-EN.pdf', '', '', 1, 0);
SET @action_id := LAST_INSERT_ID();

INSERT INTO `oc_information_block_action_description`
  (`information_block_action_id`, `language_id`, `label`)
VALUES
  (@action_id, @language_id, 'Preuzmite katalog');

-- 3. Serija Q
INSERT INTO `oc_information_block`
  (`information_id`, `image`, `layout`, `status`, `sort_order`, `date_added`, `date_modified`)
VALUES
  (@information_id, 'https://www.repro-grav.com/image/catalog/kategorije/Q-serija.jpg', 'image_right', 1, 20, NOW(), NOW());
SET @block_id := LAST_INSERT_ID();

INSERT INTO `oc_information_block_description`
  (`information_block_id`, `language_id`, `title`, `description`, `image_alt`)
VALUES
  (
    @block_id,
    @language_id,
    'Serija Q',
    '<p>Q serija obuhvaća pouzdane laserske strojeve za proizvodnju, kombinira sve Trotec vrline koje su vam potrebne za učinkovito lasersko rezanje.</p>

<p>S radnom površinom do 1300 x 900 mm i snagom lasera do 120 W, reže i gravira razne materijale: obradite akril i drvo (režite do 15 mm), tekstil, papir ili karton za znakove, rukotvorine, modele ili ukrasne predmete.</p>',
    'Laserski strojevi serije Q'
  );

INSERT INTO `oc_information_block_action`
  (`information_block_id`, `type`, `url`, `filename`, `mask`, `new_window`, `sort_order`)
VALUES
  (@block_id, 'file', 'https://www.troteclaser.com/static/pdf/q-series/catalog-q-series-TEC-EN.pdf', '', '', 1, 0);
SET @action_id := LAST_INSERT_ID();

INSERT INTO `oc_information_block_action_description`
  (`information_block_action_id`, `language_id`, `label`)
VALUES
  (@action_id, @language_id, 'Preuzmite katalog');

-- 4. Serija SpeedMarker
INSERT INTO `oc_information_block`
  (`information_id`, `image`, `layout`, `status`, `sort_order`, `date_added`, `date_modified`)
VALUES
  (@information_id, 'https://www.repro-grav.com/image/catalog/banneri/speedmarker.jpg', 'image_right', 1, 30, NOW(), NOW());
SET @block_id := LAST_INSERT_ID();

INSERT INTO `oc_information_block_description`
  (`information_block_id`, `language_id`, `title`, `description`, `image_alt`)
VALUES
  (
    @block_id,
    @language_id,
    'Serija SpeedMarker',
    '<p>Laserski marker serije SpeedMarker jamče visoku produktivnost, podržavaju procese automatizacije i nadahnjuje jednostavnošću rukovanja.</p>

<p>Beskrajne mogućnosti uz AdvancedScripting, seriju SpeedMarker čini pravim izborom za proizvođače strojeva, alatničare, gravere.</p>

<p>Učinkovita izrada trajnih oznaka na gotovo svim metalima i, s opcijom MOPA, na mnogim plastikama, je zajamčeno.</p>',
    'Laserski marker serije SpeedMarker'
  );

INSERT INTO `oc_information_block_action`
  (`information_block_id`, `type`, `url`, `filename`, `mask`, `new_window`, `sort_order`)
VALUES
  (@block_id, 'file', 'https://www.troteclaser.com/static/pdf/speedmarker/2023-05-Catalog-SpeedMarker-TEC-EN.pdf', '', '', 1, 0);
SET @action_id := LAST_INSERT_ID();

INSERT INTO `oc_information_block_action_description`
  (`information_block_action_id`, `language_id`, `label`)
VALUES
  (@action_id, @language_id, 'Preuzmite katalog');

-- 5. U serija
INSERT INTO `oc_information_block`
  (`information_id`, `image`, `layout`, `status`, `sort_order`, `date_added`, `date_modified`)
VALUES
  (@information_id, 'https://www.repro-grav.com/image/catalog/kategorije/U-serija2.jpg', 'image_right', 1, 40, NOW(), NOW());
SET @block_id := LAST_INSERT_ID();

INSERT INTO `oc_information_block_description`
  (`information_block_id`, `language_id`, `title`, `description`, `image_alt`)
VALUES
  (
    @block_id,
    @language_id,
    'Laser za označivanje U serije',
    '<p>Odlično za lasersko označivanje pojedinačnih komada i malih serija za svega nekoliko sekundi</p>

<p>Kompaktan dizajn, s radnim područjem veličine do 190 x 190 mm (7,5 x 7,5 inča)</p>

<p>Savršen izbor za tvrtke za izradu promotivnih proizvoda, gravere specijalizane za personalizaciju te proizvodnju</p>',
    'Laser za označivanje U serije'
  );

INSERT INTO `oc_information_block_action`
  (`information_block_id`, `type`, `url`, `filename`, `mask`, `new_window`, `sort_order`)
VALUES
  (@block_id, 'file', 'https://www.troteclaser.com/static/pdf/u-series/2024-03-brochure-U300-en.pdf', '', '', 1, 0);
SET @action_id := LAST_INSERT_ID();

INSERT INTO `oc_information_block_action_description`
  (`information_block_action_id`, `language_id`, `label`)
VALUES
  (@action_id, @language_id, 'Preuzmite katalog');

SELECT
  ib.`information_id`,
  COUNT(DISTINCT ib.`information_block_id`) AS `blocks_imported`,
  COUNT(DISTINCT iba.`information_block_action_id`) AS `actions_imported`
FROM `oc_information_block` ib
LEFT JOIN `oc_information_block_action` iba
  ON iba.`information_block_id` = ib.`information_block_id`
WHERE ib.`information_id` = @information_id
GROUP BY ib.`information_id`;
