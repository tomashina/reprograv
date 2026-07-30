<?php
/**
 * Refresh the OpenCart modification cache from the command line.
 *
 * Run from the project root:
 *   php tools/refresh_ocmod.php
 *
 * This bootstraps the admin application and calls OpenCart's own modification
 * refresh controller. The CLI-only permission object is acceptable here
 * because anyone able to execute this file already has file and DB access.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$project_root = dirname(__DIR__);
$admin_root = $project_root . '/upload/admin';

if (!is_file($admin_root . '/config.php')) {
    fwrite(STDERR, "OpenCart admin config was not found.\n");
    exit(1);
}

chdir($admin_root);

define('VERSION', '3.0.3.8');

require_once $admin_root . '/config.php';

if (is_file($project_root . '/upload/env.php')) {
    require_once $project_root . '/upload/env.php';
}

require_once DIR_SYSTEM . 'startup.php';

$registry = new Registry();

$config = new Config();
$config->load('default');
$config->load('admin');
$registry->set('config', $config);

$log = new Log($config->get('error_filename'));
$registry->set('log', $log);

$event = new Event($registry);
$registry->set('event', $event);

$loader = new Loader($registry);
$registry->set('load', $loader);

$request = new Request();
$request->server['REMOTE_ADDR'] = '127.0.0.1';
$registry->set('request', $request);

class OcmodRefreshComplete extends RuntimeException {}

class OcmodCliResponse {
    public function addHeader($header) {}
    public function setOutput($output) {}
    public function redirect($url, $status = 302) {
        throw new OcmodRefreshComplete();
    }
}

$registry->set('response', new OcmodCliResponse());

$db = new DB(
    $config->get('db_engine'),
    $config->get('db_hostname'),
    $config->get('db_username'),
    $config->get('db_password'),
    $config->get('db_database'),
    $config->get('db_port')
);
$registry->set('db', $db);

$settings = $db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '0'");
foreach ($settings->rows as $setting) {
    $config->set(
        $setting['key'],
        $setting['serialized'] ? json_decode($setting['value'], true) : $setting['value']
    );
}

if ($config->get('config_timezone')) {
    date_default_timezone_set($config->get('config_timezone'));
}

$session = new stdClass();
$session->data = array('user_token' => 'cli');
$registry->set('session', $session);

$registry->set('cache', new Cache($config->get('cache_engine'), $config->get('cache_expire')));
$registry->set('url', new Url($config->get('site_url'), $config->get('site_ssl')));

$language_code = $config->get('config_admin_language') ?: 'en-gb';
$language = new Language($language_code);
$language->load($language_code);
$registry->set('language', $language);
$registry->set('document', new Document());

class OcmodCliUser {
    public function hasPermission($key, $value) {
        return $key === 'modify' && $value === 'marketplace/modification';
    }
}

$registry->set('user', new OcmodCliUser());

require_once DIR_APPLICATION . 'controller/marketplace/modification.php';

$controller = new ControllerMarketplaceModification($registry);

try {
    $controller->refresh(array('redirect' => 'marketplace/modification'));
} catch (OcmodRefreshComplete $exception) {
    // OpenCart normally exits after its final redirect.
}

fwrite(STDOUT, "OpenCart modification cache refreshed.\n");
