<?php
/**
 * Installs or refreshes the contract-withdrawal database objects.
 *
 * Run from the project root:
 *   php tools/install_contract_withdrawal.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$project_root = dirname(__DIR__);
require $project_root . '/upload/config.php';
require_once DIR_SYSTEM . 'library/db.php';
require_once DIR_SYSTEM . 'library/db/mysqli.php';

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$prefix = DB_PREFIX;

$db->query("CREATE TABLE IF NOT EXISTS `{$prefix}contract_withdrawal` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$language_query = $db->query("SELECT language_id FROM `{$prefix}language` WHERE code = 'hr-hr' LIMIT 1");
$language_id = $language_query->num_rows ? (int)$language_query->row['language_id'] : 0;

if ($language_id) {
    $seo_query = $db->query("SELECT seo_url_id FROM `{$prefix}seo_url` WHERE store_id = 0 AND language_id = '{$language_id}' AND query = 'information/contract_withdrawal' LIMIT 1");
    if ($seo_query->num_rows) {
        $db->query("UPDATE `{$prefix}seo_url` SET keyword = 'jednostrani-raskid-ugovora' WHERE seo_url_id = '" . (int)$seo_query->row['seo_url_id'] . "'");
    } else {
        $db->query("INSERT INTO `{$prefix}seo_url` SET store_id = 0, language_id = '{$language_id}', query = 'information/contract_withdrawal', keyword = 'jednostrani-raskid-ugovora'");
    }
}

$settings = array(
    'contract_withdrawal_status' => '1',
    'contract_withdrawal_admin_email' => '',
    'contract_withdrawal_return_address' => '',
    'contract_withdrawal_instructions' => ''
);

$config_rows = $db->query("SELECT `key`, `value` FROM `{$prefix}setting` WHERE store_id = 0 AND `key` IN ('config_email', 'config_address')")->rows;
$config = array();
foreach ($config_rows as $row) {
    $config[$row['key']] = $row['value'];
}
$settings['contract_withdrawal_admin_email'] = isset($config['config_email']) ? $config['config_email'] : '';
$settings['contract_withdrawal_return_address'] = isset($config['config_address']) ? $config['config_address'] : '';

foreach ($settings as $key => $value) {
    $existing = $db->query("SELECT setting_id FROM `{$prefix}setting` WHERE store_id = 0 AND `key` = '" . $db->escape($key) . "' LIMIT 1");
    if (!$existing->num_rows) {
        $db->query("INSERT INTO `{$prefix}setting` SET store_id = 0, code = 'contract_withdrawal', `key` = '" . $db->escape($key) . "', value = '" . $db->escape($value) . "', serialized = 0");
    }
}

$groups = $db->query("SELECT user_group_id, permission FROM `{$prefix}user_group` WHERE user_group_id = 1 OR LOWER(name) IN ('administrator', 'admin')")->rows;
foreach ($groups as $group) {
    $permission = json_decode($group['permission'], true);
    if (!is_array($permission)) {
        continue;
    }
    foreach (array('access', 'modify') as $type) {
        if (!isset($permission[$type]) || !is_array($permission[$type])) {
            $permission[$type] = array();
        }
        if (!in_array('sale/contract_withdrawal', $permission[$type], true)) {
            $permission[$type][] = 'sale/contract_withdrawal';
        }
    }
    $db->query("UPDATE `{$prefix}user_group` SET permission = '" . $db->escape(json_encode($permission)) . "' WHERE user_group_id = '" . (int)$group['user_group_id'] . "'");
}

fwrite(STDOUT, "Contract withdrawal module installed.\n");
