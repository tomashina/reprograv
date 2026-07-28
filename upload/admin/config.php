<?php

require_once dirname(__DIR__, 2) . '/environment.php';

$project_root = dirname(__DIR__, 2);
$upload_root = dirname(__DIR__);
$app_url = rtrim(project_env('APP_URL', 'https://reprograv.test'), '/') . '/';
$storage_root = rtrim(project_env('DIR_STORAGE', $project_root . '/storage'), '/') . '/';

// HTTP
define('HTTP_SERVER', $app_url . 'admin/');
define('HTTP_CATALOG', $app_url);

// HTTPS
define('HTTPS_SERVER', $app_url . 'admin/');
define('HTTPS_CATALOG', $app_url);

// DIR
define('DIR_APPLICATION', $upload_root . '/admin/');
define('DIR_SYSTEM', $upload_root . '/system/');
define('DIR_IMAGE', $upload_root . '/image/');
define('DIR_STORAGE', $storage_root);
define('DIR_CATALOG', $upload_root . '/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', project_env('DB_DRIVER', 'mysqli'));
define('DB_HOSTNAME', project_env('DB_HOSTNAME', '127.0.0.1'));
define('DB_USERNAME', project_env('DB_USERNAME', 'root'));
define('DB_PASSWORD', project_env('DB_PASSWORD'));
define('DB_DATABASE', project_env('DB_DATABASE', 'reprograv'));
define('DB_PORT', project_env('DB_PORT', '3306'));
define('DB_PREFIX', project_env('DB_PREFIX', 'oc_'));

// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
