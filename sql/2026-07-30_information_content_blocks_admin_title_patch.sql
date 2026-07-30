-- Dodatni patch za instalacije na kojima je već importan
-- 2026-07-30_information_content_blocks.sql.
--
-- Ovaj SQL NE briše niti ponovno uvozi blokove. Samo:
-- 1. dodaje stupac za interni admin naziv ako ne postoji
-- 2. prvom bloku stranice information_id = 34 postavlja naziv "Trotec – uvod"

SET @admin_title_column_exists := (
  SELECT COUNT(*)
  FROM `information_schema`.`COLUMNS`
  WHERE `TABLE_SCHEMA` = DATABASE()
    AND `TABLE_NAME` = 'oc_information_block'
    AND `COLUMN_NAME` = 'admin_title'
);

SET @admin_title_column_sql := IF(
  @admin_title_column_exists = 0,
  'ALTER TABLE `oc_information_block` ADD `admin_title` varchar(255) NOT NULL DEFAULT '''' AFTER `information_id`',
  'SELECT 1'
);

PREPARE admin_title_column_statement FROM @admin_title_column_sql;
EXECUTE admin_title_column_statement;
DEALLOCATE PREPARE admin_title_column_statement;

UPDATE `oc_information_block`
SET `admin_title` = 'Trotec – uvod',
    `date_modified` = NOW()
WHERE `information_id` = 34
ORDER BY `sort_order`, `information_block_id`
LIMIT 1;

SELECT
  `information_block_id`,
  `information_id`,
  `admin_title`
FROM `oc_information_block`
WHERE `information_id` = 34
ORDER BY `sort_order`, `information_block_id`
LIMIT 1;
