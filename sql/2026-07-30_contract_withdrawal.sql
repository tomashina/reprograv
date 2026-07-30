-- Repro-Grav: electronic unilateral contract withdrawal module
-- Date: 2026-07-30
-- Target: OpenCart 3, DB prefix oc_, default store 0
-- Idempotent: safe to run again after deployment.
-- If the live database uses a prefix other than oc_, replace every `oc_`
-- occurrence in this file before importing it.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `oc_contract_withdrawal` (
  `contract_withdrawal_id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(32) NOT NULL,
  `submission_key` varchar(64) NOT NULL,
  `customer_id` int(11) NOT NULL DEFAULT 0,
  `order_id` int(11) NOT NULL DEFAULT 0,
  `order_number` varchar(80) NOT NULL,
  `full_name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(80) NOT NULL DEFAULT '',
  `address_line` varchar(255) NOT NULL,
  `postal_code` varchar(32) NOT NULL,
  `city` varchar(120) NOT NULL,
  `country_code` char(2) NOT NULL DEFAULT 'HR',
  `contract_date` date DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `items` text NOT NULL,
  `note` text,
  `declaration` text NOT NULL,
  `request_snapshot` mediumtext NOT NULL,
  `snapshot_hash` varchar(64) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'received',
  `internal_note` text,
  `language_id` int(11) NOT NULL DEFAULT 0,
  `submitted_at` datetime NOT NULL,
  `consumer_notified_at` datetime DEFAULT NULL,
  `admin_notified_at` datetime DEFAULT NULL,
  `notification_error` text,
  `handled_by` int(11) NOT NULL DEFAULT 0,
  `handled_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `ip_address` varchar(64) NOT NULL DEFAULT '',
  `user_agent` varchar(512) NOT NULL DEFAULT '',
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`contract_withdrawal_id`),
  UNIQUE KEY `reference` (`reference`),
  UNIQUE KEY `submission_key` (`submission_key`),
  KEY `order_number` (`order_number`),
  KEY `email` (`email`),
  KEY `status_submitted_at` (`status`,`submitted_at`),
  KEY `customer_id` (`customer_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @rg_hr_language_id := (
  SELECT `language_id`
  FROM `oc_language`
  WHERE `code` = 'hr-hr'
  LIMIT 1
);

INSERT INTO `oc_seo_url` (`store_id`, `language_id`, `query`, `keyword`)
SELECT 0, @rg_hr_language_id, 'information/contract_withdrawal', 'jednostrani-raskid-ugovora'
WHERE @rg_hr_language_id IS NOT NULL
ON DUPLICATE KEY UPDATE `keyword` = VALUES(`keyword`);

INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`)
SELECT 0, 'contract_withdrawal', 'contract_withdrawal_status', '1', 0
WHERE NOT EXISTS (
  SELECT 1 FROM `oc_setting`
  WHERE `store_id` = 0 AND `key` = 'contract_withdrawal_status'
);

INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`)
SELECT 0, 'contract_withdrawal', 'contract_withdrawal_admin_email',
       COALESCE((SELECT `value` FROM `oc_setting` WHERE `store_id` = 0 AND `key` = 'config_email' LIMIT 1), ''),
       0
WHERE NOT EXISTS (
  SELECT 1 FROM `oc_setting`
  WHERE `store_id` = 0 AND `key` = 'contract_withdrawal_admin_email'
);

INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`)
SELECT 0, 'contract_withdrawal', 'contract_withdrawal_return_address',
       COALESCE((SELECT `value` FROM `oc_setting` WHERE `store_id` = 0 AND `key` = 'config_address' LIMIT 1), ''),
       0
WHERE NOT EXISTS (
  SELECT 1 FROM `oc_setting`
  WHERE `store_id` = 0 AND `key` = 'contract_withdrawal_return_address'
);

INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`)
SELECT 0, 'contract_withdrawal', 'contract_withdrawal_instructions', '', 0
WHERE NOT EXISTS (
  SELECT 1 FROM `oc_setting`
  WHERE `store_id` = 0 AND `key` = 'contract_withdrawal_instructions'
);

UPDATE `oc_user_group`
SET `permission` = JSON_ARRAY_APPEND(`permission`, '$.access', 'sale/contract_withdrawal')
WHERE JSON_VALID(`permission`)
  AND (`user_group_id` = 1 OR LOWER(`name`) IN ('administrator', 'admin'))
  AND NOT JSON_CONTAINS(JSON_EXTRACT(`permission`, '$.access'), JSON_QUOTE('sale/contract_withdrawal'));

UPDATE `oc_user_group`
SET `permission` = JSON_ARRAY_APPEND(`permission`, '$.modify', 'sale/contract_withdrawal')
WHERE JSON_VALID(`permission`)
  AND (`user_group_id` = 1 OR LOWER(`name`) IN ('administrator', 'admin'))
  AND NOT JSON_CONTAINS(JSON_EXTRACT(`permission`, '$.modify'), JSON_QUOTE('sale/contract_withdrawal'));

COMMIT;
