-- Repro-Grav SEO/AI migration
-- Date: 2026-07-29
-- Target: OpenCart 3, default store_id = 0, Croatian language_id = 3
-- This script is idempotent and can be run again after deployment.

START TRANSACTION;

-- Keep only canonical product, category, information and native blog sitemaps.
UPDATE `oc_setting`
SET `value` = '["product","category","information","blog"]',
    `serialized` = 1
WHERE `store_id` = 0
  AND `code` = 'feed_boost_sitemap'
  AND `key` = 'feed_boost_sitemap_item';

-- Remove the obsolete public price-validity setting and the duplicate
-- free-form LocalBusiness block. Code also enforces these rules.
UPDATE `oc_setting`
SET `value` = '0',
    `serialized` = 0
WHERE `store_id` = 0
  AND `key` IN ('hb_snippets_pricevalid', 'hb_snippets_local_enable');

-- Use the actual company logo and Repro-Grav social profile in Organization schema.
UPDATE `oc_setting`
SET `value` = 'catalog/reprograv-logo-blue.png',
    `serialized` = 0
WHERE `store_id` = 0
  AND `key` = 'hb_snippets_logo';

UPDATE `oc_setting`
SET `value` = '["https:\\/\\/www.instagram.com\\/reprograv.hr\\/"]',
    `serialized` = 1
WHERE `store_id` = 0
  AND `key` = 'hb_snippets_socials';

-- Prices must not be embedded in public product names or SEO URLs. Logged-in
-- customers still see the real price from OpenCart's price fields. Product
-- 27437 is currently disabled, but is cleaned now so a later activation cannot
-- accidentally publish the promotional price in its name and URL.
UPDATE `oc_product_description`
SET `name` = REPLACE(
    REPLACE(
        REPLACE(`name`, ' - 30€', ''),
        ' - 1€',
        ''
    ),
    ' - Posebna akcijska cijena - 6000 €',
    ''
)
WHERE `product_id` IN (27414, 27415, 27437);

UPDATE `oc_seo_url`
SET `keyword` = CASE `query`
    WHEN 'product_id=27414' THEN '6-4926-stari-rezervni-jastucic-jastuk-u-kutiji-rasprodaja'
    WHEN 'product_id=27415' THEN 'gravometall-srebro-mesing-rasprodaja'
    WHEN 'product_id=27437' THEN 'm20-x'
    ELSE `keyword`
END
WHERE `store_id` = 0
  AND `language_id` = 3
  AND `query` IN ('product_id=27414', 'product_id=27415', 'product_id=27437');

-- Reserve icon space in the homepage service module to prevent layout shifts.
UPDATE `oc_module`
SET `setting` = REPLACE(
    REPLACE(
        REPLACE(
            REPLACE(
                `setting`,
                'image\\/prezentacija-red2.svg&quot; class=&quot;ikona-black&quot; alt=&quot;Prezentacija&quot;&gt;',
                'image\\/prezentacija-red2.svg&quot; class=&quot;ikona-black&quot; alt=&quot;Prezentacija&quot; width=&quot;45&quot; height=&quot;40&quot;&gt;'
            ),
            'image\\/odrzavanje.svg&quot; class=&quot;ikona-black&quot; alt=&quot;Prezentacija&quot;&gt;',
            'image\\/odrzavanje.svg&quot; class=&quot;ikona-black&quot; alt=&quot;Održavanje&quot; width=&quot;50&quot; height=&quot;40&quot;&gt;'
        ),
        'image\\/podrska.svg&quot; class=&quot;ikona-black&quot; alt=&quot;Prezentacija&quot;&gt;',
        'image\\/podrska.svg&quot; class=&quot;ikona-black&quot; alt=&quot;Podrška&quot; width=&quot;50&quot; height=&quot;40&quot;&gt;'
    ),
    'image\\/zastupstva.svg&quot; class=&quot;ikona-black&quot; alt=&quot;Prezentacija&quot;&gt;',
    'image\\/zastupstva.svg&quot; class=&quot;ikona-black&quot; alt=&quot;Zastupništva&quot; width=&quot;50&quot; height=&quot;40&quot;&gt;'
)
WHERE `module_id` = 116
  AND `name` = 'Ikone';

-- Correct the homepage "Laserski strojevi" call-to-action. The old value
-- contained the non-existent "...-trotech" slug and sent visitors to a 404.
UPDATE `oc_module`
SET `setting` = REPLACE(
    `setting`,
    'laserski-strojevi-trotech',
    'laserski-strojevi-trotec'
)
WHERE `module_id` = 126
  AND `code` = 'basel_content';

-- The GDPR banner remains functional but no longer blocks the first paint.
-- First collapse the one duplicate form that could be produced by an older
-- revision of this migration.
UPDATE `oc_modification`
SET `xml` = REPLACE(
    `xml`,
    '<link rel="preload" as="style" href="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.min.css" onload="this.onload=null;this.rel=''stylesheet''"><noscript><link rel="preload" as="style" href="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.min.css" onload="this.onload=null;this.rel=''stylesheet''"><noscript><link href="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.min.css" rel="stylesheet"></noscript></noscript>',
    '<link rel="preload" as="style" href="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.min.css" onload="this.onload=null;this.rel=''stylesheet''"><noscript><link href="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.min.css" rel="stylesheet"></noscript>'
)
WHERE `code` = 'mpgdpr';

UPDATE `oc_modification`
SET `xml` = REPLACE(
    REPLACE(
        `xml`,
        '<link href="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.min.css" rel="stylesheet">',
        '<link rel="preload" as="style" href="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.min.css" onload="this.onload=null;this.rel=''stylesheet''"><noscript><link href="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.min.css" rel="stylesheet"></noscript>'
    ),
    '<script type="text/javascript" src="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.js"></script>',
    '<script defer src="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.js"></script>'
)
WHERE `code` = 'mpgdpr'
  AND `xml` NOT LIKE '%<link rel="preload" as="style" href="catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.min.css"%';

-- The FAQ stylesheet is tiny and not needed for the homepage first paint.
UPDATE `oc_modification`
SET `xml` = REPLACE(
    `xml`,
    '<link href="catalog/view/theme/default/stylesheet/faq.css" rel="stylesheet">',
    '<link rel="preload" as="style" href="catalog/view/theme/default/stylesheet/faq.css" onload="this.onload=null;this.rel=''stylesheet''"><noscript><link href="catalog/view/theme/default/stylesheet/faq.css" rel="stylesheet"></noscript>'
)
WHERE `code` = 'TMD Faq Module'
  AND `xml` NOT LIKE '%<link rel="preload" as="style" href="catalog/view/theme/default/stylesheet/faq.css"%';

-- Keep Basel's generated product-card data aligned with the guest privacy rule.
-- The active OCMOD XML is stored in the database, so uploading the source XML
-- alone is not enough on an already-installed shop.
UPDATE `oc_modification`
SET `xml` = REPLACE(
    REPLACE(
        REPLACE(
            REPLACE(
                REPLACE(
                    `xml`,
                    '<search><![CDATA[if (!is_null($result[''special'']) && (float)$result[''special''] >= 0) {]]></search>',
                    '<search><![CDATA[$data[''products''][] = array(]]></search>'
                ),
                '<search><![CDATA[if ((float)$result[''special'']) {]]></search>',
                '<search><![CDATA[$data[''products''][] = array(]]></search>'
            ),
            'if ((float)$result[''special'']) {',
            CONCAT(
                '$commercial_data_visible = !$this->config->get(''config_customer_price'') || $this->customer->isLogged();',
                CHAR(10),
                '		if ($commercial_data_visible && (float)$result[''special'']) {'
            )
        ),
        'if ( (float)$result[''special''] && ($this->config->get(''salebadge_status'')) ) {',
        'if ($commercial_data_visible && (float)$result[''special''] && ($this->config->get(''salebadge_status'')) ) {'
    ),
    '''quantity''  => $result[''quantity''],',
    CONCAT(
        '''commercial_data_visible'' => $commercial_data_visible,',
        CHAR(10),
        '		''quantity''  => $commercial_data_visible ? $result[''quantity''] : null,'
    )
)
WHERE `code` = 'basel_theme';

-- Native blog URL prefix.
INSERT INTO `oc_seo_url` (`store_id`, `language_id`, `query`, `keyword`)
VALUES (0, 3, 'extension/blog/home', 'blog')
ON DUPLICATE KEY UPDATE `keyword` = VALUES(`keyword`);

-- Active native blog posts found in the audited database.
INSERT INTO `oc_seo_url` (`store_id`, `language_id`, `query`, `keyword`)
VALUES
    (0, 3, 'blog_id=35', 'ciscenje-optike-trotec-lasera'),
    (0, 3, 'blog_id=40', 'reiner-jetstamp-mobilni-pisaci-oznacavanje'),
    (0, 3, 'blog_id=41', 'm20-x-graverski-stroj-personalizacija'),
    (0, 3, 'blog_id=42', 'pecat-olovke-profesionalci')
ON DUPLICATE KEY UPDATE `keyword` = VALUES(`keyword`);

-- Register the WebP generator as an installed module. The module preserves
-- original uploads and writes generated files only below image/cache.
INSERT INTO `oc_extension` (`type`, `code`)
SELECT 'module', 'webp_generator'
WHERE NOT EXISTS (
    SELECT 1
    FROM `oc_extension`
    WHERE `type` = 'module'
      AND `code` = 'webp_generator'
);

-- Grant the generator route only to groups that can already administer
-- extensions. OpenCart stores permissions as JSON in this installation.
UPDATE `oc_user_group`
SET `permission` = JSON_ARRAY_APPEND(
    `permission`,
    '$.access',
    'extension/module/webp_generator'
)
WHERE JSON_VALID(`permission`)
  AND JSON_CONTAINS(
      JSON_EXTRACT(`permission`, '$.access'),
      JSON_QUOTE('marketplace/extension')
  )
  AND NOT JSON_CONTAINS(
      JSON_EXTRACT(`permission`, '$.access'),
      JSON_QUOTE('extension/module/webp_generator')
  );

UPDATE `oc_user_group`
SET `permission` = JSON_ARRAY_APPEND(
    `permission`,
    '$.modify',
    'extension/module/webp_generator'
)
WHERE JSON_VALID(`permission`)
  AND JSON_CONTAINS(
      JSON_EXTRACT(`permission`, '$.modify'),
      JSON_QUOTE('marketplace/extension')
  )
  AND NOT JSON_CONTAINS(
      JSON_EXTRACT(`permission`, '$.modify'),
      JSON_QUOTE('extension/module/webp_generator')
  );

COMMIT;

-- File-system follow-up after this SQL:
-- 1. Refresh OpenCart Extensions > Modifications.
-- 2. Delete old sitemaps/sitemap_*_category_product*.xml files.
-- 3. In Extensions > Feeds > Boost Sitemap, generate the selected sitemaps.
